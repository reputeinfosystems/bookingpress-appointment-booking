/**
 * booking-form-host.js — the Lite booking form, expressed as a payment host.
 *
 * This is the booking form's implementation of the contract in
 * `src/assets/js/payments/host-registry.js`. Everything here is a thin adapter
 * over seams the form already had (the slot API, the bus, `payableNow`), so no
 * form behaviour changes — the point is that a gateway can now reach those seams
 * WITHOUT knowing it is talking to the booking form.
 *
 * Read it as the reference implementation: Complete Payment, Gift Card and
 * Package each write one of these, and then every gateway works on them.
 */

// Side-effect import so the registry exists before we register into it,
// regardless of script module execution order.
import '../../payments/host-registry.js';
import { watch } from 'vue';

/** Server context id. Must match the PHP `BookingFormContext::ID`. */
const CONTEXT_ID = 'booking_form';

/**
 * Where a gateway's field is drawn. The booking form puts it directly above the
 * Book button on the Summary step, which is the only place in this form where a
 * card field makes sense — so the host decides it, not the gateway.
 */
const SLOT = 'summary-step:above-actions';

/**
 * Where a gateway that owns its own action control draws it — the footer, in
 * place of the Book button. See `mountAction` on the host contract for why a
 * gateway has to ask for this rather than the form inferring it from `mode`.
 */
const ACTION_SLOT = 'summary-step:action';

/**
 * @param {object} handle The instance handle built in app.js
 *                        (`{ instanceId, state, bus, submission, ... }`).
 *
 * @returns {function} unregister
 */
export function registerBookingFormHost(handle) {
  if (typeof window === 'undefined') return () => {};

  const BPP = window.BookingPressPayments;
  if (!BPP || typeof BPP.registerHost !== 'function') {
    // Lite is mid-upgrade, or this file loaded without the registry. The form
    // itself is unaffected; only gateway mounting is lost, and a gateway that
    // needs a host will say so itself.
    return () => {};
  }

  const { instanceId, state, bus, submission } = handle;

  /** The D25 number. Resolved by Lite, never by a gateway. */
  const payable = () => {
    const fn = window.BookingPressFormV3 && window.BookingPressFormV3.payableNow;
    return typeof fn === 'function' ? Number(fn(state)) || 0 : 0;
  };

  /**
   * Render into a slot, and on release EMPTY every node that was handed out.
   *
   * Stopping the slot factory only prevents FUTURE renders. Whatever the
   * gateway already drew stays in the node, and gateways do not clear it
   * themselves — Authorize.Net's release just drops its reference. So picking
   * Authorize.Net and then PayPal left the card form on screen under PayPal.
   * The host owns the slot, so the host cleans it.
   *
   * The factory's teardown is ALWAYS kept and called: `renderInSlot` appends
   * to a per-slot list and de-duplicates only by function identity, so a
   * gateway that mounts and releases repeatedly would otherwise stack up one
   * dead factory per past selection.
   *
   * @param {string}   slotName
   * @param {function} render
   * @param {function} [onRelease]
   * @returns {function} release
   */
  function renderOwned(slotName, render, onRelease) {
    let disposed = false;
    const nodes = [];
    const api = window.BookingPressFormV3;
    if (!api || typeof api.renderInSlot !== 'function') return () => {};

    const stop = api.renderInSlot(instanceId, slotName, (ctx) => {
      if (disposed) return;
      if (ctx.node && nodes.indexOf(ctx.node) === -1) nodes.push(ctx.node);
      render(ctx.node);
    });

    return () => {
      disposed = true;
      try { if (typeof stop === 'function') stop(); } catch (e) { /* noop */ }
      nodes.forEach((node) => {
        try { node.innerHTML = ''; } catch (e) { /* noop */ }
      });
      nodes.length = 0;
      if (typeof onRelease === 'function') {
        try { onRelease(); } catch (e) { /* noop */ }
      }
    };
  }

  return BPP.registerHost({
    contextId: CONTEXT_ID,
    instanceId,

    mount(render) {
      // The slot factory re-runs whenever the step re-mounts, so `render` may
      // be called with a NEW node more than once. Gateways are told this in the
      // contract; it is why they key their state by node.
      return renderOwned(SLOT, render);
    },

    /**
     * Hand the footer's primary control to the gateway.
     *
     * `state.gatewayOwnsAction` is what the Summary step's template reads to
     * hide its Book button. It is set here rather than by the gateway so the
     * form keeps sole ownership of its own DOM, and cleared on unmount so a
     * gateway that is deselected — or that fails to render — cannot strand the
     * page with no way to book.
     */
    mountAction(render) {
      const api = window.BookingPressFormV3;
      if (!api || typeof api.renderInSlot !== 'function') return () => {};

      try { state.gatewayOwnsAction = true; } catch (e) { /* noop */ }

      return renderOwned(ACTION_SLOT, render, () => {
        state.gatewayOwnsAction = false;
      });
    },

    /**
     * The payload the form would submit right now, with add-ons given their
     * chance at it.
     *
     * Delegates to `useSubmission.buildPayload()` rather than assembling a
     * second copy here — the old PayPal code in SummaryStep did assemble one,
     * and a payload built in two places is a payload that will differ in two
     * places.
     */
    submitPayload() {
      if (typeof submission.buildPayload !== 'function') {
        // Lite mid-upgrade. Reporting a cancel is the safe answer: a gateway
        // that cannot get a payload must not stage a purchase with a guess.
        return { payload: null, cancelled: true, silent: true };
      }
      return submission.buildPayload();
    },

    onBeforeSubmit(cb) {
      // `e` is passed through untouched: `e.payload` must stay the SAME object
      // so a gateway's mutations reach the submission, and `e.cancel` must be
      // the host's own, so `cancel({ silent: true })` keeps its meaning (D26).
      return bus.on('bp-v3:before-submit', (e) => {
        if (!e || !e.payload || typeof e.cancel !== 'function') return;
        cb(e);
      });
    },

    onMethodChange(cb) {
      return bus.on('bp-v3:payment-method-selected', () => {
        cb(String(state.appointment_step_form_data.selected_payment_method || ''));
      });
    },

    watchPayable(cb) {
      // The HOST owns the reactivity. This is what lets a gateway module drop
      // its `import { watch } from 'vue'` — a gateway should not need the
      // form's framework, and a future host may not even be a Vue app.
      return watch(payable, (amount) => cb(amount));
    },

    payableNow: payable,

    selectedMethod() {
      return String(state.appointment_step_form_data.selected_payment_method || '');
    },

    gatewayConfig(gatewayId) {
      const config = state.config || {};

      // Preferred: the `client` block the gateway's own PHP published through
      // `get_enabled_methods()`. This is the payment-layer shape and the one a
      // new gateway should use.
      const methods = Array.isArray(config.payment_methods) ? config.payment_methods : [];
      const method = methods.find((m) => m && String(m.id) === String(gatewayId));
      if (method && method.client) return method.client;

      // Fallback: the separately localized `bookingpress_<id>` blob that
      // released add-ons still ship. Kept so a gateway can move onto the host
      // contract WITHOUT also having to move its config publishing in the same
      // release — the two migrations are independent.
      return config['bookingpress_' + gatewayId] || null;
    },

    nonces() {
      return state.nonces || {};
    },

    paymentRoot() {
      return String((state.rest && state.rest.paymentRoot) || '');
    },

    setBusy(isBusy) {
      const flag = !!isBusy;
      try { submission.isSubmitting.value = flag; } catch (e) { /* noop */ }
      try { state.isSubmitting = flag; } catch (e) { /* noop */ }
    },

    clearError() {
      try { submission.submitError.value = ''; } catch (e) { /* noop */ }
      try { state.submitError = ''; } catch (e) { /* noop */ }
    },

    finish(envelope) {
      // Drive the same redirect / in-built thank-you path as an on-site
      // booking. Lite never emitted `after-submit` for a submission a gateway
      // took over, so this emit is the only one the redirection feature sees.
      const url = (envelope && (envelope.redirect_data || envelope.redirect_url)) || '';
      try {
        submission.submitOk.value = true;
        state.submitOk = true;
        if (url) {
          submission.redirectUrl.value = url;
          state.redirectUrl = url;
        }
      } catch (e) { /* noop */ }
      try { submission.isSubmitting.value = false; } catch (e) { /* noop */ }
      try { state.isSubmitting = false; } catch (e) { /* noop */ }
      try {
        bus.emit('bp-v3:after-submit', { instanceId, response: envelope, ok: true });
      } catch (e) { /* noop */ }
    },

    fail(message) {
      // Deliberately does NOT arm clearSubmissionError(): that 3s timer would
      // blank a card decline, which Stripe usually answers well inside it.
      // See section 8, "The 3-second error-wipe trap".
      try { submission.submitError.value = String(message || 'Payment failed.'); } catch (e) { /* noop */ }
      try { submission.isSubmitting.value = false; } catch (e) { /* noop */ }
      try { state.isSubmitting = false; } catch (e) { /* noop */ }
    },
  });
}

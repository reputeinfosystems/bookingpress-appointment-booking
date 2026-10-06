/**
 * host-registry.js — the client-side mirror of `PaymentContextInterface`.
 *
 * WHY THIS EXISTS
 * ---------------
 * The server side of the payment layer already solved N gateways x M contexts:
 * `StripeGateway::prepare()` knows nothing about bookings, entries or forms, so
 * registering a new context gives every gateway that context for free.
 *
 * The client side did not. Each page shipped its own registry —
 * `window.BookingPressFormV3` for the Lite booking form,
 * `window.BookingPressCompletePaymentPage` for the Pro Complete Payment page —
 * with the same API, a different global and a different state shape. So every
 * gateway shipped one JS module PER PAGE, branching internally on which page it
 * was running in. Gift Card and Package would each have added another global and
 * another module per gateway. That is the original N x M problem surviving in
 * JavaScript after it was removed from PHP.
 *
 * A HOST is a page that can collect a payment. It registers itself here,
 * describing how to mount a field, how to intercept its submit, what is payable
 * and how to report success or failure. A GATEWAY subscribes and wires itself to
 * every host it is given. Neither names the other.
 *
 *   Host (owns the page)                 Gateway (owns the rails)
 *   --------------------                 ------------------------
 *   registerHost({ contextId, ... })  -> eachHost(function (host) { ... })
 *
 * Adding a context is then one host registration and no gateway release. Adding
 * a gateway is one module that works on every page, present and future.
 *
 * WHY A NEUTRAL GLOBAL
 * --------------------
 * Not folded into `BookingPressFormV3`: the booking form owns a great deal that
 * has nothing to do with payment (steps, slots, timeslots, readiness), and
 * making a Gift Card page pretend to be a form instance is how the two state
 * shapes diverged in the first place. This global carries only what a payment
 * needs. Existing registries are untouched.
 *
 * ORDERING
 * --------
 * Script module execution order is not guaranteed, so a gateway may subscribe
 * before or after its host registers. `eachHost()` therefore replays hosts that
 * already exist AND delivers ones that arrive later. Gateways never poll and
 * never depend on load order.
 */

(function initPaymentHostRegistry() {
  if (typeof window === 'undefined') return;
  if (window.BookingPressPayments && window.BookingPressPayments.registerHost) return;

  var hosts = {};
  var subscribers = [];

  /**
   * Members a host MUST provide. A host missing any of these is rejected with a
   * console error rather than registered half-working: a gateway that mounts
   * against a broken host fails later, further from the cause.
   *
   * `mountAction` is deliberately NOT here — see the note on it below. A host
   * that cannot surrender its action control is still a perfectly good host for
   * every gateway that does not want one.
   */
  var REQUIRED = [
    'contextId',
    'instanceId',
    'mount',
    'onBeforeSubmit',
    'onMethodChange',
    'watchPayable',
    'payableNow',
    'selectedMethod',
    'gatewayConfig',
    'nonces',
    'paymentRoot',
    'setBusy',
    'clearError',
    'finish',
    'fail',
  ];

  function hostKey(host) {
    return String(host.contextId) + ':' + String(host.instanceId);
  }

  function validate(host) {
    if (!host || typeof host !== 'object') return 'not an object';
    for (var i = 0; i < REQUIRED.length; i++) {
      var k = REQUIRED[i];
      if (k === 'contextId' || k === 'instanceId') {
        if (!host[k] && host[k] !== 0) return 'missing ' + k;
      } else if (typeof host[k] !== 'function') {
        return 'missing ' + k + '()';
      }
    }
    return '';
  }

  /**
   * Call a subscriber without letting it take the others down with it. One
   * misbehaving gateway must not prevent the rest from mounting — the same rule
   * the form bus follows.
   */
  function deliver(cb, host) {
    try {
      cb(host);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error('[bp-payments] host subscriber failed', { contextId: host.contextId, error: e });
    }
  }

  window.BookingPressPayments = {
    /**
     * Contract version. A gateway can refuse to wire against a registry older
     * than it expects instead of failing in some subtler way later.
     *
     * 2 — added the optional `mountAction` host member.
     *
     * Bumped rather than left at 1 because the registry genuinely grew, but a
     * gateway should still FEATURE-DETECT an optional member rather than gate
     * on this number: the version tells you what the registry offers, not what
     * the individual host in your hand chose to implement.
     */
    version: 2,

    /** Live hosts, keyed `contextId:instanceId`. Read-only by convention. */
    hosts: hosts,

    /**
     * Register a page as able to collect a payment.
     *
     * @param {object} host
     * @param {string} host.contextId       Server context id — `booking_form`,
     *                                      `complete_payment`, ... Must match the
     *                                      id the PHP context registered, because
     *                                      it is sent to `payment-v3/prepare`.
     * @param {string} host.instanceId      Distinguishes two hosts on one page.
     * @param {function} host.mount         `mount(render)` — `render(node)` is
     *                                      called with a DOM element to draw into,
     *                                      and again if the host re-renders that
     *                                      region. Returns an unmount function.
     * @param {function} host.onBeforeSubmit `cb({ payload, cancel })` before the
     *                                      host submits. `cancel({ silent: true })`
     *                                      means the gateway is TAKING OVER rather
     *                                      than aborting — see D26 / section 8.
     *                                      Returns an unsubscribe function.
     * @param {function} host.onMethodChange `cb(methodId)` when the selected
     *                                      payment method changes. Returns an
     *                                      unsubscribe function.
     * @param {function} host.watchPayable  `cb(amountMajor)` when the payable
     *                                      changes. The HOST owns the reactivity,
     *                                      so a gateway needs no framework import.
     *                                      Returns an unsubscribe function.
     * @param {function} host.payableNow    `() => number` in MAJOR units. The host
     *                                      resolves it (D25); a gateway that
     *                                      computes a price is a bug.
     * @param {function} host.selectedMethod `() => string` gateway id, '' if none.
     * @param {function} host.gatewayConfig `(gatewayId) => object|null` — the
     *                                      server-published client config.
     * @param {function} host.nonces        `() => { wpRestNonce, formNonce,
     *                                      instanceToken }`.
     * @param {function} host.paymentRoot   `() => string` — the `payment-v3` REST
     *                                      root, trailing slash optional.
     * @param {function} host.setBusy       `(boolean) => void` — the host's own
     *                                      submitting state, so the gateway drives
     *                                      the existing spinner rather than
     *                                      inventing one.
     * @param {function} host.clearError    `() => void` — clear any visible error
     *                                      before a fresh attempt. Separate from
     *                                      `fail('')` because that also releases
     *                                      the busy state.
     * @param {function} host.finish        `(envelope) => void` — settled; the host
     *                                      drives its own thank-you / redirect.
     * @param {function} host.fail          `(message) => void` — show an error and
     *                                      release the submitting state.
     *
     * @param {function} [host.mountAction] OPTIONAL. `mountAction(render)` — the
     *                                      gateway draws its OWN action control
     *                                      where the host's primary button sits,
     *                                      and the host hides that button for as
     *                                      long as the returned unmount has not
     *                                      been called. Same `render(node)` shape
     *                                      as `mount`.
     *
     *                                      WHY THIS EXISTS, AND WHY IT IS NOT
     *                                      DERIVED FROM `mode`
     *                                      ----------------------------------
     *                                      Two gateway shapes hide behind the
     *                                      single word "popup":
     *
     *                                        - PayPal Smart Buttons REPLACE the
     *                                          host's button. `createOrder` fires
     *                                          from PayPal's own control, so the
     *                                          host's submit never runs and
     *                                          `onBeforeSubmit` never fires.
     *                                        - Razorpay / Paystack / Mollie
     *                                          overlays open ON the host's button
     *                                          click. They intercept submit like
     *                                          Stripe does and must NOT take the
     *                                          button away.
     *
     *                                      A `mode` string cannot tell those
     *                                      apart, and guessing wrong either
     *                                      leaves a page with no way to pay or
     *                                      with two competing buttons. So the
     *                                      gateway says which it is, by calling
     *                                      this or not calling it.
     *
     *                                      A gateway MUST feature-detect
     *                                      (`typeof host.mountAction === 'function'`)
     *                                      and MUST degrade to `onBeforeSubmit`
     *                                      when absent, so an older host keeps
     *                                      working.
     *
     * @param {function} [host.submitPayload] OPTIONAL, and the companion to
     *                                      `mountAction`. `() => ({ payload,
     *                                      cancelled, silent })` — the payload
     *                                      the host WOULD submit right now.
     *
     *                                      A gateway that owns the action
     *                                      control never sees `onBeforeSubmit`,
     *                                      so it has no other way to obtain
     *                                      one. Building it inside the gateway
     *                                      is not an option: the payload is
     *                                      assembled from the host's own state
     *                                      AND from whatever Cart, Coupon and
     *                                      the other add-ons contribute through
     *                                      `bp-v3:before-submit`. A host
     *                                      implementing this MUST fire that
     *                                      event as part of building it, and
     *                                      report a cancellation rather than
     *                                      swallowing it.
     *
     * @returns {function} unregister
     */
    registerHost: function (host) {
      var problem = validate(host);
      if (problem) {
        // eslint-disable-next-line no-console
        console.error('[bp-payments] host rejected: ' + problem, host);
        return function () {};
      }

      var key = hostKey(host);
      hosts[key] = host;

      for (var i = 0; i < subscribers.length; i++) {
        deliver(subscribers[i], host);
      }

      return function unregister() {
        if (hosts[key] === host) delete hosts[key];
      };
    },

    /** @returns {object|null} */
    getHost: function (contextId, instanceId) {
      return hosts[String(contextId) + ':' + String(instanceId)] || null;
    },

    /**
     * Wire a gateway to every host — those already registered and those that
     * register later. This is the ONLY entry point a gateway needs, and the
     * reason gateways are indifferent to script load order.
     *
     * @param {function} cb `cb(host)`, called once per host.
     *
     * @returns {function} unsubscribe (stops FUTURE deliveries only)
     */
    eachHost: function (cb) {
      if (typeof cb !== 'function') return function () {};

      subscribers.push(cb);

      // Replay what already exists. Snapshot the keys first: a subscriber that
      // registers a host of its own must not be delivered its own host mid-loop.
      var keys = Object.keys(hosts);
      for (var i = 0; i < keys.length; i++) {
        if (hosts[keys[i]]) deliver(cb, hosts[keys[i]]);
      }

      return function unsubscribe() {
        var at = subscribers.indexOf(cb);
        if (at !== -1) subscribers.splice(at, 1);
      };
    },
  };
})();

export {};

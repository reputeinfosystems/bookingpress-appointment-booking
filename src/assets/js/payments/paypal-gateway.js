/**
 * paypal-gateway.js — the PayPal gateway client.
 *
 * WHAT IT DOES
 * ------------
 * Owns the client side of the two PayPal options (server side:
 * `src/Vue3/Payments/Gateways/PayPalGateway.php`):
 *
 *   - **Smart Buttons** (`popup`): PayPal's SDK renders its OWN buttons in place
 *     of the host's action control. `createOrder` calls `payment-v3/prepare`
 *     (which stages, seals and creates the PayPal order in one round trip),
 *     the buyer approves in PayPal's window, and `onApprove` captures and then
 *     calls `payment-v3/confirm` to verify and finalise.
 *   - **Standard / redirect**: `payment-v3/prepare` returns an `_xclick` form
 *     POST, which is submitted to paypal.com. Settlement is by IPN.
 *
 * WHY THIS FILE EXISTS
 * --------------------
 * PayPal's client used to live INSIDE the Lite booking form — about 200 lines
 * across `SummaryStep.js`, plus a hardcoded `#paypal-button-container` and an
 * `isPayPalPopupSelected` branch in its template. Pro's Complete Payment page
 * carried its own copy. That is the N x M problem the payment layer removed
 * from PHP, still present in JavaScript, and in the worst possible place: in
 * CORE, where every context inherits it.
 *
 * Now the form names no gateway and this module names no page.
 *
 * IT IS THE SECOND IMPLEMENTATION OF THE HOST CONTRACT, AND IT BENT IT
 * --------------------------------------------------------------------
 * Stripe was the first, and the contract fitted it exactly — which proved
 * nothing, because the contract was written from it. PayPal is the gateway
 * that was supposed to find the gap, and it did: Smart Buttons REPLACE the
 * host's submit button rather than intercepting it, so `onBeforeSubmit` never
 * fires in popup mode and there was no way for a gateway to say so.
 *
 * `host.mountAction()` is the (optional) member added for it. See the note on
 * it in `host-registry.js` for why a `mode === 'popup'` check could not have
 * served instead — Razorpay and Paystack are also "popup" and want the exact
 * opposite behaviour.
 *
 * FORM_POST IS EXERCISED HERE FIRST
 * ---------------------------------
 * `PrepareResult` has always had four handshakes; until this module, three
 * were used. PayPal Standard is the first `form_post`, and the shape is
 * general: a server-built field map auto-submitted to a third-party endpoint,
 * which is how most older regional processors still work.
 *
 * DELIVERY
 * --------
 * Enqueued as a script module by Lite's `Assets.php`. Its only dependency is
 * the host registry — deliberately NOT any page's app, so the same file serves
 * every host. The PayPal JS SDK (`window.paypal`) is a classic script enqueued
 * server-side in popup mode, so it runs before this deferred module.
 */

import './host-registry.js';

(function () {
  if (typeof window === 'undefined') return;

  var BPP = window.BookingPressPayments;
  if (!BPP || typeof BPP.eachHost !== 'function') {
    // eslint-disable-next-line no-console
    console.warn('[bp-v3][paypal] payment host registry unavailable — update BookingPress');
    return;
  }

  var GATEWAY_ID = 'paypal';

  // Per-HOST state, keyed `contextId:instanceId` — not by instance alone,
  // because one page may host more than one context.
  var stores = {};
  function store(host) {
    var key = host.contextId + ':' + host.instanceId;
    if (!stores[key]) {
      stores[key] = { node: null, wired: false, rendered: false, releaseAction: null, reference: '', token: '' };
    }
    return stores[key];
  }

  function config(host) {
    return host.gatewayConfig(GATEWAY_ID) || null;
  }

  function isSelected(host) {
    return String(host.selectedMethod()) === GATEWAY_ID;
  }

  /**
   * Popup mode means PayPal draws the action control. Redirect mode keeps the
   * host's own button and intercepts its submit, exactly like Stripe.
   */
  function isPopup(host) {
    var cfg = config(host);
    return !!cfg && 'popup' === String(cfg.mode);
  }

  /**
   * Whether there is anything to charge right now.
   *
   * `host.payableNow()` is the host's own resolution of the D25 number. NEVER
   * re-derive it here: every price-moving module — Staff, Service Extras,
   * Multiple Quantity, Coupon, Deposit, Tax, Tip — would have to be
   * reimplemented in this file to keep up.
   *
   * At zero, the gateway steps aside entirely (D26) so the host finalises the
   * free booking inline. Taking over would be WRONG rather than merely
   * awkward: prepare()'s zero short circuit finalises as PAID with
   * `paid_amount` 0, which is right for a 100% coupon but not for a 0 deposit.
   */
  function hasPayable(host) {
    return (Number(host.payableNow()) || 0) > 0;
  }

  // --- REST helper ---------------------------------------------------------

  async function paymentPost(host, route, body) {
    var root = String(host.paymentRoot() || '').replace(/\/+$/, '');
    if (!root) {
      return { status: 0, ok: false, error: { message: 'Payment routes are unavailable. Please update BookingPress.' } };
    }
    var nonces = host.nonces() || {};
    var payload = Object.assign({}, body || {}, {
      bp_v3_nonce: nonces.formNonce,
      bp_v3_instance_token: nonces.instanceToken,
      instanceId: host.instanceId,
    });

    var res = await fetch(root + '/' + route, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonces.wpRestNonce },
      body: JSON.stringify(payload),
    });

    var json = null;
    try { json = await res.json(); } catch (e) { json = { ok: false, error: { message: 'Server returned non-JSON.' } }; }
    return { status: res.status, ok: !!(json && json.ok), data: json && json.data, error: json && json.error };
  }

  // --- FORM_POST -----------------------------------------------------------

  /**
   * Build and submit a form from the server's field map, handing the browser
   * to the gateway.
   *
   * The previous implementation had the SERVER render the whole `<form>` as
   * HTML and injected it with `innerHTML` — which silently does not execute
   * the trailing self-submitting `<script>`, so the form had to be located and
   * submitted by hand anyway. Building it from a field map is both safer (no
   * markup from the server is parsed) and gateway-agnostic: `form_post` is a
   * handshake every gateway can now use without shipping HTML.
   */
  function submitFormPost(action, fields) {
    if (typeof document === 'undefined' || !action) return false;

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = String(action);
    form.style.display = 'none';

    var keys = Object.keys(fields || {});
    for (var i = 0; i < keys.length; i++) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = keys[i];
      // Always a string: a field map arriving with a number or null would
      // otherwise stringify to 'null' inside the POST body.
      input.value = fields[keys[i]] === null || fields[keys[i]] === undefined ? '' : String(fields[keys[i]]);
      form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    return true;
  }

  // --- Redirect mode (host keeps its button, we intercept submit) ----------

  async function runRedirectPayment(host, payload) {
    // Yield once so the host's synchronous cancel branch has finished before
    // we take the UI over.
    await Promise.resolve();
    host.clearError();
    host.setBusy(true);

    var prep = await paymentPost(host, 'prepare', {
      context: host.contextId,
      gateway: GATEWAY_ID,
      payload: payload,
    });
    if (!prep.ok || !prep.data || !prep.data.prepare) {
      host.fail((prep.error && prep.error.message) || 'Could not start the payment.');
      return;
    }

    var kind = prep.data.prepare.kind || '';
    var data = prep.data.prepare.data || {};

    // Nothing left to pay — a 100% coupon, a full gift card, a free service.
    // The server already finalised; there is no PayPal round trip at all.
    if (kind === 'settled') {
      host.finish(prep.data.envelope || {});
      return;
    }

    if (kind === 'form_post') {
      if (!submitFormPost(data.action, data.fields)) {
        host.fail('Could not open PayPal. Please try again or contact the site owner.');
      }
      // Deliberately leaves the busy state ON: the browser is navigating away
      // and releasing it would flash an interactive form during the handoff.
      return;
    }

    // A gateway configured for redirect that answers anything else is a
    // server/client mismatch, not a payment failure. Say so plainly rather
    // than showing the customer a generic decline.
    if (kind === 'redirect' && data.url) {
      window.location.href = data.url;
      return;
    }

    host.fail('Unexpected payment response.');
  }

  // --- Popup mode (PayPal draws the action control) ------------------------

  function renderButtons(host, node) {
    var s = store(host);

    if (typeof window.paypal === 'undefined' || !window.paypal || typeof window.paypal.Buttons !== 'function') {
      // eslint-disable-next-line no-console
      console.warn('[bp-v3][paypal] PayPal JS SDK not loaded');
      return;
    }
    if (!node || !node.isConnected) return;

    // Wipe any previous render before re-mounting — re-entrant safe.
    node.innerHTML = '';

    try {
      window.paypal.Buttons({
        createOrder: async function () {
          host.clearError();

          // Every abort below reports its OWN reason and then returns a falsy
          // order id. The SDK reacts to that by rejecting with a generic
          // "Expected an order id to be passed" and routing it to `onError`,
          // which would overwrite the real message — so the customer saw the
          // SDK's complaint instead of "Server total 405 does not match client
          // 487.5", and every staging failure looked identical.
          //
          // This flag tells `onError` that the failure is already on screen.
          s.handledError = false;

          // The host owns the payload. Asking it rather than rebuilding the
          // form data here is what lets this module serve Complete Payment,
          // Gift Card and Package without knowing any of their state shapes.
          if (typeof host.submitPayload !== 'function') {
            host.fail('This page cannot start a PayPal payment. Please update BookingPress.');
            s.handledError = true;
            return 0;
          }

          var built = host.submitPayload() || {};
          if (built.cancelled) {
            // An add-on refused the submission. A silent cancel means some
            // OTHER gateway is taking over, which should be impossible while
            // PayPal is the selected method — so say nothing in that case and
            // simply abort rather than contradicting it on screen.
            if (!built.silent) host.fail('Submission was cancelled by an add-on.');
            s.handledError = true;
            return 0;
          }

          var prep = await paymentPost(host, 'prepare', {
            context: host.contextId,
            gateway: GATEWAY_ID,
            payload: built.payload || {},
          });

          if (!prep.ok || !prep.data || !prep.data.prepare) {
            host.fail((prep.error && prep.error.message) || 'Failed to create PayPal order');
            // Returning a falsy order id makes the SDK abort without opening
            // its window, which is what we want after a staging failure.
            s.handledError = true;
            return 0;
          }

          var kind = prep.data.prepare.kind || '';
          var data = prep.data.prepare.data || {};

          // Zero payable reached the button anyway. Finalise inline and abort
          // the PayPal flow rather than trying to charge nothing.
          if (kind === 'settled') {
            host.finish(prep.data.envelope || {});
            s.handledError = true;
            return 0;
          }

          if (kind !== 'client_action' || !data.order_id) {
            host.fail('Unexpected payment response.');
            s.handledError = true;
            return 0;
          }

          // Kept for the confirm leg. The context's single-use token binds the
          // capture to THIS staged purchase.
          s.reference = (prep.data.reference && prep.data.reference.reference) || '';
          s.token = prep.data.token || '';

          return data.order_id;
        },

        onCancel: function () {
          // Legacy parity: a popup cancel is a soft no-op. The staged entry
          // stays `pending_payment` and the customer can retry; we surface a
          // hint so the silence is not confusing.
          host.fail('PayPal payment was cancelled. You can retry below.');
        },

        onError: function (err) {
          // eslint-disable-next-line no-console
          console.warn('[bp-v3][paypal] SDK error', err);

          // We aborted deliberately and already showed the real reason. The SDK
          // follows a falsy order id with its own "Expected an order id to be
          // passed", and reporting that would replace a precise server message
          // with a meaningless one — the difference between "Server total 405
          // does not match client 487.5" and an error nobody can act on.
          if (s.handledError) {
            s.handledError = false;
            return;
          }

          host.fail((err && err.message) || 'PayPal payment failed.');
        },

        onApprove: function (data, actions) {
          return actions.order.capture().then(async function (orderData) {
            host.setBusy(true);
            try {
              // The server re-fetches the order from PayPal and reads the
              // correlation key off `purchase_units[0].reference_id`, so what
              // is sent here LOCATES the order and never describes it.
              //
              // The key names are the gateway's, not ours:
              // `PayPalGateway::order_id_from_payload()` looks for
              // `bookingpress_payment_res` (the capture body) and falls back to
              // `paypal_order_id`. Both are sent — `data.orderID` comes
              // straight from the SDK and survives a capture body that arrives
              // in an unexpected shape.
              var finalRes = await paymentPost(host, 'confirm', {
                context: host.contextId,
                gateway: GATEWAY_ID,
                reference: s.reference,
                token: s.token,
                gateway_payload: {
                  bookingpress_payment_res: orderData,
                  paypal_order_id: (data && data.orderID) || '',
                },
              });

              if (!finalRes.ok || !finalRes.data) {
                host.fail(
                  (finalRes.error && finalRes.error.message)
                  || 'Payment could not be confirmed. Please contact the site owner.'
                );
                return;
              }

              host.finish(finalRes.data);
            } catch (e) {
              host.fail((e && e.message) || 'Payment failed.');
            }
          });
        },

        style: { layout: 'vertical', color: 'gold', shape: 'pill', label: 'paypal', fundingicons: false },
      }).render(node);

      s.rendered = true;
    } catch (e) {
      // SDK threw during init — leave the node empty so the customer can
      // re-select to retry. Deliberately NO hand-rolled fallback button: one
      // carrying the `summery-book-appointment-btn` class used to stay in the
      // DOM and shadow the SDK render.
      // eslint-disable-next-line no-console
      console.warn('[bp-v3][paypal] paypal.Buttons render threw:', e);
      s.rendered = false;
    }
  }

  /** Claim the host's action control and draw the Smart Buttons into it. */
  function takeAction(host) {
    var s = store(host);
    if (s.releaseAction) return; // already held

    if (typeof host.mountAction !== 'function') {
      // An older host that cannot surrender its button. Popup mode needs one,
      // so say so rather than leaving a page whose Book button silently does
      // the wrong thing.
      // eslint-disable-next-line no-console
      console.warn('[bp-v3][paypal] host does not support mountAction — PayPal popup unavailable on this page');
      return;
    }

    s.releaseAction = host.mountAction(function (node) {
      s.node = node;
      s.rendered = false;
      renderButtons(host, node);
    });
  }

  /** Give the action control back to the host. */
  function releaseAction(host) {
    var s = store(host);
    if (s.node) {
      try { s.node.innerHTML = ''; } catch (e) { /* noop */ }
    }
    if (s.releaseAction) {
      try { s.releaseAction(); } catch (e) { /* noop */ }
      s.releaseAction = null;
    }
    s.node = null;
    s.rendered = false;
  }

  /**
   * Reconcile what PayPal should be showing right now.
   *
   * Called on every signal that could change the answer — selection, payable,
   * a re-render — because the three interact: popup mode at a payable of 0
   * must give the button back so the host can finalise the free booking, and
   * take it again if a coupon is removed.
   */
  function sync(host) {
    if (isSelected(host) && isPopup(host) && hasPayable(host)) {
      takeAction(host);
    } else {
      releaseAction(host);
    }
  }

  // --- Wire every host -----------------------------------------------------

  BPP.eachHost(function (host) {
    var s = store(host);
    if (s.wired) return;
    s.wired = true;

    host.onMethodChange(function () { sync(host); });

    host.watchPayable(function () {
      // In popup mode the amount lives on the server-created order, which is
      // made fresh inside `createOrder` on every click — so unlike Stripe's
      // element there is nothing to update in place. All that matters is
      // whether the buttons should be present at all.
      sync(host);
    });

    host.onBeforeSubmit(function (e) {
      if (!isSelected(host)) return;

      // Popup mode never reaches the host's submit: PayPal's own button
      // drives the flow. If we get here in popup mode the customer somehow
      // used the host's control, and taking over would stage a second entry.
      if (isPopup(host)) return;

      // Nothing to pay NOW — step aside and let the host finalise inline (D26).
      if (!hasPayable(host)) return;

      var payload = Object.assign({}, e.payload);

      // Silent: we are taking the submission over, not aborting it. A plain
      // cancel() would show "Submission was cancelled by an add-on" AND arm a
      // 3s timer that blanks the error.
      e.cancel({ silent: true });

      runRedirectPayment(host, payload).catch(function (err) {
        host.fail((err && err.message) || 'Payment failed.');
      });
    });

    // Initial reconcile — the host may already have PayPal selected when this
    // module loads (a restored form, or a single-gateway site that preselects).
    sync(host);
  });
})();

export {};

/**
 * redirect-gateway.js — the shared client for every hosted-redirect gateway.
 *
 * WHY THIS FILE REPLACES A DOZEN
 * ------------------------------
 * A large share of the gateway catalogue does exactly one thing in the
 * browser: intercept the submit, POST to `payment-v3/prepare`, and send the
 * page to whatever URL comes back. Mollie, Paystack, PayFast, PayUMoney,
 * ECPay, PagSeguro, 2Checkout and others differ only in a gateway id — every
 * decision that actually varies (which API to call, what to sign, how to read
 * the callback) already lives server-side in their `*Gateway.php`.
 *
 * Shipping one JS module per gateway for that was the N x M problem again, one
 * level down: twelve copies of the same forty lines, twelve places for the
 * next payable-zero or silent-cancel fix to be applied eleven times.
 *
 * So there is one module, in Lite, and a redirect gateway opts into it instead
 * of shipping its own.
 *
 * HOW A GATEWAY OPTS IN
 * ---------------------
 *   1. Its `get_descriptor()` puts `'genericRedirect' => true` in the `client`
 *      block.
 *   2. Its feature class enqueues `Assets::MODULE_REDIRECT_GATEWAY` instead of
 *      registering a module of its own.
 *
 * Opting in is EXPLICIT, deliberately. Checking `mode === 'redirect'` would
 * have been less code and wrong: PayPal Standard is also `mode: 'redirect'`
 * and has its own client, so an inferred rule would have two modules racing to
 * handle the same submit. A gateway says so, or it is not handled here.
 *
 * WHAT A GATEWAY GIVES UP BY OPTING IN
 * ------------------------------------
 * Any client-side behaviour at all: no field to mount, no SDK, no overlay, no
 * post-redirect confirm leg. If a gateway needs one of those, it writes its
 * own module — Stripe, PayPal, Square and Razorpay all do.
 *
 * FORM_POST IS HANDLED TOO
 * ------------------------
 * Several older processors cannot take a GET redirect and want a signed field
 * map POSTed to them. That is the same shape from the customer's point of view
 * — the page goes away — so it is handled here rather than in a second module.
 *
 * DELIVERY
 * --------
 * Registered in Lite's `Assets.php` with the host registry as its only
 * dependency, so the same file serves the booking form, Complete Payment, and
 * whatever registers next.
 */

import './host-registry.js';

(function () {
  if (typeof window === 'undefined') return;

  var BPP = window.BookingPressPayments;
  if (!BPP || typeof BPP.eachHost !== 'function') {
    // eslint-disable-next-line no-console
    console.warn('[bp-v3][redirect] payment host registry unavailable — update BookingPress');
    return;
  }

  var wired = {};

  /**
   * The selected gateway, but only when it asked to be handled here.
   *
   * @returns {string} gateway id, or '' when this module should stay out of it.
   */
  function claimedGateway(host) {
    var id = String(host.selectedMethod() || '');
    if (!id) return '';

    var cfg = host.gatewayConfig(id);
    if (!cfg || cfg.genericRedirect !== true) return '';

    return id;
  }

  /**
   * `host.payableNow()` is the host's own resolution of the D25 number. NEVER
   * re-derive it. At zero the gateway steps aside (D26) and the host finalises
   * the free booking inline — taking over would be WRONG rather than merely
   * awkward, because prepare()'s zero short circuit finalises as PAID with
   * paid_amount 0, which is right for a 100% coupon but not for a 0 deposit.
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
   * Build a form from the server's field map and submit it.
   *
   * Built from data rather than injected as server-rendered HTML: no markup
   * from the server is parsed, and there is no self-submitting <script> to
   * work around (innerHTML never runs one, which every gateway that tried it
   * had to discover separately).
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
      var v = fields[keys[i]];
      // Always a string: a field arriving as a number or null would otherwise
      // stringify to 'null' inside the POST body.
      input.value = (v === null || v === undefined) ? '' : String(v);
      form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    return true;
  }

  // --- The pay flow --------------------------------------------------------

  async function runRedirectPayment(host, gatewayId, payload) {
    // Yield once so the host's synchronous cancel branch has finished before
    // we take the UI over.
    await Promise.resolve();
    host.clearError();
    host.setBusy(true);

    var prep = await paymentPost(host, 'prepare', {
      context: host.contextId,
      gateway: gatewayId,
      payload: payload,
    });
    if (!prep.ok || !prep.data || !prep.data.prepare) {
      host.fail((prep.error && prep.error.message) || 'Could not start the payment.');
      return;
    }

    var kind = prep.data.prepare.kind || '';
    var data = prep.data.prepare.data || {};

    // Nothing left to pay — a 100% coupon, a full gift card, a free service.
    // The server already finalised; there is no redirect at all.
    if (kind === 'settled') {
      host.finish(prep.data.envelope || {});
      return;
    }

    if (kind === 'redirect') {
      if (!data.url) {
        host.fail('The payment page could not be opened. Please try again or contact the site owner.');
        return;
      }
      // Busy state deliberately stays ON: the browser is navigating away, and
      // releasing it would flash an interactive form during the handoff.
      window.location.href = data.url;
      return;
    }

    if (kind === 'form_post') {
      if (!submitFormPost(data.action, data.fields)) {
        host.fail('The payment page could not be opened. Please try again or contact the site owner.');
      }
      return;
    }

    // A gateway that opted in here and then answered `client_action` needs a
    // module of its own. Say that plainly rather than showing the customer a
    // generic payment failure.
    // eslint-disable-next-line no-console
    console.error('[bp-v3][redirect] gateway "' + gatewayId + '" returned kind "' + kind + '", which this shared client cannot handle');
    host.fail('Unexpected payment response.');
  }

  // --- Wire every host -----------------------------------------------------

  BPP.eachHost(function (host) {
    var key = host.contextId + ':' + host.instanceId;
    if (wired[key]) return;
    wired[key] = true;

    host.onBeforeSubmit(function (e) {
      // Resolved per submit, not once at wire time: the customer can change
      // payment method between attempts, and one host serves every gateway
      // that opted in.
      var gatewayId = claimedGateway(host);
      if (!gatewayId) return;

      if (!hasPayable(host)) return;

      var payload = Object.assign({}, e.payload);

      // Silent: we are taking the submission over, not aborting it. A plain
      // cancel() would show "Submission was cancelled by an add-on" AND arm a
      // 3s timer that blanks the error.
      e.cancel({ silent: true });

      runRedirectPayment(host, gatewayId, payload).catch(function (err) {
        host.fail((err && err.message) || 'Payment failed.');
      });
    });
  });
})();

export {};

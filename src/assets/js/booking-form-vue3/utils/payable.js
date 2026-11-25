/**
 * payable.js — "amount payable now" seam (client analog of the PHP
 * `bookingpress_form_v3_payable_amount` / `FILTER_PAYABLE_AMOUNT`).
 *
 * The amount the customer is charged NOW is the order total by default, but a
 * partial-payment feature (Pro "Deposit") can make it LESS than the order total
 * while the full price is still owed. This helper runs the order total through a
 * generic, reusable `wp.hooks` filter so the value the client submits as
 * `service_price_without_currency` matches what the server charges + anti-tampers
 * against (the server applies the identical PHP filter). Lite ships no callback,
 * so on a Lite-only install the full order total is returned unchanged.
 *
 * This is intentionally SEPARATE from `effectivePrice` (utils/pricing.js): the
 * effective price is the per-service display price (used in the Service grid and
 * the Summary total), whereas the payable is the order-level charge — only the
 * latter drops to a deposit, so the Service grid keeps showing the full price.
 *
 * @param {object} state              The per-instance reactive state.
 * @param {number|string} fullAmount  The order total (the full price to charge).
 * @param {number|string} serviceId   The selected service the charge is for.
 * @returns {number}
 */
export function payableAmount(state, fullAmount, serviceId) {
  const full = Number(fullAmount) || 0;
  const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
  if (hooks && typeof hooks.applyFilters === 'function') {
    const hookName = 'bookingpress_form_v3_payable_amount';
    const fd = state && state.appointment_step_form_data ? state.appointment_step_form_data : {};
    const trace = !!(fd.is_any_staff_selected || (Array.isArray(fd.clubbed_staff) && fd.clubbed_staff.length));
    const hook = trace && hooks.filters && hooks.filters[hookName];
    const restore = [];

    // Trace each registered payable filter only for multi-staff submissions.
    // Wrapping preserves WordPress hook order/context and is restored immediately
    // after applyFilters, so this diagnostic does not change the amount.
    if (hook && Array.isArray(hook.handlers)) {
      hook.handlers.forEach((handler) => {
        const original = handler.callback;
        restore.push({ handler, callback: original });
        handler.callback = function (...args) {
          const before = args[0];
          const after = original.apply(null, args);
          // eslint-disable-next-line no-console
          console.info('[bp-v3][multi-staff] Payable filter', {
            namespace: handler.namespace,
            priority: handler.priority,
            before,
            after,
          });
          return after;
        };
      });
    }

    let out;
    try {
      out = hooks.applyFilters(hookName, full, {
        state,
        instanceId: state && state.instanceId,
        serviceId: parseInt(serviceId, 10) || 0,
      });
    } finally {
      restore.forEach(({ handler, callback }) => { handler.callback = callback; });
    }
    const n = Number(out);
    if (!isNaN(n) && isFinite(n)) return n;
  }
  return full;
}

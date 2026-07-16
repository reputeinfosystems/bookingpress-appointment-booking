/**
 * pricing.js — effective per-service price seam.
 *
 * The booking form's price for a service is the service's own `servicePrice`
 * by default. This helper runs that base price through a generic, reusable
 * `wp.hooks` filter — the client-side analog of the PHP
 * `bookingpress_form_v3_summary_total` hook — so a Pro feature can override the
 * EFFECTIVE price (e.g. the Staff Member module applies the selected staff's
 * price for the service). Lite ships no callback, so on a Lite-only install the
 * base price is returned unchanged.
 *
 * Used at every site that resolves the price the customer sees / submits — the
 * Summary total, the submit payload, and the PayPal create-order payload — so a
 * single override keeps the display, the anti-tamper hint, and the stored price
 * in lockstep. (The server is authoritative: it re-derives the same total via
 * the PHP `bookingpress_form_v3_summary_total` filter and rejects a mismatch.)
 */

/**
 * @param {object} state      The per-instance reactive state.
 * @param {number|string} basePrice  The service's base price.
 * @param {number|string} serviceId  The service the price is for.
 * @param {string} [context]  WHERE the price is being resolved:
 *   - `'grid'`     — the per-service display on the Service-step cards.
 *   - `'subtotal'` — the pre-discount order cost for the Summary "Subtotal" row:
 *                    service + staff price + Service Extras + Multiple Quantity,
 *                    but WITHOUT any discount (Coupon, Package, MyCred points,
 *                    Offer/Advance Discount). The discount-type consumers opt out
 *                    of this context; extras/quantity/staff stay in.
 *   - `'total'`    — (default) the full order total (Summary Total / submit /
 *                    PayPal), i.e. subtotal minus all discounts.
 *   Passed through to the filter so a consumer can scope itself: the Staff price
 *   override is a genuine per-service price and applies everywhere (incl. the
 *   card); add-ons that only change the ORDER total — Service Extras (+extras),
 *   Multiple Quantity (×persons) — opt out of `'grid'` only; and discount add-ons
 *   (Coupon/Package/MyCred/Offer Discount) opt out of BOTH `'grid'` and
 *   `'subtotal'` so they affect only the Total.
 * @returns {number}
 */
export function effectivePrice(state, basePrice, serviceId, context = 'total') {
  const base = Number(basePrice) || 0;
  const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || null;
  if (hooks && typeof hooks.applyFilters === 'function') {
    const out = hooks.applyFilters('bookingpress_form_v3_effective_price', base, {
      state,
      instanceId: state && state.instanceId,
      serviceId: parseInt(serviceId, 10) || 0,
      context: context || 'total',
    });
    const n = Number(out);
    if (!isNaN(n) && isFinite(n)) return n;
  }
  return base;
}

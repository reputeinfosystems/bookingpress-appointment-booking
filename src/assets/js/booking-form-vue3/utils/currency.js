/**
 * currency.js — price formatting parity with legacy
 * `bookingpress_price_formatter_with_currency_symbol`
 * (class.bookingpress.php:5997+) and its client-side mirror in
 * class.bookingpress_appointment_bookings.php:6157-6168.
 *
 * Inputs come from `state.config`, seeded by StateBuilder.php:
 *   - currencySymbol            actual symbol from countries JSON
 *   - symbolPosition            'before' | 'before_with_space' | 'after' | 'after_with_space'
 *   - priceSeparator            'comma-dot' | 'dot-comma' | 'space-dot' | 'space-comma' | 'Custom'
 *   - priceDecimals             integer (admin "Number of decimals")
 *   - customCommaSeparator      used when priceSeparator === 'Custom' (thousands)
 *   - customThousandSeparator   used when priceSeparator === 'Custom' (decimals)
 */

/**
 * Format an amount honoring the active payment-settings configuration.
 *
 * @param {object} config  state.config — currency formatting fields.
 * @param {number|string} amount
 * @returns {string}
 */
export function formatPrice(config, amount) {
  const cfg = config || {};
  const decimals = Math.max(0, parseInt(cfg.priceDecimals != null ? cfg.priceDecimals : 2, 10) || 0);
  const separator = String(cfg.priceSeparator || 'comma-dot');
  const symbol = String(cfg.currencySymbol || '$');
  const position = String(cfg.symbolPosition || 'before');

  let decimalSep = '.';
  let thousandsSep = ',';
  if (separator === 'dot-comma') {
    decimalSep = ',';
    thousandsSep = '.';
  } else if (separator === 'space-dot') {
    decimalSep = '.';
    thousandsSep = ' ';
  } else if (separator === 'space-comma') {
    decimalSep = ',';
    thousandsSep = ' ';
  } else if (separator === 'Custom') {
    decimalSep = String(cfg.customThousandSeparator != null ? cfg.customThousandSeparator : '.');
    thousandsSep = String(cfg.customCommaSeparator != null ? cfg.customCommaSeparator : ',');
  }

  const price = numberFormat(amount, decimals, decimalSep, thousandsSep);

  if (position === 'before_with_space') return symbol + ' ' + price;
  if (position === 'after')              return price + symbol;
  if (position === 'after_with_space')   return price + ' ' + symbol;
  return symbol + price;
}

/**
 * PHP-style number_format port — same shape as the legacy
 * `vm.bookingpress_number_format` in class.bookingpress_appointment_bookings.php:6173.
 *
 * @param {number|string} number
 * @param {number} decimals
 * @param {string} decPoint
 * @param {string} thousandsSep
 * @returns {string}
 */
function numberFormat(number, decimals, decPoint, thousandsSep) {
  const cleaned = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  const n = !isFinite(+cleaned) ? 0 : +cleaned;
  const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
  const fixed = prec ? n.toFixed(prec) : '' + Math.round(n);
  const parts = fixed.split('.');
  if (parts[0].length > 3) {
    parts[0] = parts[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousandsSep);
  }
  if ((parts[1] || '').length < prec) {
    parts[1] = parts[1] || '';
    parts[1] += new Array(prec - parts[1].length + 1).join('0');
  }
  return parts.join(decPoint);
}

<?php
/**
 * PricingServiceInterface — currency formatting and total math.
 *
 * @package BookingPress\Vue3\Contracts
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.12
 */

namespace BookingPress\Vue3\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replaces the legacy `bookingpress_price_formatter_with_currency_symbol` helper.
 *
 * Lite implements base-price math only. Pro extends total computation via the
 * `bookingpress_form_v3_summary_total` filter (taxes, coupons, deposits).
 */
interface PricingServiceInterface {

	/**
	 * Format an amount with the active currency symbol.
	 *
	 * Reads decimals / separators / position from admin settings; the
	 * `$currency` argument is an override (used by Pro multi-currency).
	 *
	 * @param float       $amount   The numeric amount to format.
	 * @param string|null $currency Optional ISO-4217 code. Null uses site default.
	 *
	 * @return string Localized price string (e.g. `"$50.00"`, `"€ 50,00"`).
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.12
	 */
	public function format_price( $amount, $currency = null );

	/**
	 * Compute the booking total for a given context.
	 *
	 * Lite returns the bare service price (rounded per `price_number_of_decimals`).
	 * Pro decorates via filter.
	 *
	 * @param array $context Computation context:
	 *                       - `service_id` int Required.
	 *                       - `form_data`  array The current `appointment_step_form_data`.
	 *
	 * @return float The total before formatting. Always rounded per setting.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.12
	 */
	public function compute_total( array $context );
}

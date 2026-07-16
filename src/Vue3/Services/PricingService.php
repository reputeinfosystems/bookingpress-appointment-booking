<?php
/**
 * PricingService — currency formatting + total math.
 *
 * Replaces the legacy `bookingpress_price_formatter_with_currency_symbol`
 * helper. Lite computes the base price; Pro decorates via
 * {@see Hooks::FILTER_SUMMARY_TOTAL}.
 *
 * @package BookingPress\Vue3\Services
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.12
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Contracts\PricingServiceInterface;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\ServiceRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PricingService implements PricingServiceInterface {

	/** @var SettingsRepository */
	private $settings;
	/** @var CustomizeRepository */
	private $customize;
	/** @var ServiceRepository */
	private $services;

	/** @var array<string, string>|null Cached payment settings (currency etc.). */
	private $payment_cache = null;

	public function __construct(
		?SettingsRepository $settings = null,
		?CustomizeRepository $customize = null,
		?ServiceRepository $services = null
	) {
		$this->settings  = $settings ?: new SettingsRepository();
		$this->customize = $customize ?: new CustomizeRepository();
		$this->services  = $services ?: new ServiceRepository();
	}

	/**
	 * @inheritDoc
	 */
	public function format_price( $amount, $currency = null ) {
		$ctx = $this->payment_context( $currency );
		return self::format_price_pure( (float) $amount, $ctx );
	}

	/**
	 * @inheritDoc
	 */
	public function compute_total( array $context ) {
		$service_id = isset( $context['service_id'] ) ? (int) $context['service_id'] : 0;
		if ( $service_id <= 0 ) {
			return 0.0;
		}
		$service = $this->services->find( $service_id );
		$price   = $service && isset( $service['servicePrice'] ) ? (float) $service['servicePrice'] : 0.0;

		$decimals = (int) $this->payment_setting( 'price_number_of_decimals', 2 );
		$total    = round( $price, max( 0, $decimals ) );

		$breakdown = array(
			'base'  => $total,
			'taxes' => array(),
			'fees'  => array(),
			'total' => $total,
		);

		/**
		 * Decorate the summary total (Pro: taxes, coupons, deposits).
		 *
		 * @param float $total
		 * @param array $breakdown
		 * @param array $context
		 */
		$total = (float) apply_filters( Hooks::FILTER_SUMMARY_TOTAL, $total, $breakdown, $context );
		return round( $total, max( 0, $decimals ) );
	}

	/**
	 * Pure formatting helper — used by the formatter and by pure tests.
	 *
	 * @param float $amount
	 * @param array $ctx Keys: `symbol`, `position` ('left'|'right'), `decimals`,
	 *                   `thousand_sep`, `decimal_sep`.
	 *
	 * @return string
	 */
	public static function format_price_pure( $amount, array $ctx ) {
		$decimals     = isset( $ctx['decimals'] ) ? (int) $ctx['decimals'] : 2;
		$thousand_sep = isset( $ctx['thousand_sep'] ) ? (string) $ctx['thousand_sep'] : ',';
		$decimal_sep  = isset( $ctx['decimal_sep'] ) ? (string) $ctx['decimal_sep'] : '.';
		$symbol       = isset( $ctx['symbol'] ) ? (string) $ctx['symbol'] : '$';
		$position     = isset( $ctx['position'] ) ? (string) $ctx['position'] : 'left';

		$number = number_format( (float) $amount, max( 0, $decimals ), $decimal_sep, $thousand_sep );

		switch ( $position ) {
			case 'right':
				return $number . $symbol;
			case 'left_space':
				return $symbol . ' ' . $number;
			case 'right_space':
				return $number . ' ' . $symbol;
			case 'left':
			default:
				return $symbol . $number;
		}
	}

	/**
	 * Build the formatting context for the active currency.
	 *
	 * @param string|null $currency_override
	 *
	 * @return array
	 */
	private function payment_context( $currency_override = null ) {
		$decimals      = (int) $this->payment_setting( 'price_number_of_decimals', 2 );
		$thousand_sep  = (string) $this->payment_setting( 'price_thousand_separator', ',' );
		$decimal_sep   = (string) $this->payment_setting( 'price_decimal_separator', '.' );
		$position      = (string) $this->payment_setting( 'currency_symbol_position', 'left' );

		$currency = null !== $currency_override
			? (string) $currency_override
			: (string) $this->payment_setting( 'payment_default_currency', 'USD' );

		return array(
			'currency'     => $currency,
			'symbol'       => self::currency_symbol( $currency ),
			'position'     => $position,
			'decimals'     => $decimals,
			'thousand_sep' => $thousand_sep,
			'decimal_sep'  => $decimal_sep,
		);
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return string
	 */
	private function payment_setting( $key, $default ) {
		if ( null === $this->payment_cache ) {
			$this->payment_cache = $this->settings->get_group( SettingsRepository::GROUP_PAYMENT );
		}
		return isset( $this->payment_cache[ $key ] ) && '' !== $this->payment_cache[ $key ]
			? $this->payment_cache[ $key ]
			: $default;
	}

	/**
	 * Minimal currency-code → symbol map. Pro can extend via the filter.
	 *
	 * Covers the most common cases; falls back to the ISO code itself
	 * for currencies not in the table. This is intentionally small —
	 * Lite ships USD by default and the admin currency picker writes
	 * the symbol separately for unusual cases.
	 *
	 * @param string $iso
	 *
	 * @return string
	 */
	private static function currency_symbol( $iso ) {
		$iso = strtoupper( (string) $iso );
		$map = array(
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
			'CNY' => '¥',
			'INR' => '₹',
			'AUD' => 'A$',
			'CAD' => 'C$',
			'CHF' => 'CHF',
			'SEK' => 'kr',
			'NOK' => 'kr',
			'DKK' => 'kr',
			'NZD' => 'NZ$',
			'BRL' => 'R$',
			'MXN' => 'MX$',
		);
		return isset( $map[ $iso ] ) ? $map[ $iso ] : $iso;
	}
}

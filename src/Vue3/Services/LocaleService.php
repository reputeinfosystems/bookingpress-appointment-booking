<?php
/**
 * LocaleService — translation context helper for the Vue3 path.
 *
 * Thin wrapper around WordPress i18n helpers so services don't reach for
 * `__()` directly (the wrapping makes it trivial to inject a mock in tests
 * and to consolidate the text-domain string in one place).
 *
 * Concrete-only — not a Pro override point.
 *
 * @package BookingPress\Vue3\Services
 */

namespace BookingPress\Vue3\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LocaleService {

	/**
	 * Plugin text domain.
	 */
	const TEXT_DOMAIN = 'bookingpress-appointment-booking';

	/**
	 * Translate a string.
	 *
	 * @param string $text English source.
	 *
	 * @return string
	 */
	public function t( $text ) {
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		return __( (string) $text, self::TEXT_DOMAIN );
	}

	/**
	 * Translate with context.
	 *
	 * @param string $text
	 * @param string $context
	 *
	 * @return string
	 */
	public function t_with_context( $text, $context ) {
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralContext
		return _x( (string) $text, (string) $context, self::TEXT_DOMAIN );
	}

	/**
	 * Current site locale string (e.g. `'en_US'`).
	 *
	 * @return string
	 */
	public function locale() {
		return function_exists( 'get_locale' ) ? (string) get_locale() : 'en_US';
	}
}

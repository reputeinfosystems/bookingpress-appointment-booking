<?php
/**
 * CaptchaService — built-in math-question spam protection.
 *
 * Lite ships a math-question captcha (paritied from
 * `bookingpress_validate_spam_protection`). Pro swaps in reCAPTCHA / hCaptcha
 * / Turnstile via the single `bookingpress_form_v3_service` filter.
 *
 * Storage: transient under `bp_v3_captcha_v1_<token>` carrying the expected
 * answer. TTL: 15 minutes — long enough for a typical session, short enough
 * to bound stale-token replay.
 *
 * @package BookingPress\Vue3\Services
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C `gate.captcha`
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Contracts\CaptchaServiceInterface;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CaptchaService implements CaptchaServiceInterface {

	const TRANSIENT_PREFIX = 'bp_v3_captcha_v1_';
	const TTL              = 900; // 15 minutes.

	/** @var SettingsRepository */
	private $settings;

	public function __construct( ?SettingsRepository $settings = null ) {
		$this->settings = $settings ?: new SettingsRepository();
	}

	/**
	 * @inheritDoc
	 */
	public function is_enabled() {
		// Google captcha related change
		global $BookingPress;
		if ( is_object( $BookingPress ) && method_exists( $BookingPress, 'bookingpress_get_customize_settings' ) ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$gcaptcha = $BookingPress->bookingpress_get_customize_settings( 'enable_google_captcha', 'booking_form' );
			if ( ( 'true' === (string) $gcaptcha || '1' === (string) $gcaptcha ) && is_plugin_active( 'bookingpress-google-captcha/bookingpress-google-captcha.php' ) ) {
				return true;
			}

			$tcaptcha = $BookingPress->bookingpress_get_customize_settings( 'enable_turnstile_captcha', 'booking_form' );
			if ( ( 'true' === (string) $tcaptcha || '1' === (string) $tcaptcha ) && is_plugin_active( 'bookingpress-turnstile-captcha/bookingpress-turnstile-captcha.php' ) ) {
				return true;
			}
		}
		$value = (string) $this->settings->get(
			'enable_spam_protection',
			SettingsRepository::GROUP_GENERAL,
			'false'
		);
		return ( 'true' === $value || '1' === $value );
	}

	/**
	 * @inheritDoc
	 */
	public function issue_challenge( $instance_token ) {
		$a = wp_rand( 1, 9 );
		$b = wp_rand( 1, 9 );
		$challenge_text = sprintf( '%d + %d = ?', $a, $b );

		$token = wp_generate_password( 24, false );
		set_transient( self::TRANSIENT_PREFIX . $token, (string) ( $a + $b ), self::TTL );

		return array(
			'challenge' => $challenge_text,
			'token'     => $token,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function verify( $token, $answer ) {
		$token = (string) $token;

		/**
		 * Allow external captcha services (Google reCAPTCHA, Turnstile, hCaptcha) to verify first.
		 */
		$filtered_ok = apply_filters( Hooks::FILTER_CAPTCHA, null, $token, $answer );
		if ( null !== $filtered_ok ) {
			return (bool) $filtered_ok;
		}

		if ( '' === $token ) {
			return false;
		}
		$expected = get_transient( self::TRANSIENT_PREFIX . $token );
		if ( false === $expected ) {
			return false;
		}
		$ok = ( (string) $expected === trim( (string) $answer ) );

		if ( $ok ) {
			// Single-use: invalidate token after a successful verify.
			delete_transient( self::TRANSIENT_PREFIX . $token );
		}
		return $ok;
	}
}

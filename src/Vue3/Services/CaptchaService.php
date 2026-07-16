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
		if ( '' === $token ) {
			return false;
		}
		$expected = get_transient( self::TRANSIENT_PREFIX . $token );
		if ( false === $expected ) {
			return false;
		}
		$ok = ( (string) $expected === trim( (string) $answer ) );

		/**
		 * Final say on captcha verification. Pro hCaptcha/reCAPTCHA may want
		 * to delegate the actual verify to their service and use this filter
		 * to short-circuit Lite's transient lookup.
		 *
		 * @param bool   $ok       Result of Lite's verification.
		 * @param string $token
		 * @param string $answer
		 */
		$ok = (bool) apply_filters( Hooks::FILTER_CAPTCHA, $ok, $token, $answer );

		if ( $ok ) {
			// Single-use: invalidate token after a successful verify.
			delete_transient( self::TRANSIENT_PREFIX . $token );
		}
		return $ok;
	}
}

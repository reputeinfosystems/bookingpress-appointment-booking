<?php
/**
 * CaptchaServiceInterface — spam-protection token issue + verify.
 *
 * @package BookingPress\Vue3\Contracts
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C `gate.captcha`
 */

namespace BookingPress\Vue3\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Issues per-instance captcha challenges and verifies the user's answer at
 * submit time.
 *
 * Lite supports the built-in math-question captcha (paritied from
 * `bookingpress_validate_spam_protection`). Pro can swap in reCAPTCHA /
 * hCaptcha / Cloudflare Turnstile via the single `bookingpress_form_v3_service`
 * registry filter — Pro's callback checks for `CaptchaServiceInterface::class`
 * and returns its own implementation (see plan §5a.2).
 *
 * The default Lite implementation is a no-op when spam protection is disabled
 * in admin settings.
 */
interface CaptchaServiceInterface {

	/**
	 * Return whether spam protection is enabled for this request.
	 *
	 * Used by `StateBuilder` to decide whether to include the captcha block
	 * in the initial state.
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Issue a fresh challenge bound to the given instance token.
	 *
	 * @param string $instance_token Per-render instance token.
	 *
	 * @return array{ challenge: string, token: string }
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C
	 */
	public function issue_challenge( $instance_token );

	/**
	 * Verify the user's answer against the stored challenge.
	 *
	 * @param string $token   The challenge token (from `issue_challenge`).
	 * @param string $answer  The user-supplied answer.
	 *
	 * @return bool True iff the answer is correct AND the token is still valid.
	 */
	public function verify( $token, $answer );
}

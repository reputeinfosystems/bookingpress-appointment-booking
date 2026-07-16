<?php
/**
 * CaptchaController — `/form-v3/captcha`.
 *
 * Issues a fresh challenge bound to the requesting instance. The challenge
 * answer is server-side; the response carries the challenge prompt and an
 * opaque token the client posts back at submit time.
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C `gate.captcha`
 */

namespace BookingPress\Vue3\REST;

use BookingPress\Vue3\Contracts\CaptchaServiceInterface;
use BookingPress\Vue3\Services\ServiceLocator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CaptchaController {

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function generate( \WP_REST_Request $request ) {
		$gate = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$captcha = ServiceLocator::get( CaptchaServiceInterface::class );
		if ( ! $captcha->is_enabled() ) {
			return Response::ok( array(
				'enabled'   => false,
				'challenge' => '',
				'token'     => '',
			) );
		}

		$instance_id = (string) $request->get_param( 'instanceId' );
		$out         = $captcha->issue_challenge( $instance_id );

		return Response::ok( array(
			'enabled'   => true,
			'challenge' => (string) $out['challenge'],
			'token'     => (string) $out['token'],
		) );
	}
}

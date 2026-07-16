<?php
/**
 * ValidationController — `/form-v3/validate-username`.
 *
 * Stateless lookup: does a WP user with the requested login already exist?
 * Used by Pro's username field; harmless on Lite-only installs where the
 * username field is hidden by default.
 *
 * @package BookingPress\Vue3\REST
 */

namespace BookingPress\Vue3\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ValidationController {

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function validate_username( \WP_REST_Request $request ) {
		$gate = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$candidate = sanitize_user( (string) $request->get_param( 'username' ), true );
		if ( '' === $candidate ) {
			return Response::error( 'bp_v3_invalid_username', 'Username is empty or invalid.', 422 );
		}

		$exists = (bool) username_exists( $candidate );
		return Response::ok( array(
			'username'   => $candidate,
			'available'  => ! $exists,
		) );
	}
}

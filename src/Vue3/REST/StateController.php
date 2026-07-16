<?php
/**
 * StateController — `/form-v3/state` (optional hydration refresh).
 *
 * Returns a minimal state echo so the client can verify it's still
 * authenticated against the current instance. M5's full state-rehydration
 * payload lives here as a forward extension point.
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §4
 */

namespace BookingPress\Vue3\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class StateController {

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function refresh( \WP_REST_Request $request ) {
		$gate = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		return Response::ok( array(
			'instanceId' => (string) $request->get_param( 'instanceId' ),
			'refreshed'  => true,
		) );
	}
}

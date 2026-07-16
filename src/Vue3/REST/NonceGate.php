<?php
/**
 * NonceGate — single chokepoint for REST authentication.
 *
 * Verifies three tokens per plan §4 / §4.1:
 *
 *   1. `X-WP-Nonce` HTTP header → `wp_rest` action (WP REST default).
 *   2. `bp_v3_nonce` body param → `bookingpress_form_v3_nonce` action
 *      (per-render form CSRF defense-in-depth).
 *   3. `bp_v3_instance_token` body param → transient bound to the requesting
 *      `instanceId` (per-render anti-replay).
 *
 * Every controller delegates to {@see self::verify()} before doing any work.
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §4.1
 */

namespace BookingPress\Vue3\REST;

use BookingPress\Vue3\Services\NonceService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NonceGate {

	/** @var NonceService */
	private $nonces;

	public function __construct( ?NonceService $nonces = null ) {
		$this->nonces = $nonces ?: new NonceService();
	}

	/**
	 * Verify the three-token triple from a REST request.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return true|\WP_Error true on pass; WP_Error on any failure.
	 */
	public function verify( \WP_REST_Request $request ) {
		// 1. WP REST nonce — header only, no _wpnonce body fallback.
		$header = $request->get_header( 'x_wp_nonce' );
		if ( ! $header || ! wp_verify_nonce( (string) $header, 'wp_rest' ) ) {
			return new \WP_Error(
				'bp_v3_rest_invalid_nonce',
				'Invalid REST nonce.',
				array( 'status' => 403 )
			);
		}

		// 2. Form-render nonce — body only, namespaced field name.
		$form_nonce = (string) $request->get_param( 'bp_v3_nonce' );
		if ( '' === $form_nonce || ! wp_verify_nonce( $form_nonce, NonceService::NONCE_ACTION ) ) {
			return new \WP_Error(
				'bp_v3_invalid_form_nonce',
				'Invalid form nonce.',
				array( 'status' => 403 )
			);
		}

		// 3. Instance token — body, opaque, transient-backed.
		$instance_id    = (string) $request->get_param( 'instanceId' );
		$instance_token = (string) $request->get_param( 'bp_v3_instance_token' );
		if ( '' === $instance_id || ! $this->nonces->is_valid_instance_token( $instance_token, $instance_id ) ) {
			return new \WP_Error(
				'bp_v3_invalid_instance',
				'Unknown form instance.',
				array( 'status' => 400 )
			);
		}

		return true;
	}
}

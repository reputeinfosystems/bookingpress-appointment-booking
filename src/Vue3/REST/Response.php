<?php
/**
 * Response — envelope helpers for Vue3 REST controllers.
 *
 * Plan §4 mandates the shape `{ ok, data, errors?, error? }`. This helper
 * builds the canonical WP_REST_Response so controllers don't reinvent it.
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §4
 */

namespace BookingPress\Vue3\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Response {

	/**
	 * Build a success envelope.
	 *
	 * @param mixed $data    Payload (typically an associative array).
	 * @param int   $status  HTTP status code. Default 200.
	 *
	 * @return \WP_REST_Response
	 */
	public static function ok( $data = array(), $status = 200 ) {
		$body = array(
			'ok'   => true,
			'data' => $data,
		);
		return new \WP_REST_Response( $body, (int) $status );
	}

	/**
	 * Build an error envelope.
	 *
	 * @param string             $code    Machine code (e.g. `bp_v3_readiness_failed`).
	 * @param string             $message Human-readable summary.
	 * @param int                $status  HTTP status. Default 400.
	 * @param array<string,mixed> $extra  Optional `errors` (per-field) or `data` keys.
	 *
	 * @return \WP_REST_Response
	 */
	public static function error( $code, $message, $status = 400, array $extra = array() ) {
		$body = array(
			'ok'    => false,
			'error' => array(
				'code'    => (string) $code,
				'message' => (string) $message,
			),
		);
		if ( isset( $extra['errors'] ) ) {
			$body['errors'] = $extra['errors'];
		}
		if ( isset( $extra['data'] ) ) {
			$body['data'] = $extra['data'];
		}
		return new \WP_REST_Response( $body, (int) $status );
	}

	/**
	 * Build an error from a WP_Error (e.g. NonceGate output).
	 *
	 * @param \WP_Error $err
	 *
	 * @return \WP_REST_Response
	 */
	public static function from_wp_error( \WP_Error $err ) {
		$data    = $err->get_error_data();
		$status  = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 400;
		$code    = $err->get_error_code();
		$message = $err->get_error_message();
		return self::error( $code, $message, $status );
	}
}

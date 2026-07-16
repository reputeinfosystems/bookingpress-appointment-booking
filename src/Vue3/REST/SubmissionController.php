<?php
/**
 * SubmissionController — `/form-v3/submit`.
 *
 * Delegates the 2-step write contract to SubmissionService. Maps the named
 * exceptions (`ReadinessFailedException`) to structured error envelopes.
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C, §M0.10
 */

namespace BookingPress\Vue3\REST;

use BookingPress\Vue3\Contracts\SubmissionServiceInterface;
use BookingPress\Vue3\Exceptions\ReadinessFailedException;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Services\ServiceLocator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SubmissionController {

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function submit( \WP_REST_Request $request ) {
		$gate = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$payload = self::extract_appointment_payload( $request );

		try {
			$envelope = ServiceLocator::get( SubmissionServiceInterface::class )
				->submit( $payload );
		} catch ( ReadinessFailedException $e ) {
			return Response::error(
				'bp_v3_readiness_failed',
				$e->getMessage(),
				400,
				array( 'data' => array( 'failed_gates' => $e->get_failed_gates() ) )
			);
		} catch ( \Throwable $e ) {
			return Response::error( 'bp_v3_internal_error', $e->getMessage(), 500 );
		}

		/**
		 * Let consumers enrich the response envelope before it is mapped to
		 * HTTP semantics. Fires for every variant so a callback can decorate
		 * both the success and the error envelope (e.g. Pro's In-Built
		 * redirection attaches the inline thank-you / failed HTML here).
		 *
		 * @param array $envelope
		 * @param array $payload
		 */
		$envelope = (array) apply_filters( Hooks::FILTER_SUBMIT_ENVELOPE, $envelope, $payload );

		// Map the service envelope variants to HTTP semantics.
		if ( isset( $envelope['variant'] ) && 'error' === $envelope['variant'] ) {
			$code    = isset( $envelope['error_code'] ) ? (string) $envelope['error_code'] : 'bp_v3_submit_error';
			$message = isset( $envelope['error_message'] ) ? (string) $envelope['error_message'] : 'Submission failed.';
			// Pass the (possibly enriched) envelope through as error data so
			// consumer-added fields — e.g. the In-Built failed-screen HTML —
			// reach the client on failure too.
			return Response::error( $code, $message, 400, array( 'data' => $envelope ) );
		}

		return Response::ok( $envelope );
	}

	/**
	 * Pull the `appointment_data` body shape out of the REST request.
	 *
	 * Accepts either a flat body (each form-field key at the top level) or a
	 * nested `appointment_data` body parameter. The Vue3 client (M6) will
	 * default to flat — the nested shape preserves a wire-compat path for
	 * the legacy client (used during M9 transition testing).
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 */
	private static function extract_appointment_payload( \WP_REST_Request $request ) {
		$nested = $request->get_param( 'appointment_data' );
		if ( is_array( $nested ) && ! empty( $nested ) ) {
			return $nested;
		}

		$body  = $request->get_json_params();
		if ( ! is_array( $body ) ) {
			$body = $request->get_body_params();
		}
		if ( ! is_array( $body ) ) {
			$body = array();
		}

		// Drop the wire-only auth + control keys; everything else is the
		// appointment_data shape.
		$strip = array(
			'_wpnonce',
			'bp_v3_nonce',
			'bp_v3_instance_token',
			'instanceId',
			'appointment_data',
		);
		foreach ( $strip as $k ) {
			unset( $body[ $k ] );
		}
		return $body;
	}
}

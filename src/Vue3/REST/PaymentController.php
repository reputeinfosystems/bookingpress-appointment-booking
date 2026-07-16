<?php
/**
 * PaymentController — `/form-v3/payment/paypal-validate` and
 * `/form-v3/payment/paypal-confirm`.
 *
 * In M4 these are stubs that pass the auth gate and return a structured
 * `bp_v3_paypal_not_implemented` error envelope. The full PayPal SDK round
 * trip lands when PaymentService::paypal_validate / paypal_confirm are
 * implemented end-to-end (currently scoped behind a sandbox-credentials
 * requirement — see M3_SIGNOFF.md "Deferred to M4 and beyond").
 *
 * The endpoints exist now so the M4 acceptance criteria (all 8 routes
 * callable from curl with valid nonce, return 403 without) are met, and so
 * the route inventory is locked when M6 starts wiring the Vue 3 client.
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.10
 */

namespace BookingPress\Vue3\REST;

use BookingPress\Vue3\Contracts\PaymentServiceInterface;
use BookingPress\Vue3\Exceptions\ReadinessFailedException;
use BookingPress\Vue3\Services\ServiceLocator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentController {

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function paypal_validate( \WP_REST_Request $request ) {
		$gate = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		try {
			$payload  = $request->get_json_params();
			$payload  = is_array( $payload ) ? $payload : array();
			$envelope = ServiceLocator::get( PaymentServiceInterface::class )
				->paypal_validate( $payload );
			return Response::ok( $envelope );
		} catch ( ReadinessFailedException $e ) {
			return Response::error(
				'bp_v3_readiness_failed',
				$e->getMessage(),
				400,
				array( 'data' => array( 'failed_gates' => $e->get_failed_gates() ) )
			);
		} catch ( \RuntimeException $e ) {
			return Response::error( 'bp_v3_paypal_error', $e->getMessage(), 400 );
		} catch ( \Throwable $e ) {
			return Response::error( 'bp_v3_internal_error', $e->getMessage(), 500 );
		}
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function paypal_confirm( \WP_REST_Request $request ) {
		$gate = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		try {
			$payload  = $request->get_json_params();
			$payload  = is_array( $payload ) ? $payload : array();
			$envelope = ServiceLocator::get( PaymentServiceInterface::class )
				->paypal_confirm( $payload );
			// finalize_booking may return an `error` envelope (e.g. the double-booking
			// guard vetoed the slot after the capture). Surface it as a failure with
			// the envelope attached — mirrors SubmissionController::submit().
			if ( is_array( $envelope ) && isset( $envelope['variant'] ) && 'error' === $envelope['variant'] ) {
				$code    = isset( $envelope['error_code'] ) ? (string) $envelope['error_code'] : 'bp_v3_paypal_error';
				$message = isset( $envelope['error_message'] ) ? (string) $envelope['error_message'] : __( 'This time slot is no longer available.', 'bookingpress-appointment-booking' );
				return Response::error( $code, $message, 409, array( 'data' => $envelope ) );
			}
			return Response::ok( $envelope );
		} catch ( ReadinessFailedException $e ) {
			return Response::error(
				'bp_v3_readiness_failed',
				$e->getMessage(),
				400,
				array( 'data' => array( 'failed_gates' => $e->get_failed_gates() ) )
			);
		} catch ( \RuntimeException $e ) {
			return Response::error( 'bp_v3_paypal_error', $e->getMessage(), 400 );
		} catch ( \Throwable $e ) {
			return Response::error( 'bp_v3_internal_error', $e->getMessage(), 500 );
		}
	}

	/**
	 * Prepare the PayPal Standard ("Legacy") full-page redirect form. Nonce
	 * gated like the other form endpoints — the booking is already staged by
	 * /submit and we only pass its `entry_id` on.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function paypal_redirect_prepare( \WP_REST_Request $request ) {
		$gate  = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		try {
			$payload  = $request->get_json_params();
			$payload  = is_array( $payload ) ? $payload : array();
			$envelope = ServiceLocator::get( PaymentServiceInterface::class )
				->paypal_redirect_prepare( $payload );
			return Response::ok( $envelope );
		} catch ( ReadinessFailedException $e ) {
			return Response::error(
				'bp_v3_readiness_failed',
				$e->getMessage(),
				400,
				array( 'data' => array( 'failed_gates' => $e->get_failed_gates() ) )
			);
		} catch ( \RuntimeException $e ) {
			return Response::error( 'bp_v3_paypal_error', $e->getMessage(), 400 );
		} catch ( \Throwable $e ) {
			return Response::error( 'bp_v3_internal_error', $e->getMessage(), 500 );
		}
	}

	/**
	 * PayPal Standard IPN listener (the webscr `notify_url` target).
	 *
	 * PUBLIC endpoint — PayPal posts server-to-server with no WordPress
	 * session/nonce, so this route is intentionally NOT nonce gated. The
	 * handler establishes authenticity by re-posting to PayPal
	 * (`cmd=_notify-validate`) before acting on anything. Always returns a
	 * bare HTTP 200 (PayPal only cares about the status code).
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function paypal_ipn( \WP_REST_Request $request ) {
		$post = $request->get_body_params();
		if ( empty( $post ) && ! empty( $_POST ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- server-to-server IPN; authenticity is established by re-posting to PayPal (_notify-validate) before use.
			$post = wp_unslash( $_POST );
		}
		try {
			ServiceLocator::get( PaymentServiceInterface::class )->paypal_ipn( is_array( $post ) ? $post : array() );
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement -- IPN must always answer 200.
			// Swallow — a failed finalize must not turn into a non-200 that
			// makes PayPal retry forever. The debug log records the reason.
		}
		return new \WP_REST_Response( null, 200 );
	}
}

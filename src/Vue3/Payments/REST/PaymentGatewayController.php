<?php
/**
 * PaymentGatewayController — thin HTTP adapter over {@see PaymentOrchestrator}.
 *
 * Contains no payment logic on purpose. Its entire job is: gate the nonce,
 * unwrap the request, call the orchestrator, and map exceptions onto HTTP.
 *
 * That last part matters more than it looks. Error mapping is currently
 * re-invented in every gateway — `bp_v3_stripe_not_paid`, `bp_v3_paypal_error`,
 * `bp_v3_stripe_entry_mismatch`, `bp_v3_stripe_finalize_failed` — so the Vue 3
 * client cannot handle failures generically and each gateway needs bespoke
 * client-side error handling. One mapping here means one set of codes for the
 * client to know, for all gateways, in all contexts.
 *
 * @package BookingPress\Vue3\Payments\REST
 */

namespace BookingPress\Vue3\Payments\REST;

use BookingPress\Vue3\Exceptions\ReadinessFailedException;
use BookingPress\Vue3\Payments\Exceptions\PaymentAmountMismatchException;
use BookingPress\Vue3\Payments\Exceptions\PaymentGatewayNotAvailableException;
use BookingPress\Vue3\Payments\PaymentHooks;
use BookingPress\Vue3\Payments\PaymentOrchestrator;
use BookingPress\Vue3\REST\NonceGate;
use BookingPress\Vue3\REST\Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentGatewayController {

	/**
	 * GET /payment-v3/methods?context=booking_form
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function methods( \WP_REST_Request $request ) {
		$gate  = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$context_id = (string) $request->get_param( 'context' );

		try {
			return Response::ok(
				array( 'methods' => PaymentOrchestrator::available_methods( $context_id ) )
			);
		} catch ( \Throwable $e ) {
			return self::map_error( $e, 'methods' );
		}
	}

	/**
	 * POST /payment-v3/prepare
	 *
	 * Body: `{ context, gateway, payload }`.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function prepare( \WP_REST_Request $request ) {
		$gate  = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$body = $request->get_json_params();
		$body = is_array( $body ) ? $body : array();

		$context_id = isset( $body['context'] ) ? (string) $body['context'] : '';
		$gateway_id = isset( $body['gateway'] ) ? (string) $body['gateway'] : '';
		$payload    = isset( $body['payload'] ) && is_array( $body['payload'] ) ? $body['payload'] : array();

		try {
			return Response::ok( PaymentOrchestrator::prepare( $context_id, $gateway_id, $payload ) );
		} catch ( \Throwable $e ) {
			return self::map_error( $e, 'prepare' );
		}
	}

	/**
	 * POST /payment-v3/step
	 *
	 * Body: `{ context, gateway, reference, token, gateway_payload }` — the
	 * same shape as confirm, because it is the same authorisation: a sealed
	 * reference plus proof of possession.
	 *
	 * Returns another `prepare` envelope rather than a booking. A step cannot
	 * settle; see PaymentOrchestrator::step() for why that restriction is the
	 * whole point of having a separate leg.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function step( \WP_REST_Request $request ) {
		$gate  = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$body = $request->get_json_params();
		$body = is_array( $body ) ? $body : array();

		$context_id  = isset( $body['context'] ) ? (string) $body['context'] : '';
		$gateway_id  = isset( $body['gateway'] ) ? (string) $body['gateway'] : '';
		$reference   = isset( $body['reference'] ) ? (string) $body['reference'] : '';
		$token       = isset( $body['token'] ) ? (string) $body['token'] : '';
		$client_data = isset( $body['gateway_payload'] ) && is_array( $body['gateway_payload'] )
			? $body['gateway_payload']
			: array();

		try {
			return Response::ok(
				PaymentOrchestrator::step( $context_id, $gateway_id, $reference, $token, $client_data )
			);
		} catch ( \Throwable $e ) {
			return self::map_error( $e, 'step' );
		}
	}

	/**
	 * POST /payment-v3/confirm
	 *
	 * Body: `{ context, gateway, reference, token, gateway_payload }`.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function confirm( \WP_REST_Request $request ) {
		$gate  = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$body = $request->get_json_params();
		$body = is_array( $body ) ? $body : array();

		$context_id  = isset( $body['context'] ) ? (string) $body['context'] : '';
		$gateway_id  = isset( $body['gateway'] ) ? (string) $body['gateway'] : '';
		$reference   = isset( $body['reference'] ) ? (string) $body['reference'] : '';
		$token       = isset( $body['token'] ) ? (string) $body['token'] : '';
		$client_data = isset( $body['gateway_payload'] ) && is_array( $body['gateway_payload'] )
			? $body['gateway_payload']
			: array();

		try {
			$envelope = PaymentOrchestrator::confirm( $context_id, $gateway_id, $reference, $token, $client_data );

			// A context may still veto after the charge — the double-booking
			// guard being the canonical case. Surface it as 409 with the
			// envelope attached, matching the existing SubmissionController
			// behaviour so the Vue 3 client needs no new branch.
			if ( isset( $envelope['variant'] ) && 'error' === $envelope['variant'] ) {
				$code    = isset( $envelope['error_code'] ) ? (string) $envelope['error_code'] : 'bp_payment_conflict';
				$message = isset( $envelope['error_message'] )
					? (string) $envelope['error_message']
					: __( 'This time slot is no longer available.', 'bookingpress-appointment-booking' );

				return Response::error( $code, $message, 409, array( 'data' => $envelope ) );
			}

			return Response::ok( $envelope );
		} catch ( \Throwable $e ) {
			return self::map_error( $e, 'confirm' );
		}
	}

	/**
	 * POST|GET /payment-v3/webhook/{gateway}
	 *
	 * ALWAYS answers 200. A non-200 makes processors retry for hours or disable
	 * the endpoint; the debug log is where failures are recorded. Mirrors the
	 * deliberate swallow already in `PaymentController::paypal_ipn()`.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function webhook( \WP_REST_Request $request ) {
		$gateway_id = (string) $request->get_param( 'gateway' );

		$payload = $request->get_json_params();

		// WP only parses a JSON body when the request SAYS it is JSON. A sender
		// that posts JSON without `Content-Type: application/json` therefore
		// arrives here with nothing — and `get_body_params()` makes it worse by
		// parsing the JSON text as form data, producing a single nonsense key
		// rather than an empty array, which then looks like a valid payload.
		//
		// Every major processor sets the header, so this is not the common path;
		// but a webhook is the one endpoint whose caller we do not control, and
		// the cost of being liberal here is one json_decode.
		if ( ! is_array( $payload ) || empty( $payload ) ) {
			$decoded = json_decode( (string) $request->get_body(), true );
			if ( is_array( $decoded ) && ! empty( $decoded ) ) {
				$payload = $decoded;
			}
		}

		if ( ! is_array( $payload ) || empty( $payload ) ) {
			$payload = $request->get_body_params();
		}
		if ( ! is_array( $payload ) ) {
			$payload = array();
		}

		try {
			PaymentOrchestrator::webhook(
				$gateway_id,
				$payload,
				(array) $request->get_headers(),
				(string) $request->get_body()
			);
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement -- webhooks must always answer 200.
			do_action( PaymentHooks::ACTION_PAYMENT_FAILED, 'webhook', $e, array( 'gateway' => $gateway_id ) );
		}

		return new \WP_REST_Response( null, 200 );
	}

	// ------------------------------------------------------------------
	// Error mapping — one table, all gateways, all contexts.
	// ------------------------------------------------------------------

	/**
	 * @param \Throwable $e
	 * @param string     $stage
	 *
	 * @return \WP_REST_Response
	 */
	private static function map_error( \Throwable $e, $stage ) {
		do_action( PaymentHooks::ACTION_PAYMENT_FAILED, $stage, $e, array() );

		if ( $e instanceof ReadinessFailedException ) {
			return Response::error(
				'bp_payment_readiness_failed',
				$e->getMessage(),
				400,
				array( 'data' => array( 'failed_gates' => $e->get_failed_gates() ) )
			);
		}

		if ( $e instanceof PaymentAmountMismatchException ) {
			// The precise numbers go to the log, never to the client — the
			// caller may be the party doing the tampering.
			return Response::error( 'bp_payment_amount_mismatch', $e->get_customer_message(), 409 );
		}

		if ( $e instanceof PaymentGatewayNotAvailableException ) {
			return Response::error( 'bp_payment_gateway_unavailable', $e->get_customer_message(), 400 );
		}

		if ( $e instanceof \InvalidArgumentException ) {
			return Response::error( 'bp_payment_bad_request', $e->getMessage(), 400 );
		}

		if ( $e instanceof \RuntimeException ) {
			return Response::error( 'bp_payment_error', $e->getMessage(), 400 );
		}

		return Response::error( 'bp_payment_internal_error', $e->getMessage(), 500 );
	}
}

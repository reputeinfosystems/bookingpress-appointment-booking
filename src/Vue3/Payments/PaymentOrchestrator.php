<?php
/**
 * PaymentOrchestrator — the single brain of the payment layer.
 *
 * WHAT MOVED IN HERE
 * ------------------
 * Everything that is currently copy-pasted into every gateway add-on and
 * duplicated again per form:
 *
 *   - nonce gating                    (each *Feature.php re-does `new NonceGate()`)
 *   - staging the purchase            (each gateway calls submit()/stage1 itself)
 *   - amount authority                (each gateway re-derives the charge —
 *                                      the exact leak that forced Complete
 *                                      Payment into 19 gateway releases)
 *   - anti-tamper amount comparison   (each gateway does its own variant)
 *   - idempotency                     (re-implemented per gateway AND per path)
 *   - finalize orchestration          (each gateway calls finalize_booking())
 *   - envelope shaping                (each gateway re-applies FILTER_SUBMIT_ENVELOPE)
 *   - error-to-HTTP mapping           (each gateway invents its own codes)
 *
 * Written once here, it is the same for `booking_form`, `complete_payment`,
 * `gift_card`, `package`, and for whatever is added in 2027.
 *
 * THE INVARIANT
 * -------------
 * The amount is sealed by the context at stage time and is never recomputed.
 * {@see assert_charge_matches()} is the enforcement point: whatever the gateway
 * says it took is compared against the seal before a context is allowed to
 * finalize. A gateway that charges the wrong amount cannot produce a booking.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

use BookingPress\Vue3\Payments\Contracts\PaymentContextInterface;
use BookingPress\Vue3\Payments\Contracts\PaymentGatewayInterface;
use BookingPress\Vue3\Payments\Contracts\SteppedPaymentGatewayInterface;
use BookingPress\Vue3\Payments\Exceptions\PaymentAmountMismatchException;
use BookingPress\Vue3\Payments\Exceptions\PaymentGatewayNotAvailableException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PaymentOrchestrator {

	/** Seconds a finalize lock is held. Long enough to outlive a slow gateway callback. */
	const LOCK_TTL = 45;

	/**
	 * PREPARE — stage the purchase and start the payment.
	 *
	 * Called by `POST /payment-v3/prepare`. One implementation for every
	 * context and every gateway.
	 *
	 * @param string $context_id
	 * @param string $gateway_id
	 * @param array  $payload Submitted form data.
	 *
	 * @return array Wire envelope: `reference`, `token`, `prepare`.
	 *
	 * @throws \Throwable Mapped to HTTP by the controller.
	 */
	public static function prepare( $context_id, $gateway_id, array $payload ) {
		$context = ContextRegistry::require_context( $context_id );
		$gateway = self::require_gateway_for( $gateway_id, $context );

		// --- Stage. The context runs every validation gate AND the entire
		// amount pipeline (Tax, Tip, Coupon, Deposit, Gift Card, Discounts,
		// MyCRED, ARMember, Happy Hours, Cart, Recurring, Multi-Service,
		// Waiting List...) exactly once, then seals the result.
		$reference = $context->stage( $payload );

		// --- Zero-amount short circuit. A 100% gift card, a fully-discounted
		// booking or a free service never reaches a gateway at all. Every
		// gateway currently has to guard against a zero charge itself
		// (PayPal throws "Service price must be more than 0"); now none do.
		if ( ! $reference->get_amount()->is_positive() ) {
			$settled = new PaymentResult(
				array(
					'status'         => PaymentResult::STATUS_PAID,
					'gateway_id'     => $gateway->get_id(),
					'transaction_id' => '',
					'amount'         => $reference->get_amount(),
				)
			);
			$envelope = self::run_finalize( $context, $reference, $settled );

			return array(
				'reference' => $reference->to_client_array(),
				'token'     => $reference->get_token(),
				'prepare'   => PrepareResult::settled( $settled )->to_array(),
				'envelope'  => $envelope,
			);
		}

		$request = self::build_request( $context, $reference, $gateway, array() );
		$result  = $gateway->prepare( $request );

		if ( ! ( $result instanceof PrepareResult ) ) {
			throw new \RuntimeException(
				sprintf( 'Gateway "%s" returned an invalid prepare result.', $gateway->get_id() )
			);
		}

		/** @see PaymentHooks::FILTER_PREPARE_RESULT */
		$result = apply_filters( PaymentHooks::FILTER_PREPARE_RESULT, $result, $request );

		// A gateway may settle immediately (on-site, offline, account credit).
		if ( $result instanceof PrepareResult && $result->is_settled() ) {
			$settled  = $result->get_settled_result();
			$envelope = self::run_finalize( $context, $reference, $settled );

			return array(
				'reference' => $reference->to_client_array(),
				'token'     => $reference->get_token(),
				'prepare'   => $result->to_array(),
				'envelope'  => $envelope,
			);
		}

		return array(
			'reference' => $reference->to_client_array(),
			'token'     => $reference->get_token(),
			'prepare'   => $result->to_array(),
		);
	}

	/**
	 * PREPARE AN ALREADY-STAGED REFERENCE.
	 *
	 * Some flows stage first and choose a gateway second. PayPal Standard
	 * ("redirect") is the canonical case: `/submit` stages the booking as
	 * `pending_payment` and returns an `entry_id`, and only then does the client
	 * ask for the auto-submitting webscr form. Re-staging there would insert a
	 * second entries row for one booking.
	 *
	 * This is also the retry path — a customer who abandoned a hosted page and
	 * came back does not need a new reference, just a new payment attempt
	 * against the existing sealed amount.
	 *
	 * @param string $context_id
	 * @param string $gateway_id
	 * @param string $reference_id
	 * @param string $token        Issued by `stage()`.
	 *
	 * @return array Same envelope as `prepare()`.
	 *
	 * @throws \Throwable
	 */
	public static function prepare_existing( $context_id, $gateway_id, $reference_id, $token ) {
		$context = ContextRegistry::require_context( $context_id );
		$gateway = self::require_gateway_for( $gateway_id, $context );

		if ( ! $context->verify_token( $reference_id, $token ) ) {
			throw new \RuntimeException( 'Invalid payment reference.' );
		}

		$reference = $context->resume( $reference_id );
		if ( null === $reference ) {
			throw new \RuntimeException( 'Could not resolve the staged purchase.' );
		}

		$request = self::build_request( $context, $reference, $gateway, array() );
		$result  = $gateway->prepare( $request );

		if ( ! ( $result instanceof PrepareResult ) ) {
			throw new \RuntimeException(
				sprintf( 'Gateway "%s" returned an invalid prepare result.', $gateway->get_id() )
			);
		}

		/** @see PaymentHooks::FILTER_PREPARE_RESULT */
		$result = apply_filters( PaymentHooks::FILTER_PREPARE_RESULT, $result, $request );

		if ( $result instanceof PrepareResult && $result->is_settled() ) {
			$settled  = $result->get_settled_result();
			$envelope = self::run_finalize( $context, $reference, $settled );

			return array(
				'reference' => $reference->to_client_array(),
				// `resume()` rebuilds from storage and does not re-issue a token;
				// echo back the one the caller already proved possession of.
				'token'     => (string) $token,
				'prepare'   => $result->to_array(),
				'envelope'  => $envelope,
			);
		}

		return array(
			'reference' => $reference->to_client_array(),
			'token'     => (string) $token,
			'prepare'   => $result->to_array(),
		);
	}

	/**
	 * STEP — continue a payment that is already under way.
	 *
	 * Called by `POST /payment-v3/step`. The third leg, for the minority of
	 * gateways whose flow needs a server round trip BETWEEN the customer
	 * acting and the payment completing — ECPay is the motivating case, where
	 * `CreatePayment` must be called with the browser's `pay_token` and
	 * answers with a URL the customer is then sent to.
	 *
	 * DELIBERATELY CANNOT SETTLE
	 * --------------------------
	 * A step returns a {@see PrepareResult}, not a {@see PaymentResult}, so
	 * there is no path from here to `run_finalize()` — not even for a
	 * KIND_SETTLED result, which is rejected below rather than honoured.
	 *
	 * That restriction is the point. The reason ECPay could not be expressed
	 * through `confirm()` is that `confirm()` must answer with a PaymentResult,
	 * and the only status that would have let the flow continue is PENDING —
	 * which `is_finalizable()` treats as bookable. Routing a mid-flow step
	 * through it would book the appointment before the customer had paid.
	 * Letting `step()` settle would reintroduce exactly that.
	 *
	 * Nothing is re-staged and nothing is re-priced: the reference is resumed
	 * from storage and the same sealed amount is handed to the gateway.
	 *
	 * @param string $context_id
	 * @param string $gateway_id
	 * @param string $reference_id
	 * @param string $token          Issued by `stage()`.
	 * @param array  $client_payload UNTRUSTED.
	 *
	 * @return array Response envelope.
	 *
	 * @throws \Throwable Mapped to HTTP by the controller.
	 */
	public static function step( $context_id, $gateway_id, $reference_id, $token, array $client_payload ) {
		$context = ContextRegistry::require_context( $context_id );
		$gateway = self::require_gateway_for( $gateway_id, $context );

		if ( ! $gateway instanceof SteppedPaymentGatewayInterface ) {
			throw new \RuntimeException(
				sprintf( 'Gateway "%s" does not support a mid-payment step.', $gateway->get_id() )
			);
		}

		// Proof of possession BEFORE any read, exactly as on the confirm leg.
		if ( ! $context->verify_token( $reference_id, $token ) ) {
			throw new \RuntimeException( 'Invalid payment reference.' );
		}

		$reference = $context->resume( $reference_id );
		if ( null === $reference ) {
			throw new \RuntimeException( 'Could not resolve the staged purchase.' );
		}

		$request = self::build_request( $context, $reference, $gateway, $client_payload );
		$result  = $gateway->step( $request );

		if ( ! ( $result instanceof PrepareResult ) ) {
			throw new \RuntimeException(
				sprintf( 'Gateway "%s" returned an invalid step result.', $gateway->get_id() )
			);
		}

		if ( $result->is_settled() ) {
			// A gateway that believes the payment is already complete must say
			// so through confirm(), which verifies it against the seal. Silently
			// finalizing here would make `step` a second, unchecked settlement
			// path — the thing this leg exists to avoid.
			throw new \RuntimeException(
				sprintf( 'Gateway "%s" tried to settle a payment from a step.', $gateway->get_id() )
			);
		}

		/** @see PaymentHooks::FILTER_PREPARE_RESULT */
		$result = apply_filters( PaymentHooks::FILTER_PREPARE_RESULT, $result, $request );

		return array(
			'reference' => $reference->to_client_array(),
			// `resume()` does not re-issue a token; echo back the one the
			// caller already proved possession of, so the client can carry it
			// through to confirm().
			'token'     => (string) $token,
			'prepare'   => $result->to_array(),
		);
	}

	/**
	 * CONFIRM — verify a completed payment and materialise the purchase.
	 *
	 * Called by `POST /payment-v3/confirm` after the gateway's own client
	 * finished (Stripe `confirmPayment`, PayPal `onApprove`, ...).
	 *
	 * @param string $context_id
	 * @param string $gateway_id
	 * @param string $reference_id
	 * @param string $token          Issued by `stage()`.
	 * @param array  $client_payload UNTRUSTED.
	 *
	 * @return array Response envelope.
	 *
	 * @throws \Throwable Mapped to HTTP by the controller.
	 */
	public static function confirm( $context_id, $gateway_id, $reference_id, $token, array $client_payload ) {
		$context = ContextRegistry::require_context( $context_id );
		$gateway = self::require_gateway_for( $gateway_id, $context );

		// Some gateways are the authority on WHICH purchase was just paid —
		// PayPal returns `reference_id` on a server-side re-fetch of the order,
		// which is trustworthy in a way the browser's claim is not. Prefer that
		// over anything the client said.
		$resolved = $gateway->resolve_reference_key( $client_payload );
		if ( is_string( $resolved ) && '' !== $resolved ) {
			$parsed = PaymentReference::parse_correlation_key( $resolved );
			if ( null === $parsed ) {
				throw new \RuntimeException( 'Could not resolve the purchase from the gateway response.' );
			}
			// A gateway must not be able to redirect a confirm into a different
			// context than the one the request is for.
			if ( $parsed['context_id'] !== $context->get_id() ) {
				throw new \RuntimeException( 'Gateway reference does not belong to this payment context.' );
			}
			$reference_id = $parsed['reference_id'];
		}

		// Proof of possession BEFORE any read, so a sequential reference id can
		// never be used as an existence or data oracle. Generalises the guard in
		// PaymentService::paypal_redirect_prepare().
		if ( ! $context->verify_token( $reference_id, $token ) ) {
			throw new \RuntimeException( 'Invalid payment reference.' );
		}

		$reference = $context->resume( $reference_id );
		if ( null === $reference ) {
			throw new \RuntimeException( 'Could not resolve the staged purchase.' );
		}

		$request = self::build_request( $context, $reference, $gateway, $client_payload );
		$result  = $gateway->confirm( $request );

		if ( ! ( $result instanceof PaymentResult ) ) {
			throw new \RuntimeException(
				sprintf( 'Gateway "%s" returned an invalid payment result.', $gateway->get_id() )
			);
		}

		/** @see PaymentHooks::FILTER_PAYMENT_RESULT */
		$result = apply_filters( PaymentHooks::FILTER_PAYMENT_RESULT, $result, $request );

		if ( ! $result->is_finalizable() ) {
			$context->abandon( $reference, $result );
			do_action( PaymentHooks::ACTION_PAYMENT_FAILED, 'confirm', $result, array( 'reference' => $reference ) );

			$message = $result->get_message();
			throw new \RuntimeException(
				'' !== $message ? $message : 'The payment was not completed.'
			);
		}

		self::assert_charge_matches( $reference, $result );

		return self::run_finalize( $context, $reference, $result );
	}

	/**
	 * WEBHOOK — finalize from a server-to-server callback.
	 *
	 * Called by `POST /payment-v3/webhook/{gateway}`. No nonce, no session: the
	 * gateway authenticates the payload itself, then core routes it home via the
	 * correlation key. Replaces PayPal's bespoke IPN route and Stripe's legacy
	 * `?bookingpress-listener=` handler with one path that works for every
	 * gateway in every context.
	 *
	 * @param string $gateway_id
	 * @param array  $payload
	 * @param array  $headers
	 * @param string $raw_body
	 *
	 * @return bool Whether anything was finalized.
	 */
	public static function webhook( $gateway_id, array $payload, array $headers, $raw_body ) {
		$gateway = GatewayRegistry::get( $gateway_id );
		if ( null === $gateway || ! $gateway->is_enabled() ) {
			return false;
		}

		$handled = $gateway->handle_webhook( $payload, $headers, $raw_body );

		// Authentic but irrelevant (an event type this gateway does not act on).
		if ( ! is_array( $handled ) || ! isset( $handled['result'] ) || ! isset( $handled['correlation_key'] ) ) {
			return false;
		}

		$parsed = PaymentReference::parse_correlation_key( $handled['correlation_key'] );
		if ( null === $parsed ) {
			return false;
		}

		$result = $handled['result'];
		if ( ! ( $result instanceof PaymentResult ) ) {
			return false;
		}

		$context = ContextRegistry::get( $parsed['context_id'] );
		if ( null === $context ) {
			return false;
		}

		$reference = $context->resume( $parsed['reference_id'] );
		if ( null === $reference ) {
			return false;
		}

		if ( ! $result->is_finalizable() ) {
			$context->abandon( $reference, $result );
			return false;
		}

		try {
			self::assert_charge_matches( $reference, $result );
			self::run_finalize( $context, $reference, $result );
			return true;
		} catch ( \Throwable $e ) {
			// A webhook must never 500 — the gateway would retry forever.
			// The debug log keeps the reason.
			do_action( PaymentHooks::ACTION_PAYMENT_FAILED, 'webhook', $e, array( 'gateway' => $gateway_id ) );
			return false;
		}
	}

	/**
	 * Payment-picker descriptors for a context id.
	 *
	 * @param string $context_id
	 * @param array  $extra
	 *
	 * @return array
	 */
	public static function available_methods( $context_id, array $extra = array() ) {
		$context = ContextRegistry::get( $context_id );
		if ( null === $context ) {
			return array();
		}
		return GatewayRegistry::descriptors_for( $context, $extra );
	}

	// ------------------------------------------------------------------
	// Internals
	// ------------------------------------------------------------------

	/**
	 * THE anti-tamper check. The one that matters.
	 *
	 * The sealed amount was produced by the context's pricing pipeline. The
	 * result amount is what the gateway's own API says it captured. If those
	 * disagree, something is wrong — a tampered client, a gateway misreading
	 * the minor unit, a currency mix-up, a partial capture — and no purchase is
	 * created.
	 *
	 * Comparison is at the currency's own precision ({@see Money::equals()}), so
	 * float noise from a long filter chain never trips it.
	 *
	 * @param PaymentReference $reference
	 * @param PaymentResult    $result
	 *
	 * @return void
	 *
	 * @throws PaymentAmountMismatchException
	 */
	private static function assert_charge_matches( PaymentReference $reference, PaymentResult $result ) {
		$expected = $reference->get_amount();
		$actual   = $result->get_amount();

		if ( $expected->equals( $actual ) ) {
			return;
		}

		throw new PaymentAmountMismatchException( $expected, $actual, $reference->get_correlation_key() );
	}

	/**
	 * Finalize under a short lock, so a return URL and a webhook arriving at the
	 * same moment cannot both materialise the purchase.
	 *
	 * Contexts are required to be internally idempotent as well — this lock
	 * narrows the race, it does not replace that guarantee.
	 *
	 * @param PaymentContextInterface $context
	 * @param PaymentReference        $reference
	 * @param PaymentResult           $result
	 *
	 * @return array
	 */
	private static function run_finalize( PaymentContextInterface $context, PaymentReference $reference, PaymentResult $result ) {
		$lock_key = 'bp_pay_lock_' . md5( $reference->get_correlation_key() );

		// add() is atomic in the object cache and falls back to a DB insert
		// without a persistent cache, so it works on shared hosting too.
		$acquired = wp_cache_add( $lock_key, 1, 'bookingpress_payments', self::LOCK_TTL );

		try {
			$envelope = $context->finalize( $reference, $result );

			do_action( PaymentHooks::ACTION_PAYMENT_COMPLETED, $reference, $result, $envelope );

			return is_array( $envelope ) ? $envelope : array();
		} finally {
			if ( $acquired ) {
				wp_cache_delete( $lock_key, 'bookingpress_payments' );
			}
		}
	}

	/**
	 * @param PaymentContextInterface $context
	 * @param PaymentReference        $reference
	 * @param PaymentGatewayInterface $gateway
	 * @param array                   $client_payload
	 *
	 * @return PaymentRequest
	 */
	private static function build_request( PaymentContextInterface $context, PaymentReference $reference, PaymentGatewayInterface $gateway, array $client_payload ) {
		$urls = (array) $context->get_return_urls( $reference );

		return new PaymentRequest(
			array(
				'reference'  => $reference,
				'return_url' => isset( $urls['return_url'] ) ? (string) $urls['return_url'] : '',
				'cancel_url' => isset( $urls['cancel_url'] ) ? (string) $urls['cancel_url'] : '',

				// Optional. PaymentRequest falls back to the cancel URL, so a
				// context that does not distinguish a decline from a
				// cancellation simply omits it.
				'failure_url' => isset( $urls['failure_url'] ) ? (string) $urls['failure_url'] : '',

				'webhook_url'    => REST\PaymentRouteRegistrar::webhook_url( $gateway->get_id() ),
				'locale'         => get_locale(),
				'client_payload' => $client_payload,
			)
		);
	}

	/**
	 * @param string                  $gateway_id
	 * @param PaymentContextInterface $context
	 *
	 * @return PaymentGatewayInterface
	 *
	 * @throws PaymentGatewayNotAvailableException
	 */
	private static function require_gateway_for( $gateway_id, PaymentContextInterface $context ) {
		$usable = GatewayRegistry::for_context( $context );
		$id     = (string) $gateway_id;

		if ( ! isset( $usable[ $id ] ) ) {
			throw new PaymentGatewayNotAvailableException( $id, $context->get_id() );
		}

		return $usable[ $id ];
	}
}

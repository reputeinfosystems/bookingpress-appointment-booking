<?php
/**
 * PaymentResult — a gateway's VERIFIED statement about what was actually paid.
 *
 * The word "verified" is load-bearing. A gateway may only construct this after
 * re-fetching the transaction from its own API server-side. Nothing here may be
 * derived from the browser payload. Both existing implementations already get
 * this right — `StripeFeature::rest_confirm()` calls
 * `\Stripe\PaymentIntent::retrieve()`, and `PaymentService::paypal_confirm()`
 * calls `fetch_paypal_order()` — but each re-derives its own status mapping and
 * error strings. This type makes the requirement explicit and the mapping
 * shared.
 *
 * The orchestrator compares `get_amount()` against the sealed reference amount
 * before letting a context finalize, which is the single anti-tamper check that
 * today is re-implemented (slightly differently) in every gateway.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

use BookingPress\Vue3\Repositories\PaymentTransactionRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PaymentResult {

	/** Funds captured. Finalize now. */
	const STATUS_PAID = 'paid';

	/** Accepted but not yet settled (eCheck, bank redirect, PayPal review). */
	const STATUS_PENDING = 'pending';

	/** Authorized only — capture happens later. */
	const STATUS_AUTHORIZED = 'authorized';

	/** Declined or errored. Do not finalize. */
	const STATUS_FAILED = 'failed';

	/** Customer abandoned the gateway. Do not finalize, do not alarm. */
	const STATUS_CANCELLED = 'cancelled';

	/** @var string */
	private $status;

	/** @var string Gateway id that produced this. */
	private $gateway_id;

	/** @var string Gateway's own transaction identifier. */
	private $transaction_id;

	/** @var Money Amount the gateway says it actually took. */
	private $amount;

	/** @var array Raw verified response, for the debug log. */
	private $raw;

	/** @var string Customer-safe failure message. Empty unless failed. */
	private $message;

	/**
	 * @param array $args status, gateway_id, transaction_id, amount, raw, message.
	 */
	public function __construct( array $args ) {
		$this->status         = isset( $args['status'] ) ? (string) $args['status'] : self::STATUS_FAILED;
		$this->gateway_id     = isset( $args['gateway_id'] ) ? (string) $args['gateway_id'] : '';
		$this->transaction_id = isset( $args['transaction_id'] ) ? (string) $args['transaction_id'] : '';
		$this->message        = isset( $args['message'] ) ? (string) $args['message'] : '';
		$this->raw            = isset( $args['raw'] ) && is_array( $args['raw'] ) ? $args['raw'] : array();
		$this->amount         = isset( $args['amount'] ) && $args['amount'] instanceof Money
			? $args['amount']
			: new Money( 0, 'USD' );
	}

	/** @return string */
	public function get_status() {
		return $this->status;
	}

	/** @return string */
	public function get_gateway_id() {
		return $this->gateway_id;
	}

	/** @return string */
	public function get_transaction_id() {
		return $this->transaction_id;
	}

	/** @return Money */
	public function get_amount() {
		return $this->amount;
	}

	/** @return array */
	public function get_raw() {
		return $this->raw;
	}

	/** @return string */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Whether this result should cause the context to materialise the purchase.
	 * PENDING counts — legacy records a pending booking rather than dropping it.
	 *
	 * @return bool
	 */
	public function is_finalizable() {
		return in_array( $this->status, array( self::STATUS_PAID, self::STATUS_PENDING ), true );
	}

	/** @return bool */
	public function is_failure() {
		return in_array( $this->status, array( self::STATUS_FAILED, self::STATUS_CANCELLED ), true );
	}

	/**
	 * Map onto the integer status the payment_transactions table stores, so
	 * contexts never hand-roll this mapping. Mirrors what
	 * `PaymentService::paypal_confirm()` and `StripeFeature::finalize()` each
	 * do inline today.
	 *
	 * @return int
	 */
	public function to_transaction_status() {
		if ( self::STATUS_PENDING === $this->status ) {
			return PaymentTransactionRepository::STATUS_PENDING;
		}
		return PaymentTransactionRepository::STATUS_PAID;
	}

	/**
	 * Shape expected by `SubmissionService::finalize_booking()`, so the booking
	 * form context is a thin adapter rather than a translation layer.
	 *
	 * @return array
	 */
	public function to_finalize_payload() {
		return array(
			'payment_gateway' => $this->gateway_id,
			'payment_status'  => $this->to_transaction_status(),
			'transaction_id'  => $this->transaction_id,
			'paid_amount'     => $this->amount->get_amount(),
			'currency'        => $this->amount->get_currency(),
			'payload'         => array(),
		);
	}
}

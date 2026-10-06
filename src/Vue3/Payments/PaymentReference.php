<?php
/**
 * PaymentReference — an immutable, SEALED description of "what is being paid for".
 *
 * THIS CLASS IS THE ANSWER TO THE ADD-ON PROBLEM
 * ----------------------------------------------
 * Roughly every Pro add-on can move the number the customer is charged: Tax,
 * Tip, Coupon, Deposit, Gift Card discount, Advance/Offer Discount, ARMember
 * discount, MyCRED discount, Happy Hours, Multiple Quantity, Service Extras,
 * Staff / Multi-Staff pricing, Custom Duration, Location pricing, Multi-Service,
 * Package, Waiting List, Cart, Recurring — and more that do not exist yet.
 *
 * All of them already hook the two existing pipelines:
 *
 *   FILTER_SUMMARY_TOTAL   -> the ORDER TOTAL   (Staff@10, HappyHours@12,
 *                             CustomDuration@13, Quantity@15, Extras@20,
 *                             MultiService@25, Coupon@30, Package@40, Location,
 *                             MultiStaff, MyCRED, ARMember, Discount...)
 *   FILTER_PAYABLE_AMOUNT  -> the AMOUNT DUE NOW (Deposit@10, Discount@11,
 *                             MultiService@11, Tax@20, Package@20, Tip,
 *                             GiftCard@25, WaitingList@30...)
 *
 * The invariant this class enforces is simple and total:
 *
 *   The pipelines run ONCE, inside the context, BEFORE any gateway is
 *   reachable. The result is sealed here. A gateway is handed this object
 *   and has no way to recompute, re-filter, or second-guess it.
 *
 * Therefore a new amount-affecting add-on — or a change to an existing one —
 * is invisible to every gateway, and requires no gateway release. That is the
 * whole point of the refactor.
 *
 * WHAT WENT WRONG BEFORE
 * ----------------------
 * `Hooks::FILTER_PAYABLE_AMOUNT` already documents this rule: the payable is
 * "stored as the stage-1 entry's `bookingpress_service_price` (so every gateway
 * — PayPal / Stripe / on-site — charges it without gateway-specific code)".
 * Complete Payment broke it by adding a SECOND amount source,
 * `SubmissionService::complete_payment_payable_for_entry()`, which gateways then
 * had to call themselves (see `StripeFeature::rest_intent()`, and
 * `PaymentService::paypal_validate()`). Once a gateway knows how to compute an
 * amount, every new context teaches it a new way — and every gateway ships
 * again. Sealing the amount at the boundary removes the ability to do that.
 *
 * ORDERS, NOT JUST BOOKINGS
 * -------------------------
 * Cart and Recurring expand one submission into N bookings paid by ONE charge
 * (`FILTER_SUBMIT_LINE_ITEMS` + `FILTER_SUBMIT_PAYMENT_GROUP`). A reference
 * therefore carries N line items and a group mode, but exactly ONE sealed
 * total. The gateway charges the total and never learns that seven appointments
 * were behind it.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PaymentReference {

	/** One charge -> one booking. */
	const GROUP_PER_BOOKING = 'per_booking';

	/** One charge -> many bookings (Cart, Recurring, Multi-Service). */
	const GROUP_SHARED = 'shared';

	/** @var string Context id that staged this — e.g. `booking_form`. */
	private $context_id;

	/** @var string Opaque id meaningful only to that context (entry id, order id...). */
	private $reference_id;

	/** @var string Per-reference secret proving the caller staged it. */
	private $token;

	/** @var Money SEALED. Decided by the pipeline; never recomputed downstream. */
	private $amount;

	/** @var Money The full order total before partial-payment features. Display/record only. */
	private $order_total;

	/** @var string Human description for the gateway's own UI / statement. */
	private $description;

	/** @var array Customer identity: id, email, first_name, last_name, phone. */
	private $customer;

	/** @var array List of line items: label, amount, quantity. */
	private $line_items;

	/** @var string One of GROUP_*. */
	private $group_mode;

	/** @var array Free-form, context-owned. Gateways pass it through opaquely. */
	private $metadata;

	/**
	 * @param array $args See the property docs above.
	 */
	public function __construct( array $args ) {
		$this->context_id   = isset( $args['context_id'] ) ? (string) $args['context_id'] : '';
		$this->reference_id = isset( $args['reference_id'] ) ? (string) $args['reference_id'] : '';
		$this->token        = isset( $args['token'] ) ? (string) $args['token'] : '';
		$this->description  = isset( $args['description'] ) ? (string) $args['description'] : '';
		$this->group_mode   = ( isset( $args['group_mode'] ) && self::GROUP_SHARED === $args['group_mode'] )
			? self::GROUP_SHARED
			: self::GROUP_PER_BOOKING;

		$this->amount = ( isset( $args['amount'] ) && $args['amount'] instanceof Money )
			? $args['amount']
			: new Money( 0, 'USD' );

		$this->order_total = ( isset( $args['order_total'] ) && $args['order_total'] instanceof Money )
			? $args['order_total']
			: $this->amount;

		$this->customer = array_merge(
			array(
				'id'         => 0,
				'email'      => '',
				'first_name' => '',
				'last_name'  => '',
				'phone'      => '',
			),
			isset( $args['customer'] ) && is_array( $args['customer'] ) ? $args['customer'] : array()
		);

		$this->line_items = isset( $args['line_items'] ) && is_array( $args['line_items'] ) ? $args['line_items'] : array();
		$this->metadata   = isset( $args['metadata'] ) && is_array( $args['metadata'] ) ? $args['metadata'] : array();
	}

	/** @return string */
	public function get_context_id() {
		return $this->context_id;
	}

	/** @return string */
	public function get_reference_id() {
		return $this->reference_id;
	}

	/** @return string */
	public function get_token() {
		return $this->token;
	}

	/**
	 * The SEALED amount to charge. This is the only number a gateway may use.
	 *
	 * @return Money
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Full order total before partial-payment features (Deposit, Gift Card...).
	 * For display and bookkeeping ONLY — never charge this.
	 *
	 * @return Money
	 */
	public function get_order_total() {
		return $this->order_total;
	}

	/** @return string */
	public function get_description() {
		return $this->description;
	}

	/** @return array */
	public function get_customer() {
		return $this->customer;
	}

	/** @return array */
	public function get_line_items() {
		return $this->line_items;
	}

	/** @return string */
	public function get_group_mode() {
		return $this->group_mode;
	}

	/** @return bool */
	public function is_shared_payment() {
		return self::GROUP_SHARED === $this->group_mode;
	}

	/**
	 * @param string $key
	 * @param mixed  $default_value
	 *
	 * @return mixed
	 */
	public function meta( $key, $default_value = null ) {
		return array_key_exists( $key, $this->metadata ) ? $this->metadata[ $key ] : $default_value;
	}

	/** @return array */
	public function get_metadata() {
		return $this->metadata;
	}

	/**
	 * Stable correlation key written into the gateway's own metadata so an
	 * out-of-band webhook can find its way home without trusting anything the
	 * browser said. Replaces the ad-hoc `metadata.custom = entry_id|is_cart`
	 * and `purchase_units[0].reference_id = entry_id` conventions, which each
	 * gateway invented separately.
	 *
	 * @return string
	 */
	public function get_correlation_key() {
		return $this->context_id . ':' . $this->reference_id;
	}

	/**
	 * Parse a correlation key back into its parts.
	 *
	 * @param string $key
	 *
	 * @return array|null Keys: context_id, reference_id. Null when malformed.
	 */
	public static function parse_correlation_key( $key ) {
		$key = (string) $key;
		if ( false === strpos( $key, ':' ) ) {
			return null;
		}
		$parts        = explode( ':', $key, 2 );
		$context_id   = $parts[0];
		$reference_id = $parts[1];
		if ( '' === $context_id || '' === $reference_id ) {
			return null;
		}
		return array(
			'context_id'   => $context_id,
			'reference_id' => $reference_id,
		);
	}

	/**
	 * Safe projection for the browser. Note the token is NOT included — it is
	 * issued to the client once by the context at stage time and replayed on
	 * confirm; it never round-trips through a gateway payload.
	 *
	 * @return array
	 */
	public function to_client_array() {
		return array(
			'context'     => $this->context_id,
			'reference'   => $this->reference_id,
			'amount'      => $this->amount->to_array(),
			'order_total' => $this->order_total->to_array(),
			'description' => $this->description,
		);
	}
}

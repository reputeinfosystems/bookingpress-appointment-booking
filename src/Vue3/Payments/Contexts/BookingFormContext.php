<?php
/**
 * BookingFormContext — the Lite `[bookingpress_form]` as a payment context.
 *
 * The reference implementation of {@see PaymentContextInterface}, and the proof
 * that the abstraction fits the existing code without rewriting it: this class
 * is an ADAPTER over `SubmissionService`, not a replacement for it. Staging is
 * still `submit()`, finalizing is still `finalize_booking()`, and every
 * `FILTER_SUBMIT_*` seam the add-ons hook keeps firing exactly where it does
 * today.
 *
 * WHERE THE AMOUNT COMES FROM
 * ---------------------------
 * `SubmissionService::submit()` already runs the full pipeline —
 * `FILTER_SUBMIT_LINE_ITEMS` expands Cart / Recurring / Multi-Service into line
 * items, then `compute_order_amounts()` runs `FILTER_SUMMARY_TOTAL` and
 * `FILTER_PAYABLE_AMOUNT` over each one (Staff, Happy Hours, Custom Duration,
 * Quantity, Extras, Coupon, Package, MyCRED, ARMember, Discount, Deposit, Tax,
 * Tip, Gift Card, Waiting List...) — and persists the result on the stage-1
 * entries row.
 *
 * So the sealed amount is READ BACK FROM THAT ROW here. It is never recomputed,
 * not at prepare, not at confirm, not in a webhook. Add an amount-affecting
 * add-on tomorrow and it participates upstream of this line, invisibly to every
 * gateway:
 *
 *     $sealed = new Money( (float) $entry['bookingpress_paid_amount'], $currency );
 *
 * @package BookingPress\Vue3\Payments\Contexts
 */

namespace BookingPress\Vue3\Payments\Contexts;

use BookingPress\Vue3\Contracts\SubmissionServiceInterface;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Payments\Capabilities;
use BookingPress\Vue3\Payments\Contracts\PaymentContextInterface;
use BookingPress\Vue3\Payments\Money;
use BookingPress\Vue3\Payments\PaymentHooks;
use BookingPress\Vue3\Payments\PaymentReference;
use BookingPress\Vue3\Payments\PaymentResult;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\EntryRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;
use BookingPress\Vue3\Services\ServiceLocator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BookingFormContext implements PaymentContextInterface {

	const ID = 'booking_form';

	/** @var EntryRepository */
	private $entries;

	/** @var SettingsRepository */
	private $settings;

	public function __construct() {
		$this->entries  = new EntryRepository();
		$this->settings = new SettingsRepository();
	}

	/** @return string */
	public function get_id() {
		return self::ID;
	}

	/**
	 * Requirements are DYNAMIC, because the active add-ons decide them.
	 *
	 * With Cart or Recurring installed one charge covers many bookings, so a
	 * gateway must declare SHARED_ORDER. With Deposit (or partial Gift Card
	 * redemption) the charge is less than the order total, so PARTIAL_PAYMENT is
	 * required. Both are detected from the live filter registrations rather than
	 * hardcoded, so a future add-on with the same shape is handled without
	 * touching this class.
	 *
	 * @return string[]
	 */
	public function get_required_capabilities() {
		$required = array( Capabilities::SEALED_AMOUNT );

		// Cart / Recurring / Multi-Service collapse N bookings into one charge.
		if ( has_filter( Hooks::FILTER_SUBMIT_PAYMENT_GROUP ) ) {
			$required[] = Capabilities::SHARED_ORDER;
		}

		// Deposit / Gift Card / any partial-payment feature.
		if ( has_filter( Hooks::FILTER_PAYABLE_AMOUNT ) ) {
			$required[] = Capabilities::PARTIAL_PAYMENT;
		}

		return array_values( array_unique( $required ) );
	}

	/**
	 * Stage via the canonical submit pipeline, then SEAL what it computed.
	 *
	 * @param array $payload
	 *
	 * @return PaymentReference
	 *
	 * @throws \RuntimeException
	 */
	public function stage( array $payload ) {
		/** @var \BookingPress\Vue3\Services\SubmissionService $submission */
		$submission = ServiceLocator::get( SubmissionServiceInterface::class );

		// Runs every readiness gate, the anti-tamper price check, line-item
		// expansion and the full amount pipeline, then inserts the stage-1 row.
		$staged = $submission->submit( $payload );

		if ( is_array( $staged ) && isset( $staged['variant'] ) && 'error' === $staged['variant'] ) {
			$message = isset( $staged['error_message'] ) ? (string) $staged['error_message'] : '';
			if ( '' === $message && isset( $staged['message'] ) ) {
				$message = (string) $staged['message'];
			}
			throw new \RuntimeException( '' !== $message ? $message : 'Could not stage the booking.' );
		}

		$entry_id = isset( $staged['entry_id'] ) ? (int) $staged['entry_id'] : 0;
		$token    = isset( $staged['entry_token'] ) ? (string) $staged['entry_token'] : '';

		if ( $entry_id <= 0 ) {
			throw new \RuntimeException( 'Could not stage the booking.' );
		}
		if ( '' === $token ) {
			// Staging that does not secure the entry must not proceed to a
			// gateway — the reference would be guessable.
			throw new \RuntimeException( 'Could not secure the staged booking.' );
		}

		return $this->reference_from_entry( $entry_id, $token );
	}

	/**
	 * Rebuild from storage alone. NO pricing filters run here — see the class
	 * docblock for why that would be a charging bug rather than a refactor.
	 *
	 * @param string $reference_id
	 *
	 * @return PaymentReference|null
	 */
	public function resume( $reference_id ) {
		$entry_id = (int) $reference_id;
		if ( $entry_id <= 0 ) {
			return null;
		}

		try {
			return $this->reference_from_entry( $entry_id, '' );
		} catch ( \RuntimeException $e ) {
			return null;
		}
	}

	/**
	 * @param string $reference_id
	 * @param string $token
	 *
	 * @return bool
	 */
	public function verify_token( $reference_id, $token ) {
		// PHASE 1 RENAME: EntryRepository::verify_paypal_entry_token() is the
		// generic proof-of-possession guard despite the PayPal-era name. Rename
		// to verify_reference_token() and keep the old name as a deprecated
		// alias — released gateway builds still call it.
		return (bool) $this->entries->verify_paypal_entry_token( (int) $reference_id, (string) $token );
	}

	/**
	 * @param PaymentReference $reference
	 * @param PaymentResult    $result
	 *
	 * @return array
	 */
	public function finalize( PaymentReference $reference, PaymentResult $result ) {
		/** @var \BookingPress\Vue3\Services\SubmissionService $submission */
		$submission = ServiceLocator::get( SubmissionServiceInterface::class );

		// finalize_booking() already owns idempotency, the double-booking guard,
		// notifications and the appointment + payment row writes. PaymentResult
		// speaks its payload shape directly, so this is a one-liner rather than
		// a translation layer.
		$envelope = $submission->finalize_booking(
			(int) $reference->get_reference_id(),
			$result->to_finalize_payload()
		);

		// Attach the in-built redirection payload exactly as
		// SubmissionController::submit() does, so the inline thank-you works
		// identically for every gateway.
		$envelope = apply_filters( Hooks::FILTER_SUBMIT_ENVELOPE, $envelope, array() );

		return is_array( $envelope ) ? $envelope : array();
	}

	/**
	 * @param PaymentReference $reference
	 * @param PaymentResult    $result
	 *
	 * @return void
	 */
	public function abandon( PaymentReference $reference, PaymentResult $result ) {
		// Deliberately NOT Hooks::ACTION_AFTER_FAILED_PAYMENT — that one means
		// "the entries row was inserted but stage 2 failed" and its signature is
		// `(int $entry_id, \Throwable $error, array $payload)`. An abandoned
		// checkout is a different event with no Throwable, so it gets the
		// payments-layer action instead.
		do_action(
			PaymentHooks::ACTION_PAYMENT_FAILED,
			'abandon',
			$result,
			array(
				'context_id'   => self::ID,
				'reference_id' => (int) $reference->get_reference_id(),
				'gateway'      => $result->get_gateway_id(),
			)
		);

		// A pending entry does NOT hold capacity. Availability is computed from
		// the appointments table alone and never consults the entries table, so
		// an abandoned or declined attempt leaves a stale entry row that blocks
		// nothing; the `bookingpress_clear_pending_entries` cron prunes it after
		// 24h and no active release is needed.
		//
		// (Verified 2026-09-19. An earlier note here claimed the opposite — that
		// a pending entry held the slot for a day — and called for releasing it
		// eagerly. That work is not needed. Recorded because the claim is easy
		// to re-derive from the fact that stage() runs before payment.)
		//
		// Add-ons holding their OWN locks — a Gift Card usage lock, a Waiting
		// List hold — do still need to release them, which is what the action
		// above is for.
	}

	/**
	 * @param PaymentReference $reference
	 *
	 * @return array
	 */
	public function get_return_urls( PaymentReference $reference ) {
		/** @var \BookingPress\Vue3\Services\SubmissionService $submission */
		$submission = ServiceLocator::get( SubmissionServiceInterface::class );

		$customize = new CustomizeRepository();
		$cancel_id = (int) $customize->get( 'after_failed_payment_redirection', CustomizeRepository::GROUP_BOOKING_FORM, 0 );
		$cancel    = ( $cancel_id > 0 ) ? get_permalink( $cancel_id ) : home_url( '/' );
		if ( empty( $cancel ) ) {
			$cancel = home_url( '/' );
		}

		return array(
			'return_url' => $submission->build_redirect_url_for_entry( (int) $reference->get_reference_id() ),
			'cancel_url' => add_query_arg( 'is_cancel', 1, esc_url_raw( $cancel ) ),
		);
	}

	// ------------------------------------------------------------------
	// Internals
	// ------------------------------------------------------------------

	/**
	 * Build a sealed reference from a persisted stage-1 entry.
	 *
	 * The single place the booking form decides what is charged — and it decides
	 * it by READING, not computing.
	 *
	 * @param int    $entry_id
	 * @param string $token
	 *
	 * @return PaymentReference
	 *
	 * @throws \RuntimeException When the entry is gone.
	 */
	private function reference_from_entry( $entry_id, $token ) {
		$entry = $this->entries->find( (int) $entry_id );
		if ( null === $entry ) {
			throw new \RuntimeException( 'Could not resolve the staged booking.' );
		}

		$currency = isset( $entry['bookingpress_service_currency'] ) && '' !== $entry['bookingpress_service_currency']
			? (string) $entry['bookingpress_service_currency']
			: (string) $this->settings->get( 'payment_default_currency', SettingsRepository::GROUP_PAYMENT, 'USD' );

		// SEALED. `stage1_insert_entry()` writes the resolved payable to BOTH
		// `bookingpress_service_price` and `bookingpress_paid_amount`. We read
		// `paid_amount` because that is the column the legacy verification path
		// and the IPN/webhook handlers already compare against.
		//
		// NOTE for the migration: gateways currently disagree about which of the
		// two to read — `PaymentService::paypal_validate()` takes
		// `bookingpress_paid_amount`, `StripeFeature::rest_intent()` takes
		// `bookingpress_service_price`. They coincide for booking-form entries,
		// which is why the divergence has gone unnoticed. Pinning it here, once,
		// removes the class of bug entirely.
		$staged_amount = isset( $entry['bookingpress_paid_amount'] ) ? (float) $entry['bookingpress_paid_amount'] : 0.0;

		// QUANTIZED to the currency's minor unit. The pipeline can produce an
		// amount the currency cannot express — JPY 45.57 (no minor unit at all),
		// or USD 45.028 on a site whose "number of decimals" display setting is
		// 3. Nobody can charge or refund such a value, so it is not a valid seal.
		$sealed = ( new Money( $staged_amount, $currency ) )->quantized();

		// ...and PERSISTED when it changed.
		//
		// Quantizing only on read was a bug: the seal said 46 JPY, the staged row
		// still said 45.57, PayPal charged 46, and then
		// `SubmissionService::finalize_booking()` — which rejects a paid amount
		// differing from the entry by more than 0.01 — refused to create the
		// booking AFTER the money had moved. The seal has to be written down
		// before anything is charged against it, not derived at each read.
		if ( abs( $sealed->get_amount() - $staged_amount ) > 0.0001 ) {
			$this->entries->update_sealed_amount( (int) $entry_id, $sealed->get_amount() );

			// Keep the in-memory row consistent with what we just wrote, so the
			// order-total fallback below and any caller reading `$entry` see the
			// same number the gateway is about to charge.
			$entry['bookingpress_paid_amount']   = $sealed->get_amount();
			$entry['bookingpress_service_price'] = $sealed->get_amount();

			do_action(
				'bookingpress_other_debug_log_entry',
				'payments',
				'sealed amount quantized to currency precision',
				'bookingpress payments',
				array(
					'entry_id' => (int) $entry_id,
					'currency' => $currency,
					'staged'   => $staged_amount,
					'sealed'   => $sealed->get_amount(),
				),
				0
			);
		}

		// `bookingpress_total_amount` is a PRO-ONLY column — it is silently
		// dropped on a Lite-only entries table. Fall back to the sealed amount so
		// Lite reports a coherent order total instead of zero. Display and
		// bookkeeping only; never charged.
		$order_total = new Money(
			isset( $entry['bookingpress_total_amount'] ) && '' !== $entry['bookingpress_total_amount']
				? (float) $entry['bookingpress_total_amount']
				: $sealed->get_amount(),
			$currency
		);

		return new PaymentReference(
			array(
				'context_id'   => self::ID,
				'reference_id' => (string) (int) $entry_id,
				'token'        => (string) $token,
				'amount'       => $sealed,
				'order_total'  => $order_total,
				'description'  => isset( $entry['bookingpress_service_name'] ) && '' !== $entry['bookingpress_service_name']
					? (string) $entry['bookingpress_service_name']
					: __( 'Appointment Booking', 'bookingpress-appointment-booking' ),
				'customer'     => array(
					'id'         => isset( $entry['bookingpress_customer_id'] ) ? (int) $entry['bookingpress_customer_id'] : 0,
					'email'      => isset( $entry['bookingpress_customer_email'] ) ? (string) $entry['bookingpress_customer_email'] : '',
					'first_name' => isset( $entry['bookingpress_customer_firstname'] ) ? (string) $entry['bookingpress_customer_firstname'] : '',
					'last_name'  => isset( $entry['bookingpress_customer_lastname'] ) ? (string) $entry['bookingpress_customer_lastname'] : '',
					'phone'      => isset( $entry['bookingpress_customer_phone'] ) ? (string) $entry['bookingpress_customer_phone'] : '',
				),
				'group_mode'   => has_filter( Hooks::FILTER_SUBMIT_PAYMENT_GROUP )
					? PaymentReference::GROUP_SHARED
					: PaymentReference::GROUP_PER_BOOKING,
				// Server-side only; never in to_client_array(). Gateways that
				// need more than the customer block — a billing address, say —
				// have their FEATURE class contribute it here rather than
				// reaching into the entry themselves. See
				// PaymentHooks::FILTER_REFERENCE_METADATA.
				'metadata'     => (array) apply_filters(
					PaymentHooks::FILTER_REFERENCE_METADATA,
					array(),
					$entry,
					self::ID
				),
			)
		);
	}
}

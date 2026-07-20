<?php
/**
 * SubmissionService — the heart of the §M0.10 2-step write contract.
 *
 * Pipeline:
 *   submit( $payload )
 *     ├── verify Submit-action gates via ValidationService
 *     ├── compute total via PricingService and compare with client-submitted
 *     │   price (refuses on mismatch — anti-tamper)
 *     ├── stage 1: EntryRepository::insert_pending → $entry_id
 *     ├── on on-site / zero-price → call finalize_booking() inline
 *     └── return envelope { variant, is_redirect, redirect_data, ... }
 *
 *   finalize_booking( $entry_id, $payment_payload )
 *     ├── stage 2: AppointmentRepository::insert → $booking_id
 *     ├── stage 2: PaymentTransactionRepository::insert → $payment_id
 *     ├── AppointmentRepository::update_payment_link( $booking_id, $payment_id, $booking_ref )
 *     ├── EntryRepository::mark_processed( $entry_id, STATUS_APPROVED )
 *     ├── fires Hooks::ACTION_AFTER_BOOKING( $booking_id, $entry_id, $payload )
 *     └── on failure: fires Hooks::ACTION_AFTER_FAILED_PAYMENT( $entry_id, $error, $payload )
 *
 * @package BookingPress\Vue3\Services
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C, §M0.10, §M0.11
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Contracts\PricingServiceInterface;
use BookingPress\Vue3\Contracts\SubmissionServiceInterface;
use BookingPress\Vue3\Contracts\ValidationServiceInterface;
use BookingPress\Vue3\Exceptions\ReadinessFailedException;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\AppointmentRepository;
use BookingPress\Vue3\Repositories\CustomerRepository;
use BookingPress\Vue3\Repositories\EntryRepository;
use BookingPress\Vue3\Repositories\PaymentTransactionRepository;
use BookingPress\Vue3\Repositories\ServiceRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SubmissionService implements SubmissionServiceInterface {

	/** @var ValidationServiceInterface */
	private $validation;
	/** @var PricingServiceInterface */
	private $pricing;
	/** @var EntryRepository */
	private $entries;
	/** @var AppointmentRepository */
	private $appointments;
	/** @var PaymentTransactionRepository */
	private $payments;
	/** @var CustomerRepository */
	private $customers;
	/** @var ServiceRepository */
	private $services;
	/** @var SettingsRepository */
	private $settings;

	public function __construct(
		?ValidationServiceInterface $validation = null,
		?PricingServiceInterface $pricing = null,
		?EntryRepository $entries = null,
		?AppointmentRepository $appointments = null,
		?PaymentTransactionRepository $payments = null,
		?CustomerRepository $customers = null,
		?ServiceRepository $services = null,
		?SettingsRepository $settings = null
	) {
		$this->validation   = $validation ?: new ValidationService();
		$this->pricing      = $pricing ?: new PricingService();
		$this->entries      = $entries ?: new EntryRepository();
		$this->appointments = $appointments ?: new AppointmentRepository();
		$this->payments     = $payments ?: new PaymentTransactionRepository();
		$this->customers    = $customers ?: new CustomerRepository();
		$this->services     = $services ?: new ServiceRepository();
		$this->settings     = $settings ?: new SettingsRepository();
	}

	/**
	 * @inheritDoc
	 */
	public function submit( array $payload ) {
		/**
		 * Reshape the submit payload server-side before any validation runs.
		 *
		 * @param array $payload
		 * @param array $context
		 */
		$payload = (array) apply_filters( Hooks::FILTER_SUBMIT_PAYLOAD, $payload, array() );

		/**
		 * Give a feature the chance to fully handle this submit and return its
		 * own envelope (e.g. Pro's Complete Payment settles an EXISTING
		 * appointment's remaining balance instead of booking a new one). Inert
		 * in Lite — no callback → null → the normal pipeline below runs.
		 */
		$intercepted = apply_filters( Hooks::FILTER_SUBMIT_INTERCEPT, null, $payload, $this );
		if ( is_array( $intercepted ) ) {
			return $intercepted;
		}

		// Appointment debug log — start of the booking process (legacy parity with
		// `bookingpress_book_front_appointment_func`'s "Booking data process starts"
		// at class.bookingpress_appointment_bookings.php:13973). The shared
		// `$bookingpress_other_debug_log_id` global threads the ref-id through every
		// subsequent entry in this request chain — identical pattern to the
		// `bookingpress_payment_log_entry` calls below. No-op unless the admin has
		// enabled the "Appointment" debug log (handler gates on the
		// `appointment_debug_logs` setting in the `debug_log_setting` group).
		global $bookingpress_other_debug_log_id;
		do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking data process starts', 'bookingpress_bookingform', $payload, $bookingpress_other_debug_log_id );

		// Expand into per-item ("line item") payloads. Lite seeds a single item
		// (`[$payload]`) and registers no callback, so a Lite-only install runs the
		// exact single-entry path below. A multi-appointment add-on (Cart) returns
		// one payload per appointment, each shaped like a single-item submit.
		$line_items = (array) apply_filters( Hooks::FILTER_SUBMIT_LINE_ITEMS, array( $payload ), $payload, array( 'op' => 'submit' ) );

		$line_items = array_values( array_filter( $line_items, 'is_array' ) );
		if ( empty( $line_items ) ) {
			$line_items = array( $payload );
		}
		$item_count = count( $line_items );
		$is_order   = $item_count > 1;

		// 1. Build the validation state snapshot (order-level, from the top-level payload).
		$state   = $this->build_validation_state( $payload );
		$result  = $this->validation->check_action( ValidationService::ACTION_SUBMIT, $state );
		$amounts = null;

		// A zero-payable booking does not need a gateway. Compute the amount only
		// when payment is the sole failed gate, then re-run readiness with that
		// server-authoritative value. This keeps malformed submissions out of the
		// pricing pipeline and never trusts the client's price hint for the bypass.
		if ( array( ValidationService::GATE_PAYMENT ) === array_values( $result['failed_gates'] ) ) {
			$amounts                 = $this->compute_order_amounts( $line_items );
			$state['payable_amount'] = $amounts['payable'];
			$result                  = $this->validation->check_action( ValidationService::ACTION_SUBMIT, $state );
		}
		if ( empty( $result['passed'] ) ) {
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking readiness gate failed', 'bookingpress_bookingform', array( 'failed_gates' => $result['failed_gates'] ), $bookingpress_other_debug_log_id );
			throw new ReadinessFailedException( $result['failed_gates'] );
		}

		// 2. Anti-tamper price check.
		//
		// `$expected` is the ORDER TOTAL (full price) — for a single item the
		// service total, for an order the SUM of the items' totals. `$payable` is
		// the amount charged NOW — equal to the order total unless a partial-payment
		// feature (Pro Deposit) reduces it via FILTER_PAYABLE_AMOUNT. The client
		// sends the payable as `service_price_without_currency` (mirrored through the
		// `bookingpress_form_v3_payable_amount` JS filter), so we compare against
		// `$payable`. Inert in Lite (single item, no callback → `$payable === $expected`).
		$amounts    = is_array( $amounts ) ? $amounts : $this->compute_order_amounts( $line_items );
		$expected   = $amounts['full'];
		$payable    = $amounts['payable'];
		$client_amt = isset( $payload['service_price_without_currency'] ) ? (float) $payload['service_price_without_currency'] : 0.0;
		if ( abs( $payable - $client_amt ) > 0.01 ) {
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking price mismatch', 'bookingpress_bookingform', array( 'server_total' => $payable, 'client_total' => $client_amt ), $bookingpress_other_debug_log_id );
			return $this->error_envelope( 'bp_v3_price_mismatch', sprintf( 'Server total %s does not match client %s.', $payable, $client_amt ) );
		}

		// 3. Stage 1 — insert one entries row per line item. The FIRST entry of a
		//    multi-item order is the "primary": it stores the ORDER charge total as
		//    its price, so every gateway (which charges the staged entry's price)
		//    charges the whole order; the other entries store their own per-item
		//    price. For a single item the stored price IS the payable, exactly as before.
		$entry_ids = array();
		foreach ( $line_items as $i => $item ) {
			$store_price = ( $is_order && 0 === $i ) ? $payable : (float) $amounts['items'][ $i ]['payable'];
			$eid = $this->stage1_insert_entry( $item, $store_price, array(
				'item_index' => $i,
				'item_count' => $item_count,
				'is_order'   => $is_order,
				// Order convention (same as $store_price): the PRIMARY entry
				// carries the ORDER-level total (the single payment row is built
				// from it); the other entries carry their own item total.
				'item_full'  => ( $is_order && 0 === $i )
					? (float) $expected
					: ( isset( $amounts['items'][ $i ]['full'] ) ? (float) $amounts['items'][ $i ]['full'] : (float) $expected ),
			) );
			if ( $eid <= 0 ) {
				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Could not record submission entry', 'bookingpress_bookingform', $item, $bookingpress_other_debug_log_id );
				return $this->error_envelope( 'bp_v3_entry_insert_failed', 'Could not record submission.' );
			}
			$entry_ids[] = $eid;
		}
		$entry_id = $entry_ids[0];

		// 4. Decide whether to finalize inline (on-site / zero-price) or
		//    leave the entry pending for the payment flow to confirm. The decision
		//    keys off the full order total (a deposit never applies to on-site, and
		//    a free service stays free).
		$gateway = isset( $payload['selected_payment_method'] ) ? (string) $payload['selected_payment_method'] : '';
		// Finalize inline (no gateway round-trip) for on-site, a free order
		// (full total 0), OR when there is nothing to charge NOW. The last case
		// is the prepaid-tender path: a feature (e.g. a redeemed Gift Card) can
		// drop the PAYABLE to 0 while the full total stays > 0, so the booking
		// completes with no payment. Inert for Lite (payable === expected) and for
		// Deposit (payable > 0); coupon-zero / free already trigger via $expected.
		$nothing_payable = ( $payable <= 0.0 );
		$finalize_inline = ( 'on-site' === $gateway ) || ( $expected <= 0.0 ) || $nothing_payable;

		if ( $finalize_inline ) {
			// A free order ($expected <= 0) or a fully prepaid-tender order
			// ($nothing_payable, e.g. a redeemed Gift Card) is settled in full, so
			// its payment is Paid — unchanged. A real ON-SITE booking collects the
			// money later, in person, so the payment stays PENDING (legacy parity:
			// class.bookingpress_appointment_bookings.php:4379-4389 passes the
			// `onsite_appointment_status` setting as the payment status — 1=Paid /
			// 2=Pending, default 2). The Vue3 migration had hard-coded STATUS_PAID
			// for every inline case, which wrongly marked on-site bookings Paid.
			$is_free_or_prepaid = ( $expected <= 0.0 ) || $nothing_payable;
			if ( $is_free_or_prepaid ) {
				$inline_payment_status = PaymentTransactionRepository::STATUS_PAID;
			} else {
				$onsite_pref           = (string) $this->settings->get( 'onsite_appointment_status', SettingsRepository::GROUP_GENERAL, 2 );
				$inline_payment_status = ( '1' === $onsite_pref )
					? PaymentTransactionRepository::STATUS_PAID
					: PaymentTransactionRepository::STATUS_PENDING;
			}
			try {
				$out = $this->finalize_booking( $entry_id, array(
					'payment_gateway' => $is_free_or_prepaid ? ' - ' : 'on-site',
					'payment_status'  => $inline_payment_status,
					'transaction_id'  => '',
					'paid_amount'     => $payable,
					'currency'        => $this->settings->get( 'payment_default_currency', SettingsRepository::GROUP_PAYMENT, 'USD' ),
					'payload'         => $payload,
				) );
				return $out;
			} catch ( \Throwable $e ) {
				do_action( Hooks::ACTION_AFTER_FAILED_PAYMENT, $entry_id, $e, $payload );
				return $this->error_envelope( 'bp_v3_finalize_failed', $e->getMessage() );
			}
		}

		// Otherwise (PayPal etc.): the REST controller's payment flow will
		// call finalize_booking() after capture. Return a stub redirect so
		// the client can pivot into the SDK / redirect form. The primary entry
		// id resumes the whole order on confirm.
		return array(
			'variant'       => 'pending_payment',
			'is_redirect'   => 0,
			'redirect_data' => '',
			'entry_id'      => $entry_id,
			'gateway'       => $gateway,
		);
	}

	/**
	 * Gateway-facing CENTRAL helper: the single server-authoritative Complete
	 * Payment amount to charge NOW for the appointment whose stage-1 entry is
	 * $entry_id. Returns NULL when $entry_id is not a (pending) Complete Payment
	 * entry, so a gateway can fall back to its normal booking-form amount (the
	 * booking form is therefore never affected).
	 *
	 * This is the ONE place every Complete Payment gateway (Stripe / Square /
	 * PayPal / on-site) should read its charge from. The computation itself is
	 * Pro's (Complete Payment is a Pro feature): it is delegated through
	 * {@see complete_payment_payable_breakdown()}, so on a Lite-only install
	 * this ALWAYS returns null and no Pro-only column is ever queried.
	 *
	 * @param int $entry_id Stage-1 entry id.
	 *
	 * @return float|null
	 */
	public function complete_payment_payable_for_entry( $entry_id ) {
		$breakdown = $this->complete_payment_payable_breakdown( $entry_id );
		return ( null === $breakdown || ! isset( $breakdown['final'] ) ) ? null : (float) $breakdown['final'];
	}

	/**
	 * Full Complete Payment payable breakdown for a stage-1 entry, or NULL when
	 * it is not a pending Complete Payment entry.
	 *
	 * Delegated to Pro via {@see Hooks::FILTER_COMPLETE_PAYMENT_BREAKDOWN}
	 * (Complete Payment — and its `bookingpress_complete_payment_token`
	 * column — exist only in Pro). Lite registers no callback → always null on
	 * a Lite-only install, which every caller treats as "a normal booking-form
	 * entry".
	 *
	 * Keys when Pro answers: due, coupon_discount, gift_discount, tip, final,
	 * final_with_currency, appointment_id, payment_id (`final` is authoritative).
	 *
	 * @param int $entry_id Stage-1 entry id.
	 *
	 * @return array|null
	 */
	public function complete_payment_payable_breakdown( $entry_id ) {
		$entry_id = (int) $entry_id;
		if ( $entry_id <= 0 ) {
			return null;
		}
		$breakdown = apply_filters( Hooks::FILTER_COMPLETE_PAYMENT_BREAKDOWN, null, $entry_id );
		return is_array( $breakdown ) ? $breakdown : null;
	}

	/**
	 * Compute the order's full + payable totals from the expanded line items.
	 *
	 * For a single line item this is identical to the previous inline
	 * `compute_total` + `FILTER_PAYABLE_AMOUNT` pair. For a multi-item order it
	 * sums each item's full total and each item's payable, so the gateway charge
	 * and the anti-tamper hint cover the whole order.
	 *
	 * @param array $line_items
	 *
	 * @return array{full:float, payable:float, items:array<int,array{full:float,payable:float}>}
	 */
	private function compute_order_amounts( array $line_items ) {
		$full_total    = 0.0;
		$payable_total = 0.0;
		$items         = array();
		foreach ( $line_items as $item ) {
			$item       = is_array( $item ) ? $item : array();
			$service_id = isset( $item['selected_service'] ) ? (int) $item['selected_service'] : 0;
			$full       = (float) $this->pricing->compute_total( array(
				'service_id' => $service_id,
				'form_data'  => $item,
			) );
			$payable = (float) apply_filters( Hooks::FILTER_PAYABLE_AMOUNT, $full, array(
				'service_id' => $service_id,
				'form_data'  => $item,
			) );
			$full_total    += $full;
			$payable_total += $payable;
			$items[]        = array( 'full' => $full, 'payable' => $payable );
		}
		return array(
			'full'    => $full_total,
			'payable' => $payable_total,
			'items'   => $items,
		);
	}

	/**
	 * Stage 2 — materialise the booking + payment rows.
	 *
	 * @param int   $entry_id
	 * @param array $payment_payload Keys: payment_gateway, payment_status,
	 *                               transaction_id, paid_amount, currency, payload.
	 *
	 * @return array Response envelope.
	 *
	 * @throws \RuntimeException On unrecoverable failure (caller handles +
	 *                           emits ACTION_AFTER_FAILED_PAYMENT).
	 */
	public function finalize_booking( $entry_id, array $payment_payload ) {
		$entry_id = (int) $entry_id;

		/**
		 * Give a feature the chance to fully handle this finalize and return its
		 * own envelope. Pro's Complete Payment claims a pending complete-payment
		 * entry here: it UPDATES the existing appointment / payment rows (clear
		 * token, approve, settle) with its own idempotency marker + in-progress
		 * lock instead of inserting new rows. Inert in Lite — no callback →
		 * null → booking-form entries run the standard path below unchanged.
		 */
		$intercepted = apply_filters( Hooks::FILTER_FINALIZE_INTERCEPT, null, $entry_id, $payment_payload, $this );
		if ( is_array( $intercepted ) ) {
			return $intercepted;
		}

		$entry    = $this->entries->find( $entry_id );
		if ( null === $entry ) {
			throw new \RuntimeException( sprintf( 'Entry %d not found.', $entry_id ) );
		}

		$payload  = isset( $payment_payload['payload'] ) ? (array) $payment_payload['payload'] : array();
		$gateway  = isset( $payment_payload['payment_gateway'] ) ? (string) $payment_payload['payment_gateway'] : 'on-site';
		$status   = isset( $payment_payload['payment_status'] ) ? (int) $payment_payload['payment_status'] : PaymentTransactionRepository::STATUS_PAID;
		$paid     = isset( $payment_payload['paid_amount'] ) ? (float) $payment_payload['paid_amount'] : 0.0;
		$currency = isset( $payment_payload['currency'] ) ? (string) $payment_payload['currency'] : 'USD';
		$txn_id   = isset( $payment_payload['transaction_id'] ) ? (string) $payment_payload['transaction_id'] : '';

		// Single booking + single payment (Lite default), or one shared payment
		// covering every booking of a multi-appointment ORDER (Cart). Inert in
		// Lite (no callback → 'per_booking').
		$group = (array) apply_filters( Hooks::FILTER_SUBMIT_PAYMENT_GROUP, array( 'mode' => 'per_booking' ), array(
			'entry'           => $entry,
			'payment_payload' => $payment_payload,
		) );
		if ( isset( $group['mode'] ) && 'shared' === (string) $group['mode'] ) {
			return $this->finalize_order( $entry, $group, $payment_payload );
		}

		global $bookingpress_debug_payment_log_id, $bookingpress_other_debug_log_id, $bookingpress_email_notifications;

		// Write the single appointment_bookings row.
		$written    = $this->write_one_booking_from_entry( $entry, $gateway, $paid, $currency, $payload, array( 'transaction_id' => $txn_id ) );

		// Double-booking guard vetoed this slot (e.g. taken by a concurrent
		// booking between submit and confirm). The guard consumer has already
		// recorded the conflict + initiated any refund + emailed; abort here
		// BEFORE any payment row / link / mark-processed / notification runs.
		if ( ! empty( $written['prevented'] ) ) {
			$reason  = isset( $written['reason'] ) && is_array( $written['reason'] ) ? $written['reason'] : array();
			$code    = isset( $reason['code'] ) ? (int) $reason['code'] : 409;
			$message = isset( $reason['message'] ) && '' !== (string) $reason['message']
				? (string) $reason['message']
				: __( 'This time slot is no longer available.', 'bookingpress-appointment-booking' );
			return $this->error_envelope( $code, $message );
		}

		$booking_id = $written['booking_id'];
		$appt_data  = $written['appt_data'];

		// Legacy parity (class.bookingpress_payment_gateways.php:647-651):
		// the customer-facing booking ref comes from a shared counter
		// (`bookingpress_last_invoice_id` under `invoice_setting`) that the
		// backend admin add-appointment flow also increments, NOT the
		// appointment_bookings PK. Reading the PK directly produces the
		// jumping-ID symptom users see when admin + frontend bookings
		// interleave with deleted/orphaned rows.
		$invoice_id = $this->next_invoice_id();

		// Payment transaction row.
		$payment_data = $this->build_payment_row_from_entry( $entry, array(
			'payment_gateway' => $gateway,
			'paid_amount'     => $paid,
			'currency'        => $currency,
			'transaction_id'  => $txn_id,
			'payment_status'  => $status,
			'booking_id'      => $booking_id,
			'invoice_id'      => $invoice_id,
		) );

		/**
		 * Pre-write hook for the payment transaction row — opportunity for Pro /
		 * add-ons to persist extra columns (coupon / tax / deposit) that the
		 * legacy admin payment views read back from THIS row. The entry is passed
		 * so a callback can copy values it already stored on the entry, which also
		 * covers the PayPal-confirm path where $payload may be empty. Inert in Lite.
		 *
		 * @param array $payment_data The payment_transactions row data.
		 * @param array $payload      The submit payload (may be empty on confirm).
		 * @param array $entry        The stage-1 entries row.
		 */
		$payment_data = (array) apply_filters( Hooks::FILTER_SUBMIT_PAYMENT, $payment_data, $payload, $entry );

		// Legacy parity (class.bookingpress_payment_gateways.php:688).
		do_action( 'bookingpress_payment_log_entry', $gateway, 'before insert payment', 'bookingpress', $payment_data, $bookingpress_debug_payment_log_id );

		$payment_id   = $this->payments->insert( $payment_data );

		// Close the triangle: link booking → payment. The booking_ref
		// written to appointment_bookings.bookingpress_booking_id matches
		// the invoice_id on the payment row — legacy parity.
		$booking_ref = (string) $invoice_id;
		$this->appointments->update_payment_link( $booking_id, $payment_id, $booking_ref );

		// Mark entry processed (advisory — not blocking).
		$this->entries->mark_processed( $entry_id, EntryRepository::STATUS_APPROVED );

		// Honour the `allow_wp_user_create` setting (legacy parity with
		// `bookingpress_create_customer` in class.bookingpress_customers.php).
		$this->maybe_create_wp_user_for_customer( $entry, (int) ( isset( $entry['bookingpress_customer_id'] ) ? $entry['bookingpress_customer_id'] : 0 ) );

		// Build the redirect URL using base64(entry_id) per §M0.10 redirect convention.
		$redirect_url = $this->build_redirect_url( $entry_id );

		/**
		 * Post-write action — add-ons persist custom-field values here.
		 *
		 * @param int   $booking_id
		 * @param int   $entry_id
		 * @param array $payload
		 */
		do_action( Hooks::ACTION_AFTER_BOOKING, $booking_id, $entry_id, $payload );

		// Notification (gated by the plan — Lite default sends one).
		$plan = (array) apply_filters( Hooks::FILTER_SUBMIT_NOTIFICATION_PLAN, array( 'send' => true, 'booking_id' => $booking_id ), array(
			'booking_id' => $booking_id,
			'entry'      => $entry,
			'is_first'   => true,
			'order_id'   => 0,
		) );
		if ( ! empty( $plan['send'] ) ) {
			$send_booking_id = isset( $plan['booking_id'] ) ? (int) $plan['booking_id'] : $booking_id;
			$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification(
				$this->notification_type_from_status( $appt_data ),
				$send_booking_id,
				isset( $entry['bookingpress_customer_email'] ) ? (string) $entry['bookingpress_customer_email'] : ''
			);
		}

		return array(
			'variant'       => 'redirect_url',
			'is_redirect'   => 1,
			'redirect_data' => $redirect_url,
			'entry_id'      => $entry_id,
			'booking_id'    => $booking_id,
			'payment_id'    => $payment_id,
		);
	}

	/**
	 * Build + insert ONE appointment_bookings row from an entry (incl. the
	 * `FILTER_SUBMIT_PRE` pre-write hook + the legacy debug logs). Shared by the
	 * single-booking and the shared-order finalize paths.
	 *
	 * @param array  $entry
	 * @param string $gateway
	 * @param float  $paid
	 * @param string $currency
	 * @param array  $payload
	 *
	 * @return array{booking_id:int, appt_data:array}
	 *
	 * @throws \RuntimeException When the insert fails.
	 */
	private function write_one_booking_from_entry( array $entry, $gateway, $paid, $currency, array $payload, array $context = array() ) {
		global $bookingpress_debug_payment_log_id, $bookingpress_other_debug_log_id;

		$appt_data = $this->build_appointment_row_from_entry( $entry, array(
			'payment_gateway' => $gateway,
			'paid_amount'     => $paid,
			'currency'        => $currency,
		) );

		/**
		 * Pre-write hook — opportunity for Pro / add-ons to add columns (and, for
		 * a cart order, to correct each booking's per-item price from the order
		 * snapshot — the primary entry stores the order total for the gateway charge).
		 *
		 * @param array $appt_data
		 * @param array $payload
		 */
		$appt_data = (array) apply_filters( Hooks::FILTER_SUBMIT_PRE, $appt_data, $payload );

		/**
		 * Last-moment double-booking GUARD (legacy parity with
		 * `bookingpress_confirm_booking`'s `bookingpress_is_appointment_booked(...,
		 * $prevent_double_booking=true)` re-check). Runs on the post-PRE `$appt_data`
		 * so staff / extra-members / corrected price / duration are all settled.
		 * Inert in Lite (no callback → no prevent). A consumer returns
		 * `['prevent'=>true, ...]` to veto: we skip the insert and return a
		 * `prevented` sentinel for the caller to handle (single → error;
		 * order → skip this occurrence, book the survivors). Because the order path
		 * inserts sequentially, prior occurrences are already in the table here — a
		 * series cannot overbook itself.
		 */
		$guard = (array) apply_filters(
			Hooks::FILTER_SUBMIT_BOOKING_GUARD,
			array( 'prevent' => false ),
			$appt_data,
			$entry,
			array_merge(
				array(
					'payload'   => $payload,
					'gateway'   => $gateway,
					'paid_amount' => $paid,
					'currency'  => $currency,
				),
				$context
			)
		);
		if ( ! empty( $guard['prevent'] ) ) {
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Appointment prevented by double-booking guard', 'bookingpress_complete_appointment', array( 'reason' => $guard, 'entry_id' => isset( $entry['bookingpress_entry_id'] ) ? (int) $entry['bookingpress_entry_id'] : 0 ), $bookingpress_other_debug_log_id );
			return array(
				'booking_id' => 0,
				'prevented'  => true,
				'reason'     => $guard,
				'appt_data'  => $appt_data,
			);
		}

		do_action( 'bookingpress_payment_log_entry', $gateway, 'before insert appointment', 'bookingpress', $appt_data, $bookingpress_debug_payment_log_id );
		do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Appointment data before insert', 'bookingpress_complete_appointment', $appt_data, $bookingpress_other_debug_log_id );

		$booking_id = $this->appointments->insert( $appt_data );
		if ( $booking_id <= 0 ) {
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Failed to insert appointment', 'bookingpress_complete_appointment', $appt_data, $bookingpress_other_debug_log_id );
			throw new \RuntimeException( 'Failed to insert appointment_bookings row.' );
		}
		do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Appointment booked successfully', 'bookingpress_complete_appointment', array( 'appointment_id' => (int) $booking_id, 'entry_id' => isset( $entry['bookingpress_entry_id'] ) ? (int) $entry['bookingpress_entry_id'] : 0 ), $bookingpress_other_debug_log_id );

		return array(
			'booking_id' => (int) $booking_id,
			'appt_data'  => $appt_data,
		);
	}

	/**
	 * Finalize a multi-appointment ORDER: write N appointment_bookings rows (one
	 * per entry of the order) under ONE shared payment_transactions row.
	 *
	 * Legacy parity (class.bookingpress_pro_payment_gateways.php:1534-2042): N
	 * bookings sharing one `bookingpress_order_id`, a single invoice id, a single
	 * payment row with `appointment_booking_ref = 0`, every booking back-linked to
	 * that one payment id + the shared booking id. `ACTION_AFTER_BOOKING` fires per
	 * booking; the email is sent once (the consumer's notification plan suppresses
	 * the non-first appointments).
	 *
	 * @param array $primary_entry The entry the gateway / inline path resumed from
	 *                             (its price is the ORDER charge total).
	 * @param array $group         The FILTER_SUBMIT_PAYMENT_GROUP result (carries `order_id`).
	 * @param array $payment_payload
	 *
	 * @return array Response envelope (same shape as the single path; adds `order_id` + `booking_ids`).
	 *
	 * @throws \RuntimeException When a booking insert fails.
	 */
	private function finalize_order( array $primary_entry, array $group, array $payment_payload ) {
		global $bookingpress_debug_payment_log_id, $bookingpress_email_notifications;

		$payload   = isset( $payment_payload['payload'] ) ? (array) $payment_payload['payload'] : array();
		$gateway   = isset( $payment_payload['payment_gateway'] ) ? (string) $payment_payload['payment_gateway'] : 'on-site';
		$status    = isset( $payment_payload['payment_status'] ) ? (int) $payment_payload['payment_status'] : PaymentTransactionRepository::STATUS_PAID;
		$paid      = isset( $payment_payload['paid_amount'] ) ? (float) $payment_payload['paid_amount'] : 0.0;
		$currency  = isset( $payment_payload['currency'] ) ? (string) $payment_payload['currency'] : 'USD';
		$txn_id    = isset( $payment_payload['transaction_id'] ) ? (string) $payment_payload['transaction_id'] : '';
		$order_id  = isset( $group['order_id'] ) ? (int) $group['order_id'] : ( isset( $primary_entry['bookingpress_order_id'] ) ? (int) $primary_entry['bookingpress_order_id'] : 0 );
		$primary_id = isset( $primary_entry['bookingpress_entry_id'] ) ? (int) $primary_entry['bookingpress_entry_id'] : 0;

		// Load every entry of the order (primary first); fall back to the lone entry.
		$entries = $order_id > 0 ? $this->entries->find_by_order( $order_id ) : array();
		if ( empty( $entries ) ) {
			$entries = array( $primary_entry );
		}

		// 1. One appointment_bookings row per entry. Each booking's paid amount is
		//    its own stored price (full payment); the consumer's FILTER_SUBMIT_PRE
		//    corrects the primary booking's per-item price from the order snapshot.
		//    The double-booking guard may veto an occurrence whose slot was taken
		//    after Apply — we SKIP it and book the survivors (legacy recurring/cart
		//    parity: record + continue). `$survivor_entries` stays index-aligned with
		//    `$booking_ids`/`$appt_datas` so the notification loop below pairs 1:1.
		$booking_ids      = array();
		$appt_datas       = array();
		$survivor_entries = array();
		foreach ( $entries as $e ) {
			$entry_price = isset( $e['bookingpress_service_price'] ) ? (float) $e['bookingpress_service_price'] : 0.0;
			$written     = $this->write_one_booking_from_entry( $e, $gateway, $entry_price, $currency, $payload, array( 'transaction_id' => $txn_id ) );
			if ( ! empty( $written['prevented'] ) ) {
				// Conflicting occurrence — guard already recorded it; drop it.
				continue;
			}
			$booking_ids[]      = $written['booking_id'];
			$appt_datas[]       = $written['appt_data'];
			$survivor_entries[] = $e;
			$this->entries->mark_processed( isset( $e['bookingpress_entry_id'] ) ? (int) $e['bookingpress_entry_id'] : 0, EntryRepository::STATUS_APPROVED );
		}

		// Every occurrence conflicted → nothing to book. The guard already recorded
		// + refunded each; return an error envelope and write NO payment row.
		if ( empty( $booking_ids ) ) {
			return $this->error_envelope( 409, __( 'The selected time slots are no longer available.', 'bookingpress-appointment-booking' ) );
		}

		// 2. ONE invoice id + ONE payment row for the whole order (built from the
		//    primary entry, whose price is the order total). booking_ref stays 0 —
		//    the consumer marks it as the order payment via FILTER_SUBMIT_ORDER_COLUMNS.
		$invoice_id   = $this->next_invoice_id();
		$payment_data = $this->build_payment_row_from_entry( $primary_entry, array(
			'payment_gateway' => $gateway,
			'paid_amount'     => $paid,
			'currency'        => $currency,
			'transaction_id'  => $txn_id,
			'payment_status'  => $status,
			'booking_id'      => 0,
			'invoice_id'      => $invoice_id,
		) );
		$payment_data = (array) apply_filters( Hooks::FILTER_SUBMIT_PAYMENT, $payment_data, $payload, $primary_entry );
		do_action( 'bookingpress_payment_log_entry', $gateway, 'before insert payment', 'bookingpress', $payment_data, $bookingpress_debug_payment_log_id );
		$payment_id = $this->payments->insert( $payment_data );

		// 3. Back-link every booking → the single payment + shared booking id (invoice).
		$booking_ref = (string) $invoice_id;
		foreach ( $booking_ids as $bid ) {
			$this->appointments->update_payment_link( $bid, $payment_id, $booking_ref );
		}

		// 4. WP user once (all entries share the customer).
		$this->maybe_create_wp_user_for_customer( $primary_entry, (int) ( isset( $primary_entry['bookingpress_customer_id'] ) ? $primary_entry['bookingpress_customer_id'] : 0 ) );

		// 5. Redirect — Lite builds the entry-scoped URL; the consumer rewrites it
		//    to the order-scoped (base64(order_id) + is_cart) URL via FILTER_SUBMIT_ENVELOPE.
		$redirect_url = $this->build_redirect_url( $primary_id );

		// 6. ACTION_AFTER_BOOKING per booking; notification gated by the plan (once per order).
		//    Iterate the SURVIVORS (index-aligned with $booking_ids/$appt_datas) so a
		//    skipped (guard-vetoed) occurrence never breaks the booking_id pairing.
		$customer_email = isset( $primary_entry['bookingpress_customer_email'] ) ? (string) $primary_entry['bookingpress_customer_email'] : '';
		foreach ( $survivor_entries as $idx => $e ) {
			$bid       = isset( $booking_ids[ $idx ] ) ? (int) $booking_ids[ $idx ] : 0;
			$e_id      = isset( $e['bookingpress_entry_id'] ) ? (int) $e['bookingpress_entry_id'] : 0;
			$appt_data = isset( $appt_datas[ $idx ] ) ? $appt_datas[ $idx ] : array();

			do_action( Hooks::ACTION_AFTER_BOOKING, $bid, $e_id, $payload );

			$plan = (array) apply_filters( Hooks::FILTER_SUBMIT_NOTIFICATION_PLAN, array( 'send' => true, 'booking_id' => $bid ), array(
				'booking_id' => $bid,
				'entry'      => $e,
				'is_first'   => ( 0 === $idx ),
				'order_id'   => $order_id,
			) );
			if ( ! empty( $plan['send'] ) ) {
				$send_booking_id = isset( $plan['booking_id'] ) ? (int) $plan['booking_id'] : $bid;
				$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification(
					$this->notification_type_from_status( $appt_data ),
					$send_booking_id,
					$customer_email
				);
			}
		}

		return array(
			'variant'       => 'redirect_url',
			'is_redirect'   => 1,
			'redirect_data' => $redirect_url,
			'entry_id'      => $primary_id,
			'booking_id'    => isset( $booking_ids[0] ) ? (int) $booking_ids[0] : 0,
			'payment_id'    => (int) $payment_id,
			'order_id'      => $order_id,
			'booking_ids'   => $booking_ids,
		);
	}

	/**
	 * Map an appointment_bookings row's status to the email-notification type
	 * label (legacy parity).
	 *
	 * @param array $appt_data
	 *
	 * @return string
	 */
	private function notification_type_from_status( array $appt_data ) {
		$status = isset( $appt_data['bookingpress_appointment_status'] ) ? (string) $appt_data['bookingpress_appointment_status'] : '';
		if ( '2' === $status ) {
			return 'Appointment Pending';
		} elseif ( '1' === $status ) {
			return 'Appointment Approved';
		} elseif ( '3' === $status ) {
			return 'Appointment Canceled';
		} elseif ( '4' === $status ) {
			return 'Appointment Rejected';
		}
		return '';
	}

	/**
	 * Stage-1 pre-flight for the PayPal popup/redirect flow.
	 *
	 * Re-uses the exact same gate validation, anti-tamper price check, and
	 * entries insert that `submit()` runs — but stops short of finalize, so
	 * the caller (PaymentService::paypal_validate) can create the PayPal
	 * order with `reference_id = entry_id` and resume on capture.
	 *
	 * @param array $payload The submit-time `appointment_step_form_data`.
	 *
	 * @return array{ entry_id:int, total:float, currency:string, payload:array }
	 *
	 * @throws ReadinessFailedException When a Submit-readiness gate fails.
	 * @throws \RuntimeException When price mismatch, zero-price (paypal
	 *                           requires > 0), or stage1 insert fails.
	 */
	public function stage1_for_paypal( array $payload ) {
		// 0. Same payload filter as submit() — addons may need to mutate.
		$payload = (array) apply_filters( Hooks::FILTER_SUBMIT_PAYLOAD, $payload, array() );

		// Appointment debug log — start of the PayPal pre-flight booking process
		// (same chain semantics as submit(); see that method for details).
		global $bookingpress_other_debug_log_id;
		do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking data process starts', 'bookingpress_bookingform', $payload, $bookingpress_other_debug_log_id );

		// Expand into per-item line items (single by default — see submit()).
		$line_items = (array) apply_filters( Hooks::FILTER_SUBMIT_LINE_ITEMS, array( $payload ), $payload, array( 'op' => 'paypal' ) );
		$line_items = array_values( array_filter( $line_items, 'is_array' ) );
		if ( empty( $line_items ) ) {
			$line_items = array( $payload );
		}
		$item_count = count( $line_items );
		$is_order   = $item_count > 1;

		// 1. Gate validation.
		$state   = $this->build_validation_state( $payload );
		$result  = $this->validation->check_action( ValidationService::ACTION_SUBMIT, $state );
		$amounts = null;

		// Keep PayPal pre-flight on the same authoritative readiness contract as
		// submit(). The explicit non-zero PayPal check below still rejects attempts
		// to start a gateway flow when there is nothing to charge.
		if ( array( ValidationService::GATE_PAYMENT ) === array_values( $result['failed_gates'] ) ) {
			$amounts                 = $this->compute_order_amounts( $line_items );
			$state['payable_amount'] = $amounts['payable'];
			$result                  = $this->validation->check_action( ValidationService::ACTION_SUBMIT, $state );
		}
		if ( empty( $result['passed'] ) ) {
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking readiness gate failed', 'bookingpress_bookingform', array( 'failed_gates' => $result['failed_gates'] ), $bookingpress_other_debug_log_id );
			throw new ReadinessFailedException( $result['failed_gates'] );
		}

		// 2. Anti-tamper price check. `$payable` is the amount charged now (the
		//    order total unless Pro Deposit reduces it via FILTER_PAYABLE_AMOUNT);
		//    the client sends it as `service_price_without_currency`. See submit().
		$amounts    = is_array( $amounts ) ? $amounts : $this->compute_order_amounts( $line_items );
		$payable    = $amounts['payable'];
		$client_amt = isset( $payload['service_price_without_currency'] ) ? (float) $payload['service_price_without_currency'] : 0.0;
		if ( abs( $payable - $client_amt ) > 0.01 ) {
			throw new \RuntimeException( sprintf( 'Server total %s does not match client %s.', $payable, $client_amt ) );
		}

		// Legacy parity (class.bookingpress_appointment_bookings.php:524):
		// PayPal requires a non-zero charge. Free services route through
		// the inline finalize path of submit(), never here.
		if ( $payable <= 0.0 ) {
			throw new \RuntimeException( 'Service price must be more than 0 for PayPal.' );
		}

		// 3. Stage 1 — insert one entries row per line item; the primary (first)
		//    entry of an order stores the order charge total (PayPal charges it).
		$entry_ids = array();
		foreach ( $line_items as $i => $item ) {
			$store_price = ( $is_order && 0 === $i ) ? $payable : (float) $amounts['items'][ $i ]['payable'];
			$eid = $this->stage1_insert_entry( $item, $store_price, array(
				'item_index' => $i,
				'item_count' => $item_count,
				'is_order'   => $is_order,
				// Same order convention as submit(): primary entry = ORDER total.
				'item_full'  => ( $is_order && 0 === $i )
					? (float) $amounts['full']
					: ( isset( $amounts['items'][ $i ]['full'] ) ? (float) $amounts['items'][ $i ]['full'] : (float) $amounts['full'] ),
			) );
			if ( $eid <= 0 ) {
				throw new \RuntimeException( 'Could not record submission.' );
			}
			$entry_ids[] = $eid;
		}
		$entry_id = $entry_ids[0];

		$currency = (string) $this->settings->get( 'payment_default_currency', SettingsRepository::GROUP_PAYMENT, 'USD' );

		return array(
			'entry_id' => (int) $entry_id,
			'total'    => (float) $payable,
			'currency' => $currency,
			'payload'  => $payload,
		);
	}

	/**
	 * Build the post-booking redirect URL for a given entry. Public
	 * companion to {@see build_redirect_url()} so PaymentService can
	 * surface the same URL convention as the PayPal success URL.
	 *
	 * @param int $entry_id
	 *
	 * @return string
	 */
	public function build_redirect_url_for_entry( $entry_id ) {
		return $this->build_redirect_url( (int) $entry_id );
	}

	// -----------------------------------------------------------------------
	// Internals
	// -----------------------------------------------------------------------

	/**
	 * Create (or attach an existing) WordPress user for the booking's
	 * customer row when the `allow_wp_user_create` setting is enabled.
	 *
	 * Mirrors the legacy `bookingpress_create_customer()` branch at
	 * core/classes/class.bookingpress_customers.php:810-891 — same lookup
	 * semantics, same auto-login condition (only newly created users),
	 * same `bookingpress_user_update_meta` action, same
	 * `wp_send_new_user_notifications()` trigger.
	 *
	 * Idempotent: a customer row whose `bookingpress_wpuser_id` is already
	 * non-zero short-circuits and only runs the auto-login hop.
	 *
	 * @param array $entry       Entries-row array (must include the
	 *                           `bookingpress_customer_email` /
	 *                           `bookingpress_customer_firstname` /
	 *                           `bookingpress_customer_lastname` keys).
	 * @param int   $customer_id `bookingpress_customers.bookingpress_customer_id`.
	 *
	 * @return void
	 */
	private function maybe_create_wp_user_for_customer( array $entry, $customer_id ) {
		$customer_id = (int) $customer_id;
		if ( $customer_id <= 0 ) {
			return;
		}

		$allow = (string) $this->settings->get( 'allow_wp_user_create', SettingsRepository::GROUP_CUSTOMER, 'false' );
		if ( 'true' !== $allow ) {
			return;
		}

		$email = isset( $entry['bookingpress_customer_email'] ) ? trim( (string) $entry['bookingpress_customer_email'] ) : '';
		if ( '' === $email || ! is_email( $email ) ) {
			return;
		}

		$customer = $this->customers->find( $customer_id );
		if ( null === $customer ) {
			return;
		}

		// Already linked — nothing to do beyond the auto-login hop, and
		// even that we skip (legacy only auto-logs newly created users).
		if ( (int) $customer['wpUserId'] > 0 ) {
			return;
		}

		$firstname = (string) ( isset( $entry['bookingpress_customer_firstname'] ) ? $entry['bookingpress_customer_firstname'] : '' );
		$lastname  = (string) ( isset( $entry['bookingpress_customer_lastname'] ) ? $entry['bookingpress_customer_lastname'] : '' );
		$fullname  = trim( $firstname . ' ' . $lastname );
		if ( '' === $fullname ) {
			$fullname = (string) ( isset( $entry['bookingpress_customer_name'] ) ? $entry['bookingpress_customer_name'] : '' );
		}
		$username_field = (string) ( isset( $entry['bookingpress_username'] ) ? $entry['bookingpress_username'] : '' );

		if ( '' !== $username_field ) {
			$user_login = $username_field;
		} elseif ( '' !== $fullname ) {
			$user_login = $fullname;
		} else {
			$user_login = $email;
		}

		$is_new_user = false;
		$existing_wp_user = get_user_by( 'email', $email );
		if ( $existing_wp_user && ! empty( $existing_wp_user->ID ) ) {
			$wp_user_id = (int) $existing_wp_user->ID;
		} else {
			$password = (string) apply_filters( 'bookingpress_user_password_change_filter', '', array(
				'bookingpress_customer_email' => $email,
				'bookingpress_entry_id'       => (int) ( isset( $entry['bookingpress_entry_id'] ) ? $entry['bookingpress_entry_id'] : 0 ),
			) );
			$send_notification = false;
			if ( '' === $password ) {
				$password          = wp_generate_password( 12, false );
				$send_notification = true;
			}

			try {
				$created = wp_create_user( $user_login, $password, $email );
			} catch ( \Exception $e ) {
				error_log( 'BookingPress: error creating WP user on booking: ' . $e->getMessage() );
				return;
			}
			if ( is_wp_error( $created ) ) {
				error_log( 'BookingPress: error creating WP user on booking: ' . $created->get_error_message() );
				return;
			}
			$wp_user_id  = (int) $created;
			$is_new_user = true;
			if ( $send_notification && $wp_user_id > 0 ) {
				wp_send_new_user_notifications( $wp_user_id );
			}
		}

		if ( $wp_user_id <= 0 ) {
			return;
		}

		do_action( 'bookingpress_user_update_meta', $wp_user_id, array(
			'first_name' => $firstname,
			'last_name'  => $lastname,
		) );

		$this->customers->attach_wp_user( $customer_id, $wp_user_id, $email );

		if ( $is_new_user ) {
			$this->maybe_auto_login_wp_user( $wp_user_id );
		}
	}

	/**
	 * Auto-login the freshly created WP user when both
	 * `allow_autologin_user == 'true'` and the visitor is not already
	 * signed in. Mirrors the legacy block at
	 * class.bookingpress_customers.php:870-883.
	 *
	 * @param int $wp_user_id
	 *
	 * @return void
	 */
	private function maybe_auto_login_wp_user( $wp_user_id ) {
		$wp_user_id = (int) $wp_user_id;
		if ( $wp_user_id <= 0 || is_user_logged_in() ) {
			return;
		}
		$allow = (string) $this->settings->get( 'allow_autologin_user', SettingsRepository::GROUP_CUSTOMER, 'false' );
		if ( 'true' !== $allow ) {
			return;
		}
		wp_set_auth_cookie( $wp_user_id, false, is_ssl() );
		wp_set_current_user( $wp_user_id );
		$user_to_pass = wp_get_current_user();
		do_action( 'wp_login', $wp_user_id, $user_to_pass );
	}

	/**
	 * Build a validation state snapshot from a submit payload.
	 *
	 * Hydrates services + payment_methods so the gate predicates have what
	 * they need.
	 *
	 * @param array $payload
	 *
	 * @return array
	 */
	private function build_validation_state( array $payload ) {
		// Services payload (just the needed sliver).
		$services = $this->services->get_all();
		$service_id = isset( $payload['selected_service'] ) ? (int) $payload['selected_service'] : 0;
		$selected_service = null;
		foreach ( $services as $service_row ) {
			if ( isset( $service_row['serviceId'] ) && (int) $service_row['serviceId'] === $service_id ) {
				$selected_service = $service_row;
				break;
			}
		}
		if ( is_array( $selected_service ) ) {
			$payload['selected_service_duration']      = (string) ( isset( $selected_service['serviceDurationVal'] ) ? (int) $selected_service['serviceDurationVal'] : 0 );
			$payload['selected_service_duration_unit'] = (string) ( isset( $selected_service['serviceDurationUnit'] ) ? $selected_service['serviceDurationUnit'] : '' );
		}

		// Build a minimal timeslot_payload that the datetime gate can match
		// against. We trust the (date, start_time) submitted because the
		// server has no easy way to re-derive the exact loaded payload.
		// However, we DO require the start_time to look like HH:MM.
		$date  = isset( $payload['selected_date'] ) ? (string) $payload['selected_date'] : '';
		$start = isset( $payload['selected_start_time'] ) ? (string) $payload['selected_start_time'] : '';
		if ( DayServiceHelper::is_day_service( $selected_service ) && DayServiceHelper::is_valid_ymd( $date ) ) {
			try {
				$rows = ( new AvailabilityService( $this->services ) )->get_timings_for_date( $service_id, $date, $payload );
			} catch ( \Throwable $e ) {
				$rows = array();
			}
			$timeslot_payload = array(
				'working_details' => ! empty( $rows ) ? array( $date => $rows ) : array(),
			);
		} else {
			$timeslot_payload = array(
				'working_details' => array(
					$date => array( array( 'start_time' => $start ) ),
				),
			);
		}

		// Build the enabled-gateway list so the payment gate can check.
		$payment = ( new PaymentService( $this->settings ) )->get_enabled_methods( array() );

		return array(
			'services'             => $services,
			'timeslot_payload'     => $timeslot_payload,
			'customer_form_fields' => array(), // Lite's required fields are validated separately by Pro / M5.
			'config'               => array( 'payment_methods' => $payment ),
			'form_data'            => $payload,
		);
	}

	/**
	 * Insert the stage-1 entries row.
	 *
	 * @param array $payload
	 * @param float $expected_total
	 * @param array $context Multi-item order context: `item_index`, `item_count`,
	 *                       `is_order` — forwarded to FILTER_SUBMIT_ORDER_COLUMNS so
	 *                       an add-on can stamp shared order columns. Empty for the
	 *                       single-item path.
	 *
	 * @return int Entry id, or 0 on failure.
	 */
	private function stage1_insert_entry( array $payload, $expected_total, array $context = array() ) {
		$service_id = (int) $payload['selected_service'];
		$service    = $this->services->find( $service_id );
		if ( null === $service ) {
			return 0;
		}
		$payload = $this->normalise_day_service_payload( $payload, $service );

		$customer_id = $this->customers->find_or_create_guest( array(
			'userEmail'           => isset( $payload['customer_email'] ) ? $payload['customer_email'] : '',
			'userFirstname'       => isset( $payload['customer_firstname'] ) ? $payload['customer_firstname'] : '',
			'userLastname'        => isset( $payload['customer_lastname'] ) ? $payload['customer_lastname'] : '',
			'userName'            => isset( $payload['customer_name'] ) ? $payload['customer_name'] : '',
			'userPhone'           => isset( $payload['customer_phone'] ) ? $payload['customer_phone'] : '',
			'userCountryDialCode' => isset( $payload['customer_phone_dial_code'] ) ? $payload['customer_phone_dial_code'] : '',
			'userTimezone'        => isset( $payload['bookingpress_customer_timezone'] ) ? $payload['bookingpress_customer_timezone'] : '',
			'wpUserId'            => is_user_logged_in() ? get_current_user_id() : 0,
		) );

		$gateway = isset( $payload['selected_payment_method'] ) ? (string) $payload['selected_payment_method'] : '';
		if ( '' === $gateway && $expected_total <= 0.0 ) {
			$gateway = ' - ';
		}

		$currency = (string) $this->settings->get( 'payment_default_currency', SettingsRepository::GROUP_PAYMENT, 'USD' );
		$status   = $this->resolve_initial_appointment_status( $gateway, $expected_total );

		// The UNIT price of the service itself (legacy `bookingpress_service_price`
		// column semantics for the booking + payment rows): catalog price by
		// default; a feature whose effective unit price differs (Custom Service
		// Duration, location pricing, …) corrects it via the seam. This is NOT
		// the entry's own price column — that stays the gateway charge below.
		$unit_price = (float) ( isset( $service['servicePrice'] ) ? $service['servicePrice'] : 0.0 );
		$unit_price = (float) apply_filters( Hooks::FILTER_SUBMIT_UNIT_PRICE, $unit_price, $service, $payload );

		// The item's FULL total (extras/persons/coupon layered — pre-deposit).
		// Staged on the entry so the finalize projection can write the legacy
		// `bookingpress_total_amount` column even on the gateway-confirm path
		// (where the payload is gone). Pro-only column — dropped on Lite tables.
		$item_full = isset( $context['item_full'] ) ? (float) $context['item_full'] : (float) $expected_total;

		$data = array(
			'bookingpress_customer_id'           => $customer_id,
			'bookingpress_customer_name'         => (string) ( isset( $payload['customer_name'] ) ? $payload['customer_name'] : '' ),
			'bookingpress_username'              => (string) ( isset( $payload['customer_username'] ) ? $payload['customer_username'] : '' ),
			'bookingpress_customer_phone'        => (string) ( isset( $payload['customer_phone'] ) ? $payload['customer_phone'] : '' ),
			'bookingpress_customer_firstname'    => (string) ( isset( $payload['customer_firstname'] ) ? $payload['customer_firstname'] : '' ),
			'bookingpress_customer_lastname'     => (string) ( isset( $payload['customer_lastname'] ) ? $payload['customer_lastname'] : '' ),
			'bookingpress_customer_country'      => (string) ( isset( $payload['customer_phone_country'] ) ? $payload['customer_phone_country'] : '' ),
			'bookingpress_customer_phone_dial_code' => (string) ( isset( $payload['customer_phone_dial_code'] ) ? $payload['customer_phone_dial_code'] : '' ),
			'bookingpress_customer_email'        => (string) ( isset( $payload['customer_email'] ) ? $payload['customer_email'] : '' ),
			'bookingpress_customer_timezone'     => (string) ( isset( $payload['bookingpress_customer_timezone'] ) ? $payload['bookingpress_customer_timezone'] : '' ),
			'bookingpress_service_id'            => $service_id,
			'bookingpress_service_name'          => (string) ( isset( $service['serviceName'] ) ? $service['serviceName'] : '' ),
			'bookingpress_service_price'         => (float) $expected_total,
			'bookingpress_service_currency'      => $currency,
			'bookingpress_service_duration_val'  => (int) ( isset( $service['serviceDurationVal'] ) ? $service['serviceDurationVal'] : 0 ),
			'bookingpress_service_duration_unit' => (string) ( isset( $service['serviceDurationUnit'] ) ? $service['serviceDurationUnit'] : 'm' ),
			'bookingpress_payment_gateway'       => $gateway,
			'bookingpress_appointment_date'      => (string) ( isset( $payload['selected_date'] ) ? $payload['selected_date'] : '' ),
			// Legacy-Pro parity: end date falls back to the START date, never to ''.
			// The client sends `selected_end_date: ''` for plain (non day-service)
			// bookings, and the column is DATE NOT NULL — an empty string would be
			// stored as `0000-00-00`, breaking duration math downstream.
			'bookingpress_appointment_end_date'  => (string) ( ( ! empty( $payload['selected_end_date'] ) && '0000-00-00' !== $payload['selected_end_date'] ) ? $payload['selected_end_date'] : ( isset( $payload['selected_date'] ) ? $payload['selected_date'] : '' ) ),
			'bookingpress_appointment_time'      => (string) ( isset( $payload['selected_start_time'] ) ? $payload['selected_start_time'] : '' ),
			'bookingpress_appointment_end_time'  => (string) ( isset( $payload['selected_end_time'] ) ? $payload['selected_end_time'] : '' ),
			'bookingpress_appointment_internal_note' => (string) ( isset( $payload['appointment_note'] ) ? $payload['appointment_note'] : '' ),
			'bookingpress_appointment_send_notifications' => 1,
			'bookingpress_appointment_status'    => $status,
			// Stage the amount to be charged on the entry itself. Legacy parity:
			// the legacy frontend booking flow stored `bookingpress_paid_amount =
			// total_amount` on the entry at creation (class.bookingpress_pro_appointment.php:2302/2359)
			// and the confirm step copied it onto the booking + payment rows.
			// The original "Updated at confirm time" stub left this at 0.0, but the
			// confirm path (build_appointment_row_from_entry / build_payment_row_from_entry)
			// derives the booking + payment amounts from the gateway response, NOT
			// from this column — so the entries row was never written and stayed 0.
			// That broke gateways whose webhook/IPN handler reads the staged entry
			// to verify the charged amount BEFORE finalize. Stage the payable amount
			// here so that verification has a non-zero figure to compare against.
			'bookingpress_paid_amount'           => (float) $expected_total,
			// Legacy entries column: the item's full total (pre-deposit). The
			// Deposit feature re-asserts the same value when a deposit applies.
			// Pro-only column — silently dropped on a Lite-only entries table.
			'bookingpress_total_amount'          => (float) $item_full,
			// Customer-timezone view of the appointment. The Vue3 path doesn't
			// do client-side TZ conversion (per §M0.4 — no client TZ math is
			// part of the contract), so we mirror the server-side selection
			// when the customer-side keys are empty. Without this fallback,
			// the legacy thank-you template reads an empty TIME column and
			// renders "12:00 am" / midnight.
			'bookingpress_selected_appointment_date' => (string) ( ! empty( $payload['customer_selected_date'] ) ? $payload['customer_selected_date'] : ( isset( $payload['selected_date'] ) ? $payload['selected_date'] : '' ) ),
			'bookingpress_selected_appointment_end_date' => (string) ( ! empty( $payload['customer_selected_end_date'] ) ? $payload['customer_selected_end_date'] : ( isset( $payload['selected_end_date'] ) ? $payload['selected_end_date'] : ( isset( $payload['selected_date'] ) ? $payload['selected_date'] : '' ) ) ),
			'bookingpress_selected_appointment_time' => (string) ( ! empty( $payload['customer_selected_time'] ) ? $payload['customer_selected_time'] : ( isset( $payload['selected_start_time'] ) ? $payload['selected_start_time'] : '' ) ),
			'bookingpress_selected_appointment_end_time' => (string) ( ! empty( $payload['customer_selected_end_time'] ) ? $payload['customer_selected_end_time'] : ( isset( $payload['selected_end_time'] ) ? $payload['selected_end_time'] : '' ) ),
		);

		/**
		 * Stage-1 pre-write hook — let Pro / add-ons persist extra columns onto
		 * the entries row (e.g. the Staff Member module's `bookingpress_staff_*`
		 * columns). Runs for both the inline and PayPal pre-flight paths, so the
		 * entry carries everything a later confirm needs. Inert in Lite.
		 *
		 * @param array $data    The entries-row data.
		 * @param array $payload The submit payload (`appointment_step_form_data`).
		 */
		$data = (array) apply_filters( Hooks::FILTER_SUBMIT_ENTRY, $data, $payload );

		// Shared order-grouping columns (e.g. Cart's order_id + is_cart). Inert in
		// Lite (no callback → []); any column the entries table lacks is dropped by
		// the repository, so a Lite-only install is unaffected.
		$order_columns = (array) apply_filters( Hooks::FILTER_SUBMIT_ORDER_COLUMNS, array(), array_merge(
			array( 'phase' => 'entry' ),
			$context
		) );
		if ( ! empty( $order_columns ) ) {
			$data = array_merge( $data, $order_columns );
		}

		// Legacy parity (class.bookingpress_payment_gateways.php:285): fire
		// the debug log hook just before the entries insert. The legacy
		// handler reads/writes $bookingpress_debug_payment_log_id so related
		// entries in the same request chain share the same ref-id.
		global $bookingpress_debug_payment_log_id, $bookingpress_other_debug_log_id;
		do_action( 'bookingpress_payment_log_entry', $gateway, 'submit appointment form front', 'bookingpress', $data, $bookingpress_debug_payment_log_id );
		do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Appointment entry data before insert', 'bookingpress_bookingform', $data, $bookingpress_other_debug_log_id );

		$entry_id = $this->entries->insert_pending( $data );
		do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Appointment entry created', 'bookingpress_bookingform', array( 'entry_id' => (int) $entry_id ), $bookingpress_other_debug_log_id );

		// Stage the resolved UNIT price for the finalize projection (survives the
		// gateway confirm, where the payload is gone). No-op when the meta table
		// (Pro-created) is absent — the projection then keeps the pre-existing
		// charge-based behaviour, so a Lite-only install is unaffected.
		if ( $entry_id ) {
			$this->stage_unit_price_meta( (int) $entry_id, $unit_price );
		}

		// Post-insert seam (payload still available) — add-ons persist related
		// side-table rows keyed by the entry id that must survive the gateway
		// confirm (where the payload is empty). Inert in Lite. See Hooks doc.
		if ( $entry_id ) {
			do_action( Hooks::ACTION_AFTER_ENTRY_INSERT, (int) $entry_id, $payload, $data );
		}

		return $entry_id;
	}

	/**
	 * Meta key for the staged unit service price (see FILTER_SUBMIT_UNIT_PRICE).
	 *
	 * @var string
	 */
	const UNIT_PRICE_META_KEY = 'bookingpress_form_v3_unit_price';

	/**
	 * Whether the (Pro-created) `bookingpress_appointment_meta` table exists.
	 * Cached per request.
	 *
	 * @return bool
	 */
	private function appointment_meta_table_exists() {
		static $exists = null;
		if ( null !== $exists ) {
			return $exists;
		}
		global $wpdb;
		$table  = $wpdb->prefix . 'bookingpress_appointment_meta';
		$found  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$exists = ( $found === $table );
		return $exists;
	}

	/**
	 * Stage the resolved unit service price against the entry (appointment_meta).
	 *
	 * @param int   $entry_id
	 * @param float $unit_price
	 *
	 * @return void
	 */
	private function stage_unit_price_meta( $entry_id, $unit_price ) {
		if ( $entry_id <= 0 || ! $this->appointment_meta_table_exists() ) {
			return;
		}
		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prefix . 'bookingpress_appointment_meta',
			array(
				'bookingpress_entry_id'               => (int) $entry_id,
				'bookingpress_appointment_meta_key'   => self::UNIT_PRICE_META_KEY,
				'bookingpress_appointment_meta_value' => (string) (float) $unit_price,
			)
		);
	}

	/**
	 * Read back the staged unit service price for an entry — NULL when nothing
	 * was staged (Lite-only install, or an entry created before this staging
	 * existed), in which case the caller keeps the previous behaviour.
	 *
	 * @param int $entry_id
	 *
	 * @return float|null
	 */
	private function staged_unit_price_for_entry( $entry_id ) {
		$entry_id = (int) $entry_id;
		if ( $entry_id <= 0 || ! $this->appointment_meta_table_exists() ) {
			return null;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'bookingpress_appointment_meta';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- internal table name; values bound.
		$value = $wpdb->get_var( $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT bookingpress_appointment_meta_value FROM `{$table}` WHERE bookingpress_entry_id = %d AND bookingpress_appointment_meta_key = %s ORDER BY bookingpress_appointment_meta_id DESC LIMIT 1",
			$entry_id,
			self::UNIT_PRICE_META_KEY
		) );
		if ( null === $value || '' === $value ) {
			return null;
		}
		return (float) $value;
	}

	/**
	 * Normalize hidden date/time payload fields for a main service using unit `d`.
	 *
	 * @param array $payload
	 * @param array $service
	 *
	 * @return array
	 */
	private function normalise_day_service_payload( array $payload, array $service ) {
		if ( ! DayServiceHelper::is_day_service( $service ) ) {
			return $payload;
		}

		$date = isset( $payload['selected_date'] ) ? (string) $payload['selected_date'] : '';
		if ( ! DayServiceHelper::is_valid_ymd( $date ) ) {
			return $payload;
		}

		$duration = DayServiceHelper::duration_days( $service );
		$end_date = DayServiceHelper::inclusive_end_date( $date, $duration );

		$payload['selected_service_duration']      = (string) $duration;
		$payload['selected_service_duration_unit'] = 'd';
		$payload['selected_end_date']              = $end_date;
		$payload['selected_start_time']            = '00:00:00';
		$payload['selected_end_time']              = '00:00:00';

		if ( empty( $payload['customer_selected_date'] ) ) {
			$payload['customer_selected_date'] = $date;
		}
		if ( empty( $payload['customer_selected_end_date'] ) ) {
			$payload['customer_selected_end_date'] = $end_date;
		}
		if ( empty( $payload['customer_selected_time'] ) ) {
			$payload['customer_selected_time'] = '00:00:00';
		}
		if ( empty( $payload['customer_selected_end_time'] ) ) {
			$payload['customer_selected_end_time'] = '00:00:00';
		}

		return $payload;
	}

	/**
	 * Initial appointment_status string for the entries row.
	 *
	 * @param string $gateway
	 * @param float  $total
	 *
	 * @return string
	 */
	private function resolve_initial_appointment_status( $gateway, $total ) {
		if ( $total <= 0.0 ) {
			// Free service → Approved per `appointment_status` admin setting (default Approved).
			$pref = (string) $this->settings->get( 'appointment_status', SettingsRepository::GROUP_GENERAL, 1 );
			return $pref;
		}
		if ( 'on-site' === $gateway ) {
			// On-site → the on-site setting (legacy parity:
			// class.bookingpress_payment_gateways.php:247 reads
			// `onsite_appointment_status`; 1=Approved / 2=Pending). When the
			// option is absent or blank, honor the admin's configured
			// "Default appointment status" instead of a hardcoded constant —
			// a blank stored row would otherwise become status 0.
			$default = (string) $this->settings->get( 'appointment_status', SettingsRepository::GROUP_GENERAL, 1 );
			if ( '' === $default ) {
				$default = '1';
			}
			$pref = (string) $this->settings->get( 'onsite_appointment_status', SettingsRepository::GROUP_GENERAL, $default );
			return ( '' === $pref ) ? $default : $pref;
		}
		// Online payment (PayPal / Stripe / Square / …) → the site's default
		// appointment status (legacy parity: class.bookingpress_payment_gateways.php:244
		// reads the `appointment_status` general setting; default 1 = Approved). The
		// Vue3 migration hard-coded 2=Pending here, which wrongly left every
		// successfully-paid online booking Pending. The booking row only materialises
		// after the gateway confirms the payment, so this status lands on approved,
		// paid appointments exactly as legacy does.
		return (string) $this->settings->get( 'appointment_status', SettingsRepository::GROUP_GENERAL, 1 );
	}

	/**
	 * Project an entries row → appointment_bookings insert row.
	 *
	 * @param array $entry Raw entry row.
	 * @param array $extra payment_gateway, paid_amount, currency.
	 *
	 * @return array
	 */
	private function build_appointment_row_from_entry( array $entry, array $extra ) {
		$paid    = isset( $extra['paid_amount'] ) ? (float) $extra['paid_amount'] : 0.0;
		$gateway = isset( $extra['payment_gateway'] ) ? (string) $extra['payment_gateway'] : 'on-site';
		$currency = isset( $extra['currency'] ) ? (string) $extra['currency'] : 'USD';

		// Determine the appointment_status integer for appointment_bookings.
		$status_int = isset( $entry['bookingpress_appointment_status'] ) ? (int) $entry['bookingpress_appointment_status'] : AppointmentRepository::STATUS_PENDING;

		// Legacy column semantics: `bookingpress_service_price` on the BOOKING row
		// is the UNIT price of the service (the legacy backend calculator re-adds
		// extras, multiplies persons, adds tax and subtracts coupon from their own
		// columns — storing the charged total here double-counts all of those).
		// The staged unit price is written at stage-1 (see FILTER_SUBMIT_UNIT_PRICE);
		// when absent (Lite-only install / legacy in-flight entry) fall back to the
		// entry's price — the previous behaviour, which is identical whenever no
		// price-shaping feature is active. Same rule for `bookingpress_due_amount`:
		// legacy stores 0 unless a deposit applies (the Deposit feature stamps the
		// entry's due and re-asserts it on the row).
		$row_unit_price = $this->staged_unit_price_for_entry( isset( $entry['bookingpress_entry_id'] ) ? (int) $entry['bookingpress_entry_id'] : 0 );
		$charge_price   = (float) ( isset( $entry['bookingpress_service_price'] ) ? $entry['bookingpress_service_price'] : 0.0 );
		if ( null === $row_unit_price ) {
			$row_service_price = $charge_price;
			$row_due_amount    = max( 0.0, $charge_price - $paid );
		} else {
			$row_service_price = (float) $row_unit_price;
			$row_due_amount    = isset( $entry['bookingpress_due_amount'] ) ? max( 0.0, (float) $entry['bookingpress_due_amount'] ) : 0.0;
		}

		$entry_data = array(
			'bookingpress_entry_id'              => (int) $entry['bookingpress_entry_id'],
			'bookingpress_customer_id'           => (int) ( isset( $entry['bookingpress_customer_id'] ) ? $entry['bookingpress_customer_id'] : 0 ),
			'bookingpress_customer_name'         => (string) ( isset( $entry['bookingpress_customer_name'] ) ? $entry['bookingpress_customer_name'] : '' ),
			'bookingpress_username'              => (string) ( isset( $entry['bookingpress_username'] ) ? $entry['bookingpress_username'] : '' ),
			'bookingpress_customer_phone'        => (string) ( isset( $entry['bookingpress_customer_phone'] ) ? $entry['bookingpress_customer_phone'] : '' ),
			'bookingpress_customer_firstname'    => (string) ( isset( $entry['bookingpress_customer_firstname'] ) ? $entry['bookingpress_customer_firstname'] : '' ),
			'bookingpress_customer_lastname'     => (string) ( isset( $entry['bookingpress_customer_lastname'] ) ? $entry['bookingpress_customer_lastname'] : '' ),
			'bookingpress_customer_country'      => (string) ( isset( $entry['bookingpress_customer_country'] ) ? $entry['bookingpress_customer_country'] : '' ),
			'bookingpress_customer_phone_dial_code' => (string) ( isset( $entry['bookingpress_customer_phone_dial_code'] ) ? $entry['bookingpress_customer_phone_dial_code'] : '' ),
			'bookingpress_customer_email'        => (string) ( isset( $entry['bookingpress_customer_email'] ) ? $entry['bookingpress_customer_email'] : '' ),
			'bookingpress_staff_member_id'       => 0,
			'bookingpress_service_id'            => (int) $entry['bookingpress_service_id'],
			'bookingpress_service_name'          => (string) ( isset( $entry['bookingpress_service_name'] ) ? $entry['bookingpress_service_name'] : '' ),
			'bookingpress_service_price'         => $row_service_price,
			'bookingpress_service_currency'      => $currency,
			'bookingpress_service_duration_val'  => (int) ( isset( $entry['bookingpress_service_duration_val'] ) ? $entry['bookingpress_service_duration_val'] : 0 ),
			'bookingpress_service_duration_unit' => (string) ( isset( $entry['bookingpress_service_duration_unit'] ) ? $entry['bookingpress_service_duration_unit'] : 'm' ),
			'bookingpress_appointment_date'      => (string) ( isset( $entry['bookingpress_appointment_date'] ) ? $entry['bookingpress_appointment_date'] : '' ),
			// Legacy-Pro parity: an empty / zero end date on the entry projects as
			// the START date (the column is DATE NOT NULL; '' would persist as
			// `0000-00-00`, breaking duration math in emails and admin pages).
			// The overnight roll-over below still upgrades the midnight case.
			'bookingpress_appointment_end_date'  => (string) ( ( ! empty( $entry['bookingpress_appointment_end_date'] ) && '0000-00-00' !== $entry['bookingpress_appointment_end_date'] ) ? $entry['bookingpress_appointment_end_date'] : ( isset( $entry['bookingpress_appointment_date'] ) ? $entry['bookingpress_appointment_date'] : '' ) ),
			'bookingpress_appointment_time'      => (string) ( isset( $entry['bookingpress_appointment_time'] ) ? $entry['bookingpress_appointment_time'] : '' ),
			'bookingpress_appointment_end_time'  => (string) ( isset( $entry['bookingpress_appointment_end_time'] ) ? $entry['bookingpress_appointment_end_time'] : '' ),
			'bookingpress_appointment_internal_note' => (string) ( isset( $entry['bookingpress_appointment_internal_note'] ) ? $entry['bookingpress_appointment_internal_note'] : '' ),
			'bookingpress_appointment_send_notification' => 1,
			'bookingpress_appointment_status'    => $status_int,
			'bookingpress_paid_amount'           => $paid,
			'bookingpress_due_amount'            => $row_due_amount,
			// Legacy total column (full item total incl. extras/persons/coupon,
			// pre-deposit) — staged on the entry; dropped on Lite-only tables.
			'bookingpress_total_amount'          => (float) ( isset( $entry['bookingpress_total_amount'] ) ? $entry['bookingpress_total_amount'] : $charge_price ),
			'bookingpress_appointment_timezone'  => (string) ( isset( $entry['bookingpress_customer_timezone'] ) ? $entry['bookingpress_customer_timezone'] : '' ),
			'bookingpress_selected_appointment_date' => (string) ( isset( $entry['bookingpress_selected_appointment_date'] ) ? $entry['bookingpress_selected_appointment_date'] : '' ),
			'bookingpress_selected_appointment_end_date' => (string) ( isset( $entry['bookingpress_selected_appointment_end_date'] ) ? $entry['bookingpress_selected_appointment_end_date'] : '' ),
			'bookingpress_selected_appointment_time' => (string) ( isset( $entry['bookingpress_selected_appointment_time'] ) ? $entry['bookingpress_selected_appointment_time'] : '' ),
			'bookingpress_selected_appointment_end_time' => (string) ( isset( $entry['bookingpress_selected_appointment_end_time'] ) ? $entry['bookingpress_selected_appointment_end_time'] : '' ),
		);

		if( 'd' !== (string) $entry_data['bookingpress_service_duration_unit'] && '00:00:00' == $entry_data['bookingpress_appointment_end_time'] ) {
			$entry_data['bookingpress_appointment_end_date'] = date( 'Y-m-d', strtotime( $entry_data['bookingpress_appointment_date'] . ' +1 day' ) );
		}

		// Shared order-grouping columns (Cart order_id + is_cart). Inert in Lite.
		$order_columns = (array) apply_filters( Hooks::FILTER_SUBMIT_ORDER_COLUMNS, array(), array(
			'phase'    => 'appointment',
			'order_id' => isset( $entry['bookingpress_order_id'] ) ? (int) $entry['bookingpress_order_id'] : 0,
			'entry'    => $entry,
		) );
		if ( ! empty( $order_columns ) ) {
			$entry_data = array_merge( $entry_data, $order_columns );
		}

		return $entry_data;
	}

	/**
	 * Project an entries row → payment_transactions insert row.
	 *
	 * @param array $entry
	 * @param array $extra
	 *
	 * @return array
	 */
	private function build_payment_row_from_entry( array $entry, array $extra ) {
		// Same legacy column semantics as build_appointment_row_from_entry():
		// unit service price + deposit-only due; fall back to the previous
		// charge-based values when no unit price was staged.
		$row_unit_price = $this->staged_unit_price_for_entry( isset( $entry['bookingpress_entry_id'] ) ? (int) $entry['bookingpress_entry_id'] : 0 );
		$charge_price   = (float) ( isset( $entry['bookingpress_service_price'] ) ? $entry['bookingpress_service_price'] : 0.0 );
		$extra_paid     = isset( $extra['paid_amount'] ) ? (float) $extra['paid_amount'] : 0.0;
		if ( null === $row_unit_price ) {
			$row_service_price = $charge_price;
			$row_due_amount    = max( 0.0, $charge_price - $extra_paid );
		} else {
			$row_service_price = (float) $row_unit_price;
			$row_due_amount    = isset( $entry['bookingpress_due_amount'] ) ? max( 0.0, (float) $entry['bookingpress_due_amount'] ) : 0.0;
		}

		$data = array(
			'bookingpress_invoice_id'            => (int) ( isset( $extra['invoice_id'] ) ? $extra['invoice_id'] : 0 ),
			'bookingpress_appointment_booking_ref' => (int) ( isset( $extra['booking_id'] ) ? $extra['booking_id'] : 0 ),
			'bookingpress_customer_id'           => (int) ( isset( $entry['bookingpress_customer_id'] ) ? $entry['bookingpress_customer_id'] : 0 ),
			'bookingpress_customer_name'         => (string) ( isset( $entry['bookingpress_customer_name'] ) ? $entry['bookingpress_customer_name'] : '' ),
			'bookingpress_username'              => (string) ( isset( $entry['bookingpress_username'] ) ? $entry['bookingpress_username'] : '' ),
			'bookingpress_customer_phone'        => (string) ( isset( $entry['bookingpress_customer_phone'] ) ? $entry['bookingpress_customer_phone'] : '' ),
			'bookingpress_customer_firstname'    => (string) ( isset( $entry['bookingpress_customer_firstname'] ) ? $entry['bookingpress_customer_firstname'] : '' ),
			'bookingpress_customer_lastname'     => (string) ( isset( $entry['bookingpress_customer_lastname'] ) ? $entry['bookingpress_customer_lastname'] : '' ),
			'bookingpress_customer_country'      => (string) ( isset( $entry['bookingpress_customer_country'] ) ? $entry['bookingpress_customer_country'] : '' ),
			'bookingpress_customer_phone_dial_code' => (string) ( isset( $entry['bookingpress_customer_phone_dial_code'] ) ? $entry['bookingpress_customer_phone_dial_code'] : '' ),
			'bookingpress_customer_email'        => (string) ( isset( $entry['bookingpress_customer_email'] ) ? $entry['bookingpress_customer_email'] : '' ),
			'bookingpress_service_id'            => (int) $entry['bookingpress_service_id'],
			'bookingpress_service_name'          => (string) ( isset( $entry['bookingpress_service_name'] ) ? $entry['bookingpress_service_name'] : '' ),
			'bookingpress_service_price'         => $row_service_price,
			'bookingpress_service_duration_val'  => (int) ( isset( $entry['bookingpress_service_duration_val'] ) ? $entry['bookingpress_service_duration_val'] : 0 ),
			'bookingpress_service_duration_unit' => (string) ( isset( $entry['bookingpress_service_duration_unit'] ) ? $entry['bookingpress_service_duration_unit'] : 'm' ),
			'bookingpress_appointment_date'      => (string) ( isset( $entry['bookingpress_appointment_date'] ) ? $entry['bookingpress_appointment_date'] : '' ),
			'bookingpress_appointment_end_date'  => (string) ( isset( $entry['bookingpress_appointment_end_date'] ) ? $entry['bookingpress_appointment_end_date'] : '' ),
			'bookingpress_appointment_start_time' => (string) ( isset( $entry['bookingpress_appointment_time'] ) ? $entry['bookingpress_appointment_time'] : '' ),
			'bookingpress_appointment_end_time'  => (string) ( isset( $entry['bookingpress_appointment_end_time'] ) ? $entry['bookingpress_appointment_end_time'] : '' ),
			'bookingpress_payment_gateway'       => (string) ( isset( $extra['payment_gateway'] ) ? $extra['payment_gateway'] : '' ),
			'bookingpress_transaction_id'        => (string) ( isset( $extra['transaction_id'] ) ? $extra['transaction_id'] : '' ),
			'bookingpress_payment_status'        => (int) ( isset( $extra['payment_status'] ) ? $extra['payment_status'] : 0 ),
			'bookingpress_payment_amount'        => (float) ( isset( $extra['paid_amount'] ) ? $extra['paid_amount'] : 0.0 ),
			'bookingpress_payment_currency'      => (string) ( isset( $extra['currency'] ) ? $extra['currency'] : 'USD' ),
			'bookingpress_paid_amount'           => (float) ( isset( $extra['paid_amount'] ) ? $extra['paid_amount'] : 0.0 ),
			'bookingpress_due_amount'            => $row_due_amount,
			// Legacy total column — the full item/order total staged on the entry
			// (the ORDER total for a multi-item primary entry). Dropped on
			// Lite-only tables.
			'bookingpress_total_amount'          => (float) ( isset( $entry['bookingpress_total_amount'] ) ? $entry['bookingpress_total_amount'] : $charge_price ),
		);

		// Shared order-grouping columns (Cart: order_id + is_cart + booking_ref=0
		// for the single order payment row). Inert in Lite.
		$order_columns = (array) apply_filters( Hooks::FILTER_SUBMIT_ORDER_COLUMNS, array(), array(
			'phase'    => 'payment',
			'order_id' => isset( $entry['bookingpress_order_id'] ) ? (int) $entry['bookingpress_order_id'] : 0,
			'entry'    => $entry,
			'extra'    => $extra,
		) );
		if ( ! empty( $order_columns ) ) {
			$data = array_merge( $data, $order_columns );
		}

		return $data;
	}

	/**
	 * Read, increment, and persist the shared invoice/booking counter, then
	 * return the new value.
	 *
	 * Mirrors the legacy backend add-appointment + payment-gateway confirm
	 * paths (`core/classes/class.bookingpress_payment_gateways.php:647-651`),
	 * which read `bookingpress_last_invoice_id` from `invoice_setting`,
	 * `++` it, write it back, and apply the
	 * `bookingpress_modify_invoice_id_externally` filter. The returned
	 * value is stored as `bookingpress_invoice_id` on the payment_logs row
	 * AND as `bookingpress_booking_id` on the appointment_bookings row, so
	 * a single counter drives both the customer-facing booking ID and the
	 * invoice number — and stays in lockstep with admin-created bookings.
	 *
	 * @return int
	 */
	private function next_invoice_id() {
		$current = (int) $this->settings->get(
			'bookingpress_last_invoice_id',
			SettingsRepository::GROUP_INVOICE,
			'0'
		);
		$next = $current + 1;
		$this->settings->set(
			'bookingpress_last_invoice_id',
			SettingsRepository::GROUP_INVOICE,
			(string) $next
		);
		return (int) apply_filters( 'bookingpress_modify_invoice_id_externally', $next );
	}

	/**
	 * @deprecated The PK-as-ref shortcut was replaced by the shared
	 *             `bookingpress_last_invoice_id` counter — see
	 *             {@see next_invoice_id()}. Retained for back-compat with
	 *             any external caller; no longer used internally.
	 *
	 * @param int $booking_id
	 *
	 * @return string
	 */
	public static function generate_booking_ref( $booking_id ) {
		return (string) (int) $booking_id;
	}

	/**
	 * Build the post-booking redirect URL per the §M0.10 token convention.
	 *
	 * @param int $entry_id
	 *
	 * @return string
	 */
	private function build_redirect_url( $entry_id ) {
		$page_id = (int) ( new \BookingPress\Vue3\Repositories\CustomizeRepository() )->get(
			'after_booking_redirection',
			\BookingPress\Vue3\Repositories\CustomizeRepository::GROUP_BOOKING_FORM,
			0
		);
		$base = ( $page_id > 0 ) ? get_permalink( $page_id ) : home_url( '/' );
		if ( empty( $base ) ) {
			$base = home_url( '/' );
		}

		$entry_hash = md5( (string) $entry_id );
		$url = add_query_arg(
			array(
				'appointment_id' => base64_encode( (string) $entry_id ),
				'bp_tp_nonce'    => wp_create_nonce( 'bpa_nonce_url-' . $entry_hash ),
			),
			$base
		);

		// Legacy parity (class.bookingpress_payment_gateways.php:309-312).
		// When autologin is enabled, finalize_booking() may have called
		// wp_set_auth_cookie() mid-request. PHP's setcookie() only writes
		// the response headers, so $_COOKIE is unchanged and the nonce
		// above was built with an empty session_token; on the next page
		// the cookie IS present and wp_verify_nonce() would fail. The
		// `bookingpress_do_autologin` action hook on the `wp` action
		// looks for `bp_tp_token_check=yes`, rebuilds the nonce in the
		// real (post-cookie) session context, and re-redirects.
		$allow_autologin = (string) $this->settings->get( 'allow_autologin_user', SettingsRepository::GROUP_CUSTOMER, 'false' );
		if ( 'true' === $allow_autologin ) {
			$url = add_query_arg( 'bp_tp_token_check', 'yes', $url );
		}

		return $url;
	}

	/**
	 * Build a structured error envelope.
	 *
	 * @param string $code
	 * @param string $message
	 *
	 * @return array
	 */
	private function error_envelope( $code, $message ) {
		return array(
			'variant'       => 'error',
			'is_redirect'   => 0,
			'redirect_data' => '',
			'error_code'    => $code,
			'error_message' => $message,
		);
	}
}

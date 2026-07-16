<?php
/**
 * PaymentServiceInterface — payment-method discovery + PayPal validate/confirm.
 *
 * @package BookingPress\Vue3\Contracts
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.10
 */

namespace BookingPress\Vue3\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exposes the enabled gateway list and runs PayPal pre-flight + confirm.
 *
 * Lite supports two gateways: `on-site` and `paypal`. Pro adds Stripe, Razorpay,
 * Mollie, etc. by appending to the list returned from {@see get_enabled_methods()}
 * via the `bookingpress_form_v3_payment_methods` filter (also runs inside this
 * implementation; the filter is the canonical extension point).
 */
interface PaymentServiceInterface {

	/**
	 * Return the gateways enabled for the current form instance.
	 *
	 * @param array $context Optional context (e.g. `service_id` for per-service
	 *                       gateway restrictions Pro may implement).
	 *
	 * @return array<int, array{
	 *     id:    string,
	 *     label: string,
	 *     mode:  string,
	 *     icon:  string,
	 *     extra: array
	 * }> Ordered list — first item is the default selection in single-gateway scenarios.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.10
	 */
	public function get_enabled_methods( array $context = array() );

	/**
	 * Pre-flight validation for PayPal SDK / redirect flow.
	 *
	 * Re-runs every Submit-readiness gate, computes the payable amount,
	 * inserts a `pending` entry, returns the URLs the SDK should call on
	 * approve / cancel.
	 *
	 * @param array $payload The submit-time `appointment_step_form_data`.
	 *
	 * @return array{ order_id: string, entry_id: int, success_url: string, cancel_url: string }
	 *
	 * @throws \BookingPress\Vue3\Exceptions\ReadinessFailedException
	 * @throws \RuntimeException When PayPal config is incomplete.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.10
	 */
	public function paypal_validate( array $payload );

	/**
	 * Confirm a PayPal capture and finalize the booking.
	 *
	 * @param array $payload Confirm-time payload — at minimum:
	 *                       - `entry_id`        int    From the validate response.
	 *                       - `paypal_order_id` string From the SDK approve callback.
	 *
	 * @return array{ variant: string, is_redirect: int, redirect_data: string }
	 *
	 * @throws \RuntimeException On capture failure.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.10
	 */
	public function paypal_confirm( array $payload );

	/**
	 * Prepare the PayPal Standard ("Legacy") full-page redirect.
	 *
	 * Legacy mode does NOT use the REST v2 Orders API / JS SDK popup — it
	 * uses PayPal Standard: an auto-submitting HTML form that POSTs to
	 * `paypal.com/cgi-bin/webscr` (merchant-email based), with an IPN
	 * `notify_url` that finalizes the booking asynchronously once PayPal
	 * confirms the payment. The booking is already staged (`pending_payment`)
	 * by {@see SubmissionServiceInterface::submit()}, so this method only
	 * needs the resulting `entry_id`.
	 *
	 * @param array $payload At minimum `entry_id` (from the pending_payment
	 *                       envelope returned by /submit).
	 *
	 * @return array{ variant: string, is_redirect: int, redirect_data: string, entry_id: int }
	 *
	 * @throws \RuntimeException When the entry is missing or PayPal (merchant
	 *                           email) config is incomplete.
	 */
	public function paypal_redirect_prepare( array $payload );

	/**
	 * Handle a PayPal Standard IPN callback (the `notify_url` target).
	 *
	 * Re-posts the notification to PayPal (`cmd=_notify-validate`) and, on a
	 * `VERIFIED` + `Completed` response whose amount / currency / receiver /
	 * `custom` (entry_id) all match the staged entry, finalizes the booking
	 * via {@see SubmissionServiceInterface::finalize_booking()}. Idempotent:
	 * a repeat IPN for an already-finalized entry is a no-op.
	 *
	 * @param array $post The raw IPN POST fields as received from PayPal.
	 *
	 * @return bool True when the booking was (or already had been) finalized.
	 */
	public function paypal_ipn( array $post );
}

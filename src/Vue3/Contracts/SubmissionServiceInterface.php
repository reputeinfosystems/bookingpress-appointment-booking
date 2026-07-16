<?php
/**
 * SubmissionServiceInterface — final-step submit handler for `/form-v3/submit`.
 *
 * @package BookingPress\Vue3\Contracts
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C, §M0.10, §M0.11
 */

namespace BookingPress\Vue3\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs Submit-readiness gates, persists the booking, and returns the redirect
 * envelope or error.
 *
 * Owns the server-side enforcement of every gate predicate in §M0.9.C — the
 * client-side `is_basic_details_validated` flag is advisory; this service
 * re-verifies before touching the database.
 */
interface SubmissionServiceInterface {

	/**
	 * Persist a booking submission and return the response envelope.
	 *
	 * Pipeline:
	 * 1. Verify nonce + instance token (handled by REST controller, not here).
	 * 2. Re-run every gate predicate via {@see ValidationServiceInterface}.
	 *    On failure, throw a readiness exception with `failed_gates` data.
	 * 3. Compute total via {@see PricingServiceInterface::compute_total()}.
	 *    Compare with client-submitted `service_price_without_currency`; on
	 *    mismatch throw `bp_v3_price_mismatch`.
	 * 4. Insert into `tbl_bookingpress_entries` (Lite columns only).
	 * 5. Fire `bookingpress_form_v3_after_booking` action with the entry id.
	 * 6. Return redirect envelope:
	 *    `{ variant: 'redirect_url'|'success', is_redirect: 0|1, redirect_data: string }`.
	 *
	 * Custom field values are accepted, used for notification templating, and
	 * **not** persisted by Lite (§M0.11).
	 *
	 * @param array $payload Validated submit payload (the full `appointment_step_form_data`).
	 *
	 * @return array Response envelope.
	 *
	 * @throws \BookingPress\Vue3\Exceptions\ReadinessFailedException
	 * @throws \BookingPress\Vue3\Exceptions\UnknownServiceException
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C, §M0.10
	 */
	public function submit( array $payload );
}

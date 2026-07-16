<?php
/**
 * TimeslotServiceInterface — orchestrates the wire-format payload for the
 * `/form-v3/timeslots` and `/form-v3/month-details` endpoints.
 *
 * @package BookingPress\Vue3\Contracts
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.4, §M0.5, §M0.6
 */

namespace BookingPress\Vue3\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Produces the full timeslot payload for one initial or month-scoped request.
 *
 * The service delegates per-day computation to {@see AvailabilityServiceInterface}
 * and is the single owner of the wire envelope (`working_details`,
 * `vcal_attributes`, `timing_token_data`, etc.).
 */
interface TimeslotServiceInterface {

	/**
	 * Produce the initial timeslot payload (first month + token).
	 *
	 * Wire shape and behaviour are defined in §M0.4. The response is cached
	 * under `bp_v3_timeslots_<token>_<date>_v1`; cache lookup is the caller's
	 * responsibility.
	 *
	 * @param array $request Validated request body:
	 *                       - `service_id`     int    Required.
	 *                       - `category_id`    int    Optional, 0 when not categorized.
	 *                       - `selected_date`  string `YYYY-MM-DD`, site tz.
	 *                       - `selected_staff` int|string Lite always sends ''.
	 *
	 * @return array Initial payload as defined in §M0.4 (Wire shape — response).
	 *
	 * @throws \BookingPress\Vue3\Exceptions\UnknownServiceException When the service id does not exist.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.4
	 */
	public function get_initial_payload( array $request );

	/**
	 * Produce a per-month payload for calendar navigation.
	 *
	 * @param array $request Validated request body:
	 *                       - `working_hour_id` string Opaque token from the initial response.
	 *                       - `from_date`       string `YYYY-MM-DD` first day of the requested month.
	 *                       - `selected_month`  string `MM`.
	 *                       - `selected_year`   string `YYYY`.
	 *                       - `service_id`      int.
	 *
	 * @return array Month-scoped payload (same envelope as the initial payload,
	 *               minus `timing_token_data` which is echoed back from the token).
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.4, §M0.5
	 */
	public function get_month_payload( array $request );
}

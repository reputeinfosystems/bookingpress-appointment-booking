<?php
/**
 * AvailabilityServiceInterface — owns "is this date / time bookable for this
 * service / staff / location triple?"
 *
 * @package BookingPress\Vue3\Contracts
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.3, §M0.4, §M0.7
 */

namespace BookingPress\Vue3\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-day availability and disabled-date queries.
 *
 * Lite ships a default implementation that ignores staff / location filters.
 * Pro 6.0+ overrides this with an implementation that honours both. The
 * service is resolved through the Vue3 DI registry (the single
 * `bookingpress_form_v3_service` filter — Pro's callback checks for
 * `AvailabilityServiceInterface::class` and returns its implementation; see
 * plan §5a.2). Only one concrete implementation is bound per interface per
 * request.
 */
interface AvailabilityServiceInterface {

	/**
	 * Return the list of bookable timings for a given date.
	 *
	 * @param int    $service_id  The selected service id. Required.
	 * @param string $date        The target date in `YYYY-MM-DD`, site timezone.
	 * @param array  $context     Optional context:
	 *                            - `staff_id`    int    Pro: filter timings by staff. Lite ignores.
	 *                            - `location_id` int    Pro: filter timings by location. Lite ignores.
	 *                            - `category_id` int    Optional category id (for filter callbacks).
	 *
	 * @return array<int, array{
	 *     start_time:           string,
	 *     end_time:             string,
	 *     formatted_start_time: string,
	 *     formatted_end_time:   string,
	 *     is_available:         int,
	 *     remaining_capacity:   int|null,
	 *     is_overnight:         int
	 * }> Zero or more `TimeslotRow` shapes as defined in §M0.4.
	 *
	 * @throws \BookingPress\Vue3\Exceptions\UnknownServiceException When the service id does not exist.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.4
	 */
	public function get_timings_for_date( $service_id, $date, array $context = array() );

	/**
	 * Return the list of disabled dates for a service.
	 *
	 * Includes admin Days Off rows, capacity-zero overrides, and past dates.
	 *
	 * @param int   $service_id The service to query.
	 * @param array $context    Optional `from_date` / `to_date` (`YYYY-MM-DD`) window.
	 *
	 * @return array<int, string> ISO date strings.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.7
	 */
	public function get_disabled_dates( $service_id, array $context = array() );
}

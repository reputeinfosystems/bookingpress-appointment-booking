<?php
/**
 * TimeslotController — `/form-v3/timeslots` and `/form-v3/month-details`.
 *
 * Thin parse → delegate → format. Business logic lives in TimeslotService.
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.4, §M0.5
 */

namespace BookingPress\Vue3\REST;

use BookingPress\Vue3\Contracts\TimeslotServiceInterface;
use BookingPress\Vue3\Exceptions\UnknownServiceException;
use BookingPress\Vue3\Services\ServiceLocator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TimeslotController {

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_initial_payload( \WP_REST_Request $request ) {
		$gate = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$request_body = array(
			'service_id'        => (int) $request->get_param( 'service_id' ),
			'category_id'       => (int) $request->get_param( 'category_id' ),
			'selected_date'     => (string) $request->get_param( 'selected_date' ),
			'selected_staff'    => (string) $request->get_param( 'selected_staff' ),
			'selected_location' => (string) $request->get_param( 'selected_location' ),
			// Serviceless-grid marker (inert in Lite — no consumer). Set by a Pro
			// feature when the Booking Form Sequence places Service after Date &
			// Time, so the day-schedule resolvers know this grid is the carrier
			// (serviceless) grid and resolve the window from the staff/general
			// shift rather than the carrier service's own shift.
			'serviceless_grid'  => (string) $request->get_param( 'serviceless_grid' ),
			// Any-staff marker (inert in Lite — no consumer). Set by the Pro Staff
			// module when the "Any Staff" option is chosen, so it can stamp each
			// slot with the staff available at it (for the post-slot assignment).
			'any_staff'         => (string) $request->get_param( 'any_staff' ),
			// Generic client-timezone passthrough (inert in Lite — no Lite
			// consumer). Pro's "Show time as per client timezone" reads these
			// from the request inside FILTER_TIMESLOT_PAYLOAD to convert + regroup
			// the slot grid into the visitor's timezone. `client_timezone` is the
			// IANA name (e.g. "Asia/Kolkata"), `client_timezone_offset` the raw
			// JS `getTimezoneOffset()` minutes, `client_dst` the 0/1 DST flag.
			'client_timezone'        => (string) $request->get_param( 'client_timezone' ),
			'client_timezone_offset' => (string) $request->get_param( 'client_timezone_offset' ),
			'client_dst'             => (string) $request->get_param( 'client_dst' ),
			// Selected service-extras passthrough (inert in Lite — no Lite
			// consumer). The Pro Service Extras module sends the legacy
			// `service_extra_details` JSON (the selection map) so its
			// FILTER_SLOT_DURATION consumer can extend the slot length by the
			// selected extras' duration — same parameter name and shape as the
			// legacy `bookingpress_modify_service_timeslot` request.
			'service_extra_details'  => (string) $request->get_param( 'service_extra_details' ),
			// Selected group-booking quantity passthrough (inert in Lite — no Lite
			// consumer). The Pro Multiple Quantity module sends the chosen number
			// of persons so its FILTER_SLOT_CAPACITY / FILTER_BOOKED_RANGES /
			// FILTER_TIMESLOT_DATA consumers can size the per-slot capacity and
			// block slots whose remaining capacity is below the chosen group size.
			'selected_quantity'      => (string) $request->get_param( 'selected_quantity' ),
			// In-cart items passthrough (inert in Lite — no Lite consumer). The Cart
			// add-on sends the already-in-cart appointments so its FILTER_BOOKED_RANGES
			// consumer counts them as occupying capacity (the next item's grid can't
			// exceed capacity / double-book). An array of per-item objects.
			'cart_booked'            => is_array( $request->get_param( 'cart_booked' ) ) ? $request->get_param( 'cart_booked' ) : array(),
			// Selected custom-duration passthrough (inert in Lite — no Lite consumer).
			// The Custom Service Duration add-on sends the chosen duration so its
			// FILTER_SLOT_DURATION consumer generates the grid with that slot length.
			'enable_custom_service_duration' => (string) $request->get_param( 'enable_custom_service_duration' ),
			'custom_service_duration_value'  => (string) $request->get_param( 'custom_service_duration_value' ),
			// Selected multi-service ids passthrough (inert in Lite — no Lite consumer).
			// The Multi Service add-on sends the chosen service ids so its
			// FILTER_SLOT_DURATION consumer builds the grid at the SUMMED duration.
			'multi_service_ids'              => (string) $request->get_param( 'multi_service_ids' ),
		);

		try {
			$payload = ServiceLocator::get( TimeslotServiceInterface::class )
				->get_initial_payload( $request_body );
		} catch ( UnknownServiceException $e ) {
			return Response::error( 'bp_v3_unknown_service', $e->getMessage(), 400 );
		} catch ( \Throwable $e ) {
			return Response::error( 'bp_v3_internal_error', $e->getMessage(), 500 );
		}

		return Response::ok( $payload );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_month_payload( \WP_REST_Request $request ) {
		$gate = new NonceGate();
		$check = $gate->verify( $request );
		if ( $check instanceof \WP_Error ) {
			return Response::from_wp_error( $check );
		}

		$request_body = array(
			'working_hour_id' => (string) $request->get_param( 'working_hour_id' ),
			'from_date'       => (string) $request->get_param( 'from_date' ),
			'selected_month'  => (string) $request->get_param( 'selected_month' ),
			'selected_year'   => (string) $request->get_param( 'selected_year' ),
			'service_id'      => (int) $request->get_param( 'service_id' ),
			// Selected staff/location passthrough (inert in Lite — no Lite
			// consumer). Mirrors get_initial_payload() so per-staff availability
			// (capacity / booked count) applies on month navigation too.
			'selected_staff'    => (string) $request->get_param( 'selected_staff' ),
			'selected_location' => (string) $request->get_param( 'selected_location' ),
			// Serviceless-grid marker — see get_initial_payload().
			'serviceless_grid'  => (string) $request->get_param( 'serviceless_grid' ),
			// Any-staff marker — see get_initial_payload().
			'any_staff'         => (string) $request->get_param( 'any_staff' ),
			// Generic client-timezone passthrough — see get_initial_payload().
			'client_timezone'        => (string) $request->get_param( 'client_timezone' ),
			'client_timezone_offset' => (string) $request->get_param( 'client_timezone_offset' ),
			'client_dst'             => (string) $request->get_param( 'client_dst' ),
			// Selected service-extras passthrough — see get_initial_payload().
			'service_extra_details'  => (string) $request->get_param( 'service_extra_details' ),
			// Selected group-booking quantity passthrough — see get_initial_payload().
			'selected_quantity'      => (string) $request->get_param( 'selected_quantity' ),
			// In-cart items passthrough — see get_initial_payload().
			'cart_booked'            => is_array( $request->get_param( 'cart_booked' ) ) ? $request->get_param( 'cart_booked' ) : array(),
			// Selected custom-duration passthrough — see get_initial_payload().
			'enable_custom_service_duration' => (string) $request->get_param( 'enable_custom_service_duration' ),
			'custom_service_duration_value'  => (string) $request->get_param( 'custom_service_duration_value' ),
			// Selected multi-service ids passthrough — see get_initial_payload().
			'multi_service_ids'              => (string) $request->get_param( 'multi_service_ids' ),
		);

		try {
			$payload = ServiceLocator::get( TimeslotServiceInterface::class )
				->get_month_payload( $request_body );
		} catch ( \Throwable $e ) {
			return Response::error( 'bp_v3_internal_error', $e->getMessage(), 500 );
		}

		return Response::ok( $payload );
	}
}

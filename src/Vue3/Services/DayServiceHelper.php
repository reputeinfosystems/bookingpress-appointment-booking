<?php
/**
 * Shared helpers for Vue3 day-service handling.
 *
 * @package BookingPress\Vue3\Services
 */

namespace BookingPress\Vue3\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DayServiceHelper {

	/**
	 * Day services are only services whose duration unit is exactly `d`.
	 *
	 * @param string $unit
	 *
	 * @return bool
	 */
	public static function is_day_unit( $unit ) {
		return 'd' === (string) $unit;
	}

	/**
	 * Detect a day-service row from either Vue3 camelCase or legacy snake keys.
	 *
	 * @param array|null $service
	 *
	 * @return bool
	 */
	public static function is_day_service( $service ) {
		if ( ! is_array( $service ) ) {
			return false;
		}
		$unit = isset( $service['serviceDurationUnit'] )
			? $service['serviceDurationUnit']
			: ( isset( $service['bookingpress_service_duration_unit'] ) ? $service['bookingpress_service_duration_unit'] : '' );
		return self::is_day_unit( $unit );
	}

	/**
	 * Return the positive day duration for a day-service row.
	 *
	 * @param array|null $service
	 *
	 * @return int
	 */
	public static function duration_days( $service ) {
		if ( ! is_array( $service ) ) {
			return 1;
		}
		$val = isset( $service['serviceDurationVal'] )
			? (int) $service['serviceDurationVal']
			: ( isset( $service['bookingpress_service_duration_val'] ) ? (int) $service['bookingpress_service_duration_val'] : 1 );
		return max( 1, $val );
	}

	/**
	 * Inclusive appointment end date for a day-service duration.
	 *
	 * Legacy date-range conflict checks treat a 1-day service as occupying only
	 * the selected date and a multi-day service as start + (duration - 1).
	 *
	 * @param string $start_date Y-m-d.
	 * @param int    $duration_days
	 *
	 * @return string
	 */
	public static function inclusive_end_date( $start_date, $duration_days ) {
		$start_date = (string) $start_date;
		if ( ! self::is_valid_ymd( $start_date ) ) {
			return $start_date;
		}
		$days = max( 1, (int) $duration_days );
		if ( 1 === $days ) {
			return $start_date;
		}
		return gmdate( 'Y-m-d', strtotime( $start_date . ' +' . ( $days - 1 ) . ' days' ) );
	}

	/**
	 * @param string $date
	 *
	 * @return bool
	 */
	public static function is_valid_ymd( $date ) {
		return is_string( $date ) && 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date );
	}
}

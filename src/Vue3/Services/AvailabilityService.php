<?php
/**
 * AvailabilityService — Lite default for "is this date/time bookable?".
 *
 * Lite ignores staff/location filters (per §M0.3 — Lite has no Staff/Location
 * UI). Pro 6.0+ overrides via the single `bookingpress_form_v3_service`
 * filter to honour staff/location.
 *
 * @package BookingPress\Vue3\Services
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.3, §M0.4, §M0.7
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Contracts\AvailabilityServiceInterface;
use BookingPress\Vue3\Exceptions\UnknownServiceException;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\AppointmentRepository;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\ServiceRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AvailabilityService implements AvailabilityServiceInterface {

	/** @var ServiceRepository */
	private $services;
	/** @var AppointmentRepository */
	private $appointments;
	/** @var SettingsRepository */
	private $settings;
	/** @var DateFormatService */
	private $dates;
	/** @var CustomizeRepository */
	private $customize;

	public function __construct(
		?ServiceRepository $services = null,
		?AppointmentRepository $appointments = null,
		?SettingsRepository $settings = null,
		?DateFormatService $dates = null,
		?CustomizeRepository $customize = null
	) {
		$this->services     = $services ?: new ServiceRepository();
		$this->appointments = $appointments ?: new AppointmentRepository();
		$this->settings     = $settings ?: new SettingsRepository();
		$this->dates        = $dates ?: new DateFormatService();
		$this->customize    = $customize ?: new CustomizeRepository();
	}

	/**
	 * @inheritDoc
	 */
	public function get_timings_for_date( $service_id, $date, array $context = array() ) {
		$service_id = (int) $service_id;
		$service    = $this->services->find( $service_id );
		if ( null === $service ) {
			throw new UnknownServiceException( sprintf( 'Service id %d not found.', $service_id ) );
		}

		if ( DayServiceHelper::is_day_service( $service ) ) {
			return $this->get_day_timings_for_date( $service_id, (string) $date, $service, $context );
		}

		// Build the Lite DEFAULT day schedule, then let a consumer replace it
		// per service. This is the Vue3 analog of the legacy
		// `bookingpress_retrieve_pro_modules_timeslots` chain — Pro's per-service
		// "Shift Management" (holidays / special days / working hours) and, at a
		// higher priority, a future per-staff shift feature hook
		// Hooks::FILTER_DAY_SCHEDULE to override the source of the day's window /
		// break gaps / off-state. Lite registers no callback, so the default
		// computed here is what a Lite-only install uses unchanged:
		//   - is_off : date is a company holiday or in the past (get_disabled_dates).
		//   - window : the general weekday working hours (may be null = closed).
		//   - breaks : the general weekday break gaps (default_workhours is_break=1).
		$schedule = $this->get_schedule_for_date( $service_id, (string) $date, $context );

		// Off, or no usable window → no slots. The date then greys naturally: it
		// falls out of the month walker's non-empty `working_details` set.
		if ( ! is_array( $schedule )
			|| ! empty( $schedule['is_off'] )
			|| empty( $schedule['window'] )
			|| ! is_array( $schedule['window'] )
			|| empty( $schedule['window']['start'] )
			|| empty( $schedule['window']['end'] )
		) {
			return array();
		}

		$window = $schedule['window'];
		$breaks = ( isset( $schedule['breaks'] ) && is_array( $schedule['breaks'] ) ) ? $schedule['breaks'] : array();

		/**
		 * Reshape the bookable window for this service/date before slots are
		 * generated. Lets a consumer narrow/shift the window per service (e.g.
		 * Pro's "Buffer Time" pushes the start forward and pulls the end in,
		 * shifting the whole generated grid). Inert in Lite (no callback).
		 *
		 * @param array  $window     `['start' => 'HH:MM:SS', 'end' => 'HH:MM:SS']`.
		 * @param int    $service_id
		 * @param string $date
		 * @param array  $context
		 */
		$window = apply_filters( Hooks::FILTER_SLOT_WINDOW, $window, (int) $service_id, (string) $date, $context );

		if ( ! is_array( $window ) || empty( $window['start'] ) || empty( $window['end'] ) ) {
			return array();
		}

		$duration_min = $this->service_duration_minutes( $service );

		/**
		 * Override the slot duration (minutes) before slots are generated. Lets a
		 * consumer build the grid with a duration that does NOT come from the
		 * service — e.g. Pro's service-after-datetime feature uses the global
		 * "Default service duration" so the grid can be built before a service is
		 * chosen. Inert in Lite (no callback → unchanged).
		 *
		 * @param int    $duration_min
		 * @param int    $service_id
		 * @param string $date
		 * @param array  $context
		 * @see Hooks::FILTER_SLOT_DURATION
		 */
		$duration_min = (int) apply_filters( Hooks::FILTER_SLOT_DURATION, (int) $duration_min, (int) $service_id, (string) $date, $context );
		if ( $duration_min <= 0 ) {
			return array();
		}

		$hide_booked = 'true' === $this->customize->get(
			'hide_already_booked_slot',
			CustomizeRepository::GROUP_BOOKING_FORM,
			'false'
		);

		/**
		 * Narrow, inert-in-Lite override of the "hide already booked slots"
		 * decision for a single service. Lets an add-on KEEP full/booked rows in
		 * the grid for a specific service even when the global setting hides them —
		 * e.g. the Waiting List add-on keeps a waiting-enabled service's full rows
		 * so they can be re-surfaced as selectable waiting slots. Lite registers no
		 * callback, so the global setting decides exactly as before for every
		 * service. Returning false keeps full rows (they arrive with
		 * `is_available = 0`); returning true hides them.
		 *
		 * @param bool   $hide_booked
		 * @param int    $service_id
		 * @param string $date
		 * @param array  $context
		 */
		$hide_booked = (bool) apply_filters( 'bookingpress_form_v3_hide_booked_slots', $hide_booked, (int) $service_id, (string) $date, $context );

		// Per-slot booking capacity. Lite has no multi-capacity UI so the
		// default is 1 (one booking per slot, unchanged behaviour). Pro 6.0+
		// hooks Hooks::FILTER_SLOT_CAPACITY to return the service's configured
		// `max_capacity`, which makes `remaining_capacity` (and `is_available`)
		// reflect the real capacity and lets the "Slots left" counter reduce
		// as bookings are made.
		$capacity = (int) apply_filters( Hooks::FILTER_SLOT_CAPACITY, 1, $service, (int) $service_id, (string) $date, $context );
		$capacity = max( 1, $capacity );

		$rows = $this->build_slots( (string) $date, $window, $duration_min, $service_id, $hide_booked, $capacity, $breaks, $context );
		

		/**
		 * Reshape per-day timeslot rows (post-build).
		 *
		 * `$service_id` is passed explicitly (mirroring Hooks::FILTER_SLOT_CAPACITY)
		 * so a consumer can resolve per-service availability rules without
		 * depending on the `$context` shape — `$context` is the full REST request
		 * on the initial-payload path but EMPTY on the month-navigation path
		 * (TimeslotService::get_month_payload), whereas the first argument here is
		 * always the correct service id.
		 *
		 * @param array  $rows
		 * @param string $date
		 * @param array  $context
		 * @param int    $service_id
		 */
		$rows = apply_filters( Hooks::FILTER_TIMESLOT_DATA, $rows, (string) $date, $context, (int) $service_id );
		

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_disabled_dates( $service_id, array $context = array() ) {
		global $wpdb;
		$table = $wpdb->prefix . 'bookingpress_default_daysoff';

		$rows = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT bookingpress_dayoff_date, bookingpress_dayoff_enddate, bookingpress_repeat, bookingpress_dayoff_repeat_frequency, bookingpress_dayoff_repeat_times, bookingpress_dayoff_parent FROM `{$table}`", ARRAY_A
		);
		$dates = array();
		$today = current_time( 'Y-m-d' );

		if ( is_array( $rows ) ) {

			foreach ( $rows as $r ) {
				$start = isset( $r['bookingpress_dayoff_date'] ) ? (string) $r['bookingpress_dayoff_date'] : '';
				$end   = isset( $r['bookingpress_dayoff_enddate'] ) ? (string) $r['bookingpress_dayoff_enddate'] : '';
				$rep   = ! empty( $r['bookingpress_repeat'] );

				if ( '' === $start ) {
					continue;
				}
				$start_ts = strtotime( $start );
				if ( false === $start_ts ) {
					continue;
				}
				$end_ts = ( '' === $end || '0000-00-00' === $end ) ? $start_ts : strtotime( $end );
				if ( false === $end_ts || $end_ts < $start_ts ) {
					$end_ts = $start_ts;
				}

				if ( $rep ) {
					// Annual repeat — emit (month-day) into each year we care about.
					$freq  = (int) ($r['bookingpress_dayoff_repeat_frequency'] ?? 1);
					$limit = (int) ($r['bookingpress_dayoff_repeat_times'] ?? 10);

					$y0 = (int) gmdate('Y', $start_ts);

					for ($i = 0; $i < $limit; $i++) {

						$y = $y0 + ($i * $freq);

						$s = strtotime($y . '-' . gmdate('m-d', $start_ts));
						$e = strtotime($y . '-' . gmdate('m-d', $end_ts));

						if (!$s || !$e) continue;

						for ($t = $s; $t <= $e; $t += DAY_IN_SECONDS) {
							$dates[] = gmdate('Y-m-d', $t);
						}
					}
				} else {
					// Single date or range — emit each day in [start, end].
					for ( $ts = $start_ts; $ts <= $end_ts; $ts += DAY_IN_SECONDS ) {
						$dates[] = gmdate( 'Y-m-d', $ts );
					}
				}
			}
		}

		// Add past dates (within the visible window).
		if ( isset( $context['from_date'] ) && isset( $context['to_date'] ) ) {
			$from_ts = strtotime( (string) $context['from_date'] );
			$to_ts   = strtotime( (string) $context['to_date'] );
			if ( false !== $from_ts && false !== $to_ts ) {
				$today_ts = strtotime( $today );
				for ( $ts = $from_ts; $ts < $today_ts && $ts <= $to_ts; $ts += DAY_IN_SECONDS ) {
					$dates[] = gmdate( 'Y-m-d', $ts );
				}
			}
		}

		$dates = array_values( array_unique( $dates ) );

		/**
		 * Reshape the disabled-dates list.
		 *
		 * @param array $dates
		 * @param int   $service_id
		 * @param array $context
		 */
		$dates = apply_filters( Hooks::FILTER_DISABLED_DATES, $dates, (int) $service_id, $context );
		return is_array( $dates ) ? $dates : array();
	}

	// -----------------------------------------------------------------------
	// Internals
	// -----------------------------------------------------------------------

	/**
	 * Build the date schedule object used by both slot and day-service paths.
	 *
	 * @param int    $service_id
	 * @param string $date
	 * @param array  $context
	 *
	 * @return array
	 */
	private function get_schedule_for_date( $service_id, $date, array $context = array() ) {
		$disabled_context = array(
			'date'      => $date,
			'from_date' => isset( $context['from_date'] ) ? (string) $context['from_date'] : $date,
			'to_date'   => isset( $context['to_date'] ) ? (string) $context['to_date'] : $date,
		);
		$disabled = $this->get_disabled_dates( $service_id, $disabled_context );
		$weekday  = strtolower( gmdate( 'l', strtotime( (string) $date ) ) );

		$schedule = array(
			'is_off' => in_array( (string) $date, $disabled, true ),
			'window' => $this->get_working_window( $weekday ),
			'breaks' => $this->get_working_breaks( $weekday ),
			'source' => 'default',
		);

		/**
		 * Resolve the working schedule (window / break gaps / off-state) for this
		 * service/date. Fired even when the default window is null so a consumer
		 * can OPEN a generally-closed weekday, and even on the off-state so a
		 * consumer can clear it. Inert in Lite (no callback).
		 *
		 * @param array  $schedule   `['is_off'=>bool,'window'=>['start','end']|null,'breaks'=>[],'source'=>'default']`.
		 * @param int    $service_id
		 * @param string $date
		 * @param array  $context
		 * @see Hooks::FILTER_DAY_SCHEDULE
		 */
		$schedule = apply_filters( Hooks::FILTER_DAY_SCHEDULE, $schedule, (int) $service_id, (string) $date, $context );

		return is_array( $schedule ) ? $schedule : array();
	}

	/**
	 * Date-level availability for main services whose duration unit is `d`.
	 *
	 * @param int    $service_id
	 * @param string $date
	 * @param array  $service
	 * @param array  $context
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_day_timings_for_date( $service_id, $date, array $service, array $context = array() ) {
		if ( ! DayServiceHelper::is_valid_ymd( $date ) ) {
			return array();
		}

		$duration = DayServiceHelper::duration_days( $service );
		for ( $i = 0; $i < $duration; $i++ ) {
			$range_date = gmdate( 'Y-m-d', strtotime( $date . ' +' . $i . ' days' ) );
			if ( ! $this->is_day_range_date_open( $service_id, $range_date, $context ) ) {
				return array();
			}
		}

		$end_date = DayServiceHelper::inclusive_end_date( $date, $duration );
		if ( $this->day_range_has_booking_overlap( $service_id, $date, $end_date, $context ) ) {
			return array();
		}

		$rows = array(
			array(
				'is_day_service'      => 1,
				'service_date'        => $date,
				'service_end_date'    => $end_date,
				'duration_days'       => $duration,
				'duration_unit'       => 'd',
				'is_available'        => 1,
				'remaining_capacity'  => 1,
			),
		);

		$rows = apply_filters( Hooks::FILTER_TIMESLOT_DATA, $rows, (string) $date, $context, (int) $service_id );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Whether one date within a day-service range is open under core rules.
	 *
	 * @param int    $service_id
	 * @param string $date
	 * @param array  $context
	 *
	 * @return bool
	 */
	private function is_day_range_date_open( $service_id, $date, array $context = array() ) {
		$schedule = $this->get_schedule_for_date( $service_id, $date, $context );
		if ( ! is_array( $schedule )
			|| ! empty( $schedule['is_off'] )
			|| empty( $schedule['window'] )
			|| ! is_array( $schedule['window'] )
			|| empty( $schedule['window']['start'] )
			|| empty( $schedule['window']['end'] )
		) {
			return false;
		}

		$window = apply_filters( Hooks::FILTER_SLOT_WINDOW, $schedule['window'], (int) $service_id, (string) $date, $context );
		return is_array( $window ) && ! empty( $window['start'] ) && ! empty( $window['end'] );
	}

	/**
	 * Block day-service starts whose occupied date range overlaps an existing
	 * booking for the same main service.
	 *
	 * @param int    $service_id
	 * @param string $start_date
	 * @param string $end_date
	 *
	 * @return bool
	 */
	private function day_range_has_booking_overlap( $service_id, $start_date, $end_date, array $context = array() ) {
		global $wpdb;
		$table = $wpdb->prefix . 'bookingpress_appointment_bookings';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT bookingpress_appointment_date AS start_d, bookingpress_appointment_end_date AS end_d, bookingpress_service_duration_val AS duration_val, bookingpress_service_duration_unit AS duration_unit FROM `{$table}` WHERE bookingpress_service_id = %d AND bookingpress_appointment_status IN (1, 2) AND bookingpress_appointment_date <= %s",
				(int) $service_id,
				(string) $end_date
			),
			ARRAY_A
		);

		$has_overlap = false;

		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$booked_start = isset( $row['start_d'] ) ? (string) $row['start_d'] : '';
			if ( ! DayServiceHelper::is_valid_ymd( $booked_start ) ) {
				continue;
			}

			if ( DayServiceHelper::is_day_unit( isset( $row['duration_unit'] ) ? $row['duration_unit'] : '' ) ) {
				$booked_end = DayServiceHelper::inclusive_end_date( $booked_start, isset( $row['duration_val'] ) ? (int) $row['duration_val'] : 1 );
			} else {
				$booked_end = isset( $row['end_d'] ) && DayServiceHelper::is_valid_ymd( (string) $row['end_d'] )
					? (string) $row['end_d']
					: $booked_start;
			}

			if ( $start_date <= $booked_end && $end_date >= $booked_start ) {
				$has_overlap = true;
				break;
			}
		}

		$has_overlap = apply_filters(
			Hooks::FILTER_DAY_SERVICE_BOOKING_OVERLAP,
			(bool) $has_overlap,
			(int) $service_id,
			(string) $start_date,
			(string) $end_date,
			$context
		);

		return (bool) $has_overlap;
	}

	/**
	 * Look up the start/end times for a weekday from `default_workhours`.
	 *
	 * @param string $weekday Lowercase weekday name (e.g. `'monday'`).
	 *
	 * @return array{start:string,end:string}|null Null when closed.
	 */
	private function get_working_window( $weekday ) {
		global $wpdb;
		$table = $wpdb->prefix . 'bookingpress_default_workhours';
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT bookingpress_start_time, bookingpress_end_time FROM `{$table}` WHERE LOWER(bookingpress_workday_key) = %s AND bookingpress_is_break = 0 AND bookingpress_start_time IS NOT NULL ORDER BY bookingpress_workhours_id ASC LIMIT 1",
				(string) $weekday
			),
			ARRAY_A
		);
		if ( ! is_array( $row ) || empty( $row['bookingpress_start_time'] ) || empty( $row['bookingpress_end_time'] ) ) {
			return null;
		}

		/** Set 00:00:00 end time to 24:00:00 */
		if( '00:00:00' === $row['bookingpress_end_time'] ){
			$row['bookingpress_end_time'] = '24:00:00';
		}

		return array(
			'start' => (string) $row['bookingpress_start_time'],
			'end'   => (string) $row['bookingpress_end_time'],
		);
	}

	/**
	 * Read the general weekday BREAK gaps from `default_workhours`.
	 *
	 * The Vue3 analog of the legacy general break handling
	 * (`bookinpgress_retrieve_default_workhours` and the priority-13
	 * `bookingpress_get_pro_default_workhours`): the `is_break = 1` rows for the
	 * weekday carry break windows that remove the slots they overlap. Read from
	 * the SAME company-level table as {@see self::get_working_window} (the
	 * general working hours), so this is general/company shift data Lite owns. A
	 * Lite-only install with no break UI simply has no `is_break = 1` rows, so
	 * the result is empty and behaviour is unchanged.
	 *
	 * Seeded into the day schedule `breaks` key and consumed by
	 * {@see self::build_slots}, which drops any slot overlapping a break. A Pro
	 * shift override (per-service or general special day) REPLACES the whole
	 * schedule with its own breaks, so this seed only applies when the day
	 * inherits the general default.
	 *
	 * @param string $weekday Lowercase weekday name (e.g. `'monday'`).
	 *
	 * @return array<int, array{start:string,end:string}> Break gaps `{start, end}` (HH:MM:SS).
	 */
	private function get_working_breaks( $weekday ) {
		global $wpdb;
		$table = $wpdb->prefix . 'bookingpress_default_workhours';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT bookingpress_start_time, bookingpress_end_time FROM `{$table}` WHERE LOWER(bookingpress_workday_key) = %s AND bookingpress_is_break = 1 AND bookingpress_start_time IS NOT NULL",
				(string) $weekday
			),
			ARRAY_A
		);
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$breaks = array();
		foreach ( $rows as $row ) {
			if ( empty( $row['bookingpress_start_time'] ) || empty( $row['bookingpress_end_time'] ) ) {
				continue;
			}
			$breaks[] = array(
				'start' => (string) $row['bookingpress_start_time'],
				'end'   => (string) $row['bookingpress_end_time'],
			);
		}
		return $breaks;
	}

	/**
	 * Convert a service row's duration to minutes.
	 *
	 * @param array $service
	 *
	 * @return int
	 */
	private function service_duration_minutes( array $service ) {
		$val  = isset( $service['serviceDurationVal'] ) ? (int) $service['serviceDurationVal'] : 0;
		$unit = isset( $service['serviceDurationUnit'] ) ? (string) $service['serviceDurationUnit'] : 'm';
		switch ( $unit ) {
			case 'h':
				return $val * 60;
			case 'd':
				return 0;
			case 'm':
			default:
				return $val;
		}
	}

	/**
	 * Resolve a `HH:MM[:SS]` time-of-day on a date to an absolute timestamp,
	 * supporting EXTENDED hours (`>= 24:00:00`) that cross into the following
	 * day(s).
	 *
	 * `strtotime( "$date $time" )` parses `24:00:00` (next-day midnight) but
	 * returns FALSE for anything beyond it (e.g. `26:00:00`). The Pro shift layer
	 * stores an overnight working hour's end as an extended hour (start `20:00:00`,
	 * end `26:00:00` = "2 AM next day"), so the window must roll over. Computing
	 * `midnight + (h*3600 + m*60 + s)` does that for any `HH:MM:SS` and is
	 * byte-identical to `strtotime` for every value `<= 24:00:00` a Lite-only
	 * install produces, so Lite behaviour is unchanged.
	 *
	 * Falls back to `strtotime` for any non-`HH:MM:SS` shape.
	 *
	 * @param string $date `Y-m-d`.
	 * @param string $time `HH:MM` or `HH:MM:SS` (hours may exceed 23 for overnight).
	 *
	 * @return int|false Timestamp, or false when unparseable.
	 */
	private function resolve_window_ts( $date, $time ) {
		$midnight = strtotime( (string) $date . ' 00:00:00' );
		if ( false === $midnight ) {
			return false;
		}
		if ( preg_match( '/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', (string) $time, $m ) ) {
			$seconds = ( (int) $m[1] * 3600 ) + ( (int) $m[2] * 60 ) + ( isset( $m[3] ) ? (int) $m[3] : 0 );
			return $midnight + $seconds;
		}
		return strtotime( (string) $date . ' ' . (string) $time );
	}

	/**
	 * Generate slot rows for a single date.
	 *
	 * @param string $date
	 * @param array  $window     Working-hours window `{start, end}` (HH:MM:SS).
	 * @param int    $duration_min
	 * @param int    $service_id
	 * @param bool   $hide_booked
	 * @param int    $capacity   Per-slot booking capacity (resolved via
	 *                           Hooks::FILTER_SLOT_CAPACITY upstream; default 1).
	 * @param array  $breaks     Break gaps `[['start'=>'HH:MM:SS','end'=>'HH:MM:SS'], …]`
	 *                           within the window; any slot overlapping a break is
	 *                           dropped. Lite passes none; supplied by Pro shift
	 *                           management (service / special-day break rows).
	 * @param array  $context    Caller context (the REST request on the initial /
	 *                           month path), forwarded to Hooks::FILTER_BOOKED_RANGES
	 *                           so a consumer can resolve per-request booked-range
	 *                           rules (e.g. Pro's per-staff capacity counts only the
	 *                           selected staff's bookings). Empty on paths with no
	 *                           request context.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function build_slots( $date, array $window, $duration_min, $service_id, $hide_booked = false, $capacity = 1, array $breaks = array(), array $context = array() ) {
		$rows = array();

		// Resolve the window start/end to absolute timestamps. We do NOT use
		// `strtotime( "$date $time" )` directly because PHP returns FALSE for an
		// EXTENDED hour like `26:00:00` (an overnight shift's "2 AM next day"),
		// which the Pro shift layer emits and relies on rolling over. Parsing the
		// time as seconds-from-midnight and adding it to the date's midnight
		// handles `00:00:00`..`47:59:59` uniformly, and is byte-identical to the
		// old strtotime result for every non-extended value (`≤ 24:00:00`) a
		// Lite-only install ever produces.
		$start_ts = $this->resolve_window_ts( $date, $window['start'] );
		$end_ts   = $this->resolve_window_ts( $date, $window['end'] );


		if ( false === $start_ts || false === $end_ts || $end_ts <= $start_ts ) {
			return $rows;
		}

		// Current server time, in the same reference frame as the slot
		// timestamps above. `current_time('timestamp')` returns the
		// WordPress-local time treated as if UTC, which matches
		// `strtotime( $date . ' HH:MM' )` since PHP's default timezone
		// inside WP is UTC. Used below to skip slots whose start has
		// already passed — those are not bookable.
		$now_ts = (int) current_time( 'timestamp' );

		// Issue 4.1 — when "Show time as per service duration" is disabled,
		// advance slot starts by the admin-configured "Default time slot step"
		// instead of by the service duration. The slot DURATION still comes
		// from $duration_min (the service's own duration); only the START
		// interval changes. So for a 60-min service and 15-min step:
		//   slots are 9:00–10:00, 9:15–10:15, … 4:00–5:00 (last start =
		//   end − duration, enforced by the loop bound below).
		//
		// Settings-key parity with legacy (class.bookingpress.php
		// §bookingpress_get_calendar_data L6765-6768, L7003-7006, L7322-7325):
		// the value the admin changes via the "Default time slot step" select
		// in the General Settings tab is stored under `default_time_slot` —
		// NOT `default_time_slot_step`. (`default_time_slot_step` is bound
		// to the *separate* "Default service duration" select in the same
		// tab.) See general_setting_tab.php L33-34 vs L47-48.
		$show_as_duration = in_array(
			(string) $this->settings->get( 'show_time_as_per_service_duration', SettingsRepository::GROUP_GENERAL, '1' ),
			array( '1', 'true' ),
			true
		);
		$step_min = $show_as_duration
			? $duration_min
			: max( 1, (int) $this->settings->get( 'default_time_slot', SettingsRepository::GROUP_GENERAL, '30' ) );

		// Issue 4.2 — when "Share timeslot between all services" is enabled,
		// count booked slots across every service, not just this one.
		$share_timeslots = in_array(
			(string) $this->settings->get( 'share_timeslot_between_services', SettingsRepository::GROUP_GENERAL, '0' ),
			array( '1', 'true' ),
			true
		);

		// "Share Capacity between timeslots" policy. Only relevant when slots
		// overlap (i.e. "Show time as per service duration" is off, so $step_min
		// is the smaller "Default time slot step" — see above). When ON (the
		// Lite default) a booking reduces the remaining capacity of EVERY slot
		// whose window overlaps it, crossed neighbours included — the current
		// Lite behaviour, matching the legacy Lite fallback of 'true'. When a
		// consumer turns it OFF, only the slot whose window EXACTLY matches a
		// booking is reduced; the crossed neighbours are dropped from the grid
		// instead, because they would physically conflict with the booking.
		// Inert in Lite (no callback → stays true).
		$share_capacity = (bool) apply_filters( Hooks::FILTER_SHARE_CAPACITY, true, (int) $service_id, (string) $date );

		// Booked time RANGES on this date. Issue 4.2 — after Issue 4.1
		// generated slots no longer align with booking start times (a 60-min
		// slot starting at 08:00 may overlap a 30-min booking that starts at
		// 08:15), so exact start-time matching misses overlaps. We instead
		// fetch (start_ts, end_ts) for every blocking booking and apply
		// `slot_start < booked_end && slot_end > booked_start` per-slot.
		$booked_ranges = $this->get_booked_ranges( $service_id, $date, $share_timeslots );

		/**
		 * Reshape the booked ranges that block slots on this date before the
		 * per-slot overlap loop runs. Lets a consumer extend/pad how a booking
		 * blocks neighbouring slots (e.g. Pro's "Buffer Time" extends each range
		 * by the BOOKED service's before+after buffer). Capacity math is
		 * unaffected — the loop still sums each overlapping range's `count`.
		 * Inert in Lite (no callback).
		 *
		 * @param array  $booked_ranges Each `['start_ts','end_ts','count','service_id']`.
		 * @param int    $service_id
		 * @param string $date
		 * @param array  $context       Caller context (the REST request). Empty on
		 *                              paths with no request context. Lets a consumer
		 *                              resolve per-request rules — e.g. Pro's per-staff
		 *                              capacity replaces the ranges with only the
		 *                              selected staff's bookings.
		 */
		$booked_ranges = apply_filters( Hooks::FILTER_BOOKED_RANGES, $booked_ranges, (int) $service_id, (string) $date, $context );
		if ( ! is_array( $booked_ranges ) ) {
			$booked_ranges = array();
		}

		// Per-slot capacity is supplied by the caller (default 1). Pro injects
		// the service's configured `max_capacity` via Hooks::FILTER_SLOT_CAPACITY.
		$capacity = max( 1, (int) $capacity );

		// Normalise break gaps to timestamps once. A slot whose [start, end)
		// overlaps any break is dropped (legacy parity: a break removes the
		// slots it covers). Lite passes none; Pro shift management supplies the
		// service / special-day break rows via Hooks::FILTER_DAY_SCHEDULE.
		$break_ranges = array();
		foreach ( $breaks as $br ) {
			if ( ! is_array( $br ) || empty( $br['start'] ) || empty( $br['end'] ) ) {
				continue;
			}
			$bs = $this->resolve_window_ts( $date, $br['start'] );
			$be = $this->resolve_window_ts( $date, $br['end'] );
			if ( false === $bs || false === $be || $be <= $bs ) {
				continue;
			}
			$break_ranges[] = array( 'start' => (int) $bs, 'end' => (int) $be );
		}

		for ( $ts = $start_ts; $ts + $duration_min * 60 <= $end_ts; $ts += $step_min * 60 ) {
			// Released-form parity: drop past slots. For future dates
			// `$ts` is always > now so this branch never triggers; for
			// today it skips every slot whose start moment has already
			// passed in WP-local time.
			if ( $ts <= $now_ts ) {
				continue;
			}

			$slot_start  = gmdate( 'H:i', $ts );
			$slot_end_ts = $ts + $duration_min * 60;
			$slot_end    = gmdate( 'H:i', $slot_end_ts );
			//$is_overnight = ( gmdate( 'Y-m-d', $slot_end_ts ) !== gmdate( 'Y-m-d', $ts ) ) ? 1 : 0;
			$is_overnight = 0; // overnight slots are not supported in Lite, so hardcode to 0.

			// Drop slots that fall inside a break gap.
			if ( ! empty( $break_ranges ) ) {
				$in_break = false;
				foreach ( $break_ranges as $brk ) {
					if ( $ts < $brk['end'] && $slot_end_ts > $brk['start'] ) {
						$in_break = true;
						break;
					}
				}
				if ( $in_break ) {
					continue;
				}
			}

			$booked_here  = 0;
			$drop_crossed = false;
			foreach ( $booked_ranges as $br ) {
				if ( $ts < $br['end_ts'] && $slot_end_ts > $br['start_ts'] ) {
					if ( $share_capacity
						|| ( $ts === (int) $br['start_ts'] && $slot_end_ts === (int) $br['end_ts'] )
					) {
						// Capacity is shared across crossed slots (ON), OR this
						// slot's window EXACTLY matches the booking — either way
						// the booking counts against this slot's capacity.
						$booked_here += $br['count'];
					} else {
						// Share Capacity is OFF and the slot only CROSSES the
						// booking (windows don't match): the slot would
						// physically conflict, so it is not bookable. Drop it
						// from the grid (legacy `unset()` parity). A drop wins
						// over any exact match on the same slot, so stop scanning.
						$drop_crossed = true;
						break;
					}
				}
			}
			if ( $drop_crossed ) {
				$booked_here = $capacity; // Instead of completely skipping and hiding overlapping slots when capacity sharing is off, this marks the slot as fully booked
			}
			$remaining_capacity = max( 0, $capacity - $booked_here );

			if ( $hide_booked && $remaining_capacity <= 0 ) {
				continue;
			}

			$rows[] = array(
				'start_time'           => $slot_start,
				'end_time'             => $slot_end,
				'formatted_start_time' => $this->dates->format_time( $slot_start ),
				'formatted_end_time'   => $this->dates->format_time( $slot_end ),
				'is_available'         => $remaining_capacity > 0 ? 1 : 0,
				'remaining_capacity'   => $remaining_capacity,
				'is_overnight'         => $is_overnight,
			);
		}

		return $rows;
	}

	/**
	 * Return booked time ranges for a date as `[{start_ts, end_ts, count}, …]`.
	 *
	 * Identical booking ranges are aggregated so multi-capacity services
	 * subtract correctly. When `$share_timeslots` is true the service filter
	 * is dropped so any booking on the date blocks overlapping slots regardless
	 * of which service was booked.
	 *
	 * A stored `bookingpress_appointment_end_time` of `'00:00:00'` is treated
	 * as end-of-day (24:00) to match the legacy mapping in
	 * `bookingpress_retrieve_timeslots()` (class.bookingpress_appointment_bookings.php
	 * §4752-4754).
	 *
	 * @param int    $service_id
	 * @param string $date
	 * @param bool   $share_timeslots
	 *
	 * @return array<int, array{start_ts:int, end_ts:int, count:int, service_id:int}>
	 */
	private function get_booked_ranges( $service_id, $date, $share_timeslots = false ) {
		global $wpdb;
		$table = $wpdb->prefix . 'bookingpress_appointment_bookings';

		if ( $share_timeslots ) {
			// `bookingpress_service_id` is selected + grouped so each range
			// retains the service the booking belongs to — a consumer (Pro's
			// Buffer Time) needs the BOOKED service to apply its own buffer when
			// timeslots are shared. Splitting an aggregate by service is neutral
			// for capacity: the overlap loop sums each range's `count`.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT bookingpress_appointment_time AS start_t, bookingpress_appointment_end_time AS end_t, bookingpress_appointment_end_date AS end_d, bookingpress_service_id AS svc, COUNT(*) AS c FROM `{$table}` WHERE bookingpress_appointment_date = %s AND bookingpress_appointment_status IN (1, 2) GROUP BY bookingpress_appointment_time, bookingpress_appointment_end_time, bookingpress_appointment_end_date, bookingpress_service_id",
					(string) $date
				),
				ARRAY_A
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT bookingpress_appointment_time AS start_t, bookingpress_appointment_end_time AS end_t, bookingpress_appointment_end_date AS end_d, bookingpress_service_id AS svc, COUNT(*) AS c FROM `{$table}` WHERE bookingpress_service_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_status IN (1, 2) GROUP BY bookingpress_appointment_time, bookingpress_appointment_end_time, bookingpress_appointment_end_date, bookingpress_service_id",
					(int) $service_id,
					(string) $date
				),
				ARRAY_A
			);
		}

		$out = array();
		if ( ! is_array( $rows ) ) {
			return $out;
		}

		foreach ( $rows as $r ) {
			$start_t = (string) $r['start_t'];
			$end_t   = (string) $r['end_t'];
			$end_d   = isset( $r['end_d'] ) ? (string) $r['end_d'] : '';

			$start_ts = strtotime( $date . ' ' . $start_t );
			if ( false === $start_ts ) {
				continue;
			}

			$end_date = ( '' === $end_d || '0000-00-00' === $end_d ) ? (string) $date : $end_d;
			if ( '' === $end_t || '00:00:00' === $end_t ) {
				if ( $end_date > (string) $date ) {
					// OVERNIGHT booking: the row already carries an explicit
					// next-day `end_date`, so a stored `00:00:00` end time is
					// literally midnight at the START of that day — NOT the
					// end-of-day boundary. Applying the end-of-day fudge below
					// would push the range a full extra day forward and wrongly
					// block every after-midnight slot of the overnight grid
					// (e.g. a 23:30→00:00 booking would span into the next-next
					// day). Anchor to the real midnight instead.
					$end_ts = strtotime( $end_date . ' 00:00:00' );
				} else {
					// Same-day booking: treat midnight end as end-of-day boundary
					// (24:00 the next second) — legacy parity for full-day /
					// day-bounded bookings.
					$end_ts = strtotime( $end_date . ' 23:59:59' ) + 1;
				}
			} else {
				$end_ts = strtotime( $end_date . ' ' . $end_t );
			}
			if ( false === $end_ts || $end_ts <= $start_ts ) {
				continue;
			}

			$out[] = array(
				'start_ts'   => (int) $start_ts,
				'end_ts'     => (int) $end_ts,
				'count'      => (int) $r['c'],
				'service_id' => isset( $r['svc'] ) ? (int) $r['svc'] : (int) $service_id,
			);
		}
		return $out;
	}
}

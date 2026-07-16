<?php
/**
 * TimeslotService — orchestrates AvailabilityService into the wire payload.
 *
 * Per §M0.4 / §M0.5 / §M0.6. Cached under
 * `bp_v3_timeslots_<token>_<date>_v1` per the M1.0 namespace lock.
 *
 * Also exposes a pure bucketing helper for the morning/afternoon/evening/
 * night classification used by the M6 component layer (the pure tests
 * exercise `bucket_pure()`).
 *
 * @package BookingPress\Vue3\Services
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Cache\FrontendFormCache;
use BookingPress\Vue3\Contracts\AvailabilityServiceInterface;
use BookingPress\Vue3\Contracts\TimeslotServiceInterface;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\ServiceRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TimeslotService implements TimeslotServiceInterface {

	/** Default bucket boundaries (24h) — used when settings are blank. */
	const DEFAULT_AFTERNOON = 12;
	const DEFAULT_EVENING   = 17;
	const DEFAULT_NIGHT     = 21;

	/** @var AvailabilityServiceInterface */
	private $availability;
	/** @var ServiceRepository */
	private $services;
	/** @var SettingsRepository */
	private $settings;

	public function __construct(
		?AvailabilityServiceInterface $availability = null,
		?ServiceRepository $services = null,
		?SettingsRepository $settings = null
	) {
		$this->availability = $availability ?: ServiceLocator::get( AvailabilityServiceInterface::class );
		$this->services     = $services ?: new ServiceRepository();
		$this->settings     = $settings ?: new SettingsRepository();
	}

	/**
	 * @inheritDoc
	 *
	 * Released-form parity: this endpoint is the FIRST call the date/time
	 * step makes after a service is picked. Behaviour:
	 *
	 *   1. Take today's server date as the starting point.
	 *   2. Walk forward day-by-day inside the current month, collecting
	 *      every date that has at least one available slot.
	 *   3. If the current month produces ZERO available dates, advance
	 *      to the first day of the next month and repeat — keep going
	 *      until a month with availability is found OR the 365-day
	 *      booking window ends.
	 *   4. Return that month's `working_details`, the per-instance
	 *      blocked-dates list for that range, a `next_month_date`
	 *      cursor pointing at the first day of the FOLLOWING month
	 *      (for the client's progressive walker), and the absolute
	 *      `max_available_date` (today + 365 days).
	 *
	 * The client uses `found_month` to know which YYYY-MM key to cache
	 * the payload under.
	 */
	public function get_initial_payload( array $request ) {
		/**
		 * Let consumers re-derive request flags knowable server-side — the first
		 * grid request of a page can fire before the client-side request filters
		 * register. {@see Hooks::FILTER_TIMESLOT_REQUEST_CONTEXT}
		 */

		$request = (array) apply_filters( Hooks::FILTER_TIMESLOT_REQUEST_CONTEXT, $request );
		

		$service_id = isset( $request['service_id'] ) ? (int) $request['service_id'] : 0;
		$start_date = isset( $request['selected_date'] ) ? (string) $request['selected_date'] : current_time( 'Y-m-d' );

		if ( $service_id <= 0 ) {
			throw new \BookingPress\Vue3\Exceptions\UnknownServiceException( 'Service id missing.' );
		}

		$token    = $this->make_token( $request );
		$today    = current_time( 'Y-m-d' );

		/**
		 * Maximum bookable date — both the month-walker stop and the payload's
		 * `max_available_date` (which the client syncs into `config.maxDate`).
		 * Lite default is today + 1 year; Pro's "Period available for booking in
		 * advance" replaces it with today + the configured period. Inert in Lite
		 * (no callback). A per-service expiration date narrows this further via
		 * FILTER_TIMESLOT_PAYLOAD below.
		 *
		 * @see Hooks::FILTER_MAX_BOOKING_DATE
		 */
		$max_date = (string) apply_filters(
			Hooks::FILTER_MAX_BOOKING_DATE,
			gmdate( 'Y-m-d', strtotime( $today . ' +1 year' ) ),
			$today,
			$request
		);

		// Cache lookup keyed by start-date + token. TTL=0 by default so
		// admin workhour / day-off edits reflect on the next render. When
		// TTL is raised via the `bookingpress_form_v3_cache_ttl` filter,
		// any admin save that bumps FrontendFormCache::get_version()
		// orphans this key automatically (no scan-delete required).
		$cache_key = FrontendFormCache::key( 'timeslots', $token . '_initial_' . $start_date );
		$ttl       = (int) apply_filters( Hooks::FILTER_CACHE_TTL, 0 );
		$cached    = ( $ttl > 0 ) ? get_transient( $cache_key ) : false;
		if ( false !== $cached ) {
			return $cached;
		}

		$found_month     = null;
		$working_details = array();
		$blocked         = array();

		$start_ts     = strtotime( $start_date );
		$cursor_year  = (int) gmdate( 'Y', $start_ts );
		$cursor_month = (int) gmdate( 'm', $start_ts );

		// Hard cap on the walk so a misconfigured service can never spin
		// the request indefinitely. 12 months is well past the 365-day
		// booking window and matches the legacy walker's safety cap.
		for ( $i = 0; $i < 12; $i++ ) {
			$first_day   = sprintf( '%04d-%02d-01', $cursor_year, $cursor_month );
			$last_day_ts = strtotime( $first_day . ' +1 month -1 day' );
			$last_day    = gmdate( 'Y-m-d', $last_day_ts );

			// On the starting month, begin from `start_date` (today). On
			// subsequent months, begin from the first of that month.
			$iter_start = ( 0 === $i ) ? $start_date : $first_day;
			if ( $iter_start > $last_day ) {
				break;
			}

			$month_blocked     = $this->availability->get_disabled_dates(
				$service_id,
				array( 'from_date' => $iter_start, 'to_date' => $last_day )
			);
			$month_blocked_set = array_flip( $month_blocked );

			$month_details = array();
			$reached_max   = false;
			for ( $ts = strtotime( $iter_start ); $ts <= $last_day_ts; $ts += DAY_IN_SECONDS ) {
				$d = gmdate( 'Y-m-d', $ts );
				if ( $d > $max_date ) {
					// Stop the DAY walk only. Breaking the outer MONTH loop
					// here (the old `break 2`) leapt past the commit below and
					// discarded every date collected so far — the cause of a
					// blank calendar whenever `$max_date` (e.g. a short "Period
					// available for booking") falls mid-month.
					$reached_max = true;
					break;
				}
				if ( isset( $month_blocked_set[ $d ] ) ) {
					continue;
				}
				try {
					$rows = $this->availability->get_timings_for_date( $service_id, $d, $request );
				} catch ( \Throwable $e ) {
					$rows = array();
				}

				if ( ! empty( $rows ) ) {
					$month_details[ $d ] = $rows;
				}
			}
			if ( ! empty( $month_details ) ) {
				$working_details = $month_details;
				$blocked         = $month_blocked;
				$found_month     = sprintf( '%04d-%02d', $cursor_year, $cursor_month );
				break;
			}
			if ( $reached_max ) {
				// Cap reached with no availability in range — nothing further
				// to walk.
				break;
			}

			// No availability this month — advance.
			$cursor_month++;
			if ( $cursor_month > 12 ) {
				$cursor_month = 1;
				$cursor_year++;
			}
		}

		// Cursor for the client's progressive walker.
		$next_month_date = null;
		if ( null !== $found_month ) {
			list( $fy, $fm ) = explode( '-', $found_month );
			$next_month_ts   = strtotime( sprintf( '%04d-%02d-01 +1 month', (int) $fy, (int) $fm ) );
			$candidate       = gmdate( 'Y-m-d', $next_month_ts );
			$next_month_date = ( $candidate <= $max_date ) ? $candidate : null;
		}

		$payload = array(
			'working_details'            => $working_details,
			'vcal_attributes'            => array(),
			'vcal_capacity_attrs'        => array(),
			'is_daysoff'                 => array(),
			'v_calendar_blocked_dates'   => $blocked,
			'next_month_date'            => $next_month_date,
			'max_available_date'         => $max_date,
			'found_month'                => $found_month,
			'timing_token_data'          => $token,
			'consider_overnight_booking' => $this->has_overnight_in( $working_details ) ? 1 : 0,
		);

		/**
		 * Reshape the full timeslot endpoint payload.
		 *
		 * @param array $payload
		 * @param array $request
		 */
		$payload = apply_filters( Hooks::FILTER_TIMESLOT_PAYLOAD, $payload, $request );

		if ( $ttl > 0 ) {
			set_transient( $cache_key, $payload, $ttl );
		}
		return $payload;
	}

	/**
	 * Whether any row across the keyed working_details map is overnight.
	 *
	 * @param array $details Map of `YYYY-MM-DD => array<slot>`.
	 *
	 * @return bool
	 */
	private function has_overnight_in( array $details ) {
		foreach ( $details as $rows ) {
			if ( ! is_array( $rows ) ) {
				continue;
			}
			if ( $this->has_overnight( $rows ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_month_payload( array $request ) {
		// Same server-side request derivation as get_initial_payload().
		$request = (array) apply_filters( Hooks::FILTER_TIMESLOT_REQUEST_CONTEXT, $request );

		$token  = isset( $request['working_hour_id'] ) ? (string) $request['working_hour_id'] : '';
		$from   = isset( $request['from_date'] ) ? (string) $request['from_date'] : current_time( 'Y-m-d' );
		$svc_id = isset( $request['service_id'] ) ? (int) $request['service_id'] : 0;

		$year  = isset( $request['selected_year'] ) ? (int) $request['selected_year'] : (int) gmdate( 'Y', strtotime( $from ) );
		$month = isset( $request['selected_month'] ) ? (int) $request['selected_month'] : (int) gmdate( 'm', strtotime( $from ) );

		// Range = first day of month → last day of month.
		$first = sprintf( '%04d-%02d-01', $year, $month );
		$last_ts = strtotime( $first . ' +1 month -1 day' );
		$last  = gmdate( 'Y-m-d', $last_ts );

		$cache_key = FrontendFormCache::key( 'timeslots', sprintf( '%s_month_%04d-%02d', $token, $year, $month ) );
		// Default TTL is 0 (no cache) so admin workhour / day-off / capacity
		// edits reflect on the next render. Hosts can raise this via the
		// `bookingpress_form_v3_cache_ttl` filter for high-traffic sites; any
		// admin save that bumps FrontendFormCache::get_version() orphans
		// these rows automatically.
		$ttl    = (int) apply_filters( Hooks::FILTER_CACHE_TTL, 0 );
		$cached = ( $ttl > 0 ) ? get_transient( $cache_key ) : false;
		if ( false !== $cached ) {
			return $cached;
		}

		$working_details = array();
		$blocked         = $this->availability->get_disabled_dates(
			$svc_id,
			array( 'from_date' => $first, 'to_date' => $last )
		);
		$blocked_set     = array_flip( $blocked );

		for ( $ts = strtotime( $first ); $ts <= $last_ts; $ts += DAY_IN_SECONDS ) {
			$d = gmdate( 'Y-m-d', $ts );
			if ( isset( $blocked_set[ $d ] ) ) {
				continue;
			}
			try {
				// Pass the full request as context (same as the initial-payload
				// path) so per-request availability consumers work on month
				// navigation too — e.g. Pro's per-staff capacity reads the
				// selected staff from the request. Lite consumers key off the
				// explicit $service_id arg, so this is inert for a Lite-only install.
				$rows = $this->availability->get_timings_for_date( $svc_id, $d, $request );
			} catch ( \Throwable $e ) {
				$rows = array();
			}
			if ( ! empty( $rows ) ) {
				$working_details[ $d ] = $rows;
			}
		}

		$payload = array(
			'working_details'            => $working_details,
			'vcal_attributes'            => array(),
			'vcal_capacity_attrs'        => array(),
			'is_daysoff'                 => array(),
			'v_calendar_blocked_dates'   => $blocked,
			'timing_token_data'          => $token,
			'consider_overnight_booking' => 0,
		);

		$payload = apply_filters( Hooks::FILTER_TIMESLOT_PAYLOAD, $payload, $request );
		if ( $ttl > 0 ) {
			set_transient( $cache_key, $payload, $ttl );
		}
		return $payload;
	}

	/**
	 * Build the opaque per-render token for cache keys.
	 *
	 * @param array $request
	 *
	 * @return string 16-char hex.
	 */
	private function make_token( array $request ) {
		$key = sprintf(
			'%s|%s|%s|%s|%s',
			isset( $request['service_id'] ) ? (string) $request['service_id'] : '',
			isset( $request['category_id'] ) ? (string) $request['category_id'] : '',
			isset( $request['selected_staff'] ) ? (string) $request['selected_staff'] : '',
			isset( $request['selected_location'] ) ? (string) $request['selected_location'] : '',
			// Client timezone (when a consumer converts the payload into the
			// visitor's timezone) so two visitors in different zones don't share
			// a cached payload when the TTL is raised above 0. Empty in Lite, so
			// the token is byte-identical to before for a Lite-only install.
			isset( $request['client_timezone'] ) ? (string) $request['client_timezone'] : ''
		);
		return substr( md5( $key ), 0, 16 );
	}

	/**
	 * Whether any row is overnight.
	 *
	 * @param array $rows
	 *
	 * @return bool
	 */
	private function has_overnight( array $rows ) {
		foreach ( $rows as $r ) {
			if ( ! empty( $r['is_overnight'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Pure bucketing helper — classify a row into morning/afternoon/evening/night.
	 *
	 * - Overnight rows always land in `night`.
	 * - Otherwise, the start-hour decides:
	 *     hour < afternoon_start → morning
	 *     hour < evening_start   → afternoon
	 *     hour < night_start     → evening
	 *     else                   → night
	 *
	 * @param array $row Slot row (must have `start_time` and `is_overnight`).
	 * @param int   $afternoon_start Hour (0-23). Default 12.
	 * @param int   $evening_start   Hour (0-23). Default 17.
	 * @param int   $night_start     Hour (0-23). Default 21.
	 *
	 * @return string One of `'morning' | 'afternoon' | 'evening' | 'night'`.
	 */
	public static function bucket_pure( array $row, $afternoon_start = self::DEFAULT_AFTERNOON, $evening_start = self::DEFAULT_EVENING, $night_start = self::DEFAULT_NIGHT ) {
		if ( ! empty( $row['is_overnight'] ) ) {
			return 'night';
		}
		$start = isset( $row['start_time'] ) ? (string) $row['start_time'] : '00:00';
		$hour  = (int) substr( $start, 0, 2 );

		if ( $hour < (int) $afternoon_start ) {
			return 'morning';
		}
		if ( $hour < (int) $evening_start ) {
			return 'afternoon';
		}
		if ( $hour < (int) $night_start ) {
			return 'evening';
		}
		return 'night';
	}
}

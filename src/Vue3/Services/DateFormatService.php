<?php
/**
 * DateFormatService — site-tz date and time formatting.
 *
 * Reads admin settings (`bpa_front_date_fmt`, `bookingpress_time_format`) and
 * applies them. Used by `TimeslotService` for `formatted_start_time` /
 * `formatted_end_time`, and by the M6 SummaryStep for display labels.
 *
 * Concrete-only — not a Pro override point.
 *
 * @package BookingPress\Vue3\Services
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.4, §M0.6
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DateFormatService {

	/** Default if the customize value is unset. */
	const DEFAULT_DATE_FORMAT = 'F j, Y';

	/** @var SettingsRepository */
	private $settings;

	/** @var CustomizeRepository */
	private $customize;

	/** @var string|null Cached date format. */
	private $date_format_cache = null;

	/** @var string|null Cached time format ('12'|'24'). */
	private $time_format_cache = null;

	/** @var string|null Cached PHP date-format string for time display. */
	private $php_time_format_cache = null;

	public function __construct( ?SettingsRepository $settings = null, ?CustomizeRepository $customize = null ) {
		$this->settings  = $settings ?: new SettingsRepository();
		$this->customize = $customize ?: new CustomizeRepository();
	}

	/**
	 * The admin-configured date format string (PHP date() format).
	 *
	 * Reads `default_date_format` from the `general_setting` group — the key
	 * the admin "Date Format" dropdown (General Settings tab) actually saves,
	 * with options `F j, Y`, `Y-m-d`, `m/d/Y`, `d/m/Y`, `d.m.Y`, `d-m-Y`.
	 * Mirrors the released form, which reads the same key:
	 * `bookingpress_get_settings( 'default_date_format', 'general_setting' )`
	 * (class.bookingpress_appointment_bookings.php:5484). Falls back to
	 * `DEFAULT_DATE_FORMAT`, which matches the stored settings default.
	 *
	 * (Previously this read `bpa_front_date_fmt` from the booking_form
	 * customize group — an unused/legacy key that is always empty, so the
	 * format silently collapsed to the `F j, Y` default regardless of the
	 * admin setting.)
	 *
	 * @return string
	 */
	public function date_format() {
		if ( null === $this->date_format_cache ) {
			$value = (string) $this->settings->get( 'default_date_format', SettingsRepository::GROUP_GENERAL, '' );
			$this->date_format_cache = '' === $value ? self::DEFAULT_DATE_FORMAT : $value;
		}
		return $this->date_format_cache;
	}

	/**
	 * `'12'` or `'24'` per admin setting.
	 *
	 * Reads `default_time_format` from the general settings group.
	 * Stored values: `'H:i'` (24-hour), `'g:i a'` / `'g:i A'` (12-hour),
	 * or `'bookingpress-wp-inherit-time-format'` (inherit from WordPress).
	 *
	 * @return string `'12'` or `'24'`
	 */
	public function time_format() {
		if ( null === $this->time_format_cache ) {
			$this->time_format_cache = ( 'H:i' === $this->php_time_format() ) ? '24' : '12';
		}
		return $this->time_format_cache;
	}

	/**
	 * Resolved PHP date-format string for time display.
	 *
	 * @return string A PHP date() format string, e.g. `'H:i'` or `'g:i A'`.
	 */
	public function php_time_format() {
		if ( null === $this->php_time_format_cache ) {
			$value = (string) $this->settings->get( 'default_time_format', SettingsRepository::GROUP_GENERAL, 'g:i a' );
			if ( 'bookingpress-wp-inherit-time-format' === $value ) {
				$value = (string) get_option( 'time_format', 'g:i a' );
			}
			// Normalise: treat blank/unknown values as 12-hour.
			$this->php_time_format_cache = ( '' !== $value ) ? $value : 'g:i a';
		}
		return $this->php_time_format_cache;
	}

	/**
	 * Format a `HH:MM` time string for display.
	 *
	 * @param string $hhmm `HH:MM` or `HH:MM:SS`.
	 *
	 * @return string Formatted time per admin setting.
	 */
	public function format_time( $hhmm ) {
		$ts = strtotime( '1970-01-01 ' . (string) $hhmm );
		if ( false === $ts ) {
			return (string) $hhmm;
		}
		return gmdate( $this->php_time_format(), $ts );
	}

	/**
	 * Format a `YYYY-MM-DD` date for display per the admin date format.
	 *
	 * @param string $ymd
	 *
	 * @return string
	 */
	public function format_date( $ymd ) {
		$ts = strtotime( (string) $ymd );
		if ( false === $ts ) {
			return (string) $ymd;
		}
		return gmdate( $this->date_format(), $ts );
	}
}

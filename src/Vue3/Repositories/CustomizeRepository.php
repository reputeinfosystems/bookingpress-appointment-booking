<?php
/**
 * CustomizeRepository — reads `bookingpress_customize_settings`.
 *
 * Per `BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md` §6 option 2: shared
 * storage with the legacy admin Customize panel. This repository is
 * read-only; the legacy admin panel keeps writing to the same table.
 *
 * Schema:
 *   bookingpress_setting_id, bookingpress_setting_name,
 *   bookingpress_setting_value, bookingpress_setting_type,
 *   bookingpress_created_at
 *
 * @package BookingPress\Vue3\Repositories
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §6
 */

namespace BookingPress\Vue3\Repositories;

use BookingPress\Vue3\Cache\FrontendFormCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomizeRepository extends BaseRepository {

	/**
	 * Customize-setting type groups. Each represents one tab of the legacy
	 * Customize admin panel.
	 */
	const GROUP_BOOKING_FORM      = 'booking_form';
	const GROUP_BOOKING_MY_BOOKING = 'booking_my_booking';
	const GROUP_BOOKING_NOTIFICATION = 'booking_notification';

	/**
	 * @inheritDoc
	 */
	protected function table_suffix() {
		return 'bookingpress_customize_settings';
	}

	/**
	 * Cache namespace override (the long table suffix is unwieldy in keys).
	 *
	 * @return string
	 */
	protected function cache_namespace() {
		return 'customize';
	}

	/**
	 * Read one customize setting.
	 *
	 * @param string $name    Setting name.
	 * @param string $group   Setting type group (e.g. `'booking_form'`).
	 * @param mixed  $default Value on cache+DB miss.
	 *
	 * @return mixed
	 */
	public function get( $name, $group, $default = '' ) {
		$name  = (string) $name;
		$group = (string) $group;
		if ( '' === $name || '' === $group ) {
			return $default;
		}

		$value = $this->remember(
			'kv_' . md5( $group . '|' . $name ),
			function () use ( $name, $group ) {
				global $wpdb;
				$table = $this->table();
				return $wpdb->get_var(
					$wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						"SELECT bookingpress_setting_value FROM `{$table}` WHERE bookingpress_setting_name = %s AND bookingpress_setting_type = %s ORDER BY bookingpress_setting_id ASC LIMIT 1",
						$name,
						$group
					)
				);
			}
		);

		return ( null === $value ) ? $default : $value;
	}

	/**
	 * Read every setting in a group as a name → value map.
	 *
	 * @param string $group
	 *
	 * @return array<string, string>
	 */
	public function get_group( $group ) {
		$group = (string) $group;
		if ( '' === $group ) {
			return array();
		}

		return $this->remember( 'group_' . md5( $group ), function () use ( $group ) {
			global $wpdb;
			$table = $this->table();
			$rows  = $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT bookingpress_setting_name AS name, bookingpress_setting_value AS value FROM `{$table}` WHERE bookingpress_setting_type = %s",
					$group
				),
				ARRAY_A
			);

			$out = array();
			if ( is_array( $rows ) ) {
				foreach ( $rows as $r ) {
					$out[ (string) $r['name'] ] = (string) ( null === $r['value'] ? '' : $r['value'] );
				}
			}
			return $out;
		} );
	}

	/**
	 * Convenience: read multiple keys from one group.
	 *
	 * @param string             $group
	 * @param array<int, string> $names
	 *
	 * @return array<string, string>
	 */
	public function get_many( $group, array $names ) {
		$all = $this->get_group( $group );
		$out = array();
		foreach ( $names as $n ) {
			$out[ $n ] = isset( $all[ $n ] ) ? $all[ $n ] : '';
		}
		return $out;
	}

	/**
	 * Invalidate the cached customize reads for a given group.
	 *
	 * Under the FrontendFormCache version-key model a single bump
	 * invalidates ALL cached namespaces in one move, so this method
	 * simply bumps the global version. There is no longer a meaningful
	 * difference between "invalidate this group" and "invalidate
	 * everything"; the orphaning is atomic and cheap.
	 *
	 * Retained for API compatibility with any external caller (Pro,
	 * extensions) that still calls `invalidate_group()` directly.
	 *
	 * @param string $group
	 *
	 * @return void
	 */
	public function invalidate_group( $group ) {
		$group = (string) $group;
		if ( '' === $group ) {
			return;
		}
		FrontendFormCache::bump( 'customize_invalidate_group:' . $group );
	}
}

<?php
/**
 * SettingsRepository — reads `bookingpress_settings`.
 *
 * Note: this table uses bare column names (`setting_id`, `setting_name`,
 * `setting_value`, `setting_type`) — NOT the `bookingpress_*` prefix used
 * by every other BookingPress table. Per the activation SQL.
 *
 * @package BookingPress\Vue3\Repositories
 */

namespace BookingPress\Vue3\Repositories;

use BookingPress\Vue3\Cache\FrontendFormCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsRepository extends BaseRepository {

	/**
	 * Known setting-type groups, used to bound `get_group()`.
	 *
	 * Lite uses: `general_setting`, `payment_setting`, `customer_setting`,
	 * `message_setting`. Pro adds more (e.g. `staff_setting`).
	 */
	const GROUP_GENERAL  = 'general_setting';
	const GROUP_PAYMENT  = 'payment_setting';
	const GROUP_CUSTOMER = 'customer_setting';
	const GROUP_MESSAGE  = 'message_setting';
	const GROUP_INVOICE  = 'invoice_setting';

	/**
	 * @inheritDoc
	 */
	protected function table_suffix() {
		return 'bookingpress_settings';
	}

	/**
	 * Return a single setting value, or the supplied default.
	 *
	 * @param string $name    Setting name.
	 * @param string $group   Setting type group (e.g. `'payment_setting'`).
	 * @param mixed  $default Value to return on cache+DB miss.
	 *
	 * @return mixed The stored value (string in DB; consumer casts).
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
						"SELECT setting_value FROM `{$table}` WHERE setting_name = %s AND setting_type = %s ORDER BY setting_id ASC LIMIT 1",
						$name,
						$group
					)
				);
			}
		);

		return ( null === $value ) ? $default : $value;
	}

	/**
	 * Return every setting in a group, normalized as a name → value map.
	 *
	 * Cached under `bp_v3_settings_v<N>_group_<md5(group)>`, where `<N>` is
	 * the current FrontendFormCache version (bumped on every admin save).
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
					"SELECT setting_name AS name, setting_value AS value FROM `{$table}` WHERE setting_type = %s",
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
	 * Write a single setting value (insert or update by name + group).
	 *
	 * Used by counter-style settings like `bookingpress_last_invoice_id`
	 * that must be persisted from the frontend booking flow. Mirrors the
	 * legacy `bookingpress_update_settings()` upsert semantics in
	 * `core/classes/class.bookingpress.php:5694`. Bumps the cache version
	 * so subsequent reads see the new value.
	 *
	 * @param string $name
	 * @param string $group
	 * @param mixed  $value
	 *
	 * @return bool True on success.
	 */
	public function set( $name, $group, $value ) {
		$name  = (string) $name;
		$group = (string) $group;
		if ( '' === $name || '' === $group ) {
			return false;
		}

		global $wpdb;
		$table  = $this->table();
		$exists = (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT COUNT(setting_id) FROM `{$table}` WHERE setting_name = %s AND setting_type = %s",
				$name,
				$group
			)
		);

		$row = array(
			'setting_value' => is_bool( $value ) ? $value : sanitize_text_field( (string) $value ),
			'setting_type'  => $group,
			'updated_at'    => current_time( 'mysql' ),
		);

		if ( $exists > 0 ) {
			$ok = $wpdb->update( $table, $row, array( 'setting_name' => $name, 'setting_type' => $group ) );
		} else {
			$row['setting_name'] = $name;
			$ok = $wpdb->insert( $table, $row );
		}

		if ( false === $ok ) {
			return false;
		}

		FrontendFormCache::bump( 'setting_set:' . $group . '|' . $name );
		return true;
	}

	/**
	 * Flush the cached result for one setting group so the next read
	 * hits the database.
	 *
	 * Under the FrontendFormCache version-key model a single bump
	 * invalidates ALL settings (and every other cached namespace) in one
	 * move, so this method simply bumps the global version. Per-group
	 * invalidation is no longer meaningful — the version increment
	 * orphans every key cheaply and atomically.
	 *
	 * Retained for API compatibility with any external caller (Pro,
	 * extensions) that still calls `flush_group()` directly.
	 *
	 * @param string $group e.g. SettingsRepository::GROUP_PAYMENT
	 */
	public function flush_group( $group ) {
		FrontendFormCache::bump( 'settings_flush_group:' . (string) $group );
	}

	/**
	 * Convenience: read multiple keys from one group in a single call.
	 *
	 * @param string             $group
	 * @param array<int, string> $names
	 *
	 * @return array<string, string> Map of name → value (empty string when missing).
	 */
	public function get_many( $group, array $names ) {
		$all = $this->get_group( $group );
		$out = array();
		foreach ( $names as $n ) {
			$out[ $n ] = isset( $all[ $n ] ) ? $all[ $n ] : '';
		}
		return $out;
	}
}

<?php
/**
 * BaseRepository — shared plumbing for every Vue3 repository.
 *
 * Provides:
 * - `$wpdb` access without forcing each repository to declare it
 * - Table-name resolution (qualified with `$wpdb->prefix`)
 * - Transient-backed read-through cache keyed under `bp_v3_*_v1`
 * - Schema introspection helper for Pro-extended tables
 * - camelCase normalization helpers
 *
 * **No legacy coupling.** Repositories never call into `$GLOBALS['BookingPress']`
 * or any `core/classes/*` method. Table names are computed locally from
 * `$wpdb->prefix` plus a static suffix constant on each subclass.
 *
 * @package BookingPress\Vue3\Repositories
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §5
 */

namespace BookingPress\Vue3\Repositories;

use BookingPress\Vue3\Cache\FrontendFormCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class BaseRepository {

	/**
	 * Legacy fixed version suffix. Retained as a public constant for any
	 * external consumer that might still reference it, but new code should
	 * not rely on it — repository cache keys now compose a *dynamic*
	 * version through {@see FrontendFormCache::key()}, which bumps on
	 * every relevant admin save.
	 *
	 * @deprecated Use FrontendFormCache::key() instead.
	 */
	const CACHE_VERSION = 'v1';

	/**
	 * Cache TTL in seconds.
	 *
	 * Defaults to 0 — persistent caching of Vue3 frontend settings is
	 * intentionally disabled while the shortcode is still in parity-build
	 * stage. Re-enable globally via the `bookingpress_form_v3_cache_ttl`
	 * filter once the centralized version-key invalidation is proven safe
	 * end-to-end. Per-call overrides are still honored via {@see remember()}.
	 */
	const DEFAULT_TTL = 0;

	/**
	 * Schema-introspection cache (per-request) — avoids hitting
	 * `SHOW COLUMNS` repeatedly for the same table.
	 *
	 * @var array<string, array<string, true>>
	 */
	private static $columns_cache = array();

	/**
	 * The unqualified table suffix (without `$wpdb->prefix`).
	 *
	 * Subclasses set this to e.g. `'bookingpress_services'`.
	 *
	 * @return string
	 */
	abstract protected function table_suffix();

	/**
	 * Fully-qualified table name (with prefix).
	 *
	 * @return string
	 */
	protected function table() {
		global $wpdb;
		return $wpdb->prefix . $this->table_suffix();
	}

	/**
	 * Repository identifier used in cache keys.
	 *
	 * Default: the table suffix without the `bookingpress_` prefix.
	 *
	 * @return string
	 */
	protected function cache_namespace() {
		return preg_replace( '/^bookingpress_/', '', $this->table_suffix() );
	}

	/**
	 * Build a cache key under the `bp_v3_<namespace>_v<N>_<key>` convention.
	 *
	 * The `<N>` segment is the current value of the FrontendFormCache
	 * version counter — bumping that counter (on any admin save that
	 * affects the frontend form) orphans every prior key in one move, so
	 * we never need to scan-delete by prefix.
	 *
	 * @param string $key Cache key suffix (e.g. `'all'`, `'by_id_42'`).
	 *
	 * @return string
	 */
	protected function cache_key( $key ) {
		return FrontendFormCache::key( $this->cache_namespace(), $key );
	}

	/**
	 * Resolve the TTL for cache writes. Filterable.
	 *
	 * @param int $default
	 *
	 * @return int Seconds.
	 */
	protected function resolve_ttl( $default = null ) {
		$ttl = null === $default ? self::DEFAULT_TTL : (int) $default;

		/**
		 * Filter the transient TTL for the Vue3 repository cache.
		 *
		 * @param int $ttl Default 900 seconds (15 minutes).
		 */
		return (int) apply_filters( 'bookingpress_form_v3_cache_ttl', $ttl );
	}

	/**
	 * Transient-backed read-through cache.
	 *
	 * Calls `$producer()` on cache miss; stores its return under
	 * `bp_v3_<namespace>_<version>_<key>` for `$ttl` seconds; returns the
	 * cached or freshly-produced value on hit.
	 *
	 * @param string   $key
	 * @param callable $producer
	 * @param int|null $ttl
	 *
	 * @return mixed
	 */
	protected function remember( $key, callable $producer, $ttl = null ) {
		$resolved_ttl = $this->resolve_ttl( $ttl );

		// TTL <= 0 means "caching disabled" — produce fresh every call.
		// Important: do NOT pass 0 to set_transient; WP treats 0 as
		// "never expires" and the value would persist forever.
		if ( $resolved_ttl <= 0 ) {
			return $producer();
		}

		$cache_key = $this->cache_key( $key );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$value = $producer();
		set_transient( $cache_key, $value, $resolved_ttl );
		return $value;
	}

	/**
	 * Purge a single cache entry written by {@see remember()}.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function invalidate( $key ) {
		delete_transient( $this->cache_key( $key ) );
	}

	/**
	 * Return the set of column names present on this repository's table.
	 *
	 * Used to defensively support Pro-extended schemas (e.g. Lite's
	 * `bookingpress_form_fields` adds `bookingpress_field_meta_key` only
	 * when Pro is installed).
	 *
	 * @return array<string, true> Column name → true map.
	 */
	protected function get_table_columns() {
		$table = $this->table();
		if ( isset( self::$columns_cache[ $table ] ) ) {
			return self::$columns_cache[ $table ];
		}

		global $wpdb;
		// SHOW COLUMNS is safe from injection because $table is fully
		// derived from $wpdb->prefix + a class constant.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_col( "SHOW COLUMNS FROM `{$table}`" );

		$cols = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $name ) {
				$cols[ $name ] = true;
			}
		}

		self::$columns_cache[ $table ] = $cols;
		return $cols;
	}

	/**
	 * Whether the table has a given column.
	 *
	 * @param string $column
	 *
	 * @return bool
	 */
	protected function has_column( $column ) {
		$cols = $this->get_table_columns();
		return isset( $cols[ $column ] );
	}

	/**
	 * Pull a column's value from a raw row or return a default.
	 *
	 * @param array  $row
	 * @param string $column
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function pluck( array $row, $column, $default = null ) {
		if ( ! array_key_exists( $column, $row ) ) {
			return $default;
		}
		$value = $row[ $column ];
		return null === $value ? $default : $value;
	}

	/**
	 * Pull a TEXT column's value and strip the WP save-time slashes.
	 *
	 * BookingPress rows are written through WP's slashed request pipeline, so a
	 * name like "Men's Haircut" is stored as "Men\'s Haircut". Legacy reads run
	 * `stripslashes_deep()` before display (e.g. class.bookingpress.php:7724);
	 * this is the ONE shared decode point for the Vue 3 repositories so every
	 * consumer (state island, REST, submit snapshots) gets the clean value and
	 * nothing downstream needs (or is allowed) to decode again.
	 *
	 * @param array  $row
	 * @param string $column
	 * @param string $default
	 *
	 * @return string
	 */
	protected function pluck_text( array $row, $column, $default = '' ) {
		$value = $this->pluck( $row, $column, $default );
		return is_string( $value ) ? stripslashes( $value ) : (string) $value;
	}

	/**
	 * Convert a snake_case key to camelCase.
	 *
	 * `bookingpress_service_name` → `bookingpressServiceName`.
	 *
	 * @param string $snake
	 *
	 * @return string
	 */
	protected function camel( $snake ) {
		$snake = (string) $snake;
		if ( '' === $snake ) {
			return $snake;
		}
		$parts = explode( '_', $snake );
		$first = array_shift( $parts );
		return $first . implode( '', array_map( 'ucfirst', $parts ) );
	}

	/**
	 * Apply a snake-to-camel rename to an associative array's keys.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	protected function camelize_keys( array $row ) {
		$out = array();
		foreach ( $row as $key => $value ) {
			$out[ $this->camel( $key ) ] = $value;
		}
		return $out;
	}
}

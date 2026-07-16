<?php
/**
 * FrontendFormCache — centralized cache version + key helper for the Vue3 form.
 *
 * Problem this exists to solve:
 *   Scattered `delete_transient` / `wp_cache_delete` calls were missing
 *   invalidation paths and could not protect against future cache keys.
 *   A single monotonically-incrementing version option lets every save
 *   handler bump once and orphan ALL prior cache rows in one move — no
 *   key-by-key cleanup required.
 *
 * Usage on writes (the "bump" side — admin save handlers):
 *
 *     FrontendFormCache::bump( 'settings_saved' );
 *
 * Usage on reads (composed inside repositories — callers don't see it):
 *
 *     $key = FrontendFormCache::key( 'services', array( 'view' => 'all' ) );
 *     // -> "bp_v3_services_v{N}_{md5(json(args))}"
 *
 * Old transient rows simply orphan when the version increments. WordPress
 * eventually garbage-collects expired transients; we never scan-delete.
 *
 * @package BookingPress\Vue3\Cache
 */

namespace BookingPress\Vue3\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FrontendFormCache {

	/** WP option that holds the current cache version (integer). */
	const VERSION_OPTION = 'bookingpress_frontend_form_cache_version';

	/**
	 * Per-request memoization of the version read. Avoids re-querying the
	 * options table for every repository read in the same request. A bump
	 * within the same request invalidates this cache via {@see bump()}.
	 *
	 * @var int|null
	 */
	private static $version_memo = null;

	/**
	 * Read the current cache version. Initializes to 1 on first call.
	 *
	 * @return int
	 */
	public static function get_version() {
		if ( null !== self::$version_memo ) {
			return self::$version_memo;
		}

		$version = (int) get_option( self::VERSION_OPTION, 0 );
		if ( $version < 1 ) {
			$version = 1;
			update_option( self::VERSION_OPTION, $version, false );
		}

		self::$version_memo = $version;
		return $version;
	}

	/**
	 * Atomically bump the version. Every existing cache key composed
	 * through {@see key()} becomes unreachable from the next read onward.
	 *
	 * Safe to call multiple times in a single request — subsequent calls
	 * are still O(1) and produce a strictly increasing sequence.
	 *
	 * @param string $reason Free-form tag for observability; ignored when
	 *                       WP_DEBUG is off.
	 *
	 * @return int The new version.
	 */
	public static function bump( $reason = '' ) {
		$current = (int) get_option( self::VERSION_OPTION, 0 );
		$next    = $current < 1 ? 2 : $current + 1;
		update_option( self::VERSION_OPTION, $next, false );

		self::$version_memo = $next;

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			do_action( 'bookingpress_form_v3_cache_version_bumped', $next, (string) $reason );
		}

		return $next;
	}

	/**
	 * Compose a versioned cache key under
	 * `bp_v3_<namespace>_v<version>_<tail>`.
	 *
	 * `$suffix` accepts two shapes:
	 *   - **string** — used as a readable tail after sanitization
	 *     (e.g. `'all'`, `'by_id_42'`). Keeps DB-visible keys debuggable.
	 *   - **array**  — JSON-encoded then md5-hashed. Use for structured
	 *     argument bags (timeslot grid params, etc.) where the natural
	 *     string form would be long or collision-prone.
	 *
	 * An empty array collapses to `'_'`, which is stable across calls.
	 *
	 * @param string       $namespace e.g. `'services'`, `'settings'`.
	 * @param string|array $suffix    Key tail. See above.
	 *
	 * @return string
	 */
	public static function key( $namespace, $suffix = '' ) {
		$namespace = preg_replace( '/[^a-z0-9_]/i', '_', (string) $namespace );

		if ( is_array( $suffix ) ) {
			$tail = empty( $suffix ) ? '_' : md5( (string) wp_json_encode( $suffix ) );
		} else {
			$tail = (string) $suffix;
			$tail = '' === $tail ? '_' : preg_replace( '/[^a-z0-9_\-]/i', '_', $tail );
		}

		return sprintf(
			'bp_v3_%s_v%d_%s',
			$namespace,
			self::get_version(),
			$tail
		);
	}

	/**
	 * Reset the in-process memo. Test-only.
	 *
	 * @internal
	 *
	 * @return void
	 */
	public static function reset_memo_for_tests() {
		self::$version_memo = null;
	}
}

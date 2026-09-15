<?php
/**
 * NonceService — issues + verifies the bp_v3_nonce / bp_v3_instance_token pair.
 *
 * Two-token model:
 * - `bp_v3_nonce` — standard WP nonce bound to action `bp_v3_form`. Short
 *   lifetime per WP's nonce lifecycle. Defends against CSRF.
 * - `bp_v3_instance_token` — opaque random string issued at render time,
 *   stored in the `{prefix}bookingpress_transient` DB table keyed by the
 *   token itself. Defends against cross-instance replay (a request issued
 *   from the page where instance A rendered must carry instance A's token,
 *   not B's). Lifetime: 12h (matches the typical longest a booking session
 *   stays open).
 *
 * Storage note: this deliberately uses the dedicated `bookingpress_transient`
 * table (direct $wpdb) rather than WP's core transient API. Core transients
 * route through the object cache when an `object-cache.php` drop-in is
 * present; a broken/orphaned drop-in (LiteSpeed/Redis/Memcached) silently
 * turns every set into a per-request no-op, so the token written at render
 * is gone by the next REST request → spurious `bp_v3_invalid_instance`
 * ("Unknown form instance"). The DB table bypasses that layer entirely.
 * Row GC is handled by the existing daily
 * `bookingpress_cleanup_transient_data_hook_callback` cron.
 *
 * Concrete-only — not a Pro override point.
 *
 * @package BookingPress\Vue3\Services
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md Appendix B
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Exceptions\InvalidNonceException;
use BookingPress\Vue3\Exceptions\UnknownInstanceException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NonceService {

	/** Nonce action name — see plan §4.1. */
	const NONCE_ACTION = 'bookingpress_form_v3_nonce';

	/** Instance-token transient lifetime (12 hours). */
	const INSTANCE_TTL = 43200;

	/** Transient key prefix. */
	const INSTANCE_PREFIX = 'bp_v3_inst_v1_';

	/**
	 * Dedicated transient table name (mirrors the legacy
	 * `{prefix}bookingpress_transient` table). Computed from `$wpdb->prefix`
	 * so this service never depends on the legacy global being registered.
	 *
	 * @return string
	 */
	private function transient_table() {
		global $wpdb;
		return $wpdb->prefix . 'bookingpress_transient';
	}

	/**
	 * Write (upsert) an instance token → instance-id binding into the
	 * dedicated transient table. Column semantics match the legacy
	 * `bookingpress_update_transient()` so the existing cleanup cron GCs it.
	 *
	 * @param string $token       The 32-char instance token.
	 * @param string $instance_id The instance id to bind.
	 *
	 * @return void
	 */
	private function store_instance( $token, $instance_id ) {
		global $wpdb;
		$table  = $this->transient_table();
		$key    = self::INSTANCE_PREFIX . $token;
		$expiry = current_time( 'timestamp' ) + self::INSTANCE_TTL; // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- parity with legacy transient table.

		$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_transient_id FROM {$table} WHERE bookingpress_transient_key = %s", $key ) ); // phpcs:ignore WordPress.DB

		if ( ! empty( $existing_id ) ) {
			$wpdb->update(
				$table,
				array(
					'bookingpress_transient_value'  => (string) $instance_id,
					'bookingpress_transient_expiry' => (string) $expiry,
				),
				array( 'bookingpress_transient_id' => $existing_id )
			);
		} else {
			$wpdb->insert(
				$table,
				array(
					'bookingpress_transient_key'    => $key,
					'bookingpress_transient_value'  => (string) $instance_id,
					'bookingpress_transient_expiry' => (string) $expiry,
				)
			);
		}
	}

	/**
	 * Read the instance-id bound to a token, honouring the 12h expiry cap.
	 *
	 * Returns `false` when the token is unknown or past its expiry (mirrors
	 * the old `get_transient()` contract the callers expect). Expiry is
	 * enforced here rather than relying solely on the cleanup cron, so a
	 * stale row can never satisfy a security check.
	 *
	 * @param string $token The instance token from the request.
	 *
	 * @return string|false The bound instance id, or false.
	 */
	private function read_instance( $token ) {
		global $wpdb;
		$table = $this->transient_table();
		$key   = self::INSTANCE_PREFIX . $token;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_transient_value, bookingpress_transient_expiry FROM {$table} WHERE bookingpress_transient_key = %s", $key ) ); // phpcs:ignore WordPress.DB

		if ( empty( $row ) ) {
			return false;
		}

		$expiry = $row->bookingpress_transient_expiry;
		if ( 'never' !== $expiry && (int) $expiry < current_time( 'timestamp' ) ) { // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- parity with legacy transient table.
			return false;
		}

		return $row->bookingpress_transient_value;
	}

	/**
	 * Issue a fresh nonce.
	 *
	 * @return string
	 */
	public function issue_nonce() {
		return wp_create_nonce( self::NONCE_ACTION );
	}

	/**
	 * Issue a fresh per-render instance token and bind it to the instance id.
	 *
	 * @param string $instance_id The 12-char shell id from `Routing::generate_unique_id()`.
	 *
	 * @return string The token to ship in the JSON island.
	 */
	public function issue_instance_token( $instance_id ) {
		$token = wp_generate_password( 32, false );
		$this->store_instance( $token, (string) $instance_id );
		return $token;
	}

	/**
	 * Verify a (nonce, instance_token, instance_id) triple from a REST body.
	 *
	 * Called by REST controllers (M4) before any action. Refuses by throwing.
	 *
	 * @param string $nonce
	 * @param string $instance_token
	 * @param string $expected_instance_id The instance id the request claims to belong to.
	 *
	 * @return void
	 *
	 * @throws InvalidNonceException
	 * @throws UnknownInstanceException
	 */
	public function verify( $nonce, $instance_token, $expected_instance_id ) {
		if ( ! wp_verify_nonce( (string) $nonce, self::NONCE_ACTION ) ) {
			throw new InvalidNonceException( 'Missing or expired bp_v3_nonce.' );
		}

		$instance_token = (string) $instance_token;
		if ( '' === $instance_token ) {
			throw new UnknownInstanceException( 'Missing bp_v3_instance_token.' );
		}

		$bound = $this->read_instance( $instance_token );
		if ( false === $bound ) {
			throw new UnknownInstanceException( 'Instance token expired or unknown.' );
		}
		if ( (string) $bound !== (string) $expected_instance_id ) {
			throw new UnknownInstanceException( 'Instance token does not match the requesting instance.' );
		}
	}

	/**
	 * Convenience predicate — used by M4 REST permission callbacks.
	 *
	 * @param string $nonce
	 *
	 * @return bool
	 */
	public function is_valid_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, self::NONCE_ACTION );
	}

	/**
	 * Predicate variant of {@see verify()} for the instance-token half.
	 *
	 * Used by NonceGate alongside the wp_rest + form-nonce checks.
	 *
	 * @param string $instance_token
	 * @param string $expected_instance_id
	 *
	 * @return bool
	 */
	public function is_valid_instance_token( $instance_token, $expected_instance_id ) {
		$instance_token = (string) $instance_token;
		if ( '' === $instance_token ) {
			return false;
		}
		$bound = $this->read_instance( $instance_token );
		if ( false === $bound ) {
			return false;
		}
		return ( (string) $bound === (string) $expected_instance_id );
	}
}

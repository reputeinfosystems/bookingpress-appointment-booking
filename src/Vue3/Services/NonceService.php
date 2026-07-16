<?php
/**
 * NonceService — issues + verifies the bp_v3_nonce / bp_v3_instance_token pair.
 *
 * Two-token model:
 * - `bp_v3_nonce` — standard WP nonce bound to action `bp_v3_form`. Short
 *   lifetime per WP's nonce lifecycle. Defends against CSRF.
 * - `bp_v3_instance_token` — opaque random string issued at render time,
 *   stored in a transient keyed by the token itself. Defends against
 *   cross-instance replay (a request issued from the page where instance
 *   A rendered must carry instance A's token, not B's). Lifetime: 12h
 *   (matches the typical longest a booking session stays open).
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
		set_transient( self::INSTANCE_PREFIX . $token, (string) $instance_id, self::INSTANCE_TTL );
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

		$bound = get_transient( self::INSTANCE_PREFIX . $instance_token );
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
		$bound = get_transient( self::INSTANCE_PREFIX . $instance_token );
		if ( false === $bound ) {
			return false;
		}
		return ( (string) $bound === (string) $expected_instance_id );
	}
}

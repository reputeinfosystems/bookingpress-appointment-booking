<?php
/**
 * Thrown by the NonceGate (M4) when a REST request's `bp_v3_nonce` body field
 * is missing, malformed, or expired.
 *
 * @package BookingPress\Vue3\Exceptions
 */

namespace BookingPress\Vue3\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runtime — request lacks a valid nonce.
 *
 * Maps to REST error code `bp_v3_nonce_invalid` (HTTP 403).
 */
class InvalidNonceException extends \RuntimeException {
}

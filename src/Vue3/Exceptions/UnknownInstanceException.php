<?php
/**
 * Thrown by the NonceGate (M4) when a REST request's `bp_v3_instance_token`
 * body field does not match any known per-render instance.
 *
 * @package BookingPress\Vue3\Exceptions
 */

namespace BookingPress\Vue3\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runtime — instance token not recognised (replay or stale tab).
 *
 * Maps to REST error code `bp_v3_instance_invalid` (HTTP 403).
 */
class UnknownInstanceException extends \RuntimeException {
}

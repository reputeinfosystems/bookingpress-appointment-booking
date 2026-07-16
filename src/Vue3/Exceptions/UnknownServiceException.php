<?php
/**
 * Thrown when a caller references a service id that does not exist in
 * `tbl_bookingpress_services`.
 *
 * @package BookingPress\Vue3\Exceptions
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.1, §M0.4
 */

namespace BookingPress\Vue3\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caller bug — either the supplied service id is not in the catalog OR an
 * unknown interface FQCN was passed to {@see \BookingPress\Vue3\Services\ServiceLocator::get()}.
 *
 * Maps to REST error code `bp_v3_unknown_service` (HTTP 400) for catalog
 * lookups, or to a 500 from the ServiceLocator path (programmer error).
 */
class UnknownServiceException extends \InvalidArgumentException {

	/**
	 * Convenience constructor for ServiceLocator misses.
	 *
	 * @param string $interface_fqcn
	 *
	 * @return self
	 */
	public static function for_interface( $interface_fqcn ) {
		return new self(
			sprintf( 'No default binding for interface "%s" in ServiceLocator.', (string) $interface_fqcn )
		);
	}
}

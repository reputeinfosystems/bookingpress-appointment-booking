<?php
/**
 * Thrown when a callback on the `bookingpress_form_v3_service` registry filter
 * returns an instance that does not implement the requested interface (see
 * `BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md` §5a.2).
 *
 * @package BookingPress\Vue3\Exceptions
 */

namespace BookingPress\Vue3\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Programmer error — an add-on registered an implementation that doesn't honour
 * the contract interface.
 *
 * Maps to a `_doing_it_wrong` notice and a graceful fallback to the Lite
 * default implementation. Never surfaced to end users.
 */
class InvalidServiceImplementationException extends \LogicException {
}

<?php
/**
 * ContextRegistry — id to payment context.
 *
 * Registering a context is how a module becomes payable:
 *
 *     add_filter( PaymentHooks::FILTER_REGISTER_CONTEXTS, function ( $contexts ) {
 *         $contexts['gift_card'] = new GiftCardPaymentContext();
 *         return $contexts;
 *     } );
 *
 * At that moment EVERY registered gateway can sell gift cards, with no gateway
 * release. That is the acceptance test for this architecture.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

use BookingPress\Vue3\Payments\Contracts\PaymentContextInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ContextRegistry {

	/** @var PaymentContextInterface[]|null Per-request cache. */
	private static $contexts = null;

	/**
	 * @return PaymentContextInterface[] Keyed by context id.
	 */
	public static function all() {
		if ( null !== self::$contexts ) {
			return self::$contexts;
		}

		$registered = apply_filters( PaymentHooks::FILTER_REGISTER_CONTEXTS, array() );
		$registered = is_array( $registered ) ? $registered : array();

		$valid = array();
		foreach ( $registered as $context ) {
			if ( ! ( $context instanceof PaymentContextInterface ) ) {
				continue;
			}
			$id = (string) $context->get_id();
			if ( '' === $id ) {
				continue;
			}
			$valid[ $id ] = $context;
		}

		self::$contexts = $valid;
		return self::$contexts;
	}

	/**
	 * @param string $id
	 *
	 * @return PaymentContextInterface|null
	 */
	public static function get( $id ) {
		$all = self::all();
		$id  = (string) $id;
		return isset( $all[ $id ] ) ? $all[ $id ] : null;
	}

	/**
	 * Resolve or throw — the orchestrator's entry point, so an unknown context
	 * is a clean 400 instead of a null-property fatal.
	 *
	 * @param string $id
	 *
	 * @return PaymentContextInterface
	 *
	 * @throws \InvalidArgumentException When no such context is registered.
	 */
	public static function require_context( $id ) {
		$context = self::get( $id );
		if ( null === $context ) {
			throw new \InvalidArgumentException(
				sprintf( 'Unknown payment context "%s".', (string) $id )
			);
		}
		return $context;
	}

	/**
	 * Test-only cache reset.
	 *
	 * @return void
	 */
	public static function reset() {
		self::$contexts = null;
	}
}

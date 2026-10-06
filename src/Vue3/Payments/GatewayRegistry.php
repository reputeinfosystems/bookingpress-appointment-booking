<?php
/**
 * GatewayRegistry — id to gateway, with compatibility and capability gating.
 *
 * Gateways register once, on a filter, and are then usable in every context
 * that exists now or later:
 *
 *     add_filter( PaymentHooks::FILTER_REGISTER_GATEWAYS, function ( $gateways ) {
 *         $gateways['stripe'] = new StripeGateway();
 *         return $gateways;
 *     } );
 *
 * That single call replaces, per gateway: two `register_rest_route()` blocks
 * per form, a `FILTER_PAYMENT_METHODS` callback, and a
 * `FILTER_INITIAL_STATE` + `FILTER_PRO_INITIAL_STATE` pair.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

use BookingPress\Vue3\Payments\Contracts\PaymentContextInterface;
use BookingPress\Vue3\Payments\Contracts\PaymentGatewayInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GatewayRegistry {

	/** @var PaymentGatewayInterface[]|null Per-request cache. */
	private static $gateways = null;

	/**
	 * All registered, compatible gateways — enabled or not.
	 *
	 * @return PaymentGatewayInterface[] Keyed by gateway id.
	 */
	public static function all() {
		if ( null !== self::$gateways ) {
			return self::$gateways;
		}

		$registered = apply_filters( PaymentHooks::FILTER_REGISTER_GATEWAYS, array() );
		$registered = is_array( $registered ) ? $registered : array();

		$valid = array();
		foreach ( $registered as $gateway ) {
			if ( ! ( $gateway instanceof PaymentGatewayInterface ) ) {
				self::complain( 'A registered payment gateway does not implement PaymentGatewayInterface.' );
				continue;
			}

			$id = (string) $gateway->get_id();
			if ( '' === $id ) {
				self::complain( 'A registered payment gateway returned an empty id.' );
				continue;
			}

			// Refuse a gateway built against an API major we do not understand,
			// rather than letting it half-work and mischarge.
			if ( ! Capabilities::is_compatible( $gateway->get_api_version() ) ) {
				self::complain(
					sprintf(
						'Gateway "%s" targets payments API %s; core provides %s. Skipped.',
						$id,
						(string) $gateway->get_api_version(),
						Capabilities::API_VERSION
					)
				);
				continue;
			}

			$caps = (array) $gateway->get_capabilities();

			// A typo like 'refunds' would otherwise silently never match.
			$unknown = array_diff( $caps, Capabilities::all() );
			if ( ! empty( $unknown ) ) {
				self::complain(
					sprintf( 'Gateway "%s" declares unknown capabilities: %s', $id, implode( ', ', $unknown ) )
				);
			}

			$missing_baseline = array_diff( Capabilities::baseline(), $caps );
			if ( ! empty( $missing_baseline ) ) {
				self::complain(
					sprintf(
						'Gateway "%s" does not declare the baseline capability %s and cannot honour a server-sealed amount. Skipped.',
						$id,
						implode( ', ', $missing_baseline )
					)
				);
				continue;
			}

			$valid[ $id ] = $gateway;
		}

		self::$gateways = $valid;
		return self::$gateways;
	}

	/**
	 * @param string $id
	 *
	 * @return PaymentGatewayInterface|null
	 */
	public static function get( $id ) {
		$all = self::all();
		$id  = (string) $id;
		return isset( $all[ $id ] ) ? $all[ $id ] : null;
	}

	/**
	 * Gateways usable in a given context: registered, compatible, enabled by
	 * the merchant, and capable of what the context requires.
	 *
	 * @param PaymentContextInterface $context
	 *
	 * @return PaymentGatewayInterface[]
	 */
	public static function for_context( PaymentContextInterface $context ) {
		$required = (array) $context->get_required_capabilities();
		$usable   = array();

		foreach ( self::all() as $id => $gateway ) {
			if ( ! $gateway->is_enabled() ) {
				continue;
			}
			$missing = array_diff( $required, (array) $gateway->get_capabilities() );
			if ( ! empty( $missing ) ) {
				// Not an error. The gateway is simply not viable HERE, and stays
				// available everywhere it is. This is what lets core ship a new
				// capability without a coordinated 20-add-on release.
				continue;
			}
			$usable[ $id ] = $gateway;
		}

		return $usable;
	}

	/**
	 * Payment-picker descriptors for a context.
	 *
	 * Core calls this while building whichever form is rendering, so a NEW FORM
	 * NEEDS NO GATEWAY CHANGE — which is the outcome the whole refactor exists
	 * to produce.
	 *
	 * @param PaymentContextInterface $context
	 * @param array                   $extra Context data for the policy filter.
	 *
	 * @return array
	 */
	public static function descriptors_for( PaymentContextInterface $context, array $extra = array() ) {
		$descriptors = array();

		foreach ( self::for_context( $context ) as $id => $gateway ) {
			$descriptor = (array) $gateway->get_descriptor( $context->get_id() );
			$descriptor['id'] = $id;
			if ( ! isset( $descriptor['capabilities'] ) ) {
				$descriptor['capabilities'] = (array) $gateway->get_capabilities();
			}
			$descriptors[] = $descriptor;
		}

		/**
		 * Policy layer — per-service restrictions, Multi-Language label
		 * overrides, role rules. Capability is already handled above.
		 *
		 * @param array  $descriptors
		 * @param string $context_id
		 * @param array  $extra
		 */
		$descriptors = apply_filters(
			PaymentHooks::FILTER_AVAILABLE_METHODS,
			$descriptors,
			$context->get_id(),
			$extra
		);

		return is_array( $descriptors ) ? $descriptors : array();
	}

	/**
	 * Surface a registration problem to developers without breaking the site.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	private static function complain( $message ) {
		do_action( 'bookingpress_other_debug_log_entry', 'payments', 'gateway registry', 'bookingpress payments', $message, 0 );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- developer-facing, WP_DEBUG only.
			error_log( 'BOOKINGPRESS payments: ' . $message );
		}
	}

	/**
	 * Test-only cache reset.
	 *
	 * @return void
	 */
	public static function reset() {
		self::$gateways = null;
	}
}

<?php
/**
 * ServiceLocator — interface-FQCN → bound-instance resolver.
 *
 * Per `BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md` §5a.2. Lite ships defaults;
 * Pro 6.0+ swaps any interface via a single `bookingpress_form_v3_service`
 * filter callback that switches on the requested interface FQCN.
 *
 * @package BookingPress\Vue3\Services
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Contracts\AvailabilityServiceInterface;
use BookingPress\Vue3\Contracts\CaptchaServiceInterface;
use BookingPress\Vue3\Contracts\PaymentServiceInterface;
use BookingPress\Vue3\Contracts\PricingServiceInterface;
use BookingPress\Vue3\Contracts\ServiceCatalogServiceInterface;
use BookingPress\Vue3\Contracts\SubmissionServiceInterface;
use BookingPress\Vue3\Contracts\TimeslotServiceInterface;
use BookingPress\Vue3\Contracts\ValidationServiceInterface;
use BookingPress\Vue3\Exceptions\InvalidServiceImplementationException;
use BookingPress\Vue3\Exceptions\UnknownServiceException;
use BookingPress\Vue3\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-request DI registry for the Vue3 services.
 *
 * Behaviour:
 * - `get( Interface::class )` returns a cached instance, instantiating the
 *   Lite default the first time it is asked.
 * - The single {@see Hooks::FILTER_SERVICE} filter (`bookingpress_form_v3_service`)
 *   runs **on first instantiation** with `( $default_instance, $interface_fqcn )`.
 *   Pro 6.0+ returns its own implementation (or a decorator around `$default`).
 * - A filter callback returning a value that is not `instanceof $interface_fqcn`
 *   raises {@see InvalidServiceImplementationException}.
 * - An unknown interface raises {@see UnknownServiceException}.
 */
final class ServiceLocator {

	/**
	 * Interface FQCN → default concrete FQCN.
	 *
	 * @var array<class-string, class-string>
	 */
	private static $defaults = array(
		// Filled in init() because PHP 5.x classes can't use ::class in
		// property initialisers reliably across all hosts the plugin
		// targets. init() runs once per request from the first get() call.
	);

	/**
	 * Per-request instance cache.
	 *
	 * @var array<class-string, object>
	 */
	private static $instances = array();

	/**
	 * Whether the defaults table has been populated this request.
	 *
	 * @var bool
	 */
	private static $bootstrapped = false;

	/**
	 * Populate the defaults table.
	 *
	 * @return void
	 */
	private static function bootstrap() {
		if ( self::$bootstrapped ) {
			return;
		}
		self::$bootstrapped = true;

		self::$defaults = array(
			ServiceCatalogServiceInterface::class => ServiceCatalogService::class,
			AvailabilityServiceInterface::class   => AvailabilityService::class,
			TimeslotServiceInterface::class       => TimeslotService::class,
			PricingServiceInterface::class        => PricingService::class,
			ValidationServiceInterface::class     => ValidationService::class,
			SubmissionServiceInterface::class     => SubmissionService::class,
			PaymentServiceInterface::class        => PaymentService::class,
			CaptchaServiceInterface::class        => CaptchaService::class,
		);
	}

	/**
	 * Resolve an interface to its bound instance.
	 *
	 * @param string $interface The interface FQCN to resolve.
	 *
	 * @return object An instance that is `instanceof $interface`.
	 *
	 * @throws UnknownServiceException When the interface has no default binding.
	 * @throws InvalidServiceImplementationException When a filter callback
	 *         returns a non-conforming instance.
	 */
	public static function get( $interface ) {
		self::bootstrap();
		$interface = (string) $interface;

		if ( isset( self::$instances[ $interface ] ) ) {
			return self::$instances[ $interface ];
		}

		if ( ! isset( self::$defaults[ $interface ] ) ) {
			throw UnknownServiceException::for_interface( $interface );
		}

		$class    = self::$defaults[ $interface ];
		$default  = new $class();

		/**
		 * Filter the resolved service instance.
		 *
		 * Pro 6.0+ overrides any Lite default by returning its own
		 * implementation (or a decorator that wraps `$default`). The callback
		 * must return an `instanceof $interface`; otherwise the locator
		 * throws InvalidServiceImplementationException.
		 *
		 * @param object $default        The Lite default instance.
		 * @param string $interface_fqcn The requested interface FQCN.
		 */
		$resolved = apply_filters( Hooks::FILTER_SERVICE, $default, $interface );

		

		if ( ! ( $resolved instanceof $interface ) ) {
			throw new InvalidServiceImplementationException(
				sprintf(
					'Filter "%s" must return an instance of %s; got %s.',
					Hooks::FILTER_SERVICE,
					$interface,
					is_object( $resolved ) ? get_class( $resolved ) : gettype( $resolved )
				)
			);
		}

		self::$instances[ $interface ] = $resolved;
		return $resolved;
	}

	/**
	 * Whether an interface has a default binding.
	 *
	 * @param string $interface
	 *
	 * @return bool
	 */
	public static function has( $interface ) {
		self::bootstrap();
		return isset( self::$defaults[ (string) $interface ] );
	}

	/**
	 * Test-only — clear the per-request cache.
	 *
	 * Production code never calls this.
	 *
	 * @return void
	 */
	public static function reset() {
		self::$instances = array();
	}
}

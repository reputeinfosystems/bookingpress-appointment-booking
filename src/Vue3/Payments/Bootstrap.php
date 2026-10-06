<?php
/**
 * Bootstrap — wires the payment layer up.
 *
 * NOT self-activating. Nothing in this subsystem runs until someone calls
 * `Bootstrap::register()`. To switch it on, add one line to
 * `src/Vue3/Routing.php::register()`, next to the existing RouteRegistrar line:
 *
 *     \BookingPress\Vue3\Payments\Bootstrap::register();
 *
 * Turning it on is SAFE AND NON-BREAKING. It mounts new routes under
 * `/payment-v3/` and registers the `booking_form` context plus the `on-site`
 * gateway. The existing `/form-v3/payment/paypal-*` and
 * `/complete-payment-v3/payment/*` routes are untouched and keep serving every
 * released gateway build, so old and new run side by side for the whole
 * migration. Nothing calls the new routes until the client is pointed at them.
 *
 * MIGRATION ORDER
 * ---------------
 *   1. (this file) Contracts + orchestrator + routes land in Lite, inert.
 *   2. PayPal becomes a `PayPalGateway` implementing the new interface.
 *      `PaymentServiceInterface::paypal_*` stays, delegating to it, deprecated —
 *      so `PaymentController` and Pro's copy of it keep working untouched.
 *   3. Pro registers `complete_payment`; Gift Card and Package register their
 *      contexts when their Vue 3 conversion lands.
 *      >> NO GATEWAY SHIPS FOR STEP 3. That is the acceptance test.
 *   4. Stripe migrates as the reference gateway — one build, every context.
 *      Then the remaining ~18, each at its own next release.
 *   5. Retire the duplicated routes and Pro's copied PaymentController.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

use BookingPress\Vue3\Payments\Contexts\BookingFormContext;
use BookingPress\Vue3\Payments\Gateways\OnSiteGateway;
use BookingPress\Vue3\Payments\Gateways\PayPalGateway;
use BookingPress\Vue3\Payments\REST\PaymentRouteRegistrar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bootstrap {

	/** @var bool */
	private static $registered = false;

	/**
	 * @return void
	 */
	public static function register() {
		if ( self::$registered ) {
			return;
		}
		self::$registered = true;

		add_action( 'rest_api_init', array( PaymentRouteRegistrar::class, 'register' ) );

		add_filter( PaymentHooks::FILTER_REGISTER_CONTEXTS, array( __CLASS__, 'register_default_contexts' ), 10, 1 );
		add_filter( PaymentHooks::FILTER_REGISTER_GATEWAYS, array( __CLASS__, 'register_default_gateways' ), 10, 1 );

		// Correct the PayPal SDK currency wherever the script is enqueued from.
		// Registered here rather than in Assets because Assets::register() only
		// runs on booking-form pages, and the page that needs this most is
		// Complete Payment.
		add_filter( 'script_loader_src', array( __CLASS__, 'filter_paypal_sdk_currency' ), 10, 2 );
	}

	/**
	 * Rewrite `currency=` on the PayPal SDK URL to the currency of the payment
	 * actually being taken.
	 *
	 * Done at the SRC level, not at the enqueue, because there are three
	 * enqueues of the `bookingpress-paypal-script` handle — the legacy
	 * `bookingpress_paypal_scripts_add()` (core), `frontend/BookingForm.php`
	 * and `Vue3/Assets.php` — and the two newer ones no-op when the handle is
	 * already registered. On the Complete Payment page the LEGACY one wins, so
	 * fixing the enqueue in Assets alone changed nothing. All three read
	 * `payment_default_currency`; this corrects the result of whichever ran.
	 *
	 * Why it matters: PayPal compares the SDK currency against the currency the
	 * order was created in and refuses the capture when they differ —
	 * "Expected currency from order api call to be TWD, got USD". A balance owed
	 * on a booking priced in USD is owed in USD no matter what the site is set
	 * to today, so the ORDER is right and the SDK URL is what has to follow.
	 *
	 * @param string $src
	 * @param string $handle
	 *
	 * @return string
	 */
	public static function filter_paypal_sdk_currency( $src, $handle ) {
		if ( 'bookingpress-paypal-script' !== $handle || ! is_string( $src ) ) {
			return $src;
		}
		if ( false === strpos( $src, 'paypal.com/sdk/js' ) ) {
			return $src;
		}

		$name = \BookingPress\Vue3\Services\PaymentService::resolve_currency( array( 'purpose' => 'paypal_sdk' ) );
		if ( '' === $name ) {
			return $src;
		}

		$helper = isset( $GLOBALS['BookingPress'] ) ? $GLOBALS['BookingPress'] : null;
		$code   = ( is_object( $helper ) && method_exists( $helper, 'bookingpress_get_currency_code' ) )
			? (string) $helper->bookingpress_get_currency_code( $name )
			: $name;
		if ( '' === $code ) {
			return $src;
		}

		// Leave the URL untouched when it already carries the right currency,
		// so the common case does not churn the src and bust any cache keyed
		// on it.
		$current = '';
		$parts   = wp_parse_url( $src, PHP_URL_QUERY );
		if ( is_string( $parts ) && '' !== $parts ) {
			$args = array();
			wp_parse_str( $parts, $args );
			$current = isset( $args['currency'] ) ? (string) $args['currency'] : '';
		}
		if ( $current === $code ) {
			return $src;
		}

		return add_query_arg( 'currency', $code, remove_query_arg( 'currency', $src ) );
	}

	/**
	 * Lite ships one context: the booking form. Pro adds `complete_payment`,
	 * Gift Card adds `gift_card`, Package adds `package` — each on this same
	 * filter, from their own plugin, with no change here.
	 *
	 * @param array $contexts
	 *
	 * @return array
	 */
	public static function register_default_contexts( $contexts ) {
		$contexts = is_array( $contexts ) ? $contexts : array();
		$contexts[ BookingFormContext::ID ] = new BookingFormContext();
		return $contexts;
	}

	/**
	 * Lite ships `on-site`. PayPal joins here in step 2 of the migration; every
	 * add-on gateway joins from its own plugin.
	 *
	 * @param array $gateways
	 *
	 * @return array
	 */
	public static function register_default_gateways( $gateways ) {
		$gateways = is_array( $gateways ) ? $gateways : array();

		$on_site = new OnSiteGateway();
		$gateways[ $on_site->get_id() ] = $on_site;

		$paypal = new PayPalGateway();
		$gateways[ $paypal->get_id() ] = $paypal;

		return $gateways;
	}
}

<?php
/**
 * PaymentRouteRegistrar — the complete REST surface for payments.
 *
 * FOUR ROUTES. FOR EVERY GATEWAY. FOR EVERY CONTEXT. FOREVER.
 *
 * Today the same surface is spread across: Lite's `RouteRegistrar` (4 PayPal
 * routes under `form-v3`), Pro's `ProRouteRegistrar` (the same 3 PayPal
 * handlers again under `complete-payment-v3`, delegating to the identical
 * `PaymentServiceInterface` methods — pure duplication with no behavioural
 * difference), and then 2 routes x 2 prefixes inside each of ~20 gateway
 * add-ons.
 *
 * The context and the gateway are PARAMETERS here, not path segments baked into
 * a registration call. That single change is what decouples the release cycles:
 * a new context adds a registry entry, not a route, so no gateway ships.
 *
 * BACKWARD COMPATIBILITY
 * ----------------------
 * The existing `form-v3/payment/paypal-*` and `complete-payment-v3/payment/*`
 * routes stay mounted and become thin shims onto these. Live sites and released
 * gateway builds keep working through the whole migration.
 *
 * @package BookingPress\Vue3\Payments\REST
 */

namespace BookingPress\Vue3\Payments\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentRouteRegistrar {

	/** Shared with the existing Vue 3 routes so nonces and root URL are unchanged. */
	const REST_NAMESPACE = 'bookingpress-app/v1';

	/** Deliberately NOT form-scoped — that was the original mistake. */
	const ROUTE_PREFIX = 'payment-v3';

	/**
	 * Hook this on `rest_api_init`.
	 *
	 * @return void
	 */
	public static function register() {
		$ns = self::REST_NAMESPACE;
		$rp = self::ROUTE_PREFIX;

		// --- Discovery. Which methods can this context offer right now? ------
		register_rest_route(
			$ns,
			"/{$rp}/methods",
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentGatewayController::class, 'methods' ),
				'args'                => array(
					'context' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// --- Stage the purchase and start the payment. -----------------------
		register_rest_route(
			$ns,
			"/{$rp}/prepare",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentGatewayController::class, 'prepare' ),
			)
		);

		// --- Verify the completed payment and materialise the purchase. ------
		register_rest_route(
			$ns,
			"/{$rp}/confirm",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentGatewayController::class, 'confirm' ),
			)
		);

		// --- Continue a payment already under way. ---------------------------
		// The third leg, for gateways whose flow needs a server round trip
		// BETWEEN the customer acting and the payment completing (ECPay). It is
		// nonce-gated and token-gated exactly like confirm, and CANNOT settle —
		// see PaymentOrchestrator::step().
		register_rest_route(
			$ns,
			"/{$rp}/step",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentGatewayController::class, 'step' ),
			)
		);

		// --- Server-to-server callback. PUBLIC BY DESIGN. --------------------
		// No nonce: the caller is a payment processor, not a browser session.
		// Authenticity is established by the gateway itself (signature check, or
		// PayPal's re-post) inside handle_webhook(), BEFORE anything is acted on.
		register_rest_route(
			$ns,
			"/{$rp}/webhook/(?P<gateway>[a-z0-9_\-]+)",
			array(
				'methods'             => array( 'POST', 'GET' ),
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentGatewayController::class, 'webhook' ),
				'args'                => array(
					'gateway' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);
	}

	/**
	 * Webhook URL for a gateway — hand this to the processor at prepare time.
	 *
	 * MIGRATION NOTE: gateways moving off a legacy listener (Stripe's
	 * `?bookingpress-listener=bpa_pro_stripe_url`) must keep that old URL
	 * working permanently. It is configured in merchants' live gateway
	 * dashboards and cannot be rewritten remotely; retiring it silently breaks
	 * settlement for every existing site.
	 *
	 * @param string $gateway_id
	 *
	 * @return string
	 */
	public static function webhook_url( $gateway_id ) {
		return rest_url(
			sprintf( '%s/%s/webhook/%s', self::REST_NAMESPACE, self::ROUTE_PREFIX, sanitize_key( (string) $gateway_id ) )
		);
	}

	/**
	 * Base URL the Vue 3 client uses for prepare/confirm.
	 *
	 * @return string
	 */
	public static function base_url() {
		return rest_url( self::REST_NAMESPACE . '/' . self::ROUTE_PREFIX );
	}
}

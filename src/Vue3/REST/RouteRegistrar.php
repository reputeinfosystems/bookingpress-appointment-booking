<?php
/**
 * RouteRegistrar — wires the 8 Vue3 REST routes on `rest_api_init`.
 *
 * Plan §4 inventory. All routes share:
 *   - namespace `bookingpress-app/v1`
 *   - prefix `/form-v3/`
 *   - `permission_callback => '__return_true'` (the form is public)
 *   - method `POST`
 *   - auth via `NonceGate::verify()` inside the controller
 *
 * @package BookingPress\Vue3\REST
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §4
 */

namespace BookingPress\Vue3\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RouteRegistrar {

	const REST_NAMESPACE = 'bookingpress-app/v1';
	const ROUTE_PREFIX   = 'form-v3';

	/** @var bool Idempotency guard. */
	private static $registered = false;

	/**
	 * Run on `rest_api_init` (registered from Routing::init()).
	 *
	 * @return void
	 */
	public static function register() {
		if ( self::$registered ) {
			return;
		}
		self::$registered = true;

		$ns = self::REST_NAMESPACE;
		$rp = self::ROUTE_PREFIX;

		register_rest_route(
			$ns,
			"/{$rp}/state",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( StateController::class, 'refresh' ),
			)
		);

		register_rest_route(
			$ns,
			"/{$rp}/timeslots",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( TimeslotController::class, 'get_initial_payload' ),
			)
		);

		register_rest_route(
			$ns,
			"/{$rp}/month-details",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( TimeslotController::class, 'get_month_payload' ),
			)
		);

		register_rest_route(
			$ns,
			"/{$rp}/submit",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( SubmissionController::class, 'submit' ),
			)
		);

		register_rest_route(
			$ns,
			"/{$rp}/captcha",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( CaptchaController::class, 'generate' ),
			)
		);

		register_rest_route(
			$ns,
			"/{$rp}/validate-username",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( ValidationController::class, 'validate_username' ),
			)
		);

		register_rest_route(
			$ns,
			"/{$rp}/payment/paypal-validate",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentController::class, 'paypal_validate' ),
			)
		);

		register_rest_route(
			$ns,
			"/{$rp}/payment/paypal-confirm",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentController::class, 'paypal_confirm' ),
			)
		);

		// PayPal Standard ("Legacy") redirect: prepare the auto-submit form.
		register_rest_route(
			$ns,
			"/{$rp}/payment/paypal-redirect-prepare",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentController::class, 'paypal_redirect_prepare' ),
			)
		);

		// PayPal Standard IPN listener (the webscr notify_url). PUBLIC — PayPal
		// posts with no session; the handler re-validates against PayPal.
		register_rest_route(
			$ns,
			"/{$rp}/payment/paypal-ipn",
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( PaymentController::class, 'paypal_ipn' ),
			)
		);
	}
}

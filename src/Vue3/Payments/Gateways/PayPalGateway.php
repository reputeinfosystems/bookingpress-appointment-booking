<?php
/**
 * PayPalGateway — PayPal on the gateway-agnostic contract.
 *
 * Ported from `PaymentService::paypal_validate() / paypal_confirm() /
 * paypal_redirect_prepare() / paypal_ipn()`. Behaviour is intended to be
 * byte-identical; what changes is that PayPal no longer OWNS the payment
 * contract, it merely implements it.
 *
 * WHAT WAS REMOVED IN THE PORT
 * ----------------------------
 * The old implementation did all of this itself. None of it is here, because
 * all of it is the orchestrator's now, for every gateway:
 *
 *   - staging the booking (`$submission->submit()`)
 *   - deciding the amount (reading `bookingpress_paid_amount`, then asking
 *     `complete_payment_payable_for_entry()` for a second opinion)
 *   - the entry-token check
 *   - the zero-amount guard ("Service price must be more than 0")
 *   - calling `finalize_booking()`
 *   - IPN idempotency and the amount comparison
 *
 * What remains is PayPal: OAuth, the Orders v2 round trip, the webscr form,
 * and IPN authentication.
 *
 * TWO MODES, ONE GATEWAY
 * ----------------------
 *   `popup`    — REST v2 Orders API + JS SDK Smart Buttons. `prepare()` creates
 *                an order and hands back its id; the SDK captures; `confirm()`
 *                re-fetches the order server-side to verify.
 *   `redirect` — PayPal Standard. `prepare()` returns an auto-submitting form
 *                POSTing to `cgi-bin/webscr`; settlement arrives later by IPN.
 *
 * AMOUNT FORMATTING IS A PAYPAL QUIRK, NOT A MONEY ONE
 * ----------------------------------------------------
 * PayPal treats HUF, JPY and TWD as zero-decimal, which is NOT what ISO 4217
 * says (HUF and TWD both have minor units). {@see Money} is deliberately
 * ISO-correct, so the PayPal-specific table lives here in
 * {@see paypal_amount_value()} — exactly the kind of thing that belongs to a
 * gateway and nowhere else.
 *
 * @package BookingPress\Vue3\Payments\Gateways
 */

namespace BookingPress\Vue3\Payments\Gateways;

use BookingPress\Vue3\Payments\Capabilities;
use BookingPress\Vue3\Payments\Contracts\PaymentGatewayInterface;
use BookingPress\Vue3\Payments\Money;
use BookingPress\Vue3\Payments\PaymentReference;
use BookingPress\Vue3\Payments\PaymentRequest;
use BookingPress\Vue3\Payments\PaymentResult;
use BookingPress\Vue3\Payments\PrepareResult;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PayPalGateway implements PaymentGatewayInterface {

	/** PayPal's own zero-decimal list. Differs from ISO 4217 — see class docblock. */
	private static $paypal_zero_decimal = array( 'HUF', 'JPY', 'TWD' );

	/** Currencies PayPal bills with three decimals. */
	private static $paypal_three_decimal = array( 'BHD', 'IQD', 'JOD', 'KWD', 'LYD', 'OMR', 'TND' );

	/**
	 * Fallback copy of the 25 currencies PayPal accepts.
	 *
	 * The live list comes from `bookingpress_paypal_supported_currency_list()`;
	 * this mirror exists so the gateway keeps working if that Vue 2-era class is
	 * retired.
	 *
	 * @var string[]
	 */
	private static $supported_currencies = array(
		'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY',
		'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD',
		'SEK', 'CHF', 'THB', 'USD', 'CNY',
	);

	/** @var SettingsRepository */
	private $settings;

	/**
	 * Per-request memo of fetched orders, keyed by PayPal order id.
	 *
	 * `resolve_reference_key()` and `confirm()` both need the verified order;
	 * without this the confirm leg would pay for a second OAuth + GET.
	 *
	 * @var array
	 */
	private $order_memo = array();

	/**
	 * @param SettingsRepository|null $settings
	 */
	public function __construct( $settings = null ) {
		$this->settings = ( $settings instanceof SettingsRepository ) ? $settings : new SettingsRepository();
	}

	// ------------------------------------------------------------------
	// Identity
	// ------------------------------------------------------------------

	/** @return string */
	public function get_id() {
		return 'paypal';
	}

	/** @return string */
	public function get_api_version() {
		return Capabilities::API_VERSION;
	}

	/**
	 * Flow shape depends on the merchant's configured mode, so the declaration
	 * is dynamic. A site on Standard/redirect advertises REDIRECT + WEBHOOK
	 * (settlement is by IPN); a site on Smart Buttons advertises POPUP.
	 *
	 * @return string[]
	 */
	public function get_capabilities() {
		$caps = array(
			Capabilities::SEALED_AMOUNT,
			Capabilities::PARTIAL_PAYMENT,
			Capabilities::SHARED_ORDER,
		);

		if ( 'popup' === $this->method_type() ) {
			$caps[] = Capabilities::POPUP;
		} else {
			$caps[] = Capabilities::REDIRECT;
			$caps[] = Capabilities::WEBHOOK;
		}

		return $caps;
	}

	/**
	 * Enabled + credentialled for the configured mode.
	 *
	 * Popup needs client id/secret; Standard needs the merchant email. Legacy
	 * checked these late and threw mid-flow; checking here means an
	 * unconfigured PayPal simply is not offered.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		if ( ! $this->is_truthy( $this->setting( 'paypal_payment' ) ) ) {
			return false;
		}

		if ( 'popup' === $this->method_type() ) {
			return '' !== $this->setting( 'paypal_client_id' ) && '' !== $this->setting( 'paypal_client_secret' );
		}

		return '' !== trim( $this->setting( 'paypal_merchant_email' ) );
	}

	/**
	 * @param string $context_id
	 *
	 * @return array
	 */
	public function get_descriptor( $context_id ) {
		$customize = new CustomizeRepository();
		$label     = $customize->get( 'paypal_text', CustomizeRepository::GROUP_BOOKING_FORM, 0 );

		return array(
			'id'     => $this->get_id(),
			'label'  => $label ? $label : __( 'PayPal', 'bookingpress-appointment-booking' ),
			'icon'   => '',
			'mode'   => $this->method_type(),
			'client' => array(
				// Public. The secret never leaves the server.
				'client_id' => $this->setting( 'paypal_client_id' ),
				'mode'      => $this->method_type(),
				'sandbox'   => $this->is_sandbox(),
			),
			'assets' => array(),
		);
	}

	// ------------------------------------------------------------------
	// Prepare
	// ------------------------------------------------------------------

	/**
	 * @param PaymentRequest $request
	 *
	 * @return PrepareResult
	 *
	 * @throws \RuntimeException
	 */
	public function prepare( PaymentRequest $request ) {
		// Reject an unsupported currency HERE rather than letting PayPal answer
		// with a raw `CURRENCY_NOT_SUPPORTED` business-validation blob. The Vue 2
		// path checked this before submitting; the Vue 3 path never did, so a
		// site priced in (say) NAD got PayPal's own error text surfaced to the
		// customer.
		$this->assert_currency_supported( $request->get_amount() );

		if ( 'popup' === $this->method_type() ) {
			return $this->prepare_popup( $request );
		}
		return $this->prepare_redirect( $request );
	}

	/**
	 * PayPal accepts 25 currencies. Anything else cannot be charged at all.
	 *
	 * @param Money $amount
	 *
	 * @return void
	 *
	 * @throws \RuntimeException
	 */
	private function assert_currency_supported( Money $amount ) {
		$currency = $amount->get_currency();

		if ( in_array( $currency, $this->supported_currencies(), true ) ) {
			return;
		}

		throw new \RuntimeException(
			sprintf(
				/* translators: %s: ISO currency code. */
				__( 'PayPal does not support payments in %s. Please choose another payment method, or change the store currency.', 'bookingpress-appointment-booking' ),
				$currency
			)
		);
	}

	/**
	 * Live supported-currency list, falling back to the local mirror.
	 *
	 * @return string[]
	 */
	private function supported_currencies() {
		global $bookingpress_payment_gateways;

		if ( is_object( $bookingpress_payment_gateways )
			&& method_exists( $bookingpress_payment_gateways, 'bookingpress_paypal_supported_currency_list' ) ) {
			$list = $bookingpress_payment_gateways->bookingpress_paypal_supported_currency_list();
			if ( is_array( $list ) && ! empty( $list ) ) {
				return array_map( 'strtoupper', $list );
			}
		}

		return self::$supported_currencies;
	}

	/**
	 * Smart Buttons: create a v2 order whose `reference_id` is our correlation
	 * key, so `confirm()` can recover the purchase from PayPal's own verified
	 * response rather than from the browser.
	 *
	 * @param PaymentRequest $request
	 *
	 * @return PrepareResult
	 *
	 * @throws \RuntimeException
	 */
	private function prepare_popup( PaymentRequest $request ) {
		$client_id     = $this->setting( 'paypal_client_id' );
		$client_secret = $this->setting( 'paypal_client_secret' );

		if ( '' === $client_id ) {
			throw new \RuntimeException( __( 'Please configure PayPal Client ID', 'bookingpress-appointment-booking' ) );
		}
		if ( '' === $client_secret ) {
			throw new \RuntimeException( __( 'Please Configure PayPal Client Secret', 'bookingpress-appointment-booking' ) );
		}

		$reference = $request->get_reference();
		$amount    = $request->get_amount();

		$access_token = $this->oauth_token( $client_id, $client_secret );

		$body = array(
			'intent'         => 'CAPTURE',
			'purchase_units' => array(
				array(
					// Legacy stored the bare entry_id here. We store the full
					// correlation key so a future context is routable without a
					// PayPal release. `resolve_reference_key()` accepts both.
					'reference_id' => $reference->get_correlation_key(),
					'description'  => $this->clamp_description( $reference->get_description() ),
					'amount'       => array(
						'currency_code' => $amount->get_currency(),
						'value'         => $this->paypal_amount_value( $amount ),
					),
				),
			),
			'payment_source' => array(
				'paypal' => array(
					'experience_context' => array(
						'shipping_preference' => 'NO_SHIPPING',
					),
				),
			),
		);

		$response = wp_remote_post(
			$this->api_base() . '/v2/checkout/orders',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		$raw    = wp_remote_retrieve_body( $response );
		$data   = json_decode( $raw, true );
		$order  = isset( $data['id'] ) ? (string) $data['id'] : '';

		if ( '' === $order ) {
			throw new \RuntimeException( $this->describe_order_failure( $data, $raw, (int) wp_remote_retrieve_response_code( $response ) ) );
		}

		return PrepareResult::client_action(
			array(
				'order_id'   => $order,
				'mode'       => 'popup',
				'client_id'  => $client_id,
				'cancel_url' => $request->get_cancel_url(),
			)
		);
	}

	/**
	 * PayPal Standard: the auto-submitting `_xclick` form.
	 *
	 * Settlement is asynchronous — the booking is finalized by the IPN, not by
	 * the browser coming back. `custom` carries the correlation key so
	 * {@see handle_webhook()} can route it home.
	 *
	 * @param PaymentRequest $request
	 *
	 * @return PrepareResult
	 *
	 * @throws \RuntimeException
	 */
	private function prepare_redirect( PaymentRequest $request ) {
		$merchant_email = trim( $this->setting( 'paypal_merchant_email' ) );
		if ( '' === $merchant_email ) {
			throw new \RuntimeException( __( 'Please configure merchant email address', 'bookingpress-appointment-booking' ) );
		}

		$reference = $request->get_reference();
		$amount    = $request->get_amount();
		$customer  = $reference->get_customer();

		$fields = array(
			'cmd'           => '_xclick',
			'business'      => $merchant_email,
			// Legacy pinned 2 decimals here regardless of currency. Kept, but
			// routed through the PayPal table so a JPY/HUF site stops sending
			// a fractional amount PayPal would reject.
			'amount'        => $this->paypal_amount_value( $amount ),
			'currency_code' => $amount->get_currency(),
			'item_name'     => $reference->get_description(),
			'item_number'   => '1',
			'custom'        => $reference->get_correlation_key(),
			'notify_url'    => $request->get_webhook_url(),
			'return'        => $request->get_return_url(),
			'cancel_return' => $request->get_cancel_url(),
			'rm'            => '2',
			'no_shipping'   => '1',
			'lc'            => 'en_US',
			'charset'       => 'UTF-8',
			'page_style'    => 'primary',
			'on0'           => 'user_email',
			'os0'           => isset( $customer['email'] ) ? (string) $customer['email'] : '',
		);

		return PrepareResult::form_post( $this->webscr_endpoint(), $fields );
	}

	// ------------------------------------------------------------------
	// Confirm
	// ------------------------------------------------------------------

	/**
	 * PayPal is authoritative about which purchase was paid — read the
	 * reference back from a server-side re-fetch, never from the browser.
	 *
	 * @param array $client_payload
	 *
	 * @return string|null
	 */
	public function resolve_reference_key( array $client_payload ) {
		$order_id = $this->order_id_from_payload( $client_payload );
		if ( '' === $order_id ) {
			return null;
		}

		try {
			$order = $this->fetch_order( $order_id );
		} catch ( \Throwable $e ) {
			return null;
		}

		$reference_id = isset( $order['purchase_units'][0]['reference_id'] )
			? (string) $order['purchase_units'][0]['reference_id']
			: '';

		if ( '' === $reference_id ) {
			return null;
		}

		// BACK-COMPAT: orders created before this port carry a bare entry_id in
		// `reference_id`. Those belong to the booking form by definition — the
		// only context that existed then.
		if ( false === strpos( $reference_id, ':' ) ) {
			return 'booking_form:' . $reference_id;
		}

		return $reference_id;
	}

	/**
	 * @param PaymentRequest $request
	 *
	 * @return PaymentResult
	 *
	 * @throws \RuntimeException
	 */
	public function confirm( PaymentRequest $request ) {
		global $bookingpress_debug_payment_log_id;

		// Legacy parity: log the popup response BEFORE any validation, so a row
		// exists even when the verification below fails.
		do_action(
			'bookingpress_payment_log_entry',
			'paypal',
			'payment popup response data',
			'bookingpress pro',
			$request->get_client_payload(),
			$bookingpress_debug_payment_log_id
		);

		$order_id = $this->order_id_from_payload( $request->get_client_payload() );
		if ( '' === $order_id ) {
			throw new \RuntimeException( __( 'Missing PayPal order id.', 'bookingpress-appointment-booking' ) );
		}

		$order = $this->fetch_order( $order_id );
		if ( ! is_array( $order ) || empty( $order ) ) {
			throw new \RuntimeException( __( 'Could not validate PayPal order.', 'bookingpress-appointment-booking' ) );
		}

		$status = isset( $order['status'] ) ? (string) $order['status'] : '';
		if ( 'COMPLETED' !== $status ) {
			throw new \RuntimeException(
				sprintf(
					/* translators: %s: PayPal order status. */
					__( 'Sorry, payment is not successed with the paypal. (status: %s)', 'bookingpress-appointment-booking' ),
					$status
				)
			);
		}

		$unit           = isset( $order['purchase_units'][0] ) ? (array) $order['purchase_units'][0] : array();
		$capture        = isset( $unit['payments']['captures'][0] ) ? (array) $unit['payments']['captures'][0] : array();
		$transaction_id = isset( $capture['id'] ) ? (string) $capture['id'] : '';
		$capture_status = isset( $capture['status'] ) ? (string) $capture['status'] : '';

		$paid_value    = isset( $unit['amount']['value'] ) ? (float) $unit['amount']['value'] : 0.0;
		$paid_currency = isset( $unit['amount']['currency_code'] ) ? (string) $unit['amount']['currency_code'] : $request->get_amount()->get_currency();

		return new PaymentResult(
			array(
				'status'         => ( 'PENDING' === $capture_status )
					? PaymentResult::STATUS_PENDING
					: PaymentResult::STATUS_PAID,
				'gateway_id'     => $this->get_id(),
				'transaction_id' => $transaction_id,
				'amount'         => new Money( $paid_value, $paid_currency ),
				'raw'            => $order,
			)
		);
	}

	// ------------------------------------------------------------------
	// IPN
	// ------------------------------------------------------------------

	/** @return bool */
	public function needs_webhook() {
		return true;
	}

	/**
	 * PayPal Standard IPN.
	 *
	 * Authenticity first: re-post verbatim with `cmd=_notify-validate` and
	 * require `VERIFIED` before reading anything. Then the PayPal-specific
	 * business rules (receiver email, payment status). The amount check,
	 * idempotency and finalize are the orchestrator's.
	 *
	 * @param array  $payload
	 * @param array  $headers
	 * @param string $raw_body
	 *
	 * @return array|null
	 */
	public function handle_webhook( array $payload, array $headers, $raw_body ) {
		global $bookingpress_debug_payment_log_id;

		do_action( 'bookingpress_payment_log_entry', 'paypal', 'legacy ipn received', 'bookingpress', $payload, $bookingpress_debug_payment_log_id );

		if ( empty( $payload ) ) {
			return null;
		}

		if ( ! $this->ipn_is_verified( $payload ) ) {
			do_action( 'bookingpress_payment_log_entry', 'paypal', 'legacy ipn NOT verified', 'bookingpress', $payload, $bookingpress_debug_payment_log_id );
			return null;
		}

		// Receiver must be this merchant — guards against a replayed IPN from
		// an unrelated PayPal account.
		$merchant = strtolower( trim( $this->setting( 'paypal_merchant_email' ) ) );
		$receiver = isset( $payload['receiver_email'] ) ? strtolower( trim( (string) $payload['receiver_email'] ) ) : '';
		if ( '' !== $merchant && $receiver !== $merchant ) {
			do_action(
				'bookingpress_payment_log_entry',
				'paypal',
				'legacy ipn receiver mismatch',
				'bookingpress',
				array(
					'expected' => $merchant,
					'got'      => $receiver,
				),
				$bookingpress_debug_payment_log_id
			);
			return null;
		}

		$payment_status = isset( $payload['payment_status'] ) ? (string) $payload['payment_status'] : '';
		if ( 'Completed' !== $payment_status && 'Pending' !== $payment_status ) {
			// Failed / refunded / denied — authentic, but not something to
			// finalize. Returning null makes the orchestrator answer 200 and act
			// on nothing, which is what PayPal wants.
			return null;
		}

		$custom = isset( $payload['custom'] ) ? (string) $payload['custom'] : '';
		if ( '' === $custom ) {
			return null;
		}

		// BACK-COMPAT: in-flight payments created before this port carry a bare
		// entry_id. Anything mid-checkout across the upgrade must still settle.
		$correlation_key = ( false === strpos( $custom, ':' ) ) ? 'booking_form:' . $custom : $custom;

		$gross    = isset( $payload['mc_gross'] ) ? (float) $payload['mc_gross'] : 0.0;
		$currency = isset( $payload['mc_currency'] ) ? (string) $payload['mc_currency'] : '';

		return array(
			'correlation_key' => $correlation_key,
			'result'          => new PaymentResult(
				array(
					'status'         => ( 'Pending' === $payment_status )
						? PaymentResult::STATUS_PENDING
						: PaymentResult::STATUS_PAID,
					'gateway_id'     => $this->get_id(),
					'transaction_id' => isset( $payload['txn_id'] ) ? (string) $payload['txn_id'] : '',
					'amount'         => new Money( $gross, $currency ),
					'raw'            => $payload,
				)
			),
		);
	}

	// ------------------------------------------------------------------
	// PayPal internals
	// ------------------------------------------------------------------

	/**
	 * Amount as PayPal wants it: plain numeric string, '.' separator, no
	 * grouping, PayPal's own decimal rules (NOT ISO 4217 — see class docblock).
	 *
	 * PRECISION GUARD — deliberate behaviour change
	 * ---------------------------------------------
	 * The decimal count comes from `price_number_of_decimals`, which is a
	 * DISPLAY preference that the original code also used for billing. On a site
	 * with that set to 0, a 10.50 booking was sent to PayPal as "10": the
	 * customer was charged 10, the booking recorded 10.50, and nothing
	 * complained.
	 *
	 * The orchestrator now compares the captured amount against the sealed one,
	 * so that silent under-charge would instead surface AFTER the money moved —
	 * the worst possible moment. So we catch it here, BEFORE the order is
	 * created: if rounding to the configured precision would change the amount,
	 * refuse with an actionable message rather than charge the wrong number.
	 *
	 * @param Money $amount
	 *
	 * @return string
	 *
	 * @throws \RuntimeException When the configured precision cannot represent
	 *                           the amount exactly.
	 */
	private function paypal_amount_value( Money $amount ) {
		$currency = $amount->get_currency();

		$configured = (int) $this->settings->get( 'price_number_of_decimals', SettingsRepository::GROUP_PAYMENT, 2 );

		if ( in_array( $currency, self::$paypal_zero_decimal, true ) ) {
			$decimals = 0;
		} elseif ( in_array( $currency, self::$paypal_three_decimal, true ) ) {
			$decimals = min( $configured, 3 );
		} else {
			$decimals = min( $configured, 2 );
		}

		$value = number_format( $amount->get_amount(), $decimals, '.', '' );

		if ( abs( (float) $value - $amount->get_amount() ) <= 0.0001 ) {
			return $value;
		}

		// The amount cannot be expressed at PayPal's precision for this
		// currency. TWO DIFFERENT CAUSES, two different fixes — the earlier
		// single message told a TWD merchant to raise a decimals setting that
		// cannot help them.
		if ( in_array( $currency, self::$paypal_zero_decimal, true ) ) {
			throw new \RuntimeException(
				sprintf(
					/* translators: 1: currency code, 2: payable amount. */
					__( 'PayPal does not accept fractional amounts in %1$s, so %2$s cannot be charged. Use whole-number prices for this currency, or choose another payment method.', 'bookingpress-appointment-booking' ),
					$currency,
					$amount->to_decimal_string()
				)
			);
		}

		throw new \RuntimeException(
			sprintf(
				/* translators: 1: payable amount, 2: currency code, 3: decimal places PayPal allows. */
				__( 'The payable amount %1$s cannot be charged in %2$s, which PayPal bills to %3$d decimal places. Adjust the price or the "Number of decimals" payment setting so the total is exact.', 'bookingpress-appointment-booking' ),
				$amount->to_decimal_string(),
				$currency,
				$decimals
			)
		);
	}

	/**
	 * `id` out of the SDK capture body. Accepts the object or a JSON string —
	 * legacy accepted both under `bookingpress_payment_res`.
	 *
	 * @param array $client_payload
	 *
	 * @return string
	 */
	private function order_id_from_payload( array $client_payload ) {
		$res = isset( $client_payload['bookingpress_payment_res'] ) ? $client_payload['bookingpress_payment_res'] : null;

		if ( null === $res && isset( $client_payload['paypal_order_id'] ) ) {
			return (string) $client_payload['paypal_order_id'];
		}

		if ( is_string( $res ) ) {
			$res = json_decode( stripslashes_deep( $res ), true );
		}

		if ( ! is_array( $res ) ) {
			return '';
		}

		return isset( $res['id'] ) ? (string) $res['id'] : '';
	}

	/**
	 * GET /v2/checkout/orders/{id}, memoized per request.
	 *
	 * @param string $order_id
	 *
	 * @return array
	 *
	 * @throws \RuntimeException
	 */
	private function fetch_order( $order_id ) {
		$order_id = (string) $order_id;
		if ( isset( $this->order_memo[ $order_id ] ) ) {
			return $this->order_memo[ $order_id ];
		}

		$client_id     = $this->setting( 'paypal_client_id' );
		$client_secret = $this->setting( 'paypal_client_secret' );
		if ( '' === $client_id || '' === $client_secret ) {
			throw new \RuntimeException( __( 'PayPal credentials are not configured.', 'bookingpress-appointment-booking' ) );
		}

		$access_token = $this->oauth_token( $client_id, $client_secret );

		$response = wp_remote_get(
			$this->api_base() . '/v2/checkout/orders/' . rawurlencode( $order_id ),
			array(
				'headers' => array( 'Authorization' => 'Bearer ' . $access_token ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_array( $body ) && ! empty( $body['error'] ) ) {
			$msg = isset( $body['error_description'] ) ? (string) $body['error_description'] : (string) $body['error'];
			throw new \RuntimeException( $msg );
		}

		$body = is_array( $body ) ? $body : array();

		$this->order_memo[ $order_id ] = $body;
		return $body;
	}

	/**
	 * @param string $client_id
	 * @param string $client_secret
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	private function oauth_token( $client_id, $client_secret ) {
		$response = wp_remote_post(
			$this->api_base() . '/v1/oauth2/token',
			array(
				'headers' => array(
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- HTTP Basic auth, per PayPal's spec.
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
				),
				'body'    => array( 'grant_type' => 'client_credentials' ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		$auth = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_array( $auth ) && ! empty( $auth['error'] ) ) {
			$msg = isset( $auth['error_description'] ) ? (string) $auth['error_description'] : (string) $auth['error'];
			throw new \RuntimeException( $msg );
		}
		if ( empty( $auth ) || empty( $auth['access_token'] ) ) {
			throw new \RuntimeException( __( 'PayPal authentication failed.', 'bookingpress-appointment-booking' ) );
		}

		return (string) $auth['access_token'];
	}

	/**
	 * Re-post an IPN with `cmd=_notify-validate` and require `VERIFIED`.
	 *
	 * @param array $payload
	 *
	 * @return bool
	 */
	private function ipn_is_verified( array $payload ) {
		$verify_url = $this->is_sandbox()
			? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr'
			: 'https://ipnpb.paypal.com/cgi-bin/webscr';

		$response = wp_remote_post(
			$verify_url,
			array(
				'method'      => 'POST',
				'timeout'     => 30,
				'httpversion' => '1.1',
				'headers'     => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
					'Connection'   => 'Close',
				),
				'body'        => array_merge( array( 'cmd' => '_notify-validate' ), $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return 'VERIFIED' === trim( (string) wp_remote_retrieve_body( $response ) );
	}

	/**
	 * PayPal's top-level `message` is useless on its own; the actionable reason
	 * is in `details[].issue`. Surface both, and log the raw body so a 422
	 * (CURRENCY_NOT_SUPPORTED, DECIMAL_PRECISION) is diagnosable.
	 *
	 * @param mixed  $data
	 * @param string $raw
	 * @param int    $http_status
	 *
	 * @return string
	 */
	private function describe_order_failure( $data, $raw, $http_status ) {
		$message = ( is_array( $data ) && isset( $data['message'] ) )
			? (string) $data['message']
			: __( 'Failed to create PayPal order', 'bookingpress-appointment-booking' );

		if ( is_array( $data ) && ! empty( $data['details'] ) && is_array( $data['details'] ) ) {
			$parts = array();
			foreach ( $data['details'] as $detail ) {
				$issue = isset( $detail['issue'] ) ? (string) $detail['issue'] : '';
				$desc  = isset( $detail['description'] ) ? (string) $detail['description'] : '';
				$field = isset( $detail['field'] ) ? (string) $detail['field'] : '';
				$line  = trim( $issue . ( '' !== $desc ? ': ' . $desc : '' ) . ( '' !== $field ? ' [' . $field . ']' : '' ) );
				if ( '' !== $line ) {
					$parts[] = $line;
				}
			}
			if ( ! empty( $parts ) ) {
				$message .= ' (' . implode( '; ', $parts ) . ')';
			}
		}

		global $bookingpress_debug_payment_log_id;
		do_action(
			'bookingpress_payment_log_entry',
			'paypal',
			'create-order failed',
			'bookingpress',
			array(
				'http_status' => (int) $http_status,
				'body'        => $raw,
			),
			$bookingpress_debug_payment_log_id
		);

		return $message;
	}

	/**
	 * PayPal rejects an over-long description on some currencies/locales.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function clamp_description( $text ) {
		$text = trim( wp_strip_all_tags( (string) $text ) );
		if ( '' === $text ) {
			$text = __( 'Appointment Booking', 'bookingpress-appointment-booking' );
		}
		return ( function_exists( 'mb_substr' ) ) ? mb_substr( $text, 0, 127 ) : substr( $text, 0, 127 );
	}

	// ------------------------------------------------------------------
	// Settings helpers
	// ------------------------------------------------------------------

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	private function setting( $key ) {
		return (string) $this->settings->get( $key, SettingsRepository::GROUP_PAYMENT, '' );
	}

	/**
	 * `popup` (Smart Buttons) or `redirect` (Standard). Anything unrecognised
	 * falls back to redirect, matching legacy.
	 *
	 * @return string
	 */
	private function method_type() {
		return ( 'popup' === $this->setting( 'paypal_payment_method_type' ) ) ? 'popup' : 'redirect';
	}

	/**
	 * NOTE the deliberate asymmetry, preserved from legacy: the REST API treats
	 * only the literal `sandbox` as sandbox, while the webscr/IPN side treats
	 * anything that is not `live` as sandbox. A site with an empty/unset mode
	 * therefore hits LIVE api-m but SANDBOX webscr. Left as-is so this port
	 * changes no behaviour; worth fixing separately, with a settings migration.
	 *
	 * @return bool
	 */
	private function is_sandbox() {
		return 'live' !== $this->setting( 'paypal_payment_mode' );
	}

	/** @return string */
	private function api_base() {
		return ( 'sandbox' === $this->setting( 'paypal_payment_mode' ) )
			? 'https://api-m.sandbox.paypal.com'
			: 'https://api-m.paypal.com';
	}

	/** @return string */
	private function webscr_endpoint() {
		$host = $this->is_sandbox() ? 'www.sandbox.paypal.com' : 'www.paypal.com';
		return 'https://' . $host . '/cgi-bin/webscr';
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	private function is_truthy( $value ) {
		$value = strtolower( (string) $value );
		return ( 'true' === $value || '1' === $value );
	}
}

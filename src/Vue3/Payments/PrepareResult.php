<?php
/**
 * PrepareResult — the normalized answer to "how does the browser pay this?".
 *
 * Today every gateway invents its own response shape and its own matching JS
 * to consume it: Stripe returns `{client_secret}` or `{checkout_url}`, PayPal
 * returns `{order_id, paypal_success_url, paypal_cancel_url}` from one method
 * and `{variant, is_redirect, redirect_data}` from another. So every gateway
 * needs bespoke client code per form, and core cannot drive the handshake
 * generically.
 *
 * Four shapes cover every gateway in the catalogue. The Lite client knows all
 * four; a gateway's JS then only does SDK work, and is written once for all
 * contexts instead of once per form.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PrepareResult {

	/**
	 * Gateway's own JS finishes the payment in-page, then core calls confirm.
	 * Stripe Payment Elements, Square, Braintree, Authorize.net Accept.js.
	 */
	const KIND_CLIENT_ACTION = 'client_action';

	/** Full-page navigation to a hosted page. Stripe Checkout, Mollie, PayPal Standard. */
	const KIND_REDIRECT = 'redirect';

	/**
	 * Auto-submitting HTML form POST — legacy gateways that cannot take a GET
	 * redirect (PayPal Standard webscr, PayU, some regional processors).
	 */
	const KIND_FORM_POST = 'form_post';

	/** Nothing to do client-side; already settled. On-site, zero-amount, 100% gift card. */
	const KIND_SETTLED = 'settled';

	/** @var string */
	private $kind;

	/** @var array Gateway-specific payload consumed by that gateway's JS. */
	private $data;

	/** @var PaymentResult|null Present only for KIND_SETTLED. */
	private $settled_result;

	/**
	 * @param string             $kind
	 * @param array              $data
	 * @param PaymentResult|null $settled_result
	 */
	private function __construct( $kind, array $data = array(), $settled_result = null ) {
		$this->kind           = (string) $kind;
		$this->data           = $data;
		$this->settled_result = $settled_result;
	}

	/**
	 * @param array $data Freeform — e.g. `client_secret`, `publishable_key`,
	 *                    `intent_id`. Reaches the gateway's own JS verbatim.
	 *
	 * @return self
	 */
	public static function client_action( array $data ) {
		return new self( self::KIND_CLIENT_ACTION, $data );
	}

	/**
	 * @param string $url
	 * @param array  $data Optional extras.
	 *
	 * @return self
	 */
	public static function redirect( $url, array $data = array() ) {
		return new self( self::KIND_REDIRECT, array_merge( $data, array( 'url' => (string) $url ) ) );
	}

	/**
	 * @param string $action_url
	 * @param array  $fields Name => value pairs POSTed to the gateway.
	 *
	 * @return self
	 */
	public static function form_post( $action_url, array $fields ) {
		return new self(
			self::KIND_FORM_POST,
			array(
				'action' => (string) $action_url,
				'fields' => $fields,
			)
		);
	}

	/**
	 * @param PaymentResult $result
	 *
	 * @return self
	 */
	public static function settled( PaymentResult $result ) {
		return new self( self::KIND_SETTLED, array(), $result );
	}

	/** @return string */
	public function get_kind() {
		return $this->kind;
	}

	/** @return array */
	public function get_data() {
		return $this->data;
	}

	/** @return bool */
	public function is_settled() {
		return self::KIND_SETTLED === $this->kind;
	}

	/** @return PaymentResult|null */
	public function get_settled_result() {
		return $this->settled_result;
	}

	/**
	 * Wire format. The `kind` tells the Lite client which of the four
	 * handshakes to run; `data` is handed to the gateway's registered JS.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'kind' => $this->kind,
			'data' => $this->data,
		);
	}
}

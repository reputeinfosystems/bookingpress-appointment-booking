<?php
/**
 * OnSiteGateway — "Pay Locally". The smallest possible conforming gateway.
 *
 * Worth reading first: it shows the entire shape of a gateway in ~100 lines,
 * with none of the SDK noise. Note what is absent — no route registration, no
 * nonce handling, no entry lookup, no amount arithmetic, no `finalize_booking()`
 * call, no envelope filtering, no error mapping. All of that is the
 * orchestrator's, for every gateway.
 *
 * It settles immediately: nothing is collected online, so `prepare()` returns
 * `PrepareResult::settled()` and the orchestrator finalizes straight away. This
 * is the same path a zero-amount booking takes (100% gift card, free service,
 * full discount), which is why that case no longer needs a special branch in
 * every gateway.
 *
 * @package BookingPress\Vue3\Payments\Gateways
 */

namespace BookingPress\Vue3\Payments\Gateways;

use BookingPress\Vue3\Payments\Capabilities;
use BookingPress\Vue3\Payments\Contracts\PaymentGatewayInterface;
use BookingPress\Vue3\Payments\PaymentRequest;
use BookingPress\Vue3\Payments\PaymentResult;
use BookingPress\Vue3\Payments\PrepareResult;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OnSiteGateway implements PaymentGatewayInterface {

	/** @var SettingsRepository */
	private $settings;

	/**
	 * @param SettingsRepository|null $settings
	 */
	public function __construct( $settings = null ) {
		$this->settings = ( $settings instanceof SettingsRepository ) ? $settings : new SettingsRepository();
	}

	/**
	 * Unchanged from the legacy value so existing `bookingpress_payment_gateway`
	 * rows, reports and exports keep resolving.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'on-site';
	}

	/** @return string */
	public function get_api_version() {
		return Capabilities::API_VERSION;
	}

	/**
	 * Settling locally imposes no constraint on WHAT is being paid for, so it
	 * can honour every shape: split orders, partial payments, any context.
	 *
	 * @return string[]
	 */
	public function get_capabilities() {
		return array(
			Capabilities::SEALED_AMOUNT,
			Capabilities::SHARED_ORDER,
			Capabilities::PARTIAL_PAYMENT,
		);
	}

	/** @return bool */
	public function is_enabled() {
		$value = $this->settings->get( 'on_site_payment', SettingsRepository::GROUP_PAYMENT, '' );
		return in_array( (string) $value, array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * @param string $context_id
	 *
	 * @return array
	 */
	public function get_descriptor( $context_id ) {
		$customize = new CustomizeRepository();
		$label     = $customize->get( 'locally_text', CustomizeRepository::GROUP_BOOKING_FORM, 0 );

		return array(
			'id'     => $this->get_id(),
			'label'  => $label ? $label : __( 'Pay Locally', 'bookingpress-appointment-booking' ),
			'icon'   => '',
			'client' => array(),
			'assets' => array(),
		);
	}

	/**
	 * Nothing to collect — hand back a settled result and let the orchestrator
	 * finalize.
	 *
	 * @param PaymentRequest $request
	 *
	 * @return PrepareResult
	 */
	public function prepare( PaymentRequest $request ) {
		return PrepareResult::settled(
			new PaymentResult(
				array(
					'status'         => PaymentResult::STATUS_PENDING,
					'gateway_id'     => $this->get_id(),
					'transaction_id' => '',
					'amount'         => $request->get_amount(),
				)
			)
		);
	}

	/**
	 * Never reached — `prepare()` always settles. Implemented to satisfy the
	 * interface and to fail loudly if the orchestrator ever routes here.
	 *
	 * @param PaymentRequest $request
	 *
	 * @return PaymentResult
	 */
	public function confirm( PaymentRequest $request ) {
		return new PaymentResult(
			array(
				'status'     => PaymentResult::STATUS_PAID,
				'gateway_id' => $this->get_id(),
				'amount'     => $request->get_amount(),
			)
		);
	}

	/**
	 * Nothing remote to consult — the client-supplied reference stands.
	 *
	 * @param array $client_payload
	 *
	 * @return string|null
	 */
	public function resolve_reference_key( array $client_payload ) {
		return null;
	}

	/**
	 * @param array  $payload
	 * @param array  $headers
	 * @param string $raw_body
	 *
	 * @return array|null
	 */
	public function handle_webhook( array $payload, array $headers, $raw_body ) {
		return null;
	}

	/** @return bool */
	public function needs_webhook() {
		return false;
	}
}

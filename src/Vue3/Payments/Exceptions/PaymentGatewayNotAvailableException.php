<?php
/**
 * PaymentGatewayNotAvailableException — the requested gateway cannot serve this
 * context.
 *
 * Three distinct causes, all landing here so the controller can answer with one
 * clean 400 instead of a fatal:
 *
 *   - the gateway add-on is not installed or not registered;
 *   - the merchant has not enabled/configured it;
 *   - it is registered and enabled but lacks a capability this context
 *     requires — e.g. Cart needs `SHARED_ORDER`, Deposit needs
 *     `PARTIAL_PAYMENT`.
 *
 * The third is the interesting one: it is how core ships a new capability
 * without forcing a same-day release of every gateway. An older gateway simply
 * stops being offered where it would not work, and keeps working everywhere
 * else.
 *
 * @package BookingPress\Vue3\Payments\Exceptions
 */

namespace BookingPress\Vue3\Payments\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentGatewayNotAvailableException extends \RuntimeException {

	/** @var string */
	private $gateway_id;

	/** @var string */
	private $context_id;

	/**
	 * @param string $gateway_id
	 * @param string $context_id
	 */
	public function __construct( $gateway_id, $context_id ) {
		$this->gateway_id = (string) $gateway_id;
		$this->context_id = (string) $context_id;

		parent::__construct(
			sprintf(
				'Payment gateway "%s" is not available for context "%s".',
				$this->gateway_id,
				$this->context_id
			)
		);
	}

	/** @return string */
	public function get_gateway_id() {
		return $this->gateway_id;
	}

	/** @return string */
	public function get_context_id() {
		return $this->context_id;
	}

	/** @return string */
	public function get_customer_message() {
		return __( 'That payment method is not available. Please choose another.', 'bookingpress-appointment-booking' );
	}
}

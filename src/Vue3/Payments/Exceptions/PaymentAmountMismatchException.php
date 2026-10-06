<?php
/**
 * PaymentAmountMismatchException — the gateway took a different amount than
 * the one core sealed.
 *
 * This is the failure this whole architecture exists to make impossible-by-
 * construction and loud-when-it-happens anyway. If it is ever thrown, one of:
 *
 *   - a gateway recomputed the amount instead of charging `get_amount()`;
 *   - a gateway mishandled a zero-decimal or three-decimal currency;
 *   - a client tampered and a gateway trusted the client;
 *   - a partial capture or a currency conversion happened gateway-side.
 *
 * In every case no purchase is created, and the message carries both numbers so
 * the debug log says exactly what disagreed.
 *
 * @package BookingPress\Vue3\Payments\Exceptions
 */

namespace BookingPress\Vue3\Payments\Exceptions;

use BookingPress\Vue3\Payments\Money;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentAmountMismatchException extends \RuntimeException {

	/** @var Money */
	private $expected;

	/** @var Money */
	private $actual;

	/** @var string */
	private $correlation_key;

	/**
	 * @param Money  $expected        The sealed amount.
	 * @param Money  $actual          What the gateway reported.
	 * @param string $correlation_key context:reference.
	 */
	public function __construct( Money $expected, Money $actual, $correlation_key = '' ) {
		$this->expected        = $expected;
		$this->actual          = $actual;
		$this->correlation_key = (string) $correlation_key;

		parent::__construct(
			sprintf(
				'Payment amount mismatch for %s: sealed %s %s, gateway reported %s %s.',
				'' !== $correlation_key ? $correlation_key : 'reference',
				$expected->to_decimal_string(),
				$expected->get_currency(),
				$actual->to_decimal_string(),
				$actual->get_currency()
			)
		);
	}

	/** @return Money */
	public function get_expected() {
		return $this->expected;
	}

	/** @return Money */
	public function get_actual() {
		return $this->actual;
	}

	/** @return string */
	public function get_correlation_key() {
		return $this->correlation_key;
	}

	/**
	 * Deliberately vague for the customer — the precise numbers belong in the
	 * debug log, not in a response that a tampering client is reading.
	 *
	 * @return string
	 */
	public function get_customer_message() {
		return __( 'We could not verify the payment amount. You have not been charged for this booking. Please contact us.', 'bookingpress-appointment-booking' );
	}
}

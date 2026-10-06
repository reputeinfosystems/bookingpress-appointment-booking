<?php
/**
 * Money — an immutable (amount, currency) pair with correct minor-unit maths.
 *
 * WHY THIS EXISTS
 * ---------------
 * Every gateway add-on currently re-implements the same two things, wrongly and
 * differently: the zero-decimal currency table and the float→minor-unit
 * conversion. Compare `StripeFeature::is_zero_decimal()` /
 * `::to_minor_unit()` with the decimal table inlined in
 * `PaymentService::paypal_validate()` — two tables, two roundings, one bug
 * surface per gateway. Both move here, once.
 *
 * IMMUTABILITY IS THE POINT
 * -------------------------
 * A Money handed to a gateway is SEALED. There is deliberately no setter, no
 * `add()`, no `with_amount()`. A gateway cannot adjust the charge, because the
 * charge was already decided by the amount pipeline (Tax, Tip, Coupon, Deposit,
 * Gift Card, MyCred, ARMember, Happy Hours, Advance Discount, …) long before the
 * gateway was invoked. See {@see AmountResolver} for that pipeline and
 * {@see PaymentReference} for the seal.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Money {

	/**
	 * Currencies with no minor unit — the amount IS the integer.
	 *
	 * @var string[]
	 */
	private static $zero_decimal = array(
		'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG',
		'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
	);

	/**
	 * Currencies with three decimals.
	 *
	 * @var string[]
	 */
	private static $three_decimal = array( 'BHD', 'IQD', 'JOD', 'KWD', 'LYD', 'OMR', 'TND' );

	/** @var float */
	private $amount;

	/** @var string ISO-4217, uppercase. */
	private $currency;

	/**
	 * @param float  $amount
	 * @param string $currency
	 */
	public function __construct( $amount, $currency ) {
		$this->amount   = round( (float) $amount, 4 );
		$this->currency = strtoupper( trim( (string) $currency ) );
	}

	/**
	 * @param float  $amount
	 * @param string $currency
	 *
	 * @return self
	 */
	public static function of( $amount, $currency ) {
		return new self( $amount, $currency );
	}

	/**
	 * Rebuild from a gateway's integer minor unit (Stripe `amount`, Square
	 * `amount_money`, …) so the comparison in
	 * {@see PaymentOrchestrator::assert_charge_matches()} is apples-to-apples.
	 *
	 * @param int    $minor
	 * @param string $currency
	 *
	 * @return self
	 */
	public static function from_minor( $minor, $currency ) {
		$currency = strtoupper( trim( (string) $currency ) );
		$factor   = pow( 10, self::decimals_for( $currency ) );
		return new self( ( (int) $minor ) / $factor, $currency );
	}

	/** @return float */
	public function get_amount() {
		return $this->amount;
	}

	/** @return string */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Decimal places this currency is charged in.
	 *
	 * @param string $currency
	 *
	 * @return int
	 */
	public static function decimals_for( $currency ) {
		$currency = strtoupper( trim( (string) $currency ) );
		if ( in_array( $currency, self::$zero_decimal, true ) ) {
			return 0;
		}
		if ( in_array( $currency, self::$three_decimal, true ) ) {
			return 3;
		}
		return 2;
	}

	/** @return int */
	public function get_decimals() {
		return self::decimals_for( $this->currency );
	}

	/**
	 * Integer minor unit — what card gateways actually want.
	 *
	 * @return int
	 */
	public function to_minor() {
		return (int) round( $this->amount * pow( 10, $this->get_decimals() ) );
	}

	/**
	 * Plain numeric string: '.' separator, no thousands grouping,
	 * currency-correct precision. Safe to drop into a JSON body regardless of
	 * the site's display locale — the dot-comma leak that broke PayPal's
	 * `amount.value` cannot happen here.
	 *
	 * @return string
	 */
	public function to_decimal_string() {
		return number_format( $this->amount, $this->get_decimals(), '.', '' );
	}

	/**
	 * This amount rounded to its own currency's minor unit.
	 *
	 * A sealed amount MUST be legal tender in its currency. A pricing pipeline
	 * can easily produce one that is not — a percentage discount, a tax rate, a
	 * split deposit, or simply a site whose "number of decimals" display setting
	 * is higher than the currency supports (USD 45.028). Such a value cannot be
	 * charged, refunded or reconciled by anyone, so it is not a valid seal.
	 *
	 * Quantizing at the seal boundary means the amount agreed, the amount
	 * charged and the amount recorded are the same number.
	 *
	 * @return self
	 */
	public function quantized() {
		return new self(
			round( $this->amount, $this->get_decimals() ),
			$this->currency
		);
	}

	/** @return bool */
	public function is_zero() {
		return abs( $this->amount ) < 0.00001;
	}

	/** @return bool */
	public function is_positive() {
		return $this->amount > 0.0;
	}

	/**
	 * Tolerant equality — compares at the currency's own precision, so float
	 * noise from the pipeline never produces a false mismatch.
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function equals( Money $other ) {
		if ( $this->currency !== $other->get_currency() ) {
			return false;
		}
		return $this->to_minor() === $other->to_minor();
	}

	/**
	 * @return array{amount:float, currency:string, minor:int, display:string}
	 */
	public function to_array() {
		return array(
			'amount'   => $this->amount,
			'currency' => $this->currency,
			'minor'    => $this->to_minor(),
			'display'  => $this->to_decimal_string(),
		);
	}
}

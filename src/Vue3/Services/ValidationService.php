<?php
/**
 * ValidationService — evaluates the six action-level gate predicates from §M0.9.C.
 *
 * Stateless apart from a single dependency: the {@see CaptchaService} for
 * `gate.captcha` (because captcha verification touches a transient — there's
 * no pure way to evaluate it). All other gates are pure functions of the
 * `$state` snapshot passed in by the caller.
 *
 * @package BookingPress\Vue3\Services
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Contracts\CaptchaServiceInterface;
use BookingPress\Vue3\Contracts\ValidationServiceInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ValidationService implements ValidationServiceInterface {

	/** Known gate names. */
	const GATE_SERVICE       = 'service';
	const GATE_DATETIME      = 'datetime';
	const GATE_BASIC_DETAILS = 'basic_details';
	const GATE_PAYMENT       = 'payment';
	const GATE_CAPTCHA       = 'captcha';
	const GATE_TERMS         = 'terms';

	/** Known action names. */
	const ACTION_MOVE             = 'move';
	const ACTION_RENDER_SUMMARY   = 'render_summary';
	const ACTION_SUBMIT           = 'submit';
	const ACTION_PAYPAL_VALIDATE  = 'paypal_validate';
	const ACTION_PAYPAL_CONFIRM   = 'paypal_confirm';

	/** @var CaptchaServiceInterface|null */
	private $captcha;

	public function __construct( ?CaptchaServiceInterface $captcha = null ) {
		$this->captcha = $captcha; // May remain null in pure tests.
	}

	/**
	 * @inheritDoc
	 */
	public function check_gate( $gate, array $state ) {
		switch ( (string) $gate ) {
			case self::GATE_SERVICE:
				return self::pure_gate_service( $state );

			case self::GATE_DATETIME:
				return self::pure_gate_datetime( $state );

			case self::GATE_BASIC_DETAILS:
				return self::pure_gate_basic_details( $state );

			case self::GATE_PAYMENT:
				return self::pure_gate_payment( $state );

			case self::GATE_TERMS:
				return self::pure_gate_terms( $state );

			case self::GATE_CAPTCHA:
				if ( null === $this->captcha || ! $this->captcha->is_enabled() ) {
					return true; // Captcha disabled → gate passes trivially.
				}
				$token  = isset( $state['form_data']['captcha_token'] ) ? (string) $state['form_data']['captcha_token'] : '';
				$answer = isset( $state['form_data']['captcha_answer'] ) ? (string) $state['form_data']['captcha_answer'] : '';
				return $this->captcha->verify( $token, $answer );

			default:
				throw new \InvalidArgumentException( sprintf( 'Unknown gate "%s".', (string) $gate ) );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function check_action( $action, array $state ) {
		$required = self::action_gate_map( (string) $action );
		$failed   = array();
		foreach ( $required as $gate ) {
			if ( ! $this->check_gate( $gate, $state ) ) {
				$failed[] = $gate;
			}
		}
		return array(
			'passed'       => empty( $failed ),
			'failed_gates' => $failed,
		);
	}

	/**
	 * Return the gate set for a named action. Used internally and by the
	 * pure tests.
	 *
	 * @param string $action
	 *
	 * @return array<int, string>
	 */
	public static function action_gate_map( $action ) {
		switch ( (string) $action ) {
			case self::ACTION_MOVE:
				return array(); // Step-level entry_gates handle move-readiness.

			case self::ACTION_RENDER_SUMMARY:
				return array( self::GATE_SERVICE, self::GATE_DATETIME );

			case self::ACTION_SUBMIT:
			case self::ACTION_PAYPAL_VALIDATE:
			case self::ACTION_PAYPAL_CONFIRM:
				return array(
					self::GATE_SERVICE,
					self::GATE_DATETIME,
					self::GATE_BASIC_DETAILS,
					self::GATE_PAYMENT,
					self::GATE_CAPTCHA,
					self::GATE_TERMS,
				);

			default:
				return array( self::GATE_SERVICE, self::GATE_DATETIME );
		}
	}

	// -----------------------------------------------------------------------
	// Pure predicate helpers — duplicated under `pure_gate_*` names so the
	// pure-logic test harness can call them without instantiating the class.
	// -----------------------------------------------------------------------

	/**
	 * gate.service — selected_service exists in services payload.
	 *
	 * @param array $state
	 *
	 * @return bool
	 */
	public static function pure_gate_service( array $state ) {
		$selected = isset( $state['form_data']['selected_service'] ) ? (int) $state['form_data']['selected_service'] : 0;
		if ( $selected <= 0 ) {
			return false;
		}
		$services = isset( $state['services'] ) && is_array( $state['services'] ) ? $state['services'] : array();
		foreach ( $services as $row ) {
			$sid = isset( $row['serviceId'] ) ? (int) $row['serviceId'] : 0;
			if ( $sid === $selected ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * gate.datetime — valid date + valid HH:MM start time + appears in loaded payload.
	 *
	 * @param array $state
	 *
	 * @return bool
	 */
	public static function pure_gate_datetime( array $state ) {
		$date  = isset( $state['form_data']['selected_date'] ) ? (string) $state['form_data']['selected_date'] : '';
		$start = isset( $state['form_data']['selected_start_time'] ) ? (string) $state['form_data']['selected_start_time'] : '';
		if ( '' === $date ) {
			return false;
		}
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return false;
		}
		$is_day_service = self::is_selected_day_service( $state );
		if ( ! $is_day_service && '' === $start ) {
			return false;
		}
		if ( $is_day_service ) {
			$payload = isset( $state['timeslot_payload']['working_details'] ) && is_array( $state['timeslot_payload']['working_details'] )
				? $state['timeslot_payload']['working_details']
				: null;
			return null !== $payload && isset( $payload[ $date ] ) && is_array( $payload[ $date ] ) && ! empty( $payload[ $date ] );
		}
		if ( ! preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $start ) ) {
			return false;
		}
		// Anti-tamper: the (date, start) pair must appear in the loaded
		// timeslot payload. The payload shape mirrors §M0.4
		// (`working_details[date] = [{start_time,...},...]`).
		$payload = isset( $state['timeslot_payload']['working_details'] ) && is_array( $state['timeslot_payload']['working_details'] )
			? $state['timeslot_payload']['working_details']
			: null;
		if ( null === $payload ) {
			// No loaded payload at all → the caller didn't hydrate state;
			// fail closed.
			return false;
		}
		if ( ! isset( $payload[ $date ] ) || ! is_array( $payload[ $date ] ) ) {
			return false;
		}
		$normalised_start = substr( $start, 0, 5 ); // 'HH:MM'.
		foreach ( $payload[ $date ] as $slot ) {
			$slot_start = isset( $slot['start_time'] ) ? substr( (string) $slot['start_time'], 0, 5 ) : '';
			if ( $slot_start === $normalised_start ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Resolve the selected service row and check whether it is a day service.
	 *
	 * @param array $state
	 *
	 * @return bool
	 */
	private static function is_selected_day_service( array $state ) {
		$selected = isset( $state['form_data']['selected_service'] ) ? (int) $state['form_data']['selected_service'] : 0;
		$services = isset( $state['services'] ) && is_array( $state['services'] ) ? $state['services'] : array();
		foreach ( $services as $row ) {
			$sid = isset( $row['serviceId'] ) ? (int) $row['serviceId'] : 0;
			if ( $sid === $selected ) {
				return DayServiceHelper::is_day_service( $row );
			}
		}
		return DayServiceHelper::is_day_unit(
			isset( $state['form_data']['selected_service_duration_unit'] )
				? $state['form_data']['selected_service_duration_unit']
				: ''
		);
	}

	/**
	 * gate.basic_details — every required field has a non-empty value.
	 *
	 * @param array $state
	 *
	 * @return bool
	 */
	public static function pure_gate_basic_details( array $state ) {
		$fields = isset( $state['customer_form_fields'] ) && is_array( $state['customer_form_fields'] )
			? $state['customer_form_fields']
			: array();
		$values = isset( $state['form_data'] ) && is_array( $state['form_data'] ) ? $state['form_data'] : array();

		foreach ( $fields as $field ) {
			if ( empty( $field['fieldRequired'] ) ) {
				continue;
			}
			$vmodel = isset( $field['vModelValue'] )
				? (string) $field['vModelValue']
				: ( isset( $field['fieldName'] ) ? (string) $field['fieldName'] : '' );
			if ( '' === $vmodel ) {
				continue;
			}
			$value = isset( $values[ $vmodel ] ) ? $values[ $vmodel ] : null;
			if ( null === $value || '' === $value || array() === $value ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * gate.payment — selected payment method matches one of the enabled gateways.
	 *
	 * @param array $state
	 *
	 * @return bool
	 */
	public static function pure_gate_payment( array $state ) {
		$selected = isset( $state['form_data']['selected_payment_method'] ) ? (string) $state['form_data']['selected_payment_method'] : '';
		if ( '' === $selected ) {
			return false;
		}
		$gateways = isset( $state['config']['payment_methods'] ) && is_array( $state['config']['payment_methods'] )
			? $state['config']['payment_methods']
			: array();
		foreach ( $gateways as $gw ) {
			$id = isset( $gw['id'] ) ? (string) $gw['id'] : '';
			if ( $id === $selected ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * gate.terms — when a required terms-and-conditions field is present in
	 * customer_form_fields, the value must be `[true]` (Element-Plus shape).
	 *
	 * Passes trivially when no terms field is in the schema.
	 *
	 * @param array $state
	 *
	 * @return bool
	 */
	public static function pure_gate_terms( array $state ) {
		$fields = isset( $state['customer_form_fields'] ) && is_array( $state['customer_form_fields'] )
			? $state['customer_form_fields']
			: array();
		foreach ( $fields as $field ) {
			$name = isset( $field['fieldName'] ) ? (string) $field['fieldName'] : '';
			if ( 'terms_and_conditions' !== $name ) {
				continue;
			}
			if ( empty( $field['fieldRequired'] ) ) {
				return true;
			}
			$value = isset( $state['form_data']['appointment_terms_conditions'] )
				? $state['form_data']['appointment_terms_conditions']
				: null;
			return is_array( $value ) && ! empty( $value ) && in_array( true, $value, true );
		}
		return true;
	}
}

<?php
/**
 * Thrown by SubmissionService / PaymentService when a server-side gate
 * predicate fails.
 *
 * @package BookingPress\Vue3\Exceptions
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C
 */

namespace BookingPress\Vue3\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runtime — server-side enforcement caught a gate failure.
 *
 * Carries the list of failed gate names so the REST controller can surface
 * `{ code: "bp_v3_readiness_failed", data: { failed_gates: [...] } }`.
 */
class ReadinessFailedException extends \RuntimeException {

	/**
	 * Gate names that failed evaluation.
	 *
	 * @var array<int, string>
	 */
	private $failed_gates = array();

	/**
	 * @param array<int, string> $failed_gates
	 * @param string             $message
	 */
	public function __construct( array $failed_gates = array(), $message = '' ) {
		$this->failed_gates = $failed_gates;
		if ( '' === $message ) {
			$message = sprintf(
				'Readiness check failed for gates: %s',
				implode( ', ', $failed_gates )
			);
		}
		parent::__construct( $message );
	}

	/**
	 * @return array<int, string>
	 */
	public function get_failed_gates() {
		return $this->failed_gates;
	}
}

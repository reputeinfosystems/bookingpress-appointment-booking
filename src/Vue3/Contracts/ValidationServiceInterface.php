<?php
/**
 * ValidationServiceInterface — owns the six action-level gate predicates.
 *
 * @package BookingPress\Vue3\Contracts
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C
 */

namespace BookingPress\Vue3\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Evaluates `gate.service`, `gate.datetime`, `gate.basic_details`,
 * `gate.payment`, `gate.captcha`, `gate.terms` against a form-state snapshot.
 *
 * Consumers:
 * - `useStepNavigation()` (client) for `canEnterStep()`.
 * - `useSubmission()` (client) for `canSubmit()`.
 * - `SubmissionController::submit()` (server) for the authoritative refusal.
 *
 * The same instance is used client-mirror and server-side via a shared
 * predicate utility so the two evaluations cannot drift.
 */
interface ValidationServiceInterface {

	/**
	 * Evaluate a single gate against a state snapshot.
	 *
	 * @param string $gate    One of the gate names defined in §M0.9.C
	 *                        (`service`, `datetime`, `basic_details`, `payment`,
	 *                        `captcha`, `terms`).
	 * @param array  $state   The full `appointment_step_form_data` snapshot
	 *                        plus loaded payload context (services, timeslots,
	 *                        customer_form_fields, config).
	 *
	 * @return bool True iff the gate passes.
	 *
	 * @throws \InvalidArgumentException For unknown gate names.
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C
	 */
	public function check_gate( $gate, array $state );

	/**
	 * Evaluate the set of gates required by a named action.
	 *
	 * @param string $action  Action name (see §M0.9.C readiness map: `move`,
	 *                        `render_summary`, `submit`, `paypal_validate`,
	 *                        `paypal_confirm`).
	 * @param array  $state   State snapshot.
	 *
	 * @return array{ passed: bool, failed_gates: array<int, string> }
	 *
	 * @see docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.C
	 */
	public function check_action( $action, array $state );
}

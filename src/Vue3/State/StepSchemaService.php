<?php
/**
 * StepSchemaService — produce + validate the step descriptor array.
 *
 * Per §M0.9.B:
 *   1. Build the Lite default schema (service → datetime → basic_details → summary).
 *   2. Run the `bookingpress_form_v3_steps` filter (Pro 6.0+ rearranges here).
 *   3. Apply the structural-validation pass (no duplicate ids, all four
 *      reserved IDs present, Summary last, hidden steps preserved,
 *      previous_step/next_step recalculated).
 *   4. On a hard structural violation, log `_doing_it_wrong` and fall back to
 *      the Lite default.
 *
 * @package BookingPress\Vue3\State
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md §M0.9.B
 */

namespace BookingPress\Vue3\State;

use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\CustomizeRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class StepSchemaService {

	/** Reserved Lite step IDs (§M0.9.B + Appendix A). */
	const STEP_SERVICE       = 'service';
	const STEP_DATETIME      = 'datetime';
	const STEP_BASIC_DETAILS = 'basic_details';
	const STEP_SUMMARY       = 'summary';

	const RESERVED_IDS = array(
		self::STEP_SERVICE,
		self::STEP_DATETIME,
		self::STEP_BASIC_DETAILS,
		self::STEP_SUMMARY,
	);

	/** @var CustomizeRepository */
	private $customize;

	public function __construct( ?CustomizeRepository $customize = null ) {
		$this->customize = $customize ?: new CustomizeRepository();
	}

	/**
	 * Build the schema for a render.
	 *
	 * @param array $context Optional context passed to the filter:
	 *                       - `is_service_loaded_from_url` int (§M0.1)
	 *                       - `hide_category_service`      string `'1'` to hide Service step (§M0.9.A)
	 *                       - `selected_service`           int (preselection)
	 *
	 * @return array<int, array<string, mixed>> Ordered step descriptors.
	 */
	public function build( array $context = array() ) {
		$default = $this->default_schema( $context );

		/**
		 * Filter the step descriptors. Pro 6.0+ rearranges here.
		 *
		 * @param array $steps   Default schema.
		 * @param array $context Caller context.
		 */
		$candidate = apply_filters( Hooks::FILTER_STEPS, $default, $context );

		if ( ! is_array( $candidate ) ) {
			$candidate = $default;
		}

		return self::structural_pass( $candidate, $default );
	}

	/**
	 * Produce the Lite default step descriptor list.
	 *
	 * @param array $context
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function default_schema( array $context = array() ) {
		$strings = $this->customize->get_many(
			CustomizeRepository::GROUP_BOOKING_FORM,
			array(
				'service_title',
				'datetime_title',
				'basic_details_title',
				'summary_title',
			)
		);
		$labels = array(
			self::STEP_SERVICE       => '' !== $strings['service_title'] ? $strings['service_title'] : 'Service',
			self::STEP_DATETIME      => '' !== $strings['datetime_title'] ? $strings['datetime_title'] : 'Date & Time',
			self::STEP_BASIC_DETAILS => '' !== $strings['basic_details_title'] ? $strings['basic_details_title'] : 'Your Details',
			self::STEP_SUMMARY       => '' !== $strings['summary_title'] ? $strings['summary_title'] : 'Summary',
		);

		// hide_category_service (§M0.9.A) hides the Service step but keeps
		// it in the array.
		$hide_service = isset( $context['hide_category_service'] ) && '1' === (string) $context['hide_category_service'];

		return array(
			array(
				'id'              => self::STEP_SERVICE,
				'tab_value'       => self::STEP_SERVICE,
				'tab_name'        => $labels[ self::STEP_SERVICE ],
				'tab_icon'        => '',
				'order'           => 1,
				'is_display_step' => $hide_service ? 0 : 1,
				'previous_step'   => '',
				'next_step'       => self::STEP_DATETIME,
				'is_allow_navigate' => 1,
				'entry_gates'     => array(),
				'meta'            => array(),
			),
			array(
				'id'              => self::STEP_DATETIME,
				'tab_value'       => self::STEP_DATETIME,
				'tab_name'        => $labels[ self::STEP_DATETIME ],
				'tab_icon'        => '',
				'order'           => 2,
				'is_display_step' => 1,
				'previous_step'   => self::STEP_SERVICE,
				'next_step'       => self::STEP_BASIC_DETAILS,
				'is_allow_navigate' => 0,
				'entry_gates'     => array( self::STEP_SERVICE ),
				'meta'            => array(),
			),
			array(
				'id'              => self::STEP_BASIC_DETAILS,
				'tab_value'       => self::STEP_BASIC_DETAILS,
				'tab_name'        => $labels[ self::STEP_BASIC_DETAILS ],
				'tab_icon'        => '',
				'order'           => 3,
				'is_display_step' => 1,
				'previous_step'   => self::STEP_DATETIME,
				'next_step'       => self::STEP_SUMMARY,
				'is_allow_navigate' => 0,
				'entry_gates'     => array( self::STEP_SERVICE, self::STEP_DATETIME ),
				'meta'            => array(),
			),
			array(
				'id'              => self::STEP_SUMMARY,
				'tab_value'       => self::STEP_SUMMARY,
				'tab_name'        => $labels[ self::STEP_SUMMARY ],
				'tab_icon'        => '',
				'order'           => 4,
				'is_display_step' => 1,
				'previous_step'   => self::STEP_BASIC_DETAILS,
				'next_step'       => '',
				'is_allow_navigate' => 0,
				'entry_gates'     => array( self::STEP_SERVICE, self::STEP_DATETIME ),
				'meta'            => array(),
			),
		);
	}

	/**
	 * Apply the structural-validation pass. Pure function; used by tests.
	 *
	 * @param array $candidate         Steps returned by the filter.
	 * @param array $fallback_default  The Lite default schema to fall back to.
	 *
	 * @return array<int, array<string, mixed>> Normalised, sorted schema.
	 */
	public static function structural_pass( array $candidate, array $fallback_default ) {
		// 1. No duplicate step IDs.
		$ids = array();
		foreach ( $candidate as $step ) {
			$id = isset( $step['id'] ) ? (string) $step['id'] : '';
			if ( '' === $id || isset( $ids[ $id ] ) ) {
				self::doing_it_wrong( 'Duplicate or empty step id detected; falling back to default schema.' );
				return self::recompute_navigation( $fallback_default );
			}
			$ids[ $id ] = true;
		}

		// 2. All four reserved IDs present.
		foreach ( self::RESERVED_IDS as $reserved ) {
			if ( ! isset( $ids[ $reserved ] ) ) {
				self::doing_it_wrong( sprintf( 'Reserved step id "%s" missing; falling back to default schema.', $reserved ) );
				return self::recompute_navigation( $fallback_default );
			}
		}

		
		// 3. Summary must end up last (after sort by `order`).
		//     Steps carry FRACTIONAL orders while being placed (Staff = follow+0.5,
		//     Location = midpoint), so we must compare as floats. We also return the
		//     spaceship (-1/0/1) rather than a subtraction: usort truncates a float
		//     return to int, which would collapse e.g. `1.25 - 2.0 = -0.75` to 0
		//     ("equal") and scramble the sort. recompute_navigation() restamps clean
		//     sequential integer orders afterwards.
		usort( $candidate, static function ( $a, $b ) {
			$ao = isset( $a['order'] ) ? (float) $a['order'] : 0.0;
			$bo = isset( $b['order'] ) ? (float) $b['order'] : 0.0;
			return $ao <=> $bo;
		} );
		
		$last = end( $candidate );
		if ( ! is_array( $last ) || ! isset( $last['id'] ) || self::STEP_SUMMARY !== (string) $last['id'] ) {
			self::doing_it_wrong( 'Summary step is not last in the sorted schema; falling back to default.' );
			return self::recompute_navigation( $fallback_default );
		}

		// 4. Recompute previous_step / next_step from post-sort order.
		return self::recompute_navigation( $candidate );
	}

	/**
	 * Recompute previous_step / next_step from the array's current order.
	 *
	 * Pure helper exposed for the test runner.
	 *
	 * @param array $steps
	 *
	 * @return array
	 */
	public static function recompute_navigation( array $steps ) {
		$count = count( $steps );
		$out   = array_values( $steps );
		for ( $i = 0; $i < $count; $i++ ) {
			$out[ $i ]['previous_step'] = $i > 0          ? (string) $out[ $i - 1 ]['id'] : '';
			$out[ $i ]['next_step']     = $i < $count - 1 ? (string) $out[ $i + 1 ]['id'] : '';
			$out[ $i ]['order']         = $i + 1;
		}
		return $out;
	}

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	private static function doing_it_wrong( $message ) {
		if ( function_exists( '_doing_it_wrong' ) ) {
			_doing_it_wrong(
				'BookingPress\\Vue3\\State\\StepSchemaService',
				esc_html( $message ),
				'1.1.0'
			);
		}
	}
}

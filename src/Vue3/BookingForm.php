<?php
/**
 * BookingForm — Vue3 greenfield shortcode renderer.
 *
 * **Greenfield — no legacy coupling.** This class must not reference any
 * `BookingPress\frontend\*`, `BookingPress\admin\*`, `core/classes/*`,
 * or `$GLOBALS['BookingPress']` symbol. See LEGACY_BEHAVIOR_CONTRACT.md for
 * the behaviour spec the new path must reproduce.
 *
 * In M1 the render is intentionally a no-op shell. M5 populates initial
 * state, M6 wires the Vue app, M7 flips `[bookingpress_form]` over.
 *
 * @package BookingPress\Vue3
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §1
 */

namespace BookingPress\Vue3;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the per-instance mount-point shell for the Vue3 form.
 *
 * In M1: returns a single `<div>` shell after enqueuing placeholder assets.
 * The shell renders for logged-in admins only (canary guard) — this
 * restriction is removed in M7 when the public `[bookingpress_form]`
 * shortcode is bound here.
 */
class BookingForm {

	/**
	 * Render the shortcode.
	 *
	 * @param array       $atts    Shortcode attrs.
	 * @param string|null $content Shortcode content (unused).
	 * @param string      $tag     Shortcode tag.
	 *
	 * @return string HTML to inject in place of the shortcode.
	 */
	public static function render_shortcode( $atts, $content = null, $tag = '' ) {
		unset( $content );

		// M7: the form is public — anyone hitting `[bookingpress_form]` (or
		// the legacy alias `[bookingpress_form_vue3]`) gets the Vue 3
		// renderer. The version gate is enforced by `Routing::init()`
		// before the shortcode is registered; if we got here, the gate
		// already approved.

		$atts = self::sanitize_atts( $atts, $tag );

		$uniq_id = Routing::generate_unique_id();

		// Register + enqueue placeholder assets. In M1 these are no-op
		// modules; M6 swaps in the real ones.
		Assets::register();
		Assets::enqueue_for_render( $uniq_id, $atts );

		// Mount-point shell. Classes + ID format + loader markup match the
		// released `src/view/frontend/appointment_booking_form_vue3.php`
		// template 1:1 so the released CSS applies unchanged AND users see
		// the same BookingPress branded loader while Vue boots.
		//
		// The `data-bp-v3-instance` attribute is kept (alongside the
		// released `data-instance`) so add-ons targeting either selector
		// keep working.
		$loading_text = function_exists( 'esc_html__' )
			? esc_html__( 'Loading booking form', 'bookingpress-appointment-booking' ) . '&hellip;'
			: 'Loading booking form&hellip;';

		return sprintf(
			'<div id="bookingpress-form-vue3-%1$s" class="bpa-frontend-main-container bpa-frontend-main-booking-calendar bpa-frontend-vue3" data-instance="%1$s" data-bp-v3-instance="%1$s">' .
				'<div id="bookingpress-form-vue3-loader-%1$s" class="bpa-back-loader-container bpa-frontend-vue3-loader">' .
					'<div class="bpa-back-loader" role="status" aria-live="polite">' .
						'<span class="screen-reader-text">%2$s</span>' .
					'</div>' .
				'</div>' .
			'</div>',
			esc_attr( $uniq_id ),
			$loading_text
		);
	}

	/**
	 * Sanitize the shortcode attributes against the Lite-known attribute set.
	 *
	 * Same attr names as legacy so M7's flip-over does not break existing
	 * pages. URL-parameter precedence (`s_id`, `bpservice_id`) is handled
	 * in M5's `StateBuilder`, not here.
	 *
	 * @param array  $atts Raw shortcode attrs.
	 * @param string $tag  Shortcode tag — `bookingpress_form` or `bookingpress_form_vue3`.
	 *
	 * @return array{ service: string, category: string, selected_service: string, selected_staff: string }
	 */
	private static function sanitize_atts( $atts, $tag = 'bookingpress_form' ) {
		$defaults = array(
			'service'          => '',
			'category'         => '',
			'selected_service' => '',
			'selected_staff'   => '',
		);
		$atts = shortcode_atts( $defaults, is_array( $atts ) ? $atts : array(), (string) $tag );
		return array_map( 'sanitize_text_field', $atts );
	}
}

<?php
/**
 * BookingPress Vue 3 Shortcode View Template.
 *
 * Rendered by BookingPress_Appointment_Bookings_Vue3::render_shortcode().
 * Emits only the static HTML shell (mount point). All reactive data is
 * delivered via the `wp-script-module-data-bookingpress-form-vue3-loader`
 * JSON island emitted by WordPress from the module data filter.
 *
 * @package BookingPress
 *
 * @var string $uniq_id       Unique instance identifier.
 * @var array  $atts          Sanitized shortcode attributes.
 * @var array  $initial_state Initial Vue state (available for future use).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bpa_root_id    = 'bookingpress-form-vue3-' . $uniq_id;
$bpa_loader_id  = 'bookingpress-form-vue3-loader-' . $uniq_id;
$bpa_root_class = 'bpa-frontend-main-container bpa-frontend-main-booking-calendar bpa-frontend-vue3';

/**
 * Filter the CSS classes applied to the Vue 3 shortcode root element.
 *
 * @param string $bpa_root_class Space-separated class list.
 * @param string $uniq_id        Unique instance identifier.
 * @param array  $atts           Sanitized shortcode attributes.
 */
$bpa_root_class = apply_filters(
	'bookingpress_form_vue3_root_class',
	$bpa_root_class,
	$uniq_id,
	$atts
);
?>
<div
	id="<?php echo esc_attr( $bpa_root_id ); ?>"
	class="<?php echo esc_attr( $bpa_root_class ); ?>"
	data-instance="<?php echo esc_attr( $uniq_id ); ?>"
>
	<div
		id="<?php echo esc_attr( $bpa_loader_id ); ?>"
		class="bpa-back-loader-container bpa-frontend-vue3-loader"
	>
		<div class="bpa-back-loader" role="status" aria-live="polite">
			<span class="screen-reader-text">
				<?php echo esc_html__( 'Loading booking form', 'bookingpress-appointment-booking' ).'&hellip;'; ?>
			</span>
		</div>
	</div>
</div>
<?php
/**
 * Fires immediately after the Vue 3 shortcode HTML shell is rendered.
 *
 * @param string $uniq_id       Unique instance identifier.
 * @param array  $atts          Sanitized shortcode attributes.
 * @param array  $initial_state Initial Vue state for the instance.
 */
do_action( 'bookingpress_form_vue3_after_shell', $uniq_id, $atts, $initial_state );

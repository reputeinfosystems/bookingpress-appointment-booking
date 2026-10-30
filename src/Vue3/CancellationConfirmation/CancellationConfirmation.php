<?php
/**
 * CancellationConfirmation — Vue3 renderer for the
 * `[bookingpress_appointment_cancellation_confirmation]` shortcode.
 *
 * The shortcode callback (`bookingpress_appointment_cancellation_confirmation_func`)
 * still renders the full page markup in PHP (vector, booking summary, Pro
 * refund block via `bookingpress_add_cancel_appointment_extra_content`). On
 * the Vue 3 path it swaps the two Element 2 tags (`el-main`, `el-button`) for
 * their rendered HTML and lets this class mount a small Vue 3 app on that
 * markup (in-DOM template, full `vue.min.js` build) instead of the legacy
 * Vue 2 `wp_footer` script. Only the Cancel button + error toast are reactive;
 * the static sections carry `v-pre` so server content is never compiled.
 *
 * The request is the EXISTING admin-ajax action
 * `bookingpress_front_cancel_appointment` with the legacy `bpa_wp_nonce`
 * nonce; no new endpoint is introduced.
 *
 * Vue 3 is the default renderer. The legacy Vue 2 page is reachable through
 *   add_filter( 'bookingpress_cancellation_confirmation_force_legacy', '__return_true' );
 * and is used automatically on WP without Script Modules (< 6.5).
 *
 * @package BookingPress\Vue3\CancellationConfirmation
 */

namespace BookingPress\Vue3\CancellationConfirmation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires the Vue 3 cancellation-confirmation app and its JSON island.
 */
class CancellationConfirmation {

	/** Script-module handle for the Vue 3 app. */
	const MODULE_APP = 'bookingpress-cancellation-confirmation-v3';

	/** Script-module handle for the per-instance loader (reads the island). */
	const MODULE_LOADER = 'bookingpress-cancellation-confirmation-v3-loader';

	/** Shared Vue vendor handle (registered defensively, same URL as the form). */
	const MODULE_VUE = 'vue';

	/**
	 * Per-instance island data, keyed by instance id.
	 *
	 * @var array<string, array>
	 */
	private static $instances_data = array();

	/** Idempotency guard for {@see register()}. */
	private static $registered = false;

	/** Idempotency guard for the module-data filter hook. */
	private static $data_hook_added = false;

	/** Idempotency guard for {@see init()}. */
	private static $default_attached = false;

	/** Idempotency guard for {@see enqueue_styles()}. */
	private static $styles_enqueued = false;

	/**
	 * Attach the default-enable filter. Wired once from
	 * {@see \BookingPress\Vue3\Routing::init()}. Idempotent.
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$default_attached ) {
			return;
		}
		self::$default_attached = true;
		add_filter( 'bookingpress_cancellation_confirmation_use_vue3', array( __CLASS__, 'default_use_vue3' ), 10, 1 );
	}

	/**
	 * Default-enable strategy for `bookingpress_cancellation_confirmation_use_vue3`
	 * (same inverted control as the Vue 3 My Booking): Vue 3 renders on every
	 * environment that supports WP Script Modules unless
	 * `bookingpress_cancellation_confirmation_force_legacy` returns true.
	 *
	 * @param bool $current Current filter value (an explicit earlier opt-in is kept).
	 *
	 * @return bool
	 */
	public static function default_use_vue3( $current ) {
		if ( (bool) apply_filters( 'bookingpress_cancellation_confirmation_force_legacy', false ) ) {
			return false;
		}
		if ( $current ) {
			return true;
		}
		return self::is_supported();
	}

	/**
	 * Whether this environment can mount the Vue 3 renderer (WP 6.5+ Script
	 * Modules). The shortcode falls back to legacy when it cannot.
	 *
	 * @return bool
	 */
	public static function is_supported() {
		return function_exists( 'wp_register_script_module' ) && function_exists( 'wp_enqueue_script_module' );
	}

	/**
	 * Register the Vue 3 script modules. Safe to call repeatedly.
	 *
	 * @return void
	 */
	public static function register() {
		if ( self::$registered ) {
			return;
		}
		self::$registered = true;

		if ( ! function_exists( 'wp_register_script_module' ) ) {
			return;
		}

		$base    = untrailingslashit( BOOKINGPRESS_URL );
		$version = defined( 'BOOKINGPRESS_VERSION' ) ? BOOKINGPRESS_VERSION : '1.0.0';
		$dir     = defined( 'BOOKINGPRESS_DIR' ) ? untrailingslashit( BOOKINGPRESS_DIR ) : '';

		// Per-file cache-buster: append the source file's mtime so edits bust
		// the browser's script-module cache without a plugin-version bump.
		$ver = static function ( $relative_path ) use ( $version, $dir ) {
			if ( '' === $dir ) {
				return $version;
			}
			$abs = $dir . '/' . ltrim( $relative_path, '/' );
			if ( ! file_exists( $abs ) ) {
				return $version;
			}
			$m = filemtime( $abs );
			return false === $m ? $version : ( $version . '.' . $m );
		};

		// Shared Vue vendor module — same handle + URL + version as the Vue 3
		// booking form / My Booking (re-registration is a no-op).
		wp_register_script_module(
			self::MODULE_VUE,
			$base . '/src/assets/js/vue.min.js',
			array(),
			$version
		);

		wp_register_script_module(
			self::MODULE_APP,
			$base . '/src/assets/js/cancellation-confirmation-vue3/app.js',
			array( self::MODULE_VUE ),
			$ver( 'src/assets/js/cancellation-confirmation-vue3/app.js' )
		);

		wp_register_script_module(
			self::MODULE_LOADER,
			$base . '/src/assets/js/cancellation-confirmation-vue3/bootstrap.js',
			array( self::MODULE_APP ),
			$ver( 'src/assets/js/cancellation-confirmation-vue3/bootstrap.js' )
		);

		if ( ! self::$data_hook_added ) {
			self::$data_hook_added = true;
			add_filter(
				'script_module_data_' . self::MODULE_LOADER,
				array( static::class, 'filter_module_data' )
			);
		}
	}

	/**
	 * Enqueue the base front CSS the page relies on. `set_front_css(1)` is
	 * CSS-only (no JS), so no Vue 2 asset is pulled in. Also hides the error
	 * toast until the app mounts (`v-cloak`), since the in-DOM template is
	 * visible before Vue compiles it.
	 *
	 * @return void
	 */
	public static function enqueue_styles() {
		if ( self::$styles_enqueued ) {
			return;
		}
		self::$styles_enqueued = true;

		$helper = isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ? $GLOBALS['BookingPress'] : null;
		if ( null !== $helper && method_exists( $helper, 'set_front_css' ) ) {
			$helper->set_front_css( 1 );
		}

		if ( function_exists( 'wp_add_inline_style' ) ) {
			wp_add_inline_style( 'bookingpress_front_css', '.bpa-appointment-cancellation_container [v-cloak]{display:none !important;}' );
		}
	}

	/**
	 * Register the modules, stash this instance's island data and enqueue the
	 * loader.
	 *
	 * @param string $instance_id    Per-render unique id (matches the mount node).
	 * @param int    $appointment_id Appointment being cancelled.
	 * @param string $cancel_token   Cancel token from the request URL.
	 *
	 * @return void
	 */
	public static function enqueue_for_render( $instance_id, $appointment_id, $cancel_token ) {
		self::register();
		self::$instances_data[ $instance_id ] = self::build_instance_state( $instance_id, $appointment_id, $cancel_token );

		if ( function_exists( 'wp_enqueue_script_module' ) ) {
			wp_enqueue_script_module( self::MODULE_LOADER );
		}
	}

	/**
	 * Compose the per-instance config the Vue app reads from the JSON island.
	 *
	 * @param string $instance_id    Per-render unique id.
	 * @param int    $appointment_id Appointment being cancelled.
	 * @param string $cancel_token   Cancel token from the request URL.
	 *
	 * @return array
	 */
	private static function build_instance_state( $instance_id, $appointment_id, $cancel_token ) {
		$data = array(
			'is_cancel_appointment_loader' => '0',
			'is_cancel_button_disabled'    => false,
			'bookingpress_cancel_variant'  => '',
			'bookingpress_cancel_msg'      => '',
		);

		// Same data seam as the legacy Vue 2 app (see
		// bookingpress_cancellation_confirmation_dynamic_data_fields_func()).
		$data = apply_filters( 'bookingpress_cancellation_confirmation_add_dynamic_data', $data );

		$state = array(
			'instanceId'    => $instance_id,
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'action'        => 'bookingpress_front_cancel_appointment',
			'nonce'         => wp_create_nonce( 'bpa_wp_nonce' ),
			'appointmentId' => intval( $appointment_id ),
			'cancelToken'   => (string) $cancel_token,
			'data'          => is_array( $data ) ? $data : array(),
			'strings'       => array(
				'error' => esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' ),
			),
		);

		/**
		 * Filter the per-instance JSON-island state for the Vue 3 cancellation
		 * confirmation app. Pro / add-ons hook this to seed their own `data` or
		 * `strings` and enqueue a script module that registers through
		 * `window.BookingPressCancellationConfirmationV3.registerAddon()`
		 * (see bootstrap.js).
		 *
		 * @param array  $state       Instance state.
		 * @param string $instance_id Per-render unique id.
		 */
		return apply_filters( 'bookingpress_cancellation_confirmation_vue3_instance_state', $state, $instance_id );
	}

	/**
	 * Filter callback for `script_module_data_bookingpress-cancellation-confirmation-v3-loader`.
	 *
	 * @param mixed $data Existing module data (passed by WP core).
	 *
	 * @return array
	 */
	public static function filter_module_data( $data ) {
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		if ( empty( $data['instances'] ) || ! is_array( $data['instances'] ) ) {
			$data['instances'] = array();
		}
		$data['instances'] = array_merge( $data['instances'], self::$instances_data );
		return $data;
	}
}

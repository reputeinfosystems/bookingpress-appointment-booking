<?php
/**
 * MyBookings — Vue3 scaffold renderer for the `[bookingpress_my_appointments]`
 * shortcode (phase MB-1A).
 *
 * This is an *opt-in* alternate renderer. The legacy Vue 2 path remains the
 * default; the legacy shortcode callback
 * (`bookingpress_my_appointments_func`) delegates here ONLY when the filter
 * `bookingpress_my_appointments_use_vue3` returns true.
 *
 * MB-1A scope (read path only):
 *   - register + enqueue the Vue 3 script modules (mirrors the booking-form
 *     Vue3 asset pattern in {@see \BookingPress\Vue3\Assets}),
 *   - emit a per-instance JSON island with the data the app needs to call the
 *     EXISTING admin-ajax action `bookingpress_get_customer_appointments`
 *     (no REST endpoint is created in this phase),
 *   - return a mount-point shell that reuses the released wrapper classes.
 *
 * Pro / add-on My Booking features (Edit Account, Change Password,
 * Reschedule, Gift Cards, Packages) are NOT part of this renderer. They plug
 * in from their own plugins via the `bookingpress_mybooking_vue3_instance_state`
 * filter (PHP island seam, see build_instance_state()) and the JS add-on
 * registry `window.BookingPressMyBookingsV3.registerAddon()` (bootstrap.js).
 *
 * @package BookingPress\Vue3\MyBookings
 */

namespace BookingPress\Vue3\MyBookings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Vue 3 My Bookings mount shell and wires its assets.
 */
class MyBookings {

	/** Script-module handle for the Vue 3 app. */
	const MODULE_APP = 'bookingpress-my-bookings-v3';

	/** Script-module handle for the per-instance loader (reads the island). */
	const MODULE_LOADER = 'bookingpress-my-bookings-v3-loader';

	/** Shared Vue vendor handle (registered defensively, same URL as the form). */
	const MODULE_VUE = 'vue';

	/** Shared BookingPress UI (Element Plus) vendor module + style handles. */
	const MODULE_UI = 'bookingpress-ui';
	const STYLE_UI  = 'bookingpress-ui';

	/** Stylesheet handle for the minimal Vue3 My Booking override layer. */
	const STYLE_HANDLE = 'bookingpress-my-bookings-v3';

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

	/**
	 * Entry point used by the legacy shortcode callback.
	 *
	 * @param array  $atts    Shortcode attributes (unused in MB-1A).
	 * @param string $uniq_id Optional caller-supplied instance id; one is
	 *                        generated when empty.
	 *
	 * @return string Mount-point shell HTML.
	 */
	public static function render( $atts = array(), $uniq_id = '' ) {
		unset( $atts );

		if ( '' === $uniq_id ) {
			$uniq_id = substr( md5( uniqid( 'bp_mb_v3_', true ) . wp_rand() ), 0, 12 );
		}

		self::register();
		self::enqueue_styles();
		self::enqueue_for_render( $uniq_id );

		$loading_text = function_exists( 'esc_html__' )
			? esc_html__( 'Loading my bookings', 'bookingpress-appointment-booking' ) . '&hellip;'
			: 'Loading my bookings&hellip;';

		// Wrapper classes mirror the released My Booking markup
		// (`core/views/frontend/appointment_my_appointments.php`) so the
		// existing CSS applies unchanged while Vue boots.
		return sprintf(
			'<div id="bookingpress-my-bookings-vue3-%1$s" class="bpa-frontend-main-container bpa-frontend-vue3 bpa-frontend-my-bookings-vue3" data-bp-mb-instance="%1$s">' .
				'<div class="bpa-front-loader-container">' .
					'<div class="bpa-front-loader" role="status" aria-live="polite">' .
						'<span class="screen-reader-text">%2$s</span>' .
					'</div>' .
				'</div>' .
			'</div>',
			esc_attr( $uniq_id ),
			$loading_text
		);
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
		// booking form. Re-registration with an identical descriptor is a
		// no-op, so this is safe even when the form already registered it.
		wp_register_script_module(
			self::MODULE_VUE,
			$base . '/src/assets/js/vue.min.js',
			array(),
			$version
		);

		// Shared BookingPress UI = Element Plus (wrapped as the `BookingPressUI`
		// Vue plugin). Same handle/URL as the booking form (defensive no-op).
		// Provides `bp-ui-date-picker`, `bp-ui-table`, `bp-ui-pagination`,
		// `bp-ui-tag`, `bp-ui-popconfirm`, `bp-ui-dialog`, etc. used for legacy
		// design parity.
		wp_register_script_module(
			self::MODULE_UI,
			$base . '/src/assets/js/bookingpress-ui.min.js',
			array(),
			$version
		);

		wp_register_script_module(
			self::MODULE_APP,
			$base . '/src/assets/js/my-bookings-vue3/app.js',
			array( self::MODULE_VUE, self::MODULE_UI ),
			$ver( 'src/assets/js/my-bookings-vue3/app.js' )
		);

		wp_register_script_module(
			self::MODULE_LOADER,
			$base . '/src/assets/js/my-bookings-vue3/bootstrap.js',
			array( self::MODULE_APP ),
			$ver( 'src/assets/js/my-bookings-vue3/bootstrap.js' )
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
	 * Enqueue the loader module and stash this instance's island data.
	 *
	 * @param string $instance_id Per-render unique id.
	 *
	 * @return void
	 */
	public static function enqueue_for_render( $instance_id ) {
		self::$instances_data[ $instance_id ] = self::build_instance_state( $instance_id );

		if ( function_exists( 'wp_enqueue_script_module' ) ) {
			wp_enqueue_script_module( self::MODULE_LOADER );
		}
	}

	/**
	 * Whether this environment can actually mount the Vue 3 renderer.
	 *
	 * The Vue 3 path is delivered via WP Script Modules (WP 6.5+). On older WP
	 * the modules cannot be registered/enqueued, so a rendered shell would never
	 * mount and the visitor would be stuck on the boot loader. The shortcode
	 * callback gates on this so it falls back to the legacy Vue 2 renderer when
	 * script modules are unavailable — never a dead Vue 3 shell.
	 *
	 * @return bool
	 */
	public static function is_supported() {
		return function_exists( 'wp_register_script_module' ) && function_exists( 'wp_enqueue_script_module' );
	}

	/**
	 * Idempotency guard for {@see init()}.
	 *
	 * @var bool
	 */
	private static $bpa_default_attached = false;

	/**
	 * Attach the Lite-only default-enable filter (MB-6D).
	 *
	 * Wired once from {@see \BookingPress\Vue3\Routing::init()} on `plugins_loaded`.
	 * The filter callback ({@see lite_only_default()}) is evaluated lazily when the
	 * shortcode runs, so Pro detection is reliable by then. Idempotent.
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$bpa_default_attached ) {
			return;
		}
		self::$bpa_default_attached = true;
		add_filter( 'bookingpress_my_appointments_use_vue3', array( __CLASS__, 'lite_only_default' ), 10, 1 );

		// PR-e3: post-login rehydrate endpoint. Logged-in only (priv) — it runs
		// right after an in-place guest login, so the request is authenticated and
		// returns the authenticated instance state + the login-gated add-on module
		// URLs the client should dynamically import (no page reload).
		add_action( 'wp_ajax_bookingpress_mybooking_vue3_rehydrate', array( __CLASS__, 'ajax_rehydrate' ) );
	}

	/**
	 * Filter-compatible default-enable strategy for the Vue 3 My Booking.
	 *
	 * Attached to `bookingpress_my_appointments_use_vue3` via {@see init()}. Vue 3
	 * is now the DEFAULT My Bookings renderer for every supported site — Lite AND
	 * Pro (the Pro parity blockers are closed). The control has been INVERTED: the
	 * way to load the legacy Vue 2 renderer is a filter that returns TRUE:
	 *   add_filter( 'bookingpress_my_appointments_force_legacy', '__return_true' );
	 *
	 * `bookingpress_my_appointments_force_legacy` is the single, priority-independent
	 * kill switch — returning true from it loads the legacy Vue 2 My Booking
	 * regardless of everything else here. When it is not set, Vue 3 renders on any
	 * environment that supports WP Script Modules (older WP with no module support
	 * still falls back to legacy so the shell can never be a dead boot loader).
	 *
	 * @param bool $current Current filter value (an explicit earlier opt-in is kept).
	 *
	 * @return bool
	 */
	public static function lite_only_default( $current ) {
		// Priority-independent kill switch: returning true from this filter loads
		// the legacy Vue 2 renderer (the inverted control — true => Vue 2).
		if ( (bool) apply_filters( 'bookingpress_my_appointments_force_legacy', false ) ) {
			return false;
		}
		if ( $current ) {
			return true; // Respect an explicit opt-in already in place.
		}
		if ( ! self::is_supported() ) {
			return false; // No script-module support → legacy (never a dead shell).
		}
		// Vue 3 is the default renderer for all supported sites (Pro included).
		return true;
	}

	/**
	 * Enqueue the base front CSS the legacy My Booking shortcode relies on, plus
	 * the minimal Vue 3 override layer.
	 *
	 * The Vue 3 shortcode branch returns BEFORE the legacy `set_front_css()` call,
	 * so without this the page would be unstyled (the released `bpa-front-*`
	 * classes the Vue 3 markup reuses would have no stylesheet). `set_front_css(1)`
	 * is CSS-only (no JS) and force-enqueues the same base sheets the legacy path
	 * uses; the override file adds only the new `bpa-front-mb-v3-*` structural
	 * rules (banners, delete-confirm, table hover actions).
	 *
	 * @return void
	 */
	private static function enqueue_styles() {
		$helper = isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ? $GLOBALS['BookingPress'] : null;
		if ( null !== $helper ) {
			if ( method_exists( $helper, 'set_front_css' ) ) {
				$helper->set_front_css( 1 );
			}
			if ( method_exists( $helper, 'bookingpress_load_mybooking_custom_css' ) ) {
				$helper->bookingpress_load_mybooking_custom_css();
			}
		}

		if ( ! function_exists( 'wp_register_style' ) || ! function_exists( 'wp_enqueue_style' ) ) {
			return;
		}

		$ui_base    = defined( 'BOOKINGPRESS_URL' ) ? untrailingslashit( BOOKINGPRESS_URL ) : '';
		$ui_version = defined( 'BOOKINGPRESS_VERSION' ) ? BOOKINGPRESS_VERSION : '1.0.0';
		// Element Plus (BookingPress UI) stylesheet — required for bp-ui-* widgets
		// (date picker, table, pagination, dialog, tag) to render correctly.
		if ( ! wp_style_is( self::STYLE_UI, 'registered' ) ) {
			wp_register_style( self::STYLE_UI, $ui_base . '/src/assets/css/bookingpress-ui.min.css', array(), $ui_version );
		}
		if ( ! wp_style_is( self::STYLE_UI, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_UI );
		}

		$base    = defined( 'BOOKINGPRESS_URL' ) ? untrailingslashit( BOOKINGPRESS_URL ) : '';
		$dir     = defined( 'BOOKINGPRESS_DIR' ) ? untrailingslashit( BOOKINGPRESS_DIR ) : '';
		$version = defined( 'BOOKINGPRESS_VERSION' ) ? BOOKINGPRESS_VERSION : '1.0.0';
		$rel     = '/css/booking-form-vue3-mybookings.css';

		if ( '' !== $dir && file_exists( $dir . $rel ) ) {
			$m       = filemtime( $dir . $rel );
			$version = false === $m ? $version : ( $version . '.' . $m );
		}

		if ( ! wp_style_is( self::STYLE_HANDLE, 'registered' ) ) {
			// Depend on the legacy front sheet so it loads after it and can layer on top.
			wp_register_style( self::STYLE_HANDLE, $base . $rel, array( 'bookingpress_front_css' ), $version );
		}
		if ( ! wp_style_is( self::STYLE_HANDLE, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_HANDLE );
		}
	}

	/**
	 * Compose the per-instance config the Vue app reads from the JSON island.
	 *
	 * Uses the EXISTING admin-ajax read action + the legacy `bpa_wp_nonce`
	 * nonce; no new endpoint is introduced.
	 *
	 * @param string $instance_id Per-render unique id.
	 *
	 * @return array
	 */
	private static function build_instance_state( $instance_id ) {
		$state = array(
			'instanceId'     => $instance_id,
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'action'         => 'bookingpress_get_customer_appointments',
			'nonce'          => wp_create_nonce( 'bpa_wp_nonce' ),
			'perPage'        => 10,
			'isUserLoggedIn' => is_user_logged_in() ? '1' : '0',
			'logoutUrl'      => function_exists( 'wp_logout_url' ) ? wp_logout_url( ( function_exists( 'get_permalink' ) && get_permalink() ) ? get_permalink() : home_url() ) : '',
			'config'         => array(
				'hide_customer_details'         => self::get_hide_customer_details(),
				'allow_cancel_appointments'     => self::get_allow_cancel_appointments(),
				'allow_customer_delete_profile' => self::get_allow_customer_delete_profile(),
			),
			'strings'        => self::collect_strings(),
			'deleteAccountContent' => self::collect_delete_account_content( $instance_id ),
		);

		/**
		 * Filter the per-instance JSON-island state for the Vue 3 My Booking app.
		 *
		 * This is the PHP-side extension seam for Pro / add-on My Booking features
		 * (mirrors the booking form's `bookingpress_form_v3_initial_state`). A
		 * feature class hooks this to add its own `config` flags and `strings`,
		 * and typically also enqueues its script module + stylesheet from the
		 * callback (so it loads ONLY when this shortcode renders), e.g.:
		 *
		 *   add_filter( 'bookingpress_mybooking_vue3_instance_state', function ( $state, $id ) {
		 *       $state['config']['my_flag'] = '1';
		 *       $state['strings']['my_label'] = __( '…', 'my-addon' );
		 *       // wp_enqueue_script_module( 'my-addon-my-bookings-v3' );
		 *       return $state;
		 *   }, 10, 2 );
		 *
		 * The JS side then registers its UI through
		 * `window.BookingPressMyBookingsV3.registerAddon()` (see bootstrap.js).
		 *
		 * @param array  $state       Instance state (ajaxUrl/nonce/config/strings/…).
		 * @param string $instance_id Per-render unique id.
		 */
		return apply_filters( 'bookingpress_mybooking_vue3_instance_state', $state, $instance_id );
	}

	/**
	 * PR-e3 — post-login rehydrate endpoint (admin-ajax, logged-in only).
	 *
	 * After an in-place guest login (the Pro login form calls
	 * `ctx.onAuthenticated`), the page is authenticated but the login-gated Pro /
	 * add-on My Booking modules (Edit Account, Change Password, Reschedule, myCred
	 * Reward Points, Gift Card, Package) were never enqueued for the guest, so
	 * their tabs / row actions are missing until a reload. This endpoint rebuilds
	 * the instance state AS THE NOW-LOGGED-IN USER — so the existing
	 * `bookingpress_mybooking_vue3_instance_state` filter seeds every feature's
	 * authenticated `config` + `strings` for free — and returns them together with
	 * the list of module URLs the client should dynamically `import()` (each module
	 * then registers its UI through the add-on registry). A fresh `nonce` +
	 * `logoutUrl` are returned too (the guest-seeded logout nonce was for uid 0).
	 *
	 * @return void
	 */
	public static function ajax_rehydrate() {
		$error = array( 'variant' => 'error' );

		$wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' ) ) {
			wp_send_json( $error );
		}
		if ( ! is_user_logged_in() ) {
			wp_send_json( $error );
		}

		$instance_id = isset( $_POST['instance_id'] ) ? sanitize_text_field( wp_unslash( $_POST['instance_id'] ) ) : '';
		if ( '' === $instance_id ) {
			$instance_id = substr( md5( uniqid( 'bp_mb_v3_', true ) . wp_rand() ), 0, 12 );
		}

		// Fires the instance_state filter as the logged-in user → every login-gated
		// feature seeds its authenticated config/strings.
		$state = self::build_instance_state( $instance_id );

		/**
		 * Collect the login-gated add-on module URLs the client should dynamically
		 * import after an in-place login. Each login-gated My Booking feature hooks
		 * this to append `array( 'handle' => <module-handle>, 'src' => <url> )` when
		 * its own (non-login) enable condition holds. Only applied here (logged-in
		 * rehydrate), so it adds zero overhead to normal front-end renders.
		 *
		 * @param array $modules List of module descriptors.
		 */
		$modules = apply_filters( 'bookingpress_mybooking_vue3_rehydrate_modules', array() );

		$out_modules = array();
		if ( is_array( $modules ) ) {
			foreach ( $modules as $m ) {
				if ( is_array( $m ) && ! empty( $m['src'] ) ) {
					$out_modules[] = array(
						'handle' => isset( $m['handle'] ) ? (string) $m['handle'] : '',
						'src'    => esc_url_raw( $m['src'] ),
					);
				} elseif ( is_string( $m ) && '' !== $m ) {
					$out_modules[] = array(
						'handle' => '',
						'src'    => esc_url_raw( $m ),
					);
				}
			}
		}

		wp_send_json(
			array(
				'variant'   => 'success',
				'config'    => isset( $state['config'] ) && is_array( $state['config'] ) ? $state['config'] : array(),
				'strings'   => isset( $state['strings'] ) && is_array( $state['strings'] ) ? $state['strings'] : array(),
				'nonce'     => isset( $state['nonce'] ) ? $state['nonce'] : '',
				'logoutUrl' => isset( $state['logoutUrl'] ) ? $state['logoutUrl'] : '',
				'modules'   => $out_modules,
			)
		);
	}

	/**
	 * Read the legacy `hide_customer_details` customize toggle (read-only).
	 *
	 * @return string '1' when the customer header should be hidden, else '0'.
	 */
	private static function get_hide_customer_details() {
		$helper = isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ? $GLOBALS['BookingPress'] : null;
		if ( null === $helper || ! method_exists( $helper, 'bookingpress_get_customize_settings' ) ) {
			return '0';
		}
		$value = $helper->bookingpress_get_customize_settings( 'hide_customer_details', 'booking_my_booking' );
		return ( 'true' === $value ) ? '1' : '0';
	}

	/**
	 * Read the legacy `allow_to_cancel_appointment` customize toggle (read-only).
	 *
	 * This is the global gate the legacy My Booking template uses to decide
	 * whether the cancel action is offered at all (Pro additionally gates per
	 * row via the `allow_cancelling` item flag).
	 *
	 * @return string '1' when cancellation is allowed, else '0'.
	 */
	private static function get_allow_cancel_appointments() {
		$helper = isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ? $GLOBALS['BookingPress'] : null;
		if ( null === $helper || ! method_exists( $helper, 'bookingpress_get_customize_settings' ) ) {
			return '0';
		}
		$value = $helper->bookingpress_get_customize_settings( 'allow_to_cancel_appointment', 'booking_my_booking' );
		return ( 'true' === $value ) ? '1' : '0';
	}

	/**
	 * Whether the Delete Account tab should be offered (read-only).
	 *
	 * Legacy-compatible: Lite shows the Delete Account option unconditionally,
	 * and Pro only hides it when the admin explicitly turns it off. So this
	 * returns '1' unless `allow_customer_delete_profile` is explicitly 'false'.
	 *
	 * @return string '1' to show the tab, '0' to hide it.
	 */
	private static function get_allow_customer_delete_profile() {
		$helper = isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ? $GLOBALS['BookingPress'] : null;
		if ( null === $helper || ! method_exists( $helper, 'bookingpress_get_customize_settings' ) ) {
			return '1';
		}
		$value = $helper->bookingpress_get_customize_settings( 'allow_customer_delete_profile', 'booking_my_booking' );
		return ( 'false' === $value ) ? '0' : '1';
	}

	/**
	 * Backend-configured Delete Account panel content (read-only, legacy parity).
	 *
	 * Legacy renders the `delete_account_content` customize setting (HTML with
	 * the vector/SVG + headings) through `do_shortcode()`, where the
	 * `[bookingpress_delete_account]` shortcode expands into the Cancel/Delete
	 * buttons wired to Vue 2 methods. Those handlers cannot work inside a Vue 3
	 * `v-html` block, so instead the shortcode is replaced here by an empty slot
	 * `<div>`; the Vue 3 app teleports its reactive Cancel/Delete button group
	 * into that slot, keeping the buttons exactly where the admin placed the
	 * shortcode. The shortcode's `cancel_button_text` /
	 * `delete_button_text` atts are surfaced so the labels survive too. Any
	 * other shortcodes in the content still run through `do_shortcode()`.
	 *
	 * @param string $instance_id Per-render unique id (keeps the slot id unique
	 *                            when several shortcodes render on one page).
	 *
	 * @return array{html:string, slotId:string, cancelText:string, deleteText:string}
	 */
	private static function collect_delete_account_content( $instance_id ) {
		$result = array(
			'html'       => '',
			'slotId'     => 'bp-mb-v3-da-actions-' . $instance_id,
			'cancelText' => '',
			'deleteText' => '',
		);

		$helper = isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ? $GLOBALS['BookingPress'] : null;
		if ( null === $helper || ! method_exists( $helper, 'bookingpress_get_customize_settings' ) ) {
			return $result;
		}

		$content = $helper->bookingpress_get_customize_settings( 'delete_account_content', 'booking_my_booking' );
		$content = is_string( $content ) ? stripslashes( $content ) : '';
		if ( '' === trim( $content ) ) {
			return $result;
		}

		// First occurrence becomes the teleport slot; extras (if any) are dropped
		// so the slot id stays unique.
		$slot_id = $result['slotId'];
		$content = preg_replace_callback(
			'/\[bookingpress_delete_account([^\]]*)\]/',
			function ( $matches ) use ( $slot_id, &$result ) {
				$atts = shortcode_parse_atts( trim( $matches[1] ) );
				if ( is_array( $atts ) ) {
					if ( ! empty( $atts['cancel_button_text'] ) ) {
						$result['cancelText'] = $atts['cancel_button_text'];
					}
					if ( ! empty( $atts['delete_button_text'] ) ) {
						$result['deleteText'] = $atts['delete_button_text'];
					}
				}
				return '<div class="bpa-front-mb-v3-da-actions" id="' . esc_attr( $slot_id ) . '"></div>';
			},
			$content,
			1
		);
		$content = preg_replace( '/\[bookingpress_delete_account[^\]]*\]/', '', $content );

		$result['html'] = do_shortcode( $content );
		return $result;
	}

	/**
	 * Collect the handful of customizer/UI labels the scaffold renders.
	 *
	 * Pulls from the legacy `booking_my_booking` customize group when the
	 * global helper is available; otherwise falls back to translated
	 * defaults. Read-only — no coupling beyond label lookup.
	 *
	 * @return array<string, string>
	 */
	private static function collect_strings() {
		$defaults = array(
			// UI / state copy (not customizable).
			'loading'                 => esc_html__( 'Loading…', 'bookingpress-appointment-booking' ),
			'error'                   => esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' ),
			'no_appointments'         => esc_html__( 'No Appointments found!', 'bookingpress-appointment-booking' ),
			'login_message'           => esc_html__( 'Please login to your account to view bookings!', 'bookingpress-appointment-booking' ),
			'manual_booked_by_admin'  => esc_html__( 'Manual ( Booked By Admin )', 'bookingpress-appointment-booking' ),
			'duration'                => esc_html__( 'Duration', 'bookingpress-appointment-booking' ),
			'staff'                   => esc_html__( 'Staff', 'bookingpress-appointment-booking' ),
			'members'                 => esc_html__( 'Members', 'bookingpress-appointment-booking' ),
			'extras'                  => esc_html__( 'Service Extras', 'bookingpress-appointment-booking' ),
			'deposit'                 => esc_html__( 'Deposit', 'bookingpress-appointment-booking' ),
			'discount'                => esc_html__( 'Discount', 'bookingpress-appointment-booking' ),
			'tax'                     => esc_html__( 'Tax', 'bookingpress-appointment-booking' ),
			'payment_details_title'   => esc_html__( 'Payment Details', 'bookingpress-appointment-booking' ),
			'payment_method_title'    => esc_html__( 'Payment Method', 'bookingpress-appointment-booking' ),
			'prev'                    => esc_html__( 'Prev', 'bookingpress-appointment-booking' ),
			'next'                    => esc_html__( 'Next', 'bookingpress-appointment-booking' ),
			// Cancel-action copy (customizable; overridden below).
			'cancel_appointment_title'                => esc_html__( 'Cancel Appointment', 'bookingpress-appointment-booking' ),
			'cancel_appointment_confirmation_message' => esc_html__( 'Are you sure you want to cancel this appointment?', 'bookingpress-appointment-booking' ),
			'cancel_appointment_yes_btn_text'         => esc_html__( 'Yes', 'bookingpress-appointment-booking' ),
			'cancel_appointment_no_btn_text'          => esc_html__( 'No', 'bookingpress-appointment-booking' ),
			'book_again_button_title'                 => esc_html__( 'Book Again', 'bookingpress-appointment-booking' ),
			// Delete Account tab (customizable where noted; overridden below).
			'my_appointment_menu_title'               => esc_html__( 'My Appointments', 'bookingpress-appointment-booking' ),
			'delete_appointment_menu_title'           => esc_html__( 'Delete Account', 'bookingpress-appointment-booking' ),
			'logout_title'                            => esc_html__( 'Logout', 'bookingpress-appointment-booking' ),
			// Refund preview dialog (Pro row-flag driven, stays in Lite; customizable
			// where noted, overridden below).
			'paid_amount_text'                        => esc_html__( 'Paid Amount', 'bookingpress-appointment-booking' ),
			'refund_amount_text'                      => esc_html__( 'Refund Amount', 'bookingpress-appointment-booking' ),
			'refund_cancel_text'                      => esc_html__( 'Cancel', 'bookingpress-appointment-booking' ),
			'refund_apply_text'                       => esc_html__( 'Apply', 'bookingpress-appointment-booking' ),
			// Refund policy message (general message setting, not a customizer key;
			// seeded below — default empty so nothing renders when unset).
			'refund_policy_msg'                       => '',
			'staff_main_heading'                      => esc_html__( 'Staff', 'bookingpress-appointment-booking' ),
			'payment_main_heading'                    => esc_html__( 'Payment', 'bookingpress-appointment-booking' ),
			'id_main_heading'                         => esc_html__( 'ID', 'bookingpress-appointment-booking' ),
			'delete_account_heading_title'            => esc_html__( 'Delete Account', 'bookingpress-appointment-booking' ),
			'delete_account_desc'                     => esc_html__( 'Permanently delete your account and all related booking data. This action cannot be undone.', 'bookingpress-appointment-booking' ),
			'delete_account_button_title'             => esc_html__( 'Delete Account', 'bookingpress-appointment-booking' ),
			'cancel_button_text'                      => esc_html__( 'Cancel', 'bookingpress-appointment-booking' ),
			'delete_button_text'                      => esc_html__( 'Delete', 'bookingpress-appointment-booking' ),
			// Customizable labels (overridden below from `booking_my_booking`).
			'mybooking_title_text'    => esc_html__( 'My Bookings', 'bookingpress-appointment-booking' ),
			'search_appointment_title' => esc_html__( 'Search appointment', 'bookingpress-appointment-booking' ),
			'search_date_title'       => esc_html__( 'Start date', 'bookingpress-appointment-booking' ),
			'search_end_date_title'   => esc_html__( 'End date', 'bookingpress-appointment-booking' ),
			'apply_button_title'      => esc_html__( 'Apply', 'bookingpress-appointment-booking' ),
			'reset_button_title'      => esc_html__( 'Clear', 'bookingpress-appointment-booking' ),
			'booking_id_heading'      => esc_html__( 'Booking ID', 'bookingpress-appointment-booking' ),
			'service_main_heading'    => esc_html__( 'Service', 'bookingpress-appointment-booking' ),
			'date_main_heading'       => esc_html__( 'Date', 'bookingpress-appointment-booking' ),
			'status_main_heading'     => esc_html__( 'Status', 'bookingpress-appointment-booking' ),
			'booking_time_title'      => esc_html__( 'Time', 'bookingpress-appointment-booking' ),
			'total_amount_title'      => esc_html__( 'Total Amount', 'bookingpress-appointment-booking' ),
		);

		$helper = isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ? $GLOBALS['BookingPress'] : null;
		if ( null === $helper || ! method_exists( $helper, 'bookingpress_get_customize_settings' ) ) {
			return $defaults;
		}

		$keys = array(
			'mybooking_title_text',
			'search_appointment_title',
			'search_date_title',
			'search_end_date_title',
			'apply_button_title',
			'reset_button_title',
			'cancel_appointment_title',
			'cancel_appointment_confirmation_message',
			'cancel_appointment_yes_btn_text',
			'cancel_appointment_no_btn_text',
			'book_again_button_title',
			'my_appointment_menu_title',
			'delete_appointment_menu_title',
			'logout_title',
			'paid_amount_text',
			'refund_amount_text',
			'refund_cancel_text',
			'refund_apply_text',
			'id_main_heading',
			'payment_main_heading',
			'delete_account_heading_title',
			'delete_account_desc',
			'delete_account_button_title',
			'payment_details_title',
			'payment_method_title',
			'booking_id_heading',
			'service_main_heading',
			'date_main_heading',
			'status_main_heading',
			'booking_time_title',
			'total_amount_title',
		);
		$labels = $helper->bookingpress_get_customize_settings( $keys, 'booking_my_booking' );
		if ( is_array( $labels ) ) {
			foreach ( $labels as $key => $value ) {
				if ( ! empty( $value ) ) {
					$defaults[ $key ] = stripslashes_deep( $value );
				}
			}
		}

		// Refund policy message — a general message setting (not a customizer key),
		// shown atop the refund preview dialog (mirrors the legacy Pro dialog).
		if ( method_exists( $helper, 'bookingpress_get_settings' ) ) {
			$refund_policy = $helper->bookingpress_get_settings( 'refund_policy_message', 'message_setting' );
			if ( ! empty( $refund_policy ) ) {
				$defaults['refund_policy_msg'] = stripslashes_deep( $refund_policy );
			}
		}

		return $defaults;
	}

	/**
	 * Filter callback for `script_module_data_bookingpress-my-bookings-v3-loader`.
	 *
	 * WordPress emits the returned array as a JSON `<script type="application/json">`
	 * island the loader module reads on parse.
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

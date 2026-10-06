<?php
/**
 * Assets — script-module and style registration for the Vue3 greenfield form.
 *
 * Owns the `bookingpress-form-v3*` handles. Defensively re-registers the
 * shared vendor handles (`vue`, `bookingpress-ui`, `bookingpress-vcalendar`)
 * with the **same URL + version** as the intermediate Vue3 form, so a page
 * containing only `[bp_form_v3_canary]` (no `[bookingpress_form_vue3]`) still
 * has the vendor modules available.
 *
 * Idempotency: WordPress's script-module registry treats a second
 * `wp_register_script_module()` call for the same handle as a no-op (last
 * registration wins; identical URL + version produces no observable change).
 *
 * @package BookingPress\Vue3
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §1
 */

namespace BookingPress\Vue3;

use BookingPress\Vue3\Customize\CustomizeAssets;
use BookingPress\Vue3\REST\RouteRegistrar;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Services\NonceService;
use BookingPress\Vue3\State\StateBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static script-module + style registry for the Vue3 form.
 *
 * Two entry points:
 * - `register()` — idempotent global registration (called once per request).
 * - `enqueue_for_render( $instance_id, $atts )` — per-shortcode enqueue.
 */
class Assets {

	/**
	 * Handle for the per-instance loader (reads the JSON island).
	 */
	const MODULE_LOADER = 'bookingpress-form-v3-loader';

	/**
	 * Handle for the Vue 3 app module (mounts each instance).
	 */
	const MODULE_APP = 'bookingpress-form-v3';

	/**
	 * Shared vendor handles (re-registered defensively).
	 */
	const MODULE_VUE       = 'vue';
	const MODULE_UI        = 'bookingpress-ui';
	const MODULE_VCALENDAR = 'bookingpress-vcalendar';

	/**
	 * Stylesheet handles.
	 */
	const STYLE_FORM         = 'bookingpress-form-v3';
	const STYLE_UI           = 'bookingpress-ui';
	const STYLE_VCALENDAR    = 'bookingpress-vcalendar';
	const STYLE_LEGACY_FORM      = 'bookingpress-form-v3-legacy';
	const STYLE_LEGACY_THEME     = 'bookingpress-form-v3-legacy-theme';
	const STYLE_LEGACY_FRONT     = 'bookingpress-form-v3-legacy-front';
	const STYLE_LEGACY_ELEMENT   = 'bookingpress-form-v3-legacy-element';
	const STYLE_LEGACY_VARIABLES = 'bookingpress-form-v3-legacy-variables';

	/**
	 * Per-instance data, keyed by instance id.
	 *
	 * Consumed by {@see filter_module_data()} to emit the JSON island the
	 * loader module reads.
	 *
	 * @var array<string, array>
	 */
	private static $instances_data = array();

	/**
	 * Idempotency guard for `register()`.
	 *
	 * @var bool
	 */
	private static $registered = false;

	/**
	 * Idempotency guard for the module-data filter hook.
	 *
	 * @var bool
	 */
	private static $data_hook_added = false;

	/**
	 * Register all Vue3-path script modules and styles.
	 *
	 * Safe to call multiple times per request.
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

		// Per-file cache-buster: append the file's mtime to the base version
		// so source edits invalidate the browser's script-module cache without
		// requiring a plugin-version bump.
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

		// --- Shared vendor handles ----------------------------------------
		// Same handle + URL + version as the intermediate Vue3 form. Safe to
		// re-register; the registry treats identical re-registration as a
		// no-op. This guarantees the canary page works even without
		// [bookingpress_form_vue3] on the same render.
		wp_register_script_module(
			self::MODULE_VUE,
			$base . '/src/assets/js/vue.min.js',
			array(),
			$version
		);

		wp_register_script_module(
			self::MODULE_UI,
			$base . '/src/assets/js/bookingpress-ui.min.js',
			array(),
			$version
		);

		wp_register_script_module(
			self::MODULE_VCALENDAR,
			$base . '/src/assets/js/bp-vcalendar.js',
			array(),
			$version
		);

		wp_register_style(
			self::STYLE_UI,
			$base . '/src/assets/css/bookingpress-ui.min.css',
			array(),
			$version
		);

		wp_register_style(
			self::STYLE_VCALENDAR,
			$base . '/src/assets/css/bp-vcalendar.css',
			array(),
			$version
		);

		// --- Vue3 greenfield handles --------------------------------------
		wp_register_script_module(
			self::MODULE_APP,
			$base . '/src/assets/js/booking-form-vue3/app.js',
			array( self::MODULE_VUE, self::MODULE_UI, self::MODULE_VCALENDAR ),
			$ver( 'src/assets/js/booking-form-vue3/app.js' )
		);

		wp_register_script_module(
			self::MODULE_LOADER,
			$base . '/src/assets/js/booking-form-vue3/bootstrap.js',
			array( self::MODULE_APP, self::MODULE_UI ),
			$ver( 'src/assets/js/booking-form-vue3/bootstrap.js' )
		);

		// Legacy CSS variable tokens (--bpa-pt-main-green, the alpha
		// variants like --bpa-pt-main-green-alpha-12, etc.). Required for
		// the legacy `bookingpress_front.css` rules that reference these
		// alpha tokens (e.g. selected-today day-cell tint, button
		// hovers). Loaded at the global root scope (`:root`).
		wp_register_style(
			self::STYLE_LEGACY_VARIABLES,
			$base . '/css/bookingpress_variables.css',
			array(),
			$ver( 'css/bookingpress_variables.css' )
		);

		// The released `[bookingpress_form]` markup classes (`.bpa-front-*`,
		// `.bpa-frontend-*`) are styled by this file. The new Vue 3 path
		// renders the same markup so loading this verbatim gives us pixel
		// parity with the released form.
		wp_register_style(
			self::STYLE_LEGACY_FORM,
			$base . '/src/assets/css/booking-form.css',
			array( self::STYLE_UI, self::STYLE_VCALENDAR, self::STYLE_LEGACY_VARIABLES ),
			$ver( 'src/assets/css/booking-form.css' )
		);

		// Theme overlay (colors, typography overrides for `.bpa-frontend-*`).
		wp_register_style(
			self::STYLE_LEGACY_THEME,
			$base . '/src/assets/css/bookingpress_bp_theme.css',
			array( self::STYLE_LEGACY_FORM ),
			$ver( 'src/assets/css/bookingpress_bp_theme.css' )
		);

		// Legacy `bookingpress_front.css` defines layout modifiers used by
		// the released markup (e.g. `.bpa-front-tabs--vertical-left`,
		// `.bpa-front-tabs--left`). Required for the released stepper
		// layout to render as a left sidebar instead of a horizontal band.
		wp_register_style(
			self::STYLE_LEGACY_FRONT,
			$base . '/css/bookingpress_front.css',
			array( self::STYLE_LEGACY_FORM ),
			$ver( 'css/bookingpress_front.css' )
		);

		// Element-Plus theme overrides scoped under `.bpa-frontend-*`.
		// Needed for the `el-tag` pill style on category items, form-field
		// element-plus components, etc.
		wp_register_style(
			self::STYLE_LEGACY_ELEMENT,
			$base . '/css/bookingpress_element_theme.css',
			array( self::STYLE_LEGACY_FRONT ),
			$ver( 'css/bookingpress_element_theme.css' )
		);

		// `booking-form-vue3.css` becomes a thin override layer (only rules
		// the released CSS doesn't cover, e.g. mount spinner, required-field
		// asterisk addition). It now depends on the legacy form sheet so it
		// loads last and can target the same selectors.
		wp_register_style(
			self::STYLE_FORM,
			$base . '/src/assets/css/booking-form-vue3.css',
			array(
				self::STYLE_UI,
				self::STYLE_VCALENDAR,
				self::STYLE_LEGACY_FORM,
				self::STYLE_LEGACY_THEME,
				self::STYLE_LEGACY_FRONT,
				self::STYLE_LEGACY_ELEMENT,
			),
			$ver( 'src/assets/css/booking-form-vue3.css' )
		);

		// Register the JSON-island filter exactly once. The loader module
		// receives an `instances` map containing every shortcode instance
		// that rendered on this request.
		if ( ! self::$data_hook_added ) {
			self::$data_hook_added = true;
			add_filter(
				'script_module_data_' . self::MODULE_LOADER,
				array( static::class, 'filter_module_data' )
			);
		}

		// M5: register the generated customize CSS handle. The URL +
		// version are resolved lazily in `enqueue_for_render()` so that
		// (a) the file is fresh per render and (b) the cache-busting
		// version is the file's mtime.
	}

	/**
	 * Enqueue per-render assets and stash instance data for the JSON island.
	 *
	 * @param string $instance_id The per-render unique id from
	 *                            {@see Routing::generate_unique_id()}.
	 * @param array  $atts        Sanitized shortcode attributes.
	 *
	 * @return void
	 */
	public static function enqueue_for_render( $instance_id, array $atts = array() ) {
		$nonces = new NonceService();

		// M4: emit the three tokens the REST controllers expect:
		//   - `wpRestNonce`        → X-WP-Nonce header (against `wp_rest`)
		//   - `formNonce`          → `bp_v3_nonce` body field (against `bookingpress_form_v3_nonce`)
		//   - `instanceToken`      → `bp_v3_instance_token` body field (per-render anti-replay)
		$wp_rest_nonce  = wp_create_nonce( 'wp_rest' );
		$form_nonce     = $nonces->issue_nonce();
		$instance_token = $nonces->issue_instance_token( $instance_id );

		// M5 — compose the full initial state via StateBuilder. The auth
		// triple + REST hints are merged on top so the M6 client has
		// everything it needs in one object.
		$builder = new StateBuilder();
		$state   = $builder->build( $instance_id, $atts );

		$state['rest'] = array(
			'root'      => esc_url_raw( rest_url( RouteRegistrar::REST_NAMESPACE . '/' . RouteRegistrar::ROUTE_PREFIX . '/' ) ),
			'namespace' => RouteRegistrar::REST_NAMESPACE,
			'prefix'    => RouteRegistrar::ROUTE_PREFIX,
		);
		$state['nonces'] = array(
			'wpRestNonce'   => $wp_rest_nonce,
			'formNonce'     => $form_nonce,
			'instanceToken' => $instance_token,
		);

		self::$instances_data[ $instance_id ] = $state;

		if ( function_exists( 'wp_enqueue_script_module' ) ) {
			wp_enqueue_script_module( self::MODULE_LOADER );
		}

		if ( ! wp_style_is( self::STYLE_UI, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_UI );
		}
		if ( ! wp_style_is( self::STYLE_VCALENDAR, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_VCALENDAR );
		}
		// Enqueue the released-form CSS and theme overlay first so our
		// thin override layer in STYLE_FORM can selectively adjust them.
		if ( ! wp_style_is( self::STYLE_LEGACY_VARIABLES, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_LEGACY_VARIABLES );
		}
		if ( ! wp_style_is( self::STYLE_LEGACY_FORM, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_LEGACY_FORM );
		}
		if ( ! wp_style_is( self::STYLE_LEGACY_THEME, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_LEGACY_THEME );
		}
		if ( ! wp_style_is( self::STYLE_LEGACY_FRONT, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_LEGACY_FRONT );
		}
		if ( ! wp_style_is( self::STYLE_LEGACY_ELEMENT, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_LEGACY_ELEMENT );
		}
		if ( ! wp_style_is( self::STYLE_FORM, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_FORM );
		}

		// M5: customize CSS — ensure the file exists, then register +
		// enqueue with a file-mtime version so admin saves bust the cache.
		$cust = CustomizeAssets::ensure_exists();
		if ( $cust['exists'] && ! empty( $cust['url'] ) ) {
			$cust_version = defined( 'BOOKINGPRESS_VERSION' ) ? BOOKINGPRESS_VERSION : '1.0.0';
			if ( ! empty( $cust['path'] ) && file_exists( $cust['path'] ) ) {
				$mtime = filemtime( $cust['path'] );
				if ( false !== $mtime ) {
					$cust_version .= '.' . $mtime;
				}
			}
			if ( ! wp_style_is( CustomizeAssets::STYLE_HANDLE, 'registered' ) ) {
				wp_register_style(
					CustomizeAssets::STYLE_HANDLE,
					$cust['url'],
					array( self::STYLE_FORM ),
					$cust_version
				);
			}
			if ( ! wp_style_is( CustomizeAssets::STYLE_HANDLE, 'enqueued' ) ) {
				wp_enqueue_style( CustomizeAssets::STYLE_HANDLE );
			}
		}
		
		$helper = self::get_legacy_helper();
		if ( $helper && method_exists( $helper, 'bookingpress_load_booking_form_custom_css' ) ) {
			$helper->bookingpress_load_booking_form_custom_css();
		}

		// Google font enqueue — mirrors the legacy load path in
		// `class.bookingpress.php::bookingpress_load_booking_form_custom_css()`
		// (lines 5193-5198). The Vue3 customize CSS uses
		// `title_font_family` in `font-family: ...` declarations; if the
		// admin picks a non-default Google font we also need to fetch the
		// webfont so it actually renders.
		self::maybe_enqueue_google_font();

		// PayPal JS SDK — port of legacy `bookingpress_paypal_scripts_add`
		// (class.bookingpress_appointment_bookings.php:683). The legacy hook
		// fires on `bookingpress_add_frontend_js`, gated by
		// `bookingpress_is_front_page()`, which only regex-scans
		// `$wp_query->posts[].post_content` for `[bookingpress_*]` — it never
		// matches when the shortcode is rendered by a page builder template
		// or theme widget, so the SDK silently stays absent and
		// `renderPayPalButtons()` in booking-form.js no-ops. Enqueuing here,
		// at shortcode render time, makes the Vue 3 form self-sufficient no
		// matter where the shortcode lives (footer scripts print after render).
		self::maybe_enqueue_paypal_sdk();
	}

	/**
	 * Retrieve the legacy BookingPress service/category helpers instance.
	 */
	private static function get_legacy_helper() {
		if ( isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ) {
			return $GLOBALS['BookingPress'];
		}
		return null;
	}

	/**
	 * Conditionally enqueue the Google Font webfont for the configured
	 * `title_font_family` customize value. No-op when the value is empty,
	 * is the default `Poppins`, or the global Google fonts list reports
	 * the family is not a Google font (e.g. `Inherit Fonts` / a system
	 * stack).
	 *
	 * @return void
	 */
	private static function maybe_enqueue_google_font() {
		$customize = new CustomizeRepository();
		// Read via `get_group()` (NOT `get()`). The single-key `get()` path
		// stores under a separate `kv_*` transient that
		// `CustomizeRepository::invalidate_group()` does not touch, so after
		// an admin font change the kv_ transient kept handing back the
		// OLD family for up to 15 minutes — the CSS generator would emit
		// the new family, but the Google Fonts URL stayed on the old one.
		// `get_group()` shares the `group_*` transient that the customize
		// save listener already invalidates, so the font URL refreshes in
		// lock-step with the CSS.
		$bf     = $customize->get_group( CustomizeRepository::GROUP_BOOKING_FORM );
		$family = isset( $bf['title_font_family'] ) ? (string) $bf['title_font_family'] : '';
		if ( '' === $family || 'Poppins' === $family || 'Inherit Fonts' === $family ) {
			return;
		}

		// Defer to the legacy global options class for the supported
		// Google fonts list so the Vue 3 path stays consistent with what
		// the admin font picker exposes.
		global $bookingpress_global_options;
		if ( ! is_object( $bookingpress_global_options ) || ! method_exists( $bookingpress_global_options, 'bookingpress_get_google_fonts' ) ) {
			return;
		}
		$fonts = $bookingpress_global_options->bookingpress_get_google_fonts();
		if ( ! is_array( $fonts ) || ! in_array( $family, $fonts, true ) ) {
			return;
		}

		$url = 'https://fonts.googleapis.com/css2?family=' . rawurlencode( $family ) . '&display=swap';
		/** Filter parity with the legacy enqueue. */
		$url = (string) apply_filters( 'bookingpress_modify_google_font_url', $url, $family );

		$handle  = 'bookingpress-form-v3-font-' . sanitize_title( $family );
		$version = defined( 'BOOKINGPRESS_VERSION' ) ? BOOKINGPRESS_VERSION : '1.0.0';
		if ( ! wp_style_is( $handle, 'registered' ) ) {
			wp_register_style( $handle, $url, array(), $version );
		}
		if ( ! wp_style_is( $handle, 'enqueued' ) ) {
			wp_enqueue_style( $handle );
		}
	}

	/**
	 * Enqueue the PayPal JS SDK if PayPal popup mode is enabled and the
	 * credentials are set. Mirrors legacy `bookingpress_paypal_scripts_add`
	 * 1:1 so popup mode behaves the same way in the Vue 3 render path.
	 *
	 * @return void
	 */
	private static function maybe_enqueue_paypal_sdk() {
		$helper = self::get_legacy_helper();
		if ( ! $helper || ! method_exists( $helper, 'bookingpress_get_settings' ) ) {
			return;
		}

		$paypal_payment      = $helper->bookingpress_get_settings( 'paypal_payment', 'payment_setting' );
		$paypal_payment_mode = $helper->bookingpress_get_settings( 'paypal_payment_method_type', 'payment_setting' );

		// Treat the stored value the same way PaymentService::is_truthy does.
		$is_paypal_on = ( 'true' === strtolower( (string) $paypal_payment ) || '1' === (string) $paypal_payment );
		if ( ! $is_paypal_on || 'popup' !== (string) $paypal_payment_mode ) {
			return;
		}

		$client_id     = (string) $helper->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );
		$client_secret = (string) $helper->bookingpress_get_settings( 'paypal_client_secret', 'payment_setting' );
		if ( '' === $client_id || '' === $client_secret ) {
			return;
		}

		$currency_name = (string) $helper->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
		$currency_code = method_exists( $helper, 'bookingpress_get_currency_code' )
			? (string) $helper->bookingpress_get_currency_code( $currency_name )
			: ( '' !== $currency_name ? $currency_name : 'USD' );
		if ( '' === $currency_code ) {
			$currency_code = 'USD';
		}

		// Same handle as the legacy enqueue — when the legacy
		// `bookingpress_is_front_page()` gate DID fire earlier in the request,
		// this is a no-op instead of a double-load.
		if ( ! wp_script_is( 'bookingpress-paypal-script', 'enqueued' ) && ! wp_script_is( 'bookingpress-paypal-script', 'registered' ) ) {
			wp_enqueue_script(
				'bookingpress-paypal-script',
				'https://www.paypal.com/sdk/js?client-id=' . rawurlencode( $client_id ) . '&currency=' . rawurlencode( $currency_code ) . '&disable-funding=credit,card',
				array(),
				null,
				true
			);
		}
	}

	/**
	 * Filter callback for `script_module_data_bookingpress-form-v3-loader`.
	 *
	 * WordPress emits the returned array as a JSON `<script type="application/json">`
	 * island that the loader module reads on parse.
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

		/**
		 * Filter the full Vue3 module data payload before it is emitted.
		 *
		 * @param array $data Module data (includes `instances` map).
		 */
		return apply_filters( Hooks::FILTER_MODULE_DATA, $data );
	}
}

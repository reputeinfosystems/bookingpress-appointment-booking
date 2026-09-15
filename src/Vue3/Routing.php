<?php
/**
 * Routing — Vue3 greenfield path entry point.
 *
 * Owns the version gate (`should_route_to_v3()`), the M1 admin-gated canary
 * shortcode, and a per-instance unique-id generator. **No legacy coupling.**
 *
 * @package BookingPress\Vue3
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §0.1
 */

namespace BookingPress\Vue3;

use BookingPress\Vue3\Cache\FrontendFormCache;
use BookingPress\Vue3\Customize\CustomizeAssets;
use BookingPress\Vue3\REST\RouteRegistrar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static entry point for the Vue3 path.
 *
 * Registered against `plugins_loaded` from `BookingPressLoader::init()`.
 * In M1 the only user-visible side effect is the admin-gated canary
 * shortcode `[bp_form_v3_canary]`. M7 will register the new BookingForm
 * against `[bookingpress_form]` (gated by `should_route_to_v3()`).
 */
class Routing {

	/**
	 * Minimum BookingPress Pro version that owns the Vue 3 frontend contract.
	 *
	 * Pro 6.0+ ships the Vue 3-aware frontend and serves `[bookingpress_form]`
	 * through the Vue 3 renderer. Pro 5.x ships the legacy Vue 2 frontend and
	 * must keep serving it. This threshold is fixed by policy and is
	 * intentionally NOT filterable.
	 *
	 * @var string
	 */
	const PRO_V3_MIN_VERSION = '6.0';

	/**
	 * Idempotency guard — register hooks exactly once per request.
	 *
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Bootstrap the Vue3 path.
	 *
	 * Called from `BookingPressLoader::init()` on `plugins_loaded`.
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$initialized ) {
			return;
		}
		self::$initialized = true;

		// M7: flip — register `[bookingpress_form]` against the Vue 3
		// renderer when the version gate says so (Lite-only or Pro >= 6.0).
		// When the gate refuses (Pro < 6.0), Pro's own legacy override of
		// `[bookingpress_form]` keeps serving the Vue 2 form.
		if ( self::should_route_to_v3() ) {
			add_shortcode( 'bookingpress_form', array( BookingForm::class, 'render_shortcode' ) );
		}

		// `[bookingpress_form_vue3]` remains as an internal alias to the new
		// renderer through one deprecation cycle (plan §7). Any existing
		// page using this shortcode gets the new form for free.
		add_shortcode( 'bookingpress_form_vue3', array( BookingForm::class, 'render_shortcode' ) );

		// M4: register the eight `/form-v3/*` REST routes on `rest_api_init`.
		add_action( 'rest_api_init', array( RouteRegistrar::class, 'register' ) );

		// Block-theme (FSE) import-map fix. On block themes WordPress prints
		// the script-module import map in `wp_head`, but the Vue 3 form is
		// enqueued at shortcode-render time in the body (page builders like
		// Elementor, content shortcodes) — long after `wp_head` fired. The
		// head import map is therefore printed without our specifier, and the
		// module `<script>` core still emits in the footer throws
		// "Failed to resolve module specifier bookingpress-form-v3".
		// Relocating the import map to the footer (as classic themes already
		// do) fixes it. Deferred to `init` so core's `after_setup_theme`
		// hook registration has already run; guarded to block themes only.
		add_action( 'init', array( self::class, 'relocate_script_modules_for_block_theme' ) );

		// M5: register the customize-CSS lifecycle listeners
		// (invalidate-cache action + on-render fallback). Cache invalidation
		// for the data layer is handled below via FrontendFormCache; this
		// init() now only owns CSS-file regeneration.
		CustomizeAssets::init();

		// MB-6D: Lite-only default for the Vue 3 My Booking shortcode. The
		// callback opts a request into Vue 3 ONLY when Pro is inactive AND WP
		// Script Modules are available; Pro stays on the legacy renderer until
		// its parity blockers are closed. Attached lazily here (evaluated at
		// shortcode time); a later explicit filter (e.g. `__return_true` for Pro
		// dev/testing) still wins. Default off when the class is unavailable.
		if ( class_exists( '\BookingPress\Vue3\MyBookings\MyBookings' ) ) {
			\BookingPress\Vue3\MyBookings\MyBookings::init();
		}

		// Centralized frontend-form cache invalidation.
		//
		// Every admin save that affects what the Vue 3 booking form renders
		// bumps a single integer version counter (`bookingpress_frontend_
		// form_cache_version`). That counter is composed into every cache
		// key produced by `FrontendFormCache::key()`, so one bump orphans
		// all prior cache rows in one move — no per-key delete_transient
		// scatter, no SQL LIKE delete, no duplicated listener wiring.
		//
		// Each closure receives the literal hook name so the bump's `$reason`
		// argument carries useful observability when WP_DEBUG is on.
		$bump_hooks = array(
			// General settings save (includes default_time_slot_step, time
			// format, share-timeslot, show-time-as-per-service-duration,
			// phone default country, timeslot grouping). Hook name has an
			// intentional triple-o typo in the legacy source.
			'boookingpress_after_save_settings_data'           => 'general_settings_saved',
			// Default work hours save.
			'wp_ajax_bookingpress_save_default_work_hours'     => 'workhours_saved',
			// Days-off / special-days save.
			'wp_ajax_bookingpress_save_default_daysoff_details'=> 'daysoff_saved',
			// Customize panel saves (form labels/colors/fonts, my-bookings).
			'bookingpress_after_save_customize_settings'       => 'customize_saved',
			'wp_ajax_bookingpress_save_form_settings'          => 'form_settings_saved',
			'wp_ajax_bookingpress_save_my_booking_settings'    => 'my_booking_settings_saved',
			// Form-field schema save (Terms & Conditions visibility, labels).
			'wp_ajax_bookingpress_save_field_settings'         => 'form_fields_saved',
			// Service catalog mutations.
			'wp_ajax_bookingpress_add_service'                 => 'service_added',
			'wp_ajax_bookingpress_edit_service'                => 'service_edited',
			'wp_ajax_bookingpress_delete_service'              => 'service_deleted',
			'wp_ajax_bookingpress_bulk_service'                => 'services_bulk',
			'wp_ajax_bookingpress_position_services'           => 'services_reordered',
			'wp_ajax_bookingpress_duplicate_service'           => 'service_duplicated',
			// Category catalog mutations.
			'wp_ajax_bookingpress_add_categories'              => 'category_added',
			'wp_ajax_bookingpress_edit_category'               => 'category_edited',
			'wp_ajax_bookingpress_delete_category'             => 'category_deleted',
			'wp_ajax_bookingpress_bulk_category'               => 'categories_bulk',
			'wp_ajax_bookingpress_position_categories'         => 'categories_reordered',
		);

		foreach ( $bump_hooks as $hook => $reason ) {
			add_action( $hook, static function () use ( $reason ) {
				FrontendFormCache::bump( $reason );
			}, 1 );
		}
	}

	/**
	 * Relocate WordPress's script-module import-map printing from `wp_head`
	 * to `wp_footer` on block (FSE) themes.
	 *
	 * Since WP 6.5, {@see \WP_Script_Modules::add_hooks()} prints the import
	 * map (and, on 6.9+, the head-enqueued module scripts) in `wp_head` for
	 * block themes, but in `wp_footer` for classic themes. The head position
	 * assumes every script module is discoverable while the block template
	 * renders during `wp_head`.
	 *
	 * The Vue 3 booking form is enqueued at shortcode-render time, deep in the
	 * page body — which is exactly where page builders (Elementor) and content
	 * shortcodes run, long AFTER `wp_head` has fired. On a block theme the
	 * import map is therefore printed WITHOUT the `bookingpress-form-v3`
	 * specifier, yet core still prints the form's `<script type="module">` in
	 * the footer (`print_enqueued_script_modules`, always on `wp_footer`). The
	 * browser then resolves the bare specifier against a map that lacks it:
	 *
	 *   Failed to resolve module specifier "bookingpress-form-v3".
	 *   Relative references must start with either "/", "./", or "../".
	 *
	 * Classic themes never hit this because the import map is already printed
	 * in the footer, after the body (and thus our enqueue) has run.
	 *
	 * Fix: on block themes, move the import-map printing to `wp_footer`
	 * priority 1 — ahead of core's enqueued-module printing at the default
	 * priority 10 — so the footer contains one fully-populated import map
	 * before every module script. This reproduces the classic-theme ordering
	 * and is safe: `type="module"` scripts are deferred, so emitting them (and
	 * the map) in the footer instead of the head does not change execution
	 * timing, and `print_script_module()`'s shared `done` guard prevents any
	 * double-print. `modulepreload` links are left in the head untouched (they
	 * carry resolved URLs, not bare specifiers, so they need no import map).
	 *
	 * Registered on `init` (fires after core's `after_setup_theme` hook
	 * registration, before `wp_head`) so the `remove_action()` calls target
	 * the hooks core has already added.
	 *
	 * @since 1.1
	 *
	 * @return void
	 */
	public static function relocate_script_modules_for_block_theme() {
		if ( ! function_exists( 'wp_script_modules' ) || ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
			// Classic themes already print the import map in the footer; no WP
			// Script Modules API means nothing to relocate.
			return;
		}

		$modules = wp_script_modules();

		// Import map: relocate head → footer, ahead of the module scripts.
		if ( false !== has_action( 'wp_head', array( $modules, 'print_import_map' ) ) ) {
			remove_action( 'wp_head', array( $modules, 'print_import_map' ) );
			add_action( 'wp_footer', array( $modules, 'print_import_map' ), 1 );
		}

		// WP 6.9+: head-enqueued module scripts must not print in the head now
		// that the import map is gone from it, or they resolve specifiers
		// against a missing map. Core's footer printer
		// (`print_enqueued_script_modules`, wp_footer @10) re-emits them after
		// the relocated map, and its shared `done` guard stops double-printing.
		if ( method_exists( $modules, 'print_head_enqueued_script_modules' ) ) {
			remove_action( 'wp_head', array( $modules, 'print_head_enqueued_script_modules' ) );
		}

		// WP 6.5–6.8 printed ALL enqueued modules at the head position on block
		// themes. If that hook is present, relocate it to the footer too so it
		// still follows the (now-footer) import map.
		if ( false !== has_action( 'wp_head', array( $modules, 'print_enqueued_script_modules' ) ) ) {
			remove_action( 'wp_head', array( $modules, 'print_enqueued_script_modules' ) );
			add_action( 'wp_footer', array( $modules, 'print_enqueued_script_modules' ), 10 );
		}

		// Module preloads MUST follow the import map too. On block themes core
		// prints `<link rel="modulepreload">` in `wp_head` (via
		// `print_script_module_preloads`). A modulepreload begins fetching the
		// module graph, which — per the HTML spec — locks the document's
		// "import maps allowed" flag. Any import map encountered AFTER that
		// point (our relocated footer map) is then silently ignored by the
		// browser, so every bare specifier fails to resolve:
		//
		//   The specifier "@wordpress/interactivity" was a bare specifier, but
		//   was not remapped to anything.
		//   The specifier "bookingpress-form-v3" was a bare specifier, but was
		//   not remapped to anything.
		//
		// Note both core AND our specifiers fail — the tell-tale sign the map
		// was rejected wholesale, not merely under-populated. This is invisible
		// to a static "view source" check because the map is still textually
		// before the module <script> tags; only the earlier head modulepreloads
		// break it. Relocating the preloads to the footer (priority 2, after the
		// import map at 1, before the module scripts at 10) leaves nothing
		// module-related in the head, so the footer import map is the first
		// module token the browser sees and is honored. This reproduces the
		// classic-theme ordering, where preloads already print in the footer.
		if ( false !== has_action( 'wp_head', array( $modules, 'print_script_module_preloads' ) ) ) {
			remove_action( 'wp_head', array( $modules, 'print_script_module_preloads' ) );
			add_action( 'wp_footer', array( $modules, 'print_script_module_preloads' ), 2 );
		}
	}

	/**
	 * Version gate — should the new Vue3 renderer serve this request?
	 *
	 * Returns true iff:
	 *   - Pro is inactive, OR
	 *   - Pro is active AND its version is >= {@see self::PRO_V3_MIN_VERSION}
	 *     (`'6.0'`).
	 *
	 * The threshold is fixed by policy (Pro 6.0+ owns the Vue 3 frontend
	 * contract; 5.x stays on the legacy Vue 2 form) and is intentionally not
	 * filterable. The gate is **fail-closed**: if Pro is active but the
	 * version cannot be read for any reason (`get_pro_version()` returns
	 * null), the gate returns false and the legacy Vue2 renderer keeps
	 * serving the request.
	 *
	 * @return bool
	 */
	public static function should_route_to_v3() {
		if ( ! self::is_pro_active() ) {
			return true;
		}

		$pro_version = self::get_pro_version();
		if ( null === $pro_version ) {
			// Fail-closed: Pro is active but version is unknown.
			return false;
		}

		return version_compare( $pro_version, self::PRO_V3_MIN_VERSION, '>=' );
	}

	/**
	 * Whether the BookingPress Pro add-on is currently active.
	 *
	 * Ported from `src/frontend/BookingForm.php::is_pro_active()` (L104-L113)
	 * but lifted into Routing so the Vue3 path has zero references to the
	 * intermediate `BookingPress\frontend\BookingForm` class.
	 *
	 * @return bool
	 */
	public static function is_pro_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			$plugin_file = ABSPATH . 'wp-admin/includes/plugin.php';
			if ( ! file_exists( $plugin_file ) ) {
				return false;
			}
			require_once $plugin_file;
		}
		return is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' );
	}

	/**
	 * Read Pro's plugin header version defensively.
	 *
	 * Prefers the `$bookingpress_pro_version` global (Pro publishes it during
	 * its bootstrap) and falls back to `get_plugin_data()`. Returns null on
	 * any failure — callers must treat null as "unknown" and fail-closed.
	 *
	 * @return string|null
	 */
	public static function get_pro_version() {
		// Preferred — Pro publishes this global early in its bootstrap.
		if ( isset( $GLOBALS['bookingpress_pro_version'] ) && '' !== $GLOBALS['bookingpress_pro_version'] ) {
			return (string) $GLOBALS['bookingpress_pro_version'];
		}

		// Fallback — read the plugin header. Requires WP admin includes.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			$plugin_file = ABSPATH . 'wp-admin/includes/plugin.php';
			if ( ! file_exists( $plugin_file ) ) {
				return null;
			}
			require_once $plugin_file;
		}

		$pro_main = WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php';
		if ( ! file_exists( $pro_main ) ) {
			return null;
		}

		$header = @get_plugin_data( $pro_main, false, false );
		if ( ! is_array( $header ) || empty( $header['Version'] ) ) {
			return null;
		}
		return (string) $header['Version'];
	}

	/**
	 * Generate a per-render unique instance id.
	 *
	 * Used as the DOM id (`bp-v3-form-{uniqId}`) and as the key in the
	 * per-instance state registry (M5).
	 *
	 * @return string Short opaque id (12 hex chars).
	 */
	public static function generate_unique_id() {
		return substr( md5( uniqid( 'bp_v3_', true ) . wp_rand() ), 0, 12 );
	}
}

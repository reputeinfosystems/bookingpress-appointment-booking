<?php
/**
 * CustomizeAssets — lifecycle owner for the generated form-v3-custom.css file.
 *
 * Wires:
 *   - `bookingpress_form_v3_invalidate_cache` action → regen the file.
 *   - `updated_option` watcher → opportunistic regen when the legacy admin
 *     customize panel mutates the `bookingpress_customize_settings`
 *     option-like keys.
 *   - `bookingpress_after_save_customize_settings` → regen the file when
 *     the legacy customize panel saves (it writes to a custom table, so
 *     `updated_option` doesn't fire).
 *   - On-render fallback: if the file doesn't exist when a shortcode is
 *     about to enqueue it, generate it inline first.
 *
 * Scope: this class is responsible **only** for keeping the scoped CSS
 * file fresh. Data-layer cache invalidation (repositories, timeslots) is
 * handled centrally in {@see \BookingPress\Vue3\Routing::init()} via
 * {@see \BookingPress\Vue3\Cache\FrontendFormCache::bump()}.
 *
 * @package BookingPress\Vue3\Customize
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §6
 */

namespace BookingPress\Vue3\Customize;

use BookingPress\Vue3\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomizeAssets {

	/** Asset handle for the generated stylesheet. */
	const STYLE_HANDLE = 'bookingpress-form-v3-custom-css';

	/** @var bool Idempotency guard for the action listener. */
	private static $bootstrapped = false;

	/** @var bool Per-request "freshly regenerated" flag — avoid double work. */
	private static $regenerated_this_request = false;

	/**
	 * Register hook listeners. Runs once per request from Routing::init().
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$bootstrapped ) {
			return;
		}
		self::$bootstrapped = true;

		add_action( Hooks::ACTION_INVALIDATE_CACHE, array( static::class, 'regenerate' ) );

		// Opportunistic admin-side watcher: legacy admin save handlers
		// don't (yet) fire our action, so we also listen on common signals.
		// `updated_option` fires for a number of customize-side writes;
		// gating on the name prefix keeps the cost negligible.
		add_action( 'updated_option', array( static::class, 'maybe_regen_on_option_change' ), 10, 1 );

		// The legacy admin customize panel writes directly to the
		// `bookingpress_customize_settings` table (it does NOT call
		// `update_option`), so `updated_option` never fires for label /
		// color / font edits. Listen on the dedicated action to regen our
		// scoped CSS file (so colors/fonts refresh immediately).
		// Data-layer cache invalidation for this same hook is handled
		// centrally in `Routing::init()` via `FrontendFormCache::bump()`.
		add_action( 'bookingpress_after_save_customize_settings', array( static::class, 'regenerate' ), 10, 0 );
	}

	/**
	 * Force a regen of the CSS file.
	 *
	 * Public — can be called from anywhere. Idempotent within a single
	 * request.
	 *
	 * @return array{ path:string|null, url:string|null }
	 */
	public static function regenerate() {
		if ( self::$regenerated_this_request ) {
			return CustomizeCssGenerator::resolve_paths();
		}
		self::$regenerated_this_request = true;
		$out = ( new CustomizeCssGenerator() )->write_file();
		return $out;
	}

	/**
	 * Listener for `updated_option` that triggers regen when the option key
	 * suggests a customize-related write.
	 *
	 * @param string $option_name
	 *
	 * @return void
	 */
	public static function maybe_regen_on_option_change( $option_name ) {
		$option_name = (string) $option_name;
		if ( '' === $option_name ) {
			return;
		}
		// Cheap prefix sniff: any option starting with `bookingpress_`
		// might affect appearance. False positives are OK — regen is cheap.
		if ( 0 === strpos( $option_name, 'bookingpress_customize' )
			|| 0 === strpos( $option_name, 'bookingpress_booking_form' )
			|| 0 === strpos( $option_name, 'bookingpress_my_booking' ) ) {
			self::regenerate();
		}
	}

	/**
	 * Ensure the CSS file exists, generating it on demand if not.
	 *
	 * Called from `Assets::enqueue_for_render()` before the style is
	 * enqueued so the very first render after install / upgrade still
	 * shows customized colors.
	 *
	 * @return array{ path:string|null, url:string|null, exists:bool }
	 */
	public static function ensure_exists() {
		$paths = CustomizeCssGenerator::resolve_paths();
		if ( null === $paths['path'] ) {
			return array( 'path' => null, 'url' => null, 'exists' => false );
		}

		// If the generator source has been updated since the file was last
		// written, regen. This way edits to CustomizeCssGenerator.php take
		// effect on the next page render without requiring an admin save.
		$needs_regen = ! file_exists( $paths['path'] );
		if ( ! $needs_regen ) {
			$gen_path = __DIR__ . '/CustomizeCssGenerator.php';
			if ( file_exists( $gen_path ) ) {
				$file_mtime = (int) filemtime( $paths['path'] );
				$gen_mtime  = (int) filemtime( $gen_path );
				if ( $gen_mtime > $file_mtime ) {
					$needs_regen = true;
				}
			}
		}

		if ( $needs_regen ) {
			$written = self::regenerate();
			return array(
				'path'   => $written['path'],
				'url'    => $written['url'],
				'exists' => null !== $written['path'] && file_exists( $written['path'] ),
			);
		}
		return array(
			'path'   => $paths['path'],
			'url'    => $paths['url'],
			'exists' => true,
		);
	}
}

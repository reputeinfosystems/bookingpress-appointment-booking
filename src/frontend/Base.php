<?php
/**
 * Base controller for all BookingPress frontend (shortcode / public) features.
 *
 * Mirrors the architectural pattern of `BookingPress\admin\Base` but is
 * specialised for the public side: assets are registered on
 * `wp_enqueue_scripts` and conditionally enqueued by the child controller
 * only when its entry point (e.g. a shortcode) is actually rendered.
 *
 * @package BookingPress
 */

namespace BookingPress\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract base class for frontend controllers.
 */
abstract class Base {

	/**
	 * Plugin version used for asset cache-busting.
	 *
	 * @var string
	 */
	protected static $version = BOOKINGPRESS_VERSION;

	/**
	 * Common initialization. Child controllers should call `parent::init()`.
	 *
	 * Hooks `register_assets` on `wp_enqueue_scripts` so module/style
	 * handles exist before any shortcode callback runs.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( static::class, 'register_assets' ) );
	}

	/**
	 * Register script modules and styles. Override in child classes.
	 *
	 * Child implementations should REGISTER only; enqueuing is expected
	 * to happen inside the concrete render entry point (shortcode, etc.)
	 * so that assets load only on pages where they are actually used.
	 *
	 * @return void
	 */
	public static function register_assets() {
		// Child classes implement specific registration here.
	}

	/**
	 * Render a frontend view template into a buffered string.
	 *
	 * Frontend views live under `src/view/frontend/` (separate from the
	 * admin `src/views/` directory used by `BookingPress\admin\Base`).
	 *
	 * @param string $view_name Template filename without the `.php` extension.
	 * @param array  $data      Associative array of data to expose to the view.
	 *
	 * @return string Rendered HTML (empty string if template is missing).
	 */
	protected static function render_view( $view_name, $data = array() ) {
		$view_file = BOOKINGPRESS_DIR . '/src/view/frontend/' . $view_name . '.php';

		/**
		 * Filter the absolute path of a frontend view file before it is loaded.
		 *
		 * @param string $view_file Absolute filesystem path.
		 * @param string $view_name View identifier (without extension).
		 * @param array  $data      Data passed to the view.
		 */
		$view_file = apply_filters( 'bookingpress_frontend_view_path', $view_file, $view_name, $data );

		if ( ! is_string( $view_file ) || ! file_exists( $view_file ) ) {
			error_log( 'BookingPress: Frontend view file not found: ' . (string) $view_file ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return '';
		}

		if ( ! empty( $data ) ) {
			extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		ob_start();
		include $view_file;
		return (string) ob_get_clean();
	}
}

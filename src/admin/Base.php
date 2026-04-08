<?php
namespace BookingPress\admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Base class for all Admin Controllers
 */
abstract class Base {
    
    protected static $slug    = '';
    protected static $version = BOOKINGPRESS_VERSION;

    /**
     * Common initialization logic
     */
    public static function init() {
        // Automatically hook enqueuing for all child classes
        add_action( 'admin_enqueue_scripts', [ static::class, 'enqueue_assets' ] );
    }

    /**
     * Enqueue assets - can be overridden by child classes
     */
    public static function enqueue_assets( $hook ) {
        // Child classes implement specific enqueuing here
    }

    /**
     * Common View Rendering Helper
     * 
     * @param string $view_name The filename (without .php) in src/Views/
     * @param array  $data      Associative array of data to pass to the view
     */
    protected static function render_view( $view_name, $data = [] ) {
        $view_file = BOOKINGPRESS_DIR . "/src/views/{$view_name}.php";
        if ( file_exists( $view_file ) ) {
            extract( $data );
            require_once $view_file;
        } else {
            error_log( "BookingPress: View file not found: {$view_file}" );
        }
    }
}

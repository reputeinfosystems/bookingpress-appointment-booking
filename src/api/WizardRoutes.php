<?php
namespace BookingPress\api;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WizardRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/wizard/save', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'bookingpress_save_wizard_settings_rest' ],
            'permission_callback' => [ $this, 'permission_callback' ],
        ] );

        register_rest_route( 'bookingpress-app/v1', '/wizard/skip', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'bookingpress_skip_wizard_rest' ],
            'permission_callback' => [ $this, 'permission_callback' ],
        ] );

        register_rest_route( 'bookingpress-app/v1', '/wizard/download-product', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'bookingpress_download_wizard_product_rest' ],
            'permission_callback' => [ $this, 'permission_callback' ],
        ] );
    }

    public function permission_callback() {
        return current_user_can( 'manage_options' );
    }

    public function bookingpress_save_wizard_settings_rest( $request ) {
        global $BookingPress;
        $params = $request->get_json_params();

        if ( isset( $params['wizard_data'] ) ) {
            $_POST['wizard_data'] = $params['wizard_data'];
            $_REQUEST['wizard_data'] = $params['wizard_data'];
        } else {
            $_POST['wizard_data'] = $params;
            $_REQUEST['wizard_data'] = $params;
        }

        $_POST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'];

        delete_option( 'bookingpress_lite_wizard_complete' );

        $response = $BookingPress->bookingpress_save_lite_wizard_settings_func(true);

        return new \WP_REST_Response( $response ?: [ 'variant' => 'success', 'title' => esc_html__( 'Success', 'bookingpress-appointment-booking' ), 'msg' => esc_html__( 'Data saved successfully', 'bookingpress-appointment-booking' ) ], 200 );
    }

    public function bookingpress_skip_wizard_rest( $request ) {
        global $BookingPress;
        $_POST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'];

        $response = $BookingPress->bookingpress_skip_wizard_func(true);

        return new \WP_REST_Response( $response ?: [ 'variant' => 'success' ], 200 );
    }

    public function bookingpress_download_wizard_product_rest( $request ) {
        global $BookingPress;

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $params = $request->get_json_params();
        
        $normalized_params = array_map(function($value) { if ($value === true) return 'true'; if ($value === false) return 'false'; return $value; }, (array)$params);
        $_POST = $normalized_params;
        $_REQUEST = $normalized_params;
        $_POST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'];

        $response = $BookingPress->bookingpress_download_wizard_product_func(true);

        return new \WP_REST_Response( $response, 200 );
    }
}

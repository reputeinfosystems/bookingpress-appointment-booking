<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

class AddonsRoutes extends Base {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {

        register_rest_route( 'bookingpress-app/v1', '/addons/list', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_addons_list_data' ],
            'permission_callback' => $this->permission_callback_for('add_calendar_appointments')
        ] );
    }

    function get_addons_list_data($request){

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        global $bookingpress_addons;
        $response = $bookingpress_addons->bookingpress_get_remote_addons_lite_list_func();

        return new \WP_REST_Response( [
            'success' => true,
            'data' => $response
        ], 200 );
    }

}
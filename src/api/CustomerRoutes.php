<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

class CustomerRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/customer', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_customer' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'bookingpress_calendar' );
            }
        ] );
        register_rest_route( 'bookingpress-app/v1', '/customer/create', [
            'methods'  => 'POST',
            'callback' => [ $this, 'create_customer' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'add_customer' );
            }
        ] );
        register_rest_route( 'bookingpress-app/v1', '/customer/fetch_wp_users', [
            'methods'  => 'POST',
            'callback' => [ $this, 'fetch_wp_users' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'search_user' );
            }
        ] );
        register_rest_route( 'bookingpress-app/v1', '/customer/get_existing_user_details', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_existing_user_details' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'search_user' );
            }
        ] );
    }

    public function get_existing_user_details( $request ) {
        $wordpress_user_id = $request->get_param( 'wordpress_user_id' );
        global $bookingpress_customers;

        $_REQUEST['existing_user_id'] = $wordpress_user_id;
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        $response = $bookingpress_customers->bookingpress_get_existing_user_details( true );

        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }

        $json_data = [
            'success' => true,
            'data' => $response['user_details']
        ];

        return new \WP_REST_Response( $json_data, 200 );
    }

    public function get_customer( $request ) {

        global $bookingpress_calendar, $bookingpress_appointment;

        $_REQUEST['search_user_str'] = $request->get_param( 'search' );
        $_REQUEST['customer_id'] = $request->get_param( 'customer_id' ) ?? '0';
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        if( !empty( $_REQUEST['customer_id'] ) ) {
            $response = $bookingpress_appointment->bookingpress_bpa_fetch_customer_details( true );
        } else {   
            $response = $bookingpress_calendar->bookingpress_get_customer_list_func( true );
        }

        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }

        $json_data = [
            'success' => true,
            'data' => $response['appointment_customers_details']
        ];

        return new \WP_REST_Response( $json_data, 200 );
    }

    public function fetch_wp_users( $request ) {
        $query = $request->get_param( 'query' );
        global $bookingpress_customers;
        $_REQUEST['search_user_str'] = $query;
        $_REQUEST['wordpress_user_id'] = $request->get_param( 'wordpress_user_id' ) ?? '0';
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        $response = $bookingpress_customers->bookingpress_get_wpuser( true );

        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }

        $json_data = [
            'success' => true,
            'data' => $response['users']
        ];

        return new \WP_REST_Response( $json_data, 200 );
    }

    public function create_customer( $request ) {
        $customer_data = $request->get_param( 'customer_data' );
        
        $_REQUEST['wp_user'] = $customer_data['wp_user'];
        $_REQUEST['username'] = $customer_data['username'];
        $_REQUEST['firstname'] = $customer_data['firstname'];
        $_REQUEST['lastname'] = $customer_data['lastname'];
        $_REQUEST['email'] = $customer_data['email'];
        $_REQUEST['password'] = $customer_data['password'];
        $_REQUEST['phone'] = $customer_data['phone'];
        $_REQUEST['customer_phone_country'] = $customer_data['customer_phone_country'];
        $_REQUEST['customer_phone_dial_code'] = $customer_data['customer_phone_dial_code'];
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        global $bookingpress_customers;

        $response = $bookingpress_customers->bookingpress_add_customer( false, true );

        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }

        $json_data = [
            'success' => true,
            'data' => $response
        ];

        return new \WP_REST_Response( $json_data, 200 );
    }
}
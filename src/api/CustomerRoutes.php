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
            'permission_callback' => $this->permission_callback_for( 'retrieve_customers' )
        ] );
        register_rest_route( 'bookingpress-app/v1', '/customer/create', [
            'methods'  => 'POST',
            'callback' => [ $this, 'create_customer' ],
            'permission_callback' => $this->permission_callback_for( 'add_customer' )
        ] );
        register_rest_route( 'bookingpress-app/v1', '/customer/fetch_wp_users', [
            'methods'  => 'POST',
            'callback' => [ $this, 'fetch_wp_users' ],
            'permission_callback' => $this->permission_callback_for( 'search_user' )
        ] );
        register_rest_route( 'bookingpress-app/v1', '/customer/get_existing_user_details', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_existing_user_details' ],
            'permission_callback' => $this->permission_callback_for( 'search_user' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/customer/upload_avatar', [
            'methods'  => 'POST',
            'callback' => [ $this, 'upload_customer_avatar' ],
            'permission_callback' => $this->permission_callback_for( 'upload_customer_avatar' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/customer/remove_avatar', [
            'methods'  => 'POST',
            'callback' => [ $this, 'remove_customer_avatar' ],
            'permission_callback' => $this->permission_callback_for( 'remove_customer_avatar' )
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

        $_REQUEST['avatar_name'] = isset($customer_data['avatar_name'])? $customer_data['avatar_name']: "";
        $_REQUEST['avatar_url'] = isset($customer_data['avatar_url'])? $customer_data['avatar_url']: "";
        $_REQUEST['update_id'] = isset($customer_data['update_id'])? $customer_data['update_id']: 0;

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

    public function upload_customer_avatar( $request ) {
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $return_data = array(
            'error'            => 0,
            'msg'              => '',
            'upload_url'       => '',
            'upload_file_name' => '',
        );

        if (
            ! isset($_FILES['file']) ||
            empty($_FILES['file']['tmp_name'])
        ) {
            return new \WP_REST_Response([
                'error' => 1,
                'msg'   => 'No file received'
            ], 400);
        }

        $file = $_FILES['file'];
        $file_name = isset($file['name']) ? current_time('timestamp') . '_' . sanitize_file_name($file['name']) : '';

        $upload_dir = BOOKINGPRESS_TMP_IMAGES_DIR . '/';
        $upload_url = BOOKINGPRESS_TMP_IMAGES_URL . '/';

        if ( ! file_exists($upload_dir) ) {
            wp_mkdir_p($upload_dir);
        }

        $destination = $upload_dir . $file_name;
        $check_file = wp_check_filetype_and_ext(
            $file['tmp_name'],
            $file['name']
        );

        if ( empty( $check_file['ext'] ) ) {
            $return_data['error'] = 1;
            $return_data['msg']   = esc_html__('Invalid file extension. Please select valid file', 'bookingpress-appointment-booking');
            return new \WP_REST_Response($return_data, 200);
        }

        $upload_file = move_uploaded_file($file['tmp_name'], $destination);
        if ( ! $upload_file ) {
            $return_data['error'] = 1;
            $return_data['msg']   = esc_html__('Something went wrong while updating the file', 'bookingpress-appointment-booking');
            return new \WP_REST_Response($return_data, 200);
        }

        $return_data['error']            = 0;
        $return_data['msg']              = esc_html__('Avatar uploaded successfully', 'bookingpress-appointment-booking');
        $return_data['upload_url']       = $upload_url . $file_name;
        $return_data['upload_file_name'] = $file_name;

        return new \WP_REST_Response($return_data, 200);
    }

    public function remove_customer_avatar( $request ){
        global $wpdb;

        $response = array();
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        // REST input instead of $_POST
        $upload_file_url = $request->get_param('upload_file_url');

        if ( ! empty( $upload_file_url ) ) {

            $bookingpress_uploaded_avatar_url = esc_url_raw($upload_file_url);
            $bookingpress_file_name_arr = explode('/', $bookingpress_uploaded_avatar_url);
            $bookingpress_file_name = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];

            if ( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ) {
                wp_delete_file( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name );
            }

            $response['variant'] = 'success';
            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Avatar removed successfully.', 'bookingpress-appointment-booking');

            return new \WP_REST_Response($response, 200);
        }

        $response['variant'] = 'error';
        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('No file provided.', 'bookingpress-appointment-booking');

        return new \WP_REST_Response($response, 400);
    }

}
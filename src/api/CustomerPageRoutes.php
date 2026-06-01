<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

class CustomerPageRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/customer/fetch', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_customer_page_details' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'retrieve_customers' );
            }
        ] );

        register_rest_route( 'bookingpress-app/v1', '/customer/bulk-delete', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_customer_bulk_delete' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'delete_customer' );
            }
        ] );
            
        register_rest_route( 'bookingpress-app/v1', '/customer/delete', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_customer_delete' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'delete_customer' );
            }
        ] );

        register_rest_route( 'bookingpress-app/v1', '/customer/edit', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_customer_edit' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'delete_customer' );
            }
        ] );        


        register_rest_route( 'bookingpress-app/v1', '/customer/export_customer_fetch', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_export_customer_fetch' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'customer_export' );
            }
        ] ); 

        register_rest_route( 'bookingpress-app/v1', '/customer/import-file', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_import_customer_process_upload' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'customer_import' );
            }
        ] ); 

        register_rest_route( 'bookingpress-app/v1', '/customer/import', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_import_customers_details' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'customer_import' );
            }
        ] );         

    }

    function get_customer_page_details($request){
        global $bookingpress_customers;
        $search_data = $request->get_param( 'search_data' );
        $currentpage = $request->get_param( 'currentpage' );
        $custom_filter_val = $request->get_param( 'custom_filter_val' );
        $perpage = $request->get_param( 'perpage' );

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['search_data'] = $search_data;
        $_POST['currentpage'] = $_REQUEST['currentpage'] = $currentpage;
        $_REQUEST['custom_filter_val'] = $custom_filter_val;
        $_POST['perpage'] = $_REQUEST['perpage'] = $perpage;

        $response = $bookingpress_customers->bookingpress_get_customer_details();

        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }

        $json_data = [
            'success' => true,
            'data' => $response['appointment_customers_details']
        ];

        return new \WP_REST_Response( $json_data, 200 );

    }

    function bpa_customer_bulk_delete($request){

        $_POST['_wpnonce'] = $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $bulk_action = $request->get_param( 'bulk_action' );
        $delete_ids = $request->get_param( 'delete_ids' );
        
        $_POST['bulk_action'] = $_REQUEST['bulk_action'] = $bulk_action;
        $_POST['delete_ids'] = $_REQUEST['delete_ids'] = $delete_ids;

        global $bookingpress_customers;
        $response = $bookingpress_customers->bookingpress_bulk_action();

        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }
        return new \WP_REST_Response( $json_data, 200 );
    }

    function bpa_customer_delete($request){
        global $bookingpress_customers;
        $_POST['customer_id'] = $_REQUEST['customer_id'] = $customer_id = $request->get_param( 'customer_id' ); 
        $response = $bookingpress_customers->bookingpress_delete_customer( $customer_id );

        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }
        return new \WP_REST_Response( $json_data, 200 );
    }

    function bpa_customer_edit($request){
        global $bookingpress_customers;
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_POST['edit_id'] = $_REQUEST['edit_id'] = $customer_id = $request->get_param( 'edit_id' ); 
        $response = $bookingpress_customers->bookingpress_get_edit_user_details( $customer_id );
        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }
        return new \WP_REST_Response( $json_data, 200 );
    }

    function bpa_export_customer_fetch($request){
        global $bookingpress_customers;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        $export_field = $request->get_param( 'export_field' ); 

        $search_data = $request->get_param( 'search_data' ); 

        $_REQUEST['export_field'] = $export_field;
        $_REQUEST['search_data'] = $search_data;

        $response = $bookingpress_customers->bookingpress_export_customer_data_lite_func();
        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }
        return new \WP_REST_Response( $json_data, 200 );
    }

    function bpa_import_customer_process_upload($request){

        global $bookingpress_customers;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bookingpress_upload_import_customer_file' );
        $_REQUEST['action']   = 'bookingpress_upload_import_customer_file';

        $files = $request->get_file_params();   

        if( !empty( $files['file'] ) ) {
            $_FILES['file'] = $files['file'];
        }
        
        ob_start();
        $bookingpress_customers->bookingpress_upload_customer_import_file_func();
        $output = ob_get_clean();

        $response = json_decode( $output, true );

        if ( ! empty( $response['error'] ) && $response['error'] == 1 ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }

        return new \WP_REST_Response( $response, 200 );

    }
    
    function bpa_import_customers_details($request){
        global $bookingpress_customers;
        
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        $_POST['import_file_name'] = $request->get_param('import_file_name');

        $value = strtolower((string) $request->get_param('create_wp_user'));

        $_POST['create_wp_user'] = ($value === 'true' || $value === '1') ? 'true' : 'false';

        $_POST['import_file_fields'] = $request->get_param('import_file_fields');

        $response = $bookingpress_customers->bookingpress_import_customers_func();
        if( 'error' === $response['variant'] ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }
        return new \WP_REST_Response( $json_data, 200 );
    }


}
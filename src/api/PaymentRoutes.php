<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

class PaymentRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/payments', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_payments' ],
            'permission_callback' => $this->permission_callback_for( 'retrieve_payments' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/payments/bulk-delete', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bulk_delete_payments' ],
            'permission_callback' => $this->permission_callback_for( 'delete_payments' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/payments/change-status', [
            'methods'  => 'POST',
            'callback' => [ $this, 'change_payment_status' ],
            'permission_callback' => $this->permission_callback_for( 'change_payment_status' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/payments/approve-appointment', [
            'methods'  => 'POST',
            'callback' => [ $this, 'approve_appointment' ],
            'permission_callback' => $this->permission_callback_for( 'approve_appointments' )
        ] );
    }

    /**
     * Legacy payment methods validate `_wpnonce` from the request superglobals,
     * so pass the client's `bpa_wp_nonce` through instead of minting a new one.
     *
     * @param \WP_REST_Request $request
     * @return void
     */
    private function bookingpress_set_legacy_nonce( $request ) {
        $nonce = sanitize_text_field( (string) $request->get_param( '_wpnonce' ) );
        $_POST['_wpnonce'] = $_REQUEST['_wpnonce'] = $nonce;
    }

    private function bookingpress_rest_response( $response ) {
        return new \WP_REST_Response( [
            'success' => ( !empty( $response['variant'] ) && 'success' === $response['variant'] ),
            'data'    => $response
        ], 200 );
    }

    public function get_payments( $request ) {
        global $bookingpress_payment;

        $this->bookingpress_set_legacy_nonce( $request );

        $search_data = $request->get_param( 'search_data' );

        $_POST['perpage']     = $_REQUEST['perpage']     = intval( $request->get_param( 'perpage' ) );
        $_POST['currentpage'] = $_REQUEST['currentpage'] = intval( $request->get_param( 'currentpage' ) );
        $_POST['sort_by']     = $_REQUEST['sort_by']     = sanitize_text_field( (string) $request->get_param( 'sort_by' ) );
        $_POST['sort_order']  = $_REQUEST['sort_order']  = sanitize_text_field( (string) $request->get_param( 'sort_order' ) );
        $_POST['search_data'] = $_REQUEST['search_data'] = is_array( $search_data ) ? $search_data : [];

        $response = $bookingpress_payment->bookingpress_get_payments_details( true );

        if( !empty( $response['variant'] ) && 'error' === $response['variant'] ) {
            return $this->bookingpress_rest_response( $response );
        }

        $response = apply_filters( 'bookingpress_modify_payments_rest_response', $response, $request );

        return new \WP_REST_Response( [
            'success' => true,
            'data'    => $response
        ], 200 );
    }

    public function bulk_delete_payments( $request ) {
        global $bookingpress_payment;

        $this->bookingpress_set_legacy_nonce( $request );

        $delete_ids = $request->get_param( 'delete_ids' );

        $_POST['bulk_action'] = $_REQUEST['bulk_action'] = 'delete';
        $_POST['delete_ids']  = $_REQUEST['delete_ids']  = is_array( $delete_ids ) ? $delete_ids : [];

        $response = $bookingpress_payment->bookingpress_payment_log_bulk_action( true );

        return $this->bookingpress_rest_response( $response );
    }

    public function change_payment_status( $request ) {
        global $bookingpress_payment;

        $this->bookingpress_set_legacy_nonce( $request );

        $_POST['payment_log_id'] = $_REQUEST['payment_log_id'] = intval( $request->get_param( 'payment_log_id' ) );
        $_POST['payment_status'] = $_REQUEST['payment_status'] = sanitize_text_field( (string) $request->get_param( 'payment_status' ) );

        $response = $bookingpress_payment->bookingpress_change_payment_status_func( true );

        return $this->bookingpress_rest_response( $response );
    }

    public function approve_appointment( $request ) {
        global $bookingpress_payment;

        $this->bookingpress_set_legacy_nonce( $request );

        $_POST['approve_id'] = $_REQUEST['approve_id'] = intval( $request->get_param( 'approve_id' ) );

        $response = $bookingpress_payment->bookingpress_approve_appointment( true );

        return $this->bookingpress_rest_response( $response );
    }
}

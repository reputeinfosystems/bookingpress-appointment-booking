<?php

namespace BookingPress\api;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TimeRoutes extends Base {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] ); 
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/time', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_time' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for();
            }
        ] );

        register_rest_route( 'bookingpress-app/v1', '/dates', [
            'methods' => 'POST',
            'callback' => [ $this, 'get_dates' ],
            'permission_callback' => function( $request ){
                return $this->permission_callback_for();
            }
        ] );
    }

    public function get_dates( $request ){

        $_POST['appointment_data_obj'] = wp_json_encode( $request->get_param( 'appointment_data_obj' ) );
        $_POST['service_id'] = $request->get_param( 'service_id' );
        $_POST['selected_date'] = $request->get_param( 'selected_date' );
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        global $bookingpress_appointment_bookings;
        $response = $bookingpress_appointment_bookings->bookingpress_get_disable_date_func_optimized( true );

        return new \WP_REST_Response( [
            'success' => true,
            'data' => $response
        ], 200 );

    }

    public function get_time( $request ){

        $service_id = $request->get_param( 'service_id' );
        $selected_date = $request->get_param( 'selected_date' );
        $appointment_data_obj = $request->get_param( 'appointment_data_obj' );

        global $bookingpress_appointment_bookings, $BookingPress, $wpdb, $tbl_bookingpress_appointment_bookings;

        $_POST['appointment_data_obj'] = $appointment_data_obj; //phpcs:ignore
        $_POST['service_id'] = $service_id; //phpcs:ignore
        $_POST['selected_date'] = $selected_date; //phpcs:ignore
        $_POST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore

        $bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings( 'share_timeslot_between_services', 'general_setting' );

        $where_clause = '';
        if( 'true' != $bookingpress_shared_service_timeslot ){
            $where_clause = $wpdb->prepare( ' AND bookingpress_service_id = %d ', $service_id );
            $where_clause = apply_filters( 'bookingpress_booked_appointment_where_clause', $where_clause );
        }else{                
            $where_clause = apply_filters( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', $where_clause,$service_id);
        }

        $where_clause .= $wpdb->prepare( ' AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s)', '1', '2' );

        $bpa_appointment_edit_id = $request->get_param( 'appointment_update_id' ) ?? 0;

        if( !empty( $bpa_appointment_edit_id ) ){
            $where_clause .= $wpdb->prepare( ' AND bookingpress_appointment_booking_id != %d', $bpa_appointment_edit_id );
        }

        $total_booked_appiontments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE (bookingpress_appointment_date = %s) $where_clause", $selected_date), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        $response = $bookingpress_appointment_bookings->bookingpress_retrieve_timeslots( $selected_date, true, false, false, $total_booked_appiontments, true );

        return new \WP_REST_Response( [
            'success' => true,
            'data' => $response
        ], 200 );
    }
}
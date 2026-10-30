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
            'permission_callback' => $this->permission_callback_for( '', true )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/dates', [
            'methods' => 'POST',
            'callback' => [ $this, 'get_dates' ],
            'permission_callback' => $this->permission_callback_for( '', true )
        ] );
    }

    public function get_dates( $request ){

        $service_id = $request->get_param( 'service_id' );

        if( empty( $service_id ) ){
            return new \WP_REST_Response( [
                'success' => true,
                'data' => [
                    'variant' => 'success',
                    'days_off_disabled_dates' => '',
                    'front_timings' => [
                        'morning_time' => [],
                        'afternoon_time' => [],
                        'evening_time' => [],
                        'night_time' => []
                    ]
                ]
            ], 200 );
        }

        $_POST['appointment_data_obj'] = wp_json_encode( $request->get_param( 'appointment_data_obj' ) );
        $_POST['service_id'] = $service_id;
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

    /** WP Abilities */

    public static function bookingpress_ability_list_available_time_slots( $input = [] ){
        // Implement the ability logic here

        $service_id = $input['service_id'] ?? 0;
        $selected_date = $input['date'] ?? '';

        if( empty( $service_id ) ){
            return new \WP_Error(
                'invalid_service_id',
                __( 'Service ID is required to list available time slots.', 'bookingpress-appointment-booking' ),
                [ 'status' => 400 ]
            );
        }

        if( empty( $selected_date ) ){
            return new \WP_Error(
                'invalid_date',
                __( 'Date is required to list available time slots.', 'bookingpress-appointment-booking' ),
                [ 'status' => 400 ]
            );
        }

        $_POST['service_id'] = $service_id;
        $_POST['selected_date'] = $selected_date;

        /**
         * Filter the booking details used to calculate the time slots of the 'bookingpress/get-available-time-slots' ability.
         * Return a WP_Error to reject the request.
         *
         * @since 1.6.9
         * @param array $appointment_data_obj Booking details in the format of the booking form.
         * @param array $input                Ability input.
         */
        $appointment_data_obj = apply_filters( 'bookingpress_ability_time_slots_appointment_data', [], $input );

        if( is_wp_error( $appointment_data_obj ) ){
            return $appointment_data_obj;
        }

        if( ! empty( $appointment_data_obj ) ){
            $_POST['appointment_data_obj'] = $appointment_data_obj; //phpcs:ignore
        }

        global $BookingPress, $wpdb, $bookingpress_appointment_bookings, $tbl_bookingpress_appointment_bookings;
        $bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings( 'share_timeslot_between_services', 'general_setting' );

        $where_clause = '';
        if( 'true' != $bookingpress_shared_service_timeslot ){
            $where_clause = $wpdb->prepare( ' AND bookingpress_service_id = %d ', $service_id );
            $where_clause = apply_filters( 'bookingpress_booked_appointment_where_clause', $where_clause );
        }else{                
            $where_clause = apply_filters( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', $where_clause,$service_id);
        }

        $where_clause .= $wpdb->prepare( ' AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s)', '1', '2' );

        $total_booked_appiontments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE (bookingpress_appointment_date = %s) $where_clause", $selected_date), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        $response = $bookingpress_appointment_bookings->bookingpress_retrieve_timeslots( $selected_date, true, false, false, $total_booked_appiontments, true );

        $available_time_slots = array_merge( $response['morning_time'], $response['afternoon_time'], $response['evening_time'], $response['night_time'] );

        if( empty( $available_time_slots ) ){
            return new \WP_Error(
                'no_available_time_slots',
                __( 'No available time slots for the selected date.', 'bookingpress-appointment-booking' ),
                [ 'status' => 404 ]
            );
        }
        //echo json_encode( $available_time_slots );

        $timeslots = [];
        foreach( $available_time_slots as $timeslot ){
            if( !$timeslot['is_booked']){
                $capacity = self::bookingpress_format_timeslot_capacity( $timeslot );

                $timeslots[] = [
                    'start_time' => $timeslot['store_start_time'],
                    'end_time' => $timeslot['store_end_time'],
                    'total_capacity' => $capacity['total_capacity'],
                    'available_capacity' => $capacity['available_capacity'],
                    'formatted_start_time' => $timeslot['formatted_start_time'],
                    'formatted_end_time' => $timeslot['formatted_end_time']
                ];
            }
        }

        return ['available_time_slots' => $timeslots];

    }

    public static function bookingpress_ability_find_next_available_slot( $input = [] ){

        $service_id = $input['service_id'] ?? 0;

        $_REQUEST['selected_service'] = $service_id;
        $_POST['service_id'] = $service_id;
        $_REQUEST['selected_date'] = date('Y-m-d', current_time('timestamp') );
        $_POST['selected_date'] = date('Y-m-d', current_time('timestamp') );
        $_POST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        global $bookingpress_appointment_bookings;

        $timeslot = $bookingpress_appointment_bookings->bookingpress_get_disable_date_func_optimized( true, true );

        if( empty( $timeslot ) ){
            return new \WP_Error(
                'no_available_dates',
                __( 'No available dates for the selected service.', 'bookingpress-appointment-booking' ),
                [ 'status' => 404 ]
            );
        }

        $capacity = self::bookingpress_format_timeslot_capacity( $timeslot );

        $item = [
            'date' => $timeslot['store_service_date'],
            'start_time' => $timeslot['store_start_time'],
            'end_time' => $timeslot['store_end_time'],
            'total_capacity' => $capacity['total_capacity'],
            'available_capacity' => $capacity['available_capacity'],
            'formatted_start_time' => $timeslot['formatted_start_time'],
            'formatted_end_time' => $timeslot['formatted_end_time']
        ];
        
        return ['available_timeslot' => $item];

    }

    /**
     * Once a slot has bookings, bookingpress_retrieve_timeslots() lowers its max_capacity by the booked count,
     * so max_capacity is the remaining capacity and max_total_capacity is the full one.
     *
     * @param array $timeslot Time slot from bookingpress_retrieve_timeslots().
     * @return array
     */
    private static function bookingpress_format_timeslot_capacity( $timeslot ){
        $total_capacity = (int) ( $timeslot['max_total_capacity'] ?? $timeslot['max_capacity'] ?? 0 );
        $total_booked   = (int) ( $timeslot['total_booked'] ?? 0 );

        return [
            'total_capacity' => $total_capacity,
            'available_capacity' => max( 0, $total_capacity - $total_booked ),
        ];
    }
}
<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

use BookingPress\data\ServicesProviders;
use BookingPress\api\CalendarRoutes;

class AppointmentRoutes extends Base {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/appointment/create', [
            'methods'  => 'POST',
            'callback' => [ $this, 'create_appointment' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for('add_calendar_appointments');
            }
        ] );
        register_rest_route( 'bookingpress-app/v1', '/appointment/update-status', [
            'methods'  => 'POST',
            'callback' => [ $this, 'update_appointment_status' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'update_upcoming_appointments' );
            }
        ] );
        register_rest_route( 'bookingpress-app/v1', '/appointment/fetch', [
            'methods'  => 'POST',
            'callback' => [ $this, 'fetch_appointment_data' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'retrieve_calendar_appointments' );
            }
        ] );
        register_rest_route( 'bookingpress-app/v1', '/appointment/reschedule', [
            'methods'  => 'POST',
            'callback' => [ $this, 'reschedule_appointment' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'update_upcoming_appointments' );
            }
        ] );
    }

    public function reschedule_appointment( $request ) {
        //$reschedule_data = $request->get_param( 'reschedule_data' );

        global $wpdb, $tbl_bookingpress_appointment_bookings;

       

        $appointment_id = $request->get_param('appointment_update_id');

        $appointment_log_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        $service_id = $request->get_param( 'appointment_selected_service' );

        $bookingpress_booking_timestamp = strtotime( $appointment_log_data['bookingpress_appointment_date'] . ' ' . $appointment_log_data['bookingpress_appointment_time'] );
        $is_past_time = current_time('timestamp') > $bookingpress_booking_timestamp;

        /** Block if appointment is in past */

        if( 1 == $is_past_time ) {
            return new \WP_REST_Response(
                [
                    'variant' => 'error',
                    'success' => false,
                    'message' => esc_html__( 'Sorry, past appontment can not be rescheduled', 'bookingpress-appointment-booking')
                ],
                400
            );
        }
        /** Block if appointment is in past */

        $appointment_selected_date  = $request->get_param( 'appointment_booked_date' );
        $appointment_start_time     = $request->get_param( 'appointment_booked_time' );
        $appointment_end_time       = $request->get_param( 'appointment_booked_end_time' );
        if( '24:00' != $appointment_end_time ){
            $appointment_end_time = date( 'H:i:s', strtotime( $appointment_end_time ) );
        } else if( '24:00' == $appointment_end_time ){
            $appointment_end_time = '24:00:00';
        }

        $appointment_selected_date_time = strtotime( $appointment_selected_date . ' ' . $appointment_start_time );

        if( current_time( 'timestamp' ) > $appointment_selected_date_time ){
            return new \WP_REST_Response(
                [
                    'variant' => 'error',
                    'success' => false,
                    'message' => esc_html__( 'Sorry, Appointment can not be rescheduled as the selected time has been passed.', 'bookingpress-appointment-booking')
                ],
                400
            );
        }

        $updated_data = [
            'bookingpress_appointment_date' => $appointment_selected_date,
            'bookingpress_appointment_end_date' => $appointment_selected_date,
            'bookingpress_appointment_time' => $appointment_start_time,
            'bookingpress_appointment_end_time' => $appointment_end_time,
        ];

        $wpdb->update( $tbl_bookingpress_appointment_bookings, $updated_data, array( 'bookingpress_appointment_booking_id' => $appointment_id ) );

        $appointment_data = CalendarRoutes::get_single_appointment( $appointment_id );
        
        return new \WP_REST_Response(
            [
                'variant' => 'success',
                'success' => true,
                'appointment_details' => $appointment_data,
                'message' => esc_html__( 'Appointment rescheduled successfully', 'bookingpress-appointment-booking')
            ]
        );

        //return $this->create_appointment( $request );
    }

    public function fetch_appointment_data( $request ) {
        $appointment_id = $request->get_param( 'appointment_id' );

        global $bookingpress_calendar;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore
        $_POST['appointment_id'] = $appointment_id; //phpcs:ignore

        $response = $bookingpress_calendar->bookingpress_get_edit_appointment_data_func( true );

        if( !empty( $response['variant'] ) && $response['variant'] != '1' ) {
            return new \WP_REST_Response( [
                'success' => false,
            ], 400 );
        } else {
            $result = [
                'success' => true,
                'data' => $response,
            ];
            return new \WP_REST_Response( $result, 200 );
        }
    }

    public function update_appointment_status( $request ) {
        $update_appointment_id = $request->get_param( 'appointment_id' );
        $new_status = $request->get_param( 'new_status' );

        global $wpdb, $bookingpress_dashboard;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore

        $response = $bookingpress_dashboard->bookingpress_change_upcoming_appointment_status( $update_appointment_id, $new_status, true );

        if( !empty( $response['variant'] ) && $response['variant'] != '1' ) {
            return new \WP_REST_Response( [
                'success' => false,
            ], 400 );
        } else {
            $result = [
                'success' => true,
            ];
            return new \WP_REST_Response( $result, 200 );
        }
    }

    public function create_appointment( $request ) {
        
        $appointment_data = $request->get_param( 'appointment_data' );

        global $bookingpress_calendar, $wpdb, $tbl_bookingpress_appointment_bookings, $BookingPress, $bookingpress_global_options, $tbl_bookingpress_form_fields;

        $bookingpress_global_options_arr        = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_default_date_format       = $bookingpress_global_options_arr['wp_default_date_format'];
        $bookingpress_default_time_format       = $bookingpress_global_options_arr['wp_default_time_format'];
        $bookingpress_default_date_time_format  = $bookingpress_default_date_format . ' ' . $bookingpress_default_time_format;

        $_POST['appointment_data'] = wp_json_encode( $appointment_data ); //phpcs:ignore
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore

        $response = $bookingpress_calendar->bookingpress_save_appointment_booking_func( false, true );

        $where_clause = '';
        if( !empty( $appointment_data['appointment_update_id'] ) ){
            $where_clause = $wpdb->prepare( " AND bookingpress_appointment_booking_id = %d", $appointment_data['appointment_update_id'] );
        } else if( !empty( $response['payment_log_id'] ) ){
            $where_clause = $wpdb->prepare( " AND bookingpress_payment_id = %d", $response['payment_log_id'] );
        }

        $appointment_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE (bookingpress_appointment_status = %d or bookingpress_appointment_status = %d) $where_clause", 1, 2 ), ARRAY_A ); // phpcs:ignore
        $all_appointment_details = [];

        $default_form_fields = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT bookingpress_form_field_id, bookingpress_field_label, bookingpress_form_field_name FROM {$tbl_bookingpress_form_fields} WHERE (bookingpress_form_field_name = %s OR bookingpress_form_field_name = %s) AND bookingpress_field_is_default = %d",
                'email_address',
                'phone_number',
                1
            )
        );

        $email_field_label = esc_html__( 'Email Address', 'bookingpress-appointment-booking' );
        $phone_field_label = esc_html__( 'Phone Number', 'bookingpress-appointment-booking' );

        $email_field_id = 4; // Default field id for email address
        $phone_field_id = 5; // Default field id for phone number

        foreach( $default_form_fields as $field_data ){
            if( $field_data->bookingpress_form_field_name == 'email_address' ){
                $email_field_id = $field_data->bookingpress_form_field_id;
                $email_field_label = $field_data->bookingpress_field_label;
            } else if( $field_data->bookingpress_form_field_name == 'phone_number' ){
                $phone_field_id = $field_data->bookingpress_form_field_id;
                $phone_field_label = $field_data->bookingpress_field_label;
            }
        }

        foreach( $appointment_details as $appointment_detail ){

            $bookingpress_customer_name = '';
            $bookingpress_cust_fnm = isset($appointment_detail['bookingpress_customer_firstname']) ? stripslashes_deep($appointment_detail['bookingpress_customer_firstname']) : '';
            $bookingpress_cust_lnm = isset($appointment_detail['bookingpress_customer_lastname']) ? stripslashes_deep($appointment_detail['bookingpress_customer_lastname']) : '';
            $bookingpress_cust_fullnm = isset($appointment_detail['bookingpress_customer_name']) ? stripslashes_deep($appointment_detail['bookingpress_customer_name']) : '';
            $bookingpress_cust_unm = isset($appointment_detail['bookingpress_username']) ? stripslashes_deep($appointment_detail['bookingpress_username']) : '';
            $bookingpress_cust_email = isset($appointment_detail['bookingpress_customer_email']) ? $appointment_detail['bookingpress_customer_email'] : '';
            $bookingpress_cust_phone = isset($appointment_detail['bookingpress_customer_phone'])  ? $appointment_detail['bookingpress_customer_phone'] : '';

            if(!empty($bookingpress_cust_fnm) || !empty($bookingpress_cust_lnm)) {
                $bookingpress_customer_name = !empty($bookingpress_cust_fnm) ? $bookingpress_cust_fnm : '';
                $bookingpress_customer_name .= !empty($bookingpress_customer_name) ? ' ' : '';
                $bookingpress_customer_name .= !empty($bookingpress_cust_lnm) ? $bookingpress_cust_lnm : '';
            } else if(!empty($bookingpress_cust_fullnm) && empty($bookingpress_customer_name)){
                $bookingpress_customer_name = $bookingpress_cust_fullnm;
            } else if(!empty($bookingpress_cust_unm) && empty($bookingpress_customer_name)){
                $bookingpress_customer_name = $bookingpress_cust_unm;
            } else if(!empty($bookingpress_cust_email) && empty($bookingpress_customer_name)){
                $bookingpress_customer_name = $bookingpress_cust_email;
            } else if(!empty($bookingpress_cust_phone) && empty($bookingpress_customer_name)){
                $bookingpress_customer_name = $bookingpress_cust_phone;
            }

            $service_color_scheme = ServicesProviders::get_service_color_scheme( $appointment_detail['bookingpress_service_id']);
            $color_scheme_data = ServicesProviders::get_color_scheme_data( $service_color_scheme );

            $booking_metadata = [
                'form_fields' => []
            ];

            if( !empty( $bookingpress_cust_email ) ){
                $booking_metadata['form_fields'][] = [
                    'id'        => $email_field_id,
                    'label'     => $email_field_label,
                    'value'     => $bookingpress_cust_email
                ];
            }
            if( !empty( $bookingpress_cust_phone )){
                $booking_metadata['form_fields'][] = [
                    'id'        => $phone_field_id,
                    'label'     => $phone_field_label,
                    'value'     => $bookingpress_cust_phone
                ];
            }

            $all_appointment_details[] = [
                'id'            => $appointment_detail['bookingpress_appointment_booking_id'],
                'customerName'  => $bookingpress_customer_name,
                'customerId'    => $appointment_detail['bookingpress_customer_id'],
                'start_date'    => $appointment_detail['bookingpress_appointment_date'],
                'booking_date'  => date_i18n($bookingpress_default_date_format, strtotime($appointment_detail['bookingpress_appointment_date'])),
                'booking_time'  => date('H:i', strtotime($appointment_detail['bookingpress_appointment_time'])) . ' - ' . date('H:i', strtotime($appointment_detail['bookingpress_appointment_end_time'])),
                'end_date'      => ( !empty( $appointment_detail['bookingpress_appointment_end_date'] ) && $appointment_detail['bookingpress_appointment_end_date'] != '0000-00-00' ) ? $appointment_detail['bookingpress_appointment_end_date'] : $appointment_detail['bookingpress_appointment_date'],
                'start_time'    => date('H:i', strtotime($appointment_detail['bookingpress_appointment_time'])),
                'end_time'      => date('H:i', strtotime($appointment_detail['bookingpress_appointment_end_time'])),
                'serviceName'   => stripslashes_deep($appointment_detail['bookingpress_service_name']),
                'serviceId'     => $appointment_detail['bookingpress_service_id'],
                'status'        => $appointment_detail['bookingpress_appointment_status'],
                'isPast'        => strtotime( $appointment_detail['bookingpress_appointment_date'] . ' ' . $appointment_detail['bookingpress_appointment_time'] ) < current_time('timestamp') ? true : false,
                'category'      => ServicesProviders::get_service_category_id( $appointment_detail['bookingpress_service_id'] ),
                'price'         => $BookingPress->bookingpress_price_formatter_with_currency_symbol( $appointment_detail['bookingpress_paid_amount'] ),
                'theme'         => $color_scheme_data,
                'metadata'      => $booking_metadata
            ];
        }
        $response['appointment_details'] = $all_appointment_details;
    

        $result = [
            'success' => true,
            'data'  => $response,
        ];

        return new \WP_REST_Response( $result, 200 );
    }
}
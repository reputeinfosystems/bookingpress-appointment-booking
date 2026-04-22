<?php

namespace BookingPress\api;

use BookingPress\data\ServicesProviders;

class CalendarRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/calendar', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_calendar' ],
            'permission_callback' => function( $request ) {
                return $this->permission_callback_for( 'retrieve_calendar_appointments' );
            }
        ] );
    }

    public function get_calendar( $request) {
        $json_data = [];

        global $wpdb, $tbl_bookingpress_appointment_bookings, $BookingPress, $bookingpress_global_options, $tbl_bookingpress_form_fields;

        $start_date = $request->get_param( 'start_date' ) ?? date('Y-m-d', current_time( 'timestamp' ));
        $end_date = $request->get_param( 'end_date' ) ?? date('Y-m-d', strtotime('+1 month', current_time( 'timestamp' )));

        $bookingpress_global_options_arr        = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_default_date_format       = $bookingpress_global_options_arr['wp_default_date_format'];
        $bookingpress_default_time_format       = $bookingpress_global_options_arr['wp_default_time_format'];
        $bookingpress_default_date_time_format  = $bookingpress_default_date_format . ' ' . $bookingpress_default_time_format;

        $bookings_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE (bookingpress_appointment_status='1' OR bookingpress_appointment_status='2') AND bookingpress_appointment_date >= %s AND bookingpress_appointment_date <= %s ORDER BY bookingpress_appointment_date ASC, bookingpress_appointment_time ASC",
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            ),
        ARRAY_A);

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
        
        foreach( $bookings_data as $booking ){

            $bookingpress_customer_name = '';
            $bookingpress_cust_fnm = isset($booking['bookingpress_customer_firstname']) ? stripslashes_deep($booking['bookingpress_customer_firstname']) : '';
            $bookingpress_cust_lnm = isset($booking['bookingpress_customer_lastname']) ? stripslashes_deep($booking['bookingpress_customer_lastname']) : '';
            $bookingpress_cust_fullnm = isset($booking['bookingpress_customer_name']) ? stripslashes_deep($booking['bookingpress_customer_name']) : '';
            $bookingpress_cust_unm = isset($booking['bookingpress_username']) ? stripslashes_deep($booking['bookingpress_username']) : '';
            $bookingpress_cust_email = isset($booking['bookingpress_customer_email']) ? $booking['bookingpress_customer_email'] : '';
            $bookingpress_cust_phone = isset($booking['bookingpress_customer_phone'])  ? $booking['bookingpress_customer_phone'] : '';

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

            $service_color_scheme = ServicesProviders::get_service_color_scheme( $booking['bookingpress_service_id'] );
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

            $formatted_date = date_i18n($bookingpress_default_date_format, strtotime($booking['bookingpress_appointment_date']));
            $formatted_time = date_i18n($bookingpress_default_time_format, strtotime($booking['bookingpress_appointment_time'])) . ' - ' . date($bookingpress_default_time_format, strtotime($booking['bookingpress_appointment_end_time']));

            $booking_metadata['formatted_booking_date'] = $formatted_date;
            $booking_metadata['formatted_booking_time'] = $formatted_time;

            $json_data[] = [
                'id'            => $booking['bookingpress_appointment_booking_id'],
                'customerName'  => $bookingpress_customer_name,
                'customerId'    => $booking['bookingpress_customer_id'],
                'start_date'    => $booking['bookingpress_appointment_date'],
                'booking_date'  => date_i18n($bookingpress_default_date_format, strtotime($booking['bookingpress_appointment_date'])),
                'booking_time'  => date('H:i', strtotime($booking['bookingpress_appointment_time'])) . ' - ' . date('H:i', strtotime($booking['bookingpress_appointment_end_time'])),
                'end_date'      => ( !empty( $booking['bookingpress_appointment_end_date'] ) && $booking['bookingpress_appointment_end_date'] != '0000-00-00' ) ? $booking['bookingpress_appointment_end_date'] : $booking['bookingpress_appointment_date'],
                'start_time'    => date('H:i', strtotime($booking['bookingpress_appointment_time'])),
                'end_time'      => date('H:i', strtotime($booking['bookingpress_appointment_end_time'])),
                'serviceName'   => stripslashes_deep($booking['bookingpress_service_name']),
                'serviceId'     => $booking['bookingpress_service_id'],
                'status'        => $booking['bookingpress_appointment_status'],
                'isPast'        => strtotime( $booking['bookingpress_appointment_date'] . ' ' . $booking['bookingpress_appointment_time'] ) < current_time('timestamp') ? true : false,
                'category'      => ServicesProviders::get_service_category_id( $booking['bookingpress_service_id'] ),
                'price'         => $BookingPress->bookingpress_price_formatter_with_currency_symbol( $booking['bookingpress_paid_amount'] ),
                'theme'         => $color_scheme_data,
                'metadata'      => $booking_metadata
            ];
        }

        return new \WP_REST_Response( [
            'success' => true,
            'data'  => $json_data
        ], 200 );
    }

    public static function get_single_appointment( $appointment_id ){
        global $wpdb, $tbl_bookingpress_appointment_bookings, $BookingPress, $bookingpress_global_options, $tbl_bookingpress_form_fields;

        $bookingpress_global_options_arr        = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_default_date_format       = $bookingpress_global_options_arr['wp_default_date_format'];
        $bookingpress_default_time_format       = $bookingpress_global_options_arr['wp_default_time_format'];
        $bookingpress_default_date_time_format  = $bookingpress_default_date_format . ' ' . $bookingpress_default_time_format;

        $booking = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d",
                $appointment_id
            ),
            ARRAY_A
        );

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

        $bookingpress_customer_name = '';
        $bookingpress_cust_fnm = isset($booking['bookingpress_customer_firstname']) ? stripslashes_deep($booking['bookingpress_customer_firstname']) : '';
        $bookingpress_cust_lnm = isset($booking['bookingpress_customer_lastname']) ? stripslashes_deep($booking['bookingpress_customer_lastname']) : '';
        $bookingpress_cust_fullnm = isset($booking['bookingpress_customer_name']) ? stripslashes_deep($booking['bookingpress_customer_name']) : '';
        $bookingpress_cust_unm = isset($booking['bookingpress_username']) ? stripslashes_deep($booking['bookingpress_username']) : '';
        $bookingpress_cust_email = isset($booking['bookingpress_customer_email']) ? $booking['bookingpress_customer_email'] : '';
        $bookingpress_cust_phone = isset($booking['bookingpress_customer_phone'])  ? $booking['bookingpress_customer_phone'] : '';

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

        $service_color_scheme = ServicesProviders::get_service_color_scheme( $booking['bookingpress_service_id'] );
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

        $formatted_date = date_i18n($bookingpress_default_date_format, strtotime($booking['bookingpress_appointment_date']));
        $formatted_time = date_i18n($bookingpress_default_time_format, strtotime($booking['bookingpress_appointment_time'])) . ' - ' . date($bookingpress_default_time_format, strtotime($booking['bookingpress_appointment_end_time']));

        $booking_metadata['formatted_booking_date'] = $formatted_date;
        $booking_metadata['formatted_booking_time'] = $formatted_time;

        $json_data = [
            'id'            => $booking['bookingpress_appointment_booking_id'],
            'customerName'  => $bookingpress_customer_name,
            'customerId'    => $booking['bookingpress_customer_id'],
            'start_date'    => $booking['bookingpress_appointment_date'],
            'booking_date'  => date_i18n($bookingpress_default_date_format, strtotime($booking['bookingpress_appointment_date'])),
            'booking_time'  => date('H:i', strtotime($booking['bookingpress_appointment_time'])) . ' - ' . date('H:i', strtotime($booking['bookingpress_appointment_end_time'])),
            'end_date'      => ( !empty( $booking['bookingpress_appointment_end_date'] ) && $booking['bookingpress_appointment_end_date'] != '0000-00-00' ) ? $booking['bookingpress_appointment_end_date'] : $booking['bookingpress_appointment_date'],
            'start_time'    => date('H:i', strtotime($booking['bookingpress_appointment_time'])),
            'end_time'      => date('H:i', strtotime($booking['bookingpress_appointment_end_time'])),
            'serviceName'   => stripslashes_deep($booking['bookingpress_service_name']),
            'serviceId'     => $booking['bookingpress_service_id'],
            'status'        => $booking['bookingpress_appointment_status'],
            'isPast'        => strtotime( $booking['bookingpress_appointment_date'] . ' ' . $booking['bookingpress_appointment_time'] ) < current_time('timestamp') ? true : false,
            'category'      => ServicesProviders::get_service_category_id( $booking['bookingpress_service_id'] ),
            'price'         => $BookingPress->bookingpress_price_formatter_with_currency_symbol( $booking['bookingpress_paid_amount'] ),
            'theme'         => $color_scheme_data,
            'metadata'      => $booking_metadata
        ];

        return $json_data;
    }
}

<?php

namespace BookingPress\abilities;

if( !defined('ABSPATH')) exit;

class Base {

    protected static function bookingpress_default_pagination_input_schema( $item = 'items' ){

        return [
            'type' => 'object',
            'default' => [
                'page' => 1,
                'per_page' => 10,
            ],
            'properties' => [
                'page' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'default' => 1,
                    'description' => __( 'Page number for pagination', 'bookingpress-appointment-booking' ),
                ],
                'per_page' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                    'default' => 10,
                    'description' => sprintf( __( 'Number of %s per page for pagination', 'bookingpress-appointment-booking' ), $item ),
                ]
            ],
            'additionalProperties' => false
        ];

    }

    protected static function bookingpress_fetch_input_schema_for_services(){

        $pagination_schema = self::bookingpress_default_pagination_input_schema( 'services' );

        $filter_schema_properties = [
            'category' => [
                'type' => 'string',
                'default' => '',
            ],
            'service' => [
                'type' => 'string',
                'default' => '',
            ]
        ];

        $filter_schema_default = [
            'category' => '',
            'service' => ''
        ];

        return [
            'type' => $pagination_schema['type'],
            'default' => array_merge( $pagination_schema['default'], $filter_schema_default ),
            'properties' => array_merge( $pagination_schema['properties'], $filter_schema_properties ),
            'additionalProperties' => false
        ];

    }

    protected static function verify_customer_by_wp_id( $user_id = 0 ){

        if( empty( $user_id ) ){ return false; }

        $customer = get_user_by( 'id', $user_id );
        if( !$customer ){
            return false;
        }

        global $wpdb, $tbl_bookingpress_customers;

        $customer_exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $tbl_bookingpress_customers WHERE bookingpress_wpuser_id = %d",
            $user_id
        ) );

        if( !$customer_exists ){
            return false;
        }

        return true;
    }

    protected static function bookingpress_fetch_appointment_status_label_byid( $status_id ){
        global $bookingpress_global_options;
        $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();;

        $bookingpress_appointment_status_arr = $bookingpress_global_options_arr['appointment_status'] ?? [];
        foreach($bookingpress_appointment_status_arr as $status_key => $status_val){
            if($status_id == $status_val['value']){
                $bookingpress_appointment_status_label = $status_val['text'];
                break;
            }    
        }
        return isset($bookingpress_appointment_status_label) ? $bookingpress_appointment_status_label : $status_id;
    }

    protected static function bookingpress_format_appointment_date( $date = '' ){
        global $bookingpress_global_options;
        $bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
        if( !empty( $date ) ){
            return date_i18n( $bookingpress_default_date_format, strtotime( $date ) );
        }
        return '';
    }

    protected static function bookingpress_format_appointment_time( $time = '' ){
        global $bookingpress_global_options;
        $bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_default_time_format = $bookingpress_global_options_arr['wp_default_time_format'];
        if( !empty( $time ) ){
            return date_i18n( $bookingpress_default_time_format, strtotime( $time ) );
        }
        return '';
    }

    protected static function bookingpress_format_appointment_duration( $get_appointment = [] ){

        global $bookingpress_appointment;

        if( empty( $get_appointment ) ){
            return;
        }

        $service_duration             = esc_html($get_appointment['bookingpress_service_duration_val']);
        $service_duration_unit        = esc_html($get_appointment['bookingpress_service_duration_unit']);

        if( $service_duration_unit == 'h'){
            $bookingpress_sortable_duration_val = $service_duration * 60;
        } else if( $service_duration_unit == 'd'){
            $bookingpress_sortable_duration_val = $service_duration * 24 * 60;
        }else {
            $bookingpress_sortable_duration_val = $service_duration;
        }

        if( !empty( $get_appointment['bookingpress_appointment_end_date'] ) && '0000-00-00' != $get_appointment['bookingpress_appointment_end_date'] ){
            $appointment_end_date = $get_appointment['bookingpress_appointment_end_date'];
        } else {
            $appointment_end_date = $get_appointment['bookingpress_appointment_date'];
        }

        

        $bookingpress_appointment_start_datetime = $get_appointment['bookingpress_appointment_date'].' '.$get_appointment['bookingpress_appointment_time'];
        if( $appointment_end_date > $get_appointment['bookingpress_appointment_date'] && '00:00:00' != $get_appointment['bookingpress_appointment_end_time'] ){

            $end_time_data = explode( ':', $get_appointment['bookingpress_appointment_end_time'] );

            $end_hour = $end_time_data[0];
            $end_mins = $end_time_data[1];
            $end_sec = $end_time_data[2];

            if( $end_hour >= 24 ){
                $bookingpress_appointment_end_datetime = date('Y-m-d H:i:s', strtotime( $get_appointment['bookingpress_appointment_date'] .' 00:00:00 +'.$end_hour.' hours '.$end_mins.' minutes '.$end_sec.' seconds' ) );
            } else {
                $bookingpress_appointment_end_datetime = $appointment_end_date.' '.$get_appointment['bookingpress_appointment_end_time'];
            }

        } else {
            $bookingpress_appointment_end_datetime = $appointment_end_date.' '.$get_appointment['bookingpress_appointment_end_time'];
        }

        if($service_duration_unit != 'd') {
            $service_duration = $bookingpress_appointment->bookingpress_get_appointment_duration($bookingpress_appointment_start_datetime, $bookingpress_appointment_end_datetime);
        } else {
            if( 1 == $service_duration ){
                $service_duration .= ' ' . esc_html__('Day', 'bookingpress-appointment-booking');
            } else {   
                $service_duration .= ' ' . esc_html__('Days', 'bookingpress-appointment-booking');
            }                        
        }

        return trim($service_duration);
    }

}
<?php

namespace BookingPress\data;

if(!defined('ABSPATH')) exit;

class ServicesProviders {

    public static function get_services_group_with_category(){
        global $BookingPress;

        $services = $BookingPress->get_bookingpress_service_data_group_with_category();

        $no_services_data = [];
        $no_services_data[] =[
            'category_name'     => '',
            'category_services' => [
                '0' => [
                    'service_id'    => 0,
                    'service_name'  => esc_html__('Select Services', 'bookingpress-appointment-booking'),
                    'service_price' => '',
                ],
            ],
        ];

        $services = array_merge( $no_services_data, $services);

        return $services;

    }

    public static function get_all_services_with_name(){
        global $wpdb, $tbl_bookingpress_services;

        $services = $wpdb->get_results( "SELECT bookingpress_service_id, bookingpress_service_name FROM {$tbl_bookingpress_services} ORDER BY bookingpress_service_name ASC", ARRAY_A );

        if( empty( $services ) ){
            return [];
        }

        return $services;
    }

    public static function get_service_category_id( $service_id ){
        global $wpdb, $tbl_bookingpress_services;

        $service = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_category_id FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id ), ARRAY_A );

        return intval( $service['bookingpress_category_id'] );
    }

    public static function get_service_color_scheme( $service_id ){
        global $wpdb, $tbl_bookingpress_services;

        $service = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_color_scheme FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id ), ARRAY_A );

        return $service['bookingpress_color_scheme'];
    }

    public static function get_color_scheme_data( $scheme_key ){
        $color_scheme_data = [
            'scheme_1' => [
                "border" => "#F7AEBC",
                "text" => "#F24161",
                "bg" => "#FEF7F9"
            ],
            'scheme_2' => [
                "border" => "#FDE3A0",
                "text" => "#F9B303",
                "bg" => "#FFFCF6"
            ],
            'scheme_3' => [
                "border" => "#BBEAEE",
                "text" => "#3CAEBF",
                "bg" => "#F5FCFC"
            ],
            'scheme_4' => [
                "border" => "#CFCEF7",
                "text" => "#534EE2",
                "bg" => "#F8F8FE"
            ],
            'scheme_5' => [
                "border" => "#DCADAE",
                "text" => "#950609",
                "bg" => "#FBF5F5"
            ],
        
            'scheme_6' => [
                "border" => "#F6A6D5",
                "text" => "#F12E9A",
                "bg" => "#FEF7FB"
            ],
            'scheme_7' => [
                "border" => "#CEE593",
                "text" => "#8BC200",
                "bg" => "#FAFDF6"
            ],
            'scheme_8' => [
                "border" => "#C3E8FF",
                "text" => "#2DA9FF",
                "bg" => "#F6FCFF"
            ],
            'scheme_9' => [
                "border" => "#EFC6F3",
                "text" => "#C637D6",
                "bg" => "#FDF7FD"
            ],
            'scheme_10' => [
                "border" => "#E0C8DD",
                "text" => "#A4579A",
                "bg" => "#FBF8FB"
            ],
        
            'scheme_11' => [
                "border" => "#F9C2B6",
                "text" => "#F46F53",
                "bg" => "#FEF9F8"
            ],
            'scheme_12' => [
                "border" => "#B7EAB2",
                "text" => "#50C734",
                "bg" => "#F7FDF7"
            ],
            'scheme_13' => [
                "border" => "#C3D7FF",
                "text" => "#1F69FF",
                "bg" => "#F6F9FF"
            ],
            'scheme_14' => [
                "border" => "#E3D9B9",
                "text" => "#9D7808",
                "bg" => "#FBFAF5"
            ],
            'scheme_15' => [
                "border" => "#CCD0E2",
                "text" => "#626FA8",
                "bg" => "#F9F9FC"
            ],
        
            'scheme_16' => [
                "border" => "#FAC293",
                "text" => "#F56E02",
                "bg" => "#FEF9F5"
            ],
            'scheme_17' => [
                "border" => "#A0EDD8",
                "text" => "#53DAB1",
                "bg" => "#F5FDFB"
            ],
            'scheme_18' => [
                "border" => "#DCD0FF",
                "text" => "#7C4DFF",
                "bg" => "#FAF8FF"
            ],
            'scheme_19' => [
                "border" => "#E3D1D3",
                "text" => "#9D5C63",
                "bg" => "#FBF8F9"
            ],
            'scheme_20' => [
                "border" => "#CCC6BB",
                "text" => "#65532F",
                "bg" => "#F9F8F7"
            ],
        
            'scheme_21' => [
                "border" => "#F2DD9F",
                "text" => "#DEA700",
                "bg" => "#FEFBF5"
            ],
            'scheme_22' => [
                "border" => "#A3D5B2",
                "text" => "#378F31",
                "bg" => "#F5FBF7"
            ],
            'scheme_23' => [
                "border" => "#EAD1F6",
                "text" => "#AF52DE",
                "bg" => "#FCF8FE"
            ],
            'scheme_24' => [
                "border" => "#C2D4D5",
                "text" => "#1F6367",
                "bg" => "#F6F9F9"
            ],
            'scheme_25' => [
                "border" => "#CAC8CE",
                "text" => "#5D576B",
                "bg" => "#F9F8F9"
            ],
        ];

        return $color_scheme_data[$scheme_key] ?? $color_scheme_data['scheme_1'];
    }

}

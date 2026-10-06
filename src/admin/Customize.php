<?php

namespace BookingPress\admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Customize extends Base {

    protected static $slug = 'bookingpress_customize';

    public static function init() {
        parent::init();

        add_filter( 'script_module_data_bookingpress-customize-loader',[ __CLASS__, 'bookingpress_add_customize_script_module_data' ]);
    }

    public static function bookingpress_add_customize_script_module_data( $customize_data ) {

        global $wpdb, $BookingPress, $bookingpress_global_options;
        $bpa_nonce = wp_create_nonce( 'bpa_wp_nonce' );
        $customize_data['_wpnonce'] = $bpa_nonce;
        /**
         * Current Customize action.
         *
         * forms       => Booking Form customization
         * form_fields => Custom Fields customization
         */
        $customize_data['bookingpress_customize_action'] = ! empty( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'forms';

        $default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
        $disabled_date           = implode( ',', $default_daysoff_details );
        $customize_data['days_off_disabled_dates'] = $disabled_date;

        // Load fonts options
        $bookingpress_inherit_fonts_list = ['Inherit Fonts',];

        $bookingpress_default_fonts_list = $bookingpress_global_options->bookingpress_get_default_fonts();
        $bookingpress_google_fonts_list  = $bookingpress_global_options->bookingpress_get_google_fonts();            
        $bookingpress_options      = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_default_date_format = $bookingpress_options['wp_default_date_format'];
        $bookingpress_default_time_format = $bookingpress_options['wp_default_time_format'];
        $bookingpress_country_list = json_decode($bookingpress_options['country_lists']);
        $bookingpress_all_pages = array();
        $bookingpress_all_pages = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_title FROM `".$wpdb->posts."` WHERE post_type = %s AND post_status = %s", 'page', 'publish'), ARRAY_A );

        $bookingpress_fonts_list = [
            [
                'label'   => esc_html__( 'Inherit Fonts', 'bookingpress-appointment-booking' ),
                'options' => $bookingpress_inherit_fonts_list,
            ],
            [
                'label'   => esc_html__( 'Default Fonts', 'bookingpress-appointment-booking' ),
                'options' => $bookingpress_default_fonts_list,
            ],
            [
                'label'   => esc_html__( 'Google Fonts', 'bookingpress-appointment-booking' ),
                'options' => $bookingpress_google_fonts_list,
            ],
        ];

        $customize_data['bookingpress_all_global_pages'] = $bookingpress_all_pages;
        $customize_data['fonts_list']                    = $bookingpress_fonts_list;

        $bookingpress_phone_country_option              = $BookingPress->bookingpress_get_settings( 'default_phone_country_code', 'general_setting' );
        $customize_data['bookingpress_tel_input_props'] = [
            'defaultCountry' => $bookingpress_phone_country_option,
            'inputOptions'   => [
                'placeholder' => '',
            ],
        ];

        $customize_data['dummy_data'] = array(
            array(
                "id" => '#158791',
                "appointment_service_name" => 'Sample service 1',
                "appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
                "appointment_status" => 'Pending',
                "appointment_payment" => '$100.00',
                "appointment_staff" => 'Blaine Moon',
            ),
            array(
                "id" => '#158792',
                "appointment_service_name" => 'Sample service 2',
                "appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
                "appointment_status" => 'Pending',
                "appointment_payment" => '$200.00',
                "appointment_staff" => 'Gary Williams',
            ),
            array(
                "id" => '#158793',
                "appointment_service_name" => 'Sample service 3',
                "appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
                "appointment_status" => 'Pending',
                "appointment_payment" => '$300.00',
                "appointment_staff" => 'Gerardo Burton',
            ),
            array(
                "id" => '#158794',
                "appointment_service_name" => 'Sample service 4',
                "appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
                "appointment_status" => 'Pending',
                "appointment_payment" => '$400.00',
                "appointment_staff" => 'Harold Reed',
            ),
            array(
                "id" => '#158795',
                "appointment_service_name" => 'Sample service 5',
                "appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
                "appointment_status" => 'Pending',
                "appointment_payment" => '$500.00',
                "appointment_staff" => 'Fox Doe',
            ),
        );

        $bookingpress_default_date_format = $BookingPress->bookingpress_check_common_date_format($bookingpress_options['wp_default_date_format']);
        $customize_data['masks'] = array( 'input' => strtoupper($bookingpress_default_date_format),);

        $customize_data = apply_filters( 'bookingpress_customize_data', $customize_data );

        return $customize_data;
    }

    public static function enqueue_assets( $hook ) {
        if ( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'bookingpress_customize' ) {
            return;
        }

        if( ( !empty( $_GET['page'] ) && 'bookingpress_customize' == $_GET['page'] && (!empty( $_GET['action'] ) && $_GET['action'] == 'form_fields') ) ){
            return;
        }

        wp_enqueue_style(
			'bookingpress-bp-theme',
			BOOKINGPRESS_URL . '/src/assets/css/bookingpress_bp_theme.css',
			array(),
			BOOKINGPRESS_VERSION
		);

        wp_register_script_module(
            'vue',
            BOOKINGPRESS_URL . '/src/assets/js/vue.min.js',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
            'bookingpress-ui',
            BOOKINGPRESS_URL . '/src/assets/js/bookingpress-ui.min.js',
            [ 'vue' ],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
            'bookingpress-customize-model',
            BOOKINGPRESS_URL . '/src/assets/js/customize-model.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-customize-model' );

        wp_register_script_module(
            'bookingpress-customize-loader',
            BOOKINGPRESS_URL . '/src/assets/js/customize-loader.js',
            [ 'bookingpress-customize-model' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-customize-loader' );

        wp_enqueue_style(
            'bookingpress-ui',
            BOOKINGPRESS_URL . '/src/assets/css/bookingpress-ui.min.css',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_style(
            'bookingpress-admin-common',
            BOOKINGPRESS_URL . '/src/assets/css/bookingpress_admin_common.css',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_style( 'bookingpress_admin_css' );
        wp_enqueue_style( 'bookingpress_components_css' );

        wp_enqueue_style(
            'bookingpress-common',
            BOOKINGPRESS_URL . '/src/assets/css/common.css',
            [],
            BOOKINGPRESS_VERSION
        );
        
    }

    public static function render_page() {
        $action = ! empty( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'forms';
        self::render_view( 'Customize_form', [ 'title' => esc_html__( 'Customize', 'bookingpress-appointment-booking' ),] );
    }

}
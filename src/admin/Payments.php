<?php

namespace BookingPress\admin;

if( !defined( 'ABSPATH' ) ) {
    exit;
}

use BookingPress\data\ServicesProviders;

class Payments extends Base{

    protected static $slug = 'bookingpress_payments';

    public static function init(){
        parent::init();

        add_filter( 'script_module_data_bookingpress-payments-loader', [ __CLASS__, 'bookingpress_add_payments_script_module_data' ] );
        add_filter( 'exclude_additional_slug_css_js_outside_module', [ __CLASS__, 'bookingpress_exclude_payments_from_legacy_scripts' ] );
        add_filter( 'bookingpress_additional_module_exculde', [ __CLASS__, 'bookingpress_exclude_payments_from_legacy_scripts' ] );
    }

    public static function bookingpress_exclude_payments_from_legacy_scripts( $excluded_slugs ) {
        if ( is_array( $excluded_slugs ) ) {
            if ( ! in_array( 'bookingpress_payments', $excluded_slugs, true ) ) {
                $excluded_slugs[] = 'bookingpress_payments';
            }
            if ( ! in_array( 'payments', $excluded_slugs, true ) ) {
                $excluded_slugs[] = 'payments';
            }
        }
        return $excluded_slugs;
    }

    public static function bookingpress_add_payments_script_module_data( $payments_data ){

        global $BookingPress, $bookingpress_global_options;

        $bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();

        $payments_data['bpa_date_common_date_format'] = $BookingPress->bookingpress_check_common_date_format_for_picker( $bookingpress_global_details['wp_default_date_format'] );
        $payments_data['firstDayOfWeek']              = intval( $bookingpress_global_details['start_of_week'] );

        $payments_data['search_range'] = array( date( 'Y-m-d', strtotime( '-2 Month' ) ), date( 'Y-m-d' ) );

        $payments_data['search_services_data'] = ServicesProviders::get_services_group_with_category( true );
        $payments_data['payment_status_data']  = $bookingpress_global_details['payment_status'];

        $bookingpress_pagination = $bookingpress_global_details['pagination'];
        $payments_data['pagination_length'] = $bookingpress_pagination;

        $bookingpress_default_perpage_option    = $BookingPress->bookingpress_get_settings( 'per_page_item', 'general_setting' );
        $payments_data['perPage']               = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '10';
        $payments_data['pagination_length_val'] = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '10';

        $payments_data['bulk_options'] = array(
            array(
                'value' => 'bulk_action',
                'label' => esc_html__( 'Bulk Action', 'bookingpress-appointment-booking' ),
            ),
            array(
                'value' => 'delete',
                'label' => esc_html__( 'Delete', 'bookingpress-appointment-booking' ),
            ),
        );

        $payments_data['common_messages'] = [
            'error'                    => esc_html__( 'Error', 'bookingpress-appointment-booking' ),
            'something_went_wrong'     => esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' ),
            'bulk_action_select'       => esc_html__( 'Please select any action.', 'bookingpress-appointment-booking' ),
            'bulk_action_no_selection' => esc_html__( 'Please select one or more records.', 'bookingpress-appointment-booking' ),
        ];

        $payments_data = apply_filters( 'bookingpress_modify_payment_data_fields_vue3', $payments_data );

        return $payments_data;
    }

    public static function enqueue_assets( $hook ){

        if ( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== static::$slug ) {
            return;
        }

        wp_register_script_module(
            'vue',
            BOOKINGPRESS_URL .'/src/assets/js/vue.min.js',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
            'bookingpress-ui',
            BOOKINGPRESS_URL . '/src/assets/js/bookingpress-ui.min.js',
            ['vue'],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
            'bookingpress-sidemenu-drawer',
            BOOKINGPRESS_URL . '/src/assets/js/drawer-loader.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-sidemenu-drawer' );

        wp_register_script_module(
            'bookingpress-payments-loader',
            BOOKINGPRESS_URL . '/src/assets/js/payments-loader.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script( 'wp-hooks' );
        wp_enqueue_script_module( 'bookingpress-payments-loader' );

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
        wp_enqueue_style( 'bookingpress_fonts_css' );

        wp_enqueue_style(
            'bookingpress-common',
            BOOKINGPRESS_URL . '/src/assets/css/common.css',
            [],
            BOOKINGPRESS_VERSION
        );
    }

    public static function render_page(){
        self::render_view( 'Payments', [
            'title' => esc_html__( 'Manage Payments', 'bookingpress-appointment-booking' )
        ] );
    }

}

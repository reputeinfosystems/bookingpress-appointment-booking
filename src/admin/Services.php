<?php

namespace BookingPress\admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Services extends Base {

    protected static $slug = 'bookingpress_services';

    public static function init() {
        parent::init();

        add_filter( 'script_module_data_bookingpress-services-loader', [ __CLASS__, 'bookingpress_add_services_script_module_data' ] );
        add_filter( 'exclude_additional_slug_css_js_outside_module', [ __CLASS__, 'bookingpress_exclude_services_from_legacy_scripts' ] );
        add_filter( 'bookingpress_additional_module_exculde', [ __CLASS__, 'bookingpress_exclude_services_from_legacy_scripts' ] );
    }

    public static function bookingpress_exclude_services_from_legacy_scripts( $excluded_slugs ) {
        if ( is_array( $excluded_slugs ) ) {
            if ( ! in_array( 'bookingpress_services', $excluded_slugs, true ) ) {
                $excluded_slugs[] = 'bookingpress_services';
            }
            if ( ! in_array( 'services', $excluded_slugs, true ) ) {
                $excluded_slugs[] = 'services';
            }
        }
        return $excluded_slugs;
    }

    public static function bookingpress_add_services_script_module_data( $services_data ) {
        global $BookingPress, $bookingpress_global_options, $wpdb, $tbl_bookingpress_categories;

        $bpa_nonce = wp_create_nonce( 'bpa_wp_nonce' );
        $services_data['service']['_wpnonce'] = $bpa_nonce;

        $bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_currency_symbol = ! empty( $bookingpress_global_details['price_symbol'] ) ? $bookingpress_global_details['price_symbol'] : '$';

        $price_number_of_decimals = $BookingPress->bookingpress_get_settings( 'price_number_of_decimals', 'payment_setting' );
        if ( null === $price_number_of_decimals || '' === $price_number_of_decimals ) {
            $price_number_of_decimals = $BookingPress->bookingpress_get_settings( 'price_number_of_decimals', 'general_setting' );
        }
        $price_number_of_decimals = ( null !== $price_number_of_decimals && '' !== $price_number_of_decimals ) ? $price_number_of_decimals : '2';

        $services_data['default_currency_symbol']   = $bookingpress_currency_symbol;
        $services_data['service_price_currency']     = $bookingpress_currency_symbol;
        $services_data['price_number_of_decimals']  = $price_number_of_decimals;

        $bookingpress_default_perpage = $BookingPress->bookingpress_get_settings( 'per_page_item', 'general_setting' );
        $services_data['perPage']                    = ! empty( $bookingpress_default_perpage ) ? $bookingpress_default_perpage : '10';

        $bookingpress_default_time_duration_data = $BookingPress->bookingpress_get_default_timeslot_data();
        $default_service_duration_val  = ! empty( $bookingpress_default_time_duration_data['time_duration'] ) ? (int) $bookingpress_default_time_duration_data['time_duration'] : 30;
        $default_service_duration_unit = ! empty( $bookingpress_default_time_duration_data['time_unit'] ) ? $bookingpress_default_time_duration_data['time_unit'] : 'm';

        $services_data['default_service_duration_val']  = $default_service_duration_val;
        $services_data['default_service_duration_unit'] = $default_service_duration_unit;
        if ( ! isset( $services_data['service'] ) || ! is_array( $services_data['service'] ) ) {
            $services_data['service'] = [];
        }
        $services_data['service']['service_duration_val']  = $default_service_duration_val;
        $services_data['service']['service_duration_unit'] = $default_service_duration_unit;

        // Load categories for search filter & dropdowns
        $search_categories = array();
        $get_categories = $wpdb->get_results( "SELECT bookingpress_category_id, bookingpress_category_name FROM {$tbl_bookingpress_categories} ORDER BY bookingpress_category_position ASC", ARRAY_A ); // phpcs:ignore
        if ( ! empty( $get_categories ) ) {
            foreach ( $get_categories as $cat ) {
                $search_categories[] = array(
                    'bookingpress_category_id'   => $cat['bookingpress_category_id'],
                    'bookingpress_category_name' => stripslashes( $cat['bookingpress_category_name'] ),
                );
            }
        }
        $services_data['search_categories'] = $search_categories;
        $services_data['bulk_options'] = array(
            array(
                'value' => 'bulk_action',
                'label' => esc_html__( 'Bulk Action', 'bookingpress-appointment-booking' ),
            ),
            array(
                'value' => 'delete',
                'label' => esc_html__( 'Delete', 'bookingpress-appointment-booking' ),
            ),
        );

        $services_data = apply_filters( 'bookingpress_modify_service_data_fields_vue3', $services_data );

        return $services_data;
    }

    public static function enqueue_assets( $hook ) {

        if ( strpos( $hook, static::$slug ) === false && strpos( $hook, 'bookingpress_services' ) === false ) {
            return;
        }

        wp_register_script_module(
            'vue',
            BOOKINGPRESS_URL . '/src/assets/js/vue.min.js',
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
            'bookingpress-draggable-js',
            BOOKINGPRESS_URL . '/src/assets/js/vuedraggable.min.js',
            [ 'vue' ],
            BOOKINGPRESS_VERSION
        );
        wp_enqueue_script_module( 'bookingpress-draggable-js' );

        wp_register_script_module(
            'bookingpress-services-loader',
            BOOKINGPRESS_URL . '/src/assets/js/services-loader.js',
            ['bookingpress-ui', 'bookingpress-draggable-js'],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script( 'wp-i18n' );
        wp_enqueue_script( 'wp-api-fetch' );
        wp_enqueue_script_module( 'bookingpress-services-loader' );

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

    public static function render_page() {
        self::render_view( 'Services', [
            'title' => esc_html__( 'Manage Services', 'bookingpress-appointment-booking' ),
        ] );
    }
}

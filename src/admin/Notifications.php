<?php

namespace BookingPress\admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Notifications extends Base {

    protected static $slug = 'bookingpress_notifications';

    public static function init() {
        parent::init();

        add_filter( 'script_module_data_bookingpress-notifications-loader', [ __CLASS__, 'bookingpress_add_notifications_script_module_data' ] );
        add_filter( 'exclude_additional_slug_css_js_outside_module', [ __CLASS__, 'bookingpress_exclude_notifications_from_legacy_scripts' ] );
        add_filter( 'bookingpress_additional_module_exculde', [ __CLASS__, 'bookingpress_exclude_notifications_from_legacy_scripts' ] );
    }

    public static function bookingpress_exclude_notifications_from_legacy_scripts( $excluded_slugs ) {
        if ( is_array( $excluded_slugs ) ) {
            if ( ! in_array( 'bookingpress_notifications', $excluded_slugs, true ) ) {
                $excluded_slugs[] = 'bookingpress_notifications';
            }
            if ( ! in_array( 'notifications', $excluded_slugs, true ) ) {
                $excluded_slugs[] = 'notifications';
            }
        }
        return $excluded_slugs;
    }

    public static function bookingpress_add_notifications_script_module_data( $notifications_data ) {
        global $BookingPress, $bookingpress_global_options;

        if ( ! is_array( $notifications_data ) ) {
            $notifications_data = [];
        }

        $bookingpress_options = $bookingpress_global_options->bookingpress_global_options();

        $customer_placeholders    = ! empty( $bookingpress_options['customer_placeholders'] ) ? json_decode( $bookingpress_options['customer_placeholders'], true ) : [];
        $service_placeholders     = ! empty( $bookingpress_options['service_placeholders'] ) ? json_decode( $bookingpress_options['service_placeholders'], true ) : [];
        $company_placeholders     = ! empty( $bookingpress_options['company_placeholders'] ) ? json_decode( $bookingpress_options['company_placeholders'], true ) : [];
        $appointment_placeholders = ! empty( $bookingpress_options['appointment_placeholders'] ) ? json_decode( $bookingpress_options['appointment_placeholders'], true ) : [];

        $customer_placeholders    = apply_filters( 'bookingpress_customer_placeholder_list', $customer_placeholders );
        $service_placeholders     = apply_filters( 'bookingpress_service_placeholder_list', $service_placeholders );
        $company_placeholders     = apply_filters( 'bookingpress_company_placeholder_list', $company_placeholders );
        $appointment_placeholders = apply_filters( 'bookingpress_appointment_placeholder_list', $appointment_placeholders );

        $notification_duration = $BookingPress->bookingpress_get_settings( 'notification_duration', 'general_setting' );
        $notification_duration = ! empty( $notification_duration ) ? intval( $notification_duration ) : 3000;

        $notifications_data['bookingpress_customer_placeholders']    = $customer_placeholders;
        $notifications_data['bookingpress_service_placeholders']     = $service_placeholders;
        $notifications_data['bookingpress_company_placeholders']     = $company_placeholders;
        $notifications_data['bookingpress_appointment_placeholders'] = $appointment_placeholders;
        $notifications_data['bookingpress_notification_duration']    = $notification_duration;

        $notifications_data['default_notification_status'] = [
            'customer' => [
                'appointment_approved' => true,
                'appointment_pending'  => true,
                'appointment_rejected' => true,
                'appointment_canceled' => true,
                'share_appointment'    => true,
            ],
            'employee' => [
                'appointment_approved' => true,
                'appointment_pending'  => true,
                'appointment_rejected' => true,
                'appointment_canceled' => true,
                'share_appointment'    => true,
            ],
        ];

        return $notifications_data;
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, static::$slug ) === false && strpos( $hook, 'bookingpress_notifications' ) === false ) {
            return;
        }

        if ( class_exists( '\BookingPressPro\admin\Notifications' ) ) {
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
            [ 'vue' ],
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
            'bookingpress-notifications-loader',
            BOOKINGPRESS_URL . '/src/assets/js/notifications-loader.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script( 'wp-api-fetch' );
        wp_enqueue_script_module( 'bookingpress-notifications-loader' );

        // Ensure WordPress TinyMCE and editor scripts/styles are loaded
        wp_enqueue_script( 'wp-tinymce' );
        wp_enqueue_script( 'editor' );

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

        wp_enqueue_style( 'editor-buttons' );
    }

    public static function render_page() {
        if ( class_exists( '\BookingPressPro\admin\Notifications' ) ) {
            \BookingPressPro\admin\Notifications::render_page();
            return;
        }
        self::render_view( 'Notifications', [
            'title' => esc_html__( 'Notifications', 'bookingpress-appointment-booking' ),
        ] );
    }
}

<?php

namespace BookingPress\core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Permissions {

    public static function retrieve_capabilities() {
        $capabilities = array(
            'bookingpress_appointments' => array(
                'retrieve_appointments',
                'add_appointments',
                'edit_appointments',
                'approve_appointments',
                'delete_appointments',
                'search_appointment',
                'retrieve_customers',
                'search_customer',
                'search_user',
                'get_share_url_generated',
                'share_generated_url',
                'retrieve_wp_page_list',
            ),
            'bookingpress_calendar' => array(
                'retrieve_calendar_appointments',
                'add_calendar_appointments',
                'approve_appointments',
                'edit_appointments',
                'delete_appointments',
                'search_appointments',
                'search_customer',
                'retrieve_customers',
                'search_user',
            ),
            'bookingpress_customers' => array(
                'retrieve_customers',
                'add_customer',
                'edit_customer',
                'delete_customer',
                'search_customer',
                'search_user',
                'upload_customer_avatar',
                'remove_customer_avatar',
                'customer_export',
                'customer_import',
            ),
            'bookingpress_customize' => array(
                'save_form_fields',
                'save_mybooking_settings',
                'retrieve_form_fields',
                'update_field_position',
                'save_form_settings',
                'load_customization',
            ),
            'bookingpress' => array(
                'retrieve_upcoming_appointments',
                'retrieve_dashboard_summary',
                'retrieve_dashboard_chart',
                'update_upcoming_appointments',
                'set_dashboard_redirection',
            ),
            'bookingpress_notifications' => array(
                'save_email_notification',
                'retrieve_email_notification',
                'retrieve_email_notification_status',
                'remove_google_api_account',
            ),
            'bookingpress_payments' => array(
                'retrieve_payments',
                'approve_appointments',
                'add_payments',
                'edit_payments',
                'delete_payments',
                'search_payments',
                'change_payment_status',
            ),
            'bookingpress_services' => array(
                'retrieve_categories',
                'add_categories',
                'edit_categories',
                'delete_categories',
                'search_categories',
                'retrieve_services',
                'add_services',
                'edit_services',
                'delete_services',
                'search_services',
                'update_category_position',
                'manage_service_position',
                'upload_service_avatar',
                'remove_service_avatar',
            ),
            'bookingpress_settings' => array(
                'save_settings',
                'retrieve_settings',
                'delete_holidays',
                'remove_company_avatar',
                'save_workhours',
                'retrieve_workhours',
                'save_default_daysoff',
                'retrieve_default_daysoff',
                'upload_company_avatar',
                'send_test_email',
                'save_default_holidays',
                'retrieve_holidays',
                'retrieve_currency_status',
                'view_debug_payment_logs',
                'clear_debug_payment_logs',
                'download_debug_payment_logs',
                'send_test_gmail_email',
                'import_export_settings',
            ),
            'bookingpress_addons' => array(
                'retrieve_addons',
            ),
            'bookingpress_growth_tools' => array(
                'retrieve_plugin',
            ),
        );

        $capabilities = apply_filters( 'bookingpress_modify_capability_data', $capabilities );

        return $capabilities;
    }

    public static function user_can_do_action( $action_name ) {

        if ( empty( $action_name ) || ! is_string( $action_name ) ) {
            return false;
        }

        $capabilities = self::retrieve_capabilities();

        if ( empty( $capabilities ) || ! is_array( $capabilities ) ) {
            return false;
        }

        foreach ( $capabilities as $capability => $bpa_action ) {
            if (
                ! empty( $bpa_action ) &&
                is_array( $bpa_action ) &&
                in_array( $action_name, $bpa_action, true ) &&
                current_user_can( $capability )
            ) {
                return true;
            }
        }

        return false;
    }
}
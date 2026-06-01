<?php

namespace BookingPress\admin;

if( !defined( 'ABSPATH' ) ) {
    exit;
}

class Dashboard extends Base{

    protected static $slug = 'bookingpress';

    public static function init(){
        parent::init();

        add_filter( 'script_module_data_bookingpress-dashboard-loader', [ __CLASS__, 'bookingpress_add_script_module_data' ] );
        
    }

    public static function bookingpress_add_script_module_data( $dashboard_data ){

        global $BookingPress, $bookingpress_global_options, $bookingpress_slugs;

        $bookingpress_global_details  = $bookingpress_global_options->bookingpress_global_options();              
        $bpa_time_format_for_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];

        $bookingpress_wp_default_time_format = $bookingpress_global_details['wp_default_time_format'];

        $bookingpress_default_date_format = $BookingPress->bookingpress_check_common_date_format_for_picker($bookingpress_global_details['wp_default_date_format']);
        
        $bookingpres_default_time_format = $BookingPress->bookingpress_get_settings('default_time_format','general_setting');
        $bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();

        $currency_name   = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
        $currency_name   = ! empty($currency_name) ? $currency_name : 'US Dollar';
        $currency_symbol = $BookingPress->bookingpress_get_currency_symbol($currency_name);
        $dashboard_data['chart_currency_symbol'] = $currency_symbol;

        $week_number  = date( 'W' );
        $current_year = date( 'Y' );
        $week_dates   = $BookingPress->get_weekstart_date_end_date( $week_number, $current_year );
        $week_start   = $week_dates['week_start'];
        $week_end     = $week_dates['week_end'];
        $dashboard_data['custom_filter_val'] = array($week_start, $week_end);
        $dashboard_data['currently_selected_filter'] = 'custom';

        $dashboard_data['bpa_date_common_date_format'] = $bookingpress_default_date_format;
        
        if($bookingpres_default_time_format == "H:i"){
            $dashboard_data['bpa_date_time_common_date_format'] = $bookingpress_default_date_format .' HH:mm';
        } else {
            $dashboard_data['bpa_date_time_common_date_format'] = $bookingpress_default_date_format .' hh:mm a';
        }

        $dashboard_data['bpa_shortcode_title'] = [
            'today' => esc_html__( 'Today', 'bookingpress-appointment-booking' ),
            'yesterday' => esc_html__( 'Yesterday', 'bookingpress-appointment-booking' ),
            'tomorrow' => esc_html__( 'Tomorrow', 'bookingpress-appointment-booking' ),
            'this_week' => esc_html__( 'This week', 'bookingpress-appointment-booking' ),
            'last_week' => esc_html__( 'Last week', 'bookingpress-appointment-booking' ),
            'this_month' => esc_html__( 'This month', 'bookingpress-appointment-booking' ),
            'last_month' => esc_html__( 'Last month', 'bookingpress-appointment-booking' ),
            'this_year' => esc_html__( 'This year', 'bookingpress-appointment-booking' ),
            'last_year' => esc_html__( 'Last year', 'bookingpress-appointment-booking' ),
        ];

        $dashboard_data['redirect_urls'] = [
            'appointments' => add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_appointments), esc_url(admin_url() . 'admin.php?page=bookingpress')),
            'customers' => add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_customers), esc_url(admin_url() . 'admin.php?page=bookingpress')),
            'payments' => add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_payments), esc_url(admin_url() . 'admin.php?page=bookingpress')),
        ];

        $dashboard_data['chart_titles'] = [
            'approved_appointments'         => esc_html__( 'Approved Appointments', 'bookingpress-appointment-booking' ),
            'pending_appointments'          => esc_html__( 'Pending Appointments', 'bookingpress-appointment-booking' ),
            'appointments'                  => esc_html__( 'Appointments', 'bookingpress-appointment-booking' ),
            'revenue'                       => esc_html__( 'Revenue', 'bookingpress-appointment-booking' ),
            'customers'                     => esc_html__( 'Customers', 'bookingpress-appointment-booking' ),
        ];

        $dashboard_data['appointment_status'] = $bookingpress_global_details['appointment_status'];

        $dashboard_data['status_change_messages'] = [
            'success' => esc_html__( 'Appointment status updated successfully.', 'bookingpress-appointment-booking' ),
            'error' => esc_html__( 'Failed to update appointment status. Please try again.', 'bookingpress-appointment-booking' ),
            'booked_slot' => esc_html__( 'Appointment already booked for this slot.', 'bookingpress-appointment-booking' ),
        ];

        return $dashboard_data;
    }

    public static function enqueue_assets( $hook ){

        // Check if we are on the calendar page        
        if ( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'bookingpress' ) {
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
            'bookingpress-appointment-model',
            BOOKINGPRESS_URL . '/src/assets/js/appointment-model.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-appointment-model' );

        wp_register_script_module(
            'bookingpress-customer-model',
            BOOKINGPRESS_URL . '/src/assets/js/customer-model.js',
            [ 'bookingpress-ui'],
            BOOKINGPRESS_VERSION
        );
        wp_enqueue_script_module( 'bookingpress-customer-model' );

        wp_register_script_module(
            'bookingpress-sidemenu-drawer',
            BOOKINGPRESS_URL . '/src/assets/js/drawer-loader.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-sidemenu-drawer' );

        wp_register_script_module(
            'chartjs',
            BOOKINGPRESS_URL . '/src/assets/js/chart.umd.min.js',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'chartjs' );

        wp_register_script_module(
            'bookingpress-dashboard-loader',
            BOOKINGPRESS_URL . '/src/assets/js/dashboard-loader.js',
            [ 'bookingpress-ui'],
            BOOKINGPRESS_VERSION
        );
        wp_enqueue_script_module( 'bookingpress-dashboard-loader' );

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
        self::render_view( 'Dashboard', [
            'title' => esc_html__( 'BookingPress', 'bookingpress-appointment-booking' )
        ] );
    }

}
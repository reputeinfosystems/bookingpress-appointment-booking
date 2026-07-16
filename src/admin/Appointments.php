<?php

namespace BookingPress\admin;

if( !defined( 'ABSPATH' ) ) {
    exit;
}

use BookingPress\data\ServicesProviders;

class Appointments extends Base{

    public static function init(){
        parent::init();

        add_filter( 'script_module_data_bookingpress-appointment-loader', [ __CLASS__, 'bookingpress_add_script_module_data' ] );
    }

    public static function bookingpress_add_script_module_data( $appointment_data ){

        global $BookingPress, $bookingpress_global_options, $bookingpress_slugs;

        
        $bookingpress_global_details  = $bookingpress_global_options->bookingpress_global_options();              
        $bpa_time_format_for_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];

        $bookingpress_wp_default_time_format = $bookingpress_global_details['wp_default_time_format'];

        $bookingpress_default_date_format = $BookingPress->bookingpress_check_common_date_format_for_picker($bookingpress_global_details['wp_default_date_format']);
        
        $bookingpres_default_time_format = $BookingPress->bookingpress_get_settings('default_time_format','general_setting');
        $bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();

        $appointment_data['appointment_date_range'] = array( date('Y-m-d', strtotime('-3 Day')), date('Y-m-d', strtotime('+3 Day')) );
        $appointment_data['bpa_date_common_date_format'] = $bookingpress_default_date_format;

        $appointment_data['firstDayOfWeek'] = intval( $bookingpress_global_details['start_of_week'] );

        $ServiceProviders = ServicesProviders::get_services_group_with_category( true);
        $appointment_data['BookingPressServiceProviders']   = $ServiceProviders;
        $appointment_data['appointment_services_list']      = $ServiceProviders;

        $bookingpress_locale_lang = $bookingpress_global_details['locale'];
        $bookingpress_pagination  = $bookingpress_global_details['pagination'];

        $bookingpress_pagination_arr      = json_decode($bookingpress_pagination, true);
        $bookingpress_pagination_selected = $bookingpress_pagination_arr[0];

        $appointment_data['appointment_status']     = $bookingpress_global_details['appointment_status'];

        $appointment_data['pagination_length']      = $bookingpress_pagination;
        $appointment_data['pagination_selected_length'] = $bookingpress_pagination_selected;

        $bookingpress_default_perpage_option                               = $BookingPress->bookingpress_get_settings('per_page_item', 'general_setting');
        $appointment_data['perPage']               = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '20';
        $appointment_data['pagination_length_val'] = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '20';

        $appointment_data['bulk_action_labels'] = [
            'bulk_action' => esc_html__('Bulk Action', 'bookingpress-appointment-booking'),
            'delete' => esc_html__('Delete', 'bookingpress-appointment-booking')
        ];

        $appointment_data['status_change_messages'] = [
            'success' => esc_html__( 'Appointment status updated successfully.', 'bookingpress-appointment-booking' ),
            'error' => esc_html__( 'Failed to update appointment status. Please try again.', 'bookingpress-appointment-booking' ),
            'booked_slot' => esc_html__( 'Appointment already booked for this slot.', 'bookingpress-appointment-booking' ),
        ];

        $appointment_data['share_url_validation_messages'] = [
            'service_required' => esc_html__( 'Please select service.', 'bookingpress-appointment-booking' ),
            'page_required' => esc_html__( 'Please select page.', 'bookingpress-appointment-booking' ),
            'sharing_email' => esc_html__( 'Please enter email address.', 'bookingpress-appointment-booking' ),
        ];

        $bpa_default_booking_page = get_page_by_path('book-appointment');
        $bpa_default_booking_page_id = '';
        if(!empty($bpa_default_booking_page->ID)){
            $bpa_default_booking_page_id = $bpa_default_booking_page->ID;
        }
        $bpa_default_booking_page_url = get_permalink($bpa_default_booking_page_id);

        $appointment_data['selected_page_id'] = $bpa_default_booking_page_id;
        $appointment_data['generated_url'] = $bpa_default_booking_page_url;

        $bpa_new_wp_pages = array();
        $bpa_wp_pages = get_pages();
        if(!empty($bpa_wp_pages)){
            foreach($bpa_wp_pages as $bpa_wp_page_key => $bpa_wp_page_val){
                $bpa_new_wp_pages[] = array(
                    'id' => $bpa_wp_page_val->ID,
                    'title' => $bpa_wp_page_val->post_title,
                    'url' => get_permalink(get_page_by_path($bpa_wp_page_val->post_name)),
                );
            }
        }
        $appointment_data['all_share_pages'] = $bpa_new_wp_pages;
        $appointment_data['all_share_pages_list'] = array();

        $appointment_data['common_messages'] = [
            'success' => esc_html__( 'Success', 'bookingpress-appointment-booking' ),
            'url_copy_msg' => esc_html__( 'URL copied successfully.', 'bookingpress-appointment-booking' ),
            'bulk_action_select' => esc_html__( 'Please select any action.', 'bookingpress-appointment-booking' ),
            'bulk_action_no_selection' => esc_html__( 'Please select one or more records to perform action.', 'bookingpress-appointment-booking' ),
        ];

        return $appointment_data;
    }

    public static function enqueue_assets( $hook ){

        if ( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'bookingpress_appointments' ) {
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
            'bookingpress-appointment-loader',
            BOOKINGPRESS_URL . '/src/assets/js/appointment-loader.js',
            [ 'bookingpress-ui'],
            BOOKINGPRESS_VERSION
        );
        wp_enqueue_script_module( 'bookingpress-appointment-loader' );


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
        self::render_view( 'Appointments', [
            'title' => esc_html__( 'Appointments', 'bookingpress-appointment-booking' )
        ] );
    }

}
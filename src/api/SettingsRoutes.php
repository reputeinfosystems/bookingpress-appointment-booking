<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

class SettingsRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/settings/fetch',
            array(
                'methods'  => 'POST',
                'callback' => [$this, 'bookingpress_get_settings_details_rest'],
                'permission_callback' =>  $this->permission_callback_for( 'retrieve_settings' )
            )
        );

        register_rest_route( 'bookingpress-app/v1', '/settings/save',
            array(
                'methods'  => 'POST',
                'callback' => [$this, 'bookingpress_save_settings_details_rest'],
                'permission_callback' =>  $this->permission_callback_for( 'save_settings' )
            )
        );

        register_rest_route('bookingpress-app/v1', '/settings/remove_company_avatar', [
            'methods'             => 'POST',
            'callback'            =>  [$this,'bookingpress_remove_company_avatar_callback'],
            'permission_callback' =>  $this->permission_callback_for( 'remove_company_avatar' ),
        ]);       

        register_rest_route( 'bookingpress-app/v1', '/settings/upload_company_avatar', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_import_customer_process_upload' ],
            'permission_callback' =>  $this->permission_callback_for( 'upload_company_avatar' )
        ] ); 
        
        register_rest_route( 'bookingpress-app/v1', '/settings/get_default_work_hours_details', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_get_default_work_hours_details' ],
            'permission_callback' =>  $this->permission_callback_for( 'retrieve_workhours' )
        ] ); 

        register_rest_route( 'bookingpress-app/v1', '/settings/load_daysoff_details', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_load_daysoff_details' ],
            'permission_callback' =>  $this->permission_callback_for( 'retrieve_holidays' )
        ] ); 

        register_rest_route( 'bookingpress-app/v1', '/settings/save_daysoff_details', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_save_daysoff_details' ],
            'permission_callback' =>  $this->permission_callback_for( 'save_default_holidays' )
        ] );
        
        register_rest_route( 'bookingpress-app/v1', '/settings/delete_daysoff_details', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_delete_daysoff_details' ],
            'permission_callback' =>  $this->permission_callback_for( 'delete_holidays' )
        ] ); 

        register_rest_route( 'bookingpress-app/v1', '/settings/check_currency_status', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_check_currency_status' ],
            'permission_callback' =>  $this->permission_callback_for( 'retrieve_currency_status' )
        ] ); 

        register_rest_route( 'bookingpress-app/v1', '/settings/import_data_continue_process', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_import_data_continue_process' ],
            'permission_callback' =>  $this->permission_callback_for( 'import_export_settings' )
        ] ); 

        register_rest_route( 'bookingpress-app/v1', '/settings/import-data-process', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_import_data_process_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'import_export_settings' )
        ] ); 
        
        register_rest_route( 'bookingpress-app/v1', '/settings/export-data-continue-process', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_export_data_continue_process' ],
            'permission_callback' =>  $this->permission_callback_for( 'import_export_settings' )
        ] ); 

        register_rest_route( 'bookingpress-app/v1', '/settings/export-data-process', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_export_data_process_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'import_export_settings' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/export-data-stop', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_export_data_stop_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'import_export_settings' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/send_test_wpmail_email', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bookingpress_send_test_wpmail_email' ],
            'permission_callback' =>  $this->permission_callback_for( 'send_test_gmail_email' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/send_test_smtp_email', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_send_test_smtp_email_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'send_test_email' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/send_test_gmail_email', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_send_test_gmail_email_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'send_test_gmail_email')
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/signout_google_account', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_signout_google_account_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'remove_google_api_account' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/view_debug_payment_log', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_view_debug_payment_log_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'view_debug_payment_logs' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/clear_debug_payment_log', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_clear_debug_payment_log_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'clear_debug_payment_logs' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/download_debug_payment_log', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_download_debug_payment_log_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'download_debug_payment_logs' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/settings/save_default_work_hours', [
            'methods'  => 'POST',
            'callback' => [ $this, 'bpa_save_default_work_hours_func' ],
            'permission_callback' =>  $this->permission_callback_for( 'save_workhours' )
        ] );

    }

    function bookingpress_get_settings_details_rest($request){
        global $bookingpress_settings;        
        $params = $request->get_json_params();
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['setting_type'] = $_POST['setting_type'] = ! empty( $params['setting_type'] ) ? sanitize_text_field( $params['setting_type'] ) : '';
        $response = $bookingpress_settings->bookingpress_get_settings_details(true);
        if ( 'error' === $response['variant'] ) {
            return new \WP_Error(
                'rest_error',
                $response['msg'],
                array( 'status' => 400 )
            );
        }
        /* $json_data = array(
            'success' => true,
            'data'    => $response['data'],
            'variant' => $response['variant'],
            'title'   => $response['title'],
            'msg'     => $response['msg'],
        ); */

        $response['success'] = true;
        return new \WP_REST_Response( $response, 200 );
    }

    function bookingpress_save_settings_details_rest($request){
        global $bookingpress_settings;
        $normalized_params = $params = $request->get_json_params();
        $normalized_params = array_map(function($value) { if ($value === true)  return 'true'; if ($value === false) return 'false'; return $value; }, $params);
        $_POST  = $normalized_params;        
        $_REQUEST                 = $normalized_params;
        $_POST['action']          = 'bookingpress_save_settings_data';
        $_REQUEST['action']       = 'bookingpress_save_settings_data';
        $_POST['settingType']     = !empty($normalized_params['settingType']) ? $normalized_params['settingType'] : '';
        $_REQUEST['settingType']  = $_POST['settingType'];
        $_POST['_wpnonce']        = wp_create_nonce('bpa_wp_nonce');
        $_REQUEST['_wpnonce']     = $_POST['_wpnonce'];
        $response = $bookingpress_settings->bookingpress_save_settings_details(true);
        return new \WP_REST_Response(array(
            'variant' => $response['variant'],
            'title'   => $response['title'],
            'msg'     => $response['msg'],
        ), 200);
    }

    function bookingpress_remove_company_avatar_callback($request){
        global $bookingpress_settings;      
        $params = $request->get_json_params();        
        $_POST['upload_file_url'] = $params['upload_file_url'];
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $response = ['success' => false];
        if (! empty($_POST) && ! empty($_POST['upload_file_url']) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_uploaded_avatar_url = esc_url_raw($_POST['upload_file_url']); // phpcs:ignore
            $bookingpress_file_name_arr       = explode('/', $bookingpress_uploaded_avatar_url);
            $bookingpress_file_name           = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
            if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
                wp_delete_file(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
            }
            $response = ['success' => true];
        }
        return new \WP_REST_Response( $response, 200 );
    }

    function bpa_import_customer_process_upload($request){
        global $bookingpress_settings;
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bookingpress_upload_company_avatar' );
        $_REQUEST['action']   = 'bookingpress_upload_company_avatar';
        $files = $request->get_file_params();   
        if( !empty( $files['file'] ) ) {
            $_FILES['file'] = $files['file'];
        }        
        $response = $bookingpress_settings->bookingpress_upload_company_avatar_func();
        if ( ! empty( $response['error'] ) && $response['error'] == 1 ) {
            return new \WP_Error( 'rest_error', $response['msg'], [ 'status' => 400 ] );
        }
        return new \WP_REST_Response( $response, 200 );
    }

    function bpa_get_default_work_hours_details($request){
        global $bookingpress_settings;
        $response = $bookingpress_settings->bookingpress_get_default_work_hours();

        if ( 'error' === $response['variant'] ) {
            return new \WP_Error(
                'rest_error',
                $response['msg'],
                array( 'status' => 400 )
            );
        }
        return new \WP_REST_Response(
            array(
                'success' => true,
                'data'    => $response['data'],
                'variant' => $response['variant'],
                'title'   => $response['title'],
                'msg'     => $response['msg'],
                'default_break_times' => $response['default_break_times'],
                'selected_workhours' => $response['selected_workhours'],
            ),
            200
        );
    }

    function bpa_load_daysoff_details($request){

        global $wpdb, $tbl_bookingpress_default_daysoff;
        $params = $request->get_json_params();

        $daysoff_selected_year = ! empty( $params['selected_year'] )  ? sanitize_text_field( $params['selected_year'] )  : date('Y', current_time('timestamp'));

        $default_daysoff_details = array();
        
        $daysoff_details = $wpdb->get_results(  $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_default_daysoff} where bookingpress_dayoff_parent = %d", 0 ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        foreach ( $daysoff_details as $daysoff_details_key => $daysoff_details_val ) {

            $daysoff_date               = esc_html( $daysoff_details_val['bookingpress_dayoff_date'] );
            $bookingpress_dayoff_enddate = esc_html( $daysoff_details_val['bookingpress_dayoff_enddate'] );
            $daysoff_end_date           = date('c', strtotime($daysoff_date));

            if ( $bookingpress_dayoff_enddate != null && $bookingpress_dayoff_enddate != 'null' ) {
                $daysoff_end_date = date('c', strtotime($bookingpress_dayoff_enddate));
            }

            $yearly_repeat_class = ! empty( $daysoff_details_val['bookingpress_repeat'] )  ? 'bpa-daysoff-calendar-col--item__highlight--yearly bpa_selected_daysoff': 'bpa-daysoff-calendar-col--item__highlight--single-dayoff bpa_selected_daysoff';

            $dayoff_year = date('Y', strtotime($daysoff_date));

            if ( empty( $daysoff_details_val['bookingpress_repeat'] ) && ( $dayoff_year == $daysoff_selected_year ) ) {
                $default_daysoff_details[] = array(
                    'dayoff_id' => esc_html( $daysoff_details_val['bookingpress_dayoff_id'] ),
                    'id'        => date('Y-m-d', strtotime($daysoff_date)),
                    'date'      => date('c', strtotime($daysoff_date)),
                    'end_date'  => $daysoff_end_date,
                    'class'     => $yearly_repeat_class,
                    'off_name'  => stripslashes_deep( $daysoff_details_val['bookingpress_name'] ),
                );
            } elseif ( ! empty( $daysoff_details_val['bookingpress_repeat'] ) && ( $daysoff_selected_year >= $dayoff_year ) ) {
                $daysoff_new_date_month = $daysoff_selected_year . '-' . date('m-d', strtotime($daysoff_date));
                $daysoff_end_date       = date('c', strtotime($daysoff_new_date_month));

                if ( $bookingpress_dayoff_enddate != null && $bookingpress_dayoff_enddate != 'null' ) {
                    $daysoff_new_end_date_month = $daysoff_selected_year . '-' . date('m-d', strtotime($bookingpress_dayoff_enddate));
                    $daysoff_end_date           = date('c', strtotime($daysoff_new_end_date_month));
                }

                $default_daysoff_details[] = array(
                    'dayoff_id' => esc_html( $daysoff_details_val['bookingpress_dayoff_id'] ),
                    'id'        => $daysoff_new_date_month,
                    'date'      => date('c', strtotime($daysoff_new_date_month)),
                    'end_date'  => $daysoff_end_date,
                    'class'     => $yearly_repeat_class,
                    'off_name'  => stripslashes_deep( $daysoff_details_val['bookingpress_name'] ),
                );
            }
        }

        $response['variant']      = 'success';
        $response['title']        = esc_html__('Success', 'bookingpress-appointment-booking');
        $response['msg']          = esc_html__('DaysOff data retrieved successfully', 'bookingpress-appointment-booking');
        $response['daysoff_data'] = $default_daysoff_details;
        return new \WP_REST_Response( $response, 200 );
    }

    function bpa_delete_daysoff_details($request){
        global $wpdb, $tbl_bookingpress_default_daysoff;
        $params = $request->get_json_params();
        $daysoff_date = ! empty($params['days_off_form']['selected_date']) ? sanitize_text_field($params['days_off_form']['selected_date']) : ''; // phpcs:ignore
        $dayoff_id = ! empty($params['days_off_form']['dayoff_id']) ? sanitize_text_field($params['days_off_form']['dayoff_id']) : 0; // phpcs:ignore
        if (!empty($dayoff_id) ) {
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$tbl_bookingpress_default_daysoff} WHERE (bookingpress_dayoff_id = %d OR bookingpress_dayoff_parent = %d)", $dayoff_id,$dayoff_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False Positive alarm
        }
        $response['variant'] = 'success';
        $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('DaysOff deleted successfully', 'bookingpress-appointment-booking');
        return new \WP_REST_Response( $response, 200 );
    }

    function bpa_save_daysoff_details($request){
        global $wpdb, $tbl_bookingpress_default_daysoff;

        $response = array(
            'variant' => 'error',
            'title'   => esc_html__('Error', 'bookingpress-appointment-booking'),
            'msg'     => esc_html__('Something went wrong', 'bookingpress-appointment-booking'),
        );
        $params = $request->get_json_params();
        $daysoff_details = !empty($params['days_off_form']) && is_array($params['days_off_form']) ? $params['days_off_form'] : array();

        $daysoff_title = !empty($daysoff_details['daysoff_title']) ? sanitize_text_field($daysoff_details['daysoff_title']) : '';
        $is_repeat_daysoff = !empty($daysoff_details['is_repeat_days_off']) && filter_var($daysoff_details['is_repeat_days_off'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $daysoff_date = !empty($daysoff_details['selected_date']) ? sanitize_text_field($daysoff_details['selected_date']) : '';
        $daysoff_end_date = !empty($daysoff_details['selected_end_date']) ? sanitize_text_field($daysoff_details['selected_end_date']) : $daysoff_date;
        $dayoff_id = isset($daysoff_details['dayoff_id']) ? intval($daysoff_details['dayoff_id']) : 0;

        $daysoff_date = !empty($daysoff_date) ? date('Y-m-d', strtotime($daysoff_date)) : '';
        $daysoff_end_date = !empty($daysoff_end_date) ? date('Y-m-d', strtotime($daysoff_end_date)) : $daysoff_date;

        if ($daysoff_date > $daysoff_end_date) {
            $range_start = $daysoff_end_date;
            $daysoff_end_date = $daysoff_date;
            $daysoff_date = $range_start;
        }

        if (empty($daysoff_title)) {
            $response['msg'] = esc_html__('Please fill Break Title', 'bookingpress-appointment-booking');
            return new \WP_REST_Response($response, 200);
        }

        if (empty($daysoff_date) || empty($daysoff_end_date)) {
            return new \WP_REST_Response($response, 200);
        }

        $bookingpress_child_holiday_dates = array();
        $range_date = new \DateTimeImmutable($daysoff_date);
        $range_end = new \DateTimeImmutable($daysoff_end_date);
        while ($range_date < $range_end) {
            $range_date = $range_date->modify('+1 day');
            $bookingpress_child_holiday_dates[] = $range_date->format('Y-m-d');
        }

        $daysoff_database_data = array(
            'bookingpress_name'           => $daysoff_title,
            'bookingpress_dayoff_date'    => $daysoff_date,
            'bookingpress_dayoff_enddate' => $daysoff_end_date,
            'bookingpress_repeat'         => $is_repeat_daysoff,
        );

        if ($dayoff_id !== 0) {
            $wpdb->update(
                $tbl_bookingpress_default_daysoff,
                $daysoff_database_data,
                array('bookingpress_dayoff_id' => $dayoff_id)
            );
            $wpdb->delete(
                $tbl_bookingpress_default_daysoff,
                array('bookingpress_dayoff_parent' => $dayoff_id)
            );
            $dayoff_parent_id = $dayoff_id;
        } else {
            $bookingpress_daysoff_exists = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT bookingpress_dayoff_id FROM {$tbl_bookingpress_default_daysoff} WHERE bookingpress_dayoff_date >= %s AND bookingpress_dayoff_date <= %s",
                    $daysoff_date,
                    $daysoff_end_date
                ),
                ARRAY_A
            ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            if (!empty($bookingpress_daysoff_exists)) {
                $response['msg'] = esc_html__('Holiday already added.', 'bookingpress-appointment-booking');
                return new \WP_REST_Response($response, 200);
            }

            $wpdb->insert($tbl_bookingpress_default_daysoff, $daysoff_database_data);
            $dayoff_parent_id = intval($wpdb->insert_id);
        }

        foreach ($bookingpress_child_holiday_dates as $holiday_date) {
            $wpdb->insert(
                $tbl_bookingpress_default_daysoff,
                array(
                    'bookingpress_name'           => $daysoff_title,
                    'bookingpress_dayoff_date'    => $holiday_date,
                    'bookingpress_dayoff_enddate' => $daysoff_end_date,
                    'bookingpress_dayoff_parent'  => $dayoff_parent_id,
                    'bookingpress_repeat'         => $is_repeat_daysoff,
                )
            );
        }

        wp_cache_delete('bookingpress_all_general_settings');
        wp_cache_delete('bookingpress_all_customize_settings');

        $response['variant'] = 'success';
        $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
        $response['msg'] = esc_html__('Holiday has been saved successfully.', 'bookingpress-appointment-booking');

        return new \WP_REST_Response($response, 200);
    }

    function bpa_check_currency_status($request){

        global $wpdb,$bookingpress_global_options,$bookingpress_payment_gateways,$BookingPress;
        $response              = array();
        $response['variant'] = 'error';
        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
        $params = $request->get_json_params();

        $bookingpress_paypal_currency = $bookingpress_payment_gateways->bookingpress_paypal_supported_currency_list(); 

        $bookingpress_currency = (isset($params['bookingpress_currency']) && !empty($params['bookingpress_currency'])) ? sanitize_text_field($params['bookingpress_currency']) : '';
        if(empty($bookingpress_currency)) {
            $bookingpress_currency = $BookingPress->bookingpress_get_settings('payment_default_currency','payment_setting');
        }
        $message = '';
        $notAllow = array();
        if (!empty($bookingpress_currency)) {
            if (!in_array($bookingpress_currency, $bookingpress_paypal_currency)) {
                $notAllow[] = 'paypal';
            }
            $notAllow = apply_filters('bookingpress_currency_support', $notAllow, $bookingpress_currency);
            if (!empty($notAllow)) {
                $message = esc_html__('This currency is not supported by', 'bookingpress-appointment-booking');
                $message .= ' ' . implode(', ', $notAllow) . ' ';
            }
            $response = array('variant' => 'success','title'=>esc_html__('Success', 'bookingpress-appointment-booking'), 'msg' => $message);                       
        }   
        return new \WP_REST_Response( $response, 200 );
    }

    function bpa_import_data_continue_process($request){
        global $bookingpress_import_export;
        $params = $request->get_json_params();
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $import_id = !empty($params['import_id']) ? $params['import_id'] : '';
        return $bookingpress_import_export->bookingpress_import_data_continue_process_func( $import_id, true );
    }

    function bpa_import_data_process_func($request){
        $params = $request->get_json_params();
        global $bookingpress_import_export;         
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $bookingpress_import_data = !empty($params['bookingpress_import_data']) ? $params['bookingpress_import_data'] : '';        
        $_POST['bookingpress_import_data'] = $bookingpress_import_data;
        $_REQUEST['confirm_import_data']   = !empty($params['confirm_import_data']) ? sanitize_text_field($params['confirm_import_data']) : '';

        $response = $bookingpress_import_export->bookingpress_import_data_process_func( true );
        return new \WP_REST_Response( $response, 200 );
    }   

    function bpa_export_data_continue_process($request){
        $params = $request->get_json_params();
        global $bookingpress_import_export;
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $_REQUEST['_wpnonce'] = wp_create_nonce('bpa_wp_nonce');
        $export_id = !empty($params['export_id']) ? sanitize_text_field($params['export_id']) : '';
        $response = $bookingpress_import_export->bookingpress_export_data_continue_process_func($export_id, true);
        return new \WP_REST_Response($response, 200);
    }

    function bpa_export_data_process_func($request){
        $params = $request->get_json_params();
        global $bookingpress_import_export;

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $_REQUEST['_wpnonce'] = wp_create_nonce('bpa_wp_nonce');
        $bookingpress_export_list_data = !empty($params['bookingpress_export_list_data']) ? $params['bookingpress_export_list_data'] : [];
        $_POST['bookingpress_export_list_data'] = is_array($bookingpress_export_list_data)  ? wp_json_encode($bookingpress_export_list_data)  : $bookingpress_export_list_data;
        $response = $bookingpress_import_export->bookingpress_export_data_process_func(true);
        return new \WP_REST_Response($response, 200);
    }

    function bpa_export_data_stop_func($request){
        $params = $request->get_json_params();
        global $bookingpress_import_export;
        $_REQUEST['_wpnonce'] = wp_create_nonce('bpa_wp_nonce');
        $_POST['export_log_stop_id'] = !empty($params['export_log_stop_id']) ? sanitize_text_field($params['export_log_stop_id']) : '';
        $response = $bookingpress_import_export->bookingpress_export_data_stop_func(true);
        return new \WP_REST_Response($response, 200);
    }

    function bookingpress_send_test_wpmail_email($request){
        $params = $request->get_json_params();
        $response               = array();
        $response['variant']    = 'error';
        $response['title']      = esc_html__( 'Error', 'bookingpress-appointment-booking');
        $response['msg']        = esc_html__('Something went wrong', 'bookingpress-appointment-booking');
        $notification_formdata           = !empty($params['notification_formdata']) ? $params['notification_formdata'] : [];
        $notification_test_mail_formdata = !empty($params['notification_test_mail_formdata']) ? $params['notification_test_mail_formdata'] : [];
        if(!empty($notification_formdata)){
            $wpmail_test_receiver_email = !empty($notification_test_mail_formdata['wpmail_test_receiver_email']) ? sanitize_email($notification_test_mail_formdata['wpmail_test_receiver_email']) : '';
            $wpmail_test_msg            = !empty($notification_test_mail_formdata['wpmail_test_msg']) ? sanitize_text_field($notification_test_mail_formdata['wpmail_test_msg']) : '';
            $from_email = !empty($notification_formdata['sender_email']) ? sanitize_email($notification_formdata['sender_email']) : get_option('admin_email');
            $from_name  = !empty($notification_formdata['send_name']) ? sanitize_text_field($notification_formdata['send_name']) : get_option('blogname');
            $bookingpress_email_header_data  = 'From: ' . $from_name . '<' . $from_email . "> \r\n";
            $bookingpress_email_header_data .= "Content-Type: text/html; charset=UTF-8\r\n";
            $wpmail_test_msg_subject = esc_html__('Test BookingPress WordPress default mail', 'bookingpress-appointment-booking');
            $return = wp_mail($wpmail_test_receiver_email, $wpmail_test_msg_subject, $wpmail_test_msg, $bookingpress_email_header_data);
            if($return){
                $response = array(
                    'is_mail_sent' => 1,
                    'error_msg'    => '',
                );
            } else {
                $response = array(
                    'is_mail_sent' => 0,
                    'error_msg'    => esc_html__('Mail could not be sent. Please check your mail configuration.', 'bookingpress-appointment-booking'),
                );
            }
        }
        return new \WP_REST_Response($response, 200);
    }

    function bpa_send_test_smtp_email_func($request){
        global $bookingpress_email_notifications;

        $params = $request->get_json_params();
        $response              = array();
        $response['variant']   = 'error';
        $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

        $notification_formdata           = !empty($params['notification_formdata']) ? $params['notification_formdata'] : [];
        $notification_test_mail_formdata = !empty($params['notification_test_mail_formdata']) ? $params['notification_test_mail_formdata'] : [];

        if (!empty($notification_formdata)) {
            $smtp_host                = !empty($notification_formdata['smtp_host']) ? sanitize_text_field($notification_formdata['smtp_host']) : '';
            $smtp_port                = !empty($notification_formdata['smtp_port']) ? sanitize_text_field($notification_formdata['smtp_port']) : '';
            $smtp_secure              = !empty($notification_formdata['smtp_secure']) ? sanitize_text_field($notification_formdata['smtp_secure']) : 'Disabled';
            $smtp_username            = !empty($notification_formdata['smtp_username']) ? sanitize_text_field($notification_formdata['smtp_username']) : '';
            $smtp_password            = !empty($notification_formdata['smtp_password']) ? $notification_formdata['smtp_password'] : '';
            $smtp_sender_name         = !empty($notification_formdata['sender_name']) ? sanitize_text_field($notification_formdata['sender_name']) : '';
            $smtp_sender_email        = !empty($notification_formdata['sender_email']) ? sanitize_email($notification_formdata['sender_email']) : '';
            $smtp_test_receiver_email = !empty($notification_test_mail_formdata['smtp_test_receiver_email']) ? sanitize_email($notification_test_mail_formdata['smtp_test_receiver_email']) : '';
            $smtp_test_msg            = !empty($notification_test_mail_formdata['smtp_test_msg']) ? sanitize_text_field($notification_test_mail_formdata['smtp_test_msg']) : '';

            $bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_test_email_notification(
                $smtp_host,
                $smtp_port,
                $smtp_secure,
                $smtp_username,
                $smtp_password,
                $smtp_test_receiver_email,
                $smtp_test_msg,
                $smtp_sender_email,
                $smtp_sender_name
            );

            $bookingpress_email_res = json_decode($bookingpress_email_res, true);

            $response = array(
                'is_mail_sent' => $bookingpress_email_res['is_mail_sent'],
                'error_msg'    => $bookingpress_email_res['error_msg'],
            );
        }
        return new \WP_REST_Response($response, 200);
    }

    function bpa_send_test_gmail_email_func($request){
        global $bookingpress_email_notifications;
        $params = $request->get_json_params();
        $response              = array();
        $response['variant']   = 'error';
        $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

        $notification_formdata           = !empty($params['notification_formdata']) ? $params['notification_formdata'] : [];
        $notification_test_mail_formdata = !empty($params['notification_test_mail_formdata']) ? $params['notification_test_mail_formdata'] : [];
        if (!empty($notification_formdata)) {
            $gmail_client_id           = !empty($notification_formdata['gmail_client_ID']) ? sanitize_text_field($notification_formdata['gmail_client_ID']) : '';
            $gmail_client_secret       = !empty($notification_formdata['gmail_client_secret']) ? sanitize_text_field($notification_formdata['gmail_client_secret']) : '';
            $bookingpress_gmail_connect = !empty($notification_formdata['bookingpress_response_email']) ? sanitize_email($notification_formdata['bookingpress_response_email']) : '';
            $gmail_sender_name         = !empty($notification_formdata['sender_name']) ? sanitize_text_field($notification_formdata['sender_name']) : '';
            $gmail_sender_email        = !empty($notification_formdata['sender_email']) ? sanitize_email($notification_formdata['sender_email']) : '';
            $bookingpress_gmail_auth   = !empty($notification_formdata['bookingpress_gmail_auth']) ? sanitize_text_field($notification_formdata['bookingpress_gmail_auth']) : '';
            $gmail_auth_secret         = !empty($notification_formdata['gmail_auth_secret']) ? $notification_formdata['gmail_auth_secret'] : '';
            $gmail_test_receiver_email = !empty($notification_test_mail_formdata['gmail_test_receiver_email']) ? sanitize_email($notification_test_mail_formdata['gmail_test_receiver_email']) : '';
            $gmail_test_msg            = !empty($notification_test_mail_formdata['gmail_test_msg']) ? sanitize_text_field($notification_test_mail_formdata['gmail_test_msg']) : '';
            $bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_test_gmail_notification(
                $gmail_client_id,
                $gmail_client_secret,
                $gmail_auth_secret,
                $gmail_test_receiver_email,
                $gmail_test_msg,
                $bookingpress_gmail_connect,
                $bookingpress_gmail_auth,
                $gmail_sender_email,
                $gmail_sender_name
            );
            $bookingpress_email_res = json_decode($bookingpress_email_res, true);
            $response = array(
                'is_mail_sent' => $bookingpress_email_res['is_mail_sent'],
                'error_msg'    => $bookingpress_email_res['error_msg'],
            );
        }
        return new \WP_REST_Response($response, 200);
    }

    function bpa_signout_google_account_func($request){
        global $wpdb, $BookingPress;
        $response = array();
        $BookingPress->bookingpress_update_settings('bookingpress_gmail_auth_token', 'notification_setting', '');
        $BookingPress->bookingpress_update_settings('bookingpress_response_email', 'notification_setting', '');
        $BookingPress->bookingpress_update_settings('bookingpress_gmail_auth', 'notification_setting', '');
        $response['variant'] = 'success';
        $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('Sign out successfully.', 'bookingpress-appointment-booking');
        return new \WP_REST_Response($response, 200);
    }

    function bpa_view_debug_payment_log_func($request){
        global $wpdb, $tbl_bookingpress_debug_payment_log;

        $params = $request->get_json_params();
        $response            = array();
        $response['variant'] = 'error';
        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

        $_POST['perpage'] = $perpage     = !empty($params['perpage']) ? intval($params['perpage']) : 20;
        $_POST['currentpage'] = $currentpage = !empty($params['currentpage']) ? intval($params['currentpage']) : 1;
        $offset      = (!empty($currentpage) && $currentpage > 1) ? (($currentpage - 1) * $perpage) : 0;

        $bookingpress_view_log_selector = !empty($params['bookingpress_debug_log_selector']) ? sanitize_text_field($params['bookingpress_debug_log_selector']) : '';

        $data = array();
        $payment_debug_log_data   = array();
        $total_payment_debug_logs = array();

        if (!empty($bookingpress_view_log_selector)) {
            
            $total_payment_debug_logs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_debug_payment_log . ' WHERE bookingpress_payment_log_gateway = %s ORDER BY bookingpress_payment_log_id DESC', $bookingpress_view_log_selector ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            $payment_debug_logs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_debug_payment_log . ' WHERE bookingpress_payment_log_gateway = %s ORDER BY bookingpress_payment_log_id DESC LIMIT %d, %d', $bookingpress_view_log_selector, $offset, $perpage ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            if (!empty($payment_debug_logs)) {
                $bookingpress_date_format = get_option('date_format');
                foreach ($payment_debug_logs as $payment_debug_log_val) {
                    $bookingpress_payment_log_id         = !empty($payment_debug_log_val['bookingpress_payment_log_id']) ? intval($payment_debug_log_val['bookingpress_payment_log_id']) : '';
                    $bookingpress_payment_log_event      = !empty($payment_debug_log_val['bookingpress_payment_log_event']) ? esc_html($payment_debug_log_val['bookingpress_payment_log_event']) : '';
                    $bookingpress_payment_log_raw_data   = !empty($payment_debug_log_val['bookingpress_payment_log_raw_data']) ? $payment_debug_log_val['bookingpress_payment_log_raw_data'] : '';
                    $bookingpress_payment_log_added_date = !empty($payment_debug_log_val['bookingpress_payment_log_added_date']) ? esc_html($payment_debug_log_val['bookingpress_payment_log_added_date']) : '';

                    $payment_debug_log_data[] = array(
                        'payment_debug_log_id'         => $bookingpress_payment_log_id,
                        'payment_debug_log_name'        => $bookingpress_payment_log_event,
                        'payment_debug_log_data'        => stripslashes_deep($bookingpress_payment_log_raw_data),
                        'payment_debug_log_added_date'  => date($bookingpress_date_format, strtotime($bookingpress_payment_log_added_date)),
                    );
                }
            }
        }

        $data['items'] = $payment_debug_log_data;
        $data['total'] = count($total_payment_debug_logs);
        $data = apply_filters('bookingpress_modify_debug_log_data', $data, $params);
        return new \WP_REST_Response($data, 200);
    }

    function bpa_download_debug_payment_log_func($request){
        global $wpdb, $tbl_bookingpress_debug_payment_log;

        $params = $request->get_json_params();

        $response            = array();
        $response['variant'] = 'error';
        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

        $bookingpress_view_log_selector          = !empty($params['bookingpress_debug_log_selector']) ? sanitize_text_field($params['bookingpress_debug_log_selector']) : '';
        $bookingpress_selected_download_duration = !empty($params['bookingpress_selected_download_duration']) ? sanitize_text_field($params['bookingpress_selected_download_duration']) : 'all';

        if (!empty($bookingpress_view_log_selector) && !empty($bookingpress_selected_download_duration)) {

            $bookingpress_debug_payment_log_where_cond = '';

            if (!empty($params['bookingpress_selected_download_custom_duration']) && $bookingpress_selected_download_duration == 'custom') {
                $bookingpress_start_date = !empty($params['bookingpress_selected_download_custom_duration'][0]) ? date('Y-m-d 00:00:00', strtotime(sanitize_text_field($params['bookingpress_selected_download_custom_duration'][0]))) : '';
                $bookingpress_end_date   = !empty($params['bookingpress_selected_download_custom_duration'][1]) ? date('Y-m-d 23:59:59', strtotime(sanitize_text_field($params['bookingpress_selected_download_custom_duration'][1]))) : '';
                $bookingpress_debug_payment_log_where_cond = $wpdb->prepare(' AND (bookingpress_payment_log_added_date >= %s AND bookingpress_payment_log_added_date <= %s)', $bookingpress_start_date, $bookingpress_end_date);
            } elseif (!empty($bookingpress_selected_download_duration) && $bookingpress_selected_download_duration != 'custom' && $bookingpress_selected_download_duration != 'all') {
                $bookingpress_last_selected_days           = date('Y-m-d', strtotime('-' . $bookingpress_selected_download_duration . ' days'));
                $bookingpress_debug_payment_log_where_cond = $wpdb->prepare(' AND (bookingpress_payment_log_added_date >= %s)', $bookingpress_last_selected_days);
            }

            $bookingpress_debug_payment_log_query = $wpdb->prepare( 'SELECT * FROM `' . $tbl_bookingpress_debug_payment_log . "` WHERE `bookingpress_payment_log_gateway` = %s AND `bookingpress_payment_log_status` = 1 " . $bookingpress_debug_payment_log_where_cond . ' ORDER BY bookingpress_payment_log_id DESC', $bookingpress_view_log_selector); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            $bookingpress_debug_payment_log_query = apply_filters('bookingpress_modify_download_debug_log_query', $bookingpress_debug_payment_log_query, $bookingpress_view_log_selector, $params);

            $bookingpress_payment_debug_log_data = $wpdb->get_results($bookingpress_debug_payment_log_query, ARRAY_A); // phpcs:ignore

            $bookingpress_payment_debug_log_data = apply_filters('bookingpress_modify_download_debug_log_data', $bookingpress_payment_debug_log_data, $bookingpress_view_log_selector, $params);

            $bookingpress_download_data = wp_json_encode($bookingpress_payment_debug_log_data);

            if (!function_exists('WP_Filesystem')) {
                include_once ABSPATH . 'wp-admin/includes/file.php';
            }
            WP_Filesystem();
            global $wp_filesystem;

            $bookingpresss_debug_log_file_name = 'bookingpress_debug_logs_' . $bookingpress_view_log_selector . '_' . $bookingpress_selected_download_duration;
            $wp_filesystem->put_contents(BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.txt', $bookingpress_download_data, 0644);

            $debug_log_file_name = '';

            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                $zip->open(BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.zip', \ZipArchive::CREATE);
                $zip->addFile(BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.txt', $bookingpresss_debug_log_file_name . '.txt');
                $zip->close();
                $bookingpress_download_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpresss_debug_log_file_name . '.zip';
                $debug_log_file_name       = $bookingpresss_debug_log_file_name . '.zip';
            } else {
                $bookingpress_download_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpresss_debug_log_file_name . '.txt';
                $debug_log_file_name       = $bookingpresss_debug_log_file_name . '.txt';
            }

            $response['variant'] = 'success';
            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('log download successfully', 'bookingpress-appointment-booking');
            $response['url']     = admin_url('admin.php?page=bookingpress&module=settings&bookingpress_action=download_log&file=' . $debug_log_file_name);
        }

        return new \WP_REST_Response($response, 200);
    }

    function bpa_clear_debug_payment_log_func($request){
        global $wpdb, $tbl_bookingpress_debug_payment_log;
        $params = $request->get_json_params();
        $response            = array();
        $response['variant'] = 'error';
        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

        $bookingpress_view_log_selector = !empty($params['bookingpress_debug_log_selector']) ? sanitize_text_field($params['bookingpress_debug_log_selector']) : '';

        if (!empty($bookingpress_view_log_selector)) {
            $wpdb->delete(
                $tbl_bookingpress_debug_payment_log,
                array('bookingpress_payment_log_gateway' => $bookingpress_view_log_selector),
                array('%s')
            );
            $response['variant'] = 'success';
            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Debug Logs Cleared Successfully.', 'bookingpress-appointment-booking');
        }

        do_action('bookingpress_delete_debug_log_from_outside', $params);

        return new \WP_REST_Response($response, 200);
    }

    function bpa_save_default_work_hours_func($request){
        $params = $request->get_json_params();
        $_REQUEST['_wpnonce'] = wp_create_nonce('bpa_wp_nonce');
        $_REQUEST['workhours_timings'] = !empty($params['workhours_timings']) ? $params['workhours_timings']: [];
        $_REQUEST['break_data'] = !empty($params['break_data']) ? $params['break_data']: [];
        global $bookingpress_settings;
        $response = $bookingpress_settings->bookingpress_save_default_work_hours(true);
        return new \WP_REST_Response($response, 200);
    }

}
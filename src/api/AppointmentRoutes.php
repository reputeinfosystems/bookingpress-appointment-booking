<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

use BookingPress\data\ServicesProviders;
use BookingPress\api\CalendarRoutes;

class AppointmentRoutes extends Base {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {

        register_rest_route( 'bookingpress-app/v1', '/appointments', [
            'methods'  => 'POST',
            'callback' => [ $this, 'load_appointments' ],
            'permission_callback' => $this->permission_callback_for( 'retrieve_appointments' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/appointment/create', [
            'methods'  => 'POST',
            'callback' => [ $this, 'create_appointment' ],
            'permission_callback' => $this->permission_callback_for('add_calendar_appointments')
        ] );
        register_rest_route( 'bookingpress-app/v1', '/appointment/update-status', [
            'methods'  => 'POST',
            'callback' => [ $this, 'update_appointment_status' ],
            'permission_callback' => $this->permission_callback_for('update_upcoming_appointments')
        ] );
        register_rest_route( 'bookingpress-app/v1', '/appointment/fetch', [
            'methods'  => 'POST',
            'callback' => [ $this, 'fetch_appointment_data' ],
            'permission_callback' => $this->permission_callback_for('retrieve_calendar_appointments')
        ] );
        register_rest_route( 'bookingpress-app/v1', '/appointment/reschedule', [
            'methods'  => 'POST',
            'callback' => [ $this, 'reschedule_appointment' ],
            'permission_callback' => $this->permission_callback_for( 'update_upcoming_appointments' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/share-url-page-list', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_share_url_page_list' ],
            'permission_callback' => $this->permission_callback_for( 'retrieve_wp_page_list' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/generate-share-url', [
            'methods' => 'POST',
            'callback' => [ $this, 'generate_share_url' ],
            'permission_callback' => $this->permission_callback_for( 'get_share_url_generated' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/share-generated-appointment-url', [
            'methods' => 'POST', 
            'callback' => [ $this, 'share_generated_appointment_url' ],
            'permission_callback' => $this->permission_callback_for( 'share_generated_url' )
        ]);

        register_rest_route( 'bookingpress-app/v1', '/appointment/bulk-delete',[
            'methods' => 'POST',
            'callback' => [ $this, 'bulk_delete_appointments' ],
            'permission_callback' => $this->permission_callback_for( 'delete_appointments' )
        ]);

    }

    public function bulk_delete_appointments( $request ){

        global $wpdb, $BookingPress, $bookingpress_appointment;

        $response['variant'] = 'error';
        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

        $app_delete_ids = $request->get_param( 'appointment_ids' );

        $_REQUEST['_wpnonce'] = $request->get_param( '_wpnonce' );
        
        $delete_ids = ! empty($app_delete_ids) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $app_delete_ids) : array(); // phpcs:ignore

        $total_delete_ids = count( $delete_ids );
        $total_deleted = 0;
        
        if( !empty( $delete_ids ) ){
            foreach( $delete_ids as $delete_id ){
                $resp = $bookingpress_appointment->bookingpress_delete_appointment( $delete_id );
                if( $resp ){
                    $total_deleted++;
                }
            }
        }

        if( $total_deleted > 0 && $total_deleted == $total_delete_ids ){
            $response['variant'] = 'success';
            $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg'] = esc_html__('Appointments deleted successfully', 'bookingpress-appointment-booking');
        } elseif( $total_deleted > 0 && $total_deleted < $total_delete_ids ){
            $response['variant'] = 'warning';
            $response['title'] = esc_html__('Warning', 'bookingpress-appointment-booking');
            $response['msg'] = sprintf(esc_html__('Only %d out of %d appointments were deleted successfully', 'bookingpress-appointment-booking'), $total_deleted, $total_delete_ids);
        } else {
            $response['variant'] = 'error';
            $response['title'] = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg'] = esc_html__('No appointment was deleted', 'bookingpress-appointment-booking');
        }

        return new \WP_REST_Response([
            'success' => $response['variant'] === 'success' ? true : false,
            'data'  => $response
        ], 200);
    }

    public function share_generated_appointment_url( $request ){
        
        global $BookingPress, $bookingpress_email_notifications;
        $response['variant'] = 'success';
        $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
        $response['msg'] = esc_html__('Notification sent successfully', 'bookingpress-appointment-booking');

        $bpa_share_url_form_data = !empty($request->get_param('share_url_form_data')) ? $request->get_param('share_url_form_data') : array(); // phpcs:ignore
        if(!empty($bpa_share_url_form_data)){
            // Populate $_POST for template filters expecting it
            // bookingpress_modify_allowed_email_notification_flag_func() could not detect that a Share URL notification was being generated
            $_POST['share_url_form_data'] = $bpa_share_url_form_data;

            $is_email_sharing = !empty($bpa_share_url_form_data['email_sharing']) ? $bpa_share_url_form_data['email_sharing'] : false;
            if($is_email_sharing === true || $is_email_sharing == 'true' || $is_email_sharing == '1' || $is_email_sharing === 1){
                $bpa_share_email_addresses = !empty($bpa_share_url_form_data['sharing_email']) ? $bpa_share_url_form_data['sharing_email'] : '';
                if(!empty($bpa_share_email_addresses)){
                    $bpa_share_email_addresses = explode(',', $bpa_share_email_addresses);
                    foreach($bpa_share_email_addresses as $share_email_key => $share_email_val){
                        $bookingpress_cc_emails = array();
                        $bookingpress_cc_emails = apply_filters('bookingpress_add_customer_cc_email_address', $bookingpress_cc_emails, 'Share Appointment URL');
                        $bookingpress_email_notifications->bookingpress_send_email_notification('customer', 'Share Appointment URL', 0, $share_email_val, $bookingpress_cc_emails); 
                    }
                    $bookingpress_cc_emails = array();
                    $bookingpress_admin_emails = esc_html($BookingPress->bookingpress_get_settings('admin_email', 'notification_setting'));
                    $bookingpress_admin_emails = apply_filters('bookingpress_filter_admin_email_data', $bookingpress_admin_emails, 0, 'Share Appointment URL');
                    if (! empty($bookingpress_admin_emails) ) {
                        $bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, 'Share Appointment URL');
                        $bookingpress_admin_emails = explode(',', $bookingpress_admin_emails);
                        foreach ( $bookingpress_admin_emails as $admin_email_key => $admin_email_val ) {
                            $bookingpress_email_notifications->bookingpress_send_email_notification('employee', 'Share Appointment URL', 0, $admin_email_val, $bookingpress_cc_emails);
                        }
                    }
                }
            }

            do_action('bpa_externally_share_appointment_url', $bpa_share_url_form_data);
        }

        return new \WP_REST_Response([
            'success' => $response['variant'] === 'success' ? true : false,
            'data'  => $response
        ], 200);
    }

    public function generate_share_url( $request ){
        $response['variant'] = 'success';
        $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
        $response['msg'] = esc_html__('Share URL generated successfully', 'bookingpress-appointment-booking');
        $response['generated_url'] = array();

        $bpa_share_url_form_data = !empty($request->get_param('share_url_form_data')) ? $request->get_param('share_url_form_data') : array(); // phpcs:ignore
        if(!empty($bpa_share_url_form_data)){
            $bpa_final_generated_url = !empty($bpa_share_url_form_data['generated_url']) ? $bpa_share_url_form_data['generated_url'] : '';
            
            $bpa_selected_page_id = !empty($bpa_share_url_form_data['selected_page_id']) ? intval($bpa_share_url_form_data['selected_page_id']) : 0;
            if(!empty($bpa_selected_page_id)){
                $bpa_final_generated_url = get_permalink($bpa_selected_page_id);
            }
            $bpa_selected_page_wp_id = !empty($bpa_share_url_form_data['selected_page_wp_id']) ? intval($bpa_share_url_form_data['selected_page_wp_id']) : 0;
            if(!empty($bpa_selected_page_wp_id)){
                $bpa_final_generated_url = get_permalink($bpa_selected_page_wp_id);
            }

            $bpa_selected_service_id = !empty($bpa_share_url_form_data['selected_service_id']) ? intval($bpa_share_url_form_data['selected_service_id']) : 0;
            if(!empty($bpa_selected_service_id)){
                $bpa_final_generated_url = add_query_arg('s_id', $bpa_selected_service_id, $bpa_final_generated_url);
            }
            
            $bpa_final_generated_url = apply_filters('bookingpress_filter_generated_share_url_externally', $bpa_final_generated_url, $bpa_share_url_form_data);

            $bpa_allow_modify = (!empty($bpa_share_url_form_data['allow_customer_to_modify']) && ($bpa_share_url_form_data['allow_customer_to_modify'] == "true")) ? true : false;
            if($bpa_allow_modify){
                $bpa_final_generated_url = add_query_arg('allow_modify', 1, $bpa_final_generated_url);
            }else{
                $bpa_final_generated_url = add_query_arg('allow_modify', 0, $bpa_final_generated_url);
            }

            $response['generated_url'] = $bpa_final_generated_url;
        }

        return new \WP_REST_Response([
            'success' => $response['variant'] === 'success' ? true : false,
            'data'  => $response
        ], 200);
    }

    public function get_share_url_page_list( $request ){
        $response['variant'] = 'error';
        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
        $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

        $search_user_str = ! empty( $request->get_param('search_page_str') ) ? sanitize_text_field( $request->get_param('search_page_str') ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

        if(!empty($search_user_str)){
            $args = array(
                'post_type'      => 'page',
                'post_status'    => 'publish',
                's'    		 =>  $search_user_str,
                'search_columns '  => 'post_title',
                'order'          => 'ASC'
            );
            $pages = get_posts( $args );
            $bpa_new_wp_pages= array();
            foreach($pages as $bpa_wp_page_key => $bpa_wp_page_val){
                $bpa_new_wp_pages[] = array(
                    'id' => $bpa_wp_page_val->ID,
                    'title' => $bpa_wp_page_val->post_title,
                    'url' => get_permalink(get_page_by_path($bpa_wp_page_val->post_name)),
                );
            }
            if(!empty($bpa_new_wp_pages)) {                  
                $response['variant'] = 'success';
                $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');
                $response['all_page_list'] = $bpa_new_wp_pages;
            }   
        }

        return new \WP_REST_Response([
            'success' => $response['variant'] === 'success' ? true : false,
            'data'  => $response
        ], 200);
    }

    public function load_appointments( $request ){

        global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options,$tbl_bookingpress_form_fields, $bookingpress_appointment;

        $nonce_param = $request->get_param('appt_nonce');

        if( empty( $nonce_param ) || !wp_verify_nonce( $nonce_param, 'bpa_wp_nonce' ) ){
            return new \WP_REST_Response(
                [
                    'variant' => 'error',
                    'success' => false,
                    'message' => esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking')
                ],
                400
            );
        }
        
        $sort_by     = !empty($request->get_param('sort_by')) ? esc_html($request->get_param('sort_by')) : '';
        $sort_order  = !empty($request->get_param('sort_order'))? esc_html($request->get_param('sort_order')) : 'DESC';

        $perpage     = !empty($request->get_param('perpage')) ? intval($request->get_param('perpage')) : 10;
        $currentpage = !empty($request->get_param('currentpage')) ? intval($request->get_param('currentpage')) : 1;
        $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
        $bookingpress_search_data        = ! empty($request->get_param('search_data')) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $request->get_param('search_data')) : array();
        $bookingpress_search_query       = '';

        if ( ! in_array( $sort_order, array( 'ASC', 'DESC' ), true ) ) {
            $sort_order = 'DESC';
        }


        $bookingpress_appointment_sortable_columns = array(
            'created_date' => 'bpa.bookingpress_created_at',
            'staff_member_name' => 'bpa.bookingpress_staff_first_name',
            'service_name' => 'bpa.bookingpress_service_name',
            'customer_name'  => 'bpa.bookingpress_customer_firstname',
            'appointment_date' => 'bpa.bookingpress_appointment_date',
            'appointment_duration'  => 'CASE 
                                        WHEN bpa.bookingpress_service_duration_unit = "d" THEN bpa.bookingpress_service_duration_val * 1440
                                        WHEN bpa.bookingpress_service_duration_unit = "h" THEN bpa.bookingpress_service_duration_val * 60
                                        WHEN bpa.bookingpress_service_duration_unit = "m" THEN bpa.bookingpress_service_duration_val
                                        ELSE bpa.bookingpress_service_duration_val
                                    END'
        );
        
        $sort_extra_columns = '';
        $sort_extra_stmt = '';

        /*if( $BookingPress->bpa_is_pro_active() && version_compare( $wpdb->db_version(), '8.0.0', '>=') ){
            global $bookingpress_service_extra;
            if( !empty( $bookingpress_service_extra ) && method_exists( $bookingpress_service_extra, 'bookingpress_check_service_extra_module_activation' ) && $bookingpress_service_extra->bookingpress_check_service_extra_module_activation() ){
                $bookingpress_appointment_sortable_columns['appointment_duration'] = 'total_duration';
                $sort_extra_stmt = 'WITH RECURSIVE seq AS (
                    SELECT 0 AS n
                    UNION ALL SELECT n + 1 FROM seq WHERE n < 20
                )';
                $sort_extra_columns = ",(
                    CASE
                        WHEN bpa.bookingpress_service_duration_unit = 'd'
                            THEN bpa.bookingpress_service_duration_val * 1440
                        WHEN bpa.bookingpress_service_duration_unit = 'h'
                            THEN bpa.bookingpress_service_duration_val * 60
                        ELSE bpa.bookingpress_service_duration_val
                    END
                    +
                    IFNULL((
                        SELECT SUM(
                            CASE
                                WHEN JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_extra_service_details.bookingpress_extra_service_duration_unit'))) = 'h'
                                    THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_extra_service_details.bookingpress_extra_service_duration'))) AS UNSIGNED) * 
                                        CAST(JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_selected_qty'))) AS UNSIGNED) * 60

                                WHEN JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_extra_service_details.bookingpress_extra_service_duration_unit'))) = 'm'
                                    THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_extra_service_details.bookingpress_extra_service_duration'))) AS UNSIGNED) * 
                                        CAST(JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_selected_qty'))) AS UNSIGNED)

                                ELSE 0
                            END
                        )
                        FROM seq
                        WHERE JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, ']')) IS NOT NULL
                    ), 0)
                ) AS total_duration";
            }
        }*/

        $order_by_column = 'bpa.bookingpress_appointment_booking_id';

        if ( isset( $bookingpress_appointment_sortable_columns[ $sort_by ] ) ) {
            $order_by_column = $bookingpress_appointment_sortable_columns[ $sort_by ];
        }

        $bookingpress_search_query_where = 'WHERE 1=1 ';

        if (! empty($bookingpress_search_data) ) {
            if (! empty($bookingpress_search_data['search_appointment']) ) {
                $bookingpress_search_string = $bookingpress_search_data['search_appointment'];
                $bookingpress_search_result = $wpdb->get_results($wpdb->prepare('SELECT bookingpress_customer_id  FROM ' . $tbl_bookingpress_customers . " WHERE bookingpress_customer_full_name LIKE %s OR bookingpress_user_firstname LIKE %s OR bookingpress_user_lastname LIKE %s OR bookingpress_user_login LIKE %s AND (bookingpress_user_type = 1 OR bookingpress_user_type = 2)", '%' . $bookingpress_search_string . '%', '%' . $bookingpress_search_string . '%', '%' . $bookingpress_search_string . '%' , '%' . $bookingpress_search_string . '%'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                if (! empty($bookingpress_search_result) ) {
                    $bookingpress_customer_ids = array();
                    foreach ( $bookingpress_search_result as $item ) {
                        $bookingpress_customer_ids[] = $item['bookingpress_customer_id'];
                    }
                    $bookingpress_search_user_id      = implode(',', $bookingpress_customer_ids);
                    $search_query_where = ' AND (bpa.bookingpress_customer_id IN (';
                    $search_query_where .= rtrim( str_repeat( '%d,', count( $bookingpress_customer_ids ) ), ',' ). ') )';
                    array_unshift( $bookingpress_customer_ids, $search_query_where );
                    $search_query_where_str = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_customer_ids  );

                    $bookingpress_search_query_where .= $search_query_where_str;
                } else {
                    $bookingpress_search_query_where_clause = apply_filters( 'bookingpress_modify_search_query_where_after_service_name', '', $bookingpress_search_string );
                    $bookingpress_search_query_where .= $wpdb->prepare( "AND ( bpa.bookingpress_service_name LIKE %s {$bookingpress_search_query_where_clause} )", "%{$bookingpress_search_string}%" );
                }

                /** Check the search term with customer details stored in the appointment table to give more accurate result in searching
                 * When a logged in customer booked an appointment with different First & Last name
                 * in that case, searching with that first & last name did not show the appointment in the search result because those details were not stored in the customers table but were stored in the appointment table.
                 * So we added a search with those details in the appointment table to make sure the search results are accurate and also to cover such cases.
                 */

                $bookingpress_search_query_where .= $wpdb->prepare( "AND ( bpa.bookingpress_service_name LIKE %s OR bpa.bookingpress_customer_firstname LIKE %s OR bpa.bookingpress_customer_lastname LIKE %s OR bpa.bookingpress_customer_name LIKE %s OR bpa.bookingpress_username LIKE %s OR bpa.bookingpress_customer_email LIKE %s )","%{$bookingpress_search_string}%","%{$bookingpress_search_string}%","%{$bookingpress_search_string}%","%{$bookingpress_search_string}%","%{$bookingpress_search_string}%","%{$bookingpress_search_string}%");
            }
            if (! empty($bookingpress_search_data['selected_date_range']) ) {
                $bookingpress_search_date         = $bookingpress_search_data['selected_date_range'];
                $start_date                       = date('Y-m-d', strtotime($bookingpress_search_date[0]));
                $end_date                         = date('Y-m-d', strtotime($bookingpress_search_date[1]));
                $bookingpress_search_query_where .= $wpdb->prepare( " AND (bpa.bookingpress_appointment_date BETWEEN %s AND %s)", $start_date, $end_date );
            }
            if (! empty($bookingpress_search_data['customer_name']) ) {
                $bookingpress_search_name         = $bookingpress_search_data['customer_name'];

                $search_name_query = ' AND ( bpa.bookingpress_customer_id IN(';
                $search_name_query .= rtrim( str_repeat( '%d,', count( $bookingpress_search_name) ), ',' ).' ) )';
                array_unshift( $bookingpress_search_name, $search_name_query );
                $search_name_query_str = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_search_name );
                
                $bookingpress_search_query_where .= $search_name_query_str;
            }
            if (! empty($bookingpress_search_data['service_name']) ) {
                $bookingpress_search_name         = $bookingpress_search_data['service_name'];

                $search_name_query = ' AND ( bpa.bookingpress_service_id IN(';
                $search_name_query .= rtrim( str_repeat( '%d,', count( $bookingpress_search_name) ), ',' ).' ) )';
                array_unshift( $bookingpress_search_name, $search_name_query );
                $search_name_query_str = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_search_name );
                $search_name_query_str = apply_filters( 'bookingpress_modify_search_query_where_after_service_id', $search_name_query_str, $bookingpress_search_data['service_name'] );
                $bookingpress_search_query_where .= $search_name_query_str;

            }
            if (! empty($bookingpress_search_data['appointment_status'] && $bookingpress_search_data['appointment_status'] != 'all') ) {
                $bookingpress_search_name         = $bookingpress_search_data['appointment_status'];
                $bookingpress_search_query_where .= $wpdb->prepare( " AND (bpa.bookingpress_appointment_status = %s)", $bookingpress_search_name );
            }
            if(!empty( $bookingpress_search_data['search_appointment_id'])) {
                $bookingpress_search_id = $bookingpress_search_data['search_appointment_id'];
                $bookingpress_search_query_where .= $wpdb->prepare( " AND (bpa.bookingpress_booking_id = %d)", $bookingpress_search_id );
                
            }
            $bookingpress_search_query_where = apply_filters('bookingpress_appointment_view_add_filter', $bookingpress_search_query_where, $bookingpress_search_data);
        }
        $bpa_left_join_data = apply_filters( 'bookingpress_modify_left_join_table_with_appointment_page', '' );

        $get_total_appointments = $wpdb->get_results("SELECT bpa.* FROM {$tbl_bookingpress_appointment_bookings} bpa {$bpa_left_join_data} {$bookingpress_search_query}{$bookingpress_search_query_where} group by bpa.bookingpress_appointment_booking_id", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
        
        $total_appointments = $wpdb->get_results("$sort_extra_stmt SELECT bpa.* $sort_extra_columns FROM {$tbl_bookingpress_appointment_bookings} bpa {$bpa_left_join_data} {$bookingpress_search_query}{$bookingpress_search_query_where} group by bpa.bookingpress_appointment_booking_id order by {$order_by_column} {$sort_order} LIMIT {$offset} , {$perpage}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
        
        
        $appointments  = $bookingpress_formdata = array();

        if (! empty($total_appointments) ) {
            $counter = 1;

            $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
            $bookingpress_default_time_format = $bookingpress_global_options_arr['wp_default_time_format'];
            $bookingpress_default_date_time_format = $bookingpress_default_date_format . ' ' . $bookingpress_default_time_format;
            $bookingpress_appointment_status_arr = $bookingpress_global_options_arr['appointment_status'];
            
            $bookingpress_form_field_data = $wpdb->get_results("SELECT `bookingpress_form_field_name`,`bookingpress_field_label` FROM {$tbl_bookingpress_form_fields}",ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm

            foreach($bookingpress_form_field_data as $key=> $value) {                    
                $bookingpress_formdata[$value['bookingpress_form_field_name']] = stripslashes_deep($value['bookingpress_field_label']);
            }                

            foreach ( $total_appointments as $get_appointment ) {
                $appointment                   = array();
                $appointment['id']             = $counter;
                $appointment_id                = intval($get_appointment['bookingpress_appointment_booking_id']);
                $appointment['appointment_id'] = $appointment_id;
                $appointment['payment_id'] = $get_appointment['bookingpress_payment_id'];
                $payment_log                   = $wpdb->get_row($wpdb->prepare('SELECT bookingpress_invoice_id, bookingpress_customer_firstname,bookingpress_customer_lastname,bookingpress_customer_email, bookingpress_payment_gateway FROM ' . $tbl_bookingpress_payment_logs . ' WHERE bookingpress_appointment_booking_ref = %d', $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

                $appointment_date_time           = $get_appointment['bookingpress_appointment_date'] . ' ' . $get_appointment['bookingpress_appointment_time'];
                $appointment['created_date']     = date_i18n($bookingpress_default_date_time_format, strtotime($get_appointment['bookingpress_created_at']));
                $appointment['bookingpress_appointment_created_date'] = $get_appointment['bookingpress_created_at'];
                $appointment['appointment_date'] = date_i18n($bookingpress_default_date_time_format, strtotime($appointment_date_time));

                $appointment['booking_id'] = !empty($get_appointment['bookingpress_booking_id']) ? $get_appointment['bookingpress_booking_id'] : 1;
                $customer_email = ! empty($get_appointment['bookingpress_customer_email']) ? $get_appointment['bookingpress_customer_email'] : '';
                $customer_phone = ! empty($get_appointment['bookingpress_customer_phone']) ? $get_appointment['bookingpress_customer_phone'] : '';

                $bookingpress_customer_phone_dial_code = (isset($get_appointment['bookingpress_customer_phone_dial_code']) && !empty($get_appointment['bookingpress_customer_phone_dial_code'])) ? $get_appointment['bookingpress_customer_phone_dial_code'] : '';

                $appointment['customer_first_name'] = !empty($get_appointment['bookingpress_customer_firstname']) ? stripslashes_deep($get_appointment['bookingpress_customer_firstname']) :'';
                $appointment['customer_last_name'] = !empty($get_appointment['bookingpress_customer_lastname']) ? stripslashes_deep($get_appointment['bookingpress_customer_lastname']) :'';
                $customer_username = ! empty($get_appointment['bookingpress_username']) ? $get_appointment['bookingpress_username'] : '';
                if( !empty($customer_username ) ){
                    $appointment['customer_name'] = (isset($appointment['customer_name']) && !empty($appointment['customer_name']) && !empty(trim($appointment['customer_name']))) ? ($appointment['customer_name']) : stripslashes_deep($customer_username);
                } else{
                $appointment['customer_name'] = !empty($get_appointment['bookingpress_customer_name']) ? stripslashes_deep($get_appointment['bookingpress_customer_name']) : $appointment['customer_first_name'].' '.$appointment['customer_last_name'];
                $appointment['customer_name'] = !empty(trim($appointment['customer_name'])) ? ($appointment['customer_name']) : stripslashes_deep($customer_email);
                }
                $appointment['customer_email'] = stripslashes_deep($customer_email);
                $appointment['customer_phone'] = stripslashes_deep($customer_phone);

                if(!empty($customer_phone) && !empty($bookingpress_customer_phone_dial_code)){
                    $appointment['customer_phone'] = '+'.$bookingpress_customer_phone_dial_code.' '.stripslashes_deep($customer_phone);
                }

                $appointment['service_name']  = stripslashes_deep($get_appointment['bookingpress_service_name']);
                $appointment['service_id']  = intval($get_appointment['bookingpress_service_id']);
                
                $appointment['appointment_note']  = stripslashes_deep($get_appointment['bookingpress_appointment_internal_note']);                    

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

                $appointment['bookingpress_service_duration_sortable'] = (int)$bookingpress_appointment->bookingpress_get_appointment_duration_sorting($bookingpress_appointment_start_datetime, $bookingpress_appointment_end_datetime, $bookingpress_sortable_duration_val);

                $appointment['appointment_duration'] = $service_duration;
                
                $currency_name                       = $get_appointment['bookingpress_service_currency'];
                $currency_symbol                     = $BookingPress->bookingpress_get_currency_symbol($currency_name);

                if ($get_appointment['bookingpress_service_price'] == '0' ) {
                    $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0, $currency_symbol);
                    $payment_amount_without_currency = 0;
                } else {
                    $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($get_appointment['bookingpress_paid_amount'], $currency_symbol);
                    $payment_amount_without_currency = floatval($get_appointment['bookingpress_paid_amount']);
                }

                $appointment['appointment_payment'] = $payment_amount;

                $appointment['payment_numberic_amount'] = $payment_amount_without_currency;

                $bookingpress_appointment_status = esc_html($get_appointment['bookingpress_appointment_status']);
                $bookingpress_appointment_status_label = $bookingpress_appointment_status;
                foreach($bookingpress_appointment_status_arr as $status_key => $status_val){
                    if($bookingpress_appointment_status == $status_val['value']){
                        $bookingpress_appointment_status_label = $status_val['text'];
                        break;
                    }    
                }
                
                $appointment['appointment_status']  = $bookingpress_appointment_status;
                $appointment['appointment_status_label'] = $bookingpress_appointment_status_label;

                $bookingpress_view_appointment_date = date_i18n($bookingpress_default_date_format, strtotime($get_appointment['bookingpress_appointment_date']));
                $bookingpress_view_appointment_time = date($bookingpress_default_time_format, strtotime($get_appointment['bookingpress_appointment_time']))." ".esc_html__('To', 'bookingpress-appointment-booking')." ".date($bookingpress_default_time_format, strtotime($get_appointment['bookingpress_appointment_end_time']));

                $appointment['view_appointment_date'] = $bookingpress_view_appointment_date;
                $appointment['sort_appointment_date_time'] = strtotime( date('Y-m-d',strtotime($get_appointment['bookingpress_appointment_date']) ).' '.$get_appointment['bookingpress_appointment_time'] );
                $appointment['view_appointment_time'] = $bookingpress_view_appointment_time;
                $bookingpress_payment_method = ( !empty( $payment_log) && $payment_log['bookingpress_payment_gateway']  == 'on-site' ) ? 'On Site': (!empty($payment_log['bookingpress_payment_gateway']) ? $payment_log['bookingpress_payment_gateway'] : '' ); 
                $appointment['payment_method'] = $bookingpress_payment_method;
                $appointment = apply_filters('bookingpress_appointment_add_view_field', $appointment, $get_appointment);

                $bookingpress_booking_start_timestamp = strtotime( $get_appointment['bookingpress_appointment_date'] . ' ' . $get_appointment['bookingpress_appointment_time'] );
                $appointment['is_past_appointment'] = current_time('timestamp') > $bookingpress_booking_start_timestamp;
                $appointment['change_status_loader'] = '0';

                $appointments[] = $appointment;
                $counter++;
            }
        }
        
        $appointments = apply_filters('bookingpress_modify_appointment_data', $appointments);


        $data['items']       = $appointments;
        $data['form_field_data'] = $bookingpress_formdata;
        $data['items']       = $appointments;

        $data ['totalItems'] = count($get_total_appointments);

        return new \WP_REST_Response(
            [
                'variant' => 'success',
                'success' => true,
                'data' => $data,
            ]
        );
    }

    public function reschedule_appointment( $request ) {
        //$reschedule_data = $request->get_param( 'reschedule_data' );

        global $wpdb, $tbl_bookingpress_appointment_bookings;

        $appointment_id = $request->get_param('appointment_update_id');

        $appointment_log_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        $service_id = $request->get_param( 'appointment_selected_service' );

        $bookingpress_booking_timestamp = strtotime( $appointment_log_data['bookingpress_appointment_date'] . ' ' . $appointment_log_data['bookingpress_appointment_time'] );
        $is_past_time = current_time('timestamp') > $bookingpress_booking_timestamp;

        /** Block if appointment is in past */

        if( 1 == $is_past_time ) {
            return new \WP_REST_Response(
                [
                    'variant' => 'error',
                    'success' => false,
                    'message' => esc_html__( 'Sorry, past appontment can not be rescheduled', 'bookingpress-appointment-booking')
                ],
                400
            );
        }
        /** Block if appointment is in past */

        $appointment_selected_date  = $request->get_param( 'appointment_booked_date' );
        $appointment_start_time     = $request->get_param( 'appointment_booked_time' );
        $appointment_end_time       = $request->get_param( 'appointment_booked_end_time' );
        if( '24:00' != $appointment_end_time ){
            $appointment_end_time = date( 'H:i:s', strtotime( $appointment_end_time ) );
        } else if( '24:00' == $appointment_end_time ){
            $appointment_end_time = '24:00:00';
        }

        $appointment_selected_date_time = strtotime( $appointment_selected_date . ' ' . $appointment_start_time );

        if( current_time( 'timestamp' ) > $appointment_selected_date_time ){
            return new \WP_REST_Response(
                [
                    'variant' => 'error',
                    'success' => false,
                    'message' => esc_html__( 'Sorry, Appointment can not be rescheduled as the selected time has been passed.', 'bookingpress-appointment-booking')
                ],
                400
            );
        }

        $updated_data = [
            'bookingpress_appointment_date' => $appointment_selected_date,
            'bookingpress_appointment_end_date' => $appointment_selected_date,
            'bookingpress_appointment_time' => $appointment_start_time,
            'bookingpress_appointment_end_time' => $appointment_end_time,
        ];

        $wpdb->update( $tbl_bookingpress_appointment_bookings, $updated_data, array( 'bookingpress_appointment_booking_id' => $appointment_id ) );

        $appointment_data = CalendarRoutes::get_single_appointment( $appointment_id );
        
        return new \WP_REST_Response(
            [
                'variant' => 'success',
                'success' => true,
                'appointment_details' => $appointment_data,
                'message' => esc_html__( 'Appointment rescheduled successfully', 'bookingpress-appointment-booking')
            ]
        );

        //return $this->create_appointment( $request );
    }

    public function fetch_appointment_data( $request ) {
        $appointment_id = $request->get_param( 'appointment_id' );

        global $bookingpress_calendar;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore
        $_POST['appointment_id'] = $appointment_id; //phpcs:ignore

        $response = $bookingpress_calendar->bookingpress_get_edit_appointment_data_func( true );

        if( !empty( $response['variant'] ) && $response['variant'] != '1' ) {
            return new \WP_REST_Response( [
                'success' => false,
            ], 400 );
        } else {
            $result = [
                'success' => true,
                'data' => $response,
            ];
            return new \WP_REST_Response( $result, 200 );
        }
    }

    public function update_appointment_status( $request ) {
        $update_appointment_id = $request->get_param( 'appointment_id' );
        $new_status = $request->get_param( 'new_status' );

        global $wpdb, $bookingpress_dashboard;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore
        $_REQUEST['update_appointment_id'] = $update_appointment_id; //phpcs:ignore

        $response = $bookingpress_dashboard->bookingpress_change_upcoming_appointment_status( $update_appointment_id, $new_status, true );

        if( !empty( $response['variant'] ) && $response['variant'] != '1' ) {
            return new \WP_REST_Response( [
                'success' => false,
            ], 400 );
        } else {
            $result = [
                'success' => true,
            ];
            return new \WP_REST_Response( $result, 200 );
        }
    }

    public function create_appointment( $request ) {
        
        $appointment_data = $request->get_param( 'appointment_data' );

        global $bookingpress_calendar, $wpdb, $tbl_bookingpress_appointment_bookings, $BookingPress, $bookingpress_global_options, $tbl_bookingpress_form_fields;

        $bookingpress_global_options_arr        = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_default_date_format       = $bookingpress_global_options_arr['wp_default_date_format'];
        $bookingpress_default_time_format       = $bookingpress_global_options_arr['wp_default_time_format'];
        $bookingpress_default_date_time_format  = $bookingpress_default_date_format . ' ' . $bookingpress_default_time_format;

        //$_POST['appointment_data'] = wp_json_encode( $appointment_data ); //phpcs:ignore
        $_POST['appointment_data'] = wp_slash( wp_json_encode( $appointment_data ) ); //phpcs:ignore        
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore

        $response = $bookingpress_calendar->bookingpress_save_appointment_booking_func( false, true );

        $where_clause = '';
        if( !empty( $appointment_data['appointment_update_id'] ) ){
            $where_clause = $wpdb->prepare( " AND bookingpress_appointment_booking_id = %d", $appointment_data['appointment_update_id'] );
        } else if( !empty( $response['payment_log_id'] ) ){
            $where_clause = $wpdb->prepare( " AND bookingpress_payment_id = %d", $response['payment_log_id'] );
        }

        $appointment_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE (bookingpress_appointment_status = %d or bookingpress_appointment_status = %d) $where_clause", 1, 2 ), ARRAY_A ); // phpcs:ignore
        $all_appointment_details = [];

        $default_form_fields = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT bookingpress_form_field_id, bookingpress_field_label, bookingpress_form_field_name FROM {$tbl_bookingpress_form_fields} WHERE (bookingpress_form_field_name = %s OR bookingpress_form_field_name = %s) AND bookingpress_field_is_default = %d",
                'email_address',
                'phone_number',
                1
            )
        );

        $email_field_label = esc_html__( 'Email Address', 'bookingpress-appointment-booking' );
        $phone_field_label = esc_html__( 'Phone Number', 'bookingpress-appointment-booking' );

        $email_field_id = 4; // Default field id for email address
        $phone_field_id = 5; // Default field id for phone number

        foreach( $default_form_fields as $field_data ){
            if( $field_data->bookingpress_form_field_name == 'email_address' ){
                $email_field_id = $field_data->bookingpress_form_field_id;
                $email_field_label = $field_data->bookingpress_field_label;
            } else if( $field_data->bookingpress_form_field_name == 'phone_number' ){
                $phone_field_id = $field_data->bookingpress_form_field_id;
                $phone_field_label = $field_data->bookingpress_field_label;
            }
        }

        foreach( $appointment_details as $appointment_detail ){

            $bookingpress_customer_name = '';
            $bookingpress_cust_fnm = isset($appointment_detail['bookingpress_customer_firstname']) ? stripslashes_deep($appointment_detail['bookingpress_customer_firstname']) : '';
            $bookingpress_cust_lnm = isset($appointment_detail['bookingpress_customer_lastname']) ? stripslashes_deep($appointment_detail['bookingpress_customer_lastname']) : '';
            $bookingpress_cust_fullnm = isset($appointment_detail['bookingpress_customer_name']) ? stripslashes_deep($appointment_detail['bookingpress_customer_name']) : '';
            $bookingpress_cust_unm = isset($appointment_detail['bookingpress_username']) ? stripslashes_deep($appointment_detail['bookingpress_username']) : '';
            $bookingpress_cust_email = isset($appointment_detail['bookingpress_customer_email']) ? $appointment_detail['bookingpress_customer_email'] : '';
            $bookingpress_cust_phone = isset($appointment_detail['bookingpress_customer_phone'])  ? $appointment_detail['bookingpress_customer_phone'] : '';

            if(!empty($bookingpress_cust_fnm) || !empty($bookingpress_cust_lnm)) {
                $bookingpress_customer_name = !empty($bookingpress_cust_fnm) ? $bookingpress_cust_fnm : '';
                $bookingpress_customer_name .= !empty($bookingpress_customer_name) ? ' ' : '';
                $bookingpress_customer_name .= !empty($bookingpress_cust_lnm) ? $bookingpress_cust_lnm : '';
            } else if(!empty($bookingpress_cust_fullnm) && empty($bookingpress_customer_name)){
                $bookingpress_customer_name = $bookingpress_cust_fullnm;
            } else if(!empty($bookingpress_cust_unm) && empty($bookingpress_customer_name)){
                $bookingpress_customer_name = $bookingpress_cust_unm;
            } else if(!empty($bookingpress_cust_email) && empty($bookingpress_customer_name)){
                $bookingpress_customer_name = $bookingpress_cust_email;
            } else if(!empty($bookingpress_cust_phone) && empty($bookingpress_customer_name)){
                $bookingpress_customer_name = $bookingpress_cust_phone;
            }

            $service_color_scheme = ServicesProviders::get_service_color_scheme( $appointment_detail['bookingpress_service_id']);
            $color_scheme_data = ServicesProviders::get_color_scheme_data( $service_color_scheme );

            $booking_metadata = [
                'form_fields' => []
            ];

            if( !empty( $bookingpress_cust_email ) ){
                $booking_metadata['form_fields'][] = [
                    'id'        => $email_field_id,
                    'label'     => 'email',
                    'value'     => $bookingpress_cust_email
                ];
            }
            if( !empty( $bookingpress_cust_phone )){
                $booking_metadata['form_fields'][] = [
                    'id'        => $phone_field_id,
                    'label'     => 'phone',
                    'value'     => $bookingpress_cust_phone
                ];
            }
            // Fixed Issue to not shows that Date and time on reschedule model when add new appointment Issue: missing formatted_booking_date, formatted_booking_time, and customerId
            $formatted_date = date_i18n($bookingpress_default_date_format, strtotime($appointment_detail['bookingpress_appointment_date']));
            $formatted_time = date_i18n($bookingpress_default_time_format, strtotime($appointment_detail['bookingpress_appointment_time'])) . ' - ' . date_i18n($bookingpress_default_time_format, strtotime($appointment_detail['bookingpress_appointment_end_time']));

            $booking_metadata['formatted_booking_date'] = $formatted_date;
            $booking_metadata['formatted_booking_time'] = $formatted_time;

            $all_appointment_details[] = [
                'id'            => $appointment_detail['bookingpress_appointment_booking_id'],
                'customerName'  => $bookingpress_customer_name,
                'customerId'    => $appointment_detail['bookingpress_customer_id'],
                'start_date'    => $appointment_detail['bookingpress_appointment_date'],
                //'booking_date'  => date_i18n($bookingpress_default_date_format, strtotime($appointment_detail['bookingpress_appointment_date'])),
                //'booking_time'  => date('H:i', strtotime($appointment_detail['bookingpress_appointment_time'])) . ' - ' . date('H:i', strtotime($appointment_detail['bookingpress_appointment_end_time'])),
                'end_date'      => ( !empty( $appointment_detail['bookingpress_appointment_end_date'] ) && $appointment_detail['bookingpress_appointment_end_date'] != '0000-00-00' ) ? $appointment_detail['bookingpress_appointment_end_date'] : $appointment_detail['bookingpress_appointment_date'],
                'start_time'    => date('H:i', strtotime($appointment_detail['bookingpress_appointment_time'])),
                'end_time'      => date('H:i', strtotime($appointment_detail['bookingpress_appointment_end_time'])),
                'serviceName'   => stripslashes_deep($appointment_detail['bookingpress_service_name']),
                'serviceId'     => $appointment_detail['bookingpress_service_id'],
                'status'        => $appointment_detail['bookingpress_appointment_status'],
                'isPast'        => strtotime( $appointment_detail['bookingpress_appointment_date'] . ' ' . $appointment_detail['bookingpress_appointment_time'] ) < current_time('timestamp') ? true : false,
                'category'      => ServicesProviders::get_service_category_id( $appointment_detail['bookingpress_service_id'] ),
                'price'         => $BookingPress->bookingpress_price_formatter_with_currency_symbol( $appointment_detail['bookingpress_paid_amount'] ),
                'theme'         => $color_scheme_data,
                'metadata'      => $booking_metadata
            ];
        }
        $response['appointment_details'] = $all_appointment_details;

        if( empty( $all_appointment_details ) && !empty( $appointment_data['appointment_update_id']) ) {
            $response['required_action'] = 'update_status';
            $response['new_status'] = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_appointment_status FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", intval( $appointment_data['appointment_update_id'] ) ) );
            $response['appointment_id'] = intval( $appointment_data['appointment_update_id'] );
        }
    

        $result = [
            'success' => true,
            'data'  => $response,
        ];

        return new \WP_REST_Response( $result, 200 );
    }

    public static function bookingpress_ability_get_price_quote( $input = [] ){

        global $BookingPress;

        $service_id = $input['service_id'] ?? 0;

        if( empty( $service_id ) ){
            return new \WP_Error(
                'invalid_service_id',
                __( 'Invalid service ID.', 'bookingpress-appointment-booking' ),
                [ 'status' => 400 ]
            );
        }

        $repository = new \BookingPress\Vue3\Repositories\ServiceRepository();
        $service = $repository->find( $service_id );

        if( empty( $service ) ){
            return new \WP_Error(
                'service_not_found',
                __( 'Service not found.', 'bookingpress-appointment-booking' ),
                [ 'status' => 404 ]
            );
        }

        //error_log( print_r( $service, true ));

        $currency_code = ( new \BookingPress\Vue3\Repositories\SettingsRepository )->get( 'payment_default_currency', \BookingPress\Vue3\Repositories\SettingsRepository::GROUP_PAYMENT );

        $return_data = [
            'service_id' => $service_id,
            'service_name' => $service['serviceName'] ?? '',
            'price' => $service['servicePrice'] ?? 0,
            'price_with_currency' => ( new \BookingPress\Vue3\Services\PricingService )->format_price( $service['servicePrice'] ?? 0 ),
            'currency_code' => $currency_code,
            'currency_symbol' => $BookingPress->bookingpress_get_currency_symbol( $currency_code )
        ];

        return [ 'price_details' => $return_data ];

    }

    public static function bookingpress_ability_cancel_appointment( $input = [] ){

        global $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers, $bookingpress_dashboard;

        $appointment_id = $input['appointment_id'] ?? 0;
        if( empty( $appointment_id ) ){
            return new \WP_Error(
                'invalid_appointment_id',
                __( 'Invalid appointment ID.', 'bookingpress-appointment-booking' ),
                [ 'status' => 400 ]
            );
        }

        $appointment_scope = \BookingPress\abilities\BookingPressAbilities::bookingpress_appointment_scope();

        if( empty( $appointment_scope ) || ! is_user_logged_in() ) {
            return new \WP_Error(
                'invalid_scope',
                __( 'Invalid scope to get the appointment.', 'bookingpress-appointment-booking'),
                [ 'status' => 403 ]
            );
        }

        $where = $wpdb->prepare( " WHERE bookingpress_appointment_booking_id = %d", $appointment_id );

        $appointment = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} {$where}", ARRAY_A );

        if( empty( $appointment ) ){
            return new \WP_Error(
                'appointment_not_found',
                __( 'Appointment not found.', 'bookingpress-appointment-booking' ),
                [ 'status' => 404 ]
            );
        }

        if( 'customer' == $appointment_scope ) {

            $existing_customer = ( new \BookingPress\Vue3\Repositories\CustomerRepository )->find_by_wp_user_id( get_current_user_id() );

            if( empty( $existing_customer ) || $appointment['bookingpress_customer_id'] != $existing_customer['customerId'] ){
                return new \WP_Error(
                    'invalid_scope',
                    __( 'You do not have permission to cancel this appointment.', 'bookingpress-appointment-booking' ),
                    [ 'status' => 403 ]
                );
            }

            /** Check if canceling is allowed for this appointment */
            $cancel_allowed = ( new \BookingPress\Vue3\Repositories\CustomizeRepository )->get( 'allow_to_cancel_appointment', \BookingPress\Vue3\Repositories\CustomizeRepository::GROUP_BOOKING_MY_BOOKING );

            if( 'true' !== $cancel_allowed ){
                return new \WP_Error(
                    'cancel_not_allowed',
                    __( 'Appointment Cancellation is disabled for Customers.', 'bookingpress-appointment-booking' ),
                    [ 'status' => 403 ]
                );
            }
        }

        $appointment_status = $appointment['bookingpress_appointment_status'] ?? '';

        /** Appointment is neither pending nor confirmed. */
        if( !in_array( (int) $appointment_status, [1,2]) ){
            return new \WP_Error(
                'invalid_status',
                __( 'Appointment is neither pending nor confirmed so it cannot be canceled.', 'bookingpress-appointment-booking' ),
                [ 'status' => 403 ]
            );
        }

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore

        /** Customers do not have the 'update_upcoming_appointments' capability, so they use the My Bookings cancel flow which applies the cancellation time limit, refund and notification rules. */
        if( 'customer' === $appointment_scope ) {
            global $bookingpress_appointment_bookings;

            $_REQUEST['cancel_id'] = $appointment_id; //phpcs:ignore

            $response = $bookingpress_appointment_bookings->bookingpress_cancel_appointment( true );

            if( 'success' !== ( $response['variant'] ?? '' ) ){
                return new \WP_Error(
                    'cancel_failed',
                    ! empty( $response['msg'] ) ? $response['msg'] : __( 'The appointment could not be canceled.', 'bookingpress-appointment-booking' ),
                    [ 'status' => 400 ]
                );
            }
        } else {
            $_REQUEST['update_appointment_id'] = $appointment_id; //phpcs:ignore

            $response = $bookingpress_dashboard->bookingpress_change_upcoming_appointment_status( $appointment_id, 3, true );

            if( 1 !== $response ){
                return new \WP_Error(
                    'cancel_failed',
                    ( is_array( $response ) && ! empty( $response['msg'] ) ) ? $response['msg'] : __( 'The appointment could not be canceled.', 'bookingpress-appointment-booking' ),
                    [ 'status' => 400 ]
                );
            }
        }

         return [
            'success' => true,
            'message' => __( 'Appointment has been successfully canceled.', 'bookingpress-appointment-booking' )
        ];
    }

    public static function bookingpress_ability_create_appointment( $input = [] ) {

        $service_id     = ! empty( $input['service_id'] ) ? intval( $input['service_id'] ) : 0;
        $customer_id    = ! empty( $input['customer_id'] ) ? intval( $input['customer_id'] ) : 0;
        $customer_email = ! empty( $input['customer_email'] ) ? sanitize_email( $input['customer_email'] ) : '';
        $booking_date   = ! empty( $input['booking_date'] ) ? sanitize_text_field( $input['booking_date'] ) : '';
        $booking_time   = ! empty( $input['booking_time'] ) ? sanitize_text_field( $input['booking_time'] ) : '';
        $appointment_status = ! empty( $input['appointment_status'] ) ? intval( $input['appointment_status'] ) : 2; // pending

        if ( empty( $service_id ) || empty( $booking_date ) || empty( $booking_time ) ) {
            return new \WP_Error( 'invalid_input', __( 'Service ID, booking date, and booking time are required.', 'bookingpress-appointment-booking' ), [ 'status' => 400 ] );
        }
        if ( empty( $customer_id ) && empty( $customer_email ) ) {
            return new \WP_Error( 'invalid_input', __( 'Customer ID or customer email is required.', 'bookingpress-appointment-booking' ), [ 'status' => 400 ] );
        }

        // 1. Resolve the customer safely
        if ( empty( $customer_id ) ) {
            $customer = ( new \BookingPress\Vue3\Repositories\CustomerRepository )->find_by_email( $customer_email );
            if ( empty( $customer['customerId'] ) ) {
                return new \WP_Error( 'customer_not_found', __( 'No customer found with this email address.', 'bookingpress-appointment-booking' ), [ 'status' => 404 ] );
            }
            $customer_id = intval( $customer['customerId'] );
        }

        // 2. Pre-check: is the requested start time actually available?
        //    Replace with the method behind your get-available-time-slots ability.
        /** The full input is passed on so the time slot filters see the same booking details as the booking. */
        $slots = \BookingPress\api\TimeRoutes::bookingpress_ability_list_available_time_slots( array_merge( $input, [ 'service_id' => $service_id, 'date' => $booking_date ] ) );
        if ( is_wp_error( $slots ) ) {
            return new \WP_Error( 'slot_unavailable', $slots->get_error_message(), [ 'status' => 409 ] );
        }
        $start_times = wp_list_pluck( $slots['available_time_slots'] ?? [], 'start_time' );
        if ( ! in_array( $booking_time, $start_times, true ) ) {
            return new \WP_Error( 'slot_unavailable', __( 'This time slot is not available. Choose another slot.', 'bookingpress-appointment-booking' ), [ 'status' => 409 ] );
        }

        $appointment_data = [
            'appointment_selected_service'      => $service_id,
            'appointment_selected_customer'     => $customer_id,
            'customer_email'                    => $customer_email,
            'appointment_booked_date'           => $booking_date,
            'appointment_booked_time'           => $booking_time,
            'appointment_booked_end_time'       => self::get_selected_end_time( $service_id, $booking_time ),
            'appointment_custom_timing'         => 0,
            'appointment_status'                => $appointment_status,
        ];

        /**
         * Filter the appointment data saved by the 'bookingpress/create-appointment' ability.
         * Return a WP_Error to reject the booking.
         *
         * @since 1.6.9
         * @param array $appointment_data Appointment data in the format of the admin appointment form.
         * @param array $input            Ability input.
         */
        $appointment_data = apply_filters( 'bookingpress_ability_create_appointment_data', $appointment_data, $input );

        if ( is_wp_error( $appointment_data ) ) {
            return $appointment_data;
        }

        global $wpdb, $bookingpress_calendar, $tbl_bookingpress_appointment_bookings;

        // 3. Safety net: turn BookingPress's die() on a double booking into an exception
        $intercept = function ( $gateway = '', $message = '' ) {
            if ( false !== strpos( (string) $message, 'already exists in double booking table' ) ) {
                throw new \RuntimeException( 'bookingpress_double_booking' );
            }
        };

        $orig_post    = $_POST;
        $orig_request = $_REQUEST;

        $_REQUEST['appointment_data'] = $_POST['appointment_data'] = $appointment_data;
        $_POST['_wpnonce'] = $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        add_action( 'bookingpress_payment_log_entry', $intercept, 10, 2 );

        try {
            $response = $bookingpress_calendar->bookingpress_save_appointment_booking_func( false, true );
        } catch ( \RuntimeException $e ) {
            if ( 'bookingpress_double_booking' !== $e->getMessage() ) {
                throw $e;
            }
            status_header( 200 ); // undo the 409 BookingPress already set
            return new \WP_Error( 'slot_unavailable', __( 'This time slot is already booked. Choose another slot.', 'bookingpress-appointment-booking' ), [ 'status' => 409 ] );
        } finally {
            remove_action( 'bookingpress_payment_log_entry', $intercept, 10 );
            $_POST    = $orig_post;
            $_REQUEST = $orig_request;
        }

        // 4. Every remaining path returns an array or a WP_Error
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        if ( ! is_array( $response ) || 'success' !== ( $response['variant'] ?? '' ) ) {
            $msg = is_array( $response ) && ! empty( $response['msg'] ) ? $response['msg'] : __( 'The appointment could not be booked.', 'bookingpress-appointment-booking' );
            return new \WP_Error( 'booking_failed', $msg, [ 'status' => 400 ] );
        }

        $payment_log_id = intval( $response['payment_log_id'] ?? 0 );
        $appointment_id = intval( $wpdb->get_var( $wpdb->prepare(
            "SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_payment_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $payment_log_id
        ) ) );

        if ( empty( $appointment_id ) ) {
            return new \WP_Error( 'appointment_id_not_found', __( 'The appointment was booked, but its ID could not be retrieved. Do not retry.', 'bookingpress-appointment-booking' ), [ 'status' => 500, 'payment_log_id' => $payment_log_id ] );
        }

        return [
            'success'        => true,
            'appointment_id' => $appointment_id,
            'payment_log_id' => $payment_log_id,
            'message'        => $response['msg'] ?? __( 'Appointment has been booked successfully.', 'bookingpress-appointment-booking' ),
        ];
    }

    public static function bookingpress_ability_update_appointment_status( $input = [] ){

        $appointment_id = !empty( $input['appointment_id'] ) ? intval( $input['appointment_id'] ) : 0;
        $appointment_status = !empty( $input['appointment_status'] ) ? intval( $input['appointment_status'] ) : 0;

        if( ! $appointment_id || ! $appointment_status ){
            return new \WP_Error(
                'invalid_input',
                __( 'Appointment ID and appointment status are required.', 'bookingpress-appointment-booking' ),
                [ 'status' => 400 ]
            );
        }

        if( !\BookingPress\core\Permissions::user_can_do_action( 'update_upcoming_appointments') ){
            return new \WP_Error(
                'forbidden',
                __( 'You do not have permission to update upcoming appointments.', 'bookingpress-appointment-booking' ),
                [ 'status' => 403 ]
            );
        }

        /** Check the current status of the apppointment */
        $appointmentDetails = ( new \BookingPress\Vue3\Repositories\AppointmentRepository )->find( $appointment_id );

        if( empty( $appointmentDetails ) ){
            return new \WP_Error(
                'not_found',
                __( 'Appointment not found.', 'bookingpress-appointment-booking' ),
                [ 'status' => 404 ]
            );
        }

        $exisitng_status = $appointmentDetails['bookingpress_appointment_status'];

        if( $exisitng_status == $appointment_status ){
            return new \WP_Error(
                'invalid_input',
                __( 'The appointment is already in the specified status.', 'bookingpress-appointment-booking' ),
                [ 'status' => 400 ]
            );
        }

        global $wpdb, $bookingpress_dashboard;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' ); //phpcs:ignore
        $_REQUEST['update_appointment_id'] = $appointment_id; //phpcs:ignore
        $_REQUEST['update_appointment_status'] = $appointment_status; //phpcs:ignore

        $response = $bookingpress_dashboard->bookingpress_change_upcoming_appointment_status( $appointment_id, $appointment_status, true );

        if( is_wp_error( $response ) ){
            return $response;
        }

        if( 1 == $response ){
            return [
                'success' => true,
                'message' => __( 'Appointment status updated successfully.', 'bookingpress-appointment-booking' )
            ];
        }

        /** 0 means another pending/approved appointment already occupies the same slot. */
        return new \WP_Error(
            'update_failed',
            ( is_array( $response ) && ! empty( $response['msg'] ) ) ? $response['msg'] : __( 'The appointment status could not be updated. Another appointment may already be booked in the same time slot.', 'bookingpress-appointment-booking' ),
            [ 'status' => 409 ]
        );

    }

    protected static function get_selected_end_time( $service_id, $booking_time ){
        $service = ( new \BookingPress\Vue3\Repositories\ServiceRepository )->find( $service_id );
        if( empty( $service ) ){
            return '';
        }

        $duration = !empty( $service['serviceDurationVal'] ) ? intval( $service['serviceDurationVal'] ) : 0;
        if( empty( $duration ) ){
            return '';
        }

        $duration_unit = !empty( $service['serviceDurationUnit'] ) ? $service['serviceDurationUnit'] : 'minutes';

        $duration_min = ( 'h' == $duration_unit ) ? $duration * 60 : $duration;

        $start_time = \DateTime::createFromFormat( 'H:i', $booking_time );
        if( !$start_time ){
            return '';
        }

        $end_time = clone $start_time;
        $end_time->modify( '+' . $duration_min . ' minutes' );

        return $end_time->format( 'H:i' );
    }
}
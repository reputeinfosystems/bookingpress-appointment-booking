<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

class DashboardRoutes extends Base {

    public function __construct() {
        
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {

        register_rest_route( 'bookingpress-app/v1', '/dashboard/summary', [
            [
                'methods' => 'POST',
                'callback' => [ $this, 'get_summary' ],
                'permission_callback' => function () {
                    return $this->permission_callback_for('retrieve_dashboard_summary');
                }
            ]
        ] );

        register_rest_route( 'bookingpress-app/v1', '/dashboard/set-filter-session', [
            [
                'methods' => 'POST',
                'callback' => [ $this, 'set_filter_session' ],
                'permission_callback' => function () {
                    return $this->permission_callback_for('set_dashboard_redirection');
                }
            ]
        ] );

        register_rest_route( 'bookingpress-app/v1', '/dashboard/charts', [
            [
                'methods' => 'POST',
                'callback' => [ $this, 'get_charts_data' ],
                'permission_callback' => function () {
                    return $this->permission_callback_for('retrieve_dashboard_chart');
                }
            ]
        ]);

        register_rest_route( 'bookingpress-app/v1', '/dashboard/upcoming-appointments', [
            [
                'methods' => 'POST',
                'callback' => [ $this, 'get_upcoming_appointments' ],
                'permission_callback' => function () {
                    return $this->permission_callback_for('retrieve_upcoming_appointments');
                }
            ]
        ]);

    }

    public function get_upcoming_appointments( $request ){
        $nonce = $request->get_param('_wpnonce');

        if( ! wp_verify_nonce( $nonce, 'bpa_wp_nonce' ) ) {
            return new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'bookingpress-appointment-booking' ), [ 'status' => 403 ] );
        }

        global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options,$tbl_bookingpress_form_fields, $bookingpress_appointment;

        $bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_date_format    = $bookingpress_global_details['wp_default_date_format'] . '  ' . $bookingpress_global_details['wp_default_time_format'];
        $bookingpress_default_date_format = $bookingpress_global_details['wp_default_date_format'];
        $bookingpress_default_time_format = $bookingpress_global_details['wp_default_time_format'];
        $bookingpress_appointment_status_arr = $bookingpress_global_details['appointment_status'];
        $search_where                = '';
        $search_where               .= 'WHERE 1=1';
        $search_where               .= $wpdb->prepare( ' AND CONCAT( bookingpress_appointment_date, " ", bookingpress_appointment_time ) >= %s',  date('Y-m-d H:i:s', current_time('timestamp') ) );
        $search_where                = apply_filters('bookingpress_dashboard_upcoming_appointments_data_filter', $search_where);

        $upcoming_appointments = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_appointment_bookings} {$search_where} ORDER BY bookingpress_appointment_date ASC LIMIT 0, 10", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        $appointments = array();
        if (! empty($upcoming_appointments) ) {
            $counter = 1;
            foreach ( $upcoming_appointments as $get_appointment ) {

                $appointment                   = array();
                $appointment['id']             = $counter;
                $appointment_id                = intval($get_appointment['bookingpress_appointment_booking_id']);
                $appointment['appointment_id'] = $appointment_id;
                $appointment['payment_id'] = $get_appointment['bookingpress_payment_id'];
                $payment_log                   = $wpdb->get_row($wpdb->prepare('SELECT bookingpress_invoice_id, bookingpress_customer_firstname,bookingpress_customer_lastname,bookingpress_customer_email, bookingpress_payment_gateway FROM ' . $tbl_bookingpress_payment_logs . ' WHERE bookingpress_appointment_booking_ref = %d', $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

                $appointment_date_time           = $get_appointment['bookingpress_appointment_date'] . ' ' . $get_appointment['bookingpress_appointment_time'];
                $appointment['created_date']     = date_i18n($bookingpress_date_format, strtotime($get_appointment['bookingpress_created_at']));
                $appointment['appointment_date'] = date_i18n($bookingpress_date_format, strtotime($appointment_date_time));

                $appointment['service_id']       = $get_appointment['bookingpress_service_id'];

                $appointment['booking_id'] = !empty($get_appointment['bookingpress_booking_id']) ? $get_appointment['bookingpress_booking_id'] : 1;
                $customer_email = ! empty($get_appointment['bookingpress_customer_email']) ? $get_appointment['bookingpress_customer_email'] : '';
                $customer_phone = ! empty($get_appointment['bookingpress_customer_phone']) ? $get_appointment['bookingpress_customer_phone'] : '';

                $appointment['customer_name'] = !empty($get_appointment['bookingpress_customer_name']) ? stripslashes_deep($get_appointment['bookingpress_customer_name']) :'';
                $appointment['customer_first_name'] = !empty($get_appointment['bookingpress_customer_firstname']) ? stripslashes_deep($get_appointment['bookingpress_customer_firstname']) :'';
                $appointment['customer_last_name'] = !empty($get_appointment['bookingpress_customer_lastname']) ? stripslashes_deep($get_appointment['bookingpress_customer_lastname']) :'';
                $appointment['customer_email'] = stripslashes_deep($customer_email);

                $bookingpress_customer_phone_dial_code = (isset($get_appointment['bookingpress_customer_phone_dial_code']) && !empty($get_appointment['bookingpress_customer_phone_dial_code'])) ? $get_appointment['bookingpress_customer_phone_dial_code'] : '';
                $appointment['customer_phone'] = stripslashes_deep($customer_phone);

                if(!empty($customer_phone) && !empty($bookingpress_customer_phone_dial_code)){
                    $appointment['customer_phone'] = '+'.$bookingpress_customer_phone_dial_code.' '.stripslashes_deep($customer_phone);
                }

                $appointment['service_name']  = stripslashes_deep($get_appointment['bookingpress_service_name']);
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

                $bookingpress_appointment_start_datetime = $appointment_date_time;
                $bookingpress_appointment_end_datetime = $get_appointment['bookingpress_appointment_date'].' '. $get_appointment['bookingpress_appointment_end_time'];

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
                    //$payment_amount = '';
                    $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0, $currency_symbol);
                } else {
                    $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($get_appointment['bookingpress_paid_amount'], $currency_symbol);
                }
                $appointment['appointment_payment'] = $payment_amount;

                $bookingpress_appointment_status = esc_html($get_appointment['bookingpress_appointment_status']);
                $bookingpress_appointment_status_label = $bookingpress_appointment_status;
                foreach($bookingpress_appointment_status_arr as $status_key => $status_val){
                    if($bookingpress_appointment_status == $status_val['value']){
                        $bookingpress_appointment_status_label = $status_val['text'];
                        break;
                    }    
                }
                $appointment['payment_numberic_amount'] = floatval($get_appointment['bookingpress_paid_amount']);
                $appointment['appointment_status']  = $bookingpress_appointment_status;
                $appointment['appointment_status_label'] = $bookingpress_appointment_status_label;

                $bookingpress_view_appointment_date = date_i18n($bookingpress_default_date_format, strtotime($get_appointment['bookingpress_appointment_date']));
                $bookingpress_view_appointment_time = date($bookingpress_default_time_format, strtotime($get_appointment['bookingpress_appointment_time']))." ".esc_html__('To', 'bookingpress-appointment-booking')." ".date($bookingpress_default_time_format, strtotime($get_appointment['bookingpress_appointment_end_time']));

                $appointment['view_appointment_date'] = $bookingpress_view_appointment_date;
                $appointment['view_appointment_time'] = $bookingpress_view_appointment_time;
                $bookingpress_payment_method = ( !empty( $payment_log['bookingpress_payment_gateway'] ) && $payment_log['bookingpress_payment_gateway']  == 'on-site') ? 'On Site': (!empty($payment_log['bookingpress_payment_gateway']) ? $payment_log['bookingpress_payment_gateway'] : ''); 
                $appointment['payment_method'] = $bookingpress_payment_method;
                $appointment = apply_filters('bookingpress_appointment_add_view_field', $appointment, $get_appointment);

                $appointment['change_status_loader'] = 0;

                $appointments[] = $appointment;
                $counter++;
            }
        }

        $bookingpress_form_field_data = $wpdb->get_results("SELECT `bookingpress_form_field_name`,`bookingpress_field_label` FROM {$tbl_bookingpress_form_fields}",ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
        $bookingpress_formdata = array();
        foreach($bookingpress_form_field_data as $key=> $value) {                    
            $bookingpress_formdata[$value['bookingpress_form_field_name']] = $value['bookingpress_field_label'];
        }

        $appointments = apply_filters('bookingpress_modify_appointment_data', $appointments);

        $return_data['upcoming_appointments'] = $appointments;
        $return_data['form_field_data'] = $bookingpress_formdata;

        return new \WP_REST_Response(
            [
                'success' => true,
                'data' => $return_data
            ],
            200
        );
    }

    public function get_charts_data( $request ){
        $nonce = $request->get_param('_wpnonce');

        if( ! wp_verify_nonce( $nonce, 'bpa_wp_nonce' ) ) {
            return new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'bookingpress-appointment-booking' ), [ 'status' => 403 ] );
        }

        global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers,$tbl_bookingpress_payment_logs;

        $selected_filter_val       = $request->get_param('selected_filter') ?? 'week';
        $custom_filter_val         = $request->get_param('custom_filter_val') ?? [];
        $custom_filter_val         = isset($custom_filter_val) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), (array) $custom_filter_val) : array(); // phpcs:ignore
        $return_data               = array();
        $search_filter_dates       = array();
        $appointments_search_query = $payment_search_query = $customer_search_query  = '1=1';
        $customer_search_query .= ' AND bookingpress_user_status = 1';            
        $appointments_group_by     = 'bookingpress_appointment_date';
        $customer_search_query_join = '';
        $bookingpress_start_date = $bookingpress_end_date = '';

        $bookingpress_current_date = date('Y-m-d', current_time('timestamp'));
        $bookingpress_start_date =  ! empty($custom_filter_val[0]) ? date('Y-m-d', strtotime(sanitize_text_field($custom_filter_val[0]))) : $bookingpress_current_date;
        $bookingpress_end_date =  ! empty($custom_filter_val[1]) ? date('Y-m-d', strtotime(sanitize_text_field($custom_filter_val[1]))) : $bookingpress_current_date;

        if(!empty($bookingpress_start_date) && !empty($bookingpress_end_date) && $bookingpress_start_date == $bookingpress_end_date ){

            $bookingpress_current_datetime_obj = new \DateTime($bookingpress_current_date);
            $bookingpress_match_datetimt_obj = new \DateTime($bookingpress_start_date);
            $bookingpress_dates_interval = $bookingpress_current_datetime_obj->diff($bookingpress_match_datetimt_obj);
            if($bookingpress_dates_interval->days == 1 && $bookingpress_dates_interval->invert == 1){ //Yesterday condition
                $bookingpress_start_date  = $bookingpress_end_date = date('Y-m-d', strtotime('-1 days', current_time('timestamp')));                
                $start_time = strtotime('today');
                $end_time   = strtotime('tomorrow', $start_time) - 1;

                while ( $start_time <= $end_time ) {
                    array_push($search_filter_dates, date('H:i:s', $start_time));
                    $start_time = strtotime('+1 hour', $start_time);
                }

                $selected_filter_val = "yesterday";
            }else if($bookingpress_dates_interval->days == 1 && $bookingpress_dates_interval->invert == 0){ //Tomorrow condition
                $bookingpress_start_date = $bookingpress_end_date = date('Y-m-d', strtotime('+1 days', current_time('timestamp')));                            
                $start_time = strtotime('today');
                $end_time   = strtotime('tomorrow', $start_time) - 1;

                while ( $start_time <= $end_time ) {
                    array_push($search_filter_dates, date('H:i:s', $start_time));
                    $start_time = strtotime('+1 hour', $start_time);
                }

                $selected_filter_val = "tomorrow";
            }else{ // Today condition
                $bookingpress_start_date = $bookingpress_end_date = date('Y-m-d', current_time('timestamp'));                         
                $start_time = strtotime('today');
                $end_time   = strtotime('tomorrow', $start_time) - 1;

                while ( $start_time <= $end_time ) {
                    array_push($search_filter_dates, date('H:i:s', $start_time));
                    $start_time = strtotime('+1 hour', $start_time);
                }

                $selected_filter_val = "today";
            }
        }else{
            $bookingpress_tmp_end_date = date('Y-m-d', strtotime("+1 day", strtotime($bookingpress_end_date)));
            $bookingpress_get_all_dates = new \DatePeriod(
                new \DateTime($bookingpress_start_date),
                new \DateInterval('P1D'),
                new \DateTime($bookingpress_tmp_end_date)
            );

            foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
                $search_date_val = $date_val->format('M d');
                array_push($search_filter_dates, $search_date_val);
            }
        }

        if($selected_filter_val == 'today' || $selected_filter_val == 'yesterday' || $selected_filter_val == 'tomorrow') {
            $appointments_search_query .= " AND (bookingpress_appointment_date = '" . $bookingpress_start_date . "')";                               
            $payment_search_query .= " AND (bookingpress_payment_date_time BETWEEN '" . $bookingpress_start_date ." 00:00:00' AND '" . $bookingpress_start_date . " 23:59:59')";
            $customer_search_query .= " AND (bookingpress_user_created BETWEEN '" . $bookingpress_start_date . " 00:00:00' AND '" . $bookingpress_start_date . " 23:59:59')";     
            $appointments_group_by = 'bookingpress_appointment_time';                  

        } else {
            $appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '".$bookingpress_start_date."' AND '".$bookingpress_end_date."')";
            $payment_search_query .= " AND (bookingpress_payment_date_time BETWEEN '".$bookingpress_start_date . " 00:00:00' AND '".$bookingpress_end_date." 23:59:59')";
            $customer_search_query .= " AND (bookingpress_user_created BETWEEN '".$bookingpress_start_date . " 00:00:00' AND '".$bookingpress_end_date." 23:59:59')";  
        }    

        $appointments_search_query  = apply_filters('bookingpress_dashboard_appointment_summary_data_filter', $appointments_search_query);            
        $payment_search_query  = apply_filters('bookingpress_dashboard_payment_summary_data_filter', $payment_search_query);                        
        $customer_search_query_join = apply_filters('bookingpress_customer_view_join_add_filter', $customer_search_query_join);
        $customer_search_query = apply_filters('bookingpress_customer_view_add_filter', $customer_search_query);            

        $total_appointments = $wpdb->get_results("SELECT COUNT(bookingpress_appointment_booking_id) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} GROUP BY {$appointments_group_by}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                    
        $approved_appointments           = $wpdb->get_results("SELECT COUNT(bookingpress_appointment_booking_id) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = 1 AND {$appointments_search_query} GROUP BY {$appointments_group_by}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        $tmp_total_approved_appointments = array();
        foreach ( $approved_appointments as $appointment_key => $appointment_val ) {
            $total_appointments = (int) $appointment_val['total'];
            $appointment_date   = date('M d', strtotime($appointment_val['bookingpress_appointment_date']));
            if ($appointments_group_by != 'bookingpress_appointment_date' ) {
                $appointment_date = date('H:00:00', strtotime($appointment_val['bookingpress_appointment_time']));
            }
            $tmp_total_approved_appointments[ $appointment_date ] = $total_appointments;
        }
        $pending_appointments           = $wpdb->get_results("SELECT COUNT(bookingpress_appointment_booking_id) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = '2' AND {$appointments_search_query} GROUP BY {$appointments_group_by}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        $tmp_total_pending_appointments = array();
        foreach ( $pending_appointments as $appointment_key => $appointment_val ) {
            $total_appointments = (int) $appointment_val['total'];
            $appointment_date   = date('M d', strtotime($appointment_val['bookingpress_appointment_date']));
            if ($appointments_group_by != 'bookingpress_appointment_date' ) {
                $appointment_date = date('H:00:00', strtotime($appointment_val['bookingpress_appointment_time']));
            }
            $tmp_total_pending_appointments[ $appointment_date ] = $total_appointments;
        }

        $total_revenue = $wpdb->get_results("SELECT bookingpress_paid_amount, bookingpress_payment_date_time FROM {$tbl_bookingpress_payment_logs}  WHERE {$payment_search_query}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

        $revenue_amount = 0;
        $tmp_total_revenue = $bookingpress_total_revenue =array();
        foreach ( $total_revenue as $revenue_key => $revenue_val ) {
            $bookingpress_payment_actual_date  = !empty($revenue_val['bookingpress_payment_date_time']) ? $revenue_val['bookingpress_payment_date_time'] : '';
            $bookingpress_payment_date = date('M d',strtotime($bookingpress_payment_actual_date));                
            $revenue_amount = !empty($revenue_val['bookingpress_paid_amount']) ? $revenue_val['bookingpress_paid_amount'] : 0;
            if ($appointments_group_by != 'bookingpress_appointment_date' ) {
                $bookingpress_payment_date = date('H:00:00', strtotime($bookingpress_payment_actual_date));
            }
            if(array_key_exists($bookingpress_payment_date,$tmp_total_revenue)){    
                $tmp_total_revenue[ $bookingpress_payment_date] += $revenue_amount;
            } else {
                $tmp_total_revenue[ $bookingpress_payment_date] = $revenue_amount;
            }
        }                   
        $total_customers = $wpdb->get_results("SELECT cs.bookingpress_customer_id,cs.bookingpress_user_created FROM {$tbl_bookingpress_customers} as cs {$customer_search_query_join} WHERE {$customer_search_query}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
        $tmp_total_customers = array();
        foreach ( $total_customers as $customer_key => $customer_val ) {                
            $bookingpress_customer_actual_date  = !empty($customer_val['bookingpress_user_created']) ? $customer_val['bookingpress_user_created'] : '';
            $bookingpress_customer_created_date = date('M d', strtotime($bookingpress_customer_actual_date));                                
            if ($appointments_group_by != 'bookingpress_appointment_date' ) {
                $bookingpress_customer_created_date = date('H:00:00', strtotime($bookingpress_customer_actual_date));                    
            }
            if(array_key_exists($bookingpress_customer_created_date,$tmp_total_customers)){    
                $tmp_total_customers[ $bookingpress_customer_created_date] += 1;
            } else {
                $tmp_total_customers[ $bookingpress_customer_created_date] = 1;
            }
        }
        $total_approved_appointments = $total_pending_appointments = $total_revenue_data = $total_customers_data = array();
            
        foreach ( $search_filter_dates as $filter_key => $filter_val ) {
            $approved_appointment_vals = array_key_exists($filter_val, $tmp_total_approved_appointments) ? $tmp_total_approved_appointments[ $filter_val ] : 0;
            array_push($total_approved_appointments, $approved_appointment_vals);

            $pending_appointment_vals = array_key_exists($filter_val, $tmp_total_pending_appointments) ? $tmp_total_pending_appointments[ $filter_val ] : 0;
            array_push($total_pending_appointments, $pending_appointment_vals);

            $total_revenue_vals = array_key_exists($filter_val, $tmp_total_revenue) ? $tmp_total_revenue[ $filter_val ] : 0;
            array_push($total_revenue_data, $total_revenue_vals);

            $total_customer_vals    = array_key_exists($filter_val, $tmp_total_customers) ? $tmp_total_customers[ $filter_val ] : 0;
            $total_customers_data[] = $total_customer_vals;
        }

        $return_data['total_appointments']    = $total_appointments;
        $return_data['approved_appointments'] = $total_approved_appointments;
        $return_data['pending_appointments']  = $total_pending_appointments;
        $return_data['total_revenue']         = $total_revenue_data;
        $return_data['total_customers']       = $total_customers_data;
        $return_data['chart_x_axis_vals']     = $search_filter_dates;

        return new \WP_REST_Response(
            [
                'success' => true,
                'data' => $return_data
            ],
            200
        );
        
    }

    public function set_filter_session( $request ){
        $nonce = $request->get_param('_wpnonce');

        if( ! wp_verify_nonce( $nonce, 'bpa_wp_nonce' ) ) {
            return new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'bookingpress-appointment-booking' ), [ 'status' => 403 ] );
        }

        global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers;

        $return_data = array(                
            'variant' => 'error',
            'title'  => esc_html__('Error', 'bookingpress-appointment-booking'),
            'msg'    => esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking'),
            'bookingress_start_date' => '',
            'bookingress_end_date' => '',
        );
        $bookingpress_end_date = $bookingpress_start_date = '';
        $selected_filter_val       = $request->get_param( 'selected_filter') ?? 'week';// ! empty($_POST['selected_filter']) ? sanitize_text_field($_POST['selected_filter']) : 'week'; // phpcs:ignore WordPress.Security.NonceVerification
        if ($selected_filter_val == 'today' ) {
            $bookingpress_end_date  = $bookingpress_start_date = date('Y-m-d', current_time('timestamp'));                
        } elseif ($selected_filter_val == 'yesterday' ) {
            $bookingpress_end_date = $bookingpress_start_date  = date('Y-m-d', strtotime('-1 days', current_time('timestamp')));
        } elseif ($selected_filter_val == 'tomorrow' ) {
            $bookingpress_end_date = $bookingpress_start_date = date('Y-m-d', strtotime('+1 days', current_time('timestamp')));
        } elseif ($selected_filter_val == 'week' ) {
            $week_number  = date('W');
            $current_year = date('Y');
            $week_dates   = $BookingPress->get_weekstart_date_end_date($week_number, $current_year);
            $bookingpress_start_date = $week_dates['week_start'];
            $bookingpress_end_date =  $week_dates['week_end'];
        } elseif ($selected_filter_val == 'last_week' ) {
            $week_number  = date('W') - 1;
            $current_year = date('Y');
            $week_dates   = $BookingPress->get_weekstart_date_end_date($week_number, $current_year);
            $bookingpress_start_date   = $week_dates['week_start'];
            $bookingpress_end_date     = $week_dates['week_end'];
        } elseif ($selected_filter_val == 'monthly' ) {
            $monthly_dates = $BookingPress->get_monthstart_date_end_date();
            $bookingpress_start_date   = $monthly_dates['start_date'];
            $bookingpress_end_date     = $monthly_dates['end_date'];
        } elseif ($selected_filter_val == 'yearly' ) {
            $bookingpress_start_date            = date('Y-m-d', strtotime('01/01'));
            $bookingpress_end_date              = date('Y-m-d', strtotime('12/31'));
        } elseif ($selected_filter_val == 'custom' ) {
            $bookingpress_start_date  = ! empty($request->get_param('custom_filter_val')[0]) ? sanitize_text_field($request->get_param('custom_filter_val')[0]) : date('Y-m-d'); // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_end_date    = ! empty($request->get_param('custom_filter_val')[1]) ? sanitize_text_field($request->get_param('custom_filter_val')[1]) : date('Y-m-d'); // phpcs:ignore WordPress.Security.NonceVerification
        }
        if(!empty($bookingpress_start_date) && !empty($bookingpress_end_date) ) {
            $return_data = array(
                'variant' => 'success',
                'bookingress_start_date' => $bookingpress_start_date,
                'bookingress_end_date' => $bookingpress_end_date,
            );  
        }

        return new \WP_REST_Response(
            [
                'success' => $return_data['variant'] === 'success' ? true : false,
                'data' => $return_data
            ],
            200
        );
    }

    public function get_summary( $request ){

        $nonce = $request->get_param('_wpnonce');

        if( ! wp_verify_nonce( $nonce, 'bpa_wp_nonce' ) ) {
            return new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'bookingpress-appointment-booking' ), [ 'status' => 403 ] );
        }

        global $wpdb, $BookingPress, $bookingpress_global_options, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers,$tbl_bookingpress_payment_logs;

        $bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_date_format    = $bookingpress_global_details['wp_default_date_format'];

        $return_data = [
            'total_appointments'            => 0,
            'approved_appointments'         => 0,
            'pending_appointments'          => 0,
            'total_revenue'                 => 0,
            'total_customers'               => 0,
            'custom_filter_formatted_val'   => '',
        ];

        $appointments_search_query =  $payment_search_query = $customer_search_query =  '1=1';
        $bookingpress_start_date = $bookingpress_end_date = '';
        $customer_search_query .= ' AND bookingpress_user_status = 1';
        $selected_filter_val       =  $request->get_param( 'selected_filter') ?? 'week';//  ! empty($_POST['selected_filter']) ? sanitize_text_field($_POST['selected_filter']) : 'week'; // phpcs:ignore WordPress.Security.NonceVerification
        if ($selected_filter_val == 'today' ) {
            $bookingpress_start_date = $bookingpress_end_date = date('Y-m-d', current_time('timestamp'));                
        } elseif ($selected_filter_val == 'yesterday' ) {
            $bookingpress_start_date  = $bookingpress_end_date = date('Y-m-d', strtotime('-1 days', current_time('timestamp')));
            
        } elseif ($selected_filter_val == 'tomorrow' ) {
            $bookingpress_start_date  = $bookingpress_end_date = date('Y-m-d', strtotime('+1 days', current_time('timestamp')));                
        } elseif ($selected_filter_val == 'week' ) {
            $week_number  = date('W');
            $current_year = date('Y');
            $week_dates   = $BookingPress->get_weekstart_date_end_date($week_number, $current_year);
            $bookingpress_start_date = $week_dates['week_start'];
            $bookingpress_end_date    = $week_dates['week_end'];

        } elseif ($selected_filter_val == 'last_week' ) {
            $week_number  = date('W') - 1;
            $current_year = date('Y');
            $week_dates   = $BookingPress->get_weekstart_date_end_date($week_number, $current_year);
            $bookingpress_start_date   = $week_dates['week_start'];
            $bookingpress_end_date     = $week_dates['week_end'];
        } elseif ($selected_filter_val == 'monthly' ) {
            $monthly_dates = $BookingPress->get_monthstart_date_end_date();
            $bookingpress_start_date   = $monthly_dates['start_date'];
            $bookingpress_end_date     = $monthly_dates['end_date'];

        } elseif ($selected_filter_val == 'yearly' ) {
            $bookingpress_start_date            = date('Y-m-d', strtotime('01/01'));
            $bookingpress_end_date              = date('Y-m-d', strtotime('12/31'));
        } elseif ($selected_filter_val == 'custom' ) {
            $custom_filter_val = $request->get_param('custom_filter_val');
            $bookingpress_start_date  = ! empty($custom_filter_val[0]) ? sanitize_text_field(date('Y-m-d', strtotime($custom_filter_val[0]))) : date('Y-m-d'); // phpcs:ignore
            $bookingpress_end_date    = ! empty($custom_filter_val[1]) ? sanitize_text_field(date('Y-m-d', strtotime($custom_filter_val[1]))) : date('Y-m-d'); // phpcs:ignore

            $return_data['custom_filter_formatted_val'] = array(date($bookingpress_date_format, strtotime($bookingpress_start_date)), date($bookingpress_date_format, strtotime($bookingpress_end_date)));
        }        

        if($selected_filter_val == 'today' || $selected_filter_val == 'yesterday' || $selected_filter_val == 'tomorrow') {
            $appointments_search_query .= " AND (bookingpress_appointment_date = '" . $bookingpress_start_date . "')";                               
            $payment_search_query .= " AND (bookingpress_payment_date_time BETWEEN '" . $bookingpress_start_date ." 00:00:00' AND '" . $bookingpress_start_date . " 23:59:59')";
            $customer_search_query .= " AND (bookingpress_user_created BETWEEN '" . $bookingpress_start_date . " 00:00:00' AND '" . $bookingpress_start_date . " 23:59:59')";     
        } else {
            $appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '".$bookingpress_start_date."' AND '".$bookingpress_end_date."')";
            $payment_search_query .= " AND (bookingpress_payment_date_time BETWEEN '".$bookingpress_start_date . " 00:00:00' AND '".$bookingpress_end_date." 23:59:59')";
            $customer_search_query .= " AND (bookingpress_user_created BETWEEN '".$bookingpress_start_date . " 00:00:00' AND '".$bookingpress_end_date." 23:59:59')";  
        }  
        $payment_status_check = "AND bookingpress_payment_status = 1 )";
        $payment_status_check = apply_filters('bookingpress_check_payment_status', $payment_status_check);

        $appointments_search_query  = apply_filters('bookingpress_dashboard_appointment_summary_data_filter', $appointments_search_query);                    
        $payment_search_query  = apply_filters('bookingpress_dashboard_payment_summary_data_filter', $payment_search_query);            

        $total_appointments                = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} "); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
        $return_data['total_appointments'] = $total_appointments;

        $approved_appointments                = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = '1' AND {$appointments_search_query}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
        $return_data['approved_appointments'] = $approved_appointments;

        $pending_appointments                = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = '2' AND {$appointments_search_query}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
        $return_data['pending_appointments'] = $pending_appointments;
        $total_revenue = $wpdb->get_var( "SELECT( ( SELECT SUM(bookingpress_paid_amount) FROM $tbl_bookingpress_payment_logs WHERE {$payment_search_query} {$payment_status_check} ) as total FROM $tbl_bookingpress_payment_logs GROUP BY total;"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

        /* Completed */
        $completed_appointments = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = '6' AND {$appointments_search_query}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
        $return_data['total_completed_appointment'] = $completed_appointments;
        /* Completed */
            
        //$total_revenue = $wpdb->get_var("SELECT SUM(bookingpress_paid_amount) FROM {$tbl_bookingpress_payment_logs} WHERE {$payment_search_query} {$payment_status_check}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
        $total_revenue = !empty($total_revenue) ? $total_revenue : 0;              
        $total_revenue = $BookingPress->bookingpress_price_formatter_with_currency_symbol($total_revenue);            
        $return_data['total_revenue'] = $total_revenue;
        $customer_search_query_join = '';
        $customer_search_query_join = apply_filters('bookingpress_customer_view_join_add_filter', $customer_search_query_join);
        $customer_search_query = apply_filters('bookingpress_customer_view_add_filter', $customer_search_query);
        $total_customers                = $wpdb->get_var("SELECT COUNT(DISTINCT cs.bookingpress_customer_id) FROM {$tbl_bookingpress_customers} as cs {$customer_search_query_join} WHERE {$customer_search_query} "); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
        $return_data['total_customers'] = $total_customers;
        $return_data = apply_filters('bookingpress_update_summary_data', $return_data, $bookingpress_start_date,$bookingpress_end_date);

        return new \WP_REST_Response(
            [
                'success' => true,
                'data' => $return_data
            ],
            200
        );

    }

}
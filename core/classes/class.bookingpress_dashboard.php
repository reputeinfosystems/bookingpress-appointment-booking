<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_dashboard') ) {
    class bookingpress_dashboard Extends BookingPress_Core
    {
        function __construct()
        {
            add_action('bookingpress_dashboard_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_dashboard_view_func' ));
            add_action('bookingpress_dashboard_dynamic_data_fields', array( $this, 'bookingpress_dashboard_dynamic_data_fields_func' ));
            add_action('bookingpress_dashboard_dynamic_helper_vars', array( $this, 'bookingpress_dashboard_dynamic_helper_vars_func' ));
            add_action('bookingpress_dashboard_dynamic_on_load_methods', array( $this, 'bookingpress_dashboard_dynamic_on_load_methods_func' ));
            add_action('bookingpress_dashboard_dynamic_vue_methods', array( $this, 'bookingpress_dashboard_dynamic_vue_methods_func' ));

            add_action('wp_ajax_bookingpress_get_dashboard_upcoming_appointments', array( $this, 'bookingpress_dashboard_upcoming_appointments_func' ));
            add_action('wp_ajax_bookingpress_get_dashboard_summary', array( $this, 'bookingpress_dashboard_summary_func' ), 10);
            add_action('wp_ajax_bookingpress_get_charts_data', array( $this, 'get_chart_data' ));
            add_action('wp_ajax_bookingpress_change_upcoming_appointment_status', array( $this, 'bookingpress_change_upcoming_appointment_status' ));

            // Action for send email notification when appointment status changed from dropdown in backend.
            add_action('bookingpress_send_email_for_change_approved_status', array( $this, 'bookingpress_send_email_notification_for_change_status_func' ), 10, 3);
			
            add_action('bookingpress_send_email_for_change_pending_status', array( $this, 'bookingpress_send_email_notification_for_change_status_func' ), 10, 3);
			
            add_action('bookingpress_send_email_for_change_canceled_status', array( $this, 'bookingpress_send_email_notification_for_change_status_func' ), 10, 3);
			
            add_action('bookingpress_send_email_for_change_rejected_status', array( $this, 'bookingpress_send_email_notification_for_change_status_func' ), 10, 3);

            add_action( 'admin_init', array( $this, 'bookingpress_dashboard_vue_data_fields' ) );
            add_action('wp_ajax_bookingpress_set_dashboard_redirect_filter', array( $this, 'bookingpress_set_dashboard_redirect_filter_func' ));
            
        }
        
        /**
         * Dashboard module default data variables
         *
         * @return void
         */
        function bookingpress_dashboard_vue_data_fields(){
            global $bookingpress_dashboard_vue_data_fields, $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_appointment_status_arr    = $bookingpress_options['appointment_status'];

            $bookingpress_dashboard_vue_data_fields = array(
                'bulk_action'                   => 'bulk_action',
                'items'                         => array(),
                'summary_data'                  => array(
                    'total_appoint'    => 0,
                    'approved_appoint' => 0,
                    'pending_appoint'  => 0,
                    'total_revenue'    => 0,
                    'total_customers'  => 0,
                ),
                'currently_selected_filter'     => 'week',
                'custom_filter_val'             => '',
                'custom_filter_formatted_val'   => '',
                'appointment_chart_x_axis_data' => array(),
                'total_approved_appointments'   => array(),
                'total_pending_appointments'    => array(),
                'revenue_chart_x_axis_data'     => array(),
                'total_revenue'                 => array(),
                'chart_currency_symbol'         => '$',
                'total_customers_data'          => array(),
                'search_appointment_status'     => '',
                'appointment_time_slot'         => array(),
                'appointment_status'            => $bookingpress_appointment_status_arr,
                'rules'                         => array(
                    'appointment_selected_customer' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select customer', 'bookingpress-appointment-booking'),
                            'trigger'  => 'change',
                        ),
                    ),
                    'appointment_selected_service'  => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select service', 'bookingpress-appointment-booking'),
                            'trigger'  => 'change',
                        ),
                    ),
                    'appointment_booked_date'       => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select booking date', 'bookingpress-appointment-booking'),
                            'trigger'  => 'change',
                        ),
                    ),
                    'appointment_booked_time'       => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select booking time', 'bookingpress-appointment-booking'),
                            'trigger'  => 'change',
                        ),
                    ),
                ),
                'appointment_customers_list'    => array(),
                'appointment_services_list'     => array(),
                'appointment_formdata'          => array(
                    'appointment_selected_customer'     => '',
                    'appointment_selected_staff_member' => '',
                    'appointment_selected_service'      => '',
                    'appointment_booked_date'           => date('Y-m-d', current_time('timestamp')),
                    'appointment_booked_time'           => '',
                    'appointment_booked_end_time'       => '',
                    'appointment_internal_note'         => '',
                    'appointment_send_notification'     => false,
                    'appointment_status'                => '1',
                    'appointment_update_id'             => 0,
                ),
                'open_appointment_modal'        => false,
                'is_disabled'                   => false,
                'is_display_save_loader'        => '0',
            );
        }
        
        /**
         * Send email notification when dropdown status change from backend
         *
         * @param  mixed $email_notification_type
         * @param  mixed $appointment_id
         * @param  mixed $customer_email
         * @return void
         */
        function bookingpress_send_email_notification_for_change_status_func( $email_notification_type, $appointment_id, $customer_email )
        {
            global $BookingPress, $bookingpress_email_notifications;
            $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification($email_notification_type, $appointment_id, $customer_email);
        }
        
        /**
         * Change appointment status
         *
         * @param  mixed $update_appointment_id    Update appointment ID
         * @param  mixed $appointment_new_status   New status for update appointment
         * @return void
         */
        function bookingpress_change_upcoming_appointment_status( $update_appointment_id = '', $appointment_new_status = '' )
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_email_notifications;
            
            $response            = array();
            
            $bpa_check_authorization = $this->bpa_check_authentication( 'update_upcoming_appointments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $appointment_update_id  = ! empty($_REQUEST['update_appointment_id']) ? intval($_REQUEST['update_appointment_id']) : $update_appointment_id;
            $appointment_new_status = ! empty($_REQUEST['appointment_new_status']) ? sanitize_text_field($_REQUEST['appointment_new_status']) : $appointment_new_status;
            $return = 0;
            if (! empty($appointment_update_id) && ! empty($appointment_new_status) ) {

                $booked_appointment_details = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE bookingpress_appointment_booking_id = %d', $appointment_update_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                $bookingpress_appointment_date       = $booked_appointment_details['bookingpress_appointment_date'];
                $bookingpress_appointment_start_time = $booked_appointment_details['bookingpress_appointment_time'];

                $bookingpress_booked_appointment_service_id  = $booked_appointment_details['bookingpress_service_id'];
                $bookingpress_booked_appointment_customer_id = $booked_appointment_details['bookingpress_customer_id'];

                $bookingpress_customer_data = $BookingPress->get_customer_details($bookingpress_booked_appointment_customer_id);
                $customer_email             = ! empty($bookingpress_customer_data['bookingpress_user_email']) ? $bookingpress_customer_data['bookingpress_user_email'] : '';

                $is_appointment_already_booked = 0;

                if ($appointment_new_status == '1' || $appointment_new_status == '2' ) {
                    $is_appointment_already_booked = $wpdb->get_var($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_service_id = %d", $appointment_update_id, '2', '1', $bookingpress_appointment_date, $bookingpress_appointment_start_time, $bookingpress_booked_appointment_service_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    
                    $is_appointment_already_booked = apply_filters('bookinpress_is_appointment_book_for_change_status', $is_appointment_already_booked, $booked_appointment_details);
                }                

                if ($is_appointment_already_booked > 0 ) {
                    $return = 0;               
                } else {
                    $appointment_update_data = array(
                    'bookingpress_appointment_status' => $appointment_new_status,
                    );

                    $appointment_where_condition = array(
                    'bookingpress_appointment_booking_id' => $appointment_update_id,
                    );

                    $wpdb->update($tbl_bookingpress_appointment_bookings, $appointment_update_data, $appointment_where_condition);
                    $payment_new_status = '';


                    if( defined('DISABLE_WP_CRON') && true == DISABLE_WP_CRON ){ // check if WordPress cron is disabled. if disabled then send the emails directly without scheduling them.
                        $appointment_status_type = '';
                        if ($appointment_new_status == '1' ) {

                            $booked_appointment_paymanet_details = $wpdb->get_row($wpdb->prepare('SELECT bookingpress_payment_gateway FROM ' . $tbl_bookingpress_payment_logs . ' WHERE bookingpress_appointment_booking_ref = %d', $appointment_update_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
                            if(!empty($booked_appointment_paymanet_details)){                                
                                if($booked_appointment_paymanet_details['bookingpress_payment_gateway'] != 'on-site'){                            
                                    $payment_new_status = '1';
                                    $wpdb->update($tbl_bookingpress_payment_logs, array( 'bookingpress_payment_status' => $payment_new_status ), array( 'bookingpress_appointment_booking_ref' => $appointment_update_id ));
                                }
                            }        
                            $appointment_status_type = 'Appointment Approved';
                        } else if ($appointment_new_status == '2' && !empty( $appointment_update_id ) ) {
                            $appointment_status_type = 'Appointment Pending';
                        } else if ($appointment_new_status == '3' && !empty( $appointment_update_id ) ) {
                            $appointment_status_type = 'Appointment Canceled';
                        } else if ($appointment_new_status == '4' && !empty( $appointment_update_id ) ) {
                            $appointment_status_type = 'Appointment Rejected';
                        }

                        
                        $bookingpress_entry_id  = isset($booked_appointment_details['bookingpress_entry_id']) ? $booked_appointment_details['bookingpress_entry_id'] : 0;

                        do_action( 'bookingpress_before_send_upcoming_appointment_status_notification', $appointment_update_id, $bookingpress_entry_id );

                        $this->bookingpress_send_email_notification_for_change_status_func( $appointment_status_type, $appointment_update_id, $customer_email );
                    } else {
                        if( wp_next_scheduled ( 'bookingpress_send_email_for_change_status' ) ){
                            wp_clear_scheduled_hook('bookingpress_send_email_for_change_status');
                        }
                        
                        if ($appointment_new_status == '1' ) {
                           
                            $booked_appointment_paymanet_details = $wpdb->get_row($wpdb->prepare('SELECT bookingpress_payment_gateway FROM ' . $tbl_bookingpress_payment_logs . ' WHERE bookingpress_appointment_booking_ref = %d', $appointment_update_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
                            if(!empty($booked_appointment_paymanet_details)){                                
                                if($booked_appointment_paymanet_details['bookingpress_payment_gateway'] != 'on-site'){
                                    $payment_new_status = '1';                            
                                    $wpdb->update($tbl_bookingpress_payment_logs, array( 'bookingpress_payment_status' => $payment_new_status ), array( 'bookingpress_appointment_booking_ref' => $appointment_update_id ));        
                                }
                            }    			     	
                            if( wp_next_scheduled ( 'bookingpress_send_email_for_change_approved_status', array( 'Appointment Approved', $appointment_update_id, $customer_email ) ) ){
                                wp_clear_scheduled_hook('bookingpress_send_email_for_change_approved_status', array( 'Appointment Approved', $appointment_update_id, $customer_email ) );
                            }
                            if ( ! empty($appointment_update_id) ) {
                                wp_schedule_single_event( ( current_time('timestamp',true) + 10 ), 'bookingpress_send_email_for_change_approved_status', array( 'Appointment Approved', $appointment_update_id, $customer_email ), false);
                            }
                            
                        } elseif ($appointment_new_status == '2' &&  ! empty($appointment_update_id) ) {
                            if( wp_next_scheduled ( 'bookingpress_send_email_for_change_pending_status', array( 'Appointment Pending', $appointment_update_id, $customer_email ) ) ){
                                wp_clear_scheduled_hook('bookingpress_send_email_for_change_pending_status', array( 'Appointment Pending', $appointment_update_id, $customer_email ) );
                            }
    
                            wp_schedule_single_event(( current_time('timestamp',true) + 10 ), 'bookingpress_send_email_for_change_pending_status', array( 'Appointment Pending', $appointment_update_id, $customer_email ), false);
                            
                        } elseif ($appointment_new_status == '3' && ! empty($appointment_update_id) ) {
                            if( wp_next_scheduled ( 'bookingpress_send_email_for_change_canceled_status', array( 'Appointment Canceled', $appointment_update_id, $customer_email ) ) ){
                                wp_clear_scheduled_hook('bookingpress_send_email_for_change_canceled_status', array( 'Appointment Canceled', $appointment_update_id, $customer_email ) );
                            }
                            wp_schedule_single_event(( current_time('timestamp',true) + 10 ), 'bookingpress_send_email_for_change_canceled_status', array( 'Appointment Canceled', $appointment_update_id, $customer_email ), false);
                        } elseif ($appointment_new_status == '4' && ! empty($appointment_update_id) ) {
                            if( wp_next_scheduled ( 'bookingpress_send_email_for_change_rejected_status', array( 'Appointment Rejected', $appointment_update_id, $customer_email ) ) ){
                                wp_clear_scheduled_hook('bookingpress_send_email_for_change_rejected_status', array( 'Appointment Rejected', $appointment_update_id, $customer_email ) );
                            }
                            wp_schedule_single_event(( current_time('timestamp',true) + 10 ), 'bookingpress_send_email_for_change_rejected_status', array( 'Appointment Rejected', $appointment_update_id, $customer_email ), false);
                        }
                    }

					

                    do_action('bookingpress_after_change_appointment_status', $appointment_update_id, $appointment_new_status);
                    $return = 1;
                }
            }
            if (isset($_POST['action']) && sanitize_text_field($_POST['action']) != 'bookingpress_change_upcoming_appointment_status' ) { // phpcs:ignore WordPress.Security.NonceVerification
                return intval($return); 
                exit(); 
            } else {
                echo esc_html($return);                 
                exit;
            }
        }
        
        /**
         * Ajax request for get dashboard summary details
         *
         * @return void
         */
        function bookingpress_dashboard_summary_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers,$tbl_bookingpress_payment_logs, $bookingpress_global_options;
            
            $response            = array();
            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_dashboard_summary', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_date_format    = $bookingpress_global_details['wp_default_date_format'];

            $return_data = array(
            'total_appointments'    => 0,
            'approved_appointments' => 0,
            'pending_appointments'  => 0,
            'total_revenue'         => 0,
            'total_customers'       => 0,
            'custom_filter_formatted_val' => '',
            );

            $appointments_search_query =  $payment_search_query = $customer_search_query =  '1=1';
            $bookingpress_start_date = $bookingpress_end_date = '';
            $customer_search_query .= ' AND bookingpress_user_status = 1';
            $selected_filter_val       = ! empty($_POST['selected_filter']) ? sanitize_text_field($_POST['selected_filter']) : 'week'; // phpcs:ignore WordPress.Security.NonceVerification
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
                $bookingpress_start_date  = ! empty($_POST['custom_filter_val'][0]) ? sanitize_text_field(date('Y-m-d', strtotime($_POST['custom_filter_val'][0]))) : date('Y-m-d'); // phpcs:ignore
                $bookingpress_end_date    = ! empty($_POST['custom_filter_val'][1]) ? sanitize_text_field(date('Y-m-d', strtotime($_POST['custom_filter_val'][1]))) : date('Y-m-d'); // phpcs:ignore

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
            echo wp_json_encode($return_data);
            exit();
        }
        
        /**
         * Ajax request for get upcoming appointments
         *
         * @return void
         */
        function bookingpress_dashboard_upcoming_appointments_func()
        {
            //global $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $BookingPress, $tbl_bookingpress_customers, $bookingpress_global_options;
            global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options,$tbl_bookingpress_form_fields, $bookingpress_appointment;

            $return_data = array(
                'upcoming_appointments' => array(),
            );

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_upcoming_appointments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

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

            echo wp_json_encode($return_data);
            exit();
        }

        
        /**
         * Load dashboard module view file
         *
         * @return void
         */
        function bookingpress_dynamic_load_dashboard_view_func()
        {
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/dashboard/manage_dashboard.php';
            $bookingpress_load_file_name = apply_filters('bookingpress_modify_dashboard_view_file_path', $bookingpress_load_file_name);

            include $bookingpress_load_file_name;
        }
        
        /**
         * Ajax request for get dashboard module chart data
         *
         * @return void
         */
        function get_chart_data()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers,$tbl_bookingpress_payment_logs;

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_dashboard_chart', true, 'bpa_wp_nonce' );
            $response = array();
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $selected_filter_val       = isset($_POST['selected_filter']) ? sanitize_text_field($_POST['selected_filter']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
            $custom_filter_val         = isset($_POST['custom_filter_val']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), (array) $_POST['custom_filter_val']) : array(); // phpcs:ignore
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

                $bookingpress_current_datetime_obj = new DateTime($bookingpress_current_date);
                $bookingpress_match_datetimt_obj = new DateTime($bookingpress_start_date);
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
                $bookingpress_get_all_dates = new DatePeriod(
                    new DateTime($bookingpress_start_date),
                    new DateInterval('P1D'),
                    new DateTime($bookingpress_tmp_end_date)
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

            echo wp_json_encode($return_data);
            exit();
        }
        
        /**
         * Add more data variables for dashboard module
         *
         * @return void
         */
        function bookingpress_dashboard_dynamic_data_fields_func()
        {
            global $bookingpress_dashboard_vue_data_fields, $BookingPress, $bookingpress_global_options;

            $currency_name   = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
            $currency_name   = ! empty($currency_name) ? $currency_name : 'US Dollar';
            $currency_symbol = $BookingPress->bookingpress_get_currency_symbol($currency_name);
            $bookingpress_dashboard_vue_data_fields['chart_currency_symbol'] = $currency_symbol;


            $bookingpress_services_details2                                      = array();
            $bookingpress_services_details2[]                                    = array(
            'category_name'     => '',
            'category_services' => array(
            '0' => array(
            'service_id'    => 0,
            'service_name'  => esc_html__('Select service', 'bookingpress-appointment-booking'),
            'service_price' => '',
            ),
            ),
            );
            $bookingpress_services_details                                       = $BookingPress->get_bookingpress_service_data_group_with_category();
            $bookingpress_services_details2                                      = array_merge($bookingpress_services_details2, $bookingpress_services_details);
            $bookingpress_dashboard_vue_data_fields['appointment_services_list'] = $bookingpress_services_details2;

            $default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
            if (! empty($default_daysoff_details) ) {
                $default_daysoff_details                                 = array_map(
                    function ( $date ) {
                        return date('Y-m-d', strtotime($date));
                    },
                    $default_daysoff_details
                );
                $bookingpress_dashboard_vue_data_fields['disabledDates'] = $default_daysoff_details;
            } else {
                $bookingpress_dashboard_vue_data_fields['disabledDates'] = '';
                
            }
            $bookingpress_dashboard_vue_data_fields['bookingpress_loading'] = false;
            $bookingpress_dashboard_vue_data_fields['customer_id'] ='';
            $bookingpress_dashboard_vue_data_fields['appointment_formdata']['_wpnonce'] = wp_create_nonce('bpa_wp_nonce');
            $bookingpress_dashboard_vue_data_fields['form_field_data'] = array();

            $bookingpress_dashboard_vue_data_fields['bookingpress_picker_options'] = array();

            $week_number  = date( 'W' );
			$current_year = date( 'Y' );
            $week_dates   = $BookingPress->get_weekstart_date_end_date( $week_number, $current_year );
			$week_start   = $week_dates['week_start'];
			$week_end     = $week_dates['week_end'];
            $bookingpress_dashboard_vue_data_fields['custom_filter_val'] = array($week_start, $week_end);
            $bookingpress_dashboard_vue_data_fields['currently_selected_filter'] = 'custom';

            $bookingpress_dashboard_vue_data_fields = apply_filters('bookingpress_modify_dashboard_data_fields', $bookingpress_dashboard_vue_data_fields);
            echo wp_json_encode($bookingpress_dashboard_vue_data_fields);
        }
        
        /**
         * Dashboard module dynamic helper variables
         *
         * @return void
         */
        function bookingpress_dashboard_dynamic_helper_vars_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            ?>
                var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
                ELEMENT.locale(lang)

                var bookingpress_appointment_chart = ''
                var revenue_chart = ''
                var customer_chart = ''
            <?php
        }
        
        /**
         * Dashboard module onload methods
         *
         * @return void
         */
        function bookingpress_dashboard_dynamic_on_load_methods_func()
        {   global $bookingpress_global_options, $BookingPress;
            $bookingpress_global_details  = $bookingpress_global_options->bookingpress_global_options();
            ?>
            const vm = this
            <?php
            if(!empty($_GET['upgrade_action']) && ($_GET['upgrade_action'] == "upgrade_to_pro")){
                $bpa_current_date_for_bf_popup = current_time('timestamp',true); //GMT/ UTC+00 timeszone

                $bpa_sale_popup_details = $BookingPress->bookingpress_get_sales_data();

                $current_year = gmdate('Y', current_time('timestamp', true ) );


                if( !empty( $bpa_sale_popup_details[ $current_year ] ) ){
                    $sale_details = $bpa_sale_popup_details[ $current_year ];
                    
                    $bpa_bf_popup_start_time = $sale_details['start_time'];
                    $bpa_bf_popup_end_time = $sale_details['end_time'];

                    if( $bpa_current_date_for_bf_popup >= $bpa_bf_popup_start_time && $bpa_current_date_for_bf_popup <= $bpa_bf_popup_end_time ){
                    ?>
                        vm.premium_modal = true;
                        vm.bookingpress_old_premium_modal = false; 
                    <?php } else { ?>
                        vm.bookingpress_old_premium_modal = true;                                
                        vm.premium_modal = false;
                    <?php
                    }
                } else { ?>
                    vm.bookingpress_old_premium_modal = true;                                
                    vm.premium_modal = false;
                <?php
                }
            }
            ?>
            vm.loadSummary()
            vm.loadAppointments()    
            vm.loadCharts()
            vm.bookingpress_picker_options = {
                shortcuts: [
                    {
                        text: '<?php esc_html_e('Today', 'bookingpress-appointment-booking'); ?>',
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            picker.$emit('pick', [start, end]);
                        }
                    }, 
                    {
                        text: '<?php esc_html_e('Yesterday', 'bookingpress-appointment-booking'); ?>',
                        onClick(picker) {
                            var bookingpress_yesterday_date = new Date();
                            bookingpress_yesterday_date.setDate(bookingpress_yesterday_date.getDate() - 1);
                            const end = bookingpress_yesterday_date;
                            const start = bookingpress_yesterday_date;
                            picker.$emit('pick', [start, end]);
                        }
                    }, 
                    {
                        text: '<?php esc_html_e('Tomorrow', 'bookingpress-appointment-booking'); ?>',
                        onClick(picker) {
                            var bookingpress_tomorrow_date = new Date();
                            bookingpress_tomorrow_date.setDate(bookingpress_tomorrow_date.getDate() + 1);
                            const end = bookingpress_tomorrow_date;
                            const start = bookingpress_tomorrow_date;
                            picker.$emit('pick', [start, end]);
                        }
                    }, 
                    {
                        text: '<?php esc_html_e('This week', 'bookingpress-appointment-booking'); ?>',
                        onClick(picker) {
                            var bookingpress_date_obj = new Date();
                            var first_date = (bookingpress_date_obj.getDate() + 1) - bookingpress_date_obj.getDay();
                            var end_date = first_date + 6;
                            var first_date_obj = new Date(bookingpress_date_obj);
                            first_date_obj.setDate(first_date);
                            var end_date_obj = new Date(bookingpress_date_obj);
                            end_date_obj.setDate(end_date);
                            picker.$emit('pick', [first_date_obj, end_date_obj]);
                        }
                    }, 
                    {
                        text: '<?php esc_html_e('Last week', 'bookingpress-appointment-booking'); ?>',
                        onClick(picker) {
                            var first_date_obj = new Date(moment().day(-7));
                            var end_date_obj = new Date(moment().day(-1));
                            picker.$emit('pick', [first_date_obj, end_date_obj]);
                        }
                    }, 
                    {
                        text: '<?php esc_html_e('This month', 'bookingpress-appointment-booking'); ?>',
                        onClick(picker) {
                            var bookingpress_current_month_obj = new Date();
                            var bookingpress_firstday = new Date(bookingpress_current_month_obj.getFullYear(), bookingpress_current_month_obj.getMonth(), 1);
                            var bookingpress_lastday = new Date(bookingpress_current_month_obj.getFullYear(), bookingpress_current_month_obj.getMonth() + 1, 0);
                            const end = bookingpress_lastday;
                            const start = bookingpress_firstday;
                            picker.$emit('pick', [start, end]);
                        }
                    }, 
                    {
                        text: '<?php esc_html_e('Last month', 'bookingpress-appointment-booking'); ?>',
                        onClick(picker) {
                            var bookingpress_date_obj = new Date();
                            var bookingpress_firstday_prv_month = new Date(bookingpress_date_obj.getFullYear(), bookingpress_date_obj.getMonth() - 1, 1);
                            var bookingpress_lastday_prv_month = new Date(bookingpress_date_obj.getFullYear(), bookingpress_date_obj.getMonth(), 0);
                            picker.$emit('pick', [bookingpress_firstday_prv_month, bookingpress_lastday_prv_month]);
                        }
                    },
                    {
                        text: '<?php esc_html_e('This year', 'bookingpress-appointment-booking'); ?>',
                        onClick(picker) {
                            var bookingress_date_obj = new Date();
                            var bookingpress_first_day = new Date(bookingress_date_obj.getFullYear(), 0, 1);
                            var bookingpress_last_day = new Date(bookingress_date_obj.getFullYear(), 11, 31);
                            const end = bookingpress_last_day;
                            const start = bookingpress_first_day;
                            picker.$emit('pick', [start, end]);
                        }
                    },
                ],
                'firstDayOfWeek': parseInt('<?php echo esc_html($bookingpress_global_details['start_of_week']); ?>')
            };
            <?php
        }
        
        /**
         * Dashboard module methods / functions
         *
         * @return void
         */
        function bookingpress_dashboard_dynamic_vue_methods_func()
        {
            global $bookingpress_notification_duration,$bookingpress_slugs;
            ?>
            loadCharts(){
                const vm = this

                var postData = { action:'bookingpress_get_charts_data', selected_filter: vm.currently_selected_filter, custom_filter_val: vm.custom_filter_val, _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.appointment_chart_x_axis_data = response.data.chart_x_axis_vals
                    vm.revenue_chart_x_axis_data = response.data.chart_x_axis_vals
                    vm.total_approved_appointments = response.data.approved_appointments
                    vm.total_pending_appointments = response.data.pending_appointments
                    vm.total_revenue = response.data.total_revenue
                    vm.total_customers_data = response.data.total_customers

                    if(bookingpress_appointment_chart != '' && bookingpress_appointment_chart != undefined){
                        bookingpress_appointment_chart.destroy()
                    }

                    if(revenue_chart != '' && revenue_chart != undefined){
                        revenue_chart.destroy()
                    }

                    if(customer_chart != '' && customer_chart != undefined){
                        customer_chart.destroy()
                    }

                    const ctx = document.getElementById('appointments_charts').getContext('2d');
                    bookingpress_appointment_chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: vm.revenue_chart_x_axis_data,
                            datasets: [{
                                label: '<?php esc_html_e('Approved Appointment', 'bookingpress-appointment-booking'); ?>',
                                data: vm.total_approved_appointments,
                                backgroundColor: [
                                    'rgba(18, 212, 136, 0.3)',
                                ],
                                borderColor: [
                                    'rgba(18, 212, 136, 1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: '<?php esc_html_e('Pending Appointment', 'bookingpress-appointment-booking'); ?>',
                                data: vm.total_pending_appointments,
                                backgroundColor: [
                                    'rgba(245, 174, 65, 0.3)',
                                ],
                                borderColor: [
                                    'rgba(245, 174, 65, 1)',
                                ],
                                borderWidth: 1    
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: false
                                }
                            },
                            maintainAspectRatio: false,
                            responsive: true,
                            plugins:{
                                title: {
                                    display: true,
                                    text: '<?php esc_html_e('Appointments', 'bookingpress-appointment-booking'); ?>',
                                    font: {
                                        size: 16
                                    }
                                },                              
                                legend: {
                                    onClick: null,
                                    labels: {
                                        font: {
                                            size: 15
                                        }
                                    },                                    
                                },
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                }
                            }
                        }
                    });

                    const ctx2 = document.getElementById('revenue_charts').getContext('2d');
                    revenue_chart = new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: vm.revenue_chart_x_axis_data,
                            datasets: [{
                                label: '<?php esc_html_e('Revenue', 'bookingpress-appointment-booking'); ?>',
                                data: vm.total_revenue,
                                backgroundColor: [
                                    'rgba(18, 212, 136, 0.3)',
                                ],
                                borderColor: [
                                    'rgba(18, 212, 136, 1)',
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    onClick: null,
                                    labels: {
                                        font: {
                                            size: 15
                                        }
                                    },
                                },
                                title: {
                                    display: true,
                                    text: '<?php esc_html_e('Revenue', 'bookingpress-appointment-booking'); ?>',
                                    font: {
                                        size: 16
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += vm.chart_currency_symbol + ((context.parsed.y).toFixed(2))
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                }
                            }
                        }
                    });


                    const ctx3 = document.getElementById('customer_charts').getContext('2d');
                    customer_chart = new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: vm.revenue_chart_x_axis_data,
                            datasets: [{
                                label: '<?php esc_html_e('Customers', 'bookingpress-appointment-booking'); ?>',
                                data: vm.total_customers_data,
                                backgroundColor: [
                                    'rgba(33, 103, 241, 0.3)',
                                ],
                                borderColor: [
                                    'rgba(33, 103, 241, 1)',
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: false
                                }
                            },
                            plugins:{
                                title: {
                                    display: true,
                                    text: '<?php esc_html_e('Customers', 'bookingpress-appointment-booking'); ?>',
                                    font: {
                                        size: 16
                                    }
                                },
                                legend: {
                                    onClick: null,
                                    labels: {
                                        font: {
                                            size: 15
                                        }
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                }
                            }
                        }
                    });
                }.bind(this) )
                .catch( function (error) {                    
                    vm.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });    

                
            },
            async loadSummary(){
                const vm2 = this
                var postData = { action:'bookingpress_get_dashboard_summary', selected_filter: vm2.currently_selected_filter, custom_filter_val: vm2.custom_filter_val,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm2.summary_data.total_appoint = response.data.total_appointments
                    vm2.summary_data.approved_appoint = response.data.approved_appointments
                    vm2.summary_data.pending_appoint = response.data.pending_appointments
                    vm2.summary_data.total_revenue = response.data.total_revenue
                    vm2.summary_data.total_customers = response.data.total_customers
                    vm2.custom_filter_formatted_val = response.data.custom_filter_formatted_val
            <?php
            do_action('bookingpress_load_summary_dynamic_data');
            ?>
                                        

                }.bind(this) )
                .catch( function (error) {                    
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            async loadAppointments() {
                const vm2 = this
                var bookingpress_search_data = { }
                var postData = { action:'bookingpress_get_dashboard_upcoming_appointments', search_data: bookingpress_search_data,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    if( "error" == response.data.variant ){
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval( $bookingpress_notification_duration); ?>
                        });
                    } else {
                        this.items = response.data.upcoming_appointments;
                        this.totalItems = response.data.totalItems;
                        this.form_field_data = response.data.form_field_data
                    }
                    <?php do_action('bookingpress_modify_appointment_success_response_data'); ?>
                }.bind(this) )
                .catch( function (error) {                    
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            select_dashboard_filter(){
                const vm = this
                if(vm.currently_selected_filter != 'custom'){
                    this.loadSummary()
                    this.loadCharts()
                    vm.custom_filter_val = '';
                }else{
                    vm.$refs.bookingpress_custom_filter_rangepicker.$el.click();
                }
            },            
            select_dashboard_custom_date_filter(selected_value){
                this.loadSummary()
                this.loadCharts()
            },
            select_date(selected_value) {
                this.custom_filter_val[0] = this.get_formatted_date(this.custom_filter_val[0])
                this.custom_filter_val[1] = this.get_formatted_date(this.custom_filter_val[1])
                this.bookingpress_set_time_slot()
            },
            get_formatted_date(iso_date){

                if( true == /(\d{2})\T/.test( iso_date ) ){
                    let date_time_arr = iso_date.split('T');
                    return date_time_arr[0];
                }
                var __date = new Date(iso_date);
                var __year = __date.getFullYear();
                var __month = __date.getMonth()+1;
                var __day = __date.getDate();
                if (__day < 10) {
                    __day = '0' + __day;
                }
                if (__month < 10) {
                    __month = '0' + __month;
                }
                var formatted_date = __year+'-'+__month+'-'+__day;
                return formatted_date;
            },
            bookingpress_change_status(update_id, selectedValue){
                const vm2 = this
                let rowIndex;
                vm2.items.forEach(function(currentValue, index, arr){
                    if(update_id == currentValue.appointment_id){
                        vm2.items[index].change_status_loader = 1;
                        rowIndex = index;
                    }
                });
                var postData = { action:'bookingpress_change_upcoming_appointment_status', update_appointment_id: update_id, appointment_new_status: selectedValue,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    if( "error" == response.data.variant ){
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                        if( "undefined" != typeof rowIndex && "undefined" != typeof vm2.items && "undefined" != typeof vm2.items[rowIndex] ){
                            vm2.items[rowIndex].change_status_loader = 0;
                        }
                        return false;
                    }
                    if(response.data == "0" || response.data == 0){
                        vm2.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Appointment already booked for this slot', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                        vm2.loadAppointments();
                        return false;
                    }else{
                        vm2.$notify({
                            title: '<?php esc_html_e('Success', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Appointment status changed successfully', 'bookingpress-appointment-booking'); ?>',
                            type: 'success',
                            customClass: 'success_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                        vm2.loadAppointments();
                    }
                }.bind(this) )
                .catch( function (error) {
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            closeAppointmentModal() {
                const vm2= this
                vm2.$refs['appointment_formdata'].resetFields()
                vm2.resetForm()
                vm2.appointment_customers_list = [];
                vm2.open_appointment_modal = false
                <?php do_action('bookingpress_dashboard_add_appointment_model_reset') ?>
            },
            resetForm() {
                const vm2 = this
                vm2.appointment_formdata.appointment_selected_customer = ''
                vm2.appointment_formdata.appointment_selected_staff_member= ''
                vm2.appointment_formdata.appointment_selected_service = ''
                vm2.appointment_formdata.appointment_booked_date = '<?php echo esc_html(date('Y-m-d', current_time('timestamp'))); ?>';
                vm2.appointment_formdata.appointment_booked_time = ''
                vm2.appointment_formdata.appointment_internal_note = ''
                vm2.appointment_formdata.appointment_send_notification = ''
                vm2.appointment_formdata.appointment_status = '1'
                vm2.appointment_formdata.appointment_update_id = 0
            },
            /*open_add_appointment_modal() {                
                this.open_appointment_modal = true;
            },*/
            saveAppointmentBooking(bookingAppointment){
                const vm = new Vue()
                const vm2 = this
                    this.$refs[bookingAppointment].validate((valid) => {
                        <?php do_action('bookingpress_modify_request_after_validation'); ?>
                        if (valid) {
                        vm2.is_disabled = true
                        vm2.is_display_save_loader = '1'    
                        var postData = { action:'bookingpress_save_appointment_booking', _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                        postData.appointment_data = JSON.stringify(vm2.appointment_formdata);
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                        .then(function(response){
                            vm2.is_disabled = false                            
                            vm2.is_display_save_loader = '0'    
                            vm2.closeAppointmentModal()
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm2.loadAppointments()                
                        }).catch(function(error){
                            console.log(error);
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        });
                    }
                });
            },
            bookingpress_set_time_slot() {
                const vm = this
                var service_id = vm.appointment_formdata.appointment_selected_service;
                var selected_appointment_date = vm.appointment_formdata.appointment_booked_date;
                vm.appointment_formdata.appointment_booked_time = '' ;
                if(service_id != '' &&  selected_appointment_date != '') {
                    <?php 
                        do_action('bookingpress_after_selecting_service_at_backend');
                    ?>
                    var postData = { action:'bookingpress_set_appointment_time_slot', service_id: 
                    service_id,selected_date:selected_appointment_date ,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data != undefined || response.data != [])
                        {                                
                            vm.appointment_time_slot = response.data;                            
                        }
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });                    
                } else {
                    if(service_id == '' || service_id == undefined || service_id == 'undefined'){
                        vm.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Please select service to get available date and time slots.', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    }
                    vm.appointment_time_slot = '';
                }    
            }, 
            bookingpress_dashboard_redirect_filter(dashboard_filter,module,status=''){                                                                
                const vm = this;
                var bookingpress_redirect_url;                
                if(module == 'appointment' ){                                        
                    sessionStorage.setItem("bookingpress_dashboard_filter_appointment_status",status);                
                    bookingpress_redirect_url = "<?php echo  add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_appointments), esc_url(admin_url() . 'admin.php?page=bookingpress')) // phpcs:ignore ?>" 
                } else if(module == 'customer'){
                    bookingpress_redirect_url ="<?php echo  add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_customers), esc_url(admin_url() . 'admin.php?page=bookingpress')) // phpcs:ignore ?>"                                        
                } else if(module == 'payment') {
                    sessionStorage.setItem("bookingpress_dashboard_filter_payment_status",status);                
                    bookingpress_redirect_url ="<?php echo  add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_payments), esc_url(admin_url() . 'admin.php?page=bookingpress')) // phpcs:ignore ?>"    
                }
                <?php
                    do_action('bookingpress_dashboard_redirect_filter');
                ?>
                if(module != '') {
                    var postData = { action:'bookingpress_set_dashboard_redirect_filter', selected_filter: dashboard_filter
                    ,custom_filter_val:vm.custom_filter_val,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) { 
                        if(response.data.variant == 'success' && response.data.bookingress_start_date != ''&& response.data.bookingress_end_date != '' ){
                            sessionStorage.setItem("bookingpress_module_type",module);                                    
                            sessionStorage.setItem("bookingpress_dashboard_filter_start_date",response.data.bookingress_start_date);                                                               
                            sessionStorage.setItem("bookingpress_dashboard_filter_end_date",response.data.bookingress_end_date);                                    
                            window.location.href = bookingpress_redirect_url; 
                        }
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });           
                }                     
            },  
            bookingpress_get_customer_list(query){
                const vm = new Vue()
                const vm2 = this	
                if (query !== '') {
                    vm2.bookingpress_loading = true;                    
                    var customer_action = { action:'bookingpress_get_customer_list',search_user_str:query,customer_id:vm2.customer_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }                    
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                    .then(function(response){
                        vm2.bookingpress_loading = false;
                        vm2.appointment_customers_list = response.data.appointment_customers_details
                    }).catch(function(error){
                        console.log(error)
                    });
                } else {
                    vm2.appointment_customers_list = [];
                }	
            },
            bookingpress_change_date_format(selected_date){
                const vm = this
                var bookingpress_modified_date = vm.get_formatted_date(selected_date);
                vm.appointment_formdata.appointment_booked_date = bookingpress_modified_date
                vm.bookingpress_set_time_slot();
            },      
            bookingpress_full_row_clickable(row, $el, events){
                const vm = this;
                <?php do_action('bookingpress_appointment_full_row_clickable'); ?>
                vm.$refs.multipleTable.toggleRowExpansion(row);
            },
            bookingpress_row_expand(row, expanded){
                const vm = this
                if(vm.bookingpress_previous_row_obj != ''){
                    vm.$refs.multipleTable.toggleRowExpansion(vm.bookingpress_previous_row_obj, false);
                    if(vm.bookingpress_previous_row_obj != row){
                        vm.$refs.multipleTable.toggleRowExpansion(vm.bookingpress_previous_row_obj);
                        vm.bookingpress_previous_row_obj = row;
                    }else{
                        if(expanded.length == undefined){
                            vm.$refs.multipleTable.toggleRowExpansion(row);
                        }
                        vm.bookingpress_previous_row_obj = '';
                    }
                }else{
                    if(expanded.length == undefined){
                        vm.$refs.multipleTable.toggleRowExpansion(row);
                    }
                    vm.bookingpress_previous_row_obj = row;
                }
            },
            bookingpress_filter_focus(event){
                const vm = this
                if(vm.currently_selected_filter == 'custom'){
                    vm.currently_selected_filter = 'week';
                    vm.custom_filter_val = '';
                    vm.select_dashboard_filter()
                }
            },
            bookingpress_set_time(event,time_slot_data) {
                const vm = this
                if(event != '' && time_slot_data != '') {
                    for (let x in time_slot_data) {                      
                        var slot_data_arr = time_slot_data[x];
                        for(let y in slot_data_arr) {
                            var time_slot_data_arr = slot_data_arr[y];
                            for(let m in time_slot_data_arr) {                            
                                var data_arr  = time_slot_data_arr[m];
                                if(data_arr.store_start_time != undefined && data_arr.store_end_time != undefined && data_arr.store_start_time == event) {   
                                    vm.appointment_formdata.appointment_booked_end_time = data_arr.store_end_time;
                                    <?php do_action('bookingpress_admin_add_appointment_after_select_timeslot'); ?>
                                }
                            }                                                    
                        }                      
                    }
                }
            },
            <?php
	       do_action('bookingpress_dashboard_modify_dynamic_vue_methods');
        }
        
        /**
         * Ajax request for redirect from dashboard
         *
         * @return void
         */
        function bookingpress_set_dashboard_redirect_filter_func(){
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers;

            $bpa_check_authorization = $this->bpa_check_authentication( 'set_dashboard_redirection', true, 'bpa_wp_nonce' );
            
            $response            = array();
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $return_data = array(                
                'variant' => 'error',
                'title'  => esc_html__('Error', 'bookingpress-appointment-booking'),
                'msg'    => esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking'),
                'bookingress_start_date' => '',
                'bookingress_end_date' => '',
            );
            $bookingpress_end_date = $bookingpress_start_date = '';
            $selected_filter_val       = ! empty($_POST['selected_filter']) ? sanitize_text_field($_POST['selected_filter']) : 'week'; // phpcs:ignore WordPress.Security.NonceVerification
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
                $bookingpress_start_date  = ! empty($_POST['custom_filter_val'][0]) ? sanitize_text_field($_POST['custom_filter_val'][0]) : date('Y-m-d'); // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_end_date    = ! empty($_POST['custom_filter_val'][1]) ? sanitize_text_field($_POST['custom_filter_val'][1]) : date('Y-m-d'); // phpcs:ignore WordPress.Security.NonceVerification
            }
            if(!empty($bookingpress_start_date) && !empty($bookingpress_end_date) ) {
                $return_data = array(
                    'variant' => 'success',
                    'bookingress_start_date' => $bookingpress_start_date,
                    'bookingress_end_date' => $bookingpress_end_date,
                );  
            }              
            echo wp_json_encode($return_data);
            exit();
        }
    }
}

global $bookingpress_dashboard;
$bookingpress_dashboard = new bookingpress_dashboard();

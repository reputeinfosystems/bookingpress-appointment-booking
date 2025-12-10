<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_calendar') ) {
    class bookingpress_calendar Extends BookingPress_Core
    {
        function __construct()
        {
            add_action('bookingpress_calendar_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_calendar_view_func' ));
            add_action('bookingpress_calendar_dynamic_data_fields', array( $this, 'bookingpress_calendar_dynamic_data_fields_func' ));
            add_action('bookingpress_calendar_dynamic_helper_vars', array( $this, 'bookingpress_calendar_dynamic_helper_vars_func' ));
            add_action('bookingpress_calendar_dynamic_on_load_methods', array( $this, 'bookingpress_calendar_dynamic_on_load_methods_func' ));
            add_action('bookingpress_calendar_dynamic_vue_methods', array( $this, 'bookingpress_calendar_dynamic_vue_methods_func' ));
            add_action('bookingpress_calendar_dynamic_components', array( $this, 'bookingpress_calendar_dynamic_components_func' ));

            add_action('wp_ajax_bookingpress_save_appointment_booking', array( $this, 'bookingpress_save_appointment_booking_func' ), 10);
            add_action('wp_ajax_bookingpress_get_bookings_details', array( $this, 'bookingpress_get_bookings_details_func' ));
            add_action('wp_ajax_bookingpress_get_edit_appointment_data', array( $this, 'bookingpress_get_edit_appointment_data_func' ), 10);

            add_action('wp_ajax_bookingpress_set_appointment_time_slot', array( $this, 'bookingpress_set_appointment_time_slot_func' ), 10);

            add_action('admin_init', array( $this, 'bookingpress_calendar_vue_data_fields') );
            add_action('wp_ajax_bookingpress_get_search_customer_list',array($this,'bookingpress_get_search_customer_list_func'));
            add_action('wp_ajax_bookingpress_get_customer_list',array($this,'bookingpress_get_customer_list_func'));

            add_action('wp_ajax_bookingpress_get_popover_appointment_data', array( $this, 'bookingpress_get_popover_appointment_data_func' ), 10);

            add_action( 'bookingpress_after_update_appointment_service', array( $this, 'bookingpress_update_payment_log_price'), 12, 2 );
            
        }

        function bookingpress_update_payment_log_price( $bookingpress_update_id, $bookingpress_appointment_selected_services ){
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_services;

            if (! function_exists('is_plugin_active') ) {
                include ABSPATH . '/wp-admin/includes/plugin.php';
            }

            if( is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
                return;
            }

            if( empty( $bookingpress_update_id ) || empty( $bookingpress_appointment_selected_services ) ){
                return;
            }

            $payment_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_payment_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_update_id ) ); //phpcs:ignore

            if( empty( $payment_id ) ){
                return;
            }

            $bpa_service_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $bookingpress_appointment_selected_services ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
            if( empty( $bpa_service_data ) ){
                return;
            }

            $bpa_service_name = $bpa_service_data->bookingpress_service_name;
            $bpa_service_price = $bpa_service_data->bookingpress_service_price;
            $bpa_service_duration = $bpa_service_data->bookingpress_service_duration_val;
            $bpa_service_duration_unit = $bpa_service_data->bookingpress_service_duration_unit;
            
            $wpdb->update(
                $tbl_bookingpress_payment_logs,
                array(
                    'bookingpress_service_id' => $bookingpress_appointment_selected_services,
                    'bookingpress_service_name' => $bpa_service_name,
                    'bookingpress_service_price' => $bpa_service_price,
                    'bookingpress_service_duration_val' => $bpa_service_duration,
                    'bookingpress_service_duration_unit' => $bpa_service_duration_unit,
                    'bookingpress_payment_amount' => $bpa_service_price,
                    'bookingpress_paid_amount' => $bpa_service_price
                ),
                array(
                    'bookingpress_payment_log_id' => $payment_id
                )
            );

        }

        function bookingpress_get_popover_appointment_data_func(){
            global $wpdb,$BookingPress,$tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers, $tbl_bookingpress_services, $bookingpress_global_options;
            $response = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_calendar_appointments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $posted_data = array_map( array( $BookingPress, 'appointment_sanatize_field'), $_POST );  // phpcs:ignore WordPress.Security.NonceVerification

            $appointment_data = array();
            if (! empty($posted_data['bookingpress_sel_appointment_date']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $appointment_sel_data = $posted_data['bookingpress_sel_appointment_date'];
                $appointment_sel_end_date = (!empty( $posted_data['bookingpress_sel_appointment_end_date'] ) && '0000-00-00' != $posted_data['bookingpress_sel_appointment_end_date'] ) ? $posted_data['bookingpress_sel_appointment_end_date'] : $appointment_sel_data;
                $where_query = '';
                $slot_start_time = isset($posted_data['bookingpress_appointment_start_time']) ? $posted_data['bookingpress_appointment_start_time'] : '';
                $slot_end_time = isset($posted_data['bookingpress_appointment_end_time']) ? $posted_data['bookingpress_appointment_end_time'] : '';
                $cal_activeView  = isset($posted_data['activeView']) ? $posted_data['activeView'] : 'month';
                if($cal_activeView=='week' || $cal_activeView=='day') {
                    if( $BookingPress->bpa_is_pro_active() ){
                        $where_query = $wpdb->prepare(
                            "AND( CAST(CONCAT(bookingpress_appointment_date, ' ', bookingpress_appointment_time) AS DATETIME) >= %s AND CAST(CONCAT(bookingpress_appointment_end_date, ' ',  bookingpress_appointment_end_time) AS DATETIME) <= %s )", $appointment_sel_data.' '.$slot_start_time, $appointment_sel_end_date .' '. $slot_end_time
                        );
                    } else {
                        if($slot_end_time=='00:00:00'){
                            $slot_end_time = '24:00:00';
                        }
                        $where_query = $wpdb->prepare( ' AND (bookingpress_appointment_time >= %s AND bookingpress_appointment_end_time <= %s)', $slot_start_time, $slot_end_time );
                    }
                }
                
                $appointment_query_dynamic_arr= array();
                $appointment_query_dynamic_arr = apply_filters( 'bookingpress_modify_popover_appointment_data_query', $appointment_query_dynamic_arr, $posted_data );

                $appointment_columns = isset($appointment_query_dynamic_arr['appointment_columns']) ? $appointment_query_dynamic_arr['appointment_columns'] : '';
                $where_query .= isset($appointment_query_dynamic_arr['where_query']) ? $appointment_query_dynamic_arr['where_query'] : '';

                /* if( $BookingPress->bpa_is_pro_active() ){
                    $where_query .= $wpdb->prepare( ' AND bookingpress_appointment_end_date = %s ', $appointment_sel_end_date );
                } */

                $total_booked_appiontments_data = $wpdb->get_results( $wpdb->prepare( "SELECT services.bookingpress_service_name, cust.bookingpress_user_name, cust.bookingpress_user_firstname, cust.bookingpress_user_lastname, cust.bookingpress_user_email, cust.bookingpress_user_phone, cust.bookingpress_user_country_dial_code, appointment.bookingpress_appointment_time, appointment.bookingpress_appointment_end_time, appointment.bookingpress_appointment_booking_id, appointment.bookingpress_booking_id, appointment.bookingpress_appointment_date, appointment.bookingpress_appointment_end_date, appointment.bookingpress_appointment_status,appointment.bookingpress_customer_firstname, appointment.bookingpress_customer_lastname, appointment.bookingpress_customer_name, appointment.bookingpress_username {$appointment_columns} FROM {$tbl_bookingpress_appointment_bookings} appointment JOIN {$tbl_bookingpress_customers} cust ON appointment.bookingpress_customer_id=cust.bookingpress_customer_id JOIN {$tbl_bookingpress_services} services ON appointment.bookingpress_service_id=services.bookingpress_service_id WHERE appointment.bookingpress_appointment_date=%s AND (appointment.bookingpress_appointment_status='1' OR appointment.bookingpress_appointment_status='2' ) $where_query ORDER BY appointment.bookingpress_appointment_date ASC, appointment.bookingpress_appointment_time ASC", $appointment_sel_data), ARRAY_A ); //phpcs:ignore
                

                $global_data = $bookingpress_global_options->bookingpress_global_options();
                $default_date_format = $global_data['wp_default_date_format'];
                $updated_total_booked_appointment_data = [];
                if(!empty($total_booked_appiontments_data)) {
                    
                    foreach($total_booked_appiontments_data as $index=> $appointment_data) {
                        $appointment_start_date = $appointment_data['bookingpress_appointment_date'];
                        $appointment_end_date = $appointment_data['bookingpress_appointment_end_date'];
                        if( empty( $appointment_end_date ) || '0000-00-00' == $appointment_end_date || ('00:00:00' === $appointment_data['bookingpress_appointment_end_time'] && '00:00:00' != $posted_data['bookingpress_appointment_end_time'] && 'day' != $posted_data['activeView'] && 'mobile' != $posted_data['screen_size'] ) ){
                            $appointment_end_date = $appointment_data['bookingpress_appointment_date'];
                        }
                        
                        if( strtotime($appointment_start_date) >  strtotime($appointment_sel_end_date) && strtotime($appointment_sel_end_date) > strtotime($appointment_end_date)   ){
                            continue;
                        }

                        $total_booked_appiontments_data[$index]['bookingpress_service_name'] = isset($appointment_data['bookingpress_service_name']) ? stripslashes_deep($appointment_data['bookingpress_service_name']) : '';;
                        $formated_date = date($default_date_format,strtotime($appointment_data['bookingpress_appointment_date']));
                        $formated_end_date = ( !empty( $appointment_end_date ) ) ? date($default_date_format, strtotime( $appointment_end_date ) ) : $formated_date;
                        $selected_end_time = $appointment_data['bookingpress_appointment_end_time'];
                        $start_time = date($global_data['wp_default_time_format'], strtotime($appointment_data['bookingpress_appointment_time']));
                        /* if($selected_end_time == "00:00:00"){
                            $selected_end_time = "23:59:59";
                        } */
                        
                        if(isset($total_booked_appiontments_data[$index]['bookingpress_user_phone']) && !empty($total_booked_appiontments_data[$index]['bookingpress_user_phone']) && isset($total_booked_appiontments_data[$index]['bookingpress_user_country_dial_code']) && !empty($total_booked_appiontments_data[$index]['bookingpress_user_country_dial_code'])){
                            $total_booked_appiontments_data[$index]['bookingpress_user_phone'] = '+'.$total_booked_appiontments_data[$index]['bookingpress_user_country_dial_code'].' '.$total_booked_appiontments_data[$index]['bookingpress_user_phone'];
                        }

                        $end_time = date($global_data['wp_default_time_format'], strtotime($selected_end_time));
                        $total_booked_appiontments_data[$index]['bookingpress_appointment_time'] = $start_time;
                        $total_booked_appiontments_data[$index]['bookingpress_appointment_end_time'] = $end_time;
                        $total_booked_appiontments_data[$index]['bookingpress_appointment_date'] = $formated_date;
                        $total_booked_appiontments_data[$index]['bookingpress_appointment_end_date'] = $formated_end_date;
                        $bookingpress_cust_fnm = isset($appointment_data['bookingpress_customer_firstname']) ? stripslashes_deep($appointment_data['bookingpress_customer_firstname']) : '';
                        $bookingpress_cust_lnm = isset($appointment_data['bookingpress_customer_lastname']) ? stripslashes_deep($appointment_data['bookingpress_customer_lastname']) : '';
                        $bookingpress_cust_fullnm = isset($appointment_data['bookingpress_customer_name']) ? $appointment_data['bookingpress_customer_name'] : '';
                        $bookingpress_cust_unm = isset($appointment_data['bookingpress_username']) ? $appointment_data['bookingpress_username'] : '';
                        $bookingpress_display_name = '';
                        if(!empty($bookingpress_cust_fnm) || !empty($bookingpress_cust_lnm)) {
                            $bookingpress_display_name = !empty($bookingpress_cust_fnm) ? $bookingpress_cust_fnm : '';
                            $bookingpress_display_name .= !empty($bookingpress_display_name) ? ' ' : '';
                            $bookingpress_display_name .= !empty($bookingpress_cust_lnm) ? $bookingpress_cust_lnm : '';
                        } else if(!empty($bookingpress_cust_fullnm) && empty($bookingpress_display_name)){
                            $bookingpress_display_name = $bookingpress_cust_fullnm;
                        } else if(!empty($bookingpress_cust_unm) && empty($bookingpress_display_name)){
                            $bookingpress_display_name = $bookingpress_cust_unm;
                        }
                        $total_booked_appiontments_data[$index]['bookingpress_user_displayname'] = $bookingpress_display_name;
                        $bookingpress_booking_date = date('Y-m-d', strtotime($appointment_data['bookingpress_appointment_date']));                
                        $total_booked_appiontments_data[$index]['bookingpress_app_is_past'] = ( current_time('timestamp') > strtotime( $bookingpress_booking_date . ' ' . $appointment_data['bookingpress_appointment_time']) ) ;

                        $updated_total_booked_appointment_data[ $index ] = $total_booked_appiontments_data[ $index ];
                    }
                }
                $appointment_data['bookingpress_total_popover_appointemnt'] = count($updated_total_booked_appointment_data);
                $appointment_data['bookingpress_popover_appointemnt_data'] = $updated_total_booked_appointment_data;
                $appointment_data = apply_filters('bookingpress_modify_popover_appointment_data', $appointment_data);
            }
            echo wp_json_encode($appointment_data);
            exit();
        }

        /**
         * Load default data variables for calendar module
         *
         * @return void
         */
        function bookingpress_calendar_vue_data_fields(){
            global $bookingpress_calendar_vue_data_fields, $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_appointment_status_arr    = $bookingpress_options['appointment_status'];
                       
            $bookingpress_appointment_status_array = $bookingpress_appointment_status_arr;

            $bookingpress_calendar_vue_data_fields = array(
                'bulk_action'                     => 'bulk_action',
                'calendar_val'                    => '',
                'custom_filter_date_val'          => date('Y-m-d', current_time('timestamp')),
                'bpa_today_btn'                   => 0,
                'appointment_customers_list'      => array(),
                'appointment_staff_members_list'  => array(),
                'appointment_services_data'       => array(),
                'appointment_services_list'       => array(),
                'appointment_time_slot'           => array(),
                'appointment_status'              => $bookingpress_appointment_status_array,
                'rules'                           => array(
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
                'appointment_formdata'            => array(
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
                    '_wpnonce'                          => '',
                ),
                'open_calendar_appointment_modal' => false,
                'open_calendar_appointment_popover' => false,
                'bpa_calendar_event_delay' => 500,
                'bpa_calendar_event_clicks' => 0,
                'bpa_calendar_event_timer' => null,
                'bpa_calendar_event_click_timestamp' => '',
                'bpa_display_calendar_popover_loader' => '1',
                'bpa_calendar_popover_target'       => '',
                'bpa_calendar_popover_more_label'   => false,
                'bpa_calendar_popover_mask'         => false,
                'bpa_calendar_dialog_custom_cls'    => 'bpa-fsc-item__popover-card',
                'bpa_is_disable_approve_btn'      => false,
                'calendar_events_data'            => array(),
                'calendar_current_date'           => date('Y-m-d', current_time('timestamp')),
                'show_all_day_events'             => true,
                'search_customer_list'            => '',
                'search_status'                   => $bookingpress_appointment_status_array,
                'search_data'                     => array(
                    'selected_services'  => array(),
                    'selected_customers' => array(),
                    'selected_status'    => '',
                    'search_appointment_date' => '',
                ),
                'activeView'                      => 'month',
                'minEventWidth'                   => 0,
                'timeCellHeight'                  => 84,
                'is_display_save_loader'          => '0',
                'is_disabled'                     => false,
                'is_calendar_popover_approve_loader' => '0',
            );

            $week_day_array = array(0,6,5,4,3,2,1);
            $start_of_week = get_option( 'start_of_week' );
            if($start_of_week == ""){
                $start_of_week = 1;
            }
            $bookingpress_calendar_week_start = $week_day_array[$start_of_week];
            $bookingpress_calendar_vue_data_fields['bookingpress_calendar_week_start'] = $bookingpress_calendar_week_start;
            
        }
        
        /**
         * Add dynamic component for calendar module
         *
         * @return void
         */
        function bookingpress_calendar_dynamic_components_func()
        {
            ?>
                'vue-cal': vuecal
            <?php
        }
        
        /**
         * Ajax request for get edit appointment data
         *
         * @return void
         */
        function bookingpress_get_edit_appointment_data_func()
        {
            global $wpdb,$BookingPress,$tbl_bookingpress_appointment_bookings;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_calendar_appointments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $appointment_data = array();
            if (! empty($_POST['appointment_id']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $appointment_id                = intval($_POST['appointment_id']); // phpcs:ignore WordPress.Security.NonceVerification
                $appointment_data              = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                $bookingpress_service_id       = ! empty($appointment_data['bookingpress_service_id']) ? intval($appointment_data['bookingpress_service_id']) : '';
                $bookingpress_appointment_date = ! empty($appointment_data['bookingpress_appointment_date']) ? $appointment_data['bookingpress_appointment_date'] : '';
                $appointment_data['bookingpress_appointment_internal_note'] = ! empty($appointment_data['bookingpress_appointment_internal_note']) ? stripslashes_deep($appointment_data['bookingpress_appointment_internal_note']) : '';
                if (! empty($appointment_data['bookingpress_appointment_time']) ) {
                    if (! empty($appointment_data['bookingpress_service_duration_val']) && ! empty($appointment_data['bookingpress_service_duration_unit']) ) {
                        $service_time_duration      = esc_html($appointment_data['bookingpress_service_duration_val']);
                        $service_time_duration_unit = esc_html($appointment_data['bookingpress_service_duration_unit']);
                        if ($service_time_duration_unit == 'h' ) {
                            $service_time_duration = $service_time_duration * 60;
                        }
                        $service_step_duration_val = $service_time_duration;
						$bookingpress_appointment_start_time               = date( 'H:i', strtotime( $appointment_data['bookingpress_appointment_time'] ) );
						$appointment_data['bookingpress_appointment_time'] = $bookingpress_appointment_start_time;
                    }
                }
                if (! empty($bookingpress_service_id) && ! empty($bookingpress_appointment_date) ) {
                    $appointment_time_slot = $BookingPress->bookingpress_get_service_available_time($bookingpress_service_id, $bookingpress_appointment_date);

                    $appointment_data['appointment_time_slot'] = $BookingPress->bookingpress_get_daily_timeslots($appointment_time_slot);
                }
                $bookingpress_customer_id = !empty($appointment_data['bookingpress_customer_id'])  ? intval($appointment_data['bookingpress_customer_id']) : 0 ;
                $bookingpress_customer_selection_details = array();
                if(!empty($bookingpress_customer_id)) {
                    $bookingpress_customer_selection_details = $BookingPress->bookingpress_get_appointment_customer_list('',$bookingpress_customer_id);
                }                
                $appointment_data['appointment_customer_list'] = $bookingpress_customer_selection_details;                
            }

            $appointment_data = apply_filters('bookingpress_modify_edit_appointment_data', $appointment_data);

            echo wp_json_encode($appointment_data);
            exit();
        }
        
        /**
         * Ajax request for get booking details
         *
         * @return void
         */
        function bookingpress_get_bookings_details_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_calendar_appointments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){    
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $calendar_week_number = date('W', current_time('timestamp'));
            $calendar_year        = date('Y', current_time('timestamp'));
            $month_details        = $BookingPress->get_monthstart_date_end_date();
            $start_date           = $month_details['start_date'];
            $end_date             = $month_details['end_date'];

            $search_query = '';
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['search_data'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
            $search_data = ! empty($_REQUEST['search_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data']) : array(); // phpcs:ignore
            $activeView = ! empty($_REQUEST['activeView']) ? sanitize_text_field($_REQUEST['activeView']) : 'month'; // phpcs:ignore
            
            if (! empty($search_data) ) {
                $search_selected_services = ! empty($search_data['selected_services']) ? implode(',', $search_data['selected_services']) : '';

                $search_appointment_status = ! empty($search_data['selected_status']) ? $search_data['selected_status'] : '';
                if (! empty($search_appointment_status) && $search_appointment_status != 'all' ) {
                    $search_query .= $wpdb->prepare(" AND (bookingpress_appointment_status = %s)", $search_appointment_status);
                }

                $search_selected_date = !empty( $search_data['search_appointment_date']) ? $search_data['search_appointment_date'] : '';
                if (! empty($search_selected_date) ) {
                    $search_query .= $wpdb->prepare( " AND (bookingpress_appointment_date = %s)", $search_selected_date );
                }

                $search_query = apply_filters('bookingpress_calendar_add_view_filter', $search_query, $search_data);
            }

            $calendar_bookings_data = array();

            $bookings_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date {$search_query} AND (bookingpress_appointment_status='1' OR bookingpress_appointment_status='2') ORDER BY bookingpress_appointment_date ASC, bookingpress_appointment_time ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm

            foreach ( $bookings_data as $bookings_key => $bookings_val ) {

                $bookingpress_booking_date = date('Y-m-d', strtotime($bookings_val['bookingpress_appointment_date']));   
                $bookingpress_booking_end_date = (!empty( $bookings_val['bookingpress_appointment_end_date'] ) && '0000-00-00' != $bookings_val['bookingpress_appointment_end_date'] ) ? date('Y-m-d', strtotime( $bookings_val['bookingpress_appointment_end_date'] ) ) : $bookingpress_booking_date;
                $bookingpress_booking_end_date = $bookingpress_booking_date;             
                $service_start_time    = ! empty($bookings_val['bookingpress_appointment_time']) ? $bookings_val['bookingpress_appointment_time'] : '';
                $service_end_time      = ! empty($bookings_val['bookingpress_appointment_end_time']) ? $bookings_val['bookingpress_appointment_end_time'] : '';
                
                if($service_end_time == "00:00" || $service_end_time == "00:00:00"){
                    $service_end_time = "24:00";
                }


                if($service_end_time == "24:00:00" || $service_end_time == "24:00"){
                    $bookingpress_booking_end_date = date('Y-m-d', strtotime( $bookingpress_booking_date .  ' +1 day' ) );
                    $service_end_time = "00:00:01";
                }
         
                if($activeView=='month') {
                    $appointment_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id, bookingpress_appointment_date,bookingpress_appointment_time,bookingpress_appointment_end_time,( SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s AND ( bookingpress_appointment_status = '1' OR bookingpress_appointment_status = '2' ) {$search_query} ) as totalAppointment FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s AND (bookingpress_appointment_status='1' OR bookingpress_appointment_status='2') {$search_query} ORDER BY bookingpress_appointment_date ASC, bookingpress_appointment_time ASC", $bookings_val['bookingpress_appointment_date'] ,$bookings_val['bookingpress_appointment_date']), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally and $search_query is properly prepared. False Positive alarm                    
                }
                else if($activeView=='week' || $activeView=='day') {
                    $appointment_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id, bookingpress_appointment_date, bookingpress_appointment_time,bookingpress_appointment_end_time,( SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND (bookingpress_appointment_status='1' OR bookingpress_appointment_status='2') {$search_query} ) as totalAppointment FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND (bookingpress_appointment_status='1' OR bookingpress_appointment_status='2') {$search_query} ORDER BY bookingpress_appointment_date ASC, bookingpress_appointment_time ASC", $bookings_val['bookingpress_appointment_date'], $bookings_val['bookingpress_appointment_time'], $bookings_val['bookingpress_appointment_end_time'], $bookings_val['bookingpress_appointment_date'], $bookings_val['bookingpress_appointment_time'], $bookings_val['bookingpress_appointment_end_time'] ), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally and $search_query is properly prepared. False Positive alarm                    
                }
                $totalAppointmentCount=0;
                if(isset($appointment_data[0]['totalAppointment']) && $appointment_data[0]['totalAppointment']>2 && $activeView=='month') {
                    $totalAppointmentCount=$appointment_data[0]['totalAppointment']-2;
                } 
                if(isset($appointment_data[1]['totalAppointment']) && $appointment_data[1]['totalAppointment']>2 && ($activeView=='week' || $activeView=='day') && $bookings_val['bookingpress_appointment_booking_id'] == $appointment_data[0]['bookingpress_appointment_booking_id']) {
                    $totalAppointmentCount=$appointment_data[0]['totalAppointment']-2;
                } 
                if(isset($bookings_data[$bookings_key-2]) && $activeView=='month' && $bookings_data[$bookings_key-2]['bookingpress_appointment_date'] == $bookings_val['bookingpress_appointment_date']) {
                   continue;
                }
                if(isset($bookings_data[$bookings_key-2]) && ($activeView=='week' || $activeView=='day') && $bookings_data[$bookings_key-2]['bookingpress_appointment_date'] == $bookings_val['bookingpress_appointment_date'] && ($bookings_data[$bookings_key-2]['bookingpress_appointment_time'] == $bookings_val['bookingpress_appointment_time']) && ($bookings_data[$bookings_key-2]['bookingpress_appointment_end_time'] == $bookings_val['bookingpress_appointment_end_time'])) {
                    continue;
                }
                
                $bookingpress_appointment_status = $bookings_val['bookingpress_appointment_status'];
                $bookingpress_appointment_class  = 'bpa-cal-event-card';
                if ($bookingpress_appointment_status == '1' ) {
                    $bookingpress_appointment_class .= ' bpa-cal-event-card--approved';
                } elseif ($bookingpress_appointment_status == '2' ) {
                    $bookingpress_appointment_class .= ' bpa-cal-event-card--pending';
                } elseif ($bookingpress_appointment_status == '3' ) {
                    $bookingpress_appointment_class .= ' bpa-cal-event-card--cancelled';
                } elseif ($bookingpress_appointment_status == '4' ) {
                    $bookingpress_appointment_class .= ' bpa-cal-event-card--cancelled';
                }

                $bookingpress_appointment_class = apply_filters('bookingpress_modify_calendar_appointment_class', $bookingpress_appointment_class, $bookingpress_appointment_status);

                $bookingpress_event_slot_title = '';
                $bookingpress_cust_fnm = isset($bookings_val['bookingpress_customer_firstname']) ? stripslashes_deep($bookings_val['bookingpress_customer_firstname']) : '';
                $bookingpress_cust_lnm = isset($bookings_val['bookingpress_customer_lastname']) ? stripslashes_deep($bookings_val['bookingpress_customer_lastname']) : '';
                $bookingpress_cust_fullnm = isset($bookings_val['bookingpress_customer_name']) ? stripslashes_deep($bookings_val['bookingpress_customer_name']) : '';
                $bookingpress_cust_unm = isset($bookings_val['bookingpress_username']) ? stripslashes_deep($bookings_val['bookingpress_username']) : '';
                $bookingpress_cust_email = isset($bookings_val['bookingpress_customer_email']) ? $bookings_val['bookingpress_customer_email'] : '';
                $bookingpress_cust_phone = isset($bookings_val['bookingpress_customer_phone'])  ? $bookings_val['bookingpress_customer_phone'] : '';

                $bookingpress_customer_phone_dial_code = !empty($bookings_val['bookingpress_customer_phone_dial_code']) ? $bookings_val['bookingpress_customer_phone_dial_code'] : '';
                
                
                if(!empty($bookingpress_customer_phone_dial_code) && !empty($bookingpress_cust_phone)){
                    $bookingpress_cust_phone = '+'.$bookingpress_customer_phone_dial_code.' '.$bookingpress_cust_phone;
                    $bookings_val['bookingpress_customer_phone'] = $bookingpress_cust_phone;
                }

                if(!empty($bookingpress_cust_fnm) || !empty($bookingpress_cust_lnm)) {
                    $bookingpress_event_slot_title = !empty($bookingpress_cust_fnm) ? $bookingpress_cust_fnm : '';
                    $bookingpress_event_slot_title .= !empty($bookingpress_event_slot_title) ? ' ' : '';
                    $bookingpress_event_slot_title .= !empty($bookingpress_cust_lnm) ? $bookingpress_cust_lnm : '';
                } else if(!empty($bookingpress_cust_fullnm) && empty($bookingpress_event_slot_title)){
                    $bookingpress_event_slot_title = $bookingpress_cust_fullnm;
                } else if(!empty($bookingpress_cust_unm) && empty($bookingpress_event_slot_title)){
                    $bookingpress_event_slot_title = $bookingpress_cust_unm;
                } else if(!empty($bookingpress_cust_email) && empty($bookingpress_event_slot_title)){
                    $bookingpress_event_slot_title = $bookingpress_cust_email;
                } else if(!empty($bookingpress_cust_phone) && empty($bookingpress_event_slot_title)){
                    $bookingpress_event_slot_title = $bookingpress_cust_phone;
                } else {
                    $bookingpress_event_slot_title = stripslashes_deep($bookings_val['bookingpress_service_name']);
                }

                $calendar_bookings_data[] = array(
                    'start'          => $bookingpress_booking_date . ' ' . $service_start_time,
                    'end'            => $bookingpress_booking_end_date . ' ' . $service_end_time,
                    'title'          => $bookingpress_event_slot_title,
                    'class'          => $bookingpress_appointment_class,
                    'appointment_id' => intval($bookings_val['bookingpress_appointment_booking_id']),
                    'service_id'     => intval($bookings_val['bookingpress_service_id']),
                    'is_cancelled'   => ( $bookingpress_appointment_status == '3' || $bookingpress_appointment_status == '4' ) ? 1 : 0,
                    'is_past_time'   => ( current_time('timestamp') > strtotime( $bookingpress_booking_date . ' ' . $service_start_time ) ),
                    'totalEvents_onsameslot' => $totalAppointmentCount,
                );

                $calendar_bookings_data = apply_filters('bookingpress_modify_calendar_appointment_details', $calendar_bookings_data, $bookings_val);
            }

            $calendar_bookings_data = apply_filters('bookingpress_modify_calendar_all_appointment_details', $calendar_bookings_data, $_REQUEST);

            echo wp_json_encode($calendar_bookings_data);
            exit();
        }
        
        /**
         * Ajax request for save add or update appointment data
         *
         * @return void
         */
        function bookingpress_save_appointment_booking_func($validate = false)
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_entries, $bookingpress_payment_gateways, $tbl_bookingpress_payment_logs, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications,$bookingpress_debug_payment_log_id, $bookingpress_other_debug_log_id;
            $response              = array();

            if( !empty( $_POST['appointment_data'] ) && !is_array( $_POST['appointment_data'] ) ){ //phpcs:ignore
				$_POST['appointment_data'] = json_decode( stripslashes_deep( $_POST['appointment_data'] ), true ); //phpcs:ignore
				$_REQUEST['appointment_data'] = $_POST['appointment_data'] =  !empty($_POST['appointment_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data'] ) : array(); //phpcs:ignore
			}

            $bpa_check_authorization = $this->bpa_check_authentication( 'add_calendar_appointments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

            do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend add/update appointment posted data', 'bookingpress_admin_add_update_appointment', $_POST, $bookingpress_other_debug_log_id ); // phpcs:ignore WordPress.Security.NonceVerification

            if (! empty($_REQUEST) && ! empty($_REQUEST['appointment_data']) ) { // phpcs:ignore WordPress.Security.NonceVerification
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['search_data'] contains mixed array and it's been sanitized properly further
                $bookingpress_appointment_data = ! empty($_REQUEST['appointment_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['appointment_data']) : array(); // phpcs:ignore
                $bookingpress_appointment_selected_customer    = ! empty($bookingpress_appointment_data['appointment_selected_customer']) ? sanitize_text_field($bookingpress_appointment_data['appointment_selected_customer']) : '';
                $bookingpress_appointment_selected_services    = ! empty($bookingpress_appointment_data['appointment_selected_service']) ? sanitize_text_field($bookingpress_appointment_data['appointment_selected_service']) : '';
                $bookingpress_appointment_booked_date          = ! empty($bookingpress_appointment_data['appointment_booked_date']) ? sanitize_text_field($bookingpress_appointment_data['appointment_booked_date']) : '';
                if(!empty($bookingpress_appointment_booked_date)){
                    $bookingpress_appointment_booked_date = date('Y-m-d', strtotime($bookingpress_appointment_booked_date));
                }
                $bookingpress_appointment_booked_end_date = !empty( $bookingpress_appointment_data['appointment_booked_end_date'] ) ? sanitize_text_field( $bookingpress_appointment_data['appointment_booked_end_date'] ) : $bookingpress_appointment_booked_date;
                $bookingpress_appointment_booked_time          = ! empty($bookingpress_appointment_data['appointment_booked_time']) ? sanitize_text_field($bookingpress_appointment_data['appointment_booked_time']) : '';
                $bookingpress_appointment_end_time          = ! empty($bookingpress_appointment_data['appointment_booked_end_time']) ? sanitize_text_field($bookingpress_appointment_data['appointment_booked_end_time']) : '';
                $bookingpress_appointment_internal_note        = ! empty($bookingpress_appointment_data['appointment_internal_note']) ? trim(sanitize_textarea_field($bookingpress_appointment_data['appointment_internal_note'])) : '';
                $bookingpress_appointment_is_send_notification = ( sanitize_text_field($bookingpress_appointment_data['appointment_send_notification']) == 'true' ) ? 0 : 1;
                $bookingpress_appointment_is_cusomize_timing = (isset($bookingpress_appointment_data['appointment_custom_timing']) && sanitize_text_field($bookingpress_appointment_data['appointment_custom_timing']) == 'true' ) ? 1 : 0;
                $bookingpress_appointment_status               = ! empty($bookingpress_appointment_data['appointment_status']) ? sanitize_text_field($bookingpress_appointment_data['appointment_status']) : '1';

                $bookingpress_update_id = ! empty($bookingpress_appointment_data['appointment_update_id']) ? $bookingpress_appointment_data['appointment_update_id'] : '';

                if($validate){
                    if($bookingpress_appointment_is_cusomize_timing){
                        $validate_response = array();
                        $validate_response['variant'] = 'success';
                        $validate_response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                        $validate_response['msg']     = esc_html__('Appointment validated successfully..', 'bookingpress-appointment-booking');

                        $bookingpress_check_custom_time_validation = array();
                        $bookingpress_check_custom_time_validation = apply_filters( 'bookingpress_check_custom_time_validation',$bookingpress_check_custom_time_validation,$bookingpress_appointment_data);

                        if(!empty($bookingpress_check_custom_time_validation)){                                
                           echo wp_send_json($bookingpress_check_custom_time_validation); // phpcs:ignore
                           exit();                           
                        }

                        echo wp_json_encode($validate_response); // phpcs: ignore
                        exit();   
                    }
                }

                if($bookingpress_appointment_is_cusomize_timing){

                    $selected_service_duration_unit = (isset($bookingpress_appointment_data['selected_service_duration_unit']))?$bookingpress_appointment_data['selected_service_duration_unit']:'';
                    if($selected_service_duration_unit == 'd'){

                        $appointment_booked_date = (isset($bookingpress_appointment_data['appointment_booked_date']))?$bookingpress_appointment_data['appointment_booked_date']:'';
                        $appointment_booked_end_date = (isset($bookingpress_appointment_data['appointment_booked_end_date']))?$bookingpress_appointment_data['appointment_booked_end_date']:'';
                        $bookingpress_appointment_booked_end_date = $appointment_booked_end_date;

                        $date1 = new DateTime($appointment_booked_date);
                        $date2 = new DateTime($appointment_booked_end_date);                                                    
                        $interval = $date1->diff($date2);
                        $total_days_between_dates = $interval->days;
                        $total_days_between_dates = intval($total_days_between_dates) + 1;

                        $bookingpress_appointment_data['selected_service_duration'] = $total_days_between_dates;                            
                        $_REQUEST['appointment_data']['selected_service_duration'] = $total_days_between_dates;                       


                    }

                }

                if (! empty($bookingpress_appointment_selected_customer) && ! empty($bookingpress_appointment_selected_services) && ! empty($bookingpress_appointment_booked_date) && ! empty($bookingpress_appointment_booked_time) ) {
                    $customer_data     = $BookingPress->get_customer_details($bookingpress_appointment_selected_customer);
                    $customer_username = ! empty($customer_data['bookingpress_user_name']) ? ($customer_data['bookingpress_user_name']) : '';
                    
                    if( !empty($customer_data['bookingpress_user_firstname']) || !empty($customer_data['bookingpress_user_lastname'])){
                        $customer_username = $customer_data['bookingpress_user_firstname'].' '.$customer_data['bookingpress_user_lastname'];
                    }

                    if( !empty($customer_data['bookingpress_customer_full_name'])){
                        $customer_username = $customer_data['bookingpress_customer_full_name'];
                    }
                    $customer_phone    = ! empty($customer_data['bookingpress_user_phone']) ? esc_html($customer_data['bookingpress_user_phone']) : '';
                    $customer_firstname = ! empty($customer_data['bookingpress_user_firstname']) ? ($customer_data['bookingpress_user_firstname']) : '';
                    $customer_lastname  = ! empty($customer_data['bookingpress_user_lastname']) ? ($customer_data['bookingpress_user_lastname']) : '';
                    $customer_country = ! empty($customer_data['bookingpress_user_country_phone']) ? esc_html($customer_data['bookingpress_user_country_phone']) : '';
                    $customer_dial_code = !empty($customer_data['bookingpress_user_country_dial_code']) ? esc_html($customer_data['bookingpress_user_country_dial_code']) : '';
                    $customer_email   = ! empty($customer_data['bookingpress_user_email']) ? ($customer_data['bookingpress_user_email']) : '';
                    $service_data               = $BookingPress->get_service_by_id($bookingpress_appointment_selected_services);
                    $service_name               = ! empty($service_data['bookingpress_service_name']) ? ($service_data['bookingpress_service_name']) : '';
                    $service_amount             = ! empty($service_data['bookingpress_service_price']) ? (float) $service_data['bookingpress_service_price'] : 0;
                    
                    $service_duration_val = (isset($bookingpress_appointment_data['selected_service_duration']))?$bookingpress_appointment_data['selected_service_duration']:'';

                    if(empty($service_duration_val)){
                        $service_duration_val       = ! empty($service_data['bookingpress_service_duration_val']) ? esc_html($service_data['bookingpress_service_duration_val']) : '';
                    }
                    
                    $service_duration_unit      = ! empty($service_data['bookingpress_service_duration_unit']) ? esc_html($service_data['bookingpress_service_duration_unit']) : '';
                    $bookingpress_currency_name = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');

                    if(empty($bookingpress_appointment_end_time)) {
                        $bookingpress_appointment_end_time_arr = $BookingPress->bookingpress_get_service_end_time($bookingpress_appointment_selected_services, $bookingpress_appointment_booked_time, $service_duration_val, $service_duration_unit);
                        $bookingpress_appointment_end_time = !empty($bookingpress_appointment_end_time_arr) ? $bookingpress_appointment_end_time_arr['service_end_time'] : '';
                    }
                    
                    $is_appointment_already_booked = 0;
                    if (! empty($bookingpress_update_id) ) {                        
                        $appointment_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_update_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm

                        
                        $bookingpress_appointment_old_status = !empty( $appointment_details['bookingpress_appointment_status']) ? intval($appointment_details['bookingpress_appointment_status']) : '';
			
			            $bookingpress_old_appointment_service = !empty( $appointment_details['bookingpress_service_id'] ) ? intval( $appointment_details['bookingpress_service_id'] ) : 0;

                        if (($bookingpress_appointment_status == '1' || $bookingpress_appointment_status == '2' ) && !$bookingpress_appointment_is_cusomize_timing) {
                            $is_appointment_already_booked = $BookingPress->bookingpress_is_appointment_booked($bookingpress_appointment_selected_services, $bookingpress_appointment_booked_date, $bookingpress_appointment_booked_time, $bookingpress_appointment_end_time,$bookingpress_update_id);

                            $is_appointment_already_booked = apply_filters('bookingpress_check_edit_is_appointment_already_booked', $is_appointment_already_booked, $bookingpress_update_id);
                            
                            if ($is_appointment_already_booked > 0 ) {
                                do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend appointment already exists', 'bookingpress_admin_add_update_appointment', $is_appointment_already_booked, $bookingpress_other_debug_log_id );

                                $response['variant'] = 'error';
                                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                                $response['msg']     = esc_html__('Appointment already booked for this slot', 'bookingpress-appointment-booking');
                                echo wp_json_encode($response);
                                exit();
                            }                            
                        }

                        if($bookingpress_appointment_is_cusomize_timing) {

                            do_action('bookingpress_modified_appointment_data_for_backend_appointment_booking');

                            $customize_timeing_bookingpress_validation = apply_filters('bookingpress_customize_timeing_bookingpress_validation',array(),$bookingpress_appointment_selected_services,$bookingpress_appointment_booked_date,$bookingpress_appointment_booked_time, $bookingpress_appointment_end_time,0,true,$_REQUEST);

                            if(!empty($customize_timeing_bookingpress_validation)){                                
                                do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend appointment already exists', 'bookingpress_admin_add_update_appointment', $is_appointment_already_booked, $bookingpress_other_debug_log_id );
                                $response['variant'] = 'error';
                                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');

                                $message = esc_html__('Appointment already booked for this slot', 'bookingpress-appointment-booking');
                                if(isset($customize_timeing_bookingpress_validation['block_reason']) && !empty($customize_timeing_bookingpress_validation['block_reason'])){
                                    $message = $customize_timeing_bookingpress_validation['block_reason'];
                                }
                                $response['msg']     = $message;

                                echo wp_json_encode($response);
                                exit();
                            }

                        }                        

                        // get existing appointment data
                        do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend get existing appointment data', 'bookingpress_admin_add_update_appointment', $appointment_details, $bookingpress_other_debug_log_id );
                        if (! empty($appointment_details) ) {
                            $appointment_details['bookingpress_customer_id']                   = $bookingpress_appointment_selected_customer;
                            $appointment_details['bookingpress_service_id']                    = $bookingpress_appointment_selected_services;
                            $appointment_details['bookingpress_customer_name']                 = $customer_username;
                            $appointment_details['bookingpress_customer_phone']                = $customer_phone;
                            $appointment_details['bookingpress_customer_firstname']            = $customer_firstname;
                            $appointment_details['bookingpress_customer_lastname']             = $customer_lastname;
                            $appointment_details['bookingpress_customer_country']              = $customer_country;
                            $appointment_details['bookingpress_customer_email']                = $customer_email;
                            $appointment_details['bookingpress_customer_phone_dial_code']      = $customer_dial_code;
                            $appointment_details['bookingpress_service_name']                  = $service_name;
                            $appointment_details['bookingpress_service_price']                 = $service_amount;
                            $appointment_details['bookingpress_service_currency']              = $bookingpress_currency_name;
                            $appointment_details['bookingpress_service_duration_val']          = $service_duration_val;
                            $appointment_details['bookingpress_service_duration_unit']         = $service_duration_unit;
                            $appointment_details['bookingpress_appointment_date']              = $bookingpress_appointment_booked_date;
                            $appointment_details['bookingpress_appointment_end_date']          = $bookingpress_appointment_booked_end_date;
                            $appointment_details['bookingpress_appointment_time']              = $bookingpress_appointment_booked_time;
                            $appointment_details['bookingpress_appointment_end_time']          = $bookingpress_appointment_end_time;
                            $appointment_details['bookingpress_appointment_internal_note']     = $bookingpress_appointment_internal_note;
                            $appointment_details['bookingpress_appointment_send_notification'] = $bookingpress_appointment_is_send_notification;
                            $appointment_details['bookingpress_appointment_status']            = $bookingpress_appointment_status;
                            if(!empty($appointment_details['bookingpress_paid_amount'])){
                                $appointment_details['bookingpress_paid_amount']                   = $service_amount;
                            }
                            $appointment_details = apply_filters('bookingpress_modify_appointment_booking_fields', $appointment_details, $appointment_details, $bookingpress_appointment_data);

                            do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend modified existing appointment data', 'bookingpress_admin_add_update_appointment', $appointment_details, $bookingpress_other_debug_log_id );

                            $wpdb->update($tbl_bookingpress_appointment_bookings, $appointment_details, array( 'bookingpress_appointment_booking_id' => $bookingpress_update_id ));
                            
                            $wpdb->update($tbl_bookingpress_payment_logs, array('bookingpress_appointment_date' => $bookingpress_appointment_booked_date, 'bookingpress_appointment_start_time' => $bookingpress_appointment_booked_time, 'bookingpress_appointment_end_time' => $bookingpress_appointment_end_time), array('bookingpress_appointment_booking_ref' => $bookingpress_update_id));

                            $payment_new_status = '';
                            if ($bookingpress_appointment_status == '1' ) {
                                $payment_new_status = '1';
                                $booked_appointment_paymanet_details = $wpdb->get_row($wpdb->prepare('SELECT bookingpress_payment_gateway FROM ' . $tbl_bookingpress_payment_logs . ' WHERE bookingpress_appointment_booking_ref = %d', $bookingpress_update_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
                                if(!empty($booked_appointment_paymanet_details)){                                       
                                    if($booked_appointment_paymanet_details['bookingpress_payment_gateway'] != 'on-site'){                                
                                        $wpdb->update($tbl_bookingpress_payment_logs, array( 'bookingpress_payment_status' => $payment_new_status ), array( 'bookingpress_appointment_booking_ref' => $bookingpress_update_id ));
                                    }
                                }
                            }
                            
                            do_action('bookingpress_after_update_appointment', $bookingpress_update_id);

                            if ($bookingpress_appointment_is_send_notification ) {
                                if ($bookingpress_appointment_status == '4' ) {
                                    $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Rejected', $bookingpress_update_id, $customer_email);
                                } elseif ($bookingpress_appointment_status == '1' ) {
                                    $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Approved', $bookingpress_update_id, $customer_email);
                                } elseif ($bookingpress_appointment_status == '2' ) {
                                    $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Pending', $bookingpress_update_id, $customer_email);
                                } elseif ($bookingpress_appointment_status == '3' ) {
                                    $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Canceled', $bookingpress_update_id, $customer_email);
                                }
                            }       

                            if((($bookingpress_appointment_old_status != $bookingpress_appointment_status)) && (!empty($bookingpress_appointment_status) && !empty($bookingpress_update_id))) {
                                $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_appointment_status' => $bookingpress_appointment_status), array('bookingpress_appointment_booking_id' => $bookingpress_update_id) );	
                                do_action('bookingpress_after_change_appointment_status', $bookingpress_update_id, $bookingpress_appointment_status);
                            }

                            if( !empty( $bookingpress_old_appointment_service ) && $bookingpress_old_appointment_service != $bookingpress_appointment_selected_services ){
                                do_action( 'bookingpress_after_update_appointment_service', $bookingpress_update_id, $bookingpress_appointment_selected_services );
                            }

                            $response['variant'] = 'success';
                            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                            $response['msg']     = esc_html__('Appointment has been updated successfully.', 'bookingpress-appointment-booking');
                        }
                    } else {
                        
                        if($bookingpress_appointment_is_cusomize_timing) {

                            do_action('bookingpress_modified_appointment_data_for_backend_appointment_booking');

                            $customize_timeing_bookingpress_validation = apply_filters('bookingpress_customize_timeing_bookingpress_validation',array(),$bookingpress_appointment_selected_services,$bookingpress_appointment_booked_date,$bookingpress_appointment_booked_time, $bookingpress_appointment_end_time,0,true,$_REQUEST);

                            if(!empty($customize_timeing_bookingpress_validation)){                                
                                do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend appointment already exists', 'bookingpress_admin_add_update_appointment', $is_appointment_already_booked, $bookingpress_other_debug_log_id );
                                $response['variant'] = 'error';
                                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                                $message = esc_html__('Appointment already booked for this slot', 'bookingpress-appointment-booking');
                                if(isset($customize_timeing_bookingpress_validation['block_reason']) && !empty($customize_timeing_bookingpress_validation['block_reason'])){
                                    $message = $customize_timeing_bookingpress_validation['block_reason'];
                                }
                                $response['msg']     = $message;                                
                                echo wp_json_encode($response);
                                exit();
                            }

                        }

                        $bookingpress_entry_details = array(
                            'bookingpress_customer_id'     => $bookingpress_appointment_selected_customer,
                            'bookingpress_customer_name'   => $customer_username,
                            'bookingpress_customer_phone'  => $customer_phone,
                            'bookingpress_customer_firstname'  => $customer_firstname,
                            'bookingpress_customer_lastname'  => $customer_lastname,
                            'bookingpress_customer_country' => $customer_country,
                            'bookingpress_customer_email'  => $customer_email,
                            'bookingpress_customer_phone_dial_code' => $customer_dial_code,
                            'bookingpress_service_id'      => $bookingpress_appointment_selected_services,
                            'bookingpress_service_name'    => $service_name,
                            'bookingpress_service_price'   => $service_amount,
                            'bookingpress_service_currency' => $bookingpress_currency_name,
                            'bookingpress_service_duration_val' => $service_duration_val,
                            'bookingpress_service_duration_unit' => $service_duration_unit,
                            'bookingpress_payment_gateway' => 'manual',
                            'bookingpress_appointment_date' => $bookingpress_appointment_booked_date,
                            'bookingpress_appointment_time' => $bookingpress_appointment_booked_time,
                            'bookingpress_appointment_end_time' => $bookingpress_appointment_end_time,
                            'bookingpress_appointment_internal_note' => $bookingpress_appointment_internal_note,
                            'bookingpress_appointment_send_notifications' => $bookingpress_appointment_is_send_notification,
                            'bookingpress_appointment_status' => $bookingpress_appointment_status,
                            'bookingpress_paid_amount' => $service_amount,
                            'bookingpress_created_at'      => current_time('mysql'),
                        );


                        $is_recurring_appointments = (isset($bookingpress_appointment_data['is_recurring_appointments']))?$bookingpress_appointment_data['is_recurring_appointments']:'';
                        if($is_recurring_appointments == 'true'){
                            $response = apply_filters('bookingpress_add_backend_recurring_appointment', $response,$bookingpress_appointment_data, $bookingpress_entry_details);
                            echo wp_json_encode($response);
                            exit();
                        }
                                                
                        $bookingpress_entry_details = apply_filters('bookingpress_modify_backend_add_appointment_entry_data', $bookingpress_entry_details, $bookingpress_appointment_data);

                        do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend add appointment data', 'bookingpress_admin_add_update_appointment', $bookingpress_entry_details, $bookingpress_other_debug_log_id );

                        do_action('bookingpress_payment_log_entry', 'manual', 'submit appointment form backend', 'bookingpress', $bookingpress_entry_details, $bookingpress_debug_payment_log_id);

                        $wpdb->insert($tbl_bookingpress_entries, $bookingpress_entry_details);
                        $entry_id       = $wpdb->insert_id;

                        do_action('bookingpress_after_insert_entry_data_from_backend', $entry_id, $bookingpress_appointment_data);

                        $payment_log_id = 0;
                        
                        if (! empty($entry_id) ) {
                            if( $BookingPress->bpa_is_pro_exists() && $BookingPress->bpa_is_pro_active() && version_compare( $BookingPress->bpa_pro_plugin_version(), '2.7', '>=' ) ){
                                global $bookingpress_pro_payment_gateways;
                                $payment_log_id = $bookingpress_pro_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '1', '', '', 2);
                            } else {   
                                $payment_log_id = $bookingpress_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '1', '', '', 2);
                            }
                        }
                        if (! empty($payment_log_id) ) {
                            $response['variant'] = 'success';
                            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                            $response['msg']     = esc_html__('Appointment has been booked successfully.', 'bookingpress-appointment-booking');
                        }
                    }
                } else {
                    $response['msg'] = esc_html__('Please fill all required values', 'bookingpress-appointment-booking');
                }
            }

            echo wp_json_encode($response);
            exit();
        }

        /**
         * Ajax request for set appointment time slot at backend
         *
         * @return void
         */
        function bookingpress_set_appointment_time_slot_func()
        {
            global $wpdb,$tbl_bookingpress_services, $BookingPress;

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_calendar_appointments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $bookingpress_service_id    = isset($_REQUEST['service_id']) ? intval($_REQUEST['service_id']) : '';
            $bookingpress_selected_date = isset($_REQUEST['selected_date']) ? sanitize_text_field($_REQUEST['selected_date']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

            if (! empty($bookingpress_service_id) && ! empty($bookingpress_selected_date) ) {

                $appointment_time_slot             = $BookingPress->bookingpress_get_service_available_time($bookingpress_service_id, $bookingpress_selected_date);
                $bookingpress_service_slot_details = $BookingPress->bookingpress_get_daily_timeslots($appointment_time_slot);
                echo wp_json_encode($bookingpress_service_slot_details);
                exit;
            }
        }
        
        /**
         * Load calendar module view file
         *
         * @return void
         */
        function bookingpress_dynamic_load_calendar_view_func()
        {
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/calendar/manage_calendar.php';
            $bookingpress_load_file_name = apply_filters('bookingpress_modify_calendar_view_file_path', $bookingpress_load_file_name);

            include $bookingpress_load_file_name;
        }
        
        /**
         * Add more data variables for calendar module
         *
         * @return void
         */
        function bookingpress_calendar_dynamic_data_fields_func()
        {
            global $wpdb, $BookingPress, $bookingpress_calendar_vue_data_fields, $tbl_bookingpress_customers, $tbl_bookingpress_categories, $tbl_bookingpress_services, $bookingpress_global_options;

            $bookingpress_global_settings = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_time_format = $bookingpress_global_settings['wp_default_time_format'];

            // Fetch customers details



            // Fetch Services Details
            $bookingpress_services_details2                                     = array();
            $bookingpress_services_details2[]                                   = array(
            'category_name'     => '',
            'category_services' => array(
            '0' => array(
            'service_id'    => 0,
            'service_name'  => esc_html__('Select Services', 'bookingpress-appointment-booking'),
            'service_price' => '',
            ),
            ),
            );
            $bookingpress_services_details                                      = $BookingPress->get_bookingpress_service_data_group_with_category();
            $bookingpress_calendar_vue_data_fields['appointment_services_data'] = $bookingpress_services_details;

            $bookingpress_services_details                                      = array_merge($bookingpress_services_details2, $bookingpress_services_details);
            $bookingpress_calendar_vue_data_fields['appointment_services_list'] = $bookingpress_services_details;
            $bpa_nonce = wp_create_nonce('bpa_wp_nonce');
            $bookingpress_calendar_vue_data_fields['appointment_formdata']['_wpnonce'] = $bpa_nonce;

            $bookingpress_default_status_option = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');
            $bookingpress_calendar_vue_data_fields['appointment_formdata']['appointment_status'] = ! empty($bookingpress_default_status_option) ? $bookingpress_default_status_option : '1';
            $default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
            if (! empty($default_daysoff_details) ) {
                $default_daysoff_details                                = array_map(
                    function ( $date ) {
                        return date('Y-m-d', strtotime($date));
                    },
                    $default_daysoff_details
                );
                $bookingpress_calendar_vue_data_fields['disabledDates'] = $default_daysoff_details;
            } else {
                $bookingpress_calendar_vue_data_fields['disabledDates'] = '';
            }

            if($bookingpress_time_format == "g:i a"){
                $bookingpress_time_format = "hh:mm {am}";
            }else if($bookingpress_time_format == "g:i A"){
                $bookingpress_time_format = "hh:mm {AM}";
            }else{
                $bookingpress_time_format = "HH:mm";
            }
            $bookingpress_calendar_vue_data_fields['bookingpress_calendar_time_format'] = $bookingpress_time_format;
            $bookingpress_calendar_vue_data_fields['customer_id'] = '';
            $bookingpress_calendar_vue_data_fields['bookingpress_loading'] = false;
            $bookingpress_calendar_vue_data_fields['is_mask_display'] = false;
	    $bookingpress_calendar_vue_data_fields['is_display_popover'] = true;

            $bookingpress_calendar_vue_data_fields = apply_filters('bookingpress_modify_calendar_data_fields', $bookingpress_calendar_vue_data_fields);
            echo wp_json_encode($bookingpress_calendar_vue_data_fields);
        }
        		
		/**
		 * Ajax request for get search customer list
		 *
		 * @return void
		 */
		function bookingpress_get_search_customer_list_func() {

			global $wpdb, $BookingPress, $BookingPressPro;
			$response                       = array();
            $bpa_check_authorization = $this->bpa_check_authentication( 'search_customer', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $search_user_str = ! empty( $_REQUEST['search_user_str'] ) ? ( sanitize_text_field($_REQUEST['search_user_str'] )) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                   
			if(!empty($search_user_str)) {                  
                $response['variant'] = 'success';
                $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');
                $response['appointment_customers_details'] = array();
                $bookingpress_appointment_customers_details = $BookingPress->bookingpress_get_search_customer_list($search_user_str);						
                $response['appointment_customers_details'] = $bookingpress_appointment_customers_details;
            }    

			echo wp_json_encode($response);
			exit;
		}
        
        /**
         * Ajax request for get customer list
         *
         * @return void
         */
        function bookingpress_get_customer_list_func() {

			global $wpdb, $BookingPress, $BookingPressPro;
			$response                       = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_customers', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $search_user_str = ! empty( $_REQUEST['search_user_str'] ) ? ( sanitize_text_field($_REQUEST['search_user_str'] )) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $bookingpress_customer_id = ! empty( $_REQUEST['customer_id'] ) ? ( sanitize_text_field($_REQUEST['customer_id'] )) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if(!empty($search_user_str)) {                  
                $response['variant'] = 'success';
                $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');
                $response['appointment_customers_details'] = array();
                $bookingpress_appointment_customers_details = $BookingPress->bookingpress_get_appointment_customer_list($search_user_str,$bookingpress_customer_id);			
                $response['appointment_customers_details'] = $bookingpress_appointment_customers_details;
            }    
			echo wp_json_encode($response);
			exit;
		}

        
        /**
         * Add helper variables for calendar module
         *
         * @return void
         */
        function bookingpress_calendar_dynamic_helper_vars_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            ?>
            var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
            ELEMENT.locale(lang)
            <?php
            do_action('bookingpress_add_calendar_dynamic_helper_vars');
        }

        
        /**
         * Calendar module on load methods
         *
         * @return void
         */
        function bookingpress_calendar_dynamic_on_load_methods_func()
        {
            ?>
            if( 'mobile' == this.current_screen_size ){
                this.loadCalendar('day');
            } else {
                this.loadCalendar();
            }
            <?php
        }
        
        /**
         * Calendar module methods or functions
         *
         * @return void
         */
        function bookingpress_calendar_dynamic_vue_methods_func()
        {
            global $BookingPress,$bookingpress_notification_duration;
            $bookingpress_current_date          = date('Y-m-d', current_time('timestamp'));
            $bookingpress_default_status_option = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');
            $bookingpress_default_status_option = ! empty($bookingpress_default_status_option) ? $bookingpress_default_status_option : '1';
            ?>
                loadCalendar(load_activeView='month'){
                    const vm = this
                    vm.resetForm();
                    var events_details = []
                    var postData = { action:'bookingpress_get_bookings_details',activeView: load_activeView ,search_data: vm.search_data, _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if( "error" == response.data.variant ){
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval( $bookingpress_notification_duration); ?>
                            });
                        } else {
                            vm.calendar_events_data = response.data
                            vm.activeView=load_activeView;
                            <?php do_action( 'bookingpress_calendar_appointment_xhr_response' ) ?>
                            setTimeout(function(){
                                vm.is_display_popover = true;
                            },1000);
                            /* setTimeout(function(){
                                vm.eventRendered();
                            },500); */
                        }
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });
                },
                eventDblClick(event, e ){
                    const vm = this
		    	vm.is_display_popover = false;
                    if(vm.activeView=='month') {
                        vm.loadCalendar('week');
                    }else if(vm.activeView=='week') {
                        vm.loadCalendar('day');
                    }
                    vm.open_calendar_appointment_popover = false;
                },
                openAppointmentBookingModal(){
                    const vm = this;
                    vm.open_calendar_appointment_modal = true;
                },
		selected_calendar_appointment_booking_date( selected_value ){
                    const vm = this;
                    vm.bpa_today_btn = 0;
                    vm.search_data.search_appointment_date = vm.custom_filter_date_val;
                    vm.loadCalendar('day');
                },
                today_date( current_date ) {
                    const vm = this;
                    vm.bpa_today_btn = 1;
                    vm.custom_filter_date_val = current_date;
                    vm.search_data.search_appointment_date = vm.custom_filter_date_val;
                    vm.loadCalendar('day');
                },
                bookingpress_cal_prev(){
                    const vm = this;
                    vm.bpa_today_btn = 0;
                    var bookingpress_date_obj = new Date( vm.custom_filter_date_val );
                    let prev_date = this.get_formatted_date( bookingpress_date_obj.subtractDays(1) );
                    vm.custom_filter_date_val = prev_date;
                    vm.search_data.search_appointment_date = vm.custom_filter_date_val;
                    vm.loadCalendar('day');
                },
                bookingpress_cal_next(){
                    const vm = this;
                    vm.bpa_today_btn = 0;
                    var bookingpress_date_obj = new Date( vm.custom_filter_date_val );
                    let next_date = this.get_formatted_date( bookingpress_date_obj.addDays(1) );
                    vm.custom_filter_date_val = next_date;
                    vm.search_data.search_appointment_date = vm.custom_filter_date_val;
                    vm.loadCalendar('day');
                },

                openAppointmentBookingPopover( elm_target, is_more){
                    const vm = this;
                    vm.open_calendar_appointment_popover = true;
                    if( null != document.querySelector('.v-modal') ){
                        document.querySelector('.v-modal').style.backgroundColor = 'transparent';
                    }
                    if(vm.current_screen_size != 'mobile') {
                        vm.bpa_adjust_calendar_popover_position( elm_target, is_more );
                    }
                    if(vm.current_screen_size == 'mobile') {
                        document.body.style.overflow = 'hidden';
                        let body_top = document.body.getBoundingClientRect().top;
                        if( null != document.getElementById('wpadminbar') && body_top <= 0 && body_top >= -46 ){
                            document.getElementById('calendar_appointment_popover_dialog').style.paddingTop = ( body_top + 46) + 'px';
                        }
                    }
                },
                closeAppointmentBookingPopover(){
                    const vm = this;
                    document.body.style.overflow = '';
                    vm.open_calendar_appointment_popover = false;
                    vm.resetForm();
                    document.getElementById('calendar_appointment_popover_dialog').style.paddingTop = '';
                    let cal_dialog = vm.$el.querySelector('.bpa-fsc-item__popover-card');
                    vm.bpa_calendar_dialog_custom_cls = 'bpa-fsc-item__popover-card';
                },
                closeAppointmentBookingModal(){
                    const vm = this
                    const vm2 = vm;
                    vm.appointment_customers_list = []
                    vm.open_calendar_appointment_modal = false
                    vm.open_calendar_appointment_popover = false;
                    if(typeof vm.$refs['appointment_formdata'] !== 'undefined'){
                        vm.$refs['appointment_formdata'].resetFields();
                    }
                    vm.resetForm()
                    <?php do_action('bookingpress_calendar_add_appointment_model_reset'); ?>
                },
                saveAppointmentBooking(bookingAppointment){
                    const vm = new Vue();
                    const vm2 = this;              
                    let form_data = vm2.appointment_formdata;
                    if(form_data.bookingpress_appointment_popover_data !== undefined) {
                        delete form_data.bookingpress_appointment_popover_data;
                    }
                    if(form_data.bookingpress_appointemnt_popover_timeslot_title !== undefined) {
                        delete form_data.bookingpress_appointemnt_popover_timeslot_title;
                    }
                    if(form_data.bookingpress_appointemnt_popover_title !== undefined) {
                        delete form_data.bookingpress_appointemnt_popover_title;
                    } 

                    this.$refs[bookingAppointment].validate((valid) => {
                        <?php do_action('bookingpress_modify_request_after_validation'); ?>
                        if (valid) {                                    
                            vm2.is_disabled = true;
                            vm2.is_display_save_loader = '1';
                            var postData = { action:'bookingpress_save_appointment_booking', appointment_data: JSON.stringify( form_data ), _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                            .then( function (response) {                                                                        
                                vm2.is_disabled = false;
                                vm2.is_display_save_loader = '0';
                                if(response.data.variant != 'error') {
                                    vm2.closeAppointmentBookingModal();
                                    vm2.loadCalendar(vm2.activeView);         
                                }    
                                vm2.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: response.data.variant,
                                    customClass: response.data.variant+'_notification',                                                                        
                                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                });                                                    
                            }.bind(this) )
                            .catch( function (error) {
                                console.log(error);
                            });
                        }
                    });
                },
                formatted_date(selected_date){
                    const vm2 = this
                    vm2.appointment_formdata.appointment_booked_date = vm2.get_formatted_date(selected_date)
                    vm2.bookingpress_set_time_slot()
                },
                resetForm(){
                    const vm2 = this
                    vm2.appointment_formdata.appointment_selected_customer = ''
                    vm2.appointment_formdata.appointment_selected_staff_member= ''
                    vm2.appointment_formdata.appointment_selected_service = ''
                    vm2.appointment_formdata.appointment_booked_date = '<?php echo esc_html(date('Y-m-d', current_time('timestamp'))); ?>';
                    vm2.appointment_formdata.appointment_booked_time = ''
                    vm2.appointment_formdata.appointment_internal_note = ''
                    vm2.appointment_formdata.appointment_send_notification = ''
                    vm2.appointment_formdata.appointment_status = '<?php echo esc_html($bookingpress_default_status_option); ?>'                
                    vm2.appointment_formdata.appointment_update_id = 0
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
                resetFilter(){
                    const vm = this;
                    vm.search_data.selected_services = [];
                    vm.search_data.selected_customers = [];
                    vm.search_data.selected_status = '';
                    vm.search_data.search_appointment_date = '';
                    <?php 
                    do_action('bookingpress_calendar_reset_filter');
                    ?>
                    vm.loadCalendar()
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
                bookingpress_get_search_customer_list(query){
                    const vm = new Vue()
                    const vm2 = this	
                    if (query !== '') {
                        vm2.bookingpress_loading = true;                    
                        var customer_action = { action:'bookingpress_get_search_customer_list',search_user_str:query,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }                    
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                        .then(function(response){
                            vm2.bookingpress_loading = false;
                            vm2.search_customer_list = response.data.appointment_customers_details
                        }).catch(function(error){
                            console.log(error)
                        });
                    } else {
                        vm2.search_customer_list = [];
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
                bookingpress_calendar_approve_appointment(update_id, selectedValue){
                    const vm = this;
                    vm.is_calendar_popover_approve_loader = 1;
                    vm.bpa_is_disable_approve_btn = true;
                    var postData = { action:'bookingpress_change_upcoming_appointment_status', update_appointment_id: update_id, appointment_new_status: selectedValue,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) ).then( function (response) {
                        if( "error" == response.data.variant ){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm.is_calendar_popover_approve_loader = 0;
                            vm.open_calendar_appointment_popover = false;
                            return false;
                        }
                        if(response.data == "0" || response.data == 0){
                            vm.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Appointment already booked for this slot', 'bookingpress-appointment-booking'); ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm.is_calendar_popover_approve_loader = 0;
                            vm.open_calendar_appointment_popover = false;
                            vm.loadCalendar(vm.activeView);
                            return false;
                        }else{
                            vm.$notify({
                                title: '<?php esc_html_e('Success', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Appointment status changed successfully', 'bookingpress-appointment-booking'); ?>',
                                type: 'success',
                                customClass: 'success_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm.is_calendar_popover_approve_loader = 0;
                            const event_cards = document.querySelectorAll('.bpa-cal-event-card_'+update_id);
                            event_cards.forEach(event_card => {
                                if( event_card.classList.contains('bpa-cal-event-card--pending') ){
                                    event_card.classList.remove("bpa-cal-event-card--pending");
                                    event_card.classList.add("bpa-cal-event-card--approved");
                                }
                            });
                            var foundIndex = vm.calendar_events_data.findIndex(x => x.appointment_id == update_id);
                            if(foundIndex>0){
                                vm.calendar_events_data[foundIndex].class = 'bpa-cal-event-card bpa-cal-event-card--approved';
                            }
                            vm.open_calendar_appointment_popover = false;
                        }
                        vm.bpa_is_disable_approve_btn = false;
                    }.bind(this) )
                    .catch( function (error) {
                        vm.bpa_is_disable_approve_btn = false;
                        vm.is_calendar_popover_approve_loader = 0;
                        vm.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    });
                },
            <?php
            do_action('bookingpress_add_dynamic_vue_methods_for_calendar');
        }
    }
}

global $bookingpress_calendar;
$bookingpress_calendar = new bookingpress_calendar();
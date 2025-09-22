<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_customize') ) {
    class bookingpress_customize Extends BookingPress_Core
    {
        function __construct()
        {
            add_action('bookingpress_customize_dynamic_view_load', array( $this, 'bookingpress_load_customize_dynamic_view_func' ));
            add_action('bookingpress_customize_dynamic_data_fields', array( $this, 'bookingpress_dynamic_data_fields_func' ));
            add_action('bookingpress_customize_dynamic_computed_methods', array( $this, 'bookingpress_dynamic_computed_methods_func' ));
            add_action('bookingpress_customize_dynamic_on_load_methods', array( $this, 'bookingpress_dynamic_onload_methods_func' ));
            add_action('bookingpress_customize_dynamic_vue_methods', array( $this, 'bookingpress_dynamic_vue_methods_func' ), 9);
            add_action('bookingpress_customize_dynamic_components', array( $this, 'bookingpress_dynamic_components_func' ));
            add_action('bookingpress_customize_dynamic_helper_vars', array( $this, 'bookingpress_customize_helper_vars_func' ));

            add_action('wp_ajax_bookingpress_save_field_settings', array( $this, 'bookingpress_save_field_settings_data_func' ));
            add_action('wp_ajax_bookingpress_save_my_booking_settings', array( $this, 'bookingpress_save_my_booking_settings_func' ));
            add_action('wp_ajax_bookingpress_load_field_settings', array( $this, 'bookingpress_load_field_settings_func' ));
            add_action('wp_ajax_bookingpress_update_field_position', array( $this, 'bookingpress_update_field_pos_func' ));
            add_action('wp_ajax_bookingpress_save_form_settings', array( $this, 'bookingpress_save_form_settings_func' ));
            add_action('wp_ajax_bookingpress_load_bookingform_data', array( $this, 'bookingpress_load_bookingform_data_func' ));
            add_action('wp_ajax_bookingpress_load_my_booking_data', array( $this, 'bookingpress_load_my_booking_data_func' ));

            add_action( 'bookingpress_page_admin_notices', array( $this, 'bookingpress_caching_admin_notice_func') , 20);
            add_action( 'wp_ajax_bookingpress_dismiss_caching_notice', array( $this, 'bookingpress_dismiss_caching_notice_func' ));
        }
        
        function bookingpress_dismiss_caching_notice_func(){

            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['formdata'] = '';
                $response['variant']  = 'error';
                $response['title']    = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']      = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                echo wp_json_encode($response);
                exit;
            }  
            update_option( 'bookingpress_disabled_caching_notice', true );
            $response['variant']  = 'success';
            $response['title']    = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']      = esc_html__('Notice dismissed sucessfully.', 'bookingpress-appointment-booking');            
            echo wp_json_encode($response);
            exit();

        }

        function bookingpress_caching_admin_notice_func(){

            $requested_module = ! empty($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : 'dashboard'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            if($requested_module == "bookingpress_customize") {
                global $BookingPress, $bookingpress_pro_settings;
                if( !current_user_can( 'administrator' ) ){
                    return;
                }
                $bookingpress_caching_notice_disabled = get_option( 'bookingpress_disabled_caching_notice' );
                if($bookingpress_caching_notice_disabled==false){
                    $bookingpress_found_activated_caching_plugin_arr = array(); 
                    $activated_caching_plug = '';
                    if ( (is_plugin_active('wp-fastest-cache/wpFastestCache.php'))) {
                        $bookingpress_found_activated_caching_plugin_arr[] = "WP Fastest Cache";
                    } 
                    if ( (is_plugin_active('w3-total-cache/w3-total-cache.php'))) {
                        $bookingpress_found_activated_caching_plugin_arr[] = "W3 Total Cache";
                    } 
                    if ( (is_plugin_active('litespeed-cache/litespeed-cache.php'))) {
                        $bookingpress_found_activated_caching_plugin_arr[] = "LiteSpeed Cache";
                    } 
                    if ( (is_plugin_active('autoptimize/autoptimize.php'))) {
                        $bookingpress_found_activated_caching_plugin_arr[] = "Autoptimize";
                    }  
                    if ( (is_plugin_active('wp-optimize/wp-optimize.php'))) {
                        $bookingpress_found_activated_caching_plugin_arr[] = "WP-Optimize";
                    }  
                    if ( (is_plugin_active('wp-rocket/wp-rocket.php'))) {
                        $bookingpress_found_activated_caching_plugin_arr[] = "WP Rocket";
                    }  
                    if(!empty($bookingpress_found_activated_caching_plugin_arr)) {
                        $activated_caching_plug = implode(', ', $bookingpress_found_activated_caching_plugin_arr);                    
                        $bpa_notification_msg = esc_html__('It seems that you are using', 'bookingpress-appointment-booking');
                        $bpa_notification_msg .= ' <strong>'.$activated_caching_plug. '</strong> ';
                        $bpa_notification_msg .= esc_html__('caching plugin. So, do not forget to clear your website cache after doing any changes in customization or field settings section.', 'bookingpress-appointment-booking');
                        ?>
                        <div class="bpa-toast-notification --bpa-caching-note" v-if="is_disabled_caching_notice == false">
                            <div class="bpa-front-tn-body">
                                <span class="bpa-cahing-info material-icons-round">info</span>
                                <p class="bookingpress-caching-warning-msg">
                                    <strong><?php esc_html_e('Note:', 'bookingpress-appointment-booking'); ?></strong> 
                                    <?php echo $bpa_notification_msg; //phpcs:ignore ?>  
                                </p>
                                <span class="bpa-donot-show-caching-notice" @click="bookingpress_hide_caching_notice"><?php esc_html_e('Don\'t show again','bookingpress-appointment-booking')?></span>
                                <span class="bpa-uwb-close-icon material-icons-round" @click="bookingpress_close_caching_notice">close</span>
                            </div>
                        </div>   
                    <?php 
                    }
                }
            }

            $bpa_update_elementor_notice = get_option( 'bookingpress_update_elementor_widget' );
            if( !empty( $bpa_update_elementor_notice ) && 1 == $bpa_update_elementor_notice ){
                $admin_notice_elm = esc_html__( 'From BookingPress version 1.1.11, the two Elementor widgets are deprecated. Please click below button to update with the latest.', 'bookingpress-appointment-booking' );
                echo '<div class="bpa-pg-warning-belt-box bpa-wp-default-notice bpa-wp-default-error notice notice-error bookingpress-elementor-widget-notice"><p>'.$admin_notice_elm.'</p><button type="button" class="button bookingpress_replace_elementor_widgets button-primary button-large" >'.esc_html__( 'Update Elementor Widgets', 'bookingpress-appointment-booking' ).'</button>&nbsp;<span class="bpa_update_message" style="display:inline-block; height:32px; line-height:32px; padding: 0 5px;"></span><span class="spinner bpa_elementor_update_spinner" style="float:none;margin-top:-20px;"></span><br/></div>'; //phpcs:ignore
            }
        }

        /**
         * Load customize booking form data variables
         *
         * @return void
         */
        function bookingpress_load_bookingform_data_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_customize_settings, $bookingpress_customize_vue_data_fields;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'load_customization', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $tab_container_data = array(
                'service_title'         => esc_html__('Services', 'bookingpress-appointment-booking'),
                'datetime_title'        => str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ),
                'basic_details_title'   => esc_html__('Basic Details', 'bookingpress-appointment-booking'),
                'summary_title'         => esc_html__('Summary', 'bookingpress-appointment-booking'),
            );

            $category_container_data = array(
                'category_title'         => esc_html__('Select Category', 'bookingpress-appointment-booking'),
				'all_category_title'         => esc_html__( 'ALL', 'bookingpress-appointment-booking' ),
            );

            $service_container_data = array(
                'service_heading_title' => esc_html__('Select Service', 'bookingpress-appointment-booking'),
                'default_image_url'     => BOOKINGPRESS_URL . '/images/placeholder-img.jpg',
            );

            $timeslot_container_data = array(
                'timeslot_text'     => esc_html__('Time Slot', 'bookingpress-appointment-booking'),            
                'morning_text'      => esc_html__('Morning', 'bookingpress-appointment-booking'),            
                'afternoon_text'    => esc_html__('Afternoon', 'bookingpress-appointment-booking'),
                'evening_text'      => esc_html__('Evening', 'bookingpress-appointment-booking'),            
                'night_text'        => esc_html__('Night', 'bookingpress-appointment-booking'),
                'date_time_step_note' => '',
            );

            $bookingpress_colorpicker_values = array(
                'background_color'         => '#fff',
                'footer_background_color'  => '#f4f7fb',
                'border_color'             => '#CFD6E5',
                'primary_color'            => '#12D488',
                'primary_background_color' => '#e2faf1',
                'label_title_color'        => '#202C45',
                'sub_title_color'          => '#202C45',
                'sub_title_color'          => '#202C45',
                'content_color'            => '#535D71',
                'price_button_text_color'  => '#fff',
                'custom_css'               => '',
            );

            $bookingpress_font_values = array(
                'title_font_family'   => 'Poppins',
            );
            $booking_form_settings = array(
                'hide_category_service_selection' => false,
                'hide_already_booked_slot'        => false,
                'display_service_description'     => false,
                'default_select_all_category'     => false,
                'booking_form_tabs_position'      => 'left',
                'service_duration_label'          => esc_html__('Duration', 'bookingpress-appointment-booking') . ':',
                'service_price_label'             => esc_html__('Price', 'bookingpress-appointment-booking') . ':',
                'goback_button_text'              => esc_html__('Go Back', 'bookingpress-appointment-booking'),
                'next_button_text'                => esc_html__('Next', 'bookingpress-appointment-booking'),
                'book_appointment_btn_text'       => esc_html__('Book Appointment', 'bookingpress-appointment-booking'),
                'book_appointment_hours_text'     => 'h',
                'book_appointment_min_text'       => 'm',
                'after_booking_redirection'       => '',
                'after_failed_payment_redirection' =>  '',
            );
            $summary_container_data        = array(
                'summary_content_text'          => esc_html__('Your appointment booking summary', 'bookingpress-appointment-booking'),
                'payment_method_text'           => esc_html__('Select Payment Method', 'bookingpress-appointment-booking'),
                'summary_step_note'             => '',
            );
            $front_label_edit_data         = array(
                'paypal_text'                 => esc_html__('PayPal', 'bookingpress-appointment-booking'),
                'locally_text'                => esc_html__('Pay Locally', 'bookingpress-appointment-booking'),
                'total_amount_text'           => esc_html__('Total Amount Payable', 'bookingpress-appointment-booking'),
                'service_text'                => esc_html__('Service', 'bookingpress-appointment-booking'),
                'customer_text'               => esc_html__('Customer', 'bookingpress-appointment-booking'),
                'date_time_text'              => str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ),
                'appointment_details'         => esc_html__('Appointment Details', 'bookingpress-appointment-booking'),
            );
            
            $bookingpress_booking_form_data = array(
                'tab_container_data'      => $tab_container_data,
                'category_container_data' => $category_container_data,
                'service_container_data'  => $service_container_data,
                'timeslot_container_data' => $timeslot_container_data,
                'colorpicker_values'      => $bookingpress_colorpicker_values,
                'font_values'             => $bookingpress_font_values,
                'booking_form_settings'   => $booking_form_settings,
                'summary_container_data'  => $summary_container_data,
                'front_label_edit_data'   => $front_label_edit_data,
            );

            $bookingpress_booking_form_data = apply_filters('bookingpress_get_booking_form_customize_data_filter', $bookingpress_booking_form_data);

            $bookingpress_bookingform_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_type = %s", 'booking_form'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally. False Positive alarm

            $bookingpress_booking_form_settings = array();

            foreach($bookingpress_bookingform_data as $data_key => $data_val) {
                $bookingpress_setting_value = $data_val['bookingpress_setting_value'];
                if ($bookingpress_setting_value == 'false' || $bookingpress_setting_value == 'true' ) {
                    $bookingpress_setting_value = ( $bookingpress_setting_value == 'false' ) ? false : true;
                } elseif(!empty($bookingpress_setting_value) && is_string($bookingpress_setting_value)) {                    
                    $bookingpress_setting_value = stripslashes_deep($bookingpress_setting_value);
                }
                $bookingpress_booking_form_settings[$data_val['bookingpress_setting_name']]  = $bookingpress_setting_value;
            }
            foreach ( $bookingpress_booking_form_data as $bookingpress_formdata_key => $bookingpress_formdata_val ) {
                if(!empty($bookingpress_formdata_val)) {
                    foreach($bookingpress_formdata_val as $key => $value) {                    
                        $bookingpress_booking_form_data[$bookingpress_formdata_key][$key] =  isset($bookingpress_booking_form_settings[$key] ) ?$bookingpress_booking_form_settings[$key] : '';  
                    }
                }
            }

            $default_time_format = $BookingPress->bookingpress_get_settings('default_time_format','general_setting');            			
            $default_time_format = !empty($default_time_format) ? $default_time_format : 'g:i a';


            $response['variant']  = 'success';
            $response['title']    = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']      = esc_html__('Field Settings Data Retrieved Successfully', 'bookingpress-appointment-booking');

            $response['formdata'] = apply_filters( 'bookingpress_modify_loaded_form_data', $bookingpress_booking_form_data);

            if ( $default_time_format == 'bookingpress-wp-inherit-time-format' && $BookingPress->bpa_is_pro_active() && version_compare( $BookingPress->bpa_pro_plugin_version(), '2.0', '<') ) {
                $response['formdata']['booking_form_settings']['bookigpress_time_format_for_booking_form'] = 'bookingpress-wp-inherit-time-format';
                $response['formdata']['booking_form_settings']['bookigpress_check_inherit_time_format'] = true;
            }

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Load customize my-bookings data variables
         *
         * @return void
         */
        function bookingpress_load_my_booking_data_func()
        {

            global $wpdb, $BookingPress, $tbl_bookingpress_customize_settings, $bookingpress_customize_vue_data_fields;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['formdata'] = '';
                $response['variant']  = 'error';
                $response['title']    = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']      = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                echo wp_json_encode($response);
                exit;
            }       
            $bookingpress_my_booking_field_settings = array(
                'mybooking_title_text'        => esc_html__('My Bookings', 'bookingpress-appointment-booking'),
                'allow_to_cancel_appointment' => true,
                'apply_button_title'          => esc_html__('Apply', 'bookingpress-appointment-booking'),  
                'search_appointment_title'    => esc_html__('Search appointments', 'bookingpress-appointment-booking'),
                'search_date_title'    => esc_html__('Please select date', 'bookingpress-appointment-booking'),
                'search_end_date_title'    => esc_html__('Please select date', 'bookingpress-appointment-booking'),    
                'my_appointment_menu_title'    => esc_html__('My Appointments', 'bookingpress-appointment-booking'),
                'delete_appointment_menu_title'    => esc_html__('Delete Account', 'bookingpress-appointment-booking'),                  
                'after_cancelled_appointment_redirection' => '',
                'appointment_cancellation_confirmation' => '',
                'delete_account_content'      => '',
                'cancel_appointment_title' => '',
                'cancel_appointment_confirmation_message' => '',
                'cancel_appointment_no_btn_text' => '',
                'cancel_appointment_yes_btn_text' => '',
                'id_main_heading' => '',
                'service_main_heading' => '',
                'date_main_heading' => '',
                'status_main_heading' => '',
                'payment_main_heading' => '',
                'booking_id_heading' => '',
                'booking_time_title' => '',
                'payment_details_title' => '',
                'payment_method_title' => '',
                'total_amount_title' => '',	
                'cancel_booking_id_text' => '',
                'cancel_service_text' => '',
                'cancel_date_time_text' => '',
                'cancel_button_text' => '',
            );
            $bookingpress_my_booking_field_settings = apply_filters('bookingpress_get_my_booking_customize_data_filter', $bookingpress_my_booking_field_settings);

            $bookingpress_bookingform_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_type = %s", 'booking_my_booking'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally. False Positive alarm
            foreach ( $bookingpress_bookingform_data as $bookingpress_formdata_key => $bookingpress_formdata_val ) {
                $bookingpress_setting_name  = $bookingpress_formdata_val['bookingpress_setting_name'];
                $bookingpress_setting_value = $bookingpress_formdata_val['bookingpress_setting_value'];

                if ($bookingpress_setting_value == 'false' || $bookingpress_setting_value == 'true' ) {
                    $bookingpress_setting_value = ( $bookingpress_setting_value == 'false' ) ? false : true;
                } elseif(!empty($bookingpress_setting_value) && is_string($bookingpress_setting_value) ) {
                    $bookingpress_setting_value = stripslashes($bookingpress_setting_value);
                }
                if (isset($bookingpress_my_booking_field_settings[ $bookingpress_setting_name ]) ) {
                    $bookingpress_my_booking_field_settings[ $bookingpress_setting_name ] = $bookingpress_setting_value;
                }
            }
            $bookingpress_return_data = array(
                'booking_form_settings' => $bookingpress_my_booking_field_settings,
            );

            $response['variant']  = 'success';
            $response['title']    = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']      = esc_html__('Field Settings Data Retrieved Successfully', 'bookingpress-appointment-booking');
            $response['formdata'] = $bookingpress_return_data;

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Save customize module settings data
         *
         * @return void
         */
        function bookingpress_save_form_settings_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_customize_settings;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'save_form_settings', true, 'bpa_wp_nonce' );
            
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
         // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reason:$_POST variables are contains mixed array and sanitized properly using appointment_sanatize_field function
            $tab_container_data              = ! empty($_POST['tab_container_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['tab_container_data']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $category_container_data         = ! empty($_POST['category_container_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['category_container_data']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $service_container_data          = ! empty($_POST['service_container_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['service_container_data']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $timeslot_container_data         = ! empty($_POST['timeslot_container_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['timeslot_container_data']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_colorpicker_values = ! empty($_POST['colorpicker_values']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['colorpicker_values']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_font_values        = ! empty($_POST['font_values']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['font_values']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $booking_form_settings           = ! empty($_POST['booking_form_settings']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['booking_form_settings']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $summary_container_data          = ! empty($_POST['summary_container_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['summary_container_data']) : array(); // phpcs:ignore WordPress.Security.NonceVerification

            $front_label_edit_data = ! empty($_POST['front_label_edit_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['front_label_edit_data']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            
            $booking_form_settings_data = array(
                'tab_container_data' => $tab_container_data,
                'category_container_data' => $category_container_data,
                'service_container_data' => $service_container_data,
                'timeslot_container_data' => $timeslot_container_data,
                'colorpicker_values' => $bookingpress_colorpicker_values,
                'font_values' => $bookingpress_font_values,
                'booking_form_settings' => $booking_form_settings,
                'summary_container_data' => $summary_container_data,
                'front_label_edit_data' => $front_label_edit_data,
            ); 
             
            $booking_form_settings_data = apply_filters('bookingpress_before_save_customize_booking_form',$booking_form_settings_data);            

            if (! empty($booking_form_settings_data) ) {
                foreach($booking_form_settings_data as $data_key  => $data_value) {
                    if(!empty($data_value)) {
                        foreach ( $data_value as $bookingpress_setting_key => $bookingpress_setting_val ) {
                            $bookingpress_setting_val = !empty($bookingpress_setting_val) && gettype($bookingpress_setting_val) == 'array' ? wp_json_encode($bookingpress_setting_val) : $bookingpress_setting_val;
                            $bookingpress_db_fields = array(
                            'bookingpress_setting_name'  => $bookingpress_setting_key,
                            'bookingpress_setting_value' => $bookingpress_setting_val,
                            'bookingpress_setting_type'  => 'booking_form',
                            );
        
                            $is_setting_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_setting_id) as total FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_name = %s AND bookingpress_setting_type = 'booking_form'", $bookingpress_setting_key) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally. False Positive alarm
                            if ($is_setting_exists > 0 ) {
                                $wpdb->update(
                                    $tbl_bookingpress_customize_settings,
                                    $bookingpress_db_fields,
                                    array(
                                    'bookingpress_setting_name' => $bookingpress_setting_key,
                                    'bookingpress_setting_type' => 'booking_form',
                                    )
                                );
                            } else {
                                $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
                            }
                        }
                    }
                }

                $my_booking_form = array();
                $booking_form = array(
                    'background_color'         => $bookingpress_colorpicker_values['background_color'],
                    'footer_background_color'  => $bookingpress_colorpicker_values['footer_background_color'],
                    'border_color'             => $bookingpress_colorpicker_values['border_color'],
                    'primary_color'            => $bookingpress_colorpicker_values['primary_color'],
                    'primary_background_color' => $bookingpress_colorpicker_values['primary_background_color'],  
                    'label_title_color'        => $bookingpress_colorpicker_values['label_title_color'],
                    'title_font_family'        => $bookingpress_font_values['title_font_family'],
                    'content_color'            => $bookingpress_colorpicker_values['content_color'],
                    'sub_title_color'          => $bookingpress_colorpicker_values['sub_title_color'],
                    'price_button_text_color'  => $bookingpress_colorpicker_values['price_button_text_color'],
                );
                $bookingpress_action[] = 'bookingpress_save_booking_form_settings';
                $bookingpress_custom_data_arr = array(                    
                    'my_booking_form' => $my_booking_form, 
                    'booking_form' => $booking_form, 
                    'action' => $bookingpress_action,
                );
  
                $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);

                $bpa_loader_content = $this->bpa_generate_loader_with_color( $booking_form['primary_color'] );
                
                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Customize settings updated successfully.', 'bookingpress-appointment-booking');
            }            
            do_action('bookingpress_after_save_customize_settings');

            wp_cache_delete( 'bookingpress_all_general_settings' );
            wp_cache_delete( 'bookingpress_all_customize_settings' );


            echo wp_json_encode($response);
            exit();
        }

        function bpa_generate_loader_with_color( $primary_color ){
            $bpa_loader_string = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" width="256" height="256" viewBox="0 0 256 256" style="width: 100%; height: 100%;"><defs><animate repeatCount="indefinite" dur="2.2166667s" begin="0s" xlink:href="#_R_G_L_1_C_0_P_0" fill="freeze" attributeName="d" attributeType="XML" from="M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z " to="M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z " keyTimes="0;0.5037594;0.5263158;0.5789474;0.6691729;0.6992481;0.7593985;0.7669173;1" values="M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z ;M294.33 386.7 C294.33,386.7 291.96,386.7 291.96,386.7 C291.96,386.7 291.67,391.89 291.67,391.89 C291.67,391.89 292.41,396.34 292.41,396.34 C292.41,396.34 292.11,401.09 292.11,401.09 C292.11,401.09 295.67,401.09 295.67,401.09 C295.67,401.09 295.82,396.05 295.82,396.05 C295.82,396.05 295.97,391.75 295.97,391.75 C295.97,391.75 294.33,386.7 294.33,386.7z ;M303.49 386.7 C303.49,386.7 284.88,386.7 284.88,386.7 C284.88,386.7 284.88,402.72 284.88,402.72 C284.88,402.72 293.41,402.87 293.41,402.87 C293.41,402.87 293.07,405.24 293.07,405.24 C293.07,405.24 296.63,405.24 296.63,405.24 C296.63,405.24 296.82,402.57 296.82,402.57 C296.82,402.57 304.49,401.98 304.49,401.98 C304.49,401.98 303.49,386.7 303.49,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.56,398.12 265.56,398.12 C265.56,398.12 266.75,407.02 266.75,407.02 C266.75,407.02 294.78,405.83 294.78,405.83 C294.78,405.83 298.34,405.83 298.34,405.83 C298.34,405.83 332.75,406.72 332.75,406.72 C332.75,406.72 332.45,399.46 332.45,399.46 C332.45,399.46 330.97,386.7 330.97,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.56,442.32 265.56,442.32 C265.56,442.32 266.75,448.4 266.75,448.4 C266.75,448.4 283.8,447.51 283.8,447.51 C283.8,447.51 312.06,447.21 312.06,447.21 C312.06,447.21 332.75,448.1 332.75,448.1 C332.75,448.1 332.45,443.65 332.45,443.65 C332.45,443.65 330.97,386.7 330.97,386.7z ;M330.97 386.7 C330.97,386.7 263.64,386.7 263.64,386.7 C263.64,386.7 265.86,453.14 265.86,453.14 C265.86,453.14 276.98,456.11 276.98,456.11 C276.98,456.11 277.28,447.51 277.28,447.51 C277.28,447.51 319.47,447.81 319.47,447.81 C319.47,447.81 318.81,456.11 318.81,456.11 C318.81,456.11 329.63,454.92 329.63,454.92 C329.63,454.92 330.97,386.7 330.97,386.7z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.63,448.83 295.63,448.83 C295.63,448.83 295.71,448.75 295.71,448.75 C295.71,448.75 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z ;M330.93 386.68 C330.93,386.68 263.6,386.68 263.6,386.68 C263.6,386.68 265.82,453.13 265.82,453.13 C265.82,453.13 295.78,456.98 295.78,456.98 C295.78,456.98 295.89,452.83 295.89,452.83 C295.89,452.83 296.26,452.98 296.26,452.98 C296.26,452.98 295.78,457.13 295.78,457.13 C295.78,457.13 329.59,454.91 329.59,454.91 C329.59,454.91 330.93,386.68 330.93,386.68z " keySplines="0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0 0 0 0" calcMode="spline"></animate> <clipPath id="_R_G_L_1_C_0"><path id="_R_G_L_1_C_0_P_0" fill-rule="nonzero"></path></clipPath> <animate repeatCount="indefinite" dur="2.2166667s" begin="0s" xlink:href="#_R_G_L_0_C_0_P_0" fill="freeze" attributeName="d" attributeType="XML" from="M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z " to="M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z " keyTimes="0;0.1804511;0.2180451;0.2481203;0.2631579;0.2706767;0.2781955;0.2857143;0.3157895;0.3308271;0.3533835;0.3834586;0.406015;0.4135338;0.4210526;0.4511278;0.4736842;0.4887218;0.4962406;1" values="M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z ;M306.79 419.97 C306.79,419.97 306.83,419.98 306.83,419.98 C306.83,419.98 306.8,419.97 306.8,419.97 C306.8,419.97 306.78,420 306.78,420 C306.78,420 306.8,420 306.8,420 C306.8,420 306.86,420 306.86,420 C306.86,420 306.95,419.93 306.95,419.93 C306.95,419.93 306.86,419.96 306.86,419.96 C306.86,419.96 306.84,420.21 306.84,420.21 C306.84,420.21 306.89,420.1 306.89,420.1 C306.89,420.1 306.83,420.1 306.83,420.1 C306.83,420.1 306.5,420.99 306.83,420.17 C307.17,419.36 306.69,420.75 306.69,419.9 C306.69,419.04 306.89,420.14 306.89,420.14 C306.89,420.14 306.93,420.01 306.93,420.01 C306.93,420.01 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 302.88,423.24 302.88,423.24 C302.88,423.24 302.6,423.2 302.6,423.2 C302.6,423.2 302.79,423.22 302.79,423.22 C302.79,423.22 302.47,423.18 302.47,423.18 C302.47,423.18 302.62,423.19 302.62,423.19 C302.62,423.19 302.53,423.17 302.53,423.17 C302.53,423.17 302.43,423.36 302.43,423.18 C302.43,422.99 302.57,423.16 302.57,423.16 C302.57,423.16 302.4,423.16 302.4,423.16 C302.4,423.16 302.71,423.1 302.71,423.1 C302.71,423.1 302.68,423.07 302.68,423.07 C302.68,423.07 302.76,423.09 302.76,423.09 C302.76,423.09 302.66,423.2 302.66,423.2 C302.66,423.2 302.71,423.14 302.71,423.14 C302.71,423.14 302.75,423.12 302.75,423.12 C302.75,423.12 302.75,423.18 302.75,423.18 C302.75,423.18 302.53,423.22 302.53,423.22 C302.53,423.22 306.79,419.98 306.79,419.98 C306.79,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.8,419.98 306.8,419.98 C306.8,419.98 306.77,419.98 306.77,419.98 C306.77,419.98 306.79,419.98 306.79,419.98 C306.79,419.98 306.79,419.97 306.79,419.97z ;M310.92 429.74 C310.92,429.74 310.97,429.75 310.97,429.75 C310.97,429.75 310.93,429.74 310.93,429.74 C310.93,429.74 310.91,429.77 310.91,429.77 C310.91,429.77 310.94,429.77 310.94,429.77 C310.94,429.77 310.99,429.77 310.99,429.77 C310.99,429.77 311.09,429.7 311.09,429.7 C311.09,429.7 310.99,429.73 310.99,429.73 C310.99,429.73 310.9,434.91 310.9,434.91 C310.9,434.91 312.25,433.8 312.25,433.8 C312.25,433.8 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 303.93,428.18 303.93,428.18 C303.93,428.18 303.66,428.14 303.66,428.14 C303.66,428.14 303.84,428.16 303.84,428.16 C303.84,428.16 303.52,428.11 303.52,428.11 C303.52,428.11 303.67,428.12 303.67,428.12 C303.67,428.12 303.58,428.1 303.58,428.1 C303.58,428.1 303.49,428.3 303.49,428.11 C303.49,427.93 303.63,428.09 303.63,428.09 C303.63,428.09 303.45,428.1 303.45,428.1 C303.45,428.1 303.76,428.04 303.76,428.04 C303.76,428.04 303.73,428 303.73,428 C303.73,428 303.69,427.98 303.69,427.98 C303.69,427.98 303.71,428.13 303.71,428.13 C303.71,428.13 303.76,428.08 303.76,428.08 C303.76,428.08 303.8,428.06 303.8,428.06 C303.8,428.06 303.8,428.11 303.8,428.11 C303.8,428.11 303.58,428.16 303.58,428.16 C303.58,428.16 310.92,429.75 310.92,429.75 C310.92,429.75 310.91,429.75 310.91,429.75 C310.91,429.75 310.93,429.75 310.93,429.75 C310.93,429.75 310.9,429.75 310.9,429.75 C310.9,429.75 310.93,429.75 310.93,429.75 C310.93,429.75 310.92,429.74 310.92,429.74z ;M299.65 434.12 C299.65,434.12 299.7,434.13 299.7,434.13 C299.7,434.13 299.66,434.11 299.66,434.11 C299.66,434.11 299.64,434.14 299.64,434.14 C299.64,434.14 299.66,434.14 299.66,434.14 C299.66,434.14 299.72,434.15 299.72,434.15 C299.72,434.15 299.81,434.08 299.81,434.08 C299.81,434.08 299.72,434.11 299.72,434.11 C299.72,434.11 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 300.06,430.31 300.06,430.31 C300.06,430.31 299.78,430.27 299.78,430.27 C299.78,430.27 299.96,430.29 299.96,430.29 C299.96,430.29 299.65,430.25 299.65,430.25 C299.65,430.25 299.8,430.25 299.8,430.25 C299.8,430.25 299.7,430.24 299.7,430.24 C299.7,430.24 299.61,430.43 299.61,430.25 C299.61,430.06 299.75,430.22 299.75,430.22 C299.75,430.22 299.57,430.23 299.57,430.23 C299.57,430.23 299.89,430.17 299.89,430.17 C299.89,430.17 299.85,430.13 299.85,430.13 C299.85,430.13 299.82,430.12 299.82,430.12 C299.82,430.12 299.83,430.26 299.83,430.26 C299.83,430.26 299.89,430.21 299.89,430.21 C299.89,430.21 299.93,430.19 299.93,430.19 C299.93,430.19 299.93,430.25 299.93,430.25 C299.93,430.25 299.7,430.29 299.7,430.29 C299.7,430.29 299.65,434.13 299.65,434.13 C299.65,434.13 299.64,434.13 299.64,434.13 C299.64,434.13 299.66,434.13 299.66,434.13 C299.66,434.13 299.63,434.13 299.63,434.13 C299.63,434.13 299.65,434.13 299.65,434.13 C299.65,434.13 299.65,434.12 299.65,434.12z ;M292.83 434.12 C292.83,434.12 292.81,434.11 292.81,434.11 C292.81,434.11 292.84,434.12 292.84,434.12 C292.84,434.12 292.82,434.15 292.82,434.15 C292.82,434.15 292.85,434.15 292.85,434.15 C292.85,434.15 294.61,434.08 294.61,434.08 C294.61,434.08 298.37,434.07 298.37,434.07 C298.37,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.47,430.31 298.47,430.31 C298.47,430.31 294.44,430.33 294.44,430.33 C294.44,430.33 292.89,430.31 292.89,430.31 C292.89,430.31 292.69,430.25 292.69,430.25 C292.69,430.25 292.72,430.28 292.72,430.28 C292.72,430.28 292.63,430.26 292.63,430.26 C292.63,430.26 292.65,430.43 292.65,430.25 C292.65,430.06 292.56,430.15 292.56,430.15 C292.56,430.15 292.61,430.23 292.61,430.23 C292.61,430.23 292.93,430.17 292.93,430.17 C292.93,430.17 292.89,430.13 292.89,430.13 C292.89,430.13 292.85,430.12 292.85,430.12 C292.85,430.12 292.87,430.26 292.87,430.26 C292.87,430.26 292.93,430.21 292.93,430.21 C292.93,430.21 292.96,430.19 292.96,430.19 C292.96,430.19 292.96,430.25 292.96,430.25 C292.96,430.25 292.77,430.22 292.77,430.22 C292.77,430.22 292.83,434.13 292.83,434.13 C292.83,434.13 292.82,434.13 292.82,434.13 C292.82,434.13 292.84,434.13 292.84,434.13 C292.84,434.13 292.81,434.13 292.81,434.13 C292.81,434.13 292.83,434.13 292.83,434.13 C292.83,434.13 292.83,434.12 292.83,434.12z ;M286.91 434.04 C286.91,434.04 286.89,434.02 286.89,434.02 C286.89,434.02 286.92,434.03 286.92,434.03 C286.92,434.03 286.9,434.06 286.9,434.06 C286.9,434.06 286.92,434.06 286.92,434.06 C286.92,434.06 294.61,434.08 294.61,434.08 C294.61,434.08 298.39,434.03 298.39,434.03 C298.39,434.03 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.47,430.31 298.47,430.31 C298.47,430.31 294.44,430.33 294.44,430.33 C294.44,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 291.91,428.68 291.91,428.68 C291.91,428.68 291.82,428.67 291.82,428.67 C291.82,428.67 291.88,428.65 291.88,428.46 C291.88,428.28 291.78,428.37 291.78,428.37 C291.78,428.37 291.84,428.44 291.84,428.44 C291.84,428.44 292.15,428.39 292.15,428.39 C292.15,428.39 292.12,428.35 292.12,428.35 C292.12,428.35 292.08,428.33 292.08,428.33 C292.08,428.33 292.1,428.48 292.1,428.48 C292.1,428.48 292.15,428.42 292.15,428.42 C292.15,428.42 292.19,428.41 292.19,428.41 C292.19,428.41 292.19,428.46 292.19,428.46 C292.19,428.46 291.97,428.51 291.97,428.51 C291.97,428.51 287.14,434.07 287.14,434.07 C287.14,434.07 286.89,434.05 286.89,434.05 C286.89,434.05 286.92,434.05 286.92,434.05 C286.92,434.05 286.89,434.05 286.89,434.05 C286.89,434.05 286.91,434.05 286.91,434.05 C286.91,434.05 286.91,434.04 286.91,434.04z ;M286.7 429.47 C286.7,429.47 286.88,429.37 286.88,429.37 C286.88,429.37 286.52,429.45 286.52,429.45 C286.52,429.45 286.83,429.85 286.83,429.85 C286.83,429.85 286.14,434.18 286.14,434.18 C286.14,434.18 294.61,434.08 294.61,434.08 C294.61,434.08 298.37,434.08 298.37,434.08 C298.37,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.38,430.31 298.38,430.31 C298.38,430.31 294.56,430.33 294.56,430.33 C294.56,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 291.99,426.42 291.99,426.42 C291.99,426.42 291.87,426.34 291.87,426.34 C291.87,426.34 292.01,426.25 292.01,426.07 C292.01,425.88 292.05,425.99 292.05,425.99 C292.05,425.99 291.97,425.95 291.97,425.95 C291.97,425.95 292.39,425.98 292.39,425.98 C292.39,425.98 292.27,426.05 292.27,426.05 C292.27,426.05 292.35,425.99 292.35,425.99 C292.35,425.99 292.32,426 292.32,426 C292.32,426 292.4,426 292.4,426 C292.4,426 292.4,426.06 292.4,426.06 C292.4,426.06 292.39,426.05 292.39,426.05 C292.39,426.05 292.62,426.45 292.62,426.45 C292.62,426.45 286.78,429.41 286.78,429.41 C286.78,429.41 286.55,429.2 286.55,429.2 C286.55,429.2 286.62,429.38 286.62,429.38 C286.62,429.38 286.51,429.44 286.51,429.44 C286.51,429.44 286.46,429.37 286.46,429.37 C286.46,429.37 286.7,429.47 286.7,429.47z ;M286.5 424.9 C286.5,424.9 286.87,424.72 286.87,424.72 C286.87,424.72 286.13,424.87 286.13,424.87 C286.13,424.87 286.76,425.64 286.76,425.64 C286.76,425.64 285.37,434.3 285.37,434.3 C285.37,434.3 294.63,434.09 294.63,434.09 C294.63,434.09 298.37,434.09 298.37,434.09 C298.37,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.36,430.31 298.36,430.31 C298.36,430.31 294.59,430.33 294.59,430.33 C294.59,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.06,424.16 292.06,424.16 C292.06,424.16 291.91,424.01 291.91,424.01 C291.91,424.01 292.13,423.86 292.13,423.68 C292.13,423.49 292.32,423.6 292.32,423.6 C292.32,423.6 292.1,423.46 292.1,423.46 C292.1,423.46 292.62,423.57 292.62,423.57 C292.62,423.57 292.43,423.75 292.43,423.75 C292.43,423.75 292.62,423.64 292.62,423.64 C292.62,423.64 292.54,423.53 292.54,423.53 C292.54,423.53 292.65,423.57 292.65,423.57 C292.65,423.57 292.62,423.72 292.62,423.72 C292.62,423.72 292.58,423.64 292.58,423.64 C292.58,423.64 293.27,424.39 293.27,424.39 C293.27,424.39 286.43,424.75 286.43,424.75 C286.43,424.75 286.2,424.35 286.2,424.35 C286.2,424.35 286.31,424.72 286.31,424.72 C286.31,424.72 286.13,424.83 286.13,424.83 C286.13,424.83 286.02,424.68 286.02,424.68 C286.02,424.68 286.5,424.9 286.5,424.9z ;M285.53 417.93 C285.53,417.93 285.61,418.01 285.61,418.01 C285.61,418.01 285.39,417.97 285.39,417.97 C285.39,417.97 285.68,418.12 285.68,418.12 C285.68,418.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.38,434.11 298.38,434.11 C298.38,434.11 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.71,430.31 298.71,430.31 C298.71,430.31 293.3,430.31 293.3,430.31 C293.3,430.31 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.26,417.75 291.26,417.56 C291.26,417.38 291.34,417.38 291.34,417.38 C291.34,417.38 291.45,417.54 291.45,417.54 C291.45,417.54 291.21,417.5 291.21,417.5 C291.21,417.5 291.32,417.45 291.32,417.45 C291.32,417.45 291.28,417.51 291.28,417.51 C291.28,417.51 291.5,417.56 291.5,417.56 C291.5,417.56 291.52,417.54 291.52,417.54 C291.52,417.54 291.45,417.6 291.45,417.6 C291.45,417.6 291.43,417.67 291.43,417.67 C291.43,417.67 291.41,417.89 291.41,417.89 C291.41,417.89 291.24,417.95 291.24,417.95 C291.24,417.95 285.98,417.86 285.98,417.86 C285.98,417.86 286.02,417.69 286.02,417.69 C286.02,417.69 285.92,417.77 285.92,417.77 C285.92,417.77 285.81,417.62 285.81,417.62 C285.81,417.62 285.53,417.93 285.53,417.93z ;M284.93 404.18 C284.93,404.18 281.14,411.97 281.14,411.97 C281.14,411.97 273.88,412.04 273.88,412.04 C273.88,412.04 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.36,434.08 298.36,434.08 C298.36,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.35,430.31 298.35,430.31 C298.35,430.31 294.59,430.32 294.59,430.32 C294.59,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 291.91,415.81 291.91,415.81 C291.91,415.81 291.8,415.82 291.8,415.82 C291.8,415.82 291.88,415.73 291.88,415.73 C291.88,415.73 291.9,415.66 291.9,415.66 C291.9,415.66 291.8,415.65 291.8,415.65 C291.8,415.65 291.73,415.73 291.73,415.73 C291.73,415.73 291.87,415.58 291.87,415.58 C291.87,415.58 291.87,415.71 291.87,415.71 C291.87,415.71 291.83,415.72 291.83,415.72 C291.83,415.72 291.82,415.71 291.82,415.71 C291.82,415.71 291.66,414.92 291.66,414.92 C291.66,414.92 291.45,413.38 291.45,413.38 C291.45,413.38 291.09,411.81 291.09,411.81 C291.09,411.81 291.05,411.77 291.05,411.77 C291.05,411.77 289.08,410.26 289.08,410.26 C289.08,410.26 284.93,404.18 284.93,404.18z ;M298.66 404.21 C298.66,404.21 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.09 294.61,434.09 C294.61,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.76,430.32 298.76,430.32 C298.76,430.32 294.62,430.33 294.62,430.33 C294.62,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 300.75,413.19 300.75,413.19 C300.75,413.19 300.74,413.2 300.74,413.2 C300.74,413.2 300.68,413.28 300.68,413.28 C300.68,413.28 300.74,413.15 300.74,413.15 C300.74,413.15 300.76,413.19 300.76,413.19 C300.76,413.19 300.77,413.17 300.77,413.17 C300.77,413.17 303.55,406.44 303.55,406.44 C303.55,406.44 302.85,404.47 302.85,404.47 C302.85,404.47 301.29,403.47 301.29,403.47 C301.29,403.47 301.18,403.32 301.18,403.32 C301.18,403.32 298.66,404.21 298.66,404.21z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.07 294.61,434.07 C294.61,434.07 298.36,434.07 298.36,434.07 C298.36,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 307.04,419.79 307.04,419.79 C307.04,419.79 301.92,423.68 301.92,423.68 C301.92,423.68 298.73,430.31 298.73,430.31 C298.73,430.31 293.3,430.33 293.3,430.33 C293.3,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 302.59,416.02 302.59,416.02 C302.59,416.02 302.55,415.98 302.55,415.98 C302.55,415.98 302.63,415.99 302.63,415.99 C302.63,415.99 306.67,409.55 306.67,409.55 C306.67,409.55 306.65,409.61 306.65,409.61 C306.65,409.61 306.59,409.55 306.59,409.55 C306.59,409.55 306.69,409.72 306.69,409.72 C306.69,409.72 306.58,409.57 306.58,409.57 C306.58,409.57 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.09 294.61,434.09 C294.61,434.09 298.36,434.09 298.36,434.09 C298.36,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.29,420.1 306.29,420.1 C306.29,420.1 301.7,423.39 301.7,423.39 C301.7,423.39 298.38,430.31 298.38,430.31 C298.38,430.31 293.4,430.32 293.4,430.32 C293.4,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 302.63,417.02 302.63,417.02 C302.63,417.02 302.61,416.97 302.61,416.97 C302.61,416.97 302.63,416.9 302.63,416.9 C302.63,416.9 307.12,415.55 307.12,415.55 C307.12,415.55 307.51,415.47 307.51,415.47 C307.51,415.47 307.52,415.47 307.52,415.47 C307.52,415.47 309.01,412.56 309.01,412.56 C309.01,412.56 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.6,434.08 294.6,434.08 C294.6,434.08 298.37,434.07 298.37,434.07 C298.37,434.07 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.05,420.2 306.05,420.2 C306.05,420.2 301.63,423.29 301.63,423.29 C301.63,423.29 298.57,430.33 298.57,430.33 C298.57,430.33 293.35,430.32 293.35,430.32 C293.35,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 300.2,418.16 300.2,418.16 C300.2,418.16 306.72,417.16 306.72,417.16 C306.72,417.16 307.56,417.29 307.56,417.29 C307.56,417.29 307.59,417.33 307.59,417.33 C307.59,417.33 308.54,413.47 308.54,413.47 C308.54,413.47 306.71,408.22 306.71,408.22 C306.71,408.22 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.62,434.09 294.62,434.09 C294.62,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 305.8,420.3 305.8,420.3 C305.8,420.3 301.55,423.2 301.55,423.2 C301.55,423.2 298.74,430.31 298.74,430.31 C298.74,430.31 293.34,430.32 293.34,430.32 C293.34,430.32 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 300.2,418.16 300.2,418.16 C300.2,418.16 306.32,418.77 306.32,418.77 C306.32,418.77 307.34,417.78 307.34,417.78 C307.34,417.78 307.74,418.52 307.74,418.52 C307.74,418.52 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.6,434.09 294.6,434.09 C294.6,434.09 298.35,434.08 298.35,434.08 C298.35,434.08 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 305.98,420.3 305.98,420.3 C305.98,420.3 301.72,423.59 301.72,423.59 C301.72,423.59 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 297.55,418.67 297.55,418.67 C297.55,418.67 296.68,421.72 296.68,421.72 C296.68,421.72 300.57,423.18 300.57,423.18 C300.57,423.18 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 294.61,434.08 294.61,434.08 C294.61,434.08 298.36,434.09 298.36,434.09 C298.36,434.09 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.41,419.97 306.41,419.97 C306.41,419.97 301.7,423.64 301.7,423.64 C301.7,423.64 298.69,430.31 298.69,430.31 C298.69,430.31 294.56,430.33 294.56,430.33 C294.56,430.33 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 294.58,430.33 294.58,430.33 C294.58,430.33 298.38,430.31 298.38,430.31 C298.38,430.31 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.73,439.55 293.73,439.55 C293.73,439.55 298.46,439.54 298.46,439.54 C298.46,439.54 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.43,419.98 306.43,419.98 C306.43,419.98 301.75,423.57 301.75,423.57 C301.75,423.57 298.73,430.27 298.73,430.27 C298.73,430.27 293.72,430.3 293.72,430.3 C293.72,430.3 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.7,430.31 293.7,430.31 C293.7,430.31 298.74,430.26 298.74,430.26 C298.74,430.26 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z ;M301.92 404.95 C301.92,404.95 293.25,405.03 293.25,405.03 C293.25,405.03 285.98,405.1 285.98,405.1 C285.98,405.1 284.05,419.12 284.05,419.12 C284.05,419.12 285.37,434.3 285.37,434.3 C285.37,434.3 293.25,442.25 293.25,442.25 C293.25,442.25 298.5,442.3 298.5,442.3 C298.5,442.3 299.74,434.68 299.74,434.68 C299.74,434.68 303.69,434.6 303.69,434.6 C303.69,434.6 306.59,433.87 306.59,433.87 C306.59,433.87 311.49,430.09 311.49,430.09 C311.49,430.09 310.52,426.16 310.86,425.35 C311.19,424.53 310.82,424.83 310.82,423.97 C310.82,423.12 310.56,422.53 310.56,422.53 C310.56,422.53 308.71,419.49 308.71,419.49 C308.71,419.49 306.45,419.99 306.45,419.99 C306.45,419.99 301.77,423.53 301.77,423.53 C301.77,423.53 298.75,430.25 298.75,430.25 C298.75,430.25 293.3,430.28 293.3,430.28 C293.3,430.28 293.04,430.16 293.04,430.16 C293.04,430.16 291.91,428.46 291.91,428.46 C291.91,428.46 292.21,418.97 292.21,418.97 C292.21,418.97 291.95,418.04 291.95,418.04 C291.95,418.04 291.91,416.23 291.91,416.04 C291.91,415.86 292.25,414.59 292.25,414.59 C292.25,414.59 293.88,413.41 293.88,413.41 C293.88,413.41 294.99,412.85 294.99,412.85 C294.99,412.85 297.18,412.81 297.18,412.81 C297.18,412.81 299.59,413 299.59,413 C299.59,413 301.89,414.22 301.89,414.22 C301.89,414.22 302.37,415.82 302.37,415.82 C302.37,415.82 301.74,416.82 301.74,416.82 C301.74,416.82 292.58,424.16 292.58,424.16 C292.58,424.16 293.3,430.28 293.3,430.28 C293.3,430.28 298.75,430.25 298.75,430.25 C298.75,430.25 301.74,423.57 301.74,423.57 C301.74,423.57 306.45,419.97 306.45,419.97 C306.45,419.97 308.08,414.37 308.08,414.37 C308.08,414.37 310.3,409.7 310.3,409.7 C310.3,409.7 301.92,404.95 301.92,404.95z " keySplines="0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0.167 0.167 0.833 0.833;0 0 0 0" calcMode="spline"></animate> <clipPath id="_R_G_L_0_C_0"><path id="_R_G_L_0_C_0_P_0" fill-rule="nonzero"></path></clipPath> <animate attributeType="XML" attributeName="opacity" dur="2s" from="0" to="1" xlink:href="#time_group"></animate></defs> <g id="_R_G"><g id="_R_G_L_1_G" transform=" translate(127.638, 127.945) scale(3.37139, 3.37139) translate(-297.638, -420.945)"><g clip-path="url(#_R_G_L_1_C_0)"><path id="_R_G_L_1_G_G_0_D_0_P_0" fill-opacity="1" fill-rule="nonzero" d=" M328 398.61 C328,398.61 328,446.23 328,446.23 C328,449.7 325.2,452.5 321.75,452.5 C321.75,452.5 274.25,452.5 274.25,452.5 C270.8,452.5 268,449.7 268,446.23 C268,446.23 268,398.61 268,398.61 C268,395.15 270.8,392.35 274.25,392.35 C274.25,392.35 283.46,392.26 283.46,392.26 C283.46,392.26 283.46,390.38 283.46,390.38 C283.46,389.76 284.08,388.5 285.33,388.5 C286.58,388.5 287.21,389.75 287.21,390.38 C287.21,390.38 287.21,397.89 287.21,397.89 C287.21,398.53 286.59,399.78 285.33,399.78 C284.08,399.78 283.46,398.53 283.46,397.9 C283.46,397.9 283.46,396.02 283.46,396.02 C283.46,396.02 275.5,396.1 275.5,396.1 C273.43,396.1 271.75,397.79 271.75,399.86 C271.75,399.86 271.75,444.98 271.75,444.98 C271.75,447.06 273.43,448.74 275.5,448.74 C275.5,448.74 320.5,448.74 320.5,448.74 C322.57,448.74 324.25,447.06 324.25,444.98 C324.25,444.98 324.25,399.86 324.25,399.86 C324.25,397.79 322.57,396.1 320.5,396.1 C320.5,396.1 312.62,396.1 312.62,396.1 C312.62,396.1 312.63,397.06 312.63,397.99 C312.63,398.61 312,399.86 310.75,399.86 C309.5,399.86 308.88,398.61 308.88,397.98 C308.88,397.98 308.87,396.1 308.87,396.1 C308.87,396.1 301.88,396.1 301.88,396.1 C300.84,396.1 300,395.26 300,394.23 C300,393.19 300.84,392.35 301.88,392.35 C301.88,392.35 308.87,392.35 308.87,392.35 C308.87,392.35 308.87,390.47 308.87,390.47 C308.87,389.83 309.5,388.5 310.75,388.5 C312,388.5 312.62,389.84 312.62,390.47 C312.62,390.47 312.62,392.35 312.62,392.35 C312.62,392.35 321.75,392.35 321.75,392.35 C325.2,392.35 328,395.15 328,398.61z " fill="{bpa-col-primary-color}"></path></g></g> <g id="_R_G_L_0_G" transform=" translate(125.555, 126.412) scale(3.37139, 3.37139) translate(-297.638, -420.945)"><g clip-path="url(#_R_G_L_0_C_0)"><path id="_R_G_L_0_G_G_0_D_0_P_0" fill-opacity="1" fill-rule="nonzero" d=" M305.86 420.29 C305.86,420.29 307.11,419.04 307.11,415.28 C307.11,409.01 303.36,407.76 298.36,407.76 C298.36,407.76 287.11,407.76 287.11,407.76 C287.11,407.76 287.11,434.08 287.11,434.08 C287.11,434.08 294.61,434.08 294.61,434.08 C294.61,434.08 294.61,441.6 294.61,441.6 C294.61,441.6 298.36,441.6 298.36,441.6 C298.36,441.6 298.36,434.08 298.36,434.08 C302.71,434.08 305.73,434.08 307.98,431.3 C309.07,429.95 309.62,428.24 309.61,426.5 C309.61,425.58 309.51,424.67 309.3,424.05 C308.73,422.65 308.36,421.55 305.86,420.29z  M302.11 430.32 C302.11,430.32 298.36,430.32 298.36,430.32 C298.36,430.32 298.36,426.56 298.36,426.56 C298.36,424.48 300.03,422.8 302.11,422.8 C304.13,422.8 305.86,424.43 305.86,426.56 C305.86,428.78 304.03,430.32 302.11,430.32z  M299.07 419.95 C298.43,420.26 297.82,420.63 297.26,421.05 C295.87,422.1 294.61,423.58 294.61,426.56 C294.61,426.56 294.61,430.32 294.61,430.32 C294.61,430.32 290.86,430.32 290.86,430.32 C290.86,430.32 290.86,411.52 290.86,411.52 C290.86,411.52 298.36,411.52 298.36,411.52 C301.35,411.52 303.36,412.77 303.36,415.28 C303.36,417.58 301.65,418.68 299.07,419.95z " fill="{bpa-col-primary-color}"></path></g></g></g> <g id="time_group"></g></svg>';

            $bpa_loader_content = str_replace( '{bpa-col-primary-color}', $primary_color, $bpa_loader_string );    

            $destination = BOOKINGPRESS_DIR . '/images/bpa-loader.svg';
            if (! function_exists('WP_Filesystem') ) {
                include_once ABSPATH . 'wp-admin/includes/file.php';
            }

            WP_Filesystem();
            global $wp_filesystem;

            $wp_filesystem->put_contents( $destination, $bpa_loader_content, 0777 );
        }
        
        /**
         * Ajax field for update field positions
         *
         * @return void
         */
        function bookingpress_update_field_pos_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'update_field_position', true, 'bpa_wp_nonce' );
            
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

            $bookingpress_old_index = isset($_POST['old_index']) ? ( intval($_POST['old_index']) + 1 ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_new_index = isset($_POST['new_index']) ? ( intval($_POST['new_index']) + 1 ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_update_id = ! empty($_POST['update_id']) ? intval($_POST['update_id']) : 0; // phpcs:ignore WordPress.Security.NonceVerification

            $fields    = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_form_fields . ' order by bookingpress_field_position ASC', ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
            $i = 1;
            foreach ( $fields as $field ) {
                $args = array('bookingpress_field_position' => $i);
                $wpdb->update($tbl_bookingpress_form_fields, $args, array( 'bookingpress_form_field_id' => $field['bookingpress_form_field_id'] ));
               $i++;     
            }
            if (isset($_POST['old_index']) && isset($_POST['new_index']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                if ($bookingpress_new_index > $bookingpress_old_index ) {
                    $condition = 'BETWEEN ' . $bookingpress_old_index . ' AND ' . $bookingpress_new_index;
                    $fields    = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_field_position BETWEEN %d AND %d order by bookingpress_field_position ASC', $bookingperss_old_index, $bookingpress_new_index ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                    foreach ( $fields as $field ) {
                        $position = $field['bookingpress_field_position'] - 1;
                        $position = ( $field['bookingpress_field_position'] == $bookingpress_old_index ) ? $bookingpress_new_index : $position;
                        $args     = array(
                         'bookingpress_field_position' => $position,
                        );
                        $wpdb->update($tbl_bookingpress_form_fields, $args, array( 'bookingpress_form_field_id' => $field['bookingpress_form_field_id'] ));
                    }
                } else {
                    $fields = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_field_position BETWEEN %d AND %d order by bookingpress_field_position ASC', $bookingpress_new_index, $bookingpress_old_index ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                    foreach ( $fields as $field ) {
                        $position = $field['bookingpress_field_position'] + 1;
                        $position = ( $field['bookingpress_field_position'] == $bookingpress_old_index ) ? $bookingpress_new_index : $position;
                        $args     = array(
                        'bookingpress_field_position' => $position,
                        );
                        $wpdb->update($tbl_bookingpress_form_fields, $args, array( 'bookingpress_form_field_id' => $field['bookingpress_form_field_id'] ));
                    }
                }
                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Field position has been changed successfully.', 'bookingpress-appointment-booking');
            }

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Load customize field settings
         *
         * @return void
         */
        function bookingpress_load_field_settings_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_form_fields', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            if( $BookingPress->bpa_is_pro_exists() && !$BookingPress->bpa_is_pro_active() ){
                if( empty( $BookingPress->bpa_pro_plugin_version() ) ){
                    $bookingpress_field_settings_data               = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                } else {
                    $bookingpress_field_settings_data               = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d ORDER BY bookingpress_field_position ASC", 1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                }
            } else {
                $bookingpress_field_settings_data               = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
            }


            $bookingpress_field_settings_data        = apply_filters('bookingpress_modify_field_data_before_prepare', $bookingpress_field_settings_data);
            
            $bookingpress_field_settings_return_data = array();
            foreach ( $bookingpress_field_settings_data as $bookingpress_field_setting_key => $bookingpress_field_setting_val ) {
                $bookingpress_field_type = '';
                if ($bookingpress_field_setting_val['bookingpress_form_field_name'] == 'fullname' ) {
                    $bookingpress_field_type = 'Text';
                } elseif ($bookingpress_field_setting_val['bookingpress_form_field_name'] == 'firstname' ) {
                    $bookingpress_field_type = 'Text';
                } elseif ($bookingpress_field_setting_val['bookingpress_form_field_name'] == 'lastname' ) {
                    $bookingpress_field_type = 'Text';
                } elseif ($bookingpress_field_setting_val['bookingpress_form_field_name'] == 'email_address' ) {
                    $bookingpress_field_type = 'Email';
                } elseif ($bookingpress_field_setting_val['bookingpress_form_field_name'] == 'phone_number' ) {
                    $bookingpress_field_type = 'Dropdown';
                } elseif ($bookingpress_field_setting_val['bookingpress_form_field_name'] == 'note' ) {
                    $bookingpress_field_type = 'Textarea';
                } elseif($bookingpress_field_setting_val['bookingpress_form_field_name'] == 'terms_and_conditions' ) {
                    $bookingpress_field_type = 'terms_and_conditions';
                } elseif($bookingpress_field_setting_val['bookingpress_form_field_name'] == 'username' ) {
                    $bookingpress_field_type = 'Text';
                } else {
                    $bookingpress_field_type = $bookingpress_field_setting_val['bookingpress_field_type'];
                }

                $bookingpress_draggable_field_setting_fields_tmp                   = array();
                $bookingpress_draggable_field_setting_fields_tmp['id']             = intval($bookingpress_field_setting_val['bookingpress_form_field_id']);
                $bookingpress_draggable_field_setting_fields_tmp['field_name']     = $bookingpress_field_setting_val['bookingpress_form_field_name'];
                $bookingpress_draggable_field_setting_fields_tmp['field_type']     = $bookingpress_field_type;
                $bookingpress_draggable_field_setting_fields_tmp['is_edit']        = false;
                $bookingpress_draggable_field_setting_fields_tmp['is_required']    = ( $bookingpress_field_setting_val['bookingpress_field_required'] == 0 ) ? false : true;
                $bookingpress_draggable_field_setting_fields_tmp['label']          = stripslashes_deep($bookingpress_field_setting_val['bookingpress_field_label']);
                $bookingpress_draggable_field_setting_fields_tmp['placeholder']    = stripslashes_deep($bookingpress_field_setting_val['bookingpress_field_placeholder']);
                $bookingpress_draggable_field_setting_fields_tmp['error_message']  = stripslashes_deep($bookingpress_field_setting_val['bookingpress_field_error_message']);
                $bookingpress_draggable_field_setting_fields_tmp['is_hide']        = ( $bookingpress_field_setting_val['bookingpress_field_is_hide'] == 0 ) ? false : true;
                $bookingpress_draggable_field_setting_fields_tmp['field_position'] = floatval($bookingpress_field_setting_val['bookingpress_field_position']);

                $bookingpress_draggable_field_setting_fields_tmp = apply_filters('bookingpress_modify_field_data_before_load', $bookingpress_draggable_field_setting_fields_tmp, $bookingpress_field_setting_val);

                array_push($bookingpress_field_settings_return_data, $bookingpress_draggable_field_setting_fields_tmp);
            }

            $response['variant']        = 'success';
            $response['title']          = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']            = esc_html__('Field Settings Data Retrieved Successfully', 'bookingpress-appointment-booking');
            $response['field_settings'] = $bookingpress_field_settings_return_data;

            /* Add language addon filter */
            $response = apply_filters( 'bookingpress_modified_load_custom_fields_response',$response);             

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Save custom field settings data
         *
         * @return void
         */
        function bookingpress_save_field_settings_data_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'save_form_fields', true, 'bpa_wp_nonce' );
            
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

            if( !empty( $_POST['field_settings'] ) && !is_array( $_POST['field_settings'] ) ){ //phpcs:ignore
                $_POST['field_settings'] = json_decode( stripslashes_deep( $_POST['field_settings'] ), true ); //phpcs:ignore
				$_POST['field_settings'] = $this->bookingpress_boolean_type_cast( $_POST['field_settings'] ); //phpcs:ignore
            }
            $bookingpress_field_settings_data = ! empty($_POST['field_settings']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['field_settings']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            
            if (! empty($bookingpress_field_settings_data) ) {
                foreach ( $bookingpress_field_settings_data as $bookingpress_field_setting_key => $bookingpress_field_setting_val ) {
                    $bookingpress_field_name = $bookingpress_field_setting_val['field_name'];
                    $bookingpress_field_is_required = isset($bookingpress_field_setting_val['is_required']) ? (( $bookingpress_field_setting_val['is_required'] == 'false' ) ? 0 : 1) : 1;
                    
                    $bookingpress_db_fields = array(
                        'bookingpress_form_field_name'     => $bookingpress_field_name,
                        'bookingpress_field_required'      => $bookingpress_field_is_required,
                        'bookingpress_field_label'         => stripslashes_deep($bookingpress_field_setting_val['label']),
                        'bookingpress_field_placeholder'   => $bookingpress_field_setting_val['placeholder'],
                        'bookingpress_field_error_message' => $bookingpress_field_setting_val['error_message'],
                        'bookingpress_field_is_hide'       => ( $bookingpress_field_setting_val['is_hide'] == 'false' ) ? 0 : 1,
                        'bookingpress_field_position'      => $bookingpress_field_setting_val['field_position'],
                    );

                    $bpa_existing_field_id = $bookingpress_field_setting_val['id'];

                    $bookingpress_db_fields = apply_filters('bookingpress_modify_form_field_data_before_save', $bookingpress_db_fields, $bookingpress_field_setting_val);

                    $field_exist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_form_field_id) as total FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $bpa_existing_field_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                    if ($field_exist > 0 ) {
                        $wpdb->update($tbl_bookingpress_form_fields, $bookingpress_db_fields, array( 'bookingpress_form_field_id' => $bpa_existing_field_id ));
                    } else {
                        $wpdb->insert($tbl_bookingpress_form_fields, $bookingpress_db_fields);
                        $bpa_existing_field_id = $wpdb->insert_id;
                        $bookingpress_field_settings_data[$bookingpress_field_setting_key]['id'] = $bpa_existing_field_id;
                    }

                    do_action('bookingpress_insert_inner_fields', $bookingpress_db_fields, $bookingpress_field_setting_key, $bpa_existing_field_id);
                }

                do_action('bookingpress_delete_removed_fields', $bookingpress_field_settings_data);

                /* Add new action for language addon */
                do_action('bookingpress_after_save_custom_form_fields');

                wp_cache_delete( 'bpa_appointment_booking_form_fields_data_' );

                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Field Settings Data Saved Successfully', 'bookingpress-appointment-booking');
            }

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Save my-bookings settings data
         *
         * @return void
         */
        function bookingpress_save_my_booking_settings_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_customize_settings, $bookingpress_global_options;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'save_mybooking_settings', true, 'bpa_wp_nonce' );
            
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

            $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_allow_tag = json_decode($bookingpress_global_options_data['allowed_html'], true);

         // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $bookingpress_colorpicker_data = ! empty($_POST['my_booking_selected_colorpicker_values']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['my_booking_selected_colorpicker_values']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_font_values_data = ! empty($_POST['my_booking_selected_font_values']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['my_booking_selected_font_values']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            $bookingpress_settings_data    = ! empty($_POST['my_booking_field_settings']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['my_booking_field_settings']) : array(); // phpcs:ignore WordPress.Security.NonceVerification
            //$bookingpress_delete_account_content = !empty($_POST['delete_account_content']) ? wp_kses($_POST['delete_account_content'], $bookingpress_allow_tag) : '';
            $bookingpress_delete_account_content = !empty($_POST['delete_account_content']) ? stripslashes($_POST['delete_account_content']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
         // phpcs:enable
            $bookingpress_customize_settings_data = array_merge($bookingpress_colorpicker_data, $bookingpress_font_values_data, $bookingpress_settings_data);
            $bookingpress_customize_settings_data['delete_account_content'] = $bookingpress_delete_account_content;
            if (! empty($bookingpress_customize_settings_data) ) {
                foreach ( $bookingpress_customize_settings_data as $bookingpress_setting_key => $bookingpress_setting_val ) {
                    $bookingpress_db_fields = array(
                    'bookingpress_setting_name'  => $bookingpress_setting_key,
                    'bookingpress_setting_value' => $bookingpress_setting_val,
                    'bookingpress_setting_type'  => 'booking_my_booking',
                    );

                    $is_setting_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_setting_id) as total FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_name = %s AND bookingpress_setting_type = 'booking_my_booking'", $bookingpress_setting_key )); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally. False Positive alarm
                    if ($is_setting_exists > 0 ) {
                        $wpdb->update(
                            $tbl_bookingpress_customize_settings,
                            $bookingpress_db_fields,
                            array(
                            'bookingpress_setting_name' => $bookingpress_setting_key,
                            'bookingpress_setting_type' => 'booking_my_booking',
                            )
                        );
                    } else {
                        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
                    }
                }
                $my_booking_form = array(         
                    'background_color'     => $bookingpress_colorpicker_data['background_color'],
                    'row_background_color' => $bookingpress_colorpicker_data['footer_background_color'],
                    'border_color'         => $bookingpress_colorpicker_data['border_color'],
                    'primary_color'        => $bookingpress_colorpicker_data['primary_color'],
                    'label_title_color'    => $bookingpress_colorpicker_data['label_title_color'],
                    'sub_title_color'      => $bookingpress_colorpicker_data['sub_title_color'],
                    'content_color'        => $bookingpress_colorpicker_data['content_color'],
                    'title_font_family'    => $bookingpress_font_values_data['title_font_family'],
                    'price_button_text_color' => $bookingpress_colorpicker_data['price_button_text_color'],
                );
                $bookingpress_action[] = 'bookingpress_save_my_booking_settings';
                $booking_form = array();
                $bookingpress_custom_data_arr = array(                    
                    'my_booking_form' => $my_booking_form, 
                    'booking_form' => $booking_form, 
                    'action' => $bookingpress_action,
                );
                $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);
                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Customize settings updated successfully.', 'bookingpress-appointment-booking');
            }

            wp_cache_delete( 'bookingpress_all_general_settings' );
            wp_cache_delete( 'bookingpress_all_customize_settings' );

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Customize module helper variables
         *
         * @return void
         */
        function bookingpress_customize_helper_vars_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            ?>
            var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
            ELEMENT.locale(lang)
            <?php
        }
        
        /**
         * Register component for customize module
         *
         * @return void
         */
        function bookingpress_dynamic_components_func()
        {
            ?>
                'vue-cal': vuecal
            <?php
        }
        
        /**
         * Customize module methods / functions
         *
         * @return void
         */
        function bookingpress_dynamic_vue_methods_func()
        {
            global $bookingpress_notification_duration;
            ?>
                bookingpress_toggle_calendar(){
                    const vm = this
                    vm.$refs.bookingpress_range_calendar.togglePopover();
                },
                bpa_select_category(selected_category){
                    const vm = this
                    vm.bookingpress_shortcode_form.selected_category = selected_category
                },
                bpa_select_service(selected_service){
                    const vm = this
                    vm.bookingpress_shortcode_form.selected_service = selected_service
                },
                bpa_select_time(selected_time){
                    const vm = this
                    vm.bookingpress_shortcode_form.selected_time = selected_time
                },
                bpa_select_primary_color(selected_color){
                    var opacity_color = Math.round(Math.min(Math.max(0.12 || 1, 0), 1) * 255);
                    var primary_background_color = selected_color+(opacity_color.toString(16).toUpperCase())
                    this.selected_colorpicker_values.primary_background_color = primary_background_color
                    this.bookingpress_change_border_color();
                },
                bpa_reset_bookingform(){
                    const vm = this
                    vm.selected_colorpicker_values.background_color = '#FFF'
                    vm.selected_colorpicker_values.footer_background_color = '#f4f7fb'
                    vm.selected_colorpicker_values.border_color = '#CFD6E5'
                    vm.selected_colorpicker_values.primary_color = '#12D488'
                    vm.selected_colorpicker_values.primary_background_color = '#e2faf1'
                    vm.selected_colorpicker_values.label_title_color = '#202C45'
                    vm.selected_colorpicker_values.content_color = '#727E95'
                    vm.selected_colorpicker_values.price_button_text_color = '#fff'                    
                    vm.selected_font_values.title_font_family = 'Poppins'
                    vm.selected_colorpicker_values.sub_title_color = '#535D71'
                    vm.selected_colorpicker_values.border_alpha_color = '';
                    vm.bookingpress_change_border_color();
                    <?php do_action('bookingpress_reset_color_option_after'); ?>
                },      
                bpa_save_booking_form_settings_data(){
                    const vm2 = this
                    var postData = []
                    postData.action = 'bookingpress_save_form_settings'
                    
                    postData.tab_container_data = vm2.tab_container_data
                    postData.category_container_data = vm2.category_container_data
                    postData.service_container_data = vm2.service_container_data
                    postData.timeslot_container_data = vm2.timeslot_container_data
                    postData.colorpicker_values = vm2.selected_colorpicker_values
                    postData.font_values = vm2.selected_font_values
                    postData.front_label_edit_data = vm2.front_label_edit_data
                    postData.booking_form_settings = vm2.booking_form_settings
                    postData.summary_container_data = vm2.summary_container_data
                    <?php do_action('bookingpress_before_save_customize_form_settings'); ?>
                    postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.variant == 'error'){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });    
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
                bpa_save_field_settings_data(){
                    const vm2 = this
                    var postData = [];
            <?php do_action('bookingpress_before_save_field_settings_method'); ?>
                    postData.action = 'bookingpress_save_field_settings'
                    postData.field_settings = JSON.stringify(vm2.field_settings_fields);
                    postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.variant == 'error'){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',                                
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });    
                        }
            <?php do_action('bookingpress_after_save_field_settings_method'); ?>
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
                bpa_save_field_my_booking_data() {
                    const vm2 = this
                    var postData = []
                    postData.action = 'bookingpress_save_my_booking_settings'
                    postData.my_booking_field_settings = vm2.my_booking_field_settings
                    postData.my_booking_selected_font_values = vm2.selected_font_values
                    postData.my_booking_selected_colorpicker_values = vm2.selected_colorpicker_values
                    postData.delete_account_content = vm2.delete_account_content
                    postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.variant == 'error'){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });    
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
                bpa_save_customize_settings(action){
                    const vm = this
                    vm.is_display_save_loader = '1'
                    vm.is_disabled = 1
                    let bpa_validation_flag = false

                    if(action == 'form_fields') {
                        vm.bpa_save_field_settings_data()
                    } else {           
                        
                        <?php do_action('bookingpress_before_save_customize_other_settings_data'); ?>

                        if( !( bpa_validation_flag ) ){

                            vm.bpa_save_booking_form_settings_data()                    
                            vm.bpa_save_field_my_booking_data()    
                            <?php do_action('bookingpress_save_customize_other_settings_data'); ?>
                        }
                    }

                    if( !( bpa_validation_flag ) ){

                        setTimeout(function(){
                            vm.is_display_save_loader = '0'
                            vm.is_disabled = 0
                            vm.$notify({
                                title: '<?php esc_html_e('Success', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Customization settings saved successfully.', 'bookingpress-appointment-booking'); ?>',
                                type: 'success',
                                customClass: 'success_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        }, 3000);

                    } else {

                        vm.is_display_save_loader = '0'
                        vm.is_disabled = 0
                        vm.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('You can not set the "Date & Time" step as the first step in the process.', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    }
                },
                bookingpress_load_booking_form_data(){
                    const vm2 = this
                    var postData = []
                    postData.action = 'bookingpress_load_bookingform_data'
                    postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.variant == 'error'){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        }else{
                            vm2.tab_container_data = response.data.formdata.tab_container_data
                            vm2.category_container_data = response.data.formdata.category_container_data
                            vm2.service_container_data = response.data.formdata.service_container_data
                            vm2.timeslot_container_data = response.data.formdata.timeslot_container_data
                            vm2.selected_colorpicker_values = response.data.formdata.colorpicker_values
                            vm2.selected_font_values.title_font_family = response.data.formdata.font_values.title_font_family
                            vm2.booking_form_settings = response.data.formdata.booking_form_settings
                            vm2.summary_container_data = response.data.formdata.summary_container_data
                            vm2.front_label_edit_data = response.data.formdata.front_label_edit_data
                            <?php
                            do_action('bookingpress_add_booking_form_customize_data');
                            ?>
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
                bookingpress_load_field_settings_data(){
                    const vm2 = this
                    var postData = []
                    postData.action = 'bookingpress_load_field_settings'
                    postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.variant == 'error'){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        }else{
                            vm2.field_settings_fields = response.data.field_settings;
                            <?php do_action('bookingpress_after_load_field_settings'); ?>
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
                bookingpress_load_my_booking_data(){
                    const vm2 = this
                    var postData = []
                    postData.action = 'bookingpress_load_my_booking_data'
                    postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.variant == 'error'){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        }else{
                            vm2.my_booking_field_settings = response.data.formdata.booking_form_settings
                            vm2.delete_account_content = response.data.formdata.booking_form_settings.delete_account_content;
                            setTimeout(function(){
                                vm2.bookingpress_change_border_color();
                            },100);

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
                endDragposistion(){
                    const vm2 = this;

                    if(typeof vm2.drag_data.field_pos_update_id != "undefined" && typeof vm2.drag_data.old_index != "undefined" && typeof vm2.drag_data.new_index != "undefined"){

                        var field_pos_update_id = vm2.drag_data.field_pos_update_id;
                        var old_index = vm2.drag_data.old_index;
                        var new_index = vm2.drag_data.new_index;

                        var postData = [];
                        postData.action = 'bookingpress_update_field_position'
                        postData.old_index = old_index
                        postData.new_index = new_index
                        postData.update_id = field_pos_update_id
                        postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                        .then( function (response) {
                            if(response.data.variant == 'error'){
                                vm2.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: response.data.variant,
                                    customClass: response.data.variant+'_notification',
                                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                });
                            }
                            vm2.bookingpress_load_field_settings_data()
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

                    }

                },
                updateFieldPos(e){
                    const vm2 = this
                    var field_pos_update_id = e.draggedContext.element.id
                    var old_index = e.draggedContext.index
                    var new_index = e.draggedContext.futureIndex
                    vm2.drag_data = {'field_pos_update_id':field_pos_update_id,'old_index':old_index,'new_index':new_index};

   
                },
                closeFieldSettings(field_name){
                    //this.field_settings_fields[field_name].is_edit = 0
                    var field_settings = this.field_settings_fields
                    field_settings.forEach(function(item, index, arr){
                        item.is_edit = 0
                    });
                },          
                open_custom_css_modal(){
                    const vm = this;
                   // vm.css_action = '';
                    vm.add_custom_css_modal = true;
                    vm.bookigpress_form_custom_css = vm.selected_colorpicker_values.custom_css;                                        
                },
                close_custom_css_modal() {
                    const vm = this;
                    vm.add_custom_css_modal = false;
                    vm.bookigpress_form_custom_css ='';                    
                },
                bookingpress_save_custom_css() {
                    const vm = this
                    vm.selected_colorpicker_values.custom_css = vm.bookigpress_form_custom_css;                    
                    vm.close_custom_css_modal();
                },
                bookingpress_change_tab(tabname) {
                    const vm = this;
                    vm.bookingpress_tab_change_loader = '1';
                    setTimeout(function(){
                        vm.bookingpress_tab_change_loader = '0';
                        vm.activeTabName = tabname;
                    },1000);
                },
                bookingpress_customize_form_tab_phone_country_change_func( bookingpress_country_obj ){
                    const vm = this;
                    var bookingpress_selected_country = bookingpress_country_obj.iso2
                    let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_selected_country, true, 1 );
                    if( '' != exampleNumber ){
                        vm.bookingpress_tel_input_props.inputOptions.placeholder = exampleNumber;
                    }
                },
                bookingpress_clear_datepicker(){
                    const vm = this;
                    vm.appointment_date_range = '';
                },
                bookingpress_lite_after_change_position(event) {
                    const vm = this;
                    if(event == true) {
                        vm.formActiveTab = '2';
                    } else {
                        vm.formActiveTab = '1';
                    }
                },
                bookingpress_change_border_color() {
                    const vm = this
                    var border_color = vm.selected_colorpicker_values.border_color
                    var opacity_color = Math.round(Math.min(Math.max(0.12 || 1, 0), 1) * 255);
                    var border_rgba_color = border_color+(opacity_color.toString(16).toUpperCase())
                    vm.selected_colorpicker_values.border_alpha_color = border_rgba_color;

                    var form_background_color = vm.selected_colorpicker_values.background_color
                    var panel_background_color = vm.selected_colorpicker_values.footer_background_color
                    var primary_color = vm.selected_colorpicker_values.primary_color
                    var sub_title_color = vm.selected_colorpicker_values.sub_title_color                    
                    var font_family = vm.selected_font_values.title_font_family
                    var title_color = vm.selected_colorpicker_values.label_title_color
                    var content_color = vm.selected_colorpicker_values.content_color
                    
                    var css_data = '.bpa-front-cp__filter-dropdown,.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card, .bpa-front-module--bd-form .--bpa-country-dropdown .vti__dropdown-list,.bpa-cbf--tabs .el-tabs__nav-wrap, .bpa-tn__dropdown-menu,.el-popover{background-color:'+form_background_color+'} .bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card,.bpa-tn__dropdown-menu,.bpa-front-module--bd-form .--bpa-country-dropdown .vti__dropdown-list,.bpa-form-control.--bpa-country-dropdown .vti__dropdown,.bpa-cbf--tabs .el-tabs__nav-wrap,.bpa-front-cp-my-appointment .bpa-form-control input,.bpa-front-cp__filter-dropdown,.el-popover,.el-popconfirm .el-popconfirm__action ,.el-button.el-button--bpa-btn.bpa-btn__small.el-button--mini:not(.bpa-btn--danger),.bpa-ci__service-actions .bpa-ci__sa-wrap{border-color:'+border_color+'} .bpa-front-ma-table__body .bpa-front-mat__row:nth-child(even),.bpa-form-control.--bpa-country-dropdown .vti__dropdown-item.highlighted,.bpa-ci__service-actions .bpa-ci__sa-wrap{background-color:'+panel_background_color+'}  .el-date-table td.current:not(.disabled) span,.bpa-cart__item .bpa-ci__service-actions .bpa-btn--icon-without-box:hover,.bpa-front-ma-table-actions-wrap .bpa-btn--icon-without-box:hover{background-color:'+primary_color+' !important} .bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-btn--icon-without-box:hover span svg path{fill: var(--bpa-cl-white)} .el-picker-panel__content .el-date-table th,.el-popconfirm .el-popconfirm__main,.el-button.el-button--bpa-btn.bpa-btn__small.el-button--mini{color:'+sub_title_color+'} .el-picker-panel__content .el-date-table td span,.el-picker-panel__content .el-date-table th,.el-date-picker__header-label{font-family:'+font_family+'} .el-picker-panel__content .el-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span,.el-date-picker__header-label,.bpa-front-cp-my-appointment .bpa-form-control input,.bpa-form-control.--bpa-country-dropdown.vue-tel-input.bpa-form-control input{color:'+title_color+' !important} .el-date-picker__header-label:hover, .el-date-table td.today span,.el-picker-panel__content .el-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span:hover, .el-picker-panel__content .el-date-table td:not(.current):not(.today) span:hover{color:'+primary_color+'} .el-picker-panel .el-icon-d-arrow-left::before,.el-picker-panel .el-icon-arrow-left::before,.el-picker-panel .el-icon-arrow-right::before,.el-picker-panel .el-icon-d-arrow-right::before,.bpa-customize-booking-form-preview-container .bpa-form-control input::placeholder,.bpa-customize-booking-form-preview-container .bpa-form-control .el-textarea__inner::placeholder,.bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item > span.material-icons-round,.bpa-form-control--date-picker .el-input__prefix,.bpa-front-cp--fw__col.__bpa-is-search-icon span.material-icons-round{color:'+content_color+'}.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-btn--icon-without-box span svg path,.bpa-ci__service-brief .bpa-ci__expand-icon path{fill:'+content_color+'}.el-button.el-button--bpa-btn.bpa-btn__small.bpa-btn--danger.el-button--mini,.bpa-cart__item .bpa-ci__service-actions .bpa-btn--icon-without-box:hover .material-icons-round {color:var(--bpa-cl-white) !important} .bpa-custom-checkbox--is-label .el-checkbox__inner{border-color:'+border_color+'!important} .bpa-custom-checkbox--is-label .el-checkbox__input.is-checked .el-checkbox__inner{background-color:'+primary_color+'!important; border-color:'+primary_color+'!important} .bpa-custom-checkbox--is-label .el-checkbox__input.is-checked+.el-checkbox__label{color:'+primary_color+' !important} .bpa-custom-checkbox--is-label .el-checkbox__input is-checked + el-checkbox__label{ color: '+primary_color+' !important} .bpa-custom-checkbox--is-label .el-checkbox__label{ color: '+content_color+' !important} .bpa-cart__item .bpa-ci__service-actions .bpa-btn--icon-without-box:hover{border-color:'+primary_color+'!important}';

                    var bookingpress_created_element = document.createElement("style");
                    bookingpress_created_element.innerHTML = css_data;
                    document.body.appendChild(bookingpress_created_element);
                    
                },
                bookingpress_hide_caching_notice(){
                    const vm = this;
                    var bookingpress_request_data = {};
                    bookingpress_request_data.action = "bookingpress_dismiss_caching_notice";
                    bookingpress_request_data._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_request_data ) )
                    .then( function( response ) {
                        vm.is_disabled_caching_notice = true;
                    }.bind( this ) )
                    .catch( function(error){
                        console.log( error );
                    });
                },
                bookingpress_close_caching_notice(){
                    const vm = this;
                    vm.is_disabled_caching_notice = true;
                },
            <?php
        }
        
        /**
         * Customize module onload methods
         *
         * @return void
         */
        function bookingpress_dynamic_onload_methods_func()
        {   
            $request_action = ( ! empty($_REQUEST['action']) ) ? sanitize_text_field($_REQUEST['action']) : 'forms'; //// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['action'] sanitized properly
            if($request_action == 'form_fields') {
                ?>
                const vm = this
                vm.bookingpress_load_field_settings_data()
                <?php
            } else {                    
            ?>  
                const vm = this
                vm.bookingpress_load_booking_form_data()
                vm.bookingpress_load_my_booking_data()
            <?php  
                do_action('bookingpress_customize_dynamic_onload_methods_after');             
            }
        }

        function bookingpress_dynamic_computed_methods_func()
        {
            ?>
            <?php
        }
        
        /**
         * Load customize module view file
         *
         * @return void
         */
        function bookingpress_load_customize_dynamic_view_func()
        {
                
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/customize/manage_form_customize.php';
            if(!empty($_REQUEST['action']) && !empty($_REQUEST['action'] == 'form_fields')) {
                $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/customize/manage_form_field_customize.php';
            }
            $bookingpress_load_file_name = apply_filters('bookingpress_modify_customize_view_file_path', $bookingpress_load_file_name);
            include $bookingpress_load_file_name;
        }
        
        /**
         * Add more data variables to customize module.
         *
         * @return void
         */
        function bookingpress_dynamic_data_fields_func()
        {
            global $bookingpress_customize_vue_data_fields, $BookingPress, $bookingpress_global_options, $tbl_bookingpress_form_fields, $tbl_bookingpress_customize_settings,$wpdb;
            $default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
            $disabled_date           = implode(',', $default_daysoff_details);

            $bookingpress_customize_vue_data_fields = array(
                'is_display_loader'                      => '0',
                'is_display_save_loader'                 => '0',
                'is_disabled'                            => 0,
                'dragging'                               => false,
                'drag_data'                              => '',
                'activeTabName'                          => 'booking_form',
                'bpa_activeTabName'                      => 'booking_form',
                'bookingpress_tab_change_loader'         => '0',
                'formActiveTab'                          => '1',
                'appointment_date_range'                 => array(),
                'tab_container_data'                     => array(    
                    'service_title'           => esc_html__('Services', 'bookingpress-appointment-booking'),
                    'datetime_title'          => str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ),
                    'basic_details_title'     => esc_html__('Basic Details', 'bookingpress-appointment-booking'),
                    'summary_title'           => esc_html__('Summary', 'bookingpress-appointment-booking'),
                ),
                'category_container_data'                => array(
                    'category_title'         => esc_html__('Select Category', 'bookingpress-appointment-booking'),		
                    'all_category_title'     => esc_html__( 'ALL', 'bookingpress-appointment-booking' ),
                ),
                'service_container_data'                 => array(    
                    'service_heading_title' => esc_html__('Select Service', 'bookingpress-appointment-booking'),
                    'default_image_url'     => BOOKINGPRESS_URL . '/images/placeholder-img.jpg',
                ),
                'timeslot_container_data'                => array(
                    'timeslot_text'    => esc_html__('Time Slot', 'bookingpress-appointment-booking'),
                ),
                'summary_container_data'       => array(    
                    'summary_content_text'  => esc_html__('Your appointment booking summary', 'bookingpress-appointment-booking'),    
                    'payment_method_text'   => esc_html__('Select Payment Method', 'bookingpress-appointment-booking'),
                ),
                'front_label_edit_data' =>  array(
                    'paypal_text'         => esc_html__('PayPal', 'bookingpress-appointment-booking'),
                    'locally_text'        => esc_html__('Pay Locally', 'bookingpress-appointment-booking'),
                    'total_amount_text'   => esc_html__('Total Amount Payable', 'bookingpress-appointment-booking'),
                    'service_text'        => esc_html__('Service', 'bookingpress-appointment-booking'),
                    'customer_text'       => esc_html__('Customer', 'bookingpress-appointment-booking'),
                    'date_time_text'      => str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ),
                    'appointment_details'      => esc_html__('Appointment Details', 'bookingpress-appointment-booking'),
                ),
                'bookingpress_shortcode_form'            => array(
                    'selected_category'         => 'low_consultancy',
                    'selected_service'          => 'chronic_disease_management_1',
                    'selected_date'             => date('Y-m-d', current_time('timestamp')),
                    'selected_time'             => '17:00',
                    'customer_name'             => '',
                    'customer_selected_country' => 'us',
                    'cusomter_phone'            => '',
                    'customer_email'            => '',
                    'customer_note'             => '',
                ),
                'selected_colorpicker_values'            => array(
                    'background_color'         => '#fff',
                    'footer_background_color'  => '#f4f7fb',
                    'border_color'             => '#CFD6E5',
                    'primary_color'            => '#12D488',
                    'primary_background_color' => '#e2faf1',
                    'label_title_color'        => '#202C45',
                    'sub_title_color'        =>   '#535D71',
                    'content_color'            => '#727E95',
                    'price_button_text_color'  => '#fff',
                    'custom_css'               => '',    
                ),
                'selected_font_values'                   => array(
                    'title_font_size'     => '18',
                    'title_font_family'   => 'Poppins',
                    'content_font_size'   => '14',
                    'sub_title_font_size'   => '16',
                ),
                'booking_form_settings'                  => array(
                    'hide_category_service_selection' => false,
                    'hide_already_booked_slot'        => false,
                    'display_service_description'     => false,
                    'booking_form_tabs_position'      => 'left',
                    'goback_button_text'              => esc_html__('Go Back', 'bookingpress-appointment-booking'),
                    'next_button_text'                => esc_html__('Next', 'bookingpress-appointment-booking'),
                    'book_appointment_btn_text'       => esc_html__('Book Appointment', 'bookingpress-appointment-booking'),
                    'book_appointment_hours_text'     => 'h',
                    'book_appointment_min_text'       => 'm',
                    'after_booking_redirection'       => '',
                    'after_failed_payment_redirection' =>  '',
                ),
                'draggable_field_setting_fields'         => array(),
                'field_settings_fields'                  => array(
                    'fullname'      => array(
                        'field_name'     => 'fullname',
                        'field_type'     => 'Text',
                        'is_edit'        => 0,
                        'is_required'    => 0,
                        'label'          => esc_html__('Fullname', 'bookingpress-appointment-booking'),
                        'placeholder'    => esc_html__('Enter your full name', 'bookingpress-appointment-booking'),
                        'error_message'  => esc_html__('Please enter your full name', 'bookingpress-appointment-booking'),
                        'is_hide'        => 0,
                        'is_default'     => 1,
                        'field_position' => 1,
                    ),
                    'firstname'     => array(
                        'field_name'     => 'firstname',
                        'field_type'     => 'Text',
                        'is_edit'        => 0,
                        'is_required'    => 0,
                        'label'          => esc_html__('Firstname', 'bookingpress-appointment-booking'),
                        'placeholder'    => esc_html__('Enter your firstname', 'bookingpress-appointment-booking'),
                        'error_message'  => esc_html__('Please enter your firstname', 'bookingpress-appointment-booking'),
                        'is_hide'        => 0,
                        'is_default'     => 1,
                        'field_position' => 2,
                    ),
                    'lastname'      => array(
                        'field_name'     => 'lastname',
                        'field_type'     => 'Text',
                        'is_edit'        => 0,
                        'is_required'    => 0,
                        'label'          => esc_html__('Lastname', 'bookingpress-appointment-booking'),
                        'placeholder'    => esc_html__('Enter your lastname', 'bookingpress-appointment-booking'),
                        'error_message'  => esc_html__('Please enter your lastname', 'bookingpress-appointment-booking'),
                        'is_hide'        => 0,
                        'is_default'     => 1,
                        'field_position' => 3,
                    ),
                    'email_address' => array(
                        'field_name'     => 'email_address',
                        'field_type'     => 'Email',
                        'is_edit'        => 0,
                        'is_required'    => true,
                        'label'          => esc_html__('Email Address', 'bookingpress-appointment-booking'),
                        'placeholder'    => esc_html__('Enter your email address', 'bookingpress-appointment-booking'),
                        'error_message'  => esc_html__('Please enter your email address', 'bookingpress-appointment-booking'),
                        'is_hide'        => 0,
                        'is_default'     => 1,
                        'field_position' => 4,
                    ),
                    'phone_number'  => array(
                        'field_name'     => 'phone_number',
                        'field_type'     => 'Dropdown',
                        'is_edit'        => 0,
                        'is_required'    => 0,
                        'label'          => esc_html__('Phone Number', 'bookingpress-appointment-booking'),
                        'placeholder'    => esc_html__('Enter your phone number', 'bookingpress-appointment-booking'),
                        'error_message'  => esc_html__('Please enter your phone number', 'bookingpress-appointment-booking'),
                        'is_hide'        => 0,
                        'is_default'     => 1,
                        'field_position' => 5,
                        'set_custom_placeholder' => 0,
                    ),
                    'note'          => array(
                        'field_name'     => 'note',
                        'field_type'     => 'Textarea',
                        'is_edit'        => 0,
                        'is_required'    => 0,
                        'label'          => esc_html__('Note', 'bookingpress-appointment-booking'),
                        'placeholder'    => esc_html__('Enter note details', 'bookingpress-appointment-booking'),
                        'error_message'  => esc_html__('Please enter appointment note', 'bookingpress-appointment-booking'),
                        'is_hide'        => 0,
                        'is_default'     => 1,
                        'field_position' => 6,
                    ),
                    'username'          => array(
                        'field_name'     => 'username',
                        'field_type'     => 'text',
                        'is_edit'        => 0,
                        'is_required'    => true,
                        'placeholder'    => esc_html__('Enter your username', 'bookingpress-appointment-booking'),
                        'label'          => esc_html__('Username', 'bookingpress-appointment-booking'),
                        'error_message'  => esc_html__('Please enter your username', 'bookingpress-appointment-booking'),
                        'is_hide'        => 1,
                        'field_position' => 8,
                    ),
                ),
                'my_booking_field_settings'              => array(
                    'mybooking_title_text'        => esc_html__('My Bookings', 'bookingpress-appointment-booking'),
                    'allow_to_cancel_appointment' => true,
                    'apply_button_title'          => esc_html__('Apply', 'bookingpress-appointment-booking'),  
                    'search_appointment_title'    => esc_html__('Search appointments', 'bookingpress-appointment-booking'),  
                    'search_date_title'    => esc_html__('Please select date', 'bookingpress-appointment-booking'),  
                    'search_end_date_title'    => esc_html__('Please select date', 'bookingpress-appointment-booking'),  
                    'my_appointment_menu_title'    => esc_html__('My Appointments', 'bookingpress-appointment-booking'),
                    'delete_appointment_menu_title'    => esc_html__('Delete Account', 'bookingpress-appointment-booking'),
                    'after_cancelled_appointment_redirection' => '',
                    'appointment_cancellation_confirmation' => '',
                    'cancel_appointment_title' => '',
                    'cancel_appointment_confirmation_message' => '',
                    'cancel_appointment_no_btn_text' => '',
                    'cancel_appointment_yes_btn_text' => '',
                    'id_main_heading' => '',
                    'service_main_heading' => '',
                    'date_main_heading' => '',
                    'status_main_heading' => '',
                    'payment_main_heading' => '',
                    'booking_id_heading' => '',
                    'booking_time_title' => '',
                    'payment_details_title' => '',
                    'payment_method_title' => '',
                    'total_amount_title' => '',	
                    'cancel_booking_id_text' => '',
                    'cancel_service_text' => '',
                    'cancel_date_time_text' => '',
                    'cancel_button_text' => '',
                ),   
                'add_custom_css_modal' => false,    
                'bookigpress_form_custom_css' => '',
                'delete_account_content' => '',
            ); 

            $bookingpress_customize_vue_data_fields['days_off_disabled_dates'] = $disabled_date;

            // Load fonts options
            $bookingpress_inherit_fonts_list = array(
                'Inherit Fonts',
            );
            $bookingpress_default_fonts_list = $bookingpress_global_options->bookingpress_get_default_fonts();
            $bookingpress_google_fonts_list  = $bookingpress_global_options->bookingpress_get_google_fonts();            
            $bookingpress_options      = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_default_date_format = $bookingpress_options['wp_default_date_format'];
            $bookingpress_default_time_format = $bookingpress_options['wp_default_time_format'];
            $bookingpress_country_list = json_decode($bookingpress_options['country_lists']);
            $bookingpress_all_pages = array();
            $bookingpress_all_pages = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_title FROM `".$wpdb->posts."` WHERE post_type = %s AND post_status = %s", 'page', 'publish'), ARRAY_A );

            
            $bookingpress_fonts_list         = array(
                array(
                    'label'   => esc_html__('Inherit Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_inherit_fonts_list,
                ),
                array(
                    'label'   => esc_html__('Default Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_default_fonts_list,
                ),
                array(
                    'label'   => esc_html__('Google Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_google_fonts_list,
                ),
            );
            $bookingpress_customize_vue_data_fields['bookingpress_shortcode_form']['phone_countries_details'] = $bookingpress_country_list;
            $bookingpress_customize_vue_data_fields['bookingpress_all_global_pages'] = $bookingpress_all_pages;

            $bookingpress_customize_vue_data_fields['fonts_list'] = $bookingpress_fonts_list;
              $bookingpress_phone_country_option                  = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
            $bookingpress_customize_vue_data_fields['bookingpress_tel_input_props'] = array(
                'defaultCountry' => $bookingpress_phone_country_option,
                'inputOptions' => array(
                    'placeholder' => ''
                )
            );                                       
            $bookingpress_customize_vue_data_fields['dummy_data'] = array(
				array(
					"id" => '#158791',
					"appointment_service_name" => 'Sample service 1',
					"appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
					"appointment_status" => 'Pending',
					"appointment_payment" => '$100.00',
                    "appointment_staff" => 'Blaine Moon',
				),
				array(
					"id" => '#158792',
					"appointment_service_name" => 'Sample service 2',
					"appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
					"appointment_status" => 'Pending',
					"appointment_payment" => '$200.00',
                    "appointment_staff" => 'Gary Williams',
				),
				array(
					"id" => '#158793',
					"appointment_service_name" => 'Sample service 3',
					"appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
					"appointment_status" => 'Pending',
					"appointment_payment" => '$300.00',
                    "appointment_staff" => 'Gerardo Burton',
				),
				array(
					"id" => '#158794',
					"appointment_service_name" => 'Sample service 4',
					"appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
					"appointment_status" => 'Pending',
					"appointment_payment" => '$400.00',
                    "appointment_staff" => 'Harold Reed',
				),
				array(
					"id" => '#158795',
					"appointment_service_name" => 'Sample service 5',
					"appointment_date" => date($bookingpress_default_date_format.' '.$bookingpress_default_time_format,strtotime('2021-10-25 13:00:00')),
					"appointment_status" => 'Pending',
					"appointment_payment" => '$500.00',
                    "appointment_staff" => 'Fox Doe',
				),
			);
			
			$bookingpress_default_date_format = $BookingPress->bookingpress_check_common_date_format($bookingpress_options['wp_default_date_format']);
            $bookingpress_customize_vue_data_fields['masks'] = array(
                'input' => strtoupper($bookingpress_default_date_format),
            );
            $hide_category_service_selection = $BookingPress->bookingpress_get_customize_settings('hide_category_service_selection','booking_form');
            if(!empty($hide_category_service_selection ) && $hide_category_service_selection == 'true') {
                $bookingpress_customize_vue_data_fields['formActiveTab'] = '2';
            }

    
            $bookingpress_allow_wp_customer_create = $BookingPress->bookingpress_get_settings('allow_wp_user_create', 'customer_setting');
    
            $bookingpress_customize_vue_data_fields['bookingpress_allow_wp_customer_create']=$bookingpress_allow_wp_customer_create;
            $bookingpress_customize_vue_data_fields['is_disabled_caching_notice'] = get_option( 'bookingpress_disabled_caching_notice' );
            $bookingpress_customize_vue_data_fields = apply_filters('bookingpress_customize_add_dynamic_data_fields', $bookingpress_customize_vue_data_fields);
            echo wp_json_encode($bookingpress_customize_vue_data_fields);
        }

        function bookingpress_get_form_fields_func()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;
            $bookingpress_form_fields_arr = array();
            $bookingpress_get_form_fields = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_form_fields}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
            foreach ( $bookingpress_get_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {
                $bookingpress_form_fields_arr[] = array(
                'label'      => $bookingpress_form_field_val['bookingpress_field_label'],
                'field_name' => $bookingpress_form_field_val['bookingpress_form_field_name'],
                );
            }

            $bookingpress_form_fields_arr = apply_filters('bookingpress_modify_form_field_data', $bookingpress_form_fields_arr);

            return $bookingpress_form_fields_arr;
        }
    }

    global $bookingpress_customize, $bookingpress_customize_vue_data_fields;
    $bookingpress_customize = new bookingpress_customize();
}

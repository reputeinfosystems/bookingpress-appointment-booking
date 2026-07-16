<?php
namespace BookingPress\admin;

if( !defined( 'ABSPATH' ) ) {
    exit;
}

class Settings extends Base{
    
    protected static $slug = 'bookingpress_settings';
    
    public static function init(){
        parent::init();
        add_filter( 'script_module_data_bookingpress-settings', [ __CLASS__, 'bookingpress_add_settings_script_module_data_func' ] );
    }

    public static function bookingpress_add_settings_script_module_data_func($settings_data){

        global $bookingpress_global_options, $BookingPress;

        $selected_tab_name  = ! empty($_REQUEST['setting_page']) ? sanitize_text_field($_REQUEST['setting_page']) : 'general_settings';            
        $settings_data['selected_tab_name'] = $selected_tab_name;  

        $bookingpress_options             = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_country_list        = $bookingpress_options['country_lists'];
        $bookingpress_countries_currency  = json_decode($bookingpress_options['countries_json_details']);
        $timepicker_options               = $bookingpress_options['timepicker_options'];
        $bookingpress_pagination          = $bookingpress_options['pagination'];
        $bookingpress_pagination_arr      = json_decode($bookingpress_pagination, true);
        $bookingpress_pagination_selected = $bookingpress_pagination_arr[0];

        $settings_data['bpa_is_pro_active'] = $BookingPress->bpa_is_pro_active();

        $settings_data['first_day_of_week'] = intval($bookingpress_options['start_of_week']) + 1;       
        $settings_data['site_locale'] = determine_locale();
        $settings_data['bookingpress_alignment'] = is_rtl() ? 'right' : 'left';
        
        $settings_data['bpa_display_wpmail_notice'] = !empty( get_option( 'bookingpress_display_wpmail_failed_msg_notice' ) ) ? get_option( 'bookingpress_display_wpmail_failed_msg_notice' ) : false;

        $settings_data['bpa_wpmail_failed_msg_data'] = !empty( get_option( 'bookingpress_wpmail_failed_msg_data') ) ? json_decode( get_option( 'bookingpress_wpmail_failed_msg_data'), true ) : [];

        $bookingpress_appointment_statuses = $bookingpress_options['appointment_status'];
        foreach($bookingpress_appointment_statuses as $k => $v){
            if($v['value'] != 1 && $v['value'] != 2){
                unset($bookingpress_appointment_statuses[$k]);
            }
        }

        $settings_data['flags_img_url']            = BOOKINGPRESS_IMAGES_URL;
        $settings_data['default_appointment_staus'] = $bookingpress_appointment_statuses;
        $settings_data['timepicker_options']        = json_decode($timepicker_options);
        $settings_data['phone_countries_details']   = json_decode($bookingpress_country_list);
        $settings_data['currency_countries']        = $bookingpress_countries_currency;
        $settings_data['perPage']                   = $bookingpress_pagination_selected;
        $settings_data['pagination_selected_length'] = $bookingpress_pagination_selected;
        $settings_data['pagination_length']         = $bookingpress_pagination;
        $settings_data['download_log_daterange']    = array(
            date('Y-m-d', strtotime('-3 Day')),
            date('Y-m-d', strtotime('+3 Day'))
        );
        $settings_data['notification_setting_form_sender_name']  = get_option('blogname');
        $settings_data['notification_setting_form_sender_email'] = get_option('admin_email');
        $settings_data['notification_setting_form_admin_email']  = get_option('admin_email');

        $settings_data['default_timeslot_options'] = array(
            array( 'text' => esc_html__('5 min',      'bookingpress-appointment-booking'), 'value' => '5' ),
            array( 'text' => esc_html__('10 min',     'bookingpress-appointment-booking'), 'value' => '10' ),
            array( 'text' => esc_html__('15 min',     'bookingpress-appointment-booking'), 'value' => '15' ),
            array( 'text' => esc_html__('20 min',     'bookingpress-appointment-booking'), 'value' => '20' ),
            array( 'text' => esc_html__('25 min',     'bookingpress-appointment-booking'), 'value' => '25' ),
            array( 'text' => esc_html__('30 min',     'bookingpress-appointment-booking'), 'value' => '30' ),
            array( 'text' => esc_html__('35 min',     'bookingpress-appointment-booking'), 'value' => '35' ),
            array( 'text' => esc_html__('40 min',     'bookingpress-appointment-booking'), 'value' => '40' ),
            array( 'text' => esc_html__('45 min',     'bookingpress-appointment-booking'), 'value' => '45' ),
            array( 'text' => esc_html__('50 min',     'bookingpress-appointment-booking'), 'value' => '50' ),
            array( 'text' => esc_html__('55 min',     'bookingpress-appointment-booking'), 'value' => '55' ),
            array( 'text' => esc_html__('1 h',        'bookingpress-appointment-booking'), 'value' => '60' ),
            array( 'text' => esc_html__('1 h 30 min', 'bookingpress-appointment-booking'), 'value' => '90' ),
            array( 'text' => esc_html__('2 h',        'bookingpress-appointment-booking'), 'value' => '120' ),
            array( 'text' => esc_html__('2 h 30 min', 'bookingpress-appointment-booking'), 'value' => '150' ),
            array( 'text' => esc_html__('3 h',        'bookingpress-appointment-booking'), 'value' => '180' ),
            array( 'text' => esc_html__('3 h 30 min', 'bookingpress-appointment-booking'), 'value' => '210' ),
            array( 'text' => esc_html__('4 h',        'bookingpress-appointment-booking'), 'value' => '240' ),
            array( 'text' => esc_html__('4 h 30 min', 'bookingpress-appointment-booking'), 'value' => '270' ),
            array( 'text' => esc_html__('5 h',        'bookingpress-appointment-booking'), 'value' => '300' ),
            array( 'text' => esc_html__('5 h 30 min', 'bookingpress-appointment-booking'), 'value' => '330' ),
            array( 'text' => esc_html__('6 h',        'bookingpress-appointment-booking'), 'value' => '360' ),
            array( 'text' => esc_html__('6 h 30 min', 'bookingpress-appointment-booking'), 'value' => '390' ),
            array( 'text' => esc_html__('7 h',        'bookingpress-appointment-booking'), 'value' => '420' ),
            array( 'text' => esc_html__('7 h 30 min', 'bookingpress-appointment-booking'), 'value' => '450' ),
            array( 'text' => esc_html__('8 h',        'bookingpress-appointment-booking'), 'value' => '480' ),
        );

        $settings_data['default_smtp_secure_options'] = array(
            array( 'text' => esc_html__('SSL',      'bookingpress-appointment-booking'), 'value' => 'SSL' ),
            array( 'text' => esc_html__('TLS',      'bookingpress-appointment-booking'), 'value' => 'TLS' ),
            array( 'text' => esc_html__('Disabled', 'bookingpress-appointment-booking'), 'value' => 'Disabled' ),
        );

        $settings_data['price_symbol_position_val'] = array(
            array( 'text' => esc_html__('Before value', 'bookingpress-appointment-booking'), 'value' => 'before', 'position_ex' => '$100' ),
            array( 'text' => esc_html__('Before value', 'bookingpress-appointment-booking') . ', ' . esc_html__('separated with space', 'bookingpress-appointment-booking'), 'value' => 'before_with_space', 'position_ex' => '$ 100' ),
            array( 'text' => esc_html__('After value', 'bookingpress-appointment-booking'), 'value' => 'after', 'position_ex' => '100$' ),
            array( 'text' => esc_html__('After value', 'bookingpress-appointment-booking') . ', ' . esc_html__('separated with space', 'bookingpress-appointment-booking'), 'value' => 'after_with_space', 'position_ex' => '100 $' ),
        );

        $settings_data['price_separator_vals'] = array(
            array( 'text' => esc_html__('Comma-Dot',   'bookingpress-appointment-booking'), 'value' => 'comma-dot',   'separator_ex' => '15,000.00' ),
            array( 'text' => esc_html__('Dot-Comma',   'bookingpress-appointment-booking'), 'value' => 'dot-comma',   'separator_ex' => '15.000,00' ),
            array( 'text' => esc_html__('Space-Dot',   'bookingpress-appointment-booking'), 'value' => 'space-dot',   'separator_ex' => '15 000.00' ),
            array( 'text' => esc_html__('Space-Comma', 'bookingpress-appointment-booking'), 'value' => 'space-comma', 'separator_ex' => '15 000,00' ),
            array( 'text' => esc_html__('Custom',      'bookingpress-appointment-booking'), 'value' => 'Custom' ),
        );

        $settings_data['default_payment_method'] = array(
            array( 'text' => esc_html__('On-site', 'bookingpress-appointment-booking'), 'value' => 'on_site' ),
            array( 'text' => esc_html__('PayPal',  'bookingpress-appointment-booking'), 'value' => 'paypal' ),
        );

        $settings_data['message_setting_form'] = array(
            'appointment_booked_successfully'              => esc_html__('Appointment has been booked successfully.', 'bookingpress-appointment-booking'),
            'appointment_cancelled_successfully'           => esc_html__('Appointment has been cancelled successfully.', 'bookingpress-appointment-booking'),
            'duplidate_appointment_time_slot_found'        => esc_html__('I am sorry! Another appointment is already booked with this time slot. Please select another time slot which suits you the best.', 'bookingpress-appointment-booking'),
            'unsupported_currecy_selected_for_the_payment' => esc_html__('I am sorry! The selected currency is not supported by PayPal payment gateway. Please proceed with another available payment method.', 'bookingpress-appointment-booking'),
            'duplicate_email_address_found'                => esc_html__('I am sorry! This email address is already exists. Please enter another email address.', 'bookingpress-appointment-booking'),
            'no_payment_method_is_selected_for_the_booking' => esc_html__('Please select a payment method to proceed with the booking.', 'bookingpress-appointment-booking'),
            'no_appointment_time_selected_for_the_booking' => esc_html__('Please select a time slot to proceed with the booking.', 'bookingpress-appointment-booking'),
            'no_appointment_date_selected_for_the_booking' => esc_html__('Please select appointment date to proceed with the booking.', 'bookingpress-appointment-booking'),
            'no_service_selected_for_the_booking'          => esc_html__('Please select any service to book the appointment', 'bookingpress-appointment-booking'),
            'no_payment_method_available'                  => esc_html__('Oops! There is no payment method available.', 'bookingpress-appointment-booking'),
            'no_timeslots_available'                       => esc_html__('There is no time slots available', 'bookingpress-appointment-booking'),
            'cancel_appointment_confirmation'              => esc_html__('This is a cancel appointment confirmation message', 'bookingpress-appointment-booking'),
            'no_appointment_available_for_cancel'          => esc_html__('No appointment available for the cancel', 'bookingpress-appointment-booking'),
        );

        $settings_data['log_download_default_option'] = array(
            array( 'key' => esc_html__('Last 1 Day',   'bookingpress-appointment-booking'), 'value' => '1' ),
            array( 'key' => esc_html__('Last 3 Days',  'bookingpress-appointment-booking'), 'value' => '3' ),
            array( 'key' => esc_html__('Last 1 Week',  'bookingpress-appointment-booking'), 'value' => '7' ),
            array( 'key' => esc_html__('Last 2 Weeks', 'bookingpress-appointment-booking'), 'value' => '14' ),
            array( 'key' => esc_html__('Last Month',   'bookingpress-appointment-booking'), 'value' => '30' ),
            array( 'key' => esc_html__('All',          'bookingpress-appointment-booking'), 'value' => 'all' ),
            array( 'key' => esc_html__('Custom',       'bookingpress-appointment-booking'), 'value' => 'custom' ),
        );

        $settings_data['rules_dayoff'] = array(
            'dayoff_name' => array( array( 'required' => true, 'message' => esc_html__('Please enter name', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'dayoff_date' => array( array( 'required' => true, 'message' => esc_html__('Please select date','bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $settings_data['rules_company'] = array(
            'company_name'         => array( array( 'required' => true, 'message' => esc_html__('Please enter company name',    'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'company_address'      => array( array( 'required' => true, 'message' => esc_html__('Please enter company address', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'company_website'      => array( array( 'required' => true, 'message' => esc_html__('Please enter company website', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'company_phone_number' => array( array( 'required' => true, 'message' => esc_html__('Please enter phone number',    'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $settings_data['rules_notification'] = array(
            'sender_name'         => array( array( 'required' => true, 'message' => esc_html__('Please enter sender name','bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'sender_email'        => array( array( 'required' => true, 'message' => esc_html__('Please enter sender email','bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'sender_url'          => array( array( 'required' => true, 'message' => esc_html__('Please enter sender url', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'admin_email'         => array( array( 'required' => true, 'message' => esc_html__('Please enter admin email', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'success_url'         => array( array( 'required' => true, 'message' => esc_html__('Please enter successfull redirection url', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'cancel_url'          => array( array( 'required' => true, 'message' => esc_html__('Please enter cancel redirection url','bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'smtp_port'           => array( array( 'required' => true, 'message' => esc_html__('Please enter smtp port','bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'smtp_host'           => array( array( 'required' => true, 'message' => esc_html__('Please enter smtp host','bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'smtp_secure'         => array( array( 'required' => true, 'message' => esc_html__('Please enter smtp secure', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'gmail_client_ID'     => array( array( 'required' => true, 'message' => esc_html__('Please enter gmail client ID','bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'gmail_client_secret' => array( array( 'required' => true, 'message' => esc_html__('Please enter gmail client secret','bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $settings_data['rules_smtp_test_mail'] = array(
            'smtp_test_receiver_email' => array( array( 'required' => true, 'message' => esc_html__('Please enter email address', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'smtp_test_msg'            => array( array( 'required' => true, 'message' => esc_html__('Please enter message',       'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $settings_data['rules_gmail_test_mail'] = array(
            'gmail_test_receiver_email' => array( array( 'required' => true, 'message' => esc_html__('Please enter email address', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'gmail_test_msg'            => array( array( 'required' => true, 'message' => esc_html__('Please enter message',       'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $settings_data['rules_wpmail_test_mail'] = array(
            'wpmail_test_receiver_email' => array( array( 'required' => true, 'message' => esc_html__('Please enter email address', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'wpmail_test_msg'            => array( array( 'required' => true, 'message' => esc_html__('Please enter message',       'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $settings_data['rules_payment'] = array(
            'paypal_merchant_email' => array( array( 'required' => true, 'message' => esc_html__('Please enter merchant email', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'paypal_api_username'   => array( array( 'required' => true, 'message' => esc_html__('Please enter api username',   'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'paypal_client_id'      => array( array( 'required' => true, 'message' => esc_html__('Please enter client ID',      'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'paypal_client_secret'  => array( array( 'required' => true, 'message' => esc_html__('Please enter client secret',  'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'paypal_api_password'   => array( array( 'required' => true, 'message' => esc_html__('Please enter api password',   'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'paypal_api_signature'  => array( array( 'required' => true, 'message' => esc_html__('Please enter api signature',  'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $settings_data['days_off_rules'] = array(
            'daysoff_title' => array( array( 'required' => true, 'message' => esc_html__('Please enter holiday reason', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $msg = esc_html__('Please enter message', 'bookingpress-appointment-booking');
        $settings_data['rules_message'] = array(
            'appointment_booked_successfully'              => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'appointment_cancelled_successfully'           => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'duplidate_appointment_time_slot_found'        => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'unsupported_currecy_selected_for_the_payment' => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'duplicate_email_address_found'                => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'no_payment_method_is_selected_for_the_booking' => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'no_appointment_time_selected_for_the_booking' => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'no_appointment_date_selected_for_the_booking' => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'no_service_selected_for_the_booking'          => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'no_payment_method_available'                  => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'no_timeslots_available'                       => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'cancel_appointment_confirmation'              => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
            'no_appointment_available_for_cancel'          => array( array( 'required' => true, 'message' => $msg, 'trigger' => 'blur' ) ),
        );

        $settings_data['rules_add_break'] = array(
            'start_time' => array( array( 'required' => true, 'message' => esc_html__('Please enter start time', 'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
            'end_time'   => array( array( 'required' => true, 'message' => esc_html__('Please enter end time',   'bookingpress-appointment-booking'), 'trigger' => 'blur' ) ),
        );

        $default_start_time    = '00:00:00';
        $default_end_time      = '23:55:00';
        $step_duration_val     = 01;
        $default_break_timings = array();
        $curr_time             = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
        $tmp_end_time          = date( 'H:i:s', strtotime( $default_end_time ) );

        do {

            $tmp_start_time = $default_start_time;
            $tmp_time_obj = new \DateTime( $curr_time );
            $tmp_time_obj->add( new \DateInterval( 'PT' . $step_duration_val . 'H' ) );
            $end_time                = $tmp_time_obj->format( 'H:i:s' );

            if($end_time == "00:00:00"){
                $end_time = "24:00:00";
            }

            $default_break_timings[] = array(
                'start_time'           => $curr_time,
                'start_time_val'           => $curr_time,
                'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $curr_time ) ),
                'end_time'             => $end_time,
                'end_time_val'             => $end_time,
                'formatted_end_time' => date($bookingpress_options['wp_default_time_format'], strtotime($end_time))." ".($end_time == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' ),
                
            );

            if($end_time == "24:00:00"){
                break;
            }

            $tmp_time_obj            = new \DateTime( $curr_time );
            $tmp_time_obj->add( new \DateInterval( 'PT' . $step_duration_val . 'H' ) );
            $curr_time = $tmp_time_obj->format( 'H:i:s' );
        } while ( $curr_time <= $default_end_time );

        $settings_data['timeslots_grouping_list'] = $default_break_timings;

        $bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
        $settings_data['customer_phone_country'] = $bookingpress_phone_country_option;

        $settings_data['bookingpress_tel_input_settings_props'] = array(
            'defaultCountry' => $bookingpress_phone_country_option,
            'inputOptions' => array(
                'placeholder' => '',
            ),
            'validCharactersOnly' => true,
	     'dropdownOptions' => array(
                'showDialCodeInSelection' => true,
                'showDialCodeInList' => true
            )
        );

        $bookingpress_company_phone_country  = $BookingPress->bookingpress_get_settings('company_phone_country', 'company_setting');

        $settings_data['bookingpress_cmp_tel_input_settings_props'] = array(
            'defaultCountry' => $bookingpress_company_phone_country,
            'inputOptions' => array(
                'placeholder' => '',
            ),
            'validCharactersOnly' => true,
	        'dropdownOptions' => array(
                'showDialCodeInSelection' => true,
                'showDialCodeInList' => true
            )
        );

        global $bookingpress_import_export;

        $bookingpress_active_plugin_module_list = $bookingpress_import_export->bookingpress_active_plugin_module_list();

        $bookingpress_export_list = [];
        $total_records = 0;
        $setting_total_records = $bookingpress_import_export->export_item_total_records("settings") + $total_records;

        $bookingpress_export_list['settings'] = array('name'=>esc_html__('Settings','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>0,'total_record'=>$setting_total_records);

        $total_records = $bookingpress_import_export->export_item_total_records("customers");
        $bookingpress_export_list['customers'] = array('name'=>esc_html__('Customers','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>0,'total_record'=>$total_records);
        $bookingpress_export_list['customers']['child']['customer_wp_users'] = array('name'=>esc_html__('WordPress Users','bookingpress-appointment-booking'),'related'=>'','child'=>array(),'required_parent'=>0);

        $total_records = $bookingpress_import_export->export_item_total_records("appointments");
        $bookingpress_export_list['appointments'] = array('name'=>esc_html__('Appointments','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>1,'total_record'=>$total_records);

        $total_records = $bookingpress_import_export->export_item_total_records("services");
        $bookingpress_export_list['services'] = array('name'=>esc_html__('Services','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>0,'total_record'=>$total_records);

        $total_records = $bookingpress_import_export->export_item_total_records("notifications");
        $bookingpress_export_list['notifications'] = array('name'=>esc_html__('Notifications','bookingpress-appointment-booking'),'related'=>array('services'),'child'=>array(),'required_parent'=>0,'total_record'=>$total_records);
        $bookingpress_export_list['appointments']['related'][] = 'notifications';
        $bookingpress_export_list['appointments']['related'][] = 'services';

        $bookingpress_export_list['appointments']['related'][] = 'customers';
        $bookingpress_export_list['appointments']['related'][] = 'customer_wp_users';

        $current_url = get_site_url() . sanitize_text_field($_SERVER['REQUEST_URI']); // phpcs:ignore

        $bookingpress_export_list = apply_filters( 'bookingpress_modified_export_list', $bookingpress_export_list );
        $bookingpress_export_list_data = array();
        foreach($bookingpress_export_list as $key=>$val){
            $bookingpress_export_list_data[$key] = false;
            if(isset($val['child']) && !empty($val['child'])){
                foreach($val['child'] as $child_key=>$child_fields){
                    $bookingpress_export_list_data[$child_key] = false;
                }
            }
        }
        $migration_tool_form = array('export_list'=>$bookingpress_export_list);
        $migration_tool_form['bookingpress_export_list_data'] = $bookingpress_export_list_data;
        $settings_data['migration_tool_form'] = $migration_tool_form;       
        
        $bookingpress_gmailapi_redirect_uri = get_home_url().'?page=bookingpress_gmailapi'; 
        $bookingpress_gmailapi_redirect_uri = urlencode( $bookingpress_gmailapi_redirect_uri);

        $state = base64_encode( 'action:gmail_oauth' );

        $settings_data['bookingpress_gmailapi_redirect_uri'] = $bookingpress_gmailapi_redirect_uri;       
        $settings_data['gmail_oauth_state'] = $state;       

        $bookingpress_redirect_url_success_msg = esc_html__( 'Authorized redirect URI copied successfully', 'bookingpress-appointment-booking' );
        $settings_data['bookingpress_redirect_url_success_msg'] = $bookingpress_redirect_url_success_msg;

        $bookingpress_default_date_format = $BookingPress->bookingpress_check_common_date_format_for_picker($bookingpress_options['wp_default_date_format']);

        $bookingpress_start_of_week = esc_html($bookingpress_options['start_of_week']);

        $settings_data['bookingpress_start_of_week'] = intval( esc_html($bookingpress_start_of_week));
        $settings_data['bpa_date_common_date_format'] = esc_html($bookingpress_default_date_format);

        $settings_data['repeat_holiday_label'] = esc_html__('Repeat Every Year', 'bookingpress-appointment-booking');

        return $settings_data;
    }

    public static function enqueue_assets( $hook ){
        if ( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'bookingpress_settings' ) {
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
            ['vue'],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
			'bookingpress-vcalendar',
			BOOKINGPRESS_URL . '/src/assets/js/bp-vcalendar.js',
			array(),
			BOOKINGPRESS_VERSION
		);

        wp_register_script_module(
            'bookingpress-settings',
            BOOKINGPRESS_URL . '/src/assets/js/settings-loader.js',
            [ 'bookingpress-ui', 'bookingpress-vcalendar' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-settings' );
        
        wp_register_script_module(
            'bookingpress-sidemenu-drawer',
            BOOKINGPRESS_URL . '/src/assets/js/drawer-loader.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-sidemenu-drawer' );

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

        wp_enqueue_style(
            'bookingpress-vcalendar',
            BOOKINGPRESS_URL . '/src/assets/css/bp-vcalendar.css',
            [],
            BOOKINGPRESS_VERSION
        );

        /* wp_register_script('bookingpress_tel_input_js', BOOKINGPRESS_URL . '/js/bookingpress_tel_input.js', array(), BOOKINGPRESS_VERSION);
        wp_register_script('bookingpress_tel_utils_js', BOOKINGPRESS_URL . '/js/bookingpress_tel_utils.js', array(), BOOKINGPRESS_VERSION );
        wp_register_style('bookingpress_tel_input', BOOKINGPRESS_URL . '/css/bookingpress_tel_input.css', array(), BOOKINGPRESS_VERSION);

        wp_enqueue_script('bookingpress_tel_input_js');
        wp_enqueue_script('bookingpress_tel_utils_js');
        wp_enqueue_style('bookingpress_tel_input'); */
    }

    public static function render_page(){
        self::render_view( 'Settings', [
            'title' => esc_html__( 'Settings', 'bookingpress-appointment-booking' )
        ] );
    }

    public static function render_import_export_debug_logs(){
        require_once BOOKINGPRESS_DIR . '/src/views/components/settings/import_export_debug_log.php';        
    }  
    
}
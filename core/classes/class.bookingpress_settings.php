<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_settings') ) {
    class bookingpress_settings Extends BookingPress_Core
    {

        public $bpa_sending_wp_mail;
        public $bpa_sent_test_wpmail_errors;
        public $bpa_sending_wp_mail_upon_booking;

        function __construct(){

            add_action('wp_ajax_bookingpress_save_settings_data', array( $this, 'bookingpress_save_settings_details' ));
            add_action('wp_ajax_bookingpress_get_settings_details', array( $this, 'bookingpress_get_settings_details' ));
            add_action('wp_ajax_bookingpress_save_default_work_hours', array( $this, 'bookingpress_save_default_work_hours' ));
            add_action('wp_ajax_bookingpress_get_default_work_hours_details', array( $this, 'bookingpress_get_default_work_hours' ));
            add_action('wp_ajax_bookingpress_save_default_daysoff', array( $this, 'bookingpress_save_default_daysoff_details' ));
            add_action('wp_ajax_bookingpress_get_default_daysoff_details', array( $this, 'bookingpress_get_default_daysoff_details' ));

            add_action('bookingpress_settings_dynamic_vue_methods', array( $this, 'bookingpress_setting_dynamic_vue_methods_func' ), 10);
            add_action('bookingpress_settings_dynamic_data_fields', array( $this, 'bookingpress_setting_dynamic_data_fields_func' ), 10);
            add_action('bookingpress_settings_dynamic_helper_vars', array( $this, 'bookingpress_setting_dynamic_helper_vars_func' ), 10);
            add_action('bookingpress_settings_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_setting_content_func' ), 10);
            add_action('bookingpress_settings_dynamic_on_load_methods', array( $this, 'bookingpress_settings_dynamic_on_load_methods_func' ), 9);
            add_action('bookingpress_settings_dynamic_computed_methods', array( $this, 'bookingpress_settings_dynamic_computed_methods_func' ), 10);
            add_action('bookingpress_settings_dynamic_data_fields_vars', array( $this, 'bookingpress_settings_dynamic_data_fields_vars_func' ), 10);

            add_action('wp_ajax_bookingpress_upload_company_avatar', array( $this, 'bookingpress_upload_company_avatar_func' ), 10);
            add_action('wp_ajax_bookingpress_send_test_email', array( $this, 'bookingpress_send_test_email_func' ));
            
            /* gmail send notification */
            add_action('wp_ajax_bookingpress_send_test_gmail_email', array( $this, 'bookingpress_send_test_gmail_email_func' ));

            /** WordPress default send test notification */
            add_action( 'wp_ajax_bookingpress_send_test_wpmail_email', array( $this, 'bookingpress_send_test_wpmail_email_func') );
            add_action( 'wp_mail_failed', array( $this, 'bookingpress_record_failed_wpmail') );

            add_action('wp_ajax_bookingpress_save_default_daysoff_details', array( $this, 'bookingpress_save_default_daysoff_details_func' ), 10);
            add_action('wp_ajax_bookingpress_get_daysoff_details', array( $this, 'bookingpress_get_daysoff_details_func' ), 10);
            add_action('wp_ajax_bookingpress_delete_daysoff_details', array( $this, 'bookingpress_delete_daysoff_details_func' ), 10);

            add_action( 'admin_init', array( $this, 'bookingpress_dynamic_setting_data_fields' ) );

            add_action( 'init', array( $this, 'bookingpress_cleanup_transient_data') );

            add_action('wp_ajax_bookingpress_check_currency_status',array( $this, 'bookingpress_check_currency_status_func' ));

            add_action( 'wp_ajax_bookingpress_remove_company_avatar', array( $this, 'bookingpress_remove_company_avatar_func' ) );

            add_action( 'wp',array($this,'bookingpress_add_gmail_api'),10);

            add_action('wp_ajax_bookingpress_signout_google_account', array($this, 'bookingpress_signout_google_account_arr'),10);

            add_action( 'init', array( $this, 'bookingpress_validate_plugin_setup' ) );

        }

        function bookingpress_validate_plugin_setup(){

            $bpa_plugin_setup_check_time = get_transient( 'bookingpress_validate_plugin_setup_timings' );

            if( false == $bpa_plugin_setup_check_time ){

                parent::load();

                if (!function_exists('is_plugin_active')) {
                    include_once ABSPATH . 'wp-admin/includes/plugin.php';
                }

                $bpa_validate = get_option( 'bookingpress_version' );
                $bpa_pro_validate = get_option( 'bookingpress_pro_version' );
                $bvlv = !empty( $bpa_validate ) ? 1 : 0;
                $bvpv = !empty( $bpa_pro_validate ) ? 1 : 0;

                $bvava_data = [];
                $bvavd_data = [];

                $bvav_url = 'https://bookingpressplugin.com/bpa_misc/addons_list_v2.php';
                $bvav_resp = wp_remote_post(
                    $bvav_url,
                    array(
                        'method'    => 'POST',
                        'timeout'   => 45,
                        'sslverify' => false,
                        'body'      => array(
                            'bookingpress_addon_list' => 1,
                        ),
                    )
                );            
                if ( ! is_wp_error( $bvav_resp ) ) {
                    $bvav_data = base64_decode( $bvav_resp['body'] );
                    if( !empty( $bvav_data ) ){
                        $bvav_response = json_decode( $bvav_data, true );
                        if( is_array( $bvav_response ) ){
                            $bvav_filtered = array_values( $bvav_response );
                            $bvallav = array_merge( ...$bvav_filtered );
                            
                            if( !empty( $bvallav ) ){
                                foreach( $bvallav as $bvav_details ){
                                    $bvav_installer = $bvav_details['addon_installer'];
    
                                    if( file_exists( WP_PLUGIN_DIR . '/' . $bvav_installer ) ){
                                        $bvavpdata = get_plugin_data( WP_PLUGIN_DIR . '/' . $bvav_installer );
                                        $bvavactv = is_plugin_active( $bvav_installer );
                                        if( $bvavactv ){
                                            $bvava_data[ $bvav_details['addon_name'] ] = $bvavpdata['Version'];
                                        } else {
                                            $bvavd_data[ $bvav_details['addon_name'] ] = $bvavpdata['Version'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $bvav_setup_data = [
                    'bvlv' => $bvlv,
                    'bvpv' => $bvpv.static::$checksum,
                    'bvava' => $bvava_data,
                    'bvavd' => $bvavd_data,
                    'bvurl' => home_url(),
                    'bplin' => get_option('bookingpress_download_plugin_wizard'),
                ];

                $bpa_validation_data = wp_json_encode( $bvav_setup_data );

                $bpa_validation_url = 'https://bookingpressplugin.com/bpa_misc/validate_plugin_setup.php';
                $bpa_validate_setup_req = wp_remote_post(
                    $bpa_validation_url,
                    [
                        'method'    => 'POST',
                        'timeout'   => 45,
                        'sslverify' => false,
                        'body'      => [
                            'bvld'  => $bpa_validation_data
                        ]
                    ]
                );

                $validate_setup_timings = 2 * DAY_IN_SECONDS;

                set_transient( 'bookingpress_validate_plugin_setup_timings', 'status_updated', $validate_setup_timings  );

            }

        }

        
        function bookingpress_signout_google_account_arr( ){            
            global $wpdb, $BookingPress;

            $response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'remove_google_api_account', true, 'bpa_wp_nonce' );

            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $BookingPress->bookingpress_update_settings('bookingpress_gmail_auth_token', 'notification_setting', '');
            $BookingPress->bookingpress_update_settings('bookingpress_response_email','notification_setting','');
            $BookingPress->bookingpress_update_settings('bookingpress_gmail_auth','notification_setting', '');

            $response['variant'] = 'success';
            $response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
            $response['msg']     = esc_html__( 'Sign out successfully.', 'bookingpress-appointment-booking' );

            echo wp_json_encode( $response );
            die;
        }

        function bookingpress_add_gmail_api(){
            if( isset( $_GET['page'] ) && 'bookingpress_gmailapi' == $_GET['page'] ){

                if( empty( $_GET['state'] ) ){
                    echo "<script type='text/javascript' data-cfasync='false'>";
                    echo "let url = document.URL;";
                    echo "if( /\#state/.test( url ) ){";
                        echo "url = url.replace( /\#state/, '&state' );";
                        echo "window.location.href= url;";
                    echo "} else {";
                        echo "window.location.href='" . get_home_url() . "';"; //phpcs:ignore
                    echo "}";
                    echo "</script>";
                } else {
                    global $wpdb, $tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings,$BookingPress;
                    $state = base64_decode( sanitize_text_field( $_GET['state'] ) );
                    
                    if( preg_match( '/(gmail_oauth)/', $state ) ){
	                    
                        require_once BOOKINGPRESS_LIBRARY_DIR . "/gmail/vendor/autoload.php";
                        
                        $code = !empty( $_GET['code'] ) ? urldecode( $_GET['code'] ) : ''; //phpcs:ignore

                        $bookingpress_client_secret = $BookingPress->bookingpress_get_settings('gmail_client_secret', 'notification_setting');
    
                        $bookingpress_client_id =  esc_html($BookingPress->bookingpress_get_settings('gmail_client_ID', 'notification_setting'));
                        $bookingpress_redirect_url = get_home_url() .'?page=bookingpress_gmailapi';

                        $client = new Google_Client();
                        $client->setClientId($bookingpress_client_id);
                        $client->setClientSecret( $bookingpress_client_secret );
                        $client->setRedirectUri( $bookingpress_redirect_url);
                        $client->setAccessType( 'offline' );

                        $response_data  = $client->authenticate( $code );

                        if( !empty($response_data)){
                            if( isset($response_data['access_token']) && $response_data['access_token'] != '' ){

                                $access_token = $response_data['access_token'];
                            }
                        }

                        $client->setAccessToken( $response_data );

                        $service = new Google\Service\Gmail( $client );  

		                try {
			                $email = $service->users->getProfile( 'me' )->getEmailAddress();
		                } catch ( \Exception $e ) {
			                $email = '';
		                }

                        

                        ?>
                        <script>
                            window.opener.app.notification_setting_form.bookingpress_response_email = <?php echo wp_json_encode( $email ); ?>;
                            window.opener.app.notification_setting_form.bookingpress_gmail_auth = '<?php echo wp_json_encode($response_data); ?>';
                            window.opener.app.notification_setting_form.bookingpress_gmail_auth_token = <?php echo wp_json_encode( $access_token ); ?>;
                            window.close();
                        </script>
                        <?php
                    }
                }
                die;
            }
        }

        
        /**
         * Setting module default data variables
         *
         * @return void
         */
        function bookingpress_dynamic_setting_data_fields(){
            global $bookingpress_dynamic_setting_data_fields,$bookingpress_global_options;
            $bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_country_list               = $bookingpress_options['country_lists'];
            $bookingpress_countries_currency_details = json_decode($bookingpress_options['countries_json_details']);
            $timepicker_options                      = $bookingpress_options['timepicker_options'];
            $bookingpress_pagination                 = $bookingpress_options['pagination'];
            $bookingpress_pagination_arr             = json_decode($bookingpress_pagination, true);
            $bookingpress_pagination_selected        = $bookingpress_pagination_arr[0];

            $bookingpress_appointment_statuses = $bookingpress_options['appointment_status'];

            foreach($bookingpress_appointment_statuses as $k => $v){
                if($v['value'] != 1 && $v['value'] != 2){
                    unset($bookingpress_appointment_statuses[$k]);
                }
            }

            $bookingpress_dynamic_setting_data_fields = array(
                'modal_loading'                    => 'false',
                'flags_img_url'                    => BOOKINGPRESS_IMAGES_URL,
                'modals'                           => array(
                    'general_setting_modal'      => false,
                    'company_setting_modal'      => false,
                    'notification_setting_modal' => false,
                    'workhours_setting_modal'    => false,
                    'appointment_setting_modal'  => false,
                    'label_setting_modal'        => false,
                    'payment_setting_modal'      => false,
                ),
                'default_appointment_staus'        => $bookingpress_appointment_statuses,
                'default_appointment_staus'        => $bookingpress_appointment_statuses,
                'default_pagination'               => array(
                    array(
                        'text'  => '10',
                        'value' => '10',
                    ),
                    array(
                        'text'  => '20',
                        'value' => '20',
                    ),
                    array(
                        'text'  => '50',
                        'value' => '50',
                    ),
                    array(
                        'text'  => '100',
                        'value' => '100',
                    ),
                    array(
                        'text'  => '200',
                        'value' => '200',
                    ),
                    array(
                        'text'  => '300',
                        'value' => '300',
                    ),
                    array(
                        'text'  => '400',
                        'value' => '400',
                    ),
                    array(
                        'text'  => '500',
                        'value' => '500',
                    ),
                ),
                'phone_countries_details'          => json_decode($bookingpress_country_list),
                'default_smtp_secure_options'      => array(
                    array(
                        'text'  => esc_html__('SSL', 'bookingpress-appointment-booking'),
                        'value' => 'SSL',
                    ),
                    array(
                        'text'  => esc_html__('TLS', 'bookingpress-appointment-booking'),
                        'value' => 'TLS',
                    ),
                    array(
                        'text'  => esc_html__('Disabled', 'bookingpress-appointment-booking'),
                        'value' => 'Disabled',
                    ),
                ),
                'default_timeslot_options'         => array(
                    array(
                        'text'  => esc_html__('5 min', 'bookingpress-appointment-booking'),
                        'value' => '5',
                    ),
                    array(
                        'text'  => esc_html__('10 min', 'bookingpress-appointment-booking'),
                        'value' => '10',
                    ),
                    array(
                        'text'  => esc_html__('15 min', 'bookingpress-appointment-booking'),
                        'value' => '15',
                    ),
                    array(
                        'text'  => esc_html__('20 min', 'bookingpress-appointment-booking'),
                        'value' => '20',
                    ),
                    array(
                        'text'  => esc_html__('25 min', 'bookingpress-appointment-booking'),
                        'value' => '25',
                    ),
                    array(
                        'text'  => esc_html__('30 min', 'bookingpress-appointment-booking'),
                        'value' => '30',
                    ),
                    array(
                        'text'  => esc_html__('35 min', 'bookingpress-appointment-booking'),
                        'value' => '35',
                    ),
                    array(
                        'text'  => esc_html__('40 min', 'bookingpress-appointment-booking'),
                        'value' => '40',
                    ),
                    array(
                        'text'  => esc_html__('45 min', 'bookingpress-appointment-booking'),
                        'value' => '45',
                    ),
                    array(
                        'text'  => esc_html__('50 min', 'bookingpress-appointment-booking'),
                        'value' => '50',
                    ),
                    array(
                        'text'  => esc_html__('55 min', 'bookingpress-appointment-booking'),
                        'value' => '55',
                    ),
                    array(
                        'text'  => esc_html__('1 h', 'bookingpress-appointment-booking'),
                        'value' => '60',
                    ),
                    array(
                        'text'  => esc_html__('1 h 30 min', 'bookingpress-appointment-booking'),
                        'value' => '90',
                    ),
                    array(
                        'text'  => esc_html__('2 h', 'bookingpress-appointment-booking'),
                        'value' => '120',
                    ),
                    array(
                        'text'  => esc_html__('2 h 30 min', 'bookingpress-appointment-booking'),
                        'value' => '150',
                    ),
                    array(
                        'text'  => esc_html__('3 h', 'bookingpress-appointment-booking'),
                        'value' => '180',
                    ),
                    array(
                        'text'  => esc_html__('3 h 30 min', 'bookingpress-appointment-booking'),
                        'value' => '210',
                    ),
                    array(
                        'text'  => esc_html__('4 h', 'bookingpress-appointment-booking'),
                        'value' => '240',
                    ),
                    array(
                        'text'  => esc_html__('4 h 30 min', 'bookingpress-appointment-booking'),
                        'value' => '270',
                    ),
                    array(
                        'text'  => esc_html__('5 h', 'bookingpress-appointment-booking'),
                        'value' => '300',
                    ),
                    array(
                        'text'  => esc_html__('5 h 30 min', 'bookingpress-appointment-booking'),
                        'value' => '330',
                    ),
                    array(
                        'text'  => esc_html__('6 h', 'bookingpress-appointment-booking'),
                        'value' => '360',
                    ),
                    array(
                        'text'  => esc_html__('6 h 30 min', 'bookingpress-appointment-booking'),
                        'value' => '390',
                    ),
                    array(
                        'text'  => esc_html__('7 h', 'bookingpress-appointment-booking'),
                        'value' => '420',
                    ),
                    array(
                        'text'  => esc_html__('7 h 30 min', 'bookingpress-appointment-booking'),
                        'value' => '450',
                    ),
                    array(
                        'text'  => esc_html__('8 h', 'bookingpress-appointment-booking'),
                        'value' => '480',
                    )
                ),
                'price_symbol_position_val'        => array(
                    array(
                        'text'        => esc_html__('Before value', 'bookingpress-appointment-booking'),
                        'value'       => 'before',
                        'position_ex' => '$100',
                    ),
                    array(
                        'text'        => esc_html__('Before value', 'bookingpress-appointment-booking') . ', ' . esc_html__('separated with space', 'bookingpress-appointment-booking'),
                        'value'       => 'before_with_space',
                        'position_ex' => '$ 100',
                    ),
                    array(
                        'text'        => esc_html__('After value', 'bookingpress-appointment-booking'),
                        'value'       => 'after',
                        'position_ex' => '100$',
                    ),
                    array(
                        'text'        => esc_html__('After value', 'bookingpress-appointment-booking') . ', ' . esc_html__('separated with space', 'bookingpress-appointment-booking'),
                        'value'       => 'after_with_space',
                        'position_ex' => '100 $',
                    ),
                ),
                'price_separator_vals'             => array(
                    array(
                        'text'         => esc_html__('Comma-Dot', 'bookingpress-appointment-booking'),
                        'value'        => 'comma-dot',
                        'separator_ex' => '15,000.00',
                    ),
                    array(
                        'text'         => esc_html__('Dot-Comma', 'bookingpress-appointment-booking'),
                        'value'        => 'dot-comma',
                        'separator_ex' => '15.000,00',
                    ),
                    array(
                        'text'         => esc_html__('Space-Dot', 'bookingpress-appointment-booking'),
                        'value'        => 'space-dot',
                        'separator_ex' => '15 000.00',
                    ),
                    array(
                        'text'         => esc_html__('Space-Comma', 'bookingpress-appointment-booking'),
                        'value'        => 'space-comma',
                        'separator_ex' => '15 000,00',
                    ),
                    array(
                        'text'  => esc_html__('Custom', 'bookingpress-appointment-booking'),
                        'value' => 'Custom',
                    ),
                ),
                'default_payment_method'           => array(
                    array(
                        'text'  => esc_html__('On-site', 'bookingpress-appointment-booking'),
                        'value' => 'on_site',
                    ),
                    array(
                        'text'  => esc_html__('PayPal', 'bookingpress-appointment-booking'),
                        'value' => 'paypal',
                    ),
                ),
                'currency_countries'               => $bookingpress_countries_currency_details,
                'general_setting_form'             => array(
                    'default_time_slot_step'              => '30',
                    'appointment_status'                  => '1',
                    'onsite_appointment_status'           => '2',
                    'default_phone_country_code'          => 'us',
                    'per_page_item'                       => '20',
                    'phone_number_mandatory'              => false,
                    'share_timeslot_between_services'     => false,
                    'use_already_loaded_vue'              => false,
                    'load_js_css_all_pages'               => false,
                    'show_time_as_per_service_duration'   => true,
                    'default_time_slot'                   => '30',
                    'default_date_format'                   => 'F j, Y',                    
                    'general_setting_phone_number'        => '',
                    'anonymous_data'                      => 'false',
                    'default_time_format'                 => 'g:i a',
                    'bpa_afternoon_start_time'            => '12:00:00',
                    'bpa_evening_start_time'              => '16:00:00',
                    'bpa_night_start_time'                => '20:00:00',
                ),
                'company_setting_form'             => array(
                    'company_avatar_img'    => '',
                    'company_avatar_url'    => '',
                    'company_avatar_list'   => array(),
                    'company_name'          => '',
                    'company_address'       => '',
                    'company_website'       => '',
                    'company_phone_country' => 'us',
                    'company_phone_number'  => '',
                ),
                'notification_setting_form'        => array(
                    'selected_mail_service' => 'wp_mail',
                    'sender_name'           => get_option('blogname'),
                    'sender_email'          => get_option('admin_email'),
                    'admin_email'           => get_option('admin_email'),
                    'success_url'           => '',
                    'cancel_url'            => '',
                    'smtp_host'             => '',
                    'smtp_port'             => '',
                    'smtp_secure'           => 'Disabled',
                    'smtp_username'         => '',
                    'smtp_password'         => '',
                    'gmail_client_ID'       => '',
                    'gmail_client_secret'   => '',
                    'gmail_redirect_url'   => '',
                    'gmail_auth_secret'    => '',
                    'bookingpress_gmail_auth'            => '',
                    'bookingpress_response_email'        => '',
                    'bookingpress_gmail_auth_token' => '',
                ),
                'notification_smtp_test_mail_form' => array(
                    'smtp_test_receiver_email' => '',
                    'smtp_test_msg'            => '',
                ),
                'notification_gmail_test_mail_form' => array(
                    'gmail_test_receiver_email' => '',
                    'gmail_test_msg'            => '',
                ),
                'notification_wpmail_test_mail_form' => array(
                    'wpmail_test_receiver_email' => '',
                    'wpmail_test_msg'            => '',
                ),
                'payment_setting_form'             => array(
                    'payment_default_currency' => 'USD',
                    'price_symbol_position'    => 'before',
                    'price_separator'          => 'comma-dot',
                    'price_number_of_decimals' => 2,
                    'on_site_payment'          => true,
                    'paypal_payment'           => false,
                    'paypal_payment_mode'      => 'sandbox',
                    'paypal_payment_method_type' => 'lagacy',
                    'paypal_client_id'         => '',
                    'paypal_client_secret'     => '',
                    'paypal_merchant_email'    => '',
                    'paypal_api_username'      => '',
                    'paypal_api_password'      => '',
                    'paypal_api_signature'     => '',
                    'custom_comma_separator'   => '',
                    'custom_dot_separator'     => '',
                ),
                'message_setting_form'             => array(
                    'appointment_booked_successfully'                 => esc_html__('Appointment has been booked successfully.','bookingpress-appointment-booking'),
                    'appointment_cancelled_successfully'              => esc_html__('Appointment has been cancelled successfully.','bookingpress-appointment-booking'),
                    'duplidate_appointment_time_slot_found'           => esc_html__('I am sorry! Another appointment is already booked with this time slot. Please select another time slot which suits you the best.','bookingpress-appointment-booking'),
                    'unsupported_currecy_selected_for_the_payment'    => esc_html__('I am sorry! The selected currency is not supported by PayPal payment gateway. Please proceed with another available payment method.','bookingpress-appointment-booking'),
                    'duplicate_email_address_found'                   => esc_html__('I am sorry! This email address is already exists. Please enter another email address.','bookingpress-appointment-booking'),
                    'no_payment_method_is_selected_for_the_booking'   => esc_html__('Please select a payment method to proceed with the booking.','bookingpress-appointment-booking'),
                    'no_appointment_time_selected_for_the_booking'    => esc_html__('Please select a time slot to proceed with the booking.','bookingpress-appointment-booking'),
                    'no_appointment_date_selected_for_the_booking'    => esc_html__('Please select appointment date to proceed with the booking.','bookingpress-appointment-booking'),
                    'no_service_selected_for_the_booking'             => esc_html__('Please select any service to book the appointment','bookingpress-appointment-booking'),
                    'no_payment_method_available'                     => esc_html__('Oops! There is no payment method available.','bookingpress-appointment-booking'),
                    'no_timeslots_available'                          => esc_html__('There is no time slots available','bookingpress-appointment-booking'),
                    'cancel_appointment_confirmation'                 => esc_html__('This is a cancel appointment confirmation message','bookingpress-appointment-booking'),
                    'no_appointment_available_for_cancel'             => esc_html__('No appointment available for the cancel','bookingpress-appointment-booking'),
                ),
                'debug_log_setting_form'           => array(
                    'on_site_payment' => false,
                    'paypal_payment'  => false,
                ),
                'customer_setting_form'            => array(
                    'allow_wp_user_create' => false,
                    'allow_autologin_user' => true,
                ),
                'is_edit_break' => 0,
                'succesfully_send_test_email'      => 0,
                'error_send_test_email'            => 0,
                'error_text_of_test_email'         => '',
                'is_disable_send_test_email_btn'   => false,
                'is_display_send_test_mail_loader' => '0',
                'imageUrl'                         => '',
                'monday'                           => 'monday',
                'add_work_hours_display'           => '',
                'work_type_modal'                  => 'monday_work_hours',
                'work_hours_days_arr'              => array(),
                'timepicker_options'               => json_decode($timepicker_options),
                'work_start_time'                  => '',
                'work_end_time'                    => '',
                'final_work_hours_data'            => array(),
                'days_off_year_filter'             => date('Y'),
                'rules_dayoff'                     => array(
                    'dayoff_name' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter name', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'dayoff_date' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select date', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'rules_general'                    => array(                    
                ),
                'rules_company'                    => array(
                    'company_name'         => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter company name', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'company_address'      => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter company address', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'company_website'      => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter company website', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'company_phone_number' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter phone number', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'rules_notification'               => array(
                    'sender_name'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter sender name', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'sender_email'  => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter sender email', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'sender_url'    => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter sender url', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'admin_email'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter admin email', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'success_url'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter successfull redirection url', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'cancel_url'    => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter cancel redirection url', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'smtp_port'     => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter smtp port', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'smtp_host'     => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter smtp host', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'smtp_secure'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter smtp secure', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'gmail_client_ID' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter gmail client ID', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'gmail_client_secret' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter gmail client secret', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),

                ),
                'rules_smtp_test_mail'             => array(
                    'smtp_test_receiver_email' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter email address', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'smtp_test_msg'            => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'rules_gmail_test_mail'             => array(
                    'gmail_test_receiver_email' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter email address', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'gmail_test_msg'            => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'rules_wpmail_test_mail'             => array(
                    'wpmail_test_receiver_email' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter email address', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'wpmail_test_msg'            => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'rules_payment'                    => array(
                    'paypal_merchant_email' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter merchant email', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'paypal_api_username'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter api username', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'paypal_client_id'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter client ID', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),                    
                    'paypal_client_secret'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter client secret', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'paypal_api_password'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter api password', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'paypal_api_signature'  => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter api signature', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'days_off_rules'                   => array(
                    'daysoff_title' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter holiday reason', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'rules_message'                    => array(
                    'appointment_booked_successfully'                 => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'appointment_cancelled_successfully'              => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'duplidate_appointment_time_slot_found'           => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'unsupported_currecy_selected_for_the_payment'    => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'duplicate_email_address_found'                   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'no_payment_method_is_selected_for_the_booking'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'no_appointment_time_selected_for_the_booking'    => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'no_appointment_date_selected_for_the_booking'    => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'no_service_selected_for_the_booking'             => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'no_payment_method_available' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),        
                    'no_timeslots_available' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'cancel_appointment_confirmation' => array(
                        array(
                        'required' => true,
                        'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                        'trigger'  => 'blur',
                        ),
                    ),
                    'no_appointment_available_for_cancel' => array(
                        array(
                        'required' => true,
                        'message'  => esc_html__('Please enter message', 'bookingpress-appointment-booking'),
                        'trigger'  => 'blur',
                        ),
                    ),                    
                ),
                'edit_dayoff_name'                 => '',
                'edit_dayoff_date'                 => '',
                'edit_dayoff_repeat'               => false,
                'needHelpDrawer'                   => false,
                'needHelpDrawerDirection'          => 'rtl',
                'comShowFileList'                  => false,
                'workhours_timings'                => array(
                    'Monday'    => array(
                        'start_time' => '09:00:00',
                        'end_time'   => '17:00:00',
                    ),
                    'Tuesday'   => array(
                        'start_time' => '09:00:00',
                        'end_time'   => '17:00:00',
                    ),
                    'Wednesday' => array(
                        'start_time' => '09:00:00',
                        'end_time'   => '17:00:00',
                    ),
                    'Thursday'  => array(
                        'start_time' => '09:00:00',
                        'end_time'   => '17:00:00',
                    ),
                    'Friday'    => array(
                        'start_time' => '09:00:00',
                        'end_time'   => '17:00:00',
                    ),
                    'Saturday'  => array(
                        'start_time' => 'Off',
                        'end_time'   => 'Off',
                    ),
                    'Sunday'    => array(
                        'start_time' => 'Off',
                        'end_time'   => 'Off',
                    ),
                ),
                'isloading'                        => false,
                'open_add_break_modal'             => false,
                'break_modal_pos'                  => '254px',
                'break_modal_pos_right'            => '',
                'default_break_timings'            => array(),
                'break_selected_day'               => 'Monday',
                'break_timings'                    => array(
                    'start_time'     => '',
                    'end_time'       => '',
                    'edit_index'     => '',
                ),
                'rules_add_break'                  => array(
                    'start_time' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter start time', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'end_time'   => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter end time', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'selected_break_timings'           => array(
                    'Monday'    => array(),
                    'Tuesday'   => array(),
                    'Wednesday' => array(),
                    'Thursday'  => array(),
                    'Friday'    => array(),
                    'Saturday'  => array(),
                    'Sunday'    => array(),
                ),
                'days'                             => array(),
                'open_add_daysoff_details'         => false,
                'days_off_top_pos'                 => '0',
                'days_off_left_pos'                => '0',
                'days_off_form'                    => array(
                    'dayoff_id'          => 0,
                    'daysoff_title'      => '',
                    'is_repeat_days_off' => false,
                    'selected_date'      => '',
                    'selected_end_date'  => '',
                    'is_edit'            => '',
                ),
                'daysoff_default_year'             => date('Y'),
                'daysoff_selected_year'            => date('Y'),
                'is_display_loader'                => '0',
                'is_disabled'                      => false,
                'is_display_save_loader'           => '0',
                'is_mask_display'                  => false,
                'open_display_log_modal'           => false,
                'items'                            => array(),
                'multipleSelection'                => array(),
                'perPage'                          => $bookingpress_pagination_selected,
                'totalItems'                       => 0,
                'pagination_selected_length'       => $bookingpress_pagination_selected,
                'pagination_length'                => $bookingpress_pagination,
                'currentPage'                      => 1,
                'pagination_length_val'            => '10',
                'open_view_model_gateway'          => '',
                'open_view_model_gateway_name'     => '', 
                'is_display_loader_view'           => '0',
                'select_download_log'              => '7',
                'log_download_default_option'      => array(
                    array(
                        'key'   => esc_html__('Last 1 Day', 'bookingpress-appointment-booking'),
                        'value' => '1',
                    ),
                    array(
                        'key'   => esc_html__('Last 3 Days', 'bookingpress-appointment-booking'),
                        'value' => '3',
                    ),
                    array(
                        'key'   => esc_html__('Last 1 Week', 'bookingpress-appointment-booking'),
                        'value' => '7',
                    ),
                    array(
                        'key'   => esc_html__('Last 2 Weeks', 'bookingpress-appointment-booking'),
                        'value' => '14',
                    ),
                    array(
                        'key'   => esc_html__('Last Month', 'bookingpress-appointment-booking'),
                        'value' => '30',
                    ),
                    array(
                        'key'   => esc_html__('All', 'bookingpress-appointment-booking'),
                        'value' => 'all',
                    ),
                    array(
                        'key'   => esc_html__('Custom', 'bookingpress-appointment-booking'),
                        'value' => 'custom',
                    ),
                ),
                'download_log_daterange'             => array( date('Y-m-d', strtotime('-3 Day')), date('Y-m-d', strtotime('+3 Day')) ),
                'is_display_download_save_loader'    => '0',
                'proper_body_class'                  => false,
                'smtp_mail_error_text'               => '',
                'smtp_error_modal'                   => false,
                'bookingpress_currency_warnning_msg' => '',
                'bookingpress_currency_warnning'     => '0',
                'succesfully_send_test_gmail_email'      => 0,
                'error_send_test_gmail_email'            => 0,
                'error_text_of_test_gmail_email'         => '',
                'is_disable_send_test_gmail_email_btn'   => false,
                'is_display_send_test_gmail_mail_loader' => '0',
                'is_disable_send_test_wpmail_email_btn'  => false,
                'is_display_send_test_wpmail_mail_loader' => '0',
                'succesfully_send_test_wpmail_email'      => 0,
                'error_send_test_wpmail_email'            => 0,
                'gmail_mail_error_text'                   => '',

            );

        }
        
        /**
         * Ajax request for delete default daysoff details
         *
         * @return void
         */
        function bookingpress_delete_daysoff_details_func()
        {
            global $wpdb, $tbl_bookingpress_default_daysoff;

            $response              = array();
            $response['variant']   = 'error';
            $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

            $bpa_check_authorization = $this->bpa_check_authentication( 'delete_holidays', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $daysoff_date = ! empty($_REQUEST['days_off_form']['selected_date']) ? sanitize_text_field($_REQUEST['days_off_form']['selected_date']) : ''; // phpcs:ignore
            $dayoff_id = ! empty($_REQUEST['days_off_form']['dayoff_id']) ? sanitize_text_field($_REQUEST['days_off_form']['dayoff_id']) : 0; // phpcs:ignore
            if (!empty($dayoff_id) ) {
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$tbl_bookingpress_default_daysoff} WHERE (bookingpress_dayoff_id = %d OR bookingpress_dayoff_parent = %d)", $dayoff_id,$dayoff_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False Positive alarm                
            }

            $response['variant'] = 'success';
            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('DaysOff deleted successfully', 'bookingpress-appointment-booking');

            wp_send_json($response);
            exit();
        }
        
        /**
         * Ajax request for Get default daysoff details
         *
         * @return void
         */
        function bookingpress_get_daysoff_details_func()
        {
            global $wpdb, $tbl_bookingpress_default_daysoff;
            $response                 = array();
            $response['variant']      = 'error';
            $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']          = esc_html__('Something went wrong', 'bookingpress-appointment-booking');
            $response['daysoff_data'] = '';

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_holidays', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $daysoff_selected_year = ! empty($_POST['selected_year']) ? sanitize_text_field($_POST['selected_year']) : date('Y', current_time('timestamp')); // phpcs:ignore WordPress.Security.NonceVerification

            $default_daysoff_details = array();
            $daysoff_details         = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_daysoff} where bookingpress_dayoff_parent = %d",0),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False Positive alarm
            foreach ( $daysoff_details as $daysoff_details_key => $daysoff_details_val ) {
                $daysoff_date        = esc_html($daysoff_details_val['bookingpress_dayoff_date']);

                $bookingpress_dayoff_enddate = esc_html($daysoff_details_val['bookingpress_dayoff_enddate']);               
                $daysoff_end_date = date('c', strtotime($daysoff_date));
                if($bookingpress_dayoff_enddate != null && $bookingpress_dayoff_enddate != 'null'){                    
                    $daysoff_end_date = date('c', strtotime($bookingpress_dayoff_enddate));
                }

                $yearly_repeat_class = !empty($daysoff_details_val['bookingpress_repeat']) ? 'bpa-daysoff-calendar-col--item__highlight--yearly bpa_selected_daysoff' : 'bpa-daysoff-calendar-col--item__highlight--single-dayoff bpa_selected_daysoff';
                $dayoff_year = date('Y', strtotime($daysoff_date));

                if (empty($daysoff_details_val['bookingpress_repeat']) && ( $dayoff_year == $daysoff_selected_year ) ) {
                    $default_daysoff_details[] = array(
                        'dayoff_id' => esc_html($daysoff_details_val['bookingpress_dayoff_id']),
                        'id'        => date('Y-m-d', strtotime($daysoff_date)),
                        'date'      => date('c', strtotime($daysoff_date)),
                        'end_date'  => $daysoff_end_date,
                        'class'     => $yearly_repeat_class,
                        'off_name'  => stripslashes_deep($daysoff_details_val['bookingpress_name']),
                    );
                } elseif (! empty($daysoff_details_val['bookingpress_repeat']) && ( $daysoff_selected_year >= $dayoff_year ) ) {
                    $daysoff_new_date_month    = $daysoff_selected_year . '-' . date('m-d', strtotime($daysoff_date));   
                    $daysoff_end_date = date('c', strtotime($daysoff_new_date_month));                 
                    if($bookingpress_dayoff_enddate != null && $bookingpress_dayoff_enddate != 'null'){   
                        $daysoff_new_end_date_month    = $daysoff_selected_year . '-' . date('m-d', strtotime($bookingpress_dayoff_enddate));                        
                        $daysoff_end_date = date('c', strtotime($daysoff_new_end_date_month));
                    }                    
                    $default_daysoff_details[] = array(
                        'dayoff_id' => esc_html($daysoff_details_val['bookingpress_dayoff_id']),
                        'id'       => $daysoff_new_date_month,
                        'date'     => date('c', strtotime($daysoff_new_date_month)),
                        'end_date' => $daysoff_end_date,
                        'class'    => $yearly_repeat_class,
                        'off_name' => stripslashes_deep($daysoff_details_val['bookingpress_name']),
                    );
                }
            }

            $response['variant']      = 'success';
            $response['title']        = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']          = esc_html__('DaysOff data retrieved successfully', 'bookingpress-appointment-booking');
            $response['daysoff_data'] = $default_daysoff_details;

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Ajax request for save default daysoff details
         *
         * @return void
         */
        function bookingpress_save_default_daysoff_details_func()
        {
            global $wpdb, $tbl_bookingpress_default_daysoff;
            $response              = array();
            $response['variant']   = 'error';
            $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

            $bpa_check_authorization = $this->bpa_check_authentication( 'save_default_holidays', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            if (! empty($_REQUEST['daysoff_details']) ) {

                $daysoff_title     = ! empty($_REQUEST['daysoff_details']['daysoff_title']) ? sanitize_text_field($_REQUEST['daysoff_details']['daysoff_title']) : '';
                $is_repeat_daysoff = ! empty($_REQUEST['daysoff_details']['is_repeat_days_off']) ? sanitize_text_field($_REQUEST['daysoff_details']['is_repeat_days_off']) : '';
                $is_repeat_daysoff = ( $is_repeat_daysoff == 'true' ) ? 1 : 0;
                $daysoff_date      = ! empty($_REQUEST['daysoff_details']['selected_date']) ? sanitize_text_field($_REQUEST['daysoff_details']['selected_date']) : '';
                if (! empty($daysoff_date) ) {
                    $daysoff_date = date('Y-m-d', strtotime($daysoff_date));
                }

                $dayoff_id      = (isset($_REQUEST['daysoff_details']['dayoff_id'])) ? intval($_REQUEST['daysoff_details']['dayoff_id']) : 0;
                $daysoff_end_date      = ! empty($_REQUEST['daysoff_details']['selected_end_date']) ? sanitize_text_field($_REQUEST['daysoff_details']['selected_end_date']) : '';
                if (! empty($daysoff_end_date) ) {
                    $daysoff_end_date = date('Y-m-d', strtotime($daysoff_end_date));
                }
                $bookingpress_child_holiday_dates = array();
                if($daysoff_date != $daysoff_end_date){                    
                    
                    $startDate = strtotime($daysoff_date)+86400;
                    $endDate = strtotime($daysoff_end_date);                 
                    for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
                        $date = date('Y-m-d', $currentDate);
                        $bookingpress_child_holiday_dates[] = $date;
                    }                    
                }                            

                if (! empty($daysoff_title) ) {

                    if($dayoff_id != 0){
                        
                        $daysoff_database_data = array(
                            'bookingpress_name'        => $daysoff_title, 
                            'bookingpress_dayoff_date' => $daysoff_date,
                            'bookingpress_dayoff_enddate' => $daysoff_end_date,                                                  
                            'bookingpress_repeat'      => $is_repeat_daysoff,
                        );                        
                        $dayoff_where_condition = array(
                            'bookingpress_dayoff_id' => $dayoff_id,
                        );                        
                        $wpdb->update($tbl_bookingpress_default_daysoff, $daysoff_database_data, $dayoff_where_condition);                        
                        $dayoff_where_condition = array(
                            'bookingpress_dayoff_parent' => $dayoff_id,
                        );                        
                        /* $wpdb->update($tbl_bookingpress_default_daysoff, $daysoff_database_data, $dayoff_where_condition);  */          
                                                
                        $wpdb->delete($tbl_bookingpress_default_daysoff, $dayoff_where_condition);

                        foreach($bookingpress_child_holiday_dates as $holiday_date){
                            $single_holiday_date_data = array(
                                    'bookingpress_name'        => $daysoff_title,
                                    'bookingpress_dayoff_date' => $holiday_date,
                                    'bookingpress_dayoff_enddate' => $daysoff_end_date,
                                    'bookingpress_dayoff_parent' => (int)$dayoff_id,
                                    'bookingpress_repeat'      => $is_repeat_daysoff,
                            );
                            $wpdb->insert($tbl_bookingpress_default_daysoff, $single_holiday_date_data);                                
                        }
                        

                    } else {


                        $bookingpress_daysoff_exists  = $wpdb->get_row( $wpdb->prepare("SELECT bookingpress_dayoff_id FROM {$tbl_bookingpress_default_daysoff} where bookingpress_dayoff_date >= %s AND bookingpress_dayoff_date <= %s",$daysoff_date,$daysoff_end_date),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False Positive alarm
                        if(empty($bookingpress_daysoff_exists)){
                            $daysoff_database_data = array(
                                'bookingpress_name'        => $daysoff_title,
                                'bookingpress_dayoff_date' => $daysoff_date,
                                'bookingpress_dayoff_enddate' => $daysoff_end_date,                        
                                'bookingpress_repeat'      => $is_repeat_daysoff,
                            );                        
                            $wpdb->insert($tbl_bookingpress_default_daysoff, $daysoff_database_data);
                            $bookingpress_child_holiday_database_data = array();
                            if(!empty($bookingpress_child_holiday_dates)){

                                $dayoff_parent_id = $wpdb->insert_id;
                                foreach($bookingpress_child_holiday_dates as $holiday_date){
                                    $single_holiday_date_data = array(
                                            'bookingpress_name'        => $daysoff_title,
                                            'bookingpress_dayoff_date' => $holiday_date,
                                            'bookingpress_dayoff_enddate' => $daysoff_end_date,
                                            'bookingpress_dayoff_parent' => (int)$dayoff_parent_id,
                                            'bookingpress_repeat'      => $is_repeat_daysoff,
                                    );
                                    $wpdb->insert($tbl_bookingpress_default_daysoff, $single_holiday_date_data);                                
                                }
                                
                            } 
                        }else{                            
                            $response['msg'] = esc_html__('Holiday already added.', 'bookingpress-appointment-booking');
                            echo wp_json_encode($response);
                            exit();
                        }
                    }
                    $response['variant'] = 'success';
                    $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg']     = esc_html__('Holiday has been saved successfully.', 'bookingpress-appointment-booking');
                } else {
                    $response['msg'] = esc_html__('Please fill Break Title', 'bookingpress-appointment-booking');
                }
            }

            wp_cache_delete( 'bookingpress_all_general_settings' );
            wp_cache_delete( 'bookingpress_all_customize_settings' );

            echo wp_json_encode($response);
            exit();
        }

        function bookingpress_record_failed_wpmail( $wp_error_object ){

            if( true == $this->bpa_sending_wp_mail || true == $this->bpa_sending_wp_mail_upon_booking ){
                $bpa_error_data = array();
                if( isset( $wp_error_object->errors ) ){
                    foreach( $wp_error_object->errors as $key_error => $error_description ){
                        $bpa_error_data[] = implode( ' ', $error_description );
                    }
                }

                if ( ! empty( $bpa_error_data ) ) {
                    $error_description = implode(' ', $bpa_error_data ) ;
                    
                    $this->bpa_sent_test_wpmail_errors = $error_description;

                    if( true == $this->bpa_sending_wp_mail_upon_booking ){
                        update_option( 'bookingpress_display_wpmail_failed_msg_notice', true );
                        update_option( 'bookingpress_wpmail_failed_msg_data', wp_json_encode( $bpa_error_data ) );

                        global $bookingpress_other_debug_log_id;

                        $bookingpressmailer_errorinfo['error_object'] = $wp_error_object;

                        do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification WordPress default error response', 'bookingpress_email_notiifcation', $bookingpressmailer_errorinfo, $bookingpress_other_debug_log_id);
                    }
                }
            }

        }
        
        /**
         * Send test email notification for WordPress default configuration
         *
         * @return void
         */
        function bookingpress_send_test_wpmail_email_func(){
            global $bookingpress_email_notifications;
            $response               = array();
            $response['variant']    = 'error';
            $response['title']      = esc_html__( 'Error', 'bookingpress-appointment-booking');
            $response['msg']        = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

            $bpa_check_authorization = $this->bpa_check_authentication( 'send_test_gmail_email', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            if (! empty($_REQUEST['notification_formdata']) ) {

                $wpmail_test_receiver_email      = ! empty($_REQUEST['notification_test_mail_formdata']['wpmail_test_receiver_email']) ? sanitize_email($_REQUEST['notification_test_mail_formdata']['wpmail_test_receiver_email']) : '';
                $wpmail_test_msg                 = ! empty($_REQUEST['notification_test_mail_formdata']['wpmail_test_msg']) ? sanitize_text_field($_REQUEST['notification_test_mail_formdata']['wpmail_test_msg']) : '';

                $from_email = !empty( $_REQUEST['notification_formdata']['sender_email'] ) ? sanitize_email( $_REQUEST['notification_formdata']['sender_email'] ) : get_option( 'admin_email' );
                $from_name = !empty(  $_REQUEST['notification_formdata']['send_name'] ) ? sanitize_text_field( $_REQUEST['notification_formdata']['send_name'] ) : get_option( 'blogname' );

                $bookingpress_email_header_data  = 'From: ' . $from_name . '<' . $from_email . "> \r\n";
                $bookingpress_email_header_data .= "Content-Type: text/html; charset=UTF-8\r\n";
               
                $this->bpa_sending_wp_mail = true;

                $wpmail_test_msg_subject = esc_html__( ' Test BookingPress WordPress default mail', 'bookingpress-appointment-booking' );

                $return = wp_mail( $wpmail_test_receiver_email, $wpmail_test_msg_subject, $wpmail_test_msg, $bookingpress_email_header_data );

                $this->bpa_sending_wp_mail = false;

                if( !empty( $this->bpa_sent_test_wpmail_errors ) ){
                    $response = array(
                        'is_mail_sent' => 0,
                        'error_msg'    => $this->bpa_sent_test_wpmail_errors,
                    );
                } else {
                    $response = array(
                        'is_mail_sent' => 1,
                        'error_msg'    => '',
                    );
                }
                
                /* if (wp_mail($receiver_email_id, $bookingpress_email_subject, $bookingpress_email_content, $bookingpress_email_header_data, $attachments) ) {
                    $bookingpress_email_send_res['is_mail_sent'] = 1;
                    $is_mail_sent                                = 1;
                } */

            }

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Send test email notification for Google/Gmail configuration
         *
         * @return void
         */
        function bookingpress_send_test_gmail_email_func()
        {
            global $bookingpress_email_notifications;
            $response              = array();
            $response['variant']   = 'error';
            $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

            $bpa_check_authorization = $this->bpa_check_authentication( 'send_test_gmail_email', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            if (! empty($_REQUEST['notification_formdata']) ) {

                $gmail_client_id                = ! empty($_REQUEST['notification_formdata']['gmail_client_ID']) ? sanitize_text_field($_REQUEST['notification_formdata']['gmail_client_ID']) : '';
                $gmail_client_secret            = ! empty($_REQUEST['notification_formdata']['gmail_client_secret']) ? sanitize_text_field($_REQUEST['notification_formdata']['gmail_client_secret']) : '';
                $bookingpress_gmail_connect     = ! empty($_REQUEST['notification_formdata']['bookingpress_response_email']) ? sanitize_email($_REQUEST['notification_formdata']['bookingpress_response_email']) : '';
                $gmail_sender_name         = ! empty($_REQUEST['notification_formdata']['sender_name']) ? sanitize_text_field( stripslashes_deep($_REQUEST['notification_formdata']['sender_name'])) : '';
                $gmail_sender_email              = ! empty($_REQUEST['notification_formdata']['sender_email']) ? sanitize_email($_REQUEST['notification_formdata']['sender_email']) : '';
                $bookingpress_gmail_auth        = ! empty($_REQUEST['notification_formdata']['bookingpress_gmail_auth']) ? sanitize_text_field($_REQUEST['notification_formdata']['bookingpress_gmail_auth']) : '';
                $gmail_auth_secret              = ! empty($_REQUEST['notification_formdata']['gmail_auth_secret']) ? $_REQUEST['notification_formdata']['gmail_auth_secret'] : '';  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - contains password
                $gmail_test_receiver_email      = ! empty($_REQUEST['notification_test_mail_formdata']['gmail_test_receiver_email']) ? sanitize_email($_REQUEST['notification_test_mail_formdata']['gmail_test_receiver_email']) : '';
                $gmail_test_msg                 = ! empty($_REQUEST['notification_test_mail_formdata']['gmail_test_msg']) ? sanitize_text_field($_REQUEST['notification_test_mail_formdata']['gmail_test_msg']) : '';

                $bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_test_gmail_notification($gmail_client_id, $gmail_client_secret, $gmail_auth_secret, $gmail_test_receiver_email, $gmail_test_msg, $bookingpress_gmail_connect, $bookingpress_gmail_auth, $gmail_sender_email, $gmail_sender_name);
                $bookingpress_email_res = json_decode($bookingpress_email_res, true);

                $response = array(
                'is_mail_sent' => $bookingpress_email_res['is_mail_sent'],
                'error_msg'    => $bookingpress_email_res['error_msg'],
                );
            }

            echo wp_json_encode($response);
            exit();
        }

        function bookingpress_send_test_email_func(){
            global $bookingpress_email_notifications;
            $response              = array();
            $response['variant']   = 'error';
            $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

            $bpa_check_authorization = $this->bpa_check_authentication( 'send_test_email', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            if (! empty($_REQUEST['notification_formdata']) ) {
                $smtp_host                = ! empty($_REQUEST['notification_formdata']['smtp_host']) ? sanitize_text_field($_REQUEST['notification_formdata']['smtp_host']) : '';
                $smtp_port                = ! empty($_REQUEST['notification_formdata']['smtp_port']) ? sanitize_text_field($_REQUEST['notification_formdata']['smtp_port']) : '';
                $smtp_secure              = ! empty($_REQUEST['notification_formdata']['smtp_secure']) ? sanitize_text_field($_REQUEST['notification_formdata']['smtp_secure']) : 'Disabled';
                $smtp_username            = ! empty($_REQUEST['notification_formdata']['smtp_username']) ? sanitize_text_field($_REQUEST['notification_formdata']['smtp_username']) : '';
                $smtp_password            = ! empty($_REQUEST['notification_formdata']['smtp_password']) ? stripslashes_deep($_REQUEST['notification_formdata']['smtp_password']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - contains password
                $smtp_sender_name         = ! empty($_REQUEST['notification_formdata']['sender_name']) ? sanitize_text_field( stripslashes_deep( $_REQUEST['notification_formdata']['sender_name'])) : '';
                $smtp_sender_email        = ! empty($_REQUEST['notification_formdata']['sender_email']) ? sanitize_email($_REQUEST['notification_formdata']['sender_email']) : '';
                $smtp_test_receiver_email = ! empty($_REQUEST['notification_test_mail_formdata']['smtp_test_receiver_email']) ? sanitize_email($_REQUEST['notification_test_mail_formdata']['smtp_test_receiver_email']) : '';
                $smtp_test_msg            = ! empty($_REQUEST['notification_test_mail_formdata']['smtp_test_msg']) ? sanitize_text_field($_REQUEST['notification_test_mail_formdata']['smtp_test_msg']) : '';

                $bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_test_email_notification($smtp_host, $smtp_port, $smtp_secure, $smtp_username, $smtp_password, $smtp_test_receiver_email, $smtp_test_msg, $smtp_sender_email, $smtp_sender_name);
                $bookingpress_email_res = json_decode($bookingpress_email_res, true);

                $response = array(
                'is_mail_sent' => $bookingpress_email_res['is_mail_sent'],
                'error_msg'    => $bookingpress_email_res['error_msg'],
                );
            }

            wp_send_json( $response );
        }
        
        /**
         * Upload company avatar
         *
         * @return void
         */
        function bookingpress_upload_company_avatar_func()
        {
            $return_data = array(
                'error'            => 0,
                'msg'              => '',
                'upload_url'       => '',
                'upload_file_name' => '',
            );

            $bpa_check_authorization = $this->bpa_check_authentication( 'upload_company_avatar', true, 'bookingpress_upload_company_avatar' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
         
            $bookingpress_fileupload_obj = new bookingpress_fileupload_class($_FILES['file']); // phpcs:ignore

            if (! $bookingpress_fileupload_obj ) {
                $return_data['error'] = 1;
                $return_data['msg']   = $bookingpress_fileupload_obj->error_message;
            }

            $bookingpress_fileupload_obj->check_cap          = true;
            $bookingpress_fileupload_obj->check_nonce        = true;
            $bookingpress_fileupload_obj->nonce_data         = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bookingpress_fileupload_obj->nonce_action       = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
            $bookingpress_fileupload_obj->check_only_image   = true;
            $bookingpress_fileupload_obj->check_specific_ext = false;
            $bookingpress_fileupload_obj->allowed_ext        = array();

            $file_name                = isset($_FILES['file']['name']) ? current_time('timestamp') . '_' . sanitize_file_name($_FILES['file']['name']) : ''; //phpcs:ignore
            $upload_dir               = BOOKINGPRESS_TMP_IMAGES_DIR . '/';
            $upload_url               = BOOKINGPRESS_TMP_IMAGES_URL . '/';
            $bookingpress_destination = $upload_dir . $file_name;

            $check_file = wp_check_filetype_and_ext( $bookingpress_destination, $file_name );
            
            if( empty( $check_file['ext'] ) ){
                $return_data['error'] = 1;
                $return_data['upload_error'] = $upload_file;
                $return_data['msg']   = esc_html__('Invalid file extension. Please select valid file', 'bookingpress-appointment-booking');
            } else {
                $upload_file = $bookingpress_fileupload_obj->bookingpress_process_upload($bookingpress_destination);
                if ($upload_file == false ) {
                    $return_data['error'] = 1;
                    $return_data['msg']   = ! empty($upload_file->error_message) ? $upload_file->error_message : esc_html__('Something went wrong while updating the file', 'bookingpress-appointment-booking');
                } else {
                    $return_data['error']            = 0;
                    $return_data['msg']              = '';
                    $return_data['upload_url']       = $upload_url . $file_name;
                    $return_data['upload_file_name'] = $file_name;
                }
            }

            echo wp_json_encode($return_data);
            exit();
        }

        function bookingpress_settings_dynamic_data_fields_vars_func()
        {
            ?>
            <?php
        }

        
        /**
         * Settings module dynamic computed methods
         *
         * @return void
         */
        function bookingpress_settings_dynamic_computed_methods_func()
        {
            ?>
                dates() {
                      return this.days.map(day => (
                          {
                              selected_date: day.date,
                              selected_end_date: day.end_date,
                              selected_class: day.class
                          }
                      ));
                },
                attributes() {
                    return this.dates.map(date => (
                        {
                            highlight: {
                                class: date.selected_class
                            },
                            dates: date.selected_date,
                        }    
                    ));
                },                
                attributes_range() {
                    const vm = this;                    
                    var attributes_data = [];
                    attributes_data = this.dates.map(date => ({                            
                            highlight: {                                                                
                                class: date.selected_class
                            },
                            dates: { start: date.selected_date, end: date.selected_end_date },
                        } 
                    ));
                    if(vm.holiday_range_temp.start != ""){
                        var end_date = vm.holiday_range_temp.start;
                        if(vm.holiday_range_possible_end_date != ""){
                            end_date = vm.holiday_range_possible_end_date;
                        }
                        attributes_data.push({
                            highlight: true,                            
                            dates: { start: vm.holiday_range_temp.start, end: end_date },
                        }); 
                    }
                    return attributes_data; 

                }
            <?php
        }

        
        /**
         * Settings module onload methods
         *
         * @return void
         */
        function bookingpress_settings_dynamic_on_load_methods_func()
        {
            global $bookingpress_notification_duration,$BookingPress; ?>
            const vm = this                  
            var selected_tab_name = sessionStorage.getItem("current_tabname");   
            if(selected_tab_name != null) {
                vm.selected_tab_name = selected_tab_name;    
            } else if(selected_tab_name == null) {
                selected_tab_name = vm.selected_tab_name;
            }

            if(selected_tab_name == "general_settings"){
                vm.getSettingsData('general_setting', 'general_setting_form');
                <?php if(!$BookingPress->bpa_is_pro_active()){ ?>
                vm.getSettingsData('customer_setting','customer_setting_form');
                <?php } ?>
            }else if(selected_tab_name == "company_settings"){
                vm.getSettingsData('company_setting','company_setting_form')
            }else if(selected_tab_name == "notification_settings"){
                vm.getSettingsData('notification_setting','notification_setting_form')
            }else if(selected_tab_name == "workhours_settings"){
                var postdata = [];
                postdata.action = 'bookingpress_get_default_work_hours_details';
                postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                .then(function(response){
                    vm.is_disabled = false
                    vm.is_display_loader = '0'
                    vm.work_hours_days_arr = response.data.data
                    response.data.data.forEach(function(currentValue, index, arr){
                        vm.selected_break_timings[currentValue.day_name] = currentValue.break_times
                    });
                    vm.workhours_timings = response.data.selected_workhours
                    vm.default_break_timings = response.data.default_break_times
                    <?php do_action( 'bookingpress_after_fetch_default_work_hours' ); ?>
                }).catch(function(error){
                    console.log(error);
                    vm.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            }else if(selected_tab_name == "dayoff_settings"){                
            
                
                this.loadAttributes();
                this.handleWrapperEvent();
		<?php if($BookingPress->bpa_is_pro_active()){ ?>
                    vm.getSpecialdays();
                <?php } ?>
            }else if(selected_tab_name == "payment_settings"){
                vm.getSettingsData('payment_setting', 'payment_setting_form')
                vm.bookingpress_check_currency_status('');                
            }else if(selected_tab_name == "message_settings"){
                vm.getSettingsData('message_setting', 'message_setting_form')                
            }else if(selected_tab_name == 'debug_log_settings'){
                vm.getSettingsData('debug_log_setting', 'debug_log_setting_form')
            }
            <?php
            do_action('bookingpress_settings_add_dynamic_on_load_method');  ?>
            else {
                vm.selected_tab_name = 'general_settings';
                vm.getSettingsData('general_setting', 'general_setting_form');
            } 
            <?php
        }
        
        /**
         * Settings module methods/functions
         *
         * @return void
         */
        function bookingpress_setting_dynamic_vue_methods_func()
        {
            global $bookingpress_notification_duration,$BookingPress;
            ?>
            handleSizeChange(val) {                
                const vm = this
                var log_type = vm.open_view_model_gateway_name
                this.perPage = val
                this.bookingpess_view_log(log_type)
            <?php
            do_action('bookingpress_dynamic_add_method_for_pagination_size_change');
            ?>
            },        
            handleCurrentChange(val) {
                const vm = this
                var log_type = vm.open_view_model_gateway_name
                this.currentPage = val;                
                this.bookingpess_view_log(log_type, 'pagination');
            <?php
            do_action('bookingpress_dynamic_add_method_for_pagination_length_change');
            ?>
            },
            handleWrapperEvent(){
                document.addEventListener( 'click', function(e){            

                    if( e.target == null || !e.target.classList.contains('el-dialog__wrapper') ){
                        return false;
                    }
                    let all_highlighted_el = document.querySelectorAll('.vc-highlights.vc-day-layer');

                    if( all_highlighted_el.length > 0 ){
                        for( let i = 0; i < all_highlighted_el.length; i++ ){
                            let current_el = all_highlighted_el[i];
                            if( current_el.querySelector('.bpa_selected_daysoff') != null ){
                                continue;
                            }
                            current_el.parentNode.removeChild( current_el );
                        }
                    }
                });
            },
            loadAttributes() {
                const vm = this
                var loadAttrsData = []
                loadAttrsData.action = 'bookingpress_get_daysoff_details'
                loadAttrsData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                loadAttrsData.selected_year = vm.daysoff_selected_year
                axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(loadAttrsData))
                .then(function(response){
                    if(response.data.variant == 'error'){
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    }else{
                        vm.days = response.data.daysoff_data
                    }
                    vm.holiday_range_temp.start = '';
                    vm.holiday_range_temp.end = '';                    
                }).catch(function(error){
                    console.log(error)
                });
            },
            bookingpress_daysoff_selected_year(selectedValue){
                const vm = this
                if(selectedValue != undefined) {
                    var bookingpress_selected_date_obj = new Date(selectedValue)
                    var bookingpress_selected_year = bookingpress_selected_date_obj.getFullYear();
                    vm.daysoff_selected_year = bookingpress_selected_year
                    this.loadCalendarDates(bookingpress_selected_year)
                    this.loadAttributes()
                }
            },
            delete_dayoff(){
                const vm = this
                var deleteAttrData = [];               
                deleteAttrData.action = 'bookingpress_delete_daysoff_details'
                deleteAttrData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                deleteAttrData.days_off_form = vm.days_off_form
                axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(deleteAttrData))
                .then(function(response){
                    if(response.data.variant == 'error'){
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    } 
                    if(vm.days_off_form.is_edit == '1' && response.data.variant == 'success'){
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    }
                    vm.open_add_daysoff_details = false;
                    vm.loadAttributes();                     
                }).catch(function(error){
                    console.log(error)
                });    
            },
            onDragHolidayCalendar(day){
                const vm = this;
                var selected_date = day.id;                
                if(vm.holiday_range_temp.start != '' && vm.holiday_range_temp.end == ''){
                    vm.holiday_range_possible_end_date = selected_date;
                }else{
                    vm.holiday_range_possible_end_date = '';
                }                
            },
            onDayClickRange(day){                
                const vm = this;                
                var selected_date = day.id;
                var is_edit = 0;
                var edit_start_date = "";
                var edit_end_date = "";
                var edit_dayoff_id = 0;
                vm.days_off_form.is_repeat_days_off = false;
                vm.days.forEach(function(item, index, arr){
                    if(item.id == selected_date){
                        is_edit = 1;
                        vm.days_off_form.daysoff_title = item.off_name; 
                        edit_start_date = item.date; 
                        edit_end_date   = item.end_date;
                        edit_dayoff_id  = item.dayoff_id;                                                 
                        if(item.class == 'bpa-daysoff-calendar-col--item__highlight--yearly bpa_selected_daysoff'){
                            vm.days_off_form.is_repeat_days_off = true
                        }
                        <?php do_action( 'bookingpress_daysoff_external_js_data' ); ?>
                    }else{
                        if(item.date != item.end_date){
                            var start_date = new Date(item.date);
                            var end_date = new Date(item.end_date);                            
                            var compare_date = new Date(selected_date);
                            if(compare_date >= start_date && compare_date <= end_date){
                                is_edit = 1;                               
                                vm.days_off_form.daysoff_title = item.off_name; 
                                edit_start_date = item.date; 
                                edit_end_date   = item.end_date;
                                edit_dayoff_id  = item.dayoff_id;                      
                                if(item.class == 'bpa-daysoff-calendar-col--item__highlight--yearly bpa_selected_daysoff'){
                                    vm.days_off_form.is_repeat_days_off = true;
                                }                                
                            }                            
                        } 
                        if(vm.holiday_range_temp.start != '' && vm.holiday_range_temp.end != ''){

                        }
                    }
                });  

                if(is_edit == 0){
                    if(vm.holiday_range_temp.start == ''){
                        vm.holiday_range_temp.start = selected_date;
                        
                    }else{

                        var start_date_temp = new Date(vm.holiday_range_temp.start);
                        var end_date_temp = new Date(day.id);                           
                        vm.holiday_range_temp.end = selected_date;  
                        
                        var has_applied_range = false;
                        var start_date_month = "" + (start_date_temp.getMonth() + 1);
                        var end_date_month = "" + (end_date_temp.getMonth() + 1);
                                                                                              
                        vm.days_off_form.selected_date = selected_date;
                        vm.days_off_form.selected_end_date = edit_end_date;
                        vm.days_off_form.daysoff_title = '';
                        vm.days_off_form.is_repeat_days_off = false;                        
                        vm.days_off_form.is_edit = is_edit;  
                        vm.days_off_form.dayoff_id = edit_dayoff_id;              
                        vm.open_add_daysoff_details = true;                
                        var dialog_pos_x = day.el.getBoundingClientRect().left - 253;
                        var dialog_pos_y = day.el.getBoundingClientRect().top + 40;                    
                        <?php if( is_rtl() ){ ?>
                            var dialog_pos_x = day.el.getBoundingClientRect().left - 38;
                        <?php } ?>                    
                        vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.position = 'absolute';
                        vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.marginTop = '0px';
                        vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.top = dialog_pos_y + 'px';
                        vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.left = dialog_pos_x + 'px';
                                                
                        if(is_edit != 1){
                            const idx = vm.days.findIndex(d => d.id === day.id);
                            if (idx >= 0) {
                                this.days.splice(idx, 1);
                            } else {
                                if(start_date_temp > end_date_temp){
                                    this.days.push({
                                        id: day.id,
                                        date: vm.holiday_range_temp.end,
                                        end_date:vm.holiday_range_temp.start,
                                        class: 'bpa-daysoff-calendar-col--item__highlight--single-dayoff'
                                    });
                                    vm.days_off_form.selected_date = vm.holiday_range_temp.end;
                                    vm.days_off_form.selected_end_date = vm.holiday_range_temp.start;                                    
                                }else{
                                    this.days.push({
                                        id: day.id,
                                        date: vm.holiday_range_temp.start,
                                        end_date:vm.holiday_range_temp.end,
                                        class: 'bpa-daysoff-calendar-col--item__highlight--single-dayoff'
                                    });    
                                    vm.days_off_form.selected_date = vm.holiday_range_temp.start;
                                    vm.days_off_form.selected_end_date = vm.holiday_range_temp.end;                                                                     
                                }
                            }
                        }  
                        
                        
                    }
                }else{

                    if(vm.holiday_range_temp.start == ""){

                        vm.days_off_form.selected_date = edit_start_date;
                        vm.days_off_form.selected_end_date = edit_end_date;                                           
                        vm.days_off_form.is_edit = is_edit;
                        vm.days_off_form.dayoff_id = edit_dayoff_id;                                        
                        vm.open_add_daysoff_details = true                
                        var dialog_pos_x = day.el.getBoundingClientRect().left - 253;
                        var dialog_pos_y = day.el.getBoundingClientRect().top + 40;                    
                        <?php if( is_rtl() ){ ?>
                            var dialog_pos_x = day.el.getBoundingClientRect().left - 38;
                        <?php } ?>                    
                        vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.position = 'absolute';
                        vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.marginTop = '0px';
                        vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.top = dialog_pos_y + 'px';
                        vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.left = dialog_pos_x + 'px';

                    }else{
                        vm.holiday_range_temp.start = "";
                        vm.holiday_range_temp.end = "";
                    } 
                }


            },        
            onDayClick(day) {
                const vm = this
                var is_edit = 0;
                var selected_date = day.id;
                var edit_dayoff_id = 0;
                vm.days_off_form.selected_date = selected_date;
                vm.days_off_form.selected_end_date = selected_date; 
                vm.days_off_form.daysoff_title = '';
                vm.days_off_form.is_repeat_days_off = false;
                vm.days.forEach(function(item, index, arr){
                    if(item.id == selected_date){
                        is_edit = 1
                        vm.days_off_form.daysoff_title = item.off_name;
                        edit_dayoff_id  = item.dayoff_id; 
                        if(item.class == 'bpa-daysoff-calendar-col--item__highlight--yearly bpa_selected_daysoff'){
                            vm.days_off_form.is_repeat_days_off = true
                        }
                    }
                });

                vm.days_off_form.dayoff_id = edit_dayoff_id; 
                vm.days_off_form.is_edit = is_edit                
                vm.open_add_daysoff_details = true                
                var dialog_pos_x = day.el.getBoundingClientRect().left - 253;
                var dialog_pos_y = day.el.getBoundingClientRect().top + 40;
                
                <?php if( is_rtl() ){ ?>
                    var dialog_pos_x = day.el.getBoundingClientRect().left - 38;
                <?php } ?>
                
                vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.position = 'absolute';
                vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.marginTop = '0px';
                vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.top = dialog_pos_y + 'px';
                vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.left = dialog_pos_x + 'px';
                
                
                if(is_edit != 1){
                    const idx = vm.days.findIndex(d => d.id === day.id);
                    if (idx >= 0) {
                        this.days.splice(idx, 1);
                    } else {
                        this.days.push({
                            id: day.id,
                            date: day.date,
                            class: 'bpa-daysoff-calendar-col--item__highlight--single-dayoff'
                        });
                    }
                }
            },
            save_daysoff_details(form_name){
                const vm = this;
                vm.holiday_range_temp.start = "";
                vm.holiday_range_temp.end = "";
                vm.$refs[form_name].validate((valid) => {
                    if(valid) {
                        var is_exit = 0;
                        <?php
                        do_action('bookingpress_general_daysoff_validation');
                        ?>
                        if(is_exit == 0) {
                            var saveFormData = []                                
                            vm.is_disabled = true
                            vm.is_display_save_loader = '1'
                            saveFormData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                            saveFormData.action = 'bookingpress_save_default_daysoff_details'
                            saveFormData.daysoff_details = vm.days_off_form 
                            axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(saveFormData))
                            .then(function(response){                                
                                vm.is_disabled = false
                                vm.is_display_save_loader = '0'
                                vm.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: response.data.variant,
                                    customClass: response.data.variant+'_notification',
                                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                });
                                vm.open_add_daysoff_details = false
                                <?php do_action( 'bookingpress_reset_daysoff_advanced_settings' ); ?>
                                vm.loadAttributes()
                            }).catch(function(error){
                                console.log(error)
                            });
                        }
                    }    
                })
            },             
            saveEmployeeWorkhours() {
                event.preventDefault();
                const vm = new Vue()
                const vm2 = this
                vm2.is_disabled = true
                vm2.is_display_save_loader = '1'
                var postdata = []
                postdata.workhours_timings = vm2.workhours_timings;
                postdata.action = 'bookingpress_save_default_work_hours';
                postdata.break_data = vm2.selected_break_timings;
                postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';                
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                .then(function(response){
                    vm2.is_disabled = false
                    vm2.is_display_save_loader = '0'
                    vm2.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    vm2.reset_edit_break_form()
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
            },
            reset_edit_break_form(){
                const vm = this
                vm.break_timings.start_time = ''
                vm.break_timings.end_time = ''
                vm.break_timings.edit_index = ''
                vm.is_edit_break = 0
            },
            toggleBusy() {
                this.modal_loading = !this.modal_loading
            },
            close_modal(modal_name){
                this.modals[modal_name+'_modal'] = false
            },
            savegeneralSettingsData(){
                const vm = this                
                var response_variant = vm.saveSettingsData('customer_setting_form','customer_setting', true)    
                if(response_variant != 'error') {
                    vm.saveSettingsData('general_setting_form','general_setting', false)                
                }
            },
            saveSettingsData(form_name,setting_type, display_save_msg = true){
                const vm = this
                if( form_name == "general_setting_form"){
                    if( "undefined" != typeof vm.general_setting_form.default_date_format){
                        vm.bpa_date_common_date_format = vm.general_setting_form.default_date_format;
                        let bpa_common_date_time_format = vm.general_setting_form.default_date_format+' HH:mm';
                        vm.bpa_date_time_common_date_format = bpa_common_date_time_format;
                    }
                }
                if(form_name == "customer_setting_form"){
                    vm.is_disabled = true
                    vm.is_display_save_loader = '1'
                    let saveFormData = vm[form_name]
                    saveFormData.action = 'bookingpress_save_settings_data'
                    saveFormData.settingType = setting_type
                    saveFormData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    <?php do_action('bookingpress_add_settings_more_postdata'); ?>
                    let response_variant= '';
                    axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(saveFormData))
                    .then(function(response){
                        vm.is_disabled = false
                        vm.is_display_save_loader = '0'                        
                        response_variant = response.data.variant;
                        if( true == display_save_msg || "error" == response.data.variant ){
                            vm.$notify({                        
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        }
                        vm.isloading = false;
                        vm.toggleBusy()
                        <?php do_action( 'bookingpress_settings_response' ); ?>
                    }).catch(function(error){
                        console.log(error)
                    });
                    return response_variant;
                }else{                            
                    vm.$refs[form_name].validate((valid) => {                        
                        if(valid) {
                            vm.is_disabled = true
                            vm.is_display_save_loader = '1'
                            let saveFormData = vm[form_name]
                            saveFormData.action = 'bookingpress_save_settings_data'
                            saveFormData.settingType = setting_type
                            saveFormData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                            <?php do_action('bookingpress_add_settings_more_postdata'); ?>
                            axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(saveFormData))
                            .then(function(response){
                                vm.is_disabled = false
                                vm.is_display_save_loader = '0'
                                if( true == display_save_msg || "error" == response.data.variant ){

                                    vm.$notify({                        
                                        title: response.data.title,
                                        message: response.data.msg,
                                        type: response.data.variant,
                                        customClass: response.data.variant+'_notification',
                                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                    });
                                }
                                vm.isloading = false;
                                vm.toggleBusy()
                                <?php do_action( 'bookingpress_settings_response' ); ?>
                            }).catch(function(error){
                                console.log(error)
                            });
                        }
                    })
                }
            },
            getSettingsData(settingType, form_name){
                const vm = this
                let getSettingsDetails = {
                    'action': 'bookingpress_get_settings_details',
                    'setting_type': settingType,
                    '_wpnonce': '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>',
                }
                axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(getSettingsDetails))
                .then(function(response){
                    vm.is_disabled = false
                    vm.is_display_loader = '0'
                    if(response.data.data != '' || response.data.data != []){
                        vm[form_name] = response.data.data
                        if(settingType == 'company_setting'){
                            vm.$refs.avatarRef.clearFiles()
                            if(response.data.data.company_phone_country != 'undefined' || response.data.data.company_phone_country != undefined){
                                vm.bookingpress_tel_input_props.defaultCountry = response.data.data.company_phone_country
                                vm.$refs.bpa_tel_input_field._data.activeCountryCode = response.data.data.company_phone_country;
                                vm.company_setting_form.company_phone_country = response.data.data.company_phone_country
                            }
                            if(response.data.data.company_avatar_url != undefined && response.data.data.company_avatar_url != ''){
                                vm.company_setting_form.company_avatar_url = response.data.data.company_avatar_url
                                vm.company_setting_form.company_avatar_img = response.data.data.company_avatar_img
                            }    
                        }
                        if(settingType == 'general_setting')
                        {
                            if(response.data.data.default_phone_country_code != 'undefined' || response.data.data.default_phone_country_code != undefined)
                            {
                                vm.bookingpress_tel_input_settings_props.defaultCountry = response.data.data.default_phone_country_code
                                vm.$refs.bpa_tel_input_settings_field._data.activeCountryCode = response.data.data.default_phone_country_code;
                                vm.general_setting_form.default_phone_country_code = response.data.data.default_phone_country_code;
                            }
                        }
			
			<?php do_action('bookingpress_get_settings_details_response'); ?>
                    }
                }).catch(function(error){
                    console.log(error)
                });
            },
            bookingpress_upload_company_avatar_func(response, file, fileList){
                const vm2 = this
                if(response != ''){
                    vm2.company_setting_form.company_avatar_url = response.upload_url
                    vm2.company_setting_form.company_avatar_img = response.upload_file_name
                }
            },
            bookingpress_company_avatar_upload_limit(files, fileList){
                const vm2 = this
                if(files.length >= 1){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Multiple files not allowed', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                }
            },
            checkUploadedFile(file){
                const vm2 = this
                if(file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp'){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please upload jpg/png file only', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    return false
                }else{
                    var bpa_image_size = parseInt(file.size / 1000000);
                    if(bpa_image_size > 1){
                        vm2.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Please upload maximum 1 MB file only', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });                    
                        return false
                    }
                }
            },
            bookingpress_company_avatar_upload_err(err, file, fileList){
                const vm2 = this
                var bookingpress_err_msg = '<?php esc_html_e('Something went wrong', 'bookingpress-appointment-booking'); ?>';
                if(err != '' || err != undefined){
                    bookingpress_err_msg = err
                }
                vm2.$notify({
                    title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                    message: bookingpress_err_msg,
                    type: 'error',
                    customClass: 'error_notification',
                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                });
            },
            bookingpress_remove_company_avatar(){
                const vm = this
                var upload_url = vm.company_setting_form.company_avatar_url                     
                var upload_filename = vm.company_setting_form.company_avatar_img 

                var postData = { action:'bookingpress_remove_company_avatar',upload_file_url: upload_url,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {                    
                    vm.company_setting_form.company_avatar_url = ''
                    vm.company_setting_form.company_avatar_img = ''
                    vm.$refs.avatarRef.clearFiles()
                }.bind(vm) )
                .catch( function (error) {
                    console.log(error);
                });
            },
            settings_tab_select(selected_tab){                
                const vm = this
                sessionStorage.setItem("selected_tab", selected_tab.index)                
                vm.open_add_break_modal = false;
                var current_tabname = selected_tab.$el.dataset.tab_name;
                sessionStorage.setItem("current_tabname", current_tabname)

                vm.bpa_set_read_more_link();

                if(current_tabname == "general_settings"){
                    vm.getSettingsData('general_setting', 'general_setting_form')
                    <?php if(!$BookingPress->bpa_is_pro_active()){ ?>
                    vm.getSettingsData('customer_setting', 'customer_setting_form')
                    <?php } ?>
                } else if (current_tabname == "company_settings") {
                    vm.getSettingsData('company_setting','company_setting_form')
                } else if (current_tabname == "labels_settings") {
                    vm.getSettingsData('label_setting', 'label_setting_form')    
                } else if (current_tabname == "notification_settings") {
                    vm.getSettingsData('notification_setting','notification_setting_form')
                } else if (current_tabname == "payment_settings") {
                    vm.getSettingsData('payment_setting', 'payment_setting_form')                    
                    vm.bookingpress_check_currency_status('');                    
                }else if (current_tabname == "debug_log_settings") {
                    vm.getSettingsData('debug_log_setting', 'debug_log_setting_form')
                } else if (current_tabname == "message_settings") {
                    vm.getSettingsData('message_setting', 'message_setting_form')
                } else if (current_tabname == "workhours_settings") {
                    //vm.is_disabled = true
                    //vm.is_display_loader = '1'
                    //Get already added default workhours
                    var postdata = [];
                    postdata.action = 'bookingpress_get_default_work_hours_details';
                    postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                    .then(function(response){
                        vm.is_disabled = false
                        vm.is_display_loader = '0'
                        vm.work_hours_days_arr = response.data.data
                        response.data.data.forEach(function(currentValue, index, arr){
                            vm.selected_break_timings[currentValue.day_name] = currentValue.break_times
                        });
                        vm.workhours_timings = response.data.selected_workhours
                        vm.default_break_timings = response.data.default_break_times
                        <?php do_action( 'bookingpress_after_fetch_default_work_hours' ); ?>
                    }).catch(function(error){
                        console.log(error);
                        vm.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    });
                }else if( current_tabname == "dayoff_settings" ){
                    this.loadAttributes();                    
                    this.handleWrapperEvent();
                    <?php if($BookingPress->bpa_is_pro_active()){ ?>
                        vm.getSpecialdays();
                    <?php } ?>
                }
            <?php
            do_action('bookingpress_dynamic_get_settings_data');
            ?>
                else{
                    vm.selected_tab_name = 'general_settings'
                    vm.getSettingsData('general_setting', 'general_setting_form')
                }                
            },
            open_add_break_modal_func(currentElement, breakSelectedDay){
                const vm = this;
                var dialog_pos = currentElement.target.getBoundingClientRect();
                vm.break_modal_pos = (dialog_pos.top + 40)+'px'
                vm.break_modal_pos_right = (dialog_pos.right + 38)+'px';
                vm.open_add_break_modal = true          
                vm.reset_edit_break_form()
                vm.break_selected_day = breakSelectedDay

                vm.bpa_adjust_popup_position( currentElement, 'div#breaks_add_modal .el-dialog.bpa-dialog--add-break' );
            },
            savebreakdata(){
                const vm = this
                var is_edit = 0;               
                vm.$refs['break_timings'].validate((valid) => {                        
                    if(valid) {    
                        var update = 0;             
                        if(vm.break_timings.start_time > vm.break_timings.end_time) {
                            vm.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php echo addslashes( esc_html__('Start time is not greater than End time', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        }else if(vm.break_timings.start_time == vm.break_timings.end_time) {                    
                            vm.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php echo addslashes( esc_html__('Start time & End time are not same', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        } else if(vm.selected_break_timings[vm.break_selected_day] != '' ) {
                            vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
                                if(is_edit == 0) {
                                    if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {    
                                        is_edit = 1;
                                        vm.$notify({
                                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                            message: '<?php echo addslashes( esc_html__('Please enter valid time for break', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                            type: 'error',
                                            customClass: 'error_notification',
                                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                        });                
                                    } else if(currentValue['start_time'] == vm.break_timings.start_time && currentValue['end_time'] == 
                                        vm.break_timings.end_time && ( vm.break_timings.edit_index != index || vm.is_edit_break == 0 )) {                                        
                                        is_edit = 1;
                                        vm.$notify({
                                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                            message: '<?php echo addslashes( esc_html__('Break time already added', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                            type: 'error',
                                            customClass: 'error_notification',
                                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                        });
                                    }else if(((currentValue['start_time'] < vm.break_timings.start_time  && currentValue['end_time'] > vm.break_timings.start_time) || (currentValue['start_time'] < vm.break_timings.end_time  && currentValue['end_time'] > vm.break_timings.end_time) || (currentValue['start_time'] > vm.break_timings.start_time && currentValue['end_time'] <= vm.break_timings.end_time) || (currentValue['start_time'] >= vm.break_timings.start_time && currentValue['end_time'] < vm.break_timings.end_time)) && (vm.break_timings.edit_index != index || vm.is_edit_break == 0) )  {                                       
                                        is_edit = 1;
                                        vm.$notify({
                                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                            message: '<?php echo addslashes( esc_html__('Break time already added', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                            type: 'error',
                                            customClass: 'error_notification',
                                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                        });                
                                    }                                                
                                }    
                            });
                            if(is_edit == 0) {
                                var formatted_start_time = formatted_end_time = start_time_string = end_time_string = '';
                                vm.default_break_timings.forEach(function(currentValue, index, arr) {
                                    if(currentValue.start_time_val == vm.break_timings.start_time) {
                                        formatted_start_time = currentValue.formatted_start_time;
                                        start_time_string = currentValue.formatted_start_time;
                                    }
                                    if(currentValue.end_time_val == vm.break_timings.end_time) {
                                        formatted_end_time = currentValue.formatted_end_time;
                                        end_time_string = currentValue.formatted_end_time;
                                    }
                                });
                                if(vm.break_selected_day != '' && vm.is_edit_break != 0) {
                                    vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
                                        if(index == vm.break_timings.edit_index) {
                                            currentValue.start_time = vm.break_timings.start_time;
                                            currentValue.end_time = vm.break_timings.end_time;
                                            currentValue.formatted_start_time = formatted_start_time;
                                            currentValue.formatted_end_time = formatted_end_time;
                                        }
                                    });   
                                }else {
                                    vm.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time,formatted_start_time:formatted_start_time,formatted_end_time:formatted_end_time, start_time_string: start_time_string, end_time_string: end_time_string });                                    
                                }
                                vm.close_add_break_model()
                            } 
                        }  else {
                            if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {
                                vm.$notify({
                                    title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                    message: '<?php echo addslashes( esc_html__('Please enter valid time for break', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                    type: 'error',
                                    customClass: 'error_notification',
                                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                });                
                            }else{
                                var formatted_start_time = formatted_end_time = start_time_string = end_time_string = '';
                                vm.default_break_timings.forEach(function(currentValue, index, arr) {
                                    if(currentValue.start_time_val == vm.break_timings.start_time) {
                                        formatted_start_time = currentValue.formatted_start_time;
                                        start_time_string = currentValue.formatted_start_time;                                        
                                    }
                                    if(currentValue.end_time_val == vm.break_timings.end_time) {
                                        formatted_end_time = currentValue.formatted_end_time;
                                        end_time_string = currentValue.formatted_end_time;
                                    }
                                });
                                vm.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time,formatted_start_time:formatted_start_time,formatted_end_time:formatted_end_time, start_time_string: start_time_string, end_time_string: end_time_string });
                                vm.close_add_break_model();
                            }
                        }
                    }
                })                   
            },
            delete_breakhour(start_time, end_time, selected_day){
                const vm = this
                vm.selected_break_timings[selected_day].forEach(function(currentValue, index, arr){
                    if(currentValue.start_time == start_time && currentValue.end_time == end_time)
                    {
                        vm.selected_break_timings[selected_day].splice(index, 1);
                    }
                });
            },
            close_add_break_model() {
                const vm = this
                vm.$refs['break_timings'].resetFields()
                vm.reset_edit_break_form()                
                vm.open_add_break_modal = false;
            },
            edit_workhour_data(currentElement,break_start_time, break_end_time, day_name,index){
                const vm = this                
                vm.reset_edit_break_form()
                var dialog_pos = currentElement.target.getBoundingClientRect();
                vm.break_modal_pos = (dialog_pos.top - 08)+'px'
                vm.break_modal_pos_right = (dialog_pos.right + 38)+'px';                
                vm.break_timings.start_time = break_start_time
                vm.break_timings.end_time = break_end_time
                vm.break_timings.edit_index = index
                vm.is_edit_break= 1;
                vm.open_add_break_modal = true
                vm.break_selected_day = day_name

                vm.bpa_adjust_popup_position( currentElement, 'div#breaks_add_modal .el-dialog.bpa-dialog--add-break', 'bpa-bh__item' );
            },
            bookingpress_send_test_wpmail_email(){
                const vm = this;
                vm.$refs['notification_wpmail_test_mail_form'].validate( (valid) =>{
                    if( valid ){
                        vm.is_disabled = true
                        vm.is_display_send_test_wpmail_mail_loader = '1'
                        vm.is_disable_send_test_wpmail_email_btn = true
                        var postdata = []
                        postdata.action = 'bookingpress_send_test_wpmail_email'
                        postdata.notification_formdata = vm.notification_setting_form
                        postdata.notification_test_mail_formdata = vm.notification_wpmail_test_mail_form
                        postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                        .then(function(response){
                            vm.is_disabled = false
                            vm.is_display_send_test_wpmail_mail_loader = '0'    
                            vm.is_disable_send_test_wpmail_email_btn = false
                            
                            if(response.data.is_mail_sent == 1){
                                vm.succesfully_send_test_wpmail_email = 1
                                vm.error_send_test_wpmail_email = 0;
                                vm.wpmail_mail_error_text = '';
                                vm.error_text_of_test_wpmail_email = '';
                            }else{
                                vm.succesfully_send_test_wpmail_email = 0                                
                                vm.error_send_test_wpmail_email = 1
                                vm.error_text_of_test_wpmail_email = response.data.error_msg
                                vm.wpmail_mail_error_text = response.data.error_log_msg
                            }
                        }).catch(function(error){
                            console.log(error);
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php echo addslashes( esc_html__('Something went wrong..', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        });
                    }
                });
            },
            bookingpress_send_test_gmail_email(){
                const vm = this
                vm.$refs['notification_gmail_test_mail_form'].validate((valid) => {                        
                    if(valid) {
                        vm.is_disabled = true
                        vm.is_display_send_test_gmail_mail_loader = '1'
                        vm.is_disable_send_test_gmail_email_btn = true
                        var postdata = []
                        postdata.action = 'bookingpress_send_test_gmail_email'
                        postdata.notification_formdata = vm.notification_setting_form
                        postdata.notification_test_mail_formdata = vm.notification_gmail_test_mail_form
                        postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                        .then(function(response){
                            vm.is_disabled = false
                            vm.is_display_send_test_gmail_mail_loader = '0'    
                            vm.is_disable_send_test_gmail_email_btn = false
                            if(response.data.is_mail_sent == 1){
                                vm.succesfully_send_test_gmail_email = 1
                                vm.error_send_test_gmail_email = 0;
                                vm.gmail_mail_error_text = '';
                                vm.error_text_of_test_gmail_email = '';
                            }else{
                                vm.succesfully_send_test_gmail_email = 0                                
                                vm.error_send_test_gmail_email = 1
                                vm.error_text_of_test_gmail_email = response.data.error_msg
                                vm.gmail_mail_error_text = response.data.error_log_msg
                            }
                        }).catch(function(error){
                            console.log(error);
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php echo addslashes( esc_html__('Something went wrong..', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        });
                    }
                })    
            },
            bookingpress_send_test_email(){
                const vm = this
                vm.$refs['notification_smtp_test_mail_form'].validate((valid) => {                        
                    if(valid) {
                        vm.is_disabled = true
                        vm.is_display_send_test_mail_loader = '1'
                        vm.is_disable_send_test_email_btn = true
                        var postdata = []
                        postdata.action = 'bookingpress_send_test_email'
                        postdata.notification_formdata = vm.notification_setting_form
                        postdata.notification_test_mail_formdata = vm.notification_smtp_test_mail_form
                        postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                        .then(function(response){
                            vm.is_disabled = false
                            vm.is_display_send_test_mail_loader = '0'    
                            vm.is_disable_send_test_email_btn = false
                            if(response.data.is_mail_sent == 1){
                                vm.succesfully_send_test_email = 1
                                vm.error_send_test_email = 0;
                                vm.smtp_mail_error_text = '';
                                vm.error_text_of_test_email = '';
                            }else{
                                vm.succesfully_send_test_email = 0                                
                                vm.error_send_test_email = 1
                                vm.error_text_of_test_email = response.data.error_msg
                                vm.smtp_mail_error_text = response.data.error_log_msg
                            }
                        }).catch(function(error){
                            console.log(error);
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php echo addslashes( esc_html__('Something went wrong..', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        });
                    }
                })    
            },
            bookingpress_trim_value(input_value){
                input_value = input_value.trim()
                this.days_off_form['daysoff_title'] = input_value
            },
            bookingpess_view_log(log_type, request_from='', log_name='') {                                
                const vm = this
                vm.open_display_log_modal  = true                
                var postdata = []
                vm.is_display_loader_view = '1'
                if( request_from != 'pagination') {                        
                    vm.items = '';
                }

                vm.open_view_model_gateway_name = log_type;
                if(log_name != ''){
                    vm.open_view_model_gateway = log_name;
                }
                
                postdata.action = 'bookingpress_view_debug_payment_log';
                postdata.bookingpress_debug_log_selector = log_type;
                postdata.perpage=this.perPage,
                postdata.currentpage=this.currentPage,
                postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                .then(function(response){
                    vm.is_display_loader_view = '0',
                    vm.items = response.data.items;
                    vm.totalItems = response.data.total;                                                            
                }).catch(function(error){
                    console.log(error);
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php echo addslashes( esc_html__('Something went wrong..', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            bookingpess_clear_bebug_log(log_type){
                const vm = this
                vm.is_display_loader = '1'
                var postdata = []
                postdata.action = 'bookingpress_clear_debug_payment_log';
                postdata.bookingpress_debug_log_selector = log_type;
                postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                .then(function(response) {                    
                    vm.is_display_loader = '0'
                    vm.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });                    
                }).catch(function(error){
                    vm.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php echo addslashes( esc_html__('Something went wrong..', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            bookingpress_download_log(log_type,selected_download_duration,download_log_daterange) {
                const vm = this
                vm.is_display_download_save_loader = '1';
                vm.is_disabled= true;
                var postdata = []
                postdata.action = 'bookingpress_download_payment_log';
                postdata.bookingpress_debug_log_selector = log_type;
                postdata.bookingpress_selected_download_duration = selected_download_duration;
                if(selected_download_duration == 'custom') {                    
                    postdata.bookingpress_selected_download_custom_duration = download_log_daterange;
                }
                postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                .then(function(response) {                                
                       window.location.href = response.data.url;                                  
                    vm.is_display_download_save_loader = '0';                          
                    vm.is_disabled= false;
                }).catch(function(error){
                    console.log(error);
                    vm.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php echo addslashes( esc_html__('Something went wrong..', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });

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
            bookingpress_check_workhour_value(workhour_time,work_hour_day) {    
                if(workhour_time == 'Off') {
                    const vm = this
                    vm.workhours_timings[work_hour_day].start_time = 'Off';
                }
            },
            bookingpress_set_workhour_value(worktime,work_hour_day) {
                const vm = this                
                if(vm.workhours_timings[work_hour_day].end_time == 'Off') {                    
                    vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
                        if(currentValue.day_name == work_hour_day) {
                            currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
                                if(currentValue2.start_time == worktime) {
                                    vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
                                }
                            });
                        }
                    });                
                } else if(worktime >= vm.workhours_timings[work_hour_day].end_time ) {
                    vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
                        if(currentValue.day_name == work_hour_day) {                       
                            currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
                                if(currentValue2.start_time == worktime) {
                                    vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
                                }
                            });
                        }
                    });
                } else if(worktime != 'off' && vm.workhours_timings[work_hour_day].end_time == undefined) {
                    vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
                        if(currentValue.day_name == work_hour_day) {                       
                            currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
                                if(currentValue2.start_time == worktime) {
                                    vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
                                }
                            });
                        }
                    });
                }
            },            
            open_smtp_error_modal() {                
                const vm= this;
                vm.smtp_error_modal = true;
            },
            close_smtp_error_modal(){
                const vm= this;
                vm.smtp_error_modal = false;    
            },
            bookingpress_phone_country_change_func(bookingpress_country_obj){
                const vm = this
                var bookingpress_selected_country = bookingpress_country_obj.iso2
                let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_selected_country, true, 1 );
                if( '' != exampleNumber ){
                    vm.bookingpress_tel_input_props.inputOptions.placeholder = exampleNumber;
                }
                vm.company_setting_form.company_phone_country = bookingpress_selected_country
            },
            bookingpress_general_tab_phone_country_change_func(bookingpress_country_obj_gst)
            {
                const vm = this;
                var bookingpress_general_tab_selected_country = bookingpress_country_obj_gst.iso2
                
                let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_general_tab_selected_country, true, 1 );
                if( '' != exampleNumber ){
                    vm.bookingpress_tel_input_settings_props.inputOptions.placeholder = exampleNumber;
                }
                vm.general_setting_form.default_phone_country_code = bookingpress_general_tab_selected_country
            },
            bookingpress_check_currency_status(value){                
                const vm = this
                var postdata = []                
                postdata.action = 'bookingpress_check_currency_status';
                postdata.bookingpress_currency = value;            
                postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
                .then(function(response) {                        
                    if(response.data.msg != '') {
                        vm.bookingpress_currency_warnning = '1';
                        vm.bookingpress_currency_warnning_msg = response.data.msg;
                    } else {
                        vm.bookingpress_currency_warnning = '0';
                    }
                }).catch(function(error){
                    console.log(error);
                    vm.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });                   
            },
            bookingpress_disable_modal() {
                const vm = this;
                vm.holiday_range_temp.start = "";
                vm.holiday_range_temp.end = "";
                vm.loadAttributes();
                if(document.body.classList.contains("el-popup-parent--hidden")){
                    document.body.classList.remove("el-popup-parent--hidden");
                    document.body.style.paddingRight = "0px";
                }
                document.body.style.overflow = "auto";
            },
            bookingpress_gmail_insert_placeholder( event) {
                <?php $bookingpress_redirect_url_success_msg = esc_html__( 'Authorized redirect URI copied successfully', 'bookingpress-appointment-booking' ); ?>
                const vm = this
                var bookingpress_selected_placholder = event

                var bookingpress_dummy_elem = document.createElement("textarea");
                document.body.appendChild(bookingpress_dummy_elem);
                bookingpress_dummy_elem.value = bookingpress_selected_placholder;
                bookingpress_dummy_elem.select();
                document.execCommand("copy");
                document.body.removeChild(bookingpress_dummy_elem);

                vm.$notify({ title: '<?php esc_html_e( 'Success', 'bookingpress-appointment-booking' ); ?>', message: '<?php echo esc_html( $bookingpress_redirect_url_success_msg ); ?>', type: 'success', customClass: 'success_notification',duration:<?php echo intval($bookingpress_notification_duration); ?>,});
            },
            bookingpress_gmail_api_check(){
                const vm=this
                if( vm.notification_setting_form.gmail_client_secret != '' && vm.notification_setting_form.gmail_client_ID != '' ){

                    var bkp_gmail_id = vm.notification_setting_form.gmail_client_ID;
                    var bkp_gmail_secret = vm.notification_setting_form.gmail_client_secret;

                    <?php 
                        $bookingpress_gmailapi_redirect_uri = get_home_url().'?page=bookingpress_gmailapi'; 
                        $state = base64_encode( 'action:gmail_oauth' );
                    ?>

                    let url = 'https://accounts.google.com/o/oauth2/auth';

                    let oauth_url = url + '?response_type=code&access_type=offline&client_id='+bkp_gmail_id+'&redirect_uri=<?php echo urlencode( $bookingpress_gmailapi_redirect_uri); ?>&state=<?php echo $state; //phpcs:ignore ?>&scope=https://mail.google.com/&approval_prompt=force&include_granted_scopes=false';

                    window.open( oauth_url, 'BookingPress Gmail API Authentication', 'height=500, width=500');

                }
            },
            bookingpress_gmail_api_remove( auth_token, auth_email, auth_response ){
            
                const vm = this;

                if( auth_token == '' ){
                    return false;
                }
                
                let postData = {
                    action: "bookingpress_signout_google_account",
                    access_token: auth_token,
                    auth_email : auth_email,
                    access_token_data : auth_response,
                    _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    if( response.data.variant == "success" ){
                        vm.notification_setting_form.bookingpress_gmail_auth_token = '';
                        vm.notification_setting_form.bookingpress_response_email = '';
                        vm.notification_setting_form.bookingpress_gmail_auth = '';
                    }
                }.bind(this) )
				.catch( function (error) {
                    console.log( error );
                });
            },
            bookingpress_timesolts_afternoon_grouping( timing){
                const vm = this;
                vm.general_setting_form.bpa_evening_start_time = "";
                vm.general_setting_form.bpa_night_start_time = "";
            },  
            bookingpress_timesolts_evening_grouping(){
                const vm = this;
                vm.general_setting_form.bpa_night_start_time = "";
            },
            <?php
            do_action('bookingpress_add_setting_dynamic_vue_methods');
        }

        
        /**
         * Add more setting dynamic data variables
         *
         * @return void
         */
        function bookingpress_setting_dynamic_data_fields_func()
        {
            global $bookingpress_dynamic_setting_data_fields,$BookingPress,$bookingpress_global_options, $tbl_bookingpress_form_fields,$wpdb;
            $bookingpress_options  = $bookingpress_global_options->bookingpress_global_options();

            $bookingpress_default_perpage_option                               = $BookingPress->bookingpress_get_settings('per_page_item', 'general_setting');
            $bookingpress_dynamic_setting_data_fields['perPage']               = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '10';
            $bookingpress_dynamic_setting_data_fields['pagination_length_val'] = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '10';

            $selected_tab_name  = ! empty($_REQUEST['setting_page']) ? sanitize_text_field($_REQUEST['setting_page']) : 'general_settings';            
            $bookingpress_dynamic_setting_data_fields['selected_tab_name'] = $selected_tab_name;            
            
            $bookingpress_company_phone_country                                       = $BookingPress->bookingpress_get_settings('company_phone_country', 'company_setting');
            $bookingpress_dynamic_setting_data_fields['bookingpress_tel_input_props'] = array(
                'defaultCountry' => $bookingpress_company_phone_country,
                'validCharactersOnly' => true,
	            'inputOptions' => array(
                    'placeholder' => '',
                ),
		
            );
            $bookingpress_dynamic_setting_data_fields['vue_tel_mode'] = 'international';
            $bookingpress_dynamic_setting_data_fields['vue_tel_auto_format'] = true;

            $bookingpress_general_phone_country  = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
            $bookingpress_dynamic_setting_data_fields['bookingpress_tel_input_settings_props'] = array(
            'defaultCountry' => $bookingpress_general_phone_country,
            'inputOptions' => array(
                'placeholder' => '',
            ),
            'validCharactersOnly' => true,
            );
            
            $bookingpress_dynamic_setting_data_fields['bookingpress_alignment'] = is_rtl() ?'right':'left';

            $bookingpress_timezone_offset = $bookingpress_options['bookingpress_timezone_offset'];
            $wp_timezone_string = wp_timezone_string();
            $wp_timezone_string = $BookingPress->bookingpress_convert_timezone_to_offset( $wp_timezone_string );
            
     
            $bookingpress_dynamic_setting_data_fields['daysoff_timezone'] = "UTC";

            $bookingpress_dynamic_setting_data_fields['first_day_of_week'] = intval($bookingpress_options['start_of_week']) + 1;                        
            $bookingpress_form_fields = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name='email_address' ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False alarm

            $bookingpress_dynamic_setting_data_fields['bookingpress_custom_email_field_required'] = $bookingpress_form_fields[0]['bookingpress_field_required'];
            $bookingpress_dynamic_setting_data_fields['bookingpress_field_is_hide'] = $bookingpress_form_fields[0]['bookingpress_field_is_hide'];
                         
            $bookingpress_dynamic_setting_data_fields['holiday_range_possible_end_date'] = '';
            $bookingpress_dynamic_setting_data_fields['holiday_range_temp'] = array('start'=>'','end'=>'');

            $bookingpress_dynamic_setting_data_fields['bpa_display_wpmail_notice'] = !empty( get_option( 'bookingpress_display_wpmail_failed_msg_notice' ) ) ? get_option( 'bookingpress_display_wpmail_failed_msg_notice' ) : false;
            $bookingpress_dynamic_setting_data_fields['bpa_wpmail_failed_msg_data'] = !empty( get_option( 'bookingpress_wpmail_failed_msg_data') ) ? json_decode( get_option( 'bookingpress_wpmail_failed_msg_data'), true ) : [];

            /* grouping timesolt changes start */
            $default_start_time    = '00:00:00';
			$default_end_time      = '23:55:00';
			$step_duration_val     = 01;
			$default_break_timings = array();
			$curr_time             = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
			$tmp_end_time          = date( 'H:i:s', strtotime( $default_end_time ) );
			do {

				$tmp_start_time = $default_start_time;
				$tmp_time_obj = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'H' ) );
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

				$tmp_time_obj            = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'H' ) );
				$curr_time = $tmp_time_obj->format( 'H:i:s' );
			} while ( $curr_time <= $default_end_time );

			$bookingpress_dynamic_setting_data_fields['timeslots_grouping_list'] = $default_break_timings;

            /* grouping timesolt changes end */

            $bookingpress_dynamic_setting_data_fields = apply_filters('bookingpress_add_setting_dynamic_data_fields', $bookingpress_dynamic_setting_data_fields);
            
            echo wp_json_encode($bookingpress_dynamic_setting_data_fields);
        }
        
        /**
         * bookingpress_setting_dynamic_helper_vars_func
         *
         * @return void
         */
        function bookingpress_setting_dynamic_helper_vars_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];

            ?>
                var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
                ELEMENT.locale(lang)

            <?php
            do_action('bookingpress_dynamic_add_setting_helpers_vars');
        }
        
        /**
         * Load setting module view file
         *
         * @return void
         */
        function bookingpress_dynamic_load_setting_content_func()
        {
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/settings/manage_settings.php';
            $bookingpress_load_file_name = apply_filters('bookingpress_modify_settings_view_file_path', $bookingpress_load_file_name);

            include $bookingpress_load_file_name;
        }

        
        /**
         * Get settings of specific setting type
         *
         * @param  mixed $setting_type
         * @return void
         */
        public function bookingpress_get_settings_data_by_setting_type( $setting_type )
        {
            global $wpdb, $tbl_bookingpress_settings;

            if (! empty($setting_type) ) {
                $bookingpress_fetch_settings_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_settings}` WHERE setting_type = %s", $setting_type  ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally. False Positive alarm
                foreach ( $bookingpress_fetch_settings_details as $key => $value ) {
                    if ($setting_type == 'general_setting' ) {
                        if ($bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'use_already_loaded_vue' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
                               $bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
                        } elseif ($bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'phone_number_mandatory' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
                            $bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
                        } elseif ($bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'load_js_css_all_pages' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
                            $bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
                        } elseif ($bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'share_timeslot_between_services' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
							$bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
						} elseif ( $bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'show_time_as_per_service_duration' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
                            $bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
                        }
                    }
                    if ($setting_type == 'payment_setting' ) {
                        if ($bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'paypal_payment' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
                            $bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
                        } elseif ($bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'on_site_payment' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == 1 ) {
                            $bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'true';
                        }
                    }
                }
                if (! empty($bookingpress_fetch_settings_details) ) {
                    return $bookingpress_fetch_settings_details;
                }
            }

            return array();
        }
        
        /**
         * Ajax request for get setting details
         *
         * @return void
         */
        public function bookingpress_get_settings_details()
        {
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_settings', true, 'bpa_wp_nonce' );
            
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
            $response['msg']     = esc_html__('Something went wrong while fetching settings...', 'bookingpress-appointment-booking');
            $response['data']    = array();

            if (! empty($_POST['setting_type']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $setting_type               = sanitize_text_field($_POST['setting_type']); // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_settings_data = $this->bookingpress_get_settings_data_by_setting_type($setting_type);
                $bookingpress_setting_return_data = array();
                if (! empty($bookingpress_settings_data) ) {
                    foreach ( $bookingpress_settings_data as $bookingpress_setting_key => $bookingpress_setting_val ) {
                        $bookingpress_tmp_setting_val = $bookingpress_setting_val['setting_value'];
                        if ($bookingpress_tmp_setting_val == 'true' ) {
                            $bookingpress_tmp_setting_val = true;
                        } elseif ($bookingpress_tmp_setting_val == 'false' ) {
                            $bookingpress_tmp_setting_val = false;
                        }
                        if (gettype($bookingpress_tmp_setting_val) == 'boolean' ) {
                            $bookingpress_setting_return_data[ $bookingpress_setting_val['setting_name'] ] = $bookingpress_tmp_setting_val;
                        } else {
                            if ($bookingpress_setting_val['setting_name'] == 'smtp_password' ) {
                                $bookingpress_setting_return_data[ $bookingpress_setting_val['setting_name'] ] = stripslashes_deep($bookingpress_tmp_setting_val);
                            } else {
                                if (is_serialized($bookingpress_tmp_setting_val) ) {
                                    $bookingpress_setting_return_data[ $bookingpress_setting_val['setting_name'] ] = $bookingpress_tmp_setting_val;
                                } else {
                                    $bookingpress_setting_return_data[ $bookingpress_setting_val['setting_name'] ] = stripslashes_deep($bookingpress_tmp_setting_val);
                                }
                            }
                        }
                    }

                    $bookingpress_setting_return_data = apply_filters('bookingpress_modify_get_settings_data', $bookingpress_setting_return_data, $_POST); // phpcs:ignore WordPress.Security.NonceVerification
                }

                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Settings has been fetched successfully', 'bookingpress-appointment-booking');
                $response['data']    = $bookingpress_setting_return_data;                                        

                $response = apply_filters('bookingpress_modify_get_settings_response_data', $response, $_POST); //phpcs:ignore

            }

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Ajax request for save settings details
         *
         * @return void
         */
        public function bookingpress_save_settings_details()
        {
            global $BookingPress, $wpdb, $tbl_bookingpress_form_fields;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'save_settings', true, 'bpa_wp_nonce' );
            
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
            $response['msg']     = esc_html__('Something Went wrong while updating settings...', 'bookingpress-appointment-booking');

           if (! empty($_POST) && ! empty($_POST['action']) && ( sanitize_text_field($_POST['action']) == 'bookingpress_save_settings_data' ) && ! empty($_POST['settingType']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_save_settings_data = (array) $_POST; // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_setting_type       = sanitize_text_field($_POST['settingType']); // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_setting_action     = sanitize_text_field($_POST['action']); // phpcs:ignore WordPress.Security.NonceVerification
                unset($bookingpress_save_settings_data['settingType']);
                unset($bookingpress_save_settings_data['action']);
                unset($bookingpress_save_settings_data['_wpnonce']);

                if ($bookingpress_setting_type == 'notification_setting' && isset($bookingpress_save_settings_data['smtp_test_receiver_email']) && isset($bookingpress_save_settings_data['smtp_test_msg']) ) {
                    unset($bookingpress_save_settings_data['smtp_test_receiver_email']);
                    unset($bookingpress_save_settings_data['smtp_test_msg']);
                }
                $bookingpress_save_settings_data = apply_filters('bookingpress_modify_save_setting_data',$bookingpress_save_settings_data,$_POST); // phpcs:ignore WordPress.Security.NonceVerification

                $bookingpress_response_arr = array();
                
                foreach ( $bookingpress_save_settings_data as $bookingpress_setting_key => $bookingpress_setting_val ) {
                    if ($bookingpress_setting_key == 'company_avatar_url' && ! empty($bookingpress_setting_val) ) {

                            $bookingpress_avatar_url        = $bookingpress_setting_val;
                            $bookingpress_upload_image_name = isset($_POST['company_avatar_img']) ? sanitize_file_name($_POST['company_avatar_img']) : ''; // phpcs:ignore WordPress.Security.NonceVerification

                            $upload_dir                 = BOOKINGPRESS_UPLOAD_DIR . '/';
                            $bookingpress_new_file_name = current_time('timestamp') . '_' . $bookingpress_upload_image_name;
                            $upload_path                = $upload_dir . $bookingpress_new_file_name;

                            $bookingpress_upload_res = new bookingpress_fileupload_class( $bookingpress_avatar_url, true );
                            $bookingpress_upload_res->check_cap          = true;
                            $bookingpress_upload_res->check_nonce        = true;
                            $bookingpress_upload_res->nonce_data         = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
                            $bookingpress_upload_res->nonce_action       = 'bpa_wp_nonce';
                            $bookingpress_upload_res->check_only_image   = true;
                            $bookingpress_upload_res->check_specific_ext = false;
                            $bookingpress_upload_res->allowed_ext        = array();
                            $upload_response = $bookingpress_upload_res->bookingpress_process_upload( $upload_path );

                            if( true == $upload_response ){ 
                                $bookingpress_setting_val = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_new_file_name;

                                $bookingpress_file_name_arr = explode('/', $bookingpress_avatar_url);
                                $bookingpress_file_name     = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
                                if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
                                    wp_delete_file(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
                                }
                            } else {
                                continue;
                            }
                    }

                    $bpa_prevent_setting_update = apply_filters( 'bookingpress_restrict_saving_settings_data', false, $bookingpress_setting_key );

                    if( false == $bpa_prevent_setting_update ){
                        $bookingpress_res = $BookingPress->bookingpress_update_settings($bookingpress_setting_key, $bookingpress_setting_type, $bookingpress_setting_val);
                        array_push($bookingpress_response_arr, $bookingpress_res);
                    }
                }
                
                if ($bookingpress_setting_type == 'general_setting') {
                    if(isset($_POST['anonymous_data']) && $_POST['anonymous_data'] == true) { // phpcs:ignore WordPress.Security.NonceVerification
                        $this->bookingpress_set_anonymus_data_cron();
                    }
                }

                do_action('boookingpress_after_save_settings_data', $_POST); // phpcs:ignore WordPress.Security.NonceVerification

                if (! in_array('0', $bookingpress_response_arr) ) {
                    $response['variant'] = 'success';
                    $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg']     = esc_html__('Settings has been updated successfully.', 'bookingpress-appointment-booking');
                }
            }

            wp_cache_delete( 'bookingpress_all_general_settings' );
            wp_cache_delete( 'bookingpress_all_customize_settings' );

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Ajax request for save default workhours details
         *
         * @return void
         */
        public function bookingpress_save_default_work_hours()
        {
	        global $wpdb, $BookingPress, $tbl_bookingpress_default_workhours,$bookingpress_global_options;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'save_workhours', true, 'bpa_wp_nonce' );
            
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
            $response['msg']     = esc_html__('Something Went wrong while updating settings...', 'bookingpress-appointment-booking');

            $delete_breakhours_condition = array(
                'bookingpress_is_break' => 1,
            );

            $wpdb->delete($tbl_bookingpress_default_workhours, $delete_breakhours_condition);
	        $global_data = $bookingpress_global_options->bookingpress_global_options();
            if (! empty($_REQUEST['break_data']) ) {
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['search_data'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
                $break_data = array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['break_data']);
                foreach ( $break_data as $break_key => $break_val ) {
                    $dayname = strtolower($break_key);
                    foreach ( $break_val as $days_break_keys => $days_break_vals ) {
                        $break_start_time = date('H:i:s', strtotime($days_break_vals['start_time']));
                        $break_end_time   = date('H:i:s', strtotime($days_break_vals['end_time']));

                        $bookingpress_insert_breakhours_data = array(
                         'bookingpress_workday_key' => $dayname,
                         'bookingpress_start_time'  => $break_start_time,
                         'bookingpress_end_time'    => $break_end_time,
                         'bookingpress_is_break'    => 1,
                         'bookingpress_created_at'  => current_time('mysql'),
                        );

                        $wpdb->insert($tbl_bookingpress_default_workhours, $bookingpress_insert_breakhours_data);
                    }
                }
            }
            if (! empty($_REQUEST['workhours_timings']) ) {
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['search_data'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
                $workhour_timings = array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['workhours_timings']);
                foreach ( $workhour_timings as $timing_key => $timing_val ) {
                    $dayname           = strtolower($timing_key);
                    $start_time        = ( $timing_val['start_time'] != 'Off' ) ? date('H:i:s', strtotime($timing_val['start_time'])) : null;
                    $end_time          = ( $timing_val['start_time'] != 'Off' ) ? date('H:i:s', strtotime($timing_val['end_time'])) : null;
                    $workhours_counter = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_workhours_id) as total FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 0", $dayname ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm

                    if ($workhours_counter > 0 ) {
                        $bookingpress_update_data = array(
                         'bookingpress_start_time' => $start_time,
                         'bookingpress_end_time'   => $end_time,
                        );

                        $bookingpress_where_condition = array(
                         'bookingpress_workday_key' => $dayname,
                         'bookingpress_is_break'    => 0,
                        );

                        $wpdb->update($tbl_bookingpress_default_workhours, $bookingpress_update_data, $bookingpress_where_condition);
                    } else {
                        $bookingpress_insertdata = array(
                        'bookingpress_workday_key' => $dayname,
                        'bookingpress_start_time'  => $start_time,
                        'bookingpress_end_time'    => $end_time,
                        'bookingpress_is_break'    => 0,
                        'bookingpress_created_at'  => current_time('mysql'),
                        );

                        $wpdb->insert($tbl_bookingpress_default_workhours, $bookingpress_insertdata);
                    }
                }

                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Settings has been updated successfully.', 'bookingpress-appointment-booking');

                wp_cache_delete('bookingpress_default_workhours_without_break');
                wp_cache_delete('bookingpress_default_daysoff');
            }

            wp_cache_delete( 'bookingpress_all_general_settings' );
            wp_cache_delete( 'bookingpress_all_customize_settings' );

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Ajax request for get default work hours details
         *
         * @return void
         */
        public function bookingpress_get_default_work_hours()
        {
            global $wpdb, $tbl_bookingpress_default_workhours, $tbl_bookingpress_services, $bookingpress_global_options, $BookingPress;
            $response              = array();

            if(!empty($_REQUEST['action']) &&  $_REQUEST['action'] == 'bookingpress_get_default_work_hours_details') {
                $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_workhours', true, 'bpa_wp_nonce' );            
                if( preg_match( '/error/', $bpa_check_authorization ) ){
                    $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                    $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                    $response['variant'] = 'error';
                    $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                    $response['msg'] = $bpa_error_msg;

                    wp_send_json( $response );
                    die;
                }
            }

            $bookingpress_workhours_data = $bookingpress_selected_work_times = array();

            $response['data']    = $bookingpress_workhours_data;
            $response['msg']     = esc_html__('Something went wrong.', 'bookingpress-appointment-booking');
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['variant'] = 'error';

            $bookingpress_days_arr = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );

			$default_start_time      = '00:00:00';
			$default_end_time        = '23:55:00';
			$step_duration_val       = 5;
			$break_step_duration_val = 5;

	        $global_data = $bookingpress_global_options->bookingpress_global_options();
            foreach ( $bookingpress_days_arr as $days_key => $days_val ) {

                $bookingpress_breaks_arr = $bookingpress_times_arr = array();
                $curr_time               = $tmp_start_time = date('H:i:s', strtotime($default_start_time));
                $tmp_end_time            = date('H:i:s', strtotime($default_end_time));

                $bookingpress_times_arr[] = array(
                    'start_time' => 'Off',
                    'formatted_start_time' => esc_html__( 'Off', 'bookingpress-appointment-booking' ),
                );

                // Get breaks for current day and add to breaks array
                $bookingpress_get_break_workhours = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = %d ORDER BY bookingpress_start_time", $days_val, 1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
                if (! empty($bookingpress_get_break_workhours) ) {
                    foreach ( $bookingpress_get_break_workhours as $break_workhour_key => $break_workhour_val ) {
                        $bookingpress_breaks_arr[] = array(                            
				            'formatted_start_time' => date($global_data['wp_default_time_format'], strtotime($break_workhour_val['bookingpress_start_time'])),
				            'formatted_end_time'   => date($global_data['wp_default_time_format'], strtotime($break_workhour_val['bookingpress_end_time'])),
				            'start_time' => $break_workhour_val['bookingpress_start_time'],
				            'end_time'   => $break_workhour_val['bookingpress_end_time'],
                        );
                    }
                }

                // Default all workhours timing array
                do {
                    $tmp_time_obj = new DateTime($curr_time);
                    $tmp_time_obj->add(new DateInterval('PT' . $step_duration_val . 'M'));
                    $end_time = $tmp_time_obj->format('H:i:s');

                    if($end_time == "00:00:00"){
                        $end_time = "24:00:00";
                    }

                    $bookingpress_check_break_time_exist = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(bookingpress_workhours_id) FROM ' . $tbl_bookingpress_default_workhours . " WHERE bookingpress_workday_key = %s AND (bookingpress_start_time <= %s AND bookingpress_end_time >= %s) AND bookingpress_is_break = 0", $days_val, $curr_time, $end_time ) );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm

                    $bookingpress_times_arr[] = array(
                    'start_time' => $curr_time,
		            'formatted_start_time' => date($global_data['wp_default_time_format'],strtotime($curr_time)),
                    'end_time'   => $end_time,
		            'formatted_end_time' => date($global_data['wp_default_time_format'],strtotime($end_time)).' '.($end_time == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : ''),
                    );

                    if($end_time == "24:00:00"){
                        break;
                    }

                    $tmp_time_obj = new DateTime($curr_time);
                    $tmp_time_obj->add(new DateInterval('PT' . $step_duration_val . 'M'));
                    $curr_time = $tmp_time_obj->format('H:i:s');
                } while ( $curr_time <= $default_end_time );

                $bookingpress_workhours_data[] = array(
                'day_name'    => ucfirst($days_val),
                'break_times' => $bookingpress_breaks_arr,
                'worktimes'   => $bookingpress_times_arr,
                );

                $selected_timing_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 0", $days_val ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
                $selected_start_time  = $selected_timing_data['bookingpress_start_time'];
                $selected_end_time    = $selected_timing_data['bookingpress_end_time'];
                if ($selected_start_time == null ) {
                    $selected_start_time = 'Off';
                }

                if ($selected_end_time == null ) {
                    $selected_end_time = 'Off';
                }

                if($selected_end_time == "00:00:00"){
                    $selected_end_time = "24:00:00";
                }

                $bookingpress_selected_work_times[ ucfirst($days_val) ] = array(
                'start_time' => $selected_start_time,
				'formatted_start_time'=>date($global_data['wp_default_time_format'], strtotime($selected_start_time)),
                'end_time'   => $selected_end_time,
				'formatted_end_time'=>date($global_data['wp_default_time_format'], strtotime($selected_end_time))
                );
            }

            $default_break_timings = array();
            $curr_time             = $tmp_start_time = date('H:i:s', strtotime($default_start_time));
            $tmp_end_time          = date('H:i:s', strtotime($default_end_time));

            do {
                $tmp_time_obj = new DateTime($curr_time);
                $tmp_time_obj->add(new DateInterval('PT' . $step_duration_val . 'M'));
                $end_time = $tmp_time_obj->format('H:i:s');

                if($end_time == "00:00:00"){
                    $end_time = "24:00:00";
                }

                //$bookingpress_check_break_time_exist = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_workhours_id) FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND (bookingpress_start_time <= %s AND bookingpress_end_time >= %s) AND bookingpress_is_break = 1", $days_val, $curr_time, $end_time )); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm

				$break_global_data = $bookingpress_global_options->bookingpress_global_options();
                //if (! $bookingpress_check_break_time_exist ) {
                    $default_break_timings[] = array(
                    'start_time' => $curr_time,
                    'start_time_val' => $curr_time,
					'formatted_start_time' => date($break_global_data['wp_default_time_format'],strtotime($curr_time)),
                    'end_time'   => $end_time,
                    'end_time_val'   => $end_time,
					'formatted_end_time' => date($break_global_data['wp_default_time_format'],strtotime($end_time)).' '.($end_time == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : ''),
                    );
               // }

                if($end_time == "24:00:00"){
                    break;
                }

                $tmp_time_obj = new DateTime($curr_time);
                $tmp_time_obj->add(new DateInterval('PT' . $break_step_duration_val . 'M'));
                $curr_time = $tmp_time_obj->format('H:i:s');
            } while ( $curr_time <= $default_end_time );
         
            $response['data']                = $bookingpress_workhours_data;
            $response['selected_workhours']  = $bookingpress_selected_work_times;
            $response['default_break_times'] = $default_break_timings;
            $response['msg']                 = esc_html__('Workhours Data.', 'bookingpress-appointment-booking');
            $response['title']               = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['variant']             = 'success';

            if(!empty($_REQUEST['action']) &&  $_REQUEST['action'] == 'bookingpress_get_default_work_hours_details') { 
                wp_send_json($response);
            } else {
                return $response;
            }
        }
        
        /**
         * Ajax request for save default daysoff details
         *
         * @return void
         */
        public function bookingpress_save_default_daysoff_details()
        {
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'save_default_daysoff', true, 'bpa_wp_nonce' );
            
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

            if (! empty($_REQUEST['daysoff']) && ! empty($_REQUEST['action']) && ( sanitize_text_field($_REQUEST['action']) == 'bookingpress_save_default_daysoff' ) ) {
                global $wpdb, $tbl_bookingpress_default_daysoff, $BookingPress;

                // $wpdb->delete($tbl_bookingpress_default_daysoff);
                $wpdb->query("DELETE FROM {$tbl_bookingpress_default_daysoff}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False Positive alarm

                if (! empty($_REQUEST['daysoff']) ) {
                 // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['daysoff'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
                    $daysoff_arr = array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['daysoff']);
                    foreach ( $daysoff_arr as $daysoff ) {
                        $start_date    = ! empty($daysoff['dayoff_date'][0]) ? sanitize_text_field($daysoff['dayoff_date'][0]) : '';
                        $start_date    = date_format(date_create($start_date), 'Y-m-d H:i:s');
                        $end_date      = ! empty($daysoff['dayoff_date'][1]) ? sanitize_text_field($daysoff['dayoff_date'][1]) : '';
                        $end_date      = date_format(date_create($end_date), 'Y-m-d H:i:s');
                        $dayoff_name   = ! empty($daysoff['dayoff_name']) ? sanitize_text_field($daysoff['dayoff_name']) : '';
                        $dayoff_repeat = ( ! empty($daysoff['dayoff_repeat']) && sanitize_text_field($daysoff['dayoff_repeat']) == 'true' ) ? 1 : 0;
                        $args          = array(
                         'bookingpress_name'       => $dayoff_name,
                         'bookingpress_start_date' => $start_date,
                         'bookingpress_end_date'   => $end_date,
                         'bookingpress_repeat'     => $dayoff_repeat,
                         'bookingpress_created_at' => current_time('mysql'),
                        );
                        $wpdb->insert($tbl_bookingpress_default_daysoff, $args);
                    }
                }
                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Holiday settings updated successfully.', 'bookingpress-appointment-booking');
            }

            wp_cache_delete( 'bookingpress_all_general_settings' );
            wp_cache_delete( 'bookingpress_all_customize_settings' );

            wp_send_json($response);
        }
        
        /**
         * Ajax request for get default daysoff details
         *
         * @return void
         */
        public function bookingpress_get_default_daysoff_details()
        {
            global $wpdb, $tbl_bookingpress_default_daysoff;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_default_daysoff', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $days_off_arr = array();
            $days_off     = $wpdb->get_results('SELECT * FROM ' . $tbl_bookingpress_default_daysoff, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False Positive alarm
            if (! empty($days_off) ) {
                foreach ( $days_off as $day_off ) {
                    $day_off_arr                  = array();
                    $day_off_arr['id']            = $day_off['bookingpress_dayoff_id'];
                    $day_off_arr['dayoff_name']   = stripslashes_deep($day_off['bookingpress_name']);
                    $start_date                   = ! empty($day_off['bookingpress_start_date']) ? date_format(date_create($day_off['bookingpress_start_date']), 'F d, Y') : array();
                    $end_date                     = ! empty($day_off['bookingpress_end_date']) ? date_format(date_create($day_off['bookingpress_end_date']), 'F d, Y') : array();
                    $day_off_date                 = array( $start_date, $end_date );
                    $day_off_arr['dayoff_date']   = $day_off_date;
                    $day_off_arr['dayoff_repeat'] = ! empty($day_off['bookingpress_repeat']) ? true : false;
                    $days_off_arr[]               = $day_off_arr;
                }
            }

            echo wp_json_encode($days_off_arr);
            exit();
        }

        function bookingpress_remove_company_avatar_func(){
            global $wpdb;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'remove_company_avatar', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            if (! empty($_POST) && ! empty($_POST['upload_file_url']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_uploaded_avatar_url = esc_url_raw($_POST['upload_file_url']); // phpcs:ignore
                $bookingpress_file_name_arr       = explode('/', $bookingpress_uploaded_avatar_url);
                $bookingpress_file_name           = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
                if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
                    wp_delete_file(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
                }
            }
            die;
        }
        
        /**
         * Ajax request for currency supported or not
         *
         * @return void
         */
        function bookingpress_check_currency_status_func(){

            global $wpdb,$bookingpress_global_options,$bookingpress_payment_gateways,$BookingPress;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_currency_status', true, 'bpa_wp_nonce' );
            
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

            $bookingpress_paypal_currency = $bookingpress_payment_gateways->bookingpress_paypal_supported_currency_list(); 

            $bookingpress_currency = (isset($_REQUEST['bookingpress_currency']) && !empty($_REQUEST['bookingpress_currency'])) ? sanitize_text_field($_REQUEST['bookingpress_currency']) : '';
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
            echo wp_json_encode($response);
            exit();
        }    
        
        /**
         * Set anonymous data cron
         *
         * @return void
         */
        function bookingpress_set_anonymus_data_cron() {
            wp_get_schedules();
            if ( ! wp_next_scheduled('bookingpress_send_anonymous_data') ) {                
                wp_schedule_event( time(), 'weekly', 'bookingpress_send_anonymous_data');
            }
        }

        /**
         * Set transient data clean up cron - weekly
         * 
         * @return void
         */
        function bookingpress_cleanup_transient_data(){
            wp_get_schedules();
            if( !wp_next_scheduled( 'bookingpress_cleanup_transient_data_hook' ) ){
                wp_schedule_event( time(), 'weekly', 'bookingpress_cleanup_transient_data_hook');
            }
        }
    }
}
global $bookingpress_settings;
$bookingpress_settings = new bookingpress_settings();


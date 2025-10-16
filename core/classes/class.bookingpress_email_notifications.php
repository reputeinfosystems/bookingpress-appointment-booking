<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_email_notifications') ) {
    class bookingpress_email_notifications Extends BookingPress_Core
    {
        var $bookingpress_email_notification_type = '';
        var $bookingpress_email_sender_name       = '';
        var $bookingpress_email_sender_email      = '';
        var $bookingpress_admin_email             = '';
        var $bookingpress_smtp_username           = '';
        var $bookingpress_smtp_password           = '';
        var $bookingpress_smtp_host               = '';
        var $bookingpress_smtp_port               = '';
        var $bookingpress_smtp_secure             = '';
        var $bookingpress_gmail_client_id         = '';
        var $bookingpress_gmail_client_secret     = '';
        var $bookingpress_gmail_auth_token        = '';
        var $bookingpress_response_email          = '';
        var $bookingpress_template_type           = '';

        function __construct()
        {
            add_filter('bookingpress_modify_email_notification_content', array( $this, 'bookingpress_modify_email_content_func' ), 10, 4);
        }
        
        /**
         * Initialize email configurations
         *
         * @return void
         */
        function bookingpress_init_emai_config()
        {
            global $BookingPress, $bookingpress_other_debug_log_id;
            $this->bookingpress_email_notification_type = esc_html($BookingPress->bookingpress_get_settings('selected_mail_service', 'notification_setting'));
            $this->bookingpress_email_sender_name       = stripslashes_deep($BookingPress->bookingpress_get_settings('sender_name', 'notification_setting'));
            $this->bookingpress_email_sender_email      = esc_html($BookingPress->bookingpress_get_settings('sender_email', 'notification_setting'));

            if ($this->bookingpress_email_notification_type == 'smtp' ) {
                $this->bookingpress_smtp_username = esc_html($BookingPress->bookingpress_get_settings('smtp_username', 'notification_setting'));
                $this->bookingpress_smtp_password = $BookingPress->bookingpress_get_settings('smtp_password', 'notification_setting');
                $this->bookingpress_smtp_host     = $BookingPress->bookingpress_get_settings('smtp_host', 'notification_setting');
                $this->bookingpress_smtp_port     = esc_html($BookingPress->bookingpress_get_settings('smtp_port', 'notification_setting'));
                $this->bookingpress_smtp_secure   = esc_html($BookingPress->bookingpress_get_settings('smtp_secure', 'notification_setting'));
            }

            if($this->bookingpress_email_notification_type == 'Google_Gmail'){
                $this->bookingpress_gmail_client_id = esc_html($BookingPress->bookingpress_get_settings('gmail_client_ID', 'notification_setting'));
                $this->bookingpress_gmail_client_secret = $BookingPress->bookingpress_get_settings('gmail_client_secret', 'notification_setting');
                $this->bookingpress_gmail_auth_token = $BookingPress->bookingpress_get_settings('bookingpress_gmail_auth', 'notification_setting');
                $this->bookingpress_response_email = $BookingPress->bookingpress_get_settings('bookingpress_response_email', 'notification_setting');
            }

            $bookingpress_debug_log_data = "Notification type => {$this->bookingpress_email_notification_type} | Sender name => {$this->bookingpress_email_sender_name} | Sender Email => {$this->bookingpress_email_sender_email} | SMTP Username => {$this->bookingpress_smtp_username} | SMTP Password => {$this->bookingpress_smtp_password} | SMTP Host => {$this->bookingpress_smtp_host} | SMTP Port => {$this->bookingpress_smtp_port} | SMTP Secure => {$this->bookingpress_smtp_secure}";
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Init Email Configuration', 'bookingpress_email_notiifcation', $bookingpress_debug_log_data, $bookingpress_other_debug_log_id);
        }
        
        /**
         * Function for send test email notification
         *
         * @param  mixed $smtp_host
         * @param  mixed $smtp_port
         * @param  mixed $smtp_secure
         * @param  mixed $smtp_username
         * @param  mixed $smtp_password
         * @param  mixed $smtp_test_receiver_email
         * @param  mixed $smtp_test_msg
         * @param  mixed $smtp_sender_email
         * @param  mixed $smtp_sender_name
         * @return void
         */
        function bookingpress_send_test_email_notification( $smtp_host, $smtp_port, $smtp_secure, $smtp_username, $smtp_password, $smtp_test_receiver_email, $smtp_test_msg, $smtp_sender_email, $smtp_sender_name )
        {
            global $wpdb, $BookingPress, $wp_version, $bookingpress_other_debug_log_id;

            $bookingpress_debug_log_args_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Test email notification arguments data', 'bookingpress_email_notiifcation', $bookingpress_debug_log_args_data, $bookingpress_other_debug_log_id);

            $is_mail_sent     = 0;
            $return_error_msg = esc_html__('SMTP Test Email cannot sent successfully', 'bookingpress-appointment-booking');
            $return_error_log = '';

            if (! empty($smtp_host) && ! empty($smtp_port) && ! empty($smtp_secure) && ! empty($smtp_username) && ! empty($smtp_password) && ! empty($smtp_test_receiver_email) && ! empty($smtp_test_msg) && ! empty($smtp_sender_email) && ! empty($smtp_sender_name) ) {
                if (version_compare($wp_version, '5.5', '<') ) {
                    include_once ABSPATH . WPINC . '/class-phpmailer.php';
                    include_once ABSPATH . WPINC . '/class-smtp.php';
                    $BookingPressMailer = new PHPMailer();
                } else {
                    include_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                    include_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                    include_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                    $BookingPressMailer = new PHPMailer\PHPMailer\PHPMailer();
                }

                $BookingPressMailer->CharSet   = 'UTF-8';
                $BookingPressMailer->SMTPDebug = 1; // change this value to 1 for debug
                ob_start();
                echo '<span class="bpa-smtp-notification-error-msg">';
             // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - Text is escaped properly
                echo addslashes(esc_html__('The SMTP debugging output is shown below:', 'bookingpress-appointment-booking'));
                echo '</span><pre>';
                $BookingPressMailer->isSMTP();
                $BookingPressMailer->Host     = $smtp_host;
                $BookingPressMailer->SMTPAuth = true;
                $BookingPressMailer->Username = $smtp_username;
                $BookingPressMailer->Password = $smtp_password;
                if (! empty($smtp_secure) && $smtp_secure != 'Disabled' ) {
                    $BookingPressMailer->SMTPSecure = strtolower($smtp_secure);
                }
                if ($smtp_secure == 'Disabled' ) {
                    $BookingPressMailer->SMTPAutoTLS = false;
                }
                $BookingPressMailer->Port = $smtp_port;
                $BookingPressMailer->setFrom($smtp_sender_email, $smtp_sender_name);
                $BookingPressMailer->addReplyTo($smtp_sender_email, $smtp_sender_name);
                $BookingPressMailer->addAddress($smtp_test_receiver_email);
                $BookingPressMailer->isHTML(true);
                $bookingpress_email_subject  = esc_html__('BookingPress SMTP Test Email Notification', 'bookingpress-appointment-booking');
                $BookingPressMailer->Subject = $bookingpress_email_subject;
                $BookingPressMailer->Body    = $smtp_test_msg;

                if (! $BookingPressMailer->send() ) {
                    echo '</pre><span class="bpa-dialog--sns__body--error-title">';
                 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - Text is escaped properly
                    echo addslashes(esc_html__('The full debugging output is shown below:', 'bookingpress-appointment-booking'));
                    echo '</span>';
                    
                    if($BookingPressMailer){
                        echo wp_json_encode($BookingPressMailer);
                    }                    
                    
                    $smtp_debug_log    = ob_get_clean();
                    $return_error_log .= '<pre>';
                    $return_error_log .= $smtp_debug_log;
                    $return_error_log .= '</pre>';
                    $return_error_msg  = $BookingPressMailer->ErrorInfo;
                } else {
                    $smtp_debug_log   = ob_get_clean();
                    $is_mail_sent     = 1;
                    $return_error_msg = '';
                }
            }

            $return_msg = array(
            'is_mail_sent'  => $is_mail_sent,
            'error_msg'     => $return_error_msg,
            'error_log_msg' => $return_error_log,
            );

            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Test email notification send response', 'bookingpress_email_notiifcation', $return_msg, $bookingpress_other_debug_log_id);

            echo wp_json_encode($return_msg);
            exit;
        }

        /* Function for send test gmail-email notification*/

        function bookingpress_send_test_gmail_notification( $gmail_client_id, $gmail_client_secret, $gmail_auth_secret, $gmail_test_receiver_email, $gmail_test_msg, $bookingpress_gmail_connect, $bookingpress_gmail_auth, $gmail_sender_email, $gmail_sender_name )
        {
            global $wpdb, $BookingPress, $wp_version, $bookingpress_other_debug_log_id;

            $bookingpress_debug_log_args_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Test G-mail notification arguments data', 'bookingpress_email_notiifcation', $bookingpress_debug_log_args_data, $bookingpress_other_debug_log_id);

            $is_mail_sent     = 0;
            $return_error_msg = esc_html__('Gmail Test Email cannot sent successfully', 'bookingpress-appointment-booking');
            $return_error_log = '';

            require_once BOOKINGPRESS_LIBRARY_DIR . "/gmail/vendor/autoload.php";

            $bookingpress_redirect_url = get_home_url() .'?page=bookingpress_gmailapi';

            $bookingpress_gmail_auth = stripslashes_deep( $bookingpress_gmail_auth );
            $gmail_oauth_data = json_decode( $bookingpress_gmail_auth, true);

            $client = new Google_Client();
            $client->setClientId($gmail_client_id);
            $client->setClientSecret( $gmail_client_secret );
            $client->setRedirectUri( $bookingpress_redirect_url);
            $client->setAccessToken( $gmail_oauth_data );
            
            /** Refresh Google API Token */
            if( $client->isAccessTokenExpired() ){
                $is_refreshed = $client->refreshToken( $gmail_oauth_data['refresh_token'] );

                if( !empty( $is_refreshed['error'] ) ){

                    global $bookingpress_other_debug_log_id;
                    $refreshed_token_err = $is_refreshed['error'];
                    do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'failed to refresh token', 'bookingpress_email_notiifcation', $refreshed_token_err, $bookingpress_other_debug_log_id);
                    $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 1);
                    return false;
                }
                $refresh_token = $gmail_oauth_data['refresh_token'];

                if( empty( $gmail_oauth_data['refresh_token'] ) ){
                    echo "inside this refresh token empty condition";
                    $gmail_oauth_data['refresh_token'] = $refresh_token;
                }
                
                $BookingPress->bookingpress_update_settings('bookingpress_gmail_auth', 'notification_setting',wp_json_encode($gmail_oauth_data));
                $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 0);
                $client->setAccessToken( $gmail_oauth_data );
            } else {
                $verify_token_url = 'https://www.googleapis.com/oauth2/v3/tokeninfo';
                
                $args = array(
                    'timeout' => false,
                    'method' => 'GET',
                    'body' => array(
                        'access_token' => $gmail_oauth_data['access_token']
                    )
                );
                $check_access_token = wp_remote_get( $verify_token_url, $args );
                
                if( is_wp_error( $check_access_token ) ){
                    global $bookingpress_debug_integration_log_id;
                   
                    return false;
                }

                $valid_access_token_code = wp_remote_retrieve_response_code( $check_access_token );

                if( 200 != $valid_access_token_code ){
                    $validate_access_token = json_decode( wp_remote_retrieve_body( $check_access_token ), 1 );
                    $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 1);

                    global $bookingpress_debug_integration_log_id;
                   
                    return false;
                } else {
                    $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 0);
                }
                
            }

            $service = new Google\Service\Gmail( $client );

            $user = 'me';
            $subjectCharset = $charset = 'utf-8';
            $strSubject = esc_html__('BookingPress Gmail Test Email Notification', 'bookingpress-appointment-booking');

            $bookingpress_gmail_sent_data = 'From: =?' . $subjectCharset . '?B?' . base64_encode($gmail_sender_name)."?= <".$gmail_sender_email.">\r\n";
            $bookingpress_gmail_sent_data .= "To: ".$gmail_test_receiver_email."\r\n";
            $bookingpress_gmail_sent_data .= 'Subject: =?' . $subjectCharset . '?B?' . base64_encode($strSubject) . "?=\r\n";
            $bookingpress_gmail_sent_data .= "MIME-Version: 1.0\r\n";
            $bookingpress_gmail_sent_data .= "Content-Type: text/html; charset=utf-8\r\n";
            $bookingpress_gmail_sent_data .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
            $gmail_test_msg = chunk_split( base64_encode( $gmail_test_msg ) );
            $bookingpress_gmail_sent_data .= "".$gmail_test_msg."\r\n";

            $mime = rtrim(strtr(base64_encode($bookingpress_gmail_sent_data), '+/', '-_'), '=');
            $msg = new Google_Service_Gmail_Message();
            $msg->setRaw($mime);

            $return_error_msg = esc_html__('Something went wrong..','bookingpress-appointment-booking');
            try {
                $message = $service->users_messages->send('me', $msg);
                $is_mail_sent = 1;
                $return_error_msg = '';
                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Test Email notification GMail success response', 'bookingpress_email_notiifcation', $is_mail_sent, $bookingpress_other_debug_log_id);
            } catch (Exception $e) {
                $return_error_msg = 'An error occurred: ' . $e->getMessage();
                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Test Email notification GMail error response', 'bookingpress_email_notiifcation', wp_json_encode( $e ), $bookingpress_other_debug_log_id);
            }

            $return_msg = array(
                'is_mail_sent'  => $is_mail_sent,
                'error_msg'     => $return_error_msg,
                'error_log_msg' => $return_error_log,
            );

           do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Test G-mail notification send response', 'bookingpress_email_notiifcation', $return_msg, $bookingpress_other_debug_log_id);

           echo wp_json_encode($return_msg);
           exit;
        }        
        /**
         * Core function for send email notification
         *
         * @param  mixed $template_type
         * @param  mixed $notification_name
         * @param  mixed $appointment_id
         * @param  mixed $receiver_email_id
         * @return void
         */
        function bookingpress_send_email_notification( $template_type, $notification_name, $appointment_id, $receiver_email_id, $cc_emails = array(), $force = false )
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_notifications, $wp_version, $bookingpress_other_debug_log_id, $bookingpress_settings;

            $bookingpress_send_email_notification_debug_log_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification argument data', 'bookingpress_email_notiifcation', $bookingpress_send_email_notification_debug_log_data, $bookingpress_other_debug_log_id);

            $bpa_proceed_sending = apply_filters( 'bookingpress_check_email_notiication_processing', true, wp_json_encode( func_get_args() ) );
            if( !$force && !$bpa_proceed_sending ){
                return false;
            }
            $bookingpress_email_reply_to_name=$bookingpress_email_reply_to_email='';
            $this->bookingpress_init_emai_config();
            if($template_type=='customer') {
                $bookingpress_admin_emails=esc_html($BookingPress->bookingpress_get_settings('admin_email', 'notification_setting'));
                $bookingpress_admin_email_id='';
                if ( ! empty( $bookingpress_admin_emails ) ) {
                    $bookingpress_admin_emails = explode( ',', $bookingpress_admin_emails );
                    $bookingpress_admin_email_id=$bookingpress_admin_emails[0];
                }
                $reply_to_data_arr = array(
                        'bookingpress_email_reply_to_name' => $this->bookingpress_email_sender_name, 
                        'bookingpress_email_reply_to_email'=> $bookingpress_admin_email_id);
                $reply_to_data_arr = apply_filters( 'bookingpress_get_email_notiication_reply_to_data', $reply_to_data_arr,$appointment_id);
                $bookingpress_email_reply_to_name=$reply_to_data_arr['bookingpress_email_reply_to_name'];
                $bookingpress_email_reply_to_email=$reply_to_data_arr['bookingpress_email_reply_to_email'];
            }
            else if($template_type=='employee') {
                $bpa_customer_email=$bpa_customer_name='';
                $appointment_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_customer_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                $bookingpress_customer_id = !empty($appointment_data['bookingpress_customer_id'])  ? intval($appointment_data['bookingpress_customer_id']) : 0 ;
                if(!empty($bookingpress_customer_id)) {
                    $bookingpress_customer_selection_details = $BookingPress->get_customer_details($bookingpress_customer_id);
                    $bpa_firstname =  isset($bookingpress_customer_selection_details['bookingpress_user_firstname']) ? $bookingpress_customer_selection_details['bookingpress_user_firstname'] : '';
                    $bpa_lastname =  isset($bookingpress_customer_selection_details['bookingpress_user_lastname']) ? $bookingpress_customer_selection_details['bookingpress_user_lastname'] : '';
                    if(!empty($bpa_firstname)) {
                        $bpa_customer_name=$bpa_firstname.' ';
                    }
                    if(!empty($bpa_lastname)){
                        $bpa_customer_name.=$bpa_lastname;
                    }
                    $bpa_customer_email =  isset($bookingpress_customer_selection_details['bookingpress_user_email']) ? $bookingpress_customer_selection_details['bookingpress_user_email'] : '';
                    if(empty($bpa_customer_name)) {
                        $bpa_customer_name=$this->bookingpress_email_sender_name;
                    }
                }
                $reply_to_data_arr = array(
                    'bookingpress_email_reply_to_name' => $bpa_customer_name, 
                    'bookingpress_email_reply_to_email'=> $bpa_customer_email
                );
                $reply_to_data_arr = apply_filters( 'bookingpress_get_email_notiication_reply_to_data_employee', $reply_to_data_arr,$appointment_id);
                
                $bookingpress_email_reply_to_name=$reply_to_data_arr['bookingpress_email_reply_to_name'];
                $bookingpress_email_reply_to_email=$reply_to_data_arr['bookingpress_email_reply_to_email'];
            }
            if(empty($bookingpress_email_reply_to_name)) {
                $bookingpress_email_reply_to_name=$this->bookingpress_email_sender_name;
            }
            if(empty($bookingpress_email_reply_to_email)) {
                $bookingpress_email_reply_to_email=$this->bookingpress_email_sender_email;
            }
            
            $bookingpress_email_send_res = array(
            'is_mail_sent'   => 0,
            'configurations' => array(
            'notification_type' => $this->bookingpress_email_notification_type,
            'sender_name'       => $this->bookingpress_email_sender_name,
            'sender_email'      => $this->bookingpress_email_sender_email,
            'smtp_username'     => base64_encode($this->bookingpress_smtp_password),
            'smtp_host'         => $this->bookingpress_smtp_host,
            'smtp_port'         => $this->bookingpress_smtp_port,
            'smpt_secure'       => $this->bookingpress_smtp_secure,
            'gmail_client_id'   => $this->bookingpress_gmail_client_id,
            'gmail_client_secret' => $this->bookingpress_gmail_client_secret,
            'gmail_auth_token'   => $this->bookingpress_gmail_auth_token,
            'gmail_connected_email' => $this->bookingpress_response_email,
            ),
            'error_response' => 'Something went wrong while sending email notification',
            'posted_data'    => array(),
            );

            //$bookingpress_is_notification_enabled = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_notification_id) FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_receiver_type = %s AND bookingpress_notification_name = %s AND bookingpress_notification_status = 1", $template_type, $notification_name)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm
            

            $bookingpress_notification_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_notification_id,bookingpress_notification_type FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_receiver_type = %s AND bookingpress_notification_name = %s AND bookingpress_notification_status = 1", $template_type, $notification_name),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm    
            
            $bookingpress_is_notification_enabled = !empty($bookingpress_notification_data['bookingpress_notification_id']) ? $bookingpress_notification_data['bookingpress_notification_id'] : 0 ;                        

            if($bookingpress_is_notification_enabled == 0){
                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification status data', 'bookingpress_email_notiifcation', "Is notification enabled ==> ".$bookingpress_is_notification_enabled, $bookingpress_other_debug_log_id);
                return $bookingpress_email_send_res;
            }

            if (! empty($this->bookingpress_email_notification_type) && ! empty($this->bookingpress_email_sender_name) && ! empty($this->bookingpress_email_sender_email) && ! empty($template_type) && ! empty($notification_name) && ! empty($receiver_email_id) ) {
                $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id =%d", $appointment_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                $this->bookingpress_template_type = $template_type;
                $bookingpress_appointment_data = apply_filters("bookingpress_modify_client_date_time_email_notification", $bookingpress_appointment_data, $this->bookingpress_template_type );
                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification appointment data', 'bookingpress_email_notiifcation', $bookingpress_appointment_data, $bookingpress_other_debug_log_id);

                $notification_type =  !empty($bookingpress_notification_data['bookingpress_notification_type']) ? $bookingpress_notification_data['bookingpress_notification_type'] : 'default';
                $bookingpress_is_allowed_email_notification = ! empty($bookingpress_appointment_data['bookingpress_appointment_send_notification']) ? 1 : 0;
                $bookingpress_is_allowed_email_notification = apply_filters('bookingpress_modify_allowed_email_notification_flag', $bookingpress_is_allowed_email_notification);

                if ($bookingpress_is_allowed_email_notification ) {
                    $bookingpress_notification_data = array(
                        'notification_name' => $notification_name,
                        'notification_type' => $notification_type,
                    );

                    $bookingpress_notification_data = apply_filters('bookingpress_modify_email_notification_data', $bookingpress_notification_data, $template_type, $notification_name, $bookingpress_appointment_data);

                    $notification_name = ! empty($bookingpress_notification_data['notification_name']) && is_array($bookingpress_notification_data) ? $bookingpress_notification_data['notification_name'] : $notification_name;
                    $notification_type = ! empty($bookingpress_notification_data['notification_type']) && is_array($bookingpress_notification_data) ? $bookingpress_notification_data['notification_type'] : $notification_type;

                    $bookingpress_get_email_template_details = $this->bookingpress_get_email_template_details($template_type, $notification_name, $bookingpress_appointment_data, $notification_type);

                    $bookingpress_email_subject = $bookingpress_email_content = '';
                    if (! empty($bookingpress_get_email_template_details) ) {
                        $bookingpress_email_subject = $bookingpress_get_email_template_details['notification_subject'];
                        $bookingpress_email_content = $bookingpress_get_email_template_details['notification_message'];
                    }

                    $bookingpress_email_send_res['posted_data'] = $bookingpress_get_email_template_details;

                    $attachments = array();
                    $attachments = apply_filters('bookingpress_email_notification_attachment', $attachments, $bookingpress_get_email_template_details, $appointment_id, $template_type, $notification_name, $bookingpress_appointment_data);

                    $is_mail_sent = 0;

                    switch ( $this->bookingpress_email_notification_type ) {
                    case 'php_mail':

                        include_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';

                        $phpmailer_version = PHPMailer\PHPMailer\PHPMailer::VERSION;

                        $bookingpress_email_header_data = 'Date: '.date('D, j M Y H:i:s O', current_time('timestamp') )."\r\n";
                        $bookingpress_email_header_data .= 'From: ' . $this->bookingpress_email_sender_name . '<' . $this->bookingpress_email_sender_email . "> \r\n";
                        $bookingpress_email_header_data .= 'Reply-To: ' . $bookingpress_email_reply_to_name . '<' . $bookingpress_email_reply_to_email . "> \r\n";
                        $bookingpress_email_header_data .= 'Message-ID: '. sprintf('<%s@%s>', $this->bookingpress_generate_random_msgid(), $this->bookingpress_get_host_name())."\r\n";
                        $bookingpress_email_header_data .= 'X-Mailer: PHPMailer ' . $phpmailer_version . " (https://github.com/PHPMailer/PHPMailer)\r\n";
                        $bookingpress_email_header_data .= "MIME-Version: 1.0\r\n";
                        $bookingpress_email_header_data .= "Content-Type: text/html; charset=UTF-8\r\n";
						
                        if(!empty($cc_emails) && is_array($cc_emails)){
                            $bookingpress_email_header_data .= "Cc: ".implode(',', $cc_emails)."\r\n";
                        }

                        if( !empty( $attachments ) ){
                            $attachment_id = wp_rand(100,999);
							
                            $boundary = md5( $attachment_id.'_'.current_time('timestamp') );

                            $bookingpress_email_header_data = 'Date: '.date('D, j M Y H:i:s O', current_time('timestamp') )."\r\n";
                            $bookingpress_email_header_data .= 'From: ' . $this->bookingpress_email_sender_name . '<' . $this->bookingpress_email_sender_email . "> \r\n";
                            $bookingpress_email_header_data .= 'Reply-To: ' . $bookingpress_email_reply_to_name . '<' . $bookingpress_email_reply_to_email . "> \r\n";
                            $bookingpress_email_header_data .= 'Message-ID: '. sprintf('<%s@%s>', $this->bookingpress_generate_random_msgid(), $this->bookingpress_get_host_name())."\r\n";
                            $bookingpress_email_header_data .= 'X-Mailer: PHPMailer ' . $phpmailer_version . " (https://github.com/PHPMailer/PHPMailer)\r\n";
                            if(!empty($cc_emails) && is_array($cc_emails)){
                                $bookingpress_email_header_data .= "Cc: ".implode(',', $cc_emails)."\r\n";
                            }
                            
                            $bookingpress_email_header_data .= "MIME-Version: 1.0\r\n";
							$bookingpress_email_header_data .= "Content-Transfer-Encoding: 7bit\r\n";
                            $bookingpress_email_header_data .= "Content-Type: multipart/mixed; boundary = \"{$boundary}\"\r\n";

							$bookingpress_temp_email_content = "--$boundary\r\n";
                            $bookingpress_temp_email_content .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
							$bookingpress_temp_email_content .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                            $bookingpress_email_content = $bookingpress_temp_email_content . $bookingpress_email_content . "\r\n";
							foreach( $attachments as $attachment_file ){
								$attachment_name = basename( $attachment_file );
								$attachment_type = mime_content_type( $attachment_file );

								if (! function_exists('WP_Filesystem') ) {
									include_once ABSPATH . 'wp-admin/includes/file.php';
								}

								WP_Filesystem();
								global $wp_filesystem;

								$file_content  = $wp_filesystem->get_contents($attachment_file);
								$file_content = chunk_split( base64_encode( $file_content ) );

								$bookingpress_email_content .= "--$boundary\r\n";
								$bookingpress_email_content .= "Content-Type: {$attachment_type}; name={$attachment_name}\r\n";
								$bookingpress_email_content .= "Content-Disposition: attachment; filename={$attachment_name}\r\n";
								$bookingpress_email_content .= "Content-Transfer-Encoding: base64\r\n";
								$bookingpress_email_content .= "X-Attachment-Id: {$attachment_id}\r\n\r\n";
								$bookingpress_email_content .= $file_content;
							}
							$bookingpress_email_content .= "\r\n--{$boundary}--\r\n";
						}
                        
                        if (@mail($receiver_email_id, $bookingpress_email_subject, $bookingpress_email_content, $bookingpress_email_header_data) ) {
                             $bookingpress_email_send_res['is_mail_sent'] = 1;
                             $is_mail_sent                                = 1;
                        }
                        break;
                    case 'wp_mail':
                        $bookingpress_email_header_data = 'From: ' . $this->bookingpress_email_sender_name . '<' . $this->bookingpress_email_sender_email . "> \r\n";
                        $bookingpress_email_header_data .= 'Reply-To: ' . $bookingpress_email_reply_to_name . '<' . $bookingpress_email_reply_to_email . "> \r\n";
                        $bookingpress_email_header_data .= "Content-Type: text/html; charset=UTF-8\r\n";
                        if(!empty($cc_emails) && is_array($cc_emails)){
                            $bookingpress_email_header_data .= "Cc: ".implode(',', $cc_emails)."\r\n";
                        }
                        $bookingpress_settings->bpa_sending_wp_mail_upon_booking = true;
                        if (wp_mail($receiver_email_id, $bookingpress_email_subject, $bookingpress_email_content, $bookingpress_email_header_data, $attachments) ) {
                            $bookingpress_email_send_res['is_mail_sent'] = 1;
                            $is_mail_sent                                = 1;
                        }
                        $bookingpress_settings->bpa_sending_wp_mail_upon_booking = false;
                        break;
                    case 'smtp':
                        if ( ! empty($this->bookingpress_smtp_host) && ! empty($this->bookingpress_smtp_port) && ! empty($this->bookingpress_smtp_secure) ) {
                            if (version_compare($wp_version, '5.5', '<') ) {
                                include_once ABSPATH . WPINC . '/class-phpmailer.php';
                                include_once ABSPATH . WPINC . '/class-smtp.php';
                                $BookingPressMailer = new PHPMailer();
                            } else {
                                include_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                                include_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                                include_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                                $BookingPressMailer = new PHPMailer\PHPMailer\PHPMailer();
                            }

                            $BookingPressMailer->CharSet   = 'UTF-8';
                            $BookingPressMailer->SMTPDebug = 0; // change this value to 1 for debug
                            $BookingPressMailer->isSMTP();
                            $BookingPressMailer->Host     = $this->bookingpress_smtp_host;
                            $BookingPressMailer->SMTPAuth = true;
                            if( ! empty($this->bookingpress_smtp_username)  ){
                                $BookingPressMailer->Username = $this->bookingpress_smtp_username;
                            }
                            if( ! empty($this->bookingpress_smtp_password) ){
                                $BookingPressMailer->Password = $this->bookingpress_smtp_password;
                            }
                            if (! empty($this->bookingpress_smtp_secure) && $this->bookingpress_smtp_secure != 'Disabled' ) {
                                $BookingPressMailer->SMTPSecure = strtolower($this->bookingpress_smtp_secure);
                            }
                            if ($this->bookingpress_smtp_secure == 'Disabled' ) {
                                $BookingPressMailer->SMTPAutoTLS = false;
                            }
                            $BookingPressMailer->Port = $this->bookingpress_smtp_port;
                            $BookingPressMailer->setFrom($this->bookingpress_email_sender_email, $this->bookingpress_email_sender_name);
                            $BookingPressMailer->addReplyTo($bookingpress_email_reply_to_email, $bookingpress_email_reply_to_name);
                            $BookingPressMailer->addAddress($receiver_email_id);

                            if(!empty($cc_emails) && is_array($cc_emails)){
                                foreach($cc_emails as $ccemail ){
                                    $BookingPressMailer->addCC($ccemail);
                                }
                            }

                            if (! empty($attachments) ) {
                                foreach ( $attachments as $attachment ) {
                                    $BookingPressMailer->addAttachment($attachment);
                                }
                            }
                                $BookingPressMailer->isHTML(true);
                                $BookingPressMailer->Subject = $bookingpress_email_subject;
                                $BookingPressMailer->Body    = $bookingpress_email_content;

                            if ($BookingPressMailer->send() ) {
                                $is_mail_sent                                = 1;
                                $bookingpress_email_send_res['is_mail_sent'] = 1;
                                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification SMTP success response', 'bookingpress_email_notiifcation', $is_mail_sent, $bookingpress_other_debug_log_id);
                            } else {
                                $bookingpressmailer_errorinfo                  = ! empty($BookingPressMailer->ErrorInfo) ? $BookingPressMailer->ErrorInfo : '';
                                $bookingpress_email_send_res['error_response'] = $bookingpressmailer_errorinfo;
                                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification SMTP error response', 'bookingpress_email_notiifcation', $bookingpressmailer_errorinfo, $bookingpress_other_debug_log_id);
                            }
                        }
                        break;
                    case 'Google_Gmail':
                        if(!empty( $this->bookingpress_gmail_client_id) && !empty($this->bookingpress_gmail_client_secret) && !empty($this->bookingpress_gmail_auth_token) ){

                            require_once BOOKINGPRESS_LIBRARY_DIR . "/gmail/vendor/autoload.php";
                            $bookingpress_redirect_url = get_home_url() .'?page=bookingpress_gmailapi';

                            $this->bookingpress_gmail_auth_token = stripslashes_deep( $this->bookingpress_gmail_auth_token );
                            $gmail_oauth_data = json_decode( $this->bookingpress_gmail_auth_token , true);

                            $client = new Google_Client();
                            $client->setClientId($this->bookingpress_gmail_client_id);
                            $client->setClientSecret( $this->bookingpress_gmail_client_secret );
                            $client->setRedirectUri( $bookingpress_redirect_url);
                            $client->setAccessToken( $gmail_oauth_data );
                            
                            /** Refresh Google API Token */
                            if( $client->isAccessTokenExpired() ){
                                $is_refreshed = $client->refreshToken( $gmail_oauth_data['refresh_token'] );

                                if( !empty( $is_refreshed['error'] ) ){
                                    $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 1);
                                    return false;
                                }
                                $refresh_token = $gmail_oauth_data['refresh_token'];
                                $gmail_oauth_data =  $client->getAccessToken();
                                if( empty( $gmail_oauth_data['refresh_token'] ) ){
                                    $gmail_oauth_data['refresh_token'] = $refresh_token;
                                }
                                $BookingPress->bookingpress_update_settings('bookingpress_gmail_auth', 'notification_setting',wp_json_encode($gmail_oauth_data));
                                $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 0);
                                $client->setAccessToken( $gmail_oauth_data );
                            } else {
                                $verify_token_url = 'https://www.googleapis.com/oauth2/v3/tokeninfo';
                                
                                $args = array(
                                    'timeout' => false,
                                    'method' => 'GET',
                                    'body' => array(
                                        'access_token' => $gmail_oauth_data['access_token']
                                    )
                                );
                                $check_access_token = wp_remote_get( $verify_token_url, $args );
                                
                                if( is_wp_error( $check_access_token ) ){
                                    global $bookingpress_debug_integration_log_id;
                                    return false;
                                }

                                $valid_access_token_code = wp_remote_retrieve_response_code( $check_access_token );

                                if( 200 != $valid_access_token_code ){
                                    $validate_access_token = json_decode( wp_remote_retrieve_body( $check_access_token ), 1 );
                                    $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 1);
                                    return false;
                                } else {
                                    $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 0);
                                }
                                
                            }

                            $service = new Google\Service\Gmail( $client );
                            $user = 'me';
                            $subjectCharset = $charset = 'utf-8';
                            $boundary = uniqid(wp_rand(), true);
                            $strSubject = $bookingpress_email_subject;
                            $bookingpress_email_content_data = 'From: =?' . $subjectCharset . '?B?' . base64_encode($this->bookingpress_email_sender_name)."?= <".$this->bookingpress_email_sender_email.">\r\n";
                            $bookingpress_email_content_data .= 'Reply-To: =?' . $subjectCharset . '?B?' . base64_encode($bookingpress_email_reply_to_name)."?= <".$bookingpress_email_reply_to_email.">\r\n";
                            $bookingpress_email_content_data .= "To: ".$receiver_email_id."\r\n";
                            if(!empty($cc_emails) && is_array($cc_emails)){
                                $bookingpress_email_content_data .= "Cc:" .  implode(",",$cc_emails)  . "\r\n";
                            }
                            $bookingpress_email_content_data .= 'Subject: =?' . $subjectCharset . '?B?' . base64_encode($strSubject) . "?=\r\n";
                            $bookingpress_email_content_data .= 'MIME-Version: 1.0' . "\r\n";
                            $bookingpress_email_content_data .= 'Content-type: Multipart/Related; boundary="' . $boundary . '"' . "\r\n";
                            $bookingpress_email_content_data .= "\r\n--{$boundary}\r\n";
                            $bookingpress_email_content_data .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
                            $bookingpress_email_content_data .= "Content-Transfer-Encoding: base64" . "\r\n\r\n";
                            $bookingpress_email_content = chunk_split( base64_encode( $bookingpress_email_content ) );
                            $bookingpress_email_content_data .= $bookingpress_email_content . "\r\n";

                            foreach( $attachments as $attachment_file ){

                                $attachment_id = wp_rand(100,999);

                                $attachment_name = basename( $attachment_file );
                                $attachment_type = mime_content_type( $attachment_file );
                        
                                if (! function_exists('WP_Filesystem') ) {
                                    include_once ABSPATH . 'wp-admin/includes/file.php';
                                }
                        
                                WP_Filesystem();
                                global $wp_filesystem;
                        
                                $file_content  = $wp_filesystem->get_contents($attachment_file);
                                $file_content = chunk_split( base64_encode( $file_content ) );
                                $bookingpress_email_content_data .= "--$boundary\r\n";
                                $bookingpress_email_content_data .= "Content-Type: {$attachment_type}; name={$attachment_name}\r\n";
                                $bookingpress_email_content_data .= "Content-Disposition: attachment; filename={$attachment_name}\r\n";
                                $bookingpress_email_content_data .= "Content-Transfer-Encoding: base64\r\n";
                                $bookingpress_email_content_data .= "X-Attachment-Id: {$attachment_id}\r\n\r\n";
                                $bookingpress_email_content_data .= $file_content;
                            }
                            $bookingpress_email_content_data .= "\r\n--{$boundary}--\r\n";

                            // The message needs to be encoded in Base64URL
                            $mime = rtrim(strtr(base64_encode($bookingpress_email_content_data), '+/', '-_'), '=');
                            $msg = new Google_Service_Gmail_Message();
                            $msg->setRaw($mime);

                            $return_error_msg = esc_html__('Something went wrong..','bookingpress-appointment-booking');
                            try {
                                $message = $service->users_messages->send('me', $msg);
                                $is_mail_sent                                = 1;
                                $bookingpress_email_send_res['is_mail_sent'] = 1;
                                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification GMail success response', 'bookingpress_email_notiifcation', $is_mail_sent, $bookingpress_other_debug_log_id);
                            } catch (Exception $e) {
                                $return_error_msg = 'An error occurred: ' . $e->getMessage();
                                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification GMail error response', 'bookingpress_email_notiifcation', $return_error_msg, $bookingpress_other_debug_log_id);
                            }

                        }
                        break;
                    }
                    if (! empty($attachments) ) {
                        foreach ( $attachments as $attachment ) {
                            wp_delete_file($attachment);
                        }
                    }
                }
            }

            return $bookingpress_email_send_res;
        }

        function bookingpress_get_host_name(){
            $result = '';
            if (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) {
                $result = !empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : '';
            } elseif (function_exists('gethostname') && gethostname() !== false) {
                $result = gethostname();
            } elseif (php_uname('n') !== false) {
                $result = php_uname('n');
            }
            if (!static::bookingpress_is_valid_host($result)) {
                return 'localhost.localdomain';
            }

            return $result;
        }

        public static function bookingpress_is_valid_host($host){
            //Simple syntax limits
            if (
                empty($host)
                || !is_string($host)
                || strlen($host) > 256
                || !preg_match('/^([a-zA-Z\d.-]*|\[[a-fA-F\d:]+\])$/', $host)
            ) {
                return false;
            }
            //Looks like a bracketed IPv6 address
            if (strlen($host) > 2 && substr($host, 0, 1) === '[' && substr($host, -1, 1) === ']') {
                return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
            }
            //If removing all the dots results in a numeric string, it must be an IPv4 address.
            //Need to check this first because otherwise things like `999.0.0.0` are considered valid host names
            if (is_numeric(str_replace('.', '', $host))) {
                //Is it a valid IPv4 address?
                return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
            }
            //Is it a syntactically valid hostname (when embeded in a URL)?
            return filter_var('http://' . $host, FILTER_VALIDATE_URL) !== false;
        }

        function bookingpress_generate_random_msgid(){
            $len = 32; //32 bytes = 256 bits
            $bytes = '';
            if (function_exists('random_bytes')) {
                try {
                    $bytes = random_bytes($len);
                } catch (\Exception $e) {
                    //Do nothing
                }
            } elseif (function_exists('openssl_random_pseudo_bytes')) {
                /** @noinspection CryptographicallySecureRandomnessInspection */
                $bytes = openssl_random_pseudo_bytes($len);
            }
            if ($bytes === '') {
                //We failed to produce a proper random string, so make do.
                //Use a hash to force the length to the same as the other methods
                $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
            }

            //We don't care about messing up base64 format here, just want a random string
            return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
        }
        
        /**
         * Function to send email notifications directly to send emails with passed data
         *
         * @param  mixed $email_to
         * @param  mixed $email_subject
         * @param  mixed $email_content
         * @param  mixed $reply_to
         * @param  mixed $reply_to_name
         * @return void
         */
        function bookingpress_send_custom_email_notifications( $email_to = '', $email_subject = '', $email_content = '', $from_name = '', $from_email = '', $reply_to = '', $reply_to_name = '', $cc_emails = array(), $attachments = array()){
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_notifications, $wp_version, $bookingpress_other_debug_log_id;

            $this->bookingpress_init_emai_config();

            switch ( $this->bookingpress_email_notification_type ) {
                case 'php_mail':
                    $bookingpress_email_header_data = 'From: ' . $from_name . '<' . $from_email . "> \r\n";
                    $bookingpress_email_header_data .= 'Reply-To: ' . $reply_to_name . '<' . $reply_to . "> \r\n";
                    $bookingpress_email_header_data .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if(!empty($cc_emails) && is_array($cc_emails)){
                        $bookingpress_email_header_data .= "Cc: ".implode(',', $cc_emails)."\r\n";
                    }

                    if( !empty( $attachments ) ){
                        $attachment_id = wp_rand(100,999);
                        
                        $boundary = md5( $attachment_id.'_'.current_time('timestamp') );

                        $bookingpress_email_header_data = 'Date: '.date('D, j M Y H:i:s O', current_time('timestamp') )."\r\n";
                        $bookingpress_email_header_data .= 'From: ' . $this->from_name . '<' . $this->from_email . "> \r\n";
                        $bookingpress_email_header_data .= 'Reply-To: ' . $reply_to_name . '<' . $reply_to . "> \r\n";
                        $bookingpress_email_header_data .= 'X-Mailer: PHPMailer ' . $phpmailer_version . " (https://github.com/PHPMailer/PHPMailer)\r\n";
                        if(!empty($cc_emails) && is_array($cc_emails)){
                            $bookingpress_email_header_data .= "Cc: ".implode(',', $cc_emails)."\r\n";
                        }
                        
                        $bookingpress_email_header_data .= "MIME-Version: 1.0\r\n";
                        $bookingpress_email_header_data .= "Content-Transfer-Encoding: 7bit\r\n";
                        $bookingpress_email_header_data .= "Content-Type: multipart/mixed; boundary = \"{$boundary}\"\r\n";

                        $bookingpress_temp_email_content = "--$boundary\r\n";
                        $bookingpress_temp_email_content .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
                        $bookingpress_temp_email_content .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                        $bookingpress_email_content = $bookingpress_temp_email_content . $email_content . "\r\n";
                        foreach( $attachments as $attachment_file ){
                            $attachment_name = basename( $attachment_file );
                            $attachment_type = mime_content_type( $attachment_file );

                            if (! function_exists('WP_Filesystem') ) {
                                include_once ABSPATH . 'wp-admin/includes/file.php';
                            }

                            WP_Filesystem();
                            global $wp_filesystem;

                            $file_content  = $wp_filesystem->get_contents($attachment_file);
                            $file_content = chunk_split( base64_encode( $file_content ) );

                            $bookingpress_email_content .= "--$boundary\r\n";
                            $bookingpress_email_content .= "Content-Type: {$attachment_type}; name={$attachment_name}\r\n";
                            $bookingpress_email_content .= "Content-Disposition: attachment; filename={$attachment_name}\r\n";
                            $bookingpress_email_content .= "Content-Transfer-Encoding: base64\r\n";
                            $bookingpress_email_content .= "X-Attachment-Id: {$attachment_id}\r\n\r\n";
                            $bookingpress_email_content .= $file_content;
                        }
                        $bookingpress_email_content .= "\r\n--{$boundary}--\r\n";
                    }

                    if (@mail($email_to, $email_subject, $bookingpress_email_content, $bookingpress_email_header_data) ) {
                         $is_mail_sent = 1;
                    }
                    break;
                case 'wp_mail':
                    $bookingpress_email_header_data = 'From: ' . $from_name . '<' . $from_email . "> \r\n";
                    $bookingpress_email_header_data .= 'Reply-To: ' . $reply_to_name . '<' . $reply_to . "> \r\n";
                    $bookingpress_email_header_data .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if(!empty($cc_emails) && is_array($cc_emails)){
                        $bookingpress_email_header_data .= "Cc: ".implode(',', $cc_emails)."\r\n";
                    }
                    
                    if (wp_mail($email_to, $email_subject, $email_content, $bookingpress_email_header_data, $attachments) ) {
                        $is_mail_sent                                = 1;
                    }
                    break;
                case 'smtp':
                    if ( ! empty($this->bookingpress_smtp_host) && ! empty($this->bookingpress_smtp_port) && ! empty($this->bookingpress_smtp_secure) ) {
                        if (version_compare($wp_version, '5.5', '<') ) {
                            include_once ABSPATH . WPINC . '/class-phpmailer.php';
                            include_once ABSPATH . WPINC . '/class-smtp.php';
                            $BookingPressMailer = new PHPMailer();
                        } else {
                            include_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                            include_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                            include_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                            $BookingPressMailer = new PHPMailer\PHPMailer\PHPMailer();
                        }

                        $BookingPressMailer->CharSet   = 'UTF-8';
                        $BookingPressMailer->SMTPDebug = 0; // change this value to 1 for debug
                        $BookingPressMailer->isSMTP();
                        $BookingPressMailer->Host     = $this->bookingpress_smtp_host;
                        $BookingPressMailer->SMTPAuth = true;
                        if( ! empty($this->bookingpress_smtp_username) ){
                            $BookingPressMailer->Username = $this->bookingpress_smtp_username;
                        }
                        if( ! empty($this->bookingpress_smtp_password)  ){
                            $BookingPressMailer->Password = $this->bookingpress_smtp_password;
                        }
                        if (! empty($this->bookingpress_smtp_secure) && $this->bookingpress_smtp_secure != 'Disabled' ) {
                            $BookingPressMailer->SMTPSecure = strtolower($this->bookingpress_smtp_secure);
                        }
                        if ($this->bookingpress_smtp_secure == 'Disabled' ) {
                            $BookingPressMailer->SMTPAutoTLS = false;
                        }
                        $BookingPressMailer->Port = $this->bookingpress_smtp_port;
                        $BookingPressMailer->setFrom($from_email, $from_name);
                        $BookingPressMailer->addReplyTo($reply_to, $reply_to_name);
                        $BookingPressMailer->addAddress($email_to);

                        if(!empty($cc_emails) && is_array($cc_emails)){
                            foreach($cc_emails as $ccemail ){
                                $BookingPressMailer->addCC($ccemail);
                            }
                        }

                        if (! empty($attachments) ) {
                            foreach ( $attachments as $attachment ) {
                                $BookingPressMailer->addAttachment($attachment);
                            }
                        }

                        $BookingPressMailer->isHTML(true);
                        $BookingPressMailer->Subject = $email_subject;
                        $BookingPressMailer->Body    = $email_content;

                        if ($BookingPressMailer->send() ) {
                            $is_mail_sent                                = 1;
                            
                            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Custom Email notification SMTP success response', 'bookingpress_email_notiifcation',$email_content.' --->>>'. $is_mail_sent, $bookingpress_other_debug_log_id);
                        } else {
                            $bookingpressmailer_errorinfo                  = ! empty($BookingPressMailer->ErrorInfo) ? $BookingPressMailer->ErrorInfo : '';
                            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Custom notification SMTP error response', 'bookingpress_email_notiifcation', $bookingpressmailer_errorinfo, $bookingpress_other_debug_log_id);
                        }
                    }
                    break;
                case 'Google_Gmail':
                    if(!empty( $this->bookingpress_gmail_client_id) && !empty($this->bookingpress_gmail_client_secret) && !empty($this->bookingpress_gmail_auth_token) ){

                        require_once BOOKINGPRESS_LIBRARY_DIR . "/gmail/vendor/autoload.php";
                        $bookingpress_redirect_url = get_home_url() .'?page=bookingpress_gmailapi';

                        $this->bookingpress_gmail_auth_token = stripslashes_deep( $this->bookingpress_gmail_auth_token );
                        $gmail_oauth_data = json_decode( $this->bookingpress_gmail_auth_token , true);

                        $client = new Google_Client();
                        $client->setClientId($this->bookingpress_gmail_client_id);
                        $client->setClientSecret( $this->bookingpress_gmail_client_secret );
                        $client->setRedirectUri( $bookingpress_redirect_url);
                        $client->setAccessToken( $gmail_oauth_data );
                        
                        /** Refresh Google API Token */
                        if( $client->isAccessTokenExpired() ){
                            $is_refreshed = $client->refreshToken( $gmail_oauth_data['refresh_token'] );

                            if( !empty( $is_refreshed['error'] ) ){
                                $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 1);
                                return false;
                            }
                            $refresh_token = $gmail_oauth_data['refresh_token'];
                            $gmail_oauth_data =  $client->getAccessToken();
                            if( empty( $gmail_oauth_data['refresh_token'] ) ){
                                $gmail_oauth_data['refresh_token'] = $refresh_token;
                            }
                            $BookingPress->bookingpress_update_settings('bookingpress_gmail_auth', 'notification_setting',wp_json_encode($gmail_oauth_data));
                            $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 0);
                            $client->setAccessToken( $gmail_oauth_data );
                        } else {
                            $verify_token_url = 'https://www.googleapis.com/oauth2/v3/tokeninfo';
                            
                            $args = array(
                                'timeout' => false,
                                'method' => 'GET',
                                'body' => array(
                                    'access_token' => $gmail_oauth_data['access_token']
                                )
                            );
                            $check_access_token = wp_remote_get( $verify_token_url, $args );
                            
                            if( is_wp_error( $check_access_token ) ){
                                global $bookingpress_debug_integration_log_id;
                                return false;
                            }

                            $valid_access_token_code = wp_remote_retrieve_response_code( $check_access_token );

                            if( 200 != $valid_access_token_code ){
                                $validate_access_token = json_decode( wp_remote_retrieve_body( $check_access_token ), 1 );
                                $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 1);
                                return false;
                            } else {
                                $BookingPress->bookingpress_update_settings('bookingpress_gmail_invalid_auth_token', 'notification_setting', 0);
                            }
                            
                        }

                        $service = new Google\Service\Gmail( $client );
                        $user = 'me';
                        $subjectCharset = $charset = 'utf-8';
                        $boundary = uniqid(wp_rand(), true);
                        $strSubject = $email_subject;
                        $bookingpress_email_content_data = 'From: =?' . $subjectCharset . '?B?' . base64_encode($from_name)."?= <".$from_email.">\r\n";
                        $bookingpress_email_content_data .= 'Reply-To: =?' . $subjectCharset . '?B?' . base64_encode($reply_to_name)."?= <".$reply_to.">\r\n";
                        $bookingpress_email_content_data .= "To: ".$email_to."\r\n";                        
                        $bookingpress_email_content_data .= 'Subject: =?' . $subjectCharset . '?B?' . base64_encode($strSubject) . "?=\r\n";
                        $bookingpress_email_content_data .= 'MIME-Version: 1.0' . "\r\n";
                        $bookingpress_email_content_data .= 'Content-type: Multipart/Mixed; boundary="' . $boundary . '"' . "\r\n";
                        $bookingpress_email_content_data .= "\r\n--{$boundary}\r\n";
                        $bookingpress_email_content_data .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
                        $bookingpress_email_content_data .= "Content-Transfer-Encoding: base64" . "\r\n\r\n";
                        $bookingpress_email_content_data .= $email_content . "\r\n";
                        $bookingpress_email_content_data .= "\r\n--{$boundary}--\r\n";

                        if(!empty($cc_emails) && is_array($cc_emails)){
                            $bookingpress_email_content_data .= "Cc:" .  implode(",",$cc_emails)  . "\r\n";
                        }

                        if( !empty( $attachments )){

                            foreach( $attachments as $attachment_file ){
    
                                $attachment_id = wp_rand(100,999);
    
                                $attachment_name = basename( $attachment_file );
                                $attachment_type = mime_content_type( $attachment_file );
                        
                                if (! function_exists('WP_Filesystem') ) {
                                    include_once ABSPATH . 'wp-admin/includes/file.php';
                                }
                        
                                WP_Filesystem();
                                global $wp_filesystem;
                        
                                $file_content  = $wp_filesystem->get_contents($attachment_file);
                                $file_content = chunk_split( base64_encode( $file_content ) );
                                $bookingpress_email_content_data .= "--$boundary\r\n";
                                $bookingpress_email_content_data .= "Content-Type: {$attachment_type}; name={$attachment_name}\r\n";
                                $bookingpress_email_content_data .= "Content-Disposition: attachment; filename={$attachment_name}\r\n";
                                $bookingpress_email_content_data .= "Content-Transfer-Encoding: base64\r\n";
                                $bookingpress_email_content_data .= "X-Attachment-Id: {$attachment_id}\r\n\r\n";
                                $bookingpress_email_content_data .= $file_content;
                            }
                            $bookingpress_email_content_data .= "\r\n--{$boundary}--\r\n";
                        }

                        // The message needs to be encoded in Base64URL
                        $mime = rtrim(strtr(base64_encode($bookingpress_email_content_data), '+/', '-_'), '=');
                        $msg = new Google_Service_Gmail_Message();
                        $msg->setRaw($mime);

                        $return_error_msg = esc_html__('Something went wrong..','bookingpress-appointment-booking');
                        try {
                            $message = $service->users_messages->send('me', $msg);
                            $is_mail_sent                                = 1;
                            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification GMail success response', 'bookingpress_email_notiifcation', $is_mail_sent, $bookingpress_other_debug_log_id);
                        } catch (Exception $e) {
                            $return_error_msg = 'An error occurred: ' . $e->getMessage();
                            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send Email notification GMail error response', 'bookingpress_email_notiifcation', $return_error_msg, $bookingpress_other_debug_log_id);
                        }

                    }
                    break;
                }
        }
        
        /**
         * Get email template details
         *
         * @param  mixed $template_type
         * @param  mixed $notification_name
         * @param  mixed $bookingpress_appointment_data
         * @param  mixed $notification_type
         * @return void
         */
        function bookingpress_get_email_template_details( $template_type, $notification_name, $bookingpress_appointment_data, $notification_type = 'default' )
        {
            global $wpdb, $tbl_bookingpress_notifications, $bookingpress_other_debug_log_id;            

            $bookingpress_args_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Get email template details arguments data', 'bookingpress_email_notiifcation', $bookingpress_args_data, $bookingpress_other_debug_log_id);

            $bookingpress_template_data = array();
            if (! empty($template_type) && ! empty($notification_name) ) {
                $bookingpress_email_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_receiver_type = %s AND bookingpress_notification_status = 1 AND bookingpress_notification_type = %s", $notification_name, $template_type, $notification_type ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm

                $bookingpress_email_data = apply_filters('bookingpress_modify_email_template_notification_data', $bookingpress_email_data, $template_type, $notification_name, $bookingpress_appointment_data, $notification_type);

                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Get email template data', 'bookingpress_email_notiifcation', $bookingpress_email_data, $bookingpress_other_debug_log_id);

                if (! empty($bookingpress_email_data) ) {
                    $bookingpress_email_data['bookingpress_notification_subject'] = apply_filters('bookingpress_modify_email_notification_content', $bookingpress_email_data['bookingpress_notification_subject'], $bookingpress_appointment_data,$notification_name,$template_type);
                    $bookingpress_template_data['notification_subject'] = stripslashes_deep($bookingpress_email_data['bookingpress_notification_subject']);

                    $bookingpress_email_data['bookingpress_notification_message'] = apply_filters('bookingpress_modify_email_notification_content', $bookingpress_email_data['bookingpress_notification_message'], $bookingpress_appointment_data,$notification_name,$template_type);
                    $bookingpress_template_data['notification_message']           = stripslashes_deep($bookingpress_email_data['bookingpress_notification_message']);

                    $bookingpress_template_data = apply_filters('bookingpress_get_email_template_details_filter', $bookingpress_template_data, $bookingpress_email_data);
                }
            }
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Get email template return data', 'bookingpress_email_notiifcation', $bookingpress_template_data, $bookingpress_other_debug_log_id);

            return $bookingpress_template_data;
        }
        
        /**
         * Filter for modify email content
         *
         * @param  mixed $template_content
         * @param  mixed $bookingpress_appointment_data
         * @param  mixed $notification_name
         * @return void
         */
        function bookingpress_modify_email_content_func( $template_content, $bookingpress_appointment_data,$notification_name = '',$template_type='')
        {
            global $BookingPress,$bookingpress_other_debug_log_id;            

            $bookingpress_args_data = func_get_args();

            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Modify email content arguments data', 'bookingpress_email_notiifcation', $bookingpress_args_data, $bookingpress_other_debug_log_id);

            do_action('bookingpress_set_notification_language_data',$template_content, $bookingpress_appointment_data,$notification_name,$template_type);

            $template_content = apply_filters('bookingpress_modify_email_content_details_filter', $template_content, $bookingpress_appointment_data,$notification_name,$template_type);            
            $template_content = $BookingPress->bookingpress_replace_appointment_data($template_content,$bookingpress_appointment_data);
            
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Modify email content return data', 'bookingpress_email_notiifcation', $template_content, $bookingpress_other_debug_log_id);

            return $template_content;
        }
        
        /**
         * Function for send email notification after payment log entry
         *
         * @param  mixed $email_notification_type
         * @param  mixed $inserted_booking_id
         * @param  mixed $bookingpress_customer_email
         * @return void
         */
        function bookingpress_send_after_payment_log_entry_email_notification( $email_notification_type, $inserted_booking_id, $bookingpress_customer_email, $is_send_to_customer = true )
        {

            global $wpdb, $BookingPress, $bookingpress_email_notifications, $bookingpress_other_debug_log_id;

            $bookingpress_args_data = func_get_args();
            do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send email notification after payment log entry arguments data', 'bookingpress_email_notiifcation', $bookingpress_args_data, $bookingpress_other_debug_log_id);

            if (! empty($email_notification_type) ) {
                // Send customer email notification                
                $customer_email_notification_type_arr[] = $email_notification_type;
                $staffmember_email_notification_type_arr[] = $email_notification_type;

                $customer_email_notification_type_arr = apply_filters('bookingpress_send_all_custom_email_notifications',$customer_email_notification_type_arr,'customer',$inserted_booking_id,'email');

                if( '' === $bookingpress_customer_email || empty( $bookingpress_customer_email ) || !filter_var( $bookingpress_customer_email, FILTER_VALIDATE_EMAIL ) ){
                    if( !filter_var( $bookingpress_customer_email, FILTER_VALIDATE_EMAIL ) ){
                        do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send email notification after payment log entry for invalid email', 'bookingpress_email_notiifcation', $bookingpress_customer_email, $bookingpress_other_debug_log_id);
                    }
                }
                else {
                    if($is_send_to_customer == true){
                        foreach ($customer_email_notification_type_arr as $key => $email_notification_name) {
                            $bookingpress_cc_emails = array();
                            $bookingpress_cc_emails = apply_filters('bookingpress_add_customer_cc_email_address', $bookingpress_cc_emails, $email_notification_name,$inserted_booking_id );
                            $is_email_sent = $bookingpress_email_notifications->bookingpress_send_email_notification('customer', $email_notification_name, $inserted_booking_id, $bookingpress_customer_email, $bookingpress_cc_emails);
                        }
                    }
                }

                // Send admin email notification
                $bookingpress_admin_emails = esc_html($BookingPress->bookingpress_get_settings('admin_email', 'notification_setting'));
                do_action('bookingpress_other_debug_log_entry', 'email_notification_debug_logs', 'Send email notification after payment log entry admin emails data', 'bookingpress_email_notiifcation', $bookingpress_admin_emails, $bookingpress_other_debug_log_id);
                
                $staffmember_email_notification_type_arr = apply_filters('bookingpress_send_all_custom_email_notifications',$staffmember_email_notification_type_arr,'employee',$inserted_booking_id,'email');

                foreach ($staffmember_email_notification_type_arr as $key => $email_notification_name) {
                    $bookingpress_admin_emails_data = apply_filters('bookingpress_filter_admin_email_data', $bookingpress_admin_emails, $inserted_booking_id, $email_notification_name);
                    if (! empty($bookingpress_admin_emails_data) ) {
                        $bookingpress_cc_emails = array();
                        $bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $email_notification_name);
                        $bookingpress_admin_emails_data = explode(',', $bookingpress_admin_emails_data);
                        foreach ( $bookingpress_admin_emails_data as $admin_email_key => $admin_email_val ) {                            
                            $bookingpress_email_notifications->bookingpress_send_email_notification('employee', $email_notification_name, $inserted_booking_id, $admin_email_val, $bookingpress_cc_emails);
                        }                        
                    }
                }
            }
        }

    }

    global $bookingpress_email_notifications;
    $bookingpress_email_notifications = new bookingpress_email_notifications();
}

<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_spam_protection') ) {
    class bookingpress_spam_protection Extends BookingPress_Core
    {
        function __construct()
        {
            add_action('wp_ajax_bookingpress_generate_spam_captcha', array( $this, 'bookingpress_generate_spam_captcha_func' ));
            add_action('wp_ajax_nopriv_bookingpress_generate_spam_captcha', array( $this, 'bookingpress_generate_spam_captcha_func' ));

            add_action('init', array( $this, 'bookingpress_start_session' ), 1);

            // add_filter('bookingpress_validate_spam_protection', array($this, 'bookingpress_validate_spam_protection_func'), 10, 2);
        }
        
        /**
         * Validate Spam Requests
         *
         * @param  mixed $response_data   Return Data
         * @param  mixed $posted_data     POST request received data
         * @return void
         */
        function bookingpress_validate_spam_protection_func( $response_data, $posted_data )
        {
            if (! empty($_SESSION['bpa_filter_input']) && ! empty($posted_data['spam_captcha']) && ( $_SESSION['bpa_filter_input'] == md5(esc_html($posted_data['spam_captcha'])) ) ) {

                $formSubmitTime = (int) $posted_data['stime'] - 14921;
                $currentTime    = time();

                $fillOutTime = $formSubmitTime - $currentTime;

                // If form submit time is less than 3 seconds.
                if ($fillOutTime < 3 ) {
                    $response_data['is_spam'] = 0;
                }
            }

            return $response_data;
        }
        
        /**
         * Add Spam Validation Captcha to Session
         *
         * @return void
         */
        function bookingpress_generate_spam_captcha_func()
        {
            global $wpdb;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            $response['updated_nonce'] = '';
            if (! $bpa_verify_nonce_flag ) {

                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking');
                $response['updated_nonce'] = esc_html(wp_create_nonce('bpa_wp_nonce'));
                $response['updated_ics_nonce'] = esc_html( wp_create_nonce( 'bookingpress_calendar_ics') );
                wp_send_json($response);
                die();
            }

            $bookingpress_spam_captcha    = $this->bookingpress_generate_captcha_code(12);
            $_SESSION['bpa_filter_input'] = md5($bookingpress_spam_captcha);

            $response['variant']     = 'success';
            $response['title']       = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']         = esc_html__('Captcha generated successfully.', 'bookingpress-appointment-booking');
            $response['captcha_val'] = $bookingpress_spam_captcha;
            wp_send_json($response);
            die();
        }
        
        /**
         * Generate Spam Validation Captcha Code
         *
         * @param  mixed $length   Code characters length
         * @return void
         */
        function bookingpress_generate_captcha_code( $length )
        {
            $charLength = round($length * 0.8);
            $numLength  = round($length * 0.2);
            $keywords   = array(
            array(
            'count' => $charLength,
            'char'  => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            ),
            array(
            'count' => $numLength,
            'char'  => '0123456789',
            ),
            );
            $temp_array = array();
            foreach ( $keywords as $char_set ) {
                for ( $i = 0; $i < $char_set['count']; $i++ ) {
                    $temp_array[] = $char_set['char'][ wp_rand(0, strlen($char_set['char']) - 1) ];
                }
            }
            shuffle($temp_array);
            return implode('', $temp_array);
        }

        
        /**
         * Starts session if not started
         *
         * @return void
         */
        function bookingpress_start_session()
        {
            if (version_compare(PHP_VERSION, '7.0.0') >= 0 ) {
                if (( function_exists('session_status') && session_status() == PHP_SESSION_NONE && ! is_admin() ) ) {
                    @session_start(
                        array(
                        'read_and_close' => false,
                        )
                    );
                }
            } elseif (version_compare(PHP_VERSION, '5.4.0') >= 0 ) {
                if (( function_exists('session_status') && session_status() == PHP_SESSION_NONE && ! is_admin() ) ) {
                    @session_start();
                }
            } else {
                if (( session_id() == '' && ! is_admin() ) ) {
                    @session_start();
                }
            }
        }
    }
}

global $bookingpress_spam_protection;
$bookingpress_spam_protection = new bookingpress_spam_protection();

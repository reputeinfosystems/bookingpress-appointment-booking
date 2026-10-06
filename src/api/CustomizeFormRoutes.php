<?php

namespace BookingPress\api;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CustomizeFormRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {

        /**
         * Load booking form customize data.
         */
        register_rest_route( 'bookingpress-app/v1', '/customize/booking-form',[
            'methods'             => 'POST',
            'callback'            => [ $this, 'load_bookingform_data' ],
            'permission_callback' => $this->permission_callback_for( 'load_customization' ),
        ]);
    

        /**
         * Save booking form customize settings.
         */
        register_rest_route( 'bookingpress-app/v1', '/customize/booking-form/save',[
            'methods'             => 'POST',
            'callback'            => [ $this, 'save_bookingform_settings' ],
            'permission_callback' => $this->permission_callback_for( 'save_form_settings' ),
        ]);

        /**
         * Load My Bookings customize data.
         */
        register_rest_route('bookingpress-app/v1','/customize/my-booking',[
            'methods'             => 'POST',
            'callback'            => [ $this, 'load_my_booking_data' ],
            'permission_callback' => $this->permission_callback_for( 'load_customization' ),
        ]);

        /**
         * Save My Bookings customize settings.
         */
        register_rest_route( 'bookingpress-app/v1', '/customize/my-booking/save',[
            'methods'             => 'POST',
            'callback'            => [ $this, 'save_my_booking_settings' ],
            'permission_callback' => $this->permission_callback_for( 'save_mybooking_settings' ),
        ]);

        /**
         * Dismiss caching notice.
         */
        register_rest_route( 'bookingpress-app/v1','/customize/dismiss-caching-notice',[
            'methods'             => 'POST',
            'callback'            => [ $this, 'dismiss_caching_notice' ],
            'permission_callback' => $this->permission_callback_for( 'load_customization' ),
        ]);
    }

    public function load_bookingform_data( $request ) {

        global $bookingpress_customize;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        $response = $bookingpress_customize->bookingpress_load_bookingform_data_func();
        return new \WP_REST_Response( $response, 200 );
    }

    public function save_bookingform_settings( $request ) {

        global $bookingpress_customize;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_POST['_wpnonce']    = $_REQUEST['_wpnonce'];
        $params = [
            'tab_container_data',
            'category_container_data',
            'service_container_data',
            'timeslot_container_data',
            'colorpicker_values',
            'font_values',
            'booking_form_settings',
            'summary_container_data',
            'front_label_edit_data',
            'waiting_list_container_data',
            'recurring_appointment_container_data',
            'language_data',
        ];

       foreach ( $params as $param ) {
            $value = $request->get_param( $param );

            if ( is_array( $value ) ) {
                array_walk_recursive( $value,
                    function ( &$item ) {
                        if ( is_bool( $item ) ) {
                            $item = $item ? 'true' : 'false';
                        }
                    }
                );
            }

            $_POST[ $param ] = $value;
            $_REQUEST[ $param ] = $value;
        }

        // The existing function uses wp_send_json and die, so we need to catch output
        ob_start();
        $bookingpress_customize->bookingpress_save_form_settings_func();
        $output = ob_get_clean();

        // Parse the JSON output from the function
        $response = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $response = [
                'variant' => 'error',
                'title' => esc_html__('Error', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('Failed to process save request', 'bookingpress-appointment-booking'),
            ];
        }

        return new \WP_REST_Response( $response, 200 );
    }

    public function load_my_booking_data( $request ) {

        global $bookingpress_customize;

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        $response = $bookingpress_customize->bookingpress_load_my_booking_data_func();

        return new \WP_REST_Response( $response, 200 );
    }

    public function save_my_booking_settings( $request ) {

        global $bookingpress_customize;
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $_POST['_wpnonce']    = $_REQUEST['_wpnonce'];

        $params = [
            'my_booking_selected_colorpicker_values',
            'my_booking_selected_font_values',
            'my_booking_field_settings',
            'delete_account_content',
        ];

        foreach ( $params as $param ) {
            $value = $request->get_param( $param );
            if ( is_array( $value ) ) {
                array_walk_recursive(
                    $value,
                    function ( &$item ) {
                        if ( is_bool( $item ) ) {
                            $item = $item ? 'true' : 'false';
                        }
                    }
                );
            }
            $_POST[ $param ]    = $value;
            $_REQUEST[ $param ] = $value;
        }

        $response = $bookingpress_customize->bookingpress_save_my_booking_settings_func();
        return new \WP_REST_Response( $response, 200 );
    }

    public function dismiss_caching_notice( $request ) {

        update_option( 'bookingpress_disabled_caching_notice', true );
        $response = [
            'variant' => 'success',
            'title'   => esc_html__( 'Success', 'bookingpress-appointment-booking' ),
            'msg'     => esc_html__( 'Notice dismissed sucessfully.', 'bookingpress-appointment-booking' ),
        ];

        return new \WP_REST_Response( $response, 200 );
    }
}


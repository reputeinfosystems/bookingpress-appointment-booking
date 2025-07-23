<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('BookingpressFormWidget') ) {
    class BookingpressFormWidget extends WP_Widget
    {

        function __construct()
        {
            parent::__construct(
                'bookingpress_frontend_widget',
                esc_html__('Booking Forms - WordPress Booking Plugin', 'bookingpress-appointment-booking'),
                array( 'description' => esc_html__('Display frontend booking form for book appointments', 'bookingpress-appointment-booking') )
            );
        }

        public function widget( $args, $instance )
        {
            if (! is_admin() ) {
                global $BookingPress;
                $BookingPress->set_front_css(1);
                $BookingPress->set_front_js(1);
            }
            echo $args['before_widget']; //phpcs:ignore 
            echo "<div class='bpa-frontend-main-container--widget'>";
            echo do_shortcode('[bookingpress_form]');
            echo '</div>';
            echo $args['after_widget']; //phpcs:ignore 
        }
        public function form( $instance )
        {
        }
    }
    if (class_exists('WP_Widget') ) {
        function Bookingpress_register_form_widgets()
        {
            register_widget('BookingpressFormWidget');
        }
        add_action('widgets_init', 'Bookingpress_register_form_widgets');
    }
}
if (! class_exists('BookingpressMybookingWidget') ) {
    class BookingpressMybookingWidget extends WP_Widget
    {

        function __construct()
        {
            parent::__construct(
                'bookingpress_my_booking_widget',
                esc_html__('Customer Panel - BookingPress Appointment Plugin', 'bookingpress-appointment-booking'),
                array( 'description' => esc_html__('Display booked appointments', 'bookingpress-appointment-booking') )
            );
        }
        public function widget( $args, $instance )
        {
            if (! is_admin() ) {
                global $BookingPress;
                $BookingPress->set_front_css(1);
                $BookingPress->set_front_js(1);
            }
            echo $args['before_widget']; //phpcs:ignore 
            echo "<div class='bpa-frontend-main-container--widget'>";
            echo do_shortcode('[bookingpress_my_appointments]');
            echo '</div>';
            echo $args['after_widget']; //phpcs:ignore 
        }
        public function form( $instance )
        {

        }
    }
    if (class_exists('WP_Widget') ) {
        function Bookingpress_register_my_booking_widgets()
        {
            register_widget('BookingpressMybookingWidget');
        }
        add_action('widgets_init', 'Bookingpress_register_my_booking_widgets');
    }
}

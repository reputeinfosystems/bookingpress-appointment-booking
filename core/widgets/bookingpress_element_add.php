<?php

namespace ElementorBookingpress\Widgets;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (! defined('ABSPATH') ) {
    exit;
}

function bookingpress_get_services_based_on_category(){

    global $BookingPress;
    
    $retrieve_services = $BookingPress->bookingpress_retrieve_all_services( '', '', '');

    $bpa_services = array();
    foreach( $retrieve_services as $service_id => $service_data ){
        $bpa_services[ $service_id ] = $service_data['bookingpress_service_name'];
    }

    return $bpa_services;
}

if (! class_exists('bookingpress_form_shortcode') ) {

    class bookingpress_form_shortcode extends Widget_Base
    {

        public function get_name()
        {
            return 'Booking Forms - WordPress Booking Plugin';
        }
        public function get_title()
        {
            return '<span style="color:red">'.esc_html__( '(Deprecated)', 'bookingpress-appointment-booking').'</span><br/>'.esc_html__('Booking Forms - BookingPress', 'bookingpress-appointment-booking') . '<style>
            .bookingpress_element_icon{
                display: inline-block;
                width: 35px;
                height: 24px;
                background-image: url(' . BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png);
                background-repeat: no-repeat;
                background-position: bottom;
            }
            </style>';
        }
        public function get_icon()
        {
            return 'bookingpress_element_icon';
        }
        public function get_categories()
        {
            return array( 'general' );
        }
        protected function register_controls() {

            $this->start_controls_section(
                'booking_form',
                [
                    'label' => esc_html__( 'BookingPress', 'bookingpress-appointment-booking' ),
                ]
            );
           
            $this->add_control(
                'bpa_select_service',
                [
                    'label' => esc_html__( 'Select Service', 'bookingpress-appointment-booking'),
                    'type' => Controls_Manager::SELECT2,
                    'default' => array(),
                    'multiple' => true,
                    'options' => bookingpress_get_services_based_on_category( ),
                    'label_block' => true,
                    
                ]
            );

        }
        protected function render() {

            $settings = $this->get_settings_for_display();

            $bpa_service_id = 0;
            $service_id = array();
            if( !empty( $settings['bpa_select_service'])){

                foreach( $settings['bpa_select_service'] as $settings_val ){
                    $service_id[$bpa_service_id] = $settings_val;
                    $bpa_service_id++;
                }
            }
            $bpa_service_ids = implode(',', $service_id);

            if( !empty($bpa_service_ids)) {
                echo '[bookingpress_form service='.esc_attr($bpa_service_ids).']';
            } else {
                echo '[bookingpress_form]';
            }

        }

    }
}
if (! class_exists('bookingpress_my_booking') ) {

    class bookingpress_my_booking extends Widget_Base
    {

        public function get_name()
        {
            return 'Customer Panel - BookingPress Appointment Plugin';
        }
        public function get_title()
        {
            return '<span style="color:red">'.esc_html__( '(Deprecated)', 'bookingpress-appointment-booking').'</span><br/>'.esc_html__('Customer Panel - BookingPress', 'bookingpress-appointment-booking') . '<style>
        .bookingpress_element_icon{
            display: inline-block;
            width: 35px;
            height: 24px;
            background-image: url(' . BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png);
            background-repeat: no-repeat;
            background-position: bottom;
        }
        </style>
        ';
        }
        public function get_icon()
        {
            return 'bookingpress_element_icon';
        }
        public function get_categories()
        {
            return array( 'general' );
        }
        protected function render()
        {
            echo '[bookingpress_my_appointments]';
        }

    }
}


<?php

if( !defined( 'ABSPATH' ) ) { exit; }

use \Elementor\Controls_Manager;

class BookingPress_Elementor_Customer_Panel_Widget extends \Elementor\Widget_Base{

    public function get_name(){
        return 'bookingpress_elementor_customer_panel_widget';
    }

    public function get_title(){
        return esc_html__('Customer Panel - BookingPress', 'bookingpress-appointment-booking') . '<style>
        .bookingpress_element_icon{
            display: inline-block;
            width: 35px;
            height: 24px;
            background-image: url(' . BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png);
            background-repeat: no-repeat;
            background-position: 0 -5px;
        }
        </style>';
    }

    public function get_icon(){
        return 'bookingpress_element_icon';
    }

    public function get_categories(){
        return [ 'general' ];
    }

    public function get_keywords(){
        return [ 'booking', 'calendar', 'schedule', 'appointment', 'customer' ];
    }

    protected function render() {
        echo '[bookingpress_my_appointments]';
    }

}
<?php

if( !defined( 'ABSPATH' ) ){
    die;
}

class bookingpress_elementor_widget{

    function __construct(){
        add_action( 'elementor/widgets/register', array( $this, 'bookingpress_register_elementor_widget' ));
    }

    function bookingpress_register_elementor_widget( $widgets_manager ){

        require_once __DIR__ . '/bookingpress-elementor-form-widget.php';
        require_once __DIR__ . '/bookingpress-elementor-customer-panel-widget.php';

        $widgets_manager->register( new \BookingPress_Elementor_Form_Widget() );
        $widgets_manager->register( new \BookingPress_Elementor_Customer_Panel_Widget() );
    }

}

new bookingpress_elementor_widget();

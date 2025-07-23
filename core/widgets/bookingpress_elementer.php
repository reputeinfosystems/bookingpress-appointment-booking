<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_element_controller') ) {
    class bookingpress_element_controller
    {

        function __construct()
        {
            add_action('plugins_loaded', array( $this, 'bookingpress_element_widget' ));

        }
        function bookingpress_element_widget()
        {
            if (! did_action('elementor/loaded') ) {
                return;
            }
            include_once BOOKINGPRESS_WIDGET_DIR . '/bookingpress_elementer_element.php';
        }
    }
}

$bookingpress_element_controller = new bookingpress_element_controller();

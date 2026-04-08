<?php

namespace BookingPress;

if( !defined( 'ABSPATH' ) ) {
    exit;
}

use BookingPress\admin\Header;
use BookingPress\admin\Calendar;
use BookingPress\api\CalendarRoutes;
use BookingPress\api\CustomerRoutes;
use BookingPress\api\TimeRoutes;
use BookingPress\api\AppointmentRoutes;

class BookingPressLoader{

    public function __construct(){
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public static function init(){

        if( class_exists( 'BookingPressPro\BookingPressLoader' ) ){
            return;
        }

        $init_menu = true;
        if( is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
            global $bookingpress_pro_version;
            if( version_compare( $bookingpress_pro_version, '5.2', '<' ) ){
                $init_menu = false;
            }
        }

        if( true == $init_menu ){
            \BookingPress\admin\AdminMenu::init();
        }

        Header::init();
        Calendar::init();
        new CalendarRoutes();
        new CustomerRoutes();
        new TimeRoutes();
        new AppointmentRoutes();
        
    }
}

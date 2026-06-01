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
use BookingPress\api\DashboardRoutes;
use BookingPress\api\AddonsRoutes;
use BookingPress\api\CustomerPageRoutes;

use BookingPress\admin\Dashboard;
use BookingPress\admin\AddonsList;
use BookingPress\admin\Customer;

use BookingPress\frontend\BookingForm;

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
        Dashboard::init();
        AddonsList::init();
        Customer::init();
        new CalendarRoutes();
        new CustomerRoutes();
        new TimeRoutes();
        new AppointmentRoutes();
        new DashboardRoutes();
        new AddonsRoutes();
        new CustomerPageRoutes();
	
	BookingForm::init();
        
    }
}

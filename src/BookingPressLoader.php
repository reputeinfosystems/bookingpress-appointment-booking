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
use BookingPress\api\GrowthToolsRoutes;
use BookingPress\api\SettingsRoutes;
use BookingPress\admin\Appointments;
use BookingPress\api\HelpDrawerRoutes;

use BookingPress\admin\Dashboard;
use BookingPress\admin\AddonsList;
use BookingPress\admin\Customer;
use BookingPress\admin\GrowthTools;
use BookingPress\admin\Settings;

use BookingPress\frontend\BookingForm;

// Vue3 greenfield path — see docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md
use BookingPress\Vue3\Routing as Vue3Routing;

class BookingPressLoader{

    public function __construct(){
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public static function init(){

        // M7: Vue3 path is wired BEFORE the Pro early-return so the new form
        // can serve Pro 6.0+ installs too. The `should_route_to_v3()` gate
        // inside `Vue3Routing::init()` decides whether to register
        // `[bookingpress_form]`. When Pro < 6.0 is active, the gate
        // refuses and Pro's existing override of the shortcode keeps
        // serving the Vue 2 form. When Pro 6.0+ is active, Pro mirrors
        // this call from its own loader (M9) so the wiring is idempotent.
        //
        // Note: REST routes and customize-CSS listeners are also wired
        // here. They are harmless on Pro-active installs because they sit
        // under our own namespace.
        Vue3Routing::init();

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

        if( !is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
            Appointments::init();
            new AppointmentRoutes();
        }

        Header::init();
        Calendar::init();
        Dashboard::init();
        AddonsList::init();
        Customer::init();
        GrowthTools::init();
        Settings::init();
        new CalendarRoutes();
        new CustomerRoutes();
        new TimeRoutes();
        
        new DashboardRoutes();
        new AddonsRoutes();
        new CustomerPageRoutes();
        new GrowthToolsRoutes();
        new SettingsRoutes();
        new HelpDrawerRoutes();
	
	    BookingForm::init();
        
    }

    public static function bpa_safe_maybe_unserialize( $value = '', $max_depth = 128 ) {
        if( !is_string( $value ) || !is_serialized( $value ) ){
            return $value;
        }

        $trimmed_value = trim( $value );

        if( '' === $trimmed_value ){
            return $value;
        }

        $max_depth = absint( $max_depth );

        if ( $max_depth < 1 ) {
            $max_depth = 128;
        }

        /*
         * Keep this reasonably low for user-controlled data.
         * Increase only if you have a proven legitimate deep data structure.
         */
        if ( $max_depth > 512 ) {
            $max_depth = 512;
        }

        /*
        * Reject obvious object/custom/enum payloads before unserialize().
        *
        * O = object
        * C = custom Serializable object
        * E = enum; PHP notes allowed_classes does not affect enumerations.
        */
        if ( preg_match( '/(^|[;{}])(?:O|C|E):\d+:/', $trimmed_value ) ) {
            return $value;
        }

        $value = false;

        // Avoid leaking warnings from malformed serialized payloads.
        set_error_handler(
            static function () {
                return true;
            }
        );

        $options = [
            'allowed_class' => false
        ];

        $result = @unserialize( $trimmed_value, $options );

        // Valid serialized false.
        if ( false === $result && $trimmed !== 'b:0;' ) {
            return '';
        }

        // Reject any object, including nested __PHP_Incomplete_Class objects.
        if ( self::bpa_contains_object_recursive( $result, 0, $max_depth ) ) {
            return '';
        }

        return $result;
        
    }

    public static function bpa_contains_object_recursive( $value, $depth = 0, $max_depth = 64 ){
        if ( $depth > $max_depth ) {
            return true; // Treat excessive nesting as unsafe.
        }

        if ( is_object( $value ) ) {
            return true;
        }

        if ( ! is_array( $value ) ) {
            return false;
        }

        foreach ( $value as $item ) {
            if ( self::bpa_contains_object_recursive( $item, $depth + 1, $max_depth ) ) {
                return true;
            }
        }

        return false;

    }
}

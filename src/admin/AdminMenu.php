<?php
namespace BookingPress\admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class AdminMenu {
    
    public static function init() {
        // Higher priority (25) ensures it runs before individual page initializations if needed
        //add_action( 'admin_menu', [ __CLASS__, 'register_menus' ], 25 );
    }

    public static function register_menus() {
        global $bookingpress_slugs, $BookingPress;
        
        // Define all menus here

        $bookingpress_is_wizard_complete = get_option('bookingpress_lite_wizard_complete');

        $place = $BookingPress->get_free_menu_position(26.1, 0.3);

        if(empty($bookingpress_is_wizard_complete) || $bookingpress_is_wizard_complete == 0){
            /** Do Nothing For now */
        }else{
            /* $bookingpress_menu_hook = add_menu_page(esc_html__('BookingPress', 'bookingpress-appointment-booking'), esc_html__('BookingPress', 'bookingpress-appointment-booking'), 'bookingpress', $bookingpress_slugs->bookingpress, array( $this, 'route' ), BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png', $place); */
            /* add_menu_page(
                esc_html__('BookingPress', 'bookingpress-appointment-booking'), 
                esc_html__('BookingPress', 'bookingpress-appointment-booking'), 
                'bookingpress', 
                $bookingpress_slugs->bookingpress, 
                [ Dashboard::class, 'render_page' ],
                BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png', 
                $place
            );

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Dashboard', 'bookingpress-appointment-booking'), esc_html__('Dashboard', 'bookingpress-appointment-booking'), 'bookingpress', $bookingpress_slugs->bookingpress); */
        }

        

        $slug = 'bookingpress-calendar';

        if (! function_exists('is_plugin_active') ) {
            include ABSPATH . '/wp-admin/includes/plugin.php';
        }

        if( is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
            $pro_version = get_option( 'bookingpress_pro_version' );
            if( version_compare( $pro_version, '5.5', '<' ) ){
                $slug = 'bookingpress_calendar';
            }
        }
        
        /* $menus = [
            'calendar' => [
                'parent'        => $bookingpress_slugs->bookingpress,
                'page_title'    => esc_html__( 'Calendar', 'bookingpress-appointment-booking' ),
                'menu_title'    => esc_html__( 'Calendar', 'bookingpress-appointment-booking' ),
                'capability'    => 'bookingpress_calendar',
                'menu_slug'     => $slug,
                'callback'      => [ Calendar::class, 'render_page' ],
                'position'      => 1
            ],
            'customer' => [
                'parent'        => $bookingpress_slugs->bookingpress,
                'page_title'    => esc_html__( 'Customers', 'bookingpress-appointment-booking' ),
                'menu_title'    => esc_html__( 'Customers', 'bookingpress-appointment-booking' ),
                'capability'    => 'bookingpress_customers',
                'menu_slug'     => 'bookingpress_customers',
                'callback'      => [ Customer::class, 'render_page' ],
                'position'      => 2
            ],
            'addons' => [
                'parent'        => $bookingpress_slugs->bookingpress,
                'page_title'    => esc_html__( 'Add-ons', 'bookingpress-appointment-booking' ),
                'menu_title'    => esc_html__( 'Add-ons', 'bookingpress-appointment-booking' ),
                'capability'    => 'bookingpress_addons',
                'menu_slug'     => 'bookingpress_addons',
                'callback'      => [ AddonsList::class, 'render_page' ],
                'position'      => 9
            ],
            // Add other pages like 'appointments', 'services' etc. here
        ]; */

        /* foreach ( $menus as $menu ) {
            add_submenu_page( 
                $menu['parent'], 
                $menu['page_title'], 
                $menu['menu_title'], 
                $menu['capability'], 
                $menu['menu_slug'], 
                $menu['callback'],
                $menu['position'] ?? null
            );
        } */
    }
}

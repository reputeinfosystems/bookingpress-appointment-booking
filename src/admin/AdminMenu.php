<?php
namespace BookingPress\admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class AdminMenu {
    
    public static function init() {
        // Higher priority (25) ensures it runs before individual page initializations if needed
        add_action( 'admin_menu', [ __CLASS__, 'register_menus' ], 27 );
    }

    public static function register_menus() {
        global $bookingpress_slugs;
        
        // Define all menus here

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
        
        $menus = [
            'calendar' => [
                'parent'        => $bookingpress_slugs->bookingpress,
                'page_title'    => esc_html__( 'Calendar', 'bookingpress-appointment-booking' ),
                'menu_title'    => esc_html__( 'Calendar', 'bookingpress-appointment-booking' ),
                'capability'    => 'bookingpress_calendar',
                'menu_slug'     => $slug,
                'callback'      => [ Calendar::class, 'render_page' ],
                'position'      => 1
            ],
            // Add other pages like 'appointments', 'services' etc. here
        ];

        foreach ( $menus as $menu ) {
            add_submenu_page( 
                $menu['parent'], 
                $menu['page_title'], 
                $menu['menu_title'], 
                $menu['capability'], 
                $menu['menu_slug'], 
                $menu['callback'],
                $menu['position'] ?? null
            );
        }
    }
}

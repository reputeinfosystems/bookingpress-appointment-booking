<?php

namespace BookingPress\admin;

class Header extends Base{

    public static function init(){
        parent::init();
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'bookingpress_print_script_data' ] );
    }

    public static function bookingpress_scoped_pages(){

        $scoped_hooks = [
            'bookingpress',
            'bookingpress-calendar',
            'bookingpress_addons',
            'bookingpress_customers',
        ];

        return apply_filters( 'bookingpress_scoped_pages', $scoped_hooks );

    }

    public static function enqueue_assets( $hook ){

        $scoped_pages = self::bookingpress_scoped_pages();

        if( !empty( $_REQUEST['page'] ) && !in_array( $_REQUEST['page'], $scoped_pages ) ){
            return;
        }

        wp_register_script_module(
            'bookingpress-sidemenu-drawer',
            BOOKINGPRESS_URL . '/src/assets/js/drawer-loader.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-sidemenu-drawer' );

    }

    public static function bookingpress_scoped_nonces(){
        $scoped_nonces = [
            'bookingpress_page_bookingpress'            => 'bpa_dashboard_wp_nonce',
            'bookingpress_page_bookingpress-calendar'   => 'bpa_calendar_wp_nonce',
            'bookingpress_page_bookingpress_addons'     => 'bpa_addons_wp_nonce',
            'bookingpress_page_bookingpress_customers'  => 'bpa_customers_wp_nonce',
        ];

        return apply_filters( 'bookingpress_scoped_nonces', $scoped_nonces );
    }

    public static function bookingpress_print_script_data( $hook ){
        
        $scoped_pages = self::bookingpress_scoped_pages();
        
        if( !empty( $_REQUEST['page'] ) && !in_array( $_REQUEST['page'], $scoped_pages ) ){
            return;
        }

        $nonces = self::bookingpress_scoped_nonces();

        $config = [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'rest_url' => rest_url( 'bookingpress-app/v1' ),
            'rest_nonce' => wp_create_nonce( 'wp_rest' ),
            'notification_timeout' => 1500, //1.5 seconds
            'is_rtl'     => is_rtl(),
            'nonce'    => !empty( $nonces[$hook] ) ? wp_create_nonce( $nonces[$hook] ) : wp_create_nonce( 'bpa_wp_nonce' ),
            '_wpnonce' => wp_create_nonce( 'bpa_wp_nonce' ),
        ];

        wp_print_inline_script_tag(
            'window.BookingPressConfig = ' . wp_json_encode( $config ) . ';',
        );
    }

    public static function bookingpress_verify_capabilities( $cap ){

        $is_verified = true;

        if( class_exists( '\BookingPressPro\admin\Header') && method_exists( '\BookingPressPro\admin\Header', 'verify_capability' ) ){
            $is_verified = \BookingPressPro\admin\Header::verify_capability( $cap );
        }

        if( current_user_can( $cap ) && $is_verified ){
            return true;
        } else {
            return false;
        }

    }
}
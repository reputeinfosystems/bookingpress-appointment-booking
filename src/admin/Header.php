<?php

namespace BookingPress\admin;

class Header{

    public static function init(){
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'bookingpress_print_script_data' ] );
    }

    public static function bookingpress_scoped_pages(){

        $scoped_hooks = [
            'bookingpress-calendar'
        ];

        return apply_filters( 'bookingpress_scoped_pages', $scoped_hooks );

    }

    public static function bookingpress_scoped_nonces(){
        $scoped_nonces = [
            'bookingpress_page_bookingpress-calendar'   => 'bpa_calendar_wp_nonce'
        ];

        return apply_filters( 'bookingpress_scoped_nonces', $scoped_nonces );
    }

    public static function bookingpress_print_script_data( $hook ){
        
        $scoped_pages = self::bookingpress_scoped_pages();

        $is_scoped_page = array_map( function( $page ) use ( $hook ) {
            return strpos( $hook, $page ) === 0;
        }, $scoped_pages );

        if( !$is_scoped_page ){
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
}
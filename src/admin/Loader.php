<?php

namespace BookingPress\admin;

if( !defined('ABSPATH') ) {
    exit;
}

class Loader extends Base {

    protected static $appointment_slugs = [
        'bookingpress',
        'bookingpress-calendar'
    ];


    public static function init() {
        parent::init();
    }
    
    public static function enqueue_assets( $hook ){

        $scoped_pages = static::$appointment_slugs;

        $is_scoped_page = array_map( function( $page ) use ( $hook ) {
            return strpos( $hook, $page ) === 0;
        }, $scoped_pages );

    }

    public static function render_page(){
        
    }

}
<?php

namespace BookingPress\admin;

if( !defined( 'ABSPATH' ) ) {
    exit;
}

class GrowthTools extends Base{

    protected static $slug = 'bookingpress_growth_tools';

    public static function init(){

        parent::init();
        add_filter( 'script_module_data_bookingpress-growth-tools-loader', [ __CLASS__, 'bookingpress_add_script_module_data' ] );    
    }

    public static function bookingpress_add_script_module_data( $growth_tools_data ){

        
        return $growth_tools_data;
    }
    public static function enqueue_assets( $hook ){
        if ( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'bookingpress_growth_tools' ) {
            return;
        }

         wp_register_script_module(
            'vue',
            BOOKINGPRESS_URL . '/src/assets/js/vue.min.js',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
            'bookingpress-ui',
            BOOKINGPRESS_URL . '/src/assets/js/bookingpress-ui.min.js',
            ['vue'],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
            'bookingpress-growth-tools-loader',
            BOOKINGPRESS_URL . '/src/assets/js/growth-tools-loader.js',
            ['bookingpress-ui'],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-growth-tools-loader' );

        wp_enqueue_style(
            'bookingpress-ui',
            BOOKINGPRESS_URL . '/src/assets/css/bookingpress-ui.min.css',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_style(
            'bookingpress-admin-common',
            BOOKINGPRESS_URL . '/src/assets/css/bookingpress_admin_common.css',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_style( 'bookingpress_admin_css' );
        wp_enqueue_style( 'bookingpress_components_css' );
        wp_enqueue_style( 'bookingpress_fonts_css' );

        wp_enqueue_style(
            'bookingpress-common',
            BOOKINGPRESS_URL . '/src/assets/css/common.css',
            [],
            BOOKINGPRESS_VERSION
        );
    }

    public static function render_page() {
        self::render_view( 'GrowthTools', [
            'title' => esc_html__( 'Growth Plugins', 'bookingpress-appointment-booking' )
        ] );
    }

}
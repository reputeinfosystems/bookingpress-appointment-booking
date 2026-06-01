<?php

namespace BookingPress\admin;

if( !defined( 'ABSPATH' ) ) {
    exit;
}

class AddonsList extends Base{
    
    protected static $slug = 'bookingpress_addons';

    public static function init(){
        parent::init();

        add_filter( 'script_module_data_bookingpress-addons-loader', [ __CLASS__, 'bookingpress_add_script_module_data' ] );
        
    }

    public static function bookingpress_add_script_module_data( $addons_data ){
        $addons_data['wp_nonce'] = wp_create_nonce( 'bpa_wp_nonce' );
        $addons_data['bpa_lite_addons'] = (object) [];
        return $addons_data;
    }

    public static function enqueue_assets( $hook ){
        if ( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'bookingpress_addons' ) {
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
            'bookingpress-addons-loader',
            BOOKINGPRESS_URL . '/src/assets/js/addons-loader.js',
            ['bookingpress-ui'],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-addons-loader' );

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
        self::render_view( 'AddonsList', [
            'title' => esc_html__( 'Add-ons', 'bookingpress-appointment-booking' )
        ] );
    }

}
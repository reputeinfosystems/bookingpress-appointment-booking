<?php
namespace BookingPress\admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wizard extends Base {

    public static function init() {
        parent::init();
        add_filter( 'admin_body_class', [ __CLASS__, 'add_wizard_body_class' ] );
        add_filter( 'script_module_data_bookingpress-wizard-loader', [ __CLASS__, 'add_wizard_script_module_data' ] );
        add_filter( 'exclude_additional_slug_css_js_outside_module', [ __CLASS__, 'bookingpress_exclude_wizard_from_legacy_scripts' ] );
        add_filter( 'bookingpress_additional_module_exculde', [ __CLASS__, 'bookingpress_exclude_wizard_from_legacy_scripts' ] );
    }

    public static function add_wizard_body_class( $classes ) {
        if ( ! empty( $_REQUEST['page'] ) && ( $_REQUEST['page'] === 'bookingpress_lite_wizard' || $_REQUEST['page'] === 'bookingpress_wizard' ) ) {
            $classes .= ' bpa-fullscreen-wizard-setup-container';
        }
        return $classes;
    }

    public static function bookingpress_exclude_wizard_from_legacy_scripts( $excluded_slugs ) {
        if ( is_array( $excluded_slugs ) ) {
            $slugs_to_exclude = [
                'bookingpress_lite_wizard',
                'lite_wizard',
                'bookingpress_wizard',
                'wizard',
            ];
            foreach ( $slugs_to_exclude as $slug ) {
                if ( ! in_array( $slug, $excluded_slugs, true ) ) {
                    $excluded_slugs[] = $slug;
                }
            }
        }
        return $excluded_slugs;
    }

    public static function enqueue_assets( $hook ) {
        if ( empty( $_REQUEST['page'] ) || ( $_REQUEST['page'] !== 'bookingpress_lite_wizard' && $_REQUEST['page'] !== 'bookingpress' ) ) {
            return;
        }

        wp_enqueue_script( 'wp-i18n' );

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
            'bookingpress-wizard-loader',
            BOOKINGPRESS_URL . '/src/assets/js/wizard-loader.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-wizard-loader' );

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

        wp_register_style(
            'bookingpress_wizard_style',
            BOOKINGPRESS_URL . '/css/bookingpress_wizard.css',
            [ 'bookingpress-common', 'bookingpress_components_css', 'bookingpress_admin_css' ],
            BOOKINGPRESS_VERSION
        );
        wp_enqueue_style( 'bookingpress_wizard_style' );
    }

    public static function render_page() {
        self::render_view( 'Wizard', [
            'title' => esc_html__( 'Setup Wizard', 'bookingpress-appointment-booking' ),
        ] );
    }

    public static function add_wizard_script_module_data( $data ) {
        global $BookingPress;
        
        ob_start();
        $BookingPress->bookingpress_wizard_dynamic_data_fields_func();
        $output = ob_get_clean();

        $wizard_data = json_decode( $output, true );

        if ( is_array( $wizard_data ) ) {
            $data = array_merge( $data, $wizard_data );
        }

        $data['rest_url']   = rest_url( 'bookingpress-app/v1' );
        $data['rest_nonce'] = wp_create_nonce( 'wp_rest' );
        $data['admin_url']  = admin_url();
        $data['is_vue3']    = true;
        
        return $data;
    }
}

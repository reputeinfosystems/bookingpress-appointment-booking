<?php

namespace BookingPress\admin;

class Header extends Base{

    public static function init(){
        parent::init();
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'bookingpress_print_script_data' ] );
        add_filter( 'script_module_data_bookingpress-sidemenu-drawer', [ __CLASS__, 'bookingpress_header_menubar_script_data' ] );
    }

    public static function bookingpress_scoped_pages(){

        $scoped_hooks = [
            'bookingpress',
            'bookingpress-calendar',
            'bookingpress_appointments',
            'bookingpress_addons',
            'bookingpress_customers',
            'bookingpress_growth_tools',
            'bookingpress_settings',
        ];

        return apply_filters( 'bookingpress_scoped_pages', $scoped_hooks );

    }
    
    public static function bookingpress_header_menubar_script_data( $dashboard_data ){

        $bpa_current_date_for_bf_popup = current_time('timestamp', true); //GMT/ UTC+00 timeszone
        $bpa_sale_popup_details = self::bookingpress_get_sales_data();
        

        $current_year = gmdate('Y', current_time('timestamp', true ) );

        if( !empty( $bpa_sale_popup_details[ $current_year ] ) ){
            $sale_details = $bpa_sale_popup_details[ $current_year ];
            
            $bpa_bf_popup_start_time = $sale_details['start_time'];
            $bpa_bf_popup_end_time = $sale_details['end_time'];
            if( $bpa_current_date_for_bf_popup >= $bpa_bf_popup_start_time && $bpa_current_date_for_bf_popup <= $bpa_bf_popup_end_time ) {
                $dashboard_data['show_sale_popup'] = true;
                $dashboard_data['show_original_popup'] = false;
            } else {
                $dashboard_data['show_sale_popup'] = false;
                $dashboard_data['show_original_popup'] = true;
            }
        } else {
            $dashboard_data['show_sale_popup'] = false;
            $dashboard_data['show_original_popup'] = true;
        }

        if(!empty($_GET['upgrade_action']) && ($_GET['upgrade_action'] == "upgrade_to_pro")){
            $dashboard_data['show_upgrade_model_on_load'] = true;
        }

        return $dashboard_data;
    }

    public static function bookingpress_get_sales_data(){
            
        $fetch_sale_detais = get_transient( 'bookingpress_retrieve_sale_details' );
        if( false == $fetch_sale_detais ){

            $fetch_url = 'https://bookingpressplugin.com/bpa_misc/bf_sale_dates.json';
            
            $fetch_dates = wp_remote_get( $fetch_url, array( 'timeout' => 4000, 'accept' => 'application/json' ) );

            
            if( !is_wp_error( $fetch_dates ) ){
                $details = wp_remote_retrieve_body( $fetch_dates );
                $sale_details = json_decode( $details, true );

                set_transient( 'bookingpress_retrieve_sale_details', $sale_details, ( HOUR_IN_SECONDS * 12 ) );
            }
        } else {
            $sale_details = $fetch_sale_detais;
        }

        return $sale_details;
    }

    public static function enqueue_assets( $hook ){

        $scoped_pages = self::bookingpress_scoped_pages();

        if( !empty( $_REQUEST['page'] ) && !in_array( $_REQUEST['page'], $scoped_pages ) ){
            return;
        }

         wp_register_script_module(
            'vue',
            BOOKINGPRESS_URL .'/src/assets/js/vue.min.js',
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
            'bookingpress-sidemenu-drawer',
            BOOKINGPRESS_URL . '/src/assets/js/drawer-loader.js',
            [ 'bookingpress-ui' ],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-sidemenu-drawer' );

    }

    public static function bookingpress_scoped_nonces(){
        $scoped_nonces = [
            'bookingpress_page_bookingpress'                => 'bpa_dashboard_wp_nonce',
            'bookingpress_page_bookingpress_appointments'   => 'bpa_appointments_wp_nonce',
            'bookingpress_page_bookingpress-calendar'       => 'bpa_calendar_wp_nonce',
            'bookingpress_page_bookingpress_addons'         => 'bpa_addons_wp_nonce',
            'bookingpress_page_bookingpress_customers'      => 'bpa_customers_wp_nonce',
            'bookingpress_page_bookingpress_settings'  => 'bpa_settings_wp_nonce',
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
            'is_wp_mobile' => wp_is_mobile(),
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
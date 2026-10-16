<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

class AddonsRoutes extends Base {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {

        register_rest_route( 'bookingpress-app/v1', '/addons/list', [
            'methods'  => 'POST',
            'callback' => [ $this, 'get_addons_list_data' ],
            'permission_callback' => $this->permission_callback_for('add_calendar_appointments')
        ] );
    }

    public static function get_bookingpress_addon_list(){

        if( !function_exists( 'get_plugins' ) ){
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();

        $bookingpress_addon_list = [];
        $primary_plugins = ['bookingpress-appointment-booking/bookingpress-appointment-booking.php', 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php'];

        if( !empty( $all_plugins ) ){
            foreach ( $all_plugins as $plugin_key => $plugin_data ) {
                if ( !in_array( $plugin_key, $primary_plugins ) && preg_match( '/((bookingpress(.+?)\/bookingpress(.+?)(\.php)))/i', $plugin_key ) ) {
                    $bookingpress_addon_list[ $plugin_key ] = $plugin_data;
                }
            }
        }
        return $bookingpress_addon_list;

    }

    function get_addons_list_data($request){

        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        global $bookingpress_addons;

        $addons_list = self::bookingpress_addons_list();

        if( !empty( $addons_list ) ){
            $bookingpress_addon_list_css = '';
                    
            foreach ( $addons_list as $bookingpress_body_key => $bookingpress_body_data_arr ) {
                foreach ( $bookingpress_body_data_arr as $bookingpress_body_data_key => $bookingpress_body_val ) {
                    if ( is_plugin_active( $bookingpress_body_val['addon_installer'] ) ) {
                        $addons_list[$bookingpress_body_key][$bookingpress_body_data_key]['addon_isactive'] = 1;
                        $bookingpress_body_data_arr[$bookingpress_body_data_key]['addon_isactive'] = 1;
                    } else {
                        if ( ! file_exists( WP_PLUGIN_DIR . '/' . $bookingpress_body_val['addon_installer'] ) ) {
                            $addons_list[$bookingpress_body_key][$bookingpress_body_data_key]['addon_isactive'] = 2;
                            $bookingpress_body_data_arr[$bookingpress_body_data_key]['addon_isactive'] = 2;
                        }
                    }
                    $bookingpress_horizontal_postion = isset($bookingpress_body_val['addon_icon_horizontal_position'])  ? $bookingpress_body_val['addon_icon_horizontal_position'] : 0;
                    $addon_icon_vertical_position = isset($bookingpress_body_val['addon_icon_vertical_position'])  ? $bookingpress_body_val['addon_icon_vertical_position'] : 0;
                    $addon_icon_slug = isset($bookingpress_body_val['addon_icon_slug'])  ? $bookingpress_body_val['addon_icon_slug'] : '';
                    $addon_icon_background = isset($bookingpress_body_val['addon_icon_background'])  ? $bookingpress_body_val['addon_icon_background'] : '';
                    $bookingpress_addon_list_css .= '
                        .bpa-addons-container .bpa-addon-item .bpa-ai-icon.'.$addon_icon_slug.'{
                            background-color: '.$addon_icon_background.';
                            background-position: '.$bookingpress_horizontal_postion.' '.$addon_icon_vertical_position.';
                        }';   

                }
                $bookingpress_body_data_arr = apply_filters( 'bookingpress_addon_list_data_filter', $bookingpress_body_data_arr );
                $addons_list[$bookingpress_body_key] = $bookingpress_body_data_arr;
            }

            $bookingpress_addon_list_css .= '.bpa-addons-container .bpa-addon-item .bpa-ai-icon{
                background: url("https://bookingpressplugin.com/bpa_misc/icons/addon-icon-sprite.png?ver='.current_time('timestamp').'") 0 0 no-repeat var(--bpa-pt-main-green);
            }';

            $response['addons_response'] = $addons_list;
            $response['css'] = $bookingpress_addon_list_css;

            /* if( class_exists( '\BookingPressPro\admin\Addons' ) && method_exists( '\BookingPressPro\admin\Addons', 'modify_addon_lists' ) ){
                $response = \BookingPressPro\admin\Addons::modify_addon_lists( $response );
            } */

        }

        return new \WP_REST_Response( [
            'success' => true,
            'data' => $response
        ], 200 );
    }

    public static function bookingpress_addons_list( $is_array = true ){
        $addons = [];

        $fetch_list = get_transient( 'bookingpress_addons_list' );

        if( $fetch_list !== false ) {
            return $fetch_list;
        }
        
        $bookingpress_addon_url = 'https://bookingpressplugin.com/bpa_misc/addons_list_v2.php';

        $bookingpress_addons_res = wp_remote_post(
            $bookingpress_addon_url,
            array(
                'method'    => 'POST',
                'timeout'   => 45,
                'sslverify' => false,
                'body'      => array(
                    'bookingpress_addon_list' => 1,
                ),
            )
        );

        if ( ! is_wp_error( $bookingpress_addons_res ) ) {
            $bookingpress_body_res = base64_decode( $bookingpress_addons_res['body'] );
            if ( ! empty( $bookingpress_body_res ) ) {
                $addons = json_decode( $bookingpress_body_res, $is_array );
                set_transient( 'bookingpress_addons_list', $addons, (DAY_IN_SECONDS) );
            }
        }

        return $addons;
    }

}
<?php

namespace BookingPress\api;

if( !defined( 'ABSPATH' ) ){ exit; }

class GrowthToolsRoutes extends Base {
    
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes'] );
    }

    public function register_routes() {

        register_rest_route( 'bookingpress-app/v1', '/growth_tools/bookingpress_get_armember', [
            'methods' => 'POST',
            'callback' => [ $this,'bookingpress_get_armember'],
            'permission_callback' => $this->permission_callback_for( 'retrieve_plugin' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/growth_tools/bookingpress_get_arforms', [
            'methods' => 'POST',
            'callback' => [ $this,'bookingpress_get_arforms'],
            'permission_callback' => $this->permission_callback_for( 'retrieve_plugin' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/growth_tools/bookingpress_get_arprice', [
            'methods' => 'POST',
            'callback' => [ $this,'bookingpress_get_arprice'],
            'permission_callback' => $this->permission_callback_for( 'retrieve_plugin' )
        ] );

        register_rest_route( 'bookingpress-app/v1', '/growth_tools/bookingpress_get_affiliatepress', [
            'methods' => 'POST',
            'callback' => [ $this,'bookingpress_get_affiliatepress'],
            'permission_callback' => $this->permission_callback_for( 'retrieve_plugin' )
        ] );

    }

    function bookingpress_get_arprice() {

        require_once ABSPATH . 'wp-admin/includes/file.php';

        if ( ! file_exists( WP_PLUGIN_DIR . '/arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' ) ) {
    
            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }
            $response = plugins_api(
                'plugin_information',
                array(
                    'slug'   => 'arprice-responsive-pricing-table',
                    'fields' => array(
                        'sections' => false,
                        'versions' => true,
                    ),
                )
            );
            if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
                if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                }
                $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                $source   = ! empty( $response->download_link ) ? $response->download_link : '';
                
                if ( ! empty( $source ) ) {
                    if ( $upgrader->install( $source ) === true ) {
                        activate_plugin( 'arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' );
                        $arp_install_activate = 1; 
                    }
                }
            } else {
                $package_data = $this->bpa_pro_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'arprice-responsive-pricing-table' );
                $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                if( !empty( $package_url ) ) {
                    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    }
                    $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                    if ( ! empty( $package_url ) ) {
                        if ( $upgrader->install( $package_url ) === true ) {
                            activate_plugin( 'arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' );
                            $arm_install_activate = 1;
                        }
                    }
                }
            }
        }
        if( $arp_install_activate = 1 ){

            $json_data = [
                'variant' => 'success',
                'title' => esc_html__('Success', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('ARPrice Successfully installed.', 'bookingpress-appointment-booking'),
            ];

        } else {

            $json_data = [
                'variant' => 'error',
                'title' => esc_html__('error', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('Somthing went wrong please try again later.', 'bookingpress-appointment-booking'),
            ];
        }
        
        return new \WP_REST_Response( $json_data, 200 );
    }

    function bookingpress_get_affiliatepress(){

        require_once ABSPATH . 'wp-admin/includes/file.php';

        if ( ! file_exists( WP_PLUGIN_DIR . '/affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php' ) ) {
    
            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }
            $response = plugins_api(
                'plugin_information',
                array(
                    'slug'   => 'affiliatepress-affiliate-marketing',
                    'fields' => array(
                        'sections' => false,
                        'versions' => true,
                    ),
                )
            );
            if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
                if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                }
                $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                $source   = ! empty( $response->download_link ) ? $response->download_link : '';
                
                if ( ! empty( $source ) ) {
                    if ( $upgrader->install( $source ) === true ) {
                        activate_plugin( 'affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php' );
                        $affi_install_activate = 1; 
                    }
                }
            } else {
                $package_data = $this->bpa_pro_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'affiliatepress-affiliate-marketing' );
                $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                if( !empty( $package_url ) ) {
                    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    }
                    $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                    if ( ! empty( $package_url ) ) {
                        if ( $upgrader->install( $package_url ) === true ) {
                            activate_plugin( 'affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php' );
                            $affi_install_activate = 1;
                        }
                    }
                }
            }
        }
        if( $affi_install_activate = 1 ){

            $json_data = [
                'variant' => 'success',
                'title' => esc_html__('Success', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('AffiliatePress Successfully installed.', 'bookingpress-appointment-booking'),
            ];

        } else {

            $json_data = [
                'variant' => 'error',
                'title' => esc_html__('error', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('Somthing went wrong please try again later.', 'bookingpress-appointment-booking'),
            ];
        }

        return new \WP_REST_Response( $json_data, 200 );
    }

    function bookingpress_get_arforms(){

        require_once ABSPATH . 'wp-admin/includes/file.php';

        if ( ! file_exists( WP_PLUGIN_DIR . '/arforms-form-builder/arforms-form-builder.php' ) ) {
        
            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }
            $response = plugins_api(
                'plugin_information',
                array(
                    'slug'   => 'arforms-form-builder',
                    'fields' => array(
                        'sections' => false,
                        'versions' => true,
                    ),
                )
            );
            if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
                if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                }
                $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                $source   = ! empty( $response->download_link ) ? $response->download_link : '';
                
                if ( ! empty( $source ) ) {
                    if ( $upgrader->install( $source ) === true ) {
                        activate_plugin( 'arforms-form-builder/arforms-form-builder.php' );
                        $arf_install_activate = 1; 
                    }
                }
            } else {
                $package_data = $this->bpa_pro_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'arforms-form-builder' );
                $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                if( !empty( $package_url ) ) {
                    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    }
                    $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                    if ( ! empty( $package_url ) ) {
                        if ( $upgrader->install( $package_url ) === true ) {
                            activate_plugin( 'arforms-form-builder/arforms-form-builder.php' );
                            $arf_install_activate = 1;
                        } 
                    }
                }
            }
        }
        if( $arf_install_activate = 1 ){

            $json_data = [
                'variant' => 'success',
                'title' => esc_html__('Success', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('ARForms Successfully installed.', 'bookingpress-appointment-booking'),
            ];
        } else {

            $json_data = [
                'variant' => 'error',
                'title' => esc_html__('error', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('Somthing went wrong please try again later.', 'bookingpress-appointment-booking'),
            ];
        }

        return new \WP_REST_Response( $json_data, 200 );
    }

    function bookingpress_get_armember(){

        require_once ABSPATH . 'wp-admin/includes/file.php';

        if ( ! file_exists( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' ) ) {
        
            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }
            $response = plugins_api(
                'plugin_information',
                array(
                    'slug'   => 'armember-membership',
                    'fields' => array(
                        'sections' => false,
                        'versions' => true,
                    ),
                )
            );
            if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
                if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                }
                $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                $source   = ! empty( $response->download_link ) ? $response->download_link : '';
                
                if ( ! empty( $source ) ) {
                    if ( $upgrader->install( $source ) === true ) {
                        activate_plugin( 'armember-membership/armember-membership.php' );
                        $arm_install_activate = 1; 
                    }
                }
            } else {
                $package_data = $this->bpa_pro_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'armember-membership' );
                $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                if( !empty( $package_url ) ) {
                    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    }
                    $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                    if ( ! empty( $package_url ) ) {
                        if ( $upgrader->install( $package_url ) === true ) {
                            activate_plugin( 'armember-membership/armember-membership.php' );
                            $arm_install_activate = 1;
                        } 
                    }
                }
            }
        }
        if( $arm_install_activate = 1 ){

            $json_data = [
                'variant' => 'success',
                'title' => esc_html__('Success', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('ARMember Successfully installed.', 'bookingpress-appointment-booking'),
            ];

        } else {

            $json_data = [
                'variant' => 'error',
                'title' => esc_html__('error', 'bookingpress-appointment-booking'),
                'msg' => esc_html__('Somthing went wrong please try again later.', 'bookingpress-appointment-booking'),
            ];
        }
        
        return new \WP_REST_Response( $json_data, 200 );   
      
    }

    function bpa_pro_force_check_for_plugin_update( $param = [], $force_update = false, $slug = '' ){
        global $wp_version;

        if( empty( $slug ) ){
            return false;
        }

        $arf_api_url = 'https://www.arpluginshop.com';
        $args = array(
            'slug' => $slug,
        );

        if( 'armember-membership' == $slug ){
            $user_agent = 'ARMLITE-WordPress/'. $wp_version.';'.BOOKINGPRESS_HOME_URL;
        } else if( 'arprice-responsive-pricing-table' == $slug ){
            $user_agent = 'ARPLITE-WordPress/'. $wp_version.';'.BOOKINGPRESS_HOME_URL;
        } else if( 'arforms-form-builder' == $slug ){
            $user_agent = 'ARFLITE-WordPress/'. $wp_version.';'.BOOKINGPRESS_HOME_URL;
        } else {
            $user_agent = 'BKPLITE-WordPress/'. $wp_version.';'.BOOKINGPRESS_HOME_URL;
        }
    
        $request_string = array(
            'body' => array(
                'action' => 'lite_plugin_new_version_check',
                'request' => serialize( $args ),
                'api-key' => md5( BOOKINGPRESS_HOME_URL ),
                'is_update' => $force_update
            ),
            'sslverify' => false,
            'user-agent' => $user_agent
        );
    
        //Start checking for an update
        $raw_response = wp_remote_post( $arf_api_url, $request_string );
    
        if( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) ){
            $response = @unserialize( $raw_response['body'] );
        }
        
        
        if( isset( $response['access_request'] ) && !empty( $response['access_request'] ) && 'success' == $response['access_request'] ){
            if( isset( $response['access_package'] ) && !empty( $response['access_package'] ) ){
                $update_package = @unserialize( $response['access_package'] );
                if( isset( $update_package ) && is_array( $update_package ) && !empty( $update_package ) ){
                    $version = $update_package['version'];
    
                    if( !empty( $param ) ){
                        $response_arr = [];
                        foreach( $param as  $post_key ){
                            $response_arr[ $post_key ] = !empty( $update_package[ $post_key ] ) ? $update_package[ $post_key ] : '';
                        }
    
                        return $response_arr;
                    }
                }
            }
        }
        return true;
    }
}
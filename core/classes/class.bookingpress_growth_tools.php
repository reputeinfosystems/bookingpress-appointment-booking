<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'bookingpress_growth_tools') ){
    class bookingpress_growth_tools Extends BookingPress_Core{
        function __construct() {

            global $BookingPress;

            add_action('bookingpress_growth_tools_dynamic_view_load', array( $this, 'bookingpress_load_growth_tools_view_func'), 10 );
            add_action('bookingpress_growth_tools_dynamic_vue_methods', array( $this, 'bookingpress_growth_tools_dynamic_vue_methods_func' ), 10);
            add_action('bookingpress_growth_tools_dynamic_data_fields', array( $this, 'bookingpress_growth_tools_dynamic_data_fields_func' ), 10);
            add_action('wp_ajax_bookingpress_get_armember', array( $this, 'bookingpress_get_armember_func'));
            add_action('wp_ajax_bookingpress_get_arforms', array( $this, 'bookingpress_get_arforms_func'));
            add_action('wp_ajax_bookingpress_get_arprice', array( $this, 'bookingpress_get_arprice_func'));
            add_action('wp_ajax_bookingpress_get_affiliatepress', array( $this, 'bookingpress_get_affiliatepress_func') );

        }

        function bookingpress_growth_tools_dynamic_vue_methods_func(){ 
            global $BookingPress,$bookingpress_notification_duration; ?>

            bpa_download_plugins( plugin_data ){
                
                if( plugin_data == 'arforms'){
                    const vm = this;
                    vm.is_disabled = true
                    vm.is_display_arforms_save_loader = '1'
                    vm.savebtnloading = true

                    var postData = { action:'bookingpress_get_arforms',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(  response.data.variant == 'success' ){
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            location.reload();
                        }

                        vm.is_disabled = false
                        vm.is_display_arforms_save_loader = '0'
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });
                }

                if( plugin_data == 'armember' ){

                    const vm = this;
                    vm.is_disabled = true
                    vm.is_display_save_loader = '1'
                    vm.savebtnloading = true

                    var postData = { action:'bookingpress_get_armember',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(  response.data.variant == 'success' ){
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            location.reload();
                        }

                        vm.is_disabled = false
                        vm.is_display_save_loader = '0'
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });
                }

                if( plugin_data == 'arprice' ){

                    const vm = this;
                    vm.is_disabled = true
                    vm.is_display_arprice_save_loader = '1'
                    vm.savebtnloading = true

                    var postData = { action:'bookingpress_get_arprice',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(  response.data.variant == 'success' ){
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            location.reload();
                        }

                        vm.is_disabled = false
                        vm.is_display_arprice_save_loader = '0'
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });
                }

                if( plugin_data == 'affiliatepress' ){
                    const vm = this;
                    vm.is_disabled = true
                    vm.is_display_affiliatepress_save_loader = '1'
                    vm.savebtnloading = true

                    var postData = { action:'bookingpress_get_affiliatepress',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(  response.data.variant == 'success' ){
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            location.reload();
                        }

                        vm.is_disabled = false
                        vm.is_display_affiliatepress_save_loader = '0'
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });
                }
            },
            <?php
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

        function bookingpress_get_armember_func(){

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_plugin', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

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

                $response_data['variant']               = 'success';
                $response_data['title']                 = esc_html__('Success', 'bookingpress-appointment-booking');
                $response_data['msg']                   = esc_html__('ARMember Successfully installed.', 'bookingpress-appointment-booking');
            } else {

                $response_data['variant']               = 'error';
                $response_data['title']                 = esc_html__('error', 'bookingpress-appointment-booking');
                $response_data['msg']                   = esc_html__('Somthing went wrong please try again later.', 'bookingpress-appointment-booking');
            }
            wp_send_json($response_data);
            die;
        }

        function bookingpress_get_arforms_func() {

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_plugin', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

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

                $response_data['variant']               = 'success';
                $response_data['title']                 = esc_html__('Success', 'bookingpress-appointment-booking');
                $response_data['msg']                   = esc_html__('ARForms Successfully installed.', 'bookingpress-appointment-booking');
            } else {

                $response_data['variant']               = 'error';
                $response_data['title']                 = esc_html__('error', 'bookingpress-appointment-booking');
                $response_data['msg']                   = esc_html__('Somthing went wrong please try again later.', 'bookingpress-appointment-booking');
            }
            wp_send_json($response_data);
            die;
        }
        function bookingpress_get_arprice_func() {

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_plugin', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

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

                $response_data['variant']               = 'success';
                $response_data['title']                 = esc_html__('Success', 'bookingpress-appointment-booking');
                $response_data['msg']                   = esc_html__('ARPrice Successfully installed.', 'bookingpress-appointment-booking');
            } else {

                $response_data['variant']               = 'error';
                $response_data['title']                 = esc_html__('error', 'bookingpress-appointment-booking');
                $response_data['msg']                   = esc_html__('Somthing went wrong please try again later.', 'bookingpress-appointment-booking');
            }
            wp_send_json($response_data);
            die;
        }

        function bookingpress_get_affiliatepress_func(){
            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_plugin', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

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
                            $arp_install_activate = 1; 
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
                                $arp_install_activate = 1;
                            }
                        }
                    }
                }
            }
            if( $arp_install_activate = 1 ){

                $response_data['variant']               = 'success';
                $response_data['title']                 = esc_html__('Success', 'bookingpress-appointment-booking');
                $response_data['msg']                   = esc_html__('AffiliatePress Successfully installed.', 'bookingpress-appointment-booking');
            } else {

                $response_data['variant']               = 'error';
                $response_data['title']                 = esc_html__('error', 'bookingpress-appointment-booking');
                $response_data['msg']                   = esc_html__('Somthing went wrong please try again later.', 'bookingpress-appointment-booking');
            }
            wp_send_json($response_data);
            die;
        }


        function bookingpress_growth_tools_dynamic_data_fields_func() {

            global $bookingpress_growth_tools_vue_data_fields;

            $bookingpress_growth_tools_vue_data_fields = array ( 
                /* ['bpa_growth_tools'] = array(); */
                'is_display_loader'          => '0',
                'is_disabled'                => false,
                'is_display_save_loader'     => '0',
                'is_display_arforms_save_loader' => '0',
                'is_display_arprice_save_loader' => '0',
                'is_display_affiliatepress_save_loader' => '0',
            );


            echo wp_json_encode( $bookingpress_growth_tools_vue_data_fields );

        }    

        /**
         * Load Growth tools view file
         *
         * @return void
         */
        function bookingpress_load_growth_tools_view_func(){
            $bookingpress_growth_tools_view_path = BOOKINGPRESS_VIEWS_DIR . '/growth_tools/bpa_growth_tools.php';
			require $bookingpress_growth_tools_view_path;
        }
    }
    global $bookingpress_growth_tools;
    $bookingpress_growth_tools = new bookingpress_growth_tools();
}

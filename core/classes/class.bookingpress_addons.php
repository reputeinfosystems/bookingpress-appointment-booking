<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'bookingpress_addons') ){
    class bookingpress_addons Extends BookingPress_Core{
        function __construct() {

            global $BookingPress;

            if( !$BookingPress->bpa_is_pro_active() ){
                add_action( 'bookingpress_addons_dynamic_view_load', array( $this, 'bookingpress_load_addons_view_func'), 9 );
                add_action( 'bookingpress_addons_dynamic_data_fields', array( $this, 'bookingpress_addons_dynamic_data_fields_func'), 9 );
                add_action( 'bookingpress_addons_dynamic_on_load_methods', array( $this, 'bookingpress_addons_dynamic_onload_methods_func'), 9 );
                add_action( 'bookingpress_addons_dynamic_vue_methods', array( $this, 'bookingpress_addons_dynamic_vue_methods_func') );
                
                add_action( 'wp_ajax_bookingpress_get_remote_addons_lite_list', array( $this, 'bookingpress_get_remote_addons_lite_list_func') );
            }
        }
        
        /**
         * Load addons view file
         *
         * @return void
         */
        function bookingpress_load_addons_view_func(){
            $bookingpress_addons_view_path = BOOKINGPRESS_VIEWS_DIR . '/addons/addons_list.php';
			require $bookingpress_addons_view_path;
        }
        
        /**
         * Add data variables for addons page
         *
         * @return void
         */
        function bookingpress_addons_dynamic_data_fields_func(){
            global $bookingpress_addons_vue_data_fields;
            $bookingpress_addons_vue_data_fields['bpa_lite_addons'] = array();

            echo wp_json_encode( $bookingpress_addons_vue_data_fields );
        }
        
        /**
         * Addons page data variables
         *
         * @return void
         */
        function bookingpress_addons_dynamic_onload_methods_func(){
            ?>
            const vm = this;
            vm.bookingpress_get_remote_addons_lite_list();
            <?php
        }
        
        /**
         * Addons page methods/functions
         *
         * @return void
         */
        function bookingpress_addons_dynamic_vue_methods_func(){
            ?>
            bookingpress_get_remote_addons_lite_list(){
                const vm = this;
                var head = document.getElementsByTagName('head')[0];
                vm.is_display_loader = '1';
                let bookingpress_remote_addon_action = {
                    action: 'bookingpress_get_remote_addons_lite_list',
                    _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>',
                };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_remote_addon_action ) )
                .then( function (response) {
                    vm.is_display_loader = '0'
                    vm.bpa_lite_addons = response.data.addons_response
                    var addon_css = response.data.css;
                    var head = document.getElementsByTagName('head')[0];
                    var s = document.createElement('style');
                    s.setAttribute('type', 'text/css');
                    if (s.styleSheet) {
                        s.styleSheet.cssText = css;
                    } else {
                        s.appendChild(document.createTextNode(addon_css));
                    }
                    head.appendChild(s);
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);
                });
            },
            <?php
        }

        
        /**
         * Get addons list from remote
         *
         * @return void
         */
        function bookingpress_get_remote_addons_lite_list_func(){
            global $wpdb,$BookingPress;
			$response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_addons', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $response['variant']         = 'error';
            $response['title']           = esc_html__( 'Error', 'bookingpress-appointment-booking' );
            $response['msg']             = esc_html__( 'Something went wrong while fetching list of addons', 'bookingpress-appointment-booking' );
            $response['addons_response'] = '';
            $response['css'] = '';

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
                    $bookingpress_body_res = json_decode( $bookingpress_body_res, true );                        
                    $bookingpress_addon_list_css = '';
                    
                    foreach ( $bookingpress_body_res as $bookingpress_body_key => $bookingpress_body_data_arr ) {
                        foreach ( $bookingpress_body_data_arr as $bookingpress_body_data_key => $bookingpress_body_val ) {
                            if ( is_plugin_active( $bookingpress_body_val['addon_installer'] ) ) {
                                $bookingpress_body_res[$bookingpress_body_key][$bookingpress_body_data_key]['addon_isactive'] = 1;
                                $bookingpress_body_data_arr[$bookingpress_body_data_key]['addon_isactive'] = 1;
                            } else {
                                if ( ! file_exists( WP_PLUGIN_DIR . '/' . $bookingpress_body_val['addon_installer'] ) ) {
                                    $bookingpress_body_res[$bookingpress_body_key][$bookingpress_body_data_key]['addon_isactive'] = 2;
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
                        $bookingpress_body_res[$bookingpress_body_key] = $bookingpress_body_data_arr;
                    }

                    $bookingpress_addon_list_css .= '.bpa-addons-container .bpa-addon-item .bpa-ai-icon{
                        background: url("https://bookingpressplugin.com/bpa_misc/icons/addon-icon-sprite.png?ver='.current_time('timestamp').'") 0 0 no-repeat var(--bpa-pt-main-green);
                    }';
                    $bookingpress_body_res = apply_filters( 'bookingpress_addon_list_data_filter', $bookingpress_body_res );

                    $response['variant']         = 'success';
                    $response['title']           = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                    $response['msg']             = esc_html__( 'Addons list fetched successfully', 'bookingpress-appointment-booking' );
                    $response['addons_response'] = $bookingpress_body_res;
                    $response['css']             = $bookingpress_addon_list_css; 
                }
            } else {
                $response['msg'] = $bookingpress_addons_res->get_error_message();
            }
            echo wp_json_encode( $response );
            die;
        }
    }

    global $bookingpress_addons;
    $bookingpress_addons = new bookingpress_addons();
}
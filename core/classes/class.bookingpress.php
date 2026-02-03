<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('BookingPress') ) {
    class BookingPress Extends BookingPress_Core
    {
        var $bookingpress_slugs;
        function __construct()
        {
			global $wp, $wpdb, $bookingpress_capabilities_global, $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_customers, $tbl_bookingpress_settings, $tbl_bookingpress_default_workhours, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_notifications, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_entries, $bookingpress_common_date_format, $tbl_bookingpress_form_fields, $tbl_bookingpress_customize_settings,$tbl_bookingpress_debug_payment_log, $tbl_bookingpress_customers_meta, $tbl_bookingpress_other_debug_logs, $bookingpress_common_time_format, $pagenow,$Bookingpress, $tbl_bookingpress_entries_meta, $tbl_bookingpress_double_bookings,$tbl_bookingpress_transient,$tbl_bookingpress_export_data_log,$tbl_bookingpress_export_data_log_detail,$export_import_data_key_name,$tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log,$tbl_bookingpress_import_record_rel,$tbl_bookingpress_import_images;

            $tbl_bookingpress_categories           = $wpdb->prefix . 'bookingpress_categories';
            $tbl_bookingpress_services             = $wpdb->prefix . 'bookingpress_services';
            $tbl_bookingpress_servicesmeta         = $wpdb->prefix . 'bookingpress_servicesmeta';
            $tbl_bookingpress_customers            = $wpdb->prefix . 'bookingpress_customers';
            $tbl_bookingpress_settings             = $wpdb->prefix . 'bookingpress_settings';
            $tbl_bookingpress_default_workhours    = $wpdb->prefix . 'bookingpress_default_workhours';
            $tbl_bookingpress_default_daysoff      = $wpdb->prefix . 'bookingpress_default_daysoff';
            $tbl_bookingpress_notifications        = $wpdb->prefix . 'bookingpress_notifications';
            $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
            $tbl_bookingpress_payment_logs         = $wpdb->prefix . 'bookingpress_payment_transactions';
            $tbl_bookingpress_entries              = $wpdb->prefix . 'bookingpress_entries';
            $tbl_bookingpress_form_fields          = $wpdb->prefix . 'bookingpress_form_fields';
            $tbl_bookingpress_customize_settings   = $wpdb->prefix . 'bookingpress_customize_settings';
            $tbl_bookingpress_debug_payment_log    = $wpdb->prefix . 'bookingpress_debug_payment_log';
            $tbl_bookingpress_customers_meta       = $wpdb->prefix . 'bookingpress_customers_meta';
            $tbl_bookingpress_other_debug_logs     = $wpdb->prefix . 'bookingpress_other_debug_logs';
            $tbl_bookingpress_entries_meta         = $wpdb->prefix . 'bookingpress_entries_meta';
            $tbl_bookingpress_double_bookings      = $wpdb->prefix . 'bookignpress_double_bookings';
            $tbl_bookingpress_transient            = $wpdb->prefix . 'bookingpress_transient';
            $tbl_bookingpress_export_data_log        = $wpdb->prefix.'bookingpress_export_data_log';
            $tbl_bookingpress_export_data_log_detail = $wpdb->prefix.'bookingpress_export_log_detail';
            $tbl_bookingpress_import_data_log        = $wpdb->prefix.'bookingpress_import_data_log';
            $tbl_bookingpress_import_detail_log      = $wpdb->prefix.'bookingpress_import_detail_log';
            $tbl_bookingpress_import_record_rel      = $wpdb->prefix.'bookingpress_import_record_rel';
            $tbl_bookingpress_import_images          = $wpdb->prefix.'bookingpress_import_images';           

            register_activation_hook(BOOKINGPRESS_DIR . '/bookingpress-appointment-booking.php', array( 'BookingPress', 'install' ));
            register_activation_hook(BOOKINGPRESS_DIR . '/bookingpress-appointment-booking.php', array( 'BookingPress', 'bookingpress_check_network_activation' ));
            register_uninstall_hook(BOOKINGPRESS_DIR . '/bookingpress-appointment-booking.php', array( 'BookingPress', 'uninstall' ));

            /* Set Page Capabilities Global */
            $bookingpress_capabilities_global = array(
                'bookingpress'               => 'bookingpress',
                'bookingpress_calendar'      => 'bookingpress_calendar',
                'bookingpress_appointments'  => 'bookingpress_appointments',
                'bookingpress_payments'      => 'bookingpress_payments',
                'bookingpress_customers'     => 'bookingpress_customers',
                'bookingpress_services'      => 'bookingpress_services',
                'bookingpress_notifications' => 'bookingpress_notifications',
                'bookingpress_customize'     => 'bookingpress_customize',
                'bookingpress_settings'      => 'bookingpress_settings',
            );

            $this->bookingpress_slugs = $this->bookingpress_page_slugs();

            global $bookingpress_settings_table_exists;

            $default_date_format = 'F j, Y';
            //$bookingpress_settings_table_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema=%s AND table_name=%s",DB_NAME,$tbl_bookingpress_settings));
            if($bookingpress_settings_table_exists == 1) {
                $default_date_format = $this->bookingpress_get_settings('default_date_format','general_setting');
                $default_date_format = !empty($default_date_format) ? $default_date_format : 'F j, Y';         
            }
			$bookingpress_common_time_format = $this->bookingpress_check_common_time_format( get_option( 'time_format' ) );
            $bookingpress_common_date_format = $this->bookingpress_check_common_date_format($default_date_format);    

            add_action('admin_menu', array( $this, 'bookingpress_menu' ), 26);
            add_action('admin_enqueue_scripts', array( $this, 'set_css' ), 11);
            add_action('admin_enqueue_scripts', array( $this, 'set_js' ), 11);

            add_action('wp_head', array( $this, 'set_front_css' ), 1);
            add_action('wp_footer', array( $this, 'set_front_js'), 1);

            add_filter('plugin_action_links', array( $this, 'bookingpress_plugin_action_links' ), 10, 2);

            if (! function_exists('is_plugin_active') ) {
                include ABSPATH . '/wp-admin/includes/plugin.php';
            }

            if (is_plugin_active('wp-rocket/wp-rocket.php') && ! is_admin() ) {
                add_filter('script_loader_tag', array( $this, 'bookingpress_prevent_rocket_loader_script' ), 10, 2);
            }

            if (! is_admin() ) {
                add_filter('script_loader_tag', array( $this, 'bookingpress_prevent_rocket_loader_script_clf' ), 10, 2);
		        add_filter('script_loader_tag', array( $this, 'bookingpress_prevent_rocket_loader_script_clf_advanced' ), 11, 2);
            }

            if (is_plugin_active('js_composer/js_composer.php') && file_exists(BOOKINGPRESS_CORE_DIR . '/vc/bookingpress_class_vc_extend.php') ) {

                include_once BOOKINGPRESS_CORE_DIR . '/vc/bookingpress_class_vc_extend.php';

                global $bookingpress_vcextend;
                $bookingpress_vcextend = new Bookingpress_VCExtend();
            }

            add_filter('admin_footer_text', '__return_empty_string', 11);
            add_filter('update_footer', '__return_empty_string', 11);
            add_action('admin_init', array( $this, 'bookingpress_hide_update_notice' ), 1);

            add_action('bookingpress_user_update_meta', array( $this, 'bookingpress_user_update_meta_details' ), 10, 2);

            add_action('wp_ajax_bookingpress_remove_uploaded_file', array( $this, 'bookingpress_remove_uploaded_file' ), 10);

            add_action('deleted_user', array( $this, 'bookingpress_after_deleted_user_action' ), 10, 2);

            if (! empty($GLOBALS['wp_version']) && version_compare($GLOBALS['wp_version'], '5.7.2', '>') ) {
                add_filter('block_categories_all', array( $this, 'bookingpress_gutenberg_category' ), 10, 2);
            } else {
                add_filter('block_categories', array( $this, 'bookingpress_gutenberg_category' ), 10, 2);
            }

           // add_action('enqueue_block_editor_assets', array( $this, 'bookingpress_enqueue_gutenberg_assets' ));

            add_action('wp_ajax_bookingpress_get_help_data', array( $this, 'bookingpress_get_help_data_func' ));

            add_action('admin_footer', array( $this, 'bookingpress_admin_footer_func' ));

            add_action('admin_notices', array( $this, 'bookingpress_admin_notices' ));
            add_action('bookingpress_payment_log_entry', array( $this, 'bookingpress_write_payment_log' ), 10, 6);

            add_action('wp_ajax_bookingpress_view_debug_payment_log', array( $this, 'bookingpress_view_debug_payment_log_func' ), 10);
            add_action('wp_ajax_bookingpress_lite_deactivate_plugin', array( $this, 'bookingpress_lite_deactivate_plugin_func' ));
            add_action('wp_ajax_bookingpress_clear_debug_payment_log', array( $this, 'bookingpress_clear_debug_payment_log_func' ), 10);

            add_action('wp_ajax_bookingpress_download_payment_log', array( $this, 'bookingpress_download_payment_log_func' ), 10);

            add_action('admin_init', array( $this, 'bookingpress_debug_log_download_file' ));

            add_action('admin_head', array( $this, 'bookingpress_hide_admin_notices' ));

            add_action('admin_init', array( $this, 'upgrade_data' ));

            if($pagenow == "plugins.php"){
                add_action('admin_footer', array( $this, 'bookingpress_deactivate_feedback_popup' ));
            }

            add_action('wp', array( $this, 'bookingpress_get_sysinfo_func' ));

            add_action('bookingpress_other_debug_log_entry', array( $this, 'bookingpress_other_debug_logs_func' ), 10, 5);

            register_deactivation_hook(BOOKINGPRESS_DIR . '/bookingpress-appointment-booking.php', array( 'BookingPress', 'deactivate_lite_version' ));

            add_action('wp_ajax_bookingpress_dismisss_admin_notice', array($this, 'bookingpress_dismisss_admin_notice_func'));
            
            add_action( 'admin_init', array($this, 'bookingpress_add_gutenbergblock' ));

            add_action('bookingpress_send_anonymous_data',array($this,'bookingpress_send_anonymous_data_cron'));

            add_action( 'bookingpress_cleanup_transient_data_hook', array( $this, 'bookingpress_cleanup_transient_data_hook_callback') );

            //Wizard hooks
            add_filter('bookingpress_lite_wizard_dynamic_view_load', array( $this, 'bookingpress_load_wizard_view_func'), 10);
			add_action('bookingpress_lite_wizard_dynamic_vue_methods', array( $this, 'bookingpress_wizard_vue_methods_func'));
			add_action('bookingpress_lite_wizard_dynamic_on_load_methods', array( $this, 'bookingpress_wizard_on_load_methods_func'));
			add_action('bookingpress_lite_wizard_dynamic_data_fields', array( $this, 'bookingpress_wizard_dynamic_data_fields_func'));
			add_action('bookingpress_lite_wizard_dynamic_helper_vars', array( $this, 'bookingpress_wizard_dynamic_helper_vars_func'));

            add_action('wp_ajax_bookingpress_save_lite_wizard_settings', array($this, 'bookingpress_save_lite_wizard_settings_func'));

            add_action('wp_ajax_bookingpress_lite_skip_wizard', array($this, 'bookingpress_skip_wizard_func'));

            add_action('wp_ajax_bookingpress_download_wizard_product', array($this, 'bookingpress_download_wizard_product_func'));

            add_action('set_user_role', array($this, 'bookingpress_assign_caps_on_role_change'), 10, 3); 

            add_filter( 'bpa_calculate_default_break_hours', array( $this, 'bookingpress_calculate_default_workhours' ), 10, 2 );

            add_filter( 'debug_information', array( $this, 'bpa_check_server_short_tag_information') );

            add_filter( 'bookingpress_modify_update_transient_flag', array( $this, 'bookingpress_modify_update_transient_flag_func') );

        }

        function bookingpress_download_wizard_product_func(){

            
            global $wpdb, $BookingPress, $bookingpress_growth_tools;
            $total_start_ms = microtime( true );

			$final_response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $final_response['variant']        = 'error';
                $final_response['title']          = esc_html__('Error', 'bookingpress-appointment-booking');
                $final_response['msg']            = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                echo wp_json_encode($final_response);
                exit;
            }

            $arf_install_activate = 'not_installed';
            $affi_install_activate = 'not_installed';

            $download_affi = isset($_REQUEST['download_affi']) ? filter_var($_REQUEST['download_affi'], FILTER_VALIDATE_BOOLEAN) : false;
            $download_arf = isset($_REQUEST['download_arf']) ? filter_var($_REQUEST['download_arf'], FILTER_VALIDATE_BOOLEAN) : false;
			
            if( $download_affi ){

                $affi_start_ms = microtime( true );

                if ( !file_exists( WP_PLUGIN_DIR . '/affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php' ) ) {
        
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
                                $affi_install_activate = 'installed'; 
                            }
                        }
                    } else {

                        $package_data = $bookingpress_growth_tools->bpa_pro_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'affiliatepress-affiliate-marketing' );
                        $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                        if( !empty( $package_url ) ) {
                            if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                            }
                            $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                            if ( ! empty( $package_url ) ) {
                                if ( $upgrader->install( $package_url ) === true ) {
                                    activate_plugin( 'affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php' );
                                    $affi_install_activate = 'installed'; 
                                }
                            }
                        }
                    }
                } else {
                    $affi_install_activate = 'pre_installed';
                }
                $affi_end_ms = microtime( true );
            }

            if( $download_arf ){

                $arf_start_ms = microtime( true );

                if ( !file_exists( WP_PLUGIN_DIR . '/arforms-form-builder/arforms-form-builder.php' ) ) {
        
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
                                $arf_install_activate = 'installed'; 
                            }
                        }
                    } else {
                        $package_data = $bookingpress_growth_tools->bpa_pro_force_check_for_plugin_update( ['version', 'dwlurl'], false, 'arforms-form-builder' );
                        $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                        if( !empty( $package_url ) ) {
                            if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                            }
                            $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                            if ( ! empty( $package_url ) ) {
                                if ( $upgrader->install( $package_url ) === true ) {
                                    activate_plugin( 'arforms-form-builder/arforms-form-builder.php' );
                                    $arf_install_activate = 'installed';
                                } 
                            }
                        }
                    }
                } else {
                    $arf_install_activate = 'pre_installed';
                }
                $arf_end_ms = microtime( true );
            }

            $install_plugin_from_wizard = array(
                'affi_download' => $affi_install_activate,
                'arf_download'  => $arf_install_activate,
            );

            update_option('bookingpress_download_plugin_wizard', wp_json_encode( $install_plugin_from_wizard ));
			update_option('bookingpress_lite_wizard_complete', 1);

            if( is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') ){
                update_option( 'bookingpress_wizard_complete', 1 );
            }
            
            $total_end_ms = microtime( true );
			$final_response['total_time_taken'] = ( $total_end_ms - $total_start_ms ) . ' seconds';
			$final_response['total_time_taken_arforms'] = ( $arf_end_ms - $arf_start_ms ) . ' seconds';
			$final_response['total_time_taken_affilatepress'] = ( $affi_end_ms - $affi_start_ms ) . ' seconds';

            $final_response['variant']        = 'success';
			$final_response['title']          = esc_html__('Success', 'bookingpress-appointment-booking');
			$final_response['msg']            = esc_html__('Wizard finished successfully', 'bookingpress-appointment-booking');

			echo wp_json_encode($final_response);
            die;
        }

        function bookingpress_modify_update_transient_flag_func( $transient_flag ){

            /** Check if the pro plugin is active */
            if( $this->bpa_is_pro_active() ){
                if( !function_exists('is_plugin_active') ){
                    include ABSPATH . '/wp-admin/includes/plugin.php';
                }

                if( is_plugin_active( 'bookingpress-cart/bookingpress-cart.php' ) ){
                    global $bookingpress_cart_version;
                    if( version_compare( $bookingpress_cart_version, '3.2', '<' ) ){
                        $transient_flag = true;
                    }
                }

                if( is_plugin_active( 'bookingpress-custom-service-duration/bookingpress-custom-service-duration.php' ) ){
                    global $bookingpress_custom_service_duration_version;
                    if( version_compare( $bookingpress_custom_service_duration_version, '3.2', '<' ) ){
                        $transient_flag = true;
                    }
                }

                if( is_plugin_active( 'bookingpress-recurring-appointments/bookingpress-recurring-appointments.php' ) ){
                    global $recurring_appointments_list_version;
                    if( version_compare( $recurring_appointments_list_version, '1.7', '<' ) ){
                        $transient_flag = true;
                    }
                }

                if( is_plugin_active( 'bookingpress-location/bookingpress-location.php' ) ){
                    global $bookingpress_location_version;
                    if( version_compare( $bookingpress_location_version, '1.6', '<' ) ){
                        $transient_flag = true;
                    }
                }

                if( version_compare( $this->bpa_pro_plugin_version(), '3.9.8', '<') ){
                    $transient_flag = true;
                }

                if( false == $transient_flag && !empty( $_REQUEST['is_rescheduling_event'] ) && ('true' == $_REQUEST['is_rescheduling_event'] || true === $_REQUEST['is_rescheduling_event']) ){
                    $transient_flag = true;
                }
            }

            return $transient_flag;
        }

        function bpa_check_server_short_tag_information( $server_info ){

            $server_info['wp-server']['fields']['php_short_tag'] = array(
                'label' => 'PHP Short Tag',
                'value' => !empty( ini_get( 'short_open_tag') ) ? esc_html__( 'Enabled', 'bookingpress-appointment-booking' ) : esc_html__( 'Disabled', 'bookingpress-appointment-booking' )
            );

            return $server_info;
        }

        function bookingpress_get_sales_data(){
            
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

        function bookingpress_get_bf_sale_start_time(){

            $start_time = '1731936600';

            $fetch_dates_transient = get_transient( 'bookingpress_retrieve_bf_sale_dates' );

            $current_year = date('Y', current_time('timestamp', true) );

            if( false ===  $fetch_dates_transient){
                $fetch_url = 'https://bookingpressplugin.com/bpa_misc/bf_sale_dates.json';
                
                $fetch_dates = wp_remote_get( $fetch_url, array( 'timeout' => 4000, 'accept' => 'application/json' ) );
                
                if( !is_wp_error( $fetch_dates ) ){
                    $details = wp_remote_retrieve_body( $fetch_dates );
                    $date_details = json_decode( $details, true );

                    set_transient( 'bookingpress_retrieve_bf_sale_dates', $date_details, ( HOUR_IN_SECONDS * 12 ) );
                }
            } else {
                $date_details = $fetch_dates_transient;
            }

            if( !empty( $date_details[ $current_year ] ) ){
                $start_time = $date_details[ $current_year ]['start_time'];
            }

            return $start_time;
        }

        function bookingpress_get_bf_sale_end_time(){
            
            $end_time = '1733571000';

            $fetch_dates_transient = get_transient( 'bookingpress_retrieve_bf_sale_dates' );

            $current_year = date('Y', current_time('timestamp', true) );

            if( false ===  $fetch_dates_transient){
                $fetch_url = 'https://bookingpressplugin.com/bpa_misc/bf_sale_dates.json';
                
                $fetch_dates = wp_remote_get( $fetch_url, array( 'timeout' => 4000, 'accept' => 'application/json' ) );
                
                if( !is_wp_error( $fetch_dates ) ){
                    $details = wp_remote_retrieve_body( $fetch_dates );
                    $date_details = json_decode( $details, true );

                    set_transient( 'bookingpress_retrieve_bf_sale_dates', $date_details, ( HOUR_IN_SECONDS * 12 ) );
                }
            } else {
                $date_details = $fetch_dates_transient;
            }

            if( !empty( $date_details[ $current_year ] )){
                $end_time = $date_details[ $current_year ]['end_time'];
            }

            return $end_time;
        }

        /**
         * Any user role change to administrator from backend then all BookingPress capabilities assign
         *
         * @param  mixed $user_id
         * @param  mixed $role
         * @param  mixed $old_roles
         * @return void
         */
        function bookingpress_assign_caps_on_role_change($user_id, $role, $old_roles){
            global $BookingPress;
            if(!empty($user_id) && $role == "administrator"){
                $bookingpressroles = $BookingPress->bookingpress_capabilities();
                $userObj = new WP_User($user_id);
                foreach ($bookingpressroles as $bookingpress_role => $bookingpress_role_desc) {
                    $userObj->add_cap($bookingpress_role);
                }
                unset($bookingpress_role);
                unset($bookingpress_roles);
                unset($bookingpress_role_desc);
            }
        }
        
        /**
         * Skip Wizard module at backend
         *
         * @return void
         */
        function bookingpress_skip_wizard_func(){
			global $wpdb, $BookingPress;
			$response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant']        = 'error';
                $response['title']          = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']            = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                echo wp_json_encode($response);
                exit;
            }
			
			update_option('bookingpress_lite_wizard_complete', 1);

			$response['variant']        = 'success';
			$response['title']          = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['msg']            = esc_html__('Wizard skipped successfully', 'bookingpress-appointment-booking');

			echo wp_json_encode($response);
			exit;
		}
        
        /**
         * Save lite wizard details
         *
         * @return void
         */
        function bookingpress_save_lite_wizard_settings_func(){
            global $wpdb, $BookingPress, $tbl_bookingpress_default_workhours, $tbl_bookingpress_services, $tbl_bookingpress_customize_settings;
			
			$response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant']        = 'error';
                $response['title']          = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']            = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                echo wp_json_encode($response);
                exit;
            }

            $is_wizard_saved = get_option( 'bookingpress_lite_wizard_complete' );

            if( 1 == $is_wizard_saved ){
                return;
            }

			$bookingpress_wizard_data = !empty($_POST['wizard_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['wizard_data']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if(!empty($bookingpress_wizard_data)){
				$bookingpress_company_fields_data = !empty($bookingpress_wizard_data['company_fields_data']) ? $bookingpress_wizard_data['company_fields_data'] : array();
				$bookingpress_booking_options = !empty($bookingpress_wizard_data['booking_options']) ? $bookingpress_wizard_data['booking_options'] : array();
				$bookingpress_service_options = !empty($bookingpress_wizard_data['service_options']) ? $bookingpress_wizard_data['service_options'] : array();
				$bookingpress_styling_options = !empty($bookingpress_wizard_data['styling_options']) ? $bookingpress_wizard_data['styling_options'] : array();

                if(!empty($bookingpress_company_fields_data)){
					$bookingpress_logo = $bookingpress_company_fields_data['logo_img'];
					$bookingpress_logo_url = $bookingpress_company_fields_data['logo'];

					if( !empty($bookingpress_company_fields_data['logo_img']) && !empty($bookingpress_company_fields_data['logo_img']) ){
						$bookingpress_upload_image_name = $bookingpress_logo;

                        $upload_dir                 = BOOKINGPRESS_UPLOAD_DIR . '/';
						$bookingpress_new_file_name = current_time('timestamp') . '_' . $bookingpress_upload_image_name;
						$upload_path                = $upload_dir . $bookingpress_new_file_name;

                        $bookingpress_upload_res                        = new bookingpress_fileupload_class( $bookingpress_logo_url, true );
                        $bookingpress_upload_res->check_cap             = true;
                        $bookingpress_upload_res->check_nonce           = true;
                        $bookingpress_upload_res->nonce_data            = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
                        $bookingpress_upload_res->nonce_action          = 'bpa_wp_nonce';
                        $bookingpress_upload_res->check_only_image   = true;
                        $bookingpress_upload_res->check_specific_ext = false;
                        $bookingpress_upload_res->allowed_ext        = array();
                        $upload_response = $bookingpress_upload_res->bookingpress_process_upload( $upload_path );

                        if( true == $upload_response ){                            
                            $bookingpress_file_name_arr = explode('/', $bookingpress_logo_url);
                            $bookingpress_file_name     = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
                            if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
                                wp_delete_file(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
                            }
                            
                            $bookingpress_logo_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_new_file_name;
                            $BookingPress->bookingpress_update_settings('company_avatar_img', 'company_setting', $bookingpress_logo);
                            $BookingPress->bookingpress_update_settings('company_avatar_url', 'company_setting', $bookingpress_logo_url);
                        }
					}

					$BookingPress->bookingpress_update_settings('company_name', 'company_setting', $bookingpress_company_fields_data['company_name']);
					$BookingPress->bookingpress_update_settings('company_address', 'company_setting', $bookingpress_company_fields_data['address']);
					$BookingPress->bookingpress_update_settings('company_phone_country', 'company_setting', $bookingpress_company_fields_data['country']);
					$BookingPress->bookingpress_update_settings('company_phone_number', 'company_setting', $bookingpress_company_fields_data['phone_no']);
					$BookingPress->bookingpress_update_settings('company_website', 'company_setting', $bookingpress_company_fields_data['website']);
					

                    $BookingPress->bookingpress_update_settings('default_phone_country_code', 'general_setting', $bookingpress_company_fields_data['country']);

					$BookingPress->bookingpress_update_settings('default_date_format', 'general_setting', $bookingpress_company_fields_data['date_format']);
                    $BookingPress->bookingpress_update_settings('default_time_format', 'general_setting', $bookingpress_company_fields_data['time_format']);

                    $bookingpress_anonymous_data = !empty($bookingpress_company_fields_data['anonymous_usage']) ? $bookingpress_company_fields_data['anonymous_usage'] : 'false';
                    if($bookingpress_anonymous_data == "true"){
                        $BookingPress->bookingpress_update_settings('anonymous_data', 'general_setting', 'true');
                    }else{
                        $BookingPress->bookingpress_update_settings('anonymous_data', 'general_setting', 'false');
                    }
				}

				if(!empty($bookingpress_booking_options)){
					$bookingpress_days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
					foreach($bookingpress_days as $day_key => $day_val){
                        $bookingpress_start_time = $bookingpress_booking_options[$day_val]['start_time'];
                        $bookingpress_end_time = $bookingpress_booking_options[$day_val]['end_time'];

                        if($bookingpress_start_time == "off"){
                            $bookingpress_start_time = $bookingpress_end_time = null;
                        }else if($bookingpress_start_time == "24:00:00" || $bookingpress_end_time == "24:00:00"){
                            if($bookingpress_start_time == "24:00:00"){
                                $bookingpress_start_time = "00:00:00";
                            }

                            if($bookingpress_end_time == "24:00:00"){
                                $bookingpress_end_time = "00:00:00";
                            }
                        }

						$bookingpress_insert_workhour_data = array(
							'bookingpress_workday_key' => $day_val,
							'bookingpress_start_time'  => $bookingpress_start_time,
							'bookingpress_end_time'    => $bookingpress_end_time,
							'bookingpress_is_break'    => 0,
							'bookingpress_created_at'  => current_time('mysql'),
						);

                        $wpdb->update($tbl_bookingpress_default_workhours, $bookingpress_insert_workhour_data, array('bookingpress_workday_key' => $day_val));
					}
					
					
					$bookingpress_payment_currency = $bookingpress_booking_options['currency'];
					$BookingPress->bookingpress_update_settings('payment_default_currency', 'payment_setting', $bookingpress_payment_currency);
				}

				if(!empty($bookingpress_service_options)){
					$bookingpress_service_fields_data = !empty($bookingpress_service_options['service_fields_details']) ? $bookingpress_service_options['service_fields_details'] : array();

					if(!empty($bookingpress_service_fields_data)){
						foreach($bookingpress_service_fields_data as $service_key => $service_val){
							$service_name = $service_val['service_name'];
							$service_price = floatval($service_val['price']);
							$duration_val = $service_val['duration_val'];
							$duration_unit = $service_val['duration_unit'];
							$service_description = $service_val['description'];

							if(!empty($service_name) && !empty($service_price) && !empty($duration_val) && !empty($duration_unit)){
								$service_pos_data = $wpdb->get_row('SELECT * FROM ' . $tbl_bookingpress_services . ' ORDER BY bookingpress_service_position DESC LIMIT 1', ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
								
								$service_position = 0;
								if (! empty($service_pos_data) ) {
									$service_position = $service_pos_data['bookingpress_service_position'] + 1;
								}

								$bookingpress_service_data = array(
									'bookingpress_category_id' => 0,
									'bookingpress_service_name' => $service_name,
									'bookingpress_service_price' => $service_price,
									'bookingpress_service_duration_val' => $duration_val,
									'bookingpress_service_duration_unit' => $duration_unit,
									'bookingpress_service_description' => $service_description,
									'bookingpress_service_position' => $service_position,
									'bookingpress_servicedate_created' => current_time('mysql'),
								);

								$wpdb->insert($tbl_bookingpress_services, $bookingpress_service_data);
								$bookingpress_service_id = $wpdb->insert_id;
							}
						}
					}
				}

				if(!empty($bookingpress_styling_options)){
					$bookingpress_selected_fonts = !empty($bookingpress_styling_options['font_family']) ? $bookingpress_styling_options['font_family'] : '';
					if(!empty($bookingpress_selected_fonts)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'title_font_family',
							'bookingpress_setting_value' => $bookingpress_selected_fonts,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'title_font_family','bookingpress_setting_type' => 'booking_form') );
					}
					
					$bookingpress_primary_color = !empty($bookingpress_styling_options['primary_color']) ? $bookingpress_styling_options['primary_color'] : '';
					if(!empty($bookingpress_primary_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'primary_color',
							'bookingpress_setting_value' => $bookingpress_primary_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'primary_color','bookingpress_setting_type' => 'booking_form') );
					}

					$bookingpress_title_color = !empty($bookingpress_styling_options['title_color']) ? $bookingpress_styling_options['title_color'] : '';
					if(!empty($bookingpress_title_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'label_title_color',
							'bookingpress_setting_value' => $bookingpress_title_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'label_title_color','bookingpress_setting_type' => 'booking_form') );
					}

					$bookingpress_subtitle_color = !empty($bookingpress_styling_options['subtitle_color']) ? $bookingpress_styling_options['subtitle_color'] : '';
					if(!empty($bookingpress_subtitle_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'sub_title_color',
							'bookingpress_setting_value' => $bookingpress_subtitle_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'sub_title_color','bookingpress_setting_type' => 'booking_form') );
					}

					$bookingpress_content_color = !empty($bookingpress_styling_options['content_color']) ? $bookingpress_styling_options['content_color'] : '';
					if(!empty($bookingpress_content_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'content_color',
							'bookingpress_setting_value' => $bookingpress_content_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'content_color','bookingpress_setting_type' => 'booking_form') );
					}
                    $bookingpress_primary_background_color = !empty($bookingpress_styling_options['primary_background_color']) ? $bookingpress_styling_options['primary_background_color'] : '#e2faf1';
                    if(!empty($bookingpress_content_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'primary_background_color',
							'bookingpress_setting_value' => $bookingpress_primary_background_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'primary_background_color','bookingpress_setting_type' => 'booking_form') );
					}
					
					$bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
					$bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
					$bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');                    
					$bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
					$bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
					$bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
					$bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
					$bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');
                   			$bookingpress_border_color = $BookingPress->bookingpress_get_customize_settings('border_color', 'booking_form');

					$bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
					$bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
					$bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
					$bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
					$bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
					$bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
					$bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
					$bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';
                    			$bookingpress_border_color = !empty($bookingpress_border_color) ? $bookingpress_border_color : '#CFD6E5';
                    


					$bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';
					$bookingpress_custom_data_arr['action'][] = 'bookingpress_save_booking_form_settings';                       
					$my_booking_form = array(
						'background_color' => $bookingpress_background_color,
						'row_background_color' => $bookingpress_footer_background_color,
						'primary_color' => $bookingpress_primary_color,
						'content_color' => $bookingpress_content_color,
						'label_title_color' => $bookingpress_label_title_color,
						'title_font_family' => $bookingpress_title_font_family,        
						'sub_title_color'   => $bookingpress_sub_title_color,
						'price_button_text_color' => $bookingpress_price_button_text_color,
                        			'border_color'      => $bookingpress_border_color,
					);      

					$booking_form = array(
						'background_color' => $bookingpress_background_color,
						'footer_background_color' => $bookingpress_footer_background_color,
						'primary_color' => $bookingpress_primary_color,
						'primary_background_color'=> $bookingpress_primary_background_color,
						'label_title_color' => $bookingpress_label_title_color,
						'title_font_family' => $bookingpress_title_font_family,                
						'content_color' => $bookingpress_content_color,                
						'price_button_text_color' => $bookingpress_price_button_text_color,
						'sub_title_color' => $bookingpress_sub_title_color,
                        			'border_color'      => $bookingpress_border_color,
					);        
					$bookingpress_custom_data_arr['booking_form'] = $booking_form;
					$bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;
					$BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);
				}


				$response['variant']        = 'success';
                $response['title']          = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']            = esc_html__('Data saved successfully', 'bookingpress-appointment-booking');
			}
			
			echo wp_json_encode($response);
			exit;
        }
        
        /**
         * Load wizard view file
         *
         * @return void
         */
        function bookingpress_load_wizard_view_func(){
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/wizard/manage_wizard.php';
			require $bookingpress_load_file_name;
        }
        
        /**
         * Wizard methods / functions
         *
         * @return void
         */
        function bookingpress_wizard_vue_methods_func(){
            global $bookingpress_notification_duration;
			?>
			bookingpress_upload_company_avatar_func(response, file, fileList){
                const vm2 = this
                if(response != ''){
                    vm2.wizard_steps_data.company_fields_data.logo = response.upload_url
                    vm2.wizard_steps_data.company_fields_data.logo_img = response.upload_file_name
                }
            },
            bookingpress_company_avatar_upload_limit(files, fileList){
                const vm2 = this
                if(files.length >= 1){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Multiple files not allowed', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                }
            },
            checkUploadedFile(file){
                const vm2 = this
                if(file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp'){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please upload jpg/png file only', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    return false
                }else{
                    var bpa_image_size = parseInt(file.size / 1000000);
                    if(bpa_image_size > 1){
                        vm2.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Please upload maximum 1 MB file only', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });                    
                        return false
                    }
                }
            },
            bookingpress_company_avatar_upload_err(err, file, fileList){
                const vm2 = this
                var bookingpress_err_msg = '<?php esc_html_e('Something went wrong', 'bookingpress-appointment-booking'); ?>';
                if(err != '' || err != undefined){
                    bookingpress_err_msg = err
                }
                vm2.$notify({
                    title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                    message: bookingpress_err_msg,
                    type: 'error',
                    customClass: 'error_notification',
                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                });
            },
            bookingpress_remove_company_avatar(){
                const vm = this
                var upload_url = vm.wizard_steps_data.company_fields_data.logo                     
                var upload_filename = vm.wizard_steps_data.company_fields_data.logo_img 

                var postData = { action:'bookingpress_remove_uploaded_file',upload_file_url: upload_url,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {                    
                    vm.wizard_steps_data.company_fields_data.logo = ''
                    vm.wizard_steps_data.company_fields_data.logo_img = ''
                    vm.$refs.avatarRef.clearFiles()
                }.bind(vm) )
                .catch( function (error) {
                    console.log(error);
                });
            },
			bookingpress_add_service(){
				const vm = this
				var bookingpress_default_obj = vm.wizard_steps_data.service_options.service_details[0];
				vm.wizard_steps_data.service_options.service_details.push(bookingpress_default_obj);
				vm.wizard_steps_data.service_options.service_fields_details.push({service_name: '', price: '', duration_val: '30', duration_unit: 'm', description: ''});
			},
			bpa_remove_service(remove_index){
				const vm = this
				vm.wizard_steps_data.service_options.service_details.splice(remove_index, 1)
			},
			bookingpress_previous_tab(current_tab){
				const vm = this
				if(current_tab == "booking_options"){
					vm.bookingpress_active_tab = 'company_settings';
				}else if(current_tab == "service_options"){
                    vm.bookingpress_active_tab = 'booking_options';
				}else if(current_tab == "styling_options"){
					vm.bookingpress_active_tab = 'service_options';
				}else if(current_tab == "final_step"){
					vm.bookingpress_active_tab = 'styling_options';
				}
			},
			bookingpress_next_tab(current_tab){
				const vm = this
				if(current_tab == "company_settings"){
					vm.bookingpress_active_tab = 'booking_options';
				}else if(current_tab == "booking_options"){
					vm.bookingpress_active_tab = 'service_options';
				}else if(current_tab == "service_options"){
					vm.bookingpress_active_tab = 'styling_options';
				}else if(current_tab == "styling_options"){
					vm.bookingpress_last_step_disabled = false;
					vm.bookingpress_active_tab = 'final_step';
					vm.final_step_loader = '1';
					var postData = [];
					postData.action = 'bookingpress_save_lite_wizard_settings'
					postData.wizard_data = vm.wizard_steps_data
					postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant != 'error'){
							vm.final_step_loader = '0';
						}else{
							console.log(response.data.msg);
						}
					}.bind(this) )
					.catch( function (error) {                    
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					});
				}
			},
			bookingpress_copy_content(copy_data){
				const vm = this;
				var bookingpress_selected_placholder = copy_data;
				var bookingpress_dummy_elem = document.createElement("textarea");
				document.body.appendChild(bookingpress_dummy_elem);
				bookingpress_dummy_elem.value = bookingpress_selected_placholder;
				bookingpress_dummy_elem.select();
				document.execCommand("copy");
				document.body.removeChild(bookingpress_dummy_elem);
				vm.$notify(
				{ 
					title: '<?php esc_html_e('Success', 'bookingpress-appointment-booking'); ?>',
					message: '<?php echo esc_html_e('Text copied successfully.','bookingpress-appointment-booking'); ?>',
					type: 'success',
					customClass: 'success_notification',
					duration:<?php echo intval($bookingpress_notification_duration); ?>,
				});
			},
            bookingpress_finish_wizard( download_affi, download_arf ){

                const vm = this;
                vm.wizard_steps_data.install_new_product.is_display_download_save_loader = 1;
                vm.wizard_steps_data.install_new_product.is_disabled = 1;

                var postData = [];
                postData.download_affi = download_affi,
                postData.download_arf = download_arf,
                postData.action = 'bookingpress_download_wizard_product'
				postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
                    
                    vm.wizard_steps_data.install_new_product.is_display_download_save_loader = 0;
                    vm.wizard_steps_data.install_new_product.is_disabled = 0;

					if(response.data.variant != 'error'){
                        window.location.href = '<?php echo esc_html(admin_url() . 'admin.php?page=bookingpress'); ?>';
					}else{
						console.log(response.data.msg);
					}
				}.bind(this) )
				.catch( function (error) {                    
					vm.$notify({
						title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
						message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
            },
			bookingpress_skip_wizard(){
				var postData = [];
				postData.action = 'bookingpress_lite_skip_wizard'
				postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					if(response.data.variant != 'error'){
						window.location.href = '<?php echo esc_html(admin_url() . 'admin.php?page=bookingpress'); ?>';
					}else{
						console.log(response.data.msg);
					}
				}.bind(this) )
				.catch( function (error) {                    
					vm.$notify({
						title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
						message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
			},
            bookingpress_generate_alpha_color(selected_color){
                const vm = this
                var opacity_color = Math.round(Math.min(Math.max(0.12 || 1, 0), 1) * 255);
                var primary_background_color = selected_color+(opacity_color.toString(16).toUpperCase())
                vm.wizard_steps_data.styling_options.primary_background_color = primary_background_color
            },
            bookingpress_set_workhour_value_wizard(worktime,work_hour_day){
                const vm = this
                if(vm.wizard_steps_data.booking_options[work_hour_day].end_time == 'off') {   
                    vm.working_hours_arr.forEach(function(currentValue, index, arr){
                        if(currentValue.start_time == worktime) {
                            vm.wizard_steps_data.booking_options[work_hour_day].end_time = arr[index]['end_time'] ;
                        }
                    });
                }
                else if(worktime >= vm.wizard_steps_data.booking_options[work_hour_day].end_time ) {
                    vm.working_hours_arr.forEach(function(currentValue, index, arr){
                        if(currentValue.start_time == worktime) {
                            vm.wizard_steps_data.booking_options[work_hour_day].end_time = arr[index]['end_time'] ;
                        }
                    });
                }
                else if(worktime != 'off' && vm.wizard_steps_data.booking_options[work_hour_day].end_time == undefined) {
                    vm.working_hours_arr.forEach(function(currentValue, index, arr){
                        if(currentValue.start_time == worktime) {
                            vm.wizard_steps_data.booking_options[work_hour_day].end_time = arr[index]['end_time'] ;
                        }                                                                    
                    });
                }
                / * vm.wizard_steps_data.booking_options[work_hour_day].end_time */
            },
            bookingpress_check_workhour_value_wizard(workhour_time,work_hour_day) {  
                if(workhour_time == 'off') {
                    const vm = this
                    vm.wizard_steps_data.booking_options[work_hour_day].start_time = 'off';
                }
            },
			<?php
        }
        
        /**
         * Wizard onload methods
         *
         * @return void
         */
        function bookingpress_wizard_on_load_methods_func(){
            ?>
                document.body.classList.add('bpa-fullscreen-wizard-setup-container');
            <?php
        }
        
        /**
         * Wizard module data variables
         *
         * @return void
         */
        function bookingpress_wizard_dynamic_data_fields_func(){
            global $bookingpress_lite_wizard_vue_data_fields, $bookingpress_global_options;

            $bookingpress_lite_wizard_vue_data_fields = array();

            $bookingpress_lite_wizard_vue_data_fields['bookingpress_active_tab'] = 'company_settings';
			$bookingpress_lite_wizard_vue_data_fields['bookingpress_last_step_disabled'] = true;
			$bookingpress_lite_wizard_vue_data_fields['bookingpress_disabled_tabs'] = true;
			
			$bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();

            $bookingpress_country_list               = $bookingpress_options['country_lists'];
			$bookingpress_lite_wizard_vue_data_fields['phone_countries_details'] = json_decode($bookingpress_country_list);

			$bookingpress_lite_wizard_vue_data_fields['comShowFileList'] = false;

			$bookingpress_default_time_format = $bookingpress_options['wp_default_time_format'];

			$bookingpress_workhours_arr = array();
			$bookingpress_workhours_arr[] = array(
				'start_time' => 'off',
				'end_time' => 'off',
				'formatted_start_time' => esc_html__('Off', 'bookingpress-appointment-booking'),
				'formatted_end_time' => esc_html__('Off', 'bookingpress-appointment-booking'),
			);
			$default_start_time      = '00:00:00';
			$default_end_time        = '23:55:00';
			$step_duration_val       = 5;
			do{
				$tmp_start_time = $default_start_time;

				$tmp_time_obj = new DateTime($tmp_start_time);
				$tmp_time_obj->add(new DateInterval('PT' . $step_duration_val . 'M'));
				$tmp_end_time = $tmp_time_obj->format('H:i:s');
				
				if($tmp_end_time == "00:00:00"){
					$tmp_end_time = "24:00:00";
				}

				$bookingpress_workhours_arr[] = array(
					'start_time' => $tmp_start_time,
					'end_time' => $tmp_end_time,
					'formatted_start_time' => date($bookingpress_default_time_format, strtotime($tmp_start_time)),
					'formatted_end_time' => date($bookingpress_default_time_format, strtotime($tmp_end_time))." ".($tmp_end_time == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' ),
				);

				if($tmp_end_time == "24:00:00"){
					break;
				}

				$default_start_time = $tmp_end_time;
			}while($default_start_time <= $default_end_time);
			
			$bookingpress_lite_wizard_vue_data_fields['working_hours_arr'] = $bookingpress_workhours_arr;

			$bookingpress_countries_currency_details = json_decode($bookingpress_options['countries_json_details']);
			$bookingpress_lite_wizard_vue_data_fields['bookingpress_currency_options'] = $bookingpress_countries_currency_details;

			$bookingpress_inherit_fonts_list = array('Inherit Fonts',);
            $bookingpress_default_fonts_list = $bookingpress_global_options->bookingpress_get_default_fonts();
            $bookingpress_google_fonts_list  = $bookingpress_global_options->bookingpress_get_google_fonts();
			$bookingpress_fonts_list         = array(
                array(
                    'label'   => esc_html__('Inherit Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_inherit_fonts_list,
                ),
                array(
                    'label'   => esc_html__('Default Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_default_fonts_list,
                ),
                array(
                    'label'   => esc_html__('Google Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_google_fonts_list,
                ),
            );
			$bookingpress_lite_wizard_vue_data_fields['fonts_list'] = $bookingpress_fonts_list;

			$bookingpress_lite_wizard_vue_data_fields['final_step_loader'] = '1';

            $default_phone_country_code = $this->bookingpress_get_settings('default_phone_country_code','general_setting');

            $bookingpress_lite_wizard_vue_data_fields['wizard_steps_data'] = array(
				'company_fields_data' => array(
					'company_name' => '',
					'address' => '',
                    'time_format' => 'g:i a',
					'date_format' => 'F j, Y',
					'country' => $default_phone_country_code,
					'phone_no' => '',
					'website' => '',
					'logo' => '',
					'logo_img' => '',
					'logo_list' => '',
                    'anonymous_usage' => true,
				),
				'booking_options' => array(
					'monday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'tuesday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'wednesday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'thursday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'friday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'saturday' => array(
						'start_time' => 'off',
						'end_time' => 'off',
					),
					'sunday' => array(
						'start_time' => 'off',
						'end_time' => 'off',
					),
					'currency' => 'USD',
				),
				'service_options' => array(
					'service_details' => array(
						array(
							'service_name_label' => esc_html__('Service Name', 'bookingpress-appointment-booking'),
							'price_label' => esc_html__('Price', 'bookingpress-appointment-booking'),
							'duration_label' => esc_html__('Duration', 'bookingpress-appointment-booking'),
							'description_label' => esc_html__('Description', 'bookingpress-appointment-booking'),
						),
					),
					'service_fields_details' => array(
						array(
							'service_name' => '',
							'price' => '',
							'duration_val' => '30',
							'duration_unit' => 'm',
							'description' => '',
						),
					),
				),
				'styling_options' => array(
					'font_family' => 'Poppins',
					'primary_color' => '#12D488',
                    'primary_background_color' => '#e2faf1',
					'title_color' => '#202C45',
					'subtitle_color' => '#535D71',
					'content_color' => '#727E95',
				),
                'install_new_product' => array(
                    'download_affilatepress' => true,
                    'download_arforms' => true,
                    'is_display_download_save_loader' => '0',
                    'is_disabled' => false
                )
			);

            echo wp_json_encode($bookingpress_lite_wizard_vue_data_fields);
        }
        
        /**
         * Wizard module helper variables
         *
         * @return void
         */
        function bookingpress_wizard_dynamic_helper_vars_func(){
            global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang);
			<?php
        }

       
        
        /**
         * Add gutenberg blocks
         *
         * @return void
         */
        function bookingpress_add_gutenbergblock() {
            register_block_type( BOOKINGPRESS_DIR . '/js/build/bookingform' );                
            register_block_type( BOOKINGPRESS_DIR . '/js/build/mybooking' );    
        }

        
        /**
         * Dismiss admin notice
         *
         * @return void
         */
        function bookingpress_dismisss_admin_notice_func(){
            update_option('bookingpress_customize_changes_notice', 1);
            update_option('bookingpress_customize_changes_notice_1.0.51', 0);
        }

        
        /**
         * Deactivate pro version when lite deactivate
         *
         * @return void
         */
        public static function deactivate_lite_version()
        {
            $dependent = 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php';
            if (is_plugin_active($dependent) ) {
                add_action('update_option_active_plugins', array( 'BookingPress', 'deactivate_pro_version' ));
            }
        }
        
        /**
         * Deactivate pro version when lite version deactivate
         *
         * @return void
         */
        public static function deactivate_pro_version()
        {
            $dependent = 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php';
            deactivate_plugins($dependent);
        }
        
        /**
         * Pro version activate or not
         *
         * @return void
         */
        public static function bpa_is_pro_active(){
            if( !function_exists('is_plugin_active') ){
                include ABSPATH . '/wp-admin/includes/plugin.php';
            }
            $plugin_slug = 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php';
            return is_plugin_active( $plugin_slug );
        }
        
        /**
         * Is pro version exists or not
         *
         * @return void
         */
        public static function bpa_is_pro_exists(){
            $plugin_dir = WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php';
            return file_exists( $plugin_dir );
        }
        
        /**
         * Get pro version plugin version
         *
         * @return void
         */
        public static function bpa_pro_plugin_version(){
            $bpa_pro_version = get_option( 'bookingpress_pro_version' );
            return $bpa_pro_version;
        }
        
        /**
         * Insert other debug logs like integration logs
         *
         * @param  mixed $bookingpress_other_log_type
         * @param  mixed $bookingpress_other_log_event
         * @param  mixed $bookingpress_other_log_event_from
         * @param  mixed $bookingpress_other_log_raw_data
         * @param  mixed $bookingpress_ref_id
         * @return void
         */
        public function bookingpress_other_debug_logs_func( $bookingpress_other_log_type = '', $bookingpress_other_log_event = '', $bookingpress_other_log_event_from = '', $bookingpress_other_log_raw_data = '', $bookingpress_ref_id = 0 )
        {
            global $wpdb, $BookingPress, $bookingpress_other_debug_log_id, $tbl_bookingpress_other_debug_logs;

            if(is_array($bookingpress_other_log_raw_data)){
                $bookingpress_other_log_raw_data['backtrace_summary'] = wp_debug_backtrace_summary( null, 0, false );
            }else{
                $bookingpress_other_log_raw_data .= " | Backtrace Summary ==> ".wp_json_encode(wp_debug_backtrace_summary( null, 0, false ));
            }

            if(!empty($bookingpress_other_log_raw_data['appointment_data']['card_number'])){
                $bookingpress_other_log_raw_data['appointment_data']['card_number'] = str_repeat('X', strlen($bookingpress_other_log_raw_data['appointment_data']['card_number']) - 12) . substr($bookingpress_other_log_raw_data['appointment_data']['card_number'], -4);
                $bookingpress_other_log_raw_data['appointment_data']['expire_month'] = '';
                $bookingpress_other_log_raw_data['appointment_data']['expire_year'] = '';
                $bookingpress_other_log_raw_data['appointment_data']['cvv'] = '';
            }else if(!empty($bookingpress_other_log_raw_data['card_details']['card_number'])){
                $bookingpress_other_log_raw_data['card_details']['card_number'] = str_repeat('X', strlen($bookingpress_other_log_raw_data['card_details']['card_number']) - 12) . substr($bookingpress_other_log_raw_data['card_details']['card_number'], -4);
                $bookingpress_other_log_raw_data['card_details']['expire_month'] = '';
                $bookingpress_other_log_raw_data['card_details']['expire_year'] = '';
                $bookingpress_other_log_raw_data['card_details']['cvv'] = '';
            }

            $bookingpress_active_gateway = false;

            $bookingpress_active_gateway = $BookingPress->bookingpress_get_settings($bookingpress_other_log_type, 'debug_log_setting');

            $inserted_id = 0;
            if ($bookingpress_active_gateway == 'true' ) {
                if ($bookingpress_ref_id == null ) {
                    $bookingpress_ref_id = 0;
                }

                $bookingpress_database_log_data = array(
                'bookingpress_other_log_ref_id'     => sanitize_text_field($bookingpress_ref_id),
                'bookingpress_other_log_type'       => sanitize_text_field($bookingpress_other_log_type),
                'bookingpress_other_log_event'      => sanitize_text_field($bookingpress_other_log_event),
                'bookingpress_other_log_event_from' => sanitize_text_field($bookingpress_other_log_event_from),
                'bookingpress_other_log_raw_data'   => wp_json_encode(stripslashes_deep($bookingpress_other_log_raw_data)),
                'bookingpress_other_log_added_date' => current_time('mysql'),
                );

                $wpdb->insert($tbl_bookingpress_other_debug_logs, $bookingpress_database_log_data);
                $inserted_id = $wpdb->insert_id;

                if (empty($bookingpress_ref_id) ) {
                    $bookingpress_ref_id = $inserted_id;
                }
            }
            $bookingpress_other_debug_log_id = $bookingpress_ref_id;
            return $inserted_id;
        }

        function bookingpress_get_sysinfo_func()
        {
            global $BookingPress;

            $is_debug_mode_enabled = $BookingPress->bookingpress_get_settings( 'debug_mode', 'general_setting' );
            
            if (! empty($_REQUEST['bookingpress_sysinfo']) && ( $_REQUEST['bookingpress_sysinfo'] == 'bkp999repute' ) && 'true' == $is_debug_mode_enabled  ) {
                include 'wp-load.php';
                include_once ABSPATH . 'wp-admin/includes/plugin.php';

                $directaccesskey = 'bkp999repute';
                $directaccess    = isset($_REQUEST['bookingpress_sysinfo']) ? sanitize_text_field($_REQUEST['bookingpress_sysinfo']) : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash               
                

                if (is_user_logged_in() || $directaccesskey == $directaccess ) {
                } else {
                    $redirect_to = user_admin_url();
                    wp_safe_redirect($redirect_to);
                }
            
                $php_version = phpversion();

                $server_ip = isset($_SERVER['SERVER_ADDR']) ? sanitize_text_field($_SERVER['SERVER_ADDR']) : '';

                $servername = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field($_SERVER['SERVER_NAME']) : '';

                $upload_max_filesize = ini_get('upload_max_filesize');

                $post_max_size = ini_get('post_max_size');

                $max_input_vars = ini_get('max_input_vars');
              
                if (ini_get('safe_mode') ) {
                    $safe_mode = 'On';
                } else {
                    $safe_mode = 'Off';
                }

                $memory_limit = ini_get('memory_limit');

                $apache_version = '';

                $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field($_SERVER['SERVER_SOFTWARE']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                $server_signature  = isset($_SERVER['SERVER_SIGNATURE']) ? sanitize_text_field($_SERVER['SERVER_SIGNATURE']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

                if (function_exists('apache_get_version') ) {
                    $apache_version = apache_get_version();
                } else {
                    $apache_version = $server_software . '( ' . $server_signature . ' )';
                }

                $system_info = php_uname();

                global $wpdb;
                $mysql_server_version = $wpdb->db_version();

                // WordPress details

                $wordpress_version = get_bloginfo('version');

                $wordpress_sitename = get_bloginfo('name');

                $wordpress_sitedesc = get_bloginfo('description');

                $wordpress_wpurl = site_url();

                $wordpress_url = home_url();

                $wordpress_admin_email = get_bloginfo('admin_email');

                $wordpress_language = get_bloginfo('language');

                $wordpress_timezone = wp_timezone_string();

                $wordpress_date_format = get_option('date_format');

                $wordpress_time_format = get_option('time_format');

                $my_theme                      = wp_get_theme();
                $wordpress_templateurl         = $my_theme->get('Name');
                $wordpress_templateurl_version = $my_theme->get('Version');

                $wordpress_charset = get_bloginfo('charset');

                $wordpress_debug = WP_DEBUG;

                if ($wordpress_debug == true ) {
                    $wordpress_debug = 'On';
                } else {
                    $wordpress_debug = 'Off';
                }

                if (is_multisite() ) {
                    $wordpress_multisite = 'Yes';
                } else {
                    ( $wordpress_multisite = 'No' );
                }

                $plugin_dir_path      = WP_PLUGIN_DIR;
                $upload_dir_path      = wp_upload_dir();
                $bookingpress_active  = 'Deactive';
                $bookingpress_version = '';
                if (is_plugin_active('bookingpress-appointment-booking/bookingpress-appointment-booking.php') ) {
                    $bookingpress_active  = 'Active';
                    $bookingpress_version = get_option('bookingpress_version');
                }

                $folderpermission = substr(sprintf('%o', fileperms($upload_dir_path['basedir'])), -4);

                $folderlogpermission = substr(sprintf('%o', fileperms($plugin_dir_path . '/bookingpress-appointment-booking/log/')), -4);

                if (file_exists($plugin_dir_path . '/bookingpress-appointment-booking/log/response.txt') ) {
                    $folderlogfilepermission = substr(sprintf('%o', fileperms($plugin_dir_path . '/bookingpress-appointment-booking/log/response.txt')), -4);
                }

                $plugin_list    = get_plugins();
                $plugin_detail         = array();
                $active_plugins = get_option('active_plugins');

                foreach ( $plugin_list as $key => $plugin_detail ) {
                    $is_active = in_array($key, $active_plugins);
                    // filter for only gravityforms ones, may get some others if using our naming convention
                    if ($is_active == 1 ) {
                        $name      = substr($key, 0, strpos($key, '/'));
                        $plugins_main_arr[] = array(
                        'name'      => $plugin_detail['Name'],
                        'version'   => $plugin_detail['Version'],
                        'is_active' => $is_active,
                        );
                    }
                }
                
                $bookingpress_module = array(                    
                    'bookingpress_staffmember_module' => 'Staff Member Management',
                    'bookingpress_service_extra_module' => 'Service Extra',
                    'bookingpress_coupon_module' => 'Coupon Management',
                    'bookingpress_deposit_payment_module' => 'Desposit Payment',
                    'bookingpress_bring_anyone_with_you_module' => 'Multiple Quantity',                    
                );
                foreach($bookingpress_module as $key => $value) {                    
                    $is_module_active = get_option($key);
                    if($is_module_active == 'true') {
                        $activated_modules[] = $value;
                    }
                }
                $activated_modules = !empty($activated_modules) ? implode(', ',$activated_modules) : '';

                ?>

                <style type="text/css">
                table
                {
                    border:2px solid #cccccc;
                    width:900px;
                    font-family:Verdana, Arial, Helvetica, sans-serif;
                    font-size:12px;
                }
                .title
                {
                    border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; font-weight:bold;
                }
                .leftrowtitle
                {
                    border-bottom:2px solid #cccccc; border-right:2px solid #cccccc; padding:5px 0px 5px 15px; width:250px; background-color:#333333; color:#FFFFFF; font-weight:bold;
                }
                .rightrowtitle
                {
                    border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; width:650px; background-color:#333333;  color:#FFFFFF; font-weight:bold;
                }
                .leftrowdetails
                {
                    border-bottom:2px solid #cccccc; border-right:2px solid #cccccc; padding:5px 0px 5px 15px; width:250px;
                }
                .rightrowdetails
                {
                    border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; width:650px;
                }    
                </style>


                <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2" class="title">Php Details</td>
                </tr>
                <tr>
                    <td class="leftrowtitle">Variable Name</td>
                    <td class="rightrowtitle">Details</td>
                </tr>
                <tr>
                    <td class="leftrowdetails">PHP Version</td>
                    <td class="rightrowdetails"><?php echo esc_html($php_version); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">System</td>
                    <td class="rightrowdetails"><?php echo esc_html($system_info); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Apache Version</td>
                    <td class="rightrowdetails"><?php echo esc_html($apache_version); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Server Ip</td>
                    <td class="rightrowdetails"><?php echo esc_html($server_ip); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Server Name</td>
                    <td class="rightrowdetails"><?php echo esc_html($servername); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Upload Max Filesize</td>
                    <td class="rightrowdetails"><?php echo esc_html($upload_max_filesize); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Post Max Size</td>
                    <td class="rightrowdetails"><?php echo esc_html($post_max_size); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Max Input Vars</td>
                    <td class="rightrowdetails"><?php echo esc_html($max_input_vars); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Safe Mode</td>
                    <td class="rightrowdetails"><?php echo esc_html($safe_mode); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Memory Limit</td>
                    <td class="rightrowdetails"><?php echo esc_html($memory_limit); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">MySql Version</td>
                    <td class="rightrowdetails"><?php echo esc_html($mysql_server_version); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Allow URL for fopen()</td>
                    <td class="rightrowdetails"><?php echo ( 1 == ini_get('allow_url_fopen') ) ? 'Yes' : 'No'; ?></td>
                </tr>
                <tr>
                    <td colspan="2" style="border-bottom:2px solid #cccccc;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" class="title">WordPress Details</td>
                </tr>
                <tr>
                    <td class="leftrowtitle">Variable Name</td>
                    <td class="rightrowtitle">Details</td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Site Title</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_sitename); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Tagline</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_sitedesc); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Version</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_version); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">WordPress address (URL)</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_wpurl); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Site address (URL)</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_url); ?></td>
                </tr>
                <?php 
                if(current_user_can('administrator')) { ?>
                <tr>
                    <td class="leftrowdetails">Admin Email</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_admin_email); ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="leftrowdetails">Charset</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_charset); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Language</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_language); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Active theme</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_templateurl) . ' (' . esc_html($wordpress_templateurl_version) . ')'; ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Debug Mode</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_debug); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Multisite Enable</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_multisite); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Timezone</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_timezone); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Date Format</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_date_format); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Time Format</td>
                    <td class="rightrowdetails"><?php echo esc_html($wordpress_time_format); ?></td>
                </tr>
                <tr>
                    <td colspan="2" style="border-bottom:2px solid #cccccc;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" class="title">Bookingpress Details</td>
                </tr>
                <tr>
                    <td class="leftrowtitle">Variable Name</td>
                    <td class="rightrowtitle">Details</td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Bookingpress Status</td>
                    <td class="rightrowdetails"><?php
                        echo esc_html($bookingpress_active);
                        if (!function_exists('is_plugin_active')) {
                            include_once ABSPATH . 'wp-admin/includes/plugin.php';
                        }
                        if( is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') ){

                            $store_url = BOOKINGPRESS_STORE_URL;
        
                            $license = trim( get_option( 'bkp_license_key' ) );

                            $package = trim( get_option( 'bkp_license_package' ) );
                            $api_params = array(
                                'edd_action' => 'check_license',
                                'license' => $license,
                                'item_id'  => $package,
                                'url' => home_url()
                            );

                            $response = wp_remote_post( $store_url, array( 'body' => $api_params, 'timeout' => 15 ) );
                            if ( is_wp_error( $response ) ) {
                                echo '&nbsp;<span style="color:red;">( Unlicensed )</span>';
                            } else {
                                $resp_body = json_decode( wp_remote_retrieve_body( $response ), true );
                                
                                if( true == $resp_body['success'] ){
                                    $expiry_date = $resp_body['expires'];
                                    if( 'lifetime' == $expiry_date ){
                                        echo '&nbsp;<span style="color:green;font-weight:bolder;"> ( Active ) </span>';
                                    } else {
                                        if( current_time('timestamp') > strtotime( $expiry_date ) ){
                                            echo '&nbsp;<span style="color:orange;font-weight:bolder;"> ( Expired ) </span>';
                                        } else {
                                            echo '&nbsp;<span style="color:green;font-weight:bolder;"> ( Active ) </span>';
                                        }
                                    }
                                } else {
                                    echo '&nbsp;<span style="color:red;">( Unlicensed )</span>';
                                }
                            }

                        }
                    ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Bookingpress Version</td>
                    <td class="rightrowdetails"><?php echo esc_html($bookingpress_version); ?></td>
                </tr>
                <?php if(current_user_can('administrator')) { ?>
                <tr>
                    <td class="leftrowdetails">Upload Basedir</td>
                    <td class="rightrowdetails"><?php echo esc_html($upload_dir_path['basedir']); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Upload Baseurl</td>
                    <td class="rightrowdetails"><?php echo esc_url($upload_dir_path['baseurl']); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Upload Folder Permission</td>
                    <td class="rightrowdetails"><?php echo esc_html($folderpermission); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Bookingpress Log Folder Permission</td>
                    <td class="rightrowdetails"><?php echo esc_html($folderlogpermission); ?></td>
                </tr>
                <tr>
                    <td class="leftrowdetails">Bookingpress Log File Permission</td>
                    <td class="rightrowdetails"><?php echo esc_html($folderlogfilepermission); ?></td>
                </tr>
                <?php } ?>

                <tr>
                    <td colspan="2" class="title">Active Plugin List</td>
                </tr>
                
                <?php
                foreach ( $plugins_main_arr as $myplugin ) {
                    ?>
                    <tr>
                        <td class="leftrowdetails"><?php echo esc_html($myplugin['name']); ?></td>
                        <td class="rightrowdetails">
                     <?php
                        if ($myplugin['is_active'] == 1 ) {
                            echo 'ACTIVE';
                        } else {
                            echo 'INACTIVE';
                        }
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(' . esc_html($myplugin['version']) . ')';

                        if($myplugin['name'] == 'BookingPress Pro - Appointment Booking plugin') {
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$activated_modules; //phpcs:ignore
                        }
                        ?>
                        </td>                        
                    </tr>
                    <?php
                }
                ?>
                    
                    
                    
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                </table>
                <?php
                exit;
            }
        }
        
        /**
         * Get any existing customer meta
         *
         * @param  mixed $bookingpress_customer_id
         * @param  mixed $bookingpress_user_metakey
         * @return void
         */
        function get_bookingpress_customersmeta( $bookingpress_customer_id, $bookingpress_user_metakey )
        {
            global $wpdb, $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta;
            $bookingpress_customersmeta_value = '';
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_customers_meta is table name defined globally. False Positive alarm
            $bookingpress_customersmeta_details = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $tbl_bookingpress_customers_meta . ' WHERE bookingpress_customer_id = %d AND bookingpress_customersmeta_key = %s', $bookingpress_customer_id, $bookingpress_user_metakey), ARRAY_A);
            if (! empty($bookingpress_customersmeta_details) ) {
                $bookingpress_customersmeta_value = $bookingpress_customersmeta_details['bookingpress_customersmeta_value'];
            }

            return $bookingpress_customersmeta_value;
        }
        
        /**
         * Update any existing customer meta
         *
         * @param  mixed $bookingpress_customer_id
         * @param  mixed $bookingpress_user_metakey
         * @param  mixed $bookingpress_user_metavalue
         * @return void
         */
        function update_bookingpress_customersmeta( $bookingpress_customer_id, $bookingpress_user_metakey, $bookingpress_user_metavalue )
        {
            global $wpdb, $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta, $BookingPress;
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers_meta is table name defined globally. False Positive alarm
            $bookingpress_exist_meta_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_customermeta_id) as total FROM {$tbl_bookingpress_customers_meta} WHERE bookingpress_customer_id = %d AND bookingpress_customersmeta_key = %s", $bookingpress_customer_id, $bookingpress_user_metakey));
            if( is_array( $bookingpress_user_metavalue ) ){
                $bookingpress_user_metavalue = wp_json_encode( $bookingpress_user_metavalue );
            }

            if ($bookingpress_exist_meta_count > 0 ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers_meta is table name defined globally. False Positive alarm
                $bookingpress_exist_meta_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers_meta} WHERE bookingpress_customer_id = %d AND bookingpress_customersmeta_key = %s", $bookingpress_customer_id, $bookingpress_user_metakey), ARRAY_A);
                $bookingpress_customermeta_id    = $bookingpress_exist_meta_details['bookingpress_customermeta_id'];

                $bookingpress_user_meta_details = array(
                'bookingpress_customer_id'         => $bookingpress_customer_id,
                'bookingpress_customersmeta_key'   => $bookingpress_user_metakey,
                'bookingpress_customersmeta_value' => $bookingpress_user_metavalue,
                );

                $bookingpress_update_where_condition = array(
                'bookingpress_customermeta_id' => $bookingpress_customermeta_id,
                );

                $wpdb->update($tbl_bookingpress_customers_meta, $bookingpress_user_meta_details, $bookingpress_update_where_condition);
            } else {
                $bookingpress_user_meta_details = array(
                'bookingpress_customer_id'         => $bookingpress_customer_id,
                'bookingpress_customersmeta_key'   => $bookingpress_user_metakey,
                'bookingpress_customersmeta_value' => $bookingpress_user_metavalue,
                );

                $wpdb->insert($tbl_bookingpress_customers_meta, $bookingpress_user_meta_details);
            }
            return 1;
        }
        
        /**
         * Delete specific customer meta
         *
         * @param  mixed $bookingpress_customer_id
         * @param  mixed $bookingpress_user_metakey
         * @return void
         */
        function delete_bookingpress_customersmeta( $bookingpress_customer_id, $bookingpress_user_metakey )
        {
            global $wpdb, $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta;
            if (! empty($bookingpress_customer_id) && ! empty($bookingpress_user_metakey) ) {
                $wpdb->delete(
                    $tbl_bookingpress_customers_meta,
                    array(
                    'bookingpress_customer_id'       => $bookingpress_customer_id,
                    'bookingpress_customersmeta_key' => $bookingpress_user_metakey,
                    )
                );
            }
            return 1;
        }
        
        /**
         * Ajax request of deactivating lite plugin
         *
         * @return void
         */
        function bookingpress_lite_deactivate_plugin_func()
        {
            global $wpdb;
            check_ajax_referer('bookingpress_lite_deactivate_plugin', 'security');
            if (! empty($_POST['bpalite_reason']) && isset($_POST['bpalite_details']) ) {
                $bpalite_anonymous               = isset($_POST['bpalite_anonymous']) && sanitize_text_field($_POST['bpalite_anonymous']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $args                            = $_POST;
                $data                            = array(
                'option_name'  => 'bpa_deactivation_feedback',
                'option_value' => serialize($args),
                );
                $args['bpalite_site_url']        = BOOKINGPRESS_HOME_URL;
                $args['bpalite_site_ip_address'] = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                if (! $bpalite_anonymous ) {
                    $args['bpa_lite_site_email'] = get_option('admin_email');
                }

                $url = 'https://www.bookingpressplugin.com/bpa_misc/bpalite_feedback.php';

                $response = wp_remote_post(
                    $url,
                    array(
                    'timeout' => 500,
                    'body'    => $args,
                    )
                );
            }
            echo wp_json_encode(
                array(
                'status' => 'OK',
                )
            );
            die();
        }

        
        /**
         * Modify plugin action links
         *
         * @param  mixed $links
         * @param  mixed $file
         * @return void
         */
        function bookingpress_plugin_action_links( $links, $file )
        {
            global $wp, $wpdb;
            if ($file == 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) {
                if (isset($links['deactivate']) ) {
                    $deactivation_link = $links['deactivate'];
                    $bpa_is_rtl_enabled = is_rtl() ? 'bpa_rtl_enabled' : '';
                    // Insert an onClick action to allow form before deactivating
                    $extra_popup_class = '';
                    if($this->bpa_is_pro_active()) {
                        $extra_popup_class = 'bpalite-confirm-deactivate-wrapper';
                    }

                    $deactivation_link   = str_replace(
                        '<a',
                        '<div class="bpalite-deactivate-form-wrapper">
	                         <span class="bpalite-deactivate-form '.$bpa_is_rtl_enabled.' '.$extra_popup_class.'" id="bpa-deactivate-form-' . esc_attr('BookingPressLite') . '"></span>
	                     </div><a id="bpa-deactivate-link-' . esc_attr('BookingPressLite') . '"',
                        $deactivation_link
                    );
                       $links['deactivate'] = $deactivation_link;
                }
            }
            return $links;
        }

                
        /**
         * Deactive feedback popup content
         *
         * @return void
         */
        function bookingpress_deactivate_feedback_popup()
        {
            ?>
                 <style type="text/css" id="bookingpress_deactivate_popup_css">
                    .bpa-deactivate-btn{display: inline-block;font-weight: 400;text-align: center;white-space;vertical-align: nowrap;user-select: none;border: 1px solid transparent;padding: .375rem .75rem;font-size:1rem;line-height:1.5;border-radius:0.25rem;transition:color .15s }
                    .bpa-deactivate-btn-primary{
                        color: #fff;
                        background-color: #12D488;
                        border-color:none !important;
                    }
                    .bpa-deactivate-btn:hover
                    {
                        color: white;
                    }                    
                    .bpa-deactivate-btn-cancel:hover ,.bpa-deactivate-btn-cancel {
                        color: #2c3338;
                        background-color: #fff;
                        border-color:#2c3338 !important;
                        /* margin-left:350px; */
                        margin-right: 10px;
                    }
                    .bpalite-deactivate-form-active .bpalite-deactivate-form-bg {background: rgba( 0, 0, 0, .5 );position: fixed;top: 0;left: 0;width: 100%;height: 100%; z-index: 99;}
                    .bpalite-deactivate-form-wrapper {position: relative;z-index: 999;display: none; }
                    .bpalite-deactivate-form-active .bpalite-deactivate-form-wrapper {display: inline-block;}
                    .bpalite-deactivate-form {display: none;}
                    .bpalite-deactivate-form-active .bpalite-deactivate-form {position: absolute;bottom: 30px;left: 0;max-width: 500px;min-width: 360px;background: #fff;white-space: normal;}
                    .bpalite-deactivate-form-active .bpalite-deactivate-form.bpa_rtl_enabled {position: absolute;bottom: 30px;left: unset;max-width: 500px;min-width: 360px;background: #fff;white-space: normal;}
                    .bpalite-deactivate-form-head {background: #12D488;color: #fff;padding: 8px 18px;}
                    .bpalite-deactivate-confirm-head p{color: #fff; padding-left:10px}
                    .bpalite-deactivate-confirm-head{padding: 4px 18px; background:red; }
                    .bpalite-deactivate-form-body {padding: 8px 18px 0;color: #444;}
                    .bpalite-deactivate-form-body label[for="bpalite-remove-settings"] {font-weight: bold;}
                    .deactivating-spinner {display: none;}
                    .deactivating-spinner .spinner {float: none;margin: 4px 4px 0 18px;vertical-align: bottom;visibility: visible;}
                    .bpalite-deactivate-form-footer {padding: 0 18px 8px;}
                    .bpalite-deactivate-form-footer label[for="bpalite_anonymous"] {visibility: hidden;}
                    .bpalite-deactivate-form-footer p {display: flex;align-items: center;justify-content: space-between;margin: 0;}
                    #bpalite-deactivate-submit-form span {display: none;}
                    .bpalite-deactivate-form.process-response .bpalite-deactivate-form-body,.bpalite-deactivate-form.process-response .bpalite-deactivate-form-footer {position: relative;}
                    .bpalite-deactivate-form.process-response .bpalite-deactivate-form-body:after,.bpalite-deactivate-form.process-response .bpalite-deactivate-form-footer:after {content: "";display: block;position: absolute;top: 0;left: 0;width: 100%;height: 100%;background-color: rgba( 255, 255, 255, .5 );}
                    button#bpalite-deactivate-submit-btn{cursor:pointer;}
                    button#bpalite-deactivate-submit-btn[disabled=disabled]{ 
                        cursor:not-allowed;
                        opacity: 0.5;
                    }         
                    .bpalite-confirm-deactivate-wrapper{
                        width:550px;
                        max-width:600px !important;
                    }
                    .bpalite-confirm-deactivate-wrapper .bpalite-deactivate-confirm-head strong {
                        margin-bottom:unset;
                    }
                    .bpalite-confirm-deactivate-wrapper .bpalite-deactivate-confirm-head {
                        display: flex;
                        align-items: center;
                    }
                    body.rtl .bpalite-deactivate-form-footer p{ justify-content: space-between;}
                    body.rtl .bpa-deactivate-btn-cancel:hover ,.bpa-deactivate-btn-cancel{margin-left: 10px; }
                    .bookingpress-pro-notice-plugin-label{ color: #000000; }
                </style>
            <?php
            $question_options                      = array();
            $question_options['list_data_options'] = array(
            'setup-difficult'  => esc_html__('Set up is too difficult', 'bookingpress-appointment-booking'),
            'docs-improvement' => esc_html__('Lack of documentation', 'bookingpress-appointment-booking'),
            'features'         => esc_html__('Not the features I wanted', 'bookingpress-appointment-booking'),
            'better-plugin'    => esc_html__('Found a better plugin', 'bookingpress-appointment-booking'),
            'incompatibility'  => esc_html__('Incompatible with theme or plugin', 'bookingpress-appointment-booking'),
            'bought-premium'   => esc_html__('I bought premium version of BookingPress', 'bookingpress-appointment-booking'),
            'maintenance'      => esc_html__('Other', 'bookingpress-appointment-booking'),
            );

            $html2 = '<div class="bpalite-deactivate-confirm-head"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="#fff"><path d="M4.47 21h15.06c1.54 0 2.5-1.67 1.73-3L13.73 4.99c-.77-1.33-2.69-1.33-3.46 0L2.74 18c-.77 1.33.19 3 1.73 3zM12 14c-.55 0-1-.45-1-1v-2c0-.55.45-1 1-1s1 .45 1 1v2c0 .55-.45 1-1 1zm1 4h-2v-2h2v2z"/></svg><p><strong>' . esc_html__('BookingPress Lite plugin Deactivation', 'bookingpress-appointment-booking').'.</strong></p></div>';
            $html2 .= '<div class="bpalite-deactivate-form-body">';
            $html2 .= '<div class="bpalite-deactivate-options">';

            $html2 .= '<p><strong>' . esc_html__('You are using BookingPress Premium plugin on your website and it is an extension to BookingPress Lite, so, If you deactivate BookingPress Lite then it will automatically deactivate BookingPress Premium and all the add-ons', 'bookingpress-appointment-booking') . '.</strong></p></br>';

            $html2 .= '<p><label><input type="checkbox" name="bpalite-risk-confirm" id="bpalite-risk-confirm" value="risk-confirm">'.esc_html__('I understand the risk', 'bookingpress-appointment-booking').'</label></p>';
            $html2 .= '</div>';
            $html2 .= '<hr/>';
            $html2 .= '</div>';
            $html2 .= '<div class="bpalite-deactivate-form-footer"><p>';                            
            $html2 .= '<button id="bpalite-deactivate-cancel-btn" class="bpa-deactivate-btn bpa-deactivate-btn-cancel" >'.esc_html__('Cancel', 'bookingpress-appointment-booking')
            . '</button>';
            $html2 .= '<button id="bpalite-deactivate-submit-btn" disabled=disabled class="bpa-deactivate-btn bpa-deactivate-btn-primary" href="#">'.esc_html__('Proceed', 'bookingpress-appointment-booking')
            . '</button></p>';
            $html2 .= '</div>';

            $html                                  = '<div class="bpalite-deactivate-form-head"><strong>' . esc_html__('BookingPress - Sorry to see you go', 'bookingpress-appointment-booking') . '</strong></div>';
            $html                                 .= '<div class="bpalite-deactivate-form-body">';
            if (is_array($question_options['list_data_options']) ) {
                $html .= '<div class="bpalite-deactivate-options">';
                $html .= '<p><strong>' . esc_html__('Before you deactivate the BookingPress Lite plugin, would you quickly give us your reason for doing so?', 'bookingpress-appointment-booking') . '</strong></p><p>';

                foreach ( $question_options['list_data_options'] as $key => $option ) {
                    $html .= '<input type="radio" name="bpalite-deactivate-reason" id="' . esc_attr($key) . '" value="' . esc_attr($key) . '"> <label for="' . esc_attr($key) . '">' . esc_attr($option) . '</label><br>';
                }
                $html .= '</p><label id="bpalite-deactivate-details-label" for="bpalite-deactivate-reasons"><strong>' . esc_html__('How could we improve ?', 'bookingpress-appointment-booking') . '</strong></label><textarea name="bpalite-deactivate-details" id="bpalite-deactivate-details" rows="2" style="width:100%"></textarea>';
                $html .= '</div>';
            }
                $html .= '<hr/>';

                $html .= '</div>';
                $html .= '<p class="deactivating-spinner"><span class="spinner"></span> ' . esc_html__('Submitting form', 'bookingpress-appointment-booking') . '</p>';
                $html .= '<div class="bpalite-deactivate-form-footer"><p>';
                $html .= '<label for="bpalite_anonymous" title="'
                    . esc_html__('If you uncheck this option, then your email address will be sent along with your feedback. This can be used by BookingPress to get back to you for more information or a solution.', 'bookingpress-appointment-booking')
                    . '"><input type="checkbox" name="bpalite-deactivate-tracking" id="bpalite_anonymous"> ' . esc_html__('Send anonymous', 'bookingpress-appointment-booking') . '</label><br>';
                $html .= '<a id="bpalite-deactivate-submit-form"  class="bpa-deactivate-btn bpa-deactivate-btn-primary" href="#"><span>'
                . esc_html__('Submit and', 'bookingpress-appointment-booking').'</span> '.esc_html__('Deactivate', 'bookingpress-appointment-booking')
                . '</a>';
                $html .= '</p></div>';
            ?>
                <div class="bpalite-deactivate-form-bg"></div>
               
                <script type="text/javascript">
                    jQuery(document).ready(function($){
                        var bpalite_deactivateURL = $("#bpa-deactivate-link-<?php echo esc_attr('BookingPressLite'); ?>")
                            bpalite_formContainer = $('#bpa-deactivate-form-<?php echo esc_attr('BookingPressLite'); ?>'),
                            bpalite_deactivated = true,
                            bpalite_detailsStrings = {
                                'setup-difficult' : '<?php echo esc_html__('What was the dificult part?', 'bookingpress-appointment-booking'); ?>',
                                'docs-improvement' : '<?php echo esc_html__('What can we describe more?', 'bookingpress-appointment-booking'); ?>',
                                'features' : '<?php echo esc_html__('How could we improve?', 'bookingpress-appointment-booking'); ?>',
                                'better-plugin' : '<?php echo esc_html__('Can you mention it?', 'bookingpress-appointment-booking'); ?>',
                                'incompatibility' : '<?php echo esc_html__('With what plugin or theme is incompatible?', 'bookingpress-appointment-booking'); ?>',
                                'bought-premium' : '<?php echo esc_html__('Please specify experience', 'bookingpress-appointment-booking'); ?>',
                                'maintenance' : '<?php echo esc_html__('Please specify', 'bookingpress-appointment-booking'); ?>',
                            };

                        jQuery( bpalite_deactivateURL).attr('onclick', "javascript:event.preventDefault();");
                        jQuery( bpalite_deactivateURL ).on("click", function(){

                            function BPALiteSubmitData(bpalite_data, bpalite_formContainer)
                            {
                                bpalite_data['action']          = 'bookingpress_lite_deactivate_plugin';
                                bpalite_data['security']        = '<?php echo esc_html(wp_create_nonce('bookingpress_lite_deactivate_plugin')); ?>';
                                bpalite_data['dataType']        = 'json';
                                bpalite_formContainer.addClass( 'process-response' );
                                bpalite_formContainer.find(".deactivating-spinner").show();
                                jQuery.post(ajaxurl,bpalite_data,function(response)
                                {   
                                   window.location.href = bpalite_url;
                                   
                                });
                            }

                            var bpalite_url = bpalite_deactivateURL.attr( 'href' );
                            jQuery('body').toggleClass('bpalite-deactivate-form-active');

                            bpalite_formContainer.show({complete: function(){
                                var offset = bpalite_formContainer.offset();
                                if( offset.top < 50) {
                                    $(this).parent().css('top', (50 - offset.top) + 'px')
                                }
                                jQuery('html,body').animate({ scrollTop: Math.max(0, offset.top - 50) });
                            }});

                            <?php if($this->bpa_is_pro_active()) {
                                $html = $html2;
                            } ?>
                            bpalite_formContainer.html( '<?php echo $html; //phpcs:ignore ?>');
                            

                            bpalite_formContainer.on( 'change', 'input[type=radio]', function()
                            {
                                var bpalite_detailsLabel = bpalite_formContainer.find( '#bpalite-deactivate-details-label strong' );
                                var bpalite_anonymousLabel = bpalite_formContainer.find( 'label[for="bpalite_anonymous"]' )[0];
                                var bpalite_submitSpan = bpalite_formContainer.find( '#bpalite-deactivate-submit-form span' )[0];
                                var bpalite_value = bpalite_formContainer.find( 'input[name="bpalite-deactivate-reason"]:checked' ).val();

                                bpalite_detailsLabel.text( bpalite_detailsStrings[ bpalite_value ] );
                                bpalite_anonymousLabel.style.visibility = "visible";
                                bpalite_submitSpan.style.display = "inline-block";
                                if(bpalite_deactivated)
                                {
                                    bpalite_deactivated = false;
                                    jQuery('#bpalite-deactivate-submit-form').removeAttr("disabled");
                                    bpalite_formContainer.off('click', '#bpalite-deactivate-submit-form');
                                    bpalite_formContainer.on('click', '#bpalite-deactivate-submit-form', function(e){
                                        e.preventDefault();
                                        var data = {
                                            bpalite_reason: bpalite_formContainer.find('input[name="bpalite-deactivate-reason"]:checked').val(),
                                            bpalite_details: bpalite_formContainer.find('#bpalite-deactivate-details').val(),
                                            bpalite_anonymous: bpalite_formContainer.find('#bpalite_anonymous:checked').length,
                                        };
                                        BPALiteSubmitData(data, bpalite_formContainer);
                                    });
                                }
                            });

                            bpalite_formContainer.on('click', '#bpalite-deactivate-submit-form', function(e){
                                e.preventDefault();
                                BPALiteSubmitData({}, bpalite_formContainer);
                            });
                            $('.bpalite-deactivate-form-bg').on('click',function(){
                                bpalite_formContainer.fadeOut(); 
                                $('body').removeClass('bpalite-deactivate-form-active');
                            });

                            bpalite_formContainer.on( 'change', '#bpalite-risk-confirm', function() {
                                if(jQuery(this).is(":checked")) {
                                    $('#bpalite-deactivate-submit-btn').removeAttr("disabled");
                                } else {
                                    $('#bpalite-deactivate-submit-btn').attr('disabled','disabled');
                                }
                            });                            
                            bpalite_formContainer.on( 'click', '#bpalite-deactivate-cancel-btn', function(e) {
                                e.preventDefault();
                                bpalite_formContainer.fadeOut(); 
                                $('body').removeClass('bpalite-deactivate-form-active');
                                return false;
                            });
                            bpalite_formContainer.on( 'click', '#bpalite-deactivate-submit-btn', function() {
                                window.location.href = bpalite_url;
                                return false;
                            });                            
                        });
                    });
                </script>
            <?php
        }

                
        /**
         * Update lite version details
         *
         * @return void
         */
        function upgrade_data()
        {
            global $bookingpress_version;
            $bookingpress_old_version = get_option('bookingpress_version', true);
            if (version_compare($bookingpress_old_version, '1.1.51', '<') ) {
                $bookingpress_load_upgrade_file = BOOKINGPRESS_VIEWS_DIR . '/upgrade_latest_data.php';
                include $bookingpress_load_upgrade_file;
                $this->bookingpress_send_anonymous_data_cron();
            }
        }
        
        /**
         * Hide all admin notices when Bookingpress page loads
         *
         * @return void
         */
        function bookingpress_hide_admin_notices()
        {
            if (! empty($_GET['page']) && ( $_GET['page'] == 'bookingpress' ) ) {
                remove_all_actions('network_admin_notices', 100);
                remove_all_actions('user_admin_notices', 100);
                remove_all_actions('admin_notices', 100);
                remove_all_actions('all_admin_notices', 100);
            }
        }
        
        /**
         * Display admin notices
         *
         * @return void
         */
        function bookingpress_admin_notices()
        {
            global $bookingpress_version;
            $bookingpress_get_php_version = ( function_exists('phpversion') ) ? phpversion() : 0;
            $notice_html                  = '';
            if (version_compare($GLOBALS['wp_version'], '5.0', '<') ) {
                $notice_html .= '<div class="bpa-notice bpa-notice-error" style="display: block !important; position: relative !important; z-index: 9999 !important;">';
                $notice_html .= '<p>' . esc_html__('BookingPress - WordPress Appointment Booking Plugin requires a minimum WordPress version of 5.0.', 'bookingpress-appointment-booking') . '</p>';
                $notice_html .= '</div>';
             // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: output is properly escaped or hardcoded
                echo $notice_html;
            }
            $bookingpress_customize_changes_notice = get_option('bookingpress_customize_changes_notice_1.0.51');
			if($bookingpress_customize_changes_notice !='' && $bookingpress_customize_changes_notice == 1) {
                $addon_warning_notice = esc_html__('Please save your customization changes once from BookingPress -> Customize -> Forms', 'bookingpress-appointment-booking');
				echo '<div class="notice notice-warning is-dismissible bpa_customize_error_notice" data-bookingpress_confirm="'.esc_html__('Are you sure you have confirm the changes?', 'bookingpress-appointment-booking').'"><p>'.$addon_warning_notice. '</p></div>'; // phpcs:ignore 
            }
            $bookingpress_pro_version = get_option('bookingpress_pro_version');
            if( file_exists(WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') && !empty($bookingpress_pro_version) && version_compare($bookingpress_pro_version,'2.5','<')){
                $addon_notice_cal = esc_html__("It's highly recommended that BookingPress premium plugin should be updated to it's latest version to get latest changes of 'Calendar' page at the backend", "bookingpress-appointment-booking");
				echo "<div class='notice notice-error is-dismissible'><p>" .$addon_notice_cal. "</p></div>";  //phpcs:ignore
			}

            $bpa_update_elementor_notice = get_option( 'bookingpress_update_elementor_widget' );
            if( !empty( $bpa_update_elementor_notice ) && 1 == $bpa_update_elementor_notice ){

                $admin_notice_elm = esc_html__( 'From BookingPress version 1.1.11, the two Elementor widgets are deprecated. Please click below button to update with the latest.', 'bookingpress-appointment-booking' );
                
                echo '<div class="notice notice-error bookingpress-elementor-widget-notice" style="padding: 5px 12px 15px 12px;"><p>'.$admin_notice_elm.'</p><button type="button" class="button bookingpress_replace_elementor_widgets button-primary button-large" >'.esc_html__( 'Update Elementor Widgets', 'bookingpress-appointment-booking' ).'</button>&nbsp;<span class="bpa_update_message" style="display:inline-block; height:32px; line-height:32px; padding: 0 5px;"></span><span class="spinner bpa_elementor_update_spinner" style="float:none;margin-top:-25px;" ></span><br/></div>'; //phpcs:ignore

            }
        }
        
        /**
         * Load admin side main script at footer
         *
         * @return void
         */
        function bookingpress_admin_footer_func(){
            
            $requested_module = ! empty($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : 'dashboard'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            if (strpos($requested_module, 'bookingpress_') !== false || $requested_module == 'bookingpress' ) {
                global $bookingpress_global_options, $BookingPress;
                $bookingpress_global_details  = $bookingpress_global_options->bookingpress_global_options();              
                $bpa_time_format_for_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];
    
                $bookingpress_default_date_format = $BookingPress->bookingpress_check_common_date_format($bookingpress_global_details['wp_default_date_format']);
                $bookingpres_default_time_format = $BookingPress->bookingpress_get_settings('default_time_format','general_setting');
                $bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();
    
                $is_compitible_with_pro = 0;
                if( $this->bpa_is_pro_exists() && $this->bpa_is_pro_active() ){
                    if( !empty( $this->bpa_pro_plugin_version() ) && version_compare( $this->bpa_pro_plugin_version(), '1.5', '>' ) ){
                        $is_compitible_with_pro = 1;                 
                    }
                }
                $requested_module = ( ! empty($_REQUEST['page']) && ( $_REQUEST['page'] != 'bookingpress' ) ) ? sanitize_text_field(str_replace('bookingpress_', '', sanitize_text_field($_REQUEST['page']))) : 'dashboard'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                ?>
                <script>
                    var bookingpress_requested_module = '<?php echo esc_html($requested_module); ?>';
                    var bookingpress_start_of_week = "<?php echo esc_html($bookingpress_global_details['start_of_week']); ?>";
                <?php do_action('bookingpress_' . $requested_module . '_dynamic_helper_vars'); ?>
                var app;
                window.addEventListener('DOMContentLoaded', function() {
                    app = new Vue({
                        el: '#root_app',
                        directives: {
                            cancelReadOnly(el){
                                const input = el.querySelector('.el-input__inner');
                                input.removeAttribute('readonly');
                            },
                            <?php do_action('bookingpress_' . $requested_module . '_dynamic_directives'); ?>
                        },
                        components: { <?php do_action('bookingpress_' . $requested_module . '_dynamic_components'); ?> },
                        data() {
                            <?php do_action('bookingpress_' . $requested_module . '_dynamic_data_fields_vars'); ?>

                            var bookingpress_return_data = <?php do_action('bookingpress_' . $requested_module . '_dynamic_data_fields'); ?>;
                            bookingpress_return_data['needHelpDrawer'] = false;
                            bookingpress_return_data['needHelpDrawerDirection'] = 'rtl';
                            bookingpress_return_data['needHelpDrawer_add'] = false;
                            bookingpress_return_data['add_needHelpDrawerDirection'] = 'rtl';
                            bookingpress_return_data['helpDrawerData'] = '';
                            bookingpress_return_data['close_modal_on_esc'] = true;
                            <?php
                                $bookingpress_site_date = date('Y-m-d H:i:s', current_time( 'timestamp') );
                                $bookingpress_site_date = apply_filters( 'bookingpress_modify_current_date', $bookingpress_site_date );
                                if( !empty( $bookingpress_site_date ) ){
                                    $bookingpress_site_current_date = date( 'Y-m-d', strtotime( $bookingpress_site_date ) ) . ' 00:00:00';
                                } else {
                                    $bookingpress_site_current_date = "";
                                }
                            ?>
                            bookingpress_return_data["jsCurrentDate"] = new Date(<?php echo !empty( $bookingpress_site_date ) ? '"' . esc_html( $bookingpress_site_date ) . '"' : ''; ?>);
                            bookingpress_return_data["jsCurrentDateFormatted"] = new Date (<?php echo !empty( $bookingpress_site_current_date ) ? '"' . esc_html($bookingpress_site_current_date) . '"' : '' ?>);
                            bookingpress_return_data['is_display_drawer_loader'] = '0';
                            bookingpress_return_data["jsCurrentOnlyDate"] = bookingpress_return_data['jsCurrentDateFormatted'].toISOString().split("T")[0];
                            bookingpress_return_data['requested_module'] = bookingpress_requested_module;
                            bookingpress_return_data['read_more_link'] = '#';
                            bookingpress_return_data['site_locale'] = '<?php echo esc_html($bookingpress_site_current_language); ?>';
                            bookingpress_return_data['site_date'] = '<?php echo esc_html(date('Y-m-d H:i:s', current_time( 'timestamp') )); ?>';
                            bookingpress_return_data['premium_modal'] = false;
                            bookingpress_return_data['bookingpress_old_premium_modal'] = false;
                            bookingpress_return_data['is_compitible_with_pro'] = '<?php echo esc_html($is_compitible_with_pro); ?>';
                            bookingpress_return_data['current_screen_size'] = 'desktop';
                            bookingpress_return_data['bpa_fab_floating_btn'] = '0';
                            bookingpress_return_data['bpa_date_common_date_format'] = '<?php echo esc_html($bookingpress_default_date_format); ?>';

                            <?php if($bookingpres_default_time_format == "H:i"){ ?>
                                bookingpress_return_data['bpa_date_time_common_date_format'] = '<?php echo esc_html($bookingpress_default_date_format); ?>'+' HH:mm';
                            <?php } else { ?>
                                bookingpress_return_data['bpa_date_time_common_date_format'] = '<?php echo esc_html($bookingpress_default_date_format); ?>'+' hh:mm a';
                            <?php }  ?>                    

                            <?php do_action('bookingpress_admin_vue_data_variables_script'); ?>

                            <?php
                            $bookingpress_allowed_disable_date_filter_pickeroptions = apply_filters( 'bookingpress_allowed_disable_date_filter_pickeroptions', false, $requested_module);
                            if (( isset($_REQUEST['page']) && $_REQUEST['page'] == 'bookingpress' ) || $bookingpress_allowed_disable_date_filter_pickeroptions || $requested_module == 'appointments' || $requested_module == 'calendar' || $requested_module == 'services' || $requested_module == 'staff_members' || $requested_module == 'settings' || $requested_module == 'timesheet' || $requested_module == 'payments' || $requested_module == 'customize' || $requested_module == 'customers') {
                                ?>
                                bookingpress_return_data['pickerOptions'] = {
                                    disabledDate(Time) {
                                        var dd = String(Time.getDate()).padStart(2, '0');
                                        var mm = String(Time.getMonth() + 1).padStart(2, '0'); //January is 0!
                                        var yyyy = Time.getFullYear();
                                        var time = yyyy+ '-' + mm + '-' + dd;
                                        if( "undefined" == typeof bookingpress_return_data['disabledDates'].indexOf ){
                                            var date = new Date();
                                            var newDateArr = [];
                                            for( let dcount in bookingpress_return_data['disabledDates'] ){
                                                let dis_date = bookingpress_return_data['disabledDates'][dcount];
                                                newDateArr.push( dis_date );
                                            }
                                            var disable_date = newDateArr.indexOf( time ) > -1;
                                            var date = new Date();
                                            date.setDate(date.getDate()-1);
                                            var disable_past_date = Time.getTime() < date.getTime();
                                            if(disable_date == true) {
                                                return disable_date; 
                                            } else {
                                                return disable_past_date;
                                            } 
                                        } else {
                                            var disable_date = bookingpress_return_data['disabledDates'].indexOf(time) > -1;
                                            var date = new Date();
                                            date.setDate(date.getDate()-1);
                                            var disable_past_date = Time.getTime() < date.getTime();
                                            if(disable_date == true) {
                                                return disable_date; 
                                            } else {
                                                return disable_past_date;
                                            } 
                                        }
                                      },
                                      'firstDayOfWeek': parseInt(bookingpress_start_of_week),
                                },
                                bookingpress_return_data['filter_pickerOptions'] = {
                                    'firstDayOfWeek': parseInt(bookingpress_start_of_week),
                                },
                                bookingpress_return_data['disablePastDates'] = {
                                    disabledDate(Time) {						
                                        var dd = String(Time.getDate()).padStart(2, '0');
                                        var mm = String(Time.getMonth() + 1).padStart(2, '0'); //January is 0!
                                        var yyyy = Time.getFullYear();
                                        var time = yyyy+ '-' + mm + '-' + dd ;
                                        var disable_date = bookingpress_return_data['disabledOtherDates'].indexOf(time)>-1;
                                        var date = new Date();
                                        date.setDate(date.getDate()-1);
                                        var disable_past_date = Time.getTime() < date.getTime();
                                        if(disable_date == true) {
                                            return disable_date; 
                                        } else {
                                            return disable_past_date;
                                        }
                                    },
                                    'firstDayOfWeek': parseInt(bookingpress_start_of_week),
                                }
                                <?php
                            }
                            ?>
                            return bookingpress_return_data;            
                        },
                        computed: {
                            <?php do_action('bookingpress_' . $requested_module . '_dynamic_computed_methods'); ?>
                        },
                        filters: {
                            bookingpress_format_time: function(value){
                                var default_time_format = '<?php echo esc_html($bpa_time_format_for_timeslot); ?>';
                                return moment(String(value), "HH:mm:ss").format(default_time_format)
                            },
                            <?php do_action('bookingpress_admin_view_filter'); ?>                           
                        },
                        mounted() {
                            this.bpa_set_read_more_link();
                            let admin_menubar = document.getElementById('adminmenuwrap');
							
							if( null != admin_menubar && admin_menubar.offsetWidth > 160 ){
								document.body.classList.add('bpa_menu_large_width');
							}
                            <?php do_action('bookingpress_admin_vue_on_load_script'); ?>                            
                            document.onreadystatechange = () => { 
                                if (document.readyState == "complete") {
                                    setTimeout(function(){
                                        if(document.getElementById('bpa-page-loading-loader') != null){
                                            document.getElementById('bpa-page-loading-loader').remove();
                                            document.getElementById('bpa-main-container').style.display = 'block';
                                            if(document.getElementById('bpa-page-loading-loader-2') != null){
                                                document.getElementById('bpa-page-loading-loader-2').remove();
                                            }                                            
                                            if(document.getElementById('bpa-main-container-2') != null){
                                                document.getElementById('bpa-main-container-2').style.display = 'block';
                                            }
                                            if(document.getElementById('bpa-main-container-3') != null){
                                                document.getElementById('bpa-page-loading-loader-3').remove();
                                                document.getElementById('bpa-main-container-3').style.display = 'block';
                                            }
                                            jQuery("#bpa-loader-div").show();
                                        }
                                    }, 2000);
                                    this.bookingpress_remove_focus_for_popover();
                                } 
                              }
                              
                            if(window.screen.width >= 1200){
                                this.current_screen_size = "desktop";
                            }else if(window.screen.width < 1200 && window.screen.width >= 768){
                                this.current_screen_size = "tablet";
                            }else if(window.screen.width < 768){
                                this.current_screen_size = "mobile";
                            }  

                            window.addEventListener('resize', function(event) {                                
                                if(window.screen.width >= 1200){
                                    this.current_screen_size = "desktop";
                                }else if(window.screen.width < 1200 && window.screen.width >= 768){
                                    this.current_screen_size = "tablet";
                                }else if(window.screen.width < 768){
                                    this.current_screen_size = "mobile";
                                }
                            });                   

                            this.responsiveMenu();
                            if(window.screen.width <= 1024){
                                document.body.className = document.body.className + ' folded';
                            }
                            if(window.screen.width <= 1023){
                                document.body.className = document.body.className + ' folded bookingpress-plugin-body-area';
                            }
                            if(bookingpress_requested_module == "settings"){
                                this.loadCalendarDates();
                            }
                            jQuery(window).scroll(function() {
                                var scroll = jQuery(window).scrollTop();
                                if (scroll > 46) {
                                    jQuery("body").addClass("__bpa-is-scroll-active");
                                }
                                else {
                                    jQuery("body").removeClass("__bpa-is-scroll-active");
                                }
                            });
                            
                            <?php
                                $bpa_display_after_update = get_option( 'bookingpress_display_bf_popup_after_update' );
                                $bpa_is_dispayed_before_end = get_option( 'bpa_is_displayed_bf_sale_popup' );
                                $bpa_current_date_for_bf_popup = current_time('timestamp',true); //GMT/ UTC+00 timeszone
                                
                                $bpa_display_bf_popup = false;

                                $bpa_sale_popup_details = $this->bookingpress_get_sales_data();

                                $current_year = gmdate('Y', current_time('timestamp', true ) );


                                if( !empty( $bpa_sale_popup_details[ $current_year ] ) ){
                                    $sale_details = $bpa_sale_popup_details[ $current_year ];
                                    
                                    $bpa_bf_popup_start_time = $sale_details['start_time'];
                                    $bpa_bf_popup_end_time = $sale_details['end_time'];
                                    
                                    if( ( isset($_REQUEST['page']) && $_REQUEST['page'] != 'bookingpress_lite_wizard') && ( 'false' == $bpa_is_dispayed_before_end || false == $bpa_is_dispayed_before_end ) && ( $bpa_current_date_for_bf_popup >= $bpa_bf_popup_start_time && $bpa_current_date_for_bf_popup <= $bpa_bf_popup_end_time ) ){
                                        $bpa_display_bf_popup = true;
                                    }
                                }
                                if( ( isset( $bpa_display_after_update ) && 1 == $bpa_display_after_update ) && true == $bpa_display_bf_popup  ){
                            ?>
                                    this.premium_modal = true;
                            <?php
                                    update_option( 'bookingpress_display_bf_popup_after_update', 0 );
                                    if( true == $bpa_display_bf_popup ){
                                        update_option( 'bpa_is_displayed_bf_sale_popup', true );
                                        update_option('bookingpress_display_bf_popup_after_update_date',true);
                                    }
                                }


                                if( !empty( $bpa_sale_popup_details[ $current_year ] ) ){
                                    $sale_details = $bpa_sale_popup_details[ $current_year ];
                                    
                                    $bpa_bf_popup_start_time = $sale_details['start_time'];
                                    $bpa_bf_popup_end_time = $sale_details['end_time'];
                                
                                    if( $bpa_current_date_for_bf_popup >= $bpa_bf_popup_start_time && $bpa_current_date_for_bf_popup <= $bpa_bf_popup_end_time ) {
                                        $bookingpress_display_bf_popup_after_update_date  = get_option('bookingpress_display_bf_popup_after_update_date'); 
                                        if( $bookingpress_display_bf_popup_after_update_date == true ) {
                                        ?>
                                            this.premium_modal = true;
                                        <?php
                                        update_option( 'bookingpress_display_bf_popup_after_update_date',false);
                                        }
                                    }
                                }

                                do_action('bookingpress_' . $requested_module . '_dynamic_on_load_methods'); ?>
                        },
                        methods: {
                            bpa_set_read_more_link(){
                                const vm = this;
                                var bpa_requested_module = vm.requested_module;
                                var read_more_link = "";
                                <?php  
                                    $page_action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
                                ?>
                                if(bpa_requested_module == "dashboard"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/dashboard/";
                                }else if(bpa_requested_module == "services"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/services/";    
                                }else if(bpa_requested_module == "customers"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/customers/";
                                }else if(bpa_requested_module == "calendar"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/admin-calender-view/";    
                                }else if(bpa_requested_module == "appointments"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/appointments/";
                                }else if(bpa_requested_module == "notifications"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/email-notifications-message/";
                                }else if(bpa_requested_module == "payments"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/payment/";
                                }else if(bpa_requested_module == "customize") {
                                    <?php if($page_action == 'form_fields') { ?>
                                        read_more_link = "https://www.bookingpressplugin.com/documents/customize-custom-fields/";
                                    <?php } else { ?>
                                        read_more_link = "https://www.bookingpressplugin.com/documents/customize-booking-form/";
                                    <?php } ?>
                                }else if(bpa_requested_module == "lite_wizard"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/installing-updating-bookingpress/";
                                }else if(bpa_requested_module == "addons"){
                                    read_more_link = "https://www.bookingpressplugin.com/addons";
                                }else if(bpa_requested_module == "settings"){
                                    read_more_link = "https://www.bookingpressplugin.com/documents/general-settings/";
                                    var selected_tab = sessionStorage.getItem("current_tabname");
                                    if(selected_tab == "general_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/general-settings/";
                                    }else if(selected_tab == "company_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/company-settings/";
                                    }else if(selected_tab == "notification_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/notifications-settings/";
                                    }else if(selected_tab == "workhours_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/work-hours-settings/";
                                    }else if(selected_tab == "dayoff_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/holidays-settings/";
                                    }else if(selected_tab == "payment_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/payments-settings/";
                                    }else if(selected_tab == "message_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/messages-settings/";
                                    }else if(selected_tab == "debug_log_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/debug-log-settings/";
                                    }
                                }else{
                                    var selected_tab = sessionStorage.getItem("current_tabname");
                                    if(selected_tab == "general_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/general-settings/";
                                    }else if(selected_tab == "company_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/company-settings/";
                                    }else if(selected_tab == "notification_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/notifications-settings/";
                                    }else if(selected_tab == "workhours_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/work-hours-settings/";
                                    }else if(selected_tab == "dayoff_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/holidays-settings/";
                                    }else if(selected_tab == "payment_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/payments-settings/";
                                    }else if(selected_tab == "message_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/messages-settings/";
                                    }else if(selected_tab == "debug_log_settings"){
                                        read_more_link = "https://www.bookingpressplugin.com/documents/debug-log-settings/";
                                    }
                                }

                                <?php
                                    do_action('bookingpress_modify_readmore_link');
                                ?>

                                vm.read_more_link = read_more_link;
                            },
                            bpa_fab_floating_action_btn(){
                                const vm = this;
                                vm.bpa_fab_floating_btn = 1;
                            },
                            bpa_fab_floating_close_btn(){
                                const vm = this;
                                vm.bpa_fab_floating_btn = 0;
                            },
                            open_premium_modal(){
                                const vm = this; 
                            <?php
                                $bpa_current_date_for_bf_popup = current_time('timestamp', true); //GMT/ UTC+00 timeszone
                                $bpa_sale_popup_details = $this->bookingpress_get_sales_data();

                                $current_year = gmdate('Y', current_time('timestamp', true ) );

                                if( !empty( $bpa_sale_popup_details[ $current_year ] ) ){
                                    $sale_details = $bpa_sale_popup_details[ $current_year ];
                                    
                                    $bpa_bf_popup_start_time = $sale_details['start_time'];
                                    $bpa_bf_popup_end_time = $sale_details['end_time'];
                                
                                    if( $bpa_current_date_for_bf_popup >= $bpa_bf_popup_start_time && $bpa_current_date_for_bf_popup <= $bpa_bf_popup_end_time ) {
                                    ?>
                                            vm.premium_modal = true;
                                            vm.bookingpress_old_premium_modal = false;
                                    <?php
                                        }else{ ?>
                                            vm.bookingpress_old_premium_modal = true;                                
                                            vm.premium_modal = false;
                                    <?php }
                                } else {
                                    ?>
                                    vm.bookingpress_old_premium_modal = true;                                
                                    vm.premium_modal = false;
                                    <?php
                                }?>
                            },
                            bpa_close_premium_modal(){
                                const vm = this;
                                vm.premium_modal = false;
                                vm.bookingpress_old_premium_modal = false;
                            },
                            bookingpress_redirect_premium_page(){
                                window.open('https://www.bookingpressplugin.com/pricing/?utm_source=liteversion&utm_medium=plugin&utm_campaign=Upgrade+to+Premium&utm_id=bookingpress_2', '_blank');
                            },
                            bookingpress_redirect_sale_premium_page(){
                                window.open('https://www.bookingpressplugin.com/pricing/?utm_source=blackfriday_liteversionpopup&utm_medium=liteversion&utm_campaign=blackfriday', '_blank');
                            },
                            bookingpress_redirect_lite_vs_preminum_page(){
                                window.open('https://www.bookingpressplugin.com/bookingpress-lite-vs-premium', '_blank');
                            },
                            bookingpress_redirect_lite_vs_other_page(){
                                window.open('https://www.bookingpressplugin.com/bookly-vs-amelia-vs-bookingpress', '_blank');
                            },
                            async loadCalendarDates(selected_year = new Date().getFullYear()){
                                const vm = this
                                for(var i=0; i<=11; i++){
                                    var bookingpress_calendar_start_date = new Date();
                                    bookingpress_calendar_start_date.setFullYear(selected_year, i, 1);
                                    
                                    var bookingpress_calendar_end_date = new Date();
                                    bookingpress_calendar_end_date.setFullYear(selected_year, i+1, 0);

                                    var calendar_name = 'calendar_'+i;
                                    var bookingpress_calendar_obj = vm.$refs[calendar_name];
                                    await bookingpress_calendar_obj.move(bookingpress_calendar_start_date)
                                }
                            },
                            bookingpress_get_elm_parents(elem, selector) {

                                if (!Element.prototype.matches) {
                                    Element.prototype.matches = Element.prototype.matchesSelector ||
                                        Element.prototype.mozMatchesSelector ||
                                        Element.prototype.msMatchesSelector ||
                                        Element.prototype.oMatchesSelector ||
                                        Element.prototype.webkitMatchesSelector ||
                                        function(s) {
                                            var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                                                i = matches.length;
                                            while (--i >= 0 && matches.item(i) !== this) {}
                                            return i > -1;
                                        };
                                }

                                var parents = [];

                                for (; elem && elem !== document; elem = elem.parentNode) {
                                    if (selector) {
                                        if (elem.matches(selector)) {
                                            parents.push(elem);
                                        }
                                        continue;
                                    }
                                    parents.push(elem);
                                }

                                return parents;
                            },
                            bookingpress_remove_focus_for_popover(){
                                const vm = this;
                                let list_items = document.querySelectorAll( '.list-group-item' );
                                if( list_items.length > 0 ){
                                    for( l = 0; l < list_items.length; l++ ){
                                        let current_item = list_items[l];
                                        current_item.addEventListener('mouseout', function(e){
                                            
                                            let tooltipElm = e.target.querySelector('.bpa-table-actions span.el-tooltip');
                                            if( null != tooltipElm ){

                                                let has_popover = tooltipElm.querySelector('.el-button.bpa-btn');
                                                
                                                if( null != has_popover ){
                                                    let popoverId = has_popover.getAttribute('aria-describedby');
                                                    let popoverDiv = document.getElementById( popoverId );
                                                    let target = e.target;
                                                    if( target.classList.contains('list-group-item') && target.nodename == 'SPAN' ){
                                                        return false;
                                                    }
                                                    let toElm = e.toElement;
                                                    let target_parents = vm.bookingpress_get_elm_parents( toElm, '.list-group-item');
                                                    
                                                    if( target_parents.length > 0 ){
                                                        return false;
                                                    }
                                                    
                                                    if( null != popoverDiv ){
                                                        let popover_pos = popoverDiv.getBoundingClientRect();
                                                        let is_popover_visible = ( popover_pos.width > 0 && popover_pos.height > 0 ) ? true : false;

                                                        if( is_popover_visible ){
                                                            let parent_elm = vm.bookingpress_get_elm_parents( toElm, '#' + popoverId );

                                                            if( 1 > parent_elm.length ){
                                                                let refBtn = document.querySelector( `.el-popover__reference[aria-describedby="${popoverId}"]`);
                                                                refBtn.click();
                                                                return false;
                                                            }

                                                            popoverDiv.addEventListener( 'mouseout', function(e){
                                                                let parent_elm2 = vm.bookingpress_get_elm_parents( e.toElement, '#' + popoverId);
                                                                
                                                                if( 1 > parent_elm2.length ){
                                                                    let refBtn = document.querySelector( `.el-popover__reference[aria-describedby="${popoverId}"]`);
                                                                    refBtn.click();
                                                                    return false;
                                                                }
                                                            });
                                                        }

                                                        

                                                    }
                                                    
                                                }
                                            }
                                        });
                                    }
                                }

                                let calendar_popover = document.querySelector('#calendar_appointment_popover_dialog');
                                if( null != calendar_popover ){
                                    calendar_popover.addEventListener('mouseup', function(e){
                                        let click_timestamp = vm.bpa_calendar_event_click_timestamp || 0;
                                        let click_parents = vm.bpa_get_target_parent( e.target, '.bpa-fsc-item__popover-card' );
                                        if( 0 == click_parents.length ){
                                            let event_timestamp = Math.round( e.timeStamp );
                                            if( (event_timestamp - click_timestamp) > 1000 ){
                                                vm.bpa_calendar_event_click_timestamp = 0;
                                                vm.closeAppointmentBookingPopover();
                                            }
                                        }
                                    });
                                }

                            },
                            bookingpress_enable_modal(){
                                document.body.style.overflow = 'hidden';
                            },
                            bookingpress_disable_modal(){
                                if(document.body.classList.contains("el-popup-parent--hidden")){
                                    document.body.classList.remove("el-popup-parent--hidden");
                                    document.body.style.paddingRight = "0px";
                                }
                                document.body.style.overflow = 'auto';
                            },
                            openNeedHelper(page_name = '', module_name = '', module_title = ''){
                                const vm = this
                                vm.helpDrawerData = ''
                                vm.is_display_drawer_loader = '1'
                                vm.needHelpDrawer = !this.needHelpDrawer
                                var help_page_name = 'list_'+'<?php echo esc_html($requested_module); ?>';
                                if(page_name != ''){
                                    help_page_name = page_name
                                }
                                var help_module_name = '<?php echo esc_html($requested_module); ?>';
                                if(module_name != ''){
                                    help_module_name = module_name
                                }
                                if(module_title != ''){
                                    this.requested_module = module_title
                                }
                                <?php do_action('bookingpress_change_help_request_module_data'); ?>
                                var postData = { action:'bookingpress_get_help_data',  module: help_module_name, page: help_page_name, type: 'list',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                                .then( function (response) {
                                    vm.is_display_drawer_loader = '0'
                                    vm.helpDrawerData = response.data;
                                    var elements = jQuery('.bpa-help-drawer__body-wrapper');
                                    if(elements.length == 0) {
                                        jQuery(".bpa-hd-header").next().andSelf().wrapAll('<div class="bpa-help-drawer__body-wrapper"></div>');
                                    }  
                                    jQuery(document).ready(function(){
                                        jQuery('figure#watch_now_btn').each(function(){
                                            var bookingpress_data_video_link = jQuery(this).attr('data-video');
                                            jQuery(this).children().wrap('<a href='+ bookingpress_data_video_link +' target="_blank" />');    
                                        });    
                                    });

                                }.bind(vm) )
                                .catch( function (error) {
                                    console.log(error);
                                });
                            },            
                            openNeedHelper_add(page_name = '', module_name = '', module_title = ''){
                                const vm = this
                                vm.helpDrawerData = ''
                                vm.is_display_drawer_loader = '1'
                                this.needHelpDrawer_add = !this.needHelpDrawer_add
                                var help_page_name = 'add_'+'<?php echo esc_html($requested_module); ?>';
                                if(page_name != ''){
                                    help_page_name = page_name
                                }
                                var help_module_name = '<?php echo esc_html($requested_module); ?>';
                                if(module_name != ''){
                                    help_module_name = module_name
                                }
                                if(module_title != ''){
                                    this.requested_module = module_title
                                }
                                var postData = { action:'bookingpress_get_help_data',  module: help_module_name, page: help_page_name, type: 'add',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'  };
                                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                                .then( function (response) {
                                    vm.is_display_drawer_loader = '0'
                                    vm.helpDrawerData = response.data;
                                }.bind(this) )
                                .catch( function (error) {
                                    console.log(error);
                                });
                            },
                            responsiveMenu(){
                                window.onload = function(){
                                    (function() {
                                        if(document.getElementById("bpa-mobile-menu") != null){
                                            document.getElementById("bpa-mobile-menu").onclick = function() {responsiveMenuFunc()};
                                        }
                                    })();
                                    function responsiveMenuFunc() {
                                        var element = document.getElementById("bpa-navbar-nav");
                                        if( element != null ){
                                            element.classList.toggle("bpa-mobile-nav");
                                        }
                                        var element2 = document.getElementById("bpa-mob-nav-overlay");
                                        if( element2 != null ){
                                            element2.classList.toggle("is-visible");
                                        }
                                        var element3 = document.getElementById("bpa-mobile-menu");
                                        if( element3 != null){
                                            element3.classList.toggle("is-active");
                                        }
                                    }    
                                }
                            },
                            open_feature_request_url(){

                                window.open('https://ideas.bookingpressplugin.com/', '_blank');
                            },
                            open_facebook_community_url(){
                                window.open('https://www.facebook.com/groups/bookingpress/', '_blank');
                            },
                            open_youtube_channel_url(){
                                window.open('https://www.youtube.com/@BookingPress/', '_blank');
                            },
                            open_need_help_url(){

                                const vm = this;
                                
                                var bpa_get_url_param = new URLSearchParams(window.location.search);
                                var bpa_get_page = bpa_get_url_param.get('page');
                                var bpa_get_action = bpa_get_url_param.get('action');
                                var bpa_get_setting_page = bpa_get_url_param.get('setting_page');

                                if( bpa_get_page == 'bookingpress_lite_wizard' ){   
                                    vm.openNeedHelper('list_quick_start_guide', 'quick_start_guide', 'Quick Start Guide');
                                    vm.bpa_fab_floating_btn = 0;

                                }else if(bpa_get_page == 'bookingpress_addons'){
                                    vm.openNeedHelper("list_license_settings", "license_settings", "Add-ons");
                                    vm.bpa_fab_floating_btn = 0;

                                }else if( bpa_get_page == 'bookingpress_calendar' ){
                                    vm.openNeedHelper('list_calendar', 'calendar', 'Calendar');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_appointments' ){
                                    vm.openNeedHelper('list_appointments', 'appointments', 'Appointments');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_payments'){
                                    vm.openNeedHelper('list_payments', 'payments', 'Payments')
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_customers'){
                                    vm.openNeedHelper('list_customers', 'customers', 'Customers');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_services'){
                                    vm.openNeedHelper('list_services', 'services', 'Services');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_notifications'){
                                    vm.openNeedHelper('list_notifications', 'notifications', 'Notifications');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_customize' && null == bpa_get_action )  {
                                    vm.openNeedHelper('list_customize', 'customize', 'Customize');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_customize' && ( null != bpa_get_action && bpa_get_action == 'forms' )) {
                                    vm.openNeedHelper('list_customize', 'customize', 'Customize');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_customize' && ( null != bpa_get_action && bpa_get_action == 'form_fields' )) {
                                    vm.openNeedHelper('list_customize_field', 'customize_field', 'Customize');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress'){
                                    vm.openNeedHelper('list_dashboard', 'dashboard', 'Dashboard');
                                    vm.bpa_fab_floating_btn = 0;

                                } else if( bpa_get_page == 'bookingpress_settings'){
                                    var selected_tab_name = sessionStorage.getItem("current_tabname");
                                    console.log( selected_tab_name );
                                    if( null == selected_tab_name && null == bpa_get_setting_page ){
                                        vm.openNeedHelper('list_general_settings', 'general_settings', 'General Settings');
                                        vm.bpa_fab_floating_btn = 0;

                                    }else if( selected_tab_name == 'company_settings' ){
                                        vm.openNeedHelper("list_company_settings", "company_settings", "Company Settings");
                                        vm.bpa_fab_floating_btn = 0;

                                    } else if( selected_tab_name == 'general_settings'){
                                        vm.openNeedHelper('list_general_settings', 'general_settings', 'General Settings');
                                        vm.bpa_fab_floating_btn = 0;

                                    } else if( selected_tab_name == 'notification_settings'){
                                        vm.openNeedHelper('list_notification_settings', 'notification_settings', 'Notification Settings');
                                        vm.bpa_fab_floating_btn = 0;

                                    } else if( selected_tab_name == 'workhours_settings'){
                                        vm.openNeedHelper('list_workhours_settings', 'workhours_settings', 'Working Hours Settings');
                                        vm.bpa_fab_floating_btn = 0;

                                    } else if( selected_tab_name == 'dayoff_settings'){
                                        vm.openNeedHelper('list_daysoff_settings', 'daysoff_settings', 'Holidays');
                                        vm.bpa_fab_floating_btn = 0;

                                    } else if( selected_tab_name == 'payment_settings' ){
                                        vm.openNeedHelper('list_payment_settings', 'payment_settings', 'Payment Settings');
                                        vm.bpa_fab_floating_btn = 0;

                                    } else if( selected_tab_name == 'message_settings'){
                                        vm.openNeedHelper('list_message_settings', 'message_settings', 'Message Settings');
                                        vm.bpa_fab_floating_btn = 0;

                                    } else if( selected_tab_name == 'debug_log_settings'){
                                        vm.openNeedHelper('list_debug_log_settings', 'debug_log_settings', 'Debug Log Settings');
                                        vm.bpa_fab_floating_btn = 0;

                                    } 
                                }
                                <?php do_action('bpa_add_extra_tab_outside_func'); ?>

                            },
                            open_add_appointment_modal() {
                                this.open_appointment_modal = true;
                            },
                            bookingpress_appointment_change_service(){
                                const vm = this;
                                
                                let selected_service = vm.appointment_formdata.appointment_selected_service;
                                let services_lists = vm.appointment_services_list;
                                let max_capacity = 0;
                                let min_capacity = 0;
                                let selected_service_duration_unit = '';
                                let selected_service_duration = '';
                                for( let categories of services_lists ){
                                    let category_service_list = categories.category_services;
                                    for( let services of category_service_list ){
                                        let service_id = services.service_id;
                                        if( service_id == selected_service ){                                            
                                            max_capacity = ( "undefined" != typeof services.service_max_capacity ) ? services.service_max_capacity : 1;
                                            min_capacity = ( "undefined" != typeof services.service_min_capacity ) ? services.service_min_capacity : 1;
                                            selected_service_duration_unit = ( "undefined" != typeof services.service_duration_unit ) ? services.service_duration_unit : '';
                                            selected_service_duration = ( "undefined" != typeof services.service_duration ) ? services.service_duration : '';
                                            if(vm.is_compitible_with_pro == 0) {
                                                max_capacity--;
                                            }
                                            break;
                                        }
                                    }
                                }
                                <?php do_action('bookingpress_before_change_backend_service'); ?>
                                vm.appointment_formdata.selected_bring_members = 0;
                                if(vm.is_compitible_with_pro == 1) {
                                    vm.appointment_formdata.selected_bring_members = 1;
                                    if( min_capacity > 1 ){
                                        vm.appointment_formdata.selected_bring_members = min_capacity;
                                    }
                                }
                                vm.appointment_formdata.bookingpress_bring_anyone_max_capacity = parseInt(max_capacity);
                                vm.appointment_formdata.bookingpress_bring_anyone_min_capacity = parseInt(min_capacity);
                                vm.appointment_formdata.selected_service_duration_unit = selected_service_duration_unit;
                                vm.appointment_formdata.selected_service_duration = selected_service_duration;
                                vm.bookingpress_appointment_get_disable_dates();

                                <?php do_action('bookingpress_change_backend_service'); ?>
                            },
                            bookingpress_appointment_get_disable_dates( reset_timeslot_field = true ){
                                const vm = this;
                                let bookingpress_appointment_form_data = vm.appointment_formdata;

                                if( true == reset_timeslot_field ){
                                    vm.appointment_formdata.appointment_booked_time = "";
                                }
                                var bookingpress_appointment_date = vm.appointment_formdata.appointment_booked_date;
                                var bookingpress_moment_formatted_date = moment(bookingpress_appointment_date);
                                bookingpress_appointment_date = bookingpress_moment_formatted_date.format('YYYY-MM-DD');

                                let is_rescheduling_event = false;
                                if( "undefined" != typeof bookingpress_appointment_form_data.appointment_update_id && bookingpress_appointment_form_data.appointment_update_id != "" ){
                                    is_rescheduling_event = true;
                                }

                                let postData = {
                                    action:"bookingpress_get_disable_date",
                                    _wpnonce:bookingpress_appointment_form_data._wpnonce,
                                    appointment_data_obj:bookingpress_appointment_form_data,
                                    service_id: bookingpress_appointment_form_data.appointment_selected_service,
                                    selected_date: bookingpress_appointment_date,
                                    bpa_fetch_data: true,
                                    selected_service: bookingpress_appointment_form_data.appointment_selected_service,
                                    is_rescheduling_event : is_rescheduling_event,
                                };
                                <?php do_action( 'bookingpress_set_additional_appointment_xhr_data' ) ?>
                                postData.appointment_data_obj = JSON.stringify( postData.appointment_data_obj );
                                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                                .then( function(response){
                                    if( response.data.variant == "success" ){
                                        let disableDates = response.data.days_off_disabled_dates;
                                        let disableDates_arr = disableDates.split(',');                                        
                                        let disableDates_formatted = [];
                                        disableDates_arr.forEach(function( date ){                                            
                                            let formatted_date = vm.get_formatted_date( date );
                                            disableDates_formatted.push( formatted_date );
                                        });
                                        
                                        vm.pickerOptions.disabledDate = function(Time){                                            
                                            let currentDate = new Date( Time );
                                            currentDate = vm.get_formatted_date( currentDate );
                                            var date = new Date();
                                            
                                            date.setDate(date.getDate()-1);
                                              
                                            var disable_past_date = Time.getTime() < date.getTime();

                                            if( disableDates_formatted.indexOf( currentDate ) > -1 ){
                                                return true;
                                            } else {
                                                return disable_past_date;
                                            }
                                        };

                                        /** timeslot data start */

                                        if(typeof response.data.front_timings !== "undefined") {
                                            
                                            let timeslot_response_data = response.data.front_timings;
                                            let morning_times = timeslot_response_data.morning_time;
                                            let afternoon_times = timeslot_response_data.afternoon_time;
                                            let evening_times = timeslot_response_data.evening_time;
                                            let night_times = timeslot_response_data.night_time;
                                            
                                            let timeslot_data = {
                                                morning_time: {
                                                    timeslot_label: "Morning",
                                                    timeslots: morning_times
                                                },
                                                afternoon_time: {
                                                    timeslot_label: "Afternoon",
                                                    timeslots: afternoon_times
                                                },
                                                evening_time: {
                                                    timeslot_label: "Evening",
                                                    timeslots: evening_times
                                                },
                                                night_time: {
                                                    timeslot_label: "Night",
                                                    timeslots: night_times
                                                }
                                            };

                                        vm.appointment_time_slot = timeslot_data;

                                        }
                                        
                                        /** timeslot data end */

                                        /** change current date */
                                        vm.disabledDates = disableDates_formatted;

                                        if(typeof vm.disabledDates != 'undefined'){
                                            if(typeof vm.disabledDates.length != 'undefined'){
                                                if(vm.disabledDates.includes(vm.appointment_formdata.appointment_booked_date)){                                                    
                                                    vm.appointment_formdata.appointment_booked_date = response.data.selected_date;
                                                } 
                                            }
                                        }
                                        /* vm.appointment_formdata.appointment_booked_date = response.data.selected_date;  */                                                                          
                                        /** change current date */
                                        
                                        <?php 
                                            do_action('bookingpress_additional_disable_dates');
                                        ?>
                                    }
                                }.bind(this) )
                                    .catch( function (error) {
                                        console.log(error);
                                });

                                <?php do_action('bookingpress_after_get_backend_disable_dates'); ?>
                            },
                            select_appointment_booking_date( selected_value ){
                                const vm = this;
                                vm.appointment_formdata.appointment_booked_date = vm.appointment_formdata.selected_date = selected_value;
                                let bookingpress_appointment_form_data = vm.appointment_formdata;
                                let postData = {
                                    action:"bookingpress_front_get_timings",
                                    _wpnonce:bookingpress_appointment_form_data._wpnonce,
                                    appointment_data_obj:JSON.stringify( bookingpress_appointment_form_data ),
                                    service_id: bookingpress_appointment_form_data.appointment_selected_service,
                                    selected_date: selected_value
                                };                                
                                <?php do_action('bookingpress_get_front_timing_set_additional_appointment_xhr_data') ?>

                                if( "" != bookingpress_appointment_form_data.selected_staffmember ){
                                    postData.staffmember_id = bookingpress_appointment_form_data.selected_staffmember;
                                    postData.bookingpress_selected_staffmember = {
                                        selected_staff_member_id: bookingpress_appointment_form_data.selected_staffmember
                                    }
                                }

                                if(  "undefined" != typeof vm.bookingpress_loaded_extras && "undefined" != typeof vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service] ){
                                    let bpa_selected_extras = {};
                                    vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service].forEach(function( element ){
                                        let is_selected = element.bookingpress_is_selected;
                                        if( "true" == is_selected || true == is_selected ){
                                            bpa_selected_extras[ element.bookingpress_extra_services_id ] = {
                                                "bookingpress_is_selected":"true",
                                                "bookingpress_selected_qty":element.bookingpress_selected_qty
                                            };
                                        }
                                    });
                                    postData.appointment_data_obj.bookingpress_selected_extra_details = bpa_selected_extras;
                                }
                                if( "undefined" != typeof vm.appointment_formdata.selected_bring_members ){
                                    postData.appointment_data_obj.bookingpress_selected_bring_members = vm.appointment_formdata.selected_bring_members;
                                }

                                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                                .then( function(response) {

                                    vm.appointment_formdata.appointment_booked_time = "";
                                    vm.appointment_formdata.appointment_booked_end_time = "";
                                    
                                    let timeslot_response_data = response.data;
                                    let morning_times = timeslot_response_data.morning_time;
                                    let afternoon_times = timeslot_response_data.afternoon_time;
                                    let evening_times = timeslot_response_data.evening_time;
                                    let night_times = timeslot_response_data.night_time;
                                    
                                    let timeslot_data = {
                                        morning_time: {
                                            timeslot_label: "Morning",
                                            timeslots: morning_times
                                        },
                                        afternoon_time: {
                                            timeslot_label: "Afternoon",
                                            timeslots: afternoon_times
                                        },
                                        evening_time: {
                                            timeslot_label: "Evening",
                                            timeslots: evening_times
                                        },
                                        night_time: {
                                            timeslot_label: "Night",
                                            timeslots: night_times
                                        }
                                    };
                                    vm.appointment_time_slot = timeslot_data;

                                    <?php do_action('bookingpress_backend_after_get_timeslot_response'); ?>

                                
                                }.bind(this) )
                                .catch( function( error ){
                                    console.log( error )
                                })
                            },
                            editAppointmentData(index,row, calendar = false, elm_target, is_more ) {
                                const vm2 = this;
                                var edit_id = row.appointment_id;
                                vm2.appointment_formdata.appointment_update_id = edit_id;
                                if( true == calendar ){
                                    var bookingpress_appointment_date = row.start_date;
                                    var bookingpress_appointment_end_date = row.end_date;
                                    var bookingpress_moment_formatted_date = moment(bookingpress_appointment_date);
                                    var bookingpress_moment_formatted_end_date = moment(bookingpress_appointment_end_date);
                                    bookingpress_appointment_date = bookingpress_moment_formatted_date.format('YYYY-MM-DD');
                                    bookingpress_appointment_end_date = bookingpress_moment_formatted_end_date.format( 'YYYY-MM-DD');
                                    var bookingpress_appointment_start_time = bookingpress_moment_formatted_date.format('HH:mm:ss');
                                    var bookingpress_appointment_end_time = bookingpress_moment_formatted_end_date.format('HH:mm:ss');
                                    var postData = { action:'bookingpress_get_popover_appointment_data', bookingpress_sel_appointment_date: bookingpress_appointment_date, bookingpress_sel_appointment_end_date: bookingpress_appointment_end_date, appointment_id: edit_id, bookingpress_appointment_start_time : bookingpress_appointment_start_time,bookingpress_appointment_end_time: bookingpress_appointment_end_time ,activeView : vm2.activeView,search_data : vm2.search_data,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
									postData.screen_size = vm2.current_screen_size;
                                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                                        .then( function (response) {
                                            if(response.data != undefined || response.data != [])
                                            {
                                                appointment_start_time = response.data.bookingpress_popover_appointemnt_data[0].bookingpress_appointment_time;
                                                appointment_end_time = response.data.bookingpress_popover_appointemnt_data[0].bookingpress_appointment_end_time;
                                                vm2.appointment_formdata.bookingpress_appointemnt_popover_timeslot_title = appointment_start_time+ ' - '+appointment_end_time;
                                                vm2.appointment_formdata.bookingpress_appointemnt_popover_title = response.data.bookingpress_popover_appointemnt_data[0].bookingpress_appointment_date;
                                                vm2.appointment_formdata.bookingpress_appointment_popover_data = response.data.bookingpress_popover_appointemnt_data;
                                                vm2.appointment_formdata.bookingpress_total_popover_appointemnt = response.data.bookingpress_total_popover_appointemnt;
                                                vm2.appointment_formdata.row = {
                                                    appointment_id: response.data.bookingpress_popover_appointemnt_data[0].bookingpress_appointment_booking_id,
                                                };
                                                vm2.bpa_display_calendar_popover_loader = 0;

                                            }											
                                        }.bind(this) )
                                        .catch( function (error) {
                                            console.log(error);
                                        });

                                } else {
                                    if( 'calendar_popover' == calendar ){
                                        var edit_id = index;
                                        vm2.appointment_formdata.appointment_update_id = edit_id;
                                        vm2.openAppointmentBookingModal();
                                        
                                    } else {
                                        vm2.open_add_appointment_modal();
                                    }
                                    var postData = { action:'bookingpress_get_edit_appointment_data', payment_log_id: edit_id, appointment_id: edit_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                                        .then( function (response) {
                                            if(response.data != undefined || response.data != [])
                                            {   
                                                var bookingpress_tmp_date = new Date(response.data.bookingpress_appointment_date).toLocaleString("en-US", {timeZone: 'UTC'});
                                                var bookingpress_tmp_date2 = response.data.bookingpress_appointment_date;
                                                
                                                //bookingpress_tmp_date2 = bookingpress_tmp_date2.replace(/-/g, "/");
                                                
                                                vm2.appointment_customers_list = response.data.appointment_customer_list;  
                                                vm2.appointment_formdata.appointment_selected_customer = response.data.bookingpress_customer_id
                                                vm2.customer_id = vm2.appointment_formdata.appointment_selected_customer
                                                vm2.appointment_formdata.appointment_selected_service = response.data.bookingpress_service_id
                                                //vm2.appointment_formdata.appointment_booked_date = new Date(response.data.bookingpress_appointment_date)
                                                //vm2.appointment_formdata.appointment_booked_date = bookingpress_tmp_date
                                                vm2.appointment_formdata.appointment_booked_date = bookingpress_tmp_date2;
                                                vm2.appointment_formdata.appointment_booked_time = response.data.bookingpress_appointment_time
                                                vm2.appointment_formdata.appointment_booked_end_time = response.data.bookingpress_appointment_end_time
                                                vm2.appointment_formdata.appointment_internal_note = response.data.bookingpress_appointment_internal_note            
                                                vm2.appointment_time_slot = response.data.appointment_time_slot    

                                                vm2.appointment_formdata.appointment_status = response.data.bookingpress_appointment_status
                                                <?php
                                                    do_action('bookingpress_edit_appointment_details');
                                                ?>
                                                vm2.bookingpress_appointment_get_disable_dates( false );
                                            }
                                        }.bind(this) )
                                        .catch( function (error) {
                                            console.log(error);
                                        });
                                }
                            },
                            /* eventRendered(){
                                const vm = this;
                                if( vm.activeView != 'month' ){
                                    let totalEvents = document.querySelectorAll( '.bpa-cal-event-card' );
                                    if( 0 < totalEvents.length ){
                                        for( let x = 0; x < totalEvents.length; x++ ){
                                            let currentEvent = totalEvents[x];
                                            if( null == currentEvent ){
                                                continue;
                                            }
                                            let currentEventOuterHeight = parseInt( currentEvent.offsetHeight );
                                            let currentEventStyleHeight = parseInt(currentEvent.style.height);
                                            if( currentEventStyleHeight > currentEventOuterHeight ){
                                               currentEvent.style.minHeight = currentEventStyleHeight + 'px';
                                            }
                                        }
                                    }
                                }
                            }, */
                            editEventCalendar( event, e ){
                                const vm = this;
                                let bpa_screen_size = vm.current_screen_size;
                                //vm.bpa_calendar_event_clicks++;
                                let clickTimestamp = Math.round( e.timeStamp );
                                vm.bpa_calendar_event_click_timestamp = clickTimestamp;
                                //if( 1 == vm.bpa_calendar_event_clicks ){
                                    //vm.bpa_calendar_event_clicks = 0;
                                    if( "mobile" != bpa_screen_size ){
                                        vm.editEvent( event, e );
                                    } else {
                                        vm.editEvent_mobile( event, e );
                                    }
                                //}  else {
                                    /* e.stopPropagation();
                                    e.preventDefault();
                                    e.stopPropagation();
                                    clearTimeout( vm.bpa_calendar_event_timer );
                                    return false;
                                    vm.bpa_calendar_event_clicks = 0; */
                                //}
                            },
                            editEvent(event, e){
                                const vm = this;
                                vm.bpa_calendar_popover_target = e.target;
                                vm.openAppointmentBookingPopover( e.target, false );
                                let row = {
                                    appointment_id: event.appointment_id,
                                    start_date : event.start,
                                    end_date : event.end
                                };
                                if( e.target.classList.contains('vuecal_more_event') ){
                                    vm.appointment_formdata.first_expanded_collapse='';
                                }
                                else {
                                    vm.appointment_formdata.first_expanded_collapse = String(event.appointment_id);
                                }
                                vm.bpa_display_calendar_popover_loader = '1';
                                this.editAppointmentData( -1, row, true, e.target );
                                
                            },
                            editEvent_mobile(event, e){
								const vm = this;
                                let row = {
                                    appointment_id: event.appointment_id,
                                    start_date : event.start,
                                    end_date : event.end
                                };

                                vm.bpa_calendar_popover_target = e.target;
                                vm.openAppointmentBookingPopover( e.target, false );

                                if( e.target.classList.contains('vuecal_more_event') ){
                                    vm.appointment_formdata.first_expanded_collapse='';
                                }
                                else {
                                    vm.appointment_formdata.first_expanded_collapse = String(event.appointment_id);
                                }
                                
                                vm.bpa_display_calendar_popover_loader = '1';
                                this.editAppointmentData( -1, row, true, e.target );
                                
                            },
                            more_event_callback( cell, view, events, goNarrower ){
								const vm = this;
								let row = {};
								if( events.length > 0 ){
									row.appointment_id = events[0].appointment_id;
									row.start_date = events[0].start;
									row.end_date= events[0].end;
								}
								let target = document.querySelector( `.${cell.class}.vuecal__cell--selected.vuecal__cell--has-events .vuecal_more_event` );

								vm.appointment_formdata.first_expanded_collapse='';
                                vm.bpa_display_calendar_popover_loader = '1';
								this.editAppointmentData( -1, row, true, target, true );
                                vm.bpa_calendar_popover_target = target;
                                vm.openAppointmentBookingPopover( target, true );
							},
                            bpa_adjust_calendar_popover_position( element, is_more = false ){
                                const vm = this;

                                let cal_popover_wrapper = document.getElementById( 'calendar_appointment_popover_dialog' );

                                let parent_cls_check = 'vuecal__event';
                                if( true == is_more ){
                                    parent_cls_check = 'vuecal__cell';
                                }

                                document.body.style.overflow = 'hidden';

                                if( null != cal_popover_wrapper ){
                                    let cal_dialog = cal_popover_wrapper.querySelector( '.el-dialog' );
                                    setTimeout(function(){
                                        let dialog_height = cal_dialog.offsetHeight;
                                        let dialog_top = cal_dialog.getBoundingClientRect().top;
                                        dialog_top = Math.floor( dialog_top );
                                        let parent;
                                        if( element.classList.contains( parent_cls_check  ) || true == is_more ){
                                            parent = element;
                                        } else {
                                            let parents = vm.bpa_get_target_parent( element, `.${parent_cls_check}` );
                                            if( parents.length > 0 ){
                                                parent = parents[0];
                                            }
                                        }

                                        if( "undefined" == typeof parent ){
                                            return false;
                                        }

                                        let parent_width = parent.offsetWidth;
                                        let parent_left = parent.getBoundingClientRect().left;
                                        parent_left = Math.round( parent_left );
                                        let window_width = window.outerWidth;

                                        let available_space_on_right = window_width - ( parent_left + parent_width );
                                        let available_space_on_left = parent_left - window.pageXOffset;
                                        

                                        let dialog_proposed_width = 380;

                                        let place_on_right = true;
                                        if( available_space_on_right < dialog_proposed_width ){
                                            place_on_right = false;
                                        }
                                        
                                        if( place_on_right == false && ( available_space_on_left < dialog_proposed_width ) ){
                                            vm.bpa_calendar_dialog_custom_cls = 'bpa-fsc-item__popover-card bpa-is-center';
                                            return false;
                                        }
                                        
                                        let parent_top = parent.getBoundingClientRect().top;
                                        parent_top = Math.round( parent_top );

                                        let open_on_top = false;
                                        if( parent_top > dialog_height ){
                                            open_on_top = true;
                                        }

                                        let windowHeight = window.outerHeight;
                                        let parentHeight = parent.offsetHeight;

                                        let popover_top = 150;
                                        let use_fixed_top = false;
                                        if( parentHeight > windowHeight ){
                                            

                                            if( true == open_on_top ){
                                                open_on_top = false;
                                            }

                                            if( parent_top < 130 ){
                                                use_fixed_top = true;
                                            }

                                        }

                                        let popover_top_adjustment = 60;
                                        if( true == is_more ){
                                            popover_top_adjustment = 80;
                                        }

                                        
                                        vm.bpa_calendar_dialog_custom_cls = 'bpa-fsc-item__popover-card';
                                        cal_dialog.style.left = '';
                                        cal_dialog.style.right = '';
                                        cal_dialog.style.top = '';
                                        cal_dialog.style.bottom = '';
                                        let parent_height = parent_top + parent.offsetHeight;
                                        
                                        if( true == place_on_right && false == open_on_top ){
                                            vm.bpa_calendar_dialog_custom_cls += ' bpa_cal_popover_bottom_right';

                                            cal_dialog.style.margin = 0;
                                            cal_dialog.style.position = 'absolute';
                                            cal_dialog.style.left = (parent_left + parent_width + 20) + 'px';
                                            if( true == use_fixed_top ){
                                                cal_dialog.style.top =  popover_top + 'px';
                                            } else {
                                                cal_dialog.style.top = ( ( parent_top ) - popover_top_adjustment ) + 'px';
                                            }

                                            vm.bpa_calendar_dialog_custom_cls += ' active';

                                        } else if( true == place_on_right && true == open_on_top ){
                                            vm.bpa_calendar_dialog_custom_cls += ' bpa_cal_popover_top_right';
                                            
                                            cal_dialog.style.margin = 0;
                                            
                                            cal_dialog.style.position = 'absolute';
                                            cal_dialog.style.left = (parent_left + parent_width + 20) + 'px';
                                            if( true == use_fixed_top ){
                                                cal_dialog.style.top =  popover_top + 'px';
                                            } else {
                                                cal_dialog.style.top = ( ( parent_height - dialog_height ) + popover_top_adjustment ) + 'px';
                                            }

                                            vm.bpa_calendar_dialog_custom_cls += ' active';

                                        } else if( false == place_on_right && true == open_on_top ){
                                            vm.bpa_calendar_dialog_custom_cls += ' bpa_cal_popover_top_left';

                                            cal_dialog.style.margin = 0;
                                            cal_dialog.style.position = 'absolute';
                                            cal_dialog.style.left = ( ( parent_left - dialog_proposed_width ) - 20) + 'px';
                                            if( true == use_fixed_top ){
                                                cal_dialog.style.top =  popover_top + 'px';
                                            } else {
                                                cal_dialog.style.top = ( ( parent_height - dialog_height ) + popover_top_adjustment ) + 'px';
                                            }

                                            vm.bpa_calendar_dialog_custom_cls += ' active';

                                        } else if( false == place_on_right && false == open_on_top ){
                                            vm.bpa_calendar_dialog_custom_cls += ' bpa_cal_popover_bottom_left';

                                            cal_dialog.style.margin = 0;
                                            cal_dialog.style.position = 'absolute';
                                            cal_dialog.style.left = ( (parent_left - dialog_proposed_width) - 20) + 'px';
                                            if( true == use_fixed_top ){
                                                cal_dialog.style.top =  popover_top + 'px';
                                            } else {
                                                cal_dialog.style.top = ( ( parent_top ) - popover_top_adjustment ) + 'px';
                                            }

                                            vm.bpa_calendar_dialog_custom_cls += ' active';
                                        }

                                    },1);
                                }

                            },
                            bpa_adjust_calendar_popover_position_old( element, is_more = false ){
                                const vm = this;
								
								let parent_cls_check = 'vuecal__event';
								let height_adjust = 500;
								if( true == is_more ){
									parent_cls_check = 'vuecal__cell';
									height_adjust = 200;
								}
                                let parent;

                                if( element.classList.contains(parent_cls_check) ){
                                    parent = element;
                                } else {
                                    let parents = vm.bpa_get_target_parent( element, `.${parent_cls_check}` );
                                    if( parents.length > 0 ){
                                        parent = parents[0];
                                    }
                                }

                                
                                let dialog_pos_x = parent.getBoundingClientRect().left + parent.offsetWidth + 20;
                                let dialog_pos_y = parent.getBoundingClientRect().top - (height_adjust - parent.offsetHeight);

                                <?php if( is_rtl() ){ ?>
                                    dialog_pos_x = parent.getBoundingClientRect().left - 38;
                                <?php } ?>

                                vm.$el.querySelector('.bpa-fsc-item__popover-card').style.position = 'absolute';
                                vm.$el.querySelector('.bpa-fsc-item__popover-card').style.marginTop = '0px';
                                vm.$el.querySelector('.bpa-fsc-item__popover-card').style.top = dialog_pos_y + 'px';
                                vm.$el.querySelector('.bpa-fsc-item__popover-card').style.left = dialog_pos_x + 'px';

                            },
                            get_formatted_datetime(iso_date) {			
                                var __date = new Date(iso_date);
                                var hour = __date.getHours();
                                var minute = __date.getMinutes();
                                var second = __date.getSeconds();

                                if (minute < 10) {
                                    minute = '0' + minute;
                                }
                                if (second < 10) {
                                    second = '0' + second;
                                }
                                var formatted_time = hour + ":" + minute + ":" + second;				
                                var __year = __date.getFullYear();
                                var __month = __date.getMonth()+1;
                                var __day = __date.getDate();
                                if (__day < 10) {
                                    __day = '0' + __day;
                                }
                                if (__month < 10) {
                                    __month = '0' + __month;
                                }

                                var formatted_date = __year+'-'+__month+'-'+__day;
                                return formatted_date+' '+formatted_time; 
                            },
                            bpa_get_target_parent( elem, selector ){
                                if (!Element.prototype.matches) {
                                    Element.prototype.matches = Element.prototype.matchesSelector ||
                                        Element.prototype.mozMatchesSelector ||
                                        Element.prototype.msMatchesSelector ||
                                        Element.prototype.oMatchesSelector ||
                                        Element.prototype.webkitMatchesSelector ||
                                        function(s) {
                                            var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                                                i = matches.length;
                                            while (--i >= 0 && matches.item(i) !== this) {}
                                            return i > -1;
                                        };
                                }

                                var parents = [];

                                for (; elem && elem !== document; elem = elem.parentNode) {
                                    if (selector) {
                                        if (elem.matches(selector)) {
                                            parents.push(elem);
                                        }
                                        continue;
                                    }
                                    parents.push(elem);
                                }

                                return parents;
                            },
                            bpa_adjust_popup_position( currentElement, selector, sourceCls = '', position, view = '' ){

                                let paths = currentElement.path;
                                let buttonElm = null;
                                
                                if( typeof paths != 'undefined' ){
                                    for( let x in paths ){
                                        let currentPath = paths[x];
                                        let currentPathNodeName = currentPath.nodeName;
                                        if( "BUTTON" == currentPathNodeName || ( 'undefined' != typeof sourceCls && '' != sourceCls && currentPath.classList.contains(sourceCls)) ){
                                            buttonElm = currentPath;
                                            break;
                                        }
                                    }
                                } else {

                                    if( "BUTTON" == currentElement.target.nodeName || ( 'undefined' != typeof sourceCls && '' != sourceCls && currentElement.target.classList.contains(sourceCls) ) ){
                                        buttonElm = currentElement.target;
                                    } else {
                                        let par = this.bpa_get_target_parent( currentElement.target, 'button' );
                                        
                                        if( par.length > 0 ){
                                            buttonElm = par[0];
                                        } else {
                                            let par = this.bpa_get_target_parent( currentElement.target, '.' + sourceCls );
                                            if( par.length > 0 ){
                                                buttonElm = par[0];
                                            }
                                        }
                                    }
                                }
                                
                                if( null !== buttonElm ){
                                    let pos_x = buttonElm.getBoundingClientRect().left;
                                    let pos_y = buttonElm.getBoundingClientRect().top;
                                    
                                    pos_x = Math.ceil( pos_x );
                                    pos_y = Math.ceil( pos_y );

                                    let btn_height = buttonElm.offsetHeight;
                                    let pos_top = pos_y + btn_height + 20;

                                    let dialog__wrapper = document.querySelector( selector );
                                    if( null !== dialog__wrapper ){
                                        
                                        
                                        (function(pos_x, buttonElm, dialog__wrapper){
                                            setTimeout(function(){

                                                let viewportHeight = window.innerHeight;
                                                let dialogHeight = dialog__wrapper.offsetHeight;
                                                let spaceBelow = viewportHeight - pos_y - btn_height;
                                                let spaceAbove = pos_y;

                                                if(view == "tablet") {
                                                    if (spaceBelow >= dialogHeight) {
                                                        pos_top = pos_y + btn_height + 20; // Show below
                                                    } else if (spaceAbove >= dialogHeight) {
                                                        pos_top = pos_y - dialogHeight - 20; // Show above
                                                    } else {
                                                       pos_top = Math.max(minMargin, (viewportHeight - dialogHeight) / 2);
                                                    }
                                                    if (pos_top < 0) pos_top = 10;
                                                    if (pos_top + dialogHeight > viewportHeight) pos_top = viewportHeight - dialogHeight - 10;
                                                }

                                                dialog__wrapper.style.position = '';
                                                dialog__wrapper.style.margin = '';
                                                dialog__wrapper.style.top = '';
                                                dialog__wrapper.style.left = '0';

                                                dialog__wrapper.style.position = 'absolute';
                                                dialog__wrapper.style.margin = '0';
                                                dialog__wrapper.style.top = parseInt(pos_top) + 'px';

                                                let pos_to_place = pos_x + ( buttonElm.offsetWidth * 0.5 );
                                                let dialog_pos_right = dialog__wrapper.offsetWidth + dialog__wrapper.getBoundingClientRect().left;
                                                <?php
                                                if( is_rtl() ){
                                                    ?>
                                                    if( '' != position && 'right' == position ){
                                                        dialog_pos_right = dialog_pos_right - 50;
                                                        dialog__wrapper.style.left = pos_to_place - dialog_pos_right + 'px';
                                                    } else {
                                                        dialog_pos_right = dialog_pos_right - 30;
                                                        dialog__wrapper.style.left = (pos_to_place -  ( dialog__wrapper.getBoundingClientRect().left + 40 ) ) + 'px';
                                                    }
                                                <?php
                                                } else {
                                                    ?>
                                                    if( '' != position && 'right' == position ){
                                                        dialog_pos_right = dialog_pos_right - 30;
                                                        dialog__wrapper.style.left = (pos_to_place -  ( dialog__wrapper.getBoundingClientRect().left + 40 ) ) + 'px';
                                                    } else {
                                                        let currentElementId = currentElement.target.id;
                                                        let is_mobile = false;
                                                        if( null != document.getElementById('bpa-mobile-menu') ){
                                                            let is_mob_menu_visible = getComputedStyle( document.getElementById('bpa-mobile-menu') ).display;
                                                            if( 'block' == is_mob_menu_visible ){
                                                                is_mobile = true;
                                                            }
                                                        }
                                                        if( is_mobile == true && (('BUTTON' == currentElement.target.nodeName && currentElementId == 'bpa-appointment-share-url-button') || ( 'SPAN' == currentElement.target.nodeName && currentElement.target.parentNode != null && currentElement.target.parentNode.id == 'bpa-appointment-share-url-button' ) || ('SPAN' == currentElement.target.nodeName && currentElementId == 'bpa-appointment-share-url-span-txt')) ){
                                                            let buttonWidth = document.getElementById( 'bpa-appointment-share-url-button' ).offsetWidth;   
                                                            let popupWidth = dialog__wrapper.offsetWidth;
                                                            if( pos_x <= 44 ){
                                                                dialog_pos_right = dialog_pos_right - 110;
                                                            }   
                                                        }
                                                        dialog_pos_right = dialog_pos_right - 50;
                                                        dialog__wrapper.style.left = pos_to_place - dialog_pos_right + 'px';
                                                    }
                                                <?php
                                                }
                                                ?>
                                                
                                            },10)
                                        })( pos_x, buttonElm, dialog__wrapper );
                                    }
                                }
                            },
                            BPAGetParents( elem, selector ){
                                if (!Element.prototype.matches) {
                                    Element.prototype.matches = Element.prototype.matchesSelector ||
                                        Element.prototype.mozMatchesSelector ||
                                        Element.prototype.msMatchesSelector ||
                                        Element.prototype.oMatchesSelector ||
                                        Element.prototype.webkitMatchesSelector ||
                                        function(s) {
                                            var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                                                i = matches.length;
                                            while (--i >= 0 && matches.item(i) !== this) {}
                                            return i > -1;
                                        };
                                }
                            
                                var parents = [];
                            
                                for (; elem && elem !== document; elem = elem.parentNode) {
                                    if (selector) {
                                        if (elem.matches(selector)) {
                                            parents.push(elem);
                                        }
                                        continue;
                                    }
                                    parents.push(elem);
                                }
                            
                                return parents;
                            },
                            bookingpress_remove_date_range_picker_focus(event){                               
                                <?php 
                                    if(wp_is_mobile()){
                                ?>
                                const datepickerinput = document.querySelectorAll(".el-range-input");
                                if(typeof datepickerinput != "undefined"){
                                    datepickerinput.forEach((dateItem) => {
                                        dateItem.blur();
                                    });                    
                                }
                                <?php 
                                    }
                                ?>
                            },                             
                            <?php do_action( 'bookingpress_admin_panel_vue_methods'); ?>
                            <?php do_action('bookingpress_' . $requested_module . '_dynamic_vue_methods'); ?>
                        },
                    });
                });
                </script>
                <?php
            }
        }
        
        /**
         * Check current page is front page or not
         *
         * @return void
         */
        function bookingpress_is_front_page()
        {
            global $wp, $wpdb, $wp_query, $post;
            if (! is_admin() ) {
                $bookingpress_is_front_page = false;               
                $found_matches = array();
                $pattern       = '\[(\[?)(bookingpress_(.*?))(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                $posts         = $wp_query->posts;
                if (is_array($posts) ) {
                    foreach ( $posts as $mypost ) {
                        if (preg_match_all('/' . $pattern . '/s', $mypost->post_content, $matches) > 0 ) {
                               $found_matches[] = $matches;
                        }
                    }
                }
                /* Remove empty array values. */
                
                $found_matches = $this->bpa_array_trim($found_matches);
                if (! empty($found_matches) && count($found_matches) > 0 ) {
                    $bookingpress_is_front_page = true;                    
                }
                $bookingpress_is_front_page = apply_filters('bookingpress_check_front_page_or_not', $bookingpress_is_front_page);                
                return $bookingpress_is_front_page;
            }
        }

        function bpa_array_trim( $array )
        {
            if (is_array($array) ) {
                foreach ( $array as $key => $value ) {
                    if (is_array($value) ) {
                        $array[ $key ] = $this->bpa_array_trim($value);
                    } else {
                        $array[ $key ] = trim($value);
                    }
                    if (empty($array[ $key ]) ) {
                           unset($array[ $key ]);
                    }
                }
            } else {
                $array = trim($array);
            }
            return $array;
        }
        
        /**
         * Get help drawer content
         *
         * @return void
         */
        function bookingpress_get_help_data_func()
        {
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();
            }
            $bookingpress_documentation_content = '';
            if (! empty($_POST['action']) && ! empty($_POST['module']) && ! empty($_POST['page']) && ! empty($_POST['type']) ) {
                $help_module = sanitize_text_field($_POST['module']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $help_page   = sanitize_text_field($_POST['page']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $help_type   = sanitize_text_field($_POST['type']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

                $bookingpress_remote_url = 'https://www.bookingpressplugin.com/';
                
                if ($help_type == 'list' ) {
                    $bookingpress_remote_params = array(
                    'method'  => 'POST',
                    'body'    => array(
                    'action' => 'get_documentation',
                    'module' => $help_module,
                    'page'   => 'list_' . $help_module,
                    ),
                    'timeout' => 45,
                    );

                    $bookingpress_documentation_res = wp_remote_post($bookingpress_remote_url, $bookingpress_remote_params);
                    if (! is_wp_error($bookingpress_documentation_res) ) {
                        $bookingpress_documentation_content = ! empty($bookingpress_documentation_res['body']) ? $bookingpress_documentation_res['body'] : '';
                    } else {
                        $bookingpress_documentation_content = $bookingpress_documentation_res->get_error_message();
                    }
                } elseif ($help_type == 'add' ) {
                    $bookingpress_remote_params = array(
                    'method'  => 'POST',
                    'body'    => array(
                    'action' => 'get_documentation',
                    'module' => $help_module,
                    'page'   => 'list_' . $help_module,
                    ),
                    'timeout' => 45,
                    );

                    $bookingpress_documentation_res = wp_remote_post($bookingpress_remote_url, $bookingpress_remote_params);
                    if (! is_wp_error($bookingpress_documentation_res) ) {
                        $bookingpress_documentation_content = ! empty($bookingpress_documentation_res['body']) ? $bookingpress_documentation_res['body'] : '';
                    } else {
                        $bookingpress_documentation_content = $bookingpress_documentation_res->get_error_message();
                    }
                }
            }
         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: output is properly escaped or hardcoded

            echo $bookingpress_documentation_content; //phpcs:ignore
            exit();
        }
        
        /**
         * Ajax based action after deleted user
         *
         * @param  mixed $user_id
         * @param  mixed $reassign
         * @return void
         */
        function bookingpress_after_deleted_user_action( $user_id, $reassign = 1 )
        {
            global $wpdb, $tbl_bookingpress_customers;
            $wpdb->delete($tbl_bookingpress_customers, array( 'bookingpress_wpuser_id' => $user_id ), array( '%d' ));
        }

        /* Setting Capabilities for user */
        function bookingpress_capabilities()
        {
            $cap = array(
                'bookingpress'               => '',
                'bookingpress_calendar'      => esc_html__('Calendar', 'bookingpress-appointment-booking'),
                'bookingpress_appointments'  => esc_html__('Appointments', 'bookingpress-appointment-booking'),
                'bookingpress_payments'      => esc_html__('Payments', 'bookingpress-appointment-booking'),
                'bookingpress_customers'     => esc_html__('Customers', 'bookingpress-appointment-booking'),
                'bookingpress_services'      => esc_html__('Services', 'bookingpress-appointment-booking'),
                'bookingpress_notifications' => esc_html__('Notifications', 'bookingpress-appointment-booking'),
                'bookingpress_customize'     => esc_html__('Customize', 'bookingpress-appointment-booking'),
                'bookingpress_settings'      => esc_html__('Settings', 'bookingpress-appointment-booking'),
                'bookingpress_payment_settings' => esc_html__( 'Payment Settings', 'bookingpress-appointment-booking' ),
                'bookingpress_addons'        => esc_html__('Add ons', 'bookingpress-appointment-booking'),
                'bookingpress_growth_tools'  => esc_html__('Growth Plugins', 'bookingpress-appointment-booking'),
            );
            return $cap;
        }

        function bookingpress_prevent_rocket_loader_script( $tag, $handle )
        {
            $script   = htmlspecialchars($tag);
            $pattern2 = '/\/(wp\-content\/plugins\/bookingpress)|(wp\-includes\/js)/';
            preg_match($pattern2, $script, $match_script);

            /* Check if current script is loaded from bookingpress only */
            if (! isset($match_script[0]) || $match_script[0] == '' ) {
                return $tag;
            }

            $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
            preg_match_all($pattern, $tag, $matches);
            if (! is_array($matches) ) {
                return str_replace(' src', ' data-cfasync="false" src', $tag);
            } elseif (! empty($matches) && ! empty($matches[2]) && ! empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=' ) {
                return str_replace(' src', ' data-cfasync="false" src', $tag);
            } elseif (! empty($matches) && empty($matches[2]) ) {
                return str_replace(' src', ' data-cfasync="false" src', $tag);
            } else {
                return $tag;
            }
        }

        function bookingpress_prevent_rocket_loader_script_clf_advanced( $tag, $handle ){
			
			$script = htmlspecialchars($tag);

			$regex = '/(.*?)(<script(\s)id=(\'|\")bookingpress(.*)\-(after|before)(\'|\"))\>(.*?)/';

            $handle_arr = ['wcap_vue_js'];

            $handle_arr = apply_filters( 'bookingpress_skip_loader_tags', $handle_arr, $tag, $handle );

			if( preg_match( '/bookingpress/', $handle ) || preg_match( '/bookingpress/', $script ) || preg_match('/id=&#039;bookingpress/', $script) || in_array( $handle, $handle_arr ) ){
                if( preg_match( '/\=(\'|")/', $tag, $matches_ ) ){
                    if( !empty( $matches_[1] ) ){
                        $tag = str_replace( " src=", " data-cfasync=". $matches_[1]."false".$matches_[1]." src=", $tag );
                    } else {
                        $tag = str_replace(' src=', ' data-cfasync="false" src=', $tag);
                    }
                } else {
                    $tag = str_replace(' src=', ' data-cfasync="false" src=', $tag);
                }
			}

			if( preg_match( $regex, $tag, $matches ) ){
				$replaced = preg_replace( $regex, '$1<script$3id=$4bookingpress$5-$6$7 data-cfasync=$4false$7>$8', $tag );
                if( null != $replaced ){
                    $tag = $replaced;
                }
			}

            if( preg_match( '/(<img data\-cfasync\=\"false\")/', $tag ) ){
				$tag = preg_replace( '/(<img data\-cfasync\=\"false\")/', '<img ', $tag );
			}

			return $tag;
		}

        function bookingpress_prevent_rocket_loader_script_clf( $tag, $handle )
        {
            $script   = htmlspecialchars($tag);
            $pattern2 = '/\/(wp\-content\/plugins\/bookingpress)|(wp\-includes\/js)/';
            preg_match($pattern2, $script, $match_script);

            /* Check if current script is loaded from bookingpress only */
            if (! isset($match_script[0]) || $match_script[0] == '' ) {
                return $tag;
            }

            $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
            preg_match_all($pattern, $tag, $matches);

            $pattern3 = '/type\=(\'|")[a-zA-Z0-9]+\-(text\/javascript)(\'|")/';
            preg_match_all($pattern3, $tag, $match_tag);

            if (! isset($match_tag[0]) || empty($match_tag[0]) ) {
                return $tag;
            }

            if (! is_array($matches) ) {
                return str_replace(' src', ' data-cfasync="false" src', $tag);
            } elseif (! empty($matches) && ! empty($matches[2]) && ! empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=' ) {
                return str_replace(' src', ' data-cfasync="false" src', $tag);
            } elseif (! empty($matches) && empty($matches[2]) ) {
                return str_replace(' src', ' data-cfasync="false" src', $tag);
            } else {
                return $tag;
            }
        }

        /**
         * Restrict Network Activation
         */
        public static function bookingpress_check_network_activation( $network_wide )
        {
            if (! $network_wide ) {
                return;
            }

            deactivate_plugins(plugin_basename(BOOKINGPRESS_DIR . '/bookingpress.php'), true, true);

            header('Location: ' . network_admin_url('plugins.php?deactivate=true'));
            exit;
        }

        public static function install()
        {
            global $wpdb,$BookingPress, $bookingpress_version;

            $_version = get_option('bookingpress_version');
            if (empty($_version) || $_version == '' ) {
                $bookingpress_custom_css_key = uniqid();
                update_option('bookingpress_custom_css_key', $bookingpress_custom_css_key);

                include_once ABSPATH . 'wp-admin/includes/upgrade.php';
                @set_time_limit(0);
                global $wpdb, $bookingpress_version;

                $charset_collate = '';
                if ($wpdb->has_cap('collation') ) {
                    if (! empty($wpdb->charset) ) {
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                    }
                    if (! empty($wpdb->collate) ) {
                        $charset_collate .= " COLLATE $wpdb->collate";
                    }
                }

                update_option('bookingpress_version', $bookingpress_version);
                update_option('bookingpress_plugin_activated', 1);
                update_option('bookingpress_timezone', wp_timezone_string() );

                $bookingpress_pro_version = get_option('bookingpress_pro_version'); 
                if($bookingpress_pro_version != ''){
                    update_option('bookingpress_pro_exist_on_activate_lite_'.$bookingpress_version,$bookingpress_pro_version);
                }

                $bookingpress_dbtbl_create = array();
                /* Table structure for `Members activity` */
                global $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_customers, $tbl_bookingpress_settings, $tbl_bookingpress_default_workhours, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_notifications, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_entries, $tbl_bookingpress_form_fields, $tbl_bookingpress_customize_settings,$tbl_bookingpress_debug_payment_log, $tbl_bookingpress_customers_meta, $tbl_bookingpress_other_debug_logs,$tbl_bookingpress_entries_meta, $tbl_bookingpress_double_bookings,$tbl_bookingpress_transient,$tbl_bookingpress_export_data_log,$tbl_bookingpress_export_data_log_detail,$tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log,$tbl_bookingpress_import_record_rel,$tbl_bookingpress_import_images;

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_categories}`(
					`bookingpress_category_id` SMALLINT NOT NULL AUTO_INCREMENT,
					`bookingpress_category_name` VARCHAR(255) NOT NULL,
					`bookingpress_category_position` SMALLINT NOT NULL,
					`bookingpress_categorydate_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_category_id`)
				) {$charset_collate};";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_categories ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_services}`(
					`bookingpress_service_id` INT(11) NOT NULL AUTO_INCREMENT, 
					`bookingpress_category_id` SMALLINT DEFAULT 0,
					`bookingpress_service_name` VARCHAR(255) NOT NULL,
					`bookingpress_service_price` DOUBLE NOT NULL,
					`bookingpress_service_duration_val` INT(11) NOT NULL,
					`bookingpress_service_duration_unit` VARCHAR(1) NOT NULL,
					`bookingpress_service_description` TEXT NOT NULL,
					`bookingpress_service_position` INT(11) NOT NULL,
					`bookingpress_servicedate_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_service_id`)
				) {$charset_collate};";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_services ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_servicesmeta}`(
					`bookingpress_servicemeta_id` INT(11) NOT NULL AUTO_INCREMENT, 
					`bookingpress_service_id` INT(11) NOT NULL,					
					`bookingpress_servicemeta_name` VARCHAR(255) NOT NULL,
					`bookingpress_servicemeta_value` TEXT NOT NULL,
					`bookingpress_servicemetadate_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_servicemeta_id`)
				) {$charset_collate};";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_servicesmeta ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_customers}`(
					`bookingpress_customer_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_wpuser_id` bigint(11) DEFAULT NULL,
					`bookingpress_user_login` VARCHAR(60) NOT NULL DEFAULT '',
					`bookingpress_user_status` INT(1) NOT NULL,
					`bookingpress_user_type` INT(1) DEFAULT 0,
                    `bookingpress_user_name` VARCHAR(255) NOT NULL,
					`bookingpress_user_firstname` VARCHAR(255) NOT NULL,
					`bookingpress_user_lastname` VARCHAR(255) NOT NULL,
					`bookingpress_customer_full_name` VARCHAR(255) NOT NULL,
					`bookingpress_user_email` VARCHAR(255) NOT NULL,
					`bookingpress_user_phone` VARCHAR(63) DEFAULT NULL,
					`bookingpress_user_country_phone` VARCHAR(60) DEFAULT NULL,
                    `bookingpress_user_country_dial_code` VARCHAR(5) DEFAULT NULL,
                    `bookingpress_user_timezone` VARCHAR(50) DEFAULT NULL,
					`bookingpress_created_at` INT(1) NOT NULL DEFAULT 0,
					`bookingpress_created_by` INT(11) NOT NULL DEFAULT 0,
					`bookingpress_user_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_customer_id`)
				) {$charset_collate};";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_customers ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_settings}`(
					`setting_id` int(11) NOT NULL AUTO_INCREMENT,
					`setting_name` varchar(255) NOT NULL,
					`setting_value` TEXT DEFAULT NULL,
					`setting_type` varchar(255) DEFAULT NULL,
					`updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`setting_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_settings ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_default_workhours}`(
					`bookingpress_workhours_id` smallint NOT NULL AUTO_INCREMENT,
					`bookingpress_workday_key` varchar(11) NOT NULL,
					`bookingpress_start_time` time DEFAULT NULL,
					`bookingpress_end_time` time DEFAULT NULL,
					`bookingpress_is_break` TINYINT(1) DEFAULT 0,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_workhours_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_default_workhours ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_default_daysoff}`(
					`bookingpress_dayoff_id` smallint NOT NULL AUTO_INCREMENT,
					`bookingpress_name` varchar(255) NOT NULL,
					`bookingpress_dayoff_date` datetime DEFAULT NULL,
                    			`bookingpress_dayoff_enddate` date DEFAULT NULL,
                    			`bookingpress_dayoff_parent` int(11) NOT NULL DEFAULT 0,
					`bookingpress_repeat` TINYINT(1) DEFAULT 0,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_dayoff_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_default_daysoff ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_notifications}`(
					`bookingpress_notification_id` smallint NOT NULL AUTO_INCREMENT,
					`bookingpress_notification_receiver_type` varchar(11) DEFAULT 'customer',
					`bookingpress_notification_is_custom` TINYINT(1) DEFAULT 0,
					`bookingpress_notification_name` varchar(255) NOT NULL,
					`bookingpress_notification_execution_type` varchar(11) DEFAULT 'action',
					`bookingpress_notification_status` TINYINT(1) DEFAULT 0,
					`bookingpress_notification_type` varchar(255) DEFAULT 'appointment',
					`bookingpress_notification_appointment_status` varchar(255) DEFAULT 'approved',
					`bookingpress_notification_event_action` varchar(255) DEFAULT 'booked',
					`bookingpress_notification_send_only_this` TINYINT(1) DEFAULT 0,
					`bookingpress_notification_subject` TEXT DEFAULT NULL,
					`bookingpress_notification_message` TEXT DEFAULT NULL,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					`bookingpress_updated_at` datetime DEFAULT '1970-01-01 00:00:00',
					PRIMARY KEY (`bookingpress_notification_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_notifications ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_appointment_bookings}`(
					`bookingpress_appointment_booking_id` bigint(11) NOT NULL AUTO_INCREMENT,
                    `bookingpress_booking_id` varchar(255) DEFAULT NULL,
					`bookingpress_entry_id` bigint(11) DEFAULT NULL,
                    `bookingpress_payment_id` bigint(11) DEFAULT 0,
					`bookingpress_customer_id` bigint(11) NOT NULL,
                    `bookingpress_customer_name` varchar(255) DEFAULT NULL,
                    `bookingpress_username` varchar(255) DEFAULT NULL,
                    `bookingpress_customer_phone` varchar(255) DEFAULT NULL,
                    `bookingpress_customer_firstname` varchar(255) DEFAULT NULL,
                    `bookingpress_customer_lastname` varchar(255) DEFAULT NULL,
                    `bookingpress_customer_country` VARCHAR(60) DEFAULT NULL,
                    `bookingpress_customer_phone_dial_code` VARCHAR(5) DEFAULT NULL,
                    `bookingpress_customer_email` varchar(255) DEFAULT NULL,
					`bookingpress_staff_member_id` smallint DEFAULT NULL,
					`bookingpress_service_id` INT(11) NOT NULL,
					`bookingpress_service_name` varchar(255) NOT NULL,
					`bookingpress_service_price` float NOT NULL,
					`bookingpress_service_currency` varchar(100) NOT NULL,
					`bookingpress_service_duration_val` INT(11) NOT NULL,
					`bookingpress_service_duration_unit` VARCHAR(1) NOT NULL,
					`bookingpress_appointment_date` DATE NOT NULL,
					`bookingpress_appointment_end_date` DATE NOT NULL,
					`bookingpress_appointment_time` TIME NOT NULL,
                    `bookingpress_appointment_end_time` TIME NOT NULL,
					`bookingpress_appointment_internal_note` TEXT DEFAULT NULL,
					`bookingpress_appointment_send_notification` TINYINT(1) DEFAULT 0,
					`bookingpress_appointment_status` smallint(1) DEFAULT 1,	
                    `bookingpress_paid_amount` float DEFAULT 0,
                    `bookingpress_due_amount` float DEFAULT 0,
                    `bookingpress_appointment_timezone` varchar(50) DEFAULT NULL,
                    `bookingpress_selected_appointment_date` DATE NOT NULL,
                    `bookingpress_selected_appointment_end_date` DATE NOT NULL,
                    `bookingpress_selected_appointment_time` TIME NOT NULL,
                    `bookingpress_selected_appointment_end_time` TIME NOT NULL,
                    `bookingpress_appointment_token` varchar(50) DEFAULT NULL,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_appointment_booking_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_appointment_bookings ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_payment_logs}`(
					`bookingpress_payment_log_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_invoice_id` varchar(50) DEFAULT 0,
					`bookingpress_appointment_booking_ref` bigint(11) DEFAULT NULL,
					`bookingpress_customer_id` bigint(11) NOT NULL,
                    `bookingpress_customer_name` varchar(255) DEFAULT NULL,
                    `bookingpress_username` varchar(255) DEFAULT NULL,
                    `bookingpress_customer_phone` varchar(255) DEFAULT NULL,
					`bookingpress_customer_firstname` varchar(255) DEFAULT NULL,
					`bookingpress_customer_lastname` varchar(255) DEFAULT NULL,
                    `bookingpress_customer_country` VARCHAR(60) DEFAULT NULL,
                    `bookingpress_customer_phone_dial_code` VARCHAR(5) DEFAULT NULL,
					`bookingpress_customer_email` varchar(255) DEFAULT NULL,
					`bookingpress_staff_member_id` smallint DEFAULT NULL,
					`bookingpress_service_id` INT(11) NOT NULL,
					`bookingpress_service_name` varchar(255) NOT NULL,
					`bookingpress_service_price` float NOT NULL,
					`bookingpress_service_duration_val` INT(11) NOT NULL,
					`bookingpress_service_duration_unit` VARCHAR(1) NOT NULL,
					`bookingpress_appointment_date` DATE NOT NULL,
					`bookingpress_appointment_end_date` DATE NOT NULL,
					`bookingpress_appointment_start_time` TIME NOT NULL,
					`bookingpress_appointment_end_time` TIME NOT NULL,
					`bookingpress_payment_gateway` varchar(255) DEFAULT NULL,
					`bookingpress_payer_email` varchar(255) DEFAULT NULL,
					`bookingpress_transaction_id` varchar(255) DEFAULT NULL,
					`bookingpress_payment_date_time` datetime DEFAULT '1970-01-01 00:00:00',
					`bookingpress_payment_status` smallint(1) DEFAULT 1,
					`bookingpress_payment_amount` FLOAT(8, 2) DEFAULT 0,
					`bookingpress_payment_currency` varchar(100) DEFAULT NULL,
					`bookingpress_payment_type` varchar(20) DEFAULT NULL,
					`bookingpress_payment_response` TEXT DEFAULT NULL,
					`bookingpress_additional_info` TEXT DEFAULT NULL,
                    `bookingpress_paid_amount` float DEFAULT 0,
                    `bookingpress_due_amount` float DEFAULT 0,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_payment_log_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_payment_logs ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_entries}`(
					`bookingpress_entry_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_customer_id` bigint(11) DEFAULT NULL,
					`bookingpress_customer_name` varchar(255) DEFAULT NULL,
					`bookingpress_username` varchar(255) DEFAULT NULL,
					`bookingpress_customer_phone` varchar(255) DEFAULT NULL,
					`bookingpress_customer_firstname` varchar(255) DEFAULT NULL,
					`bookingpress_customer_lastname` varchar(255) DEFAULT NULL,
					`bookingpress_customer_country` VARCHAR(60) DEFAULT NULL,
                    `bookingpress_customer_phone_dial_code` VARCHAR(5) DEFAULT NULL,
					`bookingpress_customer_email` varchar(255) DEFAULT NULL,
                    `bookingpress_customer_timezone` varchar(50) DEFAULT NULL,
					`bookingpress_service_id` INT(11) NOT NULL,
					`bookingpress_service_name` varchar(255) NOT NULL,
					`bookingpress_service_price` float NOT NULL,
					`bookingpress_service_currency` varchar(100) NOT NULL,
					`bookingpress_service_duration_val` INT(11) NOT NULL,
					`bookingpress_service_duration_unit` VARCHAR(1) NOT NULL,
					`bookingpress_payment_gateway` VARCHAR(255) DEFAULT NULL,
					`bookingpress_appointment_date` DATE NOT NULL,
                    `bookingpress_appointment_end_date` DATE NOT NULL,
					`bookingpress_appointment_time` TIME NOT NULL,
                    `bookingpress_appointment_end_time` TIME NOT NULL,
					`bookingpress_appointment_internal_note` TEXT DEFAULT NULL,
					`bookingpress_appointment_send_notifications` TINYINT(1) DEFAULT 0,
					`bookingpress_appointment_status` varchar(20) NOT NULL,
                    `bookingpress_paid_amount` float DEFAULT 0,
                    `bookingpress_due_amount` float DEFAULT 0,
                    `bookingpress_selected_appointment_date` DATE NOT NULL,
                    `bookingpress_selected_appointment_end_date` DATE NOT NULL,
                    `bookingpress_selected_appointment_time` TIME NOT NULL,
                    `bookingpress_selected_appointment_end_time` TIME NOT NULL,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_entry_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_entries ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_form_fields}`(
					`bookingpress_form_field_id` SMALLINT NOT NULL AUTO_INCREMENT,
					`bookingpress_form_field_name` varchar(255) NOT NULL,
					`bookingpress_field_required` TINYINT(1) DEFAULT 0,
					`bookingpress_field_label` TEXT NOT NULL,
					`bookingpress_field_placeholder` TEXT DEFAULT NULL,
					`bookingpress_field_error_message` VARCHAR(255) DEFAULT NULL,
					`bookingpress_field_is_hide` TINYINT(1) DEFAULT 0,
					`bookingpress_field_position` FLOAT DEFAULT 0,
                    `bookingpress_field_is_default` tinyint(1) DEFAULT 0,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_form_field_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_form_fields ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_customize_settings}`(
					`bookingpress_setting_id` int(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_setting_name` varchar(255) NOT NULL,
					`bookingpress_setting_value` TEXT NOT NULL,
					`bookingpress_setting_type` varchar(255) NOT NULL,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_setting_id`)
				) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_customize_settings ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_debug_payment_log}`(
					`bookingpress_payment_log_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_payment_log_ref_id` bigint(11) NOT NULL DEFAULT '0',
					`bookingpress_payment_log_gateway` varchar(255) DEFAULT NULL,
					`bookingpress_payment_log_event` varchar(255) DEFAULT NULL,
					`bookingpress_payment_log_event_from` varchar(255) DEFAULT NULL,
					`bookingpress_payment_log_status` TINYINT(1) DEFAULT '1',
					`bookingpress_payment_log_raw_data` TEXT,
					`bookingpress_payment_log_added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_payment_log_id`)
				) {$charset_collate};";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_debug_payment_log ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_customers_meta}`(
					`bookingpress_customermeta_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_customer_id` bigint(11) NOT NULL,
					`bookingpress_customersmeta_key` varchar(255) NOT NULL,
					`bookingpress_customersmeta_value` TEXT DEFAULT NULL,
					`bookingpress_customersmeta_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_customermeta_id`)
				) {$charset_collate};";

                $bookingpress_dbtbl_create[ $tbl_bookingpress_customers_meta ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_other_debug_logs}`(
			        `bookingpress_other_log_id` int(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_other_log_ref_id` int(11) NOT NULL,
					`bookingpress_other_log_type` varchar(255) DEFAULT NULL,
					`bookingpress_other_log_event` varchar(255) DEFAULT NULL,
					`bookingpress_other_log_event_from` varchar(255) DEFAULT NULL,
					`bookingpress_other_log_raw_data` TEXT DEFAULT NULL,		
			        `bookingpress_other_log_added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			        PRIMARY KEY (`bookingpress_other_log_id`)
			    ) {$charset_collate};";

                $bookingpress_dbtbl_create[ $tbl_bookingpress_other_debug_logs ] = dbDelta($sql_table);

                
                
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_entries_meta}`(
                    `bookingpress_entry_meta_id` int(11) NOT NULL AUTO_INCREMENT,
                    `bookingpress_entry_id` int(11) NOT NULL,
                    `bookingpress_entry_meta_key` TEXT NOT NULL,
                    `bookingpress_entry_meta_value` TEXT DEFAULT NULL,
                    `bookingpress_entrymeta_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`bookingpress_entry_meta_id`)
                ) {$charset_collate};";                
                $bookingpress_dbtbl_create[ $tbl_bookingpress_entries_meta ] = dbDelta($sql_table);

                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_double_bookings}`(
                    `bookingpress_double_booking_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `bookingpress_entry_id` INT(11) NOT NULL,
                    `bookingpress_double_booking_reason` LONGTEXT NOT NULL,
                    `bookingpress_payment_gateway` VARCHAR(255) NOT NULL,
                    `bookingpress_staffmember_id` INT(11) DEFAULT 0,
                    `bookingpress_payer_email` varchar(255) DEFAULT NULL,
					`bookingpress_transaction_id` varchar(255) DEFAULT NULL,
					`bookingpress_payment_date_time` datetime DEFAULT '1970-01-01 00:00:00',
					`bookingpress_payment_status` smallint(1) DEFAULT 1,
					`bookingpress_payment_amount` FLOAT(8, 2) DEFAULT 0,
					`bookingpress_payment_currency` varchar(100) DEFAULT NULL,
					`bookingpress_payment_type` varchar(20) DEFAULT NULL,
					`bookingpress_payment_response` TEXT DEFAULT NULL,
					`bookingpress_additional_info` TEXT DEFAULT NULL,
                    `bookingpress_request_raw_data` LONGTEXT NOT NULL,
                    `bookingpress_is_refund_supported` TINYINT NOT NULL DEFAULT '0',
                    `bookingpress_refund_response` TEXT NOT NULL,
                    `bookingpress_refund_reason` LONGTEXT NOT NULL,
                    `bookingpress_is_refunded` TINYINT NOT NULL DEFAULT '0',
                    `bookingpress_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY ( `bookingpress_double_booking_id` )
                ) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_double_bookings ] = dbDelta( $sql_table );
                
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_transient}` (
                    `bookingpress_transient_id` bigint(11) NOT NULL AUTO_INCREMENT,
                    `bookingpress_transient_key` VARCHAR(100) NOT NULL,
                    `bookingpress_transient_value` LONGTEXT NOT NULL,
                    `bookingpress_transient_expiry` VARCHAR(50) NULL DEFAULT NULL,
                    `bookingpress_transient_created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`bookingpress_transient_id`)
                ) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_transient ] = dbDelta( $sql_table );

                
                
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_export_data_log}`(
                    `export_id` int(11) NOT NULL AUTO_INCREMENT,					
                    `export_data` TEXT DEFAULT NULL,
                    `export_complete` int(11) DEFAULT 0,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`export_id`)
                ) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_export_data_log ] = dbDelta($sql_table);
            
                
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_export_data_log_detail}`(
                    `export_detail_id` int(11) NOT NULL AUTO_INCREMENT,
                    `export_id` int(11) NOT NULL,
                    `export_detail_type` varchar(255) NOT NULL,
                    `export_detail_total_record` int(11) NOT NULL DEFAULT 0,
                    `export_detail_last_record` int(11) NOT NULL DEFAULT 0,
                    `export_detail_complete` int(11) DEFAULT 0,
                    `export_detail_option` varchar(255) DEFAULT NULL,
                    `export_detail_record_hide` int(11) DEFAULT 0,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`export_detail_id`)
                ) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_export_data_log_detail ] = dbDelta($sql_table);
            
            
                $tbl_bookingpress_import_data_log = $wpdb->prefix.'bookingpress_import_data_log';
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_import_data_log}`(
                    `import_id` int(11) NOT NULL AUTO_INCREMENT,					
                    `import_data` TEXT DEFAULT NULL,
                    `import_complete` int(11) DEFAULT 0,
                    `export_key` varchar(255) DEFAULT NULL,
                    `export_site_key` varchar(255) DEFAULT NULL,
                    `site_url` varchar(255) DEFAULT NULL,
                    `import_stop_reason` text DEFAULT NULL,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`import_id`)
                ) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_import_data_log ] = dbDelta($sql_table);
            
                $tbl_bookingpress_import_detail_log = $wpdb->prefix.'bookingpress_import_detail_log';
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_import_detail_log}`(
                    `detail_import_id` int(11) NOT NULL AUTO_INCREMENT,					
                    `import_id` int(11) NOT NULL,
                    `detail_import_detail_type` varchar(255) NOT NULL,
                    `detail_import_complete` int(11) DEFAULT 0,
                    `detail_import_total_record` int(11) NOT NULL DEFAULT 0,
                    `detail_import_last_record` int(11) NOT NULL DEFAULT 0,
                    `detail_import_display` int(1) NOT NULL DEFAULT 0,
                    `export_site_key` varchar(255) DEFAULT NULL,
                    `export_key` varchar(255) DEFAULT NULL,
                    `detail_import_stop_reason` text DEFAULT NULL,
                    `total_import_record` int(11) NOT NULL DEFAULT 0,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`detail_import_id`)
                ) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_import_detail_log ] = dbDelta($sql_table); 
                
                $tbl_bookingpress_import_record_rel = $wpdb->prefix.'bookingpress_import_record_rel';
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_import_record_rel}`(
                    `record_rel_id` int(11) NOT NULL AUTO_INCREMENT,					
                    `import_id` int(11) NOT NULL,
                    `record_rel_type` varchar(255) NOT NULL,
                    `record_old_id` int(11) DEFAULT 0,
                    `record_new_id` int(11) DEFAULT 0,                    
                    `record_rel_lang_type` varchar(255) DEFAULT NULL,
                    `export_key` varchar(255) DEFAULT NULL,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`record_rel_id`)
                ) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_import_record_rel ] = dbDelta($sql_table);                 
            
                $tbl_bookingpress_import_images = $wpdb->prefix.'bookingpress_import_images';
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_import_images}`(
                    `import_image_id` int(11) NOT NULL AUTO_INCREMENT,					
                    `import_id` int(11) NOT NULL,
                    `import_table` varchar(255) NOT NULL,
                    `import_field` varchar(255) DEFAULT NULL,
                    `import_table_id` int(11) DEFAULT 0,
                    `import_image_name` varchar(255) DEFAULT NULL,
                    `import_image_url` varchar(255) DEFAULT NULL,
                    `import_image_complete` int(1) DEFAULT 0,
                    `export_key` varchar(255) DEFAULT NULL,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`import_image_id`)
                ) {$charset_collate}";
                $bookingpress_dbtbl_create[ $tbl_bookingpress_import_images ] = dbDelta($sql_table);

                //$BookingPress->bookingpress_update_settings('paypal_payment_method_type', 'payment_setting', 'lagacy');

                $BookingPress->bookingpress_add_user_role_and_capabilities();

                $BookingPress->bookingpress_install_default_notification_data();

                $BookingPress->bookingpress_install_default_general_settings_data();

                $BookingPress->bookingpress_install_default_pages();

                $BookingPress->bookingpress_install_default_customize_settings_data();

                /* Plugin Action Hook After Install Process */
                do_action('bookingpress_after_activation_hook');
                do_action('bookingpress_after_install');

                add_option('bookingpress_install_date',current_time('mysql'));


                $check_db_permission = $BookingPress->bookingpress_check_db_permission();
                if($check_db_permission)
                {
                    //appointment booking table index
                    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD INDEX `bookingpress_appointment_status-appointment_date` (`bookingpress_appointment_status`, `bookingpress_appointment_date`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD INDEX `bookingpress_service_id-appointment_status-appointment_date` (`bookingpress_service_id`, `bookingpress_appointment_status`, `bookingpress_appointment_date`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    
                    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD INDEX `bookingpress_appointment_date-appointment_time-appointment_end_t` (`bookingpress_appointment_date`, `bookingpress_appointment_time`, `bookingpress_appointment_end_time`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                    //customer table index
                    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_customers}` ADD INDEX `bookingpress_user_type-user_status` (`bookingpress_user_type`, `bookingpress_user_status`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm

                    //customer meta table index
                    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_customers_meta}` ADD INDEX `bookingpress_customer_id-customersmeta_key` (`bookingpress_customer_id`, `bookingpress_customersmeta_key`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers_meta is table name defined globally. False Positive alarm

                    //payment transaction table index
                    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_payment_logs}` ADD INDEX `bookingpress_payment_date_time-payment_status` (`bookingpress_payment_date_time`, `bookingpress_payment_status`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

                    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_payment_logs}` ADD INDEX `bookingpress_service_id-appointment_date-start_time-end_time` (`bookingpress_service_id`, `bookingpress_appointment_date`, `bookingpress_appointment_start_time`, `bookingpress_appointment_end_time`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

                    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_transient}` ADD INDEX `bookingpress_transient_id_key_index` (`bookingpress_transient_id`,`bookingpress_transient_key`)"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_transient is table name defined globally. False Positive alarm
                }

            } else {
                do_action('bookingpress_reactivate_plugin');
            }

            $args  = array(
                'role'   => 'administrator',
                'fields' => 'id',
            );
            $users = get_users($args);

            if (count($users) > 0 ) {
                foreach ( $users as $key => $user_id ) {
                    $bookingpressroles = $BookingPress->bookingpress_capabilities();
                    $userObj           = new WP_User($user_id);
                    foreach ( $bookingpressroles as $bookingpressrole => $bookingpress_roledescription ) {
                        $userObj->add_cap($bookingpressrole);
                    }
                    unset($bookingpressrole);
                    unset($bookingpressroles);
                    unset($bookingpress_roledescription);
                }
            }
        }

        function bookingpress_check_db_permission()
		{
			global $wpdb;
            $results = $wpdb->get_results("SHOW GRANTS FOR CURRENT_USER;");
            $allowed_index = 0;
            foreach($results as $result)
            {
                if(is_object($result))
                {
                    foreach($result as $res)
                    {
                        $result_data = stripslashes_deep($res);
                    }
                }
                else {
                    $result_data = stripslashes_deep($result);
                }
                if( (strpos($result_data, "ALL PRIVILEGES") !== false || strpos($result_data, "INDEX") !== false) && (strpos($result_data, "ON *.*") || strpos($result_data, "`".DB_NAME."`") ) )
                {
                    $allowed_index = 1;
                    break;
                }
            }
            return $allowed_index;
		}

        public static function uninstall()
        {
            global $wp, $wpdb, $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta, $tbl_bookingpress_settings, $tbl_bookingpress_default_workhours, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_notifications,$tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_entries, $tbl_bookingpress_form_fields, $tbl_bookingpress_customize_settings, $tbl_bookingpress_debug_payment_log,$bookingpress_capabilities_global,$BookingPress,$tbl_bookingpress_other_debug_logs, $tbl_bookingpress_double_bookings, $tbl_bookingpress_entries_meta, $tbl_bookingpress_transient, $tbl_bookingpress_export_data_log, $tbl_bookingpress_export_data_log_detail, $tbl_bookingpress_import_data_log, $tbl_bookingpress_import_detail_log, $tbl_bookingpress_import_images, $tbl_bookingpress_import_record_rel;
            /**
             * Delete Meta Values
             */
            //set flag when lite uninstall and pro already exist.
            $bookingpress_version = get_option('bookingpress_version'); 
            $bookingpress_pro_version = get_option('bookingpress_pro_version'); 
            if($bookingpress_pro_version != ''){
                update_option('bookingpress_pro_exist_on_uninstall_lite_'.$bookingpress_version,$bookingpress_pro_version);
            }

            $wpdb->query('DELETE FROM `' . $wpdb->options . "` WHERE  `option_name` LIKE  '%bookingpress\_%' AND `option_name` NOT LIKE  '%bookingpress_pro\_%'");
            /**
             * Delete Plugin DB Tables
             */
            $bookingpress_tables = array(
                $tbl_bookingpress_categories,
                $tbl_bookingpress_services,
                $tbl_bookingpress_servicesmeta,
                $tbl_bookingpress_customers,
                $tbl_bookingpress_customers_meta,
                $tbl_bookingpress_settings,
                $tbl_bookingpress_default_workhours,
                $tbl_bookingpress_default_daysoff,
                $tbl_bookingpress_notifications,
                $tbl_bookingpress_appointment_bookings,
                $tbl_bookingpress_payment_logs,
                $tbl_bookingpress_entries,
                $tbl_bookingpress_form_fields,
                $tbl_bookingpress_customize_settings,
                $tbl_bookingpress_debug_payment_log,
                $tbl_bookingpress_other_debug_logs,
                $tbl_bookingpress_entries_meta,
                $tbl_bookingpress_double_bookings,
                $tbl_bookingpress_transient,
                $tbl_bookingpress_export_data_log,
                $tbl_bookingpress_export_data_log_detail,
                $tbl_bookingpress_import_data_log,
                $tbl_bookingpress_import_detail_log,
                $tbl_bookingpress_import_images,
                $tbl_bookingpress_import_record_rel
            );
            foreach ( $bookingpress_tables as $table ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $table is table name defined globally. False Positive alarm
                $wpdb->query("DROP TABLE IF EXISTS $table ");
            }

            //Remove all capabilities assigned to administrator
            $args  = array(
                'role'   => 'administrator',
                'fields' => 'id',
            );
            $users = get_users($args);
            if (count($users) > 0 ) {
                foreach ( $users as $key => $user_id ) {
                    $bookingpressroles = $BookingPress->bookingpress_capabilities();
                    $userObj           = new WP_User($user_id);
                    foreach ( $bookingpressroles as $bookingpressrole => $bookingpress_roledescription ) {
                        if($userObj->has_cap($bookingpressrole)){
                            $userObj->remove_cap($bookingpressrole, true);
                        }
                    }
                }
            }

            $args  = array(
                'role'   => 'bookingpress-customer',
                'fields' => 'id',
            );
            $users = get_users($args);
            if (count($users) > 0 ) {
                foreach ( $users as $key => $user_id ) {
                    $bookingpressroles = $BookingPress->bookingpress_capabilities();
                    $userObj           = new WP_User($user_id);
                    foreach ( $bookingpressroles as $bookingpressrole => $bookingpress_roledescription ) {
                        $userObj->remove_role('bookingpress-customer');
                        if($userObj->has_cap($bookingpressrole)){
                            $userObj->remove_cap($bookingpressrole, true);
                        }
                    }
                }
            }

            // remove capabilities
            $customer = get_role('bookingpress-customer');
            if(!empty($customer)){
                $customer->remove_cap('bookingpress-customer', true);
            }

            // remove role
            $wp_roles = new WP_Roles();
            $wp_roles->remove_role('bookingpress-customer');
            /* Plugin Action Hook After Uninstall Process */
            do_action('bookingpress_after_uninstall');
        }
        
        /**
         * Register admin menu
         *
         * @return void
         */
        function bookingpress_menu()
        {
            global $bookingpress_slugs;

            $place = $this->get_free_menu_position(26.1, 0.3);

            $bookingpress_is_wizard_complete = get_option('bookingpress_lite_wizard_complete');

            if(empty($bookingpress_is_wizard_complete) || $bookingpress_is_wizard_complete == 0){
                $bookingpress_menu_hook = add_menu_page(esc_html__('BookingPress', 'bookingpress-appointment-booking'), esc_html__('BookingPress', 'bookingpress-appointment-booking'), 'bookingpress', $bookingpress_slugs->bookingpress_lite_wizard, array( $this, 'route' ), BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png', $place);
            }else{
                $bookingpress_menu_hook = add_menu_page(esc_html__('BookingPress', 'bookingpress-appointment-booking'), esc_html__('BookingPress', 'bookingpress-appointment-booking'), 'bookingpress', $bookingpress_slugs->bookingpress, array( $this, 'route' ), BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png', $place);
            }

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Dashboard', 'bookingpress-appointment-booking'), esc_html__('Dashboard', 'bookingpress-appointment-booking'), 'bookingpress', $bookingpress_slugs->bookingpress);

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Calendar', 'bookingpress-appointment-booking'), esc_html__('Calendar', 'bookingpress-appointment-booking'), 'bookingpress_calendar', $bookingpress_slugs->bookingpress_calendar, array( $this, 'route' ));

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Appointments', 'bookingpress-appointment-booking'), esc_html__('Appointments', 'bookingpress-appointment-booking'), 'bookingpress_appointments', $bookingpress_slugs->bookingpress_appointments, array( $this, 'route' ));

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Payments', 'bookingpress-appointment-booking'), esc_html__('Payments', 'bookingpress-appointment-booking'), 'bookingpress_payments', $bookingpress_slugs->bookingpress_payments, array( $this, 'route' ));

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Customers', 'bookingpress-appointment-booking'), esc_html__('Customers', 'bookingpress-appointment-booking'), 'bookingpress_customers', $bookingpress_slugs->bookingpress_customers, array( $this, 'route' ));

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Services', 'bookingpress-appointment-booking'), esc_html__('Services', 'bookingpress-appointment-booking'), 'bookingpress_services', $bookingpress_slugs->bookingpress_services, array( $this, 'route' ));

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Notifications', 'bookingpress-appointment-booking'), esc_html__('Notifications', 'bookingpress-appointment-booking'), 'bookingpress_notifications', $bookingpress_slugs->bookingpress_notifications, array( $this, 'route' ));

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Customize', 'bookingpress-appointment-booking'), esc_html__('Customize', 'bookingpress-appointment-booking'), 'bookingpress_customize', $bookingpress_slugs->bookingpress_customize, array( $this, 'route' ));

            add_submenu_page($bookingpress_slugs->bookingpress, esc_html__('Settings', 'bookingpress-appointment-booking'), esc_html__('Settings', 'bookingpress-appointment-booking'), 'bookingpress_settings', $bookingpress_slugs->bookingpress_settings, array( $this, 'route' ));
            
            if( !$this->bpa_is_pro_active() ){
                add_submenu_page( $bookingpress_slugs->bookingpress, esc_html__( 'Add-ons', 'bookingpress-appointment-booking' ), esc_html__( 'Add-ons', 'bookingpress-appointment-booking' ), 'bookingpress_addons', $bookingpress_slugs->bookingpress_addons, array( $this, 'route' ) );

                add_submenu_page( $bookingpress_slugs->bookingpress, esc_html__( 'Growth Plugins', 'bookingpress-appointment-booking' ), esc_html__( 'Growth Plugins', 'bookingpress-appointment-booking' ), 'bookingpress_growth_tools', $bookingpress_slugs->bookingpress_growth_tools, array( $this, 'route' ) );
            }

            $upgrade_menu_text = esc_html__( 'Upgrade to Pro', 'bookingpress-appointment-booking' );

            $bpa_current_date_for_bf_popup = current_time( 'timestamp', true );

            $bpa_sale_popup_details = $this->bookingpress_get_sales_data();

            $current_year = gmdate('Y', current_time('timestamp', true ) );


            if( !empty( $bpa_sale_popup_details[ $current_year ] ) ){
                $sale_details = $bpa_sale_popup_details[ $current_year ];
                
                $bpa_bf_popup_start_time = $sale_details['start_time'];
                $bpa_bf_popup_end_time = $sale_details['end_time'];

                $type = !empty( $sale_details['type'] ) ? $sale_details['type'] : 'black_friday';
                
                if( $bpa_current_date_for_bf_popup >= $bpa_bf_popup_start_time && $bpa_current_date_for_bf_popup <= $bpa_bf_popup_end_time ){
                    if( 'birthday_sale' == $type ){
                        $upgrade_menu_text = sprintf( esc_html__( 'Birthday Sale %1$s', 'bookingpress-appointment-booking' ), '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" style="position: absolute;width: auto;height: 16px;top: 50%;transform: translateY(-50%);margin-left: 5px;margin-right: 5px;"><script xmlns="" id="eppiocemhmnlbhjplcgkofciiegomcon"></script><script xmlns=""></script><script xmlns=""></script><ellipse fill="#8899A6" cx="18" cy="26" rx="18" ry="10"></ellipse><ellipse fill="#CCD6DD" cx="18" cy="24.25" rx="18" ry="10"></ellipse><path fill="#D98B48" d="M32.675 23.685c0 4.26-6.57 7.712-14.675 7.712S3.325 27.945 3.325 23.685c0-4.258 6.57-7.711 14.675-7.711 8.104 0 14.675 3.453 14.675 7.711z"></path><path fill="#FDE0AB" d="M32.233 22.543c0 9.854-28.466 9.854-28.466 0v-8.759h28.466v8.759z"></path><path fill="#D98B48" d="M17.984 18.166c-8.984 0-14.218-4.132-14.218-4.132s-.016.924-.016 1.685c0 0 .032 4.898 2.572 4.898 2.459 0 2.28 2.348 3.834 2.591 1.541.241 1.712-.938 3.625-.938s2.25 2.106 4.203 2.106c2.289 0 2.477-2.106 4.389-2.106s2.132 1.224 3.386.885c1.507-.408.814-2.537 3.887-2.537 2.54 0 2.603-4.648 2.603-4.648 0-.76-.017-1.935-.017-1.935s-5.263 4.131-14.248 4.131z"></path><path fill="#FDE0AB" d="M32.675 12.737c0 4.259-6.57 7.712-14.675 7.712S3.325 16.996 3.325 12.737 9.895 5.025 18 5.025c8.104 0 14.675 3.453 14.675 7.712z"></path><path fill="#53E8FA" d="M25.664 13.784c-.605 0-1.095-.49-1.095-1.095V5.025c0-.605.49-1.095 1.095-1.095s1.095.49 1.095 1.095v7.664c0 .605-.49 1.095-1.095 1.095z"></path><path fill="#FAAA35" d="M25.664 6.667c-1.162 0-2.076-.532-2.445-1.423-.32-.773-.479-2.45 2.058-4.986.214-.214.56-.214.774 0 2.537 2.537 2.378 4.213 2.058 4.986-.369.891-1.283 1.423-2.445 1.423z"></path><path fill="#53E8FA" d="M18 17.068c-.605 0-1.095-.49-1.095-1.095V8.31c0-.605.49-1.095 1.095-1.095s1.095.49 1.095 1.095v7.664c0 .604-.49 1.094-1.095 1.094z"></path><path fill="#FAAA35" d="M18 9.952c-1.162 0-2.076-.532-2.445-1.423-.321-.773-.479-2.45 2.058-4.986.214-.214.56-.214.774 0 2.537 2.537 2.378 4.213 2.058 4.986-.369.891-1.283 1.423-2.445 1.423z"></path><path fill="#53E8FA" d="M10.336 13.784c-.605 0-1.095-.49-1.095-1.095V5.025c0-.605.49-1.095 1.095-1.095s1.095.49 1.095 1.095v7.664c0 .605-.49 1.095-1.095 1.095z"></path><path fill="#FAAA35" d="M10.336 6.667c-1.162 0-2.076-.532-2.445-1.423-.321-.773-.479-2.45 2.058-4.986.214-.214.56-.214.774 0 2.537 2.537 2.378 4.213 2.058 4.986-.369.891-1.283 1.423-2.445 1.423z"></path></svg>' );
                    } else if( 'black_friday' == $type ){
                        $upgrade_menu_text = esc_html__( 'Black Friday Sale 🔥', 'bookingpress-appointment-booking' );
                    } else if ( 'spring_sale' == $type ){
                        $upgrade_menu_text = esc_html__( 'Spring Sale 💐', 'bookingpress-appointment-booking' );
                    }
                }
            }
            
            add_submenu_page($bookingpress_slugs->bookingpress, $upgrade_menu_text, $upgrade_menu_text, 'bookingpress', $bookingpress_slugs->bookingpress."&upgrade_action=upgrade_to_pro", array( $this, 'route' ));

        }

        function bookingpress_page_slugs()
        {
            global $bookingpress_slugs;
            $bookingpress_slugs = new stdClass();
            /* Admin-Pages-Slug */

            $bookingpress_slugs->bookingpress               = 'bookingpress';
            $bookingpress_slugs->bookingpress_lite_wizard   = 'bookingpress_lite_wizard';
            $bookingpress_slugs->bookingpress_calendar      = 'bookingpress_calendar';
            $bookingpress_slugs->bookingpress_appointments  = 'bookingpress_appointments';
            $bookingpress_slugs->bookingpress_payments      = 'bookingpress_payments';
            $bookingpress_slugs->bookingpress_customers     = 'bookingpress_customers';
            $bookingpress_slugs->bookingpress_services      = 'bookingpress_services';
            $bookingpress_slugs->bookingpress_notifications = 'bookingpress_notifications';
            $bookingpress_slugs->bookingpress_customize     = 'bookingpress_customize';
            $bookingpress_slugs->bookingpress_settings      = 'bookingpress_settings';
            $bookingpress_slugs->bookingpress_addons        = 'bookingpress_addons';
            $bookingpress_slugs->bookingpress_growth_tools  = 'bookingpress_growth_tools';

            return $bookingpress_slugs;
        }

        function get_free_menu_position( $start, $increment = 0.1 )
        {
            foreach ( $GLOBALS['menu'] as $key => $menu ) {
                $menus_positions[] = floatval($key);
            }
            if (! in_array($start, $menus_positions) ) {
                $start = strval($start);
                return $start;
            } else {
                $start += $increment;
            }
            /* the position is already reserved find the closet one */
            while ( in_array($start, $menus_positions) ) {
                $start += $increment;
            }
            $start = strval($start);
            return $start;
        }

        function route()
        {
            global $bookingpress_slugs;
            if (isset($_REQUEST['page']) ) {
                $pageWrapperClass = '';
                if (is_rtl() ) {
                    $pageWrapperClass = 'bookingpress_page_rtl';
                }
                echo '<div class="bookingpress_page_wrapper ' . esc_html($pageWrapperClass) . '" id="root_app">';
                $requested_page = sanitize_text_field($_REQUEST['page']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                do_action('bookingpress_admin_messages', $requested_page);

                if (file_exists(BOOKINGPRESS_VIEWS_DIR . '/bookingpress_main.php') ) {
                    include BOOKINGPRESS_VIEWS_DIR . '/bookingpress_main.php';
                }
                echo '</div>';
            }
        }
        
        /**
         * Set admin CSS
         *
         * @return void
         */
        function set_css()
        {
            global $bookingpress_slugs;

            wp_register_style('bookingpress_admin_custom_css', BOOKINGPRESS_URL . '/css/bookingpress_admin_custom.css', array(), BOOKINGPRESS_VERSION);
            wp_enqueue_style('bookingpress_admin_custom_css');

            /* Plugin Style */
            wp_register_style('bookingpress_element_css', BOOKINGPRESS_URL . '/css/bookingpress_element_theme.css', array(), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_fonts_css', BOOKINGPRESS_URL . '/css/fonts/fonts.css', array(), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_calendar_css', BOOKINGPRESS_URL . '/css/bookingpress_vue_calendar.css', array(), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_root_variables_css', BOOKINGPRESS_URL . '/css/bookingpress_variables.css', array(), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_components_css', BOOKINGPRESS_URL . '/css/bookingpress_admin_components.css', array(), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_admin_css', BOOKINGPRESS_URL . '/css/bookingpress_admin.css', array( 'bookingpress_root_variables_css' ), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_admin_rtl_css', BOOKINGPRESS_URL . '/css/bookingpress_admin_rtl.css', array(), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_tel_input', BOOKINGPRESS_URL . '/css/bookingpress_tel_input.css', array(), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_wizard_style', BOOKINGPRESS_URL . '/css/bookingpress_wizard.css', array(), BOOKINGPRESS_VERSION);

            /* Add CSS file only for plugin pages. */
            if (isset($_REQUEST['page']) && in_array(sanitize_text_field($_REQUEST['page']), (array) $bookingpress_slugs) ) {
                wp_enqueue_style('bookingpress_element_css');
                wp_enqueue_style('bookingpress_fonts_css');

                if (! empty($_REQUEST['page']) && ( sanitize_text_field($_REQUEST['page']) == 'bookingpress_calendar' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_customize' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                    wp_enqueue_style('bookingpress_calendar_css');
                }

                if(!empty($_REQUEST['page']) && (sanitize_text_field($_REQUEST['page']) == 'bookingpress_lite_wizard' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_wizard')){
                    wp_enqueue_style('bookingpress_wizard_style');
                }

                wp_enqueue_style('bookingpress_root_variables_css');
                wp_enqueue_style('bookingpress_components_css');
                wp_enqueue_style('bookingpress_admin_css');
                if (is_rtl() ) {
                    wp_enqueue_style('bookingpress_admin_rtl_css');
                }
                $bookingpress_allowed_tel_input_script_backend = apply_filters('bookingpress_allowed_tel_input_script_backend',false,sanitize_text_field($_REQUEST['page']));
                if (! empty($_REQUEST['page']) && ($bookingpress_allowed_tel_input_script_backend || sanitize_text_field($_REQUEST['page']) == 'bookingpress_customers' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_customize' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_settings' || $_REQUEST['page'] == 'bookingpress_myprofile' || $_REQUEST['page'] == "bookingpress_appointments" || $_REQUEST['page'] == "bookingpress_calendar" ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                    wp_enqueue_style('bookingpress_tel_input');
                }
            }

            $bpa_current_date_for_bf_popup = current_time( 'timestamp', true );

            $bpa_sale_popup_details = $this->bookingpress_get_sales_data();

            $current_year = gmdate('Y', current_time('timestamp', true ) );

            if (!function_exists('is_plugin_active')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if( !empty( $bpa_sale_popup_details[ $current_year ] ) && !is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
                $sale_details = $bpa_sale_popup_details[ $current_year ];
                
                $bpa_bf_popup_start_time = $sale_details['start_time'];
                $bpa_bf_popup_end_time = $sale_details['end_time'];

                $sale_type = $sale_details['type'];
                
                if( $bpa_current_date_for_bf_popup >= $bpa_bf_popup_start_time && $bpa_current_date_for_bf_popup <= $bpa_bf_popup_end_time ){

                    if( 'spring_sale' == $sale_type || 'birthday_sale' == $sale_type ){
                        wp_add_inline_style( 'bookingpress_admin_custom_css', 'li#toplevel_page_bookingpress ul li:last-child{ background: #FC1E47 !important; } #toplevel_page_bookingpress ul li:last-child a{ color: white !important; position:relative; }' );
                    }
                }
            }
        }

        
        /**
         * Set front CSS
         *
         * @param  mixed $force_enqueue
         * @return void
         */
        function set_front_css( $force_enqueue = 0 )
        {
            global $wpdb, $tbl_bookingpress_form_fields;
            wp_register_style('bookingpress_element_css', BOOKINGPRESS_URL . '/css/bookingpress_element_theme.css', array(), BOOKINGPRESS_VERSION);

            wp_register_style('bookingpress_fonts_css', BOOKINGPRESS_URL . '/css/fonts/fonts.css', array(), BOOKINGPRESS_VERSION);
            wp_register_style('bookingpress_front_css', BOOKINGPRESS_URL . '/css/bookingpress_front.css', array(), BOOKINGPRESS_VERSION);
            wp_register_style('bookingpress_front_rtl_css', BOOKINGPRESS_URL . '/css/bookingpress_front_rtl.css', array(), BOOKINGPRESS_VERSION);
            wp_register_style('bookingpress_tel_input', BOOKINGPRESS_URL . '/css/bookingpress_tel_input.css', array(), BOOKINGPRESS_VERSION);

            $bookingress_load_js_css_all_pages = $this->bookingpress_get_settings('load_js_css_all_pages', 'general_setting');

            $bookingpress_after_canceled_payment_page_id = $this->bookingpress_get_customize_settings('after_failed_payment_redirection', 'booking_form');
            $bookingpress_after_approved_payment_page_id = $this->bookingpress_get_customize_settings('after_booking_redirection', 'booking_form');
            $bookingpress_cancelled_appointment_page_id = $this->bookingpress_get_customize_settings('after_cancelled_appointment_redirection', 'booking_my_booking');
            $bookingpress_cancellation_confirmation_page_id = $this->bookingpress_get_customize_settings('appointment_cancellation_confirmation', 'booking_my_booking');

            $bookingpress_current_page_id = get_the_ID();

            if ($this->bookingpress_is_front_page() || ( $bookingress_load_js_css_all_pages == 'true' ) || ( $force_enqueue == 1 ) || ($bookingpress_after_approved_payment_page_id == $bookingpress_current_page_id) || ($bookingpress_cancelled_appointment_page_id == $bookingpress_current_page_id) || ($bookingpress_after_canceled_payment_page_id == $bookingpress_current_page_id) || ($bookingpress_cancellation_confirmation_page_id == $bookingpress_current_page_id) ) {
                wp_enqueue_style('bookingpress_element_css');
                wp_enqueue_style('bookingpress_fonts_css');
                wp_enqueue_style('bookingpress_front_css');
                
                $bpa_phone_number_field_detail = wp_cache_get( 'bookingpress_phone_field_data' );
                if( false === $bpa_phone_number_field_detail ){
                    $bookingpress_form_field_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name = %s", 'phone_number'), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                    wp_cache_set( 'bookingpress_phone_field_data', $bookingpress_form_field_data );
                } else {
                    $bookingpress_form_field_data = $bpa_phone_number_field_detail;
                }
                    
                $bookingpress_is_field_hide = isset($bookingpress_form_field_data['bookingpress_field_is_hide']) ? intval($bookingpress_form_field_data['bookingpress_field_is_hide']) : 1;
                if ($bookingpress_is_field_hide == 0 ) {
                    wp_enqueue_style('bookingpress_tel_input');
                }

                if (is_rtl() ) {
                    wp_enqueue_style('bookingpress_front_rtl_css');
                }

                do_action('bookingpress_add_frontend_css');  
                
                if($bookingress_load_js_css_all_pages == 'true' ) {
                    $this->bookingpress_load_booking_form_custom_css();
                    $this->bookingpress_load_mybooking_custom_css();
                }                
            }
        }
        

        /**
         * This function is used to load booking form custom css.
         * 
         * @return void
         */
        function bookingpress_load_booking_form_custom_css() {
            $bookingpress_dependency = array('bookingpress_front_css');
            if( $this->bpa_is_pro_active() ){
                $bookingpress_dependency[] = 'bookingpress_pro_front_css';
            }
            $bookingpress_customize_css_key = get_option('bookingpress_custom_css_key', true);
            if (file_exists(BOOKINGPRESS_UPLOAD_DIR . '/bookingpress_front_custom_' . $bookingpress_customize_css_key . '.css') ) {
                $bookingpress_assets_version = BOOKINGPRESS_VERSION.'_'.current_time('timestamp');
                wp_register_style('bookingpress_front_custom_css', BOOKINGPRESS_UPLOAD_URL . '/bookingpress_front_custom_' . $bookingpress_customize_css_key . '.css', $bookingpress_dependency, $bookingpress_assets_version);
                wp_enqueue_style('bookingpress_front_custom_css');

                global $bookingpress_global_options, $BookingPress;

                $bookingpress_custom_css = $BookingPress->bookingpress_get_customize_settings('custom_css', 'booking_form');
                $bookingpress_custom_css = !empty($bookingpress_custom_css) ? stripslashes_deep( $bookingpress_custom_css ) : '';

                wp_add_inline_style( 'bookingpress_front_custom_css', $bookingpress_custom_css, 'after' );
                
                $bookingpress_google_fonts_list  = $bookingpress_global_options->bookingpress_get_google_fonts();
                
                $bookingform_title_font_family = $this->bookingpress_get_customize_settings('title_font_family', 'booking_form');
                
                
                if (! empty($bookingform_title_font_family) && ($bookingform_title_font_family != 'Poppins') && in_array( $bookingform_title_font_family, $bookingpress_google_fonts_list ) ) {
                    $bookingpress_google_font_url = 'https://fonts.googleapis.com/css2?family=' . $bookingform_title_font_family . '&display=swap';
                    $bookingpress_google_font_url = apply_filters('bookingpress_modify_google_font_url', $bookingpress_google_font_url, $bookingform_title_font_family);
                    wp_register_style('bookingpress_front_font_css_' . $bookingform_title_font_family, $bookingpress_google_font_url, array(), BOOKINGPRESS_VERSION);
                    wp_enqueue_style('bookingpress_front_font_css_' . $bookingform_title_font_family);                    
                }
            }

        }
        
        /**
         * This function is used to load my booking custom css.
         * 
         * @return void
         */
        function bookingpress_load_mybooking_custom_css() {
            $bookingpress_dependency = array('bookingpress_front_css');
            if( $this->bpa_is_pro_active() ){
                $bookingpress_dependency[] = 'bookingpress_pro_front_css';
            }
            $bookingpress_customize_css_key = get_option('bookingpress_custom_css_key', true);
            if (file_exists(BOOKINGPRESS_UPLOAD_DIR . '/bookingpress_front_mybookings_custom_' . $bookingpress_customize_css_key . '.css') ) {
                
                wp_register_style('bookingpress_front_mybookings_custom_css', BOOKINGPRESS_UPLOAD_URL . '/bookingpress_front_mybookings_custom_' . $bookingpress_customize_css_key . '.css',$bookingpress_dependency, BOOKINGPRESS_VERSION);
                wp_enqueue_style('bookingpress_front_mybookings_custom_css');

                global $bookingpress_global_options;
                $bookingpress_google_fonts_list  = $bookingpress_global_options->bookingpress_get_google_fonts();
                
                $bookingform_title_font_family = $this->bookingpress_get_customize_settings('title_font_family', 'booking_form');
                if (! empty($bookingform_title_font_family) && ($bookingform_title_font_family != 'Poppins') && in_array( $bookingform_title_font_family, $bookingpress_google_fonts_list ) ) {
                    wp_register_style('bookingpress_front_font_css_' . $bookingform_title_font_family, 'https://fonts.googleapis.com/css2?family=' . $bookingform_title_font_family . '&display=swap', array(), BOOKINGPRESS_VERSION);
                    wp_enqueue_style('bookingpress_front_font_css_' . $bookingform_title_font_family);                    
                }
            }
        }
        
        /**
         * Load mybookings custom js
         *
         * @return void
         */
        function bookingpress_load_mybookings_custom_js(){
            wp_register_script('bookingpress_v-calendar_js', BOOKINGPRESS_URL . '/js/bookingpress_v-calendar.js', array('bookingpress_vue_js'), BOOKINGPRESS_VERSION);
            wp_enqueue_script('bookingpress_v-calendar_js');
        }
        
        /**
         * Load frontside JS
         *
         * @param  mixed $force_enqueue
         * @return void
         */
        function set_front_js( $force_enqueue = 0 )
        {
            global $wpdb, $tbl_bookingpress_form_fields, $wp_version;

            wp_register_script('bookingpress_vue_js', BOOKINGPRESS_URL . '/js/bookingpress_vue.min.js', array(), BOOKINGPRESS_VERSION, true);

            wp_register_script('bookingpress_axios_js', BOOKINGPRESS_URL . '/js/bookingpress_axios.min.js', array(), BOOKINGPRESS_VERSION, true);

            wp_register_script('bookingpress_element_js', BOOKINGPRESS_URL . '/js/bookingpress_element.js', array(), BOOKINGPRESS_VERSION, true);

            wp_register_script('bookingpress_wordpress_vue_helper_js', BOOKINGPRESS_URL . '/js/bookingpress_wordpress_vue_qs_helper.js', array(), BOOKINGPRESS_VERSION, true);

            wp_register_script('bookingpress_moment_js', BOOKINGPRESS_URL . '/js/bookingpress_moment.min.js', array(), BOOKINGPRESS_VERSION, true);

            wp_register_script('bookingpress_tel_input_js', BOOKINGPRESS_URL . '/js/bookingpress_tel_input.js', array(), BOOKINGPRESS_VERSION, true);
            wp_register_script('bookingpress_tel_utils_js', BOOKINGPRESS_URL . '/js/bookingpress_tel_utils.js', array(), BOOKINGPRESS_VERSION, true );

            wp_register_script('bookingpress_v-calendar_js', BOOKINGPRESS_URL . '/js/bookingpress_v-calendar.js', array('bookingpress_vue_js'), BOOKINGPRESS_VERSION);

            if( version_compare( $wp_version, '5.0', '<' ) ){
                wp_register_script( 'wp-hooks', BOOKINGPRESS_URL . '/js/hooks.js', array(), BOOKINGPRESS_VERSION, true );
            }

            $load_calendar_js = false;
            if( is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') ){
                $bpa_pro_version = $this->bpa_pro_plugin_version();

                if( version_compare( $bpa_pro_version, '2.6', '<') ){
                    $load_calendar_js = true;
                    wp_register_script('bookingpress_calendar_js', BOOKINGPRESS_URL . '/js/bookingpress_vue_calendar.js', array(), BOOKINGPRESS_VERSION, true);
                }
            }

            $bookingress_load_js_css_all_pages = $this->bookingpress_get_settings('load_js_css_all_pages', 'general_setting');

            if ($this->bookingpress_is_front_page() || ( $bookingress_load_js_css_all_pages == 'true' ) || ( $force_enqueue == 1 ) ) {
                $get_already_loaded_vue_setting_val = $this->bookingpress_get_settings('use_already_loaded_vue', 'general_setting');
                if (! $get_already_loaded_vue_setting_val || $get_already_loaded_vue_setting_val == 'false' ) {
                    wp_enqueue_script('bookingpress_vue_js');
                }
                wp_enqueue_script('wp-hooks');
                wp_enqueue_script('bookingpress_axios_js');
                wp_enqueue_script('bookingpress_wordpress_vue_helper_js');
                wp_enqueue_script('bookingpress_element_js');
                if( true == $load_calendar_js ){
                    wp_enqueue_script('bookingpress_calendar_js');
                }
                wp_enqueue_script('bookingpress_moment_js');                

                if($bookingress_load_js_css_all_pages == 'true' ) {
                    wp_enqueue_script('bookingpress_v-calendar_js');
                }


                $bpa_phone_number_field_detail = wp_cache_get( 'bookingpress_phone_field_data' );
                if( false === $bpa_phone_number_field_detail ){
                    $bookingpress_form_field_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name = %s", 'phone_number'), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False alarm
                    wp_cache_set( 'bookingpress_phone_field_data', $bookingpress_form_field_data );
                } else {
                    $bookingpress_form_field_data = $bpa_phone_number_field_detail;
                }

                $bookingpress_is_field_hide = isset($bookingpress_form_field_data['bookingpress_field_is_hide']) ? intval($bookingpress_form_field_data['bookingpress_field_is_hide']) : 1;
                if ($bookingpress_is_field_hide == 0 ) {
                    wp_enqueue_script('bookingpress_tel_input_js');
                    wp_enqueue_script('bookingpress_tel_utils_js');
                }

                global $bookingpress_global_options;
                $bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();

                if ($bookingpress_site_current_language != 'en' ) {
                    wp_register_script('bookingpress_vue_cal_locale', BOOKINGPRESS_URL . '/js/locales/' . $bookingpress_site_current_language . '.js', array(), BOOKINGPRESS_VERSION, true);
                    wp_enqueue_script('bookingpress_vue_cal_locale');

                    wp_register_script('bookingpress_elements_locale', BOOKINGPRESS_URL . '/js/elements_locale/' . $bookingpress_site_current_language . '.js', array(), BOOKINGPRESS_VERSION, true);
                    wp_enqueue_script('bookingpress_elements_locale');
                } else {
                    wp_register_script('bookingpress_elements_locale', BOOKINGPRESS_URL . '/js/bookingpress_element_en.js', array(), BOOKINGPRESS_VERSION, true);
                    wp_enqueue_script('bookingpress_elements_locale');
                }

                //wp_localize_script('bookingpress_vue_js', 'appoint_ajax_obj', array( 'ajax_url' => admin_url('admin-ajax.php') ));
                $data = 'var appoint_ajax_obj = '.wp_json_encode( array(
                        'ajax_url' => admin_url( 'admin-ajax.php')
                    )
                ).';';

                wp_add_inline_script('bookingpress_vue_js', $data, 'before');

                do_action('bookingpress_add_frontend_js');
            }
        }
        
        /**
         * Load admin side JS
         *
         * @return void
         */
        function set_js()
        {
            global $bookingpress_slugs, $bookingpress_global_options;

            $bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();

            /* Plugin Scripts */
            wp_register_script('bookingpress_admin_custom_js', BOOKINGPRESS_URL . '/js/bookingpress_admin_custom.js', array(), BOOKINGPRESS_VERSION);
            wp_enqueue_script('bookingpress_admin_custom_js');

            wp_localize_script( 'bookingpress_admin_custom_js', 'bpa_custom_js_data', array(
                'bookingpress_ajax_url' => admin_url('admin-ajax.php')
            ) );

            $custom_inline_script = '__BOOKINGPRESSIMAGEURL = "' . esc_url(BOOKINGPRESS_IMAGES_URL) . '"';
            
            wp_add_inline_script( 'bookingpress-appointment-booking-booking-form-editor-script', $custom_inline_script, 'before' );
            
            wp_register_script('bookingpress_admin_js', BOOKINGPRESS_URL . '/js/bookingpress_vue.min.js', array(), BOOKINGPRESS_VERSION);

            wp_register_script('bookingpress_axios_js', BOOKINGPRESS_URL . '/js/bookingpress_axios.min.js', array(), BOOKINGPRESS_VERSION);
            wp_register_script('bookingpress_sortable_js', BOOKINGPRESS_URL . '/js/bookingpress_Sortable.min.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION);
            wp_register_script('bookingpress_draggable_js', BOOKINGPRESS_URL . '/js/bookingpress_draggable.min.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION);

            wp_register_script('bookingpress_element_js', BOOKINGPRESS_URL . '/js/bookingpress_element.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION);

            if ($bookingpress_site_current_language == 'en' ) {
                wp_register_script('bookingpress_element_en_js', BOOKINGPRESS_URL . '/js/bookingpress_element_en.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION);
            }

            wp_register_script('bookingpress_wordpress_vue_helper_js', BOOKINGPRESS_URL . '/js/bookingpress_wordpress_vue_qs_helper.js', array(), BOOKINGPRESS_VERSION);

            wp_register_script('bookingpress_calendar_js', BOOKINGPRESS_URL . '/js/bookingpress_vue_calendar.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION);

            wp_register_script('bookingpress_v-calendar_js', BOOKINGPRESS_URL . '/js/bookingpress_v-calendar.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION);

            wp_register_script('bookingpress_moment_js', BOOKINGPRESS_URL . '/js/bookingpress_moment.min.js', array(), BOOKINGPRESS_VERSION);

            wp_register_script('bookingpress_tel_input_js', BOOKINGPRESS_URL . '/js/bookingpress_tel_input.js', array(), BOOKINGPRESS_VERSION);
            wp_register_script('bookingpress_tel_utils_js', BOOKINGPRESS_URL . '/js/bookingpress_tel_utils.js', array(), BOOKINGPRESS_VERSION);
            
            /* Add JS file only for plugin pages. */
            if (isset($_REQUEST['page']) && ( sanitize_text_field($_REQUEST['page']) == 'bookingpress_services' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_customize' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                wp_enqueue_script('bookingpress_sortable_js');
                wp_enqueue_script('bookingpress_draggable_js');
            }

            if( isset($_REQUEST['page']) && 'bookingpress_notifications' == $_REQUEST['page'] ){
                wp_enqueue_script( 'wp-tinymce' );
            }

            if (isset($_REQUEST['page']) && in_array(sanitize_text_field($_REQUEST['page']), (array) $bookingpress_slugs) ) {
                wp_enqueue_script('bookingpress_admin_js');
                wp_enqueue_script('bookingpress_axios_js');
                wp_enqueue_script('bookingpress_wordpress_vue_helper_js');
                wp_enqueue_script('bookingpress_element_js');
                wp_enqueue_script('bookingpress_moment_js');
                if ($bookingpress_site_current_language == 'en' ) {
                    wp_enqueue_script('bookingpress_element_en_js');
                }

                if ($bookingpress_site_current_language != 'en' ) {
                    wp_register_script('bookingpress_elements_locale', BOOKINGPRESS_URL . '/js/elements_locale/' . $bookingpress_site_current_language . '.js', array( 'bookingpress_element_js' ), BOOKINGPRESS_VERSION);
                    wp_enqueue_script('bookingpress_elements_locale');
                }

                $bookingpress_allowed_tel_input_script_backend = apply_filters('bookingpress_allowed_tel_input_script_backend',false,sanitize_text_field($_REQUEST['page']));

                if (! empty($_REQUEST['page']) && ($bookingpress_allowed_tel_input_script_backend || sanitize_text_field($_REQUEST['page']) == 'bookingpress_customers' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_customize' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_settings' || ( $_REQUEST['page'] == 'bookingpress_myprofile' || $_REQUEST['page'] == "bookingpress_appointments" || $_REQUEST['page'] == "bookingpress_calendar" ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                    wp_enqueue_script('bookingpress_tel_input_js');
                    wp_enqueue_script('bookingpress_tel_utils_js');
                }
            }

            if (isset($_REQUEST['page']) && ( sanitize_text_field($_REQUEST['page']) == 'bookingpress_calendar' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_customize' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                wp_enqueue_script('bookingpress_calendar_js');

                if ($bookingpress_site_current_language != 'en' ) {
                    wp_register_script('bookingpress_vue_cal_locale', BOOKINGPRESS_URL . '/js/locales/' . $bookingpress_site_current_language . '.js', array(), BOOKINGPRESS_VERSION);
                    wp_enqueue_script('bookingpress_vue_cal_locale');
                }
            }

            if (isset($_REQUEST['page']) && (sanitize_text_field($_REQUEST['page']) == 'bookingpress_settings' || sanitize_text_field($_REQUEST['page']) == 'bookingpress_customize')  ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                wp_enqueue_script('bookingpress_v-calendar_js');
            }

            if (isset($_REQUEST['page']) && ( isset($_REQUEST['page']) && sanitize_text_field($_REQUEST['page']) == 'bookingpress' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                wp_register_script('bookingpress_charts_js', BOOKINGPRESS_URL . '/js/bookingpress_chart.min.js', array(), BOOKINGPRESS_VERSION);
                wp_enqueue_script('bookingpress_charts_js');
            }

            wp_localize_script('bookingpress_admin_custom_js', 'appoint_ajax_obj', array( 'ajax_url' => admin_url('admin-ajax.php') ));

        }
                
        /**
         * Hide update notices in plugins page
         *
         * @return void
         */
        function bookingpress_hide_update_notice()
        {
            global $bookingpress_slugs;
            if (isset($_REQUEST['page']) && in_array(sanitize_text_field($_REQUEST['page']), (array) $bookingpress_slugs) ) {
                remove_action('admin_notices', 'update_nag', 3);
                remove_action('network_admin_notices', 'update_nag', 3);
                remove_action('admin_notices', 'maintenance_nag');
                remove_action('network_admin_notices', 'maintenance_nag');
                remove_action('admin_notices', 'site_admin_notice');
                remove_action('network_admin_notices', 'site_admin_notice');
                remove_action('load-update-core.php', 'wp_update_plugins');
                add_filter('pre_site_transient_update_core', array( $this, 'bookingpress_remove_core_updates' ));
                add_filter('pre_site_transient_update_plugins', array( $this, 'bookingpress_remove_core_updates' ));
                add_filter('pre_site_transient_update_themes', array( $this, 'bookingpress_remove_core_updates' ));
            }
        }

        function bookingpress_remove_core_updates()
        {
            global $wp_version;
            return (object) array(
            'last_checked'    => time(),
            'version_checked' => $wp_version,
            );
        }

        function bookingpress_add_user_role_and_capabilities()
        {
            global $wp_roles;
            $role_name  = 'BookingPress Customer';
            $role_slug  = sanitize_title($role_name);
            $basic_caps = array(
            $role_slug => true,
            'read'     => true,
            'level_0'  => true,
            );

            $wp_roles->add_role($role_slug, $role_name, $basic_caps);
        }

        
        /**
         * Validate specific username
         *
         * @param  mixed $user_login
         * @param  mixed $invalid_username
         * @return void
         */
        function bookingpress_validate_username( $user_login, $invalid_username = '' )
        {
            $sanitized_user_login = sanitize_user($user_login);
            $err                  = '';
            // Check the username
            if ($sanitized_user_login == '' ) {
                $err = esc_html__('Please enter a username.', 'bookingpress-appointment-booking');
            } elseif (! validate_username($user_login) ) {
                if ($invalid_username == '' ) {
                    $err_msg = esc_html__('This username is invalid because it uses illegal characters. Please enter a valid username.', 'bookingpress-appointment-booking');
                } else {
                    $err_msg = $invalid_username;
                }
                $err = ( ! empty($err_msg) ) ? $err_msg : esc_html__('This username is invalid because it uses illegal characters. Please enter a valid username.', 'bookingpress-appointment-booking');
            } elseif (username_exists($sanitized_user_login) ) {
                $err = esc_html__('This username is already registered, please choose another one.', 'bookingpress-appointment-booking');
            }
            return $err;
        }

        
        /**
         * Validate specific email address
         *
         * @param  mixed $user_email
         * @param  mixed $invalid_email
         * @return void
         */
        function bookingpress_validate_email( $user_email, $invalid_email = '' )
        {
            $err = '';
            // Check the username
            if ('' == $user_email ) {
                $err = esc_html__('Please type your e-mail address.', 'bookingpress-appointment-booking');
            } elseif (! is_email($user_email) ) {
                if ($invalid_email == '' ) {
                    $err_msg = esc_html__('Please enter valid email address.', 'bookingpress-appointment-booking');
                } else {
                    $err_msg = $invalid_email;
                }
                $err = ( ! empty($err_msg) ) ? $err_msg : esc_html__('Please enter valid email address.', 'bookingpress-appointment-booking');
            } elseif (email_exists($user_email) ) {
                $err = esc_html__('This email is already registered, please choose another one.', 'bookingpress-appointment-booking');
            }
            return $err;
        }
        
        /**
         * Update usermeta details
         *
         * @param  mixed $user_ID
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_user_update_meta_details( $user_ID, $posted_data = array() )
        {
            if (! empty($user_ID) && ! empty($posted_data) ) {
                $user = new WP_User($user_ID);
                foreach ( $posted_data as $key => $val ) {
                    if ($key == 'first_name' || $key == 'last_name' ) {
                        $val = trim(sanitize_text_field($val));
                    } else if( $key == 'staff_email'){
                        wp_update_user( array(
                            'ID' => $user_ID,
                            'user_email' => sanitize_email($val)
                       ) );
                    } elseif ($key == 'role' || $key == 'roles' ) {
                        if (isset($val) && is_array($val) && ! empty($val) ) {
                            $count = 0;
                            foreach ( $val as $v ) {
                                if ($count == 0 ) {
                                    $user->set_role($v);
                                } else {
                                    $user->add_role($v);
                                }
                                $count++;
                            }
                        } else {
                            $user->add_role($val);
                        }
                    }
                    update_user_meta($user_ID, $key, $val);
                }
            }
        }
        
        /**
         * BookingPress custom function for file upload
         *
         * @param  mixed $source
         * @param  mixed $destination
         * @return void
         */
        function bookingpress_file_upload_function( $source, $destination ){

            $allow_file_upload = 1;
            $allow_file_upload = apply_filters('bookingpress_allow_file_uploads', $allow_file_upload);
            if (empty($source) || empty($destination) || empty($allow_file_upload)) {
                return false;
            }

            if (! function_exists('WP_Filesystem') ) {
                include_once ABSPATH . 'wp-admin/includes/file.php';
            }

            WP_Filesystem();
            global $wp_filesystem;

            $file_content = $wp_filesystem->get_contents($source);

            $result = $wp_filesystem->put_contents($destination, $file_content, 0777);

            return $result;
        }

        
        /**
         * Remove uploaded file
         *
         * @return void
         */
        function bookingpress_remove_uploaded_file()
        {
            global $wpdb;
            $response              = array();

            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();
            }
            if (! empty($_POST) && ! empty($_POST['upload_file_url']) ) {
                $bookingpress_uploaded_avatar_url = esc_url_raw($_POST['upload_file_url']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_file_name_arr       = explode('/', $bookingpress_uploaded_avatar_url);
                $bookingpress_file_name           = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
                if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
                    wp_delete_file(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
                }
            }
            die;
        }

        
        /**
         * Insert or Update BookingPress settings value
         *
         * @param  mixed $setting_name
         * @param  mixed $setting_type
         * @param  mixed $setting_value
         * @return void
         */
        public function bookingpress_update_settings( $setting_name, $setting_type, $setting_value = '' )
        {
            global $wpdb, $tbl_bookingpress_settings;
            if (! empty($setting_name) ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally. False Positive alarm
                $bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` WHERE setting_name = %s AND setting_type = %s", $setting_name, $setting_type));

                if ($bookingpress_check_record_existance > 0 ) {
                    // If record already exists then update data.
                    $bookingpress_update_data = array(
                    'setting_value' => ( ! empty($setting_value) && (gettype($setting_value) === 'boolean' || $setting_name == 'smtp_password') ) ? $setting_value : sanitize_text_field($setting_value),
                    'setting_type'  => $setting_type,
                    'updated_at'    => current_time('mysql'),
                    );

                    $bpa_update_where_condition = array(
                     'setting_name' => $setting_name,
                     'setting_type' => $setting_type,
                    );

                    $bpa_update_affected_rows = $wpdb->update($tbl_bookingpress_settings, $bookingpress_update_data, $bpa_update_where_condition);
                    if ($bpa_update_affected_rows > 0 ) {
                        wp_cache_delete($setting_name);
                        wp_cache_set($setting_name, $setting_value);
                        return 1;
                    }
                } else {
                    // If record not exists hen insert data.

                    $bookingpress_insert_data = array(
                    'setting_name'  => $setting_name,
                    'setting_value' => ( ! empty($setting_value) && (gettype($setting_value) === 'boolean' || $setting_name == 'smtp_password') ) ? $setting_value : sanitize_text_field($setting_value),
                    'setting_type'  => $setting_type,
                    'updated_at'    => current_time('mysql'),
                    );

                    $bookingpress_inserted_id = $wpdb->insert($tbl_bookingpress_settings, $bookingpress_insert_data);
                    if ($bookingpress_inserted_id > 0 ) {
                        wp_cache_delete($setting_name);
                        wp_cache_set($setting_name, $setting_value);
                        return 1;
                    }
                }
            }

            return 0;
        }
        
        /**
         * Get customize setting value
         *
         * @param  mixed $setting_names
         * @param  mixed $setting_type
         * @return void
         */

        public function bookingpress_get_customize_settings( $setting_name, $setting_type ){

            if( $this->bpa_is_pro_active() && version_compare( $this->bpa_pro_plugin_version(), '2.0', '<') ){
                return $this->bookingpress_get_customize_settings_lagecy( $setting_name, $setting_type );
            }
            global $bookingpress_global_options, $bookingpress_settings_table_exists;
            if( 1 != $bookingpress_settings_table_exists ){
                return '';
            }
            $bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();
            
            $customize_settings = $bookingpress_options['customize_settings'];

            
            if( !is_array( $setting_name ) && !empty( $customize_settings[ $setting_type ][ $setting_name ] ) ){
                $return_customize_setting_data = $customize_settings[ $setting_type ][ $setting_name ];
                $return_customize_setting_data = apply_filters( 'bookingpress_modified_get_customize_settings',$return_customize_setting_data,$setting_type,$setting_name);
                return $return_customize_setting_data;
            } else if( is_array( $setting_name ) ){
                $return_data = array();
                foreach( $setting_name as $setting_name_key ){
                    if( !empty( $customize_settings[ $setting_type ][ $setting_name_key ] ) ){
                        $return_customize_setting_data = $customize_settings[ $setting_type ][ $setting_name_key ];
                        
                        $return_customize_setting_data = apply_filters( 'bookingpress_modified_get_customize_settings',$return_customize_setting_data,$setting_type,$setting_name_key);

                        $return_data[ $setting_name_key ] = $return_customize_setting_data;
                    } else {
                        $return_data[ $setting_name_key ] = '';
                    }
                }
                return $return_data;
            } else {
                return '';
            }
        }
                
        /**
         * bookingpress_get_customize_settings_lagecy
         *
         * @param  mixed $setting_names
         * @param  mixed $setting_type
         * 
         * @deprecated on 20th March 2023
         * @return void
         */
        public function bookingpress_get_customize_settings_lagecy( $setting_names, $setting_type ){

            if( empty( $setting_names ) ){
                return '';
            }

            global $wpdb, $tbl_bookingpress_customize_settings;

            if( is_array( $setting_names ) ){
                $settings_placeholders = 'bookingpress_setting_name IN (';
                $settings_placeholders .= rtrim( str_repeat( '%s,', count( $setting_names ) ), ',' );
                $settings_placeholders .= ')';

                $customize_setting_key =  implode(",",$setting_names)."|^|".$setting_type;
                
                array_unshift( $setting_names, $settings_placeholders );
                $settings_query_where = call_user_func_array(array( $wpdb, 'prepare' ), $setting_names );
                
                $customize_setting_value = wp_cache_get($customize_setting_key);

                if($customize_setting_value === false){
                    $bookingpress_get_setting  = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_setting_name,bookingpress_setting_value FROM `{$tbl_bookingpress_customize_settings}` WHERE {$settings_query_where} AND bookingpress_setting_type = %s", $setting_type ), ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_customize_settings is a table query and $settings_query_where is already passed through wpdb->prepare function
                    wp_cache_set($customize_setting_key,$bookingpress_get_setting);
                }
                else{
                    $bookingpress_get_setting  = $customize_setting_value;
                }
                
                if( !empty( $bookingpress_get_setting ) ){
                    $bookingpress_setting_value = array();
                    foreach( $bookingpress_get_setting as $bookingpress_setting ){
                        $bookingpress_setting_value[ $bookingpress_setting['bookingpress_setting_name'] ] = $bookingpress_setting['bookingpress_setting_value'];
                    }
                    return $bookingpress_setting_value;
                } else {
                    return array();
                }

            } else {
                $bookingpress_setting_value = '';

                $customize_setting_key =  $setting_names."|^|".$setting_type;
                $customize_setting_value = wp_cache_get($customize_setting_key);

                if($customize_setting_value === false){
                    $bookingpress_get_setting   = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_setting_value FROM `{$tbl_bookingpress_customize_settings}` WHERE bookingpress_setting_name = %s AND bookingpress_setting_type = %s", $setting_names, $setting_type), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally. False alarm
                    wp_cache_set($customize_setting_key,$bookingpress_get_setting);
                }
                else{
                    $bookingpress_get_setting  = $customize_setting_value;
                }
                if( !empty( $bookingpress_get_setting ) ){
                    $bookingpress_setting_value = $bookingpress_get_setting['bookingpress_setting_value'];
                }
                
                return $bookingpress_setting_value;
            }

        }

        /**
         * Get specific setting value.
         *
         * @param  mixed $setting_name
         * @param  mixed $setting_type
         * @return void
         */
        public function bookingpress_get_settings( $setting_name, $setting_type )
        {
            if( $this->bpa_is_pro_active() && version_compare( $this->bpa_pro_plugin_version(), '2.0', '<') ){
                return $this->bookingpress_get_settings_lagecy( $setting_name, $setting_type );
            }
            global $bookingpress_global_options, $bookingpress_settings_table_exists;
            /* if( 1 != $bookingpress_settings_table_exists ){
                return '';
            } */
            $bookingpress_general_setting_options                    = $bookingpress_global_options->bookingpress_global_options();
            
            //echo "inside"; exit;

            $general_settings = $bookingpress_general_setting_options['general_settings'];


            if( !is_array( $setting_name ) && !empty( $general_settings[ $setting_type ][ $setting_name ] ) ){                
                $return_setting_data = $general_settings[ $setting_type ][ $setting_name ];
                $return_setting_data = apply_filters( 'bookingpress_modified_get_settings',$return_setting_data,$setting_type,$setting_name);
                return $return_setting_data;

            } else if( is_array( $setting_name ) ){
                $return_data = array();
                foreach( $setting_name as $setting_name_key ){
                    if( !empty( $general_settings[ $setting_type ][ $setting_name_key ] ) ){
                        $return_setting_data = $general_settings[ $setting_type ][ $setting_name_key ];
                        $return_setting_data = apply_filters( 'bookingpress_modified_get_settings',$return_setting_data,$setting_type,$setting_name_key);
                        $return_data[ $setting_name_key ] = $return_setting_data;
                    } else {
                        $return_data[ $setting_name_key ] = '';
                    }
                }
                return $return_data;
            } else {
                return '';
            }

            //echo "out"; exit;
        }

        
        /**
         * Get specific setting value.
         *
         * @param  mixed $setting_name
         * @param  mixed $setting_type
         * 
         * @deprecated on 20th March 2023
         * @return void
         */
        public function bookingpress_get_settings_lagecy( $setting_name, $setting_type )
        {
            global $wpdb, $tbl_bookingpress_settings;
            $bookingpress_setting_value = '';
            if (! empty($setting_name) ) {
                if ( false !== wp_cache_get($setting_name) ) {
                    $bookingpress_setting_value = wp_cache_get($setting_name);
                } else {

                    $bookingpress_get_setting   = $wpdb->get_row($wpdb->prepare("SELECT setting_value FROM `{$tbl_bookingpress_settings}` WHERE setting_name = %s AND setting_type = %s", $setting_name, $setting_type), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally. False Positive alarm

                    if( !empty( $bookingpress_get_setting ) ){
                        $bookingpress_setting_value = $bookingpress_get_setting['setting_value'];
                    }
                    wp_cache_set($setting_name, $bookingpress_setting_value);
                }
            }

            return $bookingpress_setting_value;
        }

        
        /**
         * Get currency symbol from curreny name
         *
         * @param  mixed $currency_name
         * @return void
         */
        public function bookingpress_get_currency_symbol( $currency_name )
        {
            if (! empty($currency_name) ) {
                global $bookingpress_global_options;
                $bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_countries_currency_details = json_decode($bookingpress_options['countries_json_details']);

                $bookingpress_currency_symbol = '';

                foreach ( $bookingpress_countries_currency_details as $currency_key => $currency_val ) {
                    if ($currency_val->code == $currency_name ) {
                        $bookingpress_currency_symbol = $currency_val->symbol;
                        break;
                    }
                }

                return $bookingpress_currency_symbol;
            }

            return '';
        }
        
        /**
         * Get currency code from currency name
         *
         * @param  mixed $currency_name
         * @return void
         */
        public function bookingpress_get_currency_code( $currency_name )
        {
            if (! empty($currency_name) ) {
                global $bookingpress_global_options;
                $bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_countries_currency_details = json_decode($bookingpress_options['countries_json_details']);

                $bookingpress_currency_code = '';

                foreach ( $bookingpress_countries_currency_details as $currency_key => $currency_val ) {
                    if ($currency_val->code == $currency_name ) {
                        $bookingpress_currency_code = $currency_val->code;
                        break;
                    }
                }

                return $bookingpress_currency_code;
            }

            return '';
        }
        
        /**
         * Format price with currency and other general setting which applied in 'General settings'
         *
         * @param  mixed $price
         * @param  mixed $currency_symbol
         * @param  mixed $is_symbol_added
         * @return void
         */
        public function bookingpress_price_formatter_with_currency_symbol( $price, $currency_symbol = '', $is_symbol_added = 1, $return_float_value = false )
        {
            $bookingpress_decimal_points = $this->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');
            $bookingpress_decimal_points = intval($bookingpress_decimal_points);
            if (gettype($price) == 'string' ) {
                $price = floatval($price);
            }

            if( true == $return_float_value ){
                return number_format( $price, $bookingpress_decimal_points, '.', '' );
            }

            $bookingpress_price_separator_pos = $this->bookingpress_get_settings('price_separator', 'payment_setting');
            if ($bookingpress_price_separator_pos == 'comma-dot' && gettype($price) != 'NULL') {
                $price = number_format($price, $bookingpress_decimal_points, '.', ',');
            } elseif ($bookingpress_price_separator_pos == 'dot-comma' && gettype($price) != 'NULL') {
                $price = number_format($price, $bookingpress_decimal_points, ',', '.');
            } elseif ($bookingpress_price_separator_pos == 'space-dot' && gettype($price) != 'NULL') {
                $price = number_format($price, $bookingpress_decimal_points, '.', ' ');
            } elseif ($bookingpress_price_separator_pos == 'space-comma' && gettype($price) != 'NULL') {
                $price = number_format($price, $bookingpress_decimal_points, ',', ' ');
            } elseif ($bookingpress_price_separator_pos == 'Custom' && gettype($price) != 'NULL') {
                $bookingpress_comma_separator = $this->bookingpress_get_settings('custom_comma_separator', 'payment_setting');
                $bookingpress_dot_separator   = $this->bookingpress_get_settings('custom_dot_separator', 'payment_setting');
                $price                        = number_format($price, $bookingpress_decimal_points, $bookingpress_dot_separator, $bookingpress_comma_separator);
            }

            $bookingpress_price_with_symbol = $price;
            if($is_symbol_added == 1){
                if (empty($currency_symbol) ) {
                    $bookingpress_currency_name = $this->bookingpress_get_settings('payment_default_currency', 'payment_setting');
                    $currency_symbol            = ! empty($bookingpress_currency_name) ? $this->bookingpress_get_currency_symbol($bookingpress_currency_name) : '';
                }

                $bookingpress_price_symbol_position = $this->bookingpress_get_settings('price_symbol_position', 'payment_setting');

                $bookingpress_price_with_symbol = $currency_symbol . $price;

                if ($bookingpress_price_symbol_position == 'before' ) {
                    $bookingpress_price_with_symbol = $currency_symbol . $price;
                } elseif ($bookingpress_price_symbol_position == 'before_with_space' ) {
                    $bookingpress_price_with_symbol = $currency_symbol . ' ' . $price;
                } elseif ($bookingpress_price_symbol_position == 'after' ) {
                    $bookingpress_price_with_symbol = $price . $currency_symbol;
                } elseif ($bookingpress_price_symbol_position == 'after_with_space' ) {
                    $bookingpress_price_with_symbol = $price . ' ' . $currency_symbol;
                }
            }

            return $bookingpress_price_with_symbol;
        }


        /**
         * Format price to default format for payment gateways
         *
         * @param  mixed $price
         * @return $price
         */
        public function bookingpress_convert_amount_to_default_format($price){
            global $bookingpress_global_options;
            $bookingpress_options          = $bookingpress_global_options->bookingpress_global_options();

            $bookingpress_decimal_points = $this->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');
            $bookingpress_decimal_points = intval($bookingpress_decimal_points);
            if (gettype($price) == 'string' ) {
                $price = floatval($price);
            }
            

            $bookingpress_price_separator_pos = $this->bookingpress_get_settings('price_separator', 'payment_setting');
            if ($bookingpress_price_separator_pos == 'dot-comma' && gettype($price) != 'NULL') {
                $step1 = str_replace('.', '[DOT]', $price);
                $step2 = str_replace(',', '.', $step1);
                $price = str_replace('[DOT]', '', $step2);
            } elseif ($bookingpress_price_separator_pos == 'space-dot' && gettype($price) != 'NULL') {
                $price = str_replace(' ', '', $price);
            } elseif ($bookingpress_price_separator_pos == 'space-comma' && gettype($price) != 'NULL') {
                $price = str_replace(',', '.', $price);
            } elseif ($bookingpress_price_separator_pos == 'Custom' && gettype($price) != 'NULL') {
                 $price = str_replace($bookingpress_dot_separator, '.', $price);
                $price = str_replace($bookingpress_comma_separator, '', $price);
            }

            return $price;

        }
        
        /**
         * Get service end time based start time
         *
         * @param  mixed $service_id
         * @param  mixed $service_start_time
         * @param  mixed $service_duration_val
         * @param  mixed $service_duration_unit
         * @return void
         */
        public function bookingpress_get_service_end_time( $service_id, $service_start_time, $service_duration_val = '', $service_duration_unit = '' )
        {
            global $wpdb, $tbl_bookingpress_services;
            if (! empty($service_id) ) {
                $service_duration      = ! empty($service_duration_val) ? $service_duration_val : '';
                $service_unit_duration = ! empty($service_duration_unit) ? $service_duration_unit : '';                                

                if (empty($service_duration) && empty($service_unit_duration) ) {
                    $service_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_service_duration_val,bookingpress_service_duration_unit FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                    if(!empty($service_data)){                        
                        $service_duration      = $service_data['bookingpress_service_duration_val'];
                        $service_unit_duration = $service_data['bookingpress_service_duration_unit'];
                    }                    
                }

                $service_mins = $service_duration;
                if ($service_unit_duration == 'h' ) {
                    $service_mins = $service_duration * 60;
                }

                $service_end_time_obj = new DateTime($service_start_time);
                $service_end_time_obj->add(new DateInterval('PT' . $service_mins . 'M'));
                $service_end_time = $service_end_time_obj->format('H:i');

                return array(
                'service_start_time' => $service_start_time,
                'service_end_time'   => $service_end_time,
                );

            }

            return array();
        }

        
        /**
         * Get default timeslot details
         *
         * @return void
         */
        public function bookingpress_get_default_timeslot_data()
        {
            $bookingpress_default_timeslot_data = esc_html($this->bookingpress_get_settings('default_time_slot_step', 'general_setting'));
            $bookingpress_default_timeslot_data = ! empty($bookingpress_default_timeslot_data) ? esc_html($bookingpress_default_timeslot_data) : 60;
            $time_duration  = $bookingpress_time_duration= $bookingpress_default_timeslot_data;
            $time_unit = 'm';
            
            if ($time_duration >= 60 ) {
                $time_duration = ( $time_duration / 60 );                
                if(is_int($time_duration) == false) {
                    $time_duration = $bookingpress_time_duration;
                } else {               
                    $time_unit     = 'h';
                }    
            }
            return array(
            'time_duration'    => $time_duration,
            'time_unit'        => $time_unit,
            'default_timeslot' => $bookingpress_default_timeslot_data,
            );
        }

        public function bookingpress_retrieve_off_days( $start_date = '', $number_of_days = 366, $selected_service = '', $selected_service_duration = '', $selected_staffmember = '' ){

            global $wpdb, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_default_workhours, $BookingPress;

            $current_date = date( 'Y-m-d', current_time('timestamp') );

            $end_date = date('Y-m-d', strtotime($start_date . '+' . $number_of_days.' day'));

            $retrieve_default_holidays = array(
                'offdays' => array(),
                'exclude_offdays' => array()
            );

            $retrieve_default_holidays = apply_filters( 'bookingpress_modify_default_holidays', $retrieve_default_holidays, $selected_service, $selected_service_duration, $selected_staffmember );
            

            /** checking the holidays from the start date */
            $bpa_default_daysoff_where = $wpdb->prepare( 'WHERE (bookingpress_dayoff_date >= %s OR bookingpress_repeat = %d)', $start_date .' 00:00:00',1);

            $exclude_days = !empty( $retrieve_default_holidays['exclude_offdays'] ) ? $retrieve_default_holidays['exclude_offdays'] : array();
            

            /** if dates are available in exclusion, then add a where clause with those dates */
            if( !empty( $exclude_days ) ){

                $bpa_default_daysoff_where .= ' AND bookingpress_dayoff_date NOT IN ( ';
                $bpa_default_daysoff_where .= rtrim( str_repeat( '%s,', count( $exclude_days ) ), ',' ) . ' )';

                array_unshift( $exclude_days, $bpa_default_daysoff_where );
                $bpa_default_daysoff_where = call_user_func_array( array( $wpdb, 'prepare' ), $exclude_days );

            }

            $daysoff_details = $wpdb->get_results("SELECT bookingpress_dayoff_date,bookingpress_repeat FROM {$tbl_bookingpress_default_daysoff} {$bpa_default_daysoff_where}", ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False alarm

            $default_off_dates = array();
            $repeative_off_dates = array();

            if( !empty( $retrieve_default_holidays['offdays'] ) ){    
                $daysoff_details = array_merge( $daysoff_details, $retrieve_default_holidays['offdays']);
            }

            if( !empty( $daysoff_details ) ){
                $total_daysoff_details = count( $daysoff_details );
                $daysoff_counter = 0;
                while( 0 < $total_daysoff_details ){
                    
                    $holidays_data = $daysoff_details[ $daysoff_counter ];

                    $default_off_dates[] = date('Y-m-d', strtotime( $holidays_data['bookingpress_dayoff_date'] ) );
                    if( 1 == $holidays_data['bookingpress_repeat'] ){
                        $repeative_off_dates[] = date( 'm-d', strtotime( $holidays_data['bookingpress_dayoff_date'] ) );
                    }

                    $total_daysoff_details--;
                    $daysoff_counter++;
                }
            }

            $default_off_days = array(
                'skip_check' => false,
                'off_days' => array()
            );
            $default_off_days = apply_filters( 'bookingpress_modify_default_off_days', $default_off_days, $selected_service, $selected_service_duration, $selected_staffmember );
            
            if( false == $default_off_days['skip_check'] ){
                $bookingpress_off_days_data = $wpdb->get_results( $wpdb->prepare( "SELECT LOWER(bookingpress_workday_key) AS bookingpress_workday_key FROM {$tbl_bookingpress_default_workhours} WHERE (bookingpress_start_time IS NULL OR ( ABS( TIME_TO_SEC( TIMEDIFF( bookingpress_start_Time, ( CASE WHEN bookingpress_end_time = '00:00:00' THEN '24:00:00' ELSE bookingpress_end_time END ) ) ) DIV 60 ) < %d ) ) AND bookingpress_is_break = %d", $selected_service_duration, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm

                if( !empty( $bookingpress_off_days_data ) ){
                    foreach( $bookingpress_off_days_data as $bpa_default_workdata ){
                        $default_off_days['off_days'][] = $bpa_default_workdata['bookingpress_workday_key'];
                    }
                }
            } else {

            }


            $bpa_begin_date = new DateTime( $start_date );
            $bpa_end_date = new DateTime( $end_date );

            $bpa_interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($bpa_begin_date, $bpa_interval, $bpa_end_date);

            $default_daysoff_dates = array();

            foreach( $period as $dt ){
                $weekday_name = $dt->format( 'l' );

                if( ( ( !empty( $exclude_days ) && ( in_array( $dt->format('Y-m-d H:i:s'), $exclude_days ) || in_array( $dt->format('Y-m-d'), $exclude_days ) ) ) ) ){
                    continue;
                }

                if( in_array( strtolower( $weekday_name ), $default_off_days['off_days'] ) ){
                    $default_daysoff_dates[] = $dt->format( 'Y-m-d H:i:s' );
                }
                
                if( in_array( $dt->format('Y-m-d'), $default_off_dates ) ){
                    $default_daysoff_dates[] = $dt->format( 'Y-m-d H:i:s' );
                }

                if( in_array( $dt->format( 'm-d' ), $repeative_off_dates ) ){
                    $default_daysoff_dates[] = $dt->format( 'Y-m-d H:i:s' );
                }
            }

            

            if( $start_date > $current_date ){
                $bpa_check_end_date = new DateTime( $current_date );

                $bpa_interval = DateInterval::createFromDateString( '1 day' );
                $period = new DatePeriod( $bpa_check_end_date, $bpa_interval, $bpa_begin_date );

                foreach( $period as $dt ){
                    $default_daysoff_dates[] = $dt->format( 'Y-m-d H:i:s' );
                }
            }

            
            return $default_daysoff_dates;
        }
        
        /**
         * Get default daysoff dates
         *
         * @param  mixed $booking_date
         * @param  mixed $booking_time
         * @param  mixed $bookingpress_selected_service
         * @param  mixed $bookingpress_selected_staffmember_id
         * @return void
         */
        public function bookingpress_get_default_dayoff_dates($booking_date = '', $booking_time = '', $bookingpress_selected_service = '',$bookingpress_selected_staffmember_id = '')
        {
            global $wpdb, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_default_workhours;

            $bpa_default_hours_with_nobreak = wp_cache_get( 'bookingpress_default_workhours_without_break' );
            if( false === $bpa_default_hours_with_nobreak ){
                $bookingpress_workhours_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_is_break = 0", ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False alarm
                wp_cache_set( 'bookingpress_default_workhours_without_break', $bookingpress_workhours_data );
            } else {
                $bookingpress_workhours_data = $bpa_default_hours_with_nobreak;
            }
            
            $is_monday_break             = 0;
            $is_tuesday_break            = 0;
            $is_wednesday_break          = 0;
            $is_thursday_break           = 0;
            $is_friday_break             = 0;
            $is_saturday_break           = 0;
            $is_sunday_break             = 0;

            foreach ( $bookingpress_workhours_data as $workhour_key => $workhour_val ) {
                $bookingpress_start_time = $workhour_val['bookingpress_start_time'];
                $bookingpress_end_time   = $workhour_val['bookingpress_end_time'];
                if ($workhour_val['bookingpress_workday_key'] == 'monday' && ( $bookingpress_start_time == null || $bookingpress_end_time == null ) ) {
                    $is_monday_break = 1;
                } elseif ($workhour_val['bookingpress_workday_key'] == 'tuesday' && ( $bookingpress_start_time == null || $bookingpress_end_time == null ) ) {
                    $is_tuesday_break = 1;
                } elseif ($workhour_val['bookingpress_workday_key'] == 'wednesday' && ( $bookingpress_start_time == null || $bookingpress_end_time == null ) ) {
                    $is_wednesday_break = 1;
                } elseif ($workhour_val['bookingpress_workday_key'] == 'thursday' && ( $bookingpress_start_time == null || $bookingpress_end_time == null ) ) {
                    $is_thursday_break = 1;
                } elseif ($workhour_val['bookingpress_workday_key'] == 'friday' && ( $bookingpress_start_time == null || $bookingpress_end_time == null ) ) {
                    $is_friday_break = 1;
                } elseif ($workhour_val['bookingpress_workday_key'] == 'saturday' && ( $bookingpress_start_time == null || $bookingpress_end_time == null ) ) {
                    $is_saturday_break = 1;
                } elseif ($workhour_val['bookingpress_workday_key'] == 'sunday' && ( $bookingpress_start_time == null || $bookingpress_end_time == null ) ) {
                    $is_sunday_break = 1;
                }
            }

            $break_days = array();
            $break_days['monday'] = $is_monday_break;
            $break_days['tuesday'] = $is_tuesday_break;
            $break_days['wednesday'] = $is_wednesday_break;
            $break_days['thursday'] = $is_thursday_break;
            $break_days['friday'] = $is_friday_break;
            $break_days['saturday'] = $is_saturday_break;
            $break_days['sunday'] = $is_sunday_break;

            // I think we need to consider service time OR staff member time here
            
            $break_days = apply_filters('bookingpress_modify_working_hours', $break_days, $bookingpress_selected_service,$bookingpress_selected_staffmember_id);

            $is_monday_break             = $break_days['monday'];
            $is_tuesday_break            = $break_days['tuesday'];
            $is_wednesday_break          = $break_days['wednesday'];
            $is_thursday_break           = $break_days['thursday'];
            $is_friday_break             = $break_days['friday'];
            $is_saturday_break           = $break_days['saturday'];
            $is_sunday_break             = $break_days['sunday'];



            $default_year            = date('Y', current_time('timestamp'));
            $default_daysoff_details = array();

            $calendar_start_date = $calendar_next_date = date('Y-m-d', current_time('timestamp'));
            $calendar_end_date   = date('Y-m-d', strtotime('+1 year', current_time('timestamp')));
            for ( $i = 1; $i <= 730; $i++ ) {
                $current_day_name = date('l', strtotime($calendar_next_date));
                if ($current_day_name == 'Monday' && $is_monday_break == 1 ) {
                    $daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
                    array_push($default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
                } elseif ($current_day_name == 'Tuesday' && $is_tuesday_break == 1 ) {
                    $daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
                    array_push($default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
                } elseif ($current_day_name == 'Wednesday' && $is_wednesday_break == 1 ) {
                    $daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
                    array_push($default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
                } elseif ($current_day_name == 'Thursday' && $is_thursday_break == 1 ) {
                    $daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
                    array_push($default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
                } elseif ($current_day_name == 'Friday' && $is_friday_break == 1 ) {
                    $daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
                    array_push($default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
                } elseif ($current_day_name == 'Saturday' && $is_saturday_break == 1 ) {
                    $daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
                    array_push($default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
                } elseif ($current_day_name == 'Sunday' && $is_sunday_break == 1 ) {
                    $daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
                    array_push($default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
                }

                $calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
            }
            $bpa_default_daysoff = wp_cache_get( 'bookingpress_default_daysoff' );
            if( false === $bpa_default_daysoff ){
                $daysoff_details = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_default_daysoff}", ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False alarm
                wp_cache_set( 'bookingpress_default_daysoff', $daysoff_details );
            } else {
                $daysoff_details = $bpa_default_daysoff;
            }
            foreach ( $daysoff_details as $daysoff_details_key => $daysoff_details_val ) {
                $daysoff_date = esc_html($daysoff_details_val['bookingpress_dayoff_date']);

                $dayoff_year = date('Y', strtotime($daysoff_date));

                if (empty($daysoff_details_val['bookingpress_repeat']) ) {
                    array_push($default_daysoff_details, date('c', strtotime($daysoff_date)));
                } elseif (! empty($daysoff_details_val['bookingpress_repeat']) ) {
                    for ( $i = $default_year; $i <= 2035; $i++ ) {
                        $daysoff_new_date_month = $i . '-' . date('m-d', strtotime($daysoff_date));
                        array_push($default_daysoff_details, date('c', strtotime($daysoff_new_date_month)));
                    }
                }
            }

			$default_daysoff_details = apply_filters('bookingpress_modify_default_daysoff_details', $default_daysoff_details, $booking_date, $booking_time);

			return $default_daysoff_details;
		}
		
		/**
		 * Check is appointment booked or not
		 *
		 * @param  mixed $service_id
		 * @param  mixed $booking_date
		 * @param  mixed $booking_start_time
		 * @param  mixed $booking_end_time
		 * @param  mixed $appointment_id
		 * @return void
		 */
		public function bookingpress_is_appointment_booked($service_id, $booking_date, $booking_start_time, $booking_end_time,$appointment_id = 0, $prevent_double_booking = false, $appointment_data = array(), $entry_data = array() ){
			global $wpdb, $tbl_bookingpress_default_workhours, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_services;

            $bpa_external_validation_check = apply_filters( 'bookingpress_check_pro_version_booked_appointment', array(), $service_id, $booking_date, $booking_start_time, $booking_end_time, $appointment_id, $prevent_double_booking, $appointment_data, $entry_data );

            if( !empty( $bpa_external_validation_check ) && 1 == $bpa_external_validation_check['prevent_validation_process'] ){
                if( true == $prevent_double_booking ){
                    return $bpa_external_validation_check;
                } else {
                    return $bpa_external_validation_check['response'];
                }
            }

			$is_appointment_booked = 0;

            if($booking_end_time == "00:00"){
                $booking_end_time = "24:00:00";
            }

            $wpdb_query_fun = 'get_var';
            $wpdb_query_col = 'COUNT(bookingpress_appointment_booking_id) AS total';
            if( true == $prevent_double_booking ){
                $wpdb_query_fun = 'get_results';
                $wpdb_query_col = '*';
            }

			//Check shared service timeslot switch enabled or not
			$bookingpress_shared_service_timeslot = $this->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');

			if($bookingpress_shared_service_timeslot == 'true'){

                $where_clause = '';
                $where_clause.= apply_filters( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', $where_clause,$service_id);

                if(!empty($appointment_id)) {
                    $is_appointment_booked = $wpdb->$wpdb_query_fun( $wpdb->prepare( "SELECT {$wpdb_query_col} FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s  AND bookingpress_appointment_booking_id != %d AND ( ( bookingpress_appointment_time >= %s AND bookingpress_appointment_time < %s) OR (bookingpress_appointment_time < %s AND bookingpress_appointment_end_time > %s ) ) AND ( bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s )  $where_clause", $booking_date,$appointment_id, $booking_start_time, $booking_end_time, $booking_end_time, $booking_start_time, '1', '2' ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name
                } else {
                    $is_appointment_booked = $wpdb->$wpdb_query_fun( $wpdb->prepare( "SELECT {$wpdb_query_col} FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s AND ((bookingpress_appointment_time >= %s AND bookingpress_appointment_time < %s) OR (bookingpress_appointment_time < %s AND bookingpress_appointment_end_time > %s ) ) AND ( bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s ) $where_clause", $booking_date, $booking_start_time, $booking_end_time, $booking_end_time, $booking_start_time, '1', '2' ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name
                }
			}else{
                $where_clause = '';
                $where_clause = apply_filters( 'bookingpress_booked_appointment_where_clause', $where_clause );

                $where_clause .= $wpdb->prepare( ' AND ( bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s )', '1', '2' );

                if(!empty($appointment_id)) {
                    $is_appointment_booked = $wpdb->$wpdb_query_fun( $wpdb->prepare( "SELECT {$wpdb_query_col} FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s AND bookingpress_appointment_booking_id != %d AND bookingpress_service_id = %d AND ( ( bookingpress_appointment_time >= %s AND bookingpress_appointment_time < %s) OR (bookingpress_appointment_time < %s AND bookingpress_appointment_end_time > %s ) ) $where_clause", $booking_date,$appointment_id,$service_id, $booking_start_time, $booking_end_time, $booking_end_time, $booking_start_time ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name
                } else {
                    $is_appointment_booked = $wpdb->$wpdb_query_fun( $wpdb->prepare( "SELECT {$wpdb_query_col} FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s AND bookingpress_service_id = %d AND ( ( bookingpress_appointment_time >= %s AND bookingpress_appointment_time < %s) OR (bookingpress_appointment_time < %s AND bookingpress_appointment_end_time > %s ) ) $where_clause", $booking_date, $service_id, $booking_start_time, $booking_end_time, $booking_end_time, $booking_start_time ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name
                }
			}

            /* $bookingpress_is_appointment_cancelled = $this->bookingpress_is_appointment_cancelled_or_rejected($service_id, $booking_date, $booking_start_time, $booking_end_time);
            if($bookingpress_is_appointment_cancelled){
                $is_appointment_booked = 0;
            } */
            
			return $is_appointment_booked;
		}
        
        /**
         * Check is appointment cancelled or rejected or not
         *
         * @param  mixed $service_id
         * @param  mixed $booking_date
         * @param  mixed $booking_start_time
         * @param  mixed $booking_end_time
         * @return void
         */
        public function bookingpress_is_appointment_cancelled_or_rejected($service_id, $booking_date, $booking_start_time, $booking_end_time){
            global $wpdb, $tbl_bookingpress_default_workhours, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_services;
            $bookingpress_shared_service_timeslot = $this->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');                        

            $bookingpress_is_appointment_canceled = 0;

			if($bookingpress_shared_service_timeslot == 'true'){                
                $where_clause = '';
                $where_clause.= apply_filters( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', $where_clause,$service_id);

                $check_timeslot_canceled_data = $wpdb->get_row($wpdb->prepare("SELECT `bookingpress_appointment_booking_ref` FROM {$tbl_bookingpress_payment_logs} WHERE 1 = 1 $where_clause AND  bookingpress_appointment_date LIKE %s AND ((bookingpress_appointment_start_time >= %s AND bookingpress_appointment_start_time < %s) OR (bookingpress_appointment_start_time < %s AND bookingpress_appointment_end_time > %s ) ) ORDER BY bookingpress_appointment_booking_ref DESC", "%{$booking_date}%", $booking_start_time, $booking_end_time, $booking_end_time, $booking_start_time ),ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
            } else {                
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
                $check_timeslot_canceled_data = $wpdb->get_row($wpdb->prepare("SELECT `bookingpress_appointment_booking_ref` FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_service_id = %d AND ((bookingpress_appointment_start_time >= %s  AND bookingpress_appointment_start_time < %s) OR (bookingpress_appointment_start_time < %s AND bookingpress_appointment_end_time > %s ) ) ORDER BY bookingpress_appointment_booking_ref DESC", "%{$booking_date}%",$service_id,$booking_start_time, $booking_end_time, $booking_end_time, $booking_start_time ),ARRAY_A ); 
            }     
            
            if (! empty($check_timeslot_canceled_data) ) {
                $appointment_id                = $check_timeslot_canceled_data['bookingpress_appointment_booking_ref'];
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                $bookingpress_appointment_data = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d AND ( bookingpress_appointment_status = '3' OR bookingpress_appointment_status = '4')", $appointment_id));
                
                if ($bookingpress_appointment_data > 0 ) {
                    $bookingpress_is_appointment_canceled = 1;
                }
            }

            return $bookingpress_is_appointment_canceled;
        }

         // 04May 2023 Changes
        function bookingpress_convert_timezone_to_offset( $timezone_string, $bookingpress_timezone = '' ){

            global $bookingpress_global_options;

            $bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_timezone_offset = $bookingpress_options['bookingpress_timezone_offset'];

            $bookingpress_exceptional_timezone = ['America/St_Johns','Australia/Adelaide', 'Australia/Broken_Hill', 'Pacific/Chatham'];
            $bookingpress_exceptional_timezone = apply_filters( 'bookingpress_exclude_timezone_converstion_to_offset', $bookingpress_exceptional_timezone );

            if( in_array( $timezone_string, $bookingpress_exceptional_timezone ) ){
                return $timezone_string;
            }
            
            if( 'UTC' == $timezone_string){
                return '+00:00';
            } else if( array_key_exists( $timezone_string, $bookingpress_timezone_offset ) ){
            
                $wp_timezone_data = new DateTimeZone($timezone_string);
                $wp_timezone_dtls = $wp_timezone_data->getTransitions();
                
                $wp_timezone_current = array();
                foreach( $wp_timezone_dtls as $k => $wp_timezone_detail ){
                    if( current_time( 'timestamp' ) < $wp_timezone_detail['ts'] ){
                        $wp_timezone_current[] = $wp_timezone_dtls[ $k - 1 ];
                    }
                }
                
                $wp_curr_timezone_data = !empty( $wp_timezone_current[0] ) ? $wp_timezone_current[0] : array( 'offset' => '' );

                $wp_offset = ( $wp_curr_timezone_data['offset'] !== "" ) ? ( $wp_curr_timezone_data['offset'] / ( 60 * 60 ) ) : $bookingpress_timezone_offset[ $timezone_string ];
                if( $wp_curr_timezone_data['offset'] !== '' ){

                    if( $wp_offset < 0 ){
                        if( $wp_offset > -10 ){
                            $wp_offset = '-0' . abs( $wp_offset ) . ":00";
                        } else {
                            $wp_offset = $wp_offset . ":00";
                        }
                    } else {

                        $decimal = $wp_offset;

                        $hours = floor($decimal);
                        $minutes = round(($decimal - $hours) * 60);

                        $wp_offset = sprintf("+%02d:%02d", $hours, $minutes);

                    }
                    $timezone_string = $wp_offset;
                } else {
                    $timezone_string = $wp_offset;
                }
            }

            return $timezone_string;
        }
        // 04May 2023 Changes


        function bookingpress_calculate_default_workhours( $service_timing_arr, $workhours_break_data ){

            $slot_start_time = $service_timing_arr['store_start_time'];
			$slot_end_time = $service_timing_arr['store_end_time'];

            foreach( $workhours_break_data as $blocked_time ){
                $blocked_start_time = $blocked_time['start_time'];
                $blocked_end_time = $blocked_time['end_time'];

                if( ( $blocked_start_time >= $slot_start_time && $blocked_end_time <= $slot_end_time ) || ( $blocked_start_time < $slot_end_time && $blocked_end_time > $slot_start_time) ){
                    $service_timing_arr['is_blocked'] = true;
                    $service_timing_arr['break_time_start'] = $blocked_start_time;
                    $service_timing_arr['break_end_time'] = $blocked_end_time;
                }

            }

            return $service_timing_arr;

        }

        function bookingpress_check_for_the_holidays( $selected_date = '', $selected_service_id = '', $type = 'company' ){
            global $tbl_bookingpress_default_daysoff, $wpdb;

            $is_holiday = false;

            if( empty( $selected_date ) ){
                return $is_holiday;
            }

            if( 'company' == $type || empty( $type ) ){
                /** Check if selected days is in holiday */
                $bpa_default_daysoff_where = $wpdb->prepare( 'WHERE (bookingpress_dayoff_date >= %s OR bookingpress_repeat = %d)', $selected_date .' 00:00:00',1);
                $daysoff_details = $wpdb->get_results("SELECT bookingpress_dayoff_date,bookingpress_repeat FROM {$tbl_bookingpress_default_daysoff} {$bpa_default_daysoff_where}");// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False alarm
    
                $is_holiday = false;
                if( !empty( $daysoff_details ) ){
                    foreach( $daysoff_details as $daysoff_data ){
                        if( $daysoff_data->bookingpress_dayoff_date == $selected_date .' 00:00:00' ){
                            $service_timings_data['is_daysoff'] = true;
                            $is_holiday = true;
                        } else if( $daysoff_data->bookingpress_repeat == 1 ){
                            $current_date_without_year = date('m-d', strtotime( $selected_date ) );
                            $holiday_date_without_year = date('m-d', strtotime( $daysoff_data->bookingpress_dayoff_date ) );
                            if( $holiday_date_without_year == $current_date_without_year ){
                                $service_timings_data['is_daysoff'] = true;
                                $is_holiday = true;
                            }
                        }
                    }
                }
            }

            $is_holiday = apply_filters( 'bookingpress_check_advance_repeated_default_holidays_'. $type, $is_holiday, $selected_date, $selected_service_id );

            return $is_holiday;

        }

        function bookinpgress_retrieve_default_workhours( $selected_service_id, $selected_date, $minimum_time_required, $service_max_capacity, $bookingpress_show_time_as_per_service_duration ){

            $service_timings = array();

            global $wpdb, $tbl_bookingpress_default_workhours, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_services, $tbl_bookingpress_default_daysoff;

            $current_day  = ! empty( $selected_date ) ? strtolower( date( 'l', strtotime( $selected_date ) ) ) : strtolower( date( 'l', current_time( 'timestamp' ) ) );
            $current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));

            $bpa_current_date = date('Y-m-d', current_time('timestamp'));
            $bpa_current_time = date( 'H:i', current_time('timestamp') );

            $service_time_duration     = $this->bookingpress_get_default_timeslot_data();
            $default_timeslot_step = $service_step_duration_val = $service_time_duration['default_timeslot'];

            $bookingpress_current_time_timestamp = current_time('timestamp');
            $service_time_duration_unit = $service_time_duration['time_unit'];            
            
            if (! empty($selected_service_id) ) {
                $service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_services is a table name
                if (! empty($service_data) ) {
                    $service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
                    $service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
                    if ($service_time_duration_unit == 'h' ) {
                        $service_time_duration = $service_time_duration * 60;
                    } elseif($service_time_duration_unit == 'd') {           
                        $service_time_duration = $service_time_duration * 24 * 60;
                    }
                    $default_timeslot_step = $service_step_duration_val = $service_time_duration;
                }
            }

            $bpa_fetch_updated_slots = false;
            if( isset( $_POST['bpa_fetch_data'] ) && 'true' == sanitize_text_field($_POST['bpa_fetch_data'] )){  // phpcs:ignore
                $bpa_fetch_updated_slots = true;
            }

            $service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );

            $bookingpress_show_time_as_per_service_duration = $this->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );
            if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
                $bookingpress_default_time_slot = $this->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
                $default_timeslot_step      = $bookingpress_default_time_slot;
            } else {
                $default_timeslot_step = $service_step_duration_val;
            }

            $get_default_work_hours_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 0 AND bookingpress_start_time IS NOT NULL", $current_day), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm

            $workhour_data = array();
            $workhours_break_data = array();

            $is_holiday = $this->bookingpress_check_for_the_holidays( $selected_date, $selected_service_id );

            if( true == $is_holiday ){
                return $service_timings;
            }

            $get_default_work_hous_break_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 1 AND bookingpress_start_time IS NOT NULL", $current_day), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm

            if( !empty( $get_default_work_hous_break_data )  ){
                foreach( $get_default_work_hous_break_data as $default_workhour_data ){
                    $break_data = array(
                        'break_start_time' => date( 'H:i', strtotime( $default_workhour_data['bookingpress_start_time'] ) ),
                        'break_end_time' => date('H:i', strtotime( $default_workhour_data['bookingpress_end_time'] ) )
                    );
                    $workhours_break_data[]  = $break_data;
                }
            }
            

            if( !empty( $get_default_work_hours_data ) ){
                $service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($get_default_work_hours_data['bookingpress_start_time'])), $selected_service_id );
                $service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($get_default_work_hours_data['bookingpress_end_time'])), $selected_service_id );

                if($service_end_time == "00:00"){
                    $service_end_time = "24:00";
                }

                if ($service_start_time != null && $service_end_time != null ) {  
                    $interval = new DateInterval( 'PT'. $default_timeslot_step .'M' );
                    $startDateTime = new DateTime( $selected_date .' '.$service_start_time, new DateTimeZone( wp_timezone_string() ) );
                    $endDateTime = new DateTime( $selected_date .' '.$service_end_time, new DateTimeZone( wp_timezone_string() ) );

                    $tc = 0;
                    while( $startDateTime <= $endDateTime ){
                        
                        $slotStart = $startDateTime;
                        $slotEnd = clone $slotStart;
                        $slotEnd->add( new DateInterval( 'PT'.$service_step_duration_val . 'M' ) );
                    
                        $service_tmp_current_time = $service_current_time = $slotStart->format('H:i');
                    
                        if ($service_current_time == '00:00' ) {
                            $service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
                        } else {
                            $service_tmp_time_obj = new DateTime($selected_date.' '.$service_current_time);
                            $service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
                            
                            $service_current_time = $service_tmp_time_obj->format('H:i');
                            $service_current_date = $service_tmp_time_obj->format('Y-m-d');
                            if( $service_current_date > $selected_date ){
                                if( $service_end_time == '24:00' && strtotime($service_current_date.' '.$service_current_time) > strtotime( $service_current_date . ' 00:00' ) ){
                                    break;
                                }
                            }
                        }
                    
                        if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
                            $service_current_time = $service_end_time;
                        }
                    
                        $bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);
                        $is_booked_for_minimum = false;
                    
                        if( 'disabled' != $minimum_time_required ){
                            $bookingpress_slot_start_datetime       = $selected_date . ' ' . $service_tmp_current_time . ':00';
                            $bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
                            $bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );
                            
                            if( $bookingpress_time_diff <= $minimum_time_required ){
                                $is_booked_for_minimum = true;
                            }
                        }
                    
                        
                        if( ($bookingpress_timediff_in_minutes >= $service_step_duration_val) && $service_current_time <= $service_end_time && $slotEnd <= $endDateTime ){
                            if( strtotime( $selected_date .' ' . $service_tmp_current_time ) > current_time( 'timestamp' ) && !$is_booked_for_minimum ){
                                $is_break = false;
                                if( !empty( $workhours_break_data ) ){
                                    foreach( $workhours_break_data as $break_hour_data ){
                                        $break_start_time = new DateTime( $selected_date .' '. $break_hour_data['break_start_time'], new DateTimeZone( wp_timezone_string() ) );
                                        $break_end_time = new DateTime( $selected_date .' '. $break_hour_data['break_end_time'], new DateTimeZone( wp_timezone_string() ) );

                                        if( ($break_start_time >= $slotStart && $break_end_time <= $slotEnd ) || ( $break_start_time < $slotEnd && $break_end_time > $slotStart ) ){
                                            $is_break = true;
                                            break;
                                        }
                                    }
                                }
                    
                                if( true == $is_break ){
                                    $startDateTime = $break_end_time;
                                    continue;
                                }

                                $service_timing_arr = array(
                                    'start_time' => $service_tmp_current_time,
                                    'end_time'   => $service_current_time,
                                    'store_start_time' => $service_tmp_current_time,
                                    'store_end_time'   => $service_current_time,
                                    'break_start_time' => !empty( $break_start_time ) ? $break_start_time->format('H:i:s') : '',
                                    'break_end_time' => !empty( $break_end_time ) ? $break_end_time->format('H:i:s') : '',
                                    'store_service_date' => $selected_date,
                                    'is_next_day' => false,
                                    'is_booked'  => 0,
                                    'max_capacity' => $service_max_capacity,
                                    'counter_pos' => $tc,
                                    'total_booked' => 0
                                );
                                $workhour_data[] = $service_timing_arr;
                            }
                        }
                    
                        $startDateTime->add( $interval );
                        $tc++;
                    }
                    $service_timings = $workhour_data;
                }
            }

            return $service_timings;

        }

        /**
         * Core function to get default workhours
         *
         * @param  mixed $selected_service_id
         * @param  mixed $selected_date
         * @param  mixed $minimum_time_required
         * @param  mixed $service_max_capacity
         * @param  mixed $bookingpress_show_time_as_per_service_duration
         * @return void
         */
        public function bookinpgress_retrieve_default_workhours_legacy( $selected_service_id, $selected_date, $minimum_time_required, $service_max_capacity, $bookingpress_show_time_as_per_service_duration ){
            // phpcs:ignore WordPress.Security.NonceVerification
            $service_timings = array();

            global $wpdb, $tbl_bookingpress_default_workhours, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_services, $tbl_bookingpress_default_daysoff;

            $bookingpress_hide_already_booked_slot = $this->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
            $bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

            $bookingpress_timezone = isset($_POST['client_timezone_offset']) ? sanitize_text_field( $_POST['client_timezone_offset'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

            $current_day  = ! empty( $selected_date ) ? strtolower( date( 'l', strtotime( $selected_date ) ) ) : strtolower( date( 'l', current_time( 'timestamp' ) ) );
            $current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));

            $bpa_current_date = date('Y-m-d', current_time('timestamp'));

            $bookingpress_timeslot_display_in_client_timezone = $this->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );
			$display_slots_in_client_timezone = false;

             // 04May 2023 Changes
            $client_timezone_string = !empty( $_COOKIE['bookingpress_client_timezone'] ) ? sanitize_text_field($_COOKIE['bookingpress_client_timezone']) : '';
            if( 'true' == $bookingpress_timeslot_display_in_client_timezone && !empty( $client_timezone_string ) ){
                $client_timezone_offset = $this->bookingpress_convert_timezone_to_offset( $client_timezone_string, $bookingpress_timezone );
                $wordpress_timezone_offset = $this->bookingpress_convert_timezone_to_offset( wp_timezone_string() );                
                if( $client_timezone_offset  == $wordpress_timezone_offset ){
                    $bookingpress_timeslot_display_in_client_timezone = 'false';
                }
            }
             // 04May 2023 Changes

            if( isset($bookingpress_timezone) && '' !== $bookingpress_timezone && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true') ){
                $display_slots_in_client_timezone = true;
            }

            if( strtotime( $bpa_current_date ) > strtotime( $selected_date ) && false == $display_slots_in_client_timezone ){
                return $service_timings;
            }

            $change_store_date = ( !empty( $_POST['bpa_change_store_date'] ) && 'true' == sanitize_text_field( $_POST['bpa_change_store_date'] ) ) ? true : false; //phpcs:ignore
            
            $bpa_current_time = date('H:i', current_time('timestamp'));
            $service_time_duration     = $this->bookingpress_get_default_timeslot_data();
            $default_timeslot_step = $service_step_duration_val = $service_time_duration['default_timeslot'];
            

            $bookingpress_current_time_timestamp = current_time('timestamp');
            $service_time_duration_unit = $service_time_duration['time_unit'];
            
			
			if (! empty($selected_service_id) ) {
				$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_services is a table name
				if (! empty($service_data) ) {
					$service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
					$service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
					if ($service_time_duration_unit == 'h' ) {
						$service_time_duration = $service_time_duration * 60;
					} elseif($service_time_duration_unit == 'd') {           
						$service_time_duration = $service_time_duration * 24 * 60;
					}
					$default_timeslot_step = $service_step_duration_val = $service_time_duration;
				}
			}
            

            $bpa_fetch_updated_slots = false;
            if( isset( $_POST['bpa_fetch_data'] ) && 'true' == sanitize_text_field($_POST['bpa_fetch_data'] )){  // phpcs:ignore
                $bpa_fetch_updated_slots = true;
            }

            $service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );
            
			$bookingpress_show_time_as_per_service_duration = $this->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );
            if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
                $bookingpress_default_time_slot = $this->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
                $default_timeslot_step      = $bookingpress_default_time_slot;
            } else {
                $default_timeslot_step = $service_step_duration_val;
            }
            
            $get_default_work_hours_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 0 AND bookingpress_start_time IS NOT NULL", $current_day), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
            
            $workhour_data = array();
            $workhours_break_data = array();

            /** Check if selected days is in holiday */
            $bpa_default_daysoff_where = $wpdb->prepare( 'WHERE (bookingpress_dayoff_date >= %s OR bookingpress_repeat = %d)', $selected_date .' 00:00:00',1);
            $daysoff_details = $wpdb->get_results("SELECT bookingpress_dayoff_date,bookingpress_repeat FROM {$tbl_bookingpress_default_daysoff} {$bpa_default_daysoff_where}");// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False alarm

            $is_holiday = false;
            if( !empty( $daysoff_details ) ){
                foreach( $daysoff_details as $daysoff_data ){
                    if( $daysoff_data->bookingpress_dayoff_date == $selected_date .' 00:00:00' ){
                        $service_timings_data['is_daysoff'] = true;
                        $is_holiday = true;
                    } else if( $daysoff_data->bookingpress_repeat == 1 ){
                        $current_date_without_year = date('m-d', strtotime( $selected_date ) );
                        $holiday_date_without_year = date('m-d', strtotime( $daysoff_data->bookingpress_dayoff_date ) );
                        if( $holiday_date_without_year == $current_date_without_year ){
                            $service_timings_data['is_daysoff'] = true;
                            $is_holiday = true;
                        }
                    }
                }
            }

            $is_holiday = apply_filters( 'bookingpress_check_advance_repeated_default_holidays', $is_holiday, $selected_date );

            if( $is_holiday ){
                return $service_timings_data;
            }

            $get_default_work_hous_break_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 1 AND bookingpress_start_time IS NOT NULL", $current_day), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm

            if( !empty( $get_default_work_hous_break_data )  ){
                foreach( $get_default_work_hous_break_data as $default_workhour_data ){
                    $break_data = array(
                        'start_time' => date( 'H:i', strtotime( $default_workhour_data['bookingpress_start_time'] ) ),
                        'end_time' => date('H:i', strtotime( $default_workhour_data['bookingpress_end_time'] ) )
                    );

                    $workhours_break_data[]  = $break_data;
                }
            }
            
            if( !empty( $get_default_work_hours_data ) ){
                $service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($get_default_work_hours_data['bookingpress_start_time'])), $selected_service_id );
                $service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($get_default_work_hours_data['bookingpress_end_time'])), $selected_service_id );

                if($service_end_time == "00:00"){
                    $service_end_time = "24:00";
                }

                if ($service_start_time != null && $service_end_time != null ) {                    

                    while ( $service_current_time <= $service_end_time ) {
                        if ($service_current_time > $service_end_time ) {
                            break;
                        }

                        $service_tmp_date_time = $selected_date .' '.$service_current_time;
                        
                        $service_tmp_end_time = date( 'Y-m-d', ( strtotime($selected_date. ' ' . $service_current_time ) + ( $service_step_duration_val * 60 ) ) );

                        if( $service_tmp_end_time > $selected_date ){
                            if( $service_step_duration_val > 1440 && $service_time_duration_unit != 'd' ){
                                break;
                            }
                        }
                        
                        $service_tmp_current_time = $service_current_time;
                        if ($service_current_time == '00:00' ) {
                            $service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
                        } else {
                            $service_tmp_time_obj = new DateTime($selected_date.' '.$service_current_time);
                            $service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
                            
                            $service_current_time = $service_tmp_time_obj->format('H:i');
                            $service_current_date = $service_tmp_time_obj->format('Y-m-d');
                            if( $service_current_date > $selected_date ){
                                if( $service_end_time == '24:00' && strtotime($service_current_date.' '.$service_current_time) > strtotime( $service_current_date . ' 00:00' ) ){
                                    break;
                                }
							}
                            
                        }

                        $break_start_time      = '';
                        $break_end_time        = '';

                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm

                        $is_appointment_booked = 0;
                        if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
                            $service_current_time = $service_end_time;
                        }

                        $bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);
                        $is_booked_for_minimum = false;
                        
                        if( 'disabled' != $minimum_time_required ){
							$bookingpress_slot_start_datetime       = $selected_date . ' ' . $service_tmp_current_time . ':00';
							$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
							$bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );
							
							if( $bookingpress_time_diff <= $minimum_time_required ){
                                $is_booked_for_minimum = true;
							}
						}

                        if ($break_start_time != $service_tmp_current_time && ($bookingpress_timediff_in_minutes >= $service_step_duration_val) && $service_current_time <= $service_end_time ) {
                            if ($bpa_current_date == $current_date ) {
                                if ($service_tmp_current_time > $bpa_current_time && !$is_booked_for_minimum ) {
                                    $service_timing_arr = array(
                                        'start_time' => $service_tmp_current_time,
                                        'end_time'   => $service_current_time,
                                        'store_start_time' => $service_tmp_current_time,
                                        'store_end_time'   => $service_current_time,
                                        'break_start_time' => $break_start_time,
                                        'break_end_time' => $break_end_time,
                                        'store_service_date' => $selected_date,
                                        'is_booked'  => 0,
                                        'max_capacity' => $service_max_capacity,
                                        'total_booked' => 0
                                    );

                                    if( !empty( $workhours_break_data ) ){
                                        $service_timing_arr = apply_filters( 'bpa_calculate_default_break_hours', $service_timing_arr, $workhours_break_data );
                                    }

                                    if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
                                        $service_current_time = $service_timing_arr['break_end_time'];
                                        continue;
                                    }

                                    if( $display_slots_in_client_timezone ){

                                        $booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
                                        $booking_timeslot_end = $selected_date .' '.$service_current_time.':00';
                                        
                                        $booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
                                        $booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);

                                        
                                        $service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
                                        $service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

                                        $booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );
                                        if( $change_store_date ) {

                                            $store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
                                            
                                            $service_timing_arr['store_service_date'] = $store_selected_date;
                                            
                                            $store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
                                            if( strtotime( $store_selection_datetime ) < current_time('timestamp' )  || $store_selected_date != $selected_date ){
                                                continue;
                                            }
                                        }

                                        if( $selected_date < $booking_timeslot_start_date){
                                            break;
                                        }
                                    }
                                    $workhour_data[] = $service_timing_arr;
                                }
                            } else {
                                if( !$is_booked_for_minimum ) {
                                    
                                    $service_timing_arr = array(
                                        'start_time'       => $service_tmp_current_time,
                                        'end_time'         => $service_current_time,
                                        'store_start_time'       => $service_tmp_current_time,
                                        'store_end_time'         => $service_current_time,
                                        'break_start_time' => $break_start_time,
                                        'break_end_time'   => $break_end_time,
                                        'store_service_date' => $selected_date,
                                        'is_booked'        => 0,
                                        'max_capacity' => $service_max_capacity,
                                        'total_booked' => 0
                                    );

                                    if( !empty( $workhours_break_data ) ){
                                        $service_timing_arr = apply_filters( 'bpa_calculate_default_break_hours', $service_timing_arr, $workhours_break_data );
                                    }

                                    if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
                                        $service_current_time = $service_timing_arr['break_end_time'];
                                        continue;
                                    }

                                    if( $display_slots_in_client_timezone ){

                                        $booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
                                        $booking_timeslot_end = $selected_date .' '.$service_current_time.':00';
                                        
                                        
                                        $booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
                                        $booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);

                                        
                                        $service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
                                        $service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

                                        $booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );
                                        
                                        if( $change_store_date ) {
                                            $store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
                                        
                                            $service_timing_arr['store_service_date'] = $store_selected_date;
                                            $store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
                                            
                                            if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
                                                continue;
                                            }
                                        }
                                        
                                    }
                                    
                                    $workhour_data[] = $service_timing_arr;
                                }
                            }
                        } else {
                            if($service_current_time >= $service_end_time){
                                break;
                            }
                        }

                        if ( !empty($break_end_time) ) {
                            $service_current_time = $break_end_time;
                        }

                        if ($service_current_time == $service_end_time ) {
                            break;
                        }
                        
                        if($default_timeslot_step != $service_step_duration_val && empty($break_start_time)){

                            $service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_tmp_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
							$service_current_time = $service_tmp_time_obj->format('H:i');
							
							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
							if( $service_current_date > $selected_date ){
								break;
							}
                        }

                    }

                    $service_timings = $workhour_data;
                }
            }
            
            return $service_timings;

        }

        		
		/**
		 * Core function of get timeslots
		 *
		 * @param  mixed $service_id
		 * @param  mixed $selected_date
		 * @param  mixed $bookingpress_timezone
		 * @return void
		 */
		public function bookingpress_get_service_available_time( $service_id = 0, $selected_date = '', $bookingpress_timezone = '' ) {
			global $wpdb, $tbl_bookingpress_default_workhours, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_services;
			
			
			$bookingpress_hide_already_booked_slot = $this->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
			$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

			$current_day  = ! empty( $selected_date ) ? strtolower( date( 'l', strtotime( $selected_date ) ) ) : strtolower( date( 'l', current_time( 'timestamp' ) ) );
            $current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));

			$bpa_current_date = date('Y-m-d', strtotime(current_time('mysql')));

			if(empty($bookingpress_timezone)){
		    	$bpa_current_time = date( 'H:i',strtotime(current_time('mysql')));
			}else{
				$bpa_current_time = date('H:i');
			}

			$default_daysoff_details = $this->bookingpress_get_default_dayoff_dates();
			if ( ! empty( $default_daysoff_details ) ) {
				foreach ( $default_daysoff_details as $key => $value ) {
					if ( date( 'Y-m-d', strtotime( $value ) ) == $current_date ) {
						return array();
						exit;
					}
				}
			}


			$service_time_duration     = $this->bookingpress_get_default_timeslot_data();
			$default_timeslot_step = $service_step_duration_val = $service_time_duration['default_timeslot'];
            
            $service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_services is a table name
            if (! empty($service_data) ) {
                $service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
                $service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
                if ($service_time_duration_unit == 'h' ) {
                    $service_time_duration = $service_time_duration * 60;
                } elseif($service_time_duration_unit == 'd') {           
                    $service_time_duration = $service_time_duration * 24 * 60;
                }
                $default_timeslot_step = $service_step_duration_val = $service_time_duration;
            }

            $bookingpress_show_time_as_per_service_duration = $this->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );
            if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
				$bookingpress_default_time_slot = $this->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
				$default_timeslot_step      = $bookingpress_default_time_slot;
			}
            
            //$service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $service_id, $service_time_duration_unit );

			//Check shared service timeslot switch enabled or not
			//$bookingpress_shared_service_timeslot = $this->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');

            $already_booked_time_arr = $workhour_data = $break_hour_arr = array();
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
            $get_default_work_hours_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 0", $current_day), ARRAY_A);
            if (! empty($get_default_work_hours_data) ) {
                $service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($get_default_work_hours_data['bookingpress_start_time'])), $service_id );
                $service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($get_default_work_hours_data['bookingpress_end_time'])), $service_id );

                if($service_end_time == "00:00"){
                    $service_end_time = "24:00";
                }

                if ($service_start_time != null && $service_end_time != null ) {
                    while ( $service_current_time <= $service_end_time ) {
                        if ($service_current_time > $service_end_time ) {
                            break;
                        }

                        $service_tmp_date_time = $selected_date .' '.$service_current_time;
        
                        $service_tmp_end_time = date( 'Y-m-d', ( strtotime($selected_date. ' ' . $service_current_time ) + ( $service_step_duration_val * 60 ) ) );

                        if( $service_tmp_end_time > $selected_date ){
                            if( $service_step_duration_val > 1440 && $service_time_duration_unit != 'd' ){
                                break;
                            }
                        }
                        
                        $service_tmp_current_time = $service_current_time;
                        if ($service_current_time == '00:00' ) {
                            $service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
                        } else {
                            $service_tmp_time_obj = new DateTime($selected_date .' ' . $service_current_time);
                            $service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
                            $service_current_time = $service_tmp_time_obj->format('H:i');
                            $service_current_date = $service_tmp_time_obj->format('Y-m-d');
                            if( $service_current_date > $selected_date ){
                                if( $service_end_time == '24:00' && strtotime($service_current_date.' '.$service_current_time) > strtotime( $service_current_date . ' 00:00' ) ){
                                    break;
                                }
                            }
                        }

                        $break_start_time      = '';
                        $break_end_time        = '';
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
                        $check_break_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_workhours_id) as total FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 1 AND (bookingpress_start_time BETWEEN %s AND %s)", $current_day, $service_tmp_current_time, $service_current_time));

                        if ($check_break_existance > 0 ) {
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
                            $get_break_workhours = $wpdb->get_row($wpdb->prepare("SELECT TIMEDIFF(bookingpress_end_time, bookingpress_start_time) as time_diff, bookingpress_start_time, bookingpress_end_time FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 1 AND (bookingpress_start_time BETWEEN %s AND %s )", $current_day, $service_tmp_current_time, $service_current_time), ARRAY_A);
                            $time_difference     = date('H:i', strtotime($get_break_workhours['time_diff']));

                            $break_start_time     = date('H:i', strtotime($get_break_workhours['bookingpress_start_time']));
                            $break_end_time       = date('H:i', strtotime($get_break_workhours['bookingpress_end_time']));
                            $service_current_time = $break_start_time;
                        }

                        $is_appointment_booked = 0;
                        if( empty( $_POST['action'] ) || $_POST['action'] != 'bookingpress_get_edit_appointment_data' ){ // phpcs:ignore
                            $is_appointment_booked = $this->bookingpress_is_appointment_booked($service_id, $current_date, $service_tmp_current_time, $service_current_time);
                        }
						
                        $is_already_booked = ( $is_appointment_booked > 0 ) ? 1 : 0;                       
                        if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
                            $service_current_time = $service_end_time;
                        }

                        $bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);

                        if ($is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
                                  continue;
                        } else {
                            if ($break_start_time != $service_tmp_current_time && ($bookingpress_timediff_in_minutes >= $service_step_duration_val) && $service_current_time <= $service_end_time ) {
                                if ($bpa_current_date == $current_date ) {
                                    if ($service_tmp_current_time > $bpa_current_time ) {
                                        $workhour_data[] = array(
                                        'start_time' => $service_tmp_current_time,
                                        'end_time'   => $service_current_time,
                                        'break_start_time' => $break_start_time,
                                        'break_end_time' => $break_end_time,
                                        'is_booked'  => $is_already_booked,
                                        );
                                    } else {
                                        /* $workhour_data[] = array(
                                        'start_time' => $service_tmp_current_time,
                                        'end_time'   => $service_current_time,
                                        'break_start_time' => $break_start_time,
                                        'break_end_time' => $break_end_time,
                                        'is_booked'  => 1,
                                        ); */
                                    }
                                } else {
                                    $workhour_data[] = array(
                                    'start_time'       => $service_tmp_current_time,
                                    'end_time'         => $service_current_time,
                                    'break_start_time' => $break_start_time,
                                    'break_end_time'   => $break_end_time,
                                    'is_booked'        => $is_already_booked,
                                    );
                                }
                            }else{
                                if($service_current_time >= $service_end_time){
                                    break;
                                }
                            }
                        }

                        if (! empty($break_end_time) ) {
                            $service_current_time = $break_end_time;
                        }

                        if ($service_current_time == $service_end_time ) {
                            break;
                        }

                        if($default_timeslot_step != $service_step_duration_val && empty($break_start_time)){
                            $service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_tmp_current_time);
                            $service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
                            $service_current_time = $service_tmp_time_obj->format('H:i');

                            $service_current_date = $service_tmp_time_obj->format('Y-m-d');
                            if( $service_current_date > $selected_date ){
                                break;
                            }
                        }
                    }
                }
            }
            
			//$workhour_data = apply_filters( 'bookingpress_modify_service_time', $workhour_data, $current_date, $current_day, $service_id, $selected_date, $bookingpress_timezone );

			return $workhour_data;
		}

        /**
		 * Core function of get timeslots
		 *
		 * @param  mixed $service_id
		 * @param  mixed $selected_date
		 * @param  mixed $bookingpress_timezone
		 * @return void
		 */
		public function bookingpress_get_daily_timeslots( $appointment_time_slot = array() ) {
			global $bookingpress_global_options;

			$bookingpress_global_details      = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_default_time_format = $bookingpress_global_details['wp_default_time_format'];
            $bookingpress_default_time_format = apply_filters('bookingpress_change_time_slot_format',$bookingpress_default_time_format);
            $morning_time   = array();
            $afternoon_time = array();
            $evening_time   = array();
            $night_time     = array();

            $bookingpress_service_slot_details = array();
            if (! empty($appointment_time_slot) ) {
                foreach ( $appointment_time_slot as $key => $value ) {

                    $service_start_time = date('H', strtotime($value['start_time']));
                    $service_end_time   = date('H', strtotime($value['end_time']));

                    if ($service_start_time >= 0 && $service_start_time < 12 ) {
                        $morning_time[] = array(
                        'start_time'  => $value['start_time'],
						'start_time_format' => date( $bookingpress_default_time_format, strtotime( $value['start_time'] ) ),
                        'end_time'    => $value['end_time'],
						'end_time_format'   => date( $bookingpress_default_time_format, strtotime( $value['end_time'] ) ),
                        'is_disabled' => ( $value['is_booked'] == 1 ) ? true : false,
                        );
                    } elseif ($service_start_time >= 12 && $service_start_time < 16 ) {
                        $afternoon_time[] = array(
                        'start_time'  => $value['start_time'],
						'start_time_format' => date( $bookingpress_default_time_format, strtotime( $value['start_time'] ) ),
                        'end_time'    => $value['end_time'],
						'end_time_format'   => date( $bookingpress_default_time_format, strtotime( $value['end_time'] ) ),
                        'is_disabled' => ( $value['is_booked'] == 1 ) ? true : false,
                        );
                    } elseif ($service_start_time >= 16 && $service_start_time < 20 ) {
                        $evening_time[] = array(
                        'start_time'  => $value['start_time'],
                        'start_time_format' => date( $bookingpress_default_time_format, strtotime( $value['start_time'] ) ),
                        'end_time'          => $value['end_time'],
                        'end_time_format'   => date( $bookingpress_default_time_format, strtotime( $value['end_time'] ) ),
                        'is_disabled' => ( $value['is_booked'] == 1 ) ? true : false,
                        );
                    } else {
                        $night_time[] = array(
                        'start_time'  => $value['start_time'],
						'start_time_format' => date( $bookingpress_default_time_format, strtotime( $value['start_time'] ) ),
                        'end_time'    => $value['end_time'],
						'end_time_format'   => date( $bookingpress_default_time_format, strtotime( $value['end_time'] ) ),
                        'is_disabled' => ( $value['is_booked'] == 1 ) ? true : false,
                        );
                    }
                }
            }

            $bookingpress_service_slot_details['morning_time']   = array(
            'timeslot_label' => esc_html__('Morning', 'bookingpress-appointment-booking'),
            'timeslots'      => $morning_time,
            );
            $bookingpress_service_slot_details['afternoon_time'] = array(
            'timeslot_label' => esc_html__('Afternoon', 'bookingpress-appointment-booking'),
            'timeslots'      => $afternoon_time,
            );
            $bookingpress_service_slot_details['evening_time']   = array(
            'timeslot_label' => esc_html__('Evening', 'bookingpress-appointment-booking'),
            'timeslots'      => $evening_time,
            );
            $bookingpress_service_slot_details['night_time']     = array(
            'timeslot_label' => esc_html__('Night', 'bookingpress-appointment-booking'),
            'timeslots'      => $night_time,
            );

            return $bookingpress_service_slot_details;
        }

        
        /**
         * Get week startdate and end date from week number and year
         *
         * @param  mixed $week_number
         * @param  mixed $year
         * @return void
         */
        public function get_weekstart_date_end_date( $week_number, $year )
        {
            $dto = new DateTime();
            $dto->setISODate($year, $week_number);
            $ret['week_start'] = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $ret['week_end'] = $dto->format('Y-m-d');
            return $ret;
        }
        
        /**
         * Get current month start and end date
         *
         * @return void
         */
        public function get_monthstart_date_end_date()
        {
            $month_start_date = date('Y-m-01');
            $month_end_date   = date('Y-m-t');
            return array(
            'start_date' => $month_start_date,
            'end_date'   => $month_end_date,
            );
        }
        
        /**
         * Fetch all services
         *
         * @param  mixed $service [bookingpress_form service=1]
         * @param  mixed $selected_service [bookingpress_form selected_service=1]
         * @param  mixed $bookingpress_category [bookingpress_form category=1]
         * @return array
         */
        function bookingpress_retrieve_all_services( $service = '', $selected_service = '', $bookingpress_category = 0 ){
            global $wpdb, $tbl_bookingpress_services;
            $bpa_all_services = array();

            $bookingpress_display_no_service_placeholder = false;

            $where_clause = '';
            $consider_where = false;
            $get_s_id = !empty( $_GET['s_id'] ) ? intval( $_GET['s_id'] ) : '';
            $get_s_id = apply_filters( 'bookingpress_modify_s_id_before_retrieving_service', $get_s_id );


            if( !empty( $get_s_id ) && isset( $_GET['allow_modify'] ) && 0 === intval( $_GET['allow_modify'] )){
                $where_clause .= $wpdb->prepare( ' AND bookingpress_service_id = %d', intval( $_GET['s_id'] ) );
                $consider_where = true;
            } else if( !empty( $service ) ){
                $where_clause .= ' AND bookingpress_service_id IN ('.$service.')';
                $consider_where = true;
            } else if ( isset( $bookingpress_category ) && 0 < $bookingpress_category ){
                $where_clause .= ' AND bookingpress_category_id IN ('.$bookingpress_category.')';
                $consider_where = true;
            }

            $where_clause = apply_filters( 'bookingpress_retrieve_service_where_clause', $where_clause, $service, $selected_service, $bookingpress_category );
            if( false == $consider_where && !empty( $where_clause ) ){
                $consider_where = true;
            }

            $db_all_services_without_where = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_services} ORDER BY bookingpress_service_position ASC", ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally.
           
            $db_all_services = array();
            if( !empty( $where_clause ) ){
                $db_all_services = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_services} WHERE 1 = 1 $where_clause ORDER BY bookingpress_service_position ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally.
            }
            if( count( $db_all_services ) == 0 && 0 < count( $db_all_services_without_where ) ){
                $db_all_services = $db_all_services_without_where;
            }

            $bookingpress_display_service_description = $this->bookingpress_get_customize_settings('display_service_description', 'booking_form');

            $min_text = $this->bookingpress_get_customize_settings('book_appointment_min_text', 'booking_form'); 
            $hour_text = $this->bookingpress_get_customize_settings('book_appointment_hours_text', 'booking_form'); 
            $bookingpress_duration_suffix_labels = array(
                'm' => !empty( $min_text ) ? stripslashes_deep($min_text) : esc_html__('m', 'bookingpress-appointment-booking'),
                'h' => !empty( $hour_text ) ? stripslashes_deep($hour_text) : esc_html__('h', 'bookingpress-appointment-booking'),
            );

            if( !empty( $db_all_services ) ){
                foreach( $db_all_services as $skey => $svalue ){
                    $svalue['is_visible'] = true;
                    $svalue['is_visible_with_flag'] = true;
                    $services_meta = $this->bookingpress_get_service_meta( $svalue['bookingpress_service_id'] );
                    $svalue['services_meta'] = $services_meta;

                    $service_price = $svalue['bookingpress_service_price'];

                    $svalue['bookingpress_service_price'] = $this->bookingpress_price_formatter_with_currency_symbol( $service_price );
                    $svalue['service_price_without_currency'] = (float) $service_price;
                    $svalue['bookingpress_service_name'] = stripslashes_deep($svalue['bookingpress_service_name']);        
                    
                    $svalue['bookingpress_service_duration_label'] = !empty( $bookingpress_duration_suffix_labels[ $svalue['bookingpress_service_duration_unit'] ] ) ? $bookingpress_duration_suffix_labels[ $svalue['bookingpress_service_duration_unit'] ] : $svalue['bookingpress_service_duration_unit'];
                    
                    if ($bookingpress_display_service_description == 'false' ) {
                        $svalue['display_read_more_less'] = 1;
                        $default_service_description   = $svalue['bookingpress_service_description'];
                        if (strlen($default_service_description) > 140 ) {
                               $svalue['bookingpress_service_description_with_excerpt'] = substr($default_service_description, 0, 140);
                               $svalue['display_details_more']                          = 0;
                               $svalue['display_details_less']                          = 1;
                        } else {
                            $svalue['display_read_more_less'] = 0;
                        }
                    }

                    if( !empty( $services_meta['service_image_details'] ) ){
                        $svalue['use_placeholder'] = !empty( $services_meta['service_image_details'][0]['url'] ) ? false : true;
                        $svalue['img_url'] = !empty( $services_meta['service_image_details'][0]['url'] ) ? $services_meta['service_image_details'][0]['url'] : BOOKINGPRESS_URL . '/images/placeholder-img.jpg';
                    } else {
                        $svalue['use_placeholder'] = true;
                    }
                    $bpa_all_services[ (string)$svalue['bookingpress_service_id'] ] = $svalue;
                }
            } else {
                $bookingpress_display_no_service_placeholder = true;
            }

            $bpa_all_services = apply_filters( 'bookingpress_modify_all_retrieved_services', $bpa_all_services, $service, $selected_service, $bookingpress_category );

            return $bpa_all_services;
        }
        
        /**
         * Fetch all categories based on the services we have
         *
         * @param  mixed $bpa_all_services
         * @return void
         */
        function bookingpress_retrieve_all_categories( $bpa_all_services ){
            $bpa_all_categories = array();
            global $wpdb, $tbl_bookingpress_categories;

            $get_category_ids = array();
            
            if( !empty( $bpa_all_services ) ){
                foreach( $bpa_all_services as $service_id => $service_data ){
                    $is_disabled = ( !empty( $service_data['is_disabled'] ) && 1 == $service_data['is_disabled'] ) ? true : false;
                    if( 1 == $service_data['is_visible'] && false == $is_disabled){
                        if( !isset( $get_category_ids[ $service_data['bookingpress_category_id'] ]) ){
                            $get_category_ids[ $service_data['bookingpress_category_id'] ] =array(
                                'counter' => 1,
                                'service_ids' => array(
                                    $service_id
                                )
                            );
                        } else {
                            $get_category_ids[ $service_data['bookingpress_category_id'] ]['counter']++;
                            $get_category_ids[ $service_data['bookingpress_category_id'] ]['service_ids'][] = $service_id;
                        }
                    }
                }
            }
            
                       
            $bpa_category_data = array();
            $bpa_uncategorized_data = array();

            $bookingpress_all_category_title = $this->bookingpress_get_customize_settings('all_category_title', 'booking_form');
            $bookingpress_all_category_with_services = array();
            foreach( $get_category_ids as $category_id => $category_ids_data ){
                if( 0 === $category_id ){
                    $bpa_uncategorized_data[0] = array(
                        'category_id' => 0,
                        'category_key' => 'all',
                        'category_name' => stripslashes_deep($bookingpress_all_category_title),
                        'total_services' => $category_ids_data['counter'],
                        'is_visible' => true,
                        'service_ids' => $category_ids_data['service_ids']
                    );
                } else {
                    $category_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_categories} WHERE bookingpress_category_id = %d", $category_id ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally.
                    $bpa_category_data[ $category_data['bookingpress_category_id'] ] = array(
                        'category_id' => intval( $category_data['bookingpress_category_id'] ),
                        'category_key' => $category_data['bookingpress_category_name'],
                        'category_name' => stripslashes_deep($category_data['bookingpress_category_name']),
                        'total_services' => $category_ids_data['counter'],
                        'service_ids' => $category_ids_data['service_ids'],
                        'is_visible' => true,
                        'position' => $category_data['bookingpress_category_position']
                    );
                    $bookingpress_all_category_with_services[0] = array(
                        'category_id' => 0,
                        'category_key' => 'all',
                        'is_visible' => true,
                        'category_name' => stripslashes_deep($bookingpress_all_category_title),
                        'total_services' => $category_ids_data['counter'],
                        'service_ids' => $category_ids_data['service_ids']
                    );
                }
            }
            
            usort( $bpa_category_data, function( $a, $b ){ return $a['position'] <=> $b['position']; } );

            if( !empty( $bpa_uncategorized_data ) ){
                $bpa_all_categories = array_merge( $bpa_uncategorized_data, $bpa_category_data );
            } else {
                if( count( $get_category_ids ) > 1 ){
                    $bpa_all_categories = array_merge( $bookingpress_all_category_with_services, $bpa_category_data );
                } else {
                    $bpa_all_categories = $bpa_category_data;
                }
            }
            
            return $bpa_all_categories;
        }
        
        /**
         * retrive bookingpress service meta from the service id
         *
         * @param  mixed $service_id
         * @return void
         */
        function bookingpress_get_service_meta( $service_id ){
            global $wpdb, $tbl_bookingpress_servicesmeta;

            $services_meta = array();

            if( empty( $service_id ) ){
                return $services_meta;
            }

            $get_service_metas = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_servicemeta_name, bookingpress_servicemeta_value FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d", $service_id ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_servicesmeta is table name defined globally.

            if( empty( $get_service_metas ) ){
                return $services_meta;
            }

            foreach ( $get_service_metas as $smkey => $smvalue ){
                $services_meta[ $smvalue['bookingpress_servicemeta_name'] ] = $this->bookingpress_json_decode( $smvalue['bookingpress_servicemeta_value'], true );
            }

            return $services_meta;
        }
        
        /**
         * bookingpress_json_decode
         *
         * @param  mixed $values
         * @param  mixed $as_array
         * @return void
         */
        function bookingpress_json_decode( $values, $as_array = false ){
            if( is_array( $values ) ){
                return $values;
            }
          
            if( is_object( $values ) && $as_array == false ){
                return $values;
            } else if( is_object( $values ) && true == $as_array ){
                return json_decode( wp_json_encode( $values ), true );
            }
            
            $return_array = json_decode( $values, $as_array );
            if( json_last_error() != JSON_ERROR_NONE ){
                $return_array = maybe_unserialize( $values );
                if( !$as_array ){
                    $return_array = (object)$return_array;
                }
            }
            return $return_array;
        }

        /**
         * Get service data with category grouping
         *
         * @return void
         */
        public function get_bookingpress_service_data_group_with_category()
        {
            global $wpdb, $tbl_bookingpress_categories, $tbl_bookingpress_services;

            $bookingpress_currency_name   = $this->bookingpress_get_settings('payment_default_currency', 'payment_setting');
            $bookingpress_currency_symbol = ! empty($bookingpress_currency_name) ? $this->bookingpress_get_currency_symbol($bookingpress_currency_name) : '';

            $bookingpress_services_details   = array();

            //Get all uncategorized services
            $bookingpress_get_uncategorized_services = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tbl_bookingpress_services WHERE bookingpress_category_id = %d ORDER BY bookingpress_service_id DESC", 0), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is table name
            if(!empty($bookingpress_get_uncategorized_services)){
                foreach($bookingpress_get_uncategorized_services as $uncat_ser_key => $uncat_ser_val){
                    $bookingpress_service_price = $this->bookingpress_price_formatter_with_currency_symbol($uncat_ser_val['bookingpress_service_price'], $bookingpress_currency_symbol);

                    $bookingpress_service_duration_unit = isset($uncat_ser_val['bookingpress_service_duration_unit']) ? $uncat_ser_val['bookingpress_service_duration_unit'] : '';
					$bookingpress_service_duration = isset($uncat_ser_val['bookingpress_service_duration_val']) ? $uncat_ser_val['bookingpress_service_duration_val'] : '';								

                    $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".$bookingpress_service_duration_unit;
								
                    if($bookingpress_service_duration == 1 && $bookingpress_service_duration_unit == 'm'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Min', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration > 1 && $bookingpress_service_duration_unit == 'm'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Mins', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration == 1 && $bookingpress_service_duration_unit == 'h'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Hour', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration > 1 && $bookingpress_service_duration_unit == 'h'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Hours', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration == 1 && $bookingpress_service_duration_unit == 'd'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Day', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration > 1 && $bookingpress_service_duration_unit == 'd'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Days', 'bookingpress-appointment-booking' ); 
                    }

                    $bookingpress_tmp_services[] = array(
                        'service_id'                     => $uncat_ser_val['bookingpress_service_id'],
                        'service_name'                   => stripslashes_deep($uncat_ser_val['bookingpress_service_name']),
                        'service_price'                  => $bookingpress_service_price,
                        'service_price_without_currency' => $uncat_ser_val['bookingpress_service_price'],
                        'service_price_currency'         => $bookingpress_currency_symbol,
                        'service_duration'               => $uncat_ser_val['bookingpress_service_duration_val'],
                        'service_duration_unit'          => $uncat_ser_val['bookingpress_service_duration_unit'],
                        'service_duration_formatted'      => $bookingpress_service_duration_formatted,   
                    );
                }
                
                $bookingpress_services_details[] = array(
                    'category_id'       => 0,
                    'category_name'     => esc_html__('Uncategorized', 'bookingpress-appointment-booking'),
                    'category_services' => $bookingpress_tmp_services,  
                );
            }

            $bookingpress_service_categories = $wpdb->get_results('SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_id != 0 ORDER BY bookingpress_category_position ASC', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False alarm
            foreach ( $bookingpress_service_categories as $bookingpress_service_cat_key => $bookingpress_service_cat_val ) {
                $bookingpress_cat_id       = $bookingpress_service_cat_val['bookingpress_category_id'];
                $bookingpress_tmp_services = array();
                $bookingpress_services     = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_category_id = %d', $bookingpress_cat_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False alarm
                foreach ( $bookingpress_services as $bookingpress_service_key => $bookingpress_service_val ) {
                    $bookingpress_service_price = $this->bookingpress_price_formatter_with_currency_symbol($bookingpress_service_val['bookingpress_service_price'], $bookingpress_currency_symbol);

                    $bookingpress_service_duration_unit = isset($bookingpress_service_val['bookingpress_service_duration_unit']) ? $bookingpress_service_val['bookingpress_service_duration_unit'] : '';
					$bookingpress_service_duration = isset($bookingpress_service_val['bookingpress_service_duration_val']) ? $bookingpress_service_val['bookingpress_service_duration_val'] : '';								

                    $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".$bookingpress_service_duration_unit;
								
                    if($bookingpress_service_duration == 1 && $bookingpress_service_duration_unit == 'm'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Min', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration > 1 && $bookingpress_service_duration_unit == 'm'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Mins', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration == 1 && $bookingpress_service_duration_unit == 'h'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Hour', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration > 1 && $bookingpress_service_duration_unit == 'h'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Hours', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration == 1 && $bookingpress_service_duration_unit == 'd'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Day', 'bookingpress-appointment-booking' ); 
                    }
                    if($bookingpress_service_duration > 1 && $bookingpress_service_duration_unit == 'd'){
                        $bookingpress_service_duration_formatted = $bookingpress_service_duration." ".esc_html__( 'Days', 'bookingpress-appointment-booking' ); 
                    }

                    $bookingpress_tmp_services[] = array(
                     'service_id'                     => $bookingpress_service_val['bookingpress_service_id'],
                     'service_name'                   => stripslashes_deep($bookingpress_service_val['bookingpress_service_name']),
                     'service_price'                  => $bookingpress_service_price,
                     'service_price_without_currency' => $bookingpress_service_val['bookingpress_service_price'],
                     'service_price_currency'         => $bookingpress_currency_symbol,
                     'service_duration'               => $bookingpress_service_val['bookingpress_service_duration_val'],
                     'service_duration_unit'          => $bookingpress_service_val['bookingpress_service_duration_unit'],
                     'service_duration_formatted'      => $bookingpress_service_duration_formatted,   
                    );
                }

                if (! empty($bookingpress_tmp_services) ) {
                    $bookingpress_services_details[] = array(
                    'category_id'       => $bookingpress_cat_id,
                    'category_name'     => stripslashes_deep($bookingpress_service_cat_val['bookingpress_category_name']),
                    'category_services' => $bookingpress_tmp_services,
                    );
                }
            }

            return $bookingpress_services_details;
        }
        
        /**
         * Core function for insert appointment table details
         *
         * @param  mixed $appointment_booking_data
         * @return void
         */
        function bookingpress_insert_appointment_logs( $appointment_booking_data = array() )
        {
            global $wpdb, $tbl_bookingpress_appointment_bookings;
            $appointment_inserted_id = 0;
            if (! empty($appointment_booking_data) ) {
                $wpdb->insert($tbl_bookingpress_appointment_bookings, $appointment_booking_data);
                $appointment_inserted_id = $wpdb->insert_id;
                do_action('bookingpress_after_insert_appointment', $appointment_inserted_id);
            }
            return $appointment_inserted_id;
        }

        
        /**
         * Core function for insert payment logs data
         *
         * @param  mixed $payment_log_data
         * @return void
         */
        function bookingpress_insert_payment_logs( $payment_log_data = array() )
        {
            global $wpdb, $tbl_bookingpress_payment_logs;
            $payment_log_id = 0;
            if (! empty($payment_log_data) ) {
                $wpdb->insert($tbl_bookingpress_payment_logs, $payment_log_data);
                $payment_log_id = $wpdb->insert_id;
            }

            return $payment_log_id;
        }

        
        /**
         * Get service details from specific ID
         *
         * @param  mixed $service_id
         * @return void
         */
        function get_service_by_id( $service_id )
        {
            global $wpdb, $tbl_bookingpress_services;
            $service_data = array();
            if (! empty($service_id) ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                $service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A);
            }
            return $service_data;
        }

        
        /**
         * Get customer details from specific customer id
         *
         * @param  mixed $customer_id
         * @return void
         */
        function get_customer_details( $customer_id )
        {
            global $wpdb, $tbl_bookingpress_customers;
            $customer_data = array();
            if (! empty($customer_id) ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                $customer_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id =%d", $customer_id), ARRAY_A);
            }
            return $customer_data;
        }
        
        /**
         * Install default notification data
         *
         * @return void
         */
        function bookingpress_install_default_notification_data()
        {
            global $wpdb, $tbl_bookingpress_notifications;

            $bookingpress_default_notifications_name_arr = array( 'Appointment Approved', 'Appointment Pending', 'Appointment Rejected', 'Appointment Canceled', 'Share Appointment URL' );

            $bookingpress_default_notifications_message_arr = array(
            'Appointment Approved' => sprintf( esc_html__('Dear %1$s %2$s,%3$sYou have successfully scheduled appointment.%3$sThank you for choosing us,%3$s%4$s','bookingpress-appointment-booking'), '%customer_first_name%', '%customer_last_name%', '<br/>', '%company_name%' ), // phpcs:ignore
            'Appointment Pending'  => sprintf( esc_html__('Dear %1$s %2$s,%3$sThe %4$s appointment is scheduled and it is waiting for a confirmation.%3$sThank you for choosing us,%3$s%5$s','bookingpress-appointment-booking'), '%customer_first_name%', '%customer_last_name%', '<br/>', '%service_name%', '%company_name%' ), // phpcs:ignore
            'Appointment Rejected' => sprintf( esc_html__('Dear %1$s %2$s,%3$sThe %4$s appointment, has been rejected.%3$sThank you for choosing us,%3$s%5$s','bookingpress-appointment-booking'), '%customer_first_name%', '%customer_last_name%', '<br/>', '%service_name%', '%company_name%' ), // phpcs:ignore
            'Appointment Canceled' => sprintf( esc_html__('Dear %1$s %2$s,%3$sThe %4$s appointment, has been canceled.%3$sThank you for choosing us,%3$s%5$s','bookingpress-appointment-booking'), '%customer_first_name%', '%customer_last_name%', '<br/>', '%service_name%', '%company_name%' ), // phpcs:ignore
            'Share Appointment URL' => sprintf( esc_html__('Hi%1$sPlease book your appointment with following URL: %1$s%2$s%1$sThanks,%1$s%3$s','bookingpress-appointment-booking'), '<br/>', '%share_appointment_url%', '%company_name%' ), // phpcs:ignore
            ); 

            foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
                $bookingpress_customer_notification_data = array(
                'bookingpress_notification_name'    => $bookingpress_default_notification_val,
                'bookingpress_notification_receiver_type' => 'customer',
                'bookingpress_notification_status'  => 1,
                'bookingpress_notification_type'    => 'default',
                'bookingpress_notification_subject' => ($bookingpress_default_notification_val == "Share Appointment URL") ? esc_html__('Book your appointment now', 'bookingpress-appointment-booking') : $bookingpress_default_notification_val,
                'bookingpress_notification_message' => $bookingpress_default_notifications_message_arr[ $bookingpress_default_notification_val ],
                'bookingpress_created_at'           => current_time('mysql'),
                );

                $resp = $wpdb->insert($tbl_bookingpress_notifications, $bookingpress_customer_notification_data);
                $db_data = array(
                    'last_query' => $wpdb->last_query,
                    'last_error' => $wpdb->last_error,
                    'resposne' => $resp
                );

                update_option( 'bpa_default_notificat_data_query_details_'. $bookingpress_default_notification_val.'_'. date( 'Y-m-d H:i:s', current_time('timestamp') ), wp_json_encode( $db_data) );
            }

            $bookingpress_default_notifications_arr2 = array(
                'Appointment Approved' => sprintf( esc_html__('Hi administrator,%1$sYou have one confirmed %2$s appointment. The appointment is added to your schedule.%1$sThank you,%1$s%3$s','bookingpress-appointment-booking'), '<br/>', '%service_name%', '%company_name%' ), //phpcs:ignore
                'Appointment Pending'  => sprintf( esc_html__('Hi administrator,%1$sYou have new appointment in %2$s. The appointment is waiting for a confirmation.%1$sThank you,%1$s%3$s','bookingpress-appointment-booking'), '<br/>', '%service_name%', '%company_name%' ), //phpcs:ignore
                'Appointment Rejected' => sprintf( esc_html__('Hi administrator,%1$sYour %2$s appointment, has been rejected.%1$sThank you,%1$s%3$s','bookingpress-appointment-booking'), '<br/>', '%service_name%', '%company_name%' ), //phpcs:ignore
                'Appointment Canceled' => sprintf( esc_html__('Hi administrator,%1$sYour %2$s appointment, has been canceled.%1$sThank you,%1$s%3$s','bookingpress-appointment-booking'), '<br/>', '%service_name%', '%company_name%' ), //phpcs:ignore
                'Share Appointment URL' => sprintf( esc_html__('Hi administrator,%1$sFollowing appointment URL is shared with customer. %1$s%2$s%1$sThank you,%1$s%3$s','bookingpress-appointment-booking'), '<br/>', '%share_appointment_url%', '%company_name%' ), //phpcs:ignore
            );

            foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
                $bookingpress_employee_notification_data = array(
                'bookingpress_notification_name'    => $bookingpress_default_notification_val,
                'bookingpress_notification_receiver_type' => 'employee',
                'bookingpress_notification_status'  => 1,
                'bookingpress_notification_type'    => 'default',
                'bookingpress_notification_subject' => ($bookingpress_default_notification_val == "Share Appointment URL") ? esc_html__('Book your appointment now', 'bookingpress-appointment-booking') : $bookingpress_default_notification_val,
                'bookingpress_notification_message' => $bookingpress_default_notifications_arr2[ $bookingpress_default_notification_val ],
                'bookingpress_created_at'           => current_time('mysql'),
                );

                $resp = $wpdb->insert($tbl_bookingpress_notifications, $bookingpress_employee_notification_data);

                $db_data = array(
                    'last_query' => $wpdb->last_query,
                    'last_error' => $wpdb->last_error,
                    'resposne' => $resp
                );

                update_option( 'bpa_default_notificat_data_query_details_second_'. $bookingpress_default_notification_val.'_'. date( 'Y-m-d H:i:s', current_time('timestamp') ), wp_json_encode( $db_data) );
            }
        }

        
        /**
         * Install default general settings data
         *
         * @return void
         */
        function bookingpress_install_default_general_settings_data()
        {   
            global $BookingPress;
            $default_date_format = get_option('date_format');
            if($default_date_format != 'F j, Y' && $default_date_format != 'd/m/Y'  && $default_date_format != 'm/d/Y'  && $default_date_format != 'Y-m-d' && $default_date_format != 'd.m.Y' && $default_date_format != 'd-m-Y' ) {
                $default_date_format = 'F j, Y';
            } 
            
            $wp_default_time_format  = get_option('time_format');
            if ($wp_default_time_format == 'g:i a' || $wp_default_time_format == 'g:i A') {				
                $wp_default_time_format = 'g:i a';					
            } elseif($wp_default_time_format == 'H:i') {
                $wp_default_time_format = 'H:i';	
            } else {
                $wp_default_time_format = 'g:i a';
            }

            $bookingpress_general_setting_form_default_data = array(
                'default_time_slot_step'              => '30',
                'appointment_status'                  => '1',
                'onsite_appointment_status'           => '2',
                'default_phone_country_code'          => 'us',
                'per_page_item'                       => '20',
                'redirect_url_after_booking_approved' => '',
                'redirect_url_after_booking_pending'  => '',
                'redirect_url_after_booking_canceled' => '',
                'phone_number_mandatory'              => false,
                'share_timeslot_between_services'     => false,
                'use_already_loaded_vue'              => false,
                'load_js_css_all_pages'               => false,
                'show_time_as_per_service_duration'   => 'true',
                'default_time_slot'                   => '30',
                'default_date_format'                 => $default_date_format,
                'default_time_format'                 => $wp_default_time_format,
                'anonymous_data'		              => 'false',
                'debug_mode'                          => false,
                'bpa_afternoon_start_time'            => '12:00:00',
                'bpa_evening_start_time'              => '16:00:00',
                'bpa_night_start_time'                => '20:00:00',
            );

            $bookingpress_company_setting_form_default_data      = array(
                'company_avatar_img'    => '',
                'company_avatar_url'    => '',
                'company_avatar_list'   => array(),
                'company_name'          => get_option('blogname'),
                'company_address'       => '',
                'company_website'       => '',
                'company_phone_country' => 'us',
                'company_phone_number'  => '',
            );
            $bookingpress_notification_setting_form_default_data = array(
                'selected_mail_service' => 'wp_mail',
                'sender_name'           => get_option('blogname'),
                'sender_email'          => get_option('admin_email'),
                'admin_email'           => get_option('admin_email'),
                'success_url'           => '',
                'cancel_url'            => '',
                'smtp_host'             => '',
                'smtp_port'             => '',
                'smtp_secure'           => 'Disabled',
                'smtp_username'         => '',
                'smtp_password'         => '',
                'gmail_client_ID'       => '',
                'gmail_client_secret'   => '',
                'gmail_redirect_url'    => '',
                'gmail_auth_secret'     => '',
                'bookingpress_gmail_auth' => '',
                'bookingpress_response_email' => '',
                'bookingpress_gmail_auth_token' => '',
            );
            $bookingpress_payment_setting_form_default_data      = array(
                'payment_default_currency'      => 'USD',
                'price_symbol_position'         => 'before',
                'price_separator'               => 'comma-dot',
                'price_number_of_decimals'      => 2,
                'on_site_payment'               => true,
                'paypal_payment'                => false,
                'paypal_cancel_url'             => BOOKINGPRESS_HOME_URL,
                'paypal_payment_mode'           => 'sandbox',
                'paypal_payment_method_type'    => 'lagacy'
            );
            $bookingpress_message_setting_form_default_data      = array(
            'appointment_booked_successfully'       => esc_html__('Appointment has been booked successfully.','bookingpress-appointment-booking'),
            'appointment_cancelled_successfully'    => esc_html__('Appointment has been cancelled successfully.','bookingpress-appointment-booking'),
            'duplidate_appointment_time_slot_found' => esc_html__('I am sorry! Another appointment is already booked with this time slot. Please select another time slot which suits you the best.','bookingpress-appointment-booking'),
            'unsupported_currecy_selected_for_the_payment' => esc_html__('I am sorry! The selected currency is not supported by selected payment gateway. Please proceed with another available payment method.','bookingpress-appointment-booking'),
            'duplicate_email_address_found'         => esc_html__('It seems that you are already registered with us! Please login to continue to book an appointment.','bookingpress-appointment-booking'),
            'no_payment_method_is_selected_for_the_booking' => esc_html__('Please select a payment method to proceed with the booking.','bookingpress-appointment-booking'),
            'no_appointment_time_selected_for_the_booking' => esc_html__('Please select a time slot to proceed with the booking.','bookingpress-appointment-booking'),
            'no_appointment_date_selected_for_the_booking' => esc_html__('Please select appointment date to proceed with the booking.','bookingpress-appointment-booking'),
            'no_service_selected_for_the_booking'   => esc_html__('Please select any service to book an appointment.','bookingpress-appointment-booking'),
            'no_payment_method_available'  => esc_html__('There is no payment method available.','bookingpress-appointment-booking'),
            'no_timeslots_available'  => esc_html__('There is no time slots available','bookingpress-appointment-booking'),                                           
            'cancel_appointment_confirmation'  => esc_html__('Cancel appointment confirmation','bookingpress-appointment-booking'),
            'no_appointment_available_for_cancel'  => esc_html__('There is no appointment to cancel','bookingpress-appointment-booking'),
            );
            $bookingpress_customer_setting_form_default_data     = array(
                'allow_wp_user_create' => 'false',
                'allow_autologin_user' => 'true'
            );
            $bookingpress_invoice_setting_default_data           = array(
            'bookingpress_last_invoice_id' => 0,
            );        
            $bookingpress_install_default_data                   = array(
            'general_setting'               => $bookingpress_general_setting_form_default_data,
            'company_setting'               => $bookingpress_company_setting_form_default_data,
            'notification_setting'          => $bookingpress_notification_setting_form_default_data,
            'payment_setting'               => $bookingpress_payment_setting_form_default_data,
            'message_setting'               => $bookingpress_message_setting_form_default_data,
            'customer_setting'              => $bookingpress_customer_setting_form_default_data,
            'invoice_Setting'               => $bookingpress_invoice_setting_default_data,
            );

            foreach ( $bookingpress_install_default_data as $bookingpress_default_data_key => $bookingpress_default_data_val ) {
                $bookingpress_setting_type = $bookingpress_default_data_key;
                foreach ( $bookingpress_default_data_val as $bookingpress_default_data_val_key => $bookingpress_default_data_val2 ) {
                    $BookingPress->bookingpress_update_settings($bookingpress_default_data_val_key, $bookingpress_setting_type, $bookingpress_default_data_val2);
                }
            }

            global $tbl_bookingpress_default_workhours, $wpdb;

            // Install default workhours data
            $bookingpress_default_days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
            foreach ( $bookingpress_default_days as $bookingpress_default_day_key => $bookingpress_default_day_val ) {
                $default_start_time = '09:00';
                $default_end_time   = '17:00';

                $default_insert_data = array(
                'bookingpress_workday_key' => $bookingpress_default_day_val,
                );

                if ($bookingpress_default_day_val != 'saturday' && $bookingpress_default_day_val != 'sunday' ) {
                    $default_insert_data['bookingpress_start_time'] = $default_start_time;
                    $default_insert_data['bookingpress_end_time']   = $default_end_time;
                } else {
                    $default_insert_data['bookingpress_start_time'] = null;
                    $default_insert_data['bookingpress_end_time']   = null;
                }

                $wpdb->insert($tbl_bookingpress_default_workhours, $default_insert_data);
            }
        }
        
        /**
         * Install default customize settings data
         *
         * @return void
         */
        function bookingpress_install_default_customize_settings_data()
        {
            global $wpdb, $tbl_bookingpress_customize_settings, $tbl_bookingpress_form_fields, $BookingPress;

            $booking_form_shortcode_default_fields = array(
                'service_title'                   => esc_html__('Service', 'bookingpress-appointment-booking'),
                'datetime_title'                  => str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ),
                'basic_details_title'             => esc_html__('Basic Details', 'bookingpress-appointment-booking'),
                'summary_title'                   => esc_html__('Summary', 'bookingpress-appointment-booking'),
                'category_title'                  => esc_html__('Select Category', 'bookingpress-appointment-booking'),
                'service_heading_title'           => esc_html__('Select Service', 'bookingpress-appointment-booking'),
                'default_image_url'               => BOOKINGPRESS_URL . '/images/placeholder-img.jpg',            
                'timeslot_text'                   => esc_html__('Time Slot', 'bookingpress-appointment-booking'),            
                'morning_text'                    => esc_html__('Morning', 'bookingpress-appointment-booking'),            
                'afternoon_text'                  => esc_html__('Afternoon', 'bookingpress-appointment-booking'),            
                'evening_text'                    => esc_html__('Evening', 'bookingpress-appointment-booking'),            
                'night_text'                      => esc_html__('Night', 'bookingpress-appointment-booking'),      
                'date_time_step_note'             => '',
                'summary_step_note'               => '',
                'summary_content_text'            => esc_html__('Your appointment booking summary', 'bookingpress-appointment-booking'),
                'payment_method_text'             => esc_html__('Select Payment Method', 'bookingpress-appointment-booking'),
                'background_color'                => '#fff',
                'price_button_text_color'         => '#fff', 
                'footer_background_color'         => '#f4f7fb',
                'border_color'                    => '#CFD6E5',
                'primary_color'                   => '#12D488',
                'primary_background_color'        => '#e2faf1',
                'label_title_color'               => '#202C45',
                'sub_title_color'                 => '#535D71',
                'content_color'                   => '#727E95',
                'custom_css'                      => '',
                'title_font_family'               => 'Poppins',
                'hide_category_service_selection' => 'false',
                'booking_form_tabs_position'      => 'left',
                'hide_already_booked_slot'        => 'false',
                'display_service_description'     => 'true',
                'goback_button_text'              => esc_html__('Go Back', 'bookingpress-appointment-booking'),
                'next_button_text'                => esc_html__('Next:', 'bookingpress-appointment-booking'),
                'book_appointment_btn_text'       => esc_html__('Book Appointment', 'bookingpress-appointment-booking'),  
                'book_appointment_hours_text'     => 'h',        
                'book_appointment_min_text'       => 'm',        
                'paypal_text'                     => esc_html__('PayPal', 'bookingpress-appointment-booking'),
                'locally_text'                    => esc_html__('Pay Locally', 'bookingpress-appointment-booking'),
                'total_amount_text'               => esc_html__('Total Amount Payable', 'bookingpress-appointment-booking'),
                'service_text'                    => esc_html__('Service', 'bookingpress-appointment-booking'),
                'customer_text'                   => esc_html__('Customer', 'bookingpress-appointment-booking'),
                'date_time_text'                  => str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ),
                'appointment_details'             => esc_html__('Appointment Details', 'bookingpress-appointment-booking'),
                'all_category_title'              => esc_html__('ALL', 'bookingpress-appointment-booking'),
                'service_duration_label'          => esc_html__('Duration', 'bookingpress-appointment-booking') . ':',
                'service_price_label'             => esc_html__('Price', 'bookingpress-appointment-booking') . ':',
                'default_select_all_category'     => 'false',
            );

            foreach ( $booking_form_shortcode_default_fields as $booking_form_shortcode_data_key => $booking_form_shortcode_data_val ) {
                $bookingpress_customize_settings_db_fields = array(
                'bookingpress_setting_name'  => $booking_form_shortcode_data_key,
                'bookingpress_setting_value' => $booking_form_shortcode_data_val,
                'bookingpress_setting_type'  => 'booking_form',
                );
                $resp = $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields);

                $db_data = array(
                    'last_query' => $wpdb->last_query,
                    'last_error' => $wpdb->last_error,
                    'resposne' => $resp
                );

                update_option( 'booking_form_shortcode_default_fields_'. $booking_form_shortcode_data_key.'_'. date( 'Y-m-d H:i:s', current_time('timestamp') ), wp_json_encode( $db_data) );
            }

            $mybookings_shortcode_default_data = array(
                'mybooking_title_text'        => esc_html__('My Bookings', 'bookingpress-appointment-booking'),
                'hide_customer_details'       => 'false',
                'hide_search_bar'             => 'false',
                'allow_to_cancel_appointment' => 'true',
                'apply_button_title'          => esc_html__('Apply', 'bookingpress-appointment-booking'),  
                'search_appointment_title'    => esc_html__('Search appointments', 'bookingpress-appointment-booking'),              
                'search_date_title'           => esc_html__('Please select date', 'bookingpress-appointment-booking'),
                'search_end_date_title'           => esc_html__('Please select date', 'bookingpress-appointment-booking'),
                'my_appointment_menu_title'    => esc_html__('My Appointments', 'bookingpress-appointment-booking'),
                'delete_appointment_menu_title'    => esc_html__('Delete Account', 'bookingpress-appointment-booking'),
                'cancel_appointment_title' => esc_html__('Cancel Appointment', 'bookingpress-appointment-booking'),
                'cancel_appointment_confirmation_message' => esc_html__('Are you sure you want to cancel the appointment?', 'bookingpress-appointment-booking'),
                'cancel_appointment_no_btn_text' => esc_html__('No', 'bookingpress-appointment-booking'),
                'cancel_appointment_yes_btn_text' => esc_html__('Yes', 'bookingpress-appointment-booking'),
                'id_main_heading' => esc_html__('ID', 'bookingpress-appointment-booking'),
                'service_main_heading' => esc_html__('Service', 'bookingpress-appointment-booking'),
                'date_main_heading' => esc_html__('Date', 'bookingpress-appointment-booking'),
                'status_main_heading' => esc_html__('Status', 'bookingpress-appointment-booking'),
                'payment_main_heading' => esc_html__('Payment', 'bookingpress-appointment-booking'),
                'booking_id_heading' => esc_html__('Booking ID', 'bookingpress-appointment-booking'),
                'booking_time_title' => esc_html__('Time', 'bookingpress-appointment-booking'),
                'payment_details_title' => esc_html__('Payment Details', 'bookingpress-appointment-booking'),
                'payment_method_title' => esc_html__('Payment Method', 'bookingpress-appointment-booking'),
                'total_amount_title' => esc_html__('Total Amount', 'bookingpress-appointment-booking'),
                'cancel_booking_id_text' => esc_html__('Booking ID', 'bookingpress-appointment-booking'),
                'cancel_service_text' => esc_html__('Service', 'bookingpress-appointment-booking'),
                'cancel_date_time_text' => str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ),
                'cancel_button_text' => esc_html__('Confirm Cancellation', 'bookingpress-appointment-booking'),
                'delete_account_content' => '<div class="bpa-front-cp-delete-account-wrapper">
                <div class="bpa-front-dcw__vector">
                <svg width="306" height="187" viewBox="0 0 306 187" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3" d="M211.536 186.663C212.664 185.998 213.781 185.31 214.886 184.601C228.966 175.555 241.049 162.228 245.039 145.975C249.03 129.722 243.319 110.575 229.022 101.876C216.592 94.3128 200.676 95.7294 186.676 99.6925C172.675 103.654 159.189 109.917 144.716 111.417C127.574 113.193 109.197 108.323 93.6292 115.715C76.351 123.918 68.5627 146.337 74.1599 164.628C76.7263 173.011 81.6272 180.396 87.9578 186.431L211.536 186.663Z" fill="#E9EDF5"/>
                <path opacity="0.6" d="M103.995 186.593L103.943 186.56C102.839 185.858 101.747 185.134 100.695 184.407C85.3464 173.787 74.9624 159.448 71.4556 144.032C67.3307 125.9 73.8934 106.47 87.0587 97.837C97.3193 91.1084 111.161 90.3286 128.202 95.5185C132.633 96.8688 137.08 98.4988 141.382 100.077C150.216 103.317 159.351 106.669 168.749 107.714C174.567 108.36 180.631 108.151 186.496 107.945C197.282 107.568 208.434 107.18 218.24 112.195C234.362 120.437 242.849 143.415 237.159 163.415C234.703 172.048 229.708 178.879 220.457 186.255L220.4 186.3H220.327L103.995 186.593ZM107.813 92.5555C99.8804 92.5555 93.0179 94.4349 87.2908 98.1898C74.2661 106.731 67.7803 125.971 71.8668 143.939C75.3497 159.251 85.674 173.499 100.935 184.06C101.965 184.773 103.034 185.483 104.117 186.17L220.253 185.877C229.392 178.577 234.329 171.822 236.754 163.297C242.391 143.48 233.999 120.724 218.047 112.569C208.337 107.604 197.241 107.993 186.509 108.365C180.629 108.571 174.551 108.783 168.702 108.132C159.251 107.082 150.092 103.722 141.234 100.472C136.94 98.8967 132.498 97.268 128.077 95.9217C120.707 93.6762 113.941 92.5555 107.813 92.5555Z" fill="#9192FF"/>
                <path class="bpa-front-dcw__vector-primary-color" d="M263.772 177.548C259.619 177.548 256.242 174.392 256.242 170.514C256.242 166.636 259.619 163.48 263.772 163.48C267.925 163.48 271.301 166.636 271.301 170.514C271.301 174.392 267.925 177.548 263.772 177.548ZM263.772 166.103C261.066 166.103 258.864 168.082 258.864 170.514C258.864 172.947 261.066 174.925 263.772 174.925C266.478 174.925 268.679 172.947 268.679 170.514C268.681 168.082 266.479 166.103 263.772 166.103Z" />
                <path d="M261.124 186.256H244.645L241.828 157.664H263.941L261.124 186.256Z" fill="white"/>
                <path class="bpa-front-dcw__vector-primary-color" d="M261.378 186.537H244.388L241.516 157.381H264.25L261.378 186.537ZM244.899 185.973H260.867L263.628 157.945H242.138L244.899 185.973Z" />
                <path d="M258.255 183.88H247.509L245.672 165.957H260.092L258.255 183.88Z" fill="#E9EDF5"/>
                <path d="M222.623 68.0227C219.484 69.4758 215.304 71.8149 216.099 75.9732C216.878 80.052 221.801 79.3155 224.675 78.3406C226.803 77.6187 228.859 76.648 230.753 75.4345C231.982 74.6467 233.711 73.2847 233.291 71.5963C232.931 70.1464 231.248 69.7216 229.955 69.9873C228.08 70.3723 225.939 71.5147 224.47 72.7187C223.136 73.8119 222.097 75.514 222.359 77.2986C222.597 78.9306 223.944 80.0939 225.444 80.6054C227.312 81.2435 229.846 81.1128 231.723 80.5426C232.865 80.1964 233.92 79.1628 233.173 77.9263C232.289 76.4628 230.454 76.8174 229.182 77.4608C227.466 78.3301 225.432 79.4662 224.584 81.2885C223.939 82.6725 224.042 84.3892 225.296 85.3914C226.525 86.3737 228.298 86.3768 229.787 86.2638C231.019 86.1697 233.031 85.9123 233.227 84.3578C233.424 82.7907 231.497 82.0773 230.272 82.5407C228.376 83.2573 227.187 85.5964 226.612 87.4072C225.933 89.5476 225.945 91.8365 226.605 93.9768C226.688 94.2488 227.117 94.1327 227.034 93.8586C226.175 91.0739 226.473 88.014 227.943 85.4845C228.647 84.272 230.035 82.3566 231.692 82.8922C232.577 83.1788 233.205 84.226 232.42 85.0148C231.869 85.5692 230.94 85.7042 230.199 85.7837C229.125 85.8987 227.97 85.9448 226.914 85.6728C225.093 85.2041 224.187 83.5565 224.865 81.7593C225.418 80.2926 226.773 79.372 228.063 78.6021C229.289 77.8709 231.734 76.3393 232.789 78.1502C233.973 80.1786 230.125 80.393 229.068 80.4736C227.517 80.5908 225.873 80.5353 224.517 79.6806C223.08 78.7768 222.437 77.2149 222.96 75.5778C223.485 73.9312 224.852 72.8338 226.313 72.0262C227.452 71.3965 228.788 70.6631 230.074 70.4152C230.98 70.2405 232.124 70.3838 232.664 71.2437C233.31 72.2731 232.539 73.3966 231.793 74.0996C229.704 76.0674 226.548 77.3101 223.849 78.1408C221.037 79.0059 216.425 79.1921 216.465 75.0537C216.499 71.4749 220.039 69.7049 222.847 68.4056C223.107 68.2864 222.882 67.9035 222.623 68.0227Z" fill="#202C45"/>
                <path d="M57.8316 69C58.7441 70.9192 59.5744 72.8689 60.3822 74.8266L61.5653 77.7724L62.6993 80.7355L61.0069 80.2381L63.8466 78.0098C64.7949 77.2684 65.7618 76.5535 66.7194 75.8227L67.876 74.9407L68.4105 76.279C68.7952 77.2419 69.1519 78.2167 69.5246 79.185C69.8934 80.1545 70.2342 81.136 70.591 82.1109C70.9266 83.0937 71.2767 84.0712 71.5924 85.0633C71.92 86.0501 72.2251 87.0462 72.5102 88.0489L71.6945 88.3818C71.1945 87.4653 70.7157 86.5409 70.2568 85.6084C69.7873 84.68 69.3522 83.737 68.9013 82.8006C68.4729 81.8549 68.0285 80.9159 67.6121 79.9662C67.1996 79.0152 66.7712 78.0695 66.3706 77.1119L68.063 77.5682C67.1134 78.3069 66.1743 79.0603 65.2154 79.7871L62.3359 81.965L61.113 82.8895L60.6435 81.4663L59.65 78.4528L58.7044 75.4221C58.0942 73.3942 57.5053 71.3596 57 69.2944L57.8316 69Z" fill="#FFA121"/>
                <path d="M197.509 49.2944C197.004 51.3596 196.415 53.3942 195.805 55.4221L194.859 58.4528L193.865 61.4663L193.396 62.8895L192.173 61.965L189.294 59.7871C188.335 59.059 187.396 58.3069 186.446 57.5682L188.138 57.1119C187.738 58.0682 187.309 59.0139 186.897 59.9662C186.48 60.9159 186.036 61.8549 185.608 62.8006C185.158 63.737 184.723 64.68 184.253 65.6084C183.795 66.5422 183.316 67.4653 182.816 68.3818L182 68.0489C182.286 67.0449 182.592 66.0501 182.918 65.0633C183.233 64.0712 183.584 63.095 183.919 62.1109C184.276 61.136 184.617 60.1559 184.986 59.185C185.358 58.2167 185.715 57.2419 186.1 56.279L186.634 54.9407L187.791 55.8227C188.747 56.5522 189.715 57.2684 190.664 58.0098L193.503 60.2381L191.811 60.7355L192.945 57.7724L194.128 54.8266C194.936 52.8689 195.766 50.9205 196.679 49L197.509 49.2944Z" fill="#FFA121"/>
                <path class="bpa-front-dcw__vector-primary-color" d="M170.793 105.362L151.217 104.174L125.673 110.924C116.714 113.29 109.442 119.825 106.14 128.479L90.1797 170.275L121.544 142.483L130.182 186.239L188.773 186.05L188.585 125.298L170.793 105.362Z"/>
                <path d="M155.177 128.044C151.629 128.044 148.316 126.662 146.125 124.22L146.544 123.844C148.889 126.457 152.596 127.796 156.456 127.418C160.593 127.016 164.207 124.703 166.374 121.07L166.858 121.359C164.598 125.146 160.827 127.56 156.512 127.979C156.063 128.022 155.619 128.044 155.177 128.044Z" fill="#C5C5FF"/>
                <path d="M157.611 120.398C153.859 122.212 149.251 120.42 147.363 116.409L146.727 115.051L140.27 101.333L136.32 92.933L155.402 83.7109L163.712 101.367C167.126 108.629 164.404 117.114 157.611 120.398Z" fill="#FFBC96"/>
                <path d="M157.222 97.7891C155.542 105.97 151.346 111.575 146.726 115.051L140.27 101.333L157.222 97.7891Z" fill="#1B1B43"/>
                <path d="M159.458 87.5528C159.458 97.4314 153.888 104.204 146.823 107.695C139.552 111.286 131.96 97.4844 128.334 88.2571C124.569 78.6796 134.932 71.4961 143.606 71.4961C152.281 71.4961 159.458 77.6729 159.458 87.5528Z" fill="#FFBC96"/>
                <path d="M159.85 81.9195C159.005 71.7783 156.944 62.1928 147.55 64.6426C143.982 59.6661 129.052 59.7603 125.39 71.5913C123.028 79.2231 115.438 82.9527 115.438 82.9527C124.545 87.7408 132.941 85.9476 144.64 77.0372C148.302 82.389 158.724 82.7644 158.724 82.7644L159.85 81.9195Z" fill="#1B1B43"/>
                <path d="M162.765 86.6849C163.176 83.4828 161.479 80.6268 158.976 80.3058C156.473 79.9848 154.111 82.3204 153.701 85.5225C153.29 88.7245 154.986 91.5805 157.489 91.9015C159.992 92.2225 162.354 89.8869 162.765 86.6849Z" fill="#FFBC96"/>
                <path d="M159.143 83.6904C158.537 84.1904 158.337 84.8682 158.343 85.5937C158.347 85.9518 158.404 86.3165 158.509 86.6627C158.619 87.0089 158.767 87.3365 159.017 87.623L158.85 87.9081C158.386 87.7556 158.04 87.3657 157.828 86.9532C157.61 86.5341 157.504 86.0672 157.504 85.5963C157.508 85.1268 157.613 84.64 157.875 84.2249C158.129 83.8084 158.552 83.4848 159.007 83.3906L159.143 83.6904Z" fill="#1B1B43"/>
                <path d="M143.414 91.3952C144.199 91.3432 144.788 90.5875 144.73 89.7075C144.671 88.8275 143.988 88.1564 143.203 88.2084C142.418 88.2605 141.829 89.0161 141.887 89.8961C141.945 90.7761 142.629 91.4473 143.414 91.3952Z" fill="#1B1B43"/>
                <path d="M136.363 94.0169C137.029 93.7595 137.334 92.946 137.046 92.1999C136.757 91.4539 135.984 91.0577 135.318 91.3151C134.653 91.5726 134.347 92.3861 134.636 93.1321C134.924 93.8782 135.698 94.2743 136.363 94.0169Z" fill="#1B1B43"/>
                <path d="M147.166 85.6844C147.195 86.1314 144.824 85.9868 143.804 86.203C142.934 86.3874 141.607 87.2349 141.011 86.3887C140.472 85.6247 141.826 84.76 143.504 84.6485C145.182 84.5371 147.136 85.2388 147.166 85.6844Z" fill="#1B1B43"/>
                <path d="M131.878 90.5943C132.144 90.9604 133.355 89.7017 134.052 89.3781C134.646 89.1022 135.91 89.13 135.702 88.1751C135.513 87.3143 134.223 87.2864 133.231 88.0093C132.24 88.7321 131.611 90.2283 131.878 90.5943Z" fill="#1B1B43"/>
                <path d="M141.375 100.649C141.917 99.9948 142.693 99.5717 143.479 99.3064C144.273 99.0465 145.089 98.9324 145.914 98.9921L145.975 99.317C145.227 99.6725 144.469 99.8701 143.74 100.106C143.004 100.334 142.313 100.605 141.574 100.913L141.375 100.649Z" fill="#1B1B43"/>
                <path d="M139.239 93.5735C139.28 94.1876 139.19 94.8401 138.873 95.4383C138.721 95.7354 138.501 96.0047 138.278 96.2288C138.059 96.453 137.837 96.6413 137.784 96.7753C137.767 96.8429 137.738 96.896 137.753 96.9981C137.749 97.0591 137.766 97.0631 137.762 97.0724C137.763 97.087 137.787 97.1082 137.802 97.1347C137.906 97.2527 138.154 97.3734 138.402 97.465C138.914 97.648 139.494 97.77 140.066 97.9053L140.058 98.2356C139.442 98.3191 138.831 98.3151 138.197 98.1772C137.881 98.099 137.552 98.0074 137.218 97.717C137.141 97.6308 137.058 97.5379 137.004 97.4172C136.95 97.2872 136.914 97.1506 136.916 97.0604C136.886 96.8655 136.926 96.6174 137.023 96.4145C137.242 96.0126 137.516 95.8336 137.718 95.6452C137.929 95.4622 138.1 95.2765 138.254 95.0656C138.559 94.6425 138.749 94.1093 138.908 93.543L139.239 93.5735Z" fill="#D28572"/>
                <path opacity="0.2" d="M154.602 92.1506C154.626 90.1606 153.033 88.5276 151.043 88.5034C149.053 88.4791 147.42 90.0726 147.396 92.0627C147.371 94.0528 148.965 95.6857 150.955 95.71C152.945 95.7343 154.578 94.1407 154.602 92.1506Z" fill="#FE5B52"/>
                <path class="bpa-front-dcw__vector-primary-color" d="M203.429 125.772C197.862 114.065 186.375 106.314 173.435 105.524L170.793 105.361L190.281 149.738L211.612 141.82L203.429 125.772Z" />
                <path d="M135.344 96.8521C135.133 93.6132 135.695 86.0066 133.724 86.8515C131.753 87.6964 131.26 98.6811 131.26 98.6811C131.26 98.6811 130.979 80.371 128.866 81.2159C126.753 82.0607 126.753 95.3003 122.106 109.385L96.0019 164.514C92.8625 171.146 96.8176 178.958 104.021 180.353C109.305 181.376 114.571 178.508 116.58 173.515C123.34 156.715 135.759 120.461 136.965 113.401C137.95 107.626 137.695 105.128 137.315 103.644C136.685 101.191 135.508 99.3801 135.344 96.8521Z" fill="#FFBC96"/>
                <path d="M122.27 108.167C123.068 105.994 123.591 103.725 124.077 101.457C124.556 99.1848 124.959 96.8956 125.373 94.6089L126.597 87.7372C126.814 86.5925 127.036 85.4479 127.304 84.3086C127.44 83.7383 127.584 83.1706 127.765 82.6042C127.856 82.3217 127.956 82.0392 128.086 81.758C128.149 81.6174 128.225 81.4768 128.319 81.3362C128.421 81.2036 128.53 81.0352 128.804 80.949L128.783 80.9543C128.864 80.9383 128.952 80.9291 129.034 80.9277C129.112 80.9397 129.19 80.9702 129.265 80.9994C129.384 81.071 129.497 81.1612 129.559 81.25C129.713 81.4304 129.798 81.6148 129.883 81.7991C130.045 82.1665 130.161 82.5366 130.258 82.908C130.456 83.6507 130.594 84.3974 130.714 85.1442C130.949 86.6403 131.111 88.1417 131.236 89.6444C131.48 92.6513 131.61 95.6634 131.58 98.6821L130.94 98.6689C131.01 96.9645 131.139 95.2721 131.331 93.5797C131.537 91.8899 131.773 90.1962 132.305 88.5383C132.451 88.1258 132.61 87.7146 132.855 87.3207C132.984 87.1271 133.128 86.9308 133.343 86.7716C133.546 86.6005 133.863 86.5183 134.152 86.5581C134.22 86.574 134.284 86.5978 134.35 86.6204C134.417 86.6496 134.472 86.6482 134.557 86.7238C134.595 86.7544 134.632 86.7888 134.667 86.8233C134.697 86.8565 134.708 86.883 134.729 86.9122C134.762 86.9692 134.809 87.0329 134.829 87.0873C134.928 87.3101 134.993 87.525 135.048 87.7385C135.158 88.1669 135.221 88.5913 135.284 89.0171C135.504 90.7188 135.562 92.4231 135.548 94.1248C135.542 94.983 135.488 95.8159 135.562 96.6409C135.63 97.4685 135.781 98.2895 135.995 99.0973C136.209 99.905 136.483 100.698 136.78 101.486C137.085 102.266 137.39 103.092 137.561 103.933C137.924 105.619 137.964 107.351 137.808 109.048C137.65 110.747 137.318 112.422 136.875 114.062L136.631 113.999C137.041 112.362 137.338 110.696 137.463 109.02C137.586 107.346 137.513 105.653 137.131 104.03C136.947 103.212 136.666 102.444 136.347 101.65C136.041 100.856 135.757 100.048 135.532 99.2206C135.306 98.3917 135.144 97.5428 135.069 96.682C134.986 95.8186 135.037 94.9551 135.036 94.1209C135.04 92.4377 134.969 90.748 134.745 89.0914C134.683 88.6802 134.618 88.2664 134.513 87.8751C134.463 87.6801 134.402 87.4878 134.327 87.3247C134.31 87.2783 134.288 87.2597 134.27 87.2239C134.249 87.1801 134.236 87.1761 134.236 87.1761C134.25 87.1655 134.191 87.1549 134.162 87.143C134.127 87.1324 134.093 87.1151 134.058 87.1072C133.809 87.0674 133.537 87.2875 133.335 87.6218C133.134 87.9494 132.985 88.3314 132.852 88.7173C132.356 90.2917 132.127 91.9748 131.937 93.6434C131.757 95.3159 131.64 97.0056 131.582 98.6848L130.941 98.6715C130.969 95.678 130.871 92.6765 130.641 89.6895C130.526 88.1961 130.375 86.7053 130.151 85.2304C130.037 84.4943 129.904 83.7595 129.72 83.0486C129.629 82.6931 129.519 82.3429 129.38 82.022C129.311 81.8655 129.231 81.705 129.14 81.6002C129.096 81.5325 129.05 81.5127 129.017 81.4835C129.002 81.4848 128.991 81.4742 128.981 81.4689C128.97 81.4768 128.964 81.4782 128.952 81.4715L128.93 81.4768C128.92 81.4755 128.828 81.5445 128.765 81.644C128.697 81.7448 128.632 81.8588 128.576 81.9835C128.461 82.2302 128.364 82.4968 128.275 82.766C128.099 83.3072 127.953 83.8656 127.816 84.4266C127.546 85.55 127.316 86.688 127.095 87.8274L125.831 94.6872C125.414 96.9765 124.959 99.2604 124.442 101.532C123.916 103.8 123.353 106.07 122.508 108.253L122.27 108.167Z" fill="#D28572"/>
                <path d="M221.814 172.788C221.718 179.278 218.889 183.814 215.346 186.428L208.152 186.315L206.372 186.287H206.333L197.876 167.037L197.859 166.992L190.281 149.741L211.61 141.82L219.571 161.359C221.205 165.679 221.864 169.486 221.814 172.788Z" fill="#FFBC96"/>
                <path d="M215.91 165.02L159.917 171.284L152.632 168.93C149.333 167.865 145.721 168.52 143.013 170.683C136.66 175.758 126.331 185.111 129.008 185.863C133.515 187.131 139.401 184.758 144.685 183.432C147.605 182.698 150.688 183.046 153.366 184.417L157.03 186.294L212.81 186.286L215.91 165.02Z" fill="#FFBC96"/>
                <path d="M130.344 184.873C131.854 183.99 133.389 183.15 134.928 182.321C136.462 181.484 138.023 180.699 139.571 179.886C140.334 179.485 141.151 179.074 142.004 178.778C142.856 178.485 143.737 178.272 144.631 178.174C145.525 178.073 146.426 178.063 147.316 178.143C148.204 178.241 149.084 178.412 149.923 178.701L149.837 179.02C148.988 178.854 148.135 178.741 147.28 178.732C146.426 178.704 145.573 178.762 144.735 178.904C143.898 179.045 143.074 179.256 142.281 179.554C141.481 179.835 140.742 180.235 139.952 180.631C138.384 181.405 136.832 182.207 135.253 182.956C133.678 183.715 132.097 184.463 130.494 185.166L130.344 184.873Z" fill="#D28572"/>
                <path d="M131.156 182.074C132.174 181.111 133.236 180.207 134.322 179.328C135.403 178.443 136.519 177.599 137.661 176.789C138.809 175.988 139.971 175.203 141.206 174.524C142.446 173.872 143.709 173.163 145.19 173.023L145.218 173.352C144.578 173.521 143.954 173.77 143.359 174.116C142.75 174.421 142.166 174.791 141.584 175.158C140.419 175.894 139.283 176.687 138.146 177.476C137.008 178.266 135.891 179.091 134.757 179.894C133.63 180.706 132.506 181.528 131.368 182.329L131.156 182.074Z" fill="#D28572"/>
                <path d="M205.285 166.39L197.773 166.574L197.794 167.419L205.306 167.235L205.285 166.39Z" fill="#D28572"/>
                <path d="M185.084 138.502L184.578 138.75L190.032 149.855L190.538 149.607L185.084 138.502Z" fill="#E9EDF5"/>
                <path opacity="0.2" d="M221.815 172.788C221.719 179.278 218.89 183.814 215.347 186.428L208.153 186.315C208.148 186.305 208.148 186.298 208.148 186.288C207.883 185.347 207.742 184.355 207.742 183.33C207.742 178.67 210.638 174.693 214.733 173.093C215.972 172.609 217.32 172.344 218.728 172.344C219.797 172.344 220.835 172.496 221.815 172.788Z" fill="#FE5B52"/>
                <path d="M132.624 179.492H100.512V186.253H132.624V179.492Z" fill="#1B1B43"/>
                <path d="M111.404 186.253H60.4176L45.7695 135.268H96.7554L111.404 186.253Z" fill="#E9EDF5"/>
                <path d="M60.2147 186.311L45.4883 135.055H61.2625V135.478H46.0507L60.6205 186.193L60.2147 186.311Z" fill="#1B1B43"/>
                <path d="M105.942 167.612L96.5965 135.478H73.0938V135.055H96.9135L106.348 167.494L105.942 167.612Z" fill="#1B1B43"/>
                <path d="M109.075 6.46072C109.74 4.22377 108.466 1.87097 106.229 1.20561C103.992 0.540239 101.64 1.81426 100.974 4.05121C100.309 6.28816 101.583 8.64095 103.82 9.30632C106.057 9.97168 108.41 8.69767 109.075 6.46072Z" fill="#E9EDF5"/>
                <path d="M23.7421 113.037C24.4333 110.11 22.6207 107.177 19.6936 106.485C16.7665 105.794 13.8333 107.607 13.1421 110.534C12.4509 113.461 14.2635 116.394 17.1906 117.085C20.1177 117.777 23.0509 115.964 23.7421 113.037Z" fill="#E9EDF5"/>
                <path d="M285.202 126.709C287.536 126.709 289.428 124.817 289.428 122.484C289.428 120.15 287.536 118.258 285.202 118.258C282.868 118.258 280.977 120.15 280.977 122.484C280.977 124.817 282.868 126.709 285.202 126.709Z" fill="#E9EDF5"/>
                <path d="M279.927 125.251L279.916 124.687C280.598 124.674 281.269 124.557 281.91 124.337L282.093 124.87C281.397 125.109 280.668 125.236 279.927 125.251ZM277.749 124.956C277.042 124.746 276.375 124.426 275.765 124.007L276.085 123.543C276.646 123.927 277.26 124.222 277.911 124.417L277.749 124.956ZM284.037 123.845L283.7 123.393C284.246 122.985 284.723 122.499 285.118 121.947L285.576 122.275C285.147 122.874 284.63 123.402 284.037 123.845ZM274.165 122.499C273.712 121.914 273.352 121.267 273.1 120.574L273.63 120.382C273.862 121.019 274.192 121.615 274.61 122.154L274.165 122.499ZM286.564 120.31L286.027 120.138C286.234 119.493 286.339 118.819 286.339 118.136C286.339 117.963 286.332 117.792 286.319 117.622L286.88 117.577C286.894 117.761 286.902 117.948 286.901 118.136C286.902 118.879 286.788 119.61 286.564 120.31ZM272.679 118.419C272.675 118.326 272.673 118.233 272.672 118.139C272.672 117.49 272.759 116.85 272.931 116.233L273.473 116.383C273.315 116.951 273.236 117.541 273.236 118.136C273.236 118.221 273.238 118.309 273.241 118.395L272.679 118.419ZM285.85 115.652C285.593 115.025 285.24 114.443 284.801 113.921L285.232 113.559C285.708 114.126 286.092 114.757 286.372 115.439L285.85 115.652ZM274.311 114.542L273.84 114.232C274.246 113.615 274.742 113.067 275.317 112.603L275.671 113.041C275.143 113.469 274.685 113.973 274.311 114.542ZM283.276 112.591C282.7 112.228 282.076 111.958 281.416 111.79L281.556 111.243C282.271 111.426 282.951 111.72 283.576 112.113L283.276 112.591ZM277.423 112.026L277.22 111.501C277.907 111.234 278.63 111.078 279.369 111.035L279.4 111.598C278.721 111.637 278.056 111.781 277.423 112.026Z" fill="#9192FF"/>
                <path d="M185.208 23.3295C183.499 23.3295 182.109 21.9395 182.109 20.2311C182.109 18.5228 183.499 17.1328 185.208 17.1328C186.916 17.1328 188.306 18.5228 188.306 20.2311C188.306 21.9395 186.916 23.3295 185.208 23.3295ZM185.208 17.6965C183.81 17.6965 182.673 18.8332 182.673 20.2311C182.673 21.6291 183.81 22.7671 185.208 22.7671C186.606 22.7671 187.742 21.6291 187.742 20.2311C187.742 18.8332 186.604 17.6965 185.208 17.6965Z" fill="#9192FF"/>
                <path d="M306 186H2V187H306V186Z" fill="#727E95"/>
                </svg>
                </div>
                <div class="bpa-front-dcw__body">
                <div class="bpa-front-dcw__body-title" aria-label="Sorry to see you go. Please Confirm">Sorry to see you go. Please Confirm</div>
                <div class="bpa-front-dcw__body-sub-title" aria-label="All your data will be erased">All your data will be erased</div>
                [bookingpress_delete_account cancel_button_text = "cancel" delete_button_text="delete"]
                </div>
                </div>',
            );

            foreach ( $mybookings_shortcode_default_data as $mybookings_shortcode_data_key => $mybookings_shortcode_data_val ) {
                $bookingpress_customize_settings_db_fields = array(
                'bookingpress_setting_name'  => $mybookings_shortcode_data_key,
                'bookingpress_setting_value' => $mybookings_shortcode_data_val,
                'bookingpress_setting_type'  => 'booking_my_booking',
                );
                $resp = $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields);

                $db_data = array(
                    'last_query' => $wpdb->last_query,
                    'last_error' => $wpdb->last_error,
                    'resposne' => $resp
                );

                update_option( 'bpa_mybookings_shortcode_default_data'. $mybookings_shortcode_data_key.'_'. date( 'Y-m-d H:i:s', current_time('timestamp') ), wp_json_encode( $db_data) );
            }

            $form_fields_default_data = array(
                'fullname'      => array(
                    'field_name'     => 'fullname',
                    'field_type'     => 'Text',
                    'is_edit'        => 0,
                    'is_required'    => 0,
                    'label'          => wp_kses_post( __('Fullname', 'bookingpress-appointment-booking') ),
                    'placeholder'    => wp_kses_post( __('Enter your full name', 'bookingpress-appointment-booking') ),
                    'error_message'  => wp_kses_post(__('Please enter your full name', 'bookingpress-appointment-booking') ),
                    'is_hide'        => 1,
                    'field_position' => 1,
                ),
                'firstname'     => array(
                    'field_name'     => 'firstname',
                    'field_type'     => 'Text',
                    'is_edit'        => 0,
                    'is_required'    => true,
                    'label'          => wp_kses_post( __('Firstname', 'bookingpress-appointment-booking') ),
                    'placeholder'    => wp_kses_post( __('Enter your firstname', 'bookingpress-appointment-booking') ),
                    'error_message'  => wp_kses_post( __('Please enter your firstname', 'bookingpress-appointment-booking') ),
                    'is_hide'        => 0,
                    'field_position' => 2,
                ),
                'lastname'      => array(
                    'field_name'     => 'lastname',
                    'field_type'     => 'Text',
                    'is_edit'        => 0,
                    'is_required'    => true,
                    'label'          => wp_kses_post( __('Lastname', 'bookingpress-appointment-booking') ),
                    'placeholder'    => wp_kses_post( __('Enter your lastname', 'bookingpress-appointment-booking') ),
                    'error_message'  => wp_kses_post( __('Please enter your lastname', 'bookingpress-appointment-booking') ),
                    'is_hide'        => 0,
                    'field_position' => 3,
                ),
                'email_address' => array(
                    'field_name'     => 'email_address',
                    'field_type'     => 'Email',
                    'is_edit'        => 0,
                    'is_required'    => true,
                    'label'          => wp_kses_post( __('Email Address', 'bookingpress-appointment-booking') ),
                    'placeholder'    => wp_kses_post( __('Enter your email address', 'bookingpress-appointment-booking') ),
                    'error_message'  => wp_kses_post( __('Please enter your email address', 'bookingpress-appointment-booking') ),
                    'is_hide'        => 0,
                    'field_position' => 4,
                ),
                'phone_number'  => array(
                    'field_name'     => 'phone_number',
                    'field_type'     => 'Dropdown',
                    'is_edit'        => 0,
                    'is_required'    => true,
                    'label'          => wp_kses_post( __('Phone Number', 'bookingpress-appointment-booking') ),
                    'placeholder'    => wp_kses_post( __('Enter your phone number', 'bookingpress-appointment-booking') ),
                    'error_message'  => wp_kses_post( __('Please enter your phone number', 'bookingpress-appointment-booking') ),
                    'is_hide'        => 0,
                    'field_position' => 5,
                ),
                'note'          => array(
                    'field_name'     => 'note',
                    'field_type'     => 'Textarea',
                    'is_edit'        => 0,
                    'is_required'    => 0,
                    'label'          => wp_kses_post( __('Note', 'bookingpress-appointment-booking') ),
                    'placeholder'    => wp_kses_post( __('Enter note details', 'bookingpress-appointment-booking') ),
                    'error_message'  => wp_kses_post( __('Please enter appointment note', 'bookingpress-appointment-booking') ),
                    'is_hide'        => 0,
                    'field_position' => 6,
                ),
                'terms_condition'          => array(
                    'field_name'     => 'terms_and_conditions',
                    'field_type'     => 'terms_and_conditions',
                    'is_edit'        => 0,
                    'is_required'    => true,
                    'placeholder'    => '',
                    'label'          => str_replace( '&amp;', '&', wp_kses_post( __('I agree with <a target="_blank" href="#">terms & conditions</a>', 'bookingpress-appointment-booking') ) ),
                    'error_message'  => wp_kses_post( __('Please tick this box if you want to proceed', 'bookingpress-appointment-booking') ),
                    'is_hide'        => 1,
                    'field_position' => 7,
                ),
                'username'          => array(
                    'field_name'     => 'username',
                    'field_type'     => 'text',
                    'is_edit'        => 0,
                    'is_required'    => true,
                    'placeholder'    => wp_kses_post( __('Enter your username', 'bookingpress-appointment-booking') ),
                    'label'          => wp_kses_post( __('Username', 'bookingpress-appointment-booking') ),
                    'error_message'  => wp_kses_post( __('Please enter your username', 'bookingpress-appointment-booking') ),
                    'is_hide'        => 1,
                    'field_position' => 8,
                ),
            );

            foreach ( $form_fields_default_data as $form_field_key => $form_field_val ) {
                $form_field_db_data = array(
                    'bookingpress_form_field_name'     => $form_field_val['field_name'],
                    'bookingpress_field_required'      => $form_field_val['is_required'],
                    'bookingpress_field_label'         => stripslashes_deep($form_field_val['label']),
                    'bookingpress_field_placeholder'   => $form_field_val['placeholder'],
                    'bookingpress_field_error_message' => $form_field_val['error_message'],
                    'bookingpress_field_is_hide'       => $form_field_val['is_hide'],
                    'bookingpress_field_position'      => $form_field_val['field_position'],
                    'bookingpress_field_is_default'    => 1,
                );

                $resp = $wpdb->insert($tbl_bookingpress_form_fields, $form_field_db_data);
                $db_data = array(
                    'last_query' => $wpdb->last_query,
                    'last_error' => $wpdb->last_error,
                    'form_field_data' => $form_field_db_data,
                    'resposne' => $resp
                );

                update_option( 'bpa_defult_field_installation_details_'. $form_field_val['field_name'].'_'. date( 'Y-m-d H:i:s', current_time('timestamp') ), wp_json_encode( $db_data) );
            }

            $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';
            $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_booking_form_settings';                       
            $my_booking_form = array(
                'background_color' => $booking_form_shortcode_default_fields['background_color'],
                'row_background_color' => $booking_form_shortcode_default_fields['footer_background_color'],
                'primary_color' => $booking_form_shortcode_default_fields['primary_color'],
                'content_color' => $booking_form_shortcode_default_fields['content_color'],
                'border_color' => $booking_form_shortcode_default_fields['border_color'],
                'label_title_color'=>$booking_form_shortcode_default_fields['label_title_color'],
                'title_font_family' => $booking_form_shortcode_default_fields['title_font_family'],
                'sub_title_color' => $booking_form_shortcode_default_fields['sub_title_color'],
                'price_button_text_color' => '#fff',
            );        
            $booking_form = array(
                'background_color' => $booking_form_shortcode_default_fields['background_color'],
                'footer_background_color' => $booking_form_shortcode_default_fields['footer_background_color'],
                'border_color' => $booking_form_shortcode_default_fields['border_color'],
                'primary_color' => $booking_form_shortcode_default_fields['primary_color'],
                'primary_background_color'=> $booking_form_shortcode_default_fields['primary_background_color'],
                'label_title_color' => $booking_form_shortcode_default_fields['label_title_color'],
                'title_font_family' => $booking_form_shortcode_default_fields['title_font_family'],                
                'content_color' => $booking_form_shortcode_default_fields['content_color'],                
                'price_button_text_color' => '#fff',                
                'sub_title_color' => $booking_form_shortcode_default_fields['sub_title_color'],
            );        

            $bookingpress_custom_data_arr['booking_form'] = $booking_form;
            $bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;

            $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);
        }
        
        /**
         * Install default pages
         *
         * @return void
         */
        function bookingpress_install_default_pages()
        {   global $wpdb,$tbl_bookingpress_customize_settings;

            $post_table = $wpdb->posts;

            $post_author = get_current_user_id();

            $bookingpress_bookingformpage_content = '<!-- wp:shortcode -->[bookingpress_form]<!-- /wp:shortcode -->';
            $bookingpress_bookingform_page_details = array(
                'post_title'    => esc_html__('Book an Appointment', 'bookingpress-appointment-booking'),
                'post_name'     => 'book-appointment',
                'post_content'  => $bookingpress_bookingformpage_content,
                'post_status'   => 'publish',
                'post_parent'   => 0,
                'post_author'   => 1,
                'post_type'     => 'page',
                'post_author'   => $post_author,
                'post_date'     => current_time( 'mysql' ),
                'post_date_gmt' => current_time( 'mysql', 1 ),
            );
            
            $wpdb->insert( $post_table, $bookingpress_bookingform_page_details );
            $bookingpress_post_id = $wpdb->insert_id;

            $current_guid = get_post_field( 'guid', $bookingpress_post_id );
            $where = array( 'ID' => $bookingpress_post_id );
            if( '' === $current_guid ){
                $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $bookingpress_post_id ) ), $where );
            }

            $bookingpress_customize_settings_db_fields = array(
                'bookingpress_setting_name'  => 'default_booking_page',
                'bookingpress_setting_value' => $bookingpress_post_id,
                'bookingpress_setting_type'  => 'booking_form',
            );
            $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );


            $bookingpress_mybooking_content = '<!-- wp:shortcode -->[bookingpress_my_appointments]<!-- /wp:shortcode -->';
            $bookingpress_mybooking_page_details = array(
                'post_title'   => esc_html__('My Bookings', 'bookingpress-appointment-booking'),
                'post_name'    => 'my-bookings',
                'post_content' => $bookingpress_mybooking_content,
                'post_status'  => 'publish',
                'post_parent'  => 0,
                'post_author'  => 1,
                'post_type'    => 'page',
                'post_author'   => $post_author,
                'post_date'     => current_time( 'mysql' ),
                'post_date_gmt' => current_time( 'mysql', 1 ),
            );

            $wpdb->insert( $post_table, $bookingpress_mybooking_page_details );
            $bookingpress_post_id = $wpdb->insert_id;

            $current_guid = get_post_field( 'guid', $bookingpress_post_id );
            $where = array( 'ID' => $bookingpress_post_id );
            if( '' === $current_guid ){
                $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $bookingpress_post_id ) ), $where );
            }

            $bookingpress_customize_settings_db_fields = array(
                'bookingpress_setting_name'  => 'default_mybooking_page',
                'bookingpress_setting_value' => $bookingpress_post_id,
                'bookingpress_setting_type'  => 'booking_my_booking',
            );
            $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
            

            $bookingpress_thankyoupage_content = '
                <div class="bpa-front-thankyou-module-container">
                    <div class="bpa-front-tmc__head">
                        <svg width="100" height="100" viewBox="0 0 100 100" fill="none" class="bpa-front-tmc__vector--confirmation" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M90.7002 85.4607C84.4375 93.0096 72.3956 90.9928 62.8277 93.3708C53.5335 95.6808 44.3866 102.22 35.2915 99.2321C26.2099 96.2486 22.8396 85.5957 16.7964 78.2482C10.8588 71.0289 1.14803 65.6954 0.0978856 56.4421C-0.951171 47.1984 6.67152 39.5219 11.4957 31.5411C15.8676 24.3084 20.1527 17.1387 26.9344 12.0452C34.1982 6.58954 42.3994 1.52524 51.5153 1.4835C60.7 1.44146 68.4886 7.2199 76.4135 11.8187C84.8089 16.6905 95.7104 20.0477 99.0838 29.0875C102.453 38.1169 94.606 47.2495 93.1897 56.7733C91.7484 66.4649 96.9793 77.8921 90.7002 85.4607Z" class="bpa-front-vc__bg"/>
                            <path d="M8.13042 32.8242L8.16292 32.9233H8.25556L8.18062 32.9772L8.21164 33.0718L8.13042 33.0133L8.04921 33.0718L8.08023 32.9772L8.00529 32.9233H8.09792L8.13042 32.8242Z" stroke="#12D488" stroke-width="0.758084"/>
                            <path d="M98.6324 39.8248L98.6649 39.9239H98.7575L98.6826 39.9778L98.7136 40.0724L98.6324 40.0139L98.5512 40.0724L98.5822 39.9778L98.5072 39.9239H98.5999L98.6324 39.8248Z" stroke="#12D488" stroke-width="0.758084"/>
                            <path d="M70.5923 92.4332L70.6248 92.5323H70.7175L70.6425 92.5862L70.6736 92.6808L70.5923 92.6223L70.5111 92.6808L70.5421 92.5862L70.4672 92.5323H70.5598L70.5923 92.4332Z" stroke="#F5AE41" stroke-width="0.758084"/>
                            <path d="M47.4169 1.12892C47.4169 1.53965 47.0797 1.8788 46.6561 1.8788C46.2326 1.8788 45.8954 1.53965 45.8954 1.12892C45.8954 0.718185 46.2326 0.379042 46.6561 0.379042C47.0797 0.379042 47.4169 0.718185 47.4169 1.12892Z" stroke="#EE2445" stroke-opacity="0.7" stroke-width="0.758084"/>
                            <path d="M94.1486 65.4766C94.1486 65.8873 93.8114 66.2265 93.3878 66.2265C92.9643 66.2265 92.6271 65.8873 92.6271 65.4766C92.6271 65.0658 92.9643 64.7267 93.3878 64.7267C93.8114 64.7267 94.1486 65.0658 94.1486 65.4766Z" stroke="#EE2445" stroke-opacity="0.6" stroke-width="0.758084"/>
                            <path d="M2.51086 65.4766C2.51086 65.8873 2.17369 66.2265 1.75013 66.2265C1.32656 66.2265 0.989393 65.8873 0.989393 65.4766C0.989393 65.0658 1.32656 64.7267 1.75013 64.7267C2.17369 64.7267 2.51086 65.0658 2.51086 65.4766Z" stroke="#EE2445" stroke-opacity="0.6" stroke-width="0.758084"/>
                            <path d="M78.7639 11.7399H79.1429C79.1429 11.5408 78.9889 11.3756 78.7903 11.3617C78.5916 11.3479 78.4162 11.4901 78.3885 11.6872L78.7639 11.7399ZM77.6621 12.8239V12.4449C77.4602 12.4449 77.2938 12.6031 77.2836 12.8047C77.2733 13.0063 77.4229 13.1806 77.6237 13.201L77.6621 12.8239ZM78.7639 13.9977H78.3848C78.3848 14.2046 78.5508 14.3733 78.7577 14.3767C78.9646 14.3801 79.1359 14.2169 79.1427 14.0101L78.7639 13.9977ZM79.9417 12.8389V13.2179C80.1417 13.2179 80.3073 13.0625 80.3199 12.8628C80.3326 12.6631 80.1879 12.4881 79.9894 12.4629L79.9417 12.8389ZM78.3885 11.6872C78.3704 11.8168 78.3049 12.0223 78.1788 12.186C78.0617 12.3379 77.9023 12.4449 77.6621 12.4449V13.203C78.1818 13.203 78.548 12.9487 78.7791 12.6489C79.0012 12.3608 79.1068 12.0243 79.1393 11.7925L78.3885 11.6872ZM77.6237 13.201C77.7462 13.2135 77.9495 13.2749 78.1136 13.4065C78.2641 13.5273 78.3848 13.7071 78.3848 13.9977H79.1429C79.1429 13.451 78.8964 13.0626 78.5879 12.8151C78.2929 12.5785 77.9453 12.4717 77.7005 12.4468L77.6237 13.201ZM79.1427 14.0101C79.1471 13.8767 79.1964 13.666 79.3201 13.4991C79.4308 13.3498 79.6112 13.2179 79.9417 13.2179V12.4598C79.3603 12.4598 78.9582 12.7142 78.711 13.0478C78.477 13.3637 78.3933 13.7325 78.3851 13.9853L79.1427 14.0101ZM79.9894 12.4629C79.8426 12.4442 79.6106 12.3744 79.4257 12.2425C79.2503 12.1174 79.1429 11.9588 79.1429 11.7399H78.3848C78.3848 12.2686 78.67 12.6346 78.9854 12.8596C79.2913 13.0778 79.6481 13.1837 79.8939 13.2149L79.9894 12.4629Z" fill="#F4B125"/>
                            <line x1="78.6483" y1="10.6833" x2="78.6483" y2="10.3125" stroke="#F4B125" stroke-width="0.758084" stroke-linecap="round"/>
                            <line x1="78.6483" y1="16.3277" x2="78.6483" y2="15.2796" stroke="#F4B125" stroke-width="0.758084" stroke-linecap="round"/>
                            <path d="M80.623 12.8972H81.9908" stroke="#F4B125" stroke-width="0.758084" stroke-linecap="round"/>
                            <path d="M75.6084 12.8972H76.4632" stroke="#F4B125" stroke-width="0.758084" stroke-linecap="round"/>
                            <path class="bpa-head__vector-item" d="M75.3977 42.9786C74.4164 41.9652 73.4018 40.9175 73.0185 39.9995C72.6662 39.1561 72.6449 37.7592 72.6243 36.4081C72.5859 33.8939 72.5425 31.0443 70.5423 29.0632C68.5422 27.0821 65.6652 27.0391 63.1268 27.0011C61.7627 26.9807 60.3524 26.9595 59.5009 26.6106C58.5743 26.2309 57.5163 25.226 56.4931 24.2541C54.6959 22.5467 52.6584 20.6117 49.9422 20.6117C47.226 20.6117 45.1888 22.5467 43.3913 24.2541C42.3681 25.226 41.3104 26.2309 40.3835 26.6106C39.532 26.9595 38.1217 26.9807 36.7576 27.0011C34.2192 27.0391 31.3422 27.0821 29.3421 29.0632C27.3419 31.0443 27.2985 33.8939 27.2601 36.4081C27.2396 37.7592 27.2182 39.1561 26.8659 39.9995C26.4826 40.9173 25.468 41.9652 24.4868 42.9786C22.763 44.7587 20.8093 46.7768 20.8093 49.4671C20.8093 52.1575 22.763 54.1753 24.4868 55.9556C25.468 56.9691 26.4826 58.0167 26.8659 58.9348C27.2182 59.7782 27.2396 61.1751 27.2601 62.5262C27.2985 65.0404 27.3419 67.89 29.3421 69.8711C31.3422 71.8522 34.2192 71.8952 36.7576 71.9332C38.1217 71.9536 39.532 71.9747 40.3835 72.3237C41.3101 72.7034 42.3681 73.7082 43.3913 74.6802C45.1885 76.3875 47.226 78.3226 49.9422 78.3226C52.6584 78.3226 54.6957 76.3875 56.4931 74.6802C57.5163 73.7083 58.574 72.7034 59.5009 72.3237C60.3524 71.9747 61.7627 71.9536 63.1268 71.9332C65.6652 71.8952 68.5422 71.8522 70.5423 69.8711C72.5425 67.89 72.5859 65.0404 72.6243 62.5262C72.6449 61.1751 72.6662 59.7782 73.0185 58.9348C73.4018 58.017 74.4164 56.9691 75.3977 55.9556C77.1214 54.1756 79.0751 52.1575 79.0751 49.4671C79.0751 46.7768 77.1214 44.759 75.3977 42.9786ZM62.824 44.7748L47.5638 59.2025C47.1767 59.5685 46.6622 59.7727 46.1271 59.7727C45.592 59.7727 45.0775 59.5685 44.6904 59.2025L37.0604 51.9887C36.8626 51.8018 36.7039 51.5782 36.5934 51.3306C36.4829 51.083 36.4227 50.8162 36.4163 50.5456C36.4098 50.2749 36.4573 50.0057 36.556 49.7532C36.6547 49.5007 36.8026 49.27 36.9913 49.0741C37.18 48.8783 37.4058 48.7212 37.6559 48.6118C37.9059 48.5024 38.1752 48.4429 38.4485 48.4366C38.7217 48.4304 38.9936 48.4775 39.2484 48.5753C39.5033 48.6731 39.7362 48.8197 39.9339 49.0067L46.1271 54.8622L59.9505 41.7929C60.1482 41.6059 60.3811 41.4593 60.636 41.3615C60.8908 41.2636 61.1627 41.2165 61.4359 41.2228C61.7092 41.229 61.9785 41.2886 62.2285 41.3979C62.4786 41.5073 62.7044 41.6644 62.8931 41.8603C63.0818 42.0561 63.2298 42.2869 63.3284 42.5393C63.4271 42.7918 63.4746 43.0611 63.4681 43.3317C63.4617 43.6024 63.4015 43.8691 63.291 44.1167C63.1805 44.3643 63.0218 44.588 62.824 44.7748Z" />
                        </svg>
                        <div class="bpa-front-tmc__booking-id">
                            <div class="bpa-front-bi__label" aria-label="'.esc_html__('Booking ID', 'bookingpress-appointment-booking').'">'.esc_html__('Booking ID', 'bookingpress-appointment-booking').': <span class="bpa-front-bi__val">[booking_id]</span></div>
                        </div>
                        <div class="bpa-front-tmc__title" aria-label="' . esc_html__('Your Appointment Booked successfully!', 'bookingpress-appointment-booking') . '">' . esc_html__('Your Appointment Booked successfully!', 'bookingpress-appointment-booking') . '</div>
                        <p aria-label="' . esc_html__('We have sent your booking information to your email address.', 'bookingpress-appointment-booking') . '">' . esc_html__('We have sent your booking information to your email address.', 'bookingpress-appointment-booking') . '</p>
                    </div>
                    <div class="bpa-front-tmc__summary-content">
                        <div class="bpa-front-tmc__sc-item">
                            <div class="bpa-front-sc-item__label" aria-label="' . esc_html__('Service', 'bookingpress-appointment-booking') . '">' . esc_html__('Service:', 'bookingpress-appointment-booking') . '</div>
                            <div class="bpa-front-sc-item__val">[bookingpress_appointment_service]</div>
                        </div>
                        <div class="bpa-front-tmc__sc-item">
                            <div class="bpa-front-sc-item__label" aria-label="' . str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ) . '">' . str_replace( '&amp;', '&', esc_html__('Date & Time:', 'bookingpress-appointment-booking') ) . '</div>
                            <div class="bpa-front-sc-item__val">[bookingpress_appointment_datetime]</div>
                        </div>
                        <div class="bpa-front-tmc__sc-item">
                            <div class="bpa-front-sc-item__label" aria-label="' . esc_html__('Customer Name', 'bookingpress-appointment-booking') . '">' . esc_html__('Customer Name:', 'bookingpress-appointment-booking') . '</div>
                            <div class="bpa-front-sc-item__val">[bookingpress_appointment_customername]</div>
                        </div>
                    </div>
                    <div class="bpa-front-module--add-to-calendar">	
                        <div class="bpa-fm--atc__heading" aria-label="' . esc_html__('Add to Calendar', 'bookingpress-appointment-booking') . '">' . esc_html__('Add to Calendar', 'bookingpress-appointment-booking') . '</div>
                        [bookingpress_appointment_calendar_integration]
                    </div>
                </div>';

            $bookingpress_thankyou_page_details = array(
                'post_title'   => esc_html__('Thank you', 'bookingpress-appointment-booking'),
                'post_name'    => 'thank-you',
                'post_content' => $bookingpress_thankyoupage_content,
                'post_status'  => 'publish',
                'post_parent'  => 0,
                'post_author'  => 1,
                'post_type'    => 'page',
                'post_author'   => $post_author,
                'post_date'     => current_time( 'mysql' ),
                'post_date_gmt' => current_time( 'mysql', 1 ),
            );

            $wpdb->insert( $post_table, $bookingpress_thankyou_page_details );
            $bookingpress_post_id = $wpdb->insert_id;

            $current_guid = get_post_field( 'guid', $bookingpress_post_id );
            $where = array( 'ID' => $bookingpress_post_id );
            if( '' === $current_guid ){
                $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $bookingpress_post_id ) ), $where );
            }

            $bookingpress_thankyou_page_url = get_permalink($bookingpress_post_id);
            if (! empty($bookingpress_thankyou_page_url) ) {                
                $booking_form = array(
                    'after_booking_redirection' => $bookingpress_post_id,                    
                    'bookingpress_thankyou_msg' => $bookingpress_thankyoupage_content,
                );		
                foreach($booking_form as $key => $value) {
                    $bookingpress_customize_settings_db_fields = array(
                        'bookingpress_setting_name'  => $key,
                        'bookingpress_setting_value' => $value,
                        'bookingpress_setting_type'  => 'booking_form',
                    );
                    $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
                }
            }

            $bookingpress_cancelpage_content = '		
            <div class="bpa-front-cancel-module-container">
                <div class="bpa-front-cmc__title" aria-label="' . esc_html__('Sorry to hear that you have requested for cancel the appointment.', 'bookingpress-appointment-booking') . '">' . esc_html__('Sorry to hear that you have requested for cancel the appointment.', 'bookingpress-appointment-booking') . '</div>
                <div class="bpa-front-cmc__desc" aria-label="' . esc_html__('We have sent an email notification for cancel appointment. So please click on the button to cancel the appointment.', 'bookingpress-appointment-booking') . '">' . esc_html__('We have sent an email notification for cancel appointment. So please click on the button to cancel the appointment.', 'bookingpress-appointment-booking') . '</div>
            </div>';

            $bookingpress_cancel_page_details = array(
                'post_title'   => esc_html__('Appointment canceled', 'bookingpress-appointment-booking'),
                'post_name'    => 'cancel-appointment',
                'post_content' => $bookingpress_cancelpage_content,
                'post_status'  => 'publish',
                'post_parent'  => 0,
                'post_author'  => 1,
                'post_type'    => 'page',
                'post_author'   => $post_author,
                'post_date'     => current_time( 'mysql' ),
                'post_date_gmt' => current_time( 'mysql', 1 ),
            );

            $wpdb->insert( $post_table, $bookingpress_cancel_page_details );
            $bookingpress_post_id = $wpdb->insert_id;

            $current_guid = get_post_field( 'guid', $bookingpress_post_id );
            $where = array( 'ID' => $bookingpress_post_id );
            if( '' === $current_guid ){
                $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $bookingpress_post_id ) ), $where );
            }


            $bookingpress_cancel_page_url = get_permalink($bookingpress_post_id);
            if (! empty($bookingpress_cancel_page_url) ) {
                $my_booking_form = array(
                    'after_cancelled_appointment_redirection'     => $bookingpress_post_id,
                );		    
                foreach($my_booking_form as $key => $value) {
                    $bookingpress_customize_settings_db_fields = array(
                        'bookingpress_setting_name'  => $key,
                        'bookingpress_setting_value' => $value,
                        'bookingpress_setting_type'  => 'booking_my_booking',
                    );
                    $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
                }
            }

            // Cancel payment page
            $bookingpress_cancel_payment_page = '
            <div class="bpa-front-data-empty-view __bpa-is-guest-view">
                <h4>' . esc_html__('Sorry! Something went wrong. Your payment has been failed.', 'bookingpress-appointment-booking') . '</h4>
            </div>';

            $bookingpress_cancel_page_details = array(
                'post_title'   => esc_html__('Cancel Payment', 'bookingpress-appointment-booking'),
                'post_name'    => 'cancel-payment',
                'post_content' => $bookingpress_cancel_payment_page,
                'post_status'  => 'publish',
                'post_parent'  => 0,
                'post_author'  => 1,
                'post_type'    => 'page',
                'post_author'   => $post_author,
                'post_date'     => current_time( 'mysql' ),
                'post_date_gmt' => current_time( 'mysql', 1 ),
            );

            $wpdb->insert( $post_table, $bookingpress_cancel_page_details );
            $bookingpress_post_id = $wpdb->insert_id;

            $current_guid = get_post_field( 'guid', $bookingpress_post_id );
            $where = array( 'ID' => $bookingpress_post_id );
            if( '' === $current_guid ){
                $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $bookingpress_post_id ) ), $where );
            }

            $bookingpress_cancel_payment_url = get_permalink($bookingpress_post_id);
            if (! empty($bookingpress_cancel_payment_url) ) {
                $my_booking_form = array(
                    'after_failed_payment_redirection' => $bookingpress_post_id,
                    'bookingpress_failed_payment_msg' => $bookingpress_cancel_payment_page,
                );		    
                foreach($my_booking_form as $key => $value) {
                    $bookingpress_customize_settings_db_fields = array(
                        'bookingpress_setting_name'  => $key,
                        'bookingpress_setting_value' => $value,
                        'bookingpress_setting_type'  => 'booking_form',
                    );
                    $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
                }
            }

            // Appointment cancellation confirmation page
            $bookingpress_cancellation_confirmation_payment_content= '<!-- wp:shortcode -->[bookingpress_appointment_cancellation_confirmation]<!-- /wp:shortcode -->';
            $bookingpress_cancel_page_details = array(
                'post_title'   => esc_html__('Appointment cancellation confirmation', 'bookingpress-appointment-booking'),
                'post_name'    => 'appointment-cancellation-confirmation',
                'post_content' => $bookingpress_cancellation_confirmation_payment_content,
                'post_status'  => 'publish',
                'post_parent'  => 0,
                'post_author'  => 1,
                'post_type'    => 'page',
                'post_author'   => $post_author,
                'post_date'     => current_time( 'mysql' ),
                'post_date_gmt' => current_time( 'mysql', 1 ),
            );
            
            $wpdb->insert( $post_table, $bookingpress_cancel_page_details );
            $bookingpress_cancellation_post_id = $wpdb->insert_id;

            $current_guid = get_post_field( 'guid', $bookingpress_cancellation_post_id );
            $where = array( 'ID' => $bookingpress_cancellation_post_id );
            if( '' === $current_guid ){
                $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $bookingpress_cancellation_post_id ) ), $where );
            }

            $bookingpress_appointment_cancellation_payment_url = get_permalink($bookingpress_cancellation_post_id);
            if (! empty($bookingpress_appointment_cancellation_payment_url) ) {
                $bookingpress_my_booking_customize_setting['appointment_cancellation_confirmation'] = $bookingpress_cancellation_post_id;                
                foreach($bookingpress_my_booking_customize_setting as $key => $val){
                    $bookingpress_bd_data = array(
                        'bookingpress_setting_name' => $key,
                        'bookingpress_setting_value' => $val,
                        'bookingpress_setting_type' => 'booking_my_booking',
                    );
                    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
                }             
            }
        }
        
        /**
         * Change php date format to javascript date format
         *
         * @param  mixed $selected_date_format
         * @return void
         */
        function bookingpress_check_common_date_format( $selected_date_format )
        {
            $return_final_date_format              = '';
            $Bookingpres_elementer_default_formate = array(
            'Y' => 'yyyy',
            'y' => 'yy',
            'F' => 'MMMM',
            'M' => 'MMM',
            'm' => 'MM',
            'n' => 'M',
            'l' => 'dddd',
            'D' => 'ddd',
            'd' => 'dd',
            'j' => 'd',
            );
            $bookingpress_supported_date_formats   = array( 'd', 'D', 'm', 'M', 'y', 'Y', 'F', 'j', 'l', 'n' );

            if ($selected_date_format == 'F j, Y' ) {
                return 'MMMM d, yyyy';
            } elseif (substr_count($selected_date_format, '-') ) {
                $bookingpress_tmp_date_format_arr = explode('-', $selected_date_format);
                if (in_array($bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats) && in_array($bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats) && in_array($bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats) ) {

                    $return_final_date_format = '';
                    if (in_array($bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[0] ] . '-';
                    }
                    if (in_array($bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[1] ] . '-';
                    }
                    if (in_array($bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[2] ];
                    }
                    return $return_final_date_format;
                } else {
                    return 'MMMM d, yyyy';
                }
            } elseif (substr_count($selected_date_format, '/') ) {
                $bookingpress_tmp_date_format_arr = explode('/', $selected_date_format);

                if (in_array($bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats) && in_array($bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats) && in_array($bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats) ) {

                    $return_final_date_format = '';
                    if (in_array($bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[0] ] . '/';
                    }
                    if (in_array($bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[1] ] . '/';
                    }
                    if (in_array($bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[2] ];
                    }
                    return $return_final_date_format;
                } else {
                    return 'MMMM d, yyyy';
                }
            } elseif (substr_count($selected_date_format, ' ') ) {

                $bookingpress_tmp_date_format_arr = explode(' ', $selected_date_format);
                $return_final_date_format         = '';

                if (in_array($bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats) && in_array($bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats) && in_array($bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats) ) {

                    if (in_array($bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[0] ] . ' ';
                    }
                    if (in_array($bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[1] ] . ' ';
                    }
                    if (in_array($bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[2] ];
                    }
                    return $return_final_date_format;

                } else {
                    return 'MMMM d, yyyy';
                }
            } elseif (substr_count($selected_date_format, '.') ) {
                $bookingpress_tmp_date_format_arr = explode('.', $selected_date_format);
                $return_final_date_format         = '';

                if (in_array($bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats) && in_array($bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats) && in_array($bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats) ) {

                    if (in_array($bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[0] ] . '.';
                    }
                    if (in_array($bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[1] ] . '.';
                    }
                    if (in_array($bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats) ) {
                        $return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[2] ];
                    }
                    return $return_final_date_format;

                } else {
                    return 'MMMM d, yyyy';
                }
            } else {
                return 'MMMM d, yyyy';
            }
        }
       
        /**
         * Change PHP time format to javascript time format
         *
         * @param  mixed $selected_time_format
         * @return void
         */
        function bookingpress_check_common_time_format( $selected_time_format ) {
            if ( $selected_time_format == 'g:i a' ) {
                return 'h mm a';
            } else if( $selected_time_format == 'g:i A' ) {
                return 'h mm A';
            } else if( $selected_time_format == 'H:i' ) {
                return 'hh mm';
            } else if( $selected_time_format == 'H:i:s' ) {
                return 'hh mm ss';
            } else {
                return 'h mm a';
            }
        }
		        
        /**
         * bookingpress_write_response
         *
         * @param  mixed $response_data
         * @param  mixed $file_name
         * @param  mixed $mode - possible values | file - to write logs in file, db - to write logs in the database, both - to write logs in file and database both | default - file
         * @param  mixed $overwrite 
         * @return void
         */
        function bookingpress_write_response( $response_data, $file_name = '', $mode = 'file', $overwrite = false )
        {
            global $wp, $wpdb, $wp_filesystem;
            $file_path = BOOKINGPRESS_DIR . '/log/response.txt';
            if (file_exists(ABSPATH . 'wp-admin/includes/file.php') && ( 'file' == $mode || 'both' == $mode ) ) {
                include_once ABSPATH . 'wp-admin/includes/file.php';
                if (false === ( $creds = request_filesystem_credentials($file_path, '', false, false) ) ) {
                    return true;
                }

                if (! WP_Filesystem($creds) ) {
                    request_filesystem_credentials($file_path, $method, true, false);
                    return true;
                }
                @$file_data = $wp_filesystem->get_contents($file_path);
                $file_data .= $response_data;
                $file_data .= "\r\n===========================================================================\r\n";
                $breaks     = array( '<br />', '<br>', '<br/>' );
                $file_data  = str_ireplace($breaks, "\r\n", $file_data);

                if( true == $overwrite ){
                    $wp_filesystem->put_contents( $file_path, '', 0755 ); // clear file every time logs write
                }

                @$write_file = $wp_filesystem->put_contents($file_path, $file_data, 0755);
            }

            if( 'db' == $mode || 'both' == $mode ){
                $option_name = 'bpa_debug_log_' . current_time('timestamp');
                $option_value = $response_data;

                update_option( $option_name, $option_value );
            }
            return;
        }
                
        /**
         * Insert payment logs
         *
         * @param  mixed $bookingpress_log_payment_gateway
         * @param  mixed $bookingpress_log_event
         * @param  mixed $bookingpress_log_event_from
         * @param  mixed $bookingpress_payment_log_raw_data
         * @param  mixed $bookingpress_ref_id
         * @param  mixed $bookingpress_log_status
         * @return void
         */
        function bookingpress_write_payment_log( $bookingpress_log_payment_gateway, $bookingpress_log_event, $bookingpress_log_event_from = 'bookingpress', $bookingpress_payment_log_raw_data = '', $bookingpress_ref_id = 0, $bookingpress_log_status = 1 )
        {

            global $wpdb, $BookingPress,$bookingpress_debug_payment_log_id,$tbl_bookingpress_debug_payment_log;

            $bookingpress_log_payment_setting_name = $bookingpress_log_payment_gateway;
            $bookingpress_active_gateway           = false;
            if (! empty($bookingpress_log_payment_gateway) && $bookingpress_log_payment_gateway == 'on-site' ) {
                $bookingpress_log_payment_setting_name = 'on_site_payment';
            } elseif(!empty($bookingpress_log_payment_gateway)){
                $bookingpress_log_payment_setting_name = $bookingpress_log_payment_gateway.'_payment';
            }
            if (! empty($bookingpress_log_payment_setting_name) ) {
                $bookingpress_active_gateway = $BookingPress->bookingpress_get_settings($bookingpress_log_payment_setting_name, 'debug_log_setting');
            }
            $inserted_id = 0;
            if ($bookingpress_active_gateway == 'true' ) {
                if ($bookingpress_ref_id == null ) {
                    $bookingpress_ref_id = 0;
                }

                $bookingpress_database_log_data = array(
                'bookingpress_payment_log_ref_id'     => sanitize_text_field($bookingpress_ref_id),
                'bookingpress_payment_log_gateway'    => sanitize_text_field($bookingpress_log_payment_gateway),
                'bookingpress_payment_log_event'      => sanitize_text_field($bookingpress_log_event),
                'bookingpress_payment_log_event_from' => sanitize_text_field($bookingpress_log_event_from),
                'bookingpress_payment_log_status'     => sanitize_text_field($bookingpress_log_status),
                'bookingpress_payment_log_raw_data'   => wp_json_encode(stripslashes_deep($bookingpress_payment_log_raw_data)),
                'bookingpress_payment_log_added_date' => current_time('mysql'),
                );

                $wpdb->insert($tbl_bookingpress_debug_payment_log, $bookingpress_database_log_data);
                $inserted_id = $wpdb->insert_id;
                if (empty($bookingpress_ref_id) ) {
                    $bookingpress_ref_id = $inserted_id;
                }
            }
            $bookingpress_debug_payment_log_id = $bookingpress_ref_id;
            return $inserted_id;
        }
        
        /**
         * Download debug log files
         *
         * @return void
         */
        function bookingpress_debug_log_download_file()
        {

            if (! empty($_REQUEST['bookingpress_action']) && 'download_log' == sanitize_text_field($_REQUEST['bookingpress_action']) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

                $filename = ! empty($_REQUEST['file']) ? basename(sanitize_file_name($_REQUEST['file'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                if (! empty($filename) ) {
                    $file_path = BOOKINGPRESS_UPLOAD_DIR . '/' . $filename;

                    $allowexts = array( 'txt', 'zip' );

                    $file_name_bpa = substr($filename, 0, 3);

                    $checkext = explode('.', $filename);
                    $ext      = strtolower($checkext[ count($checkext) - 1 ]);

                    if (! empty($ext) && in_array($ext, $allowexts) && ! empty($filename) && file_exists($file_path) ) {
                        ignore_user_abort();
                        $now = gmdate('D, d M Y H:i:s');
                        header('Expires: Tue, 03 Jul 2020 06:00:00 GMT');
                        header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
                        header("Last-Modified: {$now} GMT");
                        header('Content-Type: application/force-download');
                        header('Content-Type: application/octet-stream');
                        header('Content-Type: application/download');
                        header("Content-Disposition: attachment;filename={$filename}");
                        header('Content-Transfer-Encoding: binary');

                        echo file_get_contents($file_path);

                        wp_delete_file($file_path);

                        $bpa_txt_file_name = str_replace('.zip', '.txt', $filename);
                        $bpa_txt_file_path = BOOKINGPRESS_UPLOAD_DIR . '/' . $bpa_txt_file_name;
                        if (file_exists($bpa_txt_file_path) ) {
                            wp_delete_file($bpa_txt_file_path);
                        }

                        die;
                    }
                }
            }
        }

                
        /**
         * View debug payment logs
         *
         * @return void
         */
        function bookingpress_view_debug_payment_log_func()
        {
            global $wpdb,$tbl_bookingpress_debug_payment_log;
            $response              = array();
            $response['variant']   = 'error';
            $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');
            
            $perpage     = isset($_POST['perpage']) ? intval($_POST['perpage']) : 20; // phpcs:ignore WordPress.Security.NonceVerification
            $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1; // phpcs:ignore WordPress.Security.NonceVerification
            $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

            $bpa_check_authorization = $this->bpa_check_authentication( 'view_debug_payment_logs', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $bookingpress_view_log_selector = isset($_REQUEST['bookingpress_debug_log_selector']) ? sanitize_text_field($_REQUEST['bookingpress_debug_log_selector']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

            if (! empty($bookingpress_view_log_selector) ) {
                /* $bookingpress_search_query  = ' WHERE 1=1';
                $bookingpress_search_query .= " AND bookingpress_payment_log_gateway = '{$bookingpress_view_log_selector}'"; */
                $total_payment_debug_logs   = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_debug_payment_log . " WHERE bookingpress_payment_log_gateway = %s ORDER BY bookingpress_payment_log_id DESC", $bookingpress_view_log_selector ), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason $tbl_bookingpress_debug_payment_log is table name
                $payment_debug_logs         = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_debug_payment_log . " WHERE bookingpress_payment_log_gateway = %s ORDER BY bookingpress_payment_log_id DESC LIMIT %d, %d", $bookingpress_view_log_selector, $offset , $perpage ), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason $tbl_bookingpress_debug_payment_log is table name
                                    
                $payment_debug_log_data = array();
                if (! empty($payment_debug_logs) ) {
                    $bookingpress_date_format = get_option('date_format');
                    foreach ( $payment_debug_logs as $payment_debug_log_key => $payment_debug_log_val ) {

                        $bookingpress_payment_log_id         = ! empty($payment_debug_log_val['bookingpress_payment_log_id']) ? intval($payment_debug_log_val['bookingpress_payment_log_id']) : '';
                        $bookingpress_payment_log_event      = ! empty($payment_debug_log_val['bookingpress_payment_log_event']) ? esc_html($payment_debug_log_val['bookingpress_payment_log_event']) : '';
                        $bookingpress_payment_log_raw_data   = ! empty($payment_debug_log_val['bookingpress_payment_log_raw_data']) ? $payment_debug_log_val['bookingpress_payment_log_raw_data'] : '';
                        $bookingpress_payment_log_added_date = ! empty($payment_debug_log_val['bookingpress_payment_log_added_date']) ? esc_html($payment_debug_log_val['bookingpress_payment_log_added_date']) : '';

                        $payment_debug_log_data[] = array(
                         'payment_debug_log_id'         => $bookingpress_payment_log_id,
                         'payment_debug_log_name'       => $bookingpress_payment_log_event,
                         'payment_debug_log_data'       => stripslashes_deep($bookingpress_payment_log_raw_data),
                         'payment_debug_log_added_date' => date($bookingpress_date_format, strtotime($bookingpress_payment_log_added_date)),
                        );
                    }
                }
            }
            $data['items'] = $payment_debug_log_data;
            $data['total'] = count($total_payment_debug_logs);

            // Modify debug logs data
            $data = apply_filters('bookingpress_modify_debug_log_data', $data, $_REQUEST);

            wp_send_json($data);
            exit;
        }

                
        /**
         * Clear debug payment logs
         *
         * @return void
         */
        function bookingpress_clear_debug_payment_log_func()
        {
            global $wpdb,$tbl_bookingpress_debug_payment_log;
            $response              = array();
            $response['variant']   = 'error';
            $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

            $bpa_check_authorization = $this->bpa_check_authentication( 'clear_debug_payment_logs', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingpress_view_log_selector = ! empty($_REQUEST['bookingpress_debug_log_selector']) ? sanitize_text_field($_REQUEST['bookingpress_debug_log_selector']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            if (! empty($bookingpress_view_log_selector) ) {
                // If data exists into payment debug log table then delete from that table.
                $wpdb->delete($tbl_bookingpress_debug_payment_log, array( 'bookingpress_payment_log_gateway' => $bookingpress_view_log_selector ), array( '%s' ));
                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Debug Logs Cleared Successfully.', 'bookingpress-appointment-booking');
            }

            do_action('bookingpress_delete_debug_log_from_outside', $_POST); // phpcs:ignore WordPress.Security.NonceVerification

            echo wp_json_encode($response);
            exit();
        }

                
        /**
         * Download payment logs
         *
         * @return void
         */
        function bookingpress_download_payment_log_func()
        {
            global $wpdb,$tbl_bookingpress_debug_payment_log;
            $response              = array();
            $response['variant']   = 'error';
            $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']       = esc_html__('Something went wrong', 'bookingpress-appointment-booking');

            $bpa_check_authorization = $this->bpa_check_authentication( 'download_debug_payment_logs', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingpress_view_log_selector          = ! empty($_REQUEST['bookingpress_debug_log_selector']) ? sanitize_text_field($_REQUEST['bookingpress_debug_log_selector']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $bookingpress_selected_download_duration = ! empty($_REQUEST['bookingpress_selected_download_duration']) ? sanitize_text_field($_REQUEST['bookingpress_selected_download_duration']) : 'all'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

            if (! empty($bookingpress_view_log_selector) && ! empty($bookingpress_selected_download_duration) ) {

                $bookingpress_debug_payment_log_where_cond = '';
                if (! empty($_REQUEST['bookingpress_selected_download_custom_duration']) && $bookingpress_selected_download_duration == 'custom' ) {
                    $bookingpress_start_date                   = !empty($_REQUEST['bookingpress_selected_download_custom_duration'][0]) ? date('Y-m-d 00:00:00', strtotime(sanitize_text_field($_REQUEST['bookingpress_selected_download_custom_duration'][0]))) : '';
                    $bookingpress_end_date                     = !empty($_REQUEST['bookingpress_selected_download_custom_duration'][1]) ? date('Y-m-d 23:59:59', strtotime(sanitize_text_field($_REQUEST['bookingpress_selected_download_custom_duration'][1]))) : '';
                    $bookingpress_debug_payment_log_where_cond = $wpdb->prepare(' AND (bookingpress_payment_log_added_date >= %s AND bookingpress_payment_log_added_date <= %s)', $bookingpress_start_date, $bookingpress_end_date);
                } elseif (! empty($bookingpress_selected_download_duration) && $bookingpress_selected_download_duration != 'custom' && $bookingpress_selected_download_duration != 'all' ) {
                    $bookingpress_last_selected_days           = date('Y-m-d', strtotime('-' . $bookingpress_selected_download_duration . ' days'));
                    $bookingpress_debug_payment_log_where_cond = $wpdb->prepare(' AND (bookingpress_payment_log_added_date >= %s)', $bookingpress_last_selected_days);
                }

                $bookingpress_debug_payment_log_query = 'SELECT * FROM `' . $tbl_bookingpress_debug_payment_log . "` WHERE `bookingpress_payment_log_gateway` = '" . $bookingpress_view_log_selector . "' AND `bookingpress_payment_log_status` = 1 " . $bookingpress_debug_payment_log_where_cond . ' ORDER BY bookingpress_payment_log_id DESC'; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_debug_payment_log is table name defined globally. False Positive alarm

                $bookingpress_debug_payment_log_query = apply_filters('bookingpress_modify_download_debug_log_query', $bookingpress_debug_payment_log_query, $bookingpress_view_log_selector, $_REQUEST);

                $bookingpress_payment_debug_log_data = $wpdb->get_results($bookingpress_debug_payment_log_query, ARRAY_A); //phpcs:ignore

                $bookingpress_payment_debug_log_data = apply_filters('bookingpress_modify_download_debug_log_data', $bookingpress_payment_debug_log_data, $bookingpress_view_log_selector, $_REQUEST);

                $bookingpress_download_data = wp_json_encode($bookingpress_payment_debug_log_data);

                if (! function_exists('WP_Filesystem') ) {
                    include_once ABSPATH . 'wp-admin/includes/file.php';
                }
                WP_Filesystem();
                global $wp_filesystem;

                $bookingpresss_debug_log_file_name = 'bookingpress_debug_logs_' . $bookingpress_view_log_selector . '_' . $bookingpress_selected_download_duration;
                $result                            = $wp_filesystem->put_contents(BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.txt', $bookingpress_download_data, 0777);

                $debug_log_file_name = '';

                if (class_exists('ZipArchive') ) {
                    $zip = new ZipArchive();
                    $zip->open(BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.zip', ZipArchive::CREATE);
                    $zip->addFile(BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.txt', $bookingpresss_debug_log_file_name . '.txt');
                    $zip->close();

                    $bookingpress_download_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpresss_debug_log_file_name . '.zip';
                    $debug_log_file_name       = $bookingpresss_debug_log_file_name . '.zip';
                } else {
                    $bookingpress_download_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpresss_debug_log_file_name . '.txt';
                    $debug_log_file_name       = $bookingpresss_debug_log_file_name . '.txt';
                }
                $response['variant']    = 'success';
                $response['title']      = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']        = esc_html__('log download successfully', 'bookingpress-appointment-booking');
                $response['url'] = admin_url('admin.php?page=bookingpress&module=settings&bookingpress_action=download_log&file=' . $debug_log_file_name);
                echo wp_json_encode($response);
                exit();
            }
            echo wp_json_encode($response);
            exit();
        }

        
        function appointment_sanatize_field( $data_array )
        {
            if( null == $data_array ){
                return $data_array;
            }

            if (is_array($data_array) ) {
                return array_map(array( $this, __FUNCTION__ ), $data_array);
            } else {
                if(preg_match( '/<[^<]+>/', $data_array ) ) {
                    global $bookingpress_global_options;                        
                    
                    $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
                    $bookingpress_allow_tag = json_decode($bookingpress_global_options_data['allowed_html'], true);

			        return wp_kses( $data_array, $bookingpress_allow_tag );
                } else {
                    return sanitize_text_field($data_array);
                }
            }
        }

        function bookingpress_gutenberg_category( $category, $post )
        {
            $new_category     = array(
            array(
            'slug'  => 'bookingpress',
            'title' => 'Bookingpress Blocks',
            ),
            );
            $final_categories = array_merge($category, $new_category);
            return $final_categories;
        }
	
        function bookingpress_select_date_before_load($date = '',$bookingpress_daysoff_date = array()){
			$bpa_current_date = $bpa_date = !empty($date) ? $date : date('Y-m-d'); // old
			$bpa_current_date = $bpa_date = !empty($date) ? $date : date('Y-m-d', current_time('timestamp')); // new change
            
            if(empty($bookingpress_daysoff_date)) {
                // reputelog -> need to check this function execution ahead
                return $bpa_current_date;
			    //$bookingpress_daysoff_date = $this->bookingpress_get_default_dayoff_dates();
            }
            $bpa_current_date =  date('c',strtotime($bpa_current_date));
            
			if(!empty($bookingpress_daysoff_date) && is_array($bookingpress_daysoff_date)) {
				foreach($bookingpress_daysoff_date as $k => $v){
					$daysoff_tmp_date = date('Y-m-d', strtotime($v));
					if($daysoff_tmp_date == $bpa_date){
						$bpa_current_date  = date('Y-m-d',strtotime($bpa_date.'+1 Day'));					
						return $this->bookingpress_select_date_before_load($bpa_current_date,$bookingpress_daysoff_date);
					}
				}	
			} 
			return $bpa_date;			
		}	
        
        /**
         * Get appointment customers list
         *
         * @param  mixed $search_user_str
         * @param  mixed $customer_id
         * @return void
         */
        function bookingpress_get_appointment_customer_list($search_user_str = '',$customer_id = '')
        {
            global $wpdb,$tbl_bookingpress_customers;
            $bookingpress_search_query_where = 'WHERE 1=1';
            $bookingpress_search_query_where .= $wpdb->prepare( ' AND cs.bookingpress_user_type = %d AND cs.bookingpress_user_status = %d ', 2,1 );
            if(!empty($search_user_str)) {
                if(!empty($customer_id )) {
                    $bookingpress_search_query_where .= "AND ((cs.bookingpress_user_login LIKE '%{$search_user_str}%' OR cs.bookingpress_user_email LIKE '%{$search_user_str}%' OR cs.bookingpress_user_firstname LIKE '%{$search_user_str}%' OR cs.bookingpress_user_lastname LIKE '%{$search_user_str}%') OR (cs.bookingpress_customer_id = {$customer_id})) ";
                } else {
                    $bookingpress_search_query_where .= "AND (cs.bookingpress_user_login LIKE '%{$search_user_str}%' OR cs.bookingpress_user_email LIKE '%{$search_user_str}%' OR cs.bookingpress_user_firstname LIKE '%{$search_user_str}%' OR cs.bookingpress_user_lastname LIKE '%{$search_user_str}%') ";
                }
            }   
            if(empty($search_user_str) && !empty($customer_id )) {
                $bookingpress_search_query_where .= $wpdb->prepare( 'AND (cs.bookingpress_customer_id =%d) ', $customer_id );
            }
            $bookingpress_search_join_query  = '';
            $bookingpress_search_join_query  = apply_filters('bookingpress_appointment_customer_list_join_filter', $bookingpress_search_join_query);
            $bookingpress_search_query_where = apply_filters('bookingpress_appointment_customer_list_filter', $bookingpress_search_query_where);

            $bookingpress_customer_details = $wpdb->get_results('SELECT cs.bookingpress_user_phone,cs.bookingpress_customer_id,cs.bookingpress_customer_id,cs.bookingpress_user_firstname,cs.bookingpress_user_lastname,cs.bookingpress_user_email FROM ' . $tbl_bookingpress_customers . ' as cs ' . $bookingpress_search_join_query . ' ' . $bookingpress_search_query_where .' group by cs.bookingpress_customer_id', ARRAY_A); //phpcs:ignore
            $bookingpress_customer_selection_details = array();
            foreach ( $bookingpress_customer_details as $bookingpress_customer_key => $bookingpress_customer_val ) {
                $bookingpress_customer_name = ( $bookingpress_customer_val['bookingpress_user_firstname'] == '' && $bookingpress_customer_val['bookingpress_user_lastname'] == '' ) ? $bookingpress_customer_val['bookingpress_user_email'] : $bookingpress_customer_val['bookingpress_user_firstname'] . ' ' . $bookingpress_customer_val['bookingpress_user_lastname'];
                if(empty($bookingpress_customer_name) && $bookingpress_customer_val['bookingpress_user_phone']) {
                    $bookingpress_customer_name = $bookingpress_customer_val['bookingpress_user_phone'];
                }
                $bookingpress_customer_selection_details[] = array(
                'text'  => stripslashes_deep($bookingpress_customer_name),
                'value' => $bookingpress_customer_val['bookingpress_customer_id'],
                );
            }            
            return $bookingpress_customer_selection_details;
        }        
        
        /**
         * Get search customers list
         *
         * @param  mixed $search_user_str
         * @return void
         */
        function bookingpress_get_search_customer_list($search_user_str = '')
        {
            global $wpdb,$tbl_bookingpress_customers;            
            $bookingpress_search_query_where = 'WHERE 1=1';
            $bookingpress_search_query_where .= $wpdb->prepare( ' AND cs.bookingpress_user_type = %d AND cs.bookingpress_user_status = %d ', 2,1 );
            if(!empty($search_user_str)) {
                $bookingpress_search_query_where .= "AND (cs.bookingpress_user_login LIKE '%{$search_user_str}%' OR cs.bookingpress_user_email LIKE '%{$search_user_str}%' OR cs.bookingpress_user_firstname LIKE '%{$search_user_str}%' OR cs.bookingpress_user_lastname LIKE '%{$search_user_str}%' OR cs.bookingpress_customer_full_name LIKE '%{$search_user_str}%') ";
            }                
            $bookingpress_search_join_query  = '';
            $bookingpress_search_join_query  = apply_filters('bookingpress_search_customer_list_join_filter', $bookingpress_search_join_query);
            $bookingpress_search_query_where = apply_filters('bookingpress_search_customer_list_filter', $bookingpress_search_query_where);

            $bookingpress_customer_details = $wpdb->get_results('SELECT cs.bookingpress_customer_id,cs.bookingpress_user_firstname,cs.bookingpress_user_lastname,cs.bookingpress_user_email FROM ' . $tbl_bookingpress_customers . ' as cs ' . $bookingpress_search_join_query . ' ' . $bookingpress_search_query_where, ARRAY_A); //phpcs:ignore
            $bookingpress_customer_selection_details = array();

            foreach ( $bookingpress_customer_details as $bookingpress_customer_key => $bookingpress_customer_val ) {
                $bookingpress_customer_name = ( $bookingpress_customer_val['bookingpress_user_firstname'] == '' && $bookingpress_customer_val['bookingpress_user_lastname'] == '' ) ? $bookingpress_customer_val['bookingpress_user_email'] : $bookingpress_customer_val['bookingpress_user_firstname'] . ' ' . $bookingpress_customer_val['bookingpress_user_lastname'];

                $bookingpress_customer_selection_details[] = array(
                'text'  => stripslashes_deep($bookingpress_customer_name),
                'value' => $bookingpress_customer_val['bookingpress_customer_id'],
                );
            }

            return $bookingpress_customer_selection_details;
        }

        /**
         * Cleaning up transient data
         * 
         * @return void
         */
        function bookingpress_cleanup_transient_data_hook_callback(){
            global $wpdb,$tbl_bookingpress_transient;

            $time_before_hour = current_time( 'timestamp' ) - DAY_IN_SECONDS;

            $wpdb->query( $wpdb->prepare( "DELETE FROM {$tbl_bookingpress_transient} WHERE bookingpress_transient_expiry <= %s", $time_before_hour ) ); //phpcs:ignore

        }
        
        /**
         * Send anonymous data through cron
         *
         * @return void
         */
        function bookingpress_send_anonymous_data_cron() {


            $bookingpress_send_anonymous_data = $this->bookingpress_get_settings('anonymous_data','general_setting'); 
            
            if($bookingpress_send_anonymous_data == 'true') {

                global $wpdb,$tbl_bookingpress_services,$tbl_bookingpress_customers,$tbl_bookingpress_form_fields,$tbl_bookingpress_payment_logs,$tbl_bookingpress_appointment_bookings, $wp_version;

                $bookingpress_total_services = $bookingpress_total_staffmember = $bookingpress_total_service_extras = $bookingpress_total_customers = $bookingpress_total_payment_transactions = $bookingpress_with_deposit_payments = $bookingpress_without_deposit_payments = $bookingpress_booking_from_backend = $bookingpress_total_appointment_custom_fields = $bookingpress_total_customer_custom_fields = 0;
    
                $activated_modules = $inactivated_modules = $active_plugins_arr = $bookingpress_gateway_wise_transactions = $inactive_plugin_arr = array();

                $bookingpress_lite_version = $boookingpress_lite_installation_date = $bookingpress_pro_version = $boookingpress_pro_installation_date =
                $home_url = $admin_url = $site_timezone = $site_locale = '';

                $bookingpress_lite_version = get_option('bookingpress_version');
                $bookingpress_lite_version = !empty($bookingpress_lite_version) ? $bookingpress_lite_version : '';
                $boookingpress_lite_installation_date = get_option('bookingpress_install_date');
                $boookingpress_lite_installation_date = !empty($boookingpress_lite_installation_date) ? $boookingpress_lite_installation_date : '';
                $bookingpress_pro_version = get_option('bookingpress_pro_version');
                $bookingpress_pro_version = !empty($bookingpress_pro_version) ? $bookingpress_pro_version : '';
                $boookingpress_pro_installation_date = get_option('bookingpress_pro_install_date');
                $boookingpress_pro_installation_date = !empty($boookingpress_pro_installation_date) ? $boookingpress_pro_installation_date : '';
                $home_url = home_url();
                $admin_url = admin_url();
                $site_locale = get_locale();
                $site_locale = !empty($site_locale) ? $site_locale : '';
                $site_timezone = wp_timezone_string();

                $tbl_bookingpress_staffmembers =  $wpdb->prefix.'bookingpress_staffmembers';
                $tbl_bookingpress_extra_services =  $wpdb->prefix.'bookingpress_extra_services';

                $server_information = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field($_SERVER['SERVER_SOFTWARE']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $my_theme   = wp_get_theme();
                $theme_data = $my_theme->get('Name').'  ('.$my_theme->get('Version').' )';
                $is_multisite = is_multisite() ? 'Yes' : 'NO';
                    
                $plugin_list    = get_plugins();                               
                $active_plugins = get_option('active_plugins');

                foreach ( $plugin_list as $key => $plugin_detail ) {
                    $is_active = in_array($key, $active_plugins);
                    if ($is_active == 1 ) {
                        $name      = substr($key, 0, strpos($key, '/'));
                        $active_plugins_arr[] = array(            
                            $plugin_detail['Name'] => $plugin_detail['Version']
                        );
                    } else {
                        $inactive_plugin_arr[]  = array(            
                            $plugin_detail['Name'] => $plugin_detail['Version']
                        );
                    }
                }

                $bookingpress_currency = $this->bookingpress_get_settings('payment_default_currency','payment_setting');

                $bookingpress_module = array(
                    'bookingpress_coupon_module' => 'Coupon Management',
                    'bookingpress_staffmember_module' => 'Staff Member Management',
                    'bookingpress_deposit_payment_module' => 'Desposit Payment',
                    'bookingpress_bring_anyone_with_you_module' => 'Bring Quantity with You',
                    'bookingpress_service_extra_module' => 'Service Extra',
                );
                foreach($bookingpress_module as $key => $value) {                    
                    $is_module_active = get_option($key);
                    if($is_module_active == 'true') {
                        $activated_modules[] = $value;
                    } else {
                        $inactivated_modules[] = $value;
                    }                    
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
                $active_addon_list = $inactive_addon_list =  array();
                if ( ! is_wp_error( $bookingpress_addons_res ) ) {
                    $bookingpress_body_res = base64_decode( $bookingpress_addons_res['body'] );
                    if ( ! empty( $bookingpress_body_res ) ) {
                        $bookingpress_body_res = json_decode( $bookingpress_body_res, true );
                        foreach ( $bookingpress_body_res as $bookingpress_body_key => $bookingpress_body_data_arr ) {  
                            foreach ( $bookingpress_body_data_arr as $bookingpress_body_data_key => $bookingpress_body_val ) {
                                if(!empty($bookingpress_body_val['addon_installer'])) {
                                    if(file_exists( WP_PLUGIN_DIR . '/'.$bookingpress_body_val['addon_installer'])) {        
                                        $is_addon_active = is_plugin_active($bookingpress_body_val['addon_installer']);
                                        if($is_addon_active) {
                                            $active_addon_list[$bookingpress_body_val['addon_name']] = $bookingpress_body_val['addon_version'];
                                        } else {
                                            $inactive_addon_list[$bookingpress_body_val['addon_name']] = $bookingpress_body_val['addon_version'];
                                        } 
                                    }
                                }
                            }
                        }
                    }
                } 
                
                $bookingpress_total_services = $wpdb->get_var( "SELECT count(bookingpress_service_id) FROM {$tbl_bookingpress_services}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm 

                $bookingpress_total_customers = $wpdb->get_var( "SELECT count(bookingpress_customer_id) FROM {$tbl_bookingpress_customers}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_customers is a table name. false alarm 

                $bookingpress_total_fields = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_form_fields}",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm 

                $bookingpress_total_payment_transactions = $wpdb->get_var( "SELECT count(bookingpress_payment_log_id) FROM {$tbl_bookingpress_payment_logs}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm 

                $bookingpress_total_transactions = $wpdb->get_results( "SELECT count(bookingpress_payment_log_id) as total ,bookingpress_payment_gateway FROM {$tbl_bookingpress_payment_logs} GROUP BY bookingpress_payment_gateway",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm 

                $bookingpress_total_appointmentes = $wpdb->get_results( "SELECT ap.*,pl.bookingpress_payment_gateway FROM {$tbl_bookingpress_appointment_bookings} as ap LEFT JOIN {$tbl_bookingpress_payment_logs} as pl ON pl.bookingpress_payment_log_id = ap.bookingpress_payment_id",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm 

                if(!empty($bookingpress_total_fields)) {
                    foreach($bookingpress_total_fields as $key => $value){
                        if(isset($value['bookingpress_field_is_default']) && $value['bookingpress_field_is_default'] == 0 && isset($value['bookingpress_is_customer_field']) && $value['bookingpress_is_customer_field'] == 0) {
                            $bookingpress_total_appointment_custom_fields++;
                        } elseif(isset($value['bookingpress_field_is_default']) && isset($value['bookingpress_is_customer_field']) && $value['bookingpress_is_customer_field'] == 1 ) {
                            $bookingpress_total_customer_custom_fields++;
                        }
                    }
                }   

                if(!empty($bookingpress_total_appointmentes)) {
                    foreach($bookingpress_total_appointmentes as $key => $value){
                        $bookingpress_deposit_details = !empty($value['bookingpress_deposit_payment_details']) ? json_decode
                        ($value['bookingpress_deposit_payment_details'], TRUE) : array();
						if(!empty($bookingpress_deposit_details) && !empty($bookingpress_deposit_details['deposit_selected_type']) && !empty($value['bookingpress_deposit_amount']) && !empty($value['bookingpress_payment_gateway']) && $value['bookingpress_payment_gateway'] ){
                            $bookingpress_with_deposit_payments++; 
                        } else {
                            $bookingpress_without_deposit_payments++;
                        }                                                    
                        if(!empty($value['bookingpress_payment_gateway']) && $value['bookingpress_payment_gateway'] == 'manual'){
                            $bookingpress_booking_from_backend++;   
                        }
                    }
                }                
                if(!empty($bookingpress_total_transactions)) {
                    foreach($bookingpress_total_transactions as $key => $value) {
                       $bookingpress_gateway_wise_transactions[$value['bookingpress_payment_gateway']] =  $value['total'];
                    }
                }                

                $bookingpress_staffmember_table_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema=%s AND table_name=%s",DB_NAME,$tbl_bookingpress_staffmembers));
                if($bookingpress_staffmember_table_exists) {
                    $bookingpress_total_staffmember = $wpdb->get_var( "SELECT count(bookingpress_staffmember_id) FROM {$tbl_bookingpress_staffmembers}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm                 
                }

                $bookingpress_extra_services_table_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema=%s AND table_name=%s",DB_NAME,$tbl_bookingpress_extra_services));
                if($bookingpress_extra_services_table_exists == 1)                {
                    $bookingpress_total_service_extras = $wpdb->get_var( "SELECT count(bookingpress_extra_services_id) FROM {$tbl_bookingpress_extra_services}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm 
                }                

                $total_wp_users =  count_users();
                $total_wp_users = $total_wp_users['total_users'];                              
              
                $bookingpress_anonymous_data = array(
                    'php_version'                             => phpversion(),
                    'bookingpress_lite_version'               => $bookingpress_lite_version,
                    'boookingpress_lite_installation_date'    => $boookingpress_lite_installation_date,
                    'bookingpress_pro_version'                => $bookingpress_pro_version,
                    'boookingpress_pro_installation_date'     => $boookingpress_pro_installation_date,
                    'wp_version'                              => $wp_version,
                    'server_information'                      => $server_information,
                    'is_multisite'                            => $is_multisite,
                    'theme_data'                              => $theme_data,
                    'home_url'                                => $home_url,
                    'admin_url'                               => $admin_url,
                    'active_plugin_list'                      => wp_json_encode($active_plugins_arr),
                    'inactivate_plugin_list'                  => wp_json_encode($inactive_plugin_arr),
                    'site_locale'                             => $site_locale,
                    'site_timezone'                           => $site_timezone,
                    'bookingpress_currency'                   => $bookingpress_currency,
                    'activated_modules'                       => wp_json_encode($activated_modules),
                    'inactive_modules'                        => wp_json_encode($inactivated_modules),
                    'activated_addons'                        => wp_json_encode($active_addon_list),
                    'inactive_addons'                         => wp_json_encode($inactive_addon_list),
                    'total_staffmembers'                      => $bookingpress_total_staffmember,
                    'total_services'                          => $bookingpress_total_services,
                    'total_service_extras'                    => $bookingpress_total_service_extras,
                    'total_wp_users'                          => $total_wp_users,
                    'total_bookingpress_customers'            => $bookingpress_total_customers,
                    'total_appointment_custom_fields'         => $bookingpress_total_appointment_custom_fields,
                    'total_customer_custom_fields'            => $bookingpress_total_customer_custom_fields,
                    'total_payment_transactions'              => $bookingpress_total_payment_transactions,
                    'payment_gateway_wise_transaction'        => wp_json_encode($bookingpress_gateway_wise_transactions),
                    'total_bookings_with_deposit_payments'    => $bookingpress_with_deposit_payments,
                    'total_bookings_without_deposit_payments' => $bookingpress_without_deposit_payments,
                    'total_bookings_made_from_backend'        => $bookingpress_booking_from_backend,
                );    
                $url = '';
                $url = 'https://bookingpressplugin.com/bpa_misc/bp_tracking_usage.php';
                $response = wp_remote_post(
                    $url,
                    array(
                    'timeout' => 500,
                    'body'    => array( 'bpa_anonymous_data' =>  wp_json_encode($bookingpress_anonymous_data)),
                    )
                );                                
            }
        }        
                
        /**
         * Generate customize module CSS at front side
         *
         * @param  mixed $bookingpress_custom_data_arr
         * @return void
         */
        function bookingpress_generate_customize_css_func($bookingpress_custom_data_arr) {
            global $BookingPress;
            $bookingpress_customize_css_content = '';
            $bookingpress_customize_css_key     = get_option('bookingpress_custom_css_key', true); 

            if(!empty($bookingpress_custom_data_arr['action'])) {
                update_option('bookingpress_customize_changes_notice', 1);
                update_option('bookingpress_customize_changes_notice_1.0.51',0);

                foreach($bookingpress_custom_data_arr['action'] as $key => $value) {            
                    $bookingpress_customize_css_content = '';
                    if( $value == 'bookingpress_save_my_booking_settings') {
                        $shortcode_background_color = $bookingpress_custom_data_arr['my_booking_form']['background_color'];
			            $shortcode_footer_background_color = $bookingpress_custom_data_arr['my_booking_form']['row_background_color'];
                        $border_color               = $bookingpress_custom_data_arr['my_booking_form']['border_color'];
                        $primary_color              = $bookingpress_custom_data_arr['my_booking_form']['primary_color'];
                        $label_title_color          = $bookingpress_custom_data_arr['my_booking_form']['label_title_color'];
                        $content_color              = $bookingpress_custom_data_arr['my_booking_form']['content_color'];
                        $sub_title_color            = $bookingpress_custom_data_arr['my_booking_form']['sub_title_color'];
                        $title_font_size            = '18px';
                        $title_font_family          = $bookingpress_custom_data_arr['my_booking_form']['title_font_family'];
                        $title_font_family          = $title_font_family == 'Inherit Fonts' ? 'inherit' : $title_font_family;
                        $price_button_color         = $bookingpress_custom_data_arr['my_booking_form']['price_button_text_color'];                        
                        $content_font_size          = '14px';
                        $sub_title_font_size        = '16px';
                        $hex                        = $primary_color;
                        list($r, $g, $b)            = is_string($hex) ? sscanf($hex, '#%02x%02x%02x') : sscanf('#12D488', '#%02x%02x%02x');
                        $primary_alpha_color        = "rgba($r,$g,$b,0.06)";
                        $border_hex                 = $content_color;
                        list($r, $g, $b)            = is_string($border_hex) ? sscanf($border_hex, '#%02x%02x%02x') : sscanf('#535D71', '#%02x%02x%02x');
                        $placeholder_color          = "rgba($r,$g,$b,0.75)";                       
                        

                        $bookingpress_customize_css_content .= '
                        .bpa-front-cp-top-navbar,.bpa-front-cp-card,
                        .bpa-front-data-empty-view--my-bookings.__bpa-is-guest-view,
                        .bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card,
                        .el-popover,
                        .bpa-front-cp-custom-popover,
                        .bpa-tn__dropdown-menu,
                        .bpa-front-ma-view-appointment-card,
                        .bpa-vac-pd__item.__bpa-pd-is-total-item,
                        .bpa-front-cp-cancel-mob-drawer {
                            background-color:'.$shortcode_background_color.' !important;
                        }
                        .bpa-front-data-empty-view--my-bookings .bpa-front-dev__form-bg{
                            fill:'.$shortcode_background_color.' !important;
                        }';
                        $bookingpress_customize_css_content .= '
                        .bpa-cp-ma-table.el-table--striped .el-table__body tr.el-table__row--striped td.el-table__cell,
                        .bpa-front-toast-notification.--bpa-error,
                        .bpa-front-toast-notification.--bpa-success,
                        .bpa-front-ma--pagination-wrapper.__bpa-is-sticky,
                        .el-year-table td.disabled .cell,
                        .el-month-table td.disabled .cell{
                            background-color: '.$shortcode_footer_background_color.' !important;
                        }
                        .bpa-front-data-empty-view--my-bookings .bpa-front-dev__panel-bg{
                            fill: '.$shortcode_footer_background_color.' !important;
                        }';

                        $bookingpress_customize_css_content .= '
                        .bpa-front-cp-top-navbar,
                        .bpa-front-cp-card,
                        .bpa-front-form-control input,
                        .el-date-picker__time-header .el-input .el-input__inner,
                        .bpa-cp-ma-table.el-table .el-table__header-wrapper tr th.el-table__cell,
                        .bpa-front-ma-view-appointment-card,
                        .bpa-ma-vac-sec-title,
                        .bpa-ma-vac--head__right .bpa-front-pill,
                        .bpa-vac-pd__item.__bpa-pd-is-total-item,
                        .bpa-front-btn__small,
                        .bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card,
                        .el-popover,
                        .el-popconfirm .el-popconfirm__action,
                        .bpa-front-cp-custom-popover,
                        .bpa-tn__dropdown-menu,
                        .bpa-front-form-control input:focus, 
                        .bpa-front-form-control .el-textarea__inner:focus, 
                        .el-date-picker__time-header .el-input .el-input__inner:focus,
                        .bpa-cp-ma-table.el-table td.el-table__cell,
                        .bpa-front-ma--pagination-wrapper .el-pager li,
                        .bpa-front-cp-custom-popover .el-date-picker__header--bordered,
                        .bpa-custom-datepicker .el-date-picker__header--bordered,
                        .bpa-tn__dropdown-head .bpa-cp-avatar__default-img,
                        .bpa-front-form-control.--bpa-country-dropdown.vue-tel-input:focus-within,
                        .el-date-picker__header--bordered {
                            border-color: ' . $border_color . ' !important;
                        }
                        .bpa-front-form-control input::placeholder,
                        .bpa-front-form-control .el-textarea__inner::placeholder,
                        .el-date-picker__time-header .el-input .el-input__inner::placeholder {
                            color:'.$placeholder_color.' !important;
                        }';
                        $bookingpress_customize_css_content .= '.bpa-front-btn--primary,
                        .bpa-front-ma--pagination-wrapper .el-pager li.active,
                        .bpa-ma-vac--action-btn-group .bpa-front-btn:hover,
                        .bpa-front-ma-table-actions-wrap .bpa-front-btn--icon-without-box:hover,
                        .bpa-front-btn--primary:hover,
                        .el-date-table td.current:not(.disabled) span  {
                            background: ' . $primary_color . ' !important;
                        }
                        .bpa-front-btn--primary,.bpa-ma-vac--action-btn-group .bpa-front-btn:hover,
                        .bpa-front-ma--pagination-wrapper .btn-prev:hover,
                        .bpa-front-ma--pagination-wrapper .btn-next:hover,
                        .bpa-front-btn--primary:hover,
                        .bpa-front-ma--pagination-wrapper .el-pager li.active,
                        .bpa-front-ma--pagination-wrapper .el-pager li:hover {
                            border-color:' . $primary_color . ' !important;
                        }
                        .el-button--bpa-front-btn.bpa-front-btn--danger.el-button--mini:hover {
                            border-color:var(--bpa-sc-danger) !important;
                        }
                        .el-button--bpa-front-btn:hover {
                            border-color:' . $content_color . ' !important;
                        }';     

                        $bookingpress_customize_css_content .= '.bpa-tn__dropdown-menu .bpa-tn__dropdown-item a.bpa-tm__item.__bpa-is-active,
                        .bpa-tn__dropdown-menu .bpa-tn__dropdown-item a.bpa-tm__item.__bpa-is-active span,
                        .bpa-front-ma--pagination-wrapper .el-pager li:hover,
                        .bpa-vac-pd__item.__bpa-pd-is-total-item .bpa-vac-pd__val,
                        .el-date-picker__header-label.active,
                        .el-date-picker__header-label:hover,
                        .el-date-table td.available:hover,
                        .el-date-table td.today:not(.current) span,
                        .el-month-table td .cell:hover,
                        .el-month-table td.current:not(.disabled) .cell,
                        .el-year-table td .cell:hover,
                        .el-year-table td.current:not(.disabled) .cell,
                        .el-picker-panel__content .el-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span:hover,
                        .el-picker-panel__content .el-date-table td:not(.current):not(.today) span:hover,
                        .bpa-front-cp-custom-popover .el-year-table td.today .cell,
                        .bpa-front-cp-custom-popover .el-month-table td.today .cell,
                        .bpa-custom-datepicker .el-year-table td.today .cell,
                        .bpa-custom-datepicker .el-month-table td.today .cell {
                            color: ' . $primary_color. ' !important;
                        }
                        .bpa-front-dcw__vector .bpa-front-dcw__vector-primary-color,.bpa-front-loader-cl-primary,
                        .bpa-tn__dropdown-menu .bpa-tn__dropdown-item a.bpa-tm__item.__bpa-is-active svg,
                        .bpa-front-data-empty-view--my-bookings .bpa-front-dev__primary-bg{
                            fill:'. $primary_color. ' !important;
                        }
                        .bpa-front-data-empty-view--my-bookings .bpa-front-dev__primary-bg{
                            stroke:'. $primary_color. ' !important;
                        }
                        .bpa-cp-ma-table.el-table td.el-table__cell .bpa-ma-date-time-details .bpa-ma-dt__time-val svg,
                        .bpa-tn__dropdown-menu .bpa-tn__dropdown-item svg,
                        .bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-front-btn--icon-without-box span svg,
                        .bpa-tn__dropdown-head .bpa-cp-avatar__default-img svg {
                            fill:'. $content_color. ' !important;
                        }
                        .bpa-ma-vac--action-btn-group .bpa-front-btn span svg {
                            fill:'. $sub_title_color. ' !important;    
                        }
                        .bpa-ma-vac--action-btn-group .bpa-front-btn:hover span svg,
                        .bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-front-btn--icon-without-box:hover span svg {
                            fill: var(--bpa-cl-white) !important;
                        }';
                        $bookingpress_customize_css_content .= '
                        .bpa-front-default-card{
                            background: ' . $shortcode_background_color . ' !important;
                        }

                        .bpa-front-btn--primary span,
                        .bpa-front-ma--pagination-wrapper .el-pager li.active,
                        .bpa-ma-vac--action-btn-group .bpa-front-btn:hover,
                        .bpa-front-ma--pagination-wrapper .btn-prev:hover span,
                        .bpa-front-ma--pagination-wrapper .btn-next:hover span,
                        .bpa-front-ma--pagination-wrapper .btn-next:hover,
                        .bpa-front-ma--pagination-wrapper .btn-prev:hover,
                        .bpa-front-ma--pagination-wrapper .el-pagination .btn-prev .el-icon:hover,
                        .bpa-front-ma--pagination-wrapper .el-pagination .btn-next .el-icon:hover,
                        .bpa-front-ma--pagination-wrapper .btn-prev:hover:before,
                        .bpa-front-ma--pagination-wrapper .btn-next:hover:after,
                        .el-date-table td.current:not(.disabled) span,
                        .bpa-front-cp-delete-account-wrapper .bpa-front-dcw__body-btn-group .bpa-front-btn--danger,
                        .bpa-front-cp-delete-account-wrapper .bpa-front-dcw__body-btn-group .bpa-front-btn--danger span{  
                            color:'.$price_button_color.' !important;
                        }';

                        $bookingpress_customize_css_content .= '
                        .bpa-front-module-heading,
                        .bpa-cp-pd__title,
                        .bpa-cp-ma-table.el-table .bpa-cp-ma-cell-val,
                        .bpa-cp-ma-table.el-table td.el-table__cell .cell,
                        .bpa-cp-ma-table.el-table .el-table__header-wrapper tr th.el-table__cell,
                        .bpa-left__service-detail .bpa-sd__appointment-title,
                        .bpa-bd__item .bpa-item--val,
                        .bpa-ma-vac-sec-title,
                        .bpa-front-form-control input,
                        .bpa-left__service-detail .bpa-sd__appointment-id,
                        .bpa-tn__dropdown-menu .bpa-tn__dropdown-item a.bpa-tm__item,
                        .bpa-tn__dropdown-menu .bpa-tn__dropdown-item,
                        .bpa-cp-ma-table.el-table td.el-table__cell,
                        .bpa-cp-ma-table.el-table td.el-table__cell .bpa-ma-date-time-details .bpa-ma-dt__time-val,
                        .bpa-bd__item .bpa-item--label,
                        .bpa-vac-pd__item .bpa-vac-pd__label,
                        .bpa-vac-pd__item .bpa-vac-pd__val,
                        .bpa-ma-vac--action-btn-group .bpa-front-btn__small,
                        .bpa-front-btn--primary,
                        .bpa-front-pill,
                        .bpa-front-ma--pagination-wrapper .el-pager li.number,
                        .bpa-front-dcw__body-title,
                        .bpa-front-dcw__body-sub-title,
                        .bpa-front-btn,
                        .el-popconfirm__main,
                        .bpa-front-btn__small,
                        .el-date-picker__header-label,
                        .el-picker-panel__content .el-date-table th,
                        .el-picker-panel__content .el-date-table td span,
                        .bpa-front-data-empty-view--my-bookings .bpa-front-dev__title,
                        .el-form-item__error,
                        .bpa-front-form-control input::placeholder,
                        .bpa-front-form-control .el-textarea__inner::placeholder,
                        .bpa-front-cp-custom-popover .el-year-table td .cell,
                        .bpa-front-cp-custom-popover .el-month-table td .cell,
                        .bpa-custom-datepicker .el-year-table td .cell,
                        .bpa-custom-datepicker .el-month-table td .cell,
                        .el-year-table td .cell,
                        .el-month-table td .cell,
                        .bpa-front-ma--pagination-wrapper .btn-prev span,
                        .bpa-front-ma--pagination-wrapper .btn-next span{ 
                        font-family: ' . $title_font_family . ' !important;   
                        }';

                        $bookingpress_customize_css_content .= '
                        .bpa-front-module-heading,
                        .bpa-cp-pd__title,
                        .bpa-cp-ma-table.el-table .el-table__header-wrapper tr th.el-table__cell,
                        .bpa-left__service-detail .bpa-sd__appointment-title,
                        .bpa-bd__item .bpa-item--val,
                        .bpa-ma-vac-sec-title,  
                        .bpa-front-form-control input,
                        .bpa-vac-pd__item.__bpa-pd-is-total-item .bpa-vac-pd__label,
                        .bpa-front-dcw__body-title,
                        .el-picker-panel__content .el-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span,
                        .el-date-picker__header-label,
                        .el-date-picker__time-header .el-input .el-input__inner,
                        .bpa-front-cp-custom-popover .el-year-table td .cell,
                        .bpa-front-cp-custom-popover .el-month-table td .cell,
                        .bpa-custom-datepicker .el-year-table td .cell,
                        .bpa-custom-datepicker .el-month-table td .cell,
                        .el-year-table td .cell,
                        .el-month-table td .cell
                        {
                            color: ' . $label_title_color . ' !important;
                        }';                    
                        $bookingpress_customize_css_content .= '
                        .bpa-left__service-detail .bpa-sd__appointment-id,
                        .bpa-tn__dropdown-menu .bpa-tn__dropdown-item span,
                        .bpa-cp-ma-table.el-table .el-table__expand-icon .el-icon-arrow-right::before,
                        .bpa-front-data-empty-view--my-bookings .bpa-front-dev__title
                        {
                            color: ' . $content_color . ' !important;
                        }';
                        $bookingpress_customize_css_content .= ' 
                        .bpa-tn__dropdown-head svg {
                            fill: ' . $content_color . ' !important;
                        }';
                        $bookingpress_customize_css_content .= '
                        .el-picker-panel .el-icon-arrow-left::before,
                        .el-picker-panel .el-icon-arrow-right::before,
                        .el-picker-panel .el-icon-d-arrow-left::before,
                        .el-picker-panel .el-icon-d-arrow-right::before,
                        .bpa-cp-ma-table.el-table .el-table__expand-icon .el-icon-arrow-right::before,
                        .bpa-front-form-control--date-picker .el-input__prefix .el-input__icon::before,
                        .bpa-front-cp--fw__col.__bpa-is-search-icon .bpa-front-form-control .el-input__prefix .el-icon-search:before,
                        .bpa-front-ma--pagination-wrapper .btn-prev::before, 
                        .bpa-front-ma--pagination-wrapper .btn-next::after {
                            background-color: ' . $content_color . ' !important;
                        }';


                        $bookingpress_customize_css_content .= '
                        .bpa-tn__dropdown-menu .bpa-tn__dropdown-item a.bpa-tm__item,
                        .bpa-cp-ma-table.el-table td.el-table__cell .cell,
                        .bpa-cp-ma-table.el-table td.el-table__cell .bpa-ma-date-time-details .bpa-ma-dt__time-val,
                        .bpa-bd__item .bpa-item--label,
                        .bpa-vac-pd__item .bpa-vac-pd__label,
                        .bpa-vac-pd__item .bpa-vac-pd__val:not(.bpa-front-text-primary-color):not(.bpa-front-text--danger-color):not(.bpa-front-text--danger-color):not(.bpa-front-text-blue-color):not(.bpa-front-text--secondary-orange-color),
                        .bpa-ma-vac--action-btn-group .bpa-front-btn__small,
                        .bpa-ma-vac--action-btn-group .bpa-front-btn span svg,
                        .bpa-cp-ma-table.el-table .bpa-cp-ma-cell-val,
                        .bpa-front-ma--pagination-wrapper .el-pager li,
                        .bpa-front-dcw__body-sub-title,
                        .bpa-front-delete-account-txt,
                        .bpa-mob-col__body .bpa-mob--service-title,
                        .bpa-mob--date-time-details .bpa-mob-dtd__date-val, 
                        .bpa-mob--date-time-details .bpa-mob-dtd__time-val,
                        .bpa-front-ma--pagination-wrapper .btn-prev span,
                        .bpa-front-ma--pagination-wrapper .btn-next span,
                        .bpa-front-ma--pagination-wrapper .el-pagination .btn-prev .el-icon,
                        .bpa-front-ma--pagination-wrapper .el-pagination .btn-next .el-icon,
                        .bpa-front-ma--pagination-wrapper .btn-prev::before,
                        .bpa-front-ma--pagination-wrapper .btn-next::after,
                        .el-picker-panel__content .el-date-table th,
                        .el-popconfirm .el-popconfirm__main,
                        .el-popconfirm .el-popconfirm__action .el-button--bpa-front-btn:not(.bpa-front-btn--danger) 
                        {
                            color: ' . $sub_title_color . ' !important;
                        }';       

                        $bookingpress_customize_css_content .= '
                        .btn-prev .el-icon-arrow-left::before,
                        .btn-next .el-icon-arrow-right::before
                        {
                            background-color: '.$sub_title_color.' !important;
                        }';

                        $bookingpress_customize_css_content .= '               
                        @media (min-width: 1200px){    
                            .bpa-front-module-heading
                            {
                                font-size: ' . $title_font_size . ' !important;
                            }    
                            .bpa-tn__dropdown-menu .bpa-tn__dropdown-item a.bpa-tm__item,            
                            .bpa-front-form-control input,
                            .bpa-front-btn--primary span,
                            .bpa-cp-ma-table.el-table .bpa-cp-ma-cell-val,
                            .bpa-cp-ma-table.el-table td.el-table__cell .cell,    
                            .bpa-front-ma--pagination-wrapper .el-pager li,
                            .bpa-left__service-detail .bpa-sd__appointment-id,
                            .bpa-bd__item .bpa-item--val,
                            .bpa-ma-vac-sec-title,
                            .bpa-front-dcw__body-sub-title,
                            .bpa-vac-pd__item .bpa-vac-pd__label,
                            .bpa-vac-pd__item .bpa-vac-pd__val,
                            .bpa-front-ma--pagination-wrapper .btn-prev span,
                            .bpa-front-ma--pagination-wrapper .btn-next span{
                                font-size: ' . $content_font_size . ' !important;               
                            }   
                            .bpa-left__service-detail .bpa-sd__appointment-title,
                            .bpa-front-dcw__body-title{
                                font-size: ' . $sub_title_font_size . ' !important;               
                            }
                            .bpa-cp-pd__title,
                            .bpa-front-btn__small,
                            .bpa-bd__item .bpa-item--label,
                            .bpa-cp-ma-table.el-table td.el-table__cell .bpa-ma-date-time-details .bpa-ma-dt__time-val,
                            .el-picker-panel__content .el-date-table th,
                            .el-picker-panel__content .el-date-table td span {
                                font-size: 13px !important;               
                            }  
                        }

                        @media (max-width: 1024px) {
                            .bpa-front-module-heading,
                            .bpa-left__service-detail .bpa-sd__appointment-title {
                                font-size: ' . $sub_title_font_size . ' !important;
                            }
                            .bpa-tn__dropdown-menu .bpa-tn__dropdown-item a.bpa-tm__item,
                            .el-table th.el-table__cell>.cell,
                            .bpa-cp-ma-table.el-table .bpa-cp-ma-cell-val,
                            .bpa-front-ma--pagination-wrapper .el-pager li,
                            .bpa-left__service-detail .bpa-sd__appointment-id,
                            .bpa-bd__item .bpa-item--label,
                            .bpa-ma-vac-sec-title,
                            .bpa-front-form-control {
                                font-size: ' . $content_font_size . ' !important;
                            }
                            .bpa-cp-pd__title,
                            .bpa-bd__item .bpa-item--label,
                            .bpa-vac-pd__item .bpa-vac-pd__label,
                            .bpa-vac-pd__item .bpa-vac-pd__val,
                            .bpa-front-btn__small,
                            .bpa-cp-ma-table.el-table td.el-table__cell .bpa-ma-date-time-details .bpa-ma-dt__time-val {
                                font-size: 13px !important;    
                            }          
                        }
                        @media (max-width: 576px){
                            .bpa-front-module-heading
                            {
                                font-size: ' . $sub_title_font_size . ' !important;
                            }
                            .bpa-tn__dropdown-menu .bpa-tn__dropdown-item a.bpa-tm__item,
                            .bpa-mob-col__body .bpa-mob--service-title ,
                            .bpa-left__service-detail .bpa-sd__appointment-title,
                            .bpa-bd__item .bpa-item--label,
                            .bpa-bd__item .bpa-item--val,
                            .bpa-ma-vac-sec-title
                            {
                                font-size: ' . $content_font_size . ' !important;
                            }
                            .bpa-mob--date-time-details .bpa-mob-dtd__date-val,
                            .bpa-mob--date-time-details .bpa-mob-dtd__time-val,
                            .bpa-left__service-detail .bpa-sd__appointment-id,
                            .bpa-vac-pd__item .bpa-vac-pd__label,
                            .bpa-vac-pd__item .bpa-vac-pd__val,
                            .bpa-front-btn__small {
                                font-size: 13px !important;    
                            }
                            .bpa-front-ma--pagination-wrapper .btn-prev, 
                            .bpa-front-ma--pagination-wrapper .btn-next{
                                border-color: ' . $border_color . ' !important;
                            }
                            .bpa-front-ma--pagination-wrapper .btn-prev:hover,
                            .bpa-front-ma--pagination-wrapper .btn-next:hover{
                                background: ' . $primary_color . ' !important;
                                border-color: ' . $primary_color . ' !important;
                                
                            }
                            .bpa-front-ma--pagination-wrapper .btn-next:hover:after,
                            .bpa-front-ma--pagination-wrapper .btn-prev:hover:before {
                                background-color: ' .$price_button_color. ' !important;
                            }
                        }';

                        $bookingpress_customize_css_content = apply_filters('bookingpress_generate_my_booking_customize_css',$bookingpress_customize_css_content,$bookingpress_custom_data_arr);       

                        if (! function_exists('WP_Filesystem') ) {
                            include_once ABSPATH . 'wp-admin/includes/file.php';
                        }
                        WP_Filesystem();
                        global $wp_filesystem;
                        $wp_upload_dir = wp_upload_dir();
                        $target_path   = $wp_upload_dir['basedir'] . '/bookingpress/bookingpress_front_mybookings_custom_' . $bookingpress_customize_css_key . '.css';
                        $result        = $wp_filesystem->put_contents($target_path, $bookingpress_customize_css_content, 0777);

                    } else if( 'bookingpress_save_booking_form_settings' == $value ){
                        $shortcode_background_color        = $bookingpress_custom_data_arr['booking_form']['background_color'];
                        $shortcode_footer_background_color = $bookingpress_custom_data_arr['booking_form']['footer_background_color'];
                        $border_color                      = $bookingpress_custom_data_arr['booking_form']['border_color'];
                        $primary_color                     = $bookingpress_custom_data_arr['booking_form']['primary_color'];
                        $primary_alpha_color               = $bookingpress_custom_data_arr['booking_form']['primary_background_color'];
                        $title_label_color                 = $bookingpress_custom_data_arr['booking_form']['label_title_color'];
                        $title_font_size                   = '18px';
                        $title_font_family                 = $bookingpress_custom_data_arr['booking_form']['title_font_family'];
                        $title_font_family                 =  $title_font_family == 'Inherit Fonts' ? 'inherit' : $title_font_family;
                        $content_color                     = $bookingpress_custom_data_arr['booking_form']['content_color'];
                        $price_button_text_content_color   = $bookingpress_custom_data_arr['booking_form']['price_button_text_color'];                        
                        $sub_title_font_size               = '16px';
                        $content_font_size                 = '14px';
                        $sub_title_color                   = $bookingpress_custom_data_arr['booking_form']['sub_title_color'];

                        $hex                               = $primary_color;
                        list($r, $g, $b)                   = is_string($hex) ? sscanf($hex, '#%02x%02x%02x') : sscanf('#12D488', '#%02x%02x%02x');
                        $box_shadow_color                  = "0 4px 8px rgba($r,$g,$b,0.06), 0 8px 16px rgba($r,$g,$b,0.16)";

                        $border_hex                        = $content_color;
                        list($r, $g, $b)                   = is_string($border_hex) ? sscanf($border_hex, '#%02x%02x%02x') : sscanf('#535D71', '#%02x%02x%02x');
                        $placeholder_color                 = "rgba($r,$g,$b,0.75)";

                        $border_rgba                 = $border_color;
                        list($r, $g, $b)            = is_string($border_rgba) ? sscanf($border_rgba, '#%02x%02x%02x') : sscanf('#CFD6E5', '#%02x%02x%02x');
                        $border_rgba          = "rgba($r,$g,$b,0.1)";

                            $bookingpress_customize_css_content .= '.bpa-front-tabs,.bpa-appointment-cancellation_container {
                                --bpa-pt-main-green: ' . $primary_color . ' !important;  
                                --bpa-pt-main-green-darker: ' . $primary_color . ' !important;
                                --bpa-pt-main-green-alpha-12: ' . $primary_alpha_color . ' !important;
                                --bpa-pt-border-color: ' . $border_color . ' !important;
                                --bpa-pt-background-color: ' . $shortcode_background_color . ' !important;
                                --bpa-pt-price-button-text-color: ' . $price_button_text_content_color . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs .bpa-front-tab-menu,
                            .bpa-front-tabs .bpa-front-default-card,
                            .bpa-full-container-loader,
                            .bpa-front-tabs .bpa-front-tabs--foot, 
                            .bpa-front-data-empty-view,
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown,
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-list,
                            .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item .bpa-front-si-card--checkmark-icon,
                            .bpa-front-thankyou-module-container, 
                            .bpa-front-cancel-module-container,
                            .bpa-front--dt__calendar .vc-nav-popover-container{
                                background-color: ' . $shortcode_background_color . ' !important;
                            }
                            .bpa-front-data-empty-view .bpa-front-dev__form-bg,
                            .bpa-front__no-timeslots-body svg .bpa-front-dev__form-bg{
                                fill: ' . $shortcode_background_color . ' !important;
                            }
                            .bpa-front-form-control .el-textarea__inner::placeholder,
                            .bpa-front-form-control input::placeholder,
                            .bpa-front-form-control--file-upload .bpa-fu__placeholder,
                            .el-date-picker__time-header .el-input .el-input__inner::placeholder {
                                color:'.$placeholder_color.' !important;
                            }
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown:hover,
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-item.highlighted,
                            .bpa-front-toast-notification.--bpa-error,
                            .bpa-front-toast-notification.--bpa-success,
                            .bpa-front-thankyou-module-container .bpa-front-cc__error-toast-notification{
                                background-color: ' . $shortcode_footer_background_color . ' !important;
                            } 
                            .bpa-front-loader-cl-primary,
                            .bpa-front-data-empty-view .bpa-front-dev__primary-bg,
                            .bpa-front__no-timeslots-body svg .bpa-front-dev__primary-bg{ 
                                fill:'.$primary_color.' !important;
                            }
                            .bpa-front-data-empty-view .bpa-front-dev__primary-bg,
                            .bpa-front__no-timeslots-body svg .bpa-front-dev__primary-bg{
                                stroke:'.$primary_color.' !important;
                            }
                            .bpa-front-thankyou-module-container .bpa-front-tmc__head .bpa-front-tmc__vector--confirmation .bpa-front-vc__bg,
                            .bpa-front-data-empty-view .bpa-front-dev__panel-bg,
                            .bpa-front__no-timeslots-body svg .bpa-front-dev__panel-bg{
                                fill: ' . $shortcode_footer_background_color . ' !important;
                            }
                            .bpa-front-module--atc-wrapper .bpa-front-btn:hover {
                                border-color: ' . $content_color . ' !important;
                            }
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu,
                            .bpa-front-default-card,
                            .bpa-front-module--service-item .bpa-front-si-card,
                            .bpa-front--dt__time-slots,
                            .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item,
                            .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,
                            .bpa-front-tabs--foot,
                            .bpa-front--dt__calendar .vc-container,
                            .bpa-front--dt__calendar .vc-header,
                            .bpa-front--dt__calendar .vc-day,
                            .bpa-front-form-control input,
                            .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details,
                            .bpa-front-form-control.--bpa-country-dropdown,
                            .bpa-front-form-control .el-textarea__inner,
                            .bpa-front-form-control--v-date-picker .el-input__inner,
                            .el-date-picker__time-header .el-input .el-input__inner,
                            .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item,
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item .bpa-front-tm--item-icon,
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-list,
                            .bpa-fm--bs__deposit-payment-module .bpa-dpm__item.--bpa-is-dpm-total-item,
                            .bpa-fm--bs__deposit-payment-module,
                            .bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-fm--bs__coupon-module-textbox,
                            .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item,
                            .bpa-front-thankyou-module-container,
                            .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item,
                            .bpa-front-module--add-to-calendar,
                            .bpa-front-module--atc-wrapper .bpa-front-btn,
                            .bpa-cp-ls__tab-menu .bpa-tm__item .bpa-tm__item-icon,
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown,
                            .bpa-front-form-control input:focus, 
                            .bpa-front-form-control .el-textarea__inner:focus, 
                            .el-date-picker__time-header .el-input .el-input__inner:focus,
                            .bpa-front-module--date-and-time.__sm .bpa-front--dt__ts-sm-back-btn .bpa-front-btn,
                            .bpa-front-module--service-items-row .bpa-front-module--service-item,
                            .bpa-custom-datepicker .el-date-picker__header--bordered,
                            .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si-card__left .bpa-front-si__default-img,
                            .bpa-fm__booking-summary-v47 .bpa-front-module--bs-customer-detail,
                            .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item.__bpa-is-disabled:hover,
                            .bpa-front--dt__calendar .vc-nav-popover-container,
                            .bpa-front--dt__calendar .vc-nav-items .vc-nav-item:hover,
                            .bpa-front-form-control--checkbox .el-checkbox__inner:hover,
                            .bpa-front-form-control--checkbox .el-checkbox__inner {
                                border-color: ' . $border_color . ' !important;
                            }
                            .bpa-front-module--service-item.__bpa-is-selected .bpa-front-si-card,
                            .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag.__bpa-is-active,
                            .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag:hover,
                            .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item:hover, 
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active .bpa-front-tm--item-icon,
                            .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item.__bpa-is-selected,
                            .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item.__is-selected,
                            .bpa-front-form-control--checkbox .el-checkbox__input.is-checked .el-checkbox__inner,
                            .bpa-front-form-control--checkbox .el-checkbox__inner:hover,
                            .el-radio__input.is-checked .el-radio__inner,
                            .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item.__bpa-is-selected  {
                                border-color: ' . $primary_color . ' !important;
                            }
                            .bpa-front--dt__calendar .vc-day .vc-day-content.is-disabled{
                                background-color:'.$border_rgba. ' !important;
                            }
                            .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item.__bpa-is-disabled{    
                                background-color:'.$border_rgba. ' !important;
                            }
                            .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item.__bpa-is-disabled:hover{
                                background-color:'.$border_rgba. ' !important;    
                            }
                            ';
                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs .bpa-front-module-heading,
                            .bpa-front-tabs .bpa-front--dt__calendar .vc-weeks .vc-weekday,
                            .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item span,
                            .bpa-front-tabs .bpa-front-form-control input,
                            .bpa-front-tabs .bpa-front-form-control .el-textarea__inner,
                            .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item .bpa-front-bs-sm__item-val,
                            .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p,
                            .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p,
                            .bpa-front-tabs .el-form-item__label .bpa-front-form-label,
                            .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .--bpa-is-desc,
                            .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item p,                        
                            .bpa-front-tabs .bpa-front-tab-menu .bpa-front-tab-menu--item,
                            .el-form-item__error,
                            .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,
                            .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong,
                            .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading,
                            .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt-ts__sub-heading,
                            .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span,
                            .bpa-front-module--bs-amount-details .bpa-fm--bs-amount-item .bpa-front-total-payment-amount-label,
                            .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-front-module--bs-ad--price,
                            .bpa-front-tabs .bpa-front--dt__calendar .vc-title, 
                            .bpa-front-tabs--foot .bpa-front-btn,
                            .bpa-front-form-control.--bpa-country-dropdown.vue-tel-input strong,
                            .bpa-front-tmc__booking-id .bpa-front-bi__label,
                            .bpa-front-tmc__booking-id .bpa-front-bi__val,
                            .bpa-front-tmc__head .bpa-front-tmc__title,
                            .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item .bpa-front-sc-item__label,
                            .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item .bpa-front-sc-item__val,
                            .bpa-front-module--add-to-calendar .bpa-fm--atc__heading,
                            .bpa-front-tmc__head p,
                            .bpa-front-data-empty-view .bpa-front-dev__title,
                            .bpa-front-form-control input::placeholder,
                            .bpa-front-form-control .el-textarea__inner::placeholder,
                            .bpa-front-form-control--file-upload .bpa-fu__placeholder,
                            .bpa-custom-datepicker .el-year-table td .cell,
                            .bpa-custom-datepicker .el-month-table td .cell,
                            .bpa-front--dt__calendar .vc-nav-title,
                            .bpa-front--dt__calendar .vc-nav-items .vc-nav-item,
                            .bpa-front-thankyou-module-container .bpa-front-cc__error-toast-notification,
                            .bpa-front__no-timeslots-body .bpa-front-ntb__val,
                            .bpa-front-module--note-desc,
                            .bpa-front-refund-confirmation-content .bpa-front-rcc__body .bpa-front-rcc__empty-msg,
                            .bpa-front--dt__calendar .vc-day .vc-day-content,
                            .bpa-front-form-control--checkbox .el-checkbox__label 
                            {
                                font-family: ' . $title_font_family . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '
                            .bpa-front-ci-pill.__bpa-is-active .bpa-front-ci-item-title,
                            .bpa-front-tabs .bpa-front-form-control input,
                            .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item .bpa-front-bs-sm__item-val,
                            .bpa-front-tabs .bpa-front-form-control .el-textarea__inner,                        
                            .bpa-front-tabs .bpa-front-module-heading,
                            .bpa-front-module--bs-amount-details .bpa-fm--bs-amount-item .bpa-front-total-payment-amount-label,
                            .bpa-front-tmc__booking-id .bpa-front-bi__val,
                            .bpa-front-tmc__head .bpa-front-tmc__title,
                            .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item .bpa-front-sc-item__val,
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-item.highlighted strong,
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-item.highlighted span,
                            .bpa-custom-datepicker .el-year-table td .cell,
                            .bpa-custom-datepicker .el-month-table td .cell{ 
                                color: ' . $title_label_color . ' !important;
                            }';
                                

                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong,
                            .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt-ts__sub-heading,
                            .bpa-front-tabs .bpa-front--dt__calendar .vc-title,             
                            .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading,
                            .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .--bpa-is-desc,
                            .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item span,
                            .bpa-front--dt__ts-sm-back-btn label,
                            .bpa-front-tabs .el-form-item__label span,
                            .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span,
                            .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item p,
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu a,
                            .bpa-front-tabs .bpa-front--dt__calendar .vc-weeks .vc-weekday,
                            .bpa-front-tabs--foot .bpa-front-btn.bpa-front-btn--borderless,
                            .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item .bpa-front-sc-item__label,
                            .bpa-front-thankyou-module-container button.bpa-front-btn:not(:hover):not(:active):not(.has-text-color),
                            .bpa-front-thankyou-module-container .bpa-front-module--atc__item a.bpa-front-btn:not(:hover):not(:active):not(.has-text-color),
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-item strong,
                            .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-item span,
                            .bpa-front--dt__calendar .vc-nav-title,
                            .bpa-front--dt__calendar .vc-nav-title:hover,
                            .bpa-front--dt__calendar .vc-nav-items .vc-nav-item:hover,
                            .bpa-front__no-timeslots-body .bpa-front-ntb__val,
                            .bpa-front-refund-confirmation-content .bpa-front-rcc__body .bpa-front-rcc__empty-msg,
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item::before{
                                color: ' . $sub_title_color . ' !important;      
                            }';

                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs--foot .bpa-front-btn.bpa-front-btn--borderless,
                            .bpa-front--dt__calendar .vc-arrows-container .vc-svg-icon path,
                            .bpa-front--dt__calendar .vc-nav-header .vc-nav-arrow .vc-svg-icon path{
                                fill: ' . $sub_title_color . ' !important;
                            }';
                            
                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p,
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu a span,
                            .bpa-front-tabs .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item:hover,
                            .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p,
                            .bpa-front-tmc__booking-id .bpa-front-bi__label,
                            .bpa-front-tmc__head,
                            .bpa-front-tmc__head p,
                            .bpa-front-module--add-to-calendar .bpa-fm--atc__heading,
                            .bpa-front-module--atc-wrapper .bpa-front-btn:hover,
                            .bpa-front-data-empty-view .bpa-front-dev__title,
                            .bpa-front-tabs .bpa-front--dt__calendar .vc-day .vc-day-content,
                            .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,
                            .bpa-front--dt__calendar .vc-nav-items .vc-nav-item,
                            .bpa-front-module--note-desc,
                            .bpa-front--dt__calendar .vc-day.is-today .vc-day-content.is-disabled,
                            .bpa-front-form-control--checkbox .el-checkbox__label {
                                color: ' . $content_color . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item .bpa-front-tm--item-icon svg,
                            .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si-card__left .bpa-front-si__default-img svg{
                                fill: ' . $content_color . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs--foot .bpa-front-btn--primary span,
                            .bpa-front-tabs--foot .bpa-front-btn--primary strong,
                            .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong.--is-service-price,
                            .bpa-front-tabs .bpa-front--dt__calendar .vc-day.is-today .vc-highlights + .bpa_day-select .vc-day-content,
                            .bpa-front-tabs .bpa-front--dt__calendar .vc-day .vc-highlights + .vc-day-content{
                                color: ' . $price_button_text_content_color . ' !important;
                            }
                            .bpa-front-tabs--foot .bpa-front-btn--primary svg{
                                fill: ' . $price_button_text_content_color . ' !important;
                            }
                            .bpa-front--dt__ts-body--item.__bpa-is-selected {
                                background-color: ' . $primary_alpha_color . ' !important;
                            }';                                         

                            $bookingpress_customize_css_content .= '
                            .bpa-front-module--booking-summary .bpa-front-module--bs-head .bpa-head__vector-item, 
                            .bpa-front-module--confirmation .bpa-head__vector--confirmation .bpa-head__vector-item,
                            .bpa-front-thankyou-module-container .bpa-front-tmc__head .bpa-front-tmc__vector--confirmation .bpa-head__vector-item {
                                fill: ' . $primary_color . ' !important;
                            }';
                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu a.bpa-front-tab-menu--item.__bpa-is-active,
                            .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-front-module--bs-ad--price,
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active::before,
                            .bpa-custom-datepicker .el-year-table td.today .cell,
                            .bpa-custom-datepicker .el-month-table td.today .cell,
                            .bpa-front--dt__calendar .vc-day.is-today .vc-day-content,
                            .bpa-front-form-control--checkbox .el-checkbox__input.is-checked + .el-checkbox__label {
                                color: ' . $primary_color . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active span,
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active .bpa-front-tm--item-icon,
                            .bpa-front-tabs .bpa-front-tabs--foot .bpa-front-btn--primary:focus {                 
                                box-shadow: ' . $box_shadow_color . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '
                            .bpa-front-form-control--radio.el-radio:focus:not(.is-focus):not(:active):not(.is-disabled) .el-radio__inner {                 
                                box-shadow: ' . $box_shadow_color . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '
                            .bpa-frontend-main-container .bpa-front-form-control--checkbox.el-checkbox .is-focus .el-checkbox__inner, .bpp-front-form-control--checkbox.el-checkbox:focus .is-focus .el-checkbox__inner, .bpp-frontend-main-container-package .bpp-front-form-control--checkbox.el-checkbox .is-focus .el-checkbox__inner {                 
                                box-shadow: ' . $box_shadow_color . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '               
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu a.__bpa-is-active span {
                                color: var(--bpa-cl-white) !important;
                            }';
                            $bookingpress_customize_css_content .= '               
                            .bpa-front-tabs--vertical-left .bpa-front-tab-menu a.__bpa-is-active .bpa-front-tm--item-icon svg {
                                fill: var(--bpa-cl-white) !important;
                            }
                            .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item svg.bpa-front-pm-pay-local-icon{
                                fill: ' . $content_color . ' !important;
                            }';

                            $bookingpress_customize_css_content .= '               
                            @media (min-width: 1200px){
                                .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading,                        
                                .bpa-front-tabs .bpa-front-module-heading, 
                                .bpa-front-tmc__head .bpa-front-tmc__title,
                                .bpa-front-cancel-module-container .bpa-front-cmc__title,
                                .bpa-front-tmc__booking-id .bpa-front-bi__label,
                                .bpa-front-module--add-to-calendar .bpa-fm--atc__heading{
                                    font-size: ' . $title_font_size . ' !important;
                                }
                                .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p,
                                .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item .bpa-front-bs-sm__item-val,    
                                .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong,
                                .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt-ts__sub-heading,
                                .bpa-front-module--bs-amount-details .bpa-fm--bs-amount-item .bpa-front-total-payment-amount-label,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-front-module--bs-ad--price,
                                .bpa-front-tabs .bpa-front--dt__calendar .vc-title,
                                .bpa-front-refund-confirmation-content .bpa-front-rcc__body .bpa-front-rcc__empty-msg,
                                .bpa-front-tmc__head p,
                                .bpa-front-cancel-module-container .bpa-front-cmc__desc {
                                    font-size: ' . $sub_title_font_size . ' !important;
                                }
                                .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .--bpa-is-desc,
                                .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item span,
                                .bpa-front-tabs .el-form-item__label span,
                                .bpa-front-tabs .bpa-front-form-control input,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span,
                                .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item p,
                                .bpa-front-tabs--vertical-left .bpa-front-tab-menu a,
                                .bpa-front-tabs .bpa-front-form-control .el-textarea__inner,    
                                .bpa-front--dt__calendar .vc-day .vc-day-content,
                                .bpa-front-tabs .bpa-front--dt__calendar .vc-weeks .vc-weekday,
                                .bpa-front-tabs--foot .bpa-front-btn,
                                .bpa-front__no-timeslots-body .bpa-front-ntb__val,
                                .bpa-front-thankyou-module-container .bpa-front-cc__error-toast-notification,
                                .bpa-front-form-control--checkbox .el-checkbox__label, {
                                    font-size: ' . $content_font_size . ' !important;
                                }
                                .--bpa-top.bpa-front-tabs--vertical-left .bpa-front-tab-menu a,
                                .bpa-front-module--note-desc{
                                    font-size: 15px !important;
                                }
                            }
                            @media (max-width: 1024px){                      
                                .bpa-front-tabs .bpa-front-module-heading,    
                                .bpa-front-tabs .bpa-front--dt__calendar .vc-title,
                                .bpa-front-tmc__head .bpa-front-tmc__title,
                                .bpa-front-cancel-module-container .bpa-front-cmc__title,
                                .bpa-front-tmc__booking-id .bpa-front-bi__label,
                                .bpa-front-module--add-to-calendar .bpa-fm--atc__heading {
                                    font-size: ' . $sub_title_font_size . ' !important;
                                }
                                .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading,
                                .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt-ts__sub-heading,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p,
                                .bpa-front-module--bs-amount-details .bpa-fm--bs-amount-item .bpa-front-total-payment-amount-label,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-front-module--bs-ad--price{
                                    font-size: 15px !important; 
                                }   
                                .bpa-front-tabs--vertical-left .bpa-front-tab-menu a,
                                .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,
                                .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .--bpa-is-desc,
                                .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p,
                                .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong,
                                .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item span,    
                                .bpa-front-tabs .bpa-front--dt__calendar .vc-weeks .vc-weekday,  
                                .bpa-front-tabs .el-form-item__label span,
                                .bpa-front-tabs .bpa-front-form-control input, 
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item .bpa-front-bs-sm__item-val,
                                .bpa-front-tabs--foot .bpa-front-btn   
                                {
                                    font-size: ' . $content_font_size . ' !important;
                                } 
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span {
                                    font-size: 13px !important;
                                }
                                .bpa-front-module--date-and-time.__sm .bpa-front--dt__ts-sm-back-btn .bpa-front-btn span svg{
                                    fill: ' . $sub_title_color . ' !important;
                                }     
                            }
                                
                            @media (max-width: 576px){                               
                                .bpa-front-tabs .bpa-front-module-heading{
                                    font-size: ' . $sub_title_font_size . ' !important;
                                }
                                .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading,    
                                .bpa-front-tabs .bpa-front--dt__calendar .vc-weeks .vc-weekday{
                                    font-size: 15px !important; 
                                }    
                                .bpa-front-tabs--vertical-left .bpa-front-tab-menu a,
                                .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,
                                .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong,
                                .bpa-front-tabs .bpa-front--dt__calendar .vc-title,
                                .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt-ts__sub-heading,
                                .bpa-front-tabs .el-form-item__label span,
                                .bpa-front-tabs .bpa-front-form-control input,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item .bpa-front-bs-sm__item-val,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span,
                                .bpa-front-module--bs-amount-details .bpa-fm--bs-amount-item .bpa-front-total-payment-amount-label,
                                .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-front-module--bs-ad--price
                                {
                                    font-size: ' . $content_font_size . ' !important;
                                } 
                                .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .--bpa-is-desc,
                                .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p,
                                .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item span,
                                .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item p,
                                .bpa-front-tabs--foot .bpa-front-btn {
                                    font-size: 13px !important; 
                                }
                                .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active span,
                                .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active .bpa-front-tm--item-icon {                 
                                    box-shadow: none !important;
                                };      
                            }';
                        $bookingpress_customize_css_content = apply_filters('bookingpress_generate_booking_form_customize_css',$bookingpress_customize_css_content,$bookingpress_custom_data_arr);           

                        if (! function_exists('WP_Filesystem') ) {
                            include_once ABSPATH . 'wp-admin/includes/file.php';
                        }
                        WP_Filesystem();
                        global $wp_filesystem;
                        $wp_upload_dir = wp_upload_dir();
                        $target_path   = $wp_upload_dir['basedir'] . '/bookingpress/bookingpress_front_custom_' . $bookingpress_customize_css_key . '.css';
                        $result        = $wp_filesystem->put_contents($target_path, $bookingpress_customize_css_content, 0777);
                    } else {
                        do_action( 'bookingpress_generate_exteranl_css_outside', $value, $bookingpress_custom_data_arr, $bookingpress_customize_css_key );
                    }
                }
            }     
        }
        
        /**
         * bookingpress_replace_appointment_data
         * This function is used to replace the appointment data
         * @param  mixed $template_content
         * @param  mixed $bookingpress_appointment_data
         * @return void
         */
        function bookingpress_replace_appointment_data($template_content,$bookingpress_appointment_data) {
            
            global $wpdb, $BookingPress, $bookingpress_spam_protection,$bookingpress_global_options,$tbl_bookingpress_categories,$tbl_bookingpress_services,$tbl_bookingpress_payment_logs,$tbl_bookingpress_appointment_bookings;
            $global_data = $bookingpress_global_options->bookingpress_global_options();                
            $default_time_format = $global_data['wp_default_time_format'];
            $default_date_format = $global_data['wp_default_date_format'];

            /* replacing the company data */
        
            $company_name    = esc_html($BookingPress->bookingpress_get_settings('company_name', 'company_setting'));
            $company_address = esc_html($BookingPress->bookingpress_get_settings('company_address', 'company_setting'));
            $company_phone   = esc_html($BookingPress->bookingpress_get_settings('company_phone_number', 'company_setting'));
            $company_website = $BookingPress->bookingpress_get_settings('company_website', 'company_setting');
    
            $company_name = stripslashes_deep( html_entity_decode( $company_name, ENT_QUOTES ) );
            $company_address = stripslashes_deep( html_entity_decode( $company_address, ENT_QUOTES ) );
            
            $template_content = str_replace('%company_address%', $company_address, $template_content);
            $template_content = str_replace('%company_name%', $company_name, $template_content);
            $template_content = str_replace('%company_phone%', $company_phone, $template_content);
            $template_content = str_replace('%company_website%', $company_website, $template_content);
    
            /*****  replacing the company data *****/
        
            if (! empty($bookingpress_appointment_data) ) {

                /* replacing the appointment data */
                $bookingpress_appointment_date       = !empty( $bookingpress_appointment_data['bookingpress_appointment_date'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_date'] ) : '';
				$bookingpress_appointment_start_time = !empty( $bookingpress_appointment_data['bookingpress_appointment_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_time'] ) : '';
				$bookingpress_appointment_end_time   = !empty( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) : '';
                $bookingpress_booking_id   = !empty( $bookingpress_appointment_data['bookingpress_booking_id'] ) ? sanitize_text_field( $bookingpress_appointment_data['bookingpress_booking_id'] ) : '';
                $bookingpress_appointment_date       = date_i18n($default_date_format, strtotime($bookingpress_appointment_date));
                $bookingpress_appointment_start_time = date($default_time_format, strtotime($bookingpress_appointment_start_time));
                $bookingpress_appointment_end_time   = date($default_time_format, strtotime($bookingpress_appointment_end_time));
                $bookingpress_booking_id = "#".$bookingpress_booking_id;        
                $template_content = str_replace('%booking_id%', $bookingpress_booking_id, $template_content);
                $template_content = str_replace('%appointment_date%', $bookingpress_appointment_date, $template_content);
                $template_content = str_replace('%appointment_time%',$bookingpress_appointment_start_time. ' - '.$bookingpress_appointment_end_time,$template_content);
                
                $bookingpress_appointment_booking_id = ! empty($bookingpress_appointment_data['bookingpress_appointment_booking_id']) ? intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']) : '';

                $bookingpress_order_id = !empty($bookingpress_appointment_data['bookingpress_order_id']) ? intval( $bookingpress_appointment_data['bookingpress_order_id']) : '';

                if(!empty($bookingpress_appointment_data['bookingpress_is_cart']) && $bookingpress_appointment_data['bookingpress_is_cart'] == 1 ){
                    $where_clause_condition = $wpdb->prepare( 'bookingpress_order_id = %d ', $bookingpress_order_id );
                } else {
                    $where_clause_condition = $wpdb->prepare( 'bookingpress_appointment_booking_ref = %d ', $bookingpress_appointment_booking_id );
                }

                $log_data = array();
                if (!empty($bookingpress_appointment_booking_id) && $bookingpress_appointment_booking_id != 0) {
                    $log_data = $wpdb->get_row( "SELECT bookingpress_payment_gateway FROM " . $tbl_bookingpress_payment_logs . " WHERE {$where_clause_condition}",ARRAY_A); // phpcs:ignore
                }

                $bookingpress_payment_method = !empty($log_data['bookingpress_payment_gateway']) ? $log_data['bookingpress_payment_gateway'] : '';
                if(!empty($bookingpress_payment_method) && $bookingpress_payment_method == 'on-site' ) {
                    $bookingpress_payment_method = $this->bookingpress_get_customize_settings('locally_text','booking_form');
                } elseif(!empty($bookingpress_payment_method) && $bookingpress_payment_method != 'manual') {
                    $bookingpress_payment_method = $this->bookingpress_get_customize_settings($bookingpress_payment_method.'_text','booking_form');
                }
                $bookingpress_payment_method = !empty($bookingpress_payment_method) ? stripslashes_deep( html_entity_decode( esc_html( $bookingpress_payment_method), ENT_QUOTES) ): '';
                $template_content = str_replace('%payment_method%',$bookingpress_payment_method,$template_content);

                /**** replacing the appointment data *****/    
        
                /* replacing the customer data */

                $bookingpress_customer_id       = !empty( $bookingpress_appointment_data['bookingpress_customer_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_id'] ) : '';
				$bookingpress_customer_email = !empty( $bookingpress_appointment_data['bookingpress_customer_email'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_email'] ) : '';
				$bookingpress_customer_firstname   = !empty( $bookingpress_appointment_data['bookingpress_customer_firstname'] ) ? stripslashes_deep( html_entity_decode( esc_html( $bookingpress_appointment_data['bookingpress_customer_firstname'] ), ENT_QUOTES) ) : '';
                $bookingpress_customer_lastname   = !empty( $bookingpress_appointment_data['bookingpress_customer_lastname'] ) ?stripslashes_deep( html_entity_decode( esc_html( $bookingpress_appointment_data['bookingpress_customer_lastname'] ), ENT_QUOTES) ) : '';
                $bookingpress_customer_fullname   = !empty( $bookingpress_appointment_data['bookingpress_customer_name'] ) ? stripslashes_deep( html_entity_decode( esc_html( $bookingpress_appointment_data['bookingpress_customer_name'] ), ENT_QUOTES) ) : '';
                $bookingpress_customer_phone   = !empty( $bookingpress_appointment_data['bookingpress_customer_phone'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_phone'] ) : '';
                $bookingpress_customer_note   = !empty( $bookingpress_appointment_data['bookingpress_appointment_internal_note'] ) ? stripslashes_deep( html_entity_decode( esc_html( $bookingpress_appointment_data['bookingpress_appointment_internal_note'] ), ENT_QUOTES) ) : '';
                

                if( !empty($bookingpress_customer_phone) && !empty( $bookingpress_appointment_data['bookingpress_customer_phone_dial_code'] ) ){

                    $customer_phone_pattern = '/(^\+'.$bookingpress_appointment_data['bookingpress_customer_phone_dial_code'].')/';
                    if( preg_match($customer_phone_pattern, $bookingpress_customer_phone) ){
                        $bookingpress_customer_phone = preg_replace( $customer_phone_pattern, '', $bookingpress_customer_phone) ;
                    }
                }

                if(!empty($bookingpress_appointment_data['bookingpress_customer_phone_dial_code'])){
                    $bookingpress_customer_phone = "+".$bookingpress_appointment_data['bookingpress_customer_phone_dial_code']." ".$bookingpress_customer_phone;
                }                
                $template_content = str_replace('%customer_email%', $bookingpress_customer_email, $template_content);
                $template_content = str_replace('%customer_first_name%', $bookingpress_customer_firstname, $template_content);
                $template_content = str_replace('%customer_full_name%', $bookingpress_customer_fullname, $template_content);
                $template_content = str_replace('%customer_last_name%', $bookingpress_customer_lastname, $template_content);
                $template_content = str_replace('%customer_phone%', $bookingpress_customer_phone, $template_content);
                $template_content = str_replace('%customer_note%', $bookingpress_customer_note, $template_content);       
        
                $bookingpress_appointment_status = !empty($bookingpress_appointment_data['bookingpress_appointment_status']) ? intval($bookingpress_appointment_data['bookingpress_appointment_status']) : '' ;        
                if(!empty($bookingpress_appointment_status) && $bookingpress_appointment_status != 3 && $bookingpress_appointment_status != 4)  {
                    
                    $bpa_unique_id = !empty($bookingpress_appointment_data['bookingpress_appointment_token']) ? $bookingpress_appointment_data['bookingpress_appointment_token'] : '';

                    if($bookingpress_appointment_booking_id){
                        $bpa_unique_id = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_appointment_token FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_booking_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $bookingpress_appointment_log_data is table name defined globally. False Positive alarm
                    }
                    
                    if(empty($bpa_unique_id)) {
                        $bpa_unique_id = $this->bookingpress_generate_token();                        
                        $wpdb->update($tbl_bookingpress_appointment_bookings,array('bookingpress_appointment_token' => $bpa_unique_id),array('bookingpress_appointment_booking_id' => $bookingpress_appointment_booking_id));
                    }
                    $appointment_cancellation_confirmation = $BookingPress->bookingpress_get_customize_settings('appointment_cancellation_confirmation','booking_my_booking');                    
                    if(!empty($appointment_cancellation_confirmation)){
                        $bookingpress_appointment_cancellation_confirmation_url = get_permalink($appointment_cancellation_confirmation);                       
                    }
                    $bookingpress_cancel_appointment_link = !empty($bookingpress_appointment_cancellation_confirmation_url) ? $bookingpress_appointment_cancellation_confirmation_url :BOOKINGPRESS_HOME_URL;
                    $bookingpress_cancel_appointment_link    = add_query_arg('appointment_id', base64_encode($bookingpress_appointment_data['bookingpress_appointment_booking_id']), $bookingpress_cancel_appointment_link);
                    $bookingpress_cancel_appointment_link = add_query_arg( 'cancel_token',$bpa_unique_id, $bookingpress_cancel_appointment_link );
                    $template_content = str_replace('%customer_cancel_appointment_link%', $bookingpress_cancel_appointment_link, $template_content);

                }     
        
                /***** replacing the customer data *****/
        
                /*  replacing the service data */
        
                $bookingpress_service_id = !empty( $bookingpress_appointment_data['bookingpress_service_id'] ) ? intval($bookingpress_appointment_data['bookingpress_service_id'] ) : '' ;	
                $bookingpress_service_name = !empty($bookingpress_appointment_data['bookingpress_service_name']) ? esc_html($bookingpress_appointment_data['bookingpress_service_name']) : '';
                $bookingpress_currency = !empty($bookingpress_appointment_data['bookingpress_service_currency']) ? esc_html($bookingpress_appointment_data['bookingpress_service_currency']) : '';
                $bookingpress_currency_symbol = $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency);
                $bookingpress_service_price = ! empty($bookingpress_appointment_data['bookingpress_service_price']) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_appointment_data['bookingpress_service_price'], $bookingpress_currency_symbol) : 0;
                $bookingpress_service_duration = $bookingpress_appointment_data['bookingpress_service_duration_val'];
                if( 'm' == $bookingpress_appointment_data['bookingpress_service_duration_unit'] ){
                    $bookingpress_service_duration .= ' ' . esc_html__( 'Mins', 'bookingpress-appointment-booking' );
                } elseif('d' == $bookingpress_appointment_data['bookingpress_service_duration_unit']) {
                    $bookingpress_service_duration .= ' ' . esc_html__( 'Days', 'bookingpress-appointment-booking' ); 
                } else {
                    $bookingpress_service_duration .= ' ' . esc_html__( 'Hours', 'bookingpress-appointment-booking' ); 
                }
                $bookingpress_service_name = !empty($bookingpress_service_name) ? stripslashes_deep( html_entity_decode( esc_html( $bookingpress_service_name ), ENT_QUOTES) ): '';

                $template_content = str_replace('%service_name%', $bookingpress_service_name, $template_content);
                $template_content = str_replace('%service_amount%', $bookingpress_service_price, $template_content);
                $template_content = str_replace('%service_duration%', $bookingpress_service_duration, $template_content);

                if( !empty( $bookingpress_service_id )){

                    $bookingpress_service_details     = $BookingPress->get_service_by_id( $bookingpress_service_id );
                    $bookingpress_service_description = !empty( $bookingpress_service_details['bookingpress_service_description'] ) ? $bookingpress_service_details['bookingpress_service_description'] : '';
                    
                    $template_content = str_replace('%service_description%', $bookingpress_service_description, $template_content);
                }

        
                /***** replacing the service data *****/        
        
                /***** replacing the category data *****/
                $bookingpress_category_name = '';
                if(!empty($bookingpress_service_id)) {
                    $bookingpress_service_data= $wpdb->get_row( $wpdb->prepare ("SELECT bookingpress_category_id FROM " . $tbl_bookingpress_services." WHERE bookingpress_service_id = %d ",$bookingpress_service_id), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                    $bookingpress_category_id = !empty($bookingpress_service_data['bookingpress_category_id']) ? intval($bookingpress_service_data['bookingpress_category_id']) : 0;                   
                    if($bookingpress_category_id == 0 ) {
                        $bookingpress_category_name = esc_html__('Uncategorized', 'bookingpress-appointment-booking');
                    } else {                        
                        $categories= $wpdb->get_row($wpdb->prepare( "SELECT bookingpress_category_name FROM " . $tbl_bookingpress_categories." WHERE bookingpress_category_id = %d",$bookingpress_category_id), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
                        $bookingpress_category_name = !empty($categories['bookingpress_category_name']) ? stripslashes_deep( html_entity_decode( esc_html( $categories['bookingpress_category_name'] ), ENT_QUOTES) ): '';
                    }
                }
                $template_content = str_replace( '%category_name%', $bookingpress_category_name, $template_content );                    
        
                /*****  replacing the category data *****/
                
                $template_content = apply_filters('bookingpress_modify_email_content_filter',$template_content,$bookingpress_appointment_data);                

            }
            return $template_content;
        }
        
        /**
         * bookingpress_get_form_field_list
         * This function is used to get form field list.
         * @return void
         */
        function bookingpress_get_form_field_list(){
            global $tbl_bookingpress_form_fields,$wpdb;
            $bookingpress_field_list_data = $wpdb->get_results( $wpdb->prepare( 'SELECT bookingpress_field_label,bookingpress_field_meta_key,bookingpress_field_type FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_is_customer_field = %d AND bookingpress_field_type != %s AND bookingpress_field_type != %s AND bookingpress_field_type != %s order by bookingpress_form_field_id ASC',0,'2_col','3_col','4_col'), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason $tbl_bookingpress_form_fields is a table name. false alarm.  
            
            return $bookingpress_field_list_data;
        }    
        
        /**
         * Function to generate token
         *
         * @param  mixed $special_chars
         * @param  mixed $extra_special_chars
         * @return void
         */
        function bookingpress_generate_token( $special_chars = true, $extra_special_chars = true ){
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            if ( $special_chars ) {
                $chars .= '!@#$%^&*()';
            }
            if ( $extra_special_chars ) {
                $chars .= '-_ []{}<>~`+=,.;:/?|';
            }
            $ms = round( microtime(true) );
            usleep(100);
            $length = 8;           
            $password = '';
            for ( $i = 0; $i < $length; $i++ ) {
                $password .= substr( $chars, wp_rand( 0, strlen( $chars ) - 1 ), 1 );
            }
            $password .= $ms;

            return base64_encode( $password );
        }

        public function bpagetapiurl(){
            $api_url = 'https://arpluginshop.com';
            return $api_url;
        }

        function bookingpress_get_remote_post_params( $plugin_info = '' ){
            global $wpdb;

			$action = '';
			$action = $plugin_info;

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin_list = get_plugins();
			$site_url    = BOOKINGPRESS_HOME_URL;
			$plugins     = array();

			$active_plugins = get_option( 'active_plugins' );

			foreach ( $plugin_list as $key => $plugin ) {
				$is_active = in_array( $key, $active_plugins );

				// filter for only BookingPress ones, may get some others if using our naming convention
				if ( strpos( strtolower( $plugin['Title'] ), 'bookingpress appointment booking' ) !== false ) {
					$name      = substr( $key, 0, strpos( $key, '/' ) );
					$plugins[] = array(
						'name'      => $name,
						'version'   => $plugin['Version'],
						'is_active' => $is_active,
					);
				}
			}
			$plugins = json_encode( $plugins );

			// get theme info
			$theme            = wp_get_theme();
			$theme_name       = $theme->get( 'Name' );
			$theme_uri        = $theme->get( 'ThemeURI' );
			$theme_version    = $theme->get( 'Version' );
			$theme_author     = $theme->get( 'Author' );
			$theme_author_uri = $theme->get( 'AuthorURI' );

			$im        = is_multisite();
			$sortorder = get_option( 'armSortOrder' );

			$post = array(
				'wp'        => get_bloginfo( 'version' ),
				'php'       => phpversion(),
				'mysql'     => $wpdb->db_version(),
				'plugins'   => $plugins,
				'tn'        => $theme_name,
				'tu'        => $theme_uri,
				'tv'        => $theme_version,
				'ta'        => $theme_author,
				'tau'       => $theme_author_uri,
				'im'        => $im,
				'sortorder' => $sortorder,
			);

			return $post;
        }

        /**
         * Determines if a color is dark or light and returns a contrasting color
         * 
         * @param string $backgroundColor Color in hex format (#RRGGBB) or rgb/rgba format
         * @return array Array containing 'is_dark' boolean and 'outline_color' string
         */
        function bookingpress_get_outline_color($backgroundColor) {
            // Initialize variables
            $r = 0;
            $g = 0;
            $b = 0;
            
            // Clean input
            $backgroundColor = trim($backgroundColor);
            
            // Parse the color based on format
            if (preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/i', $backgroundColor)) {
                // Hex color format
                $hex = ltrim($backgroundColor, '#');
                
                if (strlen($hex) === 3) {
                    // Convert shorthand hex (#RGB) to full format (#RRGGBB)
                    $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
                    $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
                    $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
                } else {
                    // Standard hex format (#RRGGBB)
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                }
            } elseif (preg_match('/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)$/i', $backgroundColor, $matches)) {
                // RGB or RGBA format
                $r = intval($matches[1]);
                $g = intval($matches[2]);
                $b = intval($matches[3]);
            } else {
                // Unsupported format
                return [
                    'is_dark' => false,
                    'outline_color' => '#000000', // Default to black outline
                    'error' => 'Unsupported color format'
                ];
            }
            
            // Calculate relative luminance using the same formula as in JavaScript
            $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
            
            // Determine if dark or light with the same threshold
            $isDark = $luminance < 0.5;
            
            // Return appropriate outline color
            return [
                'is_dark' => $isDark,
                'outline_color' => $isDark ? '#FFFFFF' : '#000000', // White for dark bg, black for light bg
                'luminance' => $luminance,
                'opposite_color' => $isDark ? '#000000' : '#FFFFFF'
            ];
        }

    }

}
global $BookingPress,$bookingpress_debug_payment_log_id;
$BookingPress = new BOOKINGPRESS();

$bookingpress_debug_payment_log_id = 0;

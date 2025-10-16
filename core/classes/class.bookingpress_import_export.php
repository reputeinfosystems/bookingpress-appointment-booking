<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_import_export') ) {
    class bookingpress_import_export Extends BookingPress_Core
    {
        function __construct(){


            add_filter('bookingpress_add_setting_dynamic_data_fields',array($this,'bookingpress_add_setting_dynamic_data_fields_func'));             
            add_action('bookingpress_add_setting_dynamic_vue_methods',array($this,'bookingpress_add_setting_dynamic_vue_methods_func'));
            add_action('bookingpress_dynamic_get_settings_data',array($this,'bookingpress_dynamic_get_settings_data_fun'));

            add_action('bookingpress_settings_add_dynamic_on_load_method',array($this,'bookingpress_settings_add_dynamic_on_load_method_func'),10);

            /* BookingPress Lite Setting Page */
            add_filter('bookingpress_lite_general_settings_add_tab_filter', array($this, 'bookingpress_general_settings_add_tab_filter_func'));

            /* Add debug log  */
            /* Function to add Debug Log in Settings - view - download and delete */                            
            add_action( 'bookingpress_lite_add_debug_log_outside', array($this, 'bookingpress_add_debug_log_outside_api_func'));            
            add_filter( 'bookingpress_modify_debug_log_data', array($this, 'bookingpress_modify_debug_log_data_outside_api_func'), 10, 2);
            add_action( 'bookingpress_delete_debug_log_from_outside', array($this, 'bookingpress_clear_debug_payment_log_api_func'), 10, 1);
            add_filter( 'bookingpress_modify_download_debug_log_data', array( $this, 'bookingpress_modify_download_debug_log_data_func' ), 10, 3 );

            add_action('bookingpress_modify_readmore_link', array($this, 'bookingpress_modify_readmore_link_func'), 11);
            add_filter('bookingpress_modify_get_settings_response_data', array( $this, 'bookingpress_modify_get_settings_response_data_func'), 10, 2 );
            add_action( 'bpa_add_extra_tab_outside_func', array( $this,'bpa_add_extra_tab_outside_func_arr'));

            /* Export Data Function Start Here */
            add_action("wp_ajax_bookingpress_export_data_process", array($this,'bookingpress_export_data_process_func'));                
            
            /* Stop Export Data */            
            add_action("wp_ajax_bookingpress_export_data_stop", array($this,'bookingpress_export_data_stop_func'));

            /* Export Data Continue Process Ajax Call */
            add_action("wp_ajax_bookingpress_export_data_continue_process", array($this,'bookingpress_export_data_continue_process_func'));            
            /* Export Data Function Over Here */

            /* Import Process Function Start Here */

            add_action("wp_ajax_bookingpress_import_data_process", array($this,'bookingpress_import_data_process_func'));
            add_action("wp_ajax_bookingpress_import_data_continue_process", array($this,'bookingpress_import_data_continue_process_func'));

            add_action( 'init', array( $this, 'bookingpress_download_export_file' ) );
            /* Import Process Function Over Here */

        }
        
        
        /**
         * Function for download export file
         *
         * @return void
        */
        function bookingpress_download_export_file(){

            if ( isset($_GET['bpa_page']) && isset($_GET['action']) && isset($_GET['export_state']) && (( !empty( $_GET['bpa_page'] ) && 'bookingpress_download' == $_GET['bpa_page'] )) && ! empty( $_GET['action'] ) && 'download_bookingpress_export' == $_GET['action'] ) {

				$nonce = ! empty( $_GET['export_state'] ) ? sanitize_text_field( $_GET['export_state'] ) : '';
				if(!wp_verify_nonce( $nonce, 'bpa_download_export_file' ) ) {
					return false;
				}

				if ( empty( $_GET['export_token'] ) ) {
					return false;
				}

                $bpa_check_authorization = $this->bpa_check_authentication( 'import_export_settings', false, 'bpa_wp_nonce' );            
                if( preg_match( '/error/', $bpa_check_authorization ) ){
                    return false;
                }                
                
                $export_token = base64_decode( $_GET['export_token'] ); // phpcs:ignore
                
                $export_id = $this->bookingpress_get_transient( $export_token );

                if( empty( $export_id ) ){
                    return false;
                }

                $export_id = base64_decode( $export_id );

                $file_name = $export_id.'.txt';
                
                $file_path = wp_upload_dir()['basedir'] . '/bookingpress_export_records/' . $file_name;
                if(file_exists($file_path)){
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
                    header('Content-Length: ' . filesize($file_path));        
                    echo file_get_contents($file_path);
                    exit;            

                }

                return false;
            }

        }

        /* Import Data Function Start here */

        /**
         * Function for generate bookingpress dynamic CSS file.
         *
        */
        function generate_bookingpress_dynamic_css_file(){
            global $BookingPress;
            
            $bookingpress_custom_data_arr = array();
            $bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
            $bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
            $bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
            $bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
            $bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
            $bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
            $bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
            $bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');    
            $bookingpress_primary_background_color = $BookingPress->bookingpress_get_customize_settings('primary_background_color', 'booking_form');
            $bookingpress_border_color= $BookingPress->bookingpress_get_customize_settings('border_color', 'booking_form');
            
            $bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
            $bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
            $bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
            $bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
            $bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
            $bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
            $bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
            $bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';    
            $bookingpress_primary_background_color = !empty($bookingpress_primary_background_color) ? $bookingpress_primary_background_color : '#e2faf1';
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
                'border_color'         => $bookingpress_border_color,
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
                'border_color'         => $bookingpress_border_color,
            );
            $bookingpress_custom_data_arr['booking_form'] = $booking_form;
            $bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;

            $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);

        }


        /**
         * Function to check table exixts or not
         *
        */
        function bookingpress_check_table_exists_func($bookingpress_table_name = "none"){
            global $wpdb;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$bookingpress_table_name'") == $bookingpress_table_name; // phpcs:ignore 
            if ($table_exists) {
                return true;
            } else {
                return false;
            }
        }               
        
        /**
         * Function for update customization settings
         *
        */
        public function bookingpress_update_customize_settings( $bookingpress_setting_name, $bookingpress_setting_type, $bookingpress_setting_value = '' )
        {
            global $wpdb, $tbl_bookingpress_customize_settings,$BookingPress;
            if (! empty($bookingpress_setting_name) ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally. False Positive alarm
                $bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_setting_id) FROM `{$tbl_bookingpress_customize_settings}` WHERE bookingpress_setting_name = %s AND bookingpress_setting_type = %s", $bookingpress_setting_name, $bookingpress_setting_type));

                $bookingpress_check_record_existance = ($bookingpress_check_record_existance == 0 || $bookingpress_check_record_existance == '')?0:$bookingpress_check_record_existance;

                if ($bookingpress_check_record_existance > 0 ) {
                    // If record already exists then update data.
                    $bookingpress_update_data = array(
                        'bookingpress_setting_value' => ( ! empty($bookingpress_setting_value) && (gettype($bookingpress_setting_value) === 'boolean') ) ? $bookingpress_setting_value : $bookingpress_setting_value,
                        'bookingpress_setting_type'  => $bookingpress_setting_type,
                    );
                    $bpa_update_where_condition = array(
                        'bookingpress_setting_name' => $bookingpress_setting_name,
                        'bookingpress_setting_type' => $bookingpress_setting_type,
                    );

                    $bpa_update_affected_rows = $wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_update_data, $bpa_update_where_condition);
                    if ($bpa_update_affected_rows > 0 ) {
                        return 1;
                    }
                } else {

                    // If record not exists then insert data.
                    $bookingpress_insert_data = array(
                        'bookingpress_setting_name'  => $bookingpress_setting_name,
                        'bookingpress_setting_value' => ( ! empty($bookingpress_setting_value) && (gettype($bookingpress_setting_value) === 'boolean') ) ? $bookingpress_setting_value : $bookingpress_setting_value,
                        'bookingpress_setting_type'  => $bookingpress_setting_type,
                        'bookingpress_created_at'    => current_time('mysql'),
                    );
                    $bookingpress_inserted_id = $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_insert_data);
                    if ($bookingpress_inserted_id > 0 ) {
                        return 1;
                    }

                }
            }
            return 0;
        }
        
        /**
         * Function for insert multiple rows
         *
        */
        function insert_multiple_rows( $table, $request, $unset_key = array('none'),$empty_null_keys = array('none')) {
            global $wpdb,$BookingPress;
            $column_keys   = '';
            $column_values = '';
            $sql           = '';
            $last_key      = array_key_last( $request );
            $first_key     = array_key_first( $request );
            foreach ( $request as $k => $value ) {
                foreach($value as $key=>$val){
                    $value[$key] = sanitize_text_field($value[$key]);
                    if(in_array($key,$empty_null_keys) && $value[$key] == ""){
                        $value[$key] = NULL;
                    }
                    if(in_array($key,$unset_key)){
                        unset($value[$key]);
                    }
                }
                $wpdb->insert($table, $value);
            }            
            return true;
        }
        
        /**
         * Function for modified import value
         *
        */
        function bookingpress_import_value_modified($import_data_v,$detail_import_detail_type = '',$key = ''){
            if(!empty($import_data_v)){
                $import_data_v = str_replace(['\\', ''], '', $import_data_v);
                $import_data_v = stripslashes_deep($import_data_v);
            }
            return $import_data_v;
        }

        /**
         * Function to get all table columns name
         *
        */
        function bookingpress_get_all_columns_func($bookingpress_table_name = "none"){
            global $wpdb;
            $database_name = $wpdb->dbname;
            $table_all_columns = $wpdb->get_results($wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s and TABLE_NAME = %s",$database_name,$bookingpress_table_name),ARRAY_A);
            if(!empty($table_all_columns)){
                $final_column_name = array();
                foreach($table_all_columns as $val){
                    $final_column_name[] = $val['COLUMN_NAME'];
                }
                return $final_column_name;
            }else{
                return array();
            }
            return $table_all_columns;
        }
        

        /**
         * Function for get related language record 
         *
        */
        function bookingpress_get_record_language_rel($record_rel_type,$export_key,$import_id,$old_id){
            global $wpdb,$tbl_bookingpress_import_record_rel,$BookingPress;            
            $bookingpress_new_id = $wpdb->get_var($wpdb->prepare("SELECT record_new_id FROM `{$tbl_bookingpress_import_record_rel}` Where record_rel_lang_type = %s AND import_id = %d AND record_old_id = %d",$record_rel_type,$import_id,$old_id));  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_import_record_rel is table name.
            return $bookingpress_new_id;
        }        


        /**
         * Function for get related record 
         *
        */
        function bookingpress_get_record_rel($record_rel_type,$export_key,$import_id,$old_id){
            global $wpdb,$tbl_bookingpress_import_record_rel,$BookingPress;            
            $bookingpress_new_id = $wpdb->get_var($wpdb->prepare("SELECT record_new_id FROM `{$tbl_bookingpress_import_record_rel}` Where record_rel_type = %s AND import_id = %d AND record_old_id = %d",$record_rel_type,$import_id,$old_id));  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_import_record_rel is table name.            
            if(($bookingpress_new_id == "" || $bookingpress_new_id == 0)){
                //$bookingpress_new_id = $old_id;
                $bookingpress_new_id = 0;
            }
            return $bookingpress_new_id;
        }

        /**
         * Set Record Images
         *
        */
        function bookingpress_set_attachment_records($import_table,$import_field = '',$import_table_id = 0,$import_id = 0,$import_image_name = '',$import_image_url = '',$export_key=''){
            global $wpdb,$tbl_bookingpress_import_images,$BookingPress;
            if($import_image_url){
                $bpa_insert_rel_data = array(
                    'import_table'      => $import_table,
                    'import_field'      => $import_field,
                    'import_id'         => $import_id,
                    'import_table_id'   => $import_table_id,
                    'import_image_name' => $import_image_name,
                    'import_image_url'  => $import_image_url,
                    'export_key'        => $export_key,                    
                );
                $wpdb->insert($tbl_bookingpress_import_images, $bpa_insert_rel_data);
            }
        }

        /**
         * Set Record Relation
         *
         */
        function bookingpress_set_record_rel($record_rel_type,$export_key,$import_id,$record_old_id,$record_new_id,$record_rel_lang_type=""){
            global $wpdb,$tbl_bookingpress_import_record_rel;
            if($record_new_id){
                $bpa_insert_rel_data = array(
                    'record_rel_type' => $record_rel_type,
                    'export_key' => $export_key,
                    'import_id' => $import_id,
                    'record_old_id' => $record_old_id,
                    'record_new_id' => $record_new_id,                    
                );
                if(!empty($record_rel_lang_type)){
                    $bpa_insert_rel_data['record_rel_lang_type'] = $record_rel_lang_type;
                }
                $wpdb->insert($tbl_bookingpress_import_record_rel, $bpa_insert_rel_data);
            }
        }

        
        function bookingpress_upload_file_func($file_url,$destination_dir=""){
            $result = array('error'=>1,'msg'=>esc_html__('Failed to download the file.','bookingpress-appointment-booking'));
            if(!empty($file_url)){                
                if(!file_exists($destination_dir)) {
                    wp_mkdir_p($destination_dir);
                }
                $file_name = basename($file_url);    
                $destination_path = $destination_dir . $file_name;
                $file_content = file_get_contents($file_url);
                if ($file_content !== false) {
                    $save_result = file_put_contents($destination_path, $file_content);
                    if ($save_result !== false) {
                        wp_mkdir_p($destination_path);                                                                      
                        $result = array('error'=>0,'msg'=>esc_html__('success.','bookingpress-appointment-booking'));
                    } else { 
                        $result = array('error'=>1,'msg'=>esc_html__('Failed to save the file.','bookingpress-appointment-booking'));                      
                    }                    
                }
            }
            return $result;
        }
        
        function bookingpress_get_max_rec_position_func($table_name = "",$column_name=""){
            global $wpdb,$BookingPress;
            $max_position = 0;
            if(!empty($table_name) && !empty($column_name)){
                $max_position = $wpdb->get_var("SELECT MAX($column_name) FROM `{$table_name}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $table_name is table name.
                if(empty($max_position)){
                    $max_position = 0;
                }
            }
            return $max_position;
        }
        
        /**
         * Create WP User If Not Exists
         *
        */
        function bookingpress_create_wp_user($staff_user_detail,$wp_user_meta='',$type = ''){   
            global $BookingPress,$wpdb;         
            if(!empty($staff_user_detail)){                                                   
                $user_email = $staff_user_detail['data']['user_email'];
                $username = $staff_user_detail['data']['user_login'];
                $password = $staff_user_detail['data']['user_pass'];               
                if(!empty($password)){
                    $password = stripslashes_deep($password);
                }
                $user = get_user_by('email', $user_email);
                if(empty($user)) {                    
                    $display_name = $staff_user_detail['data']['display_name'];
                    $user_nicename = $staff_user_detail['data']['user_nicename'];

                    if ( username_exists( $username ) ) {
                        $username = $user_email;
                    }
                    $user_data = array(
                        'user_login'    => $username, 
                        'user_pass'     => $password,
                        'user_email'    => $user_email, 
                        'display_name'  => $display_name,
                        'user_nicename'  => $user_nicename,
                    );
                    $user_id = wp_insert_user($user_data);
                    if($user_id){

                        $wpdb->update(
                            $wpdb->users,
                            array( 'user_pass' => $password),
                            array( 'ID' => $user_id )
                        );
                        $user = get_user_by('id', $user_id);
                        if($user){
                            $user->add_role('bookingpress-customer');  
                        }
                        if(isset($wp_user_meta['first_name'])){
                            $first_name = $wp_user_meta['first_name'];
                            add_user_meta($user_id,'first_name',$first_name);
                        }
                        if(isset($wp_user_meta['last_name'])){
                            $last_name = $wp_user_meta['last_name'];                    
                            add_user_meta($user_id,'last_name',$last_name);    
                        }    
                    }
                    return $user_id;
                }else{                    
                    if($user){
                        $user->add_role('bookingpress-customer');
                        return $user->ID;
                    }
                }
            }
            return 0;
        }
        

        
        /**
         * Generate Import Site File URL
        */
        function bookingpress_new_file_url($file_name){
            $file_url = '';
            if(!empty($file_name)){
                $file_url = site_url().'/wp-content/uploads/bookingpress/'.$file_name;
            }
            return $file_url;
        }

        function bookingpress_import_data_continue_process_func($import_id = ""){
            @ini_set('memory_limit','512M'); // phpcs:ignore       
            global $BookingPress,$tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log,$tbl_bookingpress_import_record_rel,$wpdb;
            
            $response                = array();
            $response['variant']     = 'error';
            $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
            $response['msg']         = esc_html__( 'Sorry, Something Wrong.', 'bookingpress-appointment-booking' );
			
            $bpa_check_authorization = $this->bpa_check_authentication( 'import_export_settings', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingperss_continue_import = $wpdb->get_row($wpdb->prepare("SELECT import_id FROM {$tbl_bookingpress_import_data_log}  WHERE import_complete = %d Order by import_id DESC",0),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
            $import_id = (isset($bookingperss_continue_import['import_id']) && !empty($bookingperss_continue_import['import_id']))? $bookingperss_continue_import['import_id']:0;
            if($import_id){
                $bookingpress_active_plugin_module_list = $this->bookingpress_active_plugin_module_list();

                $bookingpress_pro_active = (isset($bookingpress_active_plugin_module_list['bookingpress_pro']))?$bookingpress_active_plugin_module_list['bookingpress_pro']:0;
                //$bookingpress_import_data
                $upload_dir = wp_upload_dir(); // Get uploads directory info
                $new_folder_path = $upload_dir['basedir'] . '/bookingpress_import_records';
                
                $file_path = $new_folder_path . '/bookingpress_import_data-'.$import_id.'.txt';                               
                $import_data = file_get_contents($file_path);                                
                $bookingpress_import_data = $import_data;
                $bookingpress_import_data = json_decode($bookingpress_import_data,true);
                if(!empty($bookingperss_continue_import)){
                    $import_id = (isset($bookingperss_continue_import['import_id']) && !empty($bookingperss_continue_import['import_id']))? $bookingperss_continue_import['import_id']:0;
                    if($import_id){                        
                        $bookingperss_continue_import_detail = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_import_detail_log}  WHERE detail_import_complete = %d AND import_id = %d Order by detail_import_id ASC",0,$import_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log_detail is a table name. false alarm
                        if(!empty($bookingperss_continue_import_detail)){

                            $detail_import_id = (isset($bookingperss_continue_import_detail['detail_import_id']))?$bookingperss_continue_import_detail['detail_import_id']:'';
                            $detail_import_detail_type = (isset($bookingperss_continue_import_detail['detail_import_detail_type']))?$bookingperss_continue_import_detail['detail_import_detail_type']:'';
                            $detail_import_total_record = (isset($bookingperss_continue_import_detail['detail_import_total_record']))?$bookingperss_continue_import_detail['detail_import_total_record']:'';
                            $detail_import_last_record = (isset($bookingperss_continue_import_detail['detail_import_last_record']))?$bookingperss_continue_import_detail['detail_import_last_record']:0;
                            $detail_import_complete = (isset($bookingperss_continue_import_detail['detail_import_complete']))?$bookingperss_continue_import_detail['detail_import_complete']:'';
                            $export_key = (isset($bookingperss_continue_import_detail['export_key']))?$bookingperss_continue_import_detail['export_key']:'';
                            $total_import_record = (isset($bookingperss_continue_import_detail['total_import_record']))?$bookingperss_continue_import_detail['total_import_record']:'';
                            $not_import_data_reason = '';
                            $limit = 50;
                            $is_complete = 0;
                            $total_imported = 0;

                                                 
                            if($detail_import_detail_type == 'bpa_wpoption_data' && isset($bookingpress_import_data[$detail_import_detail_type])){

                                $all_option_data = (!empty($bookingpress_import_data[$detail_import_detail_type]))?$bookingpress_import_data[$detail_import_detail_type]:'';
                                if(!empty($all_option_data)){
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();                                                
                                            $key = (isset($import_record_data[$i]['key'])) ? $import_record_data[$i]['key'] : '';
                                            $value = (isset($import_record_data[$i]['value'])) ? $import_record_data[$i]['value'] : '';
                                            if($key == 'bookingpress_cart_order_id'){
                                                update_option($key,$value);
                                            } else if( 'bookingpress_invoice_html_format' == $key  ){
                                                $import_data_v = $value;
                                                $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                update_option($key,$import_data_v);
                                            }                                                                                                   
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                                    
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                    
                                }else{
                                    $is_complete = 1;
                                }
                            }else if($detail_import_detail_type == 'appointments' && isset($bookingpress_import_data[$detail_import_detail_type]) && !$bookingpress_pro_active){
                                global $tbl_bookingpress_appointment_bookings,$tbl_bookingpress_entries,$tbl_bookingpress_payment_logs,$tbl_bookingpress_entries_meta,$tbl_bookingpress_appointment_meta;
                                $limit = 40;
                                if(!empty($tbl_bookingpress_appointment_bookings) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_appointment_bookings) && !empty($tbl_bookingpress_entries) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_entries) && !empty($tbl_bookingpress_payment_logs) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_payment_logs)){
                                    $bookingpress_all_upload_fields = array();
                                    global $tbl_bookingpress_form_fields;
                                    if(!empty($tbl_bookingpress_appointment_meta) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_appointment_meta)){
                                        $all_file_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s ORDER BY bookingpress_field_position ASC", 'file'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm                                        
                                        if(!empty($all_file_fields)){
                                            foreach($all_file_fields as $al_field){
                                                $bookingpress_all_upload_fields[] = $al_field['bookingpress_field_meta_key'];
                                            }
                                        }
                                    }
                                    $get_all_payment_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_payment_logs);
                                    $get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_appointment_bookings);
                                    $get_all_entry_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_entries);
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();
                                            foreach($import_record_data[$i] as $key=>$value){
                                                if(in_array($key,$get_all_table_columns)){
                                                    $import_data_v = $import_record_data[$i][$key];
                                                    $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                    if($import_data_v == 'null' || is_null($import_data_v)){
                                                        if( $key == 'bookingpress_selected_appointment_date' || $key == 'bookingpress_selected_appointment_end_date' || $key == 'bookingpress_selected_appointment_time' || $key == 'bookingpress_selected_appointment_end_time' || $key == 'bookingpress_appointment_end_date'){
                                                            $single_import_record[$key] = 0;
                                                        } else {
                                                            $single_import_record[$key] = NULL;
                                                        }
                                                    }else{
                                                        $single_import_record[$key] = $import_data_v;
                                                    }     
                                                }
                                            }
                                            if(!empty($single_import_record)){

                                                //if(isset($single_import_record['bookingpress_customer_id'])){
                                                    $bookingpress_customer_id = $this->bookingpress_get_record_rel('customers',$export_key,$import_id,$single_import_record['bookingpress_customer_id']);
                                                    $single_import_record['bookingpress_customer_id'] = $bookingpress_customer_id;
                                                //}
                                                if(isset($single_import_record['bookingpress_staff_member_id']) && $single_import_record['bookingpress_staff_member_id'] != 0){
                                                    $bookingpress_staff_member_id = $this->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$single_import_record['bookingpress_staff_member_id']);
                                                    $single_import_record['bookingpress_staff_member_id'] = $bookingpress_staff_member_id;
                                                }
                                                //if(isset($single_import_record['bookingpress_service_id'])){
                                                    $bookingpress_service_id = $this->bookingpress_get_record_rel('services',$export_key,$import_id,$single_import_record['bookingpress_service_id']);
                                                    $single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
                                                //}


                                                $wpdb->insert($tbl_bookingpress_appointment_bookings, $single_import_record);
                                                $last_import_id = $wpdb->insert_id;
                                                
                                                if(!empty($tbl_bookingpress_appointment_meta) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_appointment_meta)){                                                    
                                                    $appointmentbooking_metadata = (isset($import_record_data[$i]['meta_data']))?$import_record_data[$i]['meta_data']:'';
                                                    if(!empty($appointmentbooking_metadata)){                                                        
                                                        $get_all_appointment_meta_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_appointment_meta);
                                                        foreach($appointmentbooking_metadata as $booking_meta){
                                                            unset($booking_meta['bookingpress_appointment_meta_id']);
                                                            unset($booking_meta['bookingpress_appointment_meta_created_date']);
                                                            $bookingpress_package_meta_col = array();
                                                            foreach($booking_meta as $mmkey=>$metaval ){
                                                                if(in_array($mmkey,$get_all_appointment_meta_table_columns)){
                                                                    $import_data_v = $metaval;
                                                                    $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$mmkey);
                                                                    if($import_data_v == 'null' || is_null($import_data_v)){
                                                                        $bookingpress_package_meta_col[$mmkey] = NULL;
                                                                    }else{
                                                                        $bookingpress_package_meta_col[$mmkey] = $import_data_v;
                                                                    }     
                                                                }
                                                            }                                                            
                                                            if(!empty($bookingpress_package_meta_col)){
                                                                $bookingpress_appointment_meta_key = (isset($bookingpress_package_meta_col['bookingpress_appointment_meta_key']))?$bookingpress_package_meta_col['bookingpress_appointment_meta_key']:'';
                                                                $bookingpress_appointment_meta_value = (isset($bookingpress_package_meta_col['bookingpress_appointment_meta_value']))?$bookingpress_package_meta_col['bookingpress_appointment_meta_value']:'';
                                                                $bookingpress_appointment_meta_id = (isset($bookingpress_package_meta_col['bookingpress_appointment_meta_id']))?$bookingpress_package_meta_col['bookingpress_appointment_meta_id']:'';
                                                                
                                                                if($bookingpress_appointment_meta_key == 'bookingpress_happy_hour_data' && !empty($bookingpress_appointment_meta_value)){
                                                                    $bookingpress_appointment_meta_value_arr = json_decode($bookingpress_appointment_meta_value,true);
                                                                    if(!empty($bookingpress_appointment_meta_value_arr) && is_array($bookingpress_appointment_meta_value_arr) && isset($bookingpress_appointment_meta_value_arr['bookingpress_service_id'])){
                                                                        $happy_bookingpress_service_id = $bookingpress_appointment_meta_value_arr['bookingpress_service_id'];                                                                        
                                                                        $bookingpress_appointment_meta_value_arr['bookingpress_service_id'] = $this->bookingpress_get_record_rel('services',$export_key,$import_id,$happy_bookingpress_service_id);
                                                                        $bookingpress_package_meta_col['bookingpress_appointment_meta_value'] = wp_json_encode($bookingpress_appointment_meta_value_arr,true);
                                                                    }
                                                                }                                                                
                                                                $bookingpress_all_file_upload_list = array();
                                                                if(!empty($bookingpress_all_upload_fields) && ($bookingpress_appointment_meta_key == 'appointment_form_fields_data' || $bookingpress_appointment_meta_key == 'appointment_details')){
                                                                    if($bookingpress_appointment_meta_key == 'appointment_details' || $bookingpress_appointment_meta_key == 'appointment_form_fields_data'){
                                                                        if(!empty($bookingpress_appointment_meta_value)){
                                                                            $bookingpress_appointment_meta_value_arr = json_decode($bookingpress_appointment_meta_value,true);
                                                                            if(!empty($bookingpress_appointment_meta_value_arr) && is_array($bookingpress_appointment_meta_value_arr) && isset($bookingpress_appointment_meta_value_arr['form_fields']) && !empty($bookingpress_appointment_meta_value_arr['form_fields'])){
                                                                                $form_fields = $bookingpress_appointment_meta_value_arr['form_fields'];
                                                                                if(is_array($form_fields)){
                                                                                    foreach($form_fields as $field_key=>$field_val){
                                                                                        if(in_array($field_key,$bookingpress_all_upload_fields) && !empty($field_val)){
                                                                                            $file_name = basename($field_val);
                                                                                            $bookingpress_all_file_upload_list[] = array(
                                                                                                'import_field' => $field_key,
                                                                                                'import_image_name' => $file_name,
                                                                                                'import_image_url' => $field_val,
                                                                                                'import_table_id' => $bookingpress_appointment_meta_id,
                                                                                            );  
                                                                                            $bookingpress_appointment_meta_value_arr['form_fields'][$field_key] = $this->bookingpress_new_file_url($file_name);
                                                                                        }
                                                                                    }
                                                                                }
                                                                                $bookingpress_package_meta_col['bookingpress_appointment_meta_value'] = wp_json_encode($bookingpress_appointment_meta_value_arr,true);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                $wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_package_meta_col);
                                                                $metadata_last_import_id = $wpdb->insert_id;
                                                                if(!empty($bookingpress_all_file_upload_list)){
                                                                    foreach($bookingpress_all_file_upload_list as $atatchval){
                                                                        $this->bookingpress_set_attachment_records($tbl_bookingpress_appointment_meta,$atatchval['import_field'],$metadata_last_import_id,$import_id,$atatchval['import_image_name'],$atatchval['import_image_url'],$export_key);
                                                                    }
                                                                }

                                                            }                                                            
                                                        }
                                                    }
                                                }
                                                $appointment_payment_data = (isset($import_record_data[$i]['payment_data']))?$import_record_data[$i]['payment_data']:'';
                                                if(!empty($appointment_payment_data) && is_array($appointment_payment_data)){
                                                    
                                                    $bookingpress_payment_log_id = (isset($appointment_payment_data['bookingpress_payment_log_id']))?$appointment_payment_data['bookingpress_payment_log_id']:0;

                                                    $bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_payment_log_id FROM `{$tbl_bookingpress_payment_logs}` WHERE 	bookingpress_payment_log_id = %d", $bookingpress_payment_log_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm 
                                                    
                                                    $bookingpress_check_record_existance = ($bookingpress_check_record_existance == 0 || $bookingpress_check_record_existance = '')?0:$bookingpress_check_record_existance;

                                                    if($bookingpress_check_record_existance == 0){

                                                        $appoitment_payment_records = array();
                                                        foreach($appointment_payment_data as $key=>$value){                                                        
                                                            if(in_array($key,$get_all_payment_table_columns)){
                                                                $import_data_v = $value;
                                                                $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                                if($import_data_v == 'null' || is_null($import_data_v)){
                                                                    if( $key == 'bookingpress_appointment_end_date' ){
                                                                        $appoitment_payment_records[$key] = 0;
                                                                    } else{
                                                                        $appoitment_payment_records[$key] = NULL;
                                                                    }
                                                                }else{
                                                                    $appoitment_payment_records[$key] = $import_data_v;
                                                                }     
                                                            }
                                                        }                                  
                                                        if(isset($appoitment_payment_records['bookingpress_customer_id'])){
                                                            $appoitment_payment_records['bookingpress_customer_id'] = $single_import_record['bookingpress_customer_id']; 
                                                        }
                                                        if(isset($appoitment_payment_records['bookingpress_service_id'])){
                                                            $appoitment_payment_records['bookingpress_service_id'] = $single_import_record['bookingpress_service_id']; 
                                                        }                                                       
                                                        $wpdb->insert($tbl_bookingpress_payment_logs, $appoitment_payment_records);
                                                    }
                                                }

                                                $appointment_entry_data = (isset($import_record_data[$i]['entry_data']))?$import_record_data[$i]['entry_data']:'';
                                                if(!empty($appointment_entry_data) && is_array($appointment_entry_data)){

                                                    $appointment_entry_record = array();
                                                    foreach($appointment_entry_data as $key=>$value){                                                       
                                                        if(in_array($key,$get_all_entry_table_columns)){
                                                            $import_data_v = $value;
                                                            $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                            if($import_data_v == 'null' || is_null($import_data_v)){

                                                                if( $key == 'bookingpress_selected_appointment_date' || $key == 'bookingpress_selected_appointment_end_date' || $key == 'bookingpress_selected_appointment_time' || $key == 'bookingpress_selected_appointment_end_time' || $key == 'bookingpress_appointment_end_date'){
                                                                    $appointment_entry_record[$key] = 0;
                                                                } else {
                                                                    $appointment_entry_record[$key] = NULL;
                                                                }
                                                                
                                                            }else{
                                                                $appointment_entry_record[$key] = $import_data_v;
                                                            }     
                                                        }
                                                    }          

                                                    if(isset($appointment_entry_record['bookingpress_customer_id'])){
                                                        $appointment_entry_record['bookingpress_customer_id'] = $single_import_record['bookingpress_customer_id']; 
                                                    }                                                    
                                                    if(isset($appointment_entry_record['bookingpress_service_id'])){
                                                        $appointment_entry_record['bookingpress_service_id'] = $single_import_record['bookingpress_service_id']; 
                                                    }                                                                        
                                                    $wpdb->insert($tbl_bookingpress_entries, $appointment_entry_record);

                                                    if(isset($appointment_entry_data['meta_data']) && !empty($appointment_entry_data['meta_data'])){
                                                        if(!empty($tbl_bookingpress_entries_meta) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_entries_meta)){
                                                            $get_all_entry_meta_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_entries_meta);
                                                            foreach($appointment_entry_data['meta_data'] as $mkey=>$mvalue){
                                                                $import_entrie_meta_data = array();
                                                                if(!empty($mvalue)){
                                                                    foreach($mvalue as $mk=>$mv){
                                                                        if(in_array($mk,$get_all_entry_meta_table_columns)){
                                                                            $import_data_v = $mv;
                                                                            $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                                            if($import_data_v == 'null' || is_null($import_data_v)){
                                                                                $import_entrie_meta_data[$mk] = NULL;
                                                                            }else{
                                                                                $import_entrie_meta_data[$mk] = $import_data_v;
                                                                            } 
                                                                        }
                                                                    }
                                                                    if(!empty($import_entrie_meta_data)){
                                                                        $wpdb->insert($tbl_bookingpress_entries_meta, $import_entrie_meta_data);
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }                                                                                          
                                            } 
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                              
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                    


                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }

                            
                            

                            }else if($detail_import_detail_type == 'images_import'){                                
                                global $tbl_bookingpress_import_images;
                                $limit = 4; 
                                if(!empty($tbl_bookingpress_import_images) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_import_images)){
                                    
                                    $total_records = $wpdb->get_var($wpdb->prepare("SELECT COUNT(import_image_id) FROM `{$tbl_bookingpress_import_images}` where import_id = %d",$import_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_import_images is a table name. false alarm
                                    if($total_records > 0){
                                        if($total_records != $detail_import_total_record){
                                            $import_detail_update = array('detail_import_total_record'=>$total_records);                                        
                                            $wpdb->update($tbl_bookingpress_import_detail_log, $import_detail_update,array('detail_import_id'=>$detail_import_id));
                                            $detail_import_total_record = $total_records;
                                        }
                                        $bookingpress_all_image_import_data = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$tbl_bookingpress_import_images} Where import_id = %d  ORDER BY import_image_id  ASC LIMIT  {$detail_import_last_record}, {$limit}",$import_id),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $table_name is table name defined globally. 
                                        if(!empty($bookingpress_all_image_import_data)){
                                            $update_last_record = $detail_import_last_record + $limit;
                                            $total_imported = 0;
                                            $destination_dir = wp_upload_dir()['basedir'] . "/bookingpress/";
                                            global $tbl_bookingpress_package_bookings_meta,$tbl_bookingpress_appointment_meta;
                                            foreach($bookingpress_all_image_import_data as $bpa_image_data){

                                                if(!empty($bpa_image_data['import_image_url'])){
                                                    $file_url = $bpa_image_data['import_image_url'];

                                                    if($bpa_image_data['import_table'] == $tbl_bookingpress_package_bookings_meta && !empty($tbl_bookingpress_package_bookings_meta) && !empty($file_url)){

                                                        $file_name = basename($file_url);
                                                        $destination_path = $destination_dir . $file_name;
                                                        $result = $this->bookingpress_upload_file_func($file_url,$destination_dir);
                                                        if($result['error'] == 0){

                                                            $upload_file_url = $this->bookingpress_new_file_url($file_name);

                                                            $bookingpress_package_meta_data = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_package_meta_value FROM {$tbl_bookingpress_package_bookings_meta} Where bookingpress_package_bookings_meta_id = %d",$bpa_image_data['import_table_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_package_bookings_meta is table name defined globally.

                                                            if(!empty($bookingpress_package_meta_data)){
                                                                $bookingpress_appointment_meta_data_arr = json_decode($bookingpress_package_meta_data,true);
                                                                if(is_array($bookingpress_package_meta_data_arr) && isset($bookingpress_package_meta_data_arr['form_fields'][$bpa_image_data['import_field']]))
                                                                $bookingpress_package_meta_data_arr['form_fields'][$bpa_image_data['import_field']] =  $upload_file_url;
                                                                $bookingpress_package_meta_data = wp_json_encode($bookingpress_package_meta_data_arr,true);
                                                                
                                                                $wpdb->update($bpa_image_data['import_table'], 
                                                                    array('bookingpress_package_meta_value' => $bookingpress_package_meta_data),
                                                                    array('bookingpress_package_bookings_meta_id' => $bpa_image_data['import_table_id'])
                                                                );
                                                            }

                                                        }                                                          

                                                    }else if($bpa_image_data['import_table'] == $tbl_bookingpress_appointment_meta && !empty($tbl_bookingpress_appointment_meta) && !empty($file_url)){                                                        
                                                        $file_name = basename($file_url);
                                                        $destination_path = $destination_dir . $file_name;
                                                        $result = $this->bookingpress_upload_file_func($file_url,$destination_dir);
                                                        if($result['error'] == 0){

                                                            $upload_file_url = $this->bookingpress_new_file_url($file_name);

                                                            $bookingpress_appointment_meta_data = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} Where bookingpress_appointment_meta_id = %d",$bpa_image_data['import_table_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally.

                                                            if(!empty($bookingpress_appointment_meta_data)){
                                                                $bookingpress_appointment_meta_data_arr = json_decode($bookingpress_appointment_meta_data,true);
                                                                if(is_array($bookingpress_appointment_meta_data_arr) && isset($bookingpress_appointment_meta_data_arr['form_fields'][$bpa_image_data['import_field']]))
                                                                $bookingpress_appointment_meta_data_arr['form_fields'][$bpa_image_data['import_field']] =  $upload_file_url;
                                                                $bookingpress_appointment_meta_data = wp_json_encode($bookingpress_appointment_meta_data_arr,true);
                                                                
                                                                $wpdb->update($bpa_image_data['import_table'], array('bookingpress_appointment_meta_value'=>$bookingpress_appointment_meta_data),array('bookingpress_appointment_meta_id'=>$bpa_image_data['import_table_id']));
                                                            }

                                                        }                                                          
                                                    }else if($bpa_image_data['import_field'] == 'bookingpress_servicemeta_id' || $bpa_image_data['import_field'] == 'bookingpress_staffmembermeta_id' || $bpa_image_data['import_field'] == 'bookingpress_customermeta_id'){
                                                        $image_value_field = 'bookingpress_servicemeta_value';
                                                        if($bpa_image_data['import_field'] == 'bookingpress_staffmembermeta_id'){
                                                            $image_value_field = 'bookingpress_staffmembermeta_value';
                                                        }else if($bpa_image_data['import_field'] == 'bookingpress_customermeta_id'){
                                                            $image_value_field = 'bookingpress_customersmeta_value';
                                                        }
                                                        $file_name = basename($file_url);
                                                        $destination_path = $destination_dir . $file_name;
                                                        $result = $this->bookingpress_upload_file_func($file_url,$destination_dir);
                                                        if($result['error'] == 0){
                                                            $upload_file_url = $this->bookingpress_new_file_url($file_name);
                                                            $image_details   = array();
                                                            $image_details[] = array(
                                                                'name' => $file_name,
                                                                'url'  => $upload_file_url,
                                                            );                                                            
                                                            $wpdb->update($bpa_image_data['import_table'], 
                                                                array($image_value_field=>maybe_serialize($image_details)),
                                                                array($bpa_image_data['import_field']=>$bpa_image_data['import_table_id'])
                                                            );
                                                        }                                                 
                                                    }else if($bpa_image_data['import_field'] == 'bookingpress_location_id'){
                                                        $file_name = basename($file_url);
                                                        $destination_path = $destination_dir . $file_name;
                                                        $result = $this->bookingpress_upload_file_func($file_url,$destination_dir);
                                                        if($result['error'] == 0){
                                                            $upload_file_url = $this->bookingpress_new_file_url($file_name);
                                                            $image_details   = array();
                                                            $image_details = array(
                                                                'bookingpress_location_img_name' => $file_name,
                                                                'bookingpress_location_img_url'  => $upload_file_url,
                                                            );                                                         
                                                            $wpdb->update($bpa_image_data['import_table'], $image_details,array($bpa_image_data['import_field']=>$bpa_image_data['import_table_id']));
    
                                                        }                                                      
                                                    }else if($bpa_image_data['import_field'] == 'package_images'){
                                                        $file_name = basename($file_url);
                                                        $destination_path = $destination_dir . $file_name;
                                                        $result = $this->bookingpress_upload_file_func($file_url,$destination_dir);
                                                        if($result['error'] == 0){
                                                            $upload_file_url = $this->bookingpress_new_file_url($file_name);
                                                            $image_details   = array();
                                                            $image_details = array(
                                                                'bookingpress_package_id'       => $bpa_image_data['import_table_id'],
                                                                'bookingpress_package_img_name' => $file_name,
                                                                'bookingpress_package_img_url'  => $upload_file_url,
                                                            );                                                         
                                                            $wpdb->insert($bpa_image_data['import_table'], $image_details);
                                                        }
                                                    }else if($bpa_image_data['import_field'] == 'company_icon_url' || $bpa_image_data['import_field'] == 'company_avatar_url'){
                                                        $file_name = basename($file_url);
                                                        $destination_path = $destination_dir . $file_name;
                                                        $result = $this->bookingpress_upload_file_func($file_url,$destination_dir);
                                                        if($result['error'] == 0){
                                                            $upload_file_url = $this->bookingpress_new_file_url($file_name);
                                                            $BookingPress->bookingpress_update_settings($bpa_image_data['import_field'],'company_setting', $upload_file_url);
                                                        }
                                                    }

                                                    do_action('bookingpress_images_import_data',$bpa_image_data,$destination_dir);

                                                }
                                                $total_imported++;
                                            }
                                            $total_imported = $total_imported + $detail_import_last_record; 
                                        }else{
                                            $not_import_data_reason = esc_html__('All image data imported.','bookingpress-appointment-booking');
                                            $is_complete = 1;
                                        }
                                    }else{
                                        $is_complete = 1;
                                    }
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }
                            
                            }else if($detail_import_detail_type == 'customers' && isset($bookingpress_import_data[$detail_import_detail_type])){
                                
                                global $tbl_bookingpress_customers,$tbl_bookingpress_customers_meta;
                                $limit = 50;                            
                                if(!empty($tbl_bookingpress_customers) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_customers)){
                                    $get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_customers);
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;                                    
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();
                                            foreach($import_record_data[$i] as $key=>$value){
                                                if(in_array($key,$get_all_table_columns)){
                                                    $import_data_v = $import_record_data[$i][$key];                                                  
                                                    if($import_data_v == 'null' || is_null($import_data_v)){
                                                        $single_import_record[$key] = NULL;  
                                                        if($key == "bookingpress_customer_full_name"){
                                                            $single_import_record[$key] = "";
                                                        }
                                                    }else{
                                                        $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                        $single_import_record[$key] = sanitize_text_field($import_data_v);
                                                    }                                                    
                                                }
                                            }
                                            if(!empty($single_import_record)){
                                                
                                                $old_id = $import_record_data[$i]['bookingpress_customer_id'];                                                
                                                unset($single_import_record['bookingpress_customer_id']);
                                                $bookingpress_old_wpuser_id = $single_import_record['bookingpress_wpuser_id'];
                                                $customer_metadata = $import_record_data[$i]['customer_metadata'];


                                                $bookingpress_user_email = (isset($single_import_record['bookingpress_user_email']))?$single_import_record['bookingpress_user_email']:'';
                                                $exists_customer_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_customer_id,bookingpress_wpuser_id FROM `{$tbl_bookingpress_customers}` Where bookingpress_user_email = %s",$bookingpress_user_email),ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers_meta is table name.

                                                $bookingpress_old_wpuser_id = $single_import_record['bookingpress_wpuser_id'];
                                                $exists_customer_id = 0;
                                                $exists_customer_user_id = 0;
                                                if(!empty($exists_customer_data)){
                                                    $exists_customer_id = (isset($exists_customer_data['bookingpress_customer_id']))?$exists_customer_data['bookingpress_customer_id']:0;
                                                    $exists_customer_user_id = (isset($exists_customer_data['bookingpress_wpuser_id']))?$exists_customer_data['bookingpress_wpuser_id']:0;
                                                }
                                                if($exists_customer_id == "" || $exists_customer_id == 0){
                                                    $customer_user_detail = $import_record_data[$i]['wp_user'];
                                                    if(!empty($customer_user_detail)){   
                                                        $wp_user_meta = $import_record_data[$i]['wp_user_meta'];
                                                        $user_id = $this->bookingpress_create_wp_user($customer_user_detail,$wp_user_meta,'customers');
                                                        $single_import_record['bookingpress_wpuser_id'] = $user_id;
                                                    }else{
                                                        $single_import_record['bookingpress_wpuser_id'] = 0;
                                                    }                                                
                                                    $bookingpress_final_wpuser_id = $single_import_record['bookingpress_wpuser_id'];
                                                    $wpdb->insert($tbl_bookingpress_customers, $single_import_record);
                                                    $last_import_id = $wpdb->insert_id;
                                                }else{
                                                    $last_import_id = $exists_customer_id;
                                                    $customer_user_detail = $import_record_data[$i]['wp_user'];
                                                    $has_exiring_user = false;
                                                    if(!empty($exists_customer_user_id)){
                                                        $exists_customer_user_id = intval($exists_customer_user_id);
                                                        $bookingpress_current_user_obj = get_user_by('id', $exists_customer_user_id);
                                                        if(!empty($bookingpress_current_user_obj)){
                                                            $has_exiring_user = true;
                                                        }                                                                                     
                                                    }                                                    
                                                    if($has_exiring_user == false && !empty($customer_user_detail)){
                                                        $wp_user_meta = $import_record_data[$i]['wp_user_meta'];
                                                        $user_id = $this->bookingpress_create_wp_user($customer_user_detail,$wp_user_meta,'customers');
                                                        $single_import_record['bookingpress_wpuser_id'] = $user_id;
                                                        $bookingpress_final_wpuser_id = $user_id;                                                        
                                                    }else{
                                                        $bookingpress_final_wpuser_id = $exists_customer_user_id;
                                                    }
                                                }
                                                if(!empty($customer_metadata) && !empty($tbl_bookingpress_customers_meta)){                                                    
                                                    foreach($customer_metadata as $custmdata){
                                                        $customer_meta_data = $custmdata;
                                                        $bookingpress_customersmeta_value = $customer_meta_data['bookingpress_customersmeta_value'];
                                                        if($customer_meta_data['bookingpress_customersmeta_key'] == 'customer_avatar_details'){
                                                            $customer_meta_data['bookingpress_customersmeta_value'] = '';
                                                        }
                                                        $customer_meta_data['bookingpress_customer_id'] = $last_import_id;
                                                        $bookingpress_customersmeta_key = $customer_meta_data['bookingpress_customersmeta_key'];

                                                        $exists_customer_meta_id = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_customer_id FROM `{$tbl_bookingpress_customers_meta}` Where bookingpress_customer_id = %d AND bookingpress_customersmeta_key = %s",$last_import_id,$bookingpress_customersmeta_key));  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers_meta is table name.
                                                        if($exists_customer_meta_id == 0 || $exists_customer_meta_id == ""){
                                                            $wpdb->insert($tbl_bookingpress_customers_meta, $customer_meta_data);
                                                            $last_meta_import_id = $wpdb->insert_id;
                                                            if($customer_meta_data['bookingpress_customersmeta_key'] == 'customer_avatar_details' && !empty($bookingpress_customersmeta_value)){
                                                                $bookingpress_customersmeta_value = str_replace(['\\', ''], '', $bookingpress_customersmeta_value);
                                                                $bookingpress_customersmeta_value = unserialize($bookingpress_customersmeta_value);
                                                                if(isset($bookingpress_customersmeta_value[0]['name']) && isset($bookingpress_customersmeta_value[0]['url'])){
                                                                    $file_name = $bookingpress_customersmeta_value[0]['name'];
                                                                    $file_url = $bookingpress_customersmeta_value[0]['url'];
                                                                    $this->bookingpress_set_attachment_records($tbl_bookingpress_customers_meta,'bookingpress_customermeta_id',$last_meta_import_id,$import_id,$file_name,$file_url,$export_key);
                                                                }                                                            
                                                            }    
                                                        }

                                                    }
                                                }
                                                $this->bookingpress_set_record_rel('customers',$export_key,$import_id,$old_id,$last_import_id,'');
                                                if($bookingpress_final_wpuser_id > 0){
                                                    $this->bookingpress_set_record_rel('customer_wp_user',$export_key,$import_id,$bookingpress_old_wpuser_id,$bookingpress_final_wpuser_id,'');
                                                }                                                
                                            } 
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                                    
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                                                 
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }

                            }else if($detail_import_detail_type == 'notifications' && isset($bookingpress_import_data[$detail_import_detail_type])){
                                global $tbl_bookingpress_notifications;
                                $limit = 50;                            
                                if(!empty($tbl_bookingpress_notifications) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_notifications)){
                                    if($detail_import_last_record == 0){
                                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_notifications"); // phpcs:ignore 
                                    } 
                                    $get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_notifications);
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();
                                            foreach($import_record_data[$i] as $key=>$value){
                                                if(in_array($key,$get_all_table_columns)){
                                                    $import_data_v = $import_record_data[$i][$key];
                                                    $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                    if($import_data_v == 'null' || is_null($import_data_v)){
                                                        $single_import_record[$key] = NULL;    
                                                    }else{
                                                        $single_import_record[$key] = $import_data_v;
                                                    }                                                    
                                                }
                                            }
                                            if(!empty($single_import_record)){
                                                if(isset($single_import_record['bookingpress_notification_service']) && $single_import_record['bookingpress_notification_service'] != '' && $single_import_record['bookingpress_notification_service'] != 0){
                                                    $bookingpress_notification_service = $single_import_record['bookingpress_notification_service'];
                                                    if($bookingpress_notification_service){
                                                        $bookingpress_notification_service_arr = explode(",",$bookingpress_notification_service);
                                                        $final_service_arr = array();
                                                        if(!empty($bookingpress_notification_service_arr)){
                                                            foreach($bookingpress_notification_service_arr as $serid){
                                                                $final_service_arr[] = $this->bookingpress_get_record_rel('services',$export_key,$import_id,$serid);
                                                            }                                                            
                                                        }
                                                        if(!empty($final_service_arr)){
                                                            $single_import_record['bookingpress_notification_service'] = implode(',',$final_service_arr);
                                                        }    
                                                    }
                                                }
                                                $old_id = $single_import_record['bookingpress_notification_id'];

                                                unset($single_import_record['bookingpress_notification_id']);
                                                
                                                $wpdb->insert($tbl_bookingpress_notifications, $single_import_record);
                                                $last_import_id = $wpdb->insert_id;
                                            } 
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                                    
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                                                 
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }

                               

                            }else if($detail_import_detail_type == 'custom_fields' && isset($bookingpress_import_data[$detail_import_detail_type])){

                                global $tbl_bookingpress_form_fields, $BookingPress;
                                $limit = 50;                            
                                if(!empty($tbl_bookingpress_form_fields) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_form_fields)){
                                    if($detail_import_last_record == 0){
                                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_form_fields"); // phpcs:ignore 
                                    }
                                    $get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_form_fields);
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();
                                            foreach($import_record_data[$i] as $key=>$value){
                                                if(in_array($key,$get_all_table_columns)){
                                                    $import_data_v = $import_record_data[$i][$key];
                                                    if( 'bookingpress_field_values' == $key ){
                                                        $import_data_v = stripslashes_deep($import_data_v);
                                                    } else {
                                                        $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                    }

                                                    if($import_data_v == 'null' || is_null($import_data_v)){
                                                        $single_import_record[$key] = NULL;    
                                                    }else{
                                                        if(!empty($import_data_v) && gettype($import_data_v) === 'string' && is_array(json_decode($import_data_v,true))){
                                                            $single_import_record[$key] = $import_data_v;
                                                        }else{
                                                            $single_import_record[$key] = $import_data_v;
                                                        }                                                        
                                                    }                                                    
                                                }
                                            }
                                            $bookingpress_field_is_default = (isset($single_import_record['bookingpress_field_is_default']))?$single_import_record['bookingpress_field_is_default']:'';
                                            if(!$bookingpress_active_plugin_module_list['bookingpress_pro'] && ($bookingpress_field_is_default == 0 || $bookingpress_field_is_default == '0')){
                                                $single_import_record = array();    
                                            }
                                            if(!empty($single_import_record)){

                                                if(!empty($single_import_record['bookingpress_field_options'])){                                                    
                                                    $bookingpress_field_options = (isset($single_import_record['bookingpress_field_options']))?$single_import_record['bookingpress_field_options']:'';
                                                    $bookingpress_field_options_arr = json_decode($bookingpress_field_options,true);
                                                    if(isset($bookingpress_field_options_arr['selected_services']) && !empty($bookingpress_field_options_arr['selected_services'])){
                                                        $bookingpress_services_arr = $bookingpress_field_options_arr['selected_services'];
                                                        $final_service_arr = array();
                                                        if(!empty($bookingpress_services_arr)){
                                                            foreach($bookingpress_services_arr as $serid){
                                                                $final_service_arr[] = $this->bookingpress_get_record_rel('services',$export_key,$import_id,$serid);
                                                            }                                                            
                                                        }
                                                        if(!empty($final_service_arr)){
                                                            $bookingpress_field_options_arr['selected_services'] = $final_service_arr;
                                                        }
                                                        $single_import_record['bookingpress_field_options'] = wp_json_encode($bookingpress_field_options_arr,true);
                                                    }

                                                }
                                                $old_id = $single_import_record['bookingpress_form_field_id'];                                                
                                                $wpdb->insert($tbl_bookingpress_form_fields, $single_import_record);
                                                $last_import_id = $wpdb->insert_id;                                                                                                                                               
                                            } 
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                                    
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                                                 
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }
                                    
                            }else if($detail_import_detail_type == 'bookingpress_servicesmeta' && isset($bookingpress_import_data[$detail_import_detail_type])){
                                global $tbl_bookingpress_servicesmeta;
                                $limit = 30;                            
                                if(!empty($tbl_bookingpress_servicesmeta) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_servicesmeta)){
                                    $get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_servicesmeta);
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();
                                            foreach($import_record_data[$i] as $key=>$value){
                                                if(in_array($key,$get_all_table_columns)){
                                                    $import_data_v = $import_record_data[$i][$key];
                                                    $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                    if($import_data_v == 'null'){
                                                        $single_import_record[$key] = NULL;    
                                                    }else{
                                                        $single_import_record[$key] = $import_data_v;
                                                    }                                                    
                                                }
                                            }                                                                                       
                                            if(!empty($single_import_record)){                                                
                                                $old_id = $single_import_record['bookingpress_servicemeta_id'];                                                 
                                                $bookingpress_service_id = $this->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
                                                if($bookingpress_service_id != '' && $bookingpress_service_id != 0){

                                                    $bookingpress_servicemeta_value = $single_import_record['bookingpress_servicemeta_value'];
                                                    if($single_import_record['bookingpress_servicemeta_name'] == 'service_image_details'){
                                                        $single_import_record['bookingpress_servicemeta_value'] = '';                                                        
                                                    }
                                                    $single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
                                                    unset($single_import_record['bookingpress_servicemeta_id']);
                                                    
                                                    if($single_import_record['bookingpress_servicemeta_name'] != 'bookingpress_woocommerce_product'){
                                                        $wpdb->insert($tbl_bookingpress_servicesmeta, $single_import_record);
                                                    }                                                    
                                                    $last_import_id = $wpdb->insert_id;  
                                                    if($single_import_record['bookingpress_servicemeta_name'] == 'service_image_details' && !empty($bookingpress_servicemeta_value)){

                                                        $bookingpress_servicemeta_value = unserialize($bookingpress_servicemeta_value);
                                                        if(isset($bookingpress_servicemeta_value[0]['name']) && isset($bookingpress_servicemeta_value[0]['url'])){
                                                            $file_name = $bookingpress_servicemeta_value[0]['name'];
                                                            $file_url = $bookingpress_servicemeta_value[0]['url'];
                                                            $this->bookingpress_set_attachment_records($tbl_bookingpress_servicesmeta,'bookingpress_servicemeta_id',$last_import_id,$import_id,$file_name,$file_url,$export_key);
                                                        }

                                                    }                                                      
                                                    
                                                }                                                
                                            } 
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                                    
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                                                 
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }                            
                            }else if($detail_import_detail_type == 'services' && isset($bookingpress_import_data[$detail_import_detail_type])){
                                global $tbl_bookingpress_services;
                                $limit = 10;                            
                                if(!empty($tbl_bookingpress_services) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_services)){
                                    $get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_services);
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    $bookingpress_service_position = $this->bookingpress_get_max_rec_position_func($tbl_bookingpress_services,'bookingpress_service_position');
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();
                                            foreach($import_record_data[$i] as $key=>$value){
                                                if(in_array($key,$get_all_table_columns)){
                                                    $import_data_v = $import_record_data[$i][$key];                                                  
                                                    if($import_data_v == 'null' || is_null($import_data_v)){
                                                        $single_import_record[$key] = NULL;    
                                                    }else{
                                                        $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                        $single_import_record[$key] = sanitize_text_field($import_data_v);
                                                    }                                                    
                                                }
                                            }
                                            if(!empty($single_import_record)){
                                                $bookingpress_service_position++;
                                                $old_id = $import_record_data[$i]['bookingpress_service_id']; 
                                                $single_import_record['bookingpress_service_position'] = $bookingpress_service_position;
                                                $bookingpress_category_id = $this->bookingpress_get_record_rel('category',$export_key,$import_id,$import_record_data[$i]['bookingpress_category_id']);
                                                if(empty($bookingpress_category_id)){
                                                    $bookingpress_category_id = 0;
                                                }
                                                $single_import_record['bookingpress_category_id'] = $bookingpress_category_id;
                                                unset($single_import_record['bookingpress_service_id']);
                                                
                                                $wpdb->insert($tbl_bookingpress_services, $single_import_record);
                                                $last_import_id = $wpdb->insert_id;
                                                $this->bookingpress_set_record_rel('services',$export_key,$import_id,$old_id,$last_import_id,'service');
                                            } 
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                                    
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                                                 
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }
                            }else if($detail_import_detail_type == 'category' && isset($bookingpress_import_data[$detail_import_detail_type])){
                                global $tbl_bookingpress_categories;
                                $limit = 1;                            
                                if(!empty($tbl_bookingpress_categories) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_categories)){
                                    $get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_categories);
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    $bookingpress_category_position = $this->bookingpress_get_max_rec_position_func($tbl_bookingpress_categories,'bookingpress_category_position');
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){                                       
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();
                                            foreach($import_record_data[$i] as $key=>$value){
                                                if(in_array($key,$get_all_table_columns)){
                                                    $import_data_v = $import_record_data[$i][$key];                                                    
                                                    if($import_data_v == 'null'){
                                                        $single_import_record[$key] = NULL;    
                                                    }else{
                                                        $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                        $single_import_record[$key] = sanitize_text_field($import_data_v);
                                                    }                                                    
                                                }
                                            }
                                            if(!empty($single_import_record)){   
                                                $bookingpress_category_position++;                                             
                                                $old_id = $single_import_record['bookingpress_category_id'];
                                                $single_import_record['bookingpress_category_position'] = $bookingpress_category_position;
                                                unset($single_import_record['bookingpress_category_id']);
                                                $wpdb->insert($tbl_bookingpress_categories, $single_import_record);
                                                $last_import_id = $wpdb->insert_id;
                                                $this->bookingpress_set_record_rel('category',$export_key,$import_id,$old_id,$last_import_id,'category');
                                            } 
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                                    
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                                                 
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }                            
                            }else if($detail_import_detail_type == 'default_daysoff' && isset($bookingpress_import_data[$detail_import_detail_type])){
                                global $tbl_bookingpress_default_daysoff;
                                $limit = 50;                            
                                if(!empty($tbl_bookingpress_default_daysoff) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_default_daysoff)){
                                    if($detail_import_last_record == 0){
                                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_default_daysoff"); // phpcs:ignore
                                    }                                    
                                    $get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_default_daysoff);
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $single_import_record = array();
                                            foreach($import_record_data[$i] as $key=>$value){
                                                if(in_array($key,$get_all_table_columns)){
                                                    $import_data_v = $import_record_data[$i][$key];
                                                    $import_data_v = $this->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
                                                    $single_import_record[$key] = sanitize_text_field($import_data_v);
                                                }
                                            }
                                            if(!empty($single_import_record)){                                                                                             
                                                unset($single_import_record['bookingpress_dayoff_id']);
                                                $wpdb->insert($tbl_bookingpress_default_daysoff, $single_import_record);
                                                $last_import_id = $wpdb->insert_id;                                                
                                            } 
                                            $total_imported++;                                          
                                        }
                                    }                                    
                                    $total_imported = $total_imported + $detail_import_last_record;                                    
                                    if($detail_import_total_record <= $total_imported){
                                        $is_complete = 1;
                                    }                                                                 
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }
                                  

                            }else if($detail_import_detail_type == 'default_workhours' && isset($bookingpress_import_data[$detail_import_detail_type])){                               
                                global $tbl_bookingpress_default_workhours;                                   
                                if(!empty($tbl_bookingpress_default_workhours) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_default_workhours)){
                                    if($detail_import_last_record == 0){
                                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_default_workhours"); // phpcs:ignore                                       
                                    }
                                    //$get_all_table_columns = $this->bookingpress_get_all_columns_func($tbl_bookingpress_default_workhours);
                                    $default_workhours_val = $bookingpress_import_data['default_workhours'];
                                    $this->insert_multiple_rows($tbl_bookingpress_default_workhours,$default_workhours_val,array('bookingpress_workhours_id'),array('bookingpress_start_time','bookingpress_end_time'));
                                    $total_imported = $detail_import_total_record;
                                    $is_complete = 1;
                                }else{
                                    $not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
                                    $is_complete = 2;
                                }                                
                            }else if($detail_import_detail_type == 'settings' && isset($bookingpress_import_data[$detail_import_detail_type])){
                                wp_cache_delete( 'bookingpress_all_general_settings' );
                                $limit = 200;  
                                global $tbl_bookingpress_settings;                      
                                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    $image_fields = array('company_avatar_url','company_icon_url');
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $setting_name  = (isset($import_record_data[$i]['setting_name']))?sanitize_text_field($import_record_data[$i]['setting_name']):'';
                                            $setting_value = (isset($import_record_data[$i]['setting_value']))?$import_record_data[$i]['setting_value']:'';
                                            $setting_type  = (isset($import_record_data[$i]['setting_type']))?sanitize_text_field($import_record_data[$i]['setting_type']):'';
                                            if(in_array($setting_name,$image_fields)){
                                                if(!empty($setting_value)){
                                                    $file_name = basename($setting_value);
                                                    $this->bookingpress_set_attachment_records($tbl_bookingpress_settings,$setting_name,0,$import_id,$file_name,$setting_value,$export_key);
                                                    $setting_value = '';
                                                }                                                
                                            }else{
                                                $setting_value = $this->bookingpress_import_value_modified($setting_value,$detail_import_detail_type,'setting_value');
                                                $BookingPress->bookingpress_update_settings($setting_name, $setting_type, $setting_value);   
                                            }                                            
                                            $total_imported++;
                                        }
                                    }
                                    $total_imported = $total_imported + $detail_import_last_record;
                                    if($total_record <= $total_imported){
                                        $is_complete = 1;
                                    }
                                }else{
                                    $not_import_data_reason = esc_html__('No Record Found.','bookingpress-appointment-booking');
                                    $is_complete = 2;                                    
                                }
                            }else if($detail_import_detail_type == 'customize' && isset($bookingpress_import_data[$detail_import_detail_type])){
                                                                
                                wp_cache_delete( 'bookingpress_all_customize_settings' );
                                $limit = 200;                        
                                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
                                    $total_record = count($bookingpress_import_data[$detail_import_detail_type]);
                                    $import_record_data = $bookingpress_import_data[$detail_import_detail_type];
                                    $total_imported = 0;
                                    $new_limit = $limit + $detail_import_last_record;
                                    for($i=$detail_import_last_record; $i<$new_limit; $i++){
                                        if(isset($import_record_data[$i])){
                                            $bookingpress_setting_name  = (isset($import_record_data[$i]['bookingpress_setting_name']))?sanitize_text_field($import_record_data[$i]['bookingpress_setting_name']):'';
                                            $bookingpress_setting_value = (isset($import_record_data[$i]['bookingpress_setting_value']))?$import_record_data[$i]['bookingpress_setting_value']:'';
                                            $bookingpress_setting_type  = (isset($import_record_data[$i]['bookingpress_setting_type']))?sanitize_text_field($import_record_data[$i]['bookingpress_setting_type']):'';
                                            $bookingpress_setting_value = $this->bookingpress_import_value_modified($bookingpress_setting_value,$detail_import_detail_type,'bookingpress_setting_value');


                                            if($bookingpress_setting_name == 'bookingpress_form_sequance'){
                                                
                                                if(!empty($bookingpress_setting_value)){

                                                    $form_sequence = $BookingPress->bookingpress_get_customize_settings( 'bookingpress_form_sequance', 'booking_form' );
                                                    $form_sequence = json_decode( $form_sequence, true );                                                    
                                                    $bookingpress_setting_value_final = json_decode($bookingpress_setting_value,true);
                                                    if(is_array($bookingpress_setting_value_final) && !empty($bookingpress_setting_value_final) && is_array($form_sequence) && !empty($form_sequence)){
                                                        $update_form_sequance = true;
                                                        foreach($form_sequence as $key){
                                                            if(!in_array($key,$bookingpress_setting_value_final)){
                                                                $update_form_sequance = false;
                                                            }
                                                        }
                                                        if($update_form_sequance){
                                                            $this->bookingpress_update_customize_settings($bookingpress_setting_name, $bookingpress_setting_type, $bookingpress_setting_value);
                                                        }
                                                    }
                                                }
                                            }else{
                                                $this->bookingpress_update_customize_settings($bookingpress_setting_name, $bookingpress_setting_type, $bookingpress_setting_value);
                                            }                                            
                                            $total_imported++;
                                        }
                                    }
                                    $total_imported = $total_imported + $detail_import_last_record;
                                    if($total_record <= $total_imported){
                                        $is_complete = 1;
                                        $this->generate_bookingpress_dynamic_css_file();
                                    }
                                }else{
                                    $not_import_data_reason = esc_html__('No Record Found.','bookingpress-appointment-booking');
                                    $is_complete = 2;                                    
                                }


                            }

                            $bookingpress_import_detail_type_data = apply_filters( 'bookingpress_modified_import_data_process',array(),$bookingpress_import_data,$detail_import_detail_type,$detail_import_last_record,$limit,$export_key,$import_id,$detail_import_total_record);
                            global $BookingPress;
                            
                            if(!empty($bookingpress_import_detail_type_data)){   
                                if(isset($bookingpress_import_detail_type_data['limit'])){
                                    $limit = $bookingpress_import_detail_type_data['limit'];
                                }                             
                                $total_imported = (isset($bookingpress_import_detail_type_data['total_imported']))?$bookingpress_import_detail_type_data['total_imported']:0;
                                if($bookingpress_import_detail_type_data['is_complete'] == 0){
                                    $is_complete = 0;
                                }else{
                                    $is_complete = 1; 
                                }
                            } 

                            $detail_import_last_record = $detail_import_last_record + $limit;
                            if($detail_import_last_record > $detail_import_total_record){
                                $detail_import_last_record = $detail_import_total_record;
                            }
                            

                            $import_detail_update = array('detail_import_last_record'=>$detail_import_last_record,'total_import_record'=>$total_imported);
                            if($is_complete != 0){
                                $import_detail_update['detail_import_complete'] = $is_complete;
                                if(!empty($not_import_data_reason)){
                                    $import_detail_update['detail_import_stop_reason'] = $not_import_data_reason;
                                }
                            }
                            $wpdb->update($tbl_bookingpress_import_detail_log, $import_detail_update,array('detail_import_id'=>$detail_import_id));
                            $response['variant']                = 'success';
                            $response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                            $response['msg']                    = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                            $response['is_complete']            = '';
                            $response['import_log_data']        = $this->get_import_log_data();
                            $response['import_detail_id']       = $detail_import_id;                                
                            echo wp_json_encode( $response );
                            die();                            
                        }else{
                            $wpdb->update($tbl_bookingpress_import_data_log, array('import_complete'=>1),array('import_id'=>$import_id));
                            $response['variant']                = 'success';
                            $response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                            $response['msg']                    = esc_html__( 'Import process completed successfully.', 'bookingpress-appointment-booking' );
                            $response['export_data_file']       = '';
                            $response['is_complete']            = $import_id;
                            $response['import_log_data']        = '';
                            $this->bookingpress_remove_import_export_directory('import');
                            echo wp_json_encode( $response );
                            die(); 

                        }

                    }    
                }

                echo wp_json_encode( $response );
                die();
            }

            
            
        }

        // Function to check if a string is valid JSON
        function bookingpress_isvalidjson($string) {            
            json_decode($string);                        
            return json_last_error() === JSON_ERROR_NONE;
        }


        /**
         * Function for export data process
         *
         * @return void
        */
        function bookingpress_import_data_process_func(){
            global $BookingPress,$tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log,$tbl_bookingpress_import_record_rel,$wpdb,$bookingpress_other_debug_log_id;
            @ini_set('memory_limit','512M');// phpcs:ignore
            set_time_limit(0);

            $response                = array();
            $response['variant']     = 'error';
            $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
            $response['msg']         = esc_html__( 'Sorry, Something Wrong.', 'bookingpress-appointment-booking' );

			$bpa_check_authorization = $this->bpa_check_authentication( 'import_export_settings', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingpress_import_data = array();
            if(!empty($_POST['bookingpress_import_data']) && isset($_POST['bookingpress_import_data'])){ //phpcs:ignore
                if(!empty($_POST['bookingpress_import_data'])){ //phpcs:ignore
                    //$_POST['bookingpress_import_data'] = gzuncompress($_POST['bookingpress_import_data']);
                }
                $bookingpress_import_data = stripslashes($_POST['bookingpress_import_data']); // phpcs:ignore
                if(!$this->bookingpress_isvalidjson($bookingpress_import_data)){
                    $response['variant']     = 'error';
                    $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
                    $response['msg']         = esc_html__( 'Sorry, Your request can not be processed your added data not valid.', 'bookingpress-appointment-booking' );
                    $response['coupon_data'] = array(); 
                    echo wp_json_encode( $response );
                    die();
                }
                $bookingpress_import_data = json_decode($bookingpress_import_data,true);                
                $bookingpress_required_all_active_addon = (isset($bookingpress_import_data['all_active_addon']))?$bookingpress_import_data['all_active_addon']:array('none');
                $bookingpress_all_active_addons = $this->bookingpress_get_active_addon_module_list();
                if(empty($bookingpress_all_active_addons)){
                    $bookingpress_all_active_addons = array('not_valid');
                }
                $all_required_active_addon_key = array();
                $bookingpress_all_required_addon_active = true;
                if(!empty($bookingpress_required_all_active_addon)){
                    foreach($bookingpress_required_all_active_addon as $addonval){
                        if(!in_array($addonval,$bookingpress_all_active_addons)){
                            $all_required_active_addon_key[] = $addonval;                            
                            $bookingpress_all_required_addon_active = false;
                        }
                    }
                }

                if(!$bookingpress_all_required_addon_active){

                    $bookingpress_all_required_active_addon = $this->bookingpress_get_addon_name_by_key_list($all_required_active_addon_key);
                    if(!empty($bookingpress_all_required_active_addon) && is_array($bookingpress_all_required_active_addon)){

                        $total_addon = count($bookingpress_all_required_active_addon);
                        $bookingpress_all_required_active_addon = implode(',',$bookingpress_all_required_active_addon);
                        if($total_addon == 1){
                            $message = sprintf(esc_html__('Sorry, Please activate %s addon to continue importing.', 'bookingpress-appointment-booking'), $bookingpress_all_required_active_addon) . ""; // phpcs:ignore
                        }else{
                            $message = sprintf(esc_html__('Sorry, Please activate %s addons to continue importing', 'bookingpress-appointment-booking'), $bookingpress_all_required_active_addon) . ""; // phpcs:ignore
                        }                        
                        $response['variant']     = 'error';
                        $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
                        $response['msg']         = $message;
                        $response['coupon_data'] = array();              
                        echo wp_json_encode( $response );
                        die(); 

                    }else{

                        
                        $response['variant']     = 'error';
                        $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
                        $response['msg']         = esc_html__( 'Sorry, Please activate all addons or modules that are active on the export website.', 'bookingpress-appointment-booking' );
                        $response['coupon_data'] = array();              
                        echo wp_json_encode( $response );
                        die();

                    }
                }                
                $bookingpress_required_all_active_addon = (isset($bookingpress_import_data['all_active_addon']))?$bookingpress_import_data['all_active_addon']:array('none');
                $site_url = (isset($bookingpress_import_data['site_url']))?sanitize_text_field($bookingpress_import_data['site_url']):'';
                $export_key = (isset($bookingpress_import_data['export_key']))?sanitize_text_field($bookingpress_import_data['export_key']):'';
                $export_site_key = (isset($bookingpress_import_data['export_site_key']))?sanitize_text_field($bookingpress_import_data['export_site_key']):'';
                $export_key_not_in = array('site_url','export_key','export_site_key','all_active_addon');
                $all_import_item_lists = $this->get_export_data_key_name_arr();

                $bookingpress_import_items_key = array();
                $bookingpress_all_import_items_key = array('none');
                foreach($all_import_item_lists as $import_key=>$all_import_items){
                    if(isset($bookingpress_import_data[$import_key])){
                        $bookingpress_all_import_items_key[] = $import_key;
                        $total_record = (!empty($bookingpress_import_data[$import_key]) && is_array($bookingpress_import_data[$import_key]))?count($bookingpress_import_data[$import_key]):0;
                        $bookingpress_import_items_key[] = array('import_key'=>$import_key,'total_record'=>$total_record);
                    }
                }
                if(empty($bookingpress_import_items_key)){
                    $response['variant']     = 'error';
                    $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
                    $response['msg']         = esc_html__( 'Sorry, Import data not found.', 'bookingpress-appointment-booking' );
                    $response['coupon_data'] = array();   
                    echo wp_json_encode( $response );
                    die();                     
                }
                $confirm_import_data = (isset($_REQUEST['confirm_import_data']))?sanitize_text_field($_REQUEST['confirm_import_data']):'';
                if(empty($confirm_import_data)){
                    $bookingpress_confirm_msg = '';
                    if(in_array('services',$bookingpress_all_import_items_key)){
                        $bookingpress_confirm_msg.= esc_html__( 'Custom Fields, Notification', 'bookingpress-appointment-booking' );
                    }
                    if(in_array('appointments',$bookingpress_all_import_items_key)){
                        if(!empty($bookingpress_confirm_msg)){
                            $bookingpress_confirm_msg.= ', '.esc_html__( 'Appointments, Payment Transaction', 'bookingpress-appointment-booking' );
                        }else{
                            $bookingpress_confirm_msg.= esc_html__( 'Appointments, Payment Transaction', 'bookingpress-appointment-booking' );
                        }                        
                    }                    
                    if(in_array('package_order',$bookingpress_all_import_items_key)){
                        if(!empty($bookingpress_confirm_msg)){
                            $bookingpress_confirm_msg.= ', '.esc_html__( 'Package Order', 'bookingpress-appointment-booking' );
                        }else{
                            $bookingpress_confirm_msg.= esc_html__( 'Package Order', 'bookingpress-appointment-booking' );
                        }                        
                    }
                    
                    $bookingpress_confirm_msg = apply_filters( 'bookingpress_modified_import_confirm_message',$bookingpress_confirm_msg,$bookingpress_all_import_items_key);
                    
                    if(!empty($bookingpress_confirm_msg)){

                        $bookingpress_confirm_msg = esc_html__( 'Import process will be removing existing data associated with', 'bookingpress-appointment-booking' ). ' ' .$bookingpress_confirm_msg.'.';

                        $response['variant']     = 'confirm';
                        $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
                        $response['msg']         = $bookingpress_confirm_msg;
                        $response['coupon_data'] = array();  
                        echo wp_json_encode( $response );
                        die();
                    }                     
                }
                $import_data = wp_json_encode($bookingpress_import_items_key);
                $bookingpress_db_fields = array(
                    'import_data'     => $import_data,
                    'import_complete' => 0,
                    'export_key'      => $export_key,
                    'export_site_key' => $export_site_key,
                    'site_url'        => $site_url,                                                    
                );
                $wpdb->insert($tbl_bookingpress_import_data_log, $bookingpress_db_fields); 
                $import_id = $wpdb->insert_id;
                foreach($bookingpress_import_items_key as $val){
                    $bookingpress_db_fields = array(
                        'import_id'                 => $import_id,
                        'detail_import_detail_type' => $val['import_key'],
                        'detail_import_complete'    => 0,
                        'detail_import_total_record' => $val['total_record'],
                        'detail_import_display' => (isset($all_import_item_lists[$val['import_key']]['is_display']))?$all_import_item_lists[$val['import_key']]['is_display']:0,
                        'export_key'      => $export_key,
                        'export_site_key' => $export_site_key,                                                 
                    );
                    $wpdb->insert($tbl_bookingpress_import_detail_log, $bookingpress_db_fields);                     
                }

                $bookingpress_db_fields = array(
                    'import_id'                 => $import_id,
                    'detail_import_detail_type' => 'images_import',
                    'detail_import_complete'    => 0,
                    'detail_import_total_record' => 0,
                    'detail_import_display' => 1,
                    'export_key'      => $export_key,
                    'export_site_key' => $export_site_key,                                                 
                );
                $wpdb->insert($tbl_bookingpress_import_detail_log, $bookingpress_db_fields);

                if(in_array('appointments',$bookingpress_all_import_items_key)){

                    global $tbl_bookingpress_appointment_bookings,$tbl_bookingpress_appointment_meta,$tbl_bookingpress_entries,$tbl_bookingpress_payment_logs,$tbl_bookingpress_entries_meta,$tbl_bookingpress_package_bookings,$tbl_bookingpress_package_bookings_meta;

                    if(!empty($tbl_bookingpress_appointment_bookings) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_appointment_bookings)){
                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_appointment_bookings");// phpcs:ignore
                    }                      
                    if(!empty($tbl_bookingpress_appointment_meta) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_appointment_meta)){
                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_appointment_meta");// phpcs:ignore
                    }    
                    if(!empty($tbl_bookingpress_entries) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_entries)){
                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_entries");// phpcs:ignore
                    }   
                    if(!empty($tbl_bookingpress_payment_logs) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_payment_logs)){
                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_payment_logs");// phpcs:ignore
                    }
                    if(!empty($tbl_bookingpress_entries_meta) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_entries_meta)){
                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_entries_meta");// phpcs:ignore
                    }
                    if(!empty($tbl_bookingpress_package_bookings_meta) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_package_bookings_meta)){
                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_package_bookings_meta");// phpcs:ignore
                    }
                    if(!empty($tbl_bookingpress_package_bookings) && $this->bookingpress_check_table_exists_func($tbl_bookingpress_package_bookings)){
                        $wpdb->query("TRUNCATE TABLE $tbl_bookingpress_package_bookings");// phpcs:ignore
                    }   
                    do_action('bookingpress_before_appointments_data_import');                   

                }

                $upload_dir = wp_upload_dir(); // Get uploads directory info
                $new_folder_path = $upload_dir['basedir'] . '/bookingpress_import_records';
                if (!file_exists($new_folder_path)) {
                    wp_mkdir_p($new_folder_path);
                }
                $file_path = $new_folder_path . '/bookingpress_import_data-'.$import_id.'.txt';

                $this->bookingpress_remove_import_export_directory( 'import' );

                file_put_contents($file_path, wp_json_encode($bookingpress_import_data,true));

                $response['variant']                = 'success';
                $response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                $response['msg']                    = esc_html__( 'Export process completed successfully.', 'bookingpress-appointment-booking' );
                $response['import_id']              = $import_id;
                $response['import_log_data']        = $this->get_import_log_data();           
                //$response['import_log_data']        = $this->get_export_log_data();

            }            
            echo wp_json_encode($response);
            exit();                           

        }

        /* Export Data Function Start here */

        /**
         * Function for stop export
         *
        */
        function bookingpress_export_data_stop_func(){
            @ini_set('memory_limit','512M');// phpcs:ignore
            global $wpdb,$tbl_bookingpress_export_data_log,$tbl_bookingpress_export_data_log_detail;

            $response                = array();
            $response['variant']     = 'error';
            $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
            $response['msg']         = esc_html__( 'Sorry, Something Wrong.', 'bookingpress-appointment-booking' );            
            $bpa_check_authorization = $this->bpa_check_authentication( 'import_export_settings', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $export_log_stop_id = (isset($_POST['export_log_stop_id']))?intval($_POST['export_log_stop_id']):0; // phpcs:ignore
            if($export_log_stop_id){
                $wpdb->delete( $tbl_bookingpress_export_data_log, array( 'export_id' => $export_log_stop_id ) );
                $wpdb->delete( $tbl_bookingpress_export_data_log_detail, array( 'export_id' => $export_log_stop_id ) );

                $response['variant']                = 'success';
                $response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                $response['msg']                    = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                $response['export_data_file']       = '';
                $response['is_complete']            = '';
                $response['export_detail_id']       = '';                                
                echo wp_json_encode( $response );
                die();                
            }
            
            echo wp_json_encode( $response );
            die();

        }

        function bookingpress_remove_import_export_directory( $mode = 'export' ){
            global $BookingPress;

            $upload_dir = wp_upload_dir();
            if( 'import' == $mode ){
                $folder_name = 'bookingpress_import_records';
            } else {
                $folder_name = 'bookingpress_export_records';
            }
            $folder_path = $upload_dir['basedir'] . '/'. $folder_name;            
            if( is_dir( $folder_path ) ){
                $exported_files = scandir( $folder_path );

                foreach( $exported_files as $file_name ){
                    if( '.' == $file_name || '..' == $file_name || 'index.php' == $file_name ){
                        continue;
                    }

                    $file_path = $folder_path .'/'.$file_name;

                    if( is_dir( $file_path ) ){
                        continue;
                    } else {   
                        wp_delete_file( $file_path );
                    }
                }
            }
        }
        
        /**
         * Function for export data continue process
         *
        */
        function bookingpress_export_data_continue_process_func($export_id = ""){
            @ini_set('memory_limit','512M');// phpcs:ignore
            global $wpdb,$tbl_bookingpress_export_data_log,$tbl_bookingpress_export_data_log_detail,$bookingpress_other_debug_log_id;

            $response                = array();
            $response['variant']     = 'error';
            $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
            $response['msg']         = esc_html__( 'Sorry, Something Wrong.', 'bookingpress-appointment-booking' );            
            $bpa_check_authorization = $this->bpa_check_authentication( 'import_export_settings', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingperss_continue_export = $wpdb->get_row($wpdb->prepare("SELECT export_id,export_data FROM {$tbl_bookingpress_export_data_log}  WHERE export_complete = %d Order by export_id DESC",0),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm            
            if(!empty($bookingperss_continue_export)){

                $export_id = (isset($bookingperss_continue_export['export_id']) && !empty($bookingperss_continue_export['export_id']))? $bookingperss_continue_export['export_id']:0;
                $export_data = (isset($bookingperss_continue_export['export_data']) && !empty($bookingperss_continue_export['export_data']))? $bookingperss_continue_export['export_data']:'';
                if($export_id){

                    $upload_dir = wp_upload_dir();
                    $new_folder_path = $upload_dir['basedir'] . '/bookingpress_export_records';                                        
                    $file_path = $new_folder_path . '/bookingpress_export_data-'.$export_id.'.txt';

                    $bookingperss_continue_export = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_export_data_log_detail}  WHERE export_detail_complete = %d AND export_id = %d Order by export_id ASC",0,$export_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log_detail is a table name. false alarm
                    if(!empty($bookingperss_continue_export)){
                        if(file_exists($file_path)){
                            
                            $bookingpress_active_plugin_module_list = $this->bookingpress_active_plugin_module_list();
                            $export_detail_id = (isset($bookingperss_continue_export['export_detail_id']))?$bookingperss_continue_export['export_detail_id']:'';
                            $export_detail_type = (isset($bookingperss_continue_export['export_detail_type']))?$bookingperss_continue_export['export_detail_type']:'';
                            $export_detail_total_record = (isset($bookingperss_continue_export['export_detail_total_record']))?$bookingperss_continue_export['export_detail_total_record']:'';
                            $export_detail_last_record = (isset($bookingperss_continue_export['export_detail_last_record']))?$bookingperss_continue_export['export_detail_last_record']:'';
                            $export_detail_complete = (isset($bookingperss_continue_export['export_detail_complete']))?$bookingperss_continue_export['export_detail_complete']:'';
                            $export_detail_option = (isset($bookingperss_continue_export['export_detail_option']))?$bookingperss_continue_export['export_detail_option']:'';

                            $xml = "";
                            $is_complete = 0;
                            $limit = 50;

                            if($export_detail_type == 'customers'){
                                $limit = 50;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"customers":[';
                                }                                
                                $type_data = $this->get_customer_export_data("",$export_detail_last_record,$limit,$export_detail_option);
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }
                            }                            
                            if($export_detail_type == 'settings'){
                                $limit = 500;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"settings":[';
                                }                                
                                $type_data = $this->get_settings_export_data("",$export_detail_last_record,$limit);
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }
                            }
                            if($export_detail_type == 'default_daysoff'){
                                global $tbl_bookingpress_default_daysoff;
                                $limit = 300;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"default_daysoff":[';
                                }                                
                                $type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_default_daysoff,'bookingpress_dayoff_id');
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }
                            }
                            
                            if($export_detail_type == 'default_workhours'){
                                $limit = 300;
                                global $tbl_bookingpress_default_workhours;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"default_workhours":[';
                                }                                
                                $type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_default_workhours,'bookingpress_workhours_id'); 
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }
                            }                                                                                                               
                            if($export_detail_type == 'customize'){
                                $limit = 600;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"customize":[';
                                }
                                $type_data = $this->get_customize_settings_export_data("",$export_detail_last_record,$limit);
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }                                
                            }

                            if($export_detail_type == 'category'){
                                $limit = 200;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"category":[';                                                                        
                                }
                                global $tbl_bookingpress_categories;                      
                                $type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_categories,'bookingpress_category_id');
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }                                          
                            }
                            if($export_detail_type == 'services'){
                                $limit = 200;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"services":[';                                                                        
                                }
                                global $tbl_bookingpress_services;                      
                                $type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_services,'bookingpress_service_id');
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }                                          
                            }

                            if($export_detail_type == 'bookingpress_servicesmeta'){
                                global $tbl_bookingpress_servicesmeta;
                                $limit = 200;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"bookingpress_servicesmeta":[';                                                                        
                                }                    
                                $type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_servicesmeta,'bookingpress_servicemeta_id');
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }                                          
                            }
                            if($export_detail_type == 'appointments'){                                
                                $limit = 80;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"appointments":[';                                                                        
                                }                    
                                $type_data = $this->get_appointments_export_data("",$export_detail_last_record,$limit);
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }
                            }

                            if($export_detail_type == 'bpa_wpoption_data'){
                                $limit = 20;
                                $bpa_wpoption_data = '';
                                if(!empty($export_data)){
                                    $export_data_arr = json_decode($export_data,true);
                                    if(is_array($export_data_arr)){
                                        $bpa_wpoption_data = $this->bookingpress_get_extra_wordpress_option_data($export_data_arr);    
                                    }                                    
                                }
                                if(!empty($bpa_wpoption_data)){
                                    $xml .= ',"bpa_wpoption_data":[';
                                    if(!empty($bpa_wpoption_data)){ 
                                        $i = 0;
                                        foreach($bpa_wpoption_data as $setting_data){                    
                                            $new_arr  = array();
                                            foreach($setting_data as $key=>$setting_val){                        
                                                    $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                                                    $new_arr[$key] = $setting_val;                        
                                            }
                                            if($i == 0){
                                                $xml .= wp_json_encode($new_arr).'';
                                            }else{
                                                $xml .= ','.wp_json_encode($new_arr).'';
                                            }
                                            $i++;                                        
                                        }                
                                    }
                                    $xml .= ']';
                                    $is_complete = 1;    
                                }else{
                                    $is_complete = 1;
                                    $xml = '';                                  
                                }
                            }
                            
                            if($export_detail_type == 'notifications'){
                                $limit = 80;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"notifications":[';                                                                        
                                }  
                                $type_data = $this->get_notifications_export_data("",$export_detail_last_record,$limit);
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }                                
                            }
                            if($export_detail_type == 'custom_fields'){
                                global $tbl_bookingpress_form_fields;                                
                                $limit = 100;
                                if($export_detail_last_record == 0){
                                    $xml .= ',"custom_fields":[';               
                                }  
                                $type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_form_fields,'bookingpress_form_field_id');
                                $xml .= $type_data['data'];
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }                                
                            }
                            
                            $bookingpress_export_detail_type_data = apply_filters( 'bookingpress_modified_export_data_result',array(),$export_detail_type,$export_detail_last_record,$limit,$export_id);                                                         
                            if(!empty($bookingpress_export_detail_type_data)){
                                if($export_detail_last_record == 0){
                                    $xml .= ',"'.$export_detail_type.'":[';                                                                      
                                }
                                $type_data = $bookingpress_export_detail_type_data;
                                $xml .= $type_data['data'];
                                if(isset($type_data['limit'])){
                                    $limit = $type_data['limit'];
                                }
                                if(empty($type_data['query_res'])){
                                    $is_complete = 1;
                                    $xml .= ']';
                                }
                            }

                            $file_contents = file_get_contents($file_path);
                            $new_content = $file_contents.$xml;                                                                
                            $result = file_put_contents($file_path, $new_content);
                            $export_detail_last_record = $export_detail_last_record + $limit;
                            if($export_detail_last_record > $export_detail_total_record){
                                $export_detail_last_record = $export_detail_total_record;
                            }
                            $export_detail_update = array('export_detail_last_record'=>$export_detail_last_record);
                            if($is_complete == 1){
                                $export_detail_update['export_detail_complete'] = 1;                                  
                            }
                            $wpdb->update($tbl_bookingpress_export_data_log_detail, $export_detail_update,array('export_detail_id'=>$export_detail_id));
                            $response['variant']                = 'success';
                            $response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                            $response['msg']                    = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                            $response['export_data_file']       = '';
                            $response['is_complete']            = '';
                            $response['export_log_data']        = $this->get_export_log_data();
                            $response['export_detail_id']       = $export_detail_id;                                
                            echo wp_json_encode( $response );
                            die();
                            

                        }
                    }else{

                        $file_contents = file_get_contents($file_path);
                        $new_content = '{'.$file_contents.'}';
                        
                        $result = file_put_contents($file_path, $new_content);

                        $wpdb->update($tbl_bookingpress_export_data_log, array('export_complete'=>1),array('export_id'=>$export_id));
                        $response['variant']                = 'success';
                        $response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                        $response['msg']                    = esc_html__( 'Export process successfully completed.', 'bookingpress-appointment-booking' );
                        $response['export_data_file']       = '';
                        $response['is_complete']            = $export_id;
                        $response['export_log_data']        = $this->get_export_log_data();
                        $response['export_detail_id']       = '';    
                                               
                        $upload_dir = wp_upload_dir();
                        $new_folder_path = $upload_dir['basedir'] . '/bookingpress_export_records';

                        $new_file_name = wp_generate_password( 10, false, false );

                        $new_file_path = $new_folder_path .'/'. $new_file_name .'.txt';
                        rename( $file_path, $new_file_path );

                        $export_id = base64_encode( $new_file_name );
                        $export_token = wp_generate_password( 6, false, false );

                        $this->bookingpress_update_transient( $export_token, $export_id, HOUR_IN_SECONDS );

                        $nonce = wp_create_nonce('bpa_download_export_file');
                        $export_file = site_url().'?bpa_page=bookingpress_download&action=download_bookingpress_export&export_token='.base64_encode( $export_token ).'&export_state='.$nonce;  

                        $response['last_export_file']       = $export_file;

                        echo wp_json_encode( $response );
                        die();                                               
                    }
                }
            }else{
                $response['variant']                = 'success';
                $response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                $response['msg']                    = esc_html__( 'Export process completed successfully.', 'bookingpress-appointment-booking' );
                
                $response['export_data_file']       = '';
                $response['is_complete']            = '';
                $response['export_detail_id']       = $export_detail_id;                                
                echo wp_json_encode( $response );
                die();
            }

            echo wp_json_encode( $response );
            die();
        }        
        
                


        /**
         * Appointment Export Data
         *
        */
        function get_appointments_export_data($xml,$export_detail_last_record,$limit){
            global $wpdb,$tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs,$tbl_bookingpress_entries,$tbl_bookingpress_entries_meta,$tbl_bookingpress_appointment_meta;
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} ORDER BY bookingpress_appointment_booking_id ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.            
            if(!empty($bookingpress_all_data)){ 
                $i = $export_detail_last_record;
                foreach($bookingpress_all_data as $setting_data){   
                    $new_arr  = array();
                    $all_appointment_key  = array();
                    foreach($setting_data as $key=>$setting_val){
                        $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                        $new_arr[$key] = $setting_val;
                        //$all_appointment_key[] = $key;
                    }
                    if($i == 0){
                        //$new_arr['appointment_keys'] = $all_appointment_key;
                    }
                    /* Add Appointment Meta Data */
                    $new_arr['meta_data'] = array();
                    if(!empty($tbl_bookingpress_appointment_meta)){
                        $bookingpress_order_id = (isset($setting_data['bookingpress_order_id']))?$setting_data['bookingpress_order_id']:0;
                        if($bookingpress_order_id == 0){
                            $bookingpress_appointment_meta_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_meta} Where bookingpress_appointment_id = %d",$setting_data['bookingpress_appointment_booking_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally.
                        }else{ 
                            $bookingpress_appointment_meta_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_meta} Where bookingpress_order_id = %d",$bookingpress_order_id),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally.
                        }                    
                        if(!empty($bookingpress_appointment_meta_data)){
                            foreach($bookingpress_appointment_meta_data as $app_m_key=>$app_m_val){
                                $bookingpress_appointment_meta_data[$app_m_key]['bookingpress_appointment_meta_value'] = (!empty($bookingpress_appointment_meta_data[$app_m_key]['bookingpress_appointment_meta_value']) && gettype($bookingpress_appointment_meta_data[$app_m_key]['bookingpress_appointment_meta_value']) === 'string')?addslashes($bookingpress_appointment_meta_data[$app_m_key]['bookingpress_appointment_meta_value']):$bookingpress_appointment_meta_data[$app_m_key]['bookingpress_appointment_meta_value'];
                                if(isset($bookingpress_appointment_meta_data[$app_m_key]['bookingpress_appointment_meta_created_date'])){
                                    unset($bookingpress_appointment_meta_data[$app_m_key]['bookingpress_appointment_meta_created_date']);
                                }

                            }
                            $new_arr['meta_data'] = $bookingpress_appointment_meta_data;
                        }    
                    }
                    /* Entry Table Data Export For Appointments */
                    $new_arr['entry_data'] = array();
                    if($setting_data['bookingpress_entry_id'] != 0){
                        $bookingpress_entry_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries} Where bookingpress_entry_id = %d",$setting_data['bookingpress_entry_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally.
                        if(!empty($bookingpress_entry_data)){
                            foreach($bookingpress_entry_data as $ent_key=>$ent_field_val){
                                $bookingpress_entry_data[$ent_key] = (!empty($bookingpress_entry_data[$ent_key]) && gettype($bookingpress_entry_data[$ent_key]) === 'string')?addslashes($bookingpress_entry_data[$ent_key]):$bookingpress_entry_data[$ent_key];                                
                            }
                            if(!empty($tbl_bookingpress_entries_meta)){
                                $bookingpress_entry_meta_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries_meta} Where bookingpress_entry_id = %d",$setting_data['bookingpress_entry_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally.
                                foreach($bookingpress_entry_meta_data as $ent_meta_key=>$ent_meta_field_val){
                                    $bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value'] = (!empty($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value']) && gettype($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value']) === 'string')?addslashes($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value']):$bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value'];    
                                    if(isset($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entrymeta_created_date'])){
                                        unset($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entrymeta_created_date']);
                                    }                            
                                }
                                $bookingpress_entry_data['meta_data'] = $bookingpress_entry_meta_data; 
                                $new_arr['entry_data'] = $bookingpress_entry_data;    
                            }
                        }
                    }
                    /* Payment Table Data Export For Appointments */
                    $new_arr['payment_data'] = array();
                    if($setting_data['bookingpress_payment_id'] != 0){
                        $bookingpress_payment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} Where bookingpress_payment_log_id = %d",$setting_data['bookingpress_payment_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally.
                        if(!empty($bookingpress_payment_data)){
                            foreach($bookingpress_payment_data as $ent_key=>$ent_field_val){
                                $bookingpress_payment_data[$ent_key] = (!empty($bookingpress_payment_data[$ent_key]) && gettype($bookingpress_payment_data[$ent_key]) === 'string')?addslashes($bookingpress_payment_data[$ent_key]):$bookingpress_payment_data[$ent_key];                                
                            }
                            $new_arr['payment_data'] = $bookingpress_payment_data;
                        }
                    }
                    if($i == 0){
                        $xml .= wp_json_encode($new_arr).'';
                    }else{
                        $xml .= ','.wp_json_encode($new_arr).'';
                    }
                    $i++;
                }                
            }    
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);            
        }

      

        


        /**
         * Common Export Data
         *
         * @return void
        */        
        function get_common_export_data($xml,$export_detail_last_record,$limit,$table_name,$field_order_by,$field_remove_key = array('none')){
            global $wpdb;
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY {$field_order_by} ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $table_name is table name defined globally.            
            if(!is_array($field_remove_key)){
                $field_remove_key = array('none');
            }
            if(!empty($bookingpress_all_data)){ 
                $i = $export_detail_last_record;
                foreach($bookingpress_all_data as $setting_data){                    
                    $new_arr  = array();
                    foreach($setting_data as $key=>$setting_val){
                        if(!in_array($key,$field_remove_key)){
                            $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                            $new_arr[$key] = $setting_val;
                        }
                    }
                    if(isset($new_arr['bookingpress_created_at'])){
                        unset($new_arr['bookingpress_created_at']);
                    }
                    if($i == 0){
                        $xml .= wp_json_encode($new_arr).'';
                    }else{
                        $xml .= ','.wp_json_encode($new_arr).'';
                    }
                    $i++;                                        
                }                
            }    
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);            
        }  

        /**
         * Function Notification Export Data 
         *
         * @return void
        */
        function get_notifications_export_data($xml,$export_detail_last_record,$limit){
            global $wpdb,$tbl_bookingpress_notifications;
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_notifications} ORDER BY bookingpress_notification_id ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.            
            $bookingpress_active_plugin_module_list = $this->bookingpress_active_plugin_module_list();
            if(!empty($bookingpress_all_data)){             
                $i = $export_detail_last_record;
                foreach($bookingpress_all_data as $setting_data){
                    $setting_data['bookingpress_notification_subject'] = (!empty($setting_data['bookingpress_notification_subject']) && gettype($setting_data['bookingpress_notification_subject']) === 'string')?addslashes($setting_data['bookingpress_notification_subject']):$setting_data['bookingpress_notification_subject'];
                    $setting_data['bookingpress_notification_message'] = (!empty($setting_data['bookingpress_notification_message']) && gettype($setting_data['bookingpress_notification_message']) === 'string')?addslashes($setting_data['bookingpress_notification_message']):$setting_data['bookingpress_notification_message'];                    
                    if(isset($setting_data['bookingpress_whatsapp_notification_message'])){
                        $setting_data['bookingpress_whatsapp_notification_message'] = (!empty($setting_data['bookingpress_whatsapp_notification_message']) && gettype($setting_data['bookingpress_whatsapp_notification_message']) === 'string')?addslashes($setting_data['bookingpress_whatsapp_notification_message']):$setting_data['bookingpress_whatsapp_notification_message'];                        
                    }
                    if(isset($setting_data['bookingpress_sms_notification_message'])){
                        $setting_data['bookingpress_sms_notification_message'] = (!empty($setting_data['bookingpress_sms_notification_message']) && gettype($setting_data['bookingpress_sms_notification_message']) === 'string')?addslashes($setting_data['bookingpress_sms_notification_message']):$setting_data['bookingpress_sms_notification_message'];
                    }
                    if($i == 0){
                        $xml .= wp_json_encode($setting_data).'';
                    }else{
                        $xml .= ','.wp_json_encode($setting_data).'';
                    } 
                    $i++;                   
                }                
            }
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);                
        }

        /**
         * Function for export customize settings
        */
        function get_customize_settings_export_data($xml,$export_detail_last_record,$limit){

            global $wpdb,$tbl_bookingpress_customize_settings;         
            $where_clause = $this->get_customize_where_exclude();
            $bookingpress_all_customize_settings = $wpdb->get_results( "SELECT bookingpress_setting_name,bookingpress_setting_value,bookingpress_setting_type FROM {$tbl_bookingpress_customize_settings} Where {$where_clause} ORDER BY bookingpress_setting_type ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.
            if(!empty($bookingpress_all_customize_settings)){
                $i = $export_detail_last_record;              
                foreach($bookingpress_all_customize_settings as $setting_data){
                    $setting_data['bookingpress_setting_value'] = (!empty($setting_data['bookingpress_setting_value']) && gettype($setting_data['bookingpress_setting_value']) === 'string')?addslashes($setting_data['bookingpress_setting_value']):$setting_data['bookingpress_setting_value'];                    
                    if($i == 0){
                        $xml .= wp_json_encode($setting_data).'';
                    }else{
                        $xml .= ','.wp_json_encode($setting_data).'';
                    }
                    $i++;                    
                }                
            }
            return array('data'=>$xml,'query_res'=>$bookingpress_all_customize_settings);         
        }

                        

        
        /**
         * Settings default day off
         *
         */
        function get_settings_default_daysoff_export_data($xml,$export_detail_last_record,$limit){
            global $wpdb,$tbl_bookingpress_default_daysoff;            
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_default_daysoff} ORDER BY setting_type ASC LIMIT  {$export_detail_last_record}, {$limit}" ,ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally.            
            if(!empty($bookingpress_all_data)){             
                foreach($bookingpress_all_data as $setting_data){                    
                    $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                    $new_arr[$key] = $setting_val;

                    $xml .= wp_json_encode($setting_data).',';
                }  

            }
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);            
        }

        
        /**
         * Function for export customer data
        */
        function get_customer_export_data($xml,$export_detail_last_record,$limit,$export_detail_option){
            global $wpdb,$tbl_bookingpress_customers,$tbl_bookingpress_customers_meta;
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_customers} ORDER BY bookingpress_customer_id ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $table_name is table name defined globally.            
            if(!empty($bookingpress_all_data)){ 
                $i = $export_detail_last_record;
                foreach($bookingpress_all_data as $setting_data){                    
                    $new_arr  = array();
                    foreach($setting_data as $key=>$setting_val){
                        $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                        $new_arr[$key] = $setting_val;
                    }
                    $bpa_customer_metadata = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_customersmeta_key, bookingpress_customersmeta_value FROM `{$tbl_bookingpress_customers_meta}` WHERE bookingpress_customer_id = %d", $setting_data['bookingpress_customer_id'] ) ,ARRAY_A);//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_customers_meta is a table name.
                    if(!empty($bpa_customer_metadata)){
                        foreach($bpa_customer_metadata as $key=>$bpa_customer_meta){
                            $bpa_customer_metadata[$key]['bookingpress_customersmeta_value'] = (!empty($bpa_customer_metadata[$key]['bookingpress_customersmeta_value']) && gettype($bpa_customer_metadata[$key]['bookingpress_customersmeta_value']) === 'string')?addslashes($bpa_customer_metadata[$key]['bookingpress_customersmeta_value']):$bpa_customer_metadata[$key]['bookingpress_customersmeta_value'];
                        }
                    }
                    $new_arr['customer_metadata'] = $bpa_customer_metadata;
                    $new_arr['wp_user'] = '';                    
                    if($export_detail_option == "customer_wp_users" && $setting_data['bookingpress_wpuser_id'] != 0){                        
                        $wp_user_detail = get_userdata($setting_data['bookingpress_wpuser_id']);
                        global $BookingPress;                        
                        if($wp_user_detail){
                            if(isset($wp_user_detail->caps)){
                                unset($wp_user_detail->caps);
                            }
                            if(isset($wp_user_detail->allcaps)){
                                unset($wp_user_detail->allcaps);
                            }                            
                            $new_arr['wp_user'] = $wp_user_detail;
                            $user_id = $setting_data['bookingpress_wpuser_id'];                        
                            $user_firstname = get_user_meta( $user_id, 'first_name', true );                        
                            $user_lastname = get_user_meta( $user_id, 'last_name', true );  
                            $new_arr['wp_user_meta'] = array('first_name'=>$user_firstname,'last_name'=>$user_lastname);
                        }
                    }
                    if($i == 0){
                        $xml .= ''.wp_json_encode($new_arr);
                    }else{
                        $xml .= ','.wp_json_encode($new_arr);
                    }                                        
                    $i++;
                }                
            }    
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);
        }

        /**
         * Settings Export
        */
        function get_settings_export_data($xml,$export_detail_last_record,$limit){            
            global $wpdb,$tbl_bookingpress_settings;     
            $where_clause = $this->get_settings_where_exclude();      
            $bookingpress_all_general_settings = $wpdb->get_results( "SELECT setting_name,setting_value,setting_type FROM {$tbl_bookingpress_settings} where {$where_clause} ORDER BY setting_type ASC LIMIT  {$export_detail_last_record}, {$limit}" ,ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.            
            if(!empty($bookingpress_all_general_settings)){ 
                $i = $export_detail_last_record;            
                foreach($bookingpress_all_general_settings as $setting_data){                    
                    $setting_data['setting_value'] = (!empty($setting_data['setting_value']) && gettype($setting_data['setting_value']) === 'string')?addslashes($setting_data['setting_value']):$setting_data['setting_value'];                    
                    if($i == 0){
                        $xml .= ''.wp_json_encode($setting_data);
                    }else{
                        $xml .= ','.wp_json_encode($setting_data);
                    } 
                    $i++;                   
                }                
            }
            return array('data'=>$xml,'query_res'=>$bookingpress_all_general_settings);
        }        

        function bookingpress_get_extra_wordpress_option_data($export_final_list = array()){
            //$bookingpress_active_plugin_module_list = $this->bookingpress_active_plugin_module_list();
            $all_option_data = array();
            $all_option_data = apply_filters( 'bookingpress_add_wordpress_default_option_data', $all_option_data,$export_final_list );
            return $all_option_data;
        }

        /**
         * Function for export data process
         *
         * @return void
        */
        function bookingpress_export_data_process_func(){
            global $BookingPress,$tbl_bookingpress_export_data_log,$tbl_bookingpress_export_data_log_detail,$wpdb,$bookingpress_other_debug_log_id;
            @ini_set('memory_limit','512M');// phpcs:ignore

            $bpa_check_authorization = $this->bpa_check_authentication( 'import_export_settings', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingpress_export_list_data = array();
            if(!empty($_POST['bookingpress_export_list_data']) && isset($_POST['bookingpress_export_list_data'])){ // phpcs:ignore
                $_POST['bookingpress_export_list_data'] = json_decode( stripslashes_deep( $_POST['bookingpress_export_list_data'] ), true ); //phpcs:ignore
                $bookingpress_export_list_data = !empty( $_POST['bookingpress_export_list_data'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['bookingpress_export_list_data']) : array(); //phpcs:ignore  
            }            
            if(!empty($bookingpress_export_list_data)){

                $upload_dir = wp_upload_dir(); // Get uploads directory info
                $new_folder_path = $upload_dir['basedir'] . '/bookingpress_export_records';
                $this->bookingpress_remove_import_export_directory();

                $bookingpress_active_plugin_module_list = $this->bookingpress_active_plugin_module_list();
                $bookingperss_continue_export = $wpdb->get_row($wpdb->prepare("SELECT export_id FROM {$tbl_bookingpress_export_data_log}  WHERE export_complete = %d",0),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
                if(!empty($bookingperss_continue_export)){
                    $wpdb->delete( $tbl_bookingpress_export_data_log, array( 'export_id' => $bookingperss_continue_export['export_id'] ) );
                    $wpdb->delete( $tbl_bookingpress_export_data_log_detail, array( 'export_id' => $bookingperss_continue_export['export_id'] ) );
                    $bookingperss_continue_export = '';
                }
                if(empty($bookingperss_continue_export)){

                    $export_list_empty = true;
                    $export_final_list = array();
                    foreach($bookingpress_export_list_data as $key=>$val){
                        if($val == 1){
                            $export_list_empty = false;
                            $export_final_list[] = $key;
                        }
                    }
                    if($export_list_empty || empty($export_final_list)){
                        $response['variant']     = 'error';
                        $response['title']       = esc_html__( 'Error', 'bookingpress-appointment-booking' );
                        $response['msg']         = esc_html__( 'Please select export list items.', 'bookingpress-appointment-booking' );
                        $response['coupon_data'] = array();              
                        echo wp_json_encode( $response );
                        die();
                    }
                    $bookingpress_db_fields = array(
                        'export_data' => wp_json_encode($export_final_list),                        
                        'export_complete' => 0,                        
                    );
                    $wpdb->insert($tbl_bookingpress_export_data_log, $bookingpress_db_fields);  
                    $export_id = $wpdb->insert_id;                    
                    $customer_wp_users = '';
                    if(!empty($export_final_list) && in_array('customer_wp_users',$export_final_list)){
                        $customer_wp_users = 'customer_wp_users';
                    }

                    $bpa_wpoption_data = $this->bookingpress_get_extra_wordpress_option_data($export_final_list);
                    
                   

                    if(!empty($bpa_wpoption_data) && is_array($bpa_wpoption_data)){
                        $total_records = count($bpa_wpoption_data);
                        
                            $new_export_key = 'bpa_wpoption_data';                        
                            $bookingpress_db_fields = array(
                                'export_id' => $export_id, 
                                'export_detail_type' => $new_export_key,
                                'export_detail_total_record' => $total_records,
                                'export_detail_last_record' => 0,
                                'export_detail_complete' => 0,
                                'export_detail_record_hide' => 1,                                                  
                            );
                            $wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 
                                                
                    }
                    foreach($export_final_list as $export_detail){
                        if($export_detail == 'customer_wp_users'){
                            continue;
                        }                        
                        $total_records = $this->export_item_total_records($export_detail,$export_id);

                        if($total_records > 0){
                            $bookingpress_db_fields = array(
                                'export_id' => $export_id, 
                                'export_detail_type' => $export_detail,
                                'export_detail_total_record' => $total_records,
                                'export_detail_last_record' => 0,
                                'export_detail_complete' => 0,                                                    
                            );
                            if($export_detail == 'customers'){
                                $bookingpress_db_fields['export_detail_option'] = $customer_wp_users;
                            }
                            $wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
                        }

                        if($export_detail == 'services'){
                            $new_export_key = 'category';
                            $total_records = $this->export_item_total_records($new_export_key);
                            if($total_records > 0){
                                $bookingpress_db_fields = array(
                                    'export_id' => $export_id, 
                                    'export_detail_type' => $new_export_key,
                                    'export_detail_total_record' => $total_records,
                                    'export_detail_last_record' => 0,
                                    'export_detail_complete' => 0,
                                    'export_detail_record_hide' => 1,                                                    
                                );
                                $wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
                            }
                            
                            $services_total_records = $this->export_item_total_records('services');
                            if($services_total_records > 0){
                                                                
                                $new_export_key = 'bookingpress_servicesmeta';
                                $total_records = $this->export_item_total_records($new_export_key);
                                $bookingpress_db_fields = array(
                                    'export_id' => $export_id, 
                                    'export_detail_type' => $new_export_key,
                                    'export_detail_total_record' => $total_records,
                                    'export_detail_last_record' => 0,
                                    'export_detail_complete' => 0,
                                    'export_detail_record_hide' => 1,                                                    
                                );
                                $wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);
    
                            }                        

                            $new_export_key = 'custom_fields';
                            $total_records = $this->export_item_total_records($new_export_key);                            
                            $bookingpress_db_fields = array(
                                'export_id' => $export_id, 
                                'export_detail_type' => $new_export_key,
                                'export_detail_total_record' => $total_records,
                                'export_detail_last_record' => 0,
                                'export_detail_complete' => 0,
                                'export_detail_record_hide' => 0, 
                                'export_detail_record_hide' => 1,                                                    
                            );
                            $wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);                            

                        }

                        if($export_detail == 'settings'){

                            $new_export_key = 'customize';
                            $total_records = $this->export_item_total_records($new_export_key);                            
                            $bookingpress_db_fields = array(
                                'export_id' => $export_id, 
                                'export_detail_type' => $new_export_key,
                                'export_detail_total_record' => $total_records,
                                'export_detail_last_record' => 0,
                                'export_detail_complete' => 0,                                                    
                            );
                            $wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);

                            $new_export_key = 'default_daysoff';
                            $total_records = $this->export_item_total_records($new_export_key);
                            if($total_records > 0){
                                $bookingpress_db_fields = array(
                                    'export_id' => $export_id, 
                                    'export_detail_type' => $new_export_key,
                                    'export_detail_total_record' => $total_records,
                                    'export_detail_last_record' => 0,
                                    'export_detail_complete' => 0,
                                    'export_detail_record_hide' => 1,                                                     
                                );
                                $wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
                            }

                            $new_export_key = 'default_workhours';
                            $total_records = $this->export_item_total_records($new_export_key);                            
                            $bookingpress_db_fields = array(
                                'export_id' => $export_id, 
                                'export_detail_type' => $new_export_key,
                                'export_detail_total_record' => $total_records,
                                'export_detail_last_record' => 0,
                                'export_detail_complete' => 0,
                                'export_detail_record_hide' => 1,                                                     
                            );
                            $wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

                            

                        }                         
                        do_action('bookingpress_add_extra_export_data',$export_detail,$export_id);

                    }                                        
                    $upload_dir = wp_upload_dir(); // Get uploads directory info
                    $new_folder_path = $upload_dir['basedir'] . '/bookingpress_export_records';
                    
                    $bookingpress_export_site_key = get_option('bookingpress_export_site_key');
                    if(empty($bookingpress_export_site_key)){
                        $bookingpress_export_site_key = wp_rand(0,5).'_'.time();
                        update_option('bookingpress_export_site_key',$bookingpress_export_site_key);
                    }
                    $bookingpress_export_key = wp_rand(0,5).'_'.time();                                 
                    $file_path = $new_folder_path . '/bookingpress_export_data-'.$export_id.'.txt';

                    $all_active_addons = $this->bookingpress_get_active_addon_module_list();
                    if(empty($all_active_addons)){
                        $all_active_addons = array();
                    }
                    $xml = '"site_url":"'.site_url().'",';
                    $xml .= '"export_key":"'.$bookingpress_export_key.'",';
                    $xml .= '"export_site_key":"'.$bookingpress_export_site_key.'",';
                    $xml .= '"all_active_addon":'.wp_json_encode($all_active_addons).'';                    
                    
                    file_put_contents($file_path, $xml);

                    $response['variant']                = 'success';
                    $response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                    $response['msg']                    = esc_html__( 'Export process successfully completed', 'bookingpress-appointment-booking' );
                    $response['export_data_file']       = '';
                    $response['export_id']              = $export_id;
                    $response['export_log_data']        = $this->get_export_log_data();
                  

                }

                echo wp_json_encode($response);
                exit();               

            }
            

        }

        /* Export Data function oer here */


        /**
         * Function for add new setting tab view file
         *
         * @param  mixed $bookingpress_file_url
         * @return void
        */
        public function bookingpress_general_settings_add_tab_filter_func($bookingpress_file_url){
			$bookingpress_file_url[] = BOOKINGPRESS_VIEWS_DIR . '/importexport/import_export_tab.php';
			return $bookingpress_file_url;
        }

        public function bookingpress_add_setting_dynamic_data_fields_func($bookingpress_dynamic_setting_data_fields){

            global $BookingPress,$wpdb;
            $bookingpress_active_plugin_module_list = $this->bookingpress_active_plugin_module_list();

            $bookingpress_export_list = [];
            $total_records = 0;
            $setting_total_records = $this->export_item_total_records("settings") + $total_records;

            $bookingpress_export_list['settings'] = array('name'=>esc_html__('Settings','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>0,'total_record'=>$setting_total_records);

            $total_records = $this->export_item_total_records("customers");
            $bookingpress_export_list['customers'] = array('name'=>esc_html__('Customers','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>0,'total_record'=>$total_records);
            $bookingpress_export_list['customers']['child']['customer_wp_users'] = array('name'=>esc_html__('WordPress Users','bookingpress-appointment-booking'),'related'=>'','child'=>array(),'required_parent'=>0);

            $total_records = $this->export_item_total_records("appointments");
            $bookingpress_export_list['appointments'] = array('name'=>esc_html__('Appointments','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>1,'total_record'=>$total_records);


            $total_records = $this->export_item_total_records("services");
            $bookingpress_export_list['services'] = array('name'=>esc_html__('Services','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>0,'total_record'=>$total_records);

            $total_records = $this->export_item_total_records("notifications");
            $bookingpress_export_list['notifications'] = array('name'=>esc_html__('Notifications','bookingpress-appointment-booking'),'related'=>array('services'),'child'=>array(),'required_parent'=>0,'total_record'=>$total_records);
            $bookingpress_export_list['appointments']['related'][] = 'notifications';
            $bookingpress_export_list['appointments']['related'][] = 'services';

            $bookingpress_export_list['appointments']['related'][] = 'customers';
            $bookingpress_export_list['appointments']['related'][] = 'customer_wp_users';
            

            $current_url = get_site_url() . sanitize_text_field($_SERVER['REQUEST_URI']); // phpcs:ignore

            $bookingpress_export_list = apply_filters( 'bookingpress_modified_export_list', $bookingpress_export_list );
            $bookingpress_export_list_data = array();
            foreach($bookingpress_export_list as $key=>$val){
                $bookingpress_export_list_data[$key] = false;
                if(isset($val['child']) && !empty($val['child'])){
                    foreach($val['child'] as $child_key=>$child_fields){
                        $bookingpress_export_list_data[$child_key] = false;
                    }
                }
            }
            $migration_tool_form = array('export_list'=>$bookingpress_export_list);
            $migration_tool_form['bookingpress_export_list_data'] = $bookingpress_export_list_data;
            $bookingpress_dynamic_setting_data_fields['migration_tool_form'] = $migration_tool_form;            
            $bookingpress_dynamic_setting_data_fields['is_display_export_loader'] = 0;
            
            $bookingpress_dynamic_setting_data_fields['export_log_data'] = '';
            $bookingpress_dynamic_setting_data_fields['continue_export_id'] = ''; 
            
            $bookingpress_dynamic_setting_data_fields['export_complete_msg'] = '';

            $bookingpress_dynamic_setting_data_fields['export_all_record'] = false;
            $bookingpress_dynamic_setting_data_fields['export_log_stop_id'] = '';
            $bookingpress_dynamic_setting_data_fields['export_last_download_file'] = '';
                       
            $bookingpress_dynamic_setting_data_fields['last_export_file'] = '';
            
            $bookingpress_dynamic_setting_data_fields['migration_tool_form']['import_file'] = [];
            $bookingpress_dynamic_setting_data_fields['migration_tool_form']['import_file_final'] = '';
            $bookingpress_dynamic_setting_data_fields['migration_tool_form']['import_data'] = '';
            $bookingpress_dynamic_setting_data_fields['migration_tool_form']['confirm_import_data'] = '';
            $bookingpress_dynamic_setting_data_fields['is_display_import_loader'] = 0;
            $bookingpress_dynamic_setting_data_fields['import_log_data'] = ''; 
            $bookingpress_dynamic_setting_data_fields['continue_import_id'] = '';

            return $bookingpress_dynamic_setting_data_fields;
        }        


        function bpa_add_extra_tab_outside_func_arr(){ ?>

            if( bpa_get_page == 'bookingpress_settings'){ 
                if( selected_tab_name == 'migration_tool_settings'){
                    vm.openNeedHelper("list_import_export_settings", "import_export_settings", "<?php echo esc_html__('Import/Export','bookingpress-appointment-booking'); ?>");
                    vm.bpa_fab_floating_btn = 0; 
                } else if(null == selected_tab_name && 'migration_tool_settings' == bpa_get_setting_page){                    
                    vm.openNeedHelper("list_import_export_settings", "import_export_settings", "<?php echo esc_html__('Import/Export','bookingpress-appointment-booking'); ?>");
                    vm.bpa_fab_floating_btn = 0; 
                }
            }
        <?php 
        }

        function bookingpress_modify_get_settings_response_data_func($response,$bookingpress_posted_data){
            if( 'migration_tool_settings' == $bookingpress_posted_data['setting_type']){
                $response['data'] = '';
            }
            return $response;
        }

        /**
         * Function for 
        */
        function bookingpress_modify_readmore_link_func(){
        ?>
            var selected_tab = sessionStorage.getItem("current_tabname");
            if(selected_tab == "migration_tool_settings"){
                read_more_link = "https://www.bookingpressplugin.com/documents/import-export-tool/";
            }
        <?php
        }

        /**
         * Dynamic tab default data load
         *
         * @return void
         */
        function bookingpress_settings_add_dynamic_on_load_method_func(){
        ?>
            else if(selected_tab_name == 'migration_tool_settings'){                
                vm.getSettingsData('migration_tool_settings', 'migration_tool_form');
            }            
        <?php 
        }

        /**
         * Function for check migration tool addon requirement
         *
         * @return void
        */
        function bookingpress_migration_tool_addon_requirement(){
            global $bookingpress_pro_version;
            $migration_tool_working = true;
            $bookingpress_version = get_option('bookingpress_version', true);
            if(is_plugin_active('bookingpress-appointment-booking/bookingpress-appointment-booking.php') && version_compare($bookingpress_version, '1.1', '>=')) {
                $migration_tool_working = true;
            }else{
                $migration_tool_working = false;
            }
            return $migration_tool_working;            
        } 

        /**
         * bookingpress_modify_download_debug_log_query_api_func
         *
         * @param  mixed $bookingpress_debug_log_query
         * @param  mixed $bookingpress_view_log_selector
         * @param  mixed $bookingpress_posted_data
         * @return void
        */
        function bookingpress_modify_download_debug_log_data_func( $bookingpress_debug_log_data, $bookingpress_view_log_selector, $bookingpress_posted_data){
            global $wpdb, $BookingPress, $tbl_bookingpress_other_debug_logs,$tbl_bookingpress_export_data_log_detail,$tbl_bookingpress_export_data_log,$tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log;

			$bookingpress_debug_payment_log_where_cond = '';
			$bookingpress_selected_download_duration   = ! empty( $bookingpress_posted_data['bookingpress_selected_download_duration'] ) ? sanitize_text_field( $bookingpress_posted_data['bookingpress_selected_download_duration'] ) : 'all';
            
            if ( $bookingpress_view_log_selector == 'migration_tool_import_debug_logs' ) {

                if ( ! empty( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'] ) && $bookingpress_selected_download_duration == 'custom' ) {
                    $bookingpress_start_date                   = date( 'Y-m-d 00:00:00', strtotime( sanitize_text_field( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'][0] ) ) );
                    $bookingpress_end_date                     = date( 'Y-m-d 23:59:59', strtotime( sanitize_text_field( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'][1] ) ) );
    
                    if(!empty($bookingpress_view_log_selector) && ($bookingpress_view_log_selector == 'migration_tool_debug_logs')) {
                        $bookingpress_debug_payment_log_where_cond = " AND (updated_at >= '" . $bookingpress_start_date . "' AND updated_at <= '" . $bookingpress_end_date . "')";
                    }
                }
                elseif ( ! empty( $bookingpress_selected_download_duration ) && $bookingpress_selected_download_duration != 'custom' && $bookingpress_selected_download_duration != 'all') {
                    if(!empty($bookingpress_view_log_selector) && ($bookingpress_view_log_selector == 'migration_tool_debug_logs')) {
                        $bookingpress_last_selected_days           = date( 'Y-m-d', strtotime( '-' . $bookingpress_selected_download_duration . ' days' ) );
                        $bookingpress_debug_payment_log_where_cond = " AND (updated_at >= '" . $bookingpress_last_selected_days . "')";
                    }
                }

                $bookingpress_export_logs = $wpdb->get_results("SELECT * FROM ".$tbl_bookingpress_import_data_log." WHERE 1 = 1 ". $bookingpress_debug_payment_log_where_cond ."  Order BY import_id DESC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

                $bookingpress_debug_log_data = array();
				$bookingpress_date_format    = get_option( 'date_format' );
                foreach ( $bookingpress_export_logs as $bookingpress_debug_log_key => $bookingpress_debug_log_val ) {	

                    $bookingpress_export_logs_detail = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tbl_bookingpress_import_detail_log." Where import_id = %d ",$bookingpress_debug_log_val['import_id']), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm
                    $bookingpress_debug_log_val['import_in_detail'] = $bookingpress_export_logs_detail;                    
                    $bookingpress_debug_log_data[] = array(
						'payment_debug_log_id'         => $bookingpress_debug_log_val['export_id'],
						'payment_debug_log_name'       => 'import_data_log',
						'payment_debug_log_data'       => wp_json_encode($bookingpress_debug_log_val),
						'payment_debug_log_added_date' => date( $bookingpress_date_format, strtotime( $bookingpress_debug_log_val['updated_at'] ) ),
					);

				}

                $bookingpress_debug_log_data = $bookingpress_debug_log_data;
			}


            if ( $bookingpress_view_log_selector == 'migration_tool_debug_logs' ) {
                if ( ! empty( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'] ) && $bookingpress_selected_download_duration == 'custom' ) {
                    $bookingpress_start_date                   = date( 'Y-m-d 00:00:00', strtotime( sanitize_text_field( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'][0] ) ) );
                    $bookingpress_end_date                     = date( 'Y-m-d 23:59:59', strtotime( sanitize_text_field( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'][1] ) ) );
    
                    if(!empty($bookingpress_view_log_selector) && ($bookingpress_view_log_selector == 'migration_tool_debug_logs')) {
                        $bookingpress_debug_payment_log_where_cond = " AND (updated_at >= '" . $bookingpress_start_date . "' AND updated_at <= '" . $bookingpress_end_date . "')";
                    }
                }
                elseif ( ! empty( $bookingpress_selected_download_duration ) && $bookingpress_selected_download_duration != 'custom' && $bookingpress_selected_download_duration != 'all') {
                    if(!empty($bookingpress_view_log_selector) && ($bookingpress_view_log_selector == 'migration_tool_debug_logs')) {
                        $bookingpress_last_selected_days           = date( 'Y-m-d', strtotime( '-' . $bookingpress_selected_download_duration . ' days' ) );
                        $bookingpress_debug_payment_log_where_cond = " AND (updated_at >= '" . $bookingpress_last_selected_days . "')";
                    }
                }

                $bookingpress_export_logs = $wpdb->get_results("SELECT * FROM ".$tbl_bookingpress_export_data_log." WHERE 1 = 1 ". $bookingpress_debug_payment_log_where_cond ."  Order BY export_id DESC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm

                $bookingpress_debug_log_data = array();
				$bookingpress_date_format    = get_option( 'date_format' );
                foreach ( $bookingpress_export_logs as $bookingpress_debug_log_key => $bookingpress_debug_log_val ) {	

                    $bookingpress_export_logs_detail = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tbl_bookingpress_export_data_log_detail." Where export_id = %d ",$bookingpress_debug_log_val['export_id']), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_export_data_log_detail is a table name. false alarm
                    $bookingpress_debug_log_val['export_in_detail'] = $bookingpress_export_logs_detail;                    
                    $bookingpress_debug_log_data[] = array(
						'payment_debug_log_id'         => $bookingpress_debug_log_val['export_id'],
						'payment_debug_log_name'       => 'export_data_log',
						'payment_debug_log_data'       => wp_json_encode(stripslashes_deep($bookingpress_debug_log_val)),
						'payment_debug_log_added_date' => date( $bookingpress_date_format, strtotime( $bookingpress_debug_log_val['updated_at'] ) ),
					);

				}

                $bookingpress_debug_log_data = $bookingpress_debug_log_data;
			}
            return $bookingpress_debug_log_data;
        }

        /**
         * bookingpress_clear_debug_payment_log_api_func
         *
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_clear_debug_payment_log_api_func($posted_data){
            global $wpdb, $BookingPress, $tbl_bookingpress_export_data_log,$tbl_bookingpress_export_data_log_detail,$tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log;
            if ( ! empty( $posted_data ) ) {
                $bookingpress_view_log_selector = ! empty( $posted_data['bookingpress_debug_log_selector'] ) ? sanitize_text_field( $posted_data['bookingpress_debug_log_selector'] ) : '';
                if ( $bookingpress_view_log_selector == 'migration_tool_debug_logs' ) {
                    $wpdb->delete( $tbl_bookingpress_export_data_log, array( 'export_complete' => 1 ) );
                }
                if ( $bookingpress_view_log_selector == 'migration_tool_import_debug_logs' ) {
                    $wpdb->delete( $tbl_bookingpress_import_data_log, array( 'import_complete' => 1 ) );                 
                }                
            }
        }

        /**
         * bookingpress_modify_debug_log_data_outside_api_func
         *
         * @param  mixed $debug_log_data
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_modify_debug_log_data_outside_api_func($debug_log_data, $posted_data){

            global $wpdb, $tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log, $tbl_bookingpress_export_data_log,$tbl_bookingpress_export_data_log_detail,$tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log;

            $bookingpress_debug_log_selector = !empty($posted_data['bookingpress_debug_log_selector']) ? sanitize_text_field($posted_data['bookingpress_debug_log_selector']) : '';
            $perpage     = isset($_POST['perpage']) ? intval($_POST['perpage']) : 20; //phpcs:ignore
            $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1; //phpcs:ignore
            $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

            if ( ! empty( $bookingpress_debug_log_selector ) && $bookingpress_debug_log_selector == 'migration_tool_import_debug_logs' ) {
                
                $bookingpress_export_logs_total_record = $wpdb->get_results("SELECT * FROM ".$tbl_bookingpress_import_data_log." Order BY import_id DESC", ARRAY_A );  // phpcs:ignore

                $bookingpress_export_logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tbl_bookingpress_import_data_log." Order BY import_id DESC LIMIT %d, %d",$offset , $perpage), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

                $bookingpress_debug_log_data = array();
				$bookingpress_date_format    = get_option( 'date_format' );
                foreach ( $bookingpress_export_logs as $bookingpress_debug_log_key => $bookingpress_debug_log_val ) {	

                    $bookingpress_export_logs_detail = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tbl_bookingpress_import_detail_log." Where import_id = %d ",$bookingpress_debug_log_val['import_id']), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm
                    $bookingpress_debug_log_val['import_in_detail'] = $bookingpress_export_logs_detail;                    
                    $bookingpress_debug_log_data[] = array(
						'payment_debug_log_id'         => $bookingpress_debug_log_val['import_id'],
						'payment_debug_log_name'       => 'import_data_log',
						'payment_debug_log_data'       => wp_json_encode(stripslashes_deep($bookingpress_debug_log_val)),
						'payment_debug_log_added_date' => date( $bookingpress_date_format, strtotime( $bookingpress_debug_log_val['updated_at'] ) ),
					);

				}
				$debug_log_data['items'] = $bookingpress_debug_log_data;
				$debug_log_data['total'] = count($bookingpress_export_logs_total_record);

			}

            if ( ! empty( $bookingpress_debug_log_selector ) && $bookingpress_debug_log_selector == 'migration_tool_debug_logs' ) {
                                
                $bookingpress_export_logs_total_record = $wpdb->get_results("SELECT * FROM ".$tbl_bookingpress_export_data_log." Order BY export_id DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. 

                $bookingpress_export_logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tbl_bookingpress_export_data_log." Order BY export_id DESC LIMIT %d, %d",$offset , $perpage), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm

                $bookingpress_debug_log_data = array();
				$bookingpress_date_format    = get_option( 'date_format' );
                foreach ( $bookingpress_export_logs as $bookingpress_debug_log_key => $bookingpress_debug_log_val ) {	
                    $bookingpress_export_logs_detail = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tbl_bookingpress_export_data_log_detail." Where export_id = %d ",$bookingpress_debug_log_val['export_id']), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_export_data_log_detail is a table name. false alarm
                    $bookingpress_debug_log_val['export_in_detail'] = $bookingpress_export_logs_detail;                    
                    $bookingpress_debug_log_data[] = array(
						'payment_debug_log_id'         => $bookingpress_debug_log_val['export_id'],
						'payment_debug_log_name'       => 'export_data_log',
						'payment_debug_log_data'       => wp_json_encode(stripslashes_deep($bookingpress_debug_log_val)),
						'payment_debug_log_added_date' => date( $bookingpress_date_format, strtotime( $bookingpress_debug_log_val['updated_at'] ) ),
					);

				}
				$debug_log_data['items'] = $bookingpress_debug_log_data;
				$debug_log_data['total'] = count($bookingpress_export_logs_total_record);
			}
            return $debug_log_data;
        }

        /**
         * API Debug Log
         *
         * @return void
         */
        function bookingpress_add_debug_log_outside_api_func(){
            global $bookingpress_common_date_format;
        ?>
            <div class="bpa-gs__cb--item">
                <div class="bpa-gs__cb--item-heading">
                    <h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?></h4>
                </div>
                <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-row type="flex" class="bpa-debug-item__body">
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                <h4> <?php esc_html_e( 'Export Logs', 'bookingpress-appointment-booking' ); ?></h4>
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                <el-form-item>
                                    <el-switch class="bpa-swtich-control" v-model="debug_log_setting_form.migration_tool_debug_logs"></el-switch>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.migration_tool_debug_logs == true">
                                    <div class="bpa-di__btn-item">
                                        <el-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('migration_tool_debug_logs', '', '<?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?>')" ><?php esc_html_e( 'View log', 'bookingpress-appointment-booking' ); ?></el-button>
                                    </div>
                                    <div class="bpa-di__btn-item">
                                        <el-popover placement="bottom" width="450" trigger="click">
                                            <div class="bpa-dialog-download"> 
                                                <el-row type="flex">
                                                    <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">			
                                                        <label for="start_time" class="el-form-item__label">
                                                            <span class="bpa-form-label"><?php esc_html_e( 'Select log duration to download', 'bookingpress-appointment-booking' ); ?></span>
                                                        </label>			
                                                    </el-col>			
                                                    <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">											
                                                        <el-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon">	
                                                            <el-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></el-option>
                                                        </el-select>										
                                                    </el-col>		
                                                </el-row>										
                                                <el-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >											
                                                        <el-date-picker popper-class="bpa-el-select--is-with-modal" class="bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" :clearable="false" value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"> </el-date-picker>
                                                    </el-col>
                                                </el-row>
                                                <el-row>													
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >										
                                                        <el-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('migration_tool_debug_logs', select_download_log, download_log_daterange)" :disabled="is_disabled" >
                                                            <span class="bpa-btn__label"><?php esc_html_e( 'Download', 'bookingpress-appointment-booking' ); ?></span>
                                                            <div class="bpa-btn--loader__circles">
                                                                <div></div>
                                                                <div></div>
                                                                <div></div>
                                                            </div>
                                                        </el-button>	
                                                    </el-col>
                                                </el-row>	
                                            </div>
                                            <el-button class="bpa-btn bpa-btn__small" slot="reference" ><?php esc_html_e( 'Download Log', 'bookingpress-appointment-booking' ); ?></el-button>
                                        </el-popover>	
                                    </div>
                                    <div class="bpa-di__btn-item">
                                        <el-popconfirm 
                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                            icon="false" 
                                            title="<?php esc_html_e( 'Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking' ); ?>"
                                            @confirm="bookingpess_clear_bebug_log('migration_tool_debug_logs')"
                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                            cancel-button-type="bpa-btn bpa-btn__small" >
                                            <el-button class="bpa-btn bpa-btn__small" slot="reference"><?php esc_html_e( 'Clear Log', 'bookingpress-appointment-booking' ); ?></el-button>
                                        </el-popconfirm>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>
                <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-row type="flex" class="bpa-debug-item__body">
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                    <h4> <?php esc_html_e( 'Import Logs', 'bookingpress-appointment-booking' ); ?></h4>
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                    <el-form-item>
                                        <el-switch class="bpa-swtich-control" v-model="debug_log_setting_form.migration_tool_import_debug_logs"></el-switch>
                                    </el-form-item>
                                </el-col>
                        </el-row>
                        <el-row>
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.migration_tool_import_debug_logs == true">
                                    <div class="bpa-di__btn-item">
                                        <el-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('migration_tool_import_debug_logs', '', '<?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?>')" ><?php esc_html_e( 'View log', 'bookingpress-appointment-booking' ); ?></el-button>
                                    </div>
                                    <div class="bpa-di__btn-item">
                                        <el-popover placement="bottom" width="450" trigger="click" >
                                            <div class="bpa-dialog-download"> 
                                                <el-row type="flex">
                                                    <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">			
                                                        <label for="start_time" class="el-form-item__label">
                                                            <span class="bpa-form-label"><?php esc_html_e( 'Select log duration to download', 'bookingpress-appointment-booking' ); ?></span>
                                                        </label>			
                                                    </el-col>			
                                                    <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">											
                                                        <el-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon">	
                                                            <el-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></el-option>
                                                        </el-select>										
                                                    </el-col>		
                                                </el-row>										
                                                <el-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >											
                                                        <el-date-picker popper-class="bpa-el-select--is-with-modal" class="bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" :clearable="false" value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"> </el-date-picker>
                                                    </el-col>
                                                </el-row>
                                                <el-row>													
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >										
                                                        <el-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('migration_tool_import_debug_logs', select_download_log, download_log_daterange)" :disabled="is_disabled" >
                                                            <span class="bpa-btn__label"><?php esc_html_e( 'Download', 'bookingpress-appointment-booking' ); ?></span>
                                                            <div class="bpa-btn--loader__circles">
                                                                <div></div>
                                                                <div></div>
                                                                <div></div>
                                                            </div>
                                                        </el-button>	
                                                    </el-col>
                                                </el-row>	
                                            </div>
                                            <el-button class="bpa-btn bpa-btn__small" slot="reference" ><?php esc_html_e( 'Download Log', 'bookingpress-appointment-booking' ); ?></el-button>
                                        </el-popover>	
                                    </div>
                                    <div class="bpa-di__btn-item">
                                        <el-popconfirm 
                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                            icon="false" 
                                            title="<?php esc_html_e( 'Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking' ); ?>"
                                            @confirm="bookingpess_clear_bebug_log('migration_tool_import_debug_logs')"
                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                            cancel-button-type="bpa-btn bpa-btn__small" >
                                            <el-button class="bpa-btn bpa-btn__small" slot="reference"><?php esc_html_e( 'Clear Log', 'bookingpress-appointment-booking' ); ?></el-button>
                                        </el-popconfirm>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>                
			</div>            
        <?php 
        }

        
        /**
         * Function for add export/import items key & name 
        */
        function get_export_data_key_name_arr(){
            $export_data_key_name = array();

            $export_data_key_name['default_workhours'] = array('title'=>esc_html__('Settings Working Hours','bookingpress-appointment-booking'),'is_display'=>0);            
            $export_data_key_name['default_special_day'] = array('title'=>esc_html__('Settings Special days','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['default_special_day_breaks'] = array('title'=>esc_html__('Settings Special days break','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['default_daysoff'] = array('title'=>esc_html__('Settings Holidays','bookingpress-appointment-booking'),'is_display'=>0); 
            $export_data_key_name['settings'] = array('title'=>esc_html__('Settings','bookingpress-appointment-booking'),'is_display'=>1);
            $export_data_key_name['customize'] = array('title'=>esc_html__('Customize','bookingpress-appointment-booking'),'is_display'=>1);

            $export_data_key_name['customers'] = array('title'=>esc_html__('Customers','bookingpress-appointment-booking'),'is_display'=>1);
            $export_data_key_name['customer_wp_users'] = array('title'=>esc_html__('Customers WordPress Users','bookingpress-appointment-booking'),'is_display'=>0);

            $export_data_key_name['category'] = array('title'=>esc_html__('Categories','bookingpress-appointment-booking'),'is_display'=>0); 
            $export_data_key_name['services'] = array('title'=>esc_html__('Services','bookingpress-appointment-booking'),'is_display'=>1);
            $export_data_key_name['bookingpress_servicesmeta'] = array('title'=>esc_html__('Service Meta','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['service_workhours'] = array('title'=>esc_html__('Service Working Hours','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['service_daysoff'] = array('title'=>esc_html__('Service Holidays','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['service_special_day'] = array('title'=>esc_html__('Service Special days','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['service_special_day_breaks'] = array('title'=>esc_html__('Service Special days break','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['extra_services'] = array('title'=>esc_html__('Service Extra','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['happy_hours_service'] = array('title'=>esc_html__('Happy Hours','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['custom_service_durations'] = array('title'=>esc_html__('Custom Service Durations','bookingpress-appointment-booking'),'is_display'=>0); 
            $export_data_key_name['advanced_discount'] = array('title'=>esc_html__('Advanced Discount','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['coupon'] = array('title'=>esc_html__('Coupon','bookingpress-appointment-booking'),'is_display'=>0);

            $export_data_key_name['staff_members'] = array('title'=>esc_html__('Staff Members','bookingpress-appointment-booking'),'is_display'=>1);
            $export_data_key_name['staffmembers_services'] = array('title'=>esc_html__('Staff Members Service','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['staff_member_workhours'] = array('title'=>esc_html__('Staff Members Working Hours','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['staffmembers_special_day'] = array('title'=>esc_html__('Staff Members Special days','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['staffmembers_special_day_breaks'] = array('title'=>esc_html__('Staff Members Special days break','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['staffmembers_daysoff'] = array('title'=>esc_html__('Staff Members days off','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['staffmembers_meta'] = array('title'=>esc_html__('Staff Members Meta','bookingpress-appointment-booking'),'is_display'=>0); 
            $export_data_key_name['custom_staffmembers_service_durations'] = array('title'=>esc_html__('Staff Members Custom Service Durations','bookingpress-appointment-booking'),'is_display'=>0);

            $export_data_key_name['location'] = array('title'=>esc_html__('Location','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['locations_service_special_days'] = array('title'=>esc_html__('Location Services Special days','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['locations_service_staff_pricing_details'] = array('title'=>esc_html__('Location Staff & Service Pricing','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['locations_service_workhours'] = array('title'=>esc_html__('Location Service Working Hours','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['locations_staff_special_days'] = array('title'=>esc_html__('Location Staff Members Special days','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['locations_staff_workhours'] =  array('title'=>esc_html__('Location Staff Members Working Hours','bookingpress-appointment-booking'),'is_display'=>0); 
            $export_data_key_name['packages'] = array('title'=>esc_html__('Packages','bookingpress-appointment-booking'),'is_display'=>1);
            $export_data_key_name['package_services'] = array('title'=>esc_html__('Package Services','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['package_images'] = array('title'=>esc_html__('Package Image','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['custom_fields'] =  array('title'=>esc_html__('Custom Fields','bookingpress-appointment-booking'),'is_display'=>0);            
            $export_data_key_name['notifications'] =  array('title'=>esc_html__('Notifications','bookingpress-appointment-booking'),'is_display'=>1);
            $export_data_key_name['multi_language_data'] = array('title'=>esc_html__('Multi-Language Data','bookingpress-appointment-booking'),'is_display'=>1);
            $export_data_key_name['bpa_wpoption_data'] =  array('title'=>esc_html__('BookingPress option data','bookingpress-appointment-booking'),'is_display'=>0);                        

            $export_data_key_name['appointments'] =  array('title'=>esc_html__('Appointments','bookingpress-appointment-booking'),'is_display'=>1);
            $export_data_key_name['package_order'] =  array('title'=>esc_html__('Package Order','bookingpress-appointment-booking'),'is_display'=>0);
            
            $export_data_key_name['guests_data'] =  array('title'=>esc_html__('Guests Data','bookingpress-appointment-booking'),'is_display'=>0);
            $export_data_key_name['images_import'] = array('title'=>esc_html__('Images Import','bookingpress-appointment-booking'),'is_display'=>0);

            $export_data_key_name = apply_filters( 'bookingpress_modified_export_key_name',$export_data_key_name);

            return $export_data_key_name;
        }



        public function bookingpress_dynamic_get_settings_data_fun(){
        ?>
        else if( current_tabname == "migration_tool_settings" ){
            vm.getSettingsData('migration_tool_settings', 'migration_tool_form_new')
        }             
        <?php
        }        

        public function bookingpress_add_setting_dynamic_vue_methods_func(){
            global $bookingpress_notification_duration;
            $bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
        ?> 
            bookingpress_import_data_continue_process_task(){
                const vm = this;
                vm.is_display_import_loader = "0";
                const CustformData = new FormData();
                var bookingpress_import_data = vm.migration_tool_form.import_data;
                var postData = { action:"bookingpress_import_data_continue_process",import_id:vm.continue_import_id, _wpnonce:"<?php echo esc_html($bookingpress_nonce); ?>"};
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.is_display_export_loader = "0";
                    if(response.data.variant == "success"){                                                                                                   
                        if(response.data.is_complete == ""){
                            if(typeof response.data.import_log_data != "undefined"){
                                vm.import_log_data = response.data.import_log_data;
                            }                             
                            vm.bookingpress_import_data_continue_process_task();
                        }else{                                                           
                            vm.continue_import_id = "";
                            vm.migration_tool_form.import_data = "";
                            vm.import_log_data = "";
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });	
                        }                                                                         
                    }else{                                                               
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
                    }                    
                }.bind(this) )
                .catch( function (error) {
                    vm.is_display_export_loader = "0";
                    vm.$notify({
                        title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
                        message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
                });                 
                                 
            },
            bookingpress_export_data_continue_process_task(){
                const vm = this;
                vm.is_display_export_loader = "0";
                const CustformData = new FormData();
                var bookingpress_export_list_data = vm.migration_tool_form.bookingpress_export_list_data;
                var postData = { action:"bookingpress_export_data_continue_process", export_id: vm.continue_export_id, _wpnonce:"<?php echo esc_html($bookingpress_nonce); ?>"};                                  
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.is_display_export_loader = "0";
                    if(response.data.variant == "success"){  
                        if(vm.export_log_stop_id == ""){                            
                            if(response.data.is_complete == ""){
                                if(typeof response.data.export_log_data != "undefined"){
                                    vm.export_log_data = response.data.export_log_data;
                                }                            
                                vm.bookingpress_export_data_continue_process_task();
                            }else{
                                if(typeof response.data.last_export_file != "undefined"){
                                        vm.export_last_download_file =  response.data.last_export_file;
                                }
                                vm.export_log_data = "";                                
                                vm.continue_export_id = "";
                                vm.export_complete_msg = "<?php esc_html_e( "The export process has been successfully completed. Please click on the 'Download File' button below to retrieve your export file.", 'bookingpress-appointment-booking' ); ?>";
                                setTimeout(function(){
                                    vm.export_complete_msg = "";
                                },4000);
                                vm.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: response.data.variant,
                                    customClass: response.data.variant+'_notification',
                                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                });

                            }    
                        }                      
                    }else{                                                               
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
                    }                    
                }.bind(this) )
                .catch( function (error) {
                    vm.is_display_export_loader = "0";
                    vm.$notify({
                        title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
                        message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
                });                
            },
            bookingpress_stop_export_process(){
                const vm = this;
                vm.is_display_export_loader = "1";
                vm.export_log_stop_id = vm.continue_export_id;
                const CustformData = new FormData();                
                var postData = { action:"bookingpress_export_data_stop", export_log_stop_id: vm.continue_export_id, _wpnonce:"<?php echo esc_html($bookingpress_nonce); ?>"};                                  
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.is_display_export_loader = "0";
                    if(response.data.variant == "success"){ 
                        vm.is_display_export_loader = "0";                   
                        vm.continue_export_id = '';                            
                        vm.export_log_data = '';
                        vm.export_last_download_file = '';  
                        vm.export_log_stop_id = "";                                                                               
                    }else{                                
                        vm.is_display_export_loader = "0";
                        vm.is_display_export_loader = "0";                       
                        vm.continue_export_id = '';                            
                        vm.export_log_data = '';
                        vm.export_last_download_file = '';
                        vm.export_log_stop_id = "";
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
                    }                    
                }.bind(this) )
                .catch( function (error) {
                    vm.is_display_export_loader = "0";
                    vm.$notify({
                        title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
                        message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
                });                
            },
            bookingpress_import_data_task(){
                const vm = this;
                vm.is_display_import_loader = "1";
                const CustformData = new FormData();
                var bookingpress_import_data = vm.migration_tool_form.import_data;
                var bookingpress_confirm_import_data = vm.migration_tool_form.confirm_import_data;
                var postData = { action:"bookingpress_import_data_process", bookingpress_import_data: bookingpress_import_data, "confirm_import_data": bookingpress_confirm_import_data, _wpnonce:"<?php echo esc_html($bookingpress_nonce); ?>"};                                  
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.is_display_import_loader = "0";
                    if(response.data.variant == "confirm"){  

                        var bodyElement = document.querySelector('body');
                        if (!bodyElement.classList.contains('bpa_custom_warning_migration')) {
                            bodyElement.classList.add('bpa_custom_warning_migration');
                        }

                        setTimeout(function(){
                            vm.$confirm(response.data.msg, 'Warning', {
                                confirmButtonText: '<?php esc_html_e( 'Continue', 'bookingpress-appointment-booking' ); ?>',
                                cancelButtonText: '<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>',
                                type: 'warning',
                                customClass: 'bpa_custom_warning_notification'
                            }).then(() => {
                                vm.migration_tool_form.confirm_import_data = "Yes";
                                vm.bookingpress_import_data_task();
                            }).catch(() => {
                                vm.migration_tool_form.import_data = "";
                            });
                        },1000);
                    }else{
                        if(response.data.variant == "success"){    
                            vm.continue_import_id = response.data.import_id;
                            if(typeof response.data.import_log_data != "undefined"){
                                vm.import_log_data = response.data.import_log_data;
                            } 
                            vm.migration_tool_form.import_data = "";                          
                            vm.bookingpress_import_data_continue_process_task(); 
                        }else{                                        
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: 'error',
                                customClass: 'error_notification',
                                duration:5000,
                            });
                        }
                    }

                }.bind(this) )
                .catch( function (error) {
                    vm.is_display_import_loader = "0";
                    vm.$notify({
                        title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
                        message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
                });

            },
            bookingpress_export_data_task(){                                
                const vm = this;
                vm.is_display_export_loader = "1";
                const CustformData = new FormData();
                var bookingpress_export_list_data = vm.migration_tool_form.bookingpress_export_list_data;
                var postData = { action:"bookingpress_export_data_process", bookingpress_export_list_data: JSON.stringify(bookingpress_export_list_data), _wpnonce:"<?php echo esc_html($bookingpress_nonce); ?>"};                                  
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.is_display_export_loader = "0";
                    if(response.data.variant == "success"){    
                        vm.bpa_select_all_export_list(false);                    
                        if(response.data.export_id){
                            vm.continue_export_id = response.data.export_id;
                            if(typeof response.data.export_log_data != "undefined"){
                                vm.export_log_data = response.data.export_log_data;
                            }                           
                            vm.bookingpress_export_data_continue_process_task();                            
                        }    
                    }else{                                        
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
                    }                    
                }.bind(this) )
                .catch( function (error) {
                    vm.is_display_export_loader = "0";
                    vm.$notify({
                        title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
                        message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
                });
                
            },
            migaration_child_active(export_list,parent_key){
                var vm = this;
                var flag_return = false;
                if(typeof vm.migration_tool_form.export_list != "undefined" && vm.migration_tool_form.export_list != "" && vm.migration_tool_form.export_list.length != 0){
                        for (const [key, value] of Object.entries(vm.migration_tool_form.export_list)) {
                            if(typeof value.related != "undefined" && value.related != "" && value.related.length != 0){
                                if(vm.migration_tool_form.bookingpress_export_list_data[key]){
                                    for (const [key_iiner, value_inner] of Object.entries(value.related)){
                                        if(parent_key == value_inner){
                                            return true;
                                        }                                    
                                    }
                                }                                 
                            }
                        }
                }
                if(export_list.required_parent == 1){
                    if(typeof export_list.child != "undefined" && export_list.child.length != 0){
                        for (const [key, value] of Object.entries(export_list.child)) {                        
                            if(vm.migration_tool_form.bookingpress_export_list_data[key] == true){
                                //return true;
                            }
                        }                                               
                    }
                }                
                return false;
            },
            bpa_check_select_all_or_not(){
                var vm = this;
                var all_select = true;
                let all_export_list = vm.migration_tool_form.bookingpress_export_list_data;
                for (const [key, value] of Object.entries(all_export_list)) {
                    if(vm.migration_tool_form.bookingpress_export_list_data[key] == false){
                        all_select = false;
                    }
                }
                return all_select;
            },
            bpa_select_all_export_list(all_select){
                var vm = this;
                vm.export_last_download_file = '';
                if(all_select){   
                    vm.export_all_record = true;                 
                    let all_export_list = vm.migration_tool_form.bookingpress_export_list_data;   
                    for (const [key, value] of Object.entries(all_export_list)) {
                        vm.migration_tool_form.bookingpress_export_list_data[key] = true;
                    }                                                         
                }else{
                    vm.export_all_record = false;
                    let all_export_list = vm.migration_tool_form.bookingpress_export_list_data;                    
                    for (const [key, value] of Object.entries(all_export_list)) {
                        vm.migration_tool_form.bookingpress_export_list_data[key] = false;
                    }
                }
            },
            bpa_select_export_list(export_list,event_data,parent_key=""){
                var vm = this;
                vm.export_last_download_file = '';             
                if(event_data){
                    if(typeof export_list.child != "undefined" && export_list.child.length != 0){
                        if(export_list.required_parent == 1){
                            for (const [key, value] of Object.entries(export_list.child)) {
                                vm.migration_tool_form.bookingpress_export_list_data[key] = true;
                            }  
                        }                                             
                    }                     
                    if(typeof export_list.related != "undefined" && export_list.related != "" && export_list.related.length != 0){
                        for (const [key, value] of Object.entries(export_list.related)) {
                            vm.migration_tool_form.bookingpress_export_list_data[value] = true;
                        }                                               
                    }                    
                    if(parent_key != ""){
                        vm.migration_tool_form.bookingpress_export_list_data[parent_key] = true;
                    }
                }else{
                    if(parent_key != ""){
                        vm.migration_tool_form.bookingpress_export_list_data[parent_key] = false;
                    }
                    if(typeof export_list.child != "undefined" && export_list.child.length != 0){
                        for (const [key, value] of Object.entries(export_list.child)) {
                            vm.migration_tool_form.bookingpress_export_list_data[key] = false;
                        }                                               
                    } 
                    if(typeof export_list.related != "undefined" && export_list.related != "" && export_list.related.length != 0){
                        for (const [key, value] of Object.entries(export_list.related)) {
                            vm.migration_tool_form.bookingpress_export_list_data[value] = false;
                        }                                               
                    }                                        
                }
                
            },
        <?php 
        }

        public function bookingpress_get_active_addon_module_list(){
            $bookingpress_active_plugin_module_list = $this->bookingpress_active_plugin_module_list();
            $bookingpress_active_addon_list = array();
            foreach($bookingpress_active_plugin_module_list as $key=>$val){
                if($val){
                    $bookingpress_active_addon_list[] = $key;
                }
            }
            return $bookingpress_active_addon_list;
        }

        
        public function bookingpress_get_addon_name_by_key_list($addon_key_list = array()){
            $bookingpress_addon_name_list = array();
            $bookingpress_all_addon_name_list = array(
                'bookingpress_pro' => esc_html__( 'BookingPress Pro', 'bookingpress-appointment-booking' ),
            );           
            $bookingpress_all_addon_name_list = apply_filters( 'bookingpress_modified_addon_list_for_import_export',$bookingpress_all_addon_name_list);
            if(!empty($bookingpress_all_addon_name_list) && !empty($addon_key_list)){
                foreach($addon_key_list as $addonkey){
                    if(isset($bookingpress_all_addon_name_list[$addonkey])){
                        $bookingpress_addon_name_list[] = $bookingpress_all_addon_name_list[$addonkey];
                        if(!empty($bookingpress_addon_name_list)){
                            if(count($bookingpress_addon_name_list) > 5){
                                break;
                            }                            
                        }
                    }
                }
            }
            return $bookingpress_addon_name_list;
        }
        
        public function bookingpress_active_plugin_module_list(){
			if (! function_exists('is_plugin_active') ) {
                include ABSPATH . '/wp-admin/includes/plugin.php';
            }   
            $bookingpress_pro = (is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php'))?1:0;
            $all_module_and_addon_list = array(                
                'bookingpress_lite' => 1,
                'bookingpress_pro' => $bookingpress_pro,
            );            
            $all_module_and_addon_list = apply_filters( 'bookingpress_modified_migration_active_module_list',$all_module_and_addon_list);
            return $all_module_and_addon_list;
        }

        function get_customize_where_exclude(){
            global $wpdb;            
            $settings_keys = array('after_booking_redirection','after_failed_payment_redirection','default_booking_page','default_mybooking_page','after_booking_redirection','after_cancelled_appointment_redirection','appointment_cancellation_confirmation');

            $settings_keys = apply_filters( 'bookingpress_removed_customize_setting_export_keys',$settings_keys);

            $bookingpress_settings_key_placeholder  = '  bookingpress_setting_name NOT IN(';
            $bookingpress_settings_key_placeholder .= rtrim( str_repeat( '%s,', count( $settings_keys ) ), ',' );
            $bookingpress_settings_key_placeholder .= ') ';
            array_unshift( $settings_keys, $bookingpress_settings_key_placeholder );
            $where_clause = call_user_func_array( array( $wpdb, 'prepare' ), $settings_keys );
            return $where_clause;
        }

        function get_settings_where_exclude(){
            global $wpdb;            
            $settings_keys = array('complete_payment_page_id');            
            $settings_keys = apply_filters( 'bookingpress_removed_settings_export_keys',$settings_keys);

            $bookingpress_settings_key_placeholder  = '  setting_name NOT IN(';
            $bookingpress_settings_key_placeholder .= rtrim( str_repeat( '%s,', count( $settings_keys ) ), ',' );
            $bookingpress_settings_key_placeholder .= ') ';
            array_unshift( $settings_keys, $bookingpress_settings_key_placeholder );
            $where_clause = call_user_func_array( array( $wpdb, 'prepare' ), $settings_keys );
            return $where_clause;
        }        



        function export_item_total_records($type = "",$export_id = 0){

            global $wpdb,$tbl_bookingpress_settings,$tbl_bookingpress_customize_settings,$tbl_bookingpress_customers,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_services,$tbl_bookingpress_notifications,$tbl_bookingpress_form_fields,$tbl_bookingpress_servicesmeta,$tbl_bookingpress_default_daysoff,$tbl_bookingpress_default_workhours;

            $total_records = 0;
            if($type == "settings"){
                $where_clause = $this->get_settings_where_exclude();
                $total_records = $wpdb->get_var("SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` Where {$where_clause}");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is table name.
            }else if($type == "default_daysoff"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_dayoff_id) FROM `{$tbl_bookingpress_default_daysoff}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_daysoff is table name.
            }else if($type == "default_workhours"){                
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_workhours_id) FROM `{$tbl_bookingpress_default_workhours}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name.
            }else if($type == "customize"){
                $where_clause = $this->get_customize_where_exclude();
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_setting_id) FROM `{$tbl_bookingpress_customize_settings}` Where {$where_clause}");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customize_settings is table name.
            }else if($type == "customers"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_customer_id) FROM `{$tbl_bookingpress_customers}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is table name.
            }else if($type == "appointments"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM `{$tbl_bookingpress_appointment_bookings}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
            }else if($type == "services"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_service_id) FROM `{$tbl_bookingpress_services}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is table name.
            }else if($type == "bookingpress_servicesmeta"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_servicemeta_id) FROM `{$tbl_bookingpress_servicesmeta}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_servicesmeta is table name.                
            }else if($type == "notifications"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_notification_id) FROM `{$tbl_bookingpress_notifications}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is table name.
            }else if($type == "custom_fields"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_form_field_id) FROM `{$tbl_bookingpress_form_fields}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
            }else if($type == "category"){
                global $tbl_bookingpress_categories;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_category_id) FROM `{$tbl_bookingpress_categories}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_categories is table name.
            }

            $total_records = apply_filters( 'bookingpress_modified_export_total_records',$total_records,$type,$export_id);

            return $total_records;
        }

        function get_continue_export_id(){
            global $wpdb,$tbl_bookingpress_export_data_log;
            $export_id = 0;
            $bookingperss_continue_export = $wpdb->get_row($wpdb->prepare("SELECT export_id FROM {$tbl_bookingpress_export_data_log}  WHERE export_complete = %d Order by export_id DESC",0),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
            if(!empty($bookingperss_continue_export)){
                return (isset($bookingperss_continue_export['export_id']) && !empty($bookingperss_continue_export['export_id']))?$bookingperss_continue_export['export_id']:0;
            }                
            return $export_id;
        }

        function get_continue_import_id(){
            global $wpdb,$tbl_bookingpress_import_data_log;
            $import_id = 0;
            $bookingperss_continue_export = $wpdb->get_row($wpdb->prepare("SELECT import_id FROM {$tbl_bookingpress_import_data_log}  WHERE import_complete = %d Order by import_id DESC",0),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
            if(!empty($bookingperss_continue_export)){
                return (isset($bookingperss_continue_export['import_id']) && !empty($bookingperss_continue_export['import_id']))?$bookingperss_continue_export['import_id']:0;
            }                
            return $import_id;
        }

        function get_import_log_data(){
            global $BookingPress,$tbl_bookingpress_import_data_log,$tbl_bookingpress_import_detail_log,$wpdb;
            $export_import_data_key_name = $this->get_export_data_key_name_arr();
            $bookingpress_import_log_data = array();
            $bookingperss_continue_import = $wpdb->get_row("SELECT import_id,import_complete FROM {$tbl_bookingpress_import_data_log} Order by import_id DESC",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
            if(!empty($bookingperss_continue_import)){
                $import_data = $bookingperss_continue_import;
                $import_data['import_detail'] = array();
                $bookingperss_import_detail = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_import_detail_log}  WHERE import_id = %d",$import_data['import_id']),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
                if(!empty($bookingperss_import_detail)){
                    $is_continue = "";
                    $bookingpress_import_details = array();
                    foreach($bookingperss_import_detail as $export_detail){
                        $export_detail['is_continue'] = "0";
                        $export_detail['label'] = (isset($export_import_data_key_name[$export_detail['detail_import_detail_type']]['title']))?$export_import_data_key_name[$export_detail['detail_import_detail_type']]['title']:$export_detail['detail_import_detail_type'];
                        if($export_detail['detail_import_complete'] == 0){
                            if($is_continue == ""){
                                $is_continue = 1;
                                $export_detail['is_continue'] = "1";
                            }
                        }
                        $bookingpress_import_details[] = $export_detail;
                    }
                    $export_file = '';
                    if($import_data['import_complete'] == 1){
                    }                    
                    $bookingpress_export_log_data[$import_data['import_id']] = array('import_complete'=>$import_data['import_complete'],'import_detail'=>$bookingpress_import_details);
                }

            }
            return $bookingpress_export_log_data;
        }

        function get_export_log_data(){
            global $BookingPress,$tbl_bookingpress_export_data_log,$tbl_bookingpress_export_data_log_detail,$wpdb;
            $export_import_data_key_name = $this->get_export_data_key_name_arr();
            $bookingpress_export_log_data = array();
            $bookingperss_continue_export = $wpdb->get_row("SELECT export_id,export_complete FROM {$tbl_bookingpress_export_data_log} Order by export_id DESC",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
            if(!empty($bookingperss_continue_export)){
                $export_data = $bookingperss_continue_export;
                $export_data['export_detail'] = array();
                $bookingperss_export_detail = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_export_data_log_detail}  WHERE export_id = %d",$export_data['export_id']),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
                if(!empty($bookingperss_export_detail)){
                    $is_continue = "";
                    $bookingpress_export_details = array();
                    foreach($bookingperss_export_detail as $export_detail){
                        $export_detail['is_continue'] = "0";
                        $export_detail['label'] = (isset($export_import_data_key_name[$export_detail['export_detail_type']]['title']))?$export_import_data_key_name[$export_detail['export_detail_type']]['title']:$export_detail['export_detail_type'];
                        if($export_detail['export_detail_complete'] == 0){
                            if($is_continue == ""){
                                $is_continue = 1;
                                $export_detail['is_continue'] = "1";
                            }
                        }
                        $bookingpress_export_details[] = $export_detail;
                    }
                    $export_file = '';
                    if($export_data['export_complete'] == 1){
                        $export_id = $export_data['export_id'];
                        $upload_dir = wp_upload_dir();
                        $new_folder_path = $upload_dir['basedir'] . '/bookingpress_export_records';                                        
                        $export_file = site_url().'/wp-content/uploads/bookingpress_export_records/bookingpress_export_data-'.$export_id.'.txt';
                    }                    
                    $bookingpress_export_log_data[$export_data['export_id']] = array('export_complete'=>$export_data['export_complete'],'export_file'=>$export_file,'export_detail'=>$bookingpress_export_details);
                }

            }
            return $bookingpress_export_log_data;
        }

    }
}
global $bookingpress_import_export;
$bookingpress_import_export = new bookingpress_import_export();

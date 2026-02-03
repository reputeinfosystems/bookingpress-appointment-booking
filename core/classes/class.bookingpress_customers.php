<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_customers') ) {
    class bookingpress_customers Extends BookingPress_Core
    {
        function __construct(){
            add_action('wp_ajax_bookingpress_get_customers', array( $this, 'bookingpress_get_customer_details' ), 10);
            add_action('wp_ajax_bookingpress_add_customer', array( $this, 'bookingpress_add_customer' ), 10);
            add_action('wp_ajax_bookingpress_get_edit_user', array( $this, 'bookingpress_get_edit_user_details' ), 10);
            add_action('wp_ajax_bookingpress_delete_customer', array( $this, 'bookingpress_delete_customer' ), 10);
            add_action('wp_ajax_bookingpress_bulk_customer', array( $this, 'bookingpress_bulk_action' ), 10);

            add_action('bookingpress_customers_dynamic_vue_methods', array( $this, 'bookingpress_customer_dynamic_vue_methods_func' ), 10);
            add_action('bookingpress_customers_dynamic_on_load_methods', array( $this, 'bookingpress_customer_dynamic_on_load_methods_func' ), 10);
            add_action('bookingpress_customers_dynamic_data_fields', array( $this, 'bookingpress_customer_dynamic_data_fields_func' ), 10);
            add_action('bookingpress_customers_dynamic_helper_vars', array( $this, 'bookingpress_customer_dynamic_helper_vars_func' ), 10);
            add_action('bookingpress_customers_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_customers_view_func' ), 10);
            add_action('wp_ajax_bookingpress_get_wpuser', array( $this, 'bookingpress_get_wpuser' ));

            add_action('wp_ajax_bookingpress_upload_customer_avatar', array( $this, 'bookingpress_upload_customer_avatar_func' ), 10);
            add_action('wp_ajax_bookingpress_get_existing_users_details', array( $this, 'bookingpress_get_existing_user_details' ), 10);

            add_action( 'admin_init', array( $this, 'bookingpress_customer_vue_data_fields') );
            add_action('user_register', array($this,'bookingpress_add_capabilities_to_new_user'));

            add_action( 'wp_ajax_bookingpress_remove_customer_avatar', array( $this, 'bookingpress_remove_customer_avatar_func'));

            add_action( 'wp_ajax_bookingpress_export_customer_data_lite', array( $this, 'bookingpress_export_customer_data_lite_func' ), 10 );

            add_action('wp_ajax_bookingpress_upload_import_customer_file', array( $this, 'bookingpress_upload_customer_import_file_func' ), 10);

            add_action('wp_ajax_bookingpress_import_customers', array( $this, 'bookingpress_import_customers_func' ), 10);

        }
                
        /**
         * Function for upload customer csv file
         *
         * @return void
        */
        function bookingpress_upload_customer_import_file_func(){

            $return_data = array(
                'error'            => 0,
                'msg'              => '',
                'upload_url'       => '',
                'upload_file_name' => '',
                );
             //phpcs:ignore 
             $bookingpress_fileupload_obj = new bookingpress_fileupload_class( $_FILES['file'] );
    
             if (! $bookingpress_fileupload_obj ) {
                $return_data['error'] = 1;
                $return_data['msg']   = $bookingpress_fileupload_obj->error_message;
             }

             $bpa_check_authorization = $this->bpa_check_authentication( 'customer_import', true, 'bookingpress_upload_import_customer_file');

             if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
             }   
             
             $bookingpress_fileupload_obj->check_cap          = true;
             $bookingpress_fileupload_obj->check_nonce        = true;
             $bookingpress_fileupload_obj->nonce_data         = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
             $bookingpress_fileupload_obj->nonce_action       = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
             $bookingpress_fileupload_obj->check_only_image   = false;
             $bookingpress_fileupload_obj->check_specific_ext = true;
             $bookingpress_fileupload_obj->allowed_ext        = array('csv');
             
             //$affiliatepress_file_name                = 'affiliate-import.csv';
             $file_name                = isset($_FILES['file']['name']) ? current_time('timestamp') . '_' . sanitize_file_name($_FILES['file']['name']) : ''; // phpcs:ignore
             $file_name                = 'customers-import.csv';

             $upload_dir               = BOOKINGPRESS_TMP_IMAGES_DIR . '/';
             $upload_url               = BOOKINGPRESS_TMP_IMAGES_URL . '/';
             $bookingpress_destination = $upload_dir . $file_name;
 
             $check_file = wp_check_filetype_and_ext( $bookingpress_destination, $file_name );
             
             if( empty( $check_file['ext'] ) ){
                 $return_data['error'] = 1;
                 $return_data['upload_error'] = $upload_file;
                 $return_data['msg']   = esc_html__('Invalid file extension. Please select valid file', 'bookingpress-appointment-booking');
             } else {
                 $upload_file = $bookingpress_fileupload_obj->bookingpress_process_upload($bookingpress_destination);
                 if ($upload_file == false ) {
                     $return_data['error'] = 1;
                     $return_data['msg']   = ! empty($upload_file->error_message) ? $upload_file->error_message : esc_html__('Something went wrong while updating the file', 'bookingpress-appointment-booking');
                 } else {
                    if (($bookingpress_handle = fopen($bookingpress_destination, 'r')) !== false) { // phpcs:ignore

                        $bookingpress_final_first_row_data = array();
                        $bookingpress_final_first_row_data_check = (array)fgetcsv($bookingpress_handle,1000,",");                        
                        if(!empty($bookingpress_final_first_row_data_check)){
                            foreach($bookingpress_final_first_row_data_check as $key=>$val){
                                $bookingpress_final_first_row_data[] = array('key'=>$key,'value'=>$val);
                            }
                        }
                        $random = rand(99999,9999999999);
                        update_option('bookingpress_import_csv_token',$random);
                        $return_data['error']              = 0;
                        $return_data['msg']                = '';
                        $return_data['import_file_fields'] = $bookingpress_final_first_row_data;
                        $return_data['import_file_name']   = $random;
   
                    }else{
                        $return_data['error'] = 1;
                        $return_data['upload_error'] = 'error';
                        $return_data['msg']   = esc_html__('File read permission not allowed.', 'bookingpress-appointment-booking');  
                    }
                 }
             }
             
             echo wp_json_encode($return_data);
             exit();             

             

        }
        

        function bookingpress_import_customers_func(){

            global $wpdb, $tbl_bookingpress_customers,$BookingPress, $tbl_bookingpress_form_fields;

            $bpa_check_authorization = $this->bpa_check_authentication( 'customer_import', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $bookingpress_import_file_name = isset($_POST['import_file_name']) ? sanitize_text_field($_POST['import_file_name']) : ''; // phpcs:ignore 
            $bookingpress_create_wp_user   = isset($_POST['create_wp_user']) ? sanitize_text_field($_POST['create_wp_user']) : ''; // phpcs:ignore 
            $bookingpress_field_data = '';
            if((isset($_POST['import_file_fields'])) && !empty($_POST['import_file_fields']) && is_array($_POST['import_file_fields'])){  // phpcs:ignore                               
                $bookingpress_field_data = !empty($_POST['import_file_fields']) ? array_map('absint', (array) $_POST['import_file_fields']) : array(); // phpcs:ignore                
            }

            $bookingpress_import_csv_token = get_option('bookingpress_import_csv_token');
            if($bookingpress_import_file_name != $bookingpress_import_csv_token){
                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__( 'Sorry, the token does not match.', 'bookingpress-appointment-booking');
                wp_send_json( $response );
                die;                
            }

            if(!empty($bookingpress_field_data) && !empty($bookingpress_import_file_name)){

                $bookingpress_upload_dir               = BOOKINGPRESS_TMP_IMAGES_DIR . '/';                
                $bookingpress_destination              = $bookingpress_upload_dir .'customers-import.csv';
                $bookingpress_import_count             = 0;
                $bookingpress_duplicate_count          = 0;                    
                $bookingpress_total_count              = 0; 
                $bookingpress_not_import_count         = 0;  

                $bookingpress_not_import_data = array();
                if(file_exists($bookingpress_destination)){

                    if (($bookingpress_handle = fopen($bookingpress_destination, "r")) !== FALSE) { // phpcs:ignore

                        $bookingpress_data_array = array();
                        $bookingpress_i = 0;
                        while (($bookingpress_row = fgetcsv($bookingpress_handle, 2000, ",")) !== FALSE) {
                            $bookingpress_i++;
                            if(empty($bookingpress_row)){
                                $bookingpress_row = fgetcsv($bookingpress_handle, 2000, ",");
                            }
                            if($bookingpress_i == 1){
                                continue;
                            }
                            $bookingpress_total_count++;
                            $bookingpress_has_import_affiliate = false;
                            if(!empty($bookingpress_row) && is_array($bookingpress_row)){
                                if(!empty($bookingpress_field_data) && is_array($bookingpress_field_data)){                                    
                                                                       
                                    $bookingpress_final_single_import_data = array();
                                    foreach($bookingpress_field_data as $fkey=>$fval){
                                         $bookingpress_import_data = (isset($bookingpress_row[$fval]))?$bookingpress_row[$fval]:'';                                         
                                         $bookingpress_final_single_import_data[$fkey] = str_replace("-","",$bookingpress_import_data);
                                    }

                                    $first_name = (isset($bookingpress_final_single_import_data['first_name']))?sanitize_text_field($bookingpress_final_single_import_data['first_name']):'';
                                    $last_name = (isset($bookingpress_final_single_import_data['last_name']))?sanitize_text_field($bookingpress_final_single_import_data['last_name']):'';
                                    $email = (isset($bookingpress_final_single_import_data['email']))?sanitize_text_field($bookingpress_final_single_import_data['email']):'';
                                    $phone = (isset($bookingpress_final_single_import_data['phone']))?sanitize_text_field($bookingpress_final_single_import_data['phone']):'';
                                    $note = (isset($bookingpress_final_single_import_data['note']))?sanitize_text_field($bookingpress_final_single_import_data['note']):'';
                                    $username = (isset($bookingpress_final_single_import_data['username']))?sanitize_text_field($bookingpress_final_single_import_data['username']):'';
                                    $user_email = (isset($bookingpress_final_single_import_data['user_email']))?sanitize_text_field($bookingpress_final_single_import_data['user_email']):'';

                                    if(empty($first_name)){
                                        $bookingpress_single_not_import = array();
                                        $bookingpress_single_not_import['row_data'] = $bookingpress_final_single_import_data;
                                        $bookingpress_single_not_import['reason'] = esc_html__( 'First Name Empty.', 'bookingpress-appointment-booking' );
                                        $bookingpress_not_import_data[] = $bookingpress_single_not_import; 
                                        $bookingpress_not_import_count++;
                                        continue;
                                    }

                                    if(empty($last_name)){
                                        $bookingpress_single_not_import = array();
                                        $bookingpress_single_not_import['row_data'] = $bookingpress_final_single_import_data;
                                        $bookingpress_single_not_import['reason'] = esc_html__( 'Last Name Empty.', 'bookingpress-appointment-booking' );
                                        $bookingpress_not_import_data[] = $bookingpress_single_not_import; 
                                        $bookingpress_not_import_count++;
                                        continue;
                                    }  
                                    
                                    if(empty($email) || (!empty($email) && !is_email($email))){
                                        $bookingpress_single_not_import = array();
                                        $bookingpress_single_not_import['row_data'] = $bookingpress_final_single_import_data;
                                        $bookingpress_single_not_import['reason'] = esc_html__( 'Customer Email Not Valid.', 'bookingpress-appointment-booking' );
                                        $bookingpress_not_import_data[] = $bookingpress_single_not_import; 
                                        $bookingpress_duplicate_count++;
                                        continue;
                                    }

                                    $bookingpress_is_customer_exist = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_customer_id) as total FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_email = %s AND bookingpress_user_type = 2", $email))); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm

                                    if($bookingpress_is_customer_exist > 0){
                                        $bookingpress_single_not_import = array();
                                        $bookingpress_single_not_import['row_data'] = $bookingpress_final_single_import_data;
                                        $bookingpress_single_not_import['reason'] = esc_html__( 'Customer Already Added.', 'bookingpress-appointment-booking' );
                                        $bookingpress_not_import_data[] = $bookingpress_single_not_import; 
                                        $bookingpress_duplicate_count++;
                                        continue;                                        
                                    }

                                    $bookingpress_wpuser_id = 0;
                                    $bookingpress_user_login = '';                                    

                                    if($bookingpress_create_wp_user == "true"){                                        

                                        if(empty($username)){
                                            $bookingpress_single_not_import = array();
                                            $bookingpress_single_not_import['row_data'] = $bookingpress_final_single_import_data;
                                            $bookingpress_single_not_import['reason'] = esc_html__( 'Customer Already Added.', 'bookingpress-appointment-booking' );
                                            $bookingpress_not_import_data[] = $bookingpress_single_not_import; 
                                            $bookingpress_not_import_count++;
                                            continue; 
                                        }

                                        if(empty($user_email) || (!empty($user_email) && !is_email($user_email))){
                                            $bookingpress_single_not_import = array();
                                            $bookingpress_single_not_import['row_data'] = $bookingpress_final_single_import_data;
                                            $bookingpress_single_not_import['reason'] = esc_html__( 'User Email Not Valid.', 'bookingpress-appointment-booking' );
                                            $bookingpress_not_import_data[] = $bookingpress_single_not_import; 
                                            $bookingpress_not_import_count++;
                                            continue; 
                                        }

                                        
                                        $has_user = get_user_by('email', $user_email);
                                        if ($has_user) {
                                            $bookingpress_wpuser_id = $has_user->ID;
                                            $bookingpress_user_login = $has_user->user_login;
                                        }else{
                                            $password = wp_generate_password(16, true, true);                                                 
                                            $user_data = [
                                                'user_login'   => $username,
                                                'user_pass'    => $password,
                                                'user_email'   => $user_email,
                                                'first_name'   => $first_name,
                                                'last_name'    => $last_name,
                                                'role'         => 'bookingpress-customer', // Optional: set any role
                                            ];
                                            
                                            $has_user = wp_insert_user($user_data);                                               
                                            if ($has_user) {                          
                                                $has_user = get_user_by('email', $user_email);               
                                                $bookingpress_wpuser_id = $has_user->ID;
                                                $bookingpress_user_login = $has_user->user_login;                                                
                                            }else{
                                                $bookingpress_single_not_import = array();
                                                $bookingpress_single_not_import['row_data'] = $bookingpress_final_single_import_data;
                                                $bookingpress_single_not_import['reason'] = esc_html__( 'WordPress User Not created.', 'bookingpress-appointment-booking' );
                                                $bookingpress_not_import_data[] = $bookingpress_single_not_import; 
                                                $bookingpress_not_import_count++;
                                                continue;                                                
                                            }
                                        }
                                        
                                    }                                    


                                    if(!empty($phone)){                                        
                                        $phone = preg_replace('/\D/', '', $phone);                                        
                                    }
                                    

                                    $customer_details = array(
                                        'bookingpress_wpuser_id'              => $bookingpress_wpuser_id,
                                        'bookingpress_user_login'             => $bookingpress_user_login,
                                        'bookingpress_user_status'            => 1,
                                        'bookingpress_user_type'              => 2,
                                        'bookingpress_user_email'             => $email,
                                        'bookingpress_user_name'              => $bookingpress_user_login,
                                        'bookingpress_customer_full_name'     => $first_name.' '.$last_name,
                                        'bookingpress_user_firstname'         => $first_name,
                                        'bookingpress_user_lastname'          => $last_name,
                                        'bookingpress_user_phone'             => $phone,
                                        'bookingpress_user_country_phone'     => '',
                                        'bookingpress_user_country_dial_code' => '',
                                        'bookingpress_user_timezone'          => '',
                                        'bookingpress_user_created'           => current_time('mysql'),
                                        'bookingpress_created_at'             => 2,
                                        'bookingpress_created_by'             => get_current_user_id(),
                                    );                                    

                                    if(!empty($phone)){
                                        
                                        $bookingpress_company_phone_country = $BookingPress->bookingpress_get_settings('company_phone_country', 'company_setting');
                                        $customer_details['bookingpress_user_country_phone'] = $bookingpress_company_phone_country;
                                        $bookingpress_company_phone_number = $BookingPress->bookingpress_get_settings('company_phone_number', 'company_setting');
                                        if(!empty($bookingpress_company_phone_number)){
                                            $bookingpress_company_phone_number = explode(" ",$bookingpress_company_phone_number);
                                            if(!empty($bookingpress_company_phone_number) && is_array($bookingpress_company_phone_number) && isset($bookingpress_company_phone_number[0])){
                                                $bookingpress_company_phone_dial = $bookingpress_company_phone_number[0];
                                                $customer_details['bookingpress_user_country_dial_code'] = $bookingpress_company_phone_dial;
                                            }                                            
                                        }
                                        
                                    }

                                    $wpdb->insert($tbl_bookingpress_customers, $customer_details);
                                    $bookingpress_customer_id = $wpdb->insert_id;

                                    if($bookingpress_customer_id){

                                        if(!empty($note)){
                                            $BookingPress->update_bookingpress_customersmeta($bookingpress_customer_id, 'customer_note', $note);
                                        }
                                        
                                        $bookingpress_import_count++;
                                        do_action('bookingpress_after_import_customer',$bookingpress_customer_id, $bookingpress_final_single_import_data, $bookingpress_field_data, $bookingpress_row);
                                    }else{
                                        $bookingpress_not_import_count++;
                                    }                                    
    
                                }
                            }                    
                            
                        }
                        fclose($bookingpress_handle); // phpcs:ignore

                        $response['variant']          = 'success';
                        $response['title']            = esc_html__( 'Success', 'bookingpress-appointment-booking');
                        $response['msg']              =  esc_html__( 'Customers Succesfully Imported', 'bookingpress-appointment-booking');
                        
                        $response['customers_import_count']     = $bookingpress_import_count;
                        $response['duplicate_count']  = $bookingpress_duplicate_count;
                        $response['customers_not_import_count'] = ($bookingpress_not_import_count+$bookingpress_duplicate_count);
                        $response['customers_total_count']      = $bookingpress_not_import_count+$bookingpress_duplicate_count+$bookingpress_import_count;

                        if(file_exists($bookingpress_destination)){
                            wp_delete_file($bookingpress_destination); // phpcs:ignore
                        }
                        wp_send_json( $response );
                        die;   

                    }else{

                        if(file_exists($bookingpress_destination)){
                            wp_delete_file($bookingpress_destination); // phpcs:ignore
                        }
                        $response['variant'] = 'error';
                        $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                        $response['msg'] =  esc_html__( 'CSV File read permission issue.', 'bookingpress-appointment-booking');
                        wp_send_json( $response );
                        die;

                    }

                }else{
                    $response['variant'] = 'error';
                    $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                    $response['msg'] =  esc_html__( 'CSV File not exists.', 'bookingpress-appointment-booking');
                    wp_send_json( $response );
                    die;
                }

            }else{

            }

            $response['variant'] = 'error';
            $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
            $response['msg'] =  esc_html__( 'Customer not import.', 'bookingpress-appointment-booking');

           
            wp_send_json( $response );
            die;

            
        }

        /**
         * Function for check export customer data lite
         *
         * @return void
        */
        function bookingpress_export_customer_data_lite_func(){
            global $wpdb, $tbl_bookingpress_customers,$BookingPress, $tbl_bookingpress_form_fields;
            $bpa_check_authorization = $this->bpa_check_authentication( 'customer_export', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_export_field = ! empty( $_REQUEST['export_field'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['export_field'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			
            $bookingpress_search_data  = ! empty($_REQUEST['search_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data']) : array(); // phpcs:ignore
            $bookingpress_search_query = $bookingpress_search_query_join = '';

            if (! empty($bookingpress_search_data['search_name']) ) {
                $bookingpress_search_customer_name = explode(' ', $bookingpress_search_data['search_name']);
                $bookingpress_search_query        .= ' AND (';
                $search_loop_counter               = 1;
                foreach ( $bookingpress_search_customer_name as $bookingpress_search_customer_key => $bookingpress_search_customer_val ) {
                    if ($search_loop_counter > 1 ) {
                        $bookingpress_search_query .= ' OR';
                    }
                    $bookingpress_search_query .= " (bookingpress_user_login LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_user_email LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_customer_full_name LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_user_firstname LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_user_lastname LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_user_phone LIKE '%{$bookingpress_search_customer_val}%')";

                    $search_loop_counter++;
                }
                $bookingpress_search_query .= ' )';
            }
            if (! empty($bookingpress_search_data['selected_date_range']) ) {
                $bookingpress_search_date         = $bookingpress_search_data['selected_date_range'];
                $start_date                       = date('Y-m-d', strtotime($bookingpress_search_date[0]));
                $end_date                         = date('Y-m-d', strtotime($bookingpress_search_date[1]));
                $bookingpress_search_query .= " AND (bookingpress_user_created BETWEEN '".$start_date." 00:00:00' AND '".$end_date." 23:59:59')";
            }

            $bookingpress_search_query_join = apply_filters('bookingpress_customer_view_join_add_filter', $bookingpress_search_query_join);

            $bookingpress_search_query = apply_filters('bookingpress_customer_view_add_filter', $bookingpress_search_query);

            $total_customers = $wpdb->get_results("SELECT cs.bookingpress_customer_id FROM {$tbl_bookingpress_customers} as cs {$bookingpress_search_query_join} WHERE cs.bookingpress_user_type = 2 AND cs.bookingpress_user_status = 1 {$bookingpress_search_query} ",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm

            $get_customers = $wpdb->get_results("SELECT cs.* FROM {$tbl_bookingpress_customers} as cs {$bookingpress_search_query_join} WHERE cs.bookingpress_user_type = 2 AND cs.bookingpress_user_status = 1 {$bookingpress_search_query} order by bookingpress_customer_id DESC ", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm

            $bookingpress_customers = array();
            if (! empty($get_customers) && !empty($bookingpress_export_field) ) {
                $counter = 1;
                foreach ( $get_customers as $customer ) {


					$bookingpress_customer_tmp_details = array();
					$bookingpress_customer_id          = intval( $customer['bookingpress_customer_id'] );
					if ( in_array( 'first_name', $bookingpress_export_field ) ) {
						$bookingpress_customer_tmp_details['First Name'] = ! empty( $customer['bookingpress_user_firstname'] ) ? '"' . sanitize_text_field( $customer['bookingpress_user_firstname'] ) . '"' : '-';
					}
					if ( in_array( 'last_name', $bookingpress_export_field ) ) {
						$bookingpress_customer_tmp_details['Last Name'] = ! empty( $customer['bookingpress_user_lastname'] ) ? '"' . sanitize_text_field( $customer['bookingpress_user_lastname'] ) . '"' : '-';
					}
					if ( in_array( 'email', $bookingpress_export_field ) ) {
						$bookingpress_customer_tmp_details['Email'] = ! empty( $customer['bookingpress_user_email'] ) ? '"' . sanitize_email( $customer['bookingpress_user_email'] ) . '"' : '-';
					}
					if ( in_array( 'phone', $bookingpress_export_field ) ) {
						$bookingpress_customer_tmp_details['Phone'] = ! empty( $customer['bookingpress_user_phone'] ) ? '"' . sanitize_text_field( $customer['bookingpress_user_phone'] ) . '"' : '-';
					}
					if ( in_array( 'note', $bookingpress_export_field ) ) {
						$bookingpress_customer_note_data           = $BookingPress->get_bookingpress_customersmeta( $customer['bookingpress_customer_id'], 'customer_note' );
						$bookingpress_customer_tmp_details['Note'] = ! empty( $bookingpress_customer_note_data ) ? '"' . sanitize_textarea_field( $bookingpress_customer_note_data ) . '"' : '-';
					}

                    if(in_array( 'username', $bookingpress_export_field ) || in_array( 'user_email', $bookingpress_export_field ) ){

                        $username = "";
                        $user_password = "";
                        $user_email = "";
                        $bookingpress_wpuser_id = (!empty($customer['bookingpress_wpuser_id']))?intval($customer['bookingpress_wpuser_id']):0;
                        if($bookingpress_wpuser_id){
                            
                            $user_info     = get_userdata($bookingpress_wpuser_id);
                            $username      = (isset($user_info->user_login))?$user_info->user_login:'';
                            $user_password = (isset($user_info->user_pass))?$user_info->user_pass:''; 
                            $user_email    = (isset($user_info->user_email))?$user_info->user_email:''; 

                            if ( in_array( 'username', $bookingpress_export_field ) ) {
                                $bookingpress_customer_tmp_details['User Name'] = ! empty( $username ) ? '"' . sanitize_textarea_field( $username ) . '"' : '-';
                            }
                            if ( in_array( 'user_email', $bookingpress_export_field ) ) {
                                $bookingpress_customer_tmp_details['User Email'] = ! empty( $user_email ) ? '"' . sanitize_textarea_field( $user_email ) . '"' : '-';
                            } 
                        }
                    }
                    $bookingpress_customers[] = $bookingpress_customer_tmp_details;
                }
            }

            /*
            ob_start();
            if (ob_get_length()) {
                ob_end_clean();
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $affiliatepress_filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            $affiliatepress_output = fopen('php://output', 'w');
            fputcsv($affiliatepress_output, array_keys($affiliatepress_columns[0]));
            foreach ($affiliatepress_exports_data as $affiliatepress_export_data) {
                fputcsv($affiliatepress_output, $affiliatepress_export_data);
            }
            fclose($affiliatepress_output);//phpcs:ignore
            exit;            
            */

			$data = array();
			if ( ! empty( $bookingpress_customers ) ) {
				array_push( $data, array_keys( $bookingpress_customers[0] ) );
				foreach ( $bookingpress_customers as $key => $value ) {
					array_push( $data, array_values( $value ) );
				}
			}
			$response['status'] = 'success';
			$response['data']   = $data;
			echo wp_json_encode( $response );
			exit;
            
        }

        /**
         * Add BookingPress capabilities when new admin user register from backend
         *
         * @param  mixed $user_id   New registered user id
         * @return void
         */
        function bookingpress_add_capabilities_to_new_user($user_id) {
            global $BookingPress;
            if ($user_id == '') {
                return;
            }
            if (user_can($user_id, 'administrator')) {
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
         * Default data variables for customer module
         *
         * @return void
         */
        function bookingpress_customer_vue_data_fields(){
            global $bookingpress_customer_vue_data_fields,$bookingpress_global_options,$BookingPress;
            $bookingpress_options                  = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_country_list             = $bookingpress_options['country_lists'];
            $bookingpress_pagination               = $bookingpress_options['pagination'];
            $bookingpress_pagination_arr           = json_decode($bookingpress_pagination, true);
            $bookingpress_pagination_selected      = $bookingpress_pagination_arr[0];

            $bookingpress_allow_customer_create = $BookingPress->bookingpress_get_settings('allow_wp_user_create', 'customer_setting');
            $bookingpress_allow_customer_create = ! empty($bookingpress_allow_customer_create) ? $bookingpress_allow_customer_create : 'false';
            $bookingpress_allow_customer_create = $bookingpress_allow_customer_create == 'true' ? true : false;

            $bookingpress_customer_vue_data_fields = array(
                'bulk_action'                => 'bulk_action',
                'bulk_options'               => array(
                    array(
                        'value' => 'bulk_action',
                        'label' => esc_html__('Bulk Action', 'bookingpress-appointment-booking'),
                    ),
                    array(
                        'value' => 'delete',
                        'label' => esc_html__('Delete', 'bookingpress-appointment-booking'),
                    ),
                ),
            
                'phone_countries_details'    => json_decode($bookingpress_country_list),
                'loading'                    => false,
                'items'                      => array(),
                'multipleSelection'          => array(),
                'perPage'                    => $bookingpress_pagination_selected,
                'totalItems'                 => 0,
                'pagination_selected_length' => $bookingpress_pagination_selected,
                'pagination_length'          => $bookingpress_pagination,
                'currentPage'                => 1,
                'open_customer_modal'        => false,
                'customer'                   => array(
                    'avatar_url'             => '',
                    'avatar_name'            => '',
                    'avatar_list'            => array(),
                    'wp_user'                => null,
                    'username'               => '',
                    'firstname'              => '',
                    'lastname'               => '',
                    'email'                  => '',
                    'phone'                  => '',
                    'customer_phone_country' => '',
                    'customer_phone_dial_code' => '',
                    'note'                   => '',
                    'update_id'              => 0,
                    '_wpnonce'               => '',
                    'password'               => '',
                ),
                'customer_detail_save'       => false,
                'wpUsersList'                => array(),
                'savebtnloading'             => false,
                'rules'                      => array(
                    'username' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter username', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'firstname' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter firstname', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'lastname'  => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter lastname', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'email'     => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter email address', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                        array(
                            'type'    => 'email',
                            'message' => esc_html__('Please enter valid email address', 'bookingpress-appointment-booking'),
                            'trigger' => 'blur',
                        ),
                    ),
                    'wp_user' => array(
                        array(
                            'required' => $bookingpress_allow_customer_create,
                            'message'  => esc_html__('Please select Wordpress User', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'customerSearch'             => '',
                'customer_search_range'      => '',
                'columnSequenceModal'        => false,
                'pagination_length_val'      => '10',
                'pagination_val'             => array(
                    array(
                        'text'  => '10',
                        'value' => '10',
                    ),
                    array(
                        'text'  => '20',
                        'value' => '20',
                    ),
                    array(
                        'text'  => '50',
                        'value' => '50',
                    ),
                    array(
                        'text'  => '100',
                        'value' => '100',
                    ),
                    array(
                        'text'  => '200',
                        'value' => '200',
                    ),
                    array(
                        'text'  => '300',
                        'value' => '300',
                    ),
                    array(
                        'text'  => '400',
                        'value' => '400',
                    ),
                    array(
                        'text'  => '500',
                        'value' => '500',
                    ),
                ),
                'cusShowFileList'            => false,
                'is_display_loader'          => '0',
                'is_disabled'                => false,
                'is_display_save_loader'     => '0',
            );
            
        }
		
		/**
		 * BookingPress core function for create customer in BookingPress
		 *
		 * @param  mixed $bookingpress_customer_data      Customer details
		 * @param  mixed $bookingpress_existing_user_id   If wordpress user already exists then pass user id
		 * @param  mixed $is_front                        1 or 2. If customer created from front or not. 1 = front and 2 = backend
		 * @param  mixed $is_customer                     Is already BookingPress Customer
		 * @param  mixed $bookingpress_customer_timezone  Created customer timezone
		 * @return void
		 */
		function bookingpress_create_customer($bookingpress_customer_data, $bookingpress_existing_user_id = 0, $is_front = 2, $is_customer = 0, $bookingpress_customer_timezone = "")
        {
			//if the is_front parameter value is 1 then appointment booked at front side else 2 then appointment is booked at backend.
			//if the is_customer create parameter value is  1 then customer is create at the backend.
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers, $tbl_bookingpress_entries, $bookingpress_email_notifications, $bookingpress_debug_payment_log_id, $bookingpress_global_options, $tbl_bookingpress_entries_meta;
            $bookingpress_customer_id = $bookingpress_wpuser_id = 0;
            $bookingpress_user_pass   = '';

            $bookingpress_terms_conditions = !empty($_POST['appointment_data']['appointment_terms_conditions'][0]) ? sanitize_text_field($_POST['appointment_data']['appointment_terms_conditions'][0] ) : ''; //phpcs:ignore

            if( empty( $bookingpress_terms_conditions ) ){
                $bookingpress_terms_conditions = !empty($_POST['appointment_data']['form_fields']['appointment_terms_conditions'][0]) ? sanitize_text_field($_POST['appointment_data']['form_fields']['appointment_terms_conditions'][0] ) : ''; //phpcs:ignore
            }

            if(empty($bookingpress_customer_timezone)){
                $bookingpress_customer_timezone = $bookingpress_global_options->bookingpress_get_site_timezone_offset();
            }
            $bookingpress_is_customer_create = 0;
            if (! empty($bookingpress_customer_data) ) {

                $bookingpress_customer_name      = ! empty($bookingpress_customer_data['bookingpress_customer_name']) ? $bookingpress_customer_data['bookingpress_customer_name'] : '';
                $bookingpress_username          = ! empty($bookingpress_customer_data['bookingpress_username']) ? $bookingpress_customer_data['bookingpress_username'] : '';
                $bookingpress_customer_phone     = ! empty($bookingpress_customer_data['bookingpress_customer_phone']) ? $bookingpress_customer_data['bookingpress_customer_phone'] : '';
                $bookingpress_customer_firstname = ! empty($bookingpress_customer_data['bookingpress_customer_firstname']) ? $bookingpress_customer_data['bookingpress_customer_firstname'] : '';
                $bookingpress_customer_lastname  = ! empty($bookingpress_customer_data['bookingpress_customer_lastname']) ? $bookingpress_customer_data['bookingpress_customer_lastname'] : '';
                $bookingpress_customer_country   = ! empty($bookingpress_customer_data['bookingpress_customer_country']) ? $bookingpress_customer_data['bookingpress_customer_country'] : '';
                $bookingpress_customer_email     = ! empty($bookingpress_customer_data['bookingpress_customer_email']) ? $bookingpress_customer_data['bookingpress_customer_email'] : '';
                $bookingpress_customer_dial_code = !empty($bookingpress_customer_data['bookingpress_customer_phone_dial_code']) ? $bookingpress_customer_data['bookingpress_customer_phone_dial_code'] : '';

                $bookingpress_user_name = '';                
                
                if((empty($bookingpress_customer_name) && empty( $bookingpress_username))  && !empty($bookingpress_customer_email) ){
                    $bookingpress_user_name = $bookingpress_customer_email;
                }
                
                if( !empty($bookingpress_username) && empty($bookingpress_customer_name)){
                    $bookingpress_user_name = $bookingpress_username;
                }

                if( empty($bookingpress_username) && !empty($bookingpress_customer_name)){
                    $bookingpress_user_name = $bookingpress_customer_name;
                }

                if(!empty($bookingpress_customer_name) && !empty($bookingpress_username)){
                    $bookingpress_user_name = $bookingpress_username;
                }
                
                $bpa_do_autologin = false;
                if (empty($bookingpress_existing_user_id) ) {
                    $bookingpress_allow_customer_create = $BookingPress->bookingpress_get_settings('allow_wp_user_create', 'customer_setting');
                    $bookingpress_allow_customer_create = ! empty($bookingpress_allow_customer_create) ? $bookingpress_allow_customer_create : 'false';
                    if ($bookingpress_allow_customer_create == 'false' || $is_front == 2 ) {
                        // If user create switch turned off then this condition executes.
                        $customer_details = array(
                            'bookingpress_wpuser_id'      => $bookingpress_wpuser_id,
                            'bookingpress_user_login'     => $bookingpress_customer_email,
                            'bookingpress_user_status'    => 1,
                            'bookingpress_user_type'      => 2,
                            'bookingpress_user_email'     => $bookingpress_customer_email,
                            'bookingpress_user_name'      => $bookingpress_user_name,
                            'bookingpress_customer_full_name'  => $bookingpress_customer_name,
                            'bookingpress_user_firstname' => $bookingpress_customer_firstname,
                            'bookingpress_user_lastname'  => $bookingpress_customer_lastname,
                            'bookingpress_user_phone'     => $bookingpress_customer_phone,
                            'bookingpress_user_country_phone' => $bookingpress_customer_country,
                            'bookingpress_user_country_dial_code' => $bookingpress_customer_dial_code,
                            'bookingpress_user_timezone'  => $bookingpress_customer_timezone,
                            'bookingpress_user_created'   => current_time('mysql'),
                            'bookingpress_created_at'     => $is_front,
                            'bookingpress_created_by'     => ( is_user_logged_in() ) ? get_current_user_id() : '',
                        );

                        $wpdb->insert($tbl_bookingpress_customers, $customer_details);
                        $bookingpress_customer_id = $wpdb->insert_id;
                        $bookingpress_is_customer_create = 1;
                        do_action( 'bookingpress_after_create_customer', $bookingpress_customer_id );
                    } elseif ($bookingpress_allow_customer_create == 'true' ) {
                        $bookingpress_is_wp_user_exist = get_user_by('email', $bookingpress_customer_email);
                        if (empty($bookingpress_is_wp_user_exist) ) {
                            // If WordPress user not exists

                            $bpa_user_raw_password = true;
                            $bpa_send_new_user_notication = 0;
                            $bookingpress_user_pass = apply_filters('bookingpress_user_password_change_filter', '', $bookingpress_customer_data);

                            $update_pass = true;
                            if( empty( $bookingpress_user_pass )){
                                $bookingpress_user_pass = wp_generate_password(12, false);
                                $bpa_send_new_user_notication = 1;
                                $update_pass = false;
                                $bpa_user_raw_password = false;
                            }

                            $bookingpress_wpuser_id = 0;
                            if(!empty($bookingpress_customer_email)) {
                                

                                if( true == $update_pass ){
                                    
                                    $entry_id = $bookingpress_customer_data['bookingpress_entry_id'];

                                    $cypherMethod = 'AES-256-CBC';
                                    $entry_token_key = $bookingpress_user_pass;

                                    $get_db_token = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_entry_meta_value FROM {$tbl_bookingpress_entries_meta} WHERE bookingpress_entry_id = %d AND bookingpress_entry_meta_key = %s", $entry_id, 'bookingpress_customer_token' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries_meta is a table name. false alarm

                                    if( !empty( $get_db_token ) ){

                                        $customer_token_data = explode( '|BPA|', $get_db_token );

                                        $encrypted_data = $customer_token_data[0];
                                        $iv = $customer_token_data[1];

                                        $bookingpress_user_pass = openssl_decrypt( $encrypted_data, $cypherMethod, $entry_token_key, 0, $iv );
                                    }

                                }
                                $bpa_do_autologin = true;
                                $bookingpress_wpuser_id = wp_create_user($bookingpress_user_name, $bookingpress_user_pass, $bookingpress_customer_email);
   
                            }
                            if(!empty($bookingpress_customer_email) && $bpa_send_new_user_notication == 1 ) {
                                wp_send_new_user_notifications($bookingpress_wpuser_id);
                            }
                            $bookingpress_user_pass = md5($bookingpress_user_pass);
                        } elseif (! empty($bookingpress_is_wp_user_exist->ID) ) {
                            $bookingpress_wpuser_id = $bookingpress_is_wp_user_exist->ID;
                            $bookingpress_user_pass = ! empty($bookingpress_is_wp_user_exist->data->user_pass) ? $bookingpress_is_wp_user_exist->data->user_pass : '';
                        }

                        /** Auto login code start */
                        $bookingpress_allow_auto_login = $BookingPress->bookingpress_get_settings('allow_autologin_user', 'customer_setting');
                        $bookingpress_allow_auto_login = ! empty($bookingpress_allow_auto_login) ? $bookingpress_allow_auto_login : 'false';

                        if( !is_user_logged_in() && true == $bpa_do_autologin && 'true' == $bookingpress_allow_auto_login && !empty( $bookingpress_wpuser_id ) ){

                            //do_action( 'bookingpress_reset_loggin', $bookingpress_wpuser_id, $bookingpress_customer_data );

                            wp_set_auth_cookie( $bookingpress_wpuser_id, false, is_ssl() );
                            wp_set_current_user( $bookingpress_wpuser_id );
                            $user_to_pass                = wp_get_current_user();
                            do_action( 'wp_login', $bookingpress_wpuser_id, $user_to_pass );
                        }
                        /** Auto login code end */

                        /* Update WordPress user firstname and lastname */
                        $booking_user_update_meta_details['first_name'] = $bookingpress_customer_firstname;
                        $booking_user_update_meta_details['last_name'] = $bookingpress_customer_lastname;
                        if ( ! empty( $bookingpress_wpuser_id ) ) {
                            do_action( 'bookingpress_user_update_meta', $bookingpress_wpuser_id, $booking_user_update_meta_details );
                        }
                        /* Update WordPress user firstname and lastname */

                        $bookingpress_is_customer_exist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_customer_id) as total FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_email = %s AND bookingpress_user_type = 2", $bookingpress_customer_email)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                        if ($bookingpress_is_customer_exist == 0 || empty($bookingpress_customer_email)) {
                            // If customer not exists then create bookingpress customer
                            $customer_details = array(
                            'bookingpress_wpuser_id'   => $bookingpress_wpuser_id,
                            'bookingpress_user_login'  => $bookingpress_customer_email,
                            'bookingpress_user_status' => 1,
                            'bookingpress_user_type'   => 2,
                            'bookingpress_user_email'  => $bookingpress_customer_email,
                            'bookingpress_user_name'   => $bookingpress_user_name,
                            'bookingpress_customer_full_name'  => $bookingpress_customer_name,
                            'bookingpress_user_firstname' => $bookingpress_customer_firstname,
                            'bookingpress_user_lastname' => $bookingpress_customer_lastname,
                            'bookingpress_user_phone'  => $bookingpress_customer_phone,
                            'bookingpress_user_country_phone' => $bookingpress_customer_country,
                            'bookingpress_user_country_dial_code' => $bookingpress_customer_dial_code,
                            'bookingpress_user_timezone' => $bookingpress_customer_timezone,
                            'bookingpress_user_created' => current_time('mysql'),
                            'bookingpress_created_at'  => $is_front,
                            'bookingpress_created_by'  => ( is_user_logged_in() ) ? get_current_user_id() : '',
                            );

                            $wpdb->insert($tbl_bookingpress_customers, $customer_details);
                            $bookingpress_customer_id = $wpdb->insert_id;
                            $bookingpress_is_customer_create = 1;
                            do_action( 'bookingpress_after_create_customer', $bookingpress_customer_id );
                        } elseif ($bookingpress_is_customer_exist > 0 ) {
                            // Get latest customer details
                            $bookingpress_customer_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_email = %s AND bookingpress_user_type = 2 ORDER BY bookingpress_customer_id DESC", $bookingpress_customer_email), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                            $bookingpress_customer_id      = $bookingpress_customer_details['bookingpress_customer_id'];

                            $customer_update_details = array(
                            'bookingpress_wpuser_id'   => $bookingpress_wpuser_id,
                            'bookingpress_user_status' => 1,
                            );

                            $customer_update_where_condition = array(
                            'bookingpress_user_email' => $bookingpress_customer_email,
                            'bookingpress_user_type'  => 2,
                            );

                            $wpdb->update($tbl_bookingpress_customers, $customer_update_details, $customer_update_where_condition);

                            // Get all customer ids with same email address and update new customer id with all customers in appointment booking table.
                            $bookingpress_customer_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_email = %s AND bookingpress_user_type = 2 ORDER BY bookingpress_customer_id DESC", $bookingpress_customer_email), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                            if (! empty($bookingpress_customer_details) ) {
                                $bookingpress_customer_ids_arr = array();

                                foreach ( $bookingpress_customer_details as $customer_key => $customer_val ) {
                                    array_push($bookingpress_customer_ids_arr, $customer_val['bookingpress_customer_id']);
                                }

                                if (! empty($bookingpress_customer_ids_arr) ) {
                                    foreach ( $bookingpress_customer_ids_arr as $customer_id_key => $customer_id_val ) {
                                        $wpdb->update($tbl_bookingpress_appointment_bookings, array( 'bookingpress_customer_id' => $bookingpress_customer_id ), array( 'bookingpress_customer_id' => $customer_id_val ));
                                    }
                                }
                            }
                        }
                    }
                } else {
					$bookingpress_wpuser_id = $bookingpress_customer_id = $bookingpress_existing_user_id; 

                    $bookingpress_is_wp_user_exist = get_user_by('ID', $bookingpress_wpuser_id);
                    $bookingpress_user_pass        = ! empty($bookingpress_is_wp_user_exist->data->user_pass) ? $bookingpress_is_wp_user_exist->data->user_pass : '';

                    $bookingpress_is_customer_exist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_customer_id) as total FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_email = %s AND bookingpress_user_type = 2", $bookingpress_customer_email)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm

                    if ($bookingpress_is_customer_exist == 0 ) {
                        $customer_details = array(
                         'bookingpress_wpuser_id'      => $bookingpress_wpuser_id,
                         'bookingpress_user_login'     => $bookingpress_customer_email,
                         'bookingpress_user_status'    => 1,
                         'bookingpress_user_type'      => 2,
                         'bookingpress_user_email'     => $bookingpress_customer_email,
                         'bookingpress_user_name'   => $bookingpress_user_name,
                         'bookingpress_customer_full_name'  => $bookingpress_customer_name,
                         'bookingpress_user_firstname' => $bookingpress_customer_firstname,
                         'bookingpress_user_lastname'  => $bookingpress_customer_lastname,
                         'bookingpress_user_phone'     => $bookingpress_customer_phone,
                         'bookingpress_user_country_phone' => $bookingpress_customer_country,
                         'bookingpress_user_country_dial_code' => $bookingpress_customer_dial_code,
                         'bookingpress_user_timezone' => $bookingpress_customer_timezone,
                         'bookingpress_user_created'   => current_time('mysql'),
                         'bookingpress_created_at'     => $is_front,
                         'bookingpress_created_by'     => ( is_user_logged_in() ) ? get_current_user_id() : '',

                        );
                        $wpdb->insert($tbl_bookingpress_customers, $customer_details);
                        $bookingpress_customer_id = $wpdb->insert_id;
                        $bookingpress_is_customer_create = 1;
                        do_action( 'bookingpress_after_create_customer', $bookingpress_customer_id );
					}else if(($bookingpress_is_customer_exist > 0 && $is_front != 2) || $is_customer == 1 ){
                        // Get latest customer details
                        $bookingpress_customer_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_email = %s AND bookingpress_user_type = 2 ORDER BY bookingpress_customer_id DESC", $bookingpress_customer_email), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm

                        $bookingpress_customer_id = $bookingpress_customer_details['bookingpress_customer_id'];

                        $customer_update_details = array(
                        'bookingpress_wpuser_id'   => $bookingpress_wpuser_id,
                        'bookingpress_user_status' => 1,
                        );

                        $customer_update_where_condition = array(
                        'bookingpress_user_email' => $bookingpress_customer_email,
                        'bookingpress_user_type'  => 2,
                        );

                        $wpdb->update($tbl_bookingpress_customers, $customer_update_details, $customer_update_where_condition);

                        // Get all customer ids with same email address and update new customer id with all customers in appointment booking table.
                        $bookingpress_customer_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_email = %s AND bookingpress_user_type = 2 ORDER BY bookingpress_customer_id DESC", $bookingpress_customer_email), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                        if (! empty($bookingpress_customer_details) ) {
                            $bookingpress_customer_ids_arr = array();

                            foreach ( $bookingpress_customer_details as $customer_key => $customer_val ) {
                                array_push($bookingpress_customer_ids_arr, $customer_val['bookingpress_customer_id']);
                            }

                            if (! empty($bookingpress_customer_ids_arr) ) {
                                foreach ( $bookingpress_customer_ids_arr as $customer_id_key => $customer_id_val ) {
                                    $wpdb->update($tbl_bookingpress_appointment_bookings, array( 'bookingpress_customer_id' => $bookingpress_customer_id ), array( 'bookingpress_customer_id' => $customer_id_val ));
                                }
                            }
                        }
                    }
                }

				if ( ! empty( $bookingpress_customer_id ) ) {
					$bookingpress_customer_note = ! empty( $bookingpress_customer_data['bookingpress_customer_note'] ) ? $bookingpress_customer_data['bookingpress_customer_note'] : '';
					$BookingPress->update_bookingpress_customersmeta( $bookingpress_customer_id, 'customer_note', $bookingpress_customer_note );

                    $bookingpress_terms_conditions_val = !empty( $bookingpress_terms_conditions ) ? $bookingpress_terms_conditions : '';
                    $BookingPress->update_bookingpress_customersmeta( $bookingpress_customer_id, 'term_and_conditions', $bookingpress_terms_conditions_val );
				}
				

                if (! empty($bookingpress_wpuser_id) ) {
                    // Assign Bookingpress customer role to wpuser
                    $booking_user_update_meta_details          = array();
                    $booking_user_update_meta_details['roles'] = array( 'bookingpress-customer' );

                    $user = new WP_User($bookingpress_wpuser_id);
                    $user->add_role('bookingpress-customer');
                }
            }

            return array(
                'bookingpress_customer_id' => $bookingpress_customer_id,
                'bookingpress_wpuser_id'   => $bookingpress_wpuser_id,
                'bookingpress_is_customer_create' => $bookingpress_is_customer_create,
            );
        }

        function bookingpress_remove_customer_avatar_func(){
            global $wpdb;
            $response = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'remove_customer_avatar', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            if (! empty($_POST) && ! empty($_POST['upload_file_url']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_uploaded_avatar_url = esc_url_raw($_POST['upload_file_url']); // phpcs:ignore
                $bookingpress_file_name_arr       = explode('/', $bookingpress_uploaded_avatar_url);
                $bookingpress_file_name           = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
                if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
                    wp_delete_file(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
                }
            }
            die;
        }
        
        /**
         * Get existing wordpress user details
         *
         * @return void
         */
        function bookingpress_get_existing_user_details()
        {
            global $wpdb, $tbl_bookingpress_customers;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'search_user', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $response['variant']      = 'error';
            $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']          = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $response['user_details'] = '';

            $existing_user_id = ! empty($_REQUEST['existing_user_id']) ? intval($_REQUEST['existing_user_id']) : 0;
            if (! empty($existing_user_id) ) {
                $bookingpress_user_details = get_user_by('id', $existing_user_id);
                $bookingpress_user_email   = $bookingpress_user_details->data->user_email;
                $bookingpress_user_name    = $bookingpress_user_details->data->user_login;
                
                $bookingpress_user_firstname = get_user_meta($existing_user_id, 'first_name', true);
                $bookingpress_user_lastname  = get_user_meta($existing_user_id, 'last_name', true);

                $bookingpress_user_data = array(
                'username'       => esc_html($bookingpress_user_name),
                'user_email'     => esc_html($bookingpress_user_email),
                'user_firstname' => esc_html($bookingpress_user_firstname),
                'user_lastname'  => esc_html($bookingpress_user_lastname),
                );

                $response['user_details'] = $bookingpress_user_data;
                $response['variant']      = 'success';
                $response['title']        = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Users details fetched successfully.', 'bookingpress-appointment-booking');
            }

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Upload customer avatar from backend
         *
         * @return void
         */
        function bookingpress_upload_customer_avatar_func()
        {
            $return_data = array(
            'error'            => 0,
            'msg'              => '',
            'upload_url'       => '',
            'upload_file_name' => '',
            );
         //phpcs:ignore 
         $bookingpress_fileupload_obj = new bookingpress_fileupload_class( $_FILES['file'] );

            if (! $bookingpress_fileupload_obj ) {
                $return_data['error'] = 1;
                $return_data['msg']   = $bookingpress_fileupload_obj->error_message;
            }

            $bpa_check_authorization = $this->bpa_check_authentication( 'upload_customer_avatar', true, 'bookingpress_upload_customer_avatar' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingpress_fileupload_obj->check_cap          = true;
            $bookingpress_fileupload_obj->check_nonce        = true;
            $bookingpress_fileupload_obj->nonce_data         = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $bookingpress_fileupload_obj->nonce_action       = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $bookingpress_fileupload_obj->check_only_image   = true;
            $bookingpress_fileupload_obj->check_specific_ext = false;
            $bookingpress_fileupload_obj->allowed_ext        = array();

            $file_name                = isset($_FILES['file']['name']) ? current_time('timestamp') . '_' . sanitize_file_name($_FILES['file']['name']) : ''; // phpcs:ignore
            $upload_dir               = BOOKINGPRESS_TMP_IMAGES_DIR . '/';
            $upload_url               = BOOKINGPRESS_TMP_IMAGES_URL . '/';
            $bookingpress_destination = $upload_dir . $file_name;

            $check_file = wp_check_filetype_and_ext( $bookingpress_destination, $file_name );
            
            if( empty( $check_file['ext'] ) ){
                $return_data['error'] = 1;
                $return_data['upload_error'] = $upload_file;
                $return_data['msg']   = esc_html__('Invalid file extension. Please select valid file', 'bookingpress-appointment-booking');
            } else {
                $upload_file = $bookingpress_fileupload_obj->bookingpress_process_upload($bookingpress_destination);
                if ($upload_file == false ) {
                    $return_data['error'] = 1;
                    $return_data['msg']   = ! empty($upload_file->error_message) ? $upload_file->error_message : esc_html__('Something went wrong while updating the file', 'bookingpress-appointment-booking');
                } else {
                    $return_data['error']            = 0;
                    $return_data['msg']              = '';
                    $return_data['upload_url']       = $upload_url . $file_name;
                    $return_data['upload_file_name'] = $file_name;
                }
            }
            
            echo wp_json_encode($return_data);
            exit();
        }
        
        /**
         * Load customers module view file
         *
         * @return void
         */
        function bookingpress_dynamic_load_customers_view_func()
        {
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/customers/manage_customers.php';
            $bookingpress_load_file_name = apply_filters('bookingpress_modify_customer_view_file_path', $bookingpress_load_file_name);

            include $bookingpress_load_file_name;
        }
        
        /**
         * Load customers module helper variables
         *
         * @return void
         */
        function bookingpress_customer_dynamic_helper_vars_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            ?>
            var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
            ELEMENT.locale(lang)
            <?php
            do_action('bookingpress_customer_add_dynamic_helper_vars');
        }
        
        /**
         * Add more dynamic data fields to customer module
         *
         * @return void
         */
        function bookingpress_customer_dynamic_data_fields_func()
        {
            global $bookingpress_customer_vue_data_fields,$BookingPress;
            $bpa_nonce = wp_create_nonce('bpa_wp_nonce');
            $bookingpress_customer_vue_data_fields['customer']['_wpnonce'] = $bpa_nonce;
            $bookingpress_customer_vue_data_fields['bookingpress_loading'] = false;
            $bookingpress_customer_vue_data_fields['wordpress_user_id'] = '';

            // pagination data
            $bookingpress_default_perpage_option                            = $BookingPress->bookingpress_get_settings('per_page_item', 'general_setting');
            $bookingpress_customer_vue_data_fields['perPage']               = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '10';
            $bookingpress_customer_vue_data_fields['pagination_length_val'] = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '10';
      
            $bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
            $bookingpress_customer_vue_data_fields['customer']['customer_phone_country'] = $bookingpress_phone_country_option;

            $bookingpress_customer_vue_data_fields['bookingpress_tel_input_props'] = array(
                'defaultCountry' => $bookingpress_phone_country_option,
                'inputOptions' => array(
                    'placeholder' => '',
                ),
                'validCharactersOnly' => true,
            );
            $bookingpress_customer_vue_data_fields['vue_tel_mode'] = 'international';
            $bookingpress_customer_vue_data_fields['vue_tel_auto_format'] = true;

            $bookingpress_customer_vue_data_fields['ExportCustomerLite'] = false;
            $bookingpress_customer_vue_data_fields['is_mask_display']            = false;

			$bookingpress_customer_vue_data_fields['is_export_button_loader_lite']    = '0';
			$bookingpress_customer_vue_data_fields['is_export_button_disabled_lite']  = false;
            $bookingpress_customer_vue_data_fields['import_customer_modal']           = false;
            $bookingpress_customer_vue_data_fields['is_import_loader_show']           = '0';

			$bookingpress_customer_vue_data_fields['customer_export_field_list_lite'] = array(
				array(
					'name' => 'first_name',
					'text' => __( 'First Name', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'last_name',
					'text' => __( 'Last Name', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'email',
					'text' => __( 'Email', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'phone',
					'text' => __( 'Phone', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'note',
					'text' => __( 'Note', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'username',
					'text' => __( 'User Name', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'user_email',
					'text' => __( 'User Email', 'bookingpress-appointment-booking' ),
				)                
			);

            $bookingpress_import_field_data = array();
            $bookingpress_import_field_data[] = array(
                'field_key'    => 'first_name',
                'field_label'  => __( 'First Name', 'bookingpress-appointment-booking' ),
                'is_required'  => 1,
                'is_userfield' => 0,
            );
            $bookingpress_import_field_data[] = array(
                'field_key'   => 'last_name',
                'field_label' => __( 'Last Name', 'bookingpress-appointment-booking' ),
                'is_required' => 1,
                'is_userfield' => 0,
            ); 
            $bookingpress_import_field_data[] = array(
                'field_key'   => 'email',
                'field_label' => __( 'Email', 'bookingpress-appointment-booking' ),
                'is_required' => 1,
                'is_userfield' => 0,
            );  
            $bookingpress_import_field_data[] = array(
                'field_key'   => 'phone',
                'field_label' => __( 'Phone', 'bookingpress-appointment-booking' ),
                'is_required' => 0,
                'is_userfield' => 0,
            );   
            $bookingpress_import_field_data[] = array(
                'field_key'   => 'note',
                'field_label' => __( 'Note', 'bookingpress-appointment-booking' ),
                'is_required' => 0,
                'is_userfield' => 0,
            ); 
            $bookingpress_import_field_data[] = array(
                'field_key'   => 'username',
                'field_label' => __( 'User Name', 'bookingpress-appointment-booking' ),
                'is_required' => 1,
                'is_userfield' => 1,
            );
            $bookingpress_import_field_data[] = array(
                'field_key'   => 'user_email',
                'field_label' => __( 'User Email', 'bookingpress-appointment-booking' ),
                'is_required' => 1,
                'is_userfield' => 1,
            );         
            
            $bookingpress_import_fields = array();            
            foreach($bookingpress_import_field_data as $bookingpress_import_field){
                $bookingpress_import_fields[$bookingpress_import_field['field_key']] = '';
            }

            $bookingpress_customer_vue_data_fields['bookingpress_import_fields']         = $bookingpress_import_fields;
            $bookingpress_customer_vue_data_fields['bookingpress_import_fields_org']     = $bookingpress_import_fields;

            $bookingpress_customer_vue_data_fields['bookingpress_import_field_data']     = $bookingpress_import_field_data;
            $bookingpress_customer_vue_data_fields['bookingpress_import_field_data_org'] = $bookingpress_import_field_data;

            $bookingpress_customer_import_rules = array();
            $bookingpress_customer_import_rules['email'] = array(
                'required' => true,
                'message' => __( 'This fields is required.', 'bookingpress-appointment-booking' ),
                'trigger' => 'blur',
            ); 
            $bookingpress_customer_import_rules['last_name'] = array(
                'required' => true,
                'message' => __( 'This fields is required.', 'bookingpress-appointment-booking' ),
                'trigger' => 'blur',
            );
            $bookingpress_customer_import_rules['first_name'] = array(
                'required' => true,
                'message' => __( 'This fields is required.', 'bookingpress-appointment-booking' ),
                'trigger' => 'blur',
            ); 
            $bookingpress_customer_import_rules['username'] = array(
                'required' => true,
                'message' => __( 'This fields is required.', 'bookingpress-appointment-booking' ),
                'trigger' => 'blur',
            );
            $bookingpress_customer_import_rules['user_email'] = array(
                'required' => true,
                'message' => __( 'This fields is required.', 'bookingpress-appointment-booking' ),
                'trigger' => 'blur',
            );

            $bookingpress_customer_vue_data_fields['import_file_fields'] = array();
            $bookingpress_customer_vue_data_fields['import_file_name'] = "";

            $bookingpress_customer_vue_data_fields['bookingpress_customer_import_rules'] = $bookingpress_customer_import_rules;  

            $bookingpress_customer_vue_data_fields['export_checked_field_lite'] = array('first_name', 'last_name', 'email', 'phone', 'note', 'username', 'user_email');

            //$bookingpress_customer_vue_data_fields['export_checked_field_lite'] = array( 'first_name', 'last_name', 'email', 'phone', 'note', 'last_appointment', 'total_appointments' );
            $bookingpress_customer_vue_data_fields['export_checked_field_lite_org'] = $bookingpress_customer_vue_data_fields['export_checked_field_lite'];


            $bookingpress_customer_vue_data_fields['import_file_list']    = [];

            $bookingpress_customer_vue_data_fields['import_loading']      = '0';
            $bookingpress_customer_vue_data_fields['complete_import']     = '0';
            $bookingpress_customer_vue_data_fields['customers_total_count']         = '0';
            $bookingpress_customer_vue_data_fields['customers_import_count']        = '0';
            $bookingpress_customer_vue_data_fields['customers_not_import_count']    = '0';
            $bookingpress_customer_vue_data_fields['duplicate_count']     = '0';
            $bookingpress_customer_vue_data_fields['is_wordpress_user_create_on_import'] = false;


            $bookingpress_customer_vue_data_fields = apply_filters('bookingpress_modify_customer_data_fields', $bookingpress_customer_vue_data_fields);
            echo wp_json_encode($bookingpress_customer_vue_data_fields);
        }
        
        /**
         * Dynamic onload methods for customer module
         *
         * @return void
         */
        function bookingpress_customer_dynamic_on_load_methods_func()
        {
            ?>
            this.loadCustomers();
            <?php
            do_action('bookingpress_customer_add_dynamic_on_load_method');
        }
        
        /**
         * Customer module methods / functions
         *
         * @return void
         */
        function bookingpress_customer_dynamic_vue_methods_func()
        {
            global $BookingPress,$bookingpress_notification_duration;
            $bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');			
			$bookingpress_export_delimeter     = ",";
        ?>
            importCustomer(form_ref){
                const vm = this;
                vm.$refs[form_ref].validate((valid) => {  
                if(valid){                                      
                        var postData = { action:"bookingpress_import_customers", _wpnonce:"<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>" };
                        postData.import_file_fields = vm.bookingpress_import_fields;
                        postData.import_file_name   = vm.import_file_name;
                        postData.create_wp_user     = vm.is_wordpress_user_create_on_import;
                        vm.import_loading           = "1";
                        vm.complete_import          = "0";
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                        .then(function(response){                                               
                            if(response.data.variant == "success"){

                                if(typeof response.data.customers_total_count != "undefined"){
                                    vm.customers_total_count = response.data.customers_total_count;
                                }
                                if(typeof response.data.customers_import_count != "undefined"){
                                    vm.customers_import_count = response.data.customers_import_count;
                                }
                                if(typeof response.data.customers_not_import_count != "undefined"){
                                    vm.customers_not_import_count = response.data.customers_not_import_count;
                                }     
                                vm.loadCustomers();                                                           
                                setTimeout(function(){                                                                        
                                    vm.import_loading           = "0";
                                    vm.complete_import          = "1";
                                },1000);

                            }else{
                                vm.import_loading = "0";
                                vm.import_customer_modal = false;
                                vm.resetImportModal();

                                vm.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: response.data.variant,
                                    customClass: response.data.variant+"_notification",
                                    duration:2000,
                                });                                
                            }
                        }.bind(this))
                        .catch( function (error) {
                            vm.import_loading = "0";
                            vm.import_customer_modal = false;
                            vm.resetImportModal();                            
                            vm.$notify({
                                title: "'.esc_html__('Error', 'affiliatepress-affiliate-marketing').'",
                                message: "'.esc_html__('Something went wrong..', 'affiliatepress-affiliate-marketing').'",
                                type: "error",
                                customClass: "error_notification",
                                duration:2000,                        
                            });
                        });  
                    }else{
                        return false;
                    }
                });
            },
            bookingpress_remove_import_file(){
                const vm = this;
                vm.import_file_fields = [];
                vm.import_file_name   = "";
            },        
            resetImportModal(){
                const vm = this;
                vm.import_file_fields = [];                 
                vm.import_file_list = [];
                vm.import_file_name = "";
                vm.import_loading = "0";
                vm.complete_import = "0";
                vm.customers_total_count = "0";
                vm.customers_import_count = "0";
                vm.customers_not_import_count = "0";
                vm.is_wordpress_user_create_on_import = false;
                vm.bookingpress_import_fields = JSON.parse(JSON.stringify(vm.bookingpress_import_fields_org));
                vm.bookingpress_import_field_data = JSON.parse(JSON.stringify(vm.bookingpress_import_field_data_org));
            },
            checkUploadedImportFile(file){
                const vm2 = this;                
                if(file.type != 'text/csv'){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please upload csv file only', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    return false
                }else{
                    var bpa_image_size = parseInt(file.size / 50000000);
                    if(bpa_image_size > 1){
                        vm2.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Please upload maximum 50 MB file only', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });                    
                        return false
                    }
                }
            },
            bookingpress_upload_customer_import_file_func(response, file, fileList){
                const vm2 = this
                if(response != ''){
                    if(response != "" && response.error == 0){
                        vm2.import_file_fields = response.import_file_fields;
                        vm2.import_file_name = response.import_file_name;
                    }else{
                        
                        if( "undefined" != typeof response.error && 1 == response.error ){
                            vm2.bookingpress_image_upload_err( response.msg, file, fileList );
                            vm2.customer.avatar_url = ''
                            vm2.customer.avatar_name = ''
                            vm2.$refs.avatarRef.clearFiles()
                        } else {
                            vm2.customer.avatar_url = response.upload_url;
                            vm2.customer.avatar_name = response.upload_file_name;
                        }

                    }
                }
            },        
            bookingpress_import_customer_data_open(){
                const vm = this;
                vm.import_customer_modal = true;
            },
			bookingpress_export_customer_lite(){
				const vm = this;	
				vm.is_export_button_disabled_lite= true;
				vm.is_export_button_loader_lite= '1';
				var bookingpress_search_data = { search_name: vm.customerSearch,search_date_range: vm.customer_search_range }
				var customer_export_data = {
					action:'bookingpress_export_customer_data_lite',
					export_field: vm.export_checked_field_lite,
					search_data : bookingpress_search_data,
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}								
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_export_data ) )
				.then(function(response) {																		
					vm.is_export_button_disabled_lite= false;
					vm.is_export_button_loader_lite= '0';					
                    console.log(response.data.data);
					vm.close_export_customer_lite_model();									
					if(response.data.data != 'undefined') {
						var export_data;
						var csv = ''; 
						if(response.data.data != '') {
							export_data = response.data.data;						
							export_data.forEach(function(row){					    				
								csv += row.join('<?php echo esc_html( $bookingpress_export_delimeter ); ?>');
								   csv += "\n";
							});	 
						}		
						const anchor = document.createElement('a');
						anchor.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);	
						anchor.target = '_blank';
						anchor.download = 'Bookingpress-export-customer.csv';					    
						anchor.click();
					}					
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});											
			},            
            bookingpress_export_customer_data_lite(currentElement){
				const vm = this;                
				vm.ExportCustomerLite = true;
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#customer_export_model .el-dialog.bpa-dailog__small');
				}                
            },
			close_export_customer_lite_model(){
				const vm = this;
				vm.ExportCustomerLite = false;
				vm.export_checked_field_lite = JSON.parse(JSON.stringify(vm.export_checked_field_lite_org));
			},            
            toggleBusy() {
                if(this.is_display_loader == '1'){
                    this.is_display_loader = '0'
                }else{
                    this.is_display_loader = '1'
                }
            },
            handleSelectionChange(val) {
                this.multipleSelection = [];
                const customer_items_obj = val
                Object.values(customer_items_obj).forEach(val => {
                    this.multipleSelection.push({customer_id : val.customer_id})
                    this.bulk_action = 'bulk_action';
                });
            },
            handleSizeChange(val) {
                this.perPage = val
                this.loadCustomers()
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.loadCustomers()
            },        
            changeCurrentPage(perPage) {
                var total_item = this.totalItems;
                var recored_perpage = perPage;
                var select_page =  this.currentPage;                
                var current_page = Math.ceil(total_item/recored_perpage);
                if(total_item <= recored_perpage ) {
                    current_page = 1;
                } else if(select_page >= current_page ) {
                    
                } else {
                    current_page = select_page;
                }
                return current_page;
            },
            changePaginationSize(selectedPage) {     
                var total_recored_perpage = selectedPage;
                var current_page = this.changeCurrentPage(total_recored_perpage);                                        
                this.perPage = selectedPage;                    
                this.currentPage = current_page;    
                this.loadCustomers()
            },
            async loadCustomers( rest_pagination = false ) {
                this.toggleBusy(); 
                const vm = this;              
                var bookingpress_module_type = bookingpress_dashboard_filter_start_date = bookingpress_dashboard_filter_end_date = selected_date_range = ''; 
                bookingpress_module_type = sessionStorage.getItem("bookingpress_module_type");                
                bookingpress_dashboard_filter_start_date = sessionStorage.getItem("bookingpress_dashboard_filter_start_date");
                bookingpress_dashboard_filter_end_date = sessionStorage.getItem("bookingpress_dashboard_filter_end_date");
                sessionStorage.removeItem("bookingpress_module_type");
                sessionStorage.removeItem("bookingpress_dashboard_filter_start_date");
                sessionStorage.removeItem("bookingpress_dashboard_filter_end_date");
                if( true == rest_pagination ){
                    this.currentPage = 1;
                }
                if(bookingpress_module_type != '' && bookingpress_module_type == 'customer' && bookingpress_dashboard_filter_start_date != '' && bookingpress_dashboard_filter_end_date != '' ) {                        
                    selected_date_range = [bookingpress_dashboard_filter_start_date,bookingpress_dashboard_filter_end_date];
                    vm.customer_search_range = selected_date_range;
                }                    
                var bookingpress_search_data = { search_name: this.customerSearch, selected_date_range: selected_date_range }
                var postData = { action:'bookingpress_get_customers', perpage:this.perPage, currentpage:this.currentPage, search_data: bookingpress_search_data,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    this.toggleBusy();
                    if( "error" == response.data.variant ){
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval( $bookingpress_notification_duration); ?>
                        });
                    } else {
                        this.items = response.data.items;
                        this.totalItems = response.data.total;
                    }
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);
                });
            },
            open_add_customer_modal(){                
                const vm2 = this
                vm2.resetForm()
                vm2.open_customer_modal = true
            },
            get_wordpress_users(query) {
                const vm = new Vue()
                const vm2 = this	
                if (query !== '') {
                    vm2.bookingpress_loading = true;                    
                    var customer_action = { action:'bookingpress_get_wpuser',search_user_str:query,wordpress_user_id:vm2.wordpress_user_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }                    
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                    .then(function(response){
                        vm2.bookingpress_loading = false;
                        vm2.wpUsersList = response.data.users
                    }).catch(function(error){
                        console.log(error)
                    });
                } else {
                    vm2.wpUsersList = [];
                }	
            },
            saveCustomerDetails(){
                const vm2 = this
                vm2.$refs['customer'].validate((valid) => {
                    if(valid){
                        vm2.is_disabled = true
                        vm2.is_display_save_loader = '1'
                        var postdata = vm2.customer;
                        <?php do_action('bookingpress_customer_change_postdata'); ?>
                        postdata.action = 'bookingpress_add_customer';
                        postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce') ); ?>'
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
                        .then(function(response){
                            vm2.is_disabled = false
                            vm2.is_display_save_loader = '0'                            
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            if (response.data.variant == 'success') {
                                vm2.open_customer_modal = false
                                vm2.customer.update_id = response.data.customer_id
                                vm2.loadCustomers()
                            }
                            vm2.savebtnloading = false
                        }).catch(function(error){
                            vm2.is_disabled = false
                            vm2.is_display_loader = '0'
                            console.log(error);
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        });
                    }
                })
            },
            editCustomerDetails(edit_id){
                const vm2 = this
                vm2.customer.update_id = edit_id
                vm2.open_add_customer_modal()
                var customer_action = { action: 'bookingpress_get_edit_user', edit_id: edit_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                .then(function(response){
                    if(response.data.variant == 'success'){
                        var edit_customer_details = response.data.edit_data;
                        vm2.customer.update_id  = edit_customer_details.bookingpress_customer_id
                        if(edit_customer_details.bookingpress_wpuser_id != '') {                        
                            vm2.customer.wp_user = parseInt(edit_customer_details.bookingpress_wpuser_id);        
                        } else {                            
                            vm2.customer.wp_user = '';
                        }
                        vm2.wordpress_user_id = vm2.customer.wp_user;
                        vm2.customer.username = edit_customer_details.bookingpress_user_name
                        vm2.customer.firstname = edit_customer_details.bookingpress_user_firstname
                        vm2.customer.lastname = edit_customer_details.bookingpress_user_lastname
                        vm2.customer.email = edit_customer_details.bookingpress_user_email
                        vm2.customer.phone = edit_customer_details.bookingpress_user_phone
                        //vm2.customer.gender = edit_customer_details.gender
                        //vm2.customer.birthdate = edit_customer_details.birthdate
                        vm2.customer.note = edit_customer_details.note
                        //vm2.customer.avatar_list = edit_customer_details.avatar_list
                        vm2.customer.avatar_url = edit_customer_details.avatar_url
                        vm2.customer.avatar_name = edit_customer_details.avatar_name
                        vm2.customer.customer_phone_country = edit_customer_details.bookingpress_user_country_phone
                        vm2.bookingpress_tel_input_props.defaultCountry = edit_customer_details.bookingpress_user_country_phone;
                        vm2.$refs.bpa_tel_input_field._data.activeCountryCode = edit_customer_details.bookingpress_user_country_phone;
                        vm2.wpUsersList = edit_customer_details.wp_user_list
                        <?php do_action('bookingpress_customer_edit_details') ?>
                    } else {
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });                        
                    }
                }).catch(function(error){
                    console.log(error)
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            deleteCustomer(delete_id){
                const vm2 = this
                var customer_action = { action: 'bookingpress_delete_customer', delete_id: delete_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                .then(function(response){
                    vm2.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    vm2.loadCustomers()
                }).catch(function(error){
                    console.log(error)
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            bulk_actions() {
                const vm = new Vue()
                const vm2 = this
                if(this.bulk_action == "bulk_action")
                {
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please select any action.', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                }
                else
                {
                    if(this.multipleSelection.length > 0 && this.bulk_action == "delete")
                    {
                        var customer_delete_data = {
                            action: 'bookingpress_bulk_customer',
                            delete_ids: this.multipleSelection,
                            bulk_action: 'delete',
                            _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                        }
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_delete_data ) )
                        .then(function(response){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,

                            });
                            vm2.loadCustomers();
                            vm2.multipleSelection = [];
                            vm2.totalItems = vm2.items.length
                        }).catch(function(error){
                            console.log(error);
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        });
                    }
                    else
                    {    
                        if(this.multipleSelection.length == 0) {                                
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Please select one or more records.', 'bookingpress-appointment-booking'); ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        }else{
            <?php do_action('bookingpress_customer_dynamic_bulk_action'); ?>
                        }                            
                    }
                }
            },
            resetForm() {                        
                const vm2 = this                
                vm2.customer.update_id = 0;
                vm2.customer.username = '';
                vm2.customer.wp_user = '';
                vm2.customer.firstname = '';
                vm2.customer.lastname = '';
                vm2.customer.email = '';
                vm2.customer.phone = '';
                vm2.customer.note = '';
                vm2.customer.password = '';
                vm2.customer.avatar_list = [];
                vm2.customer.avatar_url = '';
                vm2.customer.avatar_name = '';
                vm2.customer.customer_phone_country = vm2.bookingpress_tel_input_props.defaultCountry;
                vm2.wordpress_user_id = '';
                vm2._wpnonce = '<?php wp_create_nonce('bpa_wp_nonce'); ?>';
                <?php do_action('bookingpress_reset_customer_fields_data') ?>
            },
            resetFilter(){
                const vm2 = this
                vm2.customerSearch =''; 
                vm2.customer_search_range = '';                          
                vm2.loadCustomers()
            },
            closeCustomerModal() {
                const vm2 = this
                vm2.$refs['customer'].resetFields()
                vm2.open_customer_modal = false
                vm2.resetForm()
            },
            bookingpress_upload_customer_avatar_func(response, file, fileList){
                const vm2 = this
                if(response != ''){
                    if( "undefined" != typeof response.error && 1 == response.error ){
                        vm2.bookingpress_image_upload_err( response.msg, file, fileList );
                        vm2.customer.avatar_url = ''
                        vm2.customer.avatar_name = ''
                        vm2.$refs.avatarRef.clearFiles()
                    } else {
                        vm2.customer.avatar_url = response.upload_url
                        vm2.customer.avatar_name = response.upload_file_name
                    }
                }
            },
            bookingpress_image_upload_limit(files, fileList){
                const vm2 = this
                    if(vm2.customer.avatar_url != ''){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Multiple files not allowed', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                }
            },
            bookingpress_image_upload_err(err, file, fileList){
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
            checkUploadedFile(file){
                const vm2 = this
                if(file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp'){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please upload jpg/png file only', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
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
            bookingpress_remove_customer_avatar() {
                const vm = this
                var upload_url = vm.customer.avatar_url
                var upload_filename = vm.customer.avatar_name
                var postData = { action:'bookingpress_remove_customer_avatar', upload_file_url: upload_url,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.customer.avatar_url = ''
                    vm.customer.avatar_name = ''
                    vm.$refs.avatarRef.clearFiles()
                }.bind(vm) )
                .catch( function (error) {
                    console.log(error);
                });
            },            
            closeBulkAction(){
                this.$refs.multipleTable.clearSelection();
                this.bulk_action = 'bulk_action';
            },
            select_date(selected_value) {
                const vm2 = this
                vm2.customer.birthdate = this.get_formatted_date(this.customer.birthdate)
            },
            get_formatted_date(iso_date){

                if( true == /(\d{2})\T/.test( iso_date ) ){
                    let date_time_arr = iso_date.split('T');
                    return date_time_arr[0];
                }
                var __date = new Date(iso_date);
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
                return formatted_date;
            },
            customer_details_save(){
                this.customer_detail_save = !this.customer_detail_save
            },
            bookingpress_get_existing_user_details(bookingpress_selected_user_id){
                const vm = this
                if(bookingpress_selected_user_id != 'add_new') {
                    var postData = { action:'bookingpress_get_existing_users_details', existing_user_id: bookingpress_selected_user_id, _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.user_details != '' || response.data.user_details != undefined){
                            vm.customer.username  = response.data.user_details.username
                            vm.customer.firstname = response.data.user_details.user_firstname
                            vm.customer.lastname = response.data.user_details.user_lastname
                            vm.customer.email = response.data.user_details.user_email
                        }
                    }.bind(vm) )
                    .catch( function (error) {
                        console.log(error);
                    });
                }
            },
            bookingpress_phone_country_change_func(bookingpress_country_obj){
                const vm = this
                var bookingpress_selected_country = bookingpress_country_obj.iso2
                let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_selected_country, true, 1 );
                if( '' != exampleNumber ){
                    vm.bookingpress_tel_input_props.inputOptions.placeholder = exampleNumber;
                }
                vm.customer.customer_phone_country = bookingpress_selected_country
                vm.customer.customer_phone_dial_code = bookingpress_country_obj.dialCode;
            },
            <?php
            do_action('bookingpress_customer_add_dynamic_vue_methods');
        }
        
        /**
         * Get all customers details for customer module
         *
         * @return void
         */
        function bookingpress_get_customer_details()
        {
            global $wpdb, $tbl_bookingpress_customers, $tbl_bookingpress_appointment_bookings,$BookingPress,$bookingpress_global_options;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_customers', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $perpage     = isset($_POST['perpage']) ? intval($_POST['perpage']) : 10; // phpcs:ignore WordPress.Security.NonceVerification
            $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1; // phpcs:ignore WordPress.Security.NonceVerification
            $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
         // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['search_data'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
            $bookingpress_search_data  = ! empty($_REQUEST['search_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data']) : array(); // phpcs:ignore
            $bookingpress_search_query = $bookingpress_search_query_join = '';

            if (! empty($bookingpress_search_data['search_name']) ) {
                $bookingpress_search_customer_name = explode(' ', $bookingpress_search_data['search_name']);
                $bookingpress_search_query        .= ' AND (';
                $search_loop_counter               = 1;
                foreach ( $bookingpress_search_customer_name as $bookingpress_search_customer_key => $bookingpress_search_customer_val ) {
                    if ($search_loop_counter > 1 ) {
                        $bookingpress_search_query .= ' OR';
                    }
                    $bookingpress_search_query .= " (bookingpress_user_login LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_user_email LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_customer_full_name LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_user_firstname LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_user_lastname LIKE '%{$bookingpress_search_customer_val}%' OR bookingpress_user_phone LIKE '%{$bookingpress_search_customer_val}%')";

                    $search_loop_counter++;
                }
                $bookingpress_search_query .= ' )';
            }
            if (! empty($bookingpress_search_data['selected_date_range']) ) {
                $bookingpress_search_date         = $bookingpress_search_data['selected_date_range'];
                $start_date                       = date('Y-m-d', strtotime($bookingpress_search_date[0]));
                $end_date                         = date('Y-m-d', strtotime($bookingpress_search_date[1]));
                $bookingpress_search_query .= " AND (bookingpress_user_created BETWEEN '".$start_date." 00:00:00' AND '".$end_date." 23:59:59')";
            }

            $bookingpress_search_query_join = apply_filters('bookingpress_customer_view_join_add_filter', $bookingpress_search_query_join);

            $bookingpress_search_query = apply_filters('bookingpress_customer_view_add_filter', $bookingpress_search_query);

            $total_customers = $wpdb->get_results("SELECT cs.bookingpress_customer_id FROM {$tbl_bookingpress_customers} as cs {$bookingpress_search_query_join} WHERE cs.bookingpress_user_type = 2 AND cs.bookingpress_user_status = 1 {$bookingpress_search_query} ",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm

            $get_customers = $wpdb->get_results("SELECT cs.* FROM {$tbl_bookingpress_customers} as cs {$bookingpress_search_query_join} WHERE cs.bookingpress_user_type = 2 AND cs.bookingpress_user_status = 1 {$bookingpress_search_query} group by cs.bookingpress_customer_id order by bookingpress_customer_id DESC LIMIT {$offset} , {$perpage}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm

            $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
            $bookingpress_default_time_format = $bookingpress_global_options_arr['wp_default_time_format'];
            $bookingpress_default_date_time_format = $bookingpress_default_date_format . ' ' . $bookingpress_default_time_format;
            
            $bookingpress_customers = array();
            if (! empty($get_customers) ) {
                $counter = 1;
                foreach ( $get_customers as $customer ) {

                    $bookingpress_avatar_url              = get_avatar_url($customer['bookingpress_wpuser_id']);
                    $bookingpress_get_existing_avatar_url = $BookingPress->get_bookingpress_customersmeta($customer['bookingpress_customer_id'], 'customer_avatar_details');
                    $bookingpress_get_existing_avatar_url = ! empty($bookingpress_get_existing_avatar_url) ? maybe_unserialize($bookingpress_get_existing_avatar_url) : array();
                    if (! empty($bookingpress_get_existing_avatar_url[0]['url']) ) {
                        $bookingpress_avatar_url = $bookingpress_get_existing_avatar_url[0]['url'];
                    } else {
                        $bookingpress_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
                    }
                    $bookingpress_customer_tmp_details                       = array();
                    $bookingpress_customer_tmp_details['id']                 = $counter;
                    $bookingpress_customer_tmp_details['customer_id']        = intval($customer['bookingpress_customer_id']);
                    $bookingpress_customer_tmp_details['customer_avatar']    = esc_url($bookingpress_avatar_url);
                    $bookingpress_customer_tmp_details['customer_username'] = stripslashes_deep($customer['bookingpress_user_name']);
                    $bookingpress_customer_tmp_details['customer_fullname'] = (!empty($customer['bookingpress_customer_full_name']) && !is_null($customer['bookingpress_customer_full_name']))?stripslashes_deep($customer['bookingpress_customer_full_name']):'';
                    $bookingpress_customer_tmp_details['customer_firstname'] = stripslashes_deep($customer['bookingpress_user_firstname']);
                    $bookingpress_customer_tmp_details['customer_lastname']  = stripslashes_deep($customer['bookingpress_user_lastname']);
                    $bookingpress_customer_tmp_details['customer_email']     = stripslashes_deep($customer['bookingpress_user_email']);
                    $bookingpress_customer_tmp_details['customer_phone']     = esc_html($customer['bookingpress_user_phone']);

                    // Fetch last appointment
                    $last_appointment_data            = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_customer_id = %d ORDER BY bookingpress_appointment_booking_id DESC LIMIT 1", $customer['bookingpress_customer_id']), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    $default_date_time_format         = get_option('date_format') . ' ' . get_option('time_format');
                    $last_appointment_booked_datetime = ! empty($last_appointment_data['bookingpress_created_at']) ? date_i18n($bookingpress_default_date_time_format, strtotime($last_appointment_data['bookingpress_created_at'])) : '-';

                    // Count total appointment
                    $total_appointments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_customer_id = %d", $customer['bookingpress_customer_id'])); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                    $bookingpress_customer_tmp_details['customer_last_appointment']  = $last_appointment_booked_datetime;
                    $bookingpress_customer_tmp_details['customer_total_appointment'] = $total_appointments;

                    $bookingpress_customers[] = $bookingpress_customer_tmp_details;
                    $counter++;
                }
            }
            $data['items'] = $bookingpress_customers;
            $data['total'] = count($total_customers);
            wp_send_json($data);
            die();
        }
                
        /**
         * Ajax request for get wordpress user except user who has role of administrator, bookingpress-staffmember, bookingpress-customer
         *
         * @return void
         */
        function bookingpress_get_wpuser()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_customers;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'search_user', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $search_user_str = ! empty( $_REQUEST['search_user_str'] ) ? sanitize_text_field( $_REQUEST['search_user_str'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $wordpress_user_id = ! empty( $_REQUEST['wordpress_user_id'] ) ? intval( $_REQUEST['wordpress_user_id'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            
			if(!empty($search_user_str)) {                    
                $args                = array(
                    'search' => '*'.$search_user_str.'*',
					'fields' => array( 'user_login','id'),
                    'role__not_in' => array( 'administrator','bookingpress-staffmember','bookingpress-customer'),
                );
                $wpusers             = get_users($args);
                $bookingpress_existing_user_data = $existing_users_data = array();
                if(!empty($wordpress_user_id)) {
                    $user_data = '';
                    $user_data = get_userdata($wordpress_user_id);                
                    if(!empty($user_data)) {        
                        $existing_users_data[] = array(
                            'value' => $user_data->ID,				
                            'label' => $user_data->user_login,
                        );                         
                    }                                
                }
                if (!empty($wpusers) ) {
                    foreach ( $wpusers as $wpuser ) {
                        $user                  = array();
                        $user['value']         = $wpuser->id;
                        $user['label']         = $wpuser->user_login;
                        $existing_users_data[] = $user;
                    }
                }         
                $bookingpress_existing_user_data[] = array(
                    'category'     => esc_html__('Select Existing User', 'bookingpress-appointment-booking'),
                    'wp_user_data' => $existing_users_data,
                );
                $response['variant']               = 'success';
                $response['users']                 = $bookingpress_existing_user_data;
                $response['title']                 = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']                   = esc_html__('Customer Data.', 'bookingpress-appointment-booking');
            }     
            wp_send_json($response);
        }
                
        /**
         * Ajax request for add customer from backend
         *
         * @return void
         */
        function bookingpress_add_customer($is_api = false)
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_customers;
            $response                = array();

            $response['customer_id'] = '';
            $response['wpuser_id']   = '';
            $response['variant']     = 'error';
            $response['title']       = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']         = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

            if($is_api == true){
                $is_error = $this->bookingpress_validate_token();
                if( is_wp_error( $is_error ) ){
                    return $is_error;
                }
            } else {
                $bpa_check_authorization = $this->bpa_check_authentication( 'add_customer', true, 'bpa_wp_nonce' );
            
                if( preg_match( '/error/', $bpa_check_authorization ) ){
                    $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                    $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                    $response['variant'] = 'error';
                    $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                    $response['msg'] = $bpa_error_msg;

                    wp_send_json( $response );
                    die;
                }
            }

            if (! empty($_REQUEST) ) {
                $bookingpress_existing_user_id = ! empty($_REQUEST['wp_user']) ? trim(sanitize_text_field($_REQUEST['wp_user'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_username         = ! empty($_REQUEST['username']) ? sanitize_text_field($_REQUEST['username']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_firstname        = ! empty($_REQUEST['firstname']) ? trim(sanitize_text_field($_REQUEST['firstname'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_lastname         = ! empty($_REQUEST['lastname']) ? trim(sanitize_text_field($_REQUEST['lastname'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_email            = ! empty($_REQUEST['email']) ? sanitize_email($_REQUEST['email']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_user_pass        = wp_generate_password(12, false);
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['search_data'] contains password and will be hashed using wp_create_user function. 
                $bookingpress_password = ! empty($_REQUEST['password']) ? $_REQUEST['password'] : $bookingpress_user_pass;

                if (strlen($bookingpress_firstname) > 255 ) {
                    $response['variant'] = 'error';
                    $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                    $response['msg'] = esc_html__('Firstname is too long...', 'bookingpress-appointment-booking');

                    if($is_api == true){
                        return $response;;
                    }

                    wp_send_json($response);
                    die();
                }

                if (strlen($bookingpress_lastname) > 255 ) {
                    $response['variant'] = 'error';
                    $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                    $response['msg'] = esc_html__('Lastname is too long...', 'bookingpress-appointment-booking');
                    if($is_api == true){
                        return $response;;
                    }
                    wp_send_json($response);
                    die();
                }

                if (strlen($bookingpress_email) > 255 ) {
                    $response['variant'] = 'error';
                    $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                    $response['msg'] = esc_html__('Email address is too long...', 'bookingpress-appointment-booking');
                    if($is_api == true){
                        return $response;;
                    }
                    wp_send_json($response);
                    die();
                }

                $bookingpress_allow_customer_create = $BookingPress->bookingpress_get_settings('allow_wp_user_create', 'customer_setting');
                $bookingpress_allow_customer_create = ! empty($bookingpress_allow_customer_create) ? $bookingpress_allow_customer_create : 'false';

                if (! empty($bookingpress_existing_user_id) && $bookingpress_existing_user_id == 'add_new' && email_exists($bookingpress_email) ) {
                    $response['variant'] = 'error';
                    $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                    $response['msg'] = esc_html__('Email address is already exists', 'bookingpress-appointment-booking');
                    if($is_api == true){
                        return $response;;
                    }
                    wp_send_json($response);
                    die();
                }
                
                if( !empty($bookingpress_username )){
                    $bookingpress_user_name = $bookingpress_username;
                } else {
                    $bookingpress_user_name = ! empty($bookingpress_firstname) ? $bookingpress_firstname : $bookingpress_email;
                }

                if (! empty($bookingpress_existing_user_id) && $bookingpress_existing_user_id == 'add_new' && ! empty($bookingpress_password) && !empty($bookingpress_user_name)) {
                    $wp_create_wp_user_id          = wp_create_user($bookingpress_user_name, $bookingpress_password, $bookingpress_email);
                    if($is_api == true){
                        if(is_wp_error($wp_create_wp_user_id)){
                            $response['variant'] = 'error';
                            $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                            $response['msg'] = $wp_create_wp_user_id->get_error_message();
                            return $response;
                        }
                    }
                    $bookingpress_existing_user_id = $wp_create_wp_user_id;
                }
                $bookingpress_phone         = ! empty($_REQUEST['phone']) ? trim(sanitize_text_field($_REQUEST['phone'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_country_phone = ! empty($_REQUEST['customer_phone_country']) ? trim(sanitize_text_field($_REQUEST['customer_phone_country'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_country_dial_code = !empty($_REQUEST['customer_phone_dial_code']) ? trim(sanitize_text_field($_REQUEST['customer_phone_dial_code'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_note          = ! empty($_REQUEST['note']) ? trim(sanitize_textarea_field($_REQUEST['note'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                $bookingpress_update_id     = ! empty($_REQUEST['update_id']) ? ( intval($_REQUEST['update_id']) ) : 0;

                if( !empty($bookingpress_phone) && !empty( $bookingpress_country_dial_code) ){

                    $customer_phone_pattern = '/(^\+'.$bookingpress_country_dial_code.')/';
                    if( preg_match($customer_phone_pattern, $bookingpress_phone) ){
                        $bookingpress_phone = preg_replace( $customer_phone_pattern, '', $bookingpress_phone) ;
                    }
                }

                $booking_user_update_meta_details['first_name'] = $bookingpress_firstname;
                $booking_user_update_meta_details['last_name']  = $bookingpress_lastname;

                if (empty($bookingpress_update_id) ) {
                    $bookingpress_customer_details = array(
                    'bookingpress_customer_name'      => $bookingpress_user_name,
                    'bookingpress_customer_phone'     => $bookingpress_phone,
                    'bookingpress_customer_firstname' => $bookingpress_firstname,
                    'bookingpress_customer_lastname'  => $bookingpress_lastname,
                    'bookingpress_customer_country'   => $bookingpress_country_phone,
                    'bookingpress_customer_email'     => $bookingpress_email,
                    'bookingpress_customer_note'      => $bookingpress_note,
                    'bookingpress_customer_phone_dial_code' => $bookingpress_country_dial_code,
                    );

                    if (! empty($bookingpress_existing_user_id) ) {
                        do_action('bookingpress_user_update_meta', $bookingpress_existing_user_id, $booking_user_update_meta_details);
                    }
                    
			        $bookingpress_customer_details = $this->bookingpress_create_customer($bookingpress_customer_details, $bookingpress_existing_user_id,2,1);

                    if (is_array($bookingpress_customer_details) && isset($bookingpress_customer_details['bookingpress_customer_id']) && isset($bookingpress_customer_details['bookingpress_wpuser_id']) ) {
                        $bookingpress_update_id        = $bookingpress_customer_details['bookingpress_customer_id'];
                        $bookingpress_existing_user_id = $bookingpress_customer_details['bookingpress_wpuser_id'];

                        do_action('bookingpress_after_update_customer', $bookingpress_update_id);
                        do_action('bookingpress_after_create_new_customer', $bookingpress_update_id);                        

                        $response['customer_id'] = $bookingpress_update_id;
                        $response['wpuser_id']   = $bookingpress_existing_user_id;
                        $response['variant']     = 'success';
                        $response['title']       = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg']         = esc_html__('Customer has been added succsssfully.', 'bookingpress-appointment-booking');
                    }
                } else {
                    $bookingpress_existing_customer_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = %d", $bookingpress_update_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                    $bookingpress_existing_wp_user_id = !empty($bookingpress_existing_customer_details['bookingpress_wpuser_id']) ? $bookingpress_existing_customer_details['bookingpress_wpuser_id'] : '';
                    if (! empty($bookingpress_existing_customer_details) ) {
                        $bookingpress_existing_user_id       = empty($bookingpress_existing_user_id) ? $bookingpress_existing_customer_details['bookingpress_wpuser_id'] : $bookingpress_existing_user_id;
                        $bookingpress_existing_users_details = get_userdata($bookingpress_existing_user_id);
                        if($bookingpress_existing_user_id != $bookingpress_existing_wp_user_id ) {
                            $userObj = new WP_User( $bookingpress_existing_wp_user_id );                   
                            $userObj->remove_role('bookingpress-customer');
                        }
                        if (! empty($bookingpress_existing_users_details->roles) && is_array($bookingpress_existing_users_details->roles) ) {
                               $bookingpress_user_roles = $bookingpress_existing_users_details->roles;
                               array_push($bookingpress_user_roles, 'bookingpress-customer');
                               $booking_user_update_meta_details['roles'] = $bookingpress_user_roles;
                        }
                        do_action('bookingpress_user_update_meta', $bookingpress_existing_user_id, $booking_user_update_meta_details);
                        

                        $bookingpress_update_fields = array(
                            'bookingpress_user_phone'     => $bookingpress_phone,
                            'bookingpress_user_country_phone' => $bookingpress_country_phone,
                            'bookingpress_wpuser_id'      => $bookingpress_existing_user_id,
                            'bookingpress_user_country_dial_code' => $bookingpress_country_dial_code,
                        );
                        if(!empty($bookingpress_user_name)){
                            $bookingpress_update_fields['bookingpress_user_name'] = $bookingpress_user_name;
                        }
                        if(!empty($bookingpress_firstname)){
                            $bookingpress_update_fields['bookingpress_user_firstname'] = $bookingpress_firstname;
                        }
                        if(!empty($bookingpress_lastname)){
                            $bookingpress_update_fields['bookingpress_user_lastname'] = $bookingpress_lastname;
                        }
                        if(!empty($bookingpress_email)){
                            $bookingpress_update_fields['bookingpress_user_email'] = $bookingpress_email;
                        }

                        $bookingpress_update_where_condition = array(
                        'bookingpress_customer_id' => $bookingpress_update_id,
                        );

                        $wpdb->update($tbl_bookingpress_customers, $bookingpress_update_fields, $bookingpress_update_where_condition);

                        $BookingPress->update_bookingpress_customersmeta($bookingpress_update_id, 'customer_note', $bookingpress_note);

                        do_action('bookingpress_after_update_customer', $bookingpress_update_id);

                        do_action('bookingpress_after_update_bookingpress_customer', $bookingpress_update_id); 
                        
                        
                        $response['customer_id'] = $bookingpress_update_id;
                        $response['wpuser_id']   = $bookingpress_existing_user_id;
                        $response['variant']     = 'success';
                        $response['title']       = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg']         = esc_html__('Customer has been updated succsssfully.', 'bookingpress-appointment-booking');
                    }
                }

                $user_image_details = array();
                if (! empty($_REQUEST['avatar_name']) && ! empty($_REQUEST['avatar_url']) ) {
                    $user_img_url  = esc_url_raw($_REQUEST['avatar_url']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
                    $user_img_name = sanitize_file_name($_REQUEST['avatar_name']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

                    $bookingpress_get_existing_avatar_details = $BookingPress->get_bookingpress_customersmeta($bookingpress_update_id, 'customer_avatar_details');
                    $bookingpress_get_existing_avatar_details = ! empty($bookingpress_get_existing_avatar_details) ? maybe_unserialize($bookingpress_get_existing_avatar_details) : array();
                    $bookingpress_get_existing_avatar_url     = ! empty($bookingpress_get_existing_avatar_details[0]['url']) ? $bookingpress_get_existing_avatar_details[0]['url'] : '';

                    if ($user_img_url != $bookingpress_get_existing_avatar_url ) {
                        global $BookingPress;
                        $upload_dir                 = BOOKINGPRESS_UPLOAD_DIR . '/';
                        $bookingpress_new_file_name = current_time('timestamp') . '_' . $user_img_name;
                        $upload_path                = $upload_dir . $bookingpress_new_file_name;

                        $bookingpress_upload_res = new bookingpress_fileupload_class( $user_img_url, true );
                        $bookingpress_upload_res->check_cap          = true;
                        $bookingpress_upload_res->check_nonce        = true;
                        $bookingpress_upload_res->nonce_data         = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
                        $bookingpress_upload_res->nonce_action       = 'bpa_wp_nonce';
                        $bookingpress_upload_res->check_only_image   = true;
                        $bookingpress_upload_res->check_specific_ext = false;
                        $bookingpress_upload_res->allowed_ext        = array();
                        $upload_response = $bookingpress_upload_res->bookingpress_process_upload( $upload_path );

                        if( true == $upload_response ){

                            $user_image_new_url   = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_new_file_name;
                            $user_image_details[] = array(
                            'name' => $bookingpress_new_file_name,
                            'url'  => $user_image_new_url,
                            );

                            $BookingPress->update_bookingpress_customersmeta($bookingpress_update_id, 'customer_avatar_details', maybe_serialize($user_image_details));

                            $bookingpress_file_name_arr = explode('/', $user_img_url);
                            $bookingpress_file_name     = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
                            if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
                                wp_delete_file(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
                            }

                            if (! empty($bookingpress_get_existing_avatar_url) ) {
                                // Remove old image and upload new image
                                $bookingpress_file_name_arr = explode('/', $bookingpress_get_existing_avatar_url);
                                $bookingpress_file_name     = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
                                if( file_exists( BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpress_file_name ) ){   
                                    wp_delete_file(BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpress_file_name);
                                }
                            }
                        }
                    }
                } else {
                    $BookingPress->update_bookingpress_customersmeta($bookingpress_update_id, 'customer_avatar_details', maybe_serialize($user_image_details));
                }
            }
            if($is_api){
                return $response;
            }else{
                wp_send_json($response);
                die();
            }
            
        }
        
        /**
         * Ajax request for get edit customer details
         *
         * @return void
         */
        function bookingpress_get_edit_user_details()
        {
            global $wpdb, $tbl_bookingpress_customers, $BookingPress;

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_customers', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $response['variant']   = 'error';
            $response['title']     = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']       = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $response['edit_data'] = array();
            if (! empty($_POST['edit_id']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_edit_id               = intval($_POST['edit_id']); // phpcs:ignore WordPress.Security.NonceVerification
                $bookingpress_edit_customer_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = %d ORDER BY bookingpress_customer_id DESC", $bookingpress_edit_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                if (! empty($bookingpress_edit_customer_details) ) {
                    $bookingpress_wpuser_id = $bookingpress_edit_customer_details['bookingpress_wpuser_id'];
                    if (! empty($bookingpress_wpuser_id) ) {
                        $bookingpress_edit_customer_details['bookingpress_wpuser_id'] = $data = ! empty(get_user_by('ID', $bookingpress_wpuser_id)) ? $bookingpress_wpuser_id : '';
                    } else {
                        $bookingpress_edit_customer_details['bookingpress_wpuser_id'] = '';
                    }
                    $bookingpress_edit_customer_details['bookingpress_user_name'] = stripslashes_deep($bookingpress_edit_customer_details['bookingpress_user_name']);
                    $bookingpress_edit_customer_details['bookingpress_user_firstname'] = stripslashes_deep($bookingpress_edit_customer_details['bookingpress_user_firstname']);
                    $bookingpress_edit_customer_details['bookingpress_user_lastname'] = stripslashes_deep($bookingpress_edit_customer_details['bookingpress_user_lastname']);
                    $bookingpress_edit_customer_details['bookingpress_user_email'] = stripslashes_deep($bookingpress_edit_customer_details['bookingpress_user_email']); 

                    // Get customers meta details
                    // $bookingpress_customer_gender    = get_user_meta( $bookingpress_wpuser_id, 'gender', true );
                    // $bookingpress_customer_birthdate = get_user_meta( $bookingpress_wpuser_id, 'birthdate', true );

                    $bookingpress_customer_note_data                   = $BookingPress->get_bookingpress_customersmeta($bookingpress_edit_id, 'customer_note');
                    $bookingpress_edit_customer_details['note']        = stripslashes_deep($bookingpress_customer_note_data);
                    $bookingpress_get_existing_avatar_list             = $BookingPress->get_bookingpress_customersmeta($bookingpress_edit_id, 'customer_avatar_details');

                    //$bookingpress_edit_customer_details['avatar_list'] = $bookingpress_get_existing_avatar_list;

                    $bookingpress_get_existing_avatar_list             = ! empty($bookingpress_get_existing_avatar_list) ? maybe_unserialize($bookingpress_get_existing_avatar_list) : array();
                    $bookingpress_edit_customer_details['avatar_name'] = ! empty($bookingpress_get_existing_avatar_list[0]['name']) ? $bookingpress_get_existing_avatar_list[0]['name'] : '';
                    $bookingpress_edit_customer_details['avatar_url']  = ! empty($bookingpress_get_existing_avatar_list[0]['url']) ? $bookingpress_get_existing_avatar_list[0]['url'] : '';

                    // $bookingpress_edit_customer_details['gender']    = ! empty( $bookingpress_customer_gender ) ? $bookingpress_customer_gender : '';
                    // $bookingpress_edit_customer_details['birthdate'] = ! empty( $bookingpress_customer_birthdate ) ? $bookingpress_customer_birthdate : '';
                    if(!empty($bookingpress_wpuser_id)) {
                        $user_data = '';                    
                        $user_data = get_userdata($bookingpress_wpuser_id);                    
                        if(!empty($user_data)) {                        
                            $bookingpress_existing_user_data[] = array(
                                'category' => __('Select Existing User','bookingpress-appointment-booking'),
                                'wp_user_data' => array(
                                    array(
                                        'value' => $user_data->ID,				
                                        'label' => $user_data->user_login,
                                    )
                                ),
                            );
                            $bookingpress_edit_customer_details['wp_user_list'] = $bookingpress_existing_user_data;                    
                        }
                    }    
                    $bookingpress_edit_customer_details = apply_filters( 'bookingpress_modify_edit_customer_details', $bookingpress_edit_customer_details, $bookingpress_edit_id );

                    $response['edit_data'] = $bookingpress_edit_customer_details;
                    $response['msg']       = esc_html__('Edit data retrieved successfully', 'bookingpress-appointment-booking');
                    $response['variant']   = 'success';
                    $response['title']     = esc_html__('Success', 'bookingpress-appointment-booking');

                }
            }

            echo wp_json_encode($response);
            exit();
        }

        
        /**
         * Delete customer function
         *
         * @param  mixed $delete_id   Customer ID which you want to delete
         * @return void
         */
        function bookingpress_delete_customer( $delete_id, $is_api = false )
        {
            global $wpdb, $tbl_bookingpress_customers,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs;
            $response              = array();


            if($is_api == true){
                $is_error = $this->bookingpress_validate_token();
                if( is_wp_error( $is_error ) ){
                    return $is_error;
                }
            } else {
                $bpa_check_authorization = $this->bpa_check_authentication( 'delete_customer', true, 'bpa_wp_nonce' );
            
                if( preg_match( '/error/', $bpa_check_authorization ) ){
                    $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                    $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                    $response['variant'] = 'error';
                    $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                    $response['msg'] = $bpa_error_msg;

                    wp_send_json( $response );
                    die;
                }
            }
            
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $return              = false;

            if (! empty($_POST['delete_id']) || intval($delete_id) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $delete_customer_id = ! empty($_POST['delete_id']) ? intval($_POST['delete_id']) : intval($delete_id); // phpcs:ignore WordPress.Security.NonceVerification
                do_action('bookingpress_before_delete_customer', $delete_customer_id);
                if (! empty($delete_customer_id) ) {
                    $is_deleted = $wpdb->delete( $tbl_bookingpress_customers, array( 'bookingpress_customer_id' => $delete_customer_id ) );

                    if ($is_deleted) {
                        $wpdb->delete($tbl_bookingpress_appointment_bookings, array( 'bookingpress_customer_id' => $delete_customer_id ));
                        $wpdb->delete($tbl_bookingpress_payment_logs, array( 'bookingpress_customer_id' => $delete_customer_id ));
    
                        $response['variant'] = 'success';
                        $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg']     = esc_html__('Customer has been deleted successfully.', 'bookingpress-appointment-booking');
    
                        $return = true;
                    }else{
                        $response['variant'] = 'error';
                        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                        $response['msg']     = esc_html__('Customer has been deleted successfully.', 'bookingpress-appointment-booking');
    
                        $return = false;
                    }
                    
                }
            }
            

            if (! empty($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_delete_customer' ) { // phpcs:ignore
                echo wp_json_encode($response);
                exit();
            }

            return $return;
        }

        
        /**
         * Customer module bulk actions
         *
         * @return void
         */
        function bookingpress_bulk_action()
        {
            global $BookingPress;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'delete_customer', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            if (! empty($_POST['bulk_action']) && sanitize_text_field($_POST['bulk_action']) == 'delete' ) { // phpcs:ignore
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['delete_ids'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
                $delete_ids = ! empty($_POST['delete_ids']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['delete_ids']) : array(); // phpcs:ignore
                if (! empty($delete_ids) ) {
                    foreach ( $delete_ids as $delete_key => $delete_val ) {
                        $delete_customer_id = $delete_val['customer_id'];
                        $return             = $this->bookingpress_delete_customer($delete_customer_id);
                        if ($return ) {
                            $response['variant'] = 'success';
                            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                            $response['msg']     = esc_html__('Customer has been deleted successfully.', 'bookingpress-appointment-booking');
                        }
                    }
                }
            }
            echo wp_json_encode($response);
            exit();
        }
    }
}
global $bookingpress_customers;
$bookingpress_customers                = new bookingpress_customers();

<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_appointment') ) {
    class bookingpress_appointment Extends BookingPress_Core
    {
        function __construct()
        {
            add_action('wp_ajax_bookingpress_get_appointments', array( $this, 'bookingpress_get_appointment' ));
            //add_action('wp_ajax_bookingpress_get_search_employess', array( $this, 'bookingpress_search_employess' ));
            add_action('wp_ajax_bookingpress_delete_appointment', array( $this, 'bookingpress_delete_appointment' ));
            //add_action('wp_ajax_bookingpress_edit_appointment', array( $this, 'bookingpress_edit_appointment' ));
            add_action('wp_ajax_bookingpress_bulk_appointment', array( $this, 'bookingpress_bulk_appointment' ));

            add_action('bookingpress_appointments_dynamic_vue_methods', array( $this, 'bookingpress_appointment_dynamic_vue_methods_func' ), 10);
            add_action('bookingpress_appointments_dynamic_on_load_methods', array( $this, 'bookingpress_appointment_dynamic_on_load_methods_func' ), 10);
            add_action('bookingpress_appointments_dynamic_data_fields', array( $this, 'bookingpress_appointment_dynamic_data_fields_func' ), 10);
            add_action('bookingpress_appointments_dynamic_directives', array( $this, 'bookingpress_appointment_dynamic_directives' ), 10);
            add_action('bookingpress_appointments_dynamic_helper_vars', array( $this, 'bookingpress_appointment_dynamic_helper_func' ), 10);
            add_action('bookingpress_appointments_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_appointment_view_func' ), 10);

            add_action( 'admin_init', array( $this, 'bookingpress_appointment_vue_data_fields' ) );

            if( !is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
                add_action( 'bookingpress_modify_appointment_data_fields', array( $this, 'bookingpress_appointment_customer_data_fields'), 10 );
                add_action( 'bookingpress_modify_calendar_data_fields', array( $this, 'bookingpress_appointment_customer_data_fields'), 10 );
                add_action( 'bookingpress_appointment_add_dynamic_vue_methods', array( $this, 'bookingpress_appointment_customer_vue_methods'), 10 );
                add_action( 'bookingpress_add_dynamic_vue_methods_for_calendar', array( $this, 'bookingpress_appointment_customer_vue_methods'), 10 );
            }

            add_action('wp_ajax_bookingpress_generate_share_url', array($this, 'bookingpress_generate_share_url_func'));
            add_action('wp_ajax_bookingpress_share_generated_url', array($this, 'bookingpress_share_generated_url_func'));
            add_filter('bookingpress_modify_email_notification_content', array( $this, 'bookingpress_modify_email_content_func' ), 11, 4);
			add_filter('bookingpress_modify_allowed_email_notification_flag', array($this, 'bookingpress_modify_allowed_email_notification_flag_func'));

            add_action( 'wp_ajax_bookingpress_get_wp_page_list', array( $this, 'bookingpress_get_wp_page_list_func' ) );
            add_action( 'wp_ajax_bpa_fetch_customer_details', array( $this, 'bookingpress_bpa_fetch_customer_details' ) );

            add_filter('bookingpress_appointment_add_view_field', array( $this, 'bookingpress_appointment_add_view_field_func' ), 10, 2);
            
        }

        function bookingpress_bpa_fetch_customer_details(){
            global $wpdb, $BookingPress;
			$response = array();

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

            $response['variant'] = 'success';
			$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');
			$response['appointment_customers_details'] = array();
			$bookingpress_customer_id = ! empty( $_REQUEST['customer_id'] ) ? intval( $_REQUEST['customer_id'] ) : '';
			$bookingpress_appointment_customers_details = array();
			if(!empty($bookingpress_customer_id)) {                  
                $response['variant'] = 'success';
                $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');
                $response['appointment_customers_details'] = array();
                $bookingpress_appointment_customers_details = $BookingPress->bookingpress_get_appointment_customer_list('',$bookingpress_customer_id);			
                $response['appointment_customers_details'] = $bookingpress_appointment_customers_details;
            }    
			$response['appointment_customers_details'] = $bookingpress_appointment_customers_details;
			echo wp_json_encode($response);
			exit;
        }

        function bookingpress_appointment_add_view_field_func($appointment, $get_appointment){

            if(!empty($appointment['payment_method'])){
                global $BookingPress;
                $payment_gateway_label_temp = $payment_gateway = $appointment['payment_method'];
                if(!empty($payment_gateway) && ($payment_gateway == 'on-site' || $payment_gateway == 'on site' || $payment_gateway == 'On Site') ) {
                    $appointment['payment_method'] = $BookingPress->bookingpress_get_customize_settings('locally_text','booking_form');
                } elseif(!empty($payment_gateway) && $payment_gateway != 'manual') {
                    $appointment['payment_method'] = $BookingPress->bookingpress_get_customize_settings($payment_gateway.'_text','booking_form');
                    if(empty($appointment['payment_method'])) {
                        $appointment['payment_method'] = $payment_gateway_label_temp;
                    }
                }  
            }
            return $appointment;
        }
        
        /**
         * bookingpress_get_wp_page_list_func
         *
         * @return void
         */
        function bookingpress_get_wp_page_list_func()
        {
            global $wpdb, $BookingPress;
			$response                       = array();
            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_wp_page_list', true, 'bpa_wp_nonce' );
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
            $search_user_str = ! empty( $_REQUEST['search_page_str'] ) ? ( sanitize_text_field($_REQUEST['search_page_str'] )) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            if(!empty($search_user_str)){
                $args = array(
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    's'    		 =>  $search_user_str,
		            'search_columns '  => 'post_title',
                    'order'          => 'ASC'
                );
                $pages = get_posts( $args );
                $bpa_new_wp_pages= array();
                foreach($pages as $bpa_wp_page_key => $bpa_wp_page_val){
                    $bpa_new_wp_pages[] = array(
                        'id' => $bpa_wp_page_val->ID,
                        'title' => $bpa_wp_page_val->post_title,
                        'url' => get_permalink(get_page_by_path($bpa_wp_page_val->post_name)),
                    );
                }
                if(!empty($bpa_new_wp_pages)) {                  
                    $response['variant'] = 'success';
                    $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');
                    $response['all_page_list'] = $bpa_new_wp_pages;
                }   
            } 
			echo wp_json_encode($response);
			exit;

        }
        
        /**
         * Allow share appointment url email notification without appointment booked
         *
         * @param  mixed $bookingpress_is_allowed_email_notification
         * @return void
         */
        function bookingpress_modify_allowed_email_notification_flag_func($bookingpress_is_allowed_email_notification){
			if(!empty($_POST['share_url_form_data']['generated_url'])){ //phpcs:ignore
				$bookingpress_is_allowed_email_notification = 1;
			}
			return $bookingpress_is_allowed_email_notification;
		}
		
		/**
		 * Function for modify email content of share appointment url
		 *
		 * @param  mixed $template_content
		 * @param  mixed $bookingpress_appointment_data
		 * @param  mixed $notification_name
		 * @return void
		 */
		function bookingpress_modify_email_content_func($template_content, $bookingpress_appointment_data,$notification_name = '',$template_type=''){
			if(!empty($_POST['share_url_form_data']['generated_url'])){ //phpcs:ignore
				$bpa_generated_link = $_POST['share_url_form_data']['generated_url']; //phpcs:ignore
				$template_content = str_replace('%share_appointment_url%', $bpa_generated_link, $template_content);
			}

			return $template_content;
		}
		
		/**
		 * Function for share generated URL through email notification
		 *
		 * @return void
		 */
		function bookingpress_share_generated_url_func(){
			global $wpdb, $BookingPress, $bookingpress_email_notifications;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'share_generated_url', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$response['variant'] = 'success';
			$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['msg'] = esc_html__('Notification sent successfully', 'bookingpress-appointment-booking');

			$bpa_share_url_form_data = !empty($_POST['share_url_form_data']) ? $_POST['share_url_form_data'] : array(); // phpcs:ignore
			if(!empty($bpa_share_url_form_data)){
				$is_email_sharing = !empty($bpa_share_url_form_data['email_sharing']) ? $bpa_share_url_form_data['email_sharing'] : false;
				if($is_email_sharing == "true"){
					$bpa_share_email_addresses = !empty($bpa_share_url_form_data['sharing_email']) ? $bpa_share_url_form_data['sharing_email'] : '';
					if(!empty($bpa_share_email_addresses)){
						$bpa_share_email_addresses = explode(',', $bpa_share_email_addresses);
						foreach($bpa_share_email_addresses as $share_email_key => $share_email_val){
                            $bookingpress_cc_emails = array();
                            $bookingpress_cc_emails = apply_filters('bookingpress_add_customer_cc_email_address', $bookingpress_cc_emails, 'Share Appointment URL');
							$bookingpress_email_notifications->bookingpress_send_email_notification('customer', 'Share Appointment URL', 0, $share_email_val, $bookingpress_cc_emails); 
						}
                        $bookingpress_cc_emails = array();
                        $bookingpress_admin_emails = esc_html($BookingPress->bookingpress_get_settings('admin_email', 'notification_setting'));
                        $bookingpress_admin_emails = apply_filters('bookingpress_filter_admin_email_data', $bookingpress_admin_emails, 0, 'Share Appointment URL');
                        if (! empty($bookingpress_admin_emails) ) {
                            $bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, 'Share Appointment URL');
                            $bookingpress_admin_emails = explode(',', $bookingpress_admin_emails);
                            foreach ( $bookingpress_admin_emails as $admin_email_key => $admin_email_val ) {
                                $bookingpress_email_notifications->bookingpress_send_email_notification('employee', 'Share Appointment URL', 0, $admin_email_val, $bookingpress_cc_emails);
                            }
						}
					}
				}

				do_action('bpa_externally_share_appointment_url', $bpa_share_url_form_data);
			}

			echo wp_json_encode($response);
			exit;
		}
        
        /**
         * Generate appointment share url
         *
         * @return void
         */
        function bookingpress_generate_share_url_func(){
			global $wpdb, $BookingPress;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_share_url_generated', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$response['variant'] = 'success';
			$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['msg'] = esc_html__('Share URL generated successfully', 'bookingpress-appointment-booking');
			$response['generated_url'] = array();

			$bpa_share_url_form_data = !empty($_POST['share_url_form_data']) ? $_POST['share_url_form_data'] : array(); // phpcs:ignore
			if(!empty($bpa_share_url_form_data)){
				$bpa_final_generated_url = !empty($bpa_share_url_form_data['generated_url']) ? $bpa_share_url_form_data['generated_url'] : '';
				
				$bpa_selected_page_id = !empty($bpa_share_url_form_data['selected_page_id']) ? intval($bpa_share_url_form_data['selected_page_id']) : 0;
				if(!empty($bpa_selected_page_id)){
					$bpa_final_generated_url = get_permalink($bpa_selected_page_id);
				}
                $bpa_selected_page_wp_id = !empty($bpa_share_url_form_data['selected_page_wp_id']) ? intval($bpa_share_url_form_data['selected_page_wp_id']) : 0;
				if(!empty($bpa_selected_page_wp_id)){
					$bpa_final_generated_url = get_permalink($bpa_selected_page_wp_id);
				}

				$bpa_selected_service_id = !empty($bpa_share_url_form_data['selected_service_id']) ? intval($bpa_share_url_form_data['selected_service_id']) : 0;
				if(!empty($bpa_selected_service_id)){
					$bpa_final_generated_url = add_query_arg('s_id', $bpa_selected_service_id, $bpa_final_generated_url);
				}
				
				$bpa_final_generated_url = apply_filters('bookingpress_filter_generated_share_url_externally', $bpa_final_generated_url, $bpa_share_url_form_data);

				$bpa_allow_modify = (!empty($bpa_share_url_form_data['allow_customer_to_modify']) && ($bpa_share_url_form_data['allow_customer_to_modify'] == "true")) ? true : false;
				if($bpa_allow_modify){
					$bpa_final_generated_url = add_query_arg('allow_modify', 1, $bpa_final_generated_url);
				}else{
                    $bpa_final_generated_url = add_query_arg('allow_modify', 0, $bpa_final_generated_url);
                }

				$response['generated_url'] = $bpa_final_generated_url;
			}

			echo wp_json_encode($response);
			exit;
		}
        
        /**
         * Load appointment page default data variables
         *
         * @return void
         */
        function bookingpress_appointment_vue_data_fields(){
            global $bookingpress_appointment_vue_data_fields, $bookingpress_global_options,$BookingPress,$wpdb, $tbl_bookingpress_customers,$bookingpress_appointment_status_array;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            $bookingpress_pagination  = $bookingpress_options['pagination'];

            $bookingpress_pagination_arr      = json_decode($bookingpress_pagination, true);
            $bookingpress_pagination_selected = $bookingpress_pagination_arr[0];

            $bookingpress_appointment_status_array    = $bookingpress_options['appointment_status'];
            $bookingpress_appointment_vue_data_fields = array(
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
                'items'                      => array(),
                'multipleSelection'          => array(),
                'appointment_customers_list' => array(),
                'appointment_services_list'  => array(),
                'perPage'                    => $bookingpress_pagination_selected,
                'totalItems'                 => 0,
                'pagination_selected_length' => $bookingpress_pagination_selected,
                'pagination_length'          => $bookingpress_pagination,
                'currentPage'                => 1,
                'search_appointment'         => '',
                'search_appointment_id'         => '',
                'appointment_date_range'     => array( date('Y-m-d', strtotime('-3 Day')), date('Y-m-d', strtotime('+3 Day')) ),
                'search_customer_name'       => '',
                'search_service_name'        => '',
                'search_service_employee'    => '',
                'search_appointment_status'  => '',
                'search_customer_list'       => '',
                'search_status'              => $bookingpress_appointment_status_array,
                'appointment_time_slot'      => array(),
                'appointment_status'         => $bookingpress_appointment_status_array,
                'service_employee'           => array(),
                'appointment_services_data'  => array(),
                'modal_loader'               => 1,
                'rules'                      => array(
                    'appointment_selected_customer' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select customer', 'bookingpress-appointment-booking'),
                            'trigger'  => 'change',
                        ),
                    ),
                    'appointment_selected_service'  => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select service', 'bookingpress-appointment-booking'),
                            'trigger'  => 'change',
                        ),
                    ),
                    'appointment_booked_date'       => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select booking date', 'bookingpress-appointment-booking'),
                            'trigger'  => 'change',
                        ),
                    ),
                    'appointment_booked_time'       => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please select booking time', 'bookingpress-appointment-booking'),
                            'trigger'  => 'change',
                        ),
                    ),
                ),
                'appointment_formdata'       => array(
                    'appointment_selected_customer'     => '',
                    'appointment_selected_staff_member' => '',
                    'appointment_selected_service'      => '',
                    'appointment_booked_date'           => date('Y-m-d', current_time('timestamp')),
                    'appointment_booked_time'           => '',
                    'appointment_booked_end_time'       => '',
                    'appointment_internal_note'         => '',
                    'appointment_send_notification'     => false,
                    'appointment_status'                => '1',
                    'appointment_update_id'             => 0,
                ),
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
                'savebtnloading'             => false,
                'open_appointment_modal'     => false,
                'is_display_loader'          => '0',
                'is_disabled'                => false,
                'is_display_save_loader'     => '0',
            );
        }
        
        /**
         * Load appointment page view file
         *
         * @return void
         */
        function bookingpress_dynamic_load_appointment_view_func()
        {
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/appointment/manage_appointment.php';
            $bookingpress_load_file_name = apply_filters('bookingpress_modify_appointment_view_file_path', $bookingpress_load_file_name);

            include $bookingpress_load_file_name;
        }

        function bookingpress_appointment_customer_vue_methods(){
            global $bookingpress_notification_duration;
            ?>
            closeCustomerModal() {
                const vm2 = this
                vm2.$refs['customer'].resetFields()
                vm2.open_customer_modal = false
                vm2.resetForm()
            },
            bpa_select_customer(){
                const vm = this;
				if(vm.appointment_formdata.appointment_selected_customer == "add_new"){
					vm.open_add_customer_modal();
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
            bookingpress_get_existing_user_details(bookingpress_selected_user_id){
                const vm = this
                if(bookingpress_selected_user_id != 'add_new') {
                    var postData = { action:'bookingpress_get_existing_users_details', existing_user_id: bookingpress_selected_user_id, _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.user_details != '' || response.data.user_details != undefined){
                            vm.customer.firstname = response.data.user_details.user_firstname
                            vm.customer.lastname = response.data.user_details.user_lastname
                            vm.customer.email = response.data.user_details.user_email
                            vm.customer.username = response.data.user_details.username
                        }
                    }.bind(vm) )
                    .catch( function (error) {
                        console.log(error);
                    });
                }
            },
            bookingpress_get_customers_details(selected_customer_id = ""){
				const vm = this
				var customer_details_action = { action: 'bpa_fetch_customer_details',customer_id:selected_customer_id, _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_details_action ) )
				.then(function(response){
					vm.appointment_customers_list = response.data.appointment_customers_details;
					if(selected_customer_id != ""){
						setTimeout(function(){
							vm.appointment_formdata.appointment_selected_customer = ''+selected_customer_id;
						}, 500);
					}
				}).catch(function(error){
					console.log(error)
				});				
			},
            open_add_customer_modal(){
                const vm2 = this
				vm2.wpUsersList = [];
                vm2.resetCustomerForm();
                vm2.open_customer_modal = true;
            },
            resetCustomerForm() {                        
                const vm2 = this                
                vm2.customer.update_id = 0;
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
				vm2.wordpress_user_id ='';
                vm2._wpnonce = '<?php wp_create_nonce('bpa_wp_nonce'); ?>';
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
            get_wordpress_users(query) {
                const vm = new Vue()
                const vm2 = this	
                if (query !== '') {
                    vm2.boookingpress_loading = true;                    
                    var customer_action = { action:'bookingpress_get_wpuser',search_user_str:query,wordpress_user_id:vm2.wordpress_user_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }                    
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                    .then(function(response){
						vm2.boookingpress_loading = false;						
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
                        postdata.action = 'bookingpress_add_customer';
						postdata._wpnonce ='<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
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
								vm2.bookingpress_get_customers_details(response.data.customer_id);
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
            <?php
        }

        function bookingpress_appointment_customer_data_fields( $bookingpress_appointment_vue_data_fields ){
            global $BookingPress, $bookingpress_global_options;

            $bookingpress_edit_customers = 0;
            if ( current_user_can( 'bookingpress_customers' ) ) {
				$bookingpress_edit_customers = 1;
			}

            $bookingpress_appointment_vue_data_fields['bookingpress_edit_customers'] = $bookingpress_edit_customers;
            $bookingpress_appointment_vue_data_fields['open_customer_modal'] = false;
            $bookingpress_appointment_vue_data_fields['cusShowFileList'] = false;
            $bookingpress_appointment_vue_data_fields['wpUsersList'] = array();
            $bookingpress_appointment_vue_data_fields['is_display_loader'] = '0';

            $bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
            $bookingpress_appointment_vue_data_fields['customer']['customer_phone_country'] = $bookingpress_phone_country_option;

            $bookingpress_appointment_vue_data_fields['bookingpress_tel_input_props'] = array(
                'defaultCountry' => $bookingpress_phone_country_option,
                'inputOptions' => array(
                    'placeholder' => '',
                ),
                'validCharactersOnly' => true,
            );

            $bookingpress_appointment_vue_data_fields['vue_tel_mode'] = 'international';
            $bookingpress_appointment_vue_data_fields['vue_tel_auto_format'] = true;

            $bookingpress_appointment_vue_data_fields['open_customer_modal'] = false;
			$bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_country_list = $bookingpress_options['country_lists'];
			$bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
			
			$bookingpress_appointment_vue_data_fields['customer'] = array(
				'avatar_url' => '',
				'avatar_name' => '',
				'avatar_list' => array(),
				'wp_user' => null,
				'firstname' => '',
				'lastname' => '',
				'email' => '',
                'username' => '',
				'phone' => '',
				'customer_phone_country' => $bookingpress_phone_country_option,
				'customer_phone_dial_code' => '',
				'note' => '',
				'update_id' => 0,
				'_wpnonce' => '',
				'password' => '',
			);

            $bookingpress_allow_customer_create = $BookingPress->bookingpress_get_settings('allow_wp_user_create', 'customer_setting');
            $bookingpress_allow_customer_create = ! empty($bookingpress_allow_customer_create) ? $bookingpress_allow_customer_create : 'false';
            $bookingpress_allow_customer_create = $bookingpress_allow_customer_create == 'true' ? true : false;

            $appointment_customer_rules = array(
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
            );

            $bookingpress_appointment_vue_data_fields['rules'] = array_merge( $bookingpress_appointment_vue_data_fields['rules'], $appointment_customer_rules );

            return $bookingpress_appointment_vue_data_fields;

        }
        
        /**
         * Add more data variables to appointment page
         *
         * @return void
         */
        function bookingpress_appointment_dynamic_data_fields_func()
        {
            global $wpdb,$BookingPress,$bookingpress_appointment_vue_data_fields,$tbl_bookingpress_customers, $tbl_bookingpress_categories, $tbl_bookingpress_services;

            // Fetch customers details
            $bookingpress_customer_details           = $wpdb->get_results('SELECT bookingpress_customer_id, bookingpress_user_firstname, bookingpress_user_lastname, bookingpress_user_email FROM ' . $tbl_bookingpress_customers . ' WHERE bookingpress_user_type = 2 AND bookingpress_user_status = 1', ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
            $bookingpress_customer_selection_details = array();
            $bookingpress_customer_name              = '';
            foreach ( $bookingpress_customer_details as $bookingpress_customer_key => $bookingpress_customer_val ) {
                $bookingpress_customer_name = ( $bookingpress_customer_val['bookingpress_user_firstname'] == '' && $bookingpress_customer_val['bookingpress_user_lastname'] == '' ) ? $bookingpress_customer_val['bookingpress_user_email'] : $bookingpress_customer_val['bookingpress_user_firstname'] . ' ' . $bookingpress_customer_val['bookingpress_user_lastname'];

                $bookingpress_customer_selection_details[] = array(
                'text'  => stripslashes_deep($bookingpress_customer_name),
                'value' => $bookingpress_customer_val['bookingpress_customer_id'],
                );
            }


            // Fetch staff members details
            $bookingpress_staff_members_details          = $wpdb->get_results('SELECT * FROM ' . $tbl_bookingpress_customers . ' WHERE bookingpress_user_type = 1 AND bookingpress_user_status = 1', ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
            $bookingpress_staff_member_selection_details = array();
            foreach ( $bookingpress_staff_members_details as $bookingpress_staff_members_key => $bookingpress_staff_members_val ) {
                $bookingpress_staff_member_selection_details[] = array(
                'text'  => $bookingpress_staff_members_val['bookingpress_user_login'],
                'value' => $bookingpress_staff_members_val['bookingpress_customer_id'],
                );
            }
            $bookingpress_appointment_vue_data_fields['appointment_staff_members_list'] = $bookingpress_staff_member_selection_details;
            $bookingpress_appointment_vue_data_fields['service_employee']               = $bookingpress_staff_member_selection_details;

            // Fetch Services Details
            $bookingpress_services_details2   = array();
            $bookingpress_services_details2[] = array(
            'category_name'     => '',
            'category_services' => array(
            '0' => array(
                        'service_id'    => 0,
                        'service_name'  => esc_html__('Select service', 'bookingpress-appointment-booking'),
                        'service_price' => '',
            ),
            ),
            );
            $bookingpress_services_details    = $BookingPress->get_bookingpress_service_data_group_with_category();
            $bookingpress_services_details2   = array_merge($bookingpress_services_details2, $bookingpress_services_details);
            $bookingpress_appointment_vue_data_fields['appointment_services_list'] = $bookingpress_services_details2;
            $bookingpress_appointment_vue_data_fields['appointment_services_data'] = $bookingpress_services_details;

            $bpa_all_services = $BookingPress->bookingpress_retrieve_all_services();
            $bookingpress_appointment_vue_data_fields['bpa_all_services_data'] = $bpa_all_services;

            $bookingpress_default_status_option = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['appointment_status'] = ! empty($bookingpress_default_status_option) ? $bookingpress_default_status_option : '1';

            // Pagination data
            $bookingpress_default_perpage_option                               = $BookingPress->bookingpress_get_settings('per_page_item', 'general_setting');
            $bookingpress_appointment_vue_data_fields['perPage']               = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '20';
            $bookingpress_appointment_vue_data_fields['pagination_length_val'] = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '20';

            $default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
            if (! empty($default_daysoff_details) ) {
                $default_daysoff_details                                   = array_map(
                    function ( $date ) {
                        return date('Y-m-d', strtotime($date));
                    },
                    $default_daysoff_details
                );
                $bookingpress_appointment_vue_data_fields['disabledDates'] = $default_daysoff_details;
            } else {
                $bookingpress_appointment_vue_data_fields['disabledDates'] = '';
            }
            $bookingpress_appointment_vue_data_fields['bookingpress_loading'] = false;
            $bookingpress_appointment_vue_data_fields['customer_id'] = '';
            
            
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['_wpnonce'] = wp_create_nonce('bpa_wp_nonce');

            $bookingpress_appointment_vue_data_fields['bookingpress_previous_row_obj'] = '';

            //Get default booking form shortcode page
			$bpa_default_booking_page = get_page_by_path('book-appointment');
            $bpa_default_booking_page_id = '';
            if(!empty($bpa_default_booking_page->ID)){
                $bpa_default_booking_page_id = $bpa_default_booking_page->ID;
            }
            $bpa_default_booking_page_url = get_permalink($bpa_default_booking_page_id);

			//Get all wp pages
			$bpa_new_wp_pages = array();
			$bpa_wp_pages = get_pages();
			if(!empty($bpa_wp_pages)){
				foreach($bpa_wp_pages as $bpa_wp_page_key => $bpa_wp_page_val){
					$bpa_new_wp_pages[] = array(
						'id' => $bpa_wp_page_val->ID,
						'title' => $bpa_wp_page_val->post_title,
						'url' => get_permalink(get_page_by_path($bpa_wp_page_val->post_name)),
					);
				}
			}
			$bookingpress_appointment_vue_data_fields['all_share_pages'] = $bpa_new_wp_pages;
            $bookingpress_appointment_vue_data_fields['all_share_pages_list'] = array();

            $bookingpress_appointment_vue_data_fields['share_url_form'] = array(
				'selected_page_id' => $bpa_default_booking_page_id,
                'selected_page_wp_id' => '',
                'selected_service_id' => '',
				'generated_url' => $bpa_default_booking_page_url,
				'allow_customer_to_modify' => false,
				'email_sharing' => false,
				'sharing_email' => '',
			);

            $bookingpress_appointment_vue_data_fields['bpa_share_url_modal'] = false;
			$bookingpress_appointment_vue_data_fields['is_share_button_loader'] = '0';
			$bookingpress_appointment_vue_data_fields['is_share_button_disabled'] = true;
            $bookingpress_appointment_vue_data_fields['is_mask_display'] = false;

            $bookingpress_appointment_vue_data_fields['share_url_rules'] = array(
                'selected_service_id' => array(
                    array(
                        'required' => true,
                        'message'  => esc_html__('Please select service', 'bookingpress-appointment-booking'),
                        'trigger'  => 'change',
                    ),
                ),
                'selected_page_wp_id' => array(
                    array(
                        'required' => true,
                        'message'  => esc_html__('Please select page', 'bookingpress-appointment-booking'),
                        'trigger'  => 'change',
                    ),
                ),
                'sharing_email' => array(
                    array(
                        'required' => true,
                        'message'  => esc_html__('Please enter email address', 'bookingpress-appointment-booking'),
                        'trigger'  => 'change',
                    ),
                ),
            );

            $bookingpress_appointment_vue_data_fields                                     = apply_filters('bookingpress_modify_appointment_data_fields', $bookingpress_appointment_vue_data_fields);
            echo wp_json_encode($bookingpress_appointment_vue_data_fields);
        }
        
        /**
         * Add appointment page helper variables
         *
         * @return void
         */
        function bookingpress_appointment_dynamic_helper_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            ?>
            var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
            ELEMENT.locale(lang)
            const createSortable = (el, options, vnode) => {
                return Sortable.create(el, {
                    ...options
                });
            };
            const sortable = {
                name: 'sortable',
                bind(el, binding, vnode) {
                    const table = el;
                    table._sortable = createSortable(table.querySelector("tbody"), binding.value, vnode);
                }
            };
            <?php
        }
        
        /**
         * Add directives for appointment page
         *
         * @return void
         */
        function bookingpress_appointment_dynamic_directives()
        {
            echo esc_html('sortable').',';
        }
                
        /**
         * Appointment page onload functions
         *
         * @return void
         */
        function bookingpress_appointment_dynamic_on_load_methods_func()
        {
            ?>
            this.loadAppointments().catch(error => {
                console.error(error)
            })
            <?php
            do_action('bookingpress_add_appointment_dynamic_on_load_methods');
        }
        
        /**
         * Appointment page methods / functions
         *
         * @return void
         */
        function bookingpress_appointment_dynamic_vue_methods_func()
        {
            global $BookingPress,$bookingpress_notification_duration;
            $bookingpress_default_status_option = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');
            $bookingpress_default_status_option = ! empty($bookingpress_default_status_option) ? $bookingpress_default_status_option : '1';
            ?>
            bpa_enable_service_share(){
                const vm = this
                if(vm.share_url_form.selected_service_id != '' && vm.share_url_form.email_sharing == true && vm.share_url_form.sharing_email != '' && vm.share_url_form.selected_page_wp_id!=''){
                    vm.is_share_button_disabled = false;
                    vm.bookingpress_generate_share_url();
                }else{
                    vm.is_share_button_disabled = true;
                    vm.bookingpress_generate_share_url();
                }
            },
            handel_appointment_changes( {column, prop, order} ){


                if (!order) {
                    this.bpa_appointment_sort_by = '';
                    this.bpa_appointment_sort_order = '';
                } else {
                    this.bpa_appointment_sort_by = prop;
                    this.bpa_appointment_sort_order = order === 'descending' ? 'DESC' : 'ASC';
                }

                this.loadAppointments();
            },
            bpa_share_appointment_url(share_url_form){
				const vm = this
                vm.$refs[share_url_form].validate((valid) => {
                    if (valid) {
                        vm.is_share_button_loader = 1;
                        vm.is_share_button_disabled = true;
                        var appointment_generate_url_details = {
                            action:'bookingpress_share_generated_url',
                            share_url_form_data: vm.share_url_form,
                            _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
                        }				
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_generate_url_details ) )
                        .then(function(response) {			
                            if(response.data.variant == "success"){
                                vm.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: 'success',
                                    customClass: 'success_notification',
                                });	
                                vm.is_share_button_loader = 0;
                                vm.is_share_button_disabled = false;
                                vm.bpa_share_url_modal = false;
                            }else{
                                vm.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: 'error',
                                    customClass: 'error_notification',
                                });	
                                vm.is_share_button_loader = 0;
                                vm.is_share_button_disabled = false;
                                vm.bpa_share_url_modal = false;
                            }
                        }).catch(function(error){
                            vm.is_share_button_loader = 0;
                            vm.is_share_button_disabled = false;
                            vm.bpa_share_url_modal = false;
                            console.log(error);
                            vm.$notify({
                                title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
                                message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
                                type: 'error',
                                customClass: 'error_notification',
                            });
                        });
                    }
                });
			},
            bookingpress_generate_share_url(){
				const vm = this
				var appointment_generate_url_details = {
					action:'bookingpress_generate_share_url',
					share_url_form_data: vm.share_url_form,
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}				
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_generate_url_details ) )
				.then(function(response) {			
					if(response.data.variant == "success"){
                        //vm.bpa_enable_service_share();
						vm.share_url_form.generated_url = response.data.generated_url;
					}else{
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: 'error',
							customClass: 'error_notification',
						});	
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
			bookingpress_copy_share_url(){
				const vm = this;
				var bpa_generated_url = vm.share_url_form.generated_url;
				var bookingpress_dummy_elem = document.createElement("textarea");
				document.body.appendChild(bookingpress_dummy_elem);
				bookingpress_dummy_elem.value = bpa_generated_url;
				bookingpress_dummy_elem.select();
				document.execCommand("copy");
				document.body.removeChild(bookingpress_dummy_elem);
				vm.$notify(
				{ 
					title: '<?php esc_html_e('Success', 'bookingpress-appointment-booking'); ?>',
					message: '<?php echo esc_html_e('URL copied successfully.','bookingpress-appointment-booking'); ?>',
					type: 'success',
					customClass: 'success_notification',
					duration:<?php echo intval($bookingpress_notification_duration); ?>,
				});
			},
			bookingpress_share_url_modal(currentElement){
				const vm = this;
				vm.bpa_share_url_modal = true;

				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#appointment_share_url .el-dialog.bpa-dialog--share-url');
				}
			},
            toggleBusy() {
                if(this.is_display_loader == '1'){
                    this.is_display_loader = '0'
                }else{
                    this.is_display_loader = '1'
                }
            },
            handleSelectionChange(val) {
                const appointment_items_obj = val
                this.multipleSelection = [];
                Object.values(appointment_items_obj).forEach(val => {
                    this.multipleSelection.push({appointment_id : val.appointment_id})
                    this.bulk_action = 'bulk_action';
                });
            },
            handleSizeChange(val) {
                this.perPage = val
                this.loadAppointments()
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.loadAppointments()
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
                this.loadAppointments()
            },
            saveAppointmentBooking(bookingAppointment){
                const vm = new Vue()
                const vm2 = this
                    this.$refs[bookingAppointment].validate((valid) => {
                        <?php do_action('bookingpress_modify_request_after_validation'); ?>
                        if (valid) {
                        vm2.is_disabled = true
                        vm2.is_display_save_loader = '1'
                        var postData = { action:'bookingpress_save_appointment_booking',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                        postData.appointment_data = JSON.stringify(vm2.appointment_formdata);
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                        .then(function(response){                            
                            vm2.is_disabled = false
                            vm2.is_display_save_loader = '0'
                            if(response.data.variant != 'error') { 
                                vm2.closeAppointmentModal()    
                                vm2.loadAppointments()
                            }
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });                        
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
                });
            },
            async loadAppointments( resetPagination = false ) {
                this.toggleBusy();
                const vm2 = this
                var bookingpress_module_type = bookingpress_dashboard_filter_start_date = bookingpress_dashboard_filter_end_date = bookingpress_dashboard_filter_appointment_status = '';

                if( true == resetPagination ){
                    vm2.currentPage = 1;
                }
                bookingpress_module_type = sessionStorage.getItem("bookingpress_module_type");                
                bookingpress_dashboard_filter_start_date = sessionStorage.getItem("bookingpress_dashboard_filter_start_date");
                bookingpress_dashboard_filter_end_date = sessionStorage.getItem("bookingpress_dashboard_filter_end_date");
                bookingpress_dashboard_filter_appointment_status = sessionStorage.getItem("bookingpress_dashboard_filter_appointment_status");                
                sessionStorage.removeItem("bookingpress_module_type");
                sessionStorage.removeItem("bookingpress_dashboard_filter_start_date");
                sessionStorage.removeItem("bookingpress_dashboard_filter_end_date");
                sessionStorage.removeItem("bookingpress_dashboard_filter_appointment_status");                
                if(bookingpress_module_type != '' && bookingpress_module_type == 'appointment' && bookingpress_dashboard_filter_start_date != '' && bookingpress_dashboard_filter_end_date != '' ) {
                    if(bookingpress_dashboard_filter_appointment_status == '1') {
                    this.search_appointment_status = '1';                    
                    }  else if(bookingpress_dashboard_filter_appointment_status == '2') {
                        this.search_appointment_status = '2'; 
                    }
                    var appointment_date_range = [bookingpress_dashboard_filter_start_date,bookingpress_dashboard_filter_end_date];
                    this.appointment_date_range = appointment_date_range;
                }                
                var bookingpress_search_data = { 'search_appointment':this.search_appointment,'selected_date_range': this.appointment_date_range, 'customer_name': this.search_customer_name,'service_name': this.search_service_name,'appointment_status': this.search_appointment_status, 'search_appointment_id' : this.search_appointment_id}  
                
            <?php do_action('bookingpress_appointment_add_post_data'); ?>

                var postData = { action:'bookingpress_get_appointments', perpage:this.perPage, currentpage:this.currentPage, search_data: bookingpress_search_data,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>',sort_by: this.bpa_appointment_sort_by, sort_order: this.bpa_appointment_sort_order};

                <?php do_action('bookingpress_modify_appointment_send_data'); ?>

                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm2.toggleBusy();
                    if( "error" == response.data.variant ){
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval( $bookingpress_notification_duration); ?>
                        });
                    } else {
                        vm2.items = response.data.items;
                        vm2.totalItems = response.data.totalItems;
                        vm2.form_field_data = response.data.form_field_data;
                    }
                    <?php do_action('bookingpress_modify_appointment_success_response_data'); ?>
                }.bind(this) )
                .catch( function (error) {                    
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            loadAppointmentsWithoutLoader() {
                const vm2 = this
                var bookingpress_module_type = bookingpress_dashboard_filter_start_date = bookingpress_dashboard_filter_end_date = bookingpress_dashboard_filter_appointment_status = '';
                bookingpress_module_type = sessionStorage.getItem("bookingpress_module_type");                
                bookingpress_dashboard_filter_start_date = sessionStorage.getItem("bookingpress_dashboard_filter_start_date");
                bookingpress_dashboard_filter_end_date = sessionStorage.getItem("bookingpress_dashboard_filter_end_date");
                bookingpress_dashboard_filter_appointment_status = sessionStorage.getItem("bookingpress_dashboard_filter_appointment_status");                
                sessionStorage.removeItem("bookingpress_module_type");
                sessionStorage.removeItem("bookingpress_dashboard_filter_start_date");
                sessionStorage.removeItem("bookingpress_dashboard_filter_end_date");
                sessionStorage.removeItem("bookingpress_dashboard_filter_appointment_status");                
                if(bookingpress_module_type != '' && bookingpress_module_type == 'appointment' && bookingpress_dashboard_filter_start_date != '' && bookingpress_dashboard_filter_end_date != '' ) {
                    if(bookingpress_dashboard_filter_appointment_status == '1') {
                    this.search_appointment_status = '1';                    
                    }  else if(bookingpress_dashboard_filter_appointment_status == '2') {
                        this.search_appointment_status = '2'; 
                    }
                    var appointment_date_range = [bookingpress_dashboard_filter_start_date,bookingpress_dashboard_filter_end_date];
                    this.appointment_date_range = appointment_date_range;
                }
                var bookingpress_search_data = { 'search_appointment':this.search_appointment,'selected_date_range': this.appointment_date_range, 'customer_name': this.search_customer_name,'service_name': this.search_service_name,'appointment_status': this.search_appointment_status}  
                
                <?php do_action('bookingpress_appointment_add_post_data'); ?>

                var postData = { action:'bookingpress_get_appointments', perpage:this.perPage, currentpage:this.currentPage, search_data: bookingpress_search_data,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>', sort_by: this.bpa_appointment_sort_by, sort_order: this.bpa_appointment_sort_order};
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    if( "error" == response.data.variant ){
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval( $bookingpress_notification_duration); ?>
                        });
                    } else {
                        vm2.items = response.data.items;
                        vm2.totalItems = response.data.totalItems;
                    }
                }.bind(this) )
                .catch( function (error) {                    
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                });
            },
            bookingpress_get_staff_members(event){
                const vm = this
                var selected_service_id = event
                var postData = { action:'bookingpress_get_service_staff_members_data', selected_service: selected_service_id, _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.appointment_formdata.appointment_selected_staff_member = ''
                    vm.appointment_staff_members_list = response.data
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);
                });
            },
                   
            bookingpress_loader_hide() {
                this.modal_loader = 0
            },            
            
            deleteAppointment(index, row) {
                const vm = new Vue()
                const vm2 = this
                var delete_id = row.appointment_id
                var appointment_delete_data = { action: 'bookingpress_delete_appointment', delete_id: delete_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_delete_data ) )
                .then(function(response){
                    vm2.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    vm2.loadAppointments()
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
            },
            bulk_actions() {                
                const vm = new Vue()
                const vm2 = this
                if(vm2.bulk_action == "bulk_action")
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
                        var appointment_delete_data = {
                            action:'bookingpress_bulk_appointment',
                            app_delete_ids: this.multipleSelection,
                            bulk_action: 'delete',
                            _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>',
                        }
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_delete_data ) )
                        .then(function(response){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm2.loadAppointments();
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
                        } else {
            <?php do_action('bookingpress_appointment_dynamic_bulk_action'); ?>
                        }
                    }
                }
            },
            isOnlyNumber: function(evt) {
                const vm = this
                this.search_appointment_id = event.target.value.replace(/[^0-9]/g, "");
            },
            resetFilter(){
                const vm = this
                vm.search_appointment = '';
                vm.appointment_date_range = ''
                vm.search_customer_name = ''
                vm.search_service_name = ''
                vm.search_appointment_status = ''
                vm.search_appointment_id = ''
                <?php 
                do_action('bookingpress_appointment_reset_filter');
                ?>
                vm.loadAppointments()
            },
            resetForm() {
                    const vm2 = this
                    vm2.appointment_formdata.appointment_selected_customer = ''
                    vm2.appointment_formdata.appointment_selected_staff_member= ''
                    vm2.appointment_formdata.appointment_selected_service = ''
                    vm2.appointment_formdata.appointment_booked_date = '<?php echo esc_html(date('Y-m-d', current_time('timestamp'))); ?>';
                    vm2.appointment_formdata.appointment_booked_time = ''
                    vm2.appointment_formdata.appointment_internal_note = ''
                    vm2.appointment_formdata.appointment_send_notification = ''
                    vm2.appointment_formdata.appointment_status = '<?php echo esc_html($bookingpress_default_status_option); ?>'
                    vm2.appointment_formdata.appointment_update_id = 0                
            },
            closeAppointmentModal() {
                const vm2= this
                vm2.$refs['appointment_formdata'].resetFields()
                vm2.resetForm()
                vm2.appointment_customers_list = [];
                vm2.open_appointment_modal = false                
                <?php do_action('bookingpress_add_appointment_model_reset') ?>
            },                
            closeBulkAction(){
                this.$refs.multipleTable.clearSelection();
                this.bulk_action = 'bulk_action';
            },
            select_date(selected_value) {
                this.appointment_formdata.appointment_booked_date = this.get_formatted_date(this.appointment_formdata.appointment_booked_date)
                this.bookingpress_set_time_slot()
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
            search_range_change(selected_value) {                
                /*if(selected_value != null) {
                    this.appointment_date_range[0] = this.get_formatted_date(this.appointment_date_range[0])                
                    this.appointment_date_range[1] = this.get_formatted_date(this.appointment_date_range[1])
                }*/
            },            
            bookingpress_set_time_slot() {
                const vm = this
                var service_id = vm.appointment_formdata.appointment_selected_service;
                var selected_appointment_date = vm.appointment_formdata.appointment_booked_date;
                vm.appointment_formdata.appointment_booked_time = '' ;
                if(service_id != '' &&  selected_appointment_date != '') {
                    <?php 
                        do_action('bookingpress_after_selecting_service_at_backend');
                    ?>
                    var postData = { action:'bookingpress_set_appointment_time_slot', service_id: 
                    service_id,selected_date:selected_appointment_date ,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data != undefined || response.data != [])
                        {                                
                            vm.appointment_time_slot = response.data;                            
                        }
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });                    
                } else {
                    if(service_id == '' || service_id == undefined || service_id == 'undefined'){
                        vm.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Please select service to get available date and time slots.', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    }
                    vm.appointment_time_slot = '';
                }
            },
            bookingpress_change_status(update_id, selectedValue){
                const vm2 = this;
                let rowIndex;
                vm2.items.forEach(function(currentValue, index, arr){
                    if(update_id == currentValue.appointment_id){
                        vm2.items[index].change_status_loader = 1;
                        rowIndex = index;
                    }
                });
                var postData = { action:'bookingpress_change_upcoming_appointment_status', update_appointment_id: update_id, appointment_new_status: selectedValue, _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    if( "error" == response.data.variant ){
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                        if( "undefined" != typeof rowIndex && "undefined" != typeof vm2.items && "undefined" != typeof vm2.items[rowIndex] ){
                            vm2.items[rowIndex].change_status_loader = 0;
                        }
                        return false;
                    }
                    if(response.data == "0" || response.data == 0){
                        vm2.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Appointment already booked for this slot', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                        vm2.loadAppointmentsWithoutLoader();
                        return false;
                    }else{
                        vm2.$notify({
                            title: '<?php esc_html_e('Success', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Appointment status changed successfully', 'bookingpress-appointment-booking'); ?>',
                            type: 'success',
                            customClass: 'success_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                        vm2.loadAppointmentsWithoutLoader();
                    }
                }.bind(this) )
                .catch( function (error) {
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,                        
                    });
                });
            },
            bookingpress_get_search_customer_list(query){
				const vm = new Vue()
				const vm2 = this	
				if (query !== '') {
					vm2.bookingpress_loading = true;                    
					var customer_action = { action:'bookingpress_get_search_customer_list',search_user_str:query,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }                    
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
					.then(function(response){
						vm2.bookingpress_loading = false;
						vm2.search_customer_list = response.data.appointment_customers_details
					}).catch(function(error){
						console.log(error)
					});
				} else {
					vm2.search_customer_list = [];
				}	
			},
            bookingpress_get_customer_list(query){
                const vm = new Vue()
                const vm2 = this	
                if (query !== '') {
                    vm2.bookingpress_loading = true;                    
                    var customer_action = { action:'bookingpress_get_customer_list',search_user_str:query,customer_id:vm2.customer_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }                    
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                    .then(function(response){
                        vm2.bookingpress_loading = false;
                        vm2.appointment_customers_list = response.data.appointment_customers_details
                    }).catch(function(error){
                        console.log(error)
                    });
                } else {
                    vm2.appointment_customers_list = [];
                }	
            },
            bookingpress_get_page_list(query){
                const vm = new Vue();
                const vm2 = this;	
                if (query !== '') {
                    vm2.bookingpress_loading = true;                    
                    var customer_action = { action:'bookingpress_get_wp_page_list',search_page_str:query,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }          
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                    .then(function(response){
                        vm2.bookingpress_loading = false;
                        vm2.all_share_pages_list = response.data.all_page_list
                    }).catch(function(error){
                        console.log(error);
                    });
                } else {
                    vm2.all_share_pages_list = [];
                }	
                
            },
            bookingpress_full_row_clickable(row, $el, events){
                const vm = this
                <?php do_action('bookingpress_appointment_full_row_clickable'); ?>
                vm.$refs.multipleTable.toggleRowExpansion(row);
            },
            bookingpress_row_expand(row, expanded){
                const vm = this
                if(vm.bookingpress_previous_row_obj != ''){
                    vm.$refs.multipleTable.toggleRowExpansion(vm.bookingpress_previous_row_obj, false);
                    if(vm.bookingpress_previous_row_obj != row){
                        vm.$refs.multipleTable.toggleRowExpansion(vm.bookingpress_previous_row_obj);
                        vm.bookingpress_previous_row_obj = row;
                    }else{
                        if(expanded.length == undefined){
                            vm.$refs.multipleTable.toggleRowExpansion(row);
                        }
                        vm.bookingpress_previous_row_obj = '';
                    }
                }else{
                    if(expanded.length == undefined){
                        vm.$refs.multipleTable.toggleRowExpansion(row);
                    }
                    vm.bookingpress_previous_row_obj = row;
                }
            },
            bookingpress_set_time(event,time_slot_data) {
                const vm = this
                if(event != '' && time_slot_data != '') {
                    for (let x in time_slot_data) {                      
                        var slot_data_arr = time_slot_data[x];                        
                        for(let y in slot_data_arr) {
                            var time_slot_data_arr = slot_data_arr[y];
                            for(let m in time_slot_data_arr) {                            
                                var data_arr  = time_slot_data_arr[m];
                                if(data_arr.store_start_time != undefined && data_arr.store_end_time != undefined && data_arr.store_start_time == event) {   
                                    vm.appointment_formdata.appointment_booked_end_time = data_arr.store_end_time;
                                    <?php do_action('bookingpress_admin_add_appointment_after_select_timeslot'); ?>
                                }
                            }                                                    
                        }                      
                    }                    
                }
            },
            <?php
            do_action('bookingpress_appointment_add_dynamic_vue_methods');
        }
        
        /**
         * Ajax request for get appointments
         *
         * @return void
         */
        function bookingpress_get_appointment()
        {
            global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options,$tbl_bookingpress_form_fields;

            $response              = array();
            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_appointments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $sort_by     = isset($_POST['sort_by']) ? esc_html($_POST['sort_by']) : '';
            $sort_order  = isset($_POST['sort_order'])? esc_html($_POST['sort_order']) : 'DESC';

            $perpage     = isset($_POST['perpage']) ? intval($_POST['perpage']) : 10; // phpcs:ignore WordPress.Security.NonceVerification
            $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1; // phpcs:ignore WordPress.Security.NonceVerification
            $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
            $bookingpress_search_data        = ! empty($_REQUEST['search_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST['search_data'] contains array and sanitized properly using appointment_sanatize_field function
            $bookingpress_search_query       = '';

            if ( ! in_array( $sort_order, array( 'ASC', 'DESC' ), true ) ) {
                $sort_order = 'DESC';
            }

            $bookingpress_appointment_sortable_columns = array(
                'created_date' => 'bpa.bookingpress_created_at',
                'staff_member_name' => 'bpa.bookingpress_staff_first_name',
                'service_name' => 'bpa.bookingpress_service_name',
                'customer_name'  => 'bpa.bookingpress_customer_firstname',
                'appointment_date' => 'bpa.bookingpress_appointment_date',
                'appointment_duration'  => 'CASE 
                                            WHEN bpa.bookingpress_service_duration_unit = "d" THEN bpa.bookingpress_service_duration_val * 1440
                                            WHEN bpa.bookingpress_service_duration_unit = "h" THEN bpa.bookingpress_service_duration_val * 60
                                            WHEN bpa.bookingpress_service_duration_unit = "m" THEN bpa.bookingpress_service_duration_val
                                            ELSE bpa.bookingpress_service_duration_val
                                        END'
            );
            
            $sort_extra_columns = '';
            $sort_extra_stmt = '';

            if( $BookingPress->bpa_is_pro_active() && version_compare( $wpdb->db_version(), '8.0.0', '>=') ){
                global $bookingpress_service_extra;
                if( !empty( $bookingpress_service_extra ) && method_exists( $bookingpress_service_extra, 'bookingpress_check_service_extra_module_activation' ) && $bookingpress_service_extra->bookingpress_check_service_extra_module_activation() ){
                    $bookingpress_appointment_sortable_columns['appointment_duration'] = 'total_duration';
                    $sort_extra_stmt = 'WITH RECURSIVE seq AS (
                        SELECT 0 AS n
                        UNION ALL SELECT n + 1 FROM seq WHERE n < 20
                    )';
                    $sort_extra_columns = ",(
                        CASE
                            WHEN bpa.bookingpress_service_duration_unit = 'd'
                                THEN bpa.bookingpress_service_duration_val * 1440
                            WHEN bpa.bookingpress_service_duration_unit = 'h'
                                THEN bpa.bookingpress_service_duration_val * 60
                            ELSE bpa.bookingpress_service_duration_val
                        END
                        +
                        IFNULL((
                            SELECT SUM(
                                CASE
                                    WHEN JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_extra_service_details.bookingpress_extra_service_duration_unit'))) = 'h'
                                        THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_extra_service_details.bookingpress_extra_service_duration'))) AS UNSIGNED) * 
                                            CAST(JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_selected_qty'))) AS UNSIGNED) * 60

                                    WHEN JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_extra_service_details.bookingpress_extra_service_duration_unit'))) = 'm'
                                        THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_extra_service_details.bookingpress_extra_service_duration'))) AS UNSIGNED) * 
                                            CAST(JSON_UNQUOTE(JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, '].bookingpress_selected_qty'))) AS UNSIGNED)

                                    ELSE 0
                                END
                            )
                            FROM seq
                            WHERE JSON_EXTRACT(bpa.bookingpress_extra_service_details, CONCAT('$[', seq.n, ']')) IS NOT NULL
                        ), 0)
                    ) AS total_duration";
                }
            }

            $order_by_column = 'bpa.bookingpress_appointment_booking_id';

            if ( isset( $bookingpress_appointment_sortable_columns[ $sort_by ] ) ) {
                $order_by_column = $bookingpress_appointment_sortable_columns[ $sort_by ];
            }

            $bookingpress_search_query_where = 'WHERE 1=1 ';

            if (! empty($bookingpress_search_data) ) {
                if (! empty($bookingpress_search_data['search_appointment']) ) {
                    $bookingpress_search_string = $bookingpress_search_data['search_appointment'];
                    $bookingpress_search_result = $wpdb->get_results($wpdb->prepare('SELECT bookingpress_customer_id  FROM ' . $tbl_bookingpress_customers . " WHERE bookingpress_customer_full_name LIKE %s OR bookingpress_user_firstname LIKE %s OR bookingpress_user_lastname LIKE %s OR bookingpress_user_login LIKE %s AND (bookingpress_user_type = 1 OR bookingpress_user_type = 2)", '%' . $bookingpress_search_string . '%', '%' . $bookingpress_search_string . '%', '%' . $bookingpress_search_string . '%' , '%' . $bookingpress_search_string . '%'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                    if (! empty($bookingpress_search_result) ) {
                        $bookingpress_customer_ids = array();
                        foreach ( $bookingpress_search_result as $item ) {
                            $bookingpress_customer_ids[] = $item['bookingpress_customer_id'];
                        }
                        $bookingpress_search_user_id      = implode(',', $bookingpress_customer_ids);
                        $search_query_where = ' AND (bpa.bookingpress_customer_id IN (';
                        $search_query_where .= rtrim( str_repeat( '%d,', count( $bookingpress_customer_ids ) ), ',' ). ') )';
                        array_unshift( $bookingpress_customer_ids, $search_query_where );
                        $search_query_where_str = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_customer_ids  );

                        $bookingpress_search_query_where .= $search_query_where_str;
                    } else {
                        $bookingpress_search_query_where_clause = apply_filters( 'bookingpress_modify_search_query_where_after_service_name', '', $bookingpress_search_string );
                        $bookingpress_search_query_where .= $wpdb->prepare( "AND ( bpa.bookingpress_service_name LIKE %s {$bookingpress_search_query_where_clause} )", "%{$bookingpress_search_string}%" );
                    }
                }
                if (! empty($bookingpress_search_data['selected_date_range']) ) {
                    $bookingpress_search_date         = $bookingpress_search_data['selected_date_range'];
                    $start_date                       = date('Y-m-d', strtotime($bookingpress_search_date[0]));
                    $end_date                         = date('Y-m-d', strtotime($bookingpress_search_date[1]));
                    $bookingpress_search_query_where .= $wpdb->prepare( " AND (bpa.bookingpress_appointment_date BETWEEN %s AND %s)", $start_date, $end_date );
                }
                if (! empty($bookingpress_search_data['customer_name']) ) {
                    $bookingpress_search_name         = $bookingpress_search_data['customer_name'];

                    $search_name_query = ' AND ( bpa.bookingpress_customer_id IN(';
                    $search_name_query .= rtrim( str_repeat( '%d,', count( $bookingpress_search_name) ), ',' ).' ) )';
                    array_unshift( $bookingpress_search_name, $search_name_query );
                    $search_name_query_str = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_search_name );
                    
                    $bookingpress_search_query_where .= $search_name_query_str;
                }
                if (! empty($bookingpress_search_data['service_name']) ) {
                    $bookingpress_search_name         = $bookingpress_search_data['service_name'];

                    $search_name_query = ' AND ( bpa.bookingpress_service_id IN(';
                    $search_name_query .= rtrim( str_repeat( '%d,', count( $bookingpress_search_name) ), ',' ).' ) )';
                    array_unshift( $bookingpress_search_name, $search_name_query );
                    $search_name_query_str = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_search_name );
                    $search_name_query_str = apply_filters( 'bookingpress_modify_search_query_where_after_service_id', $search_name_query_str, $bookingpress_search_data['service_name'] );
                    $bookingpress_search_query_where .= $search_name_query_str;

                }
                if (! empty($bookingpress_search_data['appointment_status'] && $bookingpress_search_data['appointment_status'] != 'all') ) {
                    $bookingpress_search_name         = $bookingpress_search_data['appointment_status'];
                    $bookingpress_search_query_where .= $wpdb->prepare( " AND (bpa.bookingpress_appointment_status = %s)", $bookingpress_search_name );
                }
                if(!empty( $bookingpress_search_data['search_appointment_id'])) {
                    $bookingpress_search_id = $bookingpress_search_data['search_appointment_id'];
                    $bookingpress_search_query_where .= $wpdb->prepare( " AND (bpa.bookingpress_booking_id = %d)", $bookingpress_search_id );
                    
                }
                $bookingpress_search_query_where = apply_filters('bookingpress_appointment_view_add_filter', $bookingpress_search_query_where, $bookingpress_search_data);
            }
            $bpa_left_join_data = apply_filters( 'bookingpress_modify_left_join_table_with_appointment_page', '' );

            $get_total_appointments = $wpdb->get_results("SELECT bpa.* FROM {$tbl_bookingpress_appointment_bookings} bpa {$bpa_left_join_data} {$bookingpress_search_query}{$bookingpress_search_query_where} group by bpa.bookingpress_appointment_booking_id", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
            

            
            $total_appointments = $wpdb->get_results("$sort_extra_stmt SELECT bpa.* $sort_extra_columns FROM {$tbl_bookingpress_appointment_bookings} bpa {$bpa_left_join_data} {$bookingpress_search_query}{$bookingpress_search_query_where} group by bpa.bookingpress_appointment_booking_id order by {$order_by_column} {$sort_order} LIMIT {$offset} , {$perpage}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
            
            
            $appointments  = $bookingpress_formdata = array();

            if (! empty($total_appointments) ) {
                $counter = 1;

                $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
                $bookingpress_default_time_format = $bookingpress_global_options_arr['wp_default_time_format'];
                $bookingpress_default_date_time_format = $bookingpress_default_date_format . ' ' . $bookingpress_default_time_format;
                $bookingpress_appointment_status_arr = $bookingpress_global_options_arr['appointment_status'];
                
                $bookingpress_form_field_data = $wpdb->get_results("SELECT `bookingpress_form_field_name`,`bookingpress_field_label` FROM {$tbl_bookingpress_form_fields}",ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm

                foreach($bookingpress_form_field_data as $key=> $value) {                    
                    $bookingpress_formdata[$value['bookingpress_form_field_name']] = stripslashes_deep($value['bookingpress_field_label']);
                }                

                foreach ( $total_appointments as $get_appointment ) {
                    $appointment                   = array();
                    $appointment['id']             = $counter;
                    $appointment_id                = intval($get_appointment['bookingpress_appointment_booking_id']);
                    $appointment['appointment_id'] = $appointment_id;
                    $appointment['payment_id'] = $get_appointment['bookingpress_payment_id'];
                    $payment_log                   = $wpdb->get_row($wpdb->prepare('SELECT bookingpress_invoice_id, bookingpress_customer_firstname,bookingpress_customer_lastname,bookingpress_customer_email, bookingpress_payment_gateway FROM ' . $tbl_bookingpress_payment_logs . ' WHERE bookingpress_appointment_booking_ref = %d', $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

                    $appointment_date_time           = $get_appointment['bookingpress_appointment_date'] . ' ' . $get_appointment['bookingpress_appointment_time'];
                    $appointment['created_date']     = date_i18n($bookingpress_default_date_time_format, strtotime($get_appointment['bookingpress_created_at']));
                    $appointment['bookingpress_appointment_created_date'] = $get_appointment['bookingpress_created_at'];
                    $appointment['appointment_date'] = date_i18n($bookingpress_default_date_time_format, strtotime($appointment_date_time));

                    $appointment['booking_id'] = !empty($get_appointment['bookingpress_booking_id']) ? $get_appointment['bookingpress_booking_id'] : 1;
                    $customer_email = ! empty($get_appointment['bookingpress_customer_email']) ? $get_appointment['bookingpress_customer_email'] : '';
                    $customer_phone = ! empty($get_appointment['bookingpress_customer_phone']) ? $get_appointment['bookingpress_customer_phone'] : '';

                    $bookingpress_customer_phone_dial_code = (isset($get_appointment['bookingpress_customer_phone_dial_code']) && !empty($get_appointment['bookingpress_customer_phone_dial_code'])) ? $get_appointment['bookingpress_customer_phone_dial_code'] : '';

                    $appointment['customer_first_name'] = !empty($get_appointment['bookingpress_customer_firstname']) ? stripslashes_deep($get_appointment['bookingpress_customer_firstname']) :'';
                    $appointment['customer_last_name'] = !empty($get_appointment['bookingpress_customer_lastname']) ? stripslashes_deep($get_appointment['bookingpress_customer_lastname']) :'';
                    $customer_username = ! empty($get_appointment['bookingpress_username']) ? $get_appointment['bookingpress_username'] : '';
                    if( !empty($customer_username ) ){
                        $appointment['customer_name'] = (isset($appointment['customer_name']) && !empty($appointment['customer_name']) && !empty(trim($appointment['customer_name']))) ? ($appointment['customer_name']) : stripslashes_deep($customer_username);
                    } else{
                    $appointment['customer_name'] = !empty($get_appointment['bookingpress_customer_name']) ? stripslashes_deep($get_appointment['bookingpress_customer_name']) : $appointment['customer_first_name'].' '.$appointment['customer_last_name'];
                    $appointment['customer_name'] = !empty(trim($appointment['customer_name'])) ? ($appointment['customer_name']) : stripslashes_deep($customer_email);
                    }
                    $appointment['customer_email'] = stripslashes_deep($customer_email);
                    $appointment['customer_phone'] = stripslashes_deep($customer_phone);

                    if(!empty($customer_phone) && !empty($bookingpress_customer_phone_dial_code)){
                        $appointment['customer_phone'] = '+'.$bookingpress_customer_phone_dial_code.' '.stripslashes_deep($customer_phone);
                    }

                    $appointment['service_name']  = stripslashes_deep($get_appointment['bookingpress_service_name']);
                    $appointment['service_id']  = intval($get_appointment['bookingpress_service_id']);
                    
                    $appointment['appointment_note']  = stripslashes_deep($get_appointment['bookingpress_appointment_internal_note']);                    

                    $service_duration             = esc_html($get_appointment['bookingpress_service_duration_val']);
                    $service_duration_unit        = esc_html($get_appointment['bookingpress_service_duration_unit']);

                    if( $service_duration_unit == 'h'){
                        $bookingpress_sortable_duration_val = $service_duration * 60;
                    } else if( $service_duration_unit == 'd'){
                        $bookingpress_sortable_duration_val = $service_duration * 24 * 60;
                    }else {
                        $bookingpress_sortable_duration_val = $service_duration;
                    }

                    if( !empty( $get_appointment['bookingpress_appointment_end_date'] ) && '0000-00-00' != $get_appointment['bookingpress_appointment_end_date'] ){
                        $appointment_end_date = $get_appointment['bookingpress_appointment_end_date'];
                    } else {
                        $appointment_end_date = $get_appointment['bookingpress_appointment_date'];
                    }

                    

                    $bookingpress_appointment_start_datetime = $get_appointment['bookingpress_appointment_date'].' '.$get_appointment['bookingpress_appointment_time'];
                    if( $appointment_end_date > $get_appointment['bookingpress_appointment_date'] && '00:00:00' != $get_appointment['bookingpress_appointment_end_time'] ){

                        $end_time_data = explode( ':', $get_appointment['bookingpress_appointment_end_time'] );

                        $end_hour = $end_time_data[0];
                        $end_mins = $end_time_data[1];
                        $end_sec = $end_time_data[2];

                        if( $end_hour >= 24 ){
                            $bookingpress_appointment_end_datetime = date('Y-m-d H:i:s', strtotime( $get_appointment['bookingpress_appointment_date'] .' 00:00:00 +'.$end_hour.' hours '.$end_mins.' minutes '.$end_sec.' seconds' ) );
                        } else {
                            $bookingpress_appointment_end_datetime = $appointment_end_date.' '.$get_appointment['bookingpress_appointment_end_time'];
                        }

                    } else {
                        $bookingpress_appointment_end_datetime = $appointment_end_date.' '.$get_appointment['bookingpress_appointment_end_time'];
                    }

                    if($service_duration_unit != 'd') {
                        $service_duration = $this->bookingpress_get_appointment_duration($bookingpress_appointment_start_datetime, $bookingpress_appointment_end_datetime);
                    } else {
                        if( 1 == $service_duration ){
                            $service_duration .= ' ' . esc_html__('Day', 'bookingpress-appointment-booking');
                        } else {   
                            $service_duration .= ' ' . esc_html__('Days', 'bookingpress-appointment-booking');
                        }                        
                    }  

                    $appointment['bookingpress_service_duration_sortable'] = (int)$this->bookingpress_get_appointment_duration_sorting($bookingpress_appointment_start_datetime, $bookingpress_appointment_end_datetime, $bookingpress_sortable_duration_val);

                    $appointment['appointment_duration'] = $service_duration;
                    
                    $currency_name                       = $get_appointment['bookingpress_service_currency'];
                    $currency_symbol                     = $BookingPress->bookingpress_get_currency_symbol($currency_name);

                    if ($get_appointment['bookingpress_service_price'] == '0' ) {
                        $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0, $currency_symbol);
                        $payment_amount_without_currency = 0;
                    } else {
                        $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($get_appointment['bookingpress_paid_amount'], $currency_symbol);
                        $payment_amount_without_currency = floatval($get_appointment['bookingpress_paid_amount']);
                    }

                    $appointment['appointment_payment'] = $payment_amount;

                    $appointment['payment_numberic_amount'] = $payment_amount_without_currency;

                    $bookingpress_appointment_status = esc_html($get_appointment['bookingpress_appointment_status']);
                    $bookingpress_appointment_status_label = $bookingpress_appointment_status;
                    foreach($bookingpress_appointment_status_arr as $status_key => $status_val){
                        if($bookingpress_appointment_status == $status_val['value']){
                            $bookingpress_appointment_status_label = $status_val['text'];
                            break;
                        }    
                    }
                    
                    $appointment['appointment_status']  = $bookingpress_appointment_status;
                    $appointment['appointment_status_label'] = $bookingpress_appointment_status_label;

                    $bookingpress_view_appointment_date = date_i18n($bookingpress_default_date_format, strtotime($get_appointment['bookingpress_appointment_date']));
					$bookingpress_view_appointment_time = date($bookingpress_default_time_format, strtotime($get_appointment['bookingpress_appointment_time']))." ".esc_html__('To', 'bookingpress-appointment-booking')." ".date($bookingpress_default_time_format, strtotime($get_appointment['bookingpress_appointment_end_time']));

					$appointment['view_appointment_date'] = $bookingpress_view_appointment_date;
                    $appointment['sort_appointment_date_time'] = strtotime( date('Y-m-d',strtotime($get_appointment['bookingpress_appointment_date']) ).' '.$get_appointment['bookingpress_appointment_time'] );
					$appointment['view_appointment_time'] = $bookingpress_view_appointment_time;
                    $bookingpress_payment_method = ( !empty( $payment_log) && $payment_log['bookingpress_payment_gateway']  == 'on-site' ) ? 'On Site': (!empty($payment_log['bookingpress_payment_gateway']) ? $payment_log['bookingpress_payment_gateway'] : '' ); 
                    $appointment['payment_method'] = $bookingpress_payment_method;
                    $appointment = apply_filters('bookingpress_appointment_add_view_field', $appointment, $get_appointment);

                    $bookingpress_booking_start_timestamp = strtotime( $get_appointment['bookingpress_appointment_date'] . ' ' . $get_appointment['bookingpress_appointment_time'] );
                    $appointment['is_past_appointment'] = current_time('timestamp') > $bookingpress_booking_start_timestamp;
                    $appointment['change_status_loader'] = '0';

                    $appointments[] = $appointment;
                    $counter++;
                }
            }
            
            $appointments = apply_filters('bookingpress_modify_appointment_data', $appointments);


            $data['items']       = $appointments;
            $data['form_field_data'] = $bookingpress_formdata;
            $data['items']       = $appointments;

            $data ['totalItems'] = count($get_total_appointments);
            wp_send_json($data);

        }

                
        /**
         * Delete appointment function
         *
         * @param  mixed $appointment_id   Appointment ID which you want to delete
         * @return void
         */
        function bookingpress_delete_appointment( $appointment_id = '', $is_api = false )
        {
            global $wpdb,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs;
            $response              = array();

            if($is_api == true){
                $is_error = $this->bookingpress_validate_token();
                if( is_wp_error( $is_error ) ){
                    return $is_error;
                }
            } else {
                $bpa_check_authorization = $this->bpa_check_authentication( 'delete_appointments', true, 'bpa_wp_nonce' );
            
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
            

            $appointment_id      = isset($_POST['delete_id']) ? intval($_POST['delete_id']) : $appointment_id; // phpcs:ignore WordPress.Security.NonceVerification
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $return              = false;
            if (! empty($appointment_id) ) {
                do_action('bookingpress_before_delete_appointment', $appointment_id);
                $is_deleted = $wpdb->delete($tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_booking_id' => $appointment_id ), array( '%d' ));
                if($is_deleted){
                    $wpdb->delete($tbl_bookingpress_payment_logs, array( 'bookingpress_appointment_booking_ref' => $appointment_id ), array( '%d' ));
                    $response['variant'] = 'success';
                    $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg']     = esc_html__('Appointment has been deleted successfully.', 'bookingpress-appointment-booking');
                    $return              = true;
                }else{
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                    $response['msg']     = esc_html__('Either appointment deleted or not found.', 'bookingpress-appointment-booking');
                    $return              = false;
                }
                
            }
            if ( $is_api && isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_delete_appointment' ) { // phpcs:ignore
                wp_send_json($response);
            }
            if($is_api){
                return $response;
            }
            return $return;
        }
        
        /**
         * Bulk functionality for appointment page
         *
         * @return void
         */
        function bookingpress_bulk_appointment()
        {
            global $BookingPress;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'delete_appointments', true, 'bpa_wp_nonce' );
            
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
             //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST['app_delete_ids'] contains array and sanitized properly using appointment_sanatize_field function
                $delete_ids = ! empty($_POST['app_delete_ids']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['app_delete_ids']) : array(); // phpcs:ignore
                if (! empty($delete_ids) ) {
                    foreach ( $delete_ids as $delete_key => $delete_val ) {
                        if (is_array($delete_val) ) {
                            $delete_val = $delete_val['appointment_id'];
                        }
                        $return = $this->bookingpress_delete_appointment($delete_val);
                        if ($return ) {
                            $response['variant'] = 'success';
                            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                            $response['msg']     = esc_html__('Appointment has been deleted successfully.', 'bookingpress-appointment-booking');
                        }
                    }
                }
            }
            wp_send_json($response);
        }

        function bookingpress_get_appointment_duration($appointment_start_datetime, $appointment_end_datetime){
            $service_duration = '';
            if(empty($appointment_start_datetime) || empty($appointment_end_datetime)){
                return $service_duration;
            }
            
            $bookingpress_tmp_start_datetime = new DateTime($appointment_start_datetime);
            $bookingpress_tmp_end_datetime = new DateTime($appointment_end_datetime);
            $booking_date_interval = $bookingpress_tmp_start_datetime->diff($bookingpress_tmp_end_datetime);
            $bookingpress_minute = $booking_date_interval->format('%i');
            $bookingpress_hour = $booking_date_interval->format('%h');
            $bookingpress_days = $booking_date_interval->format('%d');
            
            if($bookingpress_minute > 0) {
                $display_formatted_time = true;
                if( $bookingpress_minute == 1 ){
                    $service_duration = $bookingpress_minute.' ' . esc_html__('Min', 'bookingpress-appointment-booking'); 
                }else{
                    $service_duration = $bookingpress_minute.' ' . esc_html__('Mins', 'bookingpress-appointment-booking'); 
                }
            }
            
            if($bookingpress_hour > 0 ) {
                $display_formatted_time = true;
                if($bookingpress_hour == 1){
                    $service_duration = $bookingpress_hour.' ' . esc_html__('Hour', 'bookingpress-appointment-booking').' '.$service_duration;
                }else{
                    $service_duration = $bookingpress_hour.' ' . esc_html__('Hours', 'bookingpress-appointment-booking').' '.$service_duration;
                }
            }

            if($bookingpress_days == 1) {
                $service_duration = '24 ' . esc_html__('Hours', 'bookingpress-appointment-booking');
            }
            if($bookingpress_days > 1) {
                $service_duration = $bookingpress_days.' ' . esc_html__('Days', 'bookingpress-appointment-booking'); 
            }
            
            return $service_duration;
        }

        function bookingpress_get_appointment_duration_sorting($appointment_start_datetime, $appointment_end_datetime, $service_duration_sortable){
            $service_duration = 0;
            if(empty($appointment_start_datetime) || empty($appointment_end_datetime)){
                return $service_duration_sortable;
            }
            $bookingpress_tmp_start_datetime = new DateTime($appointment_start_datetime);
            $bookingpress_tmp_end_datetime = new DateTime($appointment_end_datetime);
            $booking_date_interval = $bookingpress_tmp_start_datetime->diff($bookingpress_tmp_end_datetime);
            $bookingpress_minute = $booking_date_interval->format('%i');
            $bookingpress_hour = $booking_date_interval->format('%h');
            $bookingpress_days = $booking_date_interval->format('%d');
            if($bookingpress_minute > 0){
                $service_duration = (int)$bookingpress_minute;
            }
            if($bookingpress_hour > 0){
                $service_duration = $service_duration + (int)$bookingpress_hour * 60;
            }
            if($bookingpress_days > 0){
                $service_duration = (int)$bookingpress_days * 24 * 60;
            }
            if($service_duration > 0){
                $service_duration_sortable = $service_duration;
            }
            return $service_duration_sortable;
        }
    }
}

global $bookingpress_appointment;
$bookingpress_appointment = new bookingpress_appointment();
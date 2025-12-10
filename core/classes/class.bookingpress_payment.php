<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_payment') ) {
    class bookingpress_payment Extends BookingPress_Core
    {
        function __construct()
        {
            add_action('bookingpress_payments_dynamic_view_load', array( $this, 'bookingpress_payments_dynamic_view_load_func' ));
            add_action('bookingpress_payments_dynamic_data_fields', array( $this, 'bookingpress_payments_dynamic_data_fields_func' ));
            add_action('bookingpress_payments_dynamic_vue_methods', array( $this, 'bookingpress_payments_dynamic_vue_methods_func' ), 10);
            add_action('bookingpress_payments_dynamic_on_load_methods', array( $this, 'bookingpress_payments_dynamic_on_load_methods_func' ), 10);
            add_action('bookingpress_payments_dynamic_helper_vars', array( $this, 'bookingpress_payments_dynamic_helper_func' ), 10);

            add_action('wp_ajax_bookingpress_get_payments_data', array( $this, 'bookingpress_get_payments_details' ), 10);
            add_action('wp_ajax_bookingpress_delete_payment_log', array( $this, 'bookingpress_delete_payment_log_func' ), 10);
            add_action('wp_ajax_bookingpress_bulk_payment_logs_action', array( $this, 'bookingpress_payment_log_bulk_action' ), 10);
            add_action('wp_ajax_bookingpress_fetch_payment_log', array( $this, 'bookingpress_fetch_payment_log_data' ), 10);

            add_action('wp_ajax_bookingpress_approve_appointment', array( $this, 'bookingpress_approve_appointment' ), 10);
            add_action( 'admin_init', array( $this, 'bookingpress_payment_vue_data_fields' ) );

            add_action('wp_ajax_bookingpress_change_payment_status', array($this, 'bookingpress_change_payment_status_func'));
        }
        
        /**
         * Ajax request for change payment status
         *
         * @return void
         */
        function bookingpress_change_payment_status_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_payment_logs;

            $bpa_check_authorization = $this->bpa_check_authentication( 'change_payment_status', true, 'bpa_wp_nonce' );
            
            $response = array();
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');
                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }


			$bookingpress_payment_log_id = !empty($_POST['payment_log_id']) ? intval($_POST['payment_log_id']) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			$bookingpress_payment_status = !empty($_POST['payment_status']) ? sanitize_text_field($_POST['payment_status']) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			if( !empty($bookingpress_payment_log_id) &&  !empty($bookingpress_payment_status) ){
				$wpdb->update($tbl_bookingpress_payment_logs, array('bookingpress_payment_status' => $bookingpress_payment_status), array('bookingpress_payment_log_id' => $bookingpress_payment_log_id));

                do_action('bookingpress_update_payment_details_externally_after_update_status', $_POST); // phpcs:ignore WordPress.Security.NonceVerification

				$response['variant'] = 'success';
				$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
				$response['msg'] = esc_html__('Payment status change successfully', 'bookingpress-appointment-booking');
			}

			echo wp_json_encode($response);
			exit;
		}
        
        /**
         * Payment Module default data variables
         *
         * @return void
         */
        function bookingpress_payment_vue_data_fields(){
            global $bookingpress_payment_vue_data_fields,$bookingpress_global_options;
            $bookingpress_options             = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_pagination          = $bookingpress_options['pagination'];
            $bookingpress_pagination_arr      = json_decode($bookingpress_pagination, true);
            $bookingpress_pagination_selected = $bookingpress_pagination_arr[0];


            $bookingpress_payment_vue_data_fields = array(
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
                'loading'                    => false,
                'items'                      => array(),
                'multipleSelection'          => array(),
                'perPage'                    => $bookingpress_pagination_selected,
                'totalItems'                 => 0,
                'pagination_selected_length' => $bookingpress_pagination_selected,
                'pagination_length'          => $bookingpress_pagination,
                'currentPage'                => 1,
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
                'search_customer_data'       => array(),
                'search_services_data'       => array(),
                'payment_status_data' => $bookingpress_options['payment_status'],
                'search_data'                => array(
                    'search_range'    => array( date('Y-m-d', strtotime('-2 Month')), date('Y-m-d') ),
                    'search_customer' => '',
                    'search_service'  => '',
                    'search_status'   => '',
                ),
                'view_payment_details_modal' => false,
                'view_payment_data'          => array(),
                'is_display_loader'          => '0',
                'is_display_loader_view'     => '0',
            );
        }
        
        /**
         * Ajax request for approve appointment
         *
         * @return void
         */
        function bookingpress_approve_appointment()
        {
            global $wpdb, $tbl_bookingpress_payment_logs, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'approve_appointments', true, 'bpa_wp_nonce' );
            
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

            $approve_payment_log_id      = ! empty($_REQUEST['approve_id']) ? intval($_REQUEST['approve_id']) : 0;
            $payment_data                = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $approve_payment_log_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

            $appointment_ref_id          = ! empty($payment_data['bookingpress_appointment_booking_ref']) ? $payment_data['bookingpress_appointment_booking_ref'] : 0;
            $bookingpress_customer_email = ! empty($payment_data['bookingpress_customer_email']) ? $payment_data['bookingpress_customer_email'] : '';
            if (! empty($appointment_ref_id) ) {

                $bookingpress_check_appointment_status = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_appointment_status FROM $tbl_bookingpress_appointment_bookings WHERE bookingpress_appointment_booking_id = %d", $appointment_ref_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_double_bookings is a table name. false alarm

                if($bookingpress_check_appointment_status != 1){                    
                    $wpdb->update($tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_status' => '1' ), array( 'bookingpress_appointment_booking_id' => $appointment_ref_id ));
                    $wpdb->update($tbl_bookingpress_payment_logs, array( 'bookingpress_payment_status' => '1' ), array( 'bookingpress_appointment_booking_ref' => $appointment_ref_id ));
                    $bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Approved', $appointment_ref_id, $bookingpress_customer_email);
                    do_action('bookingpress_after_change_appointment_status', $appointment_ref_id, '1');
                }
            } else {
                do_action('bookingpress_after_approve_appointment',$payment_data);                
            }

            $response['variant'] = 'success';
            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Appointment Approved Successfully', 'bookingpress-appointment-booking');

            echo wp_json_encode($response);
            exit();
        }
        
        /**
         * Payment Module helper variables function
         *
         * @return void
         */
        function bookingpress_payments_dynamic_helper_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            ?>
                var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
                ELEMENT.locale(lang)
            <?php
        }
        
        /**
         * Payment module view file
         *
         * @return void
         */
        function bookingpress_payments_dynamic_view_load_func()
        {
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/payment/manage_payment.php';
            $bookingpress_load_file_name = apply_filters('bookingpress_modify_payment_view_file_path', $bookingpress_load_file_name);

            include $bookingpress_load_file_name;
        }
        
        /**
         * Add more dynamic data variables for payments module
         *
         * @return void
         */
        function bookingpress_payments_dynamic_data_fields_func()
        {
            global $bookingpress_payment_vue_data_fields,$BookingPress;

            // pagination data
            $bookingpress_default_perpage_option                           = $BookingPress->bookingpress_get_settings('per_page_item', 'general_setting');
            $bookingpress_payment_vue_data_fields['perPage']               = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '10';
            $bookingpress_payment_vue_data_fields['pagination_length_val'] = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '10';

            $bookingpress_payment_vue_data_fields['search_services_data'] = $BookingPress->get_bookingpress_service_data_group_with_category();
            $bookingpress_payment_vue_data_fields['boookingpress_loading'] = false;    
            
            $bookingpress_payment_vue_data_fields['bookingpress_previous_row_obj'] = '';

            $bookingpress_payment_vue_data_fields = apply_filters('bookingpress_modify_payment_data_fields', $bookingpress_payment_vue_data_fields);
            echo wp_json_encode($bookingpress_payment_vue_data_fields);
        }
        
        /**
         * Payment Module onload methods
         *
         * @return void
         */
        function bookingpress_payments_dynamic_on_load_methods_func()
        {
            ?>
                this.loadPayments()
            <?php
            do_action('bookingpress_dynamic_add_onload_payment_methods');
        }
        
        /**
         * Payment module methods / functions
         *
         * @return void
         */
        function bookingpress_payments_dynamic_vue_methods_func()
        {
            global $bookingpress_notification_duration;
            ?>
                toggleBusy() {
                    if(this.is_display_loader == '1'){
                        this.is_display_loader = '0'
                    }else{
                        this.is_display_loader = '1'
                    }
                },
                handleSelectionChange(val) {
                    const payment_items_obj = val
                    this.multipleSelection = [];
                    Object.values(payment_items_obj).forEach(val => {
                        this.multipleSelection.push({payment_log_id : val.payment_log_id})
                        this.bulk_action = 'bulk_action';
                    });
                },
                handleSizeChange(val) {
                    this.perPage = val
                    this.loadPayments()
                },
                handleCurrentChange(val) {
                    this.currentPage = val;
                    this.loadPayments()
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
                handel_payment_appointment_data( {column, prop, order} ){


                    if (!order) {
                        this.bpa_payment_sort_by = '';
                        this.bpa_payment_sort_order = '';
                    } else {
                        this.bpa_payment_sort_by = prop;
                        this.bpa_payment_sort_order = order === 'descending' ? 'DESC' : 'ASC';
                    }

                    this.loadPayments();
                    

                },
                changePaginationSize(selectedPage) {     
                    var total_recored_perpage = selectedPage;
                    var current_page = this.changeCurrentPage(total_recored_perpage);                                        
                    this.perPage = selectedPage;                    
                    this.currentPage = current_page;    
                    this.loadPayments()
                },
                async loadPayments( reset_pagination = false ) {
                    const vm = this
                    vm.toggleBusy()
                    var bookingpress_module_type = bookingpress_dashboard_filter_start_date = bookingpress_dashboard_filter_end_date = '';
                    bookingpress_dashboard_filter_payment_status = '';
                    bookingpress_module_type = sessionStorage.getItem("bookingpress_module_type");                
                    bookingpress_dashboard_filter_start_date = sessionStorage.getItem("bookingpress_dashboard_filter_start_date");
                    bookingpress_dashboard_filter_end_date = sessionStorage.getItem("bookingpress_dashboard_filter_end_date");
                    bookingpress_dashboard_filter_payment_status = sessionStorage.getItem("bookingpress_dashboard_filter_payment_status");   
                    sessionStorage.removeItem("bookingpress_module_type");
                    sessionStorage.removeItem("bookingpress_dashboard_filter_start_date");
                    sessionStorage.removeItem("bookingpress_dashboard_filter_end_date");          
                    sessionStorage.removeItem("bookingpress_dashboard_filter_payment_status");
                    if( true == reset_pagination ){
                        this.currentPage = 1;
                    }
                    if(bookingpress_module_type != '' && bookingpress_module_type == 'payment' && bookingpress_dashboard_filter_start_date != '' && bookingpress_dashboard_filter_end_date != '' ) {                        
                        var payment_date_range = [bookingpress_dashboard_filter_start_date,bookingpress_dashboard_filter_end_date];
                        vm.search_data.search_range = payment_date_range;
                        if(bookingpress_dashboard_filter_payment_status != ''){
                           vm.search_data.search_status = bookingpress_dashboard_filter_payment_status;    
                        }
                    }                
                    var postData = { action:'bookingpress_get_payments_data', perpage:this.perPage, currentpage:this.currentPage, search_data:vm.search_data,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>',sort_by: this.bpa_payment_sort_by, sort_order: this.bpa_payment_sort_order };

                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        vm.toggleBusy()
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
                        <?php do_action( 'bookingpress_after_get_payment_response' ); ?>
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error);
                    });
                },
                loadPaymentWithoutLoader(){
                    const vm = this
                    var bookingpress_module_type = bookingpress_dashboard_filter_start_date = bookingpress_dashboard_filter_end_date = '';
                    bookingpress_dashboard_filter_payment_status = '';
                    bookingpress_module_type = sessionStorage.getItem("bookingpress_module_type");                
                    bookingpress_dashboard_filter_start_date = sessionStorage.getItem("bookingpress_dashboard_filter_start_date");
                    bookingpress_dashboard_filter_end_date = sessionStorage.getItem("bookingpress_dashboard_filter_end_date");
                    bookingpress_dashboard_filter_payment_status = sessionStorage.getItem("bookingpress_dashboard_filter_payment_status");   
                    sessionStorage.removeItem("bookingpress_module_type");
                    sessionStorage.removeItem("bookingpress_dashboard_filter_start_date");
                    sessionStorage.removeItem("bookingpress_dashboard_filter_end_date");      
                    sessionStorage.removeItem("bookingpress_dashboard_filter_payment_status");                        
                    if(bookingpress_module_type != '' && bookingpress_module_type == 'payment' && bookingpress_dashboard_filter_start_date != '' && bookingpress_dashboard_filter_end_date != '' ) {                        
                        var payment_date_range = [bookingpress_dashboard_filter_start_date,bookingpress_dashboard_filter_end_date];
                        vm.search_data.search_range = payment_date_range;
                        if(bookingpress_dashboard_filter_payment_status != ''){
                            vm.search_data.search_status = bookingpress_dashboard_filter_payment_status;    
                        }
                    }                
                    var postData = { action:'bookingpress_get_payments_data', perpage:this.perPage, currentpage:this.currentPage, search_data:vm.search_data,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };

                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
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
                closeBulkAction(){
                    this.$refs.multipleTable.clearSelection();
                    this.bulk_action = 'bulk_action';
                },
                deletePaymentLog(delete_id){
                    const vm2 = this
                    var payment_log_delete_data = { action:'bookingpress_delete_payment_log', delete_id: delete_id,_wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'}
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( payment_log_delete_data ) )
                    .then(function(response){
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                        vm2.loadPayments()
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
                bulk_actions(){
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
                            var delete_payment_logs = {
                                action:'bookingpress_bulk_payment_logs_action',
                                delete_ids: this.multipleSelection,
                                bulk_action: 'delete',
                                _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                            }
                            axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( delete_payment_logs ) )
                            .then(function(response){
                                vm2.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: response.data.variant,
                                    customClass: response.data.variant+'_notification',
                                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                });
                                vm2.loadPayments()
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
                            if(this.multipleSelection.length == 0){
                                vm2.$notify({
                                    title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                    message: '<?php esc_html_e('Please select one or more records.', 'bookingpress-appointment-booking'); ?>',
                                    type: 'error',
                                    customClass: 'error_notification',
                                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                                });
                            }else{
                                <?php do_action('bookingpress_payment_dynamic_bulk_action'); ?>
                            }
                        }
                    }
                },
                resetFilter(){
                    const vm = this
                    vm.search_data.search_range = ''
                    vm.search_data.search_customer = ''
                    vm.search_data.search_service = ''
                    vm.search_data.search_status = ''
                    <?php
                    do_action('bookingpress_payment_reset_filter');
                    ?>
                    vm.loadPayments()
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
                view_details(view_log_id)
                {
                    const vm = this
                    vm.view_payment_details_modal = true
                    vm.is_display_loader_view = '1'
                    vm.view_payment_data = {}
                    var fetch_payment_log_details = { action:'bookingpress_fetch_payment_log', log_id: view_log_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( fetch_payment_log_details, ) )
                    .then(function(response){
                        vm.is_display_loader_view = '0'
                        vm.view_payment_data = response.data
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
                ClosePaymentModal() {
                    const vm = this
                    vm.view_payment_details_modal = false
                },
                bpa_approve_appointment(payment_log_id){
                    const vm = this
                    var bpa_post_data = []
                    bpa_post_data.action = 'bookingpress_approve_appointment'
                    bpa_post_data.approve_id = payment_log_id
                    bpa_post_data._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bpa_post_data, ) )
                    .then(function(response){
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                        vm.loadPaymentWithoutLoader()
                    }).catch(function(error){
                        console.log(error);
                        vm.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,

                        });
                    });
                },
                bookingpress_change_status(payment_log_id, new_status){
                    const vm = this;
                    let rowIndex;
                    vm.items.forEach(function(currentValue, index, arr){
                        if(payment_log_id == currentValue.payment_log_id){
                            vm.items[index].change_status_loader = 1;
                            rowIndex = index;
                        }
                    });
                    var postdata = {
                        action: 'bookingpress_change_payment_status',
                        payment_log_id: payment_log_id,
                        payment_status: new_status,
                        _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>',
                    };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
                    .then(function(response){
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
                        });
                        if( "undefined" != typeof rowIndex && "undefined" != typeof vm.items && "undefined" != typeof vm.items[rowIndex] ){
                            vm.items[rowIndex].change_status_loader = 0;
                        }
                        if( "error" != response.data.variant ){   
                            if(new_status == '1'){
                                vm.bpa_approve_appointment(payment_log_id);
                            }
                            vm.loadPaymentWithoutLoader()
                        }
                    }).catch(function(error){
                        console.log(error);
                        vm.$notify({
                            title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
                            message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
                        });
                    });
                },
                bookingpress_get_search_customer_list(query){
                    const vm = new Vue()
                    const vm2 = this	
                    if (query !== '') {
                        vm2.boookingpress_loading = true;                    
                        var customer_action = { action:'bookingpress_get_search_customer_list',search_user_str:query,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }                    
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                        .then(function(response){
                            vm2.boookingpress_loading = false;
                            vm2.search_customer_data = response.data.appointment_customers_details
                        }).catch(function(error){
                            console.log(error)
                        });
                    } else {
                        vm2.search_customer_data = [];
                    }	
                },
                bookingpress_full_row_clickable(row){
                    const vm = this
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
            <?php
            do_action('bookingpress_payment_add_dynamic_vue_methods');
        }

        
        /**
         * Ajax request for get payments details
         *
         * @return void
         */
        function bookingpress_get_payments_details(){
            global $wpdb, $tbl_bookingpress_payment_logs, $BookingPress, $tbl_bookingpress_customers, $tbl_bookingpress_appointment_bookings, $bookingpress_global_options;

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_payments', true, 'bpa_wp_nonce' );
            $response = array();
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
            $perpage     = isset($_POST['perpage']) ? intval($_POST['perpage']) : 20; // phpcs:ignore WordPress.Security.NonceVerification
            $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1; // phpcs:ignore WordPress.Security.NonceVerification
            $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

            if ( ! in_array( $sort_order, array( 'ASC', 'DESC' ), true ) ) {
                $sort_order = 'DESC';
            }

            $bookingpress_payment_sortable_columns = array(
                'appointment_date' => 'bookingpress_appointment_date',
                'payment_date'     => 'bookingpress_payment_date_time',
                'staff_member_name' => 'bookingpress_staff_first_name',
                'payment_customer' => 'bookingpress_customer_firstname',
                'payment_service'  => 'bookingpress_service_name',
            );

            $order_by_column = 'bookingpress_payment_log_id';

            if ( isset( $bookingpress_payment_sortable_columns[ $sort_by ] ) ) {
                $order_by_column = $bookingpress_payment_sortable_columns[ $sort_by ];
            }

            $bookingpress_search_query = ' WHERE 1=1';
            if (! empty($_POST['search_data']) ) { // phpcs:ignore WordPress.Security.NonceVerification
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reason: $_POST['search_data'] contains mixed array and will be sanitiz properly using 'appointment_sanatize_field' function
                $search_data = array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['search_data']); // phpcs:ignore
                if (! empty($search_data['search_range']) ) {
                    $range_start_date           = date('Y-m-d', strtotime($search_data['search_range'][0])) . ' 00:00:00';
                    $range_end_date             = date('Y-m-d', strtotime($search_data['search_range'][1])) . ' 23:59:59';
                    $bookingpress_search_query .= $wpdb->prepare( " AND (bookingpress_payment_date_time BETWEEN %s AND %s )", $range_start_date, $range_end_date );
                }

                if (! empty($search_data['search_customer']) ) {
                    $bookingpress_customer_ids                = $search_data['search_customer'];
                    $search_query_where = ' AND (bookingpress_customer_id IN (';
                    $search_query_where .= rtrim( str_repeat( '%d,', count( $bookingpress_customer_ids ) ), ',' ). ') )';
                    array_unshift( $bookingpress_customer_ids, $search_query_where );
                    $bookingpress_search_query .= call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_customer_ids  );
                }
                if (! empty($search_data['search_service']) ) {
                    $bookingpress_service_ids = $search_data['search_service'];
                    $search_query_where  = ' AND (bookingpress_service_id IN (';
                    $search_query_where .= rtrim( str_repeat( '%d,', count( $bookingpress_service_ids ) ), ',' ) . ') )';
                    array_unshift( $bookingpress_service_ids, $search_query_where );
                    $bookingpress_search_query .= call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_service_ids  );
                }

                if (! empty($search_data['search_status']) && $search_data['search_status'] != 'all' ) {
                    $search_status              = $search_data['search_status'];
                    $bookingpress_search_query .= $wpdb->prepare( " AND (bookingpress_payment_status = %s)", $search_status );
                }
                

                $bookingpress_search_query = apply_filters('bookingpress_payment_add_filter', $bookingpress_search_query, $search_data);
            }

            //$total_payment_logs = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_payment_logs} {$bookingpress_search_query} ORDER BY bookingpress_payment_log_id DESC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
            //$get_payment_logs   = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_payment_logs} {$bookingpress_search_query} ORDER BY bookingpress_payment_log_id DESC LIMIT {$offset} , {$perpage}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

            /** new column sorting data */

            $total_payment_logs = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_payment_logs} {$bookingpress_search_query} ORDER BY {$order_by_column} {$sort_order}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
           
            $get_payment_logs   = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_payment_logs} {$bookingpress_search_query} ORDER BY {$order_by_column} {$sort_order} LIMIT {$offset} , {$perpage}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

            
            $payment_logs_data = array();
            if (! empty($get_payment_logs) ) {
                $bookingpress_date_format = get_option('date_format');
                foreach ( $get_payment_logs as $payment_log_key => $payment_log_val ) {
                    $bookingpress_customer = ! empty($payment_log_val['bookingpress_customer_firstname']) ? ($payment_log_val['bookingpress_customer_firstname'] . ' ' . $payment_log_val['bookingpress_customer_lastname']) : ($payment_log_val['bookingpress_customer_email']);

                    $service_name = $payment_log_val['bookingpress_service_name'];

                    $appointment_date = $payment_log_val['bookingpress_appointment_date'];
                    $payment_date     = $payment_log_val['bookingpress_payment_date_time'];
                    $payment_gateway  = $payment_log_val['bookingpress_payment_gateway'];
                    if ($payment_gateway == 'on-site' ) {
                        $payment_gateway = 'on site';
                    }


                    $currency_name   = $payment_log_val['bookingpress_payment_currency'];
                    $currency_symbol = $BookingPress->bookingpress_get_currency_symbol($currency_name);
                    if ($payment_log_val['bookingpress_payment_amount'] == '0' ) {
                        //$payment_amount = '';
                        $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0, $currency_symbol);
                    } else {
                        $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($payment_log_val['bookingpress_payment_amount'], $currency_symbol);
                    }
                    
                    // get appointment status
                    $appointment_ref_id = $payment_log_val['bookingpress_appointment_booking_ref'];
                    $appointmentData    = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_status FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_ref_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                    $bookingpress_global_settings = $bookingpress_global_options->bookingpress_global_options();
                    $bookingpress_default_date_format = $bookingpress_global_settings['wp_default_date_format'];
                    $bookingpress_default_time_format = $bookingpress_global_settings['wp_default_time_format'];
                    $bookingpress_payment_status = $bookingpress_global_settings['payment_status'];

                    $bookingpress_appointment_date = '';
                    if ($appointment_date != '0000-00-00' ) {
                        $bookingpress_appointment_date = date_i18n($bookingpress_default_date_format, strtotime($appointment_date));
                    }

                    $bookingpress_get_existing_avatar_url = $BookingPress->get_bookingpress_customersmeta($payment_log_val['bookingpress_customer_id'], 'customer_avatar_details');
                    $bookingpress_get_existing_avatar_url = ! empty($bookingpress_get_existing_avatar_url) ? maybe_unserialize($bookingpress_get_existing_avatar_url) : array();
                    if (! empty($bookingpress_get_existing_avatar_url[0]['url']) ) {
                        $bookingpress_avatar_url = $bookingpress_get_existing_avatar_url[0]['url'];
                    } else {
                        $bookingpress_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
                    }

                    $bookingpress_payment_status_label = $payment_log_val['bookingpress_payment_status'];
                    foreach($bookingpress_payment_status as $payment_status_key => $payment_status_val){
                        if($payment_status_val['value'] == $payment_log_val['bookingpress_payment_status']){
                            $bookingpress_payment_status_label = $payment_status_val['text'];
                            break;
                        }
                    }
                    $payment_gateway_label_temp = $payment_gateway;
                    if(!empty($payment_gateway) && ($payment_gateway == 'on-site' || $payment_gateway == 'on site' || $payment_gateway == 'On Site') ) {
                        $payment_gateway = $BookingPress->bookingpress_get_customize_settings('locally_text','booking_form');
                    } elseif(!empty($payment_gateway) && $payment_gateway != 'manual') {
                        $payment_gateway = $BookingPress->bookingpress_get_customize_settings($payment_gateway.'_text','booking_form');
                        if(empty($payment_gateway)) {
                            $payment_gateway = $payment_gateway_label_temp;
                        }
                    } 

                    $bookingpress_payment_status = $payment_log_val['bookingpress_payment_status'];
                    $payment             = array(
                        'payment_log_id'          => $payment_log_val['bookingpress_payment_log_id'],
                        'payment_date'            => date_i18n($bookingpress_default_date_format, strtotime($payment_date)),
                        'payment_customer'        => stripslashes_deep($bookingpress_customer),
                        'payment_service'         => stripslashes_deep($service_name),
                        'appointment_date'        => $bookingpress_appointment_date,
                        'payment_gateway'         => esc_html($payment_gateway),
                        'payment_numberic_amount' => floatval($payment_log_val['bookingpress_payment_amount']),
                        'payment_amount'          => $payment_amount,
                        'appointment_status'      => !empty($appointmentData['bookingpress_appointment_status']) ? $appointmentData['bookingpress_appointment_status'] : '',
                        'payment_status'          => $bookingpress_payment_status,
                        'payment_status_label'    => $bookingpress_payment_status_label,
                        'appointment_start_time'  => date($bookingpress_default_time_format, strtotime($payment_log_val['bookingpress_appointment_start_time'])),
                        'appointment_end_time'    => date($bookingpress_default_time_format, strtotime($payment_log_val['bookingpress_appointment_end_time'])),
                        'transaction_id'          => !empty($payment_log_val['bookingpress_transaction_id']) ? $payment_log_val['bookingpress_transaction_id'] : '-',
                        'customer_firstname'      => stripslashes_deep($payment_log_val['bookingpress_customer_firstname']),
                        'customer_lastname'       => stripslashes_deep($payment_log_val['bookingpress_customer_lastname']),
                        'customer_email'          => $payment_log_val['bookingpress_customer_email'],
                        'customer_avatar'         => $bookingpress_avatar_url,
                        'change_status_loader'    => 0,
                        'sort_payment_appointment_date' => $payment_log_val['bookingpress_appointment_date'],
                        'sort_payment_date'       => $payment_log_val['bookingpress_payment_date_time'],
                    );
                    $payment             = apply_filters('bookingpress_payment_add_view_field', $payment, $payment_log_val);
                    $payment_logs_data[] = $payment;
                }
            }

            $payment_logs_data = apply_filters('bookingpress_modify_payments_listing_data', $payment_logs_data);

            $data['items'] = $payment_logs_data;
            $data['total'] = count($total_payment_logs);
            wp_send_json($data);
        }
        
        /**
         * Delete payment log
         *
         * @param  mixed $delete_id   Payment id which need to delete
         * @return void
         */
        function bookingpress_delete_payment_log_func( $delete_id = '' )
        {
            global $wpdb, $tbl_bookingpress_payment_logs;
            $response              = array();
            $bpa_check_authorization = $this->bpa_check_authentication( 'delete_payments', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $delete_id           = ! empty($_POST['delete_id']) ? intval($_POST['delete_id']) : intval($delete_id); // phpcs:ignore WordPress.Security.NonceVerification
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $return              = false;
            if (! empty($delete_id) ) {
                $wpdb->delete($tbl_bookingpress_payment_logs, array( 'bookingpress_payment_log_id' => $delete_id ));
                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Payment Transaction has been deleted successfully.', 'bookingpress-appointment-booking');
                $return              = true;
            }
            if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_delete_payment_log' ) { // phpcs:ignore WordPress.Security.NonceVerification
                wp_send_json($response);
                die();
            }
            return $return;
        }
        
        /**
         * Payment module bulk actions
         *
         * @return void
         */
        function bookingpress_payment_log_bulk_action()
        {
            global $BookingPress;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'delete_payments', true, 'bpa_wp_nonce' );
            
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
            if (! empty($_POST['bulk_action']) && sanitize_text_field($_POST['bulk_action']) == 'delete' ) { // phpcs:ignore WordPress.Security.NonceVerification
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reason: $_POST['delete_ids'] contains mixed array and will be sanitized properly using 'appointment_sanatize_field' function
                $delete_ids = ! empty($_POST['delete_ids']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['delete_ids']) : array(); // phpcs:ignore
                if (! empty($delete_ids) ) {
                    foreach ( $delete_ids as $delete_key => $delete_val ) {
                        if (is_array($delete_val) ) {
                            $delete_val = $delete_val['payment_log_id'];
                        }
                        $return = $this->bookingpress_delete_payment_log_func($delete_val);
                        if ($return ) {
                            $response['variant'] = 'success';
                            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                            $response['msg']     = esc_html__('Payment Transaction has been deleted successfully.', 'bookingpress-appointment-booking');
                        }
                    }
                }
            }
            wp_send_json($response);
        }
        
        /**
         * Fetch payment log data
         *
         * @return void
         */
        function bookingpress_fetch_payment_log_data()
        {
            global $wpdb, $tbl_bookingpress_payment_logs, $tbl_bookingpress_customers, $BookingPress;

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_payments', true, 'bpa_wp_nonce' );
            $response = array();
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $payment_log_data = array();
            if (! empty($_POST['log_id']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $log_id           = intval($_POST['log_id']); // phpcs:ignore WordPress.Security.NonceVerification
                $payment_log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $log_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

                $payment_log_data['customer_name']     = '';
                $payment_log_data['staff_member_name'] = '';
                if (! empty($payment_log_data) ) {
                    $currency_name   = $payment_log_data['bookingpress_payment_currency'];
                    $currency_symbol = $BookingPress->bookingpress_get_currency_symbol($currency_name);

                    if ($payment_log_data['bookingpress_payment_amount'] == 0 ) {
                        $payment = '';
                    } else {
                        $payment_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($payment_log_data['bookingpress_payment_amount'], $currency_symbol);
                    }

                    $payment_log_data['bookingpress_payment_gateway']  = esc_html($payment_log_data['bookingpress_payment_gateway']);
                    $payment_log_data['bookingpress_payer_email']      = stripslashes_deep($payment_log_data['bookingpress_payer_email']);
                    $payment_log_data['bookingpress_payment_amount']   = $payment_amount;
                    $bookingpress_date_format                          = get_option('date_format');
                    $payment_log_data['bookingpress_appointment_date'] = date($bookingpress_date_format, strtotime($payment_log_data['bookingpress_appointment_date']));
                    $payment_log_data['bookingpress_service_name'] = !empty($payment_log_data['bookingpress_service_name']) ? stripslashes_deep($payment_log_data['bookingpress_service_name']) :'';
                    $payment_log_data['bookingpress_payment_status'] = $payment_log_data['bookingpress_payment_status'] != '1' ? esc_html($payment_log_data['bookingpress_payment_status']): 'Paid';
                    $customer_id     = $payment_log_data['bookingpress_customer_id'];

                    $customer_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = %d", $customer_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
                    if (! empty($customer_data) ) {
                        $customer_name                     = ! empty($customer_data['bookingpress_user_firstname']) ? ($customer_data['bookingpress_user_firstname'] . ' ' . $customer_data['bookingpress_user_lastname']) : ($customer_data['bookingpress_user_email']);
                        $payment_log_data['customer_name'] = stripslashes_deep($customer_name);
                    }

                    $payment_log_data['staff_member_name'] = '';
                }
            }

            $payment_log_data = apply_filters('bookingpress_modify_modal_payment_log_details', $payment_log_data);

            echo wp_json_encode($payment_log_data);
            exit();
        }
    }
}

global $bookingpress_payment;
$bookingpress_payment = new bookingpress_payment();
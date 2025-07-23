<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class bookingpress_notifications Extends BookingPress_Core
{

    function __construct()
    {
        add_action('wp_ajax_bookingpress_save_email_notification_data', array( $this, 'bookingpress_save_email_notification_data' ), 10);
        add_action('wp_ajax_bookingpress_get_email_notification_data', array( $this, 'bookingpress_get_email_notification_data' ), 10);
        add_action('wp_ajax_bookingpress_get_default_notification_status', array( $this, 'bookingpress_get_all_default_notification_status' ), 10);

        add_action('bookingpress_notifications_dynamic_helper_vars', array( $this, 'bookingpress_notification_dynamic_helper_vars_func' ), 10);
        add_action('bookingpress_notifications_dynamic_vue_methods', array( $this, 'bookingpress_notification_dynamic_vue_methods_func' ), 10);
        add_action('bookingpress_notifications_dynamic_data_fields', array( $this, 'bookingpress_notification_dynamic_data_fields_func' ), 10);
        add_action('bookingpress_notifications_dynamic_on_load_methods', array( $this, 'bookingpress_notification_dynamic_on_load_methods_func' ), 10);
        add_action('bookingpress_notifications_dynamic_view_load', array( $this, 'bookingpress_notifications_dynamic_view_load_func' ), 10);
        add_filter( 'bookingpress_add_global_option_data', array( $this, 'bookingpress_add_global_option_data_func' ), 11 );

        add_action( 'admin_init', array( $this, 'bookingpress_notification_vue_methods_data' ) );
    }
    
    function bookingpress_add_global_option_data_func($global_data)
    {
        //To allowed button tag in Email Notification
        if(is_array($_POST) && !empty($_POST) && isset($_POST['action']) && $_POST['action']=='bookingpress_save_email_notification_data') { //phpcs:ignore
            global $bookingpress_global_options;
            $bookingpress_email_allowed_html_tag = json_decode($global_data['allowed_html'], TRUE);
            $bookingpress_email_allowed_html_tag['button'] = $bookingpress_global_options->bookingpress_global_attributes();
            $global_data['allowed_html'] =  wp_json_encode($bookingpress_email_allowed_html_tag);
        }
        return $global_data;
    }

    /**
     * Notification module default data variables
     *
     * @return void
     */
    function bookingpress_notification_vue_methods_data(){
        global $bookingpress_notification_vue_methods_data, $bookingpress_global_options;

        $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_locale_lang = $bookingpress_options['locale'];

        $bookingpress_customer_placeholders    = json_decode($bookingpress_options['customer_placeholders']);
        $bookingpress_service_placeholders     = json_decode($bookingpress_options['service_placeholders']);
        $bookingpress_company_placeholders     = json_decode($bookingpress_options['company_placeholders']);
        $bookingpress_appointment_placeholders = json_decode($bookingpress_options['appointment_placeholders']);
        $bookingpress_placeholder_success_msg  = esc_html__('Placeholder copied', 'bookingpress-appointment-booking');

        $bookingpress_notification_vue_methods_data = array(
            'checked'                                       => true,
            'default_notification_status'                   => array(
                'customer' => array(
                    'appointment_approved' => true,
                    'appointment_pending'  => false,
                    'appointment_rejected' => true,
                    'appointment_canceled' => false,
                ),
                'employee' => array(
                    'appointment_approved' => true,
                    'appointment_pending'  => false,
                    'appointment_rejected' => true,
                    'appointment_canceled' => false,
                ),
            ),
            'bookingpress_selected_default_notification'    => 'appointment_approved',
            'bookingpress_selected_default_notification_db_name' => 'Appointment Approved',
            'activeTabName'                                 => 'customer',
            'bookingpress_email_notification_edit_text'     => '',
            'bookingpress_email_notification_subject'       => '',
            'bookingpress_custom_email_notification_text'   => '',
            'bookingpress_custom_email_notification_status' => '1',
            'bookingpress_custom_email_notification_type'   => 'action',
            'bookingpress_customer_placeholders'            => $bookingpress_customer_placeholders,
            'bookingpress_service_placeholders'             => $bookingpress_service_placeholders,
            'bookingpress_company_placeholders'             => $bookingpress_company_placeholders,
            'bookingpress_active_email_notification'        => 'appointment_approved',
            'is_display_loader'                             => '0',
            'is_disabled'                                   => false,
            'is_display_save_loader'                        => '0',
            'bookingpress_appointment_placeholders'         => $bookingpress_appointment_placeholders,
        );
    }
    
    /**
     * Onload functions / Methods of Notifications Module
     *
     * @return void
     */
    function bookingpress_notification_dynamic_on_load_methods_func()
    {
        ?>
                this.bookingpress_select_email_notification('approved', 'Appointment Approved', 'appointment_approved')
                this.bookingpress_get_all_default_notification_status()
        <?php
        do_action('bookingpress_add_notification_dynamic_on_load_methods');
    }
    
    /**
     * Load notifications module view file
     *
     * @return void
     */
    function bookingpress_notifications_dynamic_view_load_func()
    {
        $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/notifications/manage_notifications.php';
        $bookingpress_load_file_name = apply_filters('bookingpress_modify_notifications_view_file_path', $bookingpress_load_file_name);

        include $bookingpress_load_file_name;
    }
    
    /**
     * Add more data variables to notification module
     *
     * @return void
     */
    function bookingpress_notification_dynamic_data_fields_func()
    {
        global $bookingpress_notification_vue_methods_data;
        $bookingpress_notification_vue_methods_data = apply_filters('bookingpress_add_dynamic_notification_data_fields', $bookingpress_notification_vue_methods_data);
        echo wp_json_encode($bookingpress_notification_vue_methods_data);
    }
    
    /**
     * Notification module helper variables
     *
     * @return void
     */
    function bookingpress_notification_dynamic_helper_vars_func()
    {
        global $bookingpress_global_options;
        $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_locale_lang = $bookingpress_options['locale'];

        ?>
                var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
                ELEMENT.locale(lang)
        <?php
        do_action('bookingpress_add_dynamic_notification_helper_vars');
    }
    
    /**
     * Notification module methods / functions
     *
     * @return void
     */
    function bookingpress_notification_dynamic_vue_methods_func()
    {
        global $bookingpress_global_options,$bookingpress_notification_duration;
        $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_locale_lang = $bookingpress_options['locale'];

        $bookingpress_customer_placeholders    = json_decode($bookingpress_options['customer_placeholders']);
        $bookingpress_company_placeholders     = json_decode($bookingpress_options['company_placeholders']);
        $bookingpress_appointment_placeholders = json_decode($bookingpress_options['appointment_placeholders']);

        $bookingpress_placeholder_success_msg = esc_html__('Placeholder copied', 'bookingpress-appointment-booking');
        ?>
                loadTinyMCE(){
                    window.onload = function(){
                        (function() {
                            tinyMCE.init({ selector:'#bookingpress_email_notification_subject_message' });
                        })();
                    }
                },
                bookingpress_get_all_default_notification_status(){
                    const vm = this;
                    let postData = { action: 'bookingpress_get_default_notification_status', _wpnonce:'<?php echo esc_html( wp_create_nonce('bpa_wp_nonce') ); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        vm.default_notification_status = response.data
                    }.bind(this))
                    .catch( function (error) {
                        console.log(error);
                    });
                },
                bookingpress_get_notification_data(email_notification_key, email_notification_name = '', is_custom_notification = 0, bookingpress_custom_notification_id = 0){
                    const vm = this
                    var bookingpress_get_notification_post_data = []
                    bookingpress_get_notification_post_data.bookingpress_notification_receiver_type = vm.activeTabName
                    bookingpress_get_notification_post_data.bookingpress_notification_type = 'default'
                    bookingpress_get_notification_post_data.bookingpress_notification_name = email_notification_name
                    
                    bookingpress_get_notification_post_data.is_custom_notification = is_custom_notification
                    bookingpress_get_notification_post_data.custom_notification_id = bookingpress_custom_notification_id
                    bookingpress_get_notification_post_data.action = 'bookingpress_get_email_notification_data'
                    bookingpress_get_notification_post_data._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'

                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_get_notification_post_data ) )
                    .then( function (response) {
                        const bookingpress_return_notification_data = response.data.return_data;
                        vm.bookingpress_reset_default_email_notification()
                        if(response.data.variant == 'success' && bookingpress_return_notification_data.length != 0)
                        {
                            //vm.bookingpress_email_notification_edit_text = bookingpress_return_notification_data.bookingpress_notification_name
                            vm.bookingpress_email_notification_subject = bookingpress_return_notification_data.bookingpress_notification_subject

                            var bookingpress_email_notification_msg = bookingpress_return_notification_data.bookingpress_notification_message
                            document.getElementById('bookingpress_email_notification_subject_message').value = bookingpress_email_notification_msg
                            
                            setTimeout(function(){
                                if( null != tinyMCE.activeEditor ){
                                    tinyMCE.activeEditor.setContent(bookingpress_email_notification_msg);
                                }
                            },100);

                            vm.bookingpress_is_custom_notification = true
                            vm.bookingpress_custom_notification_type = bookingpress_return_notification_data.bookingpress_notification_type
                            vm.bookingpress_custom_email_notification_text = bookingpress_return_notification_data.bookingpress_notification_name
                            vm.bookingpress_custom_email_notification_status = bookingpress_return_notification_data.bookingpress_notification_status
                            vm.bookingpress_custom_email_notification_type = bookingpress_return_notification_data.bookingpress_notification_execution_type
                            vm.bookingpress_custom_email_notification_appointment_status = bookingpress_return_notification_data.bookingpress_notification_appointment_status

                            if(bookingpress_return_notification_data.selected_appointments != undefined){
                                vm.bookingpress_custom_email_notification_appointment_selected_services = bookingpress_return_notification_data.selected_appointments
                            }

                            vm.bookingpress_custom_email_notification_event_action = bookingpress_return_notification_data.bookingpress_notification_event_action

                            if(bookingpress_return_notification_data.selected_events != undefined){
                                vm.bookingpress_custom_email_notification_selected_events = bookingpress_return_notification_data.selected_events
                            }

                            vm.bookingpress_only_send_custom_email_notification = bookingpress_return_notification_data.bookingpress_notification_send_only_this
                      
                            <?php
                            do_action('bookingpress_email_notification_get_data');
                            ?>
                        }
                    }.bind(this))
                    .catch( function (error) {
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
                bookingpress_select_email_notification(email_notification_type, email_notification_name, email_notification_key, is_custom_notification = 0, bookingpress_custom_notification_id = 0)
                {
                    const vm = this;

                    let notification_label = document.querySelector( `.bpa-en-left-notification-item[data-key="${email_notification_type}"]` );
                    if( null != notification_label ){
                        email_notification_type = notification_label.getAttribute( 'data-label' );
                    }
                    

                    vm.bookingpress_active_email_notification = email_notification_key                    
                    vm.bookingpress_is_custom_email_notification = false;
                    vm.bookingpress_email_notification_edit_text = email_notification_type
                    vm.bookingpress_selected_default_notification = email_notification_key
                    vm.bookingpress_selected_default_notification_db_name = email_notification_name
                    vm.bookingpress_get_notification_data(email_notification_key, email_notification_name)                    
                },
                bookingpress_reset_default_email_notification(){
                    const vm = this
                    vm.bookingpress_email_notification_subject = '';
                    document.getElementById('bookingpress_email_notification_subject_message').value = ''
                },
                bookingpress_save_email_notification_data()
                {
                    const vm = this;
                    vm.is_disabled = true;
                    vm.is_display_save_loader = '1';
                    tinyMCE.triggerSave();
                    const formData = new FormData(vm.$refs.email_notification_form.$el);
                    const data = {};
                    for (let [key, val] of formData.entries()) {
                        Object.assign(data, { [key]: val })
                    }
                    var bookingpress_email_notification_msg_data = data.bookingpress_email_notification_subject_message;

                    let bookingpress_save_notification_data = []
                    bookingpress_save_notification_data.notification_receiver = vm.activeTabName
                    bookingpress_save_notification_data.notification_name = vm.bookingpress_selected_default_notification_db_name
                    bookingpress_save_notification_data.notification_subject = vm.bookingpress_email_notification_subject
                    bookingpress_save_notification_data.notification_msg = bookingpress_email_notification_msg_data
                    bookingpress_save_notification_data.default_notification_status = vm.default_notification_status
                    bookingpress_save_notification_data.selected_default_notification = vm.bookingpress_selected_default_notification

                    bookingpress_save_notification_data.action = 'bookingpress_save_email_notification_data'                    
                    bookingpress_save_notification_data._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                    bookingpress_save_notification_data.additional_data = []

                    <?php
                    do_action('bookingpress_add_email_notification_data');
                    ?>

                    axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(bookingpress_save_notification_data))
                    .then( function (response) {
                        vm.is_disabled = false
                        vm.is_display_save_loader = '0'
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    }.bind(this))
                    .catch( function (error) {
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
                bookingpress_insert_placeholder(selected_tag)
                {
                    const vm = this

                    var bookingpress_textarea_element = document.getElementById("bookingpress_email_notification_subject_message");
                    var bookingpress_current_val = document.getElementById("bookingpress_email_notification_subject_message").value;
                    var bookingpress_start_pos = bookingpress_textarea_element.selectionStart;
                    var bookingpress_end_pos = bookingpress_textarea_element.selectionEnd;

                    var bookingpress_before_string = bookingpress_current_val.substring(0, bookingpress_start_pos);
                    var bookingpress_after_string = bookingpress_current_val.substring(bookingpress_end_pos, bookingpress_current_val.length);

                    var bookingpress_new_appended_string = bookingpress_before_string + selected_tag + bookingpress_after_string;
                    document.getElementById("bookingpress_email_notification_subject_message").value = bookingpress_new_appended_string;
                },
                bookingpress_change_tab(){
                    const vm = this
                    vm.bookingpress_get_notification_data(vm.bookingpress_active_email_notification, vm.bookingpress_selected_default_notification_db_name)
                },               
        <?php
        do_action('bookingpress_add_dynamic_notifications_vue_methods');
    }

    
    /**
     * Get all default notifications
     *
     * @return void
     */
    function bookingpress_get_default_notifications()
    {
        global $wpdb, $tbl_bookingpress_notifications;
        $bookingpress_default_notifications_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = 'default' AND bookingpress_notification_is_custom = 0", ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm
        return $bookingpress_default_notifications_data;
    }
    
    /**
     * Get custom notification from notification id
     *
     * @param  mixed $bookingpress_notification_id
     * @return void
     */
    function bookingpress_get_custom_notification( $bookingpress_notification_id = 0 )
    {
        global $wpdb, $tbl_bookingpress_notifications, $tbl_bookingpress_notification_services, $tbl_bookingpress_notification_events;

        $bookingpress_return_data = array();

        if ($bookingpress_notification_id != 0 ) {
            $bookingpress_get_custom_notification_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_id = %d", $bookingpress_notification_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm

            if (! empty($bookingpress_get_custom_notification_data) ) {
                $bookingpress_custom_notification_type = $bookingpress_get_custom_notification_data['bookingpress_notification_type'];
                if ($bookingpress_custom_notification_type == 'appointment' ) {
                    // Fetch all selected services of appointment
                    $bookingpress_fetch_selected_services = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notification_services} WHERE bookingpress_notification_id = %d", $bookingpress_notification_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notification_services is table name defined globally. False Positive alarm

                    $bookingpress_selected_services_arr = array();

                    foreach ( $bookingpress_fetch_selected_services as $bookingpress_fetch_selected_service_key => $bookingpress_fetch_selected_service_val ) {
                        array_push($bookingpress_selected_services_arr, $bookingpress_fetch_selected_service_val['bookingpress_notification_selected_service_id']);
                    }

                    $bookingpress_get_custom_notification_data['selected_appointments'] = $bookingpress_selected_services_arr;
                } elseif ($bookingpress_custom_notification_type == 'event' ) {
                    // Fetch all selected events
                    $bookingpress_fetch_selected_events = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notification_events} WHERE bookingpress_notification_id = %d", $bookingpress_notification_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notification_events is table name defined globally. False Positive alarm

                    $bookingpress_selected_events_arr = array();

                    foreach ( $bookingpress_fetch_selected_events as $bookingpress_fetch_selected_events_key => $bookingpress_fetch_selected_events_val ) {
                        array_push($bookingpress_selected_services_arr, $bookingpress_fetch_selected_events_val['bookingpress_notification_selected_event_id']);
                    }

                       $bookingpress_get_custom_notification_data['selected_events'] = $bookingpress_selected_services_arr;
                }
            }

            $bookingpress_return_data = $bookingpress_get_custom_notification_data;
        } else {
            $bookingpress_get_custom_notification_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_is_custom = 1", ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm

            if (! empty($bookingpress_get_custom_notification_data) ) {
                foreach ( $bookingpress_get_custom_notification_data as $bookingpress_get_custom_notification_key => $bookingpress_get_custom_notification_val ) {
                    $bookingpress_notification_id = $bookingpress_get_custom_notification_val['bookingpress_notification_id'];

                    $bookingpress_get_custom_notification_other_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_id = %d", $bookingpress_notification_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm

                    if (! empty($bookingpress_get_custom_notification_other_details) ) {
                        $bookingpress_custom_notification_type = $bookingpress_get_custom_notification_other_details['bookingpress_notification_type'];
                        if ($bookingpress_custom_notification_type == 'appointment' ) {
                               // Fetch all selected services of appointment
                               $bookingpress_fetch_selected_services = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notification_services} WHERE bookingpress_notification_id = %d", $bookingpress_notification_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notification_services is table name defined globally. False Positive alarm

                               $bookingpress_selected_services_arr = array();

                            foreach ( $bookingpress_fetch_selected_services as $bookingpress_fetch_selected_service_key => $bookingpress_fetch_selected_service_val ) {
                                array_push($bookingpress_selected_services_arr, $bookingpress_fetch_selected_service_val['bookingpress_notification_selected_service_id']);
                            }

                               $bookingpress_get_custom_notification_data[ $bookingpress_get_custom_notification_key ]['selected_appointments'] = $bookingpress_selected_services_arr;
                        } elseif ($bookingpress_custom_notification_type == 'event' ) {
                            // Fetch all selected events
                            $bookingpress_fetch_selected_events = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notification_events} WHERE bookingpress_notification_id = %d", $bookingpress_notification_id ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notification_events is table name defined globally. False Positive alarm

                            $bookingpress_selected_events_arr = array();

                            foreach ( $bookingpress_fetch_selected_events as $bookingpress_fetch_selected_events_key => $bookingpress_fetch_selected_events_val ) {
                                array_push($bookingpress_selected_services_arr, $bookingpress_fetch_selected_events_val['bookingpress_notification_selected_event_id']);
                            }

                            $bookingpress_get_custom_notification_data[ $bookingpress_get_custom_notification_key ]['selected_events'] = $bookingpress_selected_services_arr;
                        }
                    }
                }
            }

            $bookingpress_return_data = $bookingpress_get_custom_notification_data;
        }

        return $bookingpress_return_data;
    }
    
    /**
     * Save email notification data
     *
     * @return void
     */
    function bookingpress_save_email_notification_data()
    {
        global $wpdb, $tbl_bookingpress_notifications,$BookingPress,$bookingpress_global_options;
        $bookingpress_return_data = array();

        $bpa_check_authorization = $this->bpa_check_authentication( 'save_email_notification', true, 'bpa_wp_nonce' );
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
        $bookingpress_return_data['variant']                = 'error';
        $bookingpress_return_data['title']                  = esc_html__('Error', 'bookingpress-appointment-booking');
        $bookingpress_return_data['msg']                    = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
        $bookingpress_return_data['return_data']            = array();
        $bookingpress_return_data['is_custom_notification'] = 0;
        if (! empty($_REQUEST) ) {
            $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_allow_tag = json_decode($bookingpress_global_options_data['allowed_html'], true);

            $bookingpress_notification_receiver = ! empty($_REQUEST['notification_receiver']) ? sanitize_text_field($_REQUEST['notification_receiver']) : '';
            $bookingpress_notification_name     = ! empty($_REQUEST['notification_name']) ? sanitize_text_field($_REQUEST['notification_name']) : '';
            $bookingpress_notification_subject  = ! empty($_REQUEST['notification_subject']) ? wp_kses($_REQUEST['notification_subject'],$bookingpress_allow_tag) : '';
            $bookingpress_notification_msg              = ! empty($_REQUEST['notification_msg']) ? wp_kses($_REQUEST['notification_msg'], $bookingpress_allow_tag) : '';
            $bookingpress_notification_msg = htmlspecialchars_decode(stripslashes_deep($bookingpress_notification_msg));
            $bookingpress_default_selected_notification = ! empty($_REQUEST['selected_default_notification']) ? sanitize_text_field($_REQUEST['selected_default_notification']) : '';
            $bookingpress_default_notification_status = ! empty($_REQUEST['default_notification_status'][ $bookingpress_notification_receiver ][ $bookingpress_default_selected_notification ]) ? sanitize_text_field($_REQUEST['default_notification_status'][ $bookingpress_notification_receiver ][ $bookingpress_default_selected_notification ]) : '';

            //$bookingpress_where_condition = "WHERE bookingpress_notification_name = '" . $bookingpress_notification_name . "' AND bookingpress_notification_type = 'default' AND bookingpress_notification_receiver_type = '" . $bookingpress_notification_receiver . "'";

            $bookingpress_if_notification_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_notification_id) FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_type = %s AND bookingpress_notification_receiver_type = %s", $bookingpress_notification_name, 'default', $bookingpress_notification_receiver  ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_notifications is a table name
            $bookingpress_notification_id = 0;
            if ($bookingpress_default_notification_status == 'true' ) {
                $bookingpress_default_notification_status = 1;
            } else {
                $bookingpress_default_notification_status = 0;
            }

            $bookingpress_database_modify_data = array(
            'bookingpress_notification_receiver_type' => $bookingpress_notification_receiver,
            'bookingpress_notification_name'          => $bookingpress_notification_name,
            'bookingpress_notification_status'        => $bookingpress_default_notification_status,
            'bookingpress_notification_type'          => 'default',
            'bookingpress_notification_subject'       => $bookingpress_notification_subject,
            'bookingpress_notification_message'       => $bookingpress_notification_msg,
            'bookingpress_updated_at'                 => current_time('mysql'),
            );

            $bookingpress_database_modify_data = apply_filters('bookingpress_save_email_notification_data_filter', $bookingpress_database_modify_data, $_REQUEST);
            if ($bookingpress_if_notification_exists > 0 ) {
                // If notification exists then get its data
                $bookingpress_exist_record_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications}  WHERE bookingpress_notification_name = %s AND bookingpress_notification_type = %s AND bookingpress_notification_receiver_type = %s", $bookingpress_notification_name, 'default', $bookingpress_notification_receiver ), ARRAY_A ); //phpcs:ignore --WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_notifications is a table name
                $bookingpress_notification_id   = $bookingpress_exist_record_data['bookingpress_notification_id'];

                $bookingpress_modify_where_condition = array(
                'bookingpress_notification_id' => $bookingpress_notification_id,
                );

                $wpdb->update($tbl_bookingpress_notifications, $bookingpress_database_modify_data, $bookingpress_modify_where_condition);
            } else {
                $bookingpress_database_modify_data['bookingpress_created_at'] = current_time('mysql');

                $wpdb->insert($tbl_bookingpress_notifications, $bookingpress_database_modify_data);
                $bookingpress_notification_id = $wpdb->insert_id;
            }

            $bookingpress_return_data['variant']                = 'success';
            $bookingpress_return_data['title']                  = esc_html__('Success', 'bookingpress-appointment-booking');
            $bookingpress_return_data['msg']                    = esc_html__('Email notifications details updated successfully.', 'bookingpress-appointment-booking');
            $bookingpress_return_data['return_data']            = array();
            $bookingpress_return_data['is_custom_notification'] = 0;

            do_action('bookingpress_after_save_email_notification_data',$_REQUEST);
        }

        echo wp_json_encode($bookingpress_return_data);
        exit();
    }

    
    /**
     * Ajax request for get email notification data
     *
     * @return void
     */
    function bookingpress_get_email_notification_data()
    {
        global $wpdb, $tbl_bookingpress_notifications, $tbl_bookingpress_notification_services, $tbl_bookingpress_notification_events;

        $bookingpress_return_data = array();
        

        $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_email_notification', true, 'bpa_wp_nonce' );
            
        if( preg_match( '/error/', $bpa_check_authorization ) ){
            $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
            $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

            $bookingpress_return_data['variant'] = 'error';
            $bookingpress_return_data['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
            $bookingpress_return_data['msg'] = $bpa_error_msg;

            wp_send_json( $bookingpress_return_data );
            die;
        }

        $bookingpress_return_data['variant']                = 'error';
        $bookingpress_return_data['title']                  = esc_html__('Error', 'bookingpress-appointment-booking');
        $bookingpress_return_data['msg']                    = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
        $bookingpress_return_data['return_data']            = array();
        $bookingpress_return_data['is_custom_notification'] = 0;

        if (! empty($_REQUEST) ) {

            $bookingpress_notification_receiver_type = ! empty($_REQUEST['bookingpress_notification_receiver_type']) ? sanitize_text_field($_REQUEST['bookingpress_notification_receiver_type']) : '';
            $bookingpress_notification_type          = ! empty($_REQUEST['bookingpress_notification_type']) ? sanitize_text_field($_REQUEST['bookingpress_notification_type']) : '';
            $bookingpress_notification_name          = ! empty($_REQUEST['bookingpress_notification_name']) ? sanitize_text_field($_REQUEST['bookingpress_notification_name']) : '';

            if (! empty($bookingpress_notification_receiver_type) && ! empty($bookingpress_notification_type) && ! empty($bookingpress_notification_name) ) {
                //$bookingpress_where_condition = " WHERE bookingpress_notification_name = '" . $bookingpress_notification_name . "' AND bookingpress_notification_type = '" . $bookingpress_notification_type . "' AND bookingpress_notification_is_custom = 0 AND bookingpress_notification_receiver_type = '" . $bookingpress_notification_receiver_type . "'";

                $bookingpress_if_notification_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_notification_id) FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_type = %s AND bookingpress_notification_is_custom = %d AND bookingpress_notification_receiver_type = %s", $bookingpress_notification_name, $bookingpress_notification_type, 0, $bookingpress_notification_receiver_type ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_notifications is a table name

                if ($bookingpress_if_notification_exists > 0 ) {
                    $bookingpress_exist_record_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_type = %s AND bookingpress_notification_is_custom = %d AND bookingpress_notification_receiver_type = %s", $bookingpress_notification_name, $bookingpress_notification_type, 0, $bookingpress_notification_receiver_type ), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_notifications is a table name
                    $bookingpress_exist_record_data['bookingpress_notification_subject'] = stripslashes_deep($bookingpress_exist_record_data['bookingpress_notification_subject']);
                    $bookingpress_exist_record_data['bookingpress_notification_message'] = stripslashes_deep($bookingpress_exist_record_data['bookingpress_notification_message']);
                    $bookingpress_exist_record_data          = apply_filters('bookingpress_get_notifiacation_data_filter', $bookingpress_exist_record_data);
                    $bookingpress_return_data['return_data'] = $bookingpress_exist_record_data;
                    $bookingpress_return_data['msg']         = esc_html__('Data received successfully', 'bookingpress-appointment-booking');
                } else {
                    $bookingpress_return_data['msg'] = esc_html__('No data received', 'bookingpress-appointment-booking');
                }

                $bookingpress_return_data['variant']                = 'success';
                $bookingpress_return_data['title']                  = esc_html__('Success', 'bookingpress-appointment-booking');
                $bookingpress_return_data['is_custom_notification'] = 0;
            }
        }

        $bookingpress_return_data = apply_filters( 'bookingpress_get_email_notification_data_modified',$bookingpress_return_data,$_REQUEST);
        
        echo wp_json_encode($bookingpress_return_data);
        exit();
    }
    
    /**
     * Ajax request for get email notification status
     *
     * @return void
     */
    function bookingpress_get_all_default_notification_status()
    {

        $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_email_notification_status', true, 'bpa_wp_nonce' );
        
        if( preg_match( '/error/', $bpa_check_authorization ) ){
            $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
            $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');
            $response = array();
            $response['variant'] = 'error';
            $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
            $response['msg'] = $bpa_error_msg;

            wp_send_json( $response );
            die;
        }
        $bookingpress_default_notification_status_data['customer'] = array(
            'appointment_approved' => true,
            'appointment_pending'  => true,
            'appointment_rejected' => true,
            'appointment_canceled' => true,
            'share_appointment' => true,
        );
        $bookingpress_default_notification_status_data['employee'] = array(
            'appointment_approved' => true,
            'appointment_pending'  => true,
            'appointment_rejected' => true,
            'appointment_canceled' => true,
            'share_appointment' => true,
        );

        $bookingpress_default_notification_data = $this->bookingpress_get_default_notifications();
        foreach ( $bookingpress_default_notification_data as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
            $bookingpress_notification_value         = ( $bookingpress_default_notification_val['bookingpress_notification_status'] == 1 ) ? true : false;
            $bookingpress_notification_receiver_type = $bookingpress_default_notification_val['bookingpress_notification_receiver_type'];
            switch ( $bookingpress_default_notification_val['bookingpress_notification_name'] ) {
                case 'Appointment Approved':
                    $bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ]['appointment_approved'] = $bookingpress_notification_value;
                    break;
                case 'Appointment Pending':
                    $bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ]['appointment_pending'] = $bookingpress_notification_value;
                    break;
                case 'Appointment Rejected':
                    $bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ]['appointment_rejected'] = $bookingpress_notification_value;
                    break;
                case 'Appointment Canceled':
                    $bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ]['appointment_canceled'] = $bookingpress_notification_value;
                    break;
                case 'Share Appointment URL':
                    $bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ]['share_appointment'] = $bookingpress_notification_value;
                    break;
            }
        }
        $bookingpress_default_notification_status_data = apply_filters('add_bookingpress_default_notification_status', $bookingpress_default_notification_status_data, $bookingpress_default_notification_data);
        echo wp_json_encode($bookingpress_default_notification_status_data);
        exit();
    }
}

global $bookingpress_notifications;
$bookingpress_notifications = new bookingpress_notifications();

?>

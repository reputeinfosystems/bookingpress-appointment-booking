<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_service_categories') ) {
    class bookingpress_service_categories Extends BookingPress_Core
    {
        function __construct()
        {
            add_action('wp_ajax_bookingpress_get_categories', array( $this, 'bookingpress_get_categories' ));
            add_action('wp_ajax_bookingpress_add_categories', array( $this, 'bookingpress_add_categories' ));
            add_action('wp_ajax_bookingpress_edit_category', array( $this, 'bookingpress_edit_category' ));
            add_action('wp_ajax_bookingpress_delete_category', array( $this, 'bookingpress_delete_category' ));
            add_action('wp_ajax_bookingpress_bulk_category', array( $this, 'bookingpress_bulk_category' ));
            add_action('wp_ajax_bookingpress_position_categories', array( $this, 'bookingpress_position_categories' ));

            add_action('bookingpress_categories_dynamic_onload_method', array( $this, 'bookingpress_dynamic_on_load_method' ));
            add_action('bookingpress_categories_dynamic_vue_methods', array( $this, 'bookingpress_dynamic_method_function' ));
            add_action('bookingpress_categories_dynamic_data_variables', array( $this, 'bookingpress_dynamic_data_variable_function' ));
            add_action('bookingpress_categories_dynamic_helper_variables', array( $this, 'bookingpress_dynamic_helper_variables_function' ));
            add_action('bookingpress_categories_dynamic_directives', array( $this, 'bookingpress_dynamic_directive_function' ));

            add_action( 'admin_init', array( $this, 'bookingpress_category_vue_data' ) );
        }
        
        /**
         * Data variables for service categories
         *
         * @return void
         */
        function bookingpress_category_vue_data(){
            global $bookingpress_category_vue_data,$bookingpress_global_options;
            $bookingpress_options             = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang         = $bookingpress_options['locale'];
            $bookingpress_pagination          = $bookingpress_options['pagination'];
            $bookingpress_pagination_arr      = json_decode($bookingpress_pagination, true);
            $bookingpress_pagination_selected = $bookingpress_pagination_arr[0];

            $bookingpress_category_vue_data = array(
                'bulk_action'                => 'bulk_action',
                'bulk_options'               => array(
                    array(
                        'value' => 'bulk_action',
                        'label' => esc_html__('Bulk Action', ' bookingpress-appointment-booking'),
                    ),
                    array(
                        'value' => 'delete',
                        'label' => esc_html__('Delete', ' bookingpress-appointment-booking'),
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
                'service_category'           => array(
                    'service_category_name'      => '',
                    'service_category_update_id' => 0,
                ),
                'open_category_modal'        => false,
                'modal_loader'               => 1,
                'rules'                      => array(
                    'service_category_name' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter category name', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'savebtnloading'             => false,
            );

        }
        
        /**
         * Register directive for service categories
         *
         * @return void
         */
        function bookingpress_dynamic_directive_function()
        {
            echo esc_html('sortable');
        }
        
        /**
         * Service categories helper variables
         *
         * @return void
         */
        function bookingpress_dynamic_helper_variables_function()
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
            do_action('bookingpress_categories_add_dynamic_helper_vars');
        }
        
        /**
         * Service categories data variables
         *
         * @return void
         */
        function bookingpress_dynamic_data_variable_function()
        {
            global $bookingpress_category_vue_data;
            echo wp_json_encode($bookingpress_category_vue_data);
        }
        
        /**
         * Service categories module on load methods
         *
         * @return void
         */
        function bookingpress_dynamic_on_load_method()
        {
            ?>
            this.loadServiceCategory();
            <?php
            do_action('bookingpress_categories_add_dynamic_on_load_method');
        }
        
        /**
         * Service category methods / functions
         *
         * @return void
         */
        function bookingpress_dynamic_method_function()
        {
            global $bookingpress_notification_duration;
            ?>
            toggleBusy() {
                this.loading = !this.loading
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            handleSizeChange(val) {
                this.perPage = val
                this.loadServiceCategory()
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.loadServiceCategory()
            },
            async loadServiceCategory() {
                this.toggleBusy();
                var postData = { action:'bookingpress_get_categories', perpage:this.perPage, currentpage:this.currentPage,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    this.toggleBusy();
                    this.items = response.data.items;
                    this.totalItems = response.data.total;
                    <?php do_action('bookingpress_after_load_service_category_data'); ?>
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);
                });
            },
            open_add_service_modal() {
                this.resetForm()
                this.open_category_modal = true
                this.bookingpress_loader_hide()
            },
            bookingpress_loader_hide() {
                this.modal_loader = 0
            },
            saveCategoryDetails: function(service_category) {
                this.$refs[service_category].validate((valid) => {
                    if (valid) {
                        const vm = new Vue()
                        const vm2 = this
                        vm2.savebtnloading = true
                        var postdata = this.service_category;
                        postdata.action = 'bookingpress_add_categories';
                        postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
                        .then(function(response){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm2.savebtnloading = false
                            if (response.data.variant == 'success') {
                                vm2.loadServiceCategory()
                                vm2.open_category_modal = false
                            }
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
                    } else {
                        return false;
                    }
                });
            },
            editServiceCategoryData(index, row) {
                const vm = new Vue()
                const vm2 = this
                vm2.open_category_modal = true
                var edit_id = row.category_id;
                var service_category_edit_data = { action:'bookingpress_edit_category', edit_id: edit_id,'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_category_edit_data ) )
                .then(function(response){
                    vm2.service_category.service_category_update_id = response.data.category_id
                    vm2.service_category.service_category_name = response.data.category_name
                    vm2.bookingpress_loader_hide()
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
            deleteServiceCategory(index, row) {
                const vm = new Vue()
                const vm2 = this
                var delete_id = row.category_id;
                var service_category_delete_data = { action:'bookingpress_delete_category', delete_id: delete_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_category_delete_data ) )
                .then(function(response){
                    vm2.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    vm2.loadServiceCategory()
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
            delete_bulk_categories() {
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
                        var service_category_delete_data = {
                            action:'bookingpress_bulk_category',
                            delete_ids: this.multipleSelection,
                            bulk_action: 'delete',
                            _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
                        }
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_category_delete_data ) )
                        .then(function(response){
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm2.loadServiceCategory();
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
                        vm2.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                            message: '<?php esc_html_e('Please select one or more records.', 'bookingpress-appointment-booking'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });
                    }
                }
            },
            resetForm() {
                this.service_category.service_category_name = ''
                this.service_category.service_category_update_id = 0
            },
            closeServiceCategoryModal() {
                const vm2 = this
                vm2.$refs['service_category'].resetFields()
                vm2.open_category_modal = false
            },
            <?php
            do_action('bookingpress_categories_add_dynamic_vue_methods');
        }
        
        /**
         * Get all categories
         *
         * @return void
         */
        function bookingpress_get_categories()
        {
            global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_categories;

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_categories', true, 'bpa_wp_nonce' );
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
            $perpage            = isset($_POST['perpage']) ? intval($_POST['perpage']) : 10; // phpcs:ignore WordPress.Security.NonceVerification
            $currentpage        = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1; // phpcs:ignore WordPress.Security.NonceVerification
            $offset             = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
            $total_categories   = $wpdb->get_results('SELECT * FROM ' . $tbl_bookingpress_categories . ' order by bookingpress_category_position ASC', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
            $categories         = $wpdb->get_results('SELECT * FROM ' . $tbl_bookingpress_categories . ' order by bookingpress_category_position ASC', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
            $service_categories = array();
            $service_categories = apply_filters('bookingpress_add_new_category_option', $service_categories);
            if (! empty($categories) ) {
                $counter = 1;
                foreach ( $categories as $category ) {
                    $service_category                  = array();
                    $service_category['id']            = $counter;
                    $service_category['category_id']   = $category['bookingpress_category_id'];
                    $service_category['category_name'] = stripslashes_deep($category['bookingpress_category_name']);
                    $total_services                    = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_category_id = %d' , $category['bookingpress_category_id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm

                    $service_category['total_services'] = count($total_services);
                    $service_categories[]               = $service_category;
                    $counter++;
                }
            }
            $data['items'] = $service_categories;
            $data['total'] = count($total_categories);
            $data = apply_filters('bookingpress_modified_service_category_response_data', $data);
            wp_send_json($data);
        }
        
        /**
         * Add/Update service category details
         *
         * @return void
         */
        function bookingpress_add_categories()
        {
            global $wpdb, $tbl_bookingpress_categories;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'add_categories', true, 'bpa_wp_nonce' );
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

            $category_id         = isset($_POST['service_category_update_id']) ? intval($_POST['service_category_update_id']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
            $category_name       = isset($_POST['service_category_name']) ? trim(sanitize_text_field($_POST['service_category_name'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            if (! empty($category_name) ) {
                if (strlen($category_name) > 255 ) {
                    $response['msg'] = esc_html__('Category name is too long...', 'bookingpress-appointment-booking');
                } else {
                    if (! empty($category_id) ) {
                        $args = array(
                        'bookingpress_category_name' => sanitize_text_field($category_name),
                        );
                        $wpdb->update($tbl_bookingpress_categories, $args, array( 'bookingpress_category_id' => $category_id ));
                        $response['variant'] = 'success';
                        $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg']     = esc_html__('Category has been updated successfully.', 'bookingpress-appointment-booking');
                    } else {
                        $category_position = 0;
                        $category          = $wpdb->get_row('SELECT * FROM ' . $tbl_bookingpress_categories . ' ORDER BY bookingpress_category_position DESC LIMIT 1', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
                        if (! empty($category) ) {
                            $category_position = $category['bookingpress_category_position'] + 1;
                        }
                        $date = current_time('mysql');
                        $args = array(
                        'bookingpress_category_name' => sanitize_text_field($category_name),
                        'bookingpress_category_position' => $category_position,
                        'bookingpress_categorydate_created' => $date,
                        );
                        $wpdb->insert($tbl_bookingpress_categories, $args, array( '%s', '%d', '%s' ));
                        $category_id             = $wpdb->insert_id;
                        $response['category_id'] = strval($category_id);
                        $response['variant']     = 'success';
                        $response['title']       = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg']         = esc_html__('Category has been created successfully.', 'bookingpress-appointment-booking');
                    }
                }
            } elseif (empty($category_name) ) {
                $response['msg'] = esc_html__('Please add valid data for category name', 'bookingpress-appointment-booking') . '.';
            }
            wp_send_json($response);
        }
        
        /**
         * Get edit cateory details
         *
         * @return void
         */
        function bookingpress_edit_category()
        {
            global $wpdb, $tbl_bookingpress_categories;
            $response              = array();
            
            $bpa_check_authorization = $this->bpa_check_authentication( 'edit_categories', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $category_id         = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            if (! empty($category_id) ) {
                $category = $wpdb->get_row( $wpdb->prepare('SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_id = %d', $category_id), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
                if (! empty($category) ) {
                    $response['category_id']   = intval($category['bookingpress_category_id']);
                    $response['category_name'] = stripslashes_deep($category['bookingpress_category_name']);
                    $response['variant']       = 'success';
                    $response['title']         = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg']           = esc_html__('Category Data.', 'bookingpress-appointment-booking');
                }
            }
            wp_send_json($response);
        }
        
        /**
         * Delete category ajax request and function also
         *
         * @param  mixed $category_id
         * @return void
         */
        function bookingpress_delete_category( $category_id = '' )
        {
            global $wpdb, $tbl_bookingpress_categories,$tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'delete_categories', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $category_id               = isset($_POST['delete_id']) ? intval($_POST['delete_id']) : $category_id; // phpcs:ignore WordPress.Security.NonceVerification
            $response['variant']       = 'error';
            $response['title']         = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']           = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $return                    = false;
            $total_categories_services = $wpdb->get_results( $wpdb->prepare( 'SELECT `bookingpress_service_id` FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_category_id = %d', $category_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
            if (! empty($total_categories_services) ) {
                $response['variant'] = 'warning';
                $response['title']   = esc_html__('warning', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('I am sorry', 'bookingpress-appointment-booking') . '! ' . esc_html__('This category can not be deleted because it has one or more services associated with it', 'bookingpress-appointment-booking') . '.';
                wp_send_json($response);
                exit;
            }
            if (! empty($category_id) ) {
                $total_categories = $wpdb->get_results('SELECT * FROM ' . $tbl_bookingpress_categories . ' order by bookingpress_category_position ASC', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
                $new_position     = count($total_categories) - 1;
                $category         = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_id = %d', $category_id ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
                if ($category['bookingpress_category_position'] != $new_position ) {
                    $this->bookingpress_position_categories($category['bookingpress_category_position'], $new_position);
                }
                $wpdb->delete($tbl_bookingpress_categories, array( 'bookingpress_category_id' => $category_id ), array( '%d' ));
                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Category has been deleted successfully', 'bookingpress-appointment-booking');
                $return              = true;
            }
            if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_delete_category' ) { // phpcs:ignore WordPress.Security.NonceVerification
                wp_send_json($response);
            }
            return $return;
        }

        /**
         * Category module bulk action
         *
         * @return void
         */
        function bookingpress_bulk_category()
        {
            global $wpdb, $tbl_bookingpress_categories,$BookingPress;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'delete_categories', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $response['variant'] = 'danger';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            if (! empty($_POST['bulk_action']) && sanitize_text_field($_POST['bulk_action']) == 'delete' ) { // phpcs:ignore WordPress.Security.NonceVerification
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['delete_ids'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
                $delete_ids = ! empty($_POST['delete_ids']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['delete_ids']) : array(); // phpcs:ignore
                if (! empty($delete_ids) ) {
                    foreach ( $delete_ids as $delete_key => $delete_val ) {
                        if (is_array($delete_val) ) {
                            $delete_val = $delete_val['category_id'];
                        }
                        $this->bookingpress_delete_category($delete_val);
                        if ($return ) {
                            $response['variant'] = 'success';
                            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                            $response['msg']     = esc_html__('Categories has been deleted successfully', 'bookingpress-appointment-booking');
                        }
                    }
                }
            }
            wp_send_json($response);
        }

        /**
         * Change categories position
         *
         * @param  mixed $old_position
         * @param  mixed $new_position
         * @return void
         */
        function bookingpress_position_categories( $old_position = '', $new_position = '' )
        {
            global $wpdb, $tbl_bookingpress_categories;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'update_category_position', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $old_position        = isset($_POST['old_position']) ? intval($_POST['old_position']) : $old_position; // phpcs:ignore WordPress.Security.NonceVerification
            $new_position        = isset($_POST['new_position']) ? intval($_POST['new_position']) : $new_position; // phpcs:ignore WordPress.Security.NonceVerification
            $response['variant'] = 'danger';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

            if ($new_position > $old_position ) {
                //$condition  = 'BETWEEN ' . $old_position . ' AND ' . $new_position;
                $categories = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_position BETWEEN %d AND %d order by bookingpress_category_position ASC', $old_position, $new_position ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm                
                foreach ( $categories as $category ) {
                    $position = $category['bookingpress_category_position'] - 1;
                    $position = ( $category['bookingpress_category_position'] == $old_position ) ? $new_position : $position;
                    $args     = array(
                        'bookingpress_category_position' => $position,
                    );
                    $wpdb->update($tbl_bookingpress_categories, $args, array( 'bookingpress_category_id' => $category['bookingpress_category_id'] ));
                }
            } else {
                $categories = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_position BETWEEN %d AND %d order by bookingpress_category_position ASC', $new_position, $old_position ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm                                
                foreach ( $categories as $category ) {
                    $position = $category['bookingpress_category_position'] + 1;
                    $position = ( $category['bookingpress_category_position'] == $old_position ) ? $new_position : $position;
                    $args     = array(
                    'bookingpress_category_position' => $position,
                    );
                    $wpdb->update($tbl_bookingpress_categories, $args, array( 'bookingpress_category_id' => $category['bookingpress_category_id'] ));
                }
            }
            $response['variant'] = 'success';
            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Category position has been changed successfully.', 'bookingpress-appointment-booking');
            
            if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_position_categories' ) { // phpcs:ignore WordPress.Security.NonceVerification
                wp_send_json($response);
            }
            return;
        }
    }
}
global $bookingpress_service_categories;
$bookingpress_service_categories = new bookingpress_service_categories();

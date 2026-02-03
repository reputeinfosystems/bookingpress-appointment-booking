<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_services') ) {
    class bookingpress_services Extends BookingPress_Core
    {
        function __construct()
        {
            
            add_action('wp_ajax_bookingpress_get_services', array( $this, 'bookingpress_get_services' ));
            add_action('wp_ajax_bookingpress_add_service', array( $this, 'bookingpress_add_service' ));
            add_action('wp_ajax_bookingpress_edit_service', array( $this, 'bookingpress_edit_service' ));
            add_action('wp_ajax_bookingpress_delete_service', array( $this, 'bookingpress_delete_service' ));
            add_action('wp_ajax_bookingpress_bulk_service', array( $this, 'bookingpress_bulk_service' ));
            add_action('wp_ajax_bookingpress_position_services', array( $this, 'bookingpress_position_services' ));
            add_action('wp_ajax_bookingpress_duplicate_service', array( $this, 'bookingpress_duplicate_service' ));
            add_action('wp_ajax_bookingpress_get_search_categories', array( $this, 'bookingpress_search_categories' ));

            add_action('bookingpress_services_dynamic_vue_methods', array( $this, 'bookingpress_service_dynamic_vue_methods_func' ), 10);
            add_action('bookingpress_services_dynamic_on_load_methods', array( $this, 'bookingpress_service_dynamic_on_load_methods_func' ), 10);
            add_action('bookingpress_services_dynamic_data_fields', array( $this, 'bookingpress_service_dynamic_data_fields_func' ), 10);
            add_action('bookingpress_services_dynamic_directives', array( $this, 'bookingpress_service_dynamic_directives_func' ), 10);
            add_action('bookingpress_services_dynamic_helper_vars', array( $this, 'bookingpress_service_dynamic_helper_func' ), 10);

            add_action('bookingpress_services_dynamic_view_load', array( $this, 'bookingpress_service_dynamic_view_load_func' ), 10);

            add_action('wp_ajax_bookingpress_upload_service', array( $this, 'bookingpress_upload_service_func' ), 10);

            add_action( 'admin_init', array( $this, 'bookingpress_service_vue_data_fields') );

            add_action( 'wp_ajax_bookingpress_remove_service_file', array( $this, 'bookingpress_remove_service_file_func') );
            
        }
        
        /**
         * Service module default data variables
         *
         * @return void
         */
        function bookingpress_service_vue_data_fields(){

            global $bookingpress_services_vue_data_fields,$bookingpress_global_options;
            $bookingpress_options             = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_pagination          = $bookingpress_options['pagination'];
            $bookingpress_pagination_arr      = json_decode($bookingpress_pagination, true);
            $bookingpress_pagination_selected = $bookingpress_pagination_arr[0];

            $bookingpress_services_vue_data_fields = array(
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
                'category_items'             => array(),
                'multipleSelection'          => array(),
                'perPage'                    => $bookingpress_pagination_selected,
                'totalItems'                 => 0,
                'pagination_selected_length' => $bookingpress_pagination_selected,
                'pagination_length'          => $bookingpress_pagination,
                'currentPage'                => 1,
                'search_service_name'        => '',
                'search_service_category'    => ! empty($_REQUEST['bookingpress_cat_id']) ? intval($_REQUEST['bookingpress_cat_id']) : '',
                'search_categories'          => array(),
                'service'                    => array(
                    'service_image'          => '',
                    'service_image_name'     => '',
                    'service_images_list'    => array(),
                    'service_name'           => '',
                    'service_category'       => null,
                    'service_duration_val'   => 30,
                    'service_duration_unit'  => 'm',
                    'service_price'          => '',
                    'service_description'    => '',
                    'service_update_id'      => 0,
                    'service_price_currency' => '',
                ),
                'open_service_modal'         => false,
                'open_manage_category_modal' => false,
                'modal_loader'               => 1,
                'activeTabName'              => 'details',
                'serviceCatOptions'          => array(),
                'rules'                      => array(
                    'service_name'         => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter service name', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'service_duration_val' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter duration', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                    'service_price'        => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter price', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'categoryRules'              => array(
                    'service_category_name' => array(
                        array(
                            'required' => true,
                            'message'  => esc_html__('Please enter category name', 'bookingpress-appointment-booking'),
                            'trigger'  => 'blur',
                        ),
                    ),
                ),
                'savebtnloading'             => false,
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
                'service_category'           => array(
                    'service_category_name'      => '',
                    'service_category_update_id' => 0,
                ),
                'open_category_modal'        => false,
                'open_add_category_modal'    => false,
                'serviceShowFileList'        => false,
                'addCategoryModal'           => false,
                'editCategoryModal'          => false,
                'dragging'                   => false,
                'enabled'                    => true,
                'category_modal_pos'         => '80px',
                'is_display_loader'          => '0',
                'is_disabled'                => false,
                'is_display_save_loader'     => '0',
                'is_multiple_checked'        => false,
            );
        }

        function bookingpress_remove_service_file_func(){
            global $wpdb;
            $response = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'remove_service_avatar', true, 'bpa_wp_nonce' );
            
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
         * Upload temporary image at server
         *
         * @return void
         */
        function bookingpress_upload_service_func()
        {
            global $BookingPress;

            $return_data = array(
                'error'            => 0,
                'msg'              => '',
                'upload_url'       => '',
                'upload_file_name' => '',
            );

            $bpa_check_authorization = $this->bpa_check_authentication( 'upload_service_avatar', true, 'bookingpress_upload_service' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');
                $response = array();
                $response['variant'] = 'error';
                $response['error'] = 1;
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
         
            $bookingpress_fileupload_obj = new bookingpress_fileupload_class($_FILES['file']); // phpcs:ignore

            if (! $bookingpress_fileupload_obj ) {
                $return_data['error'] = 1;
                $return_data['msg']   = $bookingpress_fileupload_obj->error_message;
            }

            $bookingpress_fileupload_obj->check_cap          = true;
            $bookingpress_fileupload_obj->check_nonce        = true;
            $bookingpress_fileupload_obj->nonce_data         = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bookingpress_fileupload_obj->nonce_action       = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
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
                    $return_data['upload_error'] = $upload_file;
                    $return_data['msg']   = ! empty($bookingpress_fileupload_obj->error_message) ? $bookingpress_fileupload_obj->error_message : esc_html__('Something went wrong while updating the file', 'bookingpress-appointment-booking');
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
         * Load service module view file
         *
         * @return void
         */
        function bookingpress_service_dynamic_view_load_func()
        {
            $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/services/manage_services.php';
            $bookingpress_load_file_name = apply_filters('bookingpress_modify_service_view_file_path', $bookingpress_load_file_name);

            include $bookingpress_load_file_name;
        }
        
        /**
         * Service module helper variables
         *
         * @return void
         */
        function bookingpress_service_dynamic_helper_func()
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
         * Register service module directives
         *
         * @return void
         */
        function bookingpress_service_dynamic_directives_func()
        {
            echo esc_html('sortable');
        }
        
        /**
         * Service module dynamic data fields
         *
         * @return void
         */
        function bookingpress_service_dynamic_data_fields_func()
        {
            global $BookingPress, $bookingpress_services_vue_data_fields,$wpdb,$tbl_bookingpress_categories;

            // Get Per Page Records
            $per_page_records                                 = $BookingPress->bookingpress_get_settings('per_page_item', 'general_setting');
            $bookingpress_services_vue_data_fields['perPage'] = ! empty($per_page_records) ? $per_page_records : 10;

            $bookingpress_services_vue_data_fields['price_number_of_decimals'] = $BookingPress->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');

            $categories                             = $wpdb->get_results('SELECT bookingpress_category_id,bookingpress_category_name FROM ' . $tbl_bookingpress_categories . ' order by bookingpress_category_position ASC', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
            $bookingpress_service_categories_item   = array();
            $bookingpress_service_categories_item[] = array(
            'value' => '',
            'label' => esc_html__('Select Category', 'bookingpress-appointment-booking'),
            );
            foreach ( $categories as $key => $value ) {
                $bookingpress_service_categories_item[] = array(
                'value' => $value['bookingpress_category_id'],
                'label' => stripslashes_deep($value['bookingpress_category_name']),
                );
            }
            $bookingpress_payment_deafult_currency                           = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
            $bookingpress_payment_deafult_currency                           = $BookingPress->bookingpress_get_currency_symbol($bookingpress_payment_deafult_currency);
            $bookingpress_services_vue_data_fields['service_price_currency'] = $bookingpress_payment_deafult_currency;
            $bookingpress_services_vue_data_fields['serviceCatOptions']      = $bookingpress_service_categories_item;
            $bookingpress_default_time_duration_data                         = $BookingPress->bookingpress_get_default_timeslot_data();

            $bookingpress_services_vue_data_fields['service']['service_duration_val']  = ! empty($bookingpress_default_time_duration_data['time_duration']) ? $bookingpress_default_time_duration_data['time_duration'] : 30;
            $bookingpress_services_vue_data_fields['service']['service_duration_unit'] = ! empty($bookingpress_default_time_duration_data['time_unit']) ? $bookingpress_default_time_duration_data['time_unit'] : 'm';

            $bookingpress_services_vue_data_fields = apply_filters('bookingpress_modify_service_data_fields', $bookingpress_services_vue_data_fields);

            echo wp_json_encode($bookingpress_services_vue_data_fields);
        }
        
        /**
         * Service module on load methods
         *
         * @return void
         */
        function bookingpress_service_dynamic_on_load_methods_func()
        {
            ?>
            this.loadServices().catch(error => {
                console.error(error)
            })
            this.loadSearchCategories()
            this.loadServiceCategory()
            <?php
            do_action('bookingpress_add_service_dynamic_on_load_methods');
        }
        
        /**
         * Service module functions / methods
         *
         * @return void
         */
        function bookingpress_service_dynamic_vue_methods_func()
        {
            global $BookingPress,$bookingpress_notification_duration;
            $bookingpress_default_time_duration_data = $BookingPress->bookingpress_get_default_timeslot_data();
            $bookingpress_default_time_duration      = ! empty($bookingpress_default_time_duration_data['time_duration']) ? $bookingpress_default_time_duration_data['time_duration'] : 30;
            $bookingpress_default_time_unit          = ! empty($bookingpress_default_time_duration_data['time_unit']) ? $bookingpress_default_time_duration_data['time_unit'] : 'm';
            ?>
            change_service_unit_after(){
                <?php do_action('bookingpress_after_change_service_unit'); ?>
            },
            searchCategoryData(category_id){
                const vm = this
                vm.search_service_category = category_id
                vm.loadServices()
                vm.open_manage_category_modal = false
            },
            clearBulkAction(){
                const vm = this
                vm.bulk_action = 'bulk_action';
                vm.multipleSelection = []
                vm.items.forEach(function(selectedVal, index, arr) {            
                    selectedVal.selected = false;
                })
                vm.is_multiple_checked = false;
            },
            selectAllServices(isChecked){
                const vm = this                
                var selected_service_parent = '';
                if(isChecked)
                {    
                    vm.items.forEach(function(selectedVal, index, arr) {                                                                
                        if( selectedVal.service_bulk_action == false) {                                    
                            vm.multipleSelection.push(selectedVal.service_id);                                                                    
                            selectedVal.selected = true;                                                        
                        }
                    })                            
                }
                else
                {
                    vm.clearBulkAction()
                }
            },
            handleSelectionChange(e, isChecked, service_id) {                
                const vm = this                                
                vm.bulk_action = 'bulk_action';
                if(isChecked){
                    vm.multipleSelection.push(service_id);
                }else{
                    var removeIndex = vm.multipleSelection.indexOf(service_id);
                    if(removeIndex > -1){
                        vm.multipleSelection.splice(removeIndex, 1);
                    }
                }
            },
            handleSizeChange(val) {
                this.perPage = val
                this.loadServices()
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.loadServices()
            },
            async loadServiceCategory() {
                var postData = { action:'bookingpress_get_categories', perpage:this.perPage, currentpage:this.currentPage,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    this.category_items = response.data.items;
                    <?php do_action('bookingpress_after_load_service_category_data'); ?>
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);
                });
            },
            open_add_category_modal_func(currentElement){
                this.resetCategoryForm()
                this.open_add_category_modal = true

                this.bpa_adjust_popup_position( currentElement,'div#bpa_category_add_modal .el-dialog.bpa-dialog--add-category' );

            },
            resetCategoryForm() {
                this.service_category.service_category_name = ''
                this.service_category.service_category_update_id = 0
                this.category_modal_pos = '80px'
            },
            resetFilter(){
                const vm = this
                vm.search_service_name = ''
                vm.search_service_category = []
                vm.loadServices()
                vm.is_multiple_checked = false;
                vm.multipleSelection = [];
            },
            saveCategoryDetails: function(service_category) {
                this.$refs[service_category].validate((valid) => {
                    if (valid) {
                        const vm = new Vue()
                        const vm2 = this
                        vm2.is_disabled = true
                        vm2.is_display_save_loader = '1'
                        vm2.savebtnloading = true
                        var postdata = this.service_category;
                        postdata.action = 'bookingpress_add_categories';                        
                        postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
                        .then(function(response){
                            vm2.is_disabled = false
                            vm2.is_display_save_loader = '0'
                            vm2.open_category_modal = false
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm2.savebtnloading = false
                            vm2.loadSearchCategories()
                            if (response.data.variant == 'success') {
                                vm2.loadServiceCategory()
                                vm2.open_add_category_modal = false
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
            editServiceCategoryData(edit_id, currentElement) {
                const vm = new Vue()
                const vm2 = this
                vm2.resetCategoryForm()
                var dialog_pos = currentElement.target.getBoundingClientRect();
                vm2.category_modal_pos = (dialog_pos.top - 96)+'px'
                vm2.service_category.service_category_update_id = edit_id;
                vm2.open_add_category_modal = true;

                vm2.bpa_adjust_popup_position( currentElement,'div#bpa_category_add_modal .el-dialog.bpa-dialog--add-category' );

                var service_category_edit_data = { action:'bookingpress_edit_category', edit_id: edit_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_category_edit_data ) )
                .then(function(response){
                    vm2.service_category.service_category_name = response.data.category_name
                    vm2.bookingpress_loader_hide()
                    vm2.$refs.serviceCatName.$el.children[0].focus()
                    vm2.$refs.serviceCatName.$el.children[0].blur()
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
            updateCategoryPos: function(currentElement) {
                var new_index = currentElement.newIndex;
                var old_index = currentElement.oldIndex;
                const vm = new Vue()
                const vm2 = this
                var postData = { action: 'bookingpress_position_categories', old_position: old_index, new_position: new_index, currentPage : this.currentPage, perPage: this.perPage,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'};
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then(function(response){
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
            deleteServiceCategory(delete_id) {
                const vm = new Vue()
                const vm2 = this
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
            saveServiceData(){
                const vm = new Vue()
                const vm2 = this

                <?php do_action('bookingpress_before_save_service_validation'); ?>
                
                vm2.$refs["service"].validate((valid) => {
                    if (valid) {
						var postdata = Object.assign( {}, vm2.service );
						postdata.action = 'bookingpress_add_service';
						<?php do_action('bookingpress_add_posted_data_for_save_service'); ?>
						vm2.is_disabled = true
						vm2.is_display_save_loader = '1'
						vm2.savebtnloading = true;

                        postdata._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
                        .then(function(response){
                            if(response.data.variant != 'error'){
                                vm2.closeServiceModal();
                            }
                            vm2.is_disabled = false
                            vm2.is_display_save_loader = '0'                            
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            vm2.savebtnloading = false
                            if (response.data.variant == 'success') {
                                vm2.service.service_update_id = response.data.service_id
                                vm2.loadServices()
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
                        vm2.activeTabName = 'details';
                        return false;
                    }
                });
            },
            async loadServices() {
                const vm = this
                vm.is_display_loader = '1'
                vm.enabled = true;
                var bookingpress_search_data = { 'selected_category_id': this.search_service_category, 'service_name': this.search_service_name }
                var postData = { action:'bookingpress_get_services', perpage:this.perPage, currentpage:this.currentPage, search_data: bookingpress_search_data,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.is_display_loader = '0';
                    if( "error" == response.data.variant ){
                        vm.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval( $bookingpress_notification_duration); ?>
                        });
                    } else {
                        if(this.search_service_name!='' || (this.search_service_category != "all" && this.search_service_category != '')) {
                        vm.enabled = false;
                        }
                        this.items = response.data.items;
                        this.totalItems = response.data.total;
                        setTimeout(function(){
                            vm.bookingpress_remove_focus_for_popover();
                        },1000);
                    }
                }.bind(this) )
                .catch( function (error) {
                    vm.is_display_loader = '0'
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
            async loadSearchCategories() {
                var postData = { action:'bookingpress_get_search_categories', _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) );?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    this.search_categories = response.data;
                }.bind(this) )
                .catch( function (error) {
                    console.log(error);
                });
            },
            open_add_service_modal(action = 'add') {                                                
                const vm = this;
                vm.open_service_modal = true;
                if(action ==  'add') {
                    vm.resetForm()
                    var serviceCatData = [];
                    categoriesitems = this.category_items;
                    categoriesitems.map(function(value, key) {
                        serviceCatData.push({value: value.category_id, label: value.category_name});
                    })
                    vm.serviceCatOptions = serviceCatData;
                    setTimeout(function(){
                        vm.$refs['service'].resetFields();
                    },500);
                    vm.bookingpress_loader_hide()
                    <?php
                    do_action('bookingpress_after_open_add_service_model');
                    ?>
                }
            },
            get_categories() {
                const vm = new Vue()
                const vm2 = this
                var service = { action: 'bookingpress_get_categories',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service ) )
                .then(function(response){                    
                    var serviceCatData = [];
                    categoriesitems = response.data.items;
                    categoriesitems.map(function(value, key) {
                        serviceCatData.push({value: value.category_id, label: value.category_name});
                    })
                    vm2.serviceCatOptions = serviceCatData
                }).catch(function(error){
                    console.log(error);
                });
            },
            bookingpress_loader_hide() {
                this.modal_loader = 0
            },
            openEditService(edit_id){
                const vm = new Vue()
                const vm2 = this
                vm2.service.service_update_id = edit_id
                vm2.open_add_service_modal('edit');
                //vm2.get_categories()
                var service_edit_data = { action: 'bookingpress_edit_service',edit_id: edit_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post(appoint_ajax_obj.ajax_url, Qs.stringify( service_edit_data ) )
                .then(function(response){                        
                        vm2.service.service_name = response.data.service_name
                        vm2.service.service_category = response.data.category_id
                        vm2.service.service_duration_val = response.data.service_duration
                        vm2.service.service_duration_unit = response.data.service_duration_unit
                        vm2.service.service_price = response.data.service_price
                        vm2.service.service_description = response.data.service_description
                        if(response.data.service_image_details != undefined && response.data.service_image_details != ''){
                            vm2.service.service_image = response.data.service_image_details[0].url
                        }                        
                        <?php do_action('bookingpress_edit_service_more_vue_data'); ?>

                        /* get categories data */
                        var serviceCatData = [];
                        categoriesitems = this.category_items;
                        categoriesitems.map(function(value, key) {
                            serviceCatData.push({value: value.category_id, label: value.category_name});
                        })
                        vm2.serviceCatOptions = serviceCatData;                        

                }.bind(this) )
                .catch(function(error){
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
            editServiceData(edit_id) {
                const vm2 = this
                vm2.openEditService(edit_id)
            },
            deleteService(delete_id) {
                const vm = new Vue()
                const vm2 = this
                var service_delete_data = { action: 'bookingpress_delete_service', delete_id: delete_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_delete_data ) )
                .then(function(response){
                    vm2.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    vm2.loadServices()
                    vm2.clearBulkAction()                    
                    vm2.is_multiple_checked = false;
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
            delete_bulk_services() {
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
                            action:'bookingpress_bulk_service',
                            cat_delete_ids: this.multipleSelection,
                            bulk_action: 'delete',
                            _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>',
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
                            vm2.loadServices();                            
                            vm2.is_multiple_checked = false;
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
                    {    if(this.multipleSelection.length == 0){                        
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Please select one or more records.', 'bookingpress-appointment-booking'); ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });                        
                        }else{
            <?php do_action('bookingpress_service_dynamic_bulk_action'); ?>
                        }
                    }
                }
            },
            bookingpress_duplicate_service(service_id){
                const vm = new Vue()
                const vm2 = this
                var bookingpress_dup_service_data = [];
                bookingpress_dup_service_data.action = "bookingpress_duplicate_service"
                bookingpress_dup_service_data.service_id = service_id,
                bookingpress_dup_service_data._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'

                axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(bookingpress_dup_service_data))
                .then(function(response){
                    vm2.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: response.data.variant,
                        customClass: response.data.variant+'_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    vm2.loadServices();
                    vm2.multipleSelection = [];
                    vm2.totalItems = vm2.items.length
                    if(response.data.duplicate_serv_id != '' || response.data.duplicate_serv_id != undefined)
                    {
                        vm2.openEditService(response.data.duplicate_serv_id)
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
            resetForm() {
                this.service.service_update_id = 0
                this.service.service_name = ''
                this.service.service_price = ''
                this.service.service_category = null
                this.service.service_duration_val = '<?php echo esc_html($bookingpress_default_time_duration); ?>'
                this.service.service_duration_unit = '<?php echo esc_html($bookingpress_default_time_unit); ?>'
                this.service.service_description = ''                
                this.service.service_images_list = [],
                this.service.service_image = '',
                this.service.service_image_name = '',
                this.activeTabName = 'details'
            <?php
            do_action('bookingpress_after_reset_add_service_form');
            ?>
            },
            closeServiceModal() {
                const vm2 = this
                vm2.$refs['service'].resetFields()
                vm2.resetForm()
                vm2.open_service_modal = false
                <?php
                do_action('bookingpress_after_close_add_service_form');
                ?>                
            },
            bookingpress_upload_service_func(response, file, fileList){
                const vm2 = this
                if(response != ''){

                    if( "undefined" != typeof response.error && 1 == response.error ){
                        vm2.bookingpress_image_upload_err( response.msg, file, fileList );
                        vm2.service.service_image = ''
                        vm2.service.service_image_name = ''
                        vm2.$refs.avatarRef.clearFiles()
                    } else {   
                        vm2.service.service_image = response.upload_url
                        vm2.service.service_image_name = response.upload_file_name
                    }
                }
            },
            bookingpress_image_upload_limit(files, fileList){
                const vm2 = this
                if(vm2.service.service_image != ''){
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
            bookingpress_remove_service_img(){
                const vm2 = this
                var upload_url = vm2.service.service_image
                var upload_filename = vm2.service.service_image_name

                var postData = { action:'bookingpress_remove_service_file', upload_file_url: upload_url,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm2.service.service_image = ''
                    vm2.service.service_image_name = ''
                    vm2.$refs.avatarRef.clearFiles()
                }.bind(vm2) )
                .catch( function (error) {
                    console.log(error);
                });
            },
            checkUploadedFile(file){
                const vm2 = this
                if(file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp'){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please upload jpg/png/webp file only', 'bookingpress-appointment-booking'); ?>',
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
            updateServicePos: function(currentElement){
                var new_index = currentElement.newIndex;
                var old_index = currentElement.oldIndex;
                var service_id = currentElement.item.dataset.service_id;
                const vm = new Vue()
                const vm2 = this
                var postData = { action: 'bookingpress_position_services', old_position: old_index, new_position: new_index, currentPage : this.currentPage, perPage: this.perPage,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then(function(response){
                    
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
            isNumberValidate(evt) {
                const regex = /^(?!.*(,,|,\.|\.,|\.\.))[\d.,]+$/gm;
                let m;
                if((m = regex.exec(evt)) == null ) {
                    this.service.service_price = '';
                }
                var price_number_of_decimals = this.price_number_of_decimals;                
                if((evt != null && evt.indexOf(".")>-1 && (evt.split('.')[1].length > price_number_of_decimals))){
                    this.service.service_price = evt.slice(0, -1);
                }                
            },
            isValidateZeroDecimal(evt){
                const vm = this                
                if (/[^0-9]+/.test(evt)){
                    vm.service.service_price = evt.slice(0, -1);
                }
            },
            <?php
            do_action('bookingpress_add_service_dynamic_vue_methods');
        }
        
        /**
         * Service module search categories
         *
         * @return void
         */
        function bookingpress_search_categories()
        {
            global $wpdb, $tbl_bookingpress_categories;

            $bpa_check_authorization = $this->bpa_check_authentication( 'search_categories', true, 'bpa_wp_nonce' );
            
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

            $bookingpress_search_category_details   = array();
            $bookingpress_search_category_details[] = array(
                'bookingpress_category_name' => esc_html__('All', 'bookingpress-appointment-booking'),
                'bookingpress_category_id'   => 'all',
            );
            $bookingpress_search_categories         = $wpdb->get_results('SELECT bookingpress_category_id, bookingpress_category_name FROM ' . $tbl_bookingpress_categories, ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
            foreach ( $bookingpress_search_categories as $bookingpress_category_key => $bookingpress_category_val ) {
                $bookingpress_search_category_details[] = array(
                'bookingpress_category_name' => stripslashes_deep($bookingpress_category_val['bookingpress_category_name']),
                'bookingpress_category_id'   => $bookingpress_category_val['bookingpress_category_id'],
                );
            }
            echo wp_json_encode($bookingpress_search_category_details);
            exit();
        }
        
        /**
         * Ajax request to get all services
         *
         * @return void
         */
        function bookingpress_get_services()
        {
            global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_categories, $BookingPress, $tbl_bookingpress_servicesmeta,$tbl_bookingpress_appointment_bookings;

            $bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_services', true, 'bpa_wp_nonce' );
            
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

            $perpage     = isset($_POST['perpage']) ? intval($_POST['perpage']) : 10; // phpcs:ignore WordPress.Security.NonceVerification
            $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1; // phpcs:ignore WordPress.Security.NonceVerification
            $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
            $bookingpress_search_data  = ! empty($_REQUEST['search_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['search_data'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
            $bookingpress_search_query = 'WHERE 1 = 1';
            if (! empty($bookingpress_search_data) ) {
                if (! empty($bookingpress_search_data['selected_category_id']) && $bookingpress_search_data['selected_category_id'] != 'all' ) {
                    $bookingpress_search_query .= $wpdb->prepare( " AND bookingpress_category_id = %d", $bookingpress_search_data['selected_category_id'] );
                }

                if (! empty($bookingpress_search_data['service_name']) ) {
                    $bookingpress_search_name   = $bookingpress_search_data['service_name'];
                    $bookingpress_search_query .= ! empty($bookingpress_search_query) ? ' AND ' : ' WHERE ';
                    $bookingpress_search_query .= $wpdb->prepare( "bookingpress_service_Name LIKE %s", '%'.$bookingpress_search_name.'%' );
                }
            }

            $get_total_services = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_services} {$bookingpress_search_query}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
            $total_services     = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_services} {$bookingpress_search_query} order by bookingpress_service_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
            $services           = array();
            if (! empty($total_services) ) {
                $counter      = 1;
                $current_date = date('Y-m-d', current_time('timestamp'));
                foreach ( $total_services as $get_service ) {
                    $bookingpress_service_id     = intval($get_service['bookingpress_service_id']);
                    $service                     = array();
                    $service['id']               = $counter;
                    $service['service_id']       = $bookingpress_service_id;
                    $service['service_name']     = esc_html(stripslashes_deep($get_service['bookingpress_service_name']));
                    $category_id                 = $get_service['bookingpress_category_id'];
                    $category                    = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_id = %d', $category_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
                    $service['category_id']      = $category_id;
                    $service['service_category'] = !empty($category_id) ? stripslashes_deep($category['bookingpress_category_name']) : esc_html__('Uncategorized', 'bookingpress-appointment-booking');
                    $service_duration            = esc_html($get_service['bookingpress_service_duration_val']);
                    $service_duration_unit       = esc_html($get_service['bookingpress_service_duration_unit']);

                    if ( $service_duration_unit == 'm' ) {

                        if($service_duration == 1){
                            $service_duration .= ' ' . esc_html__('Min', 'bookingpress-appointment-booking');
                        }
                        else{
                            $service_duration .= ' ' . esc_html__('Mins', 'bookingpress-appointment-booking');
                        }
                    } else {
                        if( $service_duration == 1 ) {
                            $service_duration .= ' ' . esc_html__('Hour', 'bookingpress-appointment-booking');
                        }
                        else {
                            $service_duration .= ' ' . esc_html__('Hours', 'bookingpress-appointment-booking');
                        }
                    }
                    $service['service_duration'] = $service_duration;
                    $service['service_price']    = $BookingPress->bookingpress_price_formatter_with_currency_symbol($get_service['bookingpress_service_price']);

                    // Get service image
                    $service_img_details            = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_servicemeta_value FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = 'service_image_details'", $bookingpress_service_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_servicesmeta is table name defined globally. False Positive alarm
                    $service_img_details            = ! empty($service_img_details['bookingpress_servicemeta_value']) ? maybe_unserialize($service_img_details['bookingpress_servicemeta_value']) : array();
                    $service_img_url                = ! empty($service_img_details[0]['url']) ? $service_img_details[0]['url'] : '';
                    $service['service_img_details'] = $service_img_url;

                    $bookingperss_appointments_data = '';
                    $bookingperss_appointments_data = $wpdb->get_results( $wpdb->prepare( 'SELECT bookingpress_appointment_booking_id  FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE bookingpress_service_id = %d AND bookingpress_appointment_date >= %s AND (bookingpress_appointment_status != "3" AND bookingpress_appointment_status != "4")', $bookingpress_service_id, $current_date ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    $service['service_bulk_action'] = false;
                    if (! empty($bookingperss_appointments_data) ) {
                        $service['service_bulk_action'] = true;
                    }
                    $service['selected'] = false;           
                    $services[] = $service;
                    $counter++;
                }
            }
            $data['items'] = $services;
            $data['total'] = count($get_total_services);

            $data = apply_filters('bookingpress_modify_servies_listing_data', $data, $_POST, $total_services); // phpcs:ignore WordPress.Security.NonceVerification

            wp_send_json($data);
            exit;
        }

        
        /**
         * Add/Update service module details
         *
         * @return void
         */
        function bookingpress_add_service($is_api = false)
        {
            global $wpdb, $tbl_bookingpress_categories, $tbl_bookingpress_services,$bookingpress_global_options;

            if($is_api == true){
                $is_error = $this->bookingpress_validate_token();
                if( is_wp_error( $is_error ) ){
                    return $is_error;
                }
            } else {
                $bpa_check_authorization = $this->bpa_check_authentication( 'add_services', true, 'bpa_wp_nonce' );
            
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
            }
            

            $service_id   = isset($_POST['service_update_id']) ? intval($_POST['service_update_id']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
            $service_name = ! empty($_POST['service_name']) ? trim(sanitize_text_field($_POST['service_name'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification

            do_action('bookingpress_add_service_validation');

            if (strlen($service_name) > 255 ) {
                $response            = array();
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Service name is too long...', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();
            }
            $service_duration_val  = $bpa_service_duration = isset($_POST['service_duration_val']) ? intval($_POST['service_duration_val']) : 0; // phpcs:ignore WordPress.Security.NonceVerification
            $service_duration_unit = isset($_POST['service_duration_unit']) ? sanitize_text_field($_POST['service_duration_unit']) : 'm'; // phpcs:ignore WordPress.Security.NonceVerification

            if ($service_duration_unit == 'h' ) {
                $bpa_service_duration = $bpa_service_duration * 60;
            }
            if ($bpa_service_duration > 1440 && ( $service_duration_unit == 'm' || $service_duration_unit == 'h')) {
                $response            = array();
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Service time duration cannot be greater than 24 hours', 'bookingpress-appointment-booking') . '.';
                if($is_api){
                    return $response;
                }else{
                    wp_send_json($response);
                    die();
                }
                
            }
            if ($service_duration_unit == 'd' && $bpa_service_duration > 30 ) { 
                $response            = array();
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Service time duration cannot be greater than 30 days', 'bookingpress-appointment-booking') . '.';
                if($is_api){
                    return $response;
                }else{
                    wp_send_json($response);
                    die();
                }
            }
            $service_price       = isset($_POST['service_price']) ? floatval($_POST['service_price']) : 0; // phpcs:ignore WordPress.Security.NonceVerification
            $service_category    = isset($_POST['service_category']) ? intval($_POST['service_category']) : 0; // phpcs:ignore WordPress.Security.NonceVerification
            
            $bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_allow_tag = json_decode($bookingpress_global_options_data['allowed_basic_html_tag'], true);
            $service_description = ! empty($_POST['service_description']) ? wp_kses($_POST['service_description'], $bookingpress_allow_tag) : ''; //phpcs:ignore WordPress.Security.NonceVerification
            $service_description = htmlspecialchars_decode(stripslashes_deep($service_description));
            
            $response            = array();
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

            if (! empty($service_category) ) {
                $is_service_exist = $wpdb->get_var($wpdb->prepare("SELECT (bookingpress_category_id) as total from {$tbl_bookingpress_categories} WHERE bookingpress_category_id = %d", $service_category));// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
                if ($is_service_exist == 0 ) {
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                    $response['msg']     = esc_html__('Category not found', 'bookingpress-appointment-booking');

                    if($is_api){
                        return $response;
                    }else{
                        wp_send_json($response);
                        die();
                    }
                }
            }

            if (! empty($service_name) ) {
                $args = array(
                'bookingpress_category_id'           => $service_category,
                'bookingpress_service_name'          => $service_name,
                'bookingpress_service_price'         => $service_price,
                'bookingpress_service_duration_val'  => $service_duration_val,
                'bookingpress_service_duration_unit' => $service_duration_unit,
                'bookingpress_service_description'   => $service_description,
                );
                if (! empty($service_id) ) {
                    if (empty($_POST['service_image']) ) { // phpcs:ignore WordPress.Security.NonceVerification
                        $this->bookingpress_add_service_meta($service_id, 'service_image_details', maybe_serialize( array() ) );
                    }

                    $update_args = array();
                    $update_args["bookingpress_category_id"] = $service_category;
                    $update_args["bookingpress_service_description"] = $service_description;

                    if(!empty($service_name)){
                        $update_args["bookingpress_service_name"] = $service_name;
                    }
                    if( '' !== $service_price ){
                        $update_args["bookingpress_service_price"] = $service_price;
                    }
                    if(!empty($service_duration_val)){
                        $update_args["bookingpress_service_duration_val"] = $service_duration_val;
                    }
                    if(!empty($service_duration_unit)){
                        $update_args["bookingpress_service_duration_unit"] = $service_duration_unit;
                    }
                    
                    

                    $wpdb->update($tbl_bookingpress_services, $update_args, array( 'bookingpress_service_id' => $service_id ));
                    $response['service_id'] = $service_id;
                    $response['variant']    = 'success';
                    $response['title']      = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg']        = esc_html__('Service has been updated successfully.', 'bookingpress-appointment-booking');
                } else {
                    $service_position = 0;
                    $service          = $wpdb->get_row('SELECT * FROM ' . $tbl_bookingpress_services . ' ORDER BY bookingpress_service_position DESC LIMIT 1', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                    if (! empty($service) ) {
                        $service_position = $service['bookingpress_service_position'] + 1;
                    }
                    $date                                     = current_time('mysql');
                    $args['bookingpress_service_position']    = $service_position;
                    $args['bookingpress_servicedate_created'] = $date;
                    $wpdb->insert($tbl_bookingpress_services, $args);
                    $service_id             = $wpdb->insert_id;
                    $response['service_id'] = $service_id;
                    $response['variant']    = 'success';
                    $response['title']      = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg']        = esc_html__('Service has been added successfully.', 'bookingpress-appointment-booking');
                }

                $response = apply_filters('bookingpress_after_add_update_service', $response, $service_id, $_POST); // phpcs:ignore WordPress.Security.NonceVerification

                if (! empty($service_id) ) {
                    $service_image_url      = ! empty($_POST['service_image']) ? esc_url_raw($_POST['service_image']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
                    $service_image_new_name = ! empty($_POST['service_image_name']) ? sanitize_file_name($_POST['service_image_name']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
                    if (! empty($service_image_url) && ! empty($service_image_new_name) ) {
                        global $BookingPress;
                        $upload_dir                 = BOOKINGPRESS_UPLOAD_DIR . '/';
                        $bookingpress_new_file_name = current_time('timestamp') . '_' . $service_image_new_name;
                        $upload_path                = $upload_dir . $bookingpress_new_file_name;
                        
                        $bookingpress_upload_res = new bookingpress_fileupload_class( $service_image_url, true );
                        
                        $bookingpress_upload_res->check_cap          = true;
                        $bookingpress_upload_res->check_nonce        = true;
                        $bookingpress_upload_res->nonce_data         = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
                        $bookingpress_upload_res->nonce_action       = 'bpa_wp_nonce';
                        $bookingpress_upload_res->check_only_image   = true;
                        $bookingpress_upload_res->check_specific_ext = false;
                        $bookingpress_upload_res->allowed_ext        = array();

                        $upload_response = $bookingpress_upload_res->bookingpress_process_upload( $upload_path );
                        
                        if( true == $upload_response ){
                            $service_image_new_url   = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_new_file_name;
                            $service_image_details   = array();
                            $service_image_details[] = array(
                                'name' => $bookingpress_new_file_name,
                                'url'  => $service_image_new_url,
                            );
                            $this->bookingpress_add_service_meta($service_id, 'service_image_details', maybe_serialize($service_image_details));
                            
                            $bookingpress_file_name_arr = explode('/', $service_image_url);
                            $bookingpress_file_name     = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
                            if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
                                wp_delete_file(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
                            }
                        }
                    }
                }
            } elseif (empty($service_name) ) {
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__('Please add valid data for add service', 'bookingpress-appointment-booking') . '.';
            }
            if($is_api == true){
                return $response;
            }else{
                wp_send_json($response);
            }
            
        }
        
        /**
         * Get service meta value
         *
         * @param  mixed $service_id
         * @param  mixed $meta_key
         * @return void
         */
        function bookingpress_get_service_meta( $service_id, $meta_key )
        {
            global $wpdb, $tbl_bookingpress_servicesmeta;

            $bookingpress_servicemeta_value = '';

            $servicemeta_setting_key =  $service_id."|^|".$meta_key;
            $servicemeta_setting_value = wp_cache_get($servicemeta_setting_key);

            if($servicemeta_setting_value === false){
                $bookingpress_servicemeta_details = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_servicemeta_value FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = %s", $service_id, $meta_key ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_servicesmeta is table name defined globally. False Positive alarm
                wp_cache_set($servicemeta_setting_key,$bookingpress_servicemeta_details);
            } else {
                $bookingpress_servicemeta_details  = $servicemeta_setting_value;
            }


            if (! empty($bookingpress_servicemeta_details) ) {
                $bookingpress_servicemeta_value = $bookingpress_servicemeta_details['bookingpress_servicemeta_value'];
            }

            return $bookingpress_servicemeta_value;
        }

                
        /**
         * Insert / Update service meta value
         *
         * @param  mixed $service_id
         * @param  mixed $meta_key
         * @param  mixed $meta_value
         * @return void
         */
        function bookingpress_add_service_meta( $service_id, $meta_key, $meta_value )
        {
            global $wpdb, $tbl_bookingpress_servicesmeta;
            $service_meta = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = %s", $service_id, $meta_key ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_servicesmeta is table name defined globally. False Positive alarm
            if (! empty($service_meta) ) {
                $servicemeta_id = $service_meta['bookingpress_servicemeta_id'];
                $args           = array(
                'bookingpress_servicemeta_value' => $meta_value,
                );
                $wpdb->update($tbl_bookingpress_servicesmeta, $args, array( 'bookingpress_servicemeta_id' => $servicemeta_id ));
            } else {
                $date = current_time('mysql');
                $args = array(
                'bookingpress_service_id'              => $service_id,
                'bookingpress_servicemeta_name'        => $meta_key,
                'bookingpress_servicemeta_value'       => $meta_value,
                'bookingpress_servicemetadate_created' => $date,
                );
                $wpdb->insert($tbl_bookingpress_servicesmeta, $args);
                $servicemeta_id = $wpdb->insert_id;
            }
            $servicemeta_setting_key =  $service_id."|^|".$meta_key;
            wp_cache_delete( $servicemeta_setting_key );
            return $servicemeta_id;
        }

        
        /**
         * Get edit service data
         *
         * @return void
         */
        function bookingpress_edit_service()
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta,$bookingpress_service_categories;
            $service_id  =  $_REQUEST['service_id'] = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : ''; // phpcs:ignore WordPress.Security.NonceVerification            
            $response    = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'edit_services', true, 'bpa_wp_nonce' );
            
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
            if (! empty($service_id) ) {
                $bookingpress_decimal_points = $BookingPress->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');

                $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                if (! empty($service) ) {
                    $response['service_id']            = $service['bookingpress_service_id'];
                    $response['category_id']           = !empty($service['bookingpress_category_id']) ? $service['bookingpress_category_id'] : '';
                    $response['service_name']          = stripslashes_deep($service['bookingpress_service_name']);
                    $response['service_price']         = round($service['bookingpress_service_price'],(int)$bookingpress_decimal_points);
                    $response['service_duration']      = esc_html($service['bookingpress_service_duration_val']);
                    $response['service_duration_unit'] = esc_html($service['bookingpress_service_duration_unit']);
                    $response['service_description']   = stripslashes_deep($service['bookingpress_service_description']);
                    $servicemetas                      = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_servicesmeta is a table name
                    $response['extra_data']            = '';

                    if (! empty($servicemetas) ) {
                        foreach ( $servicemetas as $key => $servicemeta ) {
                            $response[ $servicemeta['bookingpress_servicemeta_name'] ] = maybe_unserialize($servicemeta['bookingpress_servicemeta_value']);
                        }
                    }
                    
                    $response = apply_filters('bookingpress_modify_edit_service_data',$response,$service_id);
                    $response['variant'] = 'success';
                    $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg']     = esc_html__('Service Data.', 'bookingpress-appointment-booking');
                }
            }
            echo wp_json_encode($response);
            exit;
        }

        
        /**
         * Function for delete specific service
         *
         * @param  mixed $service_id
         * @return void
         */
        function bookingpress_delete_service( $service_id = '', $is_api = false )
        {
            global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta,$tbl_bookingpress_appointment_bookings;
            $response              = array();
            $return                = false;
            if($is_api == true){
                $is_error = $this->bookingpress_validate_token();
                if( is_wp_error( $is_error ) ){
                    return $is_error;
                }
            } else {
                $bpa_check_authorization = $this->bpa_check_authentication( 'delete_services', true, 'bpa_wp_nonce' );
            
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

            $service_id          = isset($_POST['delete_id']) ? intval($_POST['delete_id']) : $service_id; // phpcs:ignore WordPress.Security.NonceVerification
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

            if (! empty($service_id) ) {
                $current_date                   = date('Y-m-d', current_time('timestamp'));
                $bookingperss_appointments_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id  FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date >= %s AND (bookingpress_appointment_status != '3' AND bookingpress_appointment_status != '4')", $service_id, $current_date ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

                if (count($bookingperss_appointments_data) == 0 ) {
                    $total_services = $wpdb->get_results('SELECT * FROM ' . $tbl_bookingpress_services . ' order by bookingpress_service_position DESC', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                    $new_position   = count($total_services) - 1;
                    $service        = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_id = %d', $service_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                    if ($service['bookingpress_service_position'] != $new_position && $is_api == false) {
                        $this->bookingpress_position_services($service['bookingpress_service_position'], $new_position);
                    }
                    $is_deleted = $wpdb->delete($tbl_bookingpress_services, array( 'bookingpress_service_id' => $service_id ), array( '%d' ));
                    if($is_deleted){
                        $wpdb->delete($tbl_bookingpress_servicesmeta, array( 'bookingpress_service_id' => $service_id ), array( '%d' ));
                        do_action( 'bookingpress_after_delete_service', $service_id );																	
                        $response['variant'] = 'success';
                        $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                        $response['msg']     = esc_html__('Services has been deleted successfully.', 'bookingpress-appointment-booking');
                        $return              = true;
                    }else{
                        $response['variant'] = 'error';
                        $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                        $response['msg']     = esc_html__('Either service already deletd or service id not found.', 'bookingpress-appointment-booking');
                        $return              = true;
                    }
                    
                    if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_delete_service' ) { // phpcs:ignore WordPress.Security.NonceVerification
                        wp_send_json($response);
                    }
                    if($is_api){
                        return $response;
                    }
                    return $return;
                } else {
                    $bookingpress_error_msg = esc_html__(' I am sorry', 'bookingpress-appointment-booking') . '! ' . esc_html__('This service can not be deleted because it has one or more appointments associated with it', 'bookingpress-appointment-booking') . '.';

                    $response['variant'] = 'warning';
                    $response['title']   = esc_html__('warning', 'bookingpress-appointment-booking');
                    $response['msg']     = $bookingpress_error_msg;
                    $return              = false;
                    if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_delete_service' ) { // phpcs:ignore WordPress.Security.NonceVerification
                        wp_send_json($response);
                    }
                    if($is_api){
                        return $response;
                    }
                    return $return;
                }
            }
        }

        
        /**
         * Service module bulk actions
         *
         * @return void
         */
        function bookingpress_bulk_service()
        {
            global $BookingPress;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            
            $bpa_check_authorization = $this->bpa_check_authentication( 'delete_services', true, 'bpa_wp_nonce' );
            
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
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['cat_delete_ids'] contains mixed array and it's been sanitized properly using 'appointment_sanatize_field' function
                $delete_ids = ! empty($_POST['cat_delete_ids']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['cat_delete_ids']) : array(); // phpcs:ignore
                if (! empty($delete_ids) ) {
                    foreach ( $delete_ids as $delete_key => $delete_val ) {
                        if (is_array($delete_val) ) {
                            $delete_val = $delete_val['service_id'];
                        }
                        $return = $this->bookingpress_delete_service($delete_val);
                        if ($return ) {
                            $response['variant'] = 'success';
                            $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                            $response['msg']     = esc_html__('Services has been deleted successfully.', 'bookingpress-appointment-booking');
                        } else {
                            $response['variant'] = 'warning';
                            $response['title']   = esc_html__('Warning', 'bookingpress-appointment-booking');
                            $response['msg']     = esc_html__('Could not delete service. This service has a appointment in the future.', 'bookingpress-appointment-booking');
                            wp_send_json($response);
                            exit;
                        }
                    }
                }
            }
            wp_send_json($response);
        }

        
        /**
         * Service module change service positions
         *
         * @param  mixed $old_position
         * @param  mixed $new_position
         * @return void
         */
        function bookingpress_position_services( $old_position = '', $new_position = '' )
        {
            global $wpdb, $BookingPress, $tbl_bookingpress_services;
            $response = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'manage_service_position', true, 'bpa_wp_nonce' );
            
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
            if (isset($old_position) && isset($new_position) ) {
                if ($new_position > $old_position ) {
                    //$condition = 'BETWEEN ' . $old_position . ' AND ' . $new_position;
                    $services  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_position BETWEEN %d AND %d order by bookingpress_service_position ASC', $old_position, $new_position ), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_services is a table name
                    foreach ( $services as $service ) {
                        $position = $service['bookingpress_service_position'] - 1;
                        $position = ( $service['bookingpress_service_position'] == $old_position ) ? $new_position : $position;
                        $args     = array(
                         'bookingpress_service_position' => $position,
                        );

                        $wpdb->update($tbl_bookingpress_services, $args, array( 'bookingpress_service_id' => $service['bookingpress_service_id'] ));
                    }
                } else {
                    $services = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_position BETWEEN %d AND %d order by bookingpress_service_position ASC', $new_position, $old_position ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                    foreach ( $services as $service ) {
                        $position = $service['bookingpress_service_position'] + 1;
                        $position = ( $service['bookingpress_service_position'] == $old_position ) ? $new_position : $position;
                        $args     = array(
                        'bookingpress_service_position' => $position,
                        );
                        
                        $wpdb->update($tbl_bookingpress_services, $args, array( 'bookingpress_service_id' => $service['bookingpress_service_id'] ));
                    }
                }
                $response['variant'] = 'success';
                $response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Service position has been changed successfully.', 'bookingpress-appointment-booking');
            }
            if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_position_services' ) { // phpcs:ignore WordPress.Security.NonceVerification
                wp_send_json($response);
            }
            return;
        }

        
        /**
         * Get all services details
         *
         * @return void
         */
        function bookingpress_get_all_services()
        {
            global $wpdb, $tbl_bookingpress_services;

            $bookingpress_return_data = array();

            $bookingpress_all_service_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_services} ORDER BY bookingpress_service_id", ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
            if (! empty($bookingpress_all_service_data) ) {
                $bookingpress_return_data = $bookingpress_all_service_data;
            }

            return wp_json_encode($bookingpress_return_data);
        }

        
        /**
         * Duplicate service with all details
         *
         * @return void
         */
        function bookingpress_duplicate_service()
        {
            global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $BookingPress;
            $response              = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'add_services', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
            
            $response['variant']               = 'error';
            $response['title']                 = esc_html__('Error', 'bookingpress-appointment-booking');
            $response['duplicate_serv_id']     = '';
            $response['msg']                   = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');
            $bookingpress_duplicate_service_id = ! empty($_REQUEST['service_id']) ? intval($_REQUEST['service_id']) : 0;

            if (! empty($bookingpress_duplicate_service_id) ) {
                // Fetch duplicate data records from service and service meta
                $bookingpress_duplicate_service = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $bookingpress_duplicate_service_id ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                if (! empty($bookingpress_duplicate_service) ) {
                    // Find max position of service
                    $bookingpress_find_last_pos = $wpdb->get_row("SELECT MAX(bookingpress_service_position) as bookingpress_last_pos FROM {$tbl_bookingpress_services}", ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                    $bookingpress_new_pos       = $bookingpress_find_last_pos['bookingpress_last_pos'] + 1;

                    $bookingpress_duplicate_service_data = $bookingpress_duplicate_service;
                    unset($bookingpress_duplicate_service_data['bookingpress_service_id']);
                    $bookingpress_duplicate_service_data['bookingpress_service_name']        = esc_html__('Copy', 'bookingpress-appointment-booking') . ' ' . $bookingpress_duplicate_service_data['bookingpress_service_name'];
                    $bookingpress_duplicate_service_data['bookingpress_service_position']    = $bookingpress_new_pos;
                    $bookingpress_duplicate_service_data['bookingpress_servicedate_created'] = current_time('mysql');

                    $wpdb->insert($tbl_bookingpress_services, $bookingpress_duplicate_service_data);
                    $bookingpress_inserted_service_id = $wpdb->insert_id;

                    $bookingpress_duplicate_service_meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d", $bookingpress_duplicate_service_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_servicesmeta is table name defined globally. False Positive alarm
                    if (! empty($bookingpress_duplicate_service_meta) ) {
                        foreach ( $bookingpress_duplicate_service_meta as $bookingpress_duplicate_service_meta_key => $bookingpress_duplicate_service_meta_val ) {
                            $bookingpress_service_meta_data = $bookingpress_duplicate_service_meta_val;

                            unset($bookingpress_duplicate_service_meta_val['bookingpress_servicemeta_id']);
                            $bookingpress_duplicate_service_meta_val['bookingpress_service_id']              = $bookingpress_inserted_service_id;
                            $bookingpress_duplicate_service_meta_val['bookingpress_servicemetadate_created'] = current_time('mysql');

                            if ($bookingpress_duplicate_service_meta_val['bookingpress_servicemeta_name'] == 'service_image_details' ) {
                                // If image exists then copy image
                                $bookingpress_service_image_details = maybe_unserialize($bookingpress_duplicate_service_meta_val['bookingpress_servicemeta_value']);

                                $bookingpress_service_image_url  = ! empty($bookingpress_service_image_details[0]['url']) ? $bookingpress_service_image_details[0]['url'] : '';
                                $bookingpress_service_image_name = ! empty($bookingpress_service_image_details[0]['name']) ? $bookingpress_service_image_details[0]['name'] : '';
                                if (! empty($bookingpress_service_image_url) && ! empty($bookingpress_service_image_name) ) {
                                    $bookingpress_service_new_image_name = esc_html__('copy', 'bookingpress-appointment-booking') . '_' . $bookingpress_service_image_name;
                                    $bookingpress_upload_img_path        = BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpress_service_new_image_name;

                                    $bookingpress_upload_res = new bookingpress_fileupload_class( $bookingpress_service_image_url, true );
                                    $bookingpress_upload_res->check_cap             = true;
                                    $bookingpress_upload_res->check_nonce           = true;
                                    $bookingpress_upload_res->nonce_data            = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
                                    $bookingpress_upload_res->nonce_action          = 'bpa_wp_nonce';
                                    $bookingpress_upload_res->check_only_image   = true;
                                    $bookingpress_upload_res->check_specific_ext = false;
                                    $bookingpress_upload_res->allowed_ext        = array();
                                    $upload_response = $bookingpress_upload_res->bookingpress_process_upload( $bookingpress_upload_img_path );

                                    if( true == $upload_response ){
                                        $service_image_new_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_service_new_image_name;
                                        
                                        $bookingpress_service_image_details[0]['name'] = $bookingpress_service_new_image_name;
                                        $bookingpress_service_image_details[0]['url']  = $service_image_new_url;
                                    }
                                }

                                $bookingpress_duplicate_service_meta_val['bookingpress_servicemeta_value'] = maybe_serialize($bookingpress_service_image_details);
                            }

                            $bookingpress_service_meta_data = $bookingpress_duplicate_service_meta_val;

                            $wpdb->insert($tbl_bookingpress_servicesmeta, $bookingpress_service_meta_data);
                        }
                    }

                    do_action('bookingpress_duplicate_more_details', $bookingpress_inserted_service_id, $bookingpress_duplicate_service_id);

                    $response['variant']           = 'success';
                    $response['title']             = esc_html__('Success', 'bookingpress-appointment-booking');
                    $response['msg']               = esc_html__('Service duplicate successfully', 'bookingpress-appointment-booking');
                    $response['duplicate_serv_id'] = $bookingpress_inserted_service_id;
                } else {
                    $response['variant'] = 'error';
                    $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                    $response['msg']     = esc_html__('No service found...', 'bookingpress-appointment-booking');
                }
            }

            echo wp_json_encode($response);
            exit();
        }

    }
}
global $bookingpress_services;
$bookingpress_services = new bookingpress_services();

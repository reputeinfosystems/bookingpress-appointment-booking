<?php
namespace BookingPress\admin;

if( !defined( 'ABSPATH' ) ) {
    exit;
}

class Customer extends Base{
    
    protected static $slug = 'bookingpress_customers';
    public static function init(){
        parent::init();

        add_filter( 'script_module_data_bookingpress-customer-loader', [ __CLASS__, 'bookingpress_add_customer_script_module_data' ] );
        
    }

    public static function bookingpress_add_customer_script_module_data($customer_data ){

        global $BookingPress, $bookingpress_global_options;

        $bpa_nonce = wp_create_nonce('bpa_wp_nonce');
        $customer_data['customer']['_wpnonce'] = $bpa_nonce;
        $customer_data['bookingpress_loading'] = false;
        $customer_data['wordpress_user_id'] = '';

        $bookingpress_options                  = $bookingpress_global_options->bookingpress_global_options();
        $bookingpress_country_list             = $bookingpress_options['country_lists'];
        $bookingpress_pagination               = $bookingpress_options['pagination'];
        $bookingpress_pagination_arr           = json_decode($bookingpress_pagination, true);
        $bookingpress_pagination_selected      = $bookingpress_pagination_arr[0];

        
        $bookingpress_allow_customer_create = $BookingPress->bookingpress_get_settings('allow_wp_user_create', 'customer_setting');
        $bookingpress_allow_customer_create = ! empty($bookingpress_allow_customer_create) ? $bookingpress_allow_customer_create : 'false';
        $bookingpress_allow_customer_create = $bookingpress_allow_customer_create == 'true' ? true : false;

        // pagination data
        $bookingpress_default_perpage_option   = $BookingPress->bookingpress_get_settings('per_page_item', 'general_setting');
        $customer_data['perPage']               = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '10';
        $customer_data['pagination_length_val'] = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '10';
        $customer_data['pagination_length'] = ! empty($bookingpress_pagination) ? $bookingpress_pagination : '10';

        $bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
        $customer_data['customer']['customer_phone_country'] = $bookingpress_phone_country_option;

        $customer_data['phone_countries_details'] = json_decode($bookingpress_country_list);

        $customer_data['bookingpress_tel_input_props'] = array(
                'defaultCountry' => $bookingpress_phone_country_option,
                'inputOptions' => array(
                    'placeholder' => '',
                ),
                'validCharactersOnly' => true,
            );

        $customer_data['vue_tel_mode'] = 'international';
        $customer_data['vue_tel_auto_format'] = true;

        $customer_data['ExportCustomerLite'] = false;
        $customer_data['is_mask_display']            = false;

        $customer_data['is_export_button_loader_lite']    = '0';
        $customer_data['is_export_button_disabled_lite']  = false;
        $customer_data['import_customer_modal']           = false;
        $customer_data['is_import_loader_show']           = '0';

        $customer_data['customer_export_field_list_lite'] = array(
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

        $customer_data['bookingpress_import_fields']         = $bookingpress_import_fields;
        $customer_data['bookingpress_import_fields_org']     = $bookingpress_import_fields;

        $customer_data['bookingpress_import_field_data']     = $bookingpress_import_field_data;
        $customer_data['bookingpress_import_field_data_org'] = $bookingpress_import_field_data;

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

        $customer_data['import_file_fields'] = array();
        $customer_data['import_file_name'] = "";

        $customer_data['bookingpress_customer_import_rules'] = $bookingpress_customer_import_rules;  

        $customer_data['export_checked_field_lite'] = array('first_name', 'last_name', 'email', 'phone', 'note', 'username', 'user_email');

        $customer_data['export_checked_field_lite_org'] = $customer_data['export_checked_field_lite'];
        $customer_data['import_file_list']    = [];
        $customer_data['import_loading']      = '0';
        $customer_data['complete_import']     = '0';
        $customer_data['customers_total_count']         = '0';
        $customer_data['customers_import_count']        = '0';
        $customer_data['customers_not_import_count']    = '0';
        $customer_data['duplicate_count']     = '0';
        $customer_data['is_wordpress_user_create_on_import'] = false;

        $customer_data['rules'] = array(
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

        $customer_data['bulk_options']  = array(
            array(
                'value' => 'bulk_action',
                'label' => esc_html__('Bulk Action', 'bookingpress-appointment-booking'),
            ),
            array(
                'value' => 'delete',
                'label' => esc_html__('Delete', 'bookingpress-appointment-booking'),
            ),
        );

        $customer_data = apply_filters( 'bookingpress_customer_data', $customer_data );

        return $customer_data;
    }

    public static function enqueue_assets( $hook ){
        if ( empty( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'bookingpress_customers' ) {
            return;
        }
        
        wp_register_script_module(
            'vue',
            BOOKINGPRESS_URL . '/src/assets/js/vue.min.js',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
            'bookingpress-ui',
            BOOKINGPRESS_URL . '/src/assets/js/bookingpress-ui.min.js',
            ['vue'],
            BOOKINGPRESS_VERSION
        );

        wp_register_script_module(
            'bookingpress-appointment-model',
            BOOKINGPRESS_URL . '/src/assets/js/appointment-model.js',
            [ 'bookingpress-ui'],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-appointment-model' );

        wp_register_script_module(
            'bookingpress-customer-model',
            BOOKINGPRESS_URL . '/src/assets/js/customer-model.js',
            [ 'bookingpress-ui'],
            BOOKINGPRESS_VERSION
        );
        wp_enqueue_script_module( 'bookingpress-customer-model' );

        wp_register_script_module(
            'bookingpress-customer-loader',
            BOOKINGPRESS_URL . '/src/assets/js/customer-loader.js',
            ['bookingpress-ui'],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_script_module( 'bookingpress-customer-loader' );

        wp_enqueue_style(
            'bookingpress-ui',
            BOOKINGPRESS_URL . '/src/assets/css/bookingpress-ui.min.css',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_style(
            'bookingpress-admin-common',
            BOOKINGPRESS_URL . '/src/assets/css/bookingpress_admin_common.css',
            [],
            BOOKINGPRESS_VERSION
        );

        wp_enqueue_style( 'bookingpress_admin_css' );
        wp_enqueue_style( 'bookingpress_components_css' );
        wp_enqueue_style( 'bookingpress_fonts_css' );

        wp_enqueue_style(
            'bookingpress-common',
            BOOKINGPRESS_URL . '/src/assets/css/common.css',
            [],
            BOOKINGPRESS_VERSION
        );
    }

    public static function render_page(){
        self::render_view( 'Customer', [
            'title' => esc_html__( 'Customer', 'bookingpress-appointment-booking' )
        ] );
    }
}
<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (is_ssl() ) {
    define('BOOKINGPRESS_URL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/' . BOOKINGPRESS_DIR_NAME));
    define('BOOKINGPRESS_HOME_URL', home_url('', 'https'));
} else {
    define('BOOKINGPRESS_URL', WP_PLUGIN_URL . '/' . BOOKINGPRESS_DIR_NAME);
    define('BOOKINGPRESS_HOME_URL', home_url());
}

define('BOOKINGPRESS_MENU_URL', admin_url() . 'admin.php?page=bookingpress');

define('BOOKINGPRESS_CORE_DIR', BOOKINGPRESS_DIR . '/core');
define('BOOKINGPRESS_MAIN_FILE', plugin_basename(__FILE__));
define('BOOKINGPRESS_CLASSES_DIR', BOOKINGPRESS_DIR . '/core/classes');
define('BOOKINGPRESS_CLASSES_URL', BOOKINGPRESS_URL . '/core/classes');

define('BOOKINGPRESS_WIDGET_DIR', BOOKINGPRESS_DIR . '/core/widgets');
define('BOOKINGPRESS_WIDGET_URL', BOOKINGPRESS_URL . '/core/widgets');

define('BOOKINGPRESS_IMAGES_DIR', BOOKINGPRESS_DIR . '/images');
define('BOOKINGPRESS_IMAGES_URL', BOOKINGPRESS_URL . '/images');

define('BOOKINGPRESS_LIBRARY_DIR', BOOKINGPRESS_DIR . '/lib');
define('BOOKINGPRESS_LIBRARY_URL', BOOKINGPRESS_URL . '/lib');

define('BOOKINGPRESS_INC_DIR', BOOKINGPRESS_DIR . '/inc');

define('BOOKINGPRESS_VIEWS_DIR', BOOKINGPRESS_DIR . '/core/views');
define('BOOKINGPRESS_VIEWS_URL', BOOKINGPRESS_URL . '/core/views');


if (! defined('FS_METHOD') ) {
    @define('FS_METHOD', 'direct');
}


$bookingpress_wpupload_dir = wp_upload_dir();
$bookingpress_upload_dir   = $bookingpress_wpupload_dir['basedir'] . '/bookingpress';
$bookingpress_upload_url   = $bookingpress_wpupload_dir['baseurl'] . '/bookingpress';

if (is_ssl() ) {
    $bookingpress_upload_url = str_replace('http://', 'https://', $bookingpress_upload_url);
}

global $bookingpress_settings_table_exists, $bookingpress_customize_settings_table_exists;

$bookingpress_tmp_images_dir = $bookingpress_upload_dir . '/tmp_images';
$bookingpress_tmp_images_url = $bookingpress_upload_url . '/tmp_images';
if (! is_dir($bookingpress_upload_dir) ) {
    wp_mkdir_p($bookingpress_upload_dir);
}
if (! is_dir($bookingpress_tmp_images_dir) ) {
    wp_mkdir_p($bookingpress_tmp_images_dir);
}
define('BOOKINGPRESS_UPLOAD_DIR', $bookingpress_upload_dir);
define('BOOKINGPRESS_UPLOAD_URL', $bookingpress_upload_url);
define('BOOKINGPRESS_TMP_IMAGES_DIR', $bookingpress_tmp_images_dir);
define('BOOKINGPRESS_TMP_IMAGES_URL', $bookingpress_tmp_images_url);

$bookingpress_upload_css_dir = $bookingpress_wpupload_dir['basedir'] . '/bookingpress/css';
$bookingpress_upload_css_url = $bookingpress_wpupload_dir['baseurl'] . '/bookingpress/css';
if (! is_dir($bookingpress_upload_css_dir) ) {
    wp_mkdir_p($bookingpress_upload_css_dir);
}
define('BOOKINGPRESS_UPLOAD_CSS_DIR', $bookingpress_upload_css_dir);
define('BOOKINGPRESS_UPLOAD_CSS_URL', $bookingpress_upload_css_url);

$upload_dir = wp_upload_dir(); // Get uploads directory info
$exported_folder_path = $upload_dir['basedir'] . '/bookingpress_export_records';
if (!file_exists($exported_folder_path)) {
    wp_mkdir_p($exported_folder_path);

}

if( !file_exists( $exported_folder_path.'/index.php') ){
    $file_content = "<?php\n //silence is golden";
    $file_name = 'index.php';
    file_put_contents( $exported_folder_path.'/'.$file_name, $file_content );
}

$import_folder_path = $upload_dir['basedir'] . '/bookingpress_import_records';
if (!file_exists($import_folder_path)) {
    wp_mkdir_p($import_folder_path);

}

if( !file_exists( $import_folder_path .'/index.php') ){
    $file_content = "<?php\n //silence is golden";
    $file_name = 'index.php';
    
    file_put_contents( $import_folder_path.'/'.$file_name, $file_content );
}

global $bookingpress_version;
$bookingpress_version = '1.1.51';
define('BOOKINGPRESS_VERSION', $bookingpress_version);

global $bookingpress_ajaxurl;
$bookingpress_ajaxurl = admin_url('admin-ajax.php');

global $wp_version;
if( !empty( $wp_version ) && version_compare( $wp_version, '5.3.0', '<' ) ) {

    if( !function_exists( 'deactivate_plugins') ){
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    deactivate_plugins( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php', true, true );
    
    $plugin_deactivation_link = network_admin_url('plugins.php?deactivate=true');
    
    /* translators: 1. Plugin Deactivation Link */
    $plugin_deactivation_note = sprintf( esc_html__( 'Current WordPress version does not meet minimum requirements for BookingPress. Please update the WordPress version to at least 5.3.0 and then try again. Click %s to continue', 'bookingpress-appointment-booking' ), '<a href="'. $plugin_deactivation_link.'">'.esc_html__('here', 'bookingpress-appointment-booking').'</a>' ); 

    wp_die( $plugin_deactivation_note ); // phpcs:ignore

    exit;
}

/**
 * Plugin Main Class
 */

if( file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress-core.php') ){
    require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress-core.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_fileupload_class.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_fileupload_class.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_global_options.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_global_options.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_services.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_services.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_service_categories.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_service_categories.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_settings.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_settings.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_notifications.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_notifications.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_customers.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_customers.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_payment.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_payment.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_appointment.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_appointment.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_calendar.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_calendar.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_dashboard.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_dashboard.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_payment_gateways.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_payment_gateways.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/payment_gateways/class.bookingpress_paypal.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/payment_gateways/class.bookingpress_paypal.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_email_notifications.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_email_notifications.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_customize.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_customize.php';
}

if( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_addons.php') ){
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_addons.php';
}

if( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_import_export.php') ){
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_import_export.php';
}

if( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_growth_tools.php') ){
    include_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_growth_tools.php';
}

// Frontend files
if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/frontend/class.bookingpress_appointment_bookings.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/frontend/class.bookingpress_appointment_bookings.php';
}

if (file_exists(BOOKINGPRESS_CLASSES_DIR . '/frontend/class.bookingpress_spam_protection.php') ) {
    include_once BOOKINGPRESS_CLASSES_DIR . '/frontend/class.bookingpress_spam_protection.php';
}


// widget Files

if (file_exists(BOOKINGPRESS_WIDGET_DIR . '/class.bookingpress_frontwidget.php') ) {
    include_once BOOKINGPRESS_WIDGET_DIR . '/class.bookingpress_frontwidget.php';
}

// Elementer Files

$bpa_update_elementor_notice = get_option( 'bookingpress_update_elementor_widget' );

if ( !empty( $bpa_update_elementor_notice ) && 1 == $bpa_update_elementor_notice && file_exists( BOOKINGPRESS_WIDGET_DIR . '/bookingpress_elementer.php' ) ) {
    include_once BOOKINGPRESS_WIDGET_DIR . '/bookingpress_elementer.php';
}

if( file_exists( BOOKINGPRESS_WIDGET_DIR . '/bookingpress-elementor-widget/bookingpress-elementor-widget.php' ) ){
    include_once BOOKINGPRESS_WIDGET_DIR . '/bookingpress-elementor-widget/bookingpress-elementor-widget.php';
}

add_action('plugins_loaded', 'bookingpress_load_textdomain');
/**
 * Loading plugin text domain
 */
function bookingpress_load_textdomain()
{
    load_plugin_textdomain('bookingpress-appointment-booking', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}


if ( ! function_exists( 'array_key_first' ) ) {
	/**
	 * Polyfill for array_key_first() function added in PHP 7.3.
	 *
	 * Get the first key of the given array without affecting
	 * the internal array pointer.
	 */
	function array_key_first( array $array ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.arrayFound
		foreach ( $array as $key => $value ) {
			return $key;
		}
	}
}

if ( ! function_exists( 'array_key_last' ) ) {
	/**
	 * Polyfill for `array_key_last()` function added in PHP 7.3.
	 *
	 * Get the last key of the given array without affecting the
	 * internal array pointer.
	 */
	function array_key_last( array $array ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.arrayFound
		if ( empty( $array ) ) {
			return null;
		}

		end( $array );

		return key( $array );
	}
}

global $bpa_api_url, $BookingPress;

$bpa_api_url = $BookingPress->bpagetapiurl();
$bpa_plugin_slug = basename(dirname(__FILE__));

add_filter('pre_set_site_transient_update_plugins', 'bpa_check_for_plugin_update');

function bpa_check_for_plugin_update( $checked_data ){
    $bpa_lite_force_update_check = get_transient('bpa_lite_force_update_check');
    if( !empty( $bpa_lite_force_update_check ) ){
        global $bpa_api_url, $bpa_plugin_slug, $wp_version, $BookingPress, $bookingpress_version;

        //Comment out these two lines during testing.
		if (empty($checked_data->checked)){
            return $checked_data;
        }

        $args = array(
            'slug' => $bpa_plugin_slug,
            'version' => $bookingpress_version,
            'other_variables' => $BookingPress->bookingpress_get_remote_post_params()
        );

        $request_string = array(
            'body' => array(
                'action' => 'basic_check',
                'request' => serialize( $args ),
                'api-key' => md5( BOOKINGPRESS_HOME_URL )
            ),
            'sslverify' => false,
            'user-agent' => 'BKPLITE-WordPress/'.$wp_version.';'.BOOKINGPRESS_HOME_URL
        );

        //Start checking for an update
        $raw_response = wp_remote_post( $bpa_api_url, $request_string );

        if( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) ){
            $response = @unserialize( $raw_response['body'] );
        }

        
        if( isset( $response['access_request'] ) && !empty( $response['access_request'] ) && 'success' == $response['access_request'] ){
            if( isset( $response['access_package'] ) && !empty( $response['access_package'] ) ){
                $update_package = @unserialize( $response['access_package'] );
                
                if( isset( $update_package ) && is_object( $update_package ) && is_object( $checked_data ) && !empty( $update_package ) ){
                    $checked_data->response[$bpa_plugin_slug .'/' . $bpa_plugin_slug .'.php'] = $update_package;
                }
            }
        }
    }
    return $checked_data;
}

add_filter( 'plugins_api', 'bpa_plugin_api_call', 11, 3 );

function bpa_plugin_api_call( $res, $action, $args ){

    $bpa_lite_force_update_check = get_transient( 'bpa_lite_force_update_check' );
    if( !empty( $bpa_lite_force_update_check ) ){
        global $bpa_plugin_slug, $bpa_api_url, $wp_version, $bookingpress_version;

		if ( !isset( $args->slug ) || ( $args->slug != $bpa_plugin_slug ) ){
            return $res;
        }

        $args->version = $bookingpress_version;

		$request_string = array(
			'body' => array(
				'action' => $action,
				'request' => serialize($args),
				'api-key' => md5(BOOKINGPRESS_HOME_URL)
			),
			'sslverify' => false,
			'user-agent' => 'BKPLITE-WordPress/' . $wp_version . '; ' . BOOKINGPRESS_HOME_URL
		);

		$request = wp_remote_post($bpa_api_url, $request_string);

		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', '<p>'.esc_html__('An Unexpected HTTP Error occurred during the API request.', 'bookingpress-appointment-booking') . "</p> <p><a href='?' onclick='document.location.reload(); return false;>".esc_html__('Try again','bookingpress-appointment-booking')."</a></p>", $request->get_error_message());
		} else {
			$res = unserialize($request['body']);

			if ($res === false)
				$res = new WP_Error('plugins_api_failed', esc_html__('An unknown error occurred', 'bookingpress-appointment-booking'), $request['body']);
		}
    }

    return $res;
}

add_action( 'init', 'bpa_lite_validate_plugin_update' );

function bpa_lite_validate_plugin_update(){

    $bpa_lite_force_update_check = get_transient( 'bpa_lite_force_update_check' );

    if( empty( $bpa_lite_force_update_check ) ){
        if( !function_exists( 'plugins_api' ) ){
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }

        $validate_active_plugin = plugins_api(
            'plugin_information',
            array(
                'slug' => 'bookingpress-appointment-booking',
                'fields' => array(
                    'sections' => false,
                    'versions' => true
                )
            )
        );

        if( is_wp_error( $validate_active_plugin ) ){
            $expiration_sec = 60 * 60 * 24;
            set_transient( 'bpa_lite_force_update_check', 1, $expiration_sec );
        }
    }

}
<?php
/*
    Plugin Name: BookingPress Appointment Booking
    Description: Book appointments, create bookings, and pay online with BookingPress. Easily create appointments, manage time, and send out customized emails.
    Version: 1.1.51
    Requires at least: 5.3.0
    Requires PHP:      5.6
    Plugin URI: https://www.bookingpressplugin.com/
    Author: Booking & Appointment - Repute Infosystems
    Text Domain: bookingpress-appointment-booking
    Domain Path: /languages
    Author URI: https://www.bookingpressplugin.com/
    License: GPLv2 or later
    License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !defined( 'BOOKINGPRESS_DIR_NAME') ){
    define('BOOKINGPRESS_DIR_NAME', dirname(plugin_basename(__FILE__)));
}

if( !defined( 'BOOKINGPRESS_DIR' ) ){
    define('BOOKINGPRESS_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_DIR_NAME);
}

require_once BOOKINGPRESS_DIR . '/autoload.php';

add_filter('plugin_action_links', 'bookingpress_plugin_links', 10, 2);
function bookingpress_plugin_links($links, $file){
    global $wp, $wpdb, $BookingPress;
    if ($file == plugin_basename(__FILE__) && !is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ) {

        if( isset( $links['deactivate'] ) ) {
            $deactivation_link = $links['deactivate'];
            // Insert an onClick action to allow form before deactivating
            $deactivation_link = str_replace( '<a ',
                '<div class="bpalite-deactivate-form-wrapper">
                    <span class="bpalite-deactivate-form" id="bpa-deactivate-form-' . esc_attr('BookingPressLite') . '"></span>
                </div><a id="bpa-deactivate-link-' . esc_attr('BookingPressLite') . '"', $deactivation_link );
            $links['deactivate'] = $deactivation_link;
        }

        $link = '<a title="' . esc_html__('Upgrade To Premium', 'bookingpress-appointment-booking') . '" href="https://www.bookingpressplugin.com/pricing/?utm_source=liteversion&utm_medium=plugin&utm_campaign=Upgrade+to+Premium&utm_id=bookingpress_2" style="font-weight:bold;">' . esc_html__('Upgrade To Premium', 'bookingpress-appointment-booking') . '</a>';
        array_unshift($links, $link); /* Add Link To First Position */
    }
    return $links;
}
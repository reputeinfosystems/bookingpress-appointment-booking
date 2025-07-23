<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! defined('WPINC') ) { die; }

class Bookingpress_VCExtend
{
    protected static $instance    = null;
    var $is_bookingpress_vdextend = 0;

    public function __construct()
    {
        add_action('init', array( $this, 'Bookingpress_form' ));
        add_action('init', array( $this, 'Bookingpress_my_appointments' ));

        add_action('init', array( $this, 'Bookingpress_init_all_shortcode' ));
    }
    public function Bookingpress_init_all_shortcode()
    {
        if (function_exists('vc_add_shortcode_param') ) {
            vc_add_shortcode_param('Bookingpress_form_shortcode', array( $this, 'Bookingpress_form_shortcode_html' ));
            vc_add_shortcode_param('Bookingpress_my_appointments_shortcode', array( $this, 'Bookingpress_my_appointments_shortcode_html' ));
        }
    }
    public function Bookingpress_form()
    {
        if (function_exists('vc_map') ) {
            vc_map(
                array(
                'name'        => esc_html__('Booking Forms - WordPress Booking Plugin', 'bookingpress-appointment-booking'),
                'description' => '',
                'base'        => 'bookingpress_form',
                'category'    => esc_html__('BookingPress', 'bookingpress-appointment-booking'),
                'class'       => '',
                'controls'    => 'full',
                'icon'        => BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png',
                'params'      => array(
                array(
                'type'        => 'Bookingpress_form_shortcode',
                'heading'     => false,
                'param_name'  => 'bookingpress_form',
                'value'       => '',
                'description' => '&nbsp;',
                'admin_label' => true,
                ),
                ),
                )
            );
        }
    }
    public function Bookingpress_form_shortcode_html( $settings, $value )
    {
        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';
        ?>
        <?php
        if ($this->is_bookingpress_vdextend == 0 ) {
            $this->is_bookingpress_vdextend = 1;
            ?>
            <div><?php esc_html_e('Booking Forms - WordPress Booking Plugin', 'bookingpress-appointment-booking'); ?></div>
            </div>
            <?php
        }
    }
    public function Bookingpress_my_appointments()
    {
        if (function_exists('vc_map') ) {
            vc_map(
                array(
                'name'        => esc_html__('Customer Panel - BookingPress Appointment Plugin', 'bookingpress-appointment-booking'),
                'description' => '',
                'base'        => 'bookingpress_my_appointments',
                'category'    => esc_html__('BookingPress', 'bookingpress-appointment-booking'),
                'class'       => '',
                'controls'    => 'full',
                'icon'        => BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png',
                'params'      => array(
                array(
                'type'        => 'Bookingpress_my_appointments_shortcode',
                'heading'     => false,
                'param_name'  => 'bookingpress_my_appointments',
                'value'       => '',
                'description' => '&nbsp;',
                'admin_label' => true,
                ),
                ),
                )
            );
        }
    }
    public function Bookingpress_my_appointments_shortcode_html( $settings, $value )
    {
        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_armfield" type="hidden" value="' . esc_attr($value) . '" />';
        if ($this->is_bookingpress_vdextend == 0 ) {
            $this->is_bookingpress_vdextend = 1;
            ?>
            <div><?php esc_html_e('Customer Panel - BookingPress Appointment Plugin', 'bookingpress-appointment-booking'); ?></div>
            </div>
            <?php
        }

    }
}
?>

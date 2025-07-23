<?php

if( !defined( 'ABSPATH' ) ) { exit; }

use \Elementor\Controls_Manager;

class BookingPress_Elementor_Form_Widget extends \Elementor\Widget_Base{

    public function get_name(){
        return 'bookingpress_elementor_form_widget';
    }

    public function get_title(){
        return esc_html__('Booking Forms - BookingPress', 'bookingpress-appointment-booking') . '<style>
        .bookingpress_element_icon{
            display: inline-block;
            width: 35px;
            height: 24px;
            background-image: url(' . BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png);
            background-repeat: no-repeat;
            background-position: 0 -5px;
        }
        </style>';
    }

    public function get_icon(){
        return 'bookingpress_element_icon';
    }

    public function get_categories(){
        return [ 'general' ];
    }

    public function get_keywords(){
        return [ 'booking', 'calendar', 'schedule', 'appointment' ];
    }

    protected function register_controls(){

        global $BookingPress;
    
        $retrieve_services = $BookingPress->bookingpress_retrieve_all_services( '', '', '');

        $bpa_services = array();
        foreach( $retrieve_services as $service_id => $service_data ){
            $bpa_services[ $service_id ] = $service_data['bookingpress_service_name'];
        }

        $this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'BookingPress', 'bookingpress-appointment-booking' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
            'bpa_select_service',
            [
                'label' => esc_html__( 'Select Service', 'bookingpress-appointment-booking'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'default' => array(),
                'multiple' => true,
                'options' => $bpa_services,
                'label_block' => true,
                
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {

        $settings = $this->get_settings_for_display();

        $bpa_service_id = 0;
        $service_id = array();
        if( !empty( $settings['bpa_select_service'])){

            foreach( $settings['bpa_select_service'] as $settings_val ){
                $service_id[$bpa_service_id] = $settings_val;
                $bpa_service_id++;
            }
        }
        $bpa_service_ids = implode(',', $service_id);

        if( !empty($bpa_service_ids)) {
            echo '[bookingpress_form service='.esc_attr($bpa_service_ids).']';
        } else {
            echo '[bookingpress_form]';
        }

    }

}
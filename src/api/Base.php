<?php

namespace BookingPress\api;

use BookingPress\core\Permissions;

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    protected function validate_action_permission( $action_name = '', $skip_validation = false ) {
        if ( ! is_user_logged_in() ) {
            return new \WP_Error(
                'rest_not_logged_in',
                __( 'You must be logged in to access this resource.', 'bookingpress-appointment-booking' ),
                [ 'status' => 401 ]
            );
        }

        if( ! $skip_validation ){

            if ( empty( $action_name ) || ! is_string( $action_name ) ) {
                return new \WP_Error(
                    'rest_invalid_action',
                    __( 'Invalid permission configuration for this route.', 'bookingpress-appointment-booking' ),
                    [ 'status' => 500 ]
                );
            }
            
            if ( ! Permissions::user_can_do_action( $action_name ) ) {
                return new \WP_Error(
                    'rest_forbidden',
                    __( 'You are not allowed to access this resource.', 'bookingpress-appointment-booking' ),
                    [ 'status' => 403 ]
                );
            }
        }

        return true;
    }

    protected function permission_callback_for( $action_name = '', $skip_validation = false ) {
        return function( $request ) use ( $action_name ) {
            return $this->validate_action_permission( $action_name, $skip_validation );
        };
    }

    abstract public function register_routes();
}
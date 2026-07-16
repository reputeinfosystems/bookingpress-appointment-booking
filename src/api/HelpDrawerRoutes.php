<?php

namespace BookingPress\api;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class HelpDrawerRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/help-drawer', [
            'methods'  => 'POST',
            'callback' => [ $this, 'handle_help_drawer' ],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ] );
    }

    public function handle_help_drawer( $request ) {

        $module = $request->get_param('module');
        $page   = $request->get_param('page');
        
        $type   = $request->get_param('type');

            $wpnonce = $request->get_header('x-wp-nonce');

            if ( empty( $wpnonce ) || ! wp_verify_nonce( $wpnonce, 'wp_rest' ) ) {
            return new \WP_REST_Response(
                [
                    'variant' => 'error',
                    'success' => false,
                    'message' => esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' )
                ],
                403
            );
        }

        $bookingpress_documentation_content = '';

        if ( ! empty( $module ) && ! empty( $page ) && ! empty( $type ) ) {

            $help_module = sanitize_text_field( $module );
            $help_page   = sanitize_text_field( $page );
            $help_type   = sanitize_text_field( $type );

            $bookingpress_remote_url = 'https://www.bookingpressplugin.com/';

            if ( $help_type == 'list' || $help_type == 'add' ) {

                $bookingpress_remote_params = array(
                    'method'  => 'POST',
                    'body'    => array(
                        'action' => 'get_documentation',
                        'module' => $help_module,
                        'page'   => $help_page,
                    ),
                    'timeout' => 45,
                );

                $bookingpress_documentation_res = wp_remote_post( $bookingpress_remote_url, $bookingpress_remote_params );
                if ( ! is_wp_error( $bookingpress_documentation_res ) ) {
                    $bookingpress_documentation_content = ! empty( $bookingpress_documentation_res['body'] ) ? $bookingpress_documentation_res['body'] : '';
                } else {
                    $bookingpress_documentation_content = $bookingpress_documentation_res->get_error_message();
                }
            }
        }

        return new \WP_REST_Response(
            [
                'variant' => 'success',
                'success' => true,
                'data' => [
                    'helpDrawerData' => $bookingpress_documentation_content
                ]
            ],
            200
        );
    }

}
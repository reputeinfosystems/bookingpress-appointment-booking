<?php

namespace BookingPress\api;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NotificationRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/notifications/status', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'get_notification_status' ],
            'permission_callback' => $this->permission_callback_for( 'retrieve_email_notification_status' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/notifications/get', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'get_notification_data' ],
            'permission_callback' => $this->permission_callback_for( 'retrieve_email_notification' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/notifications/save', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'save_notification_data' ],
            'permission_callback' => $this->permission_callback_for( 'save_email_notification' ),
        ] );
    }

    /**
     * Get default notification status for all triggers
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_notification_status( $request ) {
        global $wpdb, $tbl_bookingpress_notifications;

        $bookingpress_default_notification_status_data = [
            'customer' => [
                'appointment_approved' => true,
                'appointment_pending'  => true,
                'appointment_rejected' => true,
                'appointment_canceled' => true,
                'share_appointment'    => true,
            ],
            'employee' => [
                'appointment_approved' => true,
                'appointment_pending'  => true,
                'appointment_rejected' => true,
                'appointment_canceled' => true,
                'share_appointment'    => true,
            ],
        ];

        $bookingpress_default_notifications_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = 'default' AND bookingpress_notification_is_custom = 0", ARRAY_A );

        if ( ! empty( $bookingpress_default_notifications_data ) ) {
            foreach ( $bookingpress_default_notifications_data as $val ) {
                $status   = ( '1' === (string) $val['bookingpress_notification_status'] );
                $receiver = $val['bookingpress_notification_receiver_type'];

                if ( ! isset( $bookingpress_default_notification_status_data[ $receiver ] ) ) {
                    continue;
                }

                switch ( $val['bookingpress_notification_name'] ) {
                    case 'Appointment Approved':
                        $bookingpress_default_notification_status_data[ $receiver ]['appointment_approved'] = $status;
                        break;
                    case 'Appointment Pending':
                        $bookingpress_default_notification_status_data[ $receiver ]['appointment_pending'] = $status;
                        break;
                    case 'Appointment Rejected':
                        $bookingpress_default_notification_status_data[ $receiver ]['appointment_rejected'] = $status;
                        break;
                    case 'Appointment Canceled':
                        $bookingpress_default_notification_status_data[ $receiver ]['appointment_canceled'] = $status;
                        break;
                    case 'Share Appointment URL':
                        $bookingpress_default_notification_status_data[ $receiver ]['share_appointment'] = $status;
                        break;
                }
            }
        }

        $bookingpress_default_notification_status_data = apply_filters( 'add_bookingpress_default_notification_status', $bookingpress_default_notification_status_data, $bookingpress_default_notifications_data );

        return rest_ensure_response( $bookingpress_default_notification_status_data );
    }

    /**
     * Get email notification content and settings
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_notification_data( $request ) {
        global $wpdb, $tbl_bookingpress_notifications;

        $receiver_type     = sanitize_text_field( $request->get_param( 'bookingpress_notification_receiver_type' ) ?? '' );
        $notification_type = sanitize_text_field( $request->get_param( 'bookingpress_notification_type' ) ?? 'default' );
        $notification_name = sanitize_text_field( $request->get_param( 'bookingpress_notification_name' ) ?? '' );

        $response = [
            'variant'                => 'error',
            'title'                  => esc_html__( 'Error', 'bookingpress-appointment-booking' ),
            'msg'                    => esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' ),
            'return_data'            => [],
            'is_custom_notification' => 0,
        ];

        if ( ! empty( $receiver_type ) && ! empty( $notification_type ) && ! empty( $notification_name ) ) {
            $record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_type = %s AND bookingpress_notification_is_custom = 0 AND bookingpress_notification_receiver_type = %s", $notification_name, $notification_type, $receiver_type ), ARRAY_A );

            if ( ! empty( $record ) ) {
                $record['bookingpress_notification_subject'] = stripslashes_deep( $record['bookingpress_notification_subject'] );
                $record['bookingpress_notification_message'] = stripslashes_deep( $record['bookingpress_notification_message'] );
                $record = apply_filters( 'bookingpress_get_notifiacation_data_filter', $record );

                $response['return_data'] = $record;
                $response['msg']         = esc_html__( 'Data received successfully', 'bookingpress-appointment-booking' );
            } else {
                $response['msg']         = esc_html__( 'No data received', 'bookingpress-appointment-booking' );
            }

            $response['variant'] = 'success';
            $response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
        }

        $response = apply_filters( 'bookingpress_get_email_notification_data_modified', $response, $request->get_params() );

        return rest_ensure_response( $response );
    }

    /**
     * Save email notification content and settings
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function save_notification_data( $request ) {
        global $wpdb, $tbl_bookingpress_notifications, $bookingpress_global_options;

        $response = [
            'variant'                => 'error',
            'title'                  => esc_html__( 'Error', 'bookingpress-appointment-booking' ),
            'msg'                    => esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' ),
            'return_data'            => [],
            'is_custom_notification' => 0,
        ];

        $global_options_data = $bookingpress_global_options->bookingpress_global_options();
        $allowed_html        = ! empty( $global_options_data['allowed_html'] ) ? json_decode( $global_options_data['allowed_html'], true ) : [];

        $receiver            = sanitize_text_field( $request->get_param( 'notification_receiver' ) ?? '' );
        $name                = sanitize_text_field( $request->get_param( 'notification_name' ) ?? '' );
        $raw_subject         = $request->get_param( 'notification_subject' ) ?? '';
        $subject             = ! empty( $allowed_html ) ? wp_kses( $raw_subject, $allowed_html ) : sanitize_text_field( $raw_subject );
        $raw_msg             = $request->get_param( 'notification_msg' ) ?? '';
        $msg                 = ! empty( $allowed_html ) ? wp_kses( $raw_msg, $allowed_html ) : $raw_msg;
        $msg                 = htmlspecialchars_decode( stripslashes_deep( $msg ) );

        $selected_default_notification = sanitize_text_field( $request->get_param( 'selected_default_notification' ) ?? '' );

        $status_input = $request->get_param( 'default_notification_status' );
        $status_val   = false;
        if ( is_array( $status_input ) && isset( $status_input[ $receiver ][ $selected_default_notification ] ) ) {
            $status_val = $status_input[ $receiver ][ $selected_default_notification ];
        }

        $notification_status = ( 'true' === (string) $status_val || true === $status_val || '1' === (string) $status_val || 1 === $status_val ) ? 1 : 0;

        $if_notification_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_notification_id) FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_type = %s AND bookingpress_notification_receiver_type = %s", $name, 'default', $receiver ) );

        $database_modify_data = [
            'bookingpress_notification_receiver_type' => $receiver,
            'bookingpress_notification_name'          => $name,
            'bookingpress_notification_status'        => $notification_status,
            'bookingpress_notification_type'          => 'default',
            'bookingpress_notification_subject'       => $subject,
            'bookingpress_notification_message'       => $msg,
            'bookingpress_updated_at'                 => current_time( 'mysql' ),
        ];

        $database_modify_data = apply_filters( 'bookingpress_save_email_notification_data_filter', $database_modify_data, $request->get_params() );

        if ( $if_notification_exists > 0 ) {
            $exist_record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_type = %s AND bookingpress_notification_receiver_type = %s", $name, 'default', $receiver ), ARRAY_A );
            $notification_id = $exist_record['bookingpress_notification_id'];

            $wpdb->update( $tbl_bookingpress_notifications, $database_modify_data, [ 'bookingpress_notification_id' => $notification_id ] );
        } else {
            $database_modify_data['bookingpress_created_at'] = current_time( 'mysql' );
            $wpdb->insert( $tbl_bookingpress_notifications, $database_modify_data );
        }

        $response['variant'] = 'success';
        $response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
        $response['msg']     = esc_html__( 'Email notifications details updated successfully.', 'bookingpress-appointment-booking' );

        do_action( 'bookingpress_after_save_email_notification_data', $request->get_params() );

        return rest_ensure_response( $response );
    }
}

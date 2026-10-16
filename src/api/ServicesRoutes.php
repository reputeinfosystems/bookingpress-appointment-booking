<?php

namespace BookingPress\api;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ServicesRoutes extends Base {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'bookingpress-app/v1', '/services', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'get_services' ],
            'permission_callback' => $this->permission_callback_for( 'retrieve_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/save', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'save_service' ],
            'permission_callback' => $this->permission_callback_for( 'add_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/delete', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'delete_service' ],
            'permission_callback' => $this->permission_callback_for( 'delete_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/bulk-delete', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'bulk_delete_services' ],
            'permission_callback' => $this->permission_callback_for( 'delete_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/duplicate', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'duplicate_service' ],
            'permission_callback' => $this->permission_callback_for( 'add_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/edit', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'edit_service' ],
            'permission_callback' => $this->permission_callback_for( 'retrieve_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/update-position', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'update_service_position' ],
            'permission_callback' => $this->permission_callback_for( 'add_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/categories', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'get_categories' ],
            'permission_callback' => $this->permission_callback_for( 'retrieve_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/categories/save', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'save_category' ],
            'permission_callback' => $this->permission_callback_for( 'add_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/categories/delete', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'delete_category' ],
            'permission_callback' => $this->permission_callback_for( 'delete_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/categories/update-position', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'update_category_position' ],
            'permission_callback' => $this->permission_callback_for( 'add_services' ),
        ] );

        register_rest_route( 'bookingpress-app/v1', '/services/upload-image', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'upload_service_image' ],
            'permission_callback' => $this->permission_callback_for( 'add_services' ),
        ] );
    }

    private function get_services_response( $search_service_name = '', $search_service_category = '' ) {
        global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_categories, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_appointment_bookings, $BookingPress;

        $where = 'WHERE 1 = 1';

        if ( '' !== $search_service_category && 'all' !== $search_service_category ) {
            $where .= $wpdb->prepare( ' AND bookingpress_category_id = %d', absint( $search_service_category ) );
        }

        if ( '' !== $search_service_name ) {
            $where .= $wpdb->prepare( ' AND bookingpress_service_name LIKE %s', '%' . $wpdb->esc_like( $search_service_name ) . '%' );
        }

        $service_rows = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_services} {$where} ORDER BY bookingpress_service_position ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $items        = array();
        $counter      = 1;
        $current_date = date( 'Y-m-d', current_time( 'timestamp' ) );

        foreach ( (array) $service_rows as $service_row ) {
            $service_id     = intval( $service_row['bookingpress_service_id'] );
            $category_id    = intval( $service_row['bookingpress_category_id'] );
            $category       = array();
            $duration_value = esc_html( $service_row['bookingpress_service_duration_val'] );
            $duration_unit  = esc_html( $service_row['bookingpress_service_duration_unit'] );

            if ( $category_id > 0 ) {
                $category = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_category_name FROM {$tbl_bookingpress_categories} WHERE bookingpress_category_id = %d", $category_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            }

            if ( 'm' === $duration_unit ) {
                $duration_label = $duration_value . ' ' . ( 1 === intval( $duration_value ) ? esc_html__( 'Min', 'bookingpress-appointment-booking' ) : esc_html__( 'Mins', 'bookingpress-appointment-booking' ) );
            } else {
                $duration_label = $duration_value . ' ' . ( 1 === intval( $duration_value ) ? esc_html__( 'Hour', 'bookingpress-appointment-booking' ) : esc_html__( 'Hours', 'bookingpress-appointment-booking' ) );
            }

            $service_img_meta = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_servicemeta_value FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = %s", $service_id, 'service_image_details' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $service_images   = ! empty( $service_img_meta['bookingpress_servicemeta_value'] ) ? \BookingPress\BookingPressLoader::bpa_safe_maybe_unserialize( $service_img_meta['bookingpress_servicemeta_value'] ) : array();
            $service_img_url  = ! empty( $service_images[0]['url'] ) ? $service_images[0]['url'] : '';
            $service_img_name = ! empty( $service_images[0]['name'] ) ? $service_images[0]['name'] : '';

            $future_appointments = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date >= %s AND (bookingpress_appointment_status != %s AND bookingpress_appointment_status != %s)", $service_id, $current_date, '3', '4' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            $service_enabled = ( isset( $bookingpress_services ) && method_exists( $bookingpress_services, 'bookingpress_get_service_meta' ) ) ? $bookingpress_services->bookingpress_get_service_meta( $service_id, 'show_service_on_site' ) : 'true';
            $service_enabled = empty( $service_enabled ) ? 'true' : $service_enabled;

            $items[] = array(
                'id'                         => $counter,
                'service_id'                 => $service_id,
                'service_name'               => esc_html( stripslashes_deep( $service_row['bookingpress_service_name'] ) ),
                'category_id'                => $category_id,
                'service_category'           => ! empty( $category_id ) && ! empty( $category['bookingpress_category_name'] ) ? stripslashes_deep( $category['bookingpress_category_name'] ) : esc_html__( 'Uncategorized', 'bookingpress-appointment-booking' ),
                'service_duration'           => $duration_label,
                'service_duration_val'       => $duration_value,
                'service_duration_unit'      => $duration_unit,
                'service_price'              => $BookingPress->bookingpress_price_formatter_with_currency_symbol( $service_row['bookingpress_service_price'] ),
                'service_price_raw'          => $service_row['bookingpress_service_price'],
                'service_description'        => stripslashes_deep( $service_row['bookingpress_service_description'] ),
                'service_img_details'        => $service_img_url,
                'service_image_name'         => $service_img_name,
                'service_attachment_id'      => 0,
                'service_bulk_action'        => ! empty( $future_appointments ),
                'bookingpress_service_enabled' => $service_enabled,
                'selected'                   => false,
            );

            $counter++;
        }

        $data      = array(
            'items' => $items,
            'total' => count( $service_rows ),
        );
        $post_data = array(
            'search_data' => array(
                'service_name'         => $search_service_name,
                'selected_category_id' => $search_service_category,
            ),
        );

        return rest_ensure_response( apply_filters( 'bookingpress_modify_servies_listing_data', $data, $post_data, $service_rows ) );
    }

    private function get_categories_response() {
        global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_categories;

        $categories = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_categories} ORDER BY bookingpress_category_position ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $items      = array();
        $counter    = 1;

        foreach ( (array) $categories as $category ) {
            $category_id    = intval( $category['bookingpress_category_id'] );
            $total_services = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_service_id) FROM {$tbl_bookingpress_services} WHERE bookingpress_category_id = %d", $category_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            $items[] = array(
                'id'             => $counter,
                'category_id'    => $category_id,
                'category_name'  => stripslashes_deep( $category['bookingpress_category_name'] ),
                'total_services' => intval( $total_services ),
            );

            $counter++;
        }

        $data = array(
            'items' => $items,
            'total' => count( $categories ),
        );

        return rest_ensure_response( apply_filters( 'bookingpress_modified_service_category_response_data', $data ) );
    }

    /**
     * Helper: run a legacy AJAX-style method that calls wp_send_json()+exit,
     * capture its output, decode it, and return a proper REST response.
     *
     * @param callable $callback  The method to call (e.g. [$obj, 'method']).
     * @return \WP_REST_Response
     */
    private function capture_ajax_response( callable $callback ) {
        $cb_name = is_array( $callback ) ? get_class( $callback[0] ) . '::' . $callback[1] : 'closure';
        error_log( '[BP Services REST] capture_ajax_response → calling: ' . $cb_name );

        ob_start();
        try {
            call_user_func( $callback );
        } catch ( \Throwable $e ) {
            $captured = ob_get_clean();
            error_log( '[BP Services REST] EXCEPTION in ' . $cb_name . ': ' . $e->getMessage() );
            error_log( '[BP Services REST] Captured output before exception: ' . $captured );
            return new \WP_REST_Response(
                [ 'variant' => 'error', 'msg' => $e->getMessage() ],
                500
            );
        }
        $output = ob_get_clean();

        error_log( '[BP Services REST] Raw output from ' . $cb_name . ' (length=' . strlen( $output ) . '): ' . substr( $output, 0, 500 ) );

        if ( empty( $output ) ) {
            error_log( '[BP Services REST] ERROR: Empty output from ' . $cb_name );
            return new \WP_REST_Response( [ 'variant' => 'error', 'msg' => 'Empty response from handler.' ], 500 );
        }

        $data = json_decode( $output, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( '[BP Services REST] ERROR: JSON decode failed for ' . $cb_name . ': ' . json_last_error_msg() );
            error_log( '[BP Services REST] Raw output was: ' . $output );
            return new \WP_REST_Response( [ 'variant' => 'error', 'msg' => 'Invalid JSON from handler.', 'raw' => $output ], 500 );
        }

        error_log( '[BP Services REST] SUCCESS: ' . $cb_name . ' returned ' . ( isset( $data['total'] ) ? $data['total'] . ' total, ' . count( $data['items'] ?? [] ) . ' items' : json_encode( array_keys( $data ) ) ) );
        return rest_ensure_response( $data );
    }

    public function get_services( $request ) {
        global $bookingpress_services;

        $search_service_name     = sanitize_text_field( $request->get_param( 'search_service_name' ) ?? '' );
        $search_service_category = sanitize_text_field( $request->get_param( 'search_service_category' ) ?? '' );

        return $this->get_services_response( $search_service_name, $search_service_category );

        error_log( '[BP Services REST] get_services called — name="' . $search_service_name . '", category="' . $search_service_category . '"' );
        error_log( '[BP Services REST] bookingpress_services global exists: ' . ( isset( $bookingpress_services ) ? 'YES (' . get_class( $bookingpress_services ) . ')' : 'NO — NULL!' ) );

        $_POST['action']       = 'bookingpress_get_services';
        $_REQUEST['action']    = 'bookingpress_get_services';
        $_POST['search_data']  = [
            'service_name'         => $search_service_name,
            'selected_category_id' => $search_service_category,
        ];
        $_REQUEST['search_data'] = $_POST['search_data'];
        $_POST['_wpnonce']       = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']    = wp_create_nonce( 'bpa_wp_nonce' );

        error_log( '[BP Services REST] get_services → search_data: ' . json_encode( $_POST['search_data'] ) );
        return $this->capture_ajax_response( [ $bookingpress_services, 'bookingpress_get_services' ] );
    }

    public function save_service( $request ) {
        global $bookingpress_services;

        $params = $request->get_params();
        error_log( '[BP Services REST] save_service called — params: ' . json_encode( $params ) );
        error_log( '[BP Services REST] bookingpress_services global exists: ' . ( isset( $bookingpress_services ) ? 'YES' : 'NO — NULL!' ) );

        foreach ( $params as $key => $value ) {
            $_POST[ $key ]    = $value;
            $_REQUEST[ $key ] = $value;
        }

        if ( ! isset( $_POST['show_service_on_site'] ) || '' === $_POST['show_service_on_site'] ) {
            $update_id = ! empty( $_POST['service_update_id'] ) ? intval( $_POST['service_update_id'] ) : 0;
            if ( ! empty( $update_id ) && isset( $bookingpress_services ) && method_exists( $bookingpress_services, 'bookingpress_get_service_meta' ) ) {
                $existing_status = $bookingpress_services->bookingpress_get_service_meta( $update_id, 'show_service_on_site' );
                $_POST['show_service_on_site']    = ( ! empty( $existing_status ) ) ? $existing_status : 'true';
                $_REQUEST['show_service_on_site'] = $_POST['show_service_on_site'];
            } else {
                $_POST['show_service_on_site']    = 'true';
                $_REQUEST['show_service_on_site'] = 'true';
            }
        }

        $_POST['action']      = 'bookingpress_add_service';
        $_REQUEST['action']   = 'bookingpress_add_service';
        $_POST['_wpnonce']    = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_services, 'bookingpress_add_service' ] );
    }

    public function delete_service( $request ) {
        global $bookingpress_services;

        $service_id = intval( $request->get_param( 'service_id' ) );
        error_log( '[BP Services REST] delete_service called — id=' . $service_id );

        $_POST['delete_id']    = $service_id;
        $_REQUEST['delete_id'] = $service_id;
        $_POST['action']       = 'bookingpress_delete_service';
        $_REQUEST['action']    = 'bookingpress_delete_service';
        $_POST['_wpnonce']     = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']  = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_services, 'bookingpress_delete_service' ] );
    }

    public function bulk_delete_services( $request ) {
        global $bookingpress_services;

        $delete_ids = $request->get_param( 'delete_ids' );
        error_log( '[BP Services REST] bulk_delete_services called — ids: ' . json_encode( $delete_ids ) );

        $_POST['cat_delete_ids']    = $delete_ids;
        $_REQUEST['cat_delete_ids'] = $delete_ids;
        $_POST['bulk_action']       = 'delete';
        $_REQUEST['bulk_action']    = 'delete';
        $_POST['action']            = 'bookingpress_bulk_service';
        $_REQUEST['action']         = 'bookingpress_bulk_service';
        $_POST['_wpnonce']          = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']       = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_services, 'bookingpress_bulk_service' ] );
    }

    public function duplicate_service( $request ) {
        global $bookingpress_services;

        $service_id = intval( $request->get_param( 'service_id' ) );
        error_log( '[BP Services REST] duplicate_service called — id=' . $service_id );

        $_POST['service_id']    = $service_id;
        $_REQUEST['service_id'] = $service_id;
        $_POST['action']        = 'bookingpress_duplicate_service';
        $_REQUEST['action']     = 'bookingpress_duplicate_service';
        $_POST['_wpnonce']      = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']   = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_services, 'bookingpress_duplicate_service' ] );
    }

    public function edit_service( $request ) {
        global $bookingpress_services;

        $service_id = intval( $request->get_param( 'service_id' ) );
        $_POST['edit_id']       = $service_id;
        $_REQUEST['edit_id']    = $service_id;
        $_POST['service_id']    = $service_id;
        $_REQUEST['service_id'] = $service_id;
        $_POST['action']        = 'bookingpress_edit_service';
        $_REQUEST['action']     = 'bookingpress_edit_service';
        $_POST['_wpnonce']      = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']   = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_services, 'bookingpress_edit_service' ] );
    }

    public function update_service_position( $request ) {
        global $wpdb, $tbl_bookingpress_services, $bookingpress_services;

        $params = $request->get_params();

        // Ensure current service positions in database are clean 1..N sequence
        $all_services = $wpdb->get_results( "SELECT bookingpress_service_id, bookingpress_service_position FROM {$tbl_bookingpress_services} ORDER BY bookingpress_service_position ASC, bookingpress_service_id ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if ( ! empty( $all_services ) ) {
            $seq = 1;
            foreach ( $all_services as $srv_row ) {
                if ( intval( $srv_row['bookingpress_service_position'] ) !== $seq ) {
                    $wpdb->update(
                        $tbl_bookingpress_services,
                        [ 'bookingpress_service_position' => $seq ],
                        [ 'bookingpress_service_id' => $srv_row['bookingpress_service_id'] ]
                    );
                }
                $seq++;
            }
        }

        foreach ( $params as $key => $value ) {
            $_POST[ $key ]    = $value;
            $_REQUEST[ $key ] = $value;
        }
        $_POST['action']         = 'bookingpress_position_services';
        $_REQUEST['action']      = 'bookingpress_position_services';
        $_POST['_wpnonce']       = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']    = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_services, 'bookingpress_position_services' ] );
    }

    public function get_categories( $request ) {
        return $this->get_categories_response();
    }

    public function save_category( $request ) {
        global $bookingpress_service_categories;

        $params = $request->get_params();
        foreach ( $params as $key => $value ) {
            $_POST[ $key ]    = $value;
            $_REQUEST[ $key ] = $value;
        }
        $_POST['action']       = 'bookingpress_add_categories';
        $_REQUEST['action']    = 'bookingpress_add_categories';
        $_POST['_wpnonce']     = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']  = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_service_categories, 'bookingpress_add_categories' ] );
    }

    public function delete_category( $request ) {
        global $bookingpress_service_categories;

        $category_id = intval( $request->get_param( 'category_id' ) );
        error_log( '[BP Services REST] delete_category called — id=' . $category_id );

        $_POST['delete_id']    = $category_id;
        $_REQUEST['delete_id'] = $category_id;
        $_POST['action']       = 'bookingpress_delete_category';
        $_REQUEST['action']    = 'bookingpress_delete_category';
        $_POST['_wpnonce']     = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']  = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_service_categories, 'bookingpress_delete_category' ] );
    }

    public function update_category_position( $request ) {
        global $wpdb, $tbl_bookingpress_categories, $bookingpress_service_categories;

        $params = $request->get_params();

        // Ensure category positions in database are cleanly indexed 1..N
        $all_cats = $wpdb->get_results( "SELECT bookingpress_category_id, bookingpress_category_position FROM {$tbl_bookingpress_categories} ORDER BY bookingpress_category_position ASC, bookingpress_category_id ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if ( ! empty( $all_cats ) ) {
            $seq = 1;
            foreach ( $all_cats as $cat_row ) {
                if ( intval( $cat_row['bookingpress_category_position'] ) !== $seq ) {
                    $wpdb->update(
                        $tbl_bookingpress_categories,
                        [ 'bookingpress_category_position' => $seq ],
                        [ 'bookingpress_category_id' => $cat_row['bookingpress_category_id'] ]
                    );
                }
                $seq++;
            }
        }

        foreach ( $params as $key => $value ) {
            $_POST[ $key ]    = $value;
            $_REQUEST[ $key ] = $value;
        }
        $_POST['action']          = 'bookingpress_position_categories';
        $_REQUEST['action']       = 'bookingpress_position_categories';
        $_POST['_wpnonce']        = wp_create_nonce( 'bpa_wp_nonce' );
        $_REQUEST['_wpnonce']     = wp_create_nonce( 'bpa_wp_nonce' );

        return $this->capture_ajax_response( [ $bookingpress_service_categories, 'bookingpress_position_categories' ] );
    }

    public function upload_service_image( $request ) {
        $return_data = array(
            'error'            => 0,
            'msg'              => '',
            'upload_url'       => '',
            'upload_file_name' => '',
        );

        if (
            ! isset( $_FILES['file'] ) ||
            empty( $_FILES['file']['tmp_name'] )
        ) {
            return new \WP_REST_Response( [
                'error' => 1,
                'msg'   => 'No file received'
            ], 400 );
        }

        $file      = $_FILES['file'];
        $file_name = isset( $file['name'] ) ? current_time( 'timestamp' ) . '_' . sanitize_file_name( $file['name'] ) : '';

        $upload_dir = BOOKINGPRESS_TMP_IMAGES_DIR . '/';
        $upload_url = BOOKINGPRESS_TMP_IMAGES_URL . '/';

        if ( ! file_exists( $upload_dir ) ) {
            wp_mkdir_p( $upload_dir );
        }

        $destination = $upload_dir . $file_name;
        $check_file  = wp_check_filetype_and_ext(
            $file['tmp_name'],
            $file['name']
        );

        if ( empty( $check_file['ext'] ) ) {
            $return_data['error'] = 1;
            $return_data['msg']   = esc_html__( 'Invalid file extension. Please select valid file', 'bookingpress-appointment-booking' );
            return new \WP_REST_Response( $return_data, 200 );
        }

        $upload_file = move_uploaded_file( $file['tmp_name'], $destination );
        if ( ! $upload_file ) {
            $return_data['error'] = 1;
            $return_data['msg']   = esc_html__( 'Something went wrong while updating the file', 'bookingpress-appointment-booking' );
            return new \WP_REST_Response( $return_data, 200 );
        }

        $return_data['error']            = 0;
        $return_data['msg']              = esc_html__( 'Avatar uploaded successfully', 'bookingpress-appointment-booking' );
        $return_data['upload_url']       = $upload_url . $file_name;
        $return_data['upload_file_name'] = $file_name;

        return new \WP_REST_Response( $return_data, 200 );
    }
}

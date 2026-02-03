<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $BookingPress, $wpdb, $bookingpress_version;

$bookingpress_old_version = get_option('bookingpress_version', true);

if (version_compare($bookingpress_old_version, '1.0.2', '<') ) {
    $tbl_bookingpress_default_workhours = $wpdb->prefix . 'bookingpress_default_workhours';
    $wpdb->query("UPDATE `{$tbl_bookingpress_default_workhours}` SET `bookingpress_start_time` = NULL, bookingpress_end_time = NULL WHERE bookingpress_start_time = '00:00:00' AND bookingpress_end_time = '00:00:00'"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
}


if (version_compare($bookingpress_old_version, '1.0.3', '<') ) {
    $args  = array(
        'role'   => 'administrator',
        'fields' => 'id',
    );
    $users = get_users($args);

    if (count($users) > 0 ) {
        foreach ( $users as $key => $user_id ) {
            $bookingpressroles = $BookingPress->bookingpress_capabilities();
            $userObj           = new WP_User($user_id);
            foreach ( $bookingpressroles as $bookingpressrole => $bookingpress_roledescription ) {
                $userObj->add_cap($bookingpressrole);
            }
            unset($bookingpressrole);
            unset($bookingpressroles);
            unset($bookingpress_roledescription);
        }
    }
}

if (version_compare($bookingpress_old_version, '1.0.6', '<') ) {
    $tbl_bookingpress_entries = $wpdb->prefix . 'bookingpress_entries';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_customer_id INT(11) DEFAULT NULL AFTER bookingpress_entry_id"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

    $tbl_bookingpress_users = $wpdb->prefix . 'bookingpress_users';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users} CHANGE bookingpress_wpuser_id bookingpress_wpuser_id INT(11) NULL DEFAULT NULL"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_users is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users} CHANGE bookingpress_user_password bookingpress_user_password VARCHAR(255) NULL DEFAULT NULL"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_users is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users} CHANGE bookingpress_user_country_phone bookingpress_user_country_phone VARCHAR(60) NULL DEFAULT NULL"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_users is table name defined globally. False Positive alarm

    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_entry_id INT(11) DEFAULT NULL AFTER bookingpress_appointment_booking_id"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    // Update customers avatar to usermeta table
    $tbl_bookingpress_users_meta = $wpdb->prefix . 'bookingpress_usermeta';

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    @set_time_limit(0);

    $charset_collate = '';
    if ($wpdb->has_cap('collation') ) {
        if (! empty($wpdb->charset) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }
    $bookingpress_dbtbl_create = array();
    $sql_table                 = "DROP TABLE IF EXISTS `{$tbl_bookingpress_users_meta}`;
    CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_users_meta}`(
        `bookingpress_usermeta_id` int(11) NOT NULL AUTO_INCREMENT,
        `bookingpress_customer_id` int(11) NOT NULL,
        `bookingpress_usermeta_key` TEXT NOT NULL,
        `bookingpress_usermeta_value` TEXT DEFAULT NULL,
        `bookingpress_usermeta_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`bookingpress_usermeta_id`)
    ) {$charset_collate};";

    $bookingpress_dbtbl_create[ $tbl_bookingpress_users_meta ] = dbDelta($sql_table);

    $bookingpress_customer_avatar_details = array();

    $bookingpress_customers_details = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_users} WHERE bookingpress_user_type = 2", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_users is table name defined globally. False Positive alarm
    if (is_array($bookingpress_customers_details) && ! empty($bookingpress_customers_details) ) {
        foreach ( $bookingpress_customers_details as $customer_detail_key => $customer_detail_val ) {
            $customer_id = $customer_detail_val['bookingpress_customer_id'];
            $wpuser_id   = $customer_detail_val['bookingpress_wpuser_id'];

            if (! empty($customer_id) && ! empty($wpuser_id) ) {
                $bookingpress_get_existing_avatar_details = get_user_meta($wpuser_id, 'customer_avatar_details', true);
                if (! empty($bookingpress_get_existing_avatar_details) ) {
                    $bookingpress_customer_avatar_details[] = array(
                    'bookingpress_customer_id'    => $customer_id,
                    'bookingpress_usermeta_key'   => 'customer_avatar_details',
                    'bookingpress_usermeta_value' => $bookingpress_get_existing_avatar_details,
                    );
                }
            }
        }
    }

    if (is_array($bookingpress_customer_avatar_details) && ! empty($bookingpress_customer_avatar_details) ) {
        foreach ( $bookingpress_customer_avatar_details as $customer_avatar_key => $customer_avatar_val ) {
            $BookingPress->update_bookingpress_usermeta($customer_avatar_val['bookingpress_customer_id'], $customer_avatar_val['bookingpress_usermeta_key'], maybe_serialize($customer_avatar_val['bookingpress_usermeta_value']));
        }
    }
}

if (version_compare($bookingpress_old_version, '1.0.9', '<') ) {
    $tbl_bookingpress_entries           = $wpdb->prefix . 'bookingpress_entries';
    $tbl_bookingpress_users             = $wpdb->prefix . 'bookingpress_users';
    $tbl_bookingpress_users_meta        = $wpdb->prefix . 'bookingpress_usermeta';
    $tbl_bookingpress_customers         = $wpdb->prefix . 'bookingpress_customers';
    $tbl_bookingpress_customers_meta    = $wpdb->prefix . 'bookingpress_customers_meta';
    $tbl_bookingpress_default_workhours = $wpdb->prefix . 'bookingpress_default_workhours';
     // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff,$tbl_bookingpress_users,$tbl_bookingpress_users_meta, $tbl_bookingpress_default_workhours are table names. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} CHANGE bookingpress_user_id bookingpress_customer_id bigint(11) DEFAULT NULL");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users} CHANGE bookingpress_user_id bookingpress_customer_id bigint(11) NOT NULL AUTO_INCREMENT");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_usermeta_id bookingpress_customermeta_id bigint(11) NOT NULL AUTO_INCREMENT");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_user_id bookingpress_customer_id bigint(11) NOT NULL");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_usermeta_key bookingpress_customersmeta_key TEXT NOT NULL");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_usermeta_value bookingpress_customersmeta_value TEXT DEFAULT NULL");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_usermeta_created_date bookingpress_customersmeta_created_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");


    $wpdb->query("ALTER TABLE {$tbl_bookingpress_users} DROP COLUMN bookingpress_user_password");

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_default_workhours} CHANGE bookingpress_employee_workhours_id bookingpress_workhours_id smallint NOT NULL AUTO_INCREMENT");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_default_workhours} CHANGE bookingpress_employee_workday_key bookingpress_workday_key varchar(11) NOT NULL");

    // RENAME TABLE wpa_bookingpress_users TO wpa_bookingpress_customers
    $wpdb->query("RENAME TABLE {$tbl_bookingpress_users} TO {$tbl_bookingpress_customers}");
    $wpdb->query("RENAME TABLE {$tbl_bookingpress_users_meta} TO {$tbl_bookingpress_customers_meta}");
     // phpcs:enable
}

if (version_compare($bookingpress_old_version, '1.0.12', '<') ) {
    global $BookingPress;

    $tbl_bookingpress_customers = $wpdb->prefix . 'bookingpress_customers';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_customers} ADD bookingpress_created_at INT(11)  NOT NULL DEFAULT 0  AFTER bookingpress_user_country_phone"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_customers} ADD bookingpress_created_by INT(1)  NOT NULL DEFAULT 0 AFTER bookingpress_created_at"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
    $tbl_bookingpress_payment_logs = $wpdb->prefix . 'bookingpress_payment_logs';
    $is_update_column              = $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_invoice_id bigint(11)  NOT NULL DEFAULT 0  AFTER bookingpress_payment_log_id"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
    if ($is_update_column ) {
        $bookingpress_payment_log_data = $wpdb->get_results('SELECT `bookingpress_payment_log_id` FROM ' . $tbl_bookingpress_payment_logs); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
        $i                             = 0;
        if (! empty($bookingpress_payment_log_data) ) {
            foreach ( $bookingpress_payment_log_data as $log_data ) {
                $i++;
                $bookingpress_log_id = $log_data->bookingpress_payment_log_id;
                $wpdb->update(
                    $tbl_bookingpress_payment_logs,
                    array(
                    'bookingpress_invoice_id' => $i,
                    ),
                    array( 'bookingpress_payment_log_id' => $bookingpress_log_id ),
                    array( '%d' ),
                    array( '%d' )
                );
            }
        }
        $BookingPress->bookingpress_update_settings('bookingpress_last_invoice_id', 'invoice_setting', $i);
    }
}

if (version_compare($bookingpress_old_version, '1.0.13', '<') ) {
    global $BookingPress;
    $tbl_bookingpress_settings = $wpdb->prefix . 'bookingpress_settings';

    $bookingpress_approve_redirection_page_id = $bookingpress_pending_redirection_page_id = $bookingpress_canceled_redirection_page_id = 0;

    // Get default thankyou page id & cancel page id
    $bookingpress_wp_pages = get_pages();
    $bookingpress_wp_pages = json_decode(wp_json_encode($bookingpress_wp_pages), true);
    foreach ( $bookingpress_wp_pages as $bookingpress_page_key => $bookingpress_page_val ) {
        if ($bookingpress_page_val['post_name'] == 'thank-you' ) {
            $bookingpress_pending_redirection_page_id = $bookingpress_approve_redirection_page_id = $bookingpress_page_val['ID'];
        }

        if ($bookingpress_page_val['post_name'] == 'cancel-appointment' ) {
            $bookingpress_canceled_redirection_page_id = $bookingpress_page_val['ID'];
        }
    }

    // Get current redirection urls
    $bookingpress_approve_redirection_url = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_settings} WHERE setting_name = %s", 'redirect_url_after_booking_approved'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally. False Positive alarm
    if ($bookingpress_approve_redirection_url ) {
        $bookingpress_approve_redirection_tmp_page_id = url_to_postid($bookingpress_approve_redirection_url['setting_value']);
        if (! empty($bookingpress_approve_redirection_tmp_page_id) ) {
            $bookingpress_approve_redirection_page_id = $bookingpress_approve_redirection_tmp_page_id;
        }
    }

    $bookingpress_pending_redirection_url = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_settings} WHERE setting_name = %s", 'redirect_url_after_booking_pending'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally. False Positive alarm
    if (! empty($bookingpress_pending_redirection_url) ) {
        $bookingpress_pending_redirection_tmp_page_id = url_to_postid($bookingpress_pending_redirection_url['setting_value']);
        if (! empty($bookingpress_pending_redirection_tmp_page_id) ) {
            $bookingpress_pending_redirection_page_id = $bookingpress_pending_redirection_tmp_page_id;
        }
    }

    $bookingpress_canceled_redirection_url = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_settings} WHERE setting_name = %s", 'redirect_url_after_booking_canceled'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally. False Positive alarm
    if (! empty($bookingpress_canceled_redirection_url) ) {
        $bookingpress_canceled_redirection_tmp_page_id = url_to_postid($bookingpress_canceled_redirection_url['setting_value']);
        if (! empty($bookingpress_canceled_redirection_tmp_page_id) ) {
            $bookingpress_canceled_redirection_page_id = $bookingpress_canceled_redirection_tmp_page_id;
        }
    }

    $bookingpress_redirection_rule_setting_form = array(
    'after_appointment_approved_redirection_type'      => 'fixed_redirection',
    'after_appointment_approved_fixed_redirection_val' => $bookingpress_approve_redirection_page_id,
    'after_appointment_pending_redirection_type'       => 'fixed_redirection',
    'after_appointment_pending_fixed_redirection_val'  => $bookingpress_pending_redirection_page_id,
    'after_appointment_canceled_redirection_type'      => 'fixed_redirection',
    'after_appointment_canceled_fixed_redirection_val' => $bookingpress_canceled_redirection_page_id,
    );

    foreach ( $bookingpress_redirection_rule_setting_form as $bookingpress_redirection_rule_setting_key => $bookingpress_redirection_rule_setting_val ) {
        $BookingPress->bookingpress_update_settings($bookingpress_redirection_rule_setting_key, 'redirection_rule_setting_form', $bookingpress_redirection_rule_setting_val);
    }

    $BookingPress->bookingpress_update_settings('share_timeslot_between_services', 'general_setting', false);
}

if (version_compare($bookingpress_old_version, '1.0.14', '<') ) {

    $tbl_bookingpress_settings = $wpdb->prefix . 'bookingpress_settings';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_settings} CHANGE setting_value setting_value TEXT NULL DEFAULT NULL"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally. False Positive alarm

    $tbl_bookingpress_entries              = $wpdb->prefix . 'bookingpress_entries';
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
    $tbl_bookingpress_payment_logs         = $wpdb->prefix . 'bookingpress_payment_logs';
     // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs are table names. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} CHANGE bookingpress_service_currency bookingpress_service_currency VARCHAR(100) ");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} CHANGE bookingpress_service_currency bookingpress_service_currency VARCHAR(100) ");
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} CHANGE bookingpress_payment_currency bookingpress_payment_currency VARCHAR(100) ");
    // phpcs:enable
}

if (version_compare($bookingpress_old_version, '1.0.15', '<') ) {
    $tbl_bookingpress_form_fields = $wpdb->prefix . 'bookingpress_form_fields';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_form_fields} CHANGE `bookingpress_field_position` `bookingpress_field_position` FLOAT NULL DEFAULT '0';"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
}
if (version_compare($bookingpress_old_version, '1.0.18', '<') ) {
    global $BookingPress;
    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'all_category_title',
        'bookingpress_setting_value' => 'ALL',
        'bookingpress_setting_type'  => 'booking_form',
    );    
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);

    $BookingPress->bookingpress_update_settings('no_payment_method_available', 'message_setting', 'Oops! There is no payment method available.');
}
if ( version_compare( $bookingpress_old_version, '1.0.20', '<' ) ) {
	global $BookingPress;
	$BookingPress->bookingpress_update_settings( 'show_time_as_per_service_duration', 'general_setting', 'true' );
	$BookingPress->bookingpress_update_settings( 'default_time_slot', 'general_setting', '30' );

    $tbl_bookingpress_entries              = $wpdb->prefix . 'bookingpress_entries';
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
    $tbl_bookingpress_payment_logs         = $wpdb->prefix . 'bookingpress_payment_logs';

    //Add appointment end time in appointment_booking table.
    //----------------------------------------------------------------------------------------------
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_appointment_end_time TIME DEFAULT NULL AFTER bookingpress_appointment_time"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    //Add payment id in appointment booking table
    //----------------------------------------------------------------------------------------------
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_payment_id bigint(11) DEFAULT 0 AFTER bookingpress_entry_id"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    //Update appointment end time and payment log id in appointment table.
    //----------------------------------------------------------------------------------------------
    $bookingpress_payment_logs = $wpdb->get_results("SELECT bookingpress_appointment_booking_ref, bookingpress_appointment_end_time, bookingpress_payment_log_id FROM {$tbl_bookingpress_payment_logs}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
    if(!empty($bookingpress_payment_logs) && is_array($bookingpress_payment_logs)){
        foreach($bookingpress_payment_logs as $k => $v){
            $bookingpress_appointment_id = intval($v['bookingpress_appointment_booking_ref']);
            $bookingpress_appointment_data = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
            if($bookingpress_appointment_data > 0 && !empty($v['bookingpress_appointment_end_time'])){
                $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_appointment_end_time' => $v['bookingpress_appointment_end_time'], 'bookingpress_payment_id' => $v['bookingpress_payment_log_id']), array('bookingpress_appointment_booking_id' => $bookingpress_appointment_id) );
            }
        }
    }
    //----------------------------------------------------------------------------------------------

    //Add appointment end time in bookingpress_entries table.
    //----------------------------------------------------------------------------------------------
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_appointment_end_time TIME DEFAULT NULL AFTER bookingpress_appointment_time"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

    $bookingpress_appointment_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_appointment_bookings}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    if(!empty($bookingpress_appointment_data) && is_array($bookingpress_appointment_data)) {
        foreach($bookingpress_appointment_data as $k => $v){
            $bookingpress_entry_id = intval($v['bookingpress_entry_id']);
            $bookingpress_appointment_id = intval($v['bookingpress_appointment_booking_id']);
            if(!empty($bookingpress_entry_id) && !empty($bookingpress_appointment_id)) {
                $bookingpress_entry_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d", $bookingpress_entry_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm     
                 $bookingpress_appointment_data = array();                             
                if(!empty($bookingpress_entry_data)) {        
                    if(!empty($v['bookingpress_appointment_end_time'])) {
                        $wpdb->update($tbl_bookingpress_entries, array('bookingpress_appointment_end_time' => $v['bookingpress_appointment_end_time']), array('bookingpress_entry_id' => $bookingpress_entry_id) );
                    }
                }    
            }                              
        }
    }
}
if ( version_compare( $bookingpress_old_version, '1.0.21', '<' ) ){
    global $BookingPress;
	
    $tbl_bookingpress_entries              = $wpdb->prefix . 'bookingpress_entries';
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
    $tbl_bookingpress_payment_logs         = $wpdb->prefix . 'bookingpress_payment_logs';

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_paid_amount float DEFAULT 0 AFTER bookingpress_appointment_status"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_paid_amount float DEFAULT 0 AFTER bookingpress_appointment_status"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_paid_amount float DEFAULT 0 AFTER bookingpress_additional_info"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_due_amount float DEFAULT 0 AFTER bookingpress_paid_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_due_amount float DEFAULT 0 AFTER bookingpress_paid_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_due_amount float DEFAULT 0 AFTER bookingpress_paid_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

    //Update paid amount in entries and appointment table
    $wpdb->query("UPDATE {$tbl_bookingpress_entries} as bpa_entries, {$tbl_bookingpress_appointment_bookings} as bpa_appointment_bookings SET bpa_entries.bookingpress_paid_amount = bpa_entries.bookingpress_service_price, bpa_appointment_bookings.bookingpress_paid_amount = bpa_appointment_bookings.bookingpress_service_price WHERE bpa_entries.bookingpress_entry_id = bpa_appointment_bookings.bookingpress_entry_id"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    //Update paid amount in payment log table
    $wpdb->query("UPDATE {$tbl_bookingpress_payment_logs} as bpa_payment_logs SET bpa_payment_logs.bookingpress_paid_amount = bpa_payment_logs.bookingpress_service_price"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    // Add the column in the Payment log table.
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_customer_name varchar(255) DEFAULT NULL AFTER bookingpress_customer_id"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_customer_phone varchar(255) DEFAULT NULL AFTER bookingpress_customer_name"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_customer_country VARCHAR(60) DEFAULT NULL AFTER bookingpress_customer_lastname"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

    // Add the column in the Appointmnet table.
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_customer_name varchar(255) DEFAULT NULL AFTER bookingpress_customer_id"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_customer_phone varchar(255) DEFAULT NULL AFTER bookingpress_customer_name"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm    
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_customer_firstname varchar(255) DEFAULT NULL AFTER bookingpress_customer_phone"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm    
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_customer_lastname varchar(255) DEFAULT NULL AFTER bookingpress_customer_firstname"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_customer_country varchar(60) DEFAULT NULL AFTER bookingpress_customer_lastname"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm    
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_customer_email varchar(255) DEFAULT NULL AFTER bookingpress_customer_country"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    $bookingpress_appointment_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_appointment_bookings}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    if(!empty($bookingpress_appointment_data) && is_array($bookingpress_appointment_data)) {
        foreach($bookingpress_appointment_data as $k => $v){
            $bookingpress_entry_id = intval($v['bookingpress_entry_id']);
            $bookingpress_appointment_id = intval($v['bookingpress_appointment_booking_id']);
            if(!empty($bookingpress_entry_id) && !empty($bookingpress_appointment_id)) {
                $bookingpress_entry_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d", $bookingpress_entry_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm     
                 $bookingpress_appointment_data = array();                             
                if(!empty($bookingpress_entry_data)) {
                    $bookingpress_appointment_data = array(
                        'bookingpress_customer_name' => $bookingpress_entry_data['bookingpress_customer_name'],
                        'bookingpress_customer_phone' => $bookingpress_entry_data['bookingpress_customer_phone'],
                        'bookingpress_customer_firstname' => $bookingpress_entry_data['bookingpress_customer_firstname'],
                        'bookingpress_customer_lastname' => $bookingpress_entry_data['bookingpress_customer_lastname'],
                        'bookingpress_customer_country' => $bookingpress_entry_data['bookingpress_customer_country'],
                        'bookingpress_customer_email' => $bookingpress_entry_data['bookingpress_customer_email'],
                    );
                    $wpdb->update($tbl_bookingpress_appointment_bookings,$bookingpress_appointment_data, array('bookingpress_appointment_booking_id' => $bookingpress_appointment_id) );
                    unset($bookingpress_appointment_data['bookingpress_customer_firstname']);
                    unset($bookingpress_appointment_data['bookingpress_customer_lastname']);
                    unset($bookingpress_appointment_data['bookingpress_customer_email']);
                    $wpdb->update($tbl_bookingpress_payment_logs,$bookingpress_appointment_data, array('bookingpress_appointment_booking_ref' => $bookingpress_appointment_id) );
                }    
            }                              
        }
    }
}
if ( version_compare( $bookingpress_old_version, '1.0.22', '<' ) ){
    global $BookingPress,$wpdb;
	$BookingPress->bookingpress_update_settings( 'onsite_appointment_status', 'general_setting', 'Pending' );
}    
if ( version_compare( $bookingpress_old_version, '1.0.23', '<' ) ){
    global $BookingPress,$wpdb;    
    $default_date_format = $BookingPress->bookingpress_get_customize_settings('default_date_format', 'booking_form');
    if($default_date_format != 'F j, Y' && $default_date_format != 'd/m/Y'  && $default_date_format != 'm/d/Y'  && $default_date_format != 'Y-m-d' && $default_date_format != 'd.m.Y' && $default_date_format != 'd-m-Y' ) {
        $default_date_format = 'F j, Y';
    } 
    $BookingPress->bookingpress_update_settings( 'default_date_format', 'general_setting', $default_date_format );

    $bookingpress_after_booking_redirection = $BookingPress->bookingpress_get_settings('after_appointment_approved_fixed_redirection_val', 
    'redirection_rule_setting_form');
    $bookingpress_after_booking_redirection = !empty($bookingpress_after_booking_redirection ) ? $bookingpress_after_booking_redirection  : '';
    $bookingpress_after_cancelled_redirection = $BookingPress->bookingpress_get_settings('after_appointment_canceled_fixed_redirection_val', 
    'redirection_rule_setting_form');
    $bookingpress_after_cancelled_redirection = !empty( $bookingpress_after_cancelled_redirection ) ? $bookingpress_after_cancelled_redirection : '';    
    $bookingpress_cancel_url = $BookingPress->bookingpress_get_settings('paypal_cancel_url','payment_setting');    
    $bookingpress_cancel_url = !empty($bookingpress_cancel_url) ? url_to_postid($bookingpress_cancel_url)  : '';   
    if(empty($bookingpress_cancel_url)) {
        $bookingpress_cancel_url = !empty(get_option('page_on_front')) ? get_option('page_on_front') : 0 ;  
    }
    /*
    $title_font_size = $BookingPress->bookingpress_get_customize_settings('title_font_size', 'booking_form');
    $title_font_size = !empty($title_font_size) ? $title_font_size : '16';
    */
    $booking_form_css = $BookingPress->bookingpress_get_customize_settings('custom_css', 'booking_form');
    $booking_my_booking_css = $BookingPress->bookingpress_get_customize_settings('custom_css', 'booking_my_booking');
    $booking_form_css .= $booking_my_booking_css;    

    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $booking_form = array(
        'after_booking_redirection'            => $bookingpress_after_booking_redirection,
        'after_failed_payment_redirection'     => $bookingpress_cancel_url,        
        'sub_title_color'             => '#535D71',
        'custom_css'                  => $booking_form_css,
    );		
    foreach($booking_form as $key => $value) {
        $bookingpress_customize_settings_db_fields = array(
            'bookingpress_setting_name'  => $key,
            'bookingpress_setting_value' => $value,
            'bookingpress_setting_type'  => 'booking_form',
        );
        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
    }
    $my_booking_form = array(
        'after_cancelled_appointment_redirection'     => $bookingpress_after_cancelled_redirection,
        'reset_button_title'          => esc_html__('Reset', 'bookingpress-appointment-booking'),  
        'apply_button_title'          => esc_html__('Apply', 'bookingpress-appointment-booking'),  
        'search_appointment_title'    => esc_html__('Search appointments', 'bookingpress-appointment-booking'),  
    );		    
    foreach($my_booking_form as $key => $value) {
        $bookingpress_customize_settings_db_fields = array(
            'bookingpress_setting_name'  => $key,
            'bookingpress_setting_value' => $value,
            'bookingpress_setting_type'  => 'booking_my_booking',
        );
        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
    }
}
if ( version_compare( $bookingpress_old_version, '1.0.24', '<' ) ) {    
    global $BookingPress,$wpdb;    
    update_option('bookingpress_customize_changes_notice', 0);    
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';    
    $bookingpress_get_customize_notice = get_option('bookingpress_customize_changes_notice');    
    if(empty($bookingpress_get_customize_notice) || $bookingpress_get_customize_notice == 0) {
        $booking_form = array(
            'sub_title_color' => '#535D71',
        );	
        foreach($booking_form as $key => $value) {
            $bookingpress_customize_settings_db_fields = array(
                'bookingpress_setting_name'  => $key,
                'bookingpress_setting_value' => $value,
                'bookingpress_setting_type'  => 'booking_form',
            );
            $wpdb->update( 
                $tbl_bookingpress_customize_settings,
                $bookingpress_customize_settings_db_fields, 
                array(
                    'bookingpress_setting_name' => $key,
                    'bookingpress_setting_type' => 'booking_form',
                ) 
            );
        }
    }
}
if ( version_compare( $bookingpress_old_version, '1.0.25', '<' ) ) {    
    global $BookingPress,$wpdb;    
    $bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
    $bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
    $bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
    $bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
    $bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
    $bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
    $bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
    $bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');    
    $bookingpress_primary_background_color = $BookingPress->bookingpress_get_customize_settings('primary_background_color', 'booking_form');

    $bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
    $bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
    $bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
    $bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
    $bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
    $bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
    $bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
    $bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';    
    $bookingpress_primary_background_color = !empty($bookingpress_primary_background_color) ? $bookingpress_primary_background_color : '#e2faf1';


    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';
    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_booking_form_settings';                       
    $my_booking_form = array(
        'background_color' => $bookingpress_background_color,
        'row_background_color' => $bookingpress_footer_background_color,
        'primary_color' => $bookingpress_primary_color,
        'content_color' => $bookingpress_content_color,
        'label_title_color' => $bookingpress_label_title_color,
        'title_font_family' => $bookingpress_title_font_family,        
        'sub_title_color'   => $bookingpress_sub_title_color,
        'price_button_text_color' => $bookingpress_price_button_text_color,
    );      

    $booking_form = array(
        'background_color' => $bookingpress_background_color,
        'footer_background_color' => $bookingpress_footer_background_color,
        'primary_color' => $bookingpress_primary_color,
        'primary_background_color'=> $bookingpress_primary_background_color,
        'label_title_color' => $bookingpress_label_title_color,
        'title_font_family' => $bookingpress_title_font_family,                
        'content_color' => $bookingpress_content_color,                
        'price_button_text_color' => $bookingpress_price_button_text_color,
        'sub_title_color' => $bookingpress_sub_title_color,
    );        
    $bookingpress_custom_data_arr['booking_form'] = $booking_form;
    $bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;
    $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);

}    
if ( version_compare( $bookingpress_old_version, '1.0.26', '<' ) ) {
    global $wpdb, $BookingPress;
    $tbl_bookingpress_payment_logs = $wpdb->prefix . 'bookingpress_payment_logs';
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
    $tbl_bookingpress_entries = $wpdb->prefix . 'bookingpress_entries';
    $tbl_bookingpress_customers = $wpdb->prefix . 'bookingpress_customers';
    $tbl_bookingpress_services = $wpdb->prefix . 'bookingpress_services';

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_services} CHANGE bookingpress_service_price bookingpress_service_price DOUBLE(15,5) NOT NULL"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
    
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_customer_phone_dial_code VARCHAR(5) DEFAULT NULL AFTER bookingpress_customer_country"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_customer_phone_dial_code VARCHAR(5) DEFAULT NULL AFTER bookingpress_customer_country"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_customer_phone_dial_code VARCHAR(5) DEFAULT NULL AFTER bookingpress_customer_country"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_customers} ADD bookingpress_user_country_dial_code VARCHAR(5) DEFAULT NULL AFTER bookingpress_user_country_phone"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
}
if ( version_compare( $bookingpress_old_version, '1.0.28', '<' ) ) {
    global $wpdb, $BookingPress;
    $tbl_bookingpress_payment_logs = $wpdb->prefix . 'bookingpress_payment_logs';
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';

    $wpdb->update($tbl_bookingpress_payment_logs, array('bookingpress_payment_status' => '1'), array('bookingpress_payment_status' => 'success'));
    $wpdb->update($tbl_bookingpress_payment_logs, array('bookingpress_payment_status' => '2'), array('bookingpress_payment_status' => 'pending'));
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} MODIFY bookingpress_payment_status smallint(1) DEFAULT 1"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

    $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_appointment_status' => '1'), array('bookingpress_appointment_status' => 'Approved'));
    $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_appointment_status' => '2'), array('bookingpress_appointment_status' => 'Pending'));
    $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_appointment_status' => '3'), array('bookingpress_appointment_status' => 'Cancelled'));
    $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_appointment_status' => '4'), array('bookingpress_appointment_status' => 'Rejected'));
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} MODIFY bookingpress_appointment_status smallint(1) DEFAULT 1"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

    $bookingpress_default_status_option = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');
    $bookingpress_new_default_status = 1;
    if($bookingpress_default_status_option == "Approved"){
        $bookingpress_new_default_status = 1;
    }else if($bookingpress_default_status_option == "Pending"){
        $bookingpress_new_default_status = 2;
    }
    $BookingPress->bookingpress_update_settings('appointment_status', 'general_setting', $bookingpress_new_default_status);

    $bookingpress_onsite_default_status_option = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');
    $bookingpress_onsite_new_default_status = 1;
    if($bookingpress_default_status_option == "Approved"){
        $bookingpress_onsite_new_default_status = 1;
    }else if($bookingpress_default_status_option == "Pending"){
        $bookingpress_onsite_new_default_status = 2;
    }
    $BookingPress->bookingpress_update_settings('onsite_appointment_status', 'general_setting', $bookingpress_onsite_new_default_status);
}    
if ( version_compare( $bookingpress_old_version, '1.0.29', '<' ) ) {
    global $wpdb, $BookingPress,$wpdb,$bookingpress_global_options;
    $tbl_bookingpress_payment_logs = $wpdb->prefix . 'bookingpress_payment_logs';
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
    $tbl_bookingpress_entries = $wpdb->prefix . 'bookingpress_entries';
    $bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
    $bookingpress_countries_currency_details = json_decode($bookingpress_options['countries_json_details']);        
    $bookingpress_default_currency = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
    foreach ( $bookingpress_countries_currency_details as $currency_key => $currency_val ) {
        $bookingpress_currency_code = $currency_val->code;
        $bookingpress_currency_name = $currency_val->name;
        if ( $bookingpress_currency_name == $bookingpress_default_currency ) {                        
            $BookingPress->bookingpress_update_settings( 'payment_default_currency', 'payment_setting',$bookingpress_currency_code);                
        }
        $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_service_currency' => $bookingpress_currency_code ), array('bookingpress_service_currency' => $bookingpress_currency_name ));
        $wpdb->update($tbl_bookingpress_payment_logs,array('bookingpress_payment_currency' => $bookingpress_currency_code), array('bookingpress_payment_currency' => $bookingpress_currency_name));
        $wpdb->update($tbl_bookingpress_entries,array('bookingpress_service_currency' => $bookingpress_currency_code), array('bookingpress_service_currency' => $bookingpress_currency_name));        
    }


    //Update timezones for existing customers
    $bookingpress_current_timezone = wp_timezone_string();
    $tbl_bookingpress_entries = $wpdb->prefix . 'bookingpress_entries';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_customer_timezone VARCHAR(50) DEFAULT NULL AFTER bookingpress_customer_email"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
    $wpdb->query("UPDATE {$tbl_bookingpress_entries} SET bookingpress_customer_timezone = '{$bookingpress_current_timezone}'"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
    
    $tbl_bookingpress_customers = $wpdb->prefix . 'bookingpress_customers';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_customers} ADD bookingpress_user_timezone VARCHAR(50) DEFAULT NULL AFTER bookingpress_user_country_dial_code"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
    $wpdb->query("UPDATE {$tbl_bookingpress_customers} SET bookingpress_user_timezone = '{$bookingpress_current_timezone}'"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm


    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_appointment_timezone VARCHAR(50) DEFAULT NULL AFTER bookingpress_due_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query("UPDATE {$tbl_bookingpress_appointment_bookings} SET bookingpress_appointment_timezone = '{$bookingpress_current_timezone}'"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm


    //Update booking id column to db
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_booking_id VARCHAR(255) DEFAULT NULL AFTER bookingpress_appointment_booking_id"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    $wpdb->query("UPDATE {$tbl_bookingpress_appointment_bookings} as bpa_appointment, {$tbl_bookingpress_payment_logs} as bpa_payment_logs SET bpa_appointment.bookingpress_booking_id = bpa_payment_logs.bookingpress_invoice_id WHERE bpa_appointment.bookingpress_appointment_booking_id = bpa_payment_logs.bookingpress_appointment_booking_ref"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

    //Update delete account content
    $bookingpress_delete_account_content = '<div class="bpa-front-cp-delete-account-wrapper">
    <div class="bpa-front-dcw__vector">
    <svg width="306" height="187" viewBox="0 0 306 187" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path opacity="0.3" d="M211.536 186.663C212.664 185.998 213.781 185.31 214.886 184.601C228.966 175.555 241.049 162.228 245.039 145.975C249.03 129.722 243.319 110.575 229.022 101.876C216.592 94.3128 200.676 95.7294 186.676 99.6925C172.675 103.654 159.189 109.917 144.716 111.417C127.574 113.193 109.197 108.323 93.6292 115.715C76.351 123.918 68.5627 146.337 74.1599 164.628C76.7263 173.011 81.6272 180.396 87.9578 186.431L211.536 186.663Z" fill="#E9EDF5"/>
    <path opacity="0.6" d="M103.995 186.593L103.943 186.56C102.839 185.858 101.747 185.134 100.695 184.407C85.3464 173.787 74.9624 159.448 71.4556 144.032C67.3307 125.9 73.8934 106.47 87.0587 97.837C97.3193 91.1084 111.161 90.3286 128.202 95.5185C132.633 96.8688 137.08 98.4988 141.382 100.077C150.216 103.317 159.351 106.669 168.749 107.714C174.567 108.36 180.631 108.151 186.496 107.945C197.282 107.568 208.434 107.18 218.24 112.195C234.362 120.437 242.849 143.415 237.159 163.415C234.703 172.048 229.708 178.879 220.457 186.255L220.4 186.3H220.327L103.995 186.593ZM107.813 92.5555C99.8804 92.5555 93.0179 94.4349 87.2908 98.1898C74.2661 106.731 67.7803 125.971 71.8668 143.939C75.3497 159.251 85.674 173.499 100.935 184.06C101.965 184.773 103.034 185.483 104.117 186.17L220.253 185.877C229.392 178.577 234.329 171.822 236.754 163.297C242.391 143.48 233.999 120.724 218.047 112.569C208.337 107.604 197.241 107.993 186.509 108.365C180.629 108.571 174.551 108.783 168.702 108.132C159.251 107.082 150.092 103.722 141.234 100.472C136.94 98.8967 132.498 97.268 128.077 95.9217C120.707 93.6762 113.941 92.5555 107.813 92.5555Z" fill="#9192FF"/>
    <path class="bpa-front-dcw__vector-primary-color" d="M263.772 177.548C259.619 177.548 256.242 174.392 256.242 170.514C256.242 166.636 259.619 163.48 263.772 163.48C267.925 163.48 271.301 166.636 271.301 170.514C271.301 174.392 267.925 177.548 263.772 177.548ZM263.772 166.103C261.066 166.103 258.864 168.082 258.864 170.514C258.864 172.947 261.066 174.925 263.772 174.925C266.478 174.925 268.679 172.947 268.679 170.514C268.681 168.082 266.479 166.103 263.772 166.103Z" />
    <path d="M261.124 186.256H244.645L241.828 157.664H263.941L261.124 186.256Z" fill="white"/>
    <path class="bpa-front-dcw__vector-primary-color" d="M261.378 186.537H244.388L241.516 157.381H264.25L261.378 186.537ZM244.899 185.973H260.867L263.628 157.945H242.138L244.899 185.973Z" />
    <path d="M258.255 183.88H247.509L245.672 165.957H260.092L258.255 183.88Z" fill="#E9EDF5"/>
    <path d="M222.623 68.0227C219.484 69.4758 215.304 71.8149 216.099 75.9732C216.878 80.052 221.801 79.3155 224.675 78.3406C226.803 77.6187 228.859 76.648 230.753 75.4345C231.982 74.6467 233.711 73.2847 233.291 71.5963C232.931 70.1464 231.248 69.7216 229.955 69.9873C228.08 70.3723 225.939 71.5147 224.47 72.7187C223.136 73.8119 222.097 75.514 222.359 77.2986C222.597 78.9306 223.944 80.0939 225.444 80.6054C227.312 81.2435 229.846 81.1128 231.723 80.5426C232.865 80.1964 233.92 79.1628 233.173 77.9263C232.289 76.4628 230.454 76.8174 229.182 77.4608C227.466 78.3301 225.432 79.4662 224.584 81.2885C223.939 82.6725 224.042 84.3892 225.296 85.3914C226.525 86.3737 228.298 86.3768 229.787 86.2638C231.019 86.1697 233.031 85.9123 233.227 84.3578C233.424 82.7907 231.497 82.0773 230.272 82.5407C228.376 83.2573 227.187 85.5964 226.612 87.4072C225.933 89.5476 225.945 91.8365 226.605 93.9768C226.688 94.2488 227.117 94.1327 227.034 93.8586C226.175 91.0739 226.473 88.014 227.943 85.4845C228.647 84.272 230.035 82.3566 231.692 82.8922C232.577 83.1788 233.205 84.226 232.42 85.0148C231.869 85.5692 230.94 85.7042 230.199 85.7837C229.125 85.8987 227.97 85.9448 226.914 85.6728C225.093 85.2041 224.187 83.5565 224.865 81.7593C225.418 80.2926 226.773 79.372 228.063 78.6021C229.289 77.8709 231.734 76.3393 232.789 78.1502C233.973 80.1786 230.125 80.393 229.068 80.4736C227.517 80.5908 225.873 80.5353 224.517 79.6806C223.08 78.7768 222.437 77.2149 222.96 75.5778C223.485 73.9312 224.852 72.8338 226.313 72.0262C227.452 71.3965 228.788 70.6631 230.074 70.4152C230.98 70.2405 232.124 70.3838 232.664 71.2437C233.31 72.2731 232.539 73.3966 231.793 74.0996C229.704 76.0674 226.548 77.3101 223.849 78.1408C221.037 79.0059 216.425 79.1921 216.465 75.0537C216.499 71.4749 220.039 69.7049 222.847 68.4056C223.107 68.2864 222.882 67.9035 222.623 68.0227Z" fill="#202C45"/>
    <path d="M57.8316 69C58.7441 70.9192 59.5744 72.8689 60.3822 74.8266L61.5653 77.7724L62.6993 80.7355L61.0069 80.2381L63.8466 78.0098C64.7949 77.2684 65.7618 76.5535 66.7194 75.8227L67.876 74.9407L68.4105 76.279C68.7952 77.2419 69.1519 78.2167 69.5246 79.185C69.8934 80.1545 70.2342 81.136 70.591 82.1109C70.9266 83.0937 71.2767 84.0712 71.5924 85.0633C71.92 86.0501 72.2251 87.0462 72.5102 88.0489L71.6945 88.3818C71.1945 87.4653 70.7157 86.5409 70.2568 85.6084C69.7873 84.68 69.3522 83.737 68.9013 82.8006C68.4729 81.8549 68.0285 80.9159 67.6121 79.9662C67.1996 79.0152 66.7712 78.0695 66.3706 77.1119L68.063 77.5682C67.1134 78.3069 66.1743 79.0603 65.2154 79.7871L62.3359 81.965L61.113 82.8895L60.6435 81.4663L59.65 78.4528L58.7044 75.4221C58.0942 73.3942 57.5053 71.3596 57 69.2944L57.8316 69Z" fill="#FFA121"/>
    <path d="M197.509 49.2944C197.004 51.3596 196.415 53.3942 195.805 55.4221L194.859 58.4528L193.865 61.4663L193.396 62.8895L192.173 61.965L189.294 59.7871C188.335 59.059 187.396 58.3069 186.446 57.5682L188.138 57.1119C187.738 58.0682 187.309 59.0139 186.897 59.9662C186.48 60.9159 186.036 61.8549 185.608 62.8006C185.158 63.737 184.723 64.68 184.253 65.6084C183.795 66.5422 183.316 67.4653 182.816 68.3818L182 68.0489C182.286 67.0449 182.592 66.0501 182.918 65.0633C183.233 64.0712 183.584 63.095 183.919 62.1109C184.276 61.136 184.617 60.1559 184.986 59.185C185.358 58.2167 185.715 57.2419 186.1 56.279L186.634 54.9407L187.791 55.8227C188.747 56.5522 189.715 57.2684 190.664 58.0098L193.503 60.2381L191.811 60.7355L192.945 57.7724L194.128 54.8266C194.936 52.8689 195.766 50.9205 196.679 49L197.509 49.2944Z" fill="#FFA121"/>
    <path class="bpa-front-dcw__vector-primary-color" d="M170.793 105.362L151.217 104.174L125.673 110.924C116.714 113.29 109.442 119.825 106.14 128.479L90.1797 170.275L121.544 142.483L130.182 186.239L188.773 186.05L188.585 125.298L170.793 105.362Z"/>
    <path d="M155.177 128.044C151.629 128.044 148.316 126.662 146.125 124.22L146.544 123.844C148.889 126.457 152.596 127.796 156.456 127.418C160.593 127.016 164.207 124.703 166.374 121.07L166.858 121.359C164.598 125.146 160.827 127.56 156.512 127.979C156.063 128.022 155.619 128.044 155.177 128.044Z" fill="#C5C5FF"/>
    <path d="M157.611 120.398C153.859 122.212 149.251 120.42 147.363 116.409L146.727 115.051L140.27 101.333L136.32 92.933L155.402 83.7109L163.712 101.367C167.126 108.629 164.404 117.114 157.611 120.398Z" fill="#FFBC96"/>
    <path d="M157.222 97.7891C155.542 105.97 151.346 111.575 146.726 115.051L140.27 101.333L157.222 97.7891Z" fill="#1B1B43"/>
    <path d="M159.458 87.5528C159.458 97.4314 153.888 104.204 146.823 107.695C139.552 111.286 131.96 97.4844 128.334 88.2571C124.569 78.6796 134.932 71.4961 143.606 71.4961C152.281 71.4961 159.458 77.6729 159.458 87.5528Z" fill="#FFBC96"/>
    <path d="M159.85 81.9195C159.005 71.7783 156.944 62.1928 147.55 64.6426C143.982 59.6661 129.052 59.7603 125.39 71.5913C123.028 79.2231 115.438 82.9527 115.438 82.9527C124.545 87.7408 132.941 85.9476 144.64 77.0372C148.302 82.389 158.724 82.7644 158.724 82.7644L159.85 81.9195Z" fill="#1B1B43"/>
    <path d="M162.765 86.6849C163.176 83.4828 161.479 80.6268 158.976 80.3058C156.473 79.9848 154.111 82.3204 153.701 85.5225C153.29 88.7245 154.986 91.5805 157.489 91.9015C159.992 92.2225 162.354 89.8869 162.765 86.6849Z" fill="#FFBC96"/>
    <path d="M159.143 83.6904C158.537 84.1904 158.337 84.8682 158.343 85.5937C158.347 85.9518 158.404 86.3165 158.509 86.6627C158.619 87.0089 158.767 87.3365 159.017 87.623L158.85 87.9081C158.386 87.7556 158.04 87.3657 157.828 86.9532C157.61 86.5341 157.504 86.0672 157.504 85.5963C157.508 85.1268 157.613 84.64 157.875 84.2249C158.129 83.8084 158.552 83.4848 159.007 83.3906L159.143 83.6904Z" fill="#1B1B43"/>
    <path d="M143.414 91.3952C144.199 91.3432 144.788 90.5875 144.73 89.7075C144.671 88.8275 143.988 88.1564 143.203 88.2084C142.418 88.2605 141.829 89.0161 141.887 89.8961C141.945 90.7761 142.629 91.4473 143.414 91.3952Z" fill="#1B1B43"/>
    <path d="M136.363 94.0169C137.029 93.7595 137.334 92.946 137.046 92.1999C136.757 91.4539 135.984 91.0577 135.318 91.3151C134.653 91.5726 134.347 92.3861 134.636 93.1321C134.924 93.8782 135.698 94.2743 136.363 94.0169Z" fill="#1B1B43"/>
    <path d="M147.166 85.6844C147.195 86.1314 144.824 85.9868 143.804 86.203C142.934 86.3874 141.607 87.2349 141.011 86.3887C140.472 85.6247 141.826 84.76 143.504 84.6485C145.182 84.5371 147.136 85.2388 147.166 85.6844Z" fill="#1B1B43"/>
    <path d="M131.878 90.5943C132.144 90.9604 133.355 89.7017 134.052 89.3781C134.646 89.1022 135.91 89.13 135.702 88.1751C135.513 87.3143 134.223 87.2864 133.231 88.0093C132.24 88.7321 131.611 90.2283 131.878 90.5943Z" fill="#1B1B43"/>
    <path d="M141.375 100.649C141.917 99.9948 142.693 99.5717 143.479 99.3064C144.273 99.0465 145.089 98.9324 145.914 98.9921L145.975 99.317C145.227 99.6725 144.469 99.8701 143.74 100.106C143.004 100.334 142.313 100.605 141.574 100.913L141.375 100.649Z" fill="#1B1B43"/>
    <path d="M139.239 93.5735C139.28 94.1876 139.19 94.8401 138.873 95.4383C138.721 95.7354 138.501 96.0047 138.278 96.2288C138.059 96.453 137.837 96.6413 137.784 96.7753C137.767 96.8429 137.738 96.896 137.753 96.9981C137.749 97.0591 137.766 97.0631 137.762 97.0724C137.763 97.087 137.787 97.1082 137.802 97.1347C137.906 97.2527 138.154 97.3734 138.402 97.465C138.914 97.648 139.494 97.77 140.066 97.9053L140.058 98.2356C139.442 98.3191 138.831 98.3151 138.197 98.1772C137.881 98.099 137.552 98.0074 137.218 97.717C137.141 97.6308 137.058 97.5379 137.004 97.4172C136.95 97.2872 136.914 97.1506 136.916 97.0604C136.886 96.8655 136.926 96.6174 137.023 96.4145C137.242 96.0126 137.516 95.8336 137.718 95.6452C137.929 95.4622 138.1 95.2765 138.254 95.0656C138.559 94.6425 138.749 94.1093 138.908 93.543L139.239 93.5735Z" fill="#D28572"/>
    <path opacity="0.2" d="M154.602 92.1506C154.626 90.1606 153.033 88.5276 151.043 88.5034C149.053 88.4791 147.42 90.0726 147.396 92.0627C147.371 94.0528 148.965 95.6857 150.955 95.71C152.945 95.7343 154.578 94.1407 154.602 92.1506Z" fill="#FE5B52"/>
    <path class="bpa-front-dcw__vector-primary-color" d="M203.429 125.772C197.862 114.065 186.375 106.314 173.435 105.524L170.793 105.361L190.281 149.738L211.612 141.82L203.429 125.772Z" />
    <path d="M135.344 96.8521C135.133 93.6132 135.695 86.0066 133.724 86.8515C131.753 87.6964 131.26 98.6811 131.26 98.6811C131.26 98.6811 130.979 80.371 128.866 81.2159C126.753 82.0607 126.753 95.3003 122.106 109.385L96.0019 164.514C92.8625 171.146 96.8176 178.958 104.021 180.353C109.305 181.376 114.571 178.508 116.58 173.515C123.34 156.715 135.759 120.461 136.965 113.401C137.95 107.626 137.695 105.128 137.315 103.644C136.685 101.191 135.508 99.3801 135.344 96.8521Z" fill="#FFBC96"/>
    <path d="M122.27 108.167C123.068 105.994 123.591 103.725 124.077 101.457C124.556 99.1848 124.959 96.8956 125.373 94.6089L126.597 87.7372C126.814 86.5925 127.036 85.4479 127.304 84.3086C127.44 83.7383 127.584 83.1706 127.765 82.6042C127.856 82.3217 127.956 82.0392 128.086 81.758C128.149 81.6174 128.225 81.4768 128.319 81.3362C128.421 81.2036 128.53 81.0352 128.804 80.949L128.783 80.9543C128.864 80.9383 128.952 80.9291 129.034 80.9277C129.112 80.9397 129.19 80.9702 129.265 80.9994C129.384 81.071 129.497 81.1612 129.559 81.25C129.713 81.4304 129.798 81.6148 129.883 81.7991C130.045 82.1665 130.161 82.5366 130.258 82.908C130.456 83.6507 130.594 84.3974 130.714 85.1442C130.949 86.6403 131.111 88.1417 131.236 89.6444C131.48 92.6513 131.61 95.6634 131.58 98.6821L130.94 98.6689C131.01 96.9645 131.139 95.2721 131.331 93.5797C131.537 91.8899 131.773 90.1962 132.305 88.5383C132.451 88.1258 132.61 87.7146 132.855 87.3207C132.984 87.1271 133.128 86.9308 133.343 86.7716C133.546 86.6005 133.863 86.5183 134.152 86.5581C134.22 86.574 134.284 86.5978 134.35 86.6204C134.417 86.6496 134.472 86.6482 134.557 86.7238C134.595 86.7544 134.632 86.7888 134.667 86.8233C134.697 86.8565 134.708 86.883 134.729 86.9122C134.762 86.9692 134.809 87.0329 134.829 87.0873C134.928 87.3101 134.993 87.525 135.048 87.7385C135.158 88.1669 135.221 88.5913 135.284 89.0171C135.504 90.7188 135.562 92.4231 135.548 94.1248C135.542 94.983 135.488 95.8159 135.562 96.6409C135.63 97.4685 135.781 98.2895 135.995 99.0973C136.209 99.905 136.483 100.698 136.78 101.486C137.085 102.266 137.39 103.092 137.561 103.933C137.924 105.619 137.964 107.351 137.808 109.048C137.65 110.747 137.318 112.422 136.875 114.062L136.631 113.999C137.041 112.362 137.338 110.696 137.463 109.02C137.586 107.346 137.513 105.653 137.131 104.03C136.947 103.212 136.666 102.444 136.347 101.65C136.041 100.856 135.757 100.048 135.532 99.2206C135.306 98.3917 135.144 97.5428 135.069 96.682C134.986 95.8186 135.037 94.9551 135.036 94.1209C135.04 92.4377 134.969 90.748 134.745 89.0914C134.683 88.6802 134.618 88.2664 134.513 87.8751C134.463 87.6801 134.402 87.4878 134.327 87.3247C134.31 87.2783 134.288 87.2597 134.27 87.2239C134.249 87.1801 134.236 87.1761 134.236 87.1761C134.25 87.1655 134.191 87.1549 134.162 87.143C134.127 87.1324 134.093 87.1151 134.058 87.1072C133.809 87.0674 133.537 87.2875 133.335 87.6218C133.134 87.9494 132.985 88.3314 132.852 88.7173C132.356 90.2917 132.127 91.9748 131.937 93.6434C131.757 95.3159 131.64 97.0056 131.582 98.6848L130.941 98.6715C130.969 95.678 130.871 92.6765 130.641 89.6895C130.526 88.1961 130.375 86.7053 130.151 85.2304C130.037 84.4943 129.904 83.7595 129.72 83.0486C129.629 82.6931 129.519 82.3429 129.38 82.022C129.311 81.8655 129.231 81.705 129.14 81.6002C129.096 81.5325 129.05 81.5127 129.017 81.4835C129.002 81.4848 128.991 81.4742 128.981 81.4689C128.97 81.4768 128.964 81.4782 128.952 81.4715L128.93 81.4768C128.92 81.4755 128.828 81.5445 128.765 81.644C128.697 81.7448 128.632 81.8588 128.576 81.9835C128.461 82.2302 128.364 82.4968 128.275 82.766C128.099 83.3072 127.953 83.8656 127.816 84.4266C127.546 85.55 127.316 86.688 127.095 87.8274L125.831 94.6872C125.414 96.9765 124.959 99.2604 124.442 101.532C123.916 103.8 123.353 106.07 122.508 108.253L122.27 108.167Z" fill="#D28572"/>
    <path d="M221.814 172.788C221.718 179.278 218.889 183.814 215.346 186.428L208.152 186.315L206.372 186.287H206.333L197.876 167.037L197.859 166.992L190.281 149.741L211.61 141.82L219.571 161.359C221.205 165.679 221.864 169.486 221.814 172.788Z" fill="#FFBC96"/>
    <path d="M215.91 165.02L159.917 171.284L152.632 168.93C149.333 167.865 145.721 168.52 143.013 170.683C136.66 175.758 126.331 185.111 129.008 185.863C133.515 187.131 139.401 184.758 144.685 183.432C147.605 182.698 150.688 183.046 153.366 184.417L157.03 186.294L212.81 186.286L215.91 165.02Z" fill="#FFBC96"/>
    <path d="M130.344 184.873C131.854 183.99 133.389 183.15 134.928 182.321C136.462 181.484 138.023 180.699 139.571 179.886C140.334 179.485 141.151 179.074 142.004 178.778C142.856 178.485 143.737 178.272 144.631 178.174C145.525 178.073 146.426 178.063 147.316 178.143C148.204 178.241 149.084 178.412 149.923 178.701L149.837 179.02C148.988 178.854 148.135 178.741 147.28 178.732C146.426 178.704 145.573 178.762 144.735 178.904C143.898 179.045 143.074 179.256 142.281 179.554C141.481 179.835 140.742 180.235 139.952 180.631C138.384 181.405 136.832 182.207 135.253 182.956C133.678 183.715 132.097 184.463 130.494 185.166L130.344 184.873Z" fill="#D28572"/>
    <path d="M131.156 182.074C132.174 181.111 133.236 180.207 134.322 179.328C135.403 178.443 136.519 177.599 137.661 176.789C138.809 175.988 139.971 175.203 141.206 174.524C142.446 173.872 143.709 173.163 145.19 173.023L145.218 173.352C144.578 173.521 143.954 173.77 143.359 174.116C142.75 174.421 142.166 174.791 141.584 175.158C140.419 175.894 139.283 176.687 138.146 177.476C137.008 178.266 135.891 179.091 134.757 179.894C133.63 180.706 132.506 181.528 131.368 182.329L131.156 182.074Z" fill="#D28572"/>
    <path d="M205.285 166.39L197.773 166.574L197.794 167.419L205.306 167.235L205.285 166.39Z" fill="#D28572"/>
    <path d="M185.084 138.502L184.578 138.75L190.032 149.855L190.538 149.607L185.084 138.502Z" fill="#E9EDF5"/>
    <path opacity="0.2" d="M221.815 172.788C221.719 179.278 218.89 183.814 215.347 186.428L208.153 186.315C208.148 186.305 208.148 186.298 208.148 186.288C207.883 185.347 207.742 184.355 207.742 183.33C207.742 178.67 210.638 174.693 214.733 173.093C215.972 172.609 217.32 172.344 218.728 172.344C219.797 172.344 220.835 172.496 221.815 172.788Z" fill="#FE5B52"/>
    <path d="M132.624 179.492H100.512V186.253H132.624V179.492Z" fill="#1B1B43"/>
    <path d="M111.404 186.253H60.4176L45.7695 135.268H96.7554L111.404 186.253Z" fill="#E9EDF5"/>
    <path d="M60.2147 186.311L45.4883 135.055H61.2625V135.478H46.0507L60.6205 186.193L60.2147 186.311Z" fill="#1B1B43"/>
    <path d="M105.942 167.612L96.5965 135.478H73.0938V135.055H96.9135L106.348 167.494L105.942 167.612Z" fill="#1B1B43"/>
    <path d="M109.075 6.46072C109.74 4.22377 108.466 1.87097 106.229 1.20561C103.992 0.540239 101.64 1.81426 100.974 4.05121C100.309 6.28816 101.583 8.64095 103.82 9.30632C106.057 9.97168 108.41 8.69767 109.075 6.46072Z" fill="#E9EDF5"/>
    <path d="M23.7421 113.037C24.4333 110.11 22.6207 107.177 19.6936 106.485C16.7665 105.794 13.8333 107.607 13.1421 110.534C12.4509 113.461 14.2635 116.394 17.1906 117.085C20.1177 117.777 23.0509 115.964 23.7421 113.037Z" fill="#E9EDF5"/>
    <path d="M285.202 126.709C287.536 126.709 289.428 124.817 289.428 122.484C289.428 120.15 287.536 118.258 285.202 118.258C282.868 118.258 280.977 120.15 280.977 122.484C280.977 124.817 282.868 126.709 285.202 126.709Z" fill="#E9EDF5"/>
    <path d="M279.927 125.251L279.916 124.687C280.598 124.674 281.269 124.557 281.91 124.337L282.093 124.87C281.397 125.109 280.668 125.236 279.927 125.251ZM277.749 124.956C277.042 124.746 276.375 124.426 275.765 124.007L276.085 123.543C276.646 123.927 277.26 124.222 277.911 124.417L277.749 124.956ZM284.037 123.845L283.7 123.393C284.246 122.985 284.723 122.499 285.118 121.947L285.576 122.275C285.147 122.874 284.63 123.402 284.037 123.845ZM274.165 122.499C273.712 121.914 273.352 121.267 273.1 120.574L273.63 120.382C273.862 121.019 274.192 121.615 274.61 122.154L274.165 122.499ZM286.564 120.31L286.027 120.138C286.234 119.493 286.339 118.819 286.339 118.136C286.339 117.963 286.332 117.792 286.319 117.622L286.88 117.577C286.894 117.761 286.902 117.948 286.901 118.136C286.902 118.879 286.788 119.61 286.564 120.31ZM272.679 118.419C272.675 118.326 272.673 118.233 272.672 118.139C272.672 117.49 272.759 116.85 272.931 116.233L273.473 116.383C273.315 116.951 273.236 117.541 273.236 118.136C273.236 118.221 273.238 118.309 273.241 118.395L272.679 118.419ZM285.85 115.652C285.593 115.025 285.24 114.443 284.801 113.921L285.232 113.559C285.708 114.126 286.092 114.757 286.372 115.439L285.85 115.652ZM274.311 114.542L273.84 114.232C274.246 113.615 274.742 113.067 275.317 112.603L275.671 113.041C275.143 113.469 274.685 113.973 274.311 114.542ZM283.276 112.591C282.7 112.228 282.076 111.958 281.416 111.79L281.556 111.243C282.271 111.426 282.951 111.72 283.576 112.113L283.276 112.591ZM277.423 112.026L277.22 111.501C277.907 111.234 278.63 111.078 279.369 111.035L279.4 111.598C278.721 111.637 278.056 111.781 277.423 112.026Z" fill="#9192FF"/>
    <path d="M185.208 23.3295C183.499 23.3295 182.109 21.9395 182.109 20.2311C182.109 18.5228 183.499 17.1328 185.208 17.1328C186.916 17.1328 188.306 18.5228 188.306 20.2311C188.306 21.9395 186.916 23.3295 185.208 23.3295ZM185.208 17.6965C183.81 17.6965 182.673 18.8332 182.673 20.2311C182.673 21.6291 183.81 22.7671 185.208 22.7671C186.606 22.7671 187.742 21.6291 187.742 20.2311C187.742 18.8332 186.604 17.6965 185.208 17.6965Z" fill="#9192FF"/>
    <path d="M306 186H2V187H306V186Z" fill="#727E95"/>
    </svg>
    </div>
    <div class="bpa-front-dcw__body">
    <div class="bpa-front-dcw__body-title">Sorry to see you go. Please Confirm</div>
    <div class="bpa-front-dcw__body-sub-title">All your data will be erased</div>
    [bookingpress_delete_account]
    </div>
    </div>';

    $tbl_bookingpress_customize_settings   = $wpdb->prefix . 'bookingpress_customize_settings';
    $wpdb->insert($tbl_bookingpress_customize_settings, array('bookingpress_setting_name' => 'delete_account_content', 'bookingpress_setting_value' => $bookingpress_delete_account_content, 'bookingpress_setting_type' => 'booking_my_booking'));

    $bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
    $bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
    $bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
    $bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
    $bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
    $bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
    $bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
    $bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');    
    $bookingpress_primary_background_color = $BookingPress->bookingpress_get_customize_settings('primary_background_color', 'booking_form');

    $bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
    $bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
    $bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
    $bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
    $bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
    $bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
    $bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
    $bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';    
    $bookingpress_primary_background_color = !empty($bookingpress_primary_background_color) ? $bookingpress_primary_background_color : '#e2faf1';

    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';     
    $my_booking_form = array(
        'background_color' => $bookingpress_background_color,
        'row_background_color' => $bookingpress_footer_background_color,
        'primary_color' => $bookingpress_primary_color,
        'content_color' => $bookingpress_content_color,
        'label_title_color' => $bookingpress_label_title_color,
        'title_font_family' => $bookingpress_title_font_family,        
        'sub_title_color'   => $bookingpress_sub_title_color,
        'price_button_text_color' => $bookingpress_price_button_text_color,
    );      
    $bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;
    $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);

    $my_booking_form = array(
        'search_date_title'    => esc_html__('Please select date', 'bookingpress-appointment-booking'),  
        'my_appointment_menu_title'    => esc_html__('My Appointments', 'bookingpress-appointment-booking'),
        'delete_appointment_menu_title'    => esc_html__('Delete Account', 'bookingpress-appointment-booking')
    );		    
    foreach($my_booking_form as $key => $value) {
        $bookingpress_customize_settings_db_fields = array(
            'bookingpress_setting_name'  => $key,
            'bookingpress_setting_value' => $value,
            'bookingpress_setting_type'  => 'booking_my_booking',
        );
        $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
    }
}    
if ( version_compare( $bookingpress_old_version, '1.0.31', '<' ) ) {
    global $wpdb, $BookingPress;
    $tbl_bookingpress_payment_logs = $wpdb->prefix . 'bookingpress_payment_logs';
    $tbl_bookingpress_customize_settings   = $wpdb->prefix . 'bookingpress_customize_settings';
    $tbl_bookingpress_settings = $wpdb->prefix . 'bookingpress_settings';

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} MODIFY COLUMN bookingpress_invoice_id varchar(50)"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm    

    //Get existing cancel appointment confirmation message
    $bookingpress_cancel_appointment_confirmation_message = $BookingPress->bookingpress_get_settings('confirmation_message_for_the_cancel_appointment', 'message_setting');
    
    //Insert cancel appointment confirmation message setting to customize setting table
    $bookingpress_cancel_appointment_confirmation = array(
        'bookingpress_setting_name' => 'cancel_appointment_confirmation_message',
        'bookingpress_setting_value' => $bookingpress_cancel_appointment_confirmation_message,
        'bookingpress_setting_type' => 'booking_my_booking',
    );

    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_cancel_appointment_confirmation);

    $wpdb->delete($tbl_bookingpress_settings, array('setting_name' => 'confirmation_message_for_the_cancel_appointment'));

    $bookingpress_customize_cancel_msg_arr = array(
        'cancel_appointment_title' => esc_html__('Cancel Appointment', 'bookingpress-appointment-booking'),
        'cancel_appointment_no_btn_text' => esc_html__('No', 'bookingpress-appointment-booking'),
        'cancel_appointment_yes_btn_text' => esc_html__('Yes', 'bookingpress-appointment-booking'),
    );

    foreach($bookingpress_customize_cancel_msg_arr as $cancel_msg_key => $cancel_msg_val){
        $bookingpress_cancel_msgs_data = array(
            'bookingpress_setting_name' => $cancel_msg_key,
            'bookingpress_setting_value' => $cancel_msg_val,
            'bookingpress_setting_type' => 'booking_my_booking',
        );

        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_cancel_msgs_data);
    }
    
    $BookingPress->bookingpress_update_settings('anonymous_data', 'general_setting', 'false');
}
if( version_compare( $bookingpress_old_version, '1.0.33', '<' ) ){
    global $wpdb, $bookingpress_global_options, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers, $tbl_bookingpress_entries;

    $bookingpress_timezone_offset = $bookingpress_global_options->bookingpress_get_utc_offset_of_city();

    /** Appointment Table */
    $get_appointment_timezones = $wpdb->get_results( "SELECT DISTINCT bookingpress_appointment_timezone FROM {$tbl_bookingpress_appointment_bookings}" ) ; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

    if( !empty( $get_appointment_timezones ) ){
        foreach( $get_appointment_timezones as $timezone_data ){
            $timezone_format = $timezone_data->bookingpress_appointment_timezone;

            $timezone_offset = !empty( $bookingpress_timezone_offset[ $timezone_format ] ) ? $bookingpress_timezone_offset[ $timezone_format ] : '';
            if( !empty( $timezone_offset ) ){
                $wpdb->update(
                    $tbl_bookingpress_appointment_bookings,
                    array(
                        'bookingpress_appointment_timezone' => $timezone_offset
                    ),
                    array(
                        'bookingpress_appointment_timezone' => $timezone_format
                    )
                );
            } else {
                $wpdb->update(
                    $tbl_bookingpress_appointment_bookings,
                    array(
                        'bookingpress_appointment_timezone' => $bookingpress_global_options->bookingpress_get_site_timezone_offset()
                    ),
                    array(
                        'bookingpress_appointment_timezone' => $timezone_format
                    )
                );
            }
        }
    }

    /** Customer Table */
    $get_customer_timezones = $wpdb->get_results( "SELECT DISTINCT bookingpress_user_timezone FROM {$tbl_bookingpress_customers}" ) ; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm

    if( !empty( $get_customer_timezones ) ){
        foreach( $get_customer_timezones as $timezone_data ){
            $timezone_format = $timezone_data->bookingpress_user_timezone;

            $timezone_offset = !empty( $bookingpress_timezone_offset[ $timezone_format ] ) ? $bookingpress_timezone_offset[ $timezone_format ] : '';
            if( !empty( $timezone_offset ) ){
                $wpdb->update(
                    $tbl_bookingpress_customers,
                    array(
                        'bookingpress_user_timezone' => $timezone_offset
                    ),
                    array(
                        'bookingpress_user_timezone' => $timezone_format
                    )
                );
            } else {
                $wpdb->update(
                    $tbl_bookingpress_customers,
                    array(
                        'bookingpress_user_timezone' => $bookingpress_global_options->bookingpress_get_site_timezone_offset()
                    ),
                    array(
                        'bookingpress_user_timezone' => $timezone_format
                    )
                );
            }
        }
    }

    /** Entries Table */
    $get_entries_timezones = $wpdb->get_results( "SELECT DISTINCT bookingpress_customer_timezone FROM {$tbl_bookingpress_entries}" ) ; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm

    if( !empty( $get_entries_timezones ) ){
        foreach( $get_entries_timezones as $timezone_data ){
            $timezone_format = $timezone_data->bookingpress_customer_timezone;

            $timezone_offset = !empty( $bookingpress_timezone_offset[ $timezone_format ] ) ? $bookingpress_timezone_offset[ $timezone_format ] : '';
            if( !empty( $timezone_offset ) ){
                $wpdb->update(
                    $tbl_bookingpress_entries,
                    array(
                        'bookingpress_customer_timezone' => $timezone_offset
                    ),
                    array(
                        'bookingpress_customer_timezone' => $timezone_format
                    )
                );
            } else {
                $wpdb->update(
                    $tbl_bookingpress_entries,
                    array(
                        'bookingpress_customer_timezone' => $bookingpress_global_options->bookingpress_get_site_timezone_offset()
                    ),
                    array(
                        'bookingpress_customer_timezone' => $timezone_format
                    )
                );
            }
        }
    }
}
if( version_compare( $bookingpress_old_version, '1.0.34', '<' ) ){
    global $wpdb,$BookingPress;
    $tbl_bookingpress_services = $wpdb->prefix . 'bookingpress_services';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_services} MODIFY bookingpress_category_id smallint DEFAULT 0"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm

    //Rename payment_logs table to payment_transactions
    $tbl_bookingpress_payment_logs = $wpdb->prefix . 'bookingpress_payment_logs';
    $tbl_bookingpress_payment_logs_new = $wpdb->prefix . 'bookingpress_payment_transactions';
    $wpdb->query("RENAME TABLE {$tbl_bookingpress_payment_logs} TO {$tbl_bookingpress_payment_logs_new}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

    update_option('bookingpress_lite_wizard_complete', 1);

    $tbl_bookingpress_customers_meta    = $wpdb->prefix . 'bookingpress_customers_meta';

    //change customer meta key field type text to varchar
    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_customers_meta}` CHANGE `bookingpress_customersmeta_key` `bookingpress_customersmeta_key` VARCHAR(255) NOT NULL;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers_meta is a table name. false alarm
    
    $check_db_permission = $BookingPress->bookingpress_check_db_permission();
    if($check_db_permission)
    {
        $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
        $tbl_bookingpress_customers = $wpdb->prefix . 'bookingpress_customers';
        $tbl_bookingpress_payment_transactions = $wpdb->prefix . 'bookingpress_payment_transactions';
        
        //appointment booking table index
        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD INDEX `bookingpress_appointment_status-appointment_date` (`bookingpress_appointment_status`, `bookingpress_appointment_date`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD INDEX `bookingpress_service_id-appointment_status-appointment_date` (`bookingpress_service_id`, `bookingpress_appointment_status`, `bookingpress_appointment_date`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
        
        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_bookings}` ADD INDEX `bookingpress_appointment_date-appointment_time-appointment_end_t` (`bookingpress_appointment_date`, `bookingpress_appointment_time`, `bookingpress_appointment_end_time`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

        //customer table index
        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_customers}` ADD INDEX `bookingpress_user_type-user_status` (`bookingpress_user_type`, `bookingpress_user_status`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm

        //customer meta table index
        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_customers_meta}` ADD INDEX `bookingpress_customer_id-customersmeta_key` (`bookingpress_customer_id`, `bookingpress_customersmeta_key`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers_meta is a table name. false alarm

        //payment transaction table index
        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_payment_transactions}` ADD INDEX `bookingpress_payment_date_time-payment_status` (`bookingpress_payment_date_time`, `bookingpress_payment_status`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_transactions is a table name. false alarm

        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_payment_transactions}` ADD INDEX `bookingpress_service_id-appointment_date-start_time-end_time` (`bookingpress_service_id`, `bookingpress_appointment_date`, `bookingpress_appointment_start_time`, `bookingpress_appointment_end_time`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_transactions is a table name. false alarm
    }    
    $display_service_description = $BookingPress->bookingpress_get_customize_settings('display_service_description', 'booking_form');
    $service_duration_label = $BookingPress->bookingpress_get_customize_settings('service_duration_label', 'booking_form');
    $service_price_label = $BookingPress->bookingpress_get_customize_settings('service_price_label', 'booking_form');
    if(empty($service_duration_label)) {
        $service_duration_label          = esc_html__('Duration', 'bookingpress-appointment-booking') . ':';
    }
    if(empty($service_price_label)) {
        $service_price_label             = esc_html__('Price', 'bookingpress-appointment-booking') . ':';
    }
    $display_service_description = $display_service_description == 'true' ? 'false' : 'true';
    $bookingpress_booking_form_customize_setting = array(
        'display_service_description' => $display_service_description,
        'service_duration_label' => $service_duration_label,
        'service_price_label'  => $service_price_label,
    );
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    foreach($bookingpress_booking_form_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'bookingpress_setting_name' => $key,
            'bookingpress_setting_value' => $val,
            'bookingpress_setting_type' => 'booking_form',
        );
        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);
    }
}
if( version_compare( $bookingpress_old_version, '1.0.35', '<' ) ){ 

    global $wpdb,$BookingPress, $tbl_bookingpress_form_fields;
    $bookingpress_booking_form_customize_setting = array(
        'id_main_heading' => esc_html__('ID', 'bookingpress-appointment-booking'),
        'service_main_heading' => esc_html__('Service', 'bookingpress-appointment-booking'),
        'date_main_heading' => esc_html__('Date', 'bookingpress-appointment-booking'),
        'status_main_heading' => esc_html__('Status', 'bookingpress-appointment-booking'),
        'payment_main_heading' => esc_html__('Payment', 'bookingpress-appointment-booking'),
        'booking_id_heading' => esc_html__('Booking ID', 'bookingpress-appointment-booking'),
        'booking_time_title' => esc_html__('Time', 'bookingpress-appointment-booking'),
        'payment_details_title' => esc_html__('Payment Details', 'bookingpress-appointment-booking'),
        'payment_method_title' => esc_html__('Payment Method', 'bookingpress-appointment-booking'),
        'total_amount_title' => esc_html__('Total Amount', 'bookingpress-appointment-booking'),
    );
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    foreach($bookingpress_booking_form_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'bookingpress_setting_name' => $key,
            'bookingpress_setting_value' => $val,
            'bookingpress_setting_type' => 'booking_my_booking',
        );
        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);
    }    

    /** add new column in field table */
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} ADD bookingpress_field_is_default tinyint(1) DEFAULT 0" );  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
    $all_fields = $wpdb->get_results( "SELECT bookingpress_form_field_id FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_form_field_id ASC" );  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
    if ( ! empty( $all_fields ) ) {
        foreach ( $all_fields as $field_data ) {
            $bookingpress_field_id   = $field_data->bookingpress_form_field_id;
            $wpdb->update(
                $tbl_bookingpress_form_fields,
                array(
                    'bookingpress_field_is_default' => 1,
                ),
                array(
                'bookingpress_form_field_id' => $bookingpress_field_id,
                )
            );
        }
    }
    /** add new column in field table */
    
}

if( version_compare( $bookingpress_old_version, '1.0.38', '<' ) ){ 
    $args  = array(
        'role'   => 'administrator',
        'fields' => 'id',
    );
    $users = get_users($args);

    if (count($users) > 0 ) {
        foreach ( $users as $key => $user_id ) {
            $userObj = new WP_User($user_id);
            $userObj->add_cap('bookingpress_addons');
            
            unset($bookingpressrole);
            unset($bookingpressroles);
            unset($bookingpress_roledescription);
        }
    }
}

if( version_compare( $bookingpress_old_version, '1.0.40', '<' ) ) {
    $tbl_bookingpress_services = $wpdb->prefix . 'bookingpress_services';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_services} CHANGE bookingpress_service_price bookingpress_service_price DOUBLE NOT NULL"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
}

if( version_compare( $bookingpress_old_version, '1.0.42', '<' ) ) {
    global $BookingPress;
    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'search_end_date_title',
        'bookingpress_setting_value' => esc_html__('Please select date', 'bookingpress-appointment-booking'),
        'bookingpress_setting_type'  => 'booking_my_booking',
    );    
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);

}

if( version_compare( $bookingpress_old_version, '1.0.43', '<' ) ) {
    
    $tbl_bookingpress_notifications = $wpdb->prefix . 'bookingpress_notifications';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} CHANGE bookingpress_notification_subject bookingpress_notification_subject TEXT DEFAULT NULL"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm
}

if( version_compare( $bookingpress_old_version, '1.0.44', '<' ) ) {
    $tbl_bookingpress_notifications = $wpdb->prefix . 'bookingpress_notifications';
    $bookingpress_customer_notification_data = array(
        'bookingpress_notification_name'   => 'Share Appointment URL',
        'bookingpress_notification_receiver_type' => 'customer',
        'bookingpress_notification_status' => 1,
        'bookingpress_notification_type'   => 'default',
        'bookingpress_notification_subject' => esc_html__('Book your appointment now', 'bookingpress-appointment-booking'),
        'bookingpress_notification_message' => 'Hi<br/>Please book your appointment with following URL: <br/>%share_appointment_url%<br/>Thanks,<br/>%company_name%',
        'bookingpress_created_at'          => current_time( 'mysql' ),
    );

    $wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_customer_notification_data );


    $bookingpress_other_notification_data = array(
        'bookingpress_notification_name'   => 'Share Appointment URL',
        'bookingpress_notification_receiver_type' => 'employee',
        'bookingpress_notification_status' => 1,
        'bookingpress_notification_type'   => 'default',
        'bookingpress_notification_subject' => esc_html__('Book your appointment now', 'bookingpress-appointment-booking'),
        'bookingpress_notification_message' => 'Hi administrator,<br>Following appointment URL is shared with customer. <br/>%share_appointment_url%<br/>Thank you,<br/>%company_name%',
        'bookingpress_created_at'          => current_time( 'mysql' ),
    );

    $wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_other_notification_data );
}

if( version_compare( $bookingpress_old_version, '1.0.46', '<' ) ) {
    global $BookingPress;
    $default_time_format = $BookingPress->bookingpress_get_settings('default_time_format','general_setting');
    if($default_time_format == '') {
        $wp_default_time_format  = get_option('time_format');
        if ($wp_default_time_format == 'g:i a' || $wp_default_time_format == 'g:i A') {				
            $wp_default_time_format = 'g:i a';					
        } elseif($wp_default_time_format == 'H:i') {
            $wp_default_time_format = 'H:i';	
        } else {
            $wp_default_time_format = 'g:i a';
        }        
        $BookingPress->bookingpress_update_settings('default_time_format','general_setting',$wp_default_time_format);
    }
}

if( version_compare( $bookingpress_old_version, '1.0.47', '<' ) ) {
    global $wpdb;
    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'appointment_details',
        'bookingpress_setting_value' => esc_html__('Appointment Details', 'bookingpress-appointment-booking'),
        'bookingpress_setting_type'  => 'booking_form',
    );    
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);    
}

if( version_compare( $bookingpress_old_version, '1.0.48', '<' ) ){
    update_option( 'bookingpress_display_bf_popup_after_update', 1);
    update_option( 'bpa_is_displayed_bf_sale_popup', false );

    global $BookingPress, $bookingpress_customize;
    $bookingpress_form_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');

    $bpa_loader_content = $bookingpress_customize->bpa_generate_loader_with_color( $bookingpress_form_primary_color );

}
if( version_compare( $bookingpress_old_version, '1.0.51', '<' ) ){    
    global $wpdb;
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $tbl_bookingpress_customers         = $wpdb->prefix . 'bookingpress_customers';
    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'border_color',
        'bookingpress_setting_value' => '#CFD6E5',
        'bookingpress_setting_type'  => 'booking_form',
    );
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_customers} ADD bookingpress_user_name VARCHAR(255) DEFAULT NULL AFTER bookingpress_user_type"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm

    update_option('bookingpress_customize_changes_notice_1.0.51', 1);
}

if (version_compare($bookingpress_old_version, '1.0.52', '<') ) {

    global $wpdb;
    $bookingpress_booking_form_customize_setting = array(
        'book_appointment_hours_text'	=> 'h',
        'book_appointment_min_text'	=> 'm',
    );
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    foreach($bookingpress_booking_form_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'bookingpress_setting_name' => $key,
            'bookingpress_setting_value' => $val,
            'bookingpress_setting_type' => 'booking_form',
        );
        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
    }
}
if (version_compare($bookingpress_old_version, '1.0.53', '<') ) {

    global $wpdb, $BookingPress;
    $bookingpress_booking_form_customize_setting = array(
        'date_time_step_note'	=> '',
        'summary_step_note'	=> '',
    );
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    foreach($bookingpress_booking_form_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'bookingpress_setting_name' => $key,
            'bookingpress_setting_value' => $val,
            'bookingpress_setting_type' => 'booking_form',
        );
        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
    }
    $BookingPress->bookingpress_update_settings('no_timeslots_available', 'message_setting', esc_html__('There is no time slots available','bookingpress-appointment-booking'));
    $BookingPress->bookingpress_update_settings('cancel_appointment_confirmation','message_setting', esc_html__('Cancel appointment confirmation','bookingpress-appointment-booking'));
    $BookingPress->bookingpress_update_settings('no_appointment_available_for_cancel','message_setting', esc_html__('There is no appointment to cancel','bookingpress-appointment-booking'));

    // Appointment cancellation confirmation page
    $bookingpress_cancellation_confirmation_payment_content= '<div class="wp-block-bookingpress-cancellation_confirmation">[bookingpress_appointment_cancellation_confirmation]</div>';
    $bookingpress_cancel_page_details = array(
    'post_title'   => esc_html__('Appointment cancellation confirmation', 'bookingpress-appointment-booking'),
    'post_name'    => 'appointment-cancellation-confirmation',
    'post_content' => $bookingpress_cancellation_confirmation_payment_content,
    'post_status'  => 'publish',
    'post_parent'  => 0,
    'post_author'  => 1,
    'post_type'    => 'page',
    );
    $bookingpress_cancellation_post_id            = wp_insert_post($bookingpress_cancel_page_details);
    $bookingpress_appointment_cancellation_payment_url = get_permalink($bookingpress_cancellation_post_id);

    $bookingpress_my_booking_customize_setting = array(
        'cancel_booking_id_text' => esc_html__('Booking ID', 'bookingpress-appointment-booking'),
        'cancel_service_text' => esc_html__('Service', 'bookingpress-appointment-booking'),
        'cancel_date_time_text' => str_replace( '&amp;', '&', esc_html__('Date & Time', 'bookingpress-appointment-booking') ),
        'cancel_button_text' => esc_html__('Confirm Cancellation', 'bookingpress-appointment-booking'),
    );    
    if (! empty($bookingpress_appointment_cancellation_payment_url) ) {
        $bookingpress_my_booking_customize_setting['appointment_cancellation_confirmation'] = $bookingpress_cancellation_post_id;
    }

    foreach($bookingpress_my_booking_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'bookingpress_setting_name' => $key,
            'bookingpress_setting_value' => $val,
            'bookingpress_setting_type' => 'booking_my_booking',
        );
        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
    }  

    /* To generate the customize css design */
    $bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
    $bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
    $bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
    $bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
    $bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
    $bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
    $bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
    $bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');    
    $bookingpress_primary_background_color = $BookingPress->bookingpress_get_customize_settings('primary_background_color', 'booking_form');
    $bookingpress_border_color = $BookingPress->bookingpress_get_customize_settings('border_color', 'booking_form');
    $bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
    $bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
    $bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
    $bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
    $bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
    $bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
    $bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
    $bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';    
    $bookingpress_primary_background_color = !empty($bookingpress_primary_background_color) ? $bookingpress_primary_background_color : '#e2faf1';
    $bookingpress_border_color = !empty($bookingpress_border_color) ? $bookingpress_border_color : '#CFD6E5';

    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';
    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_booking_form_settings';                       
    $my_booking_form = array(
        'background_color'        => $bookingpress_background_color,
        'row_background_color'    => $bookingpress_footer_background_color,
        'border_color'            => $bookingpress_border_color,
        'primary_color'           => $bookingpress_primary_color,
        'content_color'           => $bookingpress_content_color,
        'label_title_color'       => $bookingpress_label_title_color,
        'title_font_family'       => $bookingpress_title_font_family,        
        'sub_title_color'         => $bookingpress_sub_title_color,
        'price_button_text_color' => $bookingpress_price_button_text_color,
    );
    $booking_form = array(
        'background_color'        => $bookingpress_background_color,
        'footer_background_color' => $bookingpress_footer_background_color,
        'primary_color'           => $bookingpress_primary_color,
        'primary_background_color'=> $bookingpress_primary_background_color,
        'label_title_color'       => $bookingpress_label_title_color,
        'title_font_family'       => $bookingpress_title_font_family,                
        'content_color'           => $bookingpress_content_color,                
        'price_button_text_color' => $bookingpress_price_button_text_color,
        'sub_title_color'         => $bookingpress_sub_title_color,
        'border_color'            => $bookingpress_border_color,
    );
    $bookingpress_custom_data_arr['booking_form'] = $booking_form;
    $bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;
    $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);
}

if (version_compare($bookingpress_old_version, '1.0.54', '<') ) {
    global $wpdb;
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
    $tbl_bookingpress_customers_meta = $wpdb->prefix . 'bookingpress_customers_meta';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_appointment_token VARCHAR(50) DEFAULT NULL AFTER bookingpress_appointment_timezone");// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.
    $bookingpress_current_date = date('Y-m-d', current_time('timestamp'));
    
    $wpdb->query("UPDATE {$tbl_bookingpress_appointment_bookings} as bpa_appointment, {$tbl_bookingpress_customers_meta} as bpa_customer_meta SET bpa_appointment.bookingpress_appointment_token = bpa_customer_meta.bookingpress_customersmeta_value WHERE bpa_appointment.bookingpress_customer_id = bpa_customer_meta.bookingpress_customer_id AND bpa_customer_meta.bookingpress_customersmeta_key ='bpa_cancel_id' AND bpa_appointment.bookingpress_appointment_date >= '{$bookingpress_current_date}'"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers_meta is table name defined globally. False Positive alarm

}

if(version_compare($bookingpress_old_version, '1.0.55', '<')){
    global $wpdb, $BookingPress;
    $tbl_bookingpress_settings = $wpdb->prefix . 'bookingpress_settings';
            
    $bookingpress_my_booking_customize_setting = array(
        'gmail_client_ID' => '',
        'gmail_client_secret' => '',
        'gmail_redirect_url' => '',
        'gmail_auth_secret' => '',
        'bookingpress_gmail_auth' => '',
        'bookingpress_response_email' => '',
        'bookingpress_gmail_auth_token' => '',
    );    

    foreach($bookingpress_my_booking_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'setting_name' => $key,
            'setting_value' => $val,
            'setting_type' => 'notification_setting',
        );
        $wpdb->insert($tbl_bookingpress_settings, $bookingpress_bd_data);   
    }

}
if( version_compare($bookingpress_old_version, '1.0.62' , '<')){
    global $BookingPress,$tbl_bookingpress_form_fields,$wpdb, $tbl_bookingpress_payment_logs, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $tbl_bookingpress_customers;

    $form_fields_default_data = array(
        'terms_condition'          => array(
            'field_name'     => 'terms_and_conditions',
            'field_type'     => 'terms_and_conditions',
            'is_edit'        => 0,
            'is_required'    => true,
            'label'          => str_replace( '&amp;', '&', esc_html__('I agree with <a target="_blank" href="#">terms & conditions</a>', 'bookingpress-appointment-booking') ),
            'placeholder'    => '',
            'error_message'  => esc_html__('Please tick this box if you want to proceed', 'bookingpress-appointment-booking'),
            'is_hide'        => 1,
            'field_position' => 7,
        ),
        'username'          => array(
            'field_name'     => 'username',
            'field_type'     => 'text',
            'is_edit'        => 0,
            'is_required'    => true,
            'label'          => esc_html__('Username', 'bookingpress-appointment-booking'),
            'placeholder'    => esc_html__('Enter your username', 'bookingpress-appointment-booking'),
            'error_message'  => esc_html__('Please enter your username', 'bookingpress-appointment-booking'),
            'is_hide'        => 1,
            'field_position' => 8,
        ),
    );

    $bookingpress_pro_version = get_option('bookingpress_pro_version');
    foreach ( $form_fields_default_data as $form_field_key => $form_field_val ) {
        $form_field_db_data = array(
        'bookingpress_form_field_name'     => $form_field_val['field_name'],
        'bookingpress_field_required'      => $form_field_val['is_required'],
        'bookingpress_field_label'         => stripslashes_deep($form_field_val['label']),
        'bookingpress_field_placeholder'   => $form_field_val['placeholder'],
        'bookingpress_field_error_message' => $form_field_val['error_message'],
        'bookingpress_field_is_hide'       => $form_field_val['is_hide'],
        'bookingpress_field_position'      => $form_field_val['field_position'],
        'bookingpress_field_is_default'    => 1,
        );
        $wpdb->insert($tbl_bookingpress_form_fields, $form_field_db_data);
        $last_inserted_field_id = $wpdb->insert_id;
        if( !empty($bookingpress_pro_version) && version_compare($bookingpress_pro_version,'2.2', '<') ){

            $bookingpress_visibility = !empty($form_field_val['is_hide']) && $form_field_val['is_hide'] == '1' ? 'hidden' : 'always';
            
            $bookingpress_field_options = array(
                'layout' => '1col',
                'used_for_user_information' => 'true',
                'separate_value' => false,
                'visibility' => $bookingpress_visibility,
                'selected_services' => [],
            );
            
            $bookingpress_field_type = 'text';
            if( 'username' == $form_field_val['field_name'] ){
                $bookingpress_field_type = 'text';
                $bookingpress_field_options['minimum'] = '';
                $bookingpress_field_options['maximum'] = '';
            } else if( 'terms_and_conditions' == $form_field_val['field_name'] ){
                $bookingpress_field_type = 'terms_and_conditions';
            }
            $field_meta_key = $bookingpress_field_type . '_' . wp_generate_password( 6, false );

            $form_field_db_update_data = array(
                'bookingpress_field_options' => wp_json_encode( $bookingpress_field_options ),
                'bookingpress_field_type' => $bookingpress_field_type,
                'bookingpress_field_meta_key' => $field_meta_key,
            );

            $update_query = $wpdb->update(
                $tbl_bookingpress_form_fields,
                $form_field_db_update_data,
                array(
                    'bookingpress_form_field_id' => $last_inserted_field_id
                )
            );
        }
    }


    /* add username column */
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_username VARCHAR(255) DEFAULT NULL AFTER bookingpress_customer_name"); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_username VARCHAR(255) DEFAULT NULL AFTER bookingpress_customer_name"); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_username VARCHAR(255) DEFAULT NULL AFTER bookingpress_customer_name"); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_customers} ADD bookingpress_customer_full_name VARCHAR(255) DEFAULT NULL AFTER bookingpress_user_lastname"); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm

    $bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
    $bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
    $bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
    $bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
    $bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
    $bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
    $bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
    $bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');    
    $bookingpress_primary_background_color = $BookingPress->bookingpress_get_customize_settings('primary_background_color', 'booking_form');
    $bookingpress_border_color = $BookingPress->bookingpress_get_customize_settings('border_color', 'booking_form');
    $bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
    $bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
    $bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
    $bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
    $bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
    $bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
    $bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
    $bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';    
    $bookingpress_primary_background_color = !empty($bookingpress_primary_background_color) ? $bookingpress_primary_background_color : '#e2faf1';
    $bookingpress_border_color = !empty($bookingpress_border_color) ? $bookingpress_border_color : '#CFD6E5';

    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';
    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_booking_form_settings';                       
    $my_booking_form = array(
        'background_color'        => $bookingpress_background_color,
        'row_background_color'    => $bookingpress_footer_background_color,
        'border_color'            => $bookingpress_border_color,
        'primary_color'           => $bookingpress_primary_color,
        'content_color'           => $bookingpress_content_color,
        'label_title_color'       => $bookingpress_label_title_color,
        'title_font_family'       => $bookingpress_title_font_family,        
        'sub_title_color'         => $bookingpress_sub_title_color,
        'price_button_text_color' => $bookingpress_price_button_text_color,
    );
    $booking_form = array(
        'background_color'        => $bookingpress_background_color,
        'footer_background_color' => $bookingpress_footer_background_color,
        'primary_color' => $bookingpress_primary_color,
        'primary_background_color'=> $bookingpress_primary_background_color,
        'label_title_color' => $bookingpress_label_title_color,
        'title_font_family' => $bookingpress_title_font_family,                
        'content_color' => $bookingpress_content_color,                
        'price_button_text_color' => $bookingpress_price_button_text_color,
        'sub_title_color'         => $bookingpress_sub_title_color,
        'border_color'            => $bookingpress_border_color,
    );
    $bookingpress_custom_data_arr['booking_form'] = $booking_form;
    $bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;
    $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);

    

}


if( version_compare($bookingpress_old_version, '1.0.65' , '<')){


    $tbl_bookingpress_entries_meta = $wpdb->prefix . 'bookingpress_entries_meta';

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    @set_time_limit(0);
    $charset_collate = '';
    
    if ($wpdb->has_cap('collation') ) {
        if (! empty($wpdb->charset) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_entries_meta}`(
        `bookingpress_entry_meta_id` int(11) NOT NULL AUTO_INCREMENT,
        `bookingpress_entry_id` int(11) NOT NULL,
        `bookingpress_entry_meta_key` TEXT NOT NULL,
        `bookingpress_entry_meta_value` TEXT DEFAULT NULL,
        `bookingpress_entrymeta_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`bookingpress_entry_meta_id`)
    ) {$charset_collate};";
    
    dbDelta($sql_table);


}

if( version_compare($bookingpress_old_version, '1.0.65' , '<')){
    $BookingPress->bookingpress_update_settings('debug_mode', 'general_setting', false);
}

if( version_compare($bookingpress_old_version, '1.0.66' , '<')){
    $tbl_bookingpress_double_bookings = $wpdb->prefix . 'bookignpress_double_bookings';

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    @set_time_limit(0);
    $charset_collate = '';
    
    if ($wpdb->has_cap('collation') ) {
        if (! empty($wpdb->charset) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_double_bookings}`(
        `bookingpress_double_booking_id` INT(11) NOT NULL AUTO_INCREMENT,
        `bookingpress_entry_id` INT(11) NOT NULL,
        `bookingpress_double_booking_reason` LONGTEXT NOT NULL,
        `bookingpress_payment_gateway` VARCHAR(255) NOT NULL,
        `bookingpress_staffmember_id` INT(11) DEFAULT 0,
        `bookingpress_payer_email` varchar(255) DEFAULT NULL,
        `bookingpress_transaction_id` varchar(255) DEFAULT NULL,
        `bookingpress_payment_date_time` datetime DEFAULT '1970-01-01 00:00:00',
        `bookingpress_payment_status` smallint(1) DEFAULT 1,
        `bookingpress_payment_amount` FLOAT(8, 2) DEFAULT 0,
        `bookingpress_payment_currency` varchar(100) DEFAULT NULL,
        `bookingpress_payment_type` varchar(20) DEFAULT NULL,
        `bookingpress_payment_response` TEXT DEFAULT NULL,
        `bookingpress_additional_info` TEXT DEFAULT NULL,
        `bookingpress_request_raw_data` LONGTEXT NOT NULL,
        `bookingpress_created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY ( `bookingpress_double_booking_id` )
    ){$charset_collate}";
    
    dbDelta( $sql_table );
}

if( version_compare($bookingpress_old_version, '1.0.67' , '<')){    
    
    $bookingpress_default_select_all_category = $BookingPress->bookingpress_get_customize_settings('default_select_all_category','booking_form');
    if(empty($bookingpress_default_select_all_category)){
        $tbl_bookingpress_customize_settings   = $wpdb->prefix . 'bookingpress_customize_settings';
        $wpdb->insert($tbl_bookingpress_customize_settings, array('bookingpress_setting_name' => 'default_select_all_category', 'bookingpress_setting_value' => 'false', 'bookingpress_setting_type' => 'booking_form'));        
    }

    $tbl_bookingpress_double_booking = $wpdb->prefix . 'bookignpress_double_bookings';
    
    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_double_booking}` ADD `bookingpress_is_refund_supported` TINYINT NOT NULL DEFAULT '0' AFTER `bookingpress_request_raw_data`, ADD `bookingpress_refund_response` TEXT NOT NULL AFTER `bookingpress_is_refund_supported`, ADD `bookingpress_refund_reason` LONGTEXT NOT NULL AFTER `bookingpress_refund_response`, ADD `bookingpress_is_refunded` TINYINT NOT NULL DEFAULT '0' AFTER `bookingpress_refund_reason`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_double_booking is table name defined globally. False Positive alarm
}
if( version_compare( $bookingpress_old_version, '1.0.70', '<' ) ){
    update_option( 'bookingpress_display_bf_popup_after_update', 1);
    update_option( 'bpa_is_displayed_bf_sale_popup', false );
}

if( version_compare( $bookingpress_old_version, '1.0.71', '<' ) ){
    update_option( 'bookingpress_display_bf_popup_after_update', 0);
    update_option( 'bpa_is_displayed_bf_sale_popup', true );
}

if( version_compare( $bookingpress_old_version, '1.0.75', '<' ) ){
    global $tbl_bookingpress_default_daysoff;
    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_default_daysoff}` ADD `bookingpress_dayoff_enddate` DATE NULL DEFAULT NULL AFTER `bookingpress_dayoff_date`, ADD `bookingpress_dayoff_parent` int(11) NOT NULL DEFAULT 0 AFTER `bookingpress_dayoff_enddate`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False Positive alarm
}

if( version_compare( $bookingpress_old_version, '1.0.76', '<' ) ){ 
    $args  = array(
        'role'   => 'administrator',
        'fields' => 'id',
    );
    $users = get_users($args);

    if (count($users) > 0 ) {
        foreach ( $users as $key => $user_id ) {
            $userObj = new WP_User($user_id);
            $userObj->add_cap('bookingpress_growth_tools');
            
            unset($bookingpressrole);
            unset($bookingpressroles);
            unset($bookingpress_roledescription);
        }
    }

    update_option( 'bookingpress_display_bf_popup_after_update', 1);
    update_option( 'bpa_is_displayed_bf_sale_popup', false );
}

if( version_compare( $bookingpress_old_version, '1.0.82', '<') ){
    $tbl_bookingpress_transient = $wpdb->prefix . 'bookingpress_transient';

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    @set_time_limit(0);
    $charset_collate = '';
    
    if ($wpdb->has_cap('collation') ) {
        if (! empty($wpdb->charset) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_transient}` (
        `bookingpress_transient_id` bigint(11) NOT NULL AUTO_INCREMENT,
        `bookingpress_transient_key` VARCHAR(100) NOT NULL,
        `bookingpress_transient_value` LONGTEXT NOT NULL,
        `bookingpress_transient_expiry` VARCHAR(50) NULL DEFAULT NULL,
        `bookingpress_transient_created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`bookingpress_transient_id`)
    ) {$charset_collate}";
    
    dbDelta( $sql_table );

    $check_db_permission = $BookingPress->bookingpress_check_db_permission();

    if( 1 == $check_db_permission ){
        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_transient}` ADD INDEX `bookingpress_transient_id_key_index` (`bookingpress_transient_id`,`bookingpress_transient_key`)"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_transient is table name defined globally. False Positive alarm
    }
}

if( version_compare( $bookingpress_old_version, '1.1', '<') ){

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    @set_time_limit(0);
    $charset_collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }
    
    $tbl_bookingpress_export_data_log = $wpdb->prefix.'bookingpress_export_data_log';
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_export_data_log}`(
        `export_id` int(11) NOT NULL AUTO_INCREMENT,					
        `export_data` TEXT DEFAULT NULL,
        `export_complete` int(11) DEFAULT 0,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`export_id`)
    ) {$charset_collate}";
    dbDelta($sql_table);

    $tbl_bookingpress_export_data_log_detail = $wpdb->prefix.'bookingpress_export_log_detail';
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_export_data_log_detail}`(
        `export_detail_id` int(11) NOT NULL AUTO_INCREMENT,
        `export_id` int(11) NOT NULL,
        `export_detail_type` varchar(255) NOT NULL,
        `export_detail_total_record` int(11) NOT NULL DEFAULT 0,
        `export_detail_last_record` int(11) NOT NULL DEFAULT 0,
        `export_detail_complete` int(11) DEFAULT 0,
        `export_detail_option` varchar(255) DEFAULT NULL,
        `export_detail_record_hide` int(11) DEFAULT 0,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`export_detail_id`)
    ) {$charset_collate}";
    dbDelta($sql_table);


    $tbl_bookingpress_import_data_log = $wpdb->prefix.'bookingpress_import_data_log';
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_import_data_log}`(
        `import_id` int(11) NOT NULL AUTO_INCREMENT,					
        `import_data` TEXT DEFAULT NULL,
        `import_complete` int(11) DEFAULT 0,
        `export_key` varchar(255) DEFAULT NULL,
        `export_site_key` varchar(255) DEFAULT NULL,
        `site_url` varchar(255) DEFAULT NULL,
        `import_stop_reason` text DEFAULT NULL,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`import_id`)
    ) {$charset_collate}";
    dbDelta($sql_table);

    $tbl_bookingpress_import_detail_log = $wpdb->prefix.'bookingpress_import_detail_log';
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_import_detail_log}`(
        `detail_import_id` int(11) NOT NULL AUTO_INCREMENT,					
        `import_id` int(11) NOT NULL,
        `detail_import_detail_type` varchar(255) NOT NULL,
        `detail_import_complete` int(11) DEFAULT 0,
        `detail_import_total_record` int(11) NOT NULL DEFAULT 0,
        `detail_import_last_record` int(11) NOT NULL DEFAULT 0,
        `detail_import_display` int(1) NOT NULL DEFAULT 0,
        `export_site_key` varchar(255) DEFAULT NULL,
        `export_key` varchar(255) DEFAULT NULL,
        `detail_import_stop_reason` text DEFAULT NULL,
        `total_import_record` int(11) NOT NULL DEFAULT 0,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`detail_import_id`)
    ) {$charset_collate}";
    dbDelta($sql_table); 
    
    $tbl_bookingpress_import_record_rel = $wpdb->prefix.'bookingpress_import_record_rel';
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_import_record_rel}`(
        `record_rel_id` int(11) NOT NULL AUTO_INCREMENT,					
        `import_id` int(11) NOT NULL,
        `record_rel_type` varchar(255) NOT NULL,
        `record_old_id` int(11) DEFAULT 0,
        `record_new_id` int(11) DEFAULT 0,                    
        `record_rel_lang_type` varchar(255) DEFAULT NULL,
        `export_key` varchar(255) DEFAULT NULL,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`record_rel_id`)
    ) {$charset_collate}";
    dbDelta($sql_table);                 

    $tbl_bookingpress_import_images = $wpdb->prefix.'bookingpress_import_images';
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_import_images}`(
        `import_image_id` int(11) NOT NULL AUTO_INCREMENT,					
        `import_id` int(11) NOT NULL,
        `import_table` varchar(255) NOT NULL,
        `import_field` varchar(255) DEFAULT NULL,
        `import_table_id` int(11) DEFAULT 0,
        `import_image_name` varchar(255) DEFAULT NULL,
        `import_image_url` varchar(255) DEFAULT NULL,
        `import_image_complete` int(1) DEFAULT 0,
        `export_key` varchar(255) DEFAULT NULL,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`import_image_id`)
    ) {$charset_collate}";
    dbDelta($sql_table);    


    $args  = array(
        'role'   => 'administrator',
        'fields' => 'id',
    );
    $users = get_users($args);

    if (count($users) > 0 ) {
        foreach ( $users as $key => $user_id ) {
            $bookingpressroles = $BookingPress->bookingpress_capabilities();
            $userObj           = new WP_User($user_id);
            foreach ( $bookingpressroles as $bookingpressrole => $bookingpress_roledescription ) {
                $userObj->add_cap($bookingpressrole);
            }
            unset($bookingpressrole);
            unset($bookingpressroles);
            unset($bookingpress_roledescription);
        }
    }


}

if( version_compare( $bookingpress_old_version, '1.1.1', '<') ){
    global $BookingPress;

	$BookingPress->bookingpress_update_settings( 'bpa_afternoon_start_time', 'general_setting', '12:00:00' );
	$BookingPress->bookingpress_update_settings( 'bpa_evening_start_time', 'general_setting', '16:00:00' );
	$BookingPress->bookingpress_update_settings( 'bpa_night_start_time', 'general_setting', '20:00:00' );
}

if( version_compare( $bookingpress_old_version, '1.1.2', '<') ){
    $paypal_payment_method_type = $BookingPress->bookingpress_get_settings('paypal_payment_method_type', 'payment_setting');
    if(empty($paypal_payment_method_type)){
        $BookingPress->bookingpress_update_settings('paypal_payment_method_type', 'payment_setting', 'lagacy');
    }
}

if( version_compare( $bookingpress_old_version, '1.1.6', '<' ) ){
    global $BookingPress;
    $BookingPress->bookingpress_update_settings( 'allow_autologin_user', 'payment_setting', 'true' );
}

if( version_compare( $bookingpress_old_version, '1.1.11', '<' ) ){
    global $wpdb;

    $fetch_records = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE '%\"widgetType\":\"Booking Forms - WordPress Booking Plugin\"%' OR meta_value LIKE '%\"widgetType\":\"Customer Panel - BookingPress Appointment Plugin\"%'", '_elementor_data' ) ); //phpcs:ignore

    if( $fetch_records > 0 ){
        update_option( 'bookingpress_update_elementor_widget', '1' );
    }
}

if( version_compare( $bookingpress_old_version, '1.1.15', '<') ){
    global $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries;

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_selected_appointment_date DATE AFTER bookingpress_appointment_timezone" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_selected_appointment_end_date DATE AFTER bookingpress_selected_appointment_date" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_selected_appointment_time TIME AFTER bookingpress_selected_appointment_end_date" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_selected_appointment_end_time TIME AFTER bookingpress_selected_appointment_time" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm


    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_selected_appointment_date DATE AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_selected_appointment_end_date DATE AFTER bookingpress_selected_appointment_date" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_selected_appointment_time TIME AFTER bookingpress_selected_appointment_end_date" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_selected_appointment_end_time TIME AFTER bookingpress_selected_appointment_time" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
}

if( version_compare( $bookingpress_old_version, '1.1.22', '<') ){
    global $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $tbl_bookingpress_payment_logs;
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_appointment_end_date DATE AFTER bookingpress_appointment_date"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_appointment_end_date DATE AFTER bookingpress_appointment_date"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_appointment_end_date DATE AFTER bookingpress_appointment_date"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
}

if( version_compare( $bookingpress_old_version, '1.1.23', '<') ){
    global $wpdb, $bookingpress_import_export;

    $bookingpress_import_export->bookingpress_remove_import_export_directory();
    $bookingpress_import_export->bookingpress_remove_import_export_directory('import');

    $upload_dir = wp_upload_dir();
    $exported_dir_path = $upload_dir['basedir'] . '/bookingpress_export_records';
    $imported_dir_path = $upload_dir['basedir'] . '/bookingpress_import_records';

    $file_content = "<?php\n //silence is golden";
    $file_name = 'index.php';

    file_put_contents( $exported_dir_path.'/'.$file_name, $file_content );
    file_put_contents( $imported_dir_path.'/'.$file_name, $file_content );

}

$BookingPress->bookingpress_cleanup_transient_data_hook_callback();
$bookingpress_new_version = '1.1.51';
update_option('bookingpress_new_version_installed', 1);
update_option('bookingpress_version', $bookingpress_new_version);
update_option('bookingpress_updated_date_' . $bookingpress_new_version, current_time('mysql'));

<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div id="settings_root_app" class="bookingpress-settings bookingpress_page_wrapper">
    <?php
        if( class_exists( '\BookingPressPro\admin\Dashboard') && method_exists( '\BookingPressPro\admin\Dashboard', 'render_dashboard_header' ) ){
            \BookingPressPro\admin\Dashboard::render_dashboard_header();
            $bookingpress_notification_settigs_allowed = 0;
            $bookingpress_workhours_settigs_allowed = 0;
            $bookingpress_daysoff_settigs_allowed = 0;
            $bookingpress_payment_settings_allowed = 0;
            $bookingpress_messages_settings_allowed = 0;
            $bookingpress_debug_log_settings_allowed =0;
            if( current_user_can( 'bookingpress_notification_settigs' ) ){
                $bookingpress_notification_settigs_allowed = 1;
            }
            if( current_user_can('bookingpress_workhours_settigs') ){
                $bookingpress_workhours_settigs_allowed = 1;
            }
            if( current_user_can('bookingpress_daysoff_settigs') ){
                $bookingpress_daysoff_settigs_allowed = 1;
            }
            if ( current_user_can( 'bookingpress_payment_settings' ) ) {
                $bookingpress_payment_settings_allowed = 1;
            }
            if( current_user_can( 'bookingpress_messages_settings') ){
                $bookingpress_messages_settings_allowed =1;
            }
            if( current_user_can( 'bookingpress_debug_log_settings') ){
                $bookingpress_debug_log_settings_allowed =1;
            }
        } else {
            $bookingpress_notification_settigs_allowed = 1;
            $bookingpress_workhours_settigs_allowed = 1;
            $bookingpress_daysoff_settigs_allowed = 1;
            $bookingpress_payment_settings_allowed = 1;
            $bookingpress_messages_settings_allowed =1;
            $bookingpress_debug_log_settings_allowed =1;
            require_once __DIR__ . '/components/Header.php';    
        }
    ?>

    <div class="settings-app-root bookingpress_page_inner_wrapper" v-cloak id="settings-app-root">
        <bp-ui-main class="bpa-main-listing-card-container bpa-general-settings--main-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
            <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                <div class="bpa-back-loader"></div>
            </div>
            <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
                <div class="bpa-back-loader"></div>
            </div>

            <div id="bpa-main-container">
                <bp-ui-tab ref="bookingpress_setting_tabs" type="card" v-model="selected_tab_name" tab-position="left" class="bpa-tabs bpa-tabs--vertical__left-side" @tab-click="settings_tab_select($event)">
                    <?php
                        require __DIR__ . '/components/settings/general_setting_tab.php';
                        require __DIR__ . '/components/settings/company_setting_tab.php';

                        if( $bookingpress_notification_settigs_allowed == 1 ){
                            require __DIR__ . '/components/settings/notification_setting_tab.php';
                        }

                        if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'getCustomerTab' ) ) {
                            \BookingPressPro\admin\Settings::getCustomerTab();
                        }

                        if( $bookingpress_workhours_settigs_allowed == 1){
                            // Pro extends the general working hours to a 24-hour (up-to-next-day)
                            // window; when active it renders its own tab bound to the extended
                            // time list. Lite falls back to the standard (midnight-capped) tab.
                            if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'getWorkHoursTab' ) ) {
                                \BookingPressPro\admin\Settings::getWorkHoursTab();
                            } else {
                                require __DIR__ . '/components/settings/workhours_setting_tab.php';
                            }
                        }
                        if( $bookingpress_daysoff_settigs_allowed == 1 ){
                            require __DIR__ . '/components/settings/daysoff_setting_tab.php'; 
                        }
                        
                        if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'getSpecialDayTab' ) ) {
                            \BookingPressPro\admin\Settings::getSpecialDayTab();
                        }

                        if ( $bookingpress_payment_settings_allowed == 1 ) {
                            require __DIR__ . '/components/settings/payment_setting_tab.php';
                        }

                        if( $bookingpress_messages_settings_allowed == 1 ){
                            require __DIR__ . '/components/settings/messages_setting_tab.php';
                        }

                        if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'getLicenseTab' ) ) {
                            \BookingPressPro\admin\Settings::getLicenseTab();
                        }

                        if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'getOptinsTab' ) ) {
                            \BookingPressPro\admin\Settings::getOptinsTab();
                        }

                        if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'getIntegeraionTab' ) ) {
                            \BookingPressPro\admin\Settings::getIntegeraionTab();
                        }

                        if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'getStaffMemberTab' ) ) {
                            \BookingPressPro\admin\Settings::getStaffMemberTab();
                        }

                        if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'getSettingsOutsideTab' ) ) {
                            \BookingPressPro\admin\Settings::getSettingsOutsideTab();
                        }

                        if( $bookingpress_debug_log_settings_allowed == 1 ){
                            require __DIR__ . '/components/settings/debug_log_settings.php';
                        }

                        require __DIR__ . '/components/settings/import_export_tab.php';                        

                        $bookingpress_file_url = array();
                        $bookingpress_file_url = apply_filters( 'bookingpress_lite_general_settings_add_tab_filter', $bookingpress_file_url );
                        if ( ! empty( $bookingpress_file_url ) && is_array( $bookingpress_file_url ) ) {
                            foreach ( $bookingpress_file_url as $bookingpress_file_key => $bookingpress_file_url_val ) {
                                if ( ! empty( $bookingpress_file_url_val ) ) {
                                    require $bookingpress_file_url_val;
                                }
                            }
                        }
                    ?>
                </bp-ui-tab>
            </div>

        </bp-ui-main>
        <?php do_action('bookingpress_setting_view_data_after'); ?>
    </div>
</div>
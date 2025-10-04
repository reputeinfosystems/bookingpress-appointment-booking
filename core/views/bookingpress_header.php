<?php
    if ( ! defined( 'ABSPATH' ) ) { exit; }
    global $bookingpress_slugs, $BookingPress;
    $request_module = ( ! empty($_REQUEST['page']) && ( $_REQUEST['page'] != 'bookingpress' ) ) ? sanitize_text_field(str_replace('bookingpress_', '', $_REQUEST['page'])) : 'dashboard'; //// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['page'] sanitized properly
    $request_action = ( ! empty($_REQUEST['action']) ) ? sanitize_text_field($_REQUEST['action']) : 'forms'; //// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['action'] sanitized properly

if(!empty($request_module) && $request_module != 'lite_wizard'){
?>
<!-- 
    *** AFTER MERGE SHOULD BE TESTED IN LITE NEW & PRO OLD VERSION *** 
    ** Note for new header changes in mobile view **
    1. Task id(#85zt9z8wm) & url(https://app.clickup.com/t/85zt9z8wm)
    2. class "bpa-header-navbar--v2" is to support v1.0.64 new design.
    3. After few release developer need to merge new class css in orginal class "bpa-header-navbar".
    4. Remove unused CSS from orginal class while mergging new & old CSS.
    5. Move New CSS from bookingpress_admin.css to bookingpress_admin_components.css 
-->
<nav class="bpa-header-navbar bpa-header-navbar--v2">
    <div class="bpa-header-navbar-wrap">
        <?php
        if (current_user_can('bookingpress') ) {
            ?>
        <div class="bpa-navbar-brand">
            <a href="<?php echo esc_url(admin_url() . 'admin.php?page=bookingpress'); ?>" class="navbar-logo">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="64" height="64" rx="12"/>
                    <path d="M50 18.9608V47.2745C50 49.3359 48.325 51 46.25 51H17.75C15.675 51 14 49.3359 14 47.2745V18.9608C14 16.8993 15.675 15.2353 17.75 15.2353H23V14.1176C23 13.7451 23.375 13 24.125 13C24.875 13 25.25 13.7451 25.25 14.1176V18.5882C25.25 18.9608 24.875 19.7059 24.125 19.7059C23.375 19.7059 23 18.9608 23 18.5882V17.4706H18.5C17.25 17.4706 16.25 18.4641 16.25 19.7059V46.5294C16.25 47.7712 17.25 48.7647 18.5 48.7647H45.5C46.75 48.7647 47.75 47.7712 47.75 46.5294V19.7059C47.75 18.4641 46.75 17.4706 45.5 17.4706H41C41 17.4706 41 18.0418 41 18.5882C41 18.9608 40.625 19.7059 39.875 19.7059C39.125 19.7059 38.75 18.9608 38.75 18.5882V17.4706H33.125C32.5 17.4706 32 16.9739 32 16.3529C32 15.732 32.5 15.2353 33.125 15.2353H38.75V14.1176C38.75 13.7451 39.125 13 39.875 13C40.625 13 41 13.7451 41 14.1176V15.2353H46.25C48.325 15.2353 50 16.8993 50 18.9608Z" fill="white"/>
                    <path d="M37.2501 30.8823C37.2501 30.8823 38.0001 30.1372 38.0001 27.9019C38.0001 24.1765 35.7501 23.4314 32.7501 23.4314H26.0001V39.0784H30.5001V43.549H32.7501V39.0784C35.3501 39.0784 37.1751 39.0784 38.5251 37.4144C39.1751 36.6196 39.5001 35.6013 39.5001 34.5582C39.5001 34.0118 39.4251 33.4654 39.3001 33.1176C38.9751 32.2732 38.7501 31.6274 37.2501 30.8823ZM35.0001 36.8431C34.2501 36.8431 32.7501 36.8431 32.7501 36.8431C32.7501 36.8431 32.7501 36.098 32.7501 34.6078C32.7501 33.366 33.7501 32.3725 35.0001 32.3725C36.2001 32.3725 37.2501 33.3412 37.2501 34.6078C37.2501 35.9242 36.1501 36.8431 35.0001 36.8431ZM33.1751 30.6836C32.8001 30.8575 32.4251 31.081 32.0751 31.3294C31.2501 31.9503 30.5001 32.8444 30.5001 34.6078V36.8431H28.2501V25.6667H32.7501C34.5501 25.6667 35.7501 26.4118 35.7501 27.9019C35.7501 29.268 34.7251 29.9137 33.1751 30.6836Z" fill="white"/>
                </svg>
            </a>
        </div>
        <?php } ?>
        <div class="bpa-navbar-nav" id="bpa-navbar-nav">
            <div class="bpa-menu-toggle" id="bpa-mobile-menu">
                <span class="bpa-mm-bar"></span>
                <span class="bpa-mm-bar"></span>
                <span class="bpa-mm-bar"></span>
            </div>
            <ul>
                <?php if (current_user_can('bookingpress_calendar') ) { ?>
                <li class="bpa-nav-item <?php echo ( 'calendar' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_calendar), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 3h-1V2c0-.55-.45-1-1-1s-1 .45-1 1v1H7V2c0-.55-.45-1-1-1s-1 .45-1 1v1H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 18H5c-.55 0-1-.45-1-1V8h16v12c0 .55-.45 1-1 1z"/></svg>
                        </div>
                        <?php esc_html_e('Calendar', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_appointments') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'appointments' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_appointments), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">                        
                        <div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16 12h-3c-.55 0-1 .45-1 1v3c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-3c0-.55-.45-1-1-1zm0-10v1H8V2c0-.55-.45-1-1-1s-1 .45-1 1v1H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm2 17H6c-.55 0-1-.45-1-1V8h14v10c0 .55-.45 1-1 1z"/></svg>
                        </div>
                        <?php esc_html_e('Appointments', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_payments') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'payments' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_payments), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09v.58c0 .73-.6 1.33-1.33 1.33h-.01c-.73 0-1.33-.6-1.33-1.33v-.6c-1.33-.28-2.51-1.01-3.01-2.24-.23-.55.2-1.16.8-1.16h.24c.37 0 .67.25.81.6.29.75 1.05 1.27 2.51 1.27 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21v-.6c0-.73.6-1.33 1.33-1.33h.01c.73 0 1.33.6 1.33 1.33v.62c1.38.34 2.25 1.2 2.63 2.26.2.55-.22 1.13-.81 1.13h-.26c-.37 0-.67-.26-.77-.62-.23-.76-.86-1.25-2.12-1.25-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.02 1.83-1.39 2.83-3.13 3.16z"/></svg>
                        </div>
                        <?php esc_html_e('Payments', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_customers') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'customers' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_customers), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16.5 12c1.38 0 2.49-1.12 2.49-2.5S17.88 7 16.5 7 14 8.12 14 9.5s1.12 2.5 2.5 2.5zM9 11c1.66 0 2.99-1.34 2.99-3S10.66 5 9 5 6 6.34 6 8s1.34 3 3 3zm7.5 3c-1.83 0-5.5.92-5.5 2.75V18c0 .55.45 1 1 1h9c.55 0 1-.45 1-1v-1.25c0-1.83-3.67-2.75-5.5-2.75zM9 13c-2.33 0-7 1.17-7 3.5V18c0 .55.45 1 1 1h6v-2.25c0-.85.33-2.34 2.37-3.47C10.5 13.1 9.66 13 9 13z"/></svg>
                        </div>
                        <?php esc_html_e('Customers', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_services') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'services' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_services), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M14 9.5h3c.55 0 1-.45 1-1s-.45-1-1-1h-3c-.55 0-1 .45-1 1s.45 1 1 1zm0 7h3c.55 0 1-.45 1-1s-.45-1-1-1h-3c-.55 0-1 .45-1 1s.45 1 1 1zm5 4.5H5c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2zM7 11h3c.55 0 1-.45 1-1V7c0-.55-.45-1-1-1H7c-.55 0-1 .45-1 1v3c0 .55.45 1 1 1zm0-4h3v3H7V7zm0 11h3c.55 0 1-.45 1-1v-3c0-.55-.45-1-1-1H7c-.55 0-1 .45-1 1v3c0 .55.45 1 1 1zm0-4h3v3H7v-3z"/></svg>
                        </div>
                        <?php esc_html_e('Services', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_notifications') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'notifications' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_notifications), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><g><rect fill="none" height="24" width="24" x="0"/><path d="M19,10c1.13,0,2.16-0.39,3-1.02V18c0,1.1-0.9,2-2,2H4c-1.1,0-2-0.9-2-2V6c0-1.1,0.9-2,2-2h10.1C14.04,4.32,14,4.66,14,5 c0,1.48,0.65,2.79,1.67,3.71L12,11L5.3,6.81C4.73,6.46,4,6.86,4,7.53c0,0.29,0.15,0.56,0.4,0.72l7.07,4.42 c0.32,0.2,0.74,0.2,1.06,0l4.77-2.98C17.84,9.88,18.4,10,19,10z M16,5c0,1.66,1.34,3,3,3s3-1.34,3-3s-1.34-3-3-3S16,3.34,16,5z"/></g></svg>
                        </div>                        
                        <?php esc_html_e('Notifications', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_customize') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'customize' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>                                        
                    <el-dropdown class="bpa-nav-item-dropdown" trigger="hover">                        
                        <a href="#" class="bpa-nav-link">
                            <div class="bpa-nav-link--icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                            </div>
                            <?php esc_html_e('Customize', 'bookingpress-appointment-booking'); ?>
                        </a>
                        <el-dropdown-menu slot="dropdown" class="bpa-ni-dropdown-menu" v-cloak>                           
                            <el-dropdown-item class="bpa-ni-dropdown-menu--item <?php echo ( 'forms' == $request_action ) ? '__active' : ''; ?>">
                                <a href="<?php echo add_query_arg( array( 'page'=> $bookingpress_slugs->bookingpress_customize,'action' => 'forms'), esc_url( admin_url() . 'admin.php?page=bookingpress' ) );  // phpcs:ignore ?>" class="bpa-dm--item-link">
                                    <span>
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14.6667 0.666992C15.1267 0.666992 15.5 1.04033 15.5 1.50033V4.63116L8.00083 12.1312L7.99583 15.6628L11.5342 15.6678L15.5 11.702V16.5003C15.5 16.9603 15.1267 17.3337 14.6667 17.3337H1.33333C0.873333 17.3337 0.5 16.9603 0.5 16.5003V1.50033C0.5 1.04033 0.873333 0.666992 1.33333 0.666992H14.6667ZM16.1483 6.34033L17.3267 7.51866L10.845 14.0003L9.665 13.9987L9.66667 12.822L16.1483 6.34033ZM8 9.00033H3.83333V10.667H8V9.00033ZM10.5 5.66699H3.83333V7.33366H10.5V5.66699Z" />
                                        </svg>
                                    </span>    
                                    <?php esc_html_e( 'Forms', 'bookingpress-appointment-booking' ); ?>
                                </a>
                            </el-dropdown-item>                           
                            <el-dropdown-item class="bpa-ni-dropdown-menu--item  <?php echo ( 'form_fields' == $request_action ) ? '__active' : ''; ?>">
                                <a href="<?php echo add_query_arg( array( 'page'=> $bookingpress_slugs->bookingpress_customize,'action' => 'form_fields'), esc_url( admin_url() . 'admin.php?page=bookingpress' ) );  // phpcs:ignore ?>" class="bpa-dm--item-link">
                                    <span>
                                        <svg width="19" height="18" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.3334 9.83366L18.1367 13.2187L15.6592 13.927L17.4301 16.9945L15.9867 17.8278L14.2159 14.7612L12.3634 16.5528L12.3334 9.83366ZM10.6667 4.00033H12.3334V5.66699H16.5001C16.7211 5.66699 16.9331 5.75479 17.0893 5.91107C17.2456 6.06735 17.3334 6.27931 17.3334 6.50033V9.83366H15.6667V7.33366H7.33342V15.667H10.6667V17.3337H6.50008C6.27907 17.3337 6.06711 17.2459 5.91083 17.0896C5.75455 16.9333 5.66675 16.7213 5.66675 16.5003V12.3337H4.00008V10.667H5.66675V6.50033C5.66675 6.27931 5.75455 6.06735 5.91083 5.91107C6.06711 5.75479 6.27907 5.66699 6.50008 5.66699H10.6667V4.00033ZM2.33341 10.667V12.3337H0.666748V10.667H2.33341ZM2.33341 7.33366V9.00033H0.666748V7.33366H2.33341ZM2.33341 4.00033V5.66699H0.666748V4.00033H2.33341ZM2.33341 0.666992V2.33366H0.666748V0.666992H2.33341ZM5.66675 0.666992V2.33366H4.00008V0.666992H5.66675ZM9.00008 0.666992V2.33366H7.33342V0.666992H9.00008ZM12.3334 0.666992V2.33366H10.6667V0.666992H12.3334Z" />
                                        </svg>
                                    </span>
                                    <?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?>
                                </a>
                            </el-dropdown-item>                            
                    </el-dropdown>
                </li>
                    <?php
                }
                if (current_user_can('bookingpress_settings') ) {
                    ?>
                <li class="bpa-nav-item <?php echo ( 'settings' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                    <a href="<?php echo add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_settings), esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                        <div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><rect fill="none" height="24" width="24"/><path d="M19.5,12c0-0.23-0.01-0.45-0.03-0.68l1.86-1.41c0.4-0.3,0.51-0.86,0.26-1.3l-1.87-3.23c-0.25-0.44-0.79-0.62-1.25-0.42 l-2.15,0.91c-0.37-0.26-0.76-0.49-1.17-0.68l-0.29-2.31C14.8,2.38,14.37,2,13.87,2h-3.73C9.63,2,9.2,2.38,9.14,2.88L8.85,5.19 c-0.41,0.19-0.8,0.42-1.17,0.68L5.53,4.96c-0.46-0.2-1-0.02-1.25,0.42L2.41,8.62c-0.25,0.44-0.14,0.99,0.26,1.3l1.86,1.41 C4.51,11.55,4.5,11.77,4.5,12s0.01,0.45,0.03,0.68l-1.86,1.41c-0.4,0.3-0.51,0.86-0.26,1.3l1.87,3.23c0.25,0.44,0.79,0.62,1.25,0.42 l2.15-0.91c0.37,0.26,0.76,0.49,1.17,0.68l0.29,2.31C9.2,21.62,9.63,22,10.13,22h3.73c0.5,0,0.93-0.38,0.99-0.88l0.29-2.31 c0.41-0.19,0.8-0.42,1.17-0.68l2.15,0.91c0.46,0.2,1,0.02,1.25-0.42l1.87-3.23c0.25-0.44,0.14-0.99-0.26-1.3l-1.86-1.41 C19.49,12.45,19.5,12.23,19.5,12z M12.04,15.5c-1.93,0-3.5-1.57-3.5-3.5s1.57-3.5,3.5-3.5s3.5,1.57,3.5,3.5S13.97,15.5,12.04,15.5z"/></svg>
                        </div>
                        <?php esc_html_e('Settings', 'bookingpress-appointment-booking'); ?>
                    </a>
                </li>
                <?php } ?>
                <li class="bpa-nav-item bpa-nav-item__is-go-premium">					
                    <a href="javascript:void(0)" class="bpa-nav-link" @click="open_premium_modal">
                        <div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/><rect fill="none" height="24" width="24"/></g><g><g><g><polygon points="12.16,3 11.84,3 9.21,8.25 14.79,8.25"/></g><g><path d="M16.46,8.25h5.16l-2.07-4.14C19.21,3.43,18.52,3,17.76,3h-3.93L16.46,8.25z"/></g><g><polygon points="21.38,9.75 12.75,9.75 12.75,20.1"/></g><g><polygon points="11.25,20.1 11.25,9.75 2.62,9.75"/></g><g><path d="M7.54,8.25L10.16,3H6.24C5.48,3,4.79,3.43,4.45,4.11L2.38,8.25H7.54z"/></g></g></g></svg>
                        </div>
                        <?php
                            $bpa_current_date_for_bf_popup = current_time('timestamp',true); //GMT/ UTC+00 timeszone
                            $bpa_sale_popup_details = $BookingPress->bookingpress_get_sales_data();

                            $current_year = gmdate('Y', current_time('timestamp', true ) );

                            if( !empty( $bpa_sale_popup_details[ $current_year ] ) ){
                                $sale_details = $bpa_sale_popup_details[ $current_year ];
                                
                                $bpa_bf_popup_start_time = $sale_details['start_time'];
                                $bpa_bf_popup_end_time = $sale_details['end_time'];

                                $type = !empty( $sale_details['type'] ) ? $sale_details['type'] : 'black_friday';

                                if( $bpa_current_date_for_bf_popup >= $bpa_bf_popup_start_time && $bpa_current_date_for_bf_popup <= $bpa_bf_popup_end_time ){
                                    if( 'birthday_sale' == $type ){
                                        esc_html_e('Birthday Sale', 'bookingpress-appointment-booking');
                                    } else if( 'black_friday' == $type ){
                                        esc_html_e('Black Friday Sale', 'bookingpress-appointment-booking');
                                    } else if ( 'spring_sale' == $type ){
                                        esc_html_e( 'Spring Sale', 'bookingpress-appointment-booking' );
                                    } else {
                                        esc_html_e('Go Premium', 'bookingpress-appointment-booking');
                                    }
                                } else {
                                    esc_html_e('Go Premium', 'bookingpress-appointment-booking');
                                }
                            } else {
                                esc_html_e('Go Premium', 'bookingpress-appointment-booking');
                            }
                        ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="bpa-mob-nav-overlay" id="bpa-mob-nav-overlay"></div>
<?php } ?>

<el-dialog custom-class="bpa-dialog bpa-dialog--upgrade-to-premium bpa-dialog--black-friday-sale" modal-append-to-body=false :visible.sync="premium_modal" :close-on-press-escape="close_modal_on_esc" v-cloak>
<div class="bpa-dialog-heading" @click="premium_modal = false;"></div>
    <div class="bpa-dialog-body" @click="bookingpress_redirect_sale_premium_page">
        <el-button class="bpa-btn bpa-btn--black-friday-sale-body">Grab Deal Now</el-button>
    </div>
</el-dialog>

<el-dialog custom-class="bpa-dialog bpa-dialog--upgrade-to-premium" modal-append-to-body=false :visible.sync="bookingpress_old_premium_modal" :close-on-press-escape="close_modal_on_esc" v-cloak>
    <div class="bpa-dialog-heading">
        <div class="bpa-dialog-utp__head-wrap">
            <h3><?php esc_html_e('Unlock the Powerful Pro Features', 'bookingpress-appointment-booking'); ?></h3>
        </div>
    </div>
    <div class="bpa-dialog-body">
        <div class="bpa-utp__body-item-wrap">
            <h4><?php esc_html_e('Scale your appointment scheduling business', 'bookingpress-appointment-booking'); ?></h4>
            <p><?php esc_html_e('Simplify the booking experiences for your customers, automate employee', 'bookingpress-appointment-booking'); ?></p>
            <p style="line-height: 0 !important; margin-top: -20px !important;"><?php esc_html_e('management, and grow your business with even more features.', 'bookingpress-appointment-booking'); ?></p>
            <div class="bpa-utp__key-features">
                <h5><?php esc_html_e('Amazing Features', 'bookingpress-appointment-booking'); ?></h5>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Award Winning Design', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('60+ Premium add-ons totally free', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('20+ Payment gateways completely free', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Effective employee scheduling', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Smooth two-way sync of bookings across calendars', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Understand your business better with reports', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Email, Whatsapp, SMS & Telegram Notification', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('24/7 Real-time Support', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
            </div> 
            <div class="bpa-utp-comparison-btns">
                <h4><?php esc_html_e('Check out our complete comparison', 'bookingpress-appointment-booking'); ?></h4>
                <div class="boa-cb__wrap">
                    <el-link href="https://www.bookingpressplugin.com/bookingpress-lite-vs-premium" :underline="false" target="_blank" class="bpa-btn bpa-btn__medium bpa_lite_pro_link"><?php esc_html_e('Lite vs Premium', 'bookingpress-appointment-booking'); ?></el-link>
                </div>
            </div>
        </div>
    </div>
    <div class="bpa-dialog-footer">
        <el-button class="bpa-btn bpa-btn--primary" @click="bookingpress_redirect_premium_page">
            <?php esc_html_e('Upgrade to BookingPress Pro Now', 'bookingpress-appointment-booking'); ?>
        </el-button>
    </div>
</el-dialog>
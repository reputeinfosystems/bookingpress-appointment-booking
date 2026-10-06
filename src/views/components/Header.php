<?php
    if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    global $bookingpress_slugs, $BookingPress;
    $request_module = ( ! empty($_REQUEST['page']) && ( $_REQUEST['page'] != 'bookingpress' ) ) ? sanitize_text_field(str_replace('bookingpress_', '', $_REQUEST['page'])) : 'dashboard'; //// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['page'] sanitized properly
    $request_action = ( ! empty($_REQUEST['action']) ) ? sanitize_text_field($_REQUEST['action']) : 'forms'; //// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['action'] sanitized properly

    use BookingPress\admin\Header;
?>
<div id="bookingpress_header_wrapper">
<?php  
    if( 'lite_wizard' != $request_module ){
?>
    <nav class="bpa-header-navbar bpa-header-navbar--v2">
        <div class="bpa-header-navbar-wrap">
            <?php if (Header::bookingpress_verify_capabilities('bookingpress') ) { ?>
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
            <div class="bpa-navbar-nav" id="bpa-navbar-nav" @click="bpa_mobile_toggle_menu" :class="{'bpa-mobile-nav': toggle_drawer}">
                <div class="bpa-menu-toggle" id="bpa-mobile-menu" :class="{'is-active': toggle_drawer}">
                    <span class="bpa-mm-bar"></span>
                    <span class="bpa-mm-bar"></span>
                    <span class="bpa-mm-bar"></span>
                </div>
                <ul>
                    <?php if (Header::bookingpress_verify_capabilities('bookingpress_calendar') ) { ?>
                    <li class="bpa-nav-item <?php echo ( 'calendar' == $request_module ) ? '__active' : ''; ?>">
                        <?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
                        <a href="<?php echo add_query_arg('page', 'bookingpress-calendar', esc_url(admin_url() . 'admin.php?page=bookingpress')); ?>" class="bpa-nav-link">
                            <div class="bpa-nav-link--icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 3h-1V2c0-.55-.45-1-1-1s-1 .45-1 1v1H7V2c0-.55-.45-1-1-1s-1 .45-1 1v1H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 18H5c-.55 0-1-.45-1-1V8h16v12c0 .55-.45 1-1 1z"/></svg>
                            </div>
                            <?php esc_html_e('Calendar', 'bookingpress-appointment-booking'); ?>
                        </a>
                    </li>
                        <?php
                    }
                    if (Header::bookingpress_verify_capabilities('bookingpress_appointments') ) {
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
                    if (Header::bookingpress_verify_capabilities('bookingpress_payments') ) {
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
                    if (Header::bookingpress_verify_capabilities('bookingpress_customers') ) {
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
                   
                    if (Header::bookingpress_verify_capabilities('bookingpress_services') ) {
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
                     /** Additional Header */

                    if( class_exists( '\BookingPressPro\admin\Header') && method_exists( '\BookingPressPro\admin\Header', 'render_header_components' ) ){
                        \BookingPressPro\admin\Header::render_header_components( $request_module, $request_action );
                    }

                    if (!class_exists( '\BookingPressPro\admin\Header') && Header::bookingpress_verify_capabilities('bookingpress_notifications') ) {
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
                    if ( Header::bookingpress_verify_capabilities('bookingpress_customize') ) {
                        ?>
                        <li class="bpa-nav-item <?php echo ( 'customize' == $request_module ) ? '__active' : ''; ?>">
                            <?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>                                        
                            <bp-ui-dropdown class="bpa-nav-item-dropdown" trigger="hover">                        
                                <a href="#" class="bpa-nav-link">
                                    <div class="bpa-nav-link--icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                                    </div>
                                    <?php esc_html_e('Customize', 'bookingpress-appointment-booking'); ?>
                                </a>
                                <template #dropdown>
                                    <bp-ui-dropdown-menu slot="dropdown" class="bpa-ni-dropdown-menu" v-cloak>                           
                                        <bp-ui-dropdown-item class="bpa-ni-dropdown-menu--item <?php echo ( 'forms' == $request_action ) ? '__active' : ''; ?>">
                                            <a href="<?php echo add_query_arg( array( 'page'=> $bookingpress_slugs->bookingpress_customize,'action' => 'forms'), esc_url( admin_url() . 'admin.php?page=bookingpress' ) );  // phpcs:ignore ?>" class="bpa-dm--item-link">
                                                <span>
                                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M14.6667 0.666992C15.1267 0.666992 15.5 1.04033 15.5 1.50033V4.63116L8.00083 12.1312L7.99583 15.6628L11.5342 15.6678L15.5 11.702V16.5003C15.5 16.9603 15.1267 17.3337 14.6667 17.3337H1.33333C0.873333 17.3337 0.5 16.9603 0.5 16.5003V1.50033C0.5 1.04033 0.873333 0.666992 1.33333 0.666992H14.6667ZM16.1483 6.34033L17.3267 7.51866L10.845 14.0003L9.665 13.9987L9.66667 12.822L16.1483 6.34033ZM8 9.00033H3.83333V10.667H8V9.00033ZM10.5 5.66699H3.83333V7.33366H10.5V5.66699Z" />
                                                    </svg>
                                                </span>    
                                                <?php esc_html_e( 'Forms', 'bookingpress-appointment-booking' ); ?>
                                            </a>
                                        </bp-ui-dropdown-item>                           
                                        <bp-ui-dropdown-item class="bpa-ni-dropdown-menu--item  <?php echo ( 'form_fields' == $request_action ) ? '__active' : ''; ?>">
                                            <a href="<?php echo add_query_arg( array( 'page'=> $bookingpress_slugs->bookingpress_customize,'action' => 'form_fields'), esc_url( admin_url() . 'admin.php?page=bookingpress' ) );  // phpcs:ignore ?>" class="bpa-dm--item-link">
                                                <span>
                                                    <svg width="19" height="18" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.3334 9.83366L18.1367 13.2187L15.6592 13.927L17.4301 16.9945L15.9867 17.8278L14.2159 14.7612L12.3634 16.5528L12.3334 9.83366ZM10.6667 4.00033H12.3334V5.66699H16.5001C16.7211 5.66699 16.9331 5.75479 17.0893 5.91107C17.2456 6.06735 17.3334 6.27931 17.3334 6.50033V9.83366H15.6667V7.33366H7.33342V15.667H10.6667V17.3337H6.50008C6.27907 17.3337 6.06711 17.2459 5.91083 17.0896C5.75455 16.9333 5.66675 16.7213 5.66675 16.5003V12.3337H4.00008V10.667H5.66675V6.50033C5.66675 6.27931 5.75455 6.06735 5.91083 5.91107C6.06711 5.75479 6.27907 5.66699 6.50008 5.66699H10.6667V4.00033ZM2.33341 10.667V12.3337H0.666748V10.667H2.33341ZM2.33341 7.33366V9.00033H0.666748V7.33366H2.33341ZM2.33341 4.00033V5.66699H0.666748V4.00033H2.33341ZM2.33341 0.666992V2.33366H0.666748V0.666992H2.33341ZM5.66675 0.666992V2.33366H4.00008V0.666992H5.66675ZM9.00008 0.666992V2.33366H7.33342V0.666992H9.00008ZM12.3334 0.666992V2.33366H10.6667V0.666992H12.3334Z" />
                                                    </svg>
                                                </span>
                                                <?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?>
                                            </a>
                                        </bp-ui-dropdown-item>                            
                                    </bp-ui-dropdown-menu>
                                </template>
                            </bp-ui-dropdown>
                        </li>
                        <?php
                    }

                    if( class_exists( '\BookingPressPro\admin\Header') && method_exists( '\BookingPressPro\admin\Header', 'render_header_settings' ) ){
                        \BookingPressPro\admin\Header::render_header_settings( $request_module, $request_action );
                    }
                    
                    if (!class_exists( '\BookingPressPro\admin\Header') && Header::bookingpress_verify_capabilities('bookingpress_settings') ) {
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
                    <?php if( !class_exists( '\BookingPressPro\admin\Header') ){ ?>
                    <li class="bpa-nav-item bpa-nav-item__is-go-premium">					
                        <a href="javascript:void(0)" class="bpa-nav-link" @click="open_premium_modal">
                            <div class="bpa-nav-link--icon">
                                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/><rect fill="none" height="24" width="24"/></g><g><g><g><polygon points="12.16,3 11.84,3 9.21,8.25 14.79,8.25"/></g><g><path d="M16.46,8.25h5.16l-2.07-4.14C19.21,3.43,18.52,3,17.76,3h-3.93L16.46,8.25z"/></g><g><polygon points="21.38,9.75 12.75,9.75 12.75,20.1"/></g><g><polygon points="11.25,20.1 11.25,9.75 2.62,9.75"/></g><g><path d="M7.54,8.25L10.16,3H6.24C5.48,3,4.79,3.43,4.45,4.11L2.38,8.25H7.54z"/></g></g></g></svg>
                            </div>
                            <?php
                                $bpa_current_date_for_bf_popup = current_time('timestamp',true); //GMT/ UTC+00 timeszone
                                $bpa_sale_popup_details = BookingPress\admin\Header::bookingpress_get_sales_data();

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
                                        } else if( 'summer_sale' == $type ){
                                            esc_html_e( 'Summer Sale', 'bookingpress-appointment-booking' );
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
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="bpa-mob-nav-overlay" id="bpa-mob-nav-overlay"></div>
    <?php
        require_once __DIR__.'/UpgradeModel.php';
    ?>
<?php } ?>

</div>
<div id="bpa-admin-notices-wrapper">
    <?php if( class_exists( '\BookingPressPro\admin\Header') && method_exists( '\BookingPressPro\admin\Header', 'render_admin_notices' ) ){
        \BookingPressPro\admin\Header::render_admin_notices();
    } ?>
</div>


<div class="bpa-fab-component" id="bookingpress_help_icon_wrapper" data-request-module="<?php echo esc_attr($request_module); ?>">
     <div class="bpa-fc__active-box" :class="bpa_fab_floating_btn == 1 ? '__bpa-is-active' : ''">
        <div class="bpa-fc__item">
            <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                <template #content>
                    <span><?php esc_html_e( 'Need Help?', 'bookingpress-appointment-booking' ); ?></span>
                </template>
                <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click="open_need_help_url()">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_5488_17507)">
                            <path d="M13.9987 2.33301C7.5587 2.33301 2.33203 7.55967 2.33203 13.9997C2.33203 20.4397 7.5587 25.6663 13.9987 25.6663C20.4387 25.6663 25.6654 20.4397 25.6654 13.9997C25.6654 7.55967 20.4387 2.33301 13.9987 2.33301ZM15.1654 22.1663H12.832V19.833H15.1654V22.1663ZM17.5804 13.1247L16.5304 14.198C15.947 14.793 15.527 15.3297 15.317 16.1697C15.2237 16.543 15.1654 16.963 15.1654 17.4997H12.832V16.9163C12.832 16.3797 12.9254 15.8663 13.0887 15.388C13.322 14.7113 13.707 14.1047 14.197 13.6147L15.6437 12.1447C16.1804 11.6313 16.437 10.8613 16.2854 10.0447C16.1337 9.20467 15.4804 8.49301 14.6637 8.25967C13.3687 7.89801 12.167 8.63301 11.782 9.74134C11.642 10.173 11.2804 10.4997 10.8254 10.4997H10.4754C9.7987 10.4997 9.33203 9.84634 9.5187 9.19301C10.0204 7.47801 11.4787 6.17134 13.287 5.89134C15.0604 5.61134 16.752 6.53301 17.802 7.99134C19.1787 9.89301 18.7704 11.9347 17.5804 13.1247V13.1247Z" fill="white"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_5488_17507">
                                <rect width="28" height="28" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </bp-ui-button>
            </bp-ui-tooltip>
        </div>
        <div class="bpa-fc__item">
            <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                 <template #content>
                    <span><?php esc_html_e( 'Feature Requests', 'bookingpress-appointment-booking' ); ?></span>
                </template>
                <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click="open_feature_request_url">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_5488_17506)">
                        <path d="M10.4987 24.4997C10.4987 25.083 10.9654 25.6663 11.6654 25.6663H16.332C17.032 25.6663 17.4987 25.083 17.4987 24.4997V23.333H10.4987V24.4997ZM13.9987 2.33301C9.4487 2.33301 5.83203 5.94967 5.83203 10.4997C5.83203 13.2997 7.23203 15.7497 9.33203 17.1497V19.833C9.33203 20.4163 9.7987 20.9997 10.4987 20.9997H17.4987C18.1987 20.9997 18.6654 20.4163 18.6654 19.833V17.1497C20.7654 15.633 22.1654 13.183 22.1654 10.4997C22.1654 5.94967 18.5487 2.33301 13.9987 2.33301Z" fill="white"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_5488_17506">
                                <rect width="28" height="28" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </bp-ui-button>
            </bp-ui-tooltip>
        </div>
        <div class="bpa-fc__item">
            <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                <template #content>
                    <span><?php esc_html_e( 'Facebook Community', 'bookingpress-appointment-booking' ); ?></span>
                </template>
                <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click="open_facebook_community_url()">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.00706 18.8219C5.71367 18.7798 5.40178 18.7562 5.07012 18.7562C2.62732 18.7562 1.26305 20.0452 0.740045 20.6874C0.655217 20.7978 0.609936 20.933 0.609936 21.0727C0.609936 21.0765 0.609931 21.0803 0.609293 21.0835C0.605467 21.3195 0.605469 21.632 0.605469 21.9452C0.605469 22.2973 0.891207 22.583 1.24328 22.583H5.81763C5.74556 22.3802 5.70793 22.1646 5.70793 21.9452C5.70793 21.2194 5.70793 20.3545 5.71048 19.8436C5.71048 19.4801 5.81444 19.1261 6.00706 18.8219ZM20.3775 22.583H7.62136C7.45234 22.583 7.2897 22.516 7.17043 22.3961C7.05052 22.2769 6.98355 22.1142 6.98355 21.9452C6.98355 21.2213 6.98355 20.3602 6.9861 19.8474C6.9861 19.8462 6.9861 19.8449 6.9861 19.8436C6.9861 19.6969 7.03713 19.5553 7.12961 19.4418C7.92113 18.538 10.3129 16.2049 13.9994 16.2049C18.172 16.2049 20.2672 18.5336 20.9082 19.4118C20.979 19.5196 21.0153 19.6414 21.0153 19.7658V21.9452C21.0153 22.1142 20.9484 22.2769 20.8284 22.3961C20.7092 22.516 20.5465 22.583 20.3775 22.583ZM22.1812 22.583H26.7556C27.1077 22.583 27.3934 22.2973 27.3934 21.9452V21.072C27.3934 20.9311 27.3468 20.7946 27.2614 20.683C26.7358 20.0452 25.3709 18.7562 22.9287 18.7562C22.6143 18.7562 22.3177 18.7772 22.0384 18.8155C22.2036 19.1044 22.2909 19.4322 22.2909 19.7671V21.9452C22.2909 22.1646 22.2533 22.3802 22.1812 22.583ZM5.07012 11.1025C3.30977 11.1025 1.88108 12.5312 1.88108 14.2915C1.88108 16.0519 3.30977 17.4805 5.07012 17.4805C6.83047 17.4805 8.25916 16.0519 8.25916 14.2915C8.25916 12.5312 6.83047 11.1025 5.07012 11.1025ZM22.9287 11.1025C21.1684 11.1025 19.7397 12.5312 19.7397 14.2915C19.7397 16.0519 21.1684 17.4805 22.9287 17.4805C24.6891 17.4805 26.1178 16.0519 26.1178 14.2915C26.1178 12.5312 24.6891 11.1025 22.9287 11.1025ZM13.9994 6C11.5356 6 9.53478 8.0008 9.53478 10.4647C9.53478 12.9285 11.5356 14.9293 13.9994 14.9293C16.4633 14.9293 18.4641 12.9285 18.4641 10.4647C18.4641 8.0008 16.4633 6 13.9994 6Z" fill="white"/>
                    </svg>
                </bp-ui-button>
            </bp-ui-tooltip>
        </div>
        <div class="bpa-fc__item">
            <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                <template #content>
                    <span><?php esc_html_e( 'YouTube Channel', 'bookingpress-appointment-booking' ); ?></span>
                </template>
                <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click="open_youtube_channel_url()">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M24.8115 8.31591C24.1704 7.17545 23.4746 6.96568 22.0579 6.88591C20.6426 6.78989 17.0839 6.75 14.0008 6.75C10.9118 6.75 7.35162 6.78989 5.93787 6.88443C4.52412 6.96568 3.82685 7.17398 3.1798 8.31591C2.51946 9.45489 2.17969 11.4167 2.17969 14.8706V14.8824C2.17969 18.3215 2.51946 20.2981 3.1798 21.4252C3.82685 22.5657 4.52264 22.7725 5.93639 22.8685C7.35162 22.9513 10.9118 23 14.0008 23C17.0839 23 20.6426 22.9512 22.0593 22.87C23.4761 22.774 24.1718 22.5672 24.813 21.4267C25.4792 20.2995 25.8161 18.323 25.8161 14.8839V14.872C25.8161 11.4167 25.4792 9.45489 24.8115 8.31591Z" fill="white"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.043 19.307V10.4434L18.4293 14.8752L11.043 19.307Z" fill="#125CD4"/>
                    </svg>
                </bp-ui-button>
            </bp-ui-tooltip>
        </div>
        <div class="bpa-fc__item">
            <div class="bpa-ab__close-icon" @click="bpa_fab_floating_close_btn()">
                <bp-ui-button class="bpa-btn bpa-btn--icon-without-box">

                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_5488_17508)">
                            <path d="M18.2987 5.70973C17.9087 5.31973 17.2787 5.31973 16.8887 5.70973L11.9988 10.5897L7.10875 5.69973C6.71875 5.30973 6.08875 5.30973 5.69875 5.69973C5.30875 6.08973 5.30875 6.71973 5.69875 7.10973L10.5888 11.9997L5.69875 16.8897C5.30875 17.2797 5.30875 17.9097 5.69875 18.2997C6.08875 18.6897 6.71875 18.6897 7.10875 18.2997L11.9988 13.4097L16.8887 18.2997C17.2787 18.6897 17.9087 18.6897 18.2987 18.2997C18.6887 17.9097 18.6887 17.2797 18.2987 16.8897L13.4087 11.9997L18.2987 7.10973C18.6787 6.72973 18.6787 6.08973 18.2987 5.70973V5.70973Z" fill="white"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_5488_17508">
                                <rect width="24" height="24" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </bp-ui-button>
            </div>
        </div>
    </div>
    <div class="bpa-fc__inactive-box">
        <div class="bpa-fc__item">
            <bp-ui-button class="bpa-btn bpa-btn--icon-without-box"  @click="bpa_fab_floating_action_btn($event)">
                <svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_1129_9706)">
                        <path d="M5.73704 28.3466C6.17083 28.7805 6.61584 29.1807 7.0869 29.5524C7.18697 29.6313 7.33225 29.6216 7.42238 29.5315L12.7922 24.1617C12.9048 24.049 12.8819 23.867 12.7476 23.7812C12.2661 23.4737 11.8115 23.1104 11.3924 22.6913C10.9867 22.2856 10.6364 21.8446 10.333 21.3854C10.2459 21.2537 10.0652 21.2332 9.95356 21.3449L4.5905 26.708C4.49915 26.7993 4.48994 26.9472 4.57085 27.0479C4.933 27.4984 5.31845 27.9281 5.73704 28.3466Z" fill="white" />
                        <path d="M24.8157 31.0143C24.9613 30.933 24.9881 30.73 24.8702 30.612L19.1297 24.8715C19.0699 24.8117 18.9842 24.7886 18.9019 24.8082C17.6578 25.104 16.3679 25.1039 15.1299 24.8021C15.0474 24.7819 14.9611 24.8049 14.901 24.865L9.16771 30.5983C9.04988 30.7161 9.07642 30.9189 9.22183 31.0004C14.0512 33.7054 19.9798 33.7121 24.8157 31.0143Z" fill="white" />
                        <path d="M26.9858 29.5728C27.464 29.1943 27.916 28.7871 28.3568 28.3463C28.7684 27.9347 29.147 27.5121 29.5023 27.0688C29.583 26.9681 29.5736 26.8204 29.4824 26.7291L24.1213 21.3679C24.009 21.2556 23.8278 21.2781 23.7414 21.4114C23.4475 21.8651 23.0994 22.2929 22.7015 22.691C22.2757 23.1167 21.8142 23.4866 21.3215 23.7961C21.186 23.8813 21.1618 24.0639 21.275 24.177L26.65 29.5521C26.7402 29.6423 26.8857 29.652 26.9858 29.5728Z" fill="white" />
                        <path d="M3.10188 24.9037C3.18359 25.0485 3.38593 25.0748 3.50351 24.9571L9.22957 19.231C9.29017 19.1705 9.31299 19.0835 9.29212 19.0004C8.97677 17.7474 8.9642 16.4306 9.26594 15.1653C9.28553 15.0831 9.26252 14.9975 9.20279 14.9378L3.46208 9.197C3.34419 9.07911 3.14123 9.10577 3.05991 9.2513C0.348413 14.1024 0.368668 20.0593 3.10188 24.9037Z" fill="white" />
                        <path d="M15.0332 9.29377C16.3347 8.95951 17.7055 8.95927 19.0009 9.28736C19.0839 9.30842 19.1707 9.28565 19.2312 9.22506L24.9573 3.49899C25.0749 3.38134 25.0487 3.17894 24.9038 3.09717C20.023 0.343497 14.0099 0.350445 9.14237 3.11798C8.99801 3.20006 8.9722 3.40197 9.0896 3.51937L14.8019 9.23178C14.8627 9.29249 14.95 9.3152 15.0332 9.29377Z" fill="white" />
                        <path d="M23.7821 12.734C23.8679 12.8683 24.0499 12.8913 24.1626 12.7785L29.5324 7.40878C29.6225 7.31865 29.6322 7.17337 29.5533 7.07329C28.8609 6.19571 27.9869 5.31136 27.0488 4.55716C26.9482 4.47625 26.8002 4.48552 26.7089 4.5768L21.3458 9.93993C21.2342 10.0516 21.2547 10.2323 21.3864 10.3194C22.3527 10.9578 23.1564 11.754 23.7821 12.734Z" fill="white" />
                        <path d="M31.0058 9.22284C30.9243 9.07742 30.7215 9.05082 30.6036 9.16871L24.8704 14.9019C24.8103 14.962 24.7875 15.0482 24.8078 15.1307C25.1223 16.4111 25.1096 17.755 24.7813 19.0354C24.7599 19.1187 24.7826 19.2061 24.8435 19.267L30.5555 24.979C30.673 25.0964 30.8749 25.0706 30.9569 24.9261C33.7108 20.0736 33.7313 14.0886 31.0058 9.22284Z" fill="white" />
                        <path d="M6.99514 4.58039C6.06369 5.32684 5.18716 6.21748 4.49123 7.0969C4.41203 7.19697 4.42161 7.34245 4.51186 7.43269L9.88688 12.8078C10.0001 12.921 10.1826 12.8968 10.2678 12.7613C10.8696 11.8034 11.716 10.9482 12.6524 10.3413C12.7858 10.2549 12.8083 10.0738 12.6959 9.96146L7.33483 4.60029C7.24362 4.50906 7.09583 4.49966 6.99514 4.58039Z" fill="white" />
                    </g>
                    <defs>
                        <clipPath id="clip0_1129_9706">
                            <rect width="34" height="34.001" fill="white" transform="translate(0.460938 0.460938)" />
                        </clipPath>
                    </defs>
                </svg>
            </bp-ui-button>
        </div>
    </div>   

    <bp-ui-drawer class="bpa-help-drawer" v-if="true" v-model="needHelpDrawer" :direction="needHelpDrawerDirection" :close-on-click-modal="true" :close-on-press-escape="true" @close="closeNeedHelper" v-cloak>
        <bp-ui-container>
            <div class="bpa-back-loader-container" v-if="is_display_drawer_loader == '1'">
                <div class="bpa-back-loader"></div>
            </div>
            <header id="bp-drawer__title" class="bp-drawer__header"><span role="heading" title=""></span><bp-ui-button aria-label="close drawer" type="button" class="bp-ui-drawer__close-btn" @click="closeNeedHelper"><i class="bp-ui-dialog__close bp-icon bp-icon-close"></i></bp-ui-button></header>    
            <div class="bpa-help-drawer__body-wrapper">
                <div class="bpa-hd-header">
                    <h1 class="bpa-page-heading">{{ request_module }}</h1>
                    <bp-ui-link  :key="read_more_link" :href="read_more_link" :underline="false" target="_blank" class="bpa-btn bpa-btn__small"><?php esc_html_e('Read more', 'bookingpress-appointment-booking'); ?></bp-ui-link>
                </div>
                <div class="bpa-hd-body bp_new_single_content" v-html="helpDrawerData"></div>
            </div>
        </bp-ui-container>    
    </bp-ui-drawer>

    <bp-ui-drawer class="bpa-help-drawer" v-if="true" v-model="needHelpDrawer_add" :direction="add_needHelpDrawerDirection" show-close v-cloak>
        <bp-ui-container>
            <div class="bpa-back-loader-container" v-if="is_display_drawer_loader == '1'">
                <div class="bpa-back-loader"></div>
            </div>
            <header id="bp-drawer__title" class="bp-drawer__header"><span role="heading" title=""></span><bp-ui-button aria-label="close drawer" type="button" class="bp-ui-drawer__close-btn" @click="closeNeedHelper"><i class="bp-ui-dialog__close bp-icon bp-icon-close"></i></bp-ui-button></header> 
            <div class="bpa-help-drawer__body-wrapper">
                <div class="bpa-hd-header">
                    <h1 class="bpa-page-heading">{{ request_module }}</h1>
                    <bp-ui-link :href="read_more_link" :key="read_more_link" :underline="false" target="_blank" class="bpa-btn bpa-btn__small"><?php esc_html_e('Read more', 'bookingpress-appointment-booking'); ?></bp-ui-link>
                </div>
                <div class="bpa-hd-body bp_new_single_content" v-html="helpDrawerData"></div>
            </div>
        </bp-ui-container>    
    </bp-ui-drawer>
</div>
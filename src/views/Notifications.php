<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="root_app" class="bookingpress-notifications bookingpress_page_wrapper">
    <?php
        if ( class_exists( '\BookingPressPro\admin\Dashboard' ) && method_exists( '\BookingPressPro\admin\Dashboard', 'render_dashboard_header' ) ) {
            \BookingPressPro\admin\Dashboard::render_dashboard_header();
        } else {
            require_once __DIR__ . '/components/Header.php';
        }
    ?>

    <div class="notifications-app-root bookingpress_page_inner_wrapper" id="notifications-app-root" v-cloak>
        <bp-ui-main class="bpa-email-notifications-container bpa--is-page-scrollable-tablet" id="all-page-main-container">
            <bp-ui-container class="bpa-default-card">
                <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
                    <div class="bpa-back-loader"></div>
                </div>
                <div id="bpa-main-container">            
                    <bp-ui-row type="flex" :gutter="32">
                        <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="5">
                            <div class="bpa-en-left">
                                <div class="bpa-en-left__item">
                                    <div class="bpa-en-left__item-head">
                                        <h4 class="bpa-page-heading"><?php esc_html_e( 'Default Notifications', 'bookingpress-appointment-booking' ); ?></h4>
                                    </div>
                                    <div class="bpa-en-left__item-body">
                                        <div class="bpa-en-left_item-body--list">
                                            <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo esc_attr__( 'Appointment Approval Notification', 'bookingpress-appointment-booking' ); ?>" data-key="approved" :class="bookingpress_active_email_notification == 'appointment_approved' ? '__bpa-is-active' : ''" ref="appointmentApproved" @click="bookingpress_select_email_notification('approved', 'Appointment Approved', 'appointment_approved')">
                                                <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer'] && default_notification_status['employee'] && (default_notification_status['customer']['appointment_approved'] == true || default_notification_status['employee']['appointment_approved'] == true)">circle</span>
                                                <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                                <p><?php esc_html_e( 'On Approval', 'bookingpress-appointment-booking' ); ?></p>
                                            </div>
                                            <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo esc_attr__( 'Appointment Pending Notification', 'bookingpress-appointment-booking' ); ?>" data-key="pending" :class="bookingpress_active_email_notification == 'appointment_pending' ? '__bpa-is-active' : ''" ref="appointmentPending" @click="bookingpress_select_email_notification('pending', 'Appointment Pending', 'appointment_pending')">
                                                <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer'] && default_notification_status['employee'] && (default_notification_status['customer']['appointment_pending'] == true || default_notification_status['employee']['appointment_pending'] == true)">circle</span>
                                                <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                                <p><?php esc_html_e( 'On Pending', 'bookingpress-appointment-booking' ); ?></p>
                                            </div>
                                            <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo esc_attr__( 'Appointment Rejection Notification', 'bookingpress-appointment-booking' ); ?>" data-key="reject" :class="bookingpress_active_email_notification == 'appointment_rejected' ? '__bpa-is-active' : ''" ref="appointmentRejected" @click="bookingpress_select_email_notification('reject', 'Appointment Rejected', 'appointment_rejected')">
                                                <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer'] && default_notification_status['employee'] && (default_notification_status['customer']['appointment_rejected'] == true || default_notification_status['employee']['appointment_rejected'] == true)">circle</span>
                                                <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                                <p><?php esc_html_e( 'On Rejection', 'bookingpress-appointment-booking' ); ?></p>
                                            </div>
                                            <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo esc_attr__( 'Appointment Cancellation Notification', 'bookingpress-appointment-booking' ); ?>" data-key="cancel" :class="bookingpress_active_email_notification == 'appointment_canceled' ? '__bpa-is-active' : ''" ref="appointmentCanceled" @click="bookingpress_select_email_notification('cancel', 'Appointment Canceled', 'appointment_canceled')">
                                                <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer'] && default_notification_status['employee'] && (default_notification_status['customer']['appointment_canceled'] == true || default_notification_status['employee']['appointment_canceled'] == true)">circle</span>
                                                <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                                <p><?php esc_html_e( 'On Cancellation', 'bookingpress-appointment-booking' ); ?></p>
                                            </div>
                                            <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo esc_attr__( 'Share Appointment Notification', 'bookingpress-appointment-booking' ); ?>" data-key="share_url" :class="bookingpress_active_email_notification == 'share_appointment' ? '__bpa-is-active' : ''" ref="shareAppointment" @click="bookingpress_select_email_notification('share_url', 'Share Appointment URL', 'share_appointment')">
                                                <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer'] && default_notification_status['employee'] && (default_notification_status['customer']['share_appointment'] == true || default_notification_status['employee']['share_appointment'] == true)">circle</span>
                                                <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                                <p><?php esc_html_e( 'Share Appointment URL', 'bookingpress-appointment-booking' ); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </bp-ui-col>
                        <bp-ui-col :xs="18" :sm="18" :md="18" :lg="18" :xl="19">
                            <bp-ui-row>
                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                                            <h1 class="bpa-page-heading" v-text="bookingpress_email_notification_edit_text"></h1>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--right">
                                            <div class="bpa-hw-right-btn-group">
                                                <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_save_email_notification_data" :disabled="is_disabled">                    
                                                    <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
                                                    <div class="bpa-btn--loader__circles">                    
                                                        <div></div>
                                                        <div></div>
                                                        <div></div>
                                                    </div>
                                                </bp-ui-button>
                                            </div>
                                        </bp-ui-col>
                                    </bp-ui-row>
                                </bp-ui-col>              
                            </bp-ui-row>                    
                            <bp-ui-row type="flex" :gutter="32">
                                <bp-ui-col :xs="16" :sm="16" :md="16" :lg="16" :xl="18">                                                                
                                    <div class="bpa-en-body-card">
                                        <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_loader == '1'">
                                            <div class="bpa-back-loader"></div>
                                        </div>                        
                                        <bp-ui-row type="flex" class="bpa-en-body-card__content">
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-row type="flex">
                                                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                        <bp-ui-tab class="bpa-tabs bpa-elm-tab-container" v-model="activeTabName" @tab-click="bookingpress_change_tab($event)" @tab-change="bookingpress_change_tab($event)">
                                                            <bp-ui-tab-pane name="customer" label="<?php echo esc_attr__( 'To Customer', 'bookingpress-appointment-booking' ); ?>">
                                                                <template #label>
                                                                    <span><?php esc_html_e( 'To Customer', 'bookingpress-appointment-booking' ); ?></span>
                                                                </template>
                                                            </bp-ui-tab-pane>
                                                            <bp-ui-tab-pane name="employee" label="<?php echo esc_attr__( 'To Admin', 'bookingpress-appointment-booking' ); ?>">
                                                                <template #label>
                                                                    <span><?php esc_html_e( 'To Admin', 'bookingpress-appointment-booking' ); ?></span>
                                                                </template>
                                                            </bp-ui-tab-pane>
                                                        </bp-ui-tab>
                                                    </bp-ui-col>
                                                </bp-ui-row>
                                                <bp-ui-row type="flex">
                                                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                        <bp-ui-form class="bpa-en-body-card__content--form" id="email_notification_form" ref="email_notification_form" label-position="top" @submit.prevent>
                                                            <bp-ui-row>
                                                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                    <div class="bpa-en-status--swtich-row" v-if="activeTabName == 'customer' && default_notification_status['customer']">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Send Notification', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <bp-ui-switch class="bpa-swtich-control" v-model="default_notification_status['customer'][bookingpress_active_email_notification]"></bp-ui-switch>
                                                                    </div>
                                                                    <div class="bpa-en-status--swtich-row" v-if="activeTabName == 'employee' && default_notification_status['employee']">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Send Notification', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <bp-ui-switch class="bpa-swtich-control" v-model="default_notification_status['employee'][bookingpress_active_email_notification]"></bp-ui-switch>
                                                                    </div>
                                                                </bp-ui-col>
                                                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                    <bp-ui-form-item>
                                                                        <template #label>
                                                                            <span class="bpa-form-label"><?php esc_html_e( 'Email Subject', 'bookingpress-appointment-booking' ); ?></span>
                                                                        </template>
                                                                        <bp-ui-input class="bpa-form-control" v-model="bookingpress_email_notification_subject" placeholder="<?php esc_html_e( 'Enter Subject', 'bookingpress-appointment-booking' ); ?>"></bp-ui-input>
                                                                    </bp-ui-form-item>
                                                                </bp-ui-col>
                                                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                    <bp-ui-form-item>
                                                                        <template #label>
                                                                            <span class="bpa-form-label"><?php esc_html_e( 'Email Message', 'bookingpress-appointment-booking' ); ?></span>
                                                                        </template>
                                                                        <div v-pre id="bookingpress_editor_wrapper" style="width: 100% !important; flex: 1 1 100% !important;">
                                                                            <?php
                                                                            $bookingpress_message_content_editor = [
                                                                                'textarea_name' => 'bookingpress_email_notification_subject_message',
                                                                                'media_buttons' => false,
                                                                                'textarea_rows' => 10,
                                                                                'default_editor' => 'html',
                                                                                'editor_css' => '',
                                                                                'tinymce' => true,
                                                                            ];
                                                                            wp_editor( '', 'bookingpress_email_notification_subject_message', $bookingpress_message_content_editor );
                                                                            ?>
                                                                        </div>
                                                                        <span class="bpa-sm__field-helper-label"><?php esc_html_e( 'Allowed HTML tags <div>, <label>, <span>, <p>, <ul>, <li>, <tr>, <td>, <a>, <br>, <b>, <h1>, <h2>, <hr>', 'bookingpress-appointment-booking' ); ?></span>
                                                                    </bp-ui-form-item>
                                                                </bp-ui-col>
                                                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                    <div class="bpa-toast-notification --bpa-warning">
                                                                        <div class="bpa-front-tn-body">
                                                                            <span class="material-icons-round">info</span>
                                                                            <p><?php esc_html_e( 'Note', 'bookingpress-appointment-booking' ); ?>: <?php esc_html_e( 'Please add <br /> in the email message to add a new line', 'bookingpress-appointment-booking' ); ?>. <?php esc_html_e( 'Enter key will not be considered as new line', 'bookingpress-appointment-booking' ); ?>.</p>
                                                                        </div>
                                                                    </div>
                                                                </bp-ui-col>
                                                            </bp-ui-row>
                                                        </bp-ui-form>
                                                    </bp-ui-col>
                                                </bp-ui-row>
                                            </bp-ui-col>
                                        </bp-ui-row>                      
                                    </div>
                                </bp-ui-col>
                                <bp-ui-col :xs="8" :sm="8" :md="8" :lg="8" :xl="6">
                                    <div class="bpa-email-tags-container">
                                        <div class="bpa-gs__cb--item-heading">
                                            <h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Insert email placeholders', 'bookingpress-appointment-booking' ); ?></h4>
                                        </div>
                                        <div class="bpa-gs__cb--item-tags-body">
                                            <div>
                                                <span class="bpa-tags--item-sub-heading"><?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?></span>
                                                <template v-for="item in bookingpress_customer_placeholders" :key="item.value">
                                                    <span class="bpa-tags--item-body" @click="bookingpress_insert_placeholder(item.value)" v-if="item.value !== '%customer_cancel_appointment_link%' || (bookingpress_active_email_notification !== 'appointment_rejected' && bookingpress_active_email_notification !== 'appointment_canceled')">{{ item.name }}</span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="bpa-gs__cb--item-tags-body">
                                            <div>
                                                <span class="bpa-tags--item-sub-heading"><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?></span>
                                                <span class="bpa-tags--item-body" v-for="item in bookingpress_service_placeholders" :key="item.value" @click="bookingpress_insert_placeholder(item.value)">{{ item.name }}</span>
                                            </div>
                                        </div>
                                        <div class="bpa-gs__cb--item-tags-body">
                                            <div>
                                                <span class="bpa-tags--item-sub-heading"><?php esc_html_e( 'Company', 'bookingpress-appointment-booking' ); ?></span>
                                                <span class="bpa-tags--item-body" v-for="item in bookingpress_company_placeholders" :key="item.value" @click="bookingpress_insert_placeholder(item.value)">{{ item.name }}</span>
                                            </div>
                                        </div>
                                        <div class="bpa-gs__cb--item-tags-body">
                                            <div>
                                                <span class="bpa-tags--item-sub-heading"><?php esc_html_e( 'Appointment', 'bookingpress-appointment-booking' ); ?></span>
                                                <span class="bpa-tags--item-body" v-for="item in bookingpress_appointment_placeholders" :key="item.value" @click="bookingpress_insert_placeholder(item.value)">{{ item.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>                    
                        </bp-ui-col>
                    </bp-ui-row>
                </div>    
            </bp-ui-container>
        </bp-ui-main>
    </div>
</div>

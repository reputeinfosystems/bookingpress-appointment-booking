<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-main class="bpa-email-notifications-container bpa--is-page-scrollable-tablet" id="all-page-main-container">
    <el-container class="bpa-default-card">
        <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
            <div class="bpa-back-loader"></div>
        </div>
        <div id="bpa-main-container">            
            <el-row type="flex" :gutter="32">
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="5">
                    <div class="bpa-en-left">
                        <div class="bpa-en-left__item">
                            <div class="bpa-en-left__item-head">
                                <h4 class="bpa-page-heading"><?php esc_html_e('Default Notifications', 'bookingpress-appointment-booking'); ?></h4>
                            </div>
                            <div class="bpa-en-left__item-body">
                                <div class="bpa-en-left_item-body--list">
                                    <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo addslashes( esc_html__('Appointment Approval Notification', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>" data-key="approved" :class="bookingpress_active_email_notification == 'appointment_approved' ? '__bpa-is-active' : ''" ref="appointmentApproved" @click="bookingpress_select_email_notification('approved', 'Appointment Approved', 'appointment_approved')">
                                        <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_approved'] == true || default_notification_status['employee']['appointment_approved'] == true " >circle</span>
                                        <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                        <p><?php esc_html_e('On Approval', 'bookingpress-appointment-booking'); ?></p>
                                    </div>
                                    <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo addslashes( esc_html__('Appointment Pending Notification', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>" data-key="pending" :class="bookingpress_active_email_notification == 'appointment_pending' ? '__bpa-is-active' : ''" ref="appointmentPending" @click="bookingpress_select_email_notification('pending', 'Appointment Pending', 'appointment_pending')">
                                        <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_pending'] == true || default_notification_status['employee']['appointment_pending'] == true" >circle</span>
                                        <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                        <p><?php esc_html_e('On Pending', 'bookingpress-appointment-booking'); ?></p>
                                    </div>
                                    <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo addslashes( esc_html__( 'Appointment Rejection Notification', 'bookingpress-appointment-booking' ) ); // phpcs:ignore ?>" data-key="reject" :class="bookingpress_active_email_notification == 'appointment_rejected' ? '__bpa-is-active' : ''" ref="appointmentRejected" @click="bookingpress_select_email_notification('reject', 'Appointment Rejected', 'appointment_rejected')">
                                        <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_rejected'] == true || default_notification_status['employee']['appointment_rejected'] == true">circle</span>
                                        <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                        <p><?php esc_html_e('On Rejection', 'bookingpress-appointment-booking'); ?></p>
                                    </div>
                                    <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo addslashes( esc_html__('Appointment Cancellation Notification', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>" data-key="cancel" :class="bookingpress_active_email_notification == 'appointment_canceled' ? '__bpa-is-active' : ''" ref="appointmentCanceled" @click="bookingpress_select_email_notification('cancel', 'Appointment Canceled', 'appointment_canceled')">
                                        <span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_canceled'] == true || default_notification_status['employee']['appointment_canceled'] == true">circle</span>
                                        <span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                        <p><?php esc_html_e('On Cancellation', 'bookingpress-appointment-booking'); ?></p>
                                    </div>
                                    <div class="bpa-en-left_item-body--list__item bpa-en-left-notification-item" data-label="<?php echo addslashes( __('Share Appointment Notification', 'bookingpress-appointment-booking') ); //phpcs:ignore ?>" data-key="share_url" :class="bookingpress_active_email_notification == 'share_appointment' ? '__bpa-is-active' : ''" ref="shareAppointment" @click="bookingpress_select_email_notification('share_url','Share Appointment URL', 'share_appointment')">
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['share_appointment'] == true || default_notification_status['employee']['share_appointment'] == true">circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
										<p><?php esc_html_e( 'Share Appointment URL', 'bookingpress-appointment-booking' ); ?></p>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-col>
                <el-col :xs="18" :sm="18" :md="18" :lg="18" :xl="19">
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-row type="flex" class="bpa-mlc-head-wrap">
                                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                                    <h1 class="bpa-page-heading" v-text="bookingpress_email_notification_edit_text"></h1>
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--right">
                                    <div class="bpa-hw-right-btn-group">
                                        <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_save_email_notification_data" :disabled="is_disabled" >                    
                                            <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                                            <div class="bpa-btn--loader__circles">                    
                                                <div></div>
                                                <div></div>
                                                <div></div>
                                            </div>
                                        </el-button>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-col>              
                    </el-row>                    
                    <el-row type="flex" :gutter="32">
                        <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="18">                                                                
                            <div class="bpa-en-body-card">
                                <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                                    <div class="bpa-back-loader"></div>
                                </div>                        
                                <el-row type="flex" class="bpa-en-body-card__content">
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <el-row type="flex">
                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <el-tabs class="bpa-tabs bpa-elm-tab-container" v-model="activeTabName" @tab-click="bookingpress_change_tab">
                                                    <el-tab-pane name="customer">
                                                        <template #label>
                                                            <span><?php esc_html_e('To Customer', 'bookingpress-appointment-booking'); ?></span>
                                                        </template>
                                                    </el-tab-pane>
                                                    <el-tab-pane name="employee">
                                                        <template #label>
                                                            <span><?php esc_html_e('To Admin', 'bookingpress-appointment-booking'); ?></span>
                                                        </template>
                                                    </el-tab-pane>
                                                </el-tabs>
                                            </el-col>
                                        </el-row>
                                        <el-row type="flex">
                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <el-form class="bpa-en-body-card__content--form" id="email_notification_form" ref="email_notification_form" @submit.native.prevent>
                                                    <el-row>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <div class="bpa-en-status--swtich-row" v-if="activeTabName == 'customer'">
                                                                <label class="bpa-form-label"><?php esc_html_e('Send Notification', 'bookingpress-appointment-booking'); ?></label>
                                                                <el-switch class="bpa-swtich-control" v-model="default_notification_status[activeTabName][bookingpress_active_email_notification]"></el-switch>
                                                            </div>
                                                            <div class="bpa-en-status--swtich-row" v-if="activeTabName == 'employee'">
                                                                <label class="bpa-form-label"><?php esc_html_e('Send Notification', 'bookingpress-appointment-booking'); ?></label>
                                                                <el-switch class="bpa-swtich-control" v-model="default_notification_status[activeTabName][bookingpress_active_email_notification]"></el-switch>
                                                            </div>
                                                        </el-col>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <el-form-item>
                                                                <template #label>
                                                                    <span class="bpa-form-label"><?php esc_html_e('Email Subject', 'bookingpress-appointment-booking'); ?></span>
                                                                </template>
                                                                <el-input class="bpa-form-control" v-model="bookingpress_email_notification_subject" placeholder="<?php esc_html_e('Enter Subject', 'bookingpress-appointment-booking'); ?>"></el-input>
                                                            </el-form-item>
                                                        </el-col>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <el-form-item>
                                                                <template #label>
                                                                    <span class="bpa-form-label"><?php esc_html_e('Email Message', 'bookingpress-appointment-booking'); ?></span>
                                                                </template>
                                                                <?php
                                                                $bookingpress_message_content_editor = array(
                                                                        'textarea_name' => 'bookingpress_email_notification_subject_message',
                                                                        'media_buttons' => false,
                                                                        'textarea_rows' => 10,
                                                                        'default_editor' => 'html',
                                                                        'editor_css' => '',
                                                                        'tinymce' => true,
                                                                );
                                                                wp_editor('', 'bookingpress_email_notification_subject_message', $bookingpress_message_content_editor);
                                                                ?>
                                                                <span class="bpa-sm__field-helper-label"><?php esc_html_e('Allowed HTML tags <div>, <label>, <span>, <p>, <ul>, <li>, <tr>, <td>, <a>, <br>, <b>, <h1>, <h2>, <hr>', 'bookingpress-appointment-booking'); ?></span>
                                                            </el-form-item>
                                                        </el-col>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <div class="bpa-toast-notification --bpa-warning">
                                                                <div class="bpa-front-tn-body">
                                                                    <span class="material-icons-round">info</span>
                                                                    <p><?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?>: <?php esc_html_e('Please add <br /> in the email message to add a new line', 'bookingpress-appointment-booking'); ?>. <?php esc_html_e('Enter key will not be considered as new line', 'bookingpress-appointment-booking'); ?>.</p>
                                                                </div>
                                                            </div>
                                                        </el-col>
                                                    </el-row>
                                                </el-form>
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                </el-row>                      
                            </div>
                        </el-col>
                        <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="6">
                            <div class="bpa-email-tags-container">
                                <div class="bpa-gs__cb--item-heading">
                                    <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Insert email placeholders', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Customer', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_customer_placeholders" @click="bookingpress_insert_placeholder(item.value)" v-if="((item.value == '%customer_cancel_appointment_link%') && (bookingpress_active_email_notification != 'appointment_rejected' && bookingpress_active_email_notification != 'appointment_canceled') || (item.value != '%customer_cancel_appointment_link%'))">{{ item.name }}</span>
                                    </div>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_service_placeholders" @click="bookingpress_insert_placeholder(item.value)">{{ item.name }}</span>
                                    </div>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Company', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_company_placeholders" @click="bookingpress_insert_placeholder(item.value)">{{ item.name }}</span>
                                    </div>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Appointment', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_appointment_placeholders" @click="bookingpress_insert_placeholder(item.value)">{{ item.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </el-col>
                    </el-row>                    
                </el-col>
            </el-row>
        </div>    
    </el-container>
    
</el-main>

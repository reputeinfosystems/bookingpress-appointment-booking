<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<bp-ui-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="message_settings" label="messages" data-tab_name="message_settings">
    <template #label>
        <i class="material-icons-round">question_answer</i>
        <?php esc_html_e('Messages', 'bookingpress-appointment-booking'); ?>
    </template>
    <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_tab_loader == '1'">
        <div class="bpa-back-loader"></div>
    </div>
    <div class="bpa-general-settings-tabs--pb__card bpa-payment-settings-tabs--pb__card">
        <bp-ui-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Message Settings', 'bookingpress-appointment-booking'); ?></h1>
            </bp-ui-col>
            <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">    
                    <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('message_setting_form','message_setting')" :disabled="is_disabled" >                    
                      <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                      <div class="bpa-btn--loader__circles">                    
                          <div></div>
                          <div></div>
                          <div></div>
                      </div>
                    </bp-ui-button>
                    <?php do_action('bookingpress_message_setting_header_button'); ?>
                </div>
            </bp-ui-col>
        </bp-ui-row>
        <div class="bpa-gs--tabs-pb__content-body">
            <div class="bpa-gs__cb--item">
                <bp-ui-form id="message_setting_form" :rules="rules_message" ref="message_setting_form" :model="message_setting_form"  @submit.native.prevent>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No service selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <bp-ui-form-item prop="no_service_selected_for_the_booking">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.no_service_selected_for_the_booking"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No appointment date selected for the booking', 'bookingpress-appointment-booking'); ?></h4>    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">                
                            <bp-ui-form-item prop="no_appointment_date_selected_for_the_booking">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.no_appointment_date_selected_for_the_booking"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No appointment time selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <bp-ui-form-item prop="no_appointment_time_selected_for_the_booking">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.no_appointment_time_selected_for_the_booking"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No payment method is selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" :gutter="64">                
                            <bp-ui-form-item prop="no_payment_method_is_selected_for_the_booking">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.no_payment_method_is_selected_for_the_booking"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Duplicate email address found', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <bp-ui-form-item prop="duplicate_email_address_found">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.duplicate_email_address_found"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Unsupported currency selected for the payment', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <bp-ui-form-item prop="unsupported_currecy_selected_for_the_payment">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.unsupported_currecy_selected_for_the_payment"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Time slot already booked', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" :gutter="64">                
                            <bp-ui-form-item prop="duplidate_appointment_time_slot_found">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.duplidate_appointment_time_slot_found"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No payment method available', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <bp-ui-form-item prop="no_payment_method_available">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.no_payment_method_available"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>    
                    <?php
                    if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_message_settings_after_no_payment_method_available' ) ) {
                        BookingPressPro\admin\Settings::render_message_settings_after_no_payment_method_available();
                    }
                    ?>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No timeslots available for booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <bp-ui-form-item prop="no_timeslots_available">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.no_timeslots_available"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <?php
                    if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_message_settings_after_no_time_slot_available' ) ) {
                        BookingPressPro\admin\Settings::render_message_settings_after_no_time_slot_available();
                    }
                    ?>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Cancel Appointment Confirmation', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <bp-ui-form-item prop="cancel_appointment_confirmation">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.cancel_appointment_confirmation"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No Appointment Available to Cancel', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <bp-ui-form-item prop="no_appointment_available_for_cancel">
                            <bp-ui-input class="bpa-form-control" v-model="message_setting_form.no_appointment_available_for_cancel"></bp-ui-input>        
                            </bp-ui-form-item>                        
                        </bp-ui-col>
                    </bp-ui-row>  
                    <?php
                    if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_message_settings_outside_section' ) ) {
                        BookingPressPro\admin\Settings::render_message_settings_outside_section();
                    }
                    ?>                  
                <bp-ui-form>                    
            </div>            
        </div>            
    </div>
</bp-ui-tab-pane>


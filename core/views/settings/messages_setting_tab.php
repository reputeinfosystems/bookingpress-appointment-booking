<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="message_settings" label="messages" data-tab_name="message_settings">
    <span slot="label">
        <i class="material-icons-round">question_answer</i>
        <?php esc_html_e('Messages', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card bpa-payment-settings-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Message Settings', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">    
                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('message_setting_form','message_setting')" :disabled="is_disabled" >                    
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
        <div class="bpa-gs--tabs-pb__content-body">
            <div class="bpa-gs__cb--item">
                <el-form id="message_setting_form" :rules="rules_message" ref="message_setting_form" :model="message_setting_form"  @submit.native.prevent>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No service selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_service_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_service_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No appointment date selected for the booking', 'bookingpress-appointment-booking'); ?></h4>    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">                
                            <el-form-item prop="no_appointment_date_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_appointment_date_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No appointment time selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_appointment_time_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_appointment_time_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No payment method is selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" :gutter="64">                
                            <el-form-item prop="no_payment_method_is_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_payment_method_is_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Duplicate email address found', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="duplicate_email_address_found">
                            <el-input class="bpa-form-control" v-model="message_setting_form.duplicate_email_address_found"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Unsupported currency selected for the payment', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="unsupported_currecy_selected_for_the_payment">
                            <el-input class="bpa-form-control" v-model="message_setting_form.unsupported_currecy_selected_for_the_payment"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Time slot already booked', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" :gutter="64">                
                            <el-form-item prop="duplidate_appointment_time_slot_found">
                            <el-input class="bpa-form-control" v-model="message_setting_form.duplidate_appointment_time_slot_found"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No payment method available', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_payment_method_available">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_payment_method_available"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>    
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No timeslots available for booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_timeslots_available">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_timeslots_available"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Cancel Appointment Confirmation', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="cancel_appointment_confirmation">
                            <el-input class="bpa-form-control" v-model="message_setting_form.cancel_appointment_confirmation"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No Appointment Available to Cancel', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_appointment_available_for_cancel">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_appointment_available_for_cancel"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>                    
                <el-form>                    
            </div>            
        </div>            
    </div>
</el-tab-pane>


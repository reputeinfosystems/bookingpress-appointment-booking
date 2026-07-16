<div id="add_holiday_model_dialog" v-cloak class="bookingpress-add-holiday-dialog-container">
    <!-- <bp-ui-dialog v-model="open_add_daysoff_details" class="bpa-dialog bpa-dailog__small bpa-add-dayoff-dialog" title=""  :style="'top: '+days_off_top_pos+'; left: '+days_off_left_pos+';'" close-on-press-escape="true" :append-to-body="true" :show-close="false"  @open="bookingpress_enable_modal" @close="bookingpress_disable_modal"> -->
    
    <bp-ui-dialog v-model="open_add_daysoff_details" class="bpa-dialog bpa-dailog__small bpa-add-dayoff-dialog" :append-to-body="true" :teleported="true" :close-on-press-escape="true" @open="bookingpress_enable_modal" @close="bookingpress_disable_dayoff_modal" :close-on-click-modal="true">
        <div class="bpa-dialog-heading">
            <bp-ui-row type="flex">
                <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                    <h1 class="bpa-page-heading" v-if="days_off_form.is_edit==1"><?php esc_html_e(' Edit holiday', 'bookingpress-appointment-booking'); ?></h1>
                    <h1 class="bpa-page-heading" v-else><?php esc_html_e(' Add holiday', 'bookingpress-appointment-booking'); ?></h1>
                </bp-ui-col>
            </bp-ui-row>
        </div>
        <div class="bpa-dialog-body">
            <bp-ui-container class="bpa-grid-list-container bpa-add-categpry-container bpa-add-dayoff-container">
                <div class="bpa-form-row">
                    <bp-ui-row>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <bp-ui-form id="days_off_form" :rules="days_off_rules" ref="days_off_form" :model="days_off_form" label-position="top" @submit.prevent>
                                <div class="bpa-form-body-row">
                                    <bp-ui-row>
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                            <bp-ui-form-item prop="daysoff_title">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Holiday Name', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-input v-model="days_off_form.daysoff_title" class="bpa-form-control" placeholder="<?php esc_html_e('Enter holiday name', 'bookingpress-appointment-booking'); ?>" @blur="bookingpress_trim_value(days_off_form.daysoff_title)"></bp-ui-input>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly">
                                            <bp-ui-form-item>
                                                <label class="bpa-form-label bpa-custom-checkbox--is-label"> 
                                                    <bp-ui-checkbox v-model="days_off_form.is_repeat_days_off" :label="days_off_form.repeat_holiday_label" class="bpa-custom-checkbox--sm"></bp-ui-checkbox> 
                                                </label>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <?php
                                            if( class_exists( 'BookingPressPro\admin\Settings' ) && method_exists( 'BookingPressPro\admin\Settings', 'render_daysoff_additional_fields' ) ){
                                                BookingPressPro\admin\Settings::render_daysoff_additional_fields();
                                            }
                                        ?>
                                    </bp-ui-row>
                                </div>
                            </bp-ui-form>
                        </bp-ui-col>
                    </bp-ui-row>
                </div>
            </bp-ui-container>
        </div>
        <div class="bpa-dialog-footer">
            <div class="bpa-hw-right-btn-group">
                <bp-ui-button class="bpa-btn bpa-btn__small bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="save_daysoff_details('days_off_form')" :disabled="is_disabled" >                    
                <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                <div class="bpa-btn--loader__circles">                    
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                </bp-ui-button>
                <bp-ui-button class="bpa-btn bpa-btn__small" @click="delete_dayoff" v-if="days_off_form.is_edit == ''"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></bp-ui-button>            
                <bp-ui-button v-if="days_off_form.is_edit == '1'" @click="delete_dayoff" type="text" slot="reference" class="bpa-btn bpa-btn__small bpa-btn--danger-hover">
                    <?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>
                </bp-ui-button>            
            </div>
        </div>
    </bp-ui-dialog>
</div> 
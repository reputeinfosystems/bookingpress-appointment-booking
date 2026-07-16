<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<bp-ui-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="workhours_settings" label="hours-days-off" data-tab_name="workhours_settings">
    <template #label>
        <i class="material-icons-round">access_time</i>
        <?php esc_html_e('Working Hours', 'bookingpress-appointment-booking'); ?>        
    </template>
    <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_tab_loader == '1'">
        <div class="bpa-back-loader"></div>
    </div>
    <div class="bpa-general-settings-tabs--pb__card bpa-work-hours-tab--pb__card">
        <bp-ui-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <bp-ui-col :xs="12" :sm="12" :md="10" :lg="7" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Working Hours', 'bookingpress-appointment-booking'); ?></h1>                
            </bp-ui-col>
            <bp-ui-col :xs="12" :sm="12" :md="14" :lg="17" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">                                    
                    <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveEmployeeWorkhours()" :disabled="is_disabled" >                    
                      <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                      <div class="bpa-btn--loader__circles">                    
                          <div></div>
                          <div></div>
                          <div></div>
                      </div>
                    </bp-ui-button>
                </div>
            </bp-ui-col>
        </bp-ui-row>
        <div class="bpa-gs--tabs-pb__content-body">
            <div class="bpa-gs__cb--item" v-for="work_hours_day in work_hours_days_arr">
                <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :id="'weekday_'+work_hours_day.day_key">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="6" :xl="8" class="bpa-gs__cb-item-left">
                        <h4 v-if="work_hours_day.day_name == 'Monday'"><?php esc_html_e('Monday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Tuesday'"><?php esc_html_e('Tuesday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Wednesday'"><?php esc_html_e('Wednesday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Thursday'"><?php esc_html_e('Thursday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Friday'"><?php esc_html_e('Friday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Saturday'"><?php esc_html_e('Saturday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Sunday'"><?php esc_html_e('Sunday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else>{{ work_hours_day.day_name }}</h4>
                    </bp-ui-col>
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="18" :xl="16" class="bpa-gs__cb-item-right">
                        <bp-ui-row :gutter="24">
                            <bp-ui-col :xs="8" :sm="8" :md="8" :lg="8" :xl="9">
                                <bp-ui-select v-model="workhours_timings[work_hours_day.day_name].start_time" class="bpa-form-control bpa-form-control__left-icon" 
                                    placeholder="<?php esc_html_e('Start Time', 'bookingpress-appointment-booking'); ?>"
                                    popper-class="bpa-bp-ui-select--is-with-navbar" @change="bookingpress_set_workhour_value($event,work_hours_day.day_name)" filterable>
                                    <template #prefix>
                                        <span class="material-icons-round">access_time</span>
                                    </template>
                                    <bp-ui-option 
                                        v-for="work_timings in filtered_start_timings(work_hours_day)" 
                                        :key="work_timings.start_time"
                                        :label="work_timings.formatted_start_time" 
                                        :value="work_timings.start_time">
                                    </bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-col>
                            <bp-ui-col :xs="8" :sm="8" :md="8" :lg="8" :xl="9" v-if="workhours_timings[work_hours_day.day_name].start_time != 'Off'">
                                <bp-ui-select v-model="workhours_timings[work_hours_day.day_name].end_time" class="bpa-form-control bpa-form-control__left-icon" 
                                    placeholder="<?php esc_html_e('End Time', 'bookingpress-appointment-booking'); ?>"
                                    popper-class="bpa-bp-ui-select--is-with-navbar" @change="bookingpress_check_workhour_value($event,work_hours_day.day_name)" filterable>
                                    <template #prefix>
                                        <span class="material-icons-round">access_time</span>
                                    </template>
                                    <bp-ui-option 
                                        v-for="work_timings in filtered_end_timings(work_hours_day)" 
                                        :key="work_timings.end_time"
                                        :label="work_timings.formatted_end_time" 
                                        :value="work_timings.end_time">
                                    </bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-col>

                            <bp-ui-col :xs="8" :sm="8" :md="8" :lg="8" :xl="6" v-if="workhours_timings[work_hours_day.day_name].start_time != 'Off'">
                                <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" :class="(break_selected_day == work_hours_day.day_name && open_add_break_modal == true) ? 'bpa-btn--primary' : ''" @click="open_add_break_modal_func($event, work_hours_day.day_name)">
                                    <?php esc_html_e('Add Break', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                            </bp-ui-col>
                        </bp-ui-row>
                        <bp-ui-row class="bpa-wh--tabs-pb__break-hours" v-if="selected_break_timings[work_hours_day.day_name].length > 0 && workhours_timings[work_hours_day.day_name].start_time != 'Off'">
                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-break-hours-wrapper">
                                    <h4><?php esc_html_e('Breaks', 'bookingpress-appointment-booking'); ?></h4>
                                    <div class="bpa-bh--items">
                                        <div class="bpa-bh__item" v-for="(break_data,index) in work_hours_day.break_times">
											<p @click="edit_workhour_data($event,break_data.start_time, break_data.end_time, work_hours_day.day_name,index)">{{ break_data.formatted_start_time }} to {{ break_data.formatted_end_time }}</p>
                                            <span class="material-icons-round" @click="delete_breakhour(break_data.start_time, break_data.end_time, work_hours_day.day_name)">close</span>
                                        </div>
                                    </div>
                                </div>
                            </bp-ui-col>
                        </bp-ui-row>
                    </bp-ui-col>
                </bp-ui-row>
            </div>
        </div>
    </div>
</bp-ui-tab-pane>
<?php require_once __DIR__ . '/WorkhoursAddBreakModal.php'; ?>
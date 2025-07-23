<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="workhours_settings" label="hours-days-off" data-tab_name="workhours_settings">
    <span slot="label">
        <i class="material-icons-round">access_time</i>
        <?php esc_html_e('Working Hours', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card bpa-work-hours-tab--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <el-col :xs="12" :sm="12" :md="10" :lg="7" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Working Hours', 'bookingpress-appointment-booking'); ?></h1>                
            </el-col>
            <el-col :xs="12" :sm="12" :md="14" :lg="17" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">                                    
                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveEmployeeWorkhours()" :disabled="is_disabled" >                    
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
            <div class="bpa-gs__cb--item" v-for="work_hours_day in work_hours_days_arr">
                <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :id="'weekday_'+work_hours_day.day_key">
                    <el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="8" class="bpa-gs__cb-item-left">
                        <h4 v-if="work_hours_day.day_name == 'Monday'"><?php esc_html_e('Monday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Tuesday'"><?php esc_html_e('Tuesday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Wednesday'"><?php esc_html_e('Wednesday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Thursday'"><?php esc_html_e('Thursday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Friday'"><?php esc_html_e('Friday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Saturday'"><?php esc_html_e('Saturday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else-if="work_hours_day.day_name == 'Sunday'"><?php esc_html_e('Sunday', 'bookingpress-appointment-booking'); ?></h4>
                        <h4 v-else>{{ work_hours_day.day_name }}</h4>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="18" :xl="16" class="bpa-gs__cb-item-right">
                        <el-row :gutter="24">
                            <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="9">
                                <el-select v-model="workhours_timings[work_hours_day.day_name].start_time" class="bpa-form-control bpa-form-control__left-icon" 
                                    placeholder="<?php esc_html_e('Start Time', 'bookingpress-appointment-booking'); ?>"
                                    popper-class="bpa-el-select--is-with-navbar" @change="bookingpress_set_workhour_value($event,work_hours_day.day_name)" filterable>
                                    <span slot="prefix" class="material-icons-round">access_time</span>
									<el-option v-for="work_timings in work_hours_day.worktimes" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="work_timings.start_time != workhours_timings[work_hours_day.day_name].end_time || workhours_timings[work_hours_day.day_name].end_time == 'Off'"></el-option>
                                </el-select>
                            </el-col>
                            <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="9" v-if="workhours_timings[work_hours_day.day_name].start_time != 'Off'">
                                <el-select v-model="workhours_timings[work_hours_day.day_name].end_time" class="bpa-form-control bpa-form-control__left-icon" 
                                    placeholder="<?php esc_html_e('End Time', 'bookingpress-appointment-booking'); ?>"
                                    popper-class="bpa-el-select--is-with-navbar" @change="bookingpress_check_workhour_value($event,work_hours_day.day_name)" filterable>
                                    <span slot="prefix" class="material-icons-round">access_time</span>
                                    <el-option v-for="work_timings in work_hours_day.worktimes" :label="work_timings.formatted_end_time" :value="work_timings.end_time" v-if="( work_timings.end_time > workhours_timings[work_hours_day.day_name].start_time || work_timings.end_time == '24:00:00') "></el-option>
                                </el-select>
                            </el-col>
                            <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="6" v-if="workhours_timings[work_hours_day.day_name].start_time != 'Off'">
                                <el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" :class="(break_selected_day == work_hours_day.day_name && open_add_break_modal == true) ? 'bpa-btn--primary' : ''" @click="open_add_break_modal_func(event, work_hours_day.day_name)">
                                    <?php esc_html_e('Add Break', 'bookingpress-appointment-booking'); ?>
                                </el-button>
                            </el-col>
                        </el-row>
                        <el-row class="bpa-wh--tabs-pb__break-hours" v-if="selected_break_timings[work_hours_day.day_name].length > 0 && workhours_timings[work_hours_day.day_name].start_time != 'Off'">
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-break-hours-wrapper">
                                    <h4><?php esc_html_e('Breaks', 'bookingpress-appointment-booking'); ?></h4>
                                    <div class="bpa-bh--items">
                                        <div class="bpa-bh__item" v-for="(break_data,index) in work_hours_day.break_times">
											<p @click="edit_workhour_data(event,break_data.start_time, break_data.end_time, work_hours_day.day_name,index)">{{ break_data.formatted_start_time }} to {{ break_data.formatted_end_time }}</p>
                                            <span class="material-icons-round" @click="delete_breakhour(break_data.start_time, break_data.end_time, work_hours_day.day_name)">close</span>
                                        </div>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>
            </div>
        </div>
    </div>
</el-tab-pane>

<?php
if (! is_rtl() ) {
    ?>
		<el-dialog id="breaks_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-break" title="" :visible.sync="open_add_break_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display"  top="10px" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
    <?php
} else {
    ?>
		<el-dialog id="breaks_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-break" title="" :visible.sync="open_add_break_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display" top="10px" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
    <?php
}
?>
    <div class="bpa-dialog-heading">
        <el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" v-if="is_edit_break == '0'">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" v-else>
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Edit Break', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>			
        </el-row>
    </div>
    <div class="bpa-dialog-body">
        <el-container class="bpa-grid-list-container bpa-add-categpry-container">
            <div class="bpa-form-row">
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-form :rules="rules_add_break" ref="break_timings" :model="break_timings" label-position="top" @submit.native.prevent>
                            <div class="bpa-form-body-row">
                                <el-row :gutter="24">
                                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                        <el-form-item prop="start_time">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Start Time', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-select v-model="break_timings.start_time" class="bpa-form-control bpa-form-control__left-icon" filterable placeholder="<?php esc_html_e('Start Time', 'bookingpress-appointment-booking'); ?>">
                                                <span slot="prefix" class="material-icons-round">access_time</span>
												<el-option v-for="break_times in default_break_timings" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time" v-if="break_times.start_time > workhours_timings[break_selected_day].start_time && break_times.start_time < workhours_timings[break_selected_day].end_time"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                        <el-form-item prop="end_time">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('End Time', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-select v-model="break_timings.end_time" class="bpa-form-control bpa-form-control__left-icon" filterable placeholder="<?php esc_html_e('End Time', 'bookingpress-appointment-booking'); ?>">
                                                <span slot="prefix" class="material-icons-round">access_time</span>
												<el-option v-for="break_times in default_break_timings" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time" v-if="(break_times.start_time > workhours_timings[break_selected_day].start_time && break_times.start_time < workhours_timings[break_selected_day].end_time) && (break_times.start_time > break_timings.start_time)"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </div>
                        </el-form>
                    </el-col>
                </el-row>
            </div>
        </el-container>
    </div>
    <div class="bpa-dialog-footer">
        <div class="bpa-hw-right-btn-group">
            <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="savebreakdata"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></el-button>
            <el-button class="bpa-btn bpa-btn__small" @click="close_add_break_model()"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></el-button>
        </div>
    </div>
</el-dialog>

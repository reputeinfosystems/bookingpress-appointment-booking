<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body"  name ="dayoff_settings"  label="DaysOff" data-tab_name="dayoff_settings">
    <span slot="label">
        <i class="material-icons-round">event</i>
        <?php esc_html_e('Holidays', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card bpa-daysoff-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Holiday Settings', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">
                    <div class="bpa-daysoff-highlight-types-row">
                        <div class="bpa-daysoff-htr--item __bpa-is-yearly">                
                            <p><?php esc_html_e('Repeat Yearly', 'bookingpress-appointment-booking'); ?></p>
                        </div>
                        <div class="bpa-daysoff-htr--item">                
                            <p><?php esc_html_e('Once Off', 'bookingpress-appointment-booking'); ?></p>
                        </div>
                    </div>                  
                </div>
            </el-col>
        </el-row>
        <div class="bpa-gs--tabs-pb__content-body bpa-gs--tabs-pb__daysoff-content-body">
            <div class="bpa-gs__cb--item">
                <el-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-date-picker class="bpa-form-control bpa-form-control--date-picker bpa-form-control--date-picker__yearly" 
                        v-model="daysoff_default_year" type="year" :clearable="false" @change="bookingpress_daysoff_selected_year($event)"
                        :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-el-datepicker-widget-wrapper" :align="bookingpress_alignment"></el-date-picker>
                    </el-col>
                </el-row>
                <el-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row bpa-dcb__item-row">
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_0" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>                            
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_1" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_2" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_3" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_4" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_5" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_6" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_7" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_8" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>                
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_9" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_10" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <v-calendar @daymouseenter="onDragHolidayCalendar" class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_11" :attributes="attributes_range" @dayclick="onDayClickRange" :data-site-locale="site_locale" :data-weekday-masks="'WW'" :timezone="daysoff_timezone" :first-day-of-week="first_day_of_week"/>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </div>    
    </div>
</el-tab-pane>
<el-dialog custom-class="bpa-dialog bpa-dailog__small bpa-add-dayoff-dialog" title="" :visible.sync="open_add_daysoff_details" :visible.sync="centerDialogVisible" :style="'top: '+days_off_top_pos+'; left: '+days_off_left_pos+';'" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading" v-if="days_off_form.is_edit==1"><?php esc_html_e(' Edit holiday', 'bookingpress-appointment-booking'); ?></h1>
                <h1 class="bpa-page-heading" v-else><?php esc_html_e(' Add holiday', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
        </el-row>
    </div>
    <div class="bpa-dialog-body">
        <el-container class="bpa-grid-list-container bpa-add-categpry-container bpa-add-dayoff-container">
            <div class="bpa-form-row">
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-form id="days_off_form" :rules="days_off_rules" ref="days_off_form" :model="days_off_form" label-position="top" @submit.native.prevent>
                            <div class="bpa-form-body-row">
                                <el-row>
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <el-form-item prop="daysoff_title">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Holiday Name', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-input v-model="days_off_form.daysoff_title" class="bpa-form-control" placeholder="<?php esc_html_e('Enter holiday name', 'bookingpress-appointment-booking'); ?>" @blur="bookingpress_trim_value(days_off_form.daysoff_title)"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly">
                                        <el-form-item>
                                            <label class="bpa-form-label bpa-custom-checkbox--is-label"> 
                                                <el-checkbox v-model="days_off_form.is_repeat_days_off" class="bpa-custom-checkbox--sm"></el-checkbox> 
                                                <?php esc_html_e('Repeat Every Year', 'bookingpress-appointment-booking'); ?>
                                            </label>
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
            <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="save_daysoff_details('days_off_form')" :disabled="is_disabled" >                    
              <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
              <div class="bpa-btn--loader__circles">                    
                  <div></div>
                  <div></div>
                  <div></div>
              </div>
            </el-button>
            <el-button class="bpa-btn bpa-btn__small" @click="delete_dayoff" v-if="days_off_form.is_edit == ''"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></el-button>            
            <el-button v-if="days_off_form.is_edit == '1'" @click="delete_dayoff" type="text" slot="reference" class="bpa-btn bpa-btn__small bpa-btn--danger-hover">
                <?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>
            </el-button>            
        </div>
    </div>
</el-dialog>

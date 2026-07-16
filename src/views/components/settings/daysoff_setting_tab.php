<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<bp-ui-tab-pane class="bpa-tabs--v_ls__tab--pane-body"  name ="dayoff_settings"  label="DaysOff" data-tab_name="dayoff_settings">
    <template #label>
        <i class="material-icons-round">event</i>
        <?php esc_html_e('Holidays', 'bookingpress-appointment-booking'); ?>
    </template>
    <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_tab_loader == '1'">
        <div class="bpa-back-loader"></div>
    </div>
    <div class="bpa-general-settings-tabs--pb__card bpa-daysoff-tabs--pb__card">
        <bp-ui-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <bp-ui-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Holiday Settings', 'bookingpress-appointment-booking'); ?></h1>
            </bp-ui-col>
            <bp-ui-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
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
            </bp-ui-col>
        </bp-ui-row>
        <div class="bpa-gs--tabs-pb__content-body bpa-gs--tabs-pb__daysoff-content-body">
            <div class="bpa-gs__cb--item">
                <bp-ui-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <bp-ui-date-picker class="bpa-form-control bpa-form-control--date-picker bpa-form-control--date-picker__yearly" 
                        v-model="daysoff_default_year" type="year" :clearable="false" @change="bookingpress_daysoff_selected_year($event)"
                        :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-el-datepicker-widget-wrapper" :align="bookingpress_alignment"></bp-ui-date-picker>
                    </bp-ui-col>
                </bp-ui-row>
                <bp-ui-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row bpa-dcb__item-row">

                    <!-- <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <div class="bpa-daysoff-calendar-col--item vc-light" nav-visibility="hidden" ref="dayoff_calendar"></div>
                        </div>
                    </bp-ui-col> -->

                    <bp-ui-col v-for="(calendar, index) in 12" :key="'calendar_' + index" :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
                        <div class="bpa-daysoff-calendar-col">
                            <div class="bpa-daysoff-calendar-col--item vc-light" :ref="'dayoff_calendar_' + index"></div>
                        </div>
                    </bp-ui-col>                   
                </bp-ui-row>
            </div>
        </div>    
    </div>
</bp-ui-tab-pane>
<?php require_once __DIR__ . '/DayOffAddModel.php'; ?>
<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<bp-ui-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body" name="general_settings" data-tab_name="general_settings">
    <template #label>
        <i class="material-icons-round">settings</i>
        <?php esc_html_e( 'General Settings', 'bookingpress-appointment-booking' ); ?>
    </template>
    <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_tab_loader == '1'">
        <div class="bpa-back-loader"></div>
    </div>
    <div class="bpa-general-settings-tabs--pb__card">
        <bp-ui-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading <?php echo ( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_group' ) ) ? '__bpa-is-groupping' : ''; ?>">
            <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e( 'General Settings', 'bookingpress-appointment-booking' ); ?></h1>
            </bp-ui-col>
            <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">
                    <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="savegeneralSettingsData()" :disabled="is_disabled">
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
        <div class="bpa-gs--tabs-pb__content-body">
            <bp-ui-form :rules="rules_general" ref="general_setting_form" :model="general_setting_form" @submit.prevent>
                <div class="bpa-gs__cb--item">
                    <?php
                        if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_group' ) ) {
                            BookingPressPro\admin\Settings::render_general_settings_group();
                        }
                    ?>                   
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Default service duration', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="default_time_slot_step">
                                <bp-ui-select class="bpa-form-control" v-model="general_setting_form.default_time_slot_step"
                                    placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <bp-ui-option v-for="item in default_timeslot_options" :key="item.text" :label="item.text" :value="item.value"></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>

                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Default time slot step', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="default_time_slot">
                                <bp-ui-select class="bpa-form-control" v-model="general_setting_form.default_time_slot"
                                    placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <bp-ui-option v-for="item in default_timeslot_options" :key="item.text" :label="item.text" :value="item.value"></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <?php
                        if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_share_timeslot_setting' ) ) {
                            BookingPressPro\admin\Settings::render_general_settings_share_timeslot_setting();
                        }
                    ?>    
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Show time as per service duration', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item>
                                <bp-ui-switch class="bpa-swtich-control" v-model="general_setting_form.show_time_as_per_service_duration" @change="bpa_update_show_time_as_per_service_duration"></bp-ui-switch>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>

                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Timeslot Grouping Settings', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                    </bp-ui-row>

                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Afternoon Start Time', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="bpa_afternoon_start_time">
                                <bp-ui-select @change="bookingpress_timesolts_afternoon_grouping()" v-model="general_setting_form.bpa_afternoon_start_time" name="afternoon_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable popper-class="bpa-el-select--is-with-navbar">
                                    <template #prefix><span class="material-icons-round">access_time</span></template>
                                    <bp-ui-option v-for="bpa_timesolts in timeslots_grouping_list" :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time"></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>                     

                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Evening Start Time', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="bpa_evening_start_time">
                                <bp-ui-select @change="bookingpress_timesolts_evening_grouping()" v-model="general_setting_form.bpa_evening_start_time" name="afternoon_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable popper-class="bpa-el-select--is-with-navbar">
                                    <template #prefix><span class="material-icons-round">access_time</span></template>
                                    <bp-ui-option v-for="bpa_timesolts in filtered_evening_timeslots" :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time"></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Night Start Time', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="bpa_night_start_time">
                                <bp-ui-select v-model="general_setting_form.bpa_night_start_time" name="night_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable>
                                    <template #prefix><span class="material-icons-round">access_time</span></template>
                                    <bp-ui-option v-for="bpa_timesolts in filtered_night_timeslots" :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time"></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    
                    <?php  
                        global $BookingPress;
                        if( ! $BookingPress->bpa_is_pro_active() ){ ?>
                    
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Default appointment status', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="appointment_status">
                                <bp-ui-select class="bpa-form-control" v-model="general_setting_form.appointment_status"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <bp-ui-option v-for="item in default_appointment_staus" :label="item.text" :value="item.value"></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>

                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( "Appointment status paid with 'On site' payment method", 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="appointment_status">
                                <bp-ui-select class="bpa-form-control" v-model="general_setting_form.onsite_appointment_status"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <bp-ui-option v-for="item in default_appointment_staus" :label="item.text" :value="item.value"></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>     
                    <?php } ?>               

                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Default phone country code', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <?php
                            if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_default_phone_country' ) ) {
                                BookingPressPro\admin\Settings::render_general_settings_default_phone_country();
                            } else { 
                        ?>                          
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="general_setting_phone_number">
                                <bp-ui-tel-input :key="bookingpress_tel_input_settings_props.defaultCountry" v-model="general_setting_form.general_setting_phone_number" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_general_tab_phone_country_change_func($event)" v-bind="bookingpress_tel_input_settings_props" ref="bpa_tel_input_settings_field">
                                    <template v-slot:arrow-icon>
                                        <span class="material-icons-round">keyboard_arrow_down</span>
                                    </template>
                                </bp-ui-tel-input>
                            </bp-ui-form-item>
                        </bp-ui-col>
                        <?php } ?>
                    </bp-ui-row>
                    <?php if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_default_country_code' ) ) {
                            BookingPressPro\admin\Settings::render_general_settings_default_country_code();
                        }
                    ?>   
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Default items per page', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="per_page_item">
                                <bp-ui-select class="bpa-form-control" v-model="general_setting_form.per_page_item"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <bp-ui-option v-for="item in default_pagination" :key="item.text" :value="item.value"></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <?php if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_export_delimeter' ) ) {
                        BookingPressPro\admin\Settings::render_general_settings_export_delimeter();
                    }
                    ?>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Default date format', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="default_date_format">
                                <bp-ui-select class="bpa-form-control" v-model="general_setting_form.default_date_format" popper-class="bpa-el-select--is-with-navbar">
                                    <bp-ui-option label="<?php echo esc_html( 'F j, Y' ); ?>" value="F j, Y"><?php echo esc_html( 'F j, Y' ); ?></bp-ui-option>
                                    <bp-ui-option label="<?php echo esc_html( 'Y-m-d' ); ?>" value="Y-m-d"><?php echo esc_html( 'Y-m-d' ); ?></bp-ui-option>
                                    <bp-ui-option label="<?php echo esc_html( 'm/d/Y' ); ?>" value="m/d/Y"><?php echo esc_html( 'm/d/Y' ); ?></bp-ui-option>
                                    <bp-ui-option label="<?php echo esc_html( 'd/m/Y' ); ?>" value="d/m/Y"><?php echo esc_html( 'd/m/Y' ); ?></bp-ui-option>
                                    <bp-ui-option label="<?php echo esc_html( 'd.m.Y' ); ?>" value="d.m.Y"><?php echo esc_html( 'd.m.Y' ); ?></bp-ui-option>
                                    <bp-ui-option label="<?php echo esc_html( 'd-m-Y' ); ?>" value="d-m-Y"><?php echo esc_html( 'd-m-Y' ); ?></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>

                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Default Time Format', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="default_time_format">
                                <bp-ui-select class="bpa-form-control" v-model="general_setting_form.default_time_format" popper-class="bpa-el-select--is-with-navbar">
                                    <bp-ui-option label="<?php esc_html_e( '12 hour Format', 'bookingpress-appointment-booking' ); ?>" value="g:i a"><?php esc_html_e( '12 hour Format', 'bookingpress-appointment-booking' ); ?></bp-ui-option>
                                    <bp-ui-option label="<?php esc_html_e( '24 hour Format', 'bookingpress-appointment-booking' ); ?>" value="H:i"><?php esc_html_e( '24 hour Format', 'bookingpress-appointment-booking' ); ?></bp-ui-option>
                                    <bp-ui-option label="<?php esc_html_e( 'Inherit From Wordpress', 'bookingpress-appointment-booking' ); ?>" value="bookingpress-wp-inherit-time-format"><?php esc_html_e( 'Inherit From Wordpress', 'bookingpress-appointment-booking' ); ?></bp-ui-option>
                                </bp-ui-select>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>

                    <?php if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_after_def_time_format' ) ) {
                        BookingPressPro\admin\Settings::render_general_settings_after_def_time_format();
                    } else {
                    ?>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Share time slot between all services', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="share_timeslot_between_services">
                                <bp-ui-switch class="bpa-swtich-control" v-model="general_setting_form.share_timeslot_between_services"></bp-ui-switch>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <?php } ?>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Load JS &amp; CSS in all pages', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item>
                                <bp-ui-switch class="bpa-swtich-control" v-model="general_setting_form.load_js_css_all_pages"></bp-ui-switch>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Help us improve BookingPress by sending anonymous usage stats', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item>
                                <bp-ui-switch class="bpa-swtich-control" v-model="general_setting_form.anonymous_data"></bp-ui-switch>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Enable Debug Mode', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item>
                                <bp-ui-switch class="bpa-swtich-control" v-model="general_setting_form.debug_mode"></bp-ui-switch>
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row> 

                    <?php if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_bookingpress_loader' ) ) {
                        BookingPressPro\admin\Settings::render_general_settings_bookingpress_loader();
                    } ?>
                </div>

                <?php if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_outside_section' ) ) {
                    BookingPressPro\admin\Settings::render_general_settings_outside_section();
                } ?>                
            </bp-ui-form>

            <?php if( !class_exists( 'BookingPressPro\admin\Settings') && !method_exists( 'BookingPressPro\admin\Settings', 'render_general_settings_bookingpress_loader' ) ) {                
            ?>
                <div class="bpa-gs--tabs-pb__content-body">
                    <bp-ui-form id="customer_setting_form" ref="customer_setting_form" @submit.prevent>
                        <div class="bpa-gs__cb--item">
                            <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                    <h4><?php esc_html_e( 'Create WordPress user upon appointment booking', 'bookingpress-appointment-booking' ); ?></h4>
                                </bp-ui-col>
                                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                    <bp-ui-form-item>
                                        <bp-ui-switch class="bpa-swtich-control" v-model="customer_setting_form.allow_wp_user_create"></bp-ui-switch>
                                    </bp-ui-form-item>
                                </bp-ui-col>
                            </bp-ui-row>

                            <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="1 == customer_setting_form.allow_wp_user_create">
                                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                    <h4><?php esc_html_e( 'Auto login user after successful booking', 'bookingpress-appointment-booking' ); ?></h4>
                                </bp-ui-col>
                                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                    <bp-ui-form-item>
                                        <bp-ui-switch class="bpa-swtich-control" v-model="customer_setting_form.allow_autologin_user"></bp-ui-switch>
                                    </bp-ui-form-item>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>
                    </bp-ui-form>
                </div>
            <?php } ?>
        </div>
    </div>
</bp-ui-tab-pane>
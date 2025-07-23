<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body" name ="general_settings"  data-tab_name="general_settings">
    <span slot="label">
        <i class="material-icons-round">settings</i>
        <?php esc_html_e('General Settings', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
			<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('General Settings', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">        
                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="savegeneralSettingsData()" :disabled="is_disabled" >                    
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
            <el-form :rules="rules_general" ref="general_setting_form" :model="general_setting_form" @submit.native.prevent>
                <div class="bpa-gs__cb--item">
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Default service duration', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">                
                            <el-form-item prop="default_time_slot_step">
                                <el-select class="bpa-form-control" v-model="general_setting_form.default_time_slot_step" 
                                    placeholder="<?php esc_html_e('Minutes', 'bookingpress-appointment-booking'); ?>"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <el-option v-for="item in default_timeslot_options" :key="item.text" :label="item.text" :value="item.value"></el-option>    
                                </el-select>                        
                            </el-form-item>
                        </el-col>
                    </el-row>            
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Default time slot step', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="default_time_slot">
                                <el-select class="bpa-form-control" v-model="general_setting_form.default_time_slot" placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
									popper-class="bpa-el-select--is-with-navbar">
                                    <el-option v-for="item in default_timeslot_options" :key="item.text" :label="item.text" :value="item.value"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Show time as per service duration', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item>
                                <el-switch class="bpa-swtich-control" v-model="general_setting_form.show_time_as_per_service_duration">                                    
                                </el-switch>
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <!-- timesolts grouping start-->
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Timeslot Grouping Settings', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                    </el-row>
                     
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Afternoon Start Time', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                       
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="bpa_afternoon_start_time">
                                <el-select @change="bookingpress_timesolts_afternoon_grouping()" v-model="general_setting_form.bpa_afternoon_start_time" name ="afternoon_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable popper-class="bpa-el-select--is-with-navbar"> 
									<span slot="prefix" class="material-icons-round">access_time</span>
									<el-option v-for="bpa_timesolts in timeslots_grouping_list"  :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time"></el-option >
								</el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Evening Start Time', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                       
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="bpa_evening_start_time">
                                <el-select @change="bookingpress_timesolts_evening_grouping()" v-model="general_setting_form.bpa_evening_start_time" name ="afternoon_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable popper-class="bpa-el-select--is-with-navbar"> 
									<span slot="prefix" class="material-icons-round">access_time</span>
									<el-option v-for="bpa_timesolts in timeslots_grouping_list"  :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time" v-if="bpa_timesolts.start_time > general_setting_form.bpa_afternoon_start_time"></el-option >
								</el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Night Start Time', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="bpa_night_start_time">
                                <el-select v-model="general_setting_form.bpa_night_start_time" name ="night_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
									<span slot="prefix" class="material-icons-round">access_time</span>
									<el-option v-for="bpa_timesolts in timeslots_grouping_list"  :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time" v-if="(bpa_timesolts.start_time > general_setting_form.bpa_evening_start_time && general_setting_form.bpa_evening_start_time != '')"></el-option >
								</el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <!-- timesolts grouping end -->

                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Default appointment status', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="appointment_status">
                                <el-select class="bpa-form-control" v-model="general_setting_form.appointment_status"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <el-option v-for="item in default_appointment_staus" :label="item.text" :value="item.value"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e("Appointment status paid with 'On site' payment method", "bookingpress-appointment-booking"); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="appointment_status">    
                                <el-select class="bpa-form-control" v-model="general_setting_form.onsite_appointment_status"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <el-option v-for="item in default_appointment_staus" :label="item.text" :value="item.value"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>        
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                    <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e('Default phone country code', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="general_setting_phone_number" >
                                <vue-tel-input v-model="general_setting_form.general_setting_phone_number" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_general_tab_phone_country_change_func($event)" v-bind="bookingpress_tel_input_settings_props" ref="bpa_tel_input_settings_field">
                                    <template v-slot:arrow-icon>
                                        <span class="material-icons-round">keyboard_arrow_down</span>
                                    </template>
                                </vue-tel-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Default items per page', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="per_page_item">
                                <el-select class="bpa-form-control" v-model="general_setting_form.per_page_item"
                                    popper-class="bpa-el-select--is-with-navbar">
                                    <el-option v-for="item in default_pagination" :key="item.text" :value="item.value"></el-option>
                                </el-select>
                            </el-form-item>    
                        </el-col>
                    </el-row>            
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e( 'Default date format', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="default_time_slot">
                                <el-select class="bpa-form-control" v-model="general_setting_form.default_date_format" 	popper-class="bpa-el-select--is-with-navbar">
                                    <el-option label="<?php echo esc_html('F j, Y'); ?>" value="F j, Y"><?php echo esc_html('F j, Y'); ?></el-option>
                                    <el-option label="<?php echo esc_html('Y-m-d'); ?>" value="Y-m-d"><?php echo esc_html('Y-m-d'); ?></el-option>
                                    <el-option label="<?php echo esc_html('m/d/Y'); ?>" value="m/d/Y"><?php echo esc_html('m/d/Y'); ?></el-option>
                                    <el-option label="<?php echo esc_html('d/m/Y'); ?>" value="d/m/Y"><?php echo esc_html('d/m/Y'); ?></el-option>
                                    <el-option label="<?php echo esc_html('d.m.Y'); ?>" value="d.m.Y"><?php echo esc_html('d.m.Y'); ?></el-option>
                                    <el-option label="<?php echo esc_html('d-m-Y'); ?>" value="d-m-Y"><?php echo esc_html('d-m-Y'); ?></el-option>
                                </el-select>
                            </el-form-item>    
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Default Time Format', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="default_time_slot">
									<el-select class="bpa-form-control" v-model="general_setting_form.default_time_format" popper-class="bpa-el-select--is-with-navbar">
										<el-option label="<?php esc_html_e('12 hour Format','bookingpress-appointment-booking'); ?>" value="g:i a"><?php esc_html_e('12 hour Format','bookingpress-appointment-booking'); ?></el-option>
										<el-option label="<?php esc_html_e('24 hour Format','bookingpress-appointment-booking'); ?>" value="H:i"><?php esc_html_e('24 hour Format','bookingpress-appointment-booking'); ?></el-option>
                                        <el-option label="<?php esc_html_e('Inherit From Wordpress','bookingpress-appointment-booking'); ?>" value="bookingpress-wp-inherit-time-format"><?php esc_html_e('Inherit From Wordpress','bookingpress-appointment-booking'); ?></el-option>
									</el-select>                        
								</el-form-item>
							</el-col>
						</el-row>            
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e('Share time slot between all services', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item prop="share_timeslot_between_services">
                                <el-switch class="bpa-swtich-control" v-model="general_setting_form.share_timeslot_between_services">
                                </el-switch>    
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e('Load JS &amp; CSS in all pages', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item>
                                <el-switch class="bpa-swtich-control" v-model="general_setting_form.load_js_css_all_pages">
                                </el-switch>    
                            </el-form-item>
                        </el-col>
                    </el-row>		                 
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Help us improve BookingPress by sending anonymous usage stats', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item>
                                <el-switch class="bpa-swtich-control" v-model="general_setting_form.anonymous_data">
                                </el-switch>	
                            </el-form-item>
                        </el-col>
                    </el-row>	
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Enable Debug Mode', 'bookingpress-appointment-booking' ); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                            <el-form-item>
                                <el-switch class="bpa-swtich-control" v-model="general_setting_form.debug_mode">
                                </el-switch>	
                            </el-form-item>
                        </el-col>
                    <el-row>					
                </div>
            </el-form>
            <div class="bpa-gs--tabs-pb__content-body">    
                <el-form id="customer_setting_form" ref="customer_setting_form" @submit.native.prevent>
                    <div class="bpa-gs__cb--item">
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                <h4> <?php esc_html_e('Create WordPress user upon appointment booking', 'bookingpress-appointment-booking'); ?></h4>
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">                
                                <el-form-item>
                                    <el-switch class="bpa-swtich-control" v-model="customer_setting_form.allow_wp_user_create" ></el-switch>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="1 == customer_setting_form.allow_wp_user_create">
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                <h4><?php esc_html_e( 'Auto login user after successful booking', 'bookingpress-appointment-booking' ); ?></h4>
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">                
                                <el-form-item>
                                    <el-switch class="bpa-swtich-control" v-model="customer_setting_form.allow_autologin_user" ></el-switch>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                <el-form>
            </div>    
        </div>
    </div>
</el-tab-pane>



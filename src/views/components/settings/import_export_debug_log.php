<div class="bpa-gs__cb--item">
    <div class="bpa-gs__cb--item-heading">
        <h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?></h4>
    </div>
    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <bp-ui-row type="flex" class="bpa-debug-item__body">
                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                    <h4> <?php esc_html_e( 'Export Logs', 'bookingpress-appointment-booking' ); ?></h4>
                </bp-ui-col>
                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                    <bp-ui-form-item>
                        <bp-ui-switch class="bpa-swtich-control" v-model="debug_log_setting_form.migration_tool_debug_logs"></bp-ui-switch>
                    </bp-ui-form-item>
                </bp-ui-col>
            </bp-ui-row>
            <bp-ui-row>
                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.migration_tool_debug_logs == true">
                        <div class="bpa-di__btn-item">
                            <bp-ui-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('migration_tool_debug_logs', '', '<?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?>')" ><?php esc_html_e( 'View log', 'bookingpress-appointment-booking' ); ?></bp-ui-button>
                        </div>
                        <div class="bpa-di__btn-item">
                            <bp-ui-popover placement="bottom" width="450" trigger="click" :teleported="true">
                                <div class="bpa-dialog-download"> 
                                    <bp-ui-row type="flex">
                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">			
                                            <label for="start_time" class="bp-ui-form-item__label">
                                                <span class="bpa-form-label"><?php esc_html_e( 'Select log duration to download', 'bookingpress-appointment-booking' ); ?></span>
                                            </label>			
                                        </bp-ui-col>			
                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">											
                                            <bp-ui-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon">	
                                                <bp-ui-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></bp-ui-option>
                                            </bp-ui-select>										
                                        </bp-ui-col>		
                                    </bp-ui-row>										
                                    <bp-ui-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >											
                                            <bp-ui-date-picker popper-class="bpa-bp-ui-select--is-with-modal" class="bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" :clearable="false" value-format="yyyy-MM-dd" :first-day-of-week="bookingpress_start_of_week" range-separator=" - "> </bp-ui-date-picker>
                                        </bp-ui-col>
                                    </bp-ui-row>
                                    <bp-ui-row>													
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >										
                                            <bp-ui-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('migration_tool_debug_logs', select_download_log, download_log_daterange)" :disabled="is_disabled" >
                                                <span class="bpa-btn__label"><?php esc_html_e( 'Download', 'bookingpress-appointment-booking' ); ?></span>
                                                <div class="bpa-btn--loader__circles">
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                </div>
                                            </bp-ui-button>	
                                        </bp-ui-col>
                                    </bp-ui-row>	
                                </div>
                                <template #reference>
                                    <bp-ui-button class="bpa-btn bpa-btn__small"><?php esc_html_e( 'Download Log', 'bookingpress-appointment-booking' ); ?></bp-ui-button>
                                </template>
                            </bp-ui-popover>	
                        </div>
                        <div class="bpa-di__btn-item">
                            <bp-ui-popconfirm 
                                confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                icon="false" 
                                title="<?php esc_html_e( 'Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking' ); ?>"
                                @confirm="bookingpess_clear_bebug_log('migration_tool_debug_logs')"
                                confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                cancel-button-type="bpa-btn bpa-btn__small" >
                                <bp-ui-button class="bpa-btn bpa-btn__small"><?php esc_html_e( 'Clear Log', 'bookingpress-appointment-booking' ); ?></bp-ui-button>                                
                            </bp-ui-popconfirm>
                        </div>
                    </div>
                </bp-ui-col>
            </bp-ui-row>
        </bp-ui-col>
    </bp-ui-row>
    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <bp-ui-row type="flex" class="bpa-debug-item__body">
                    <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                        <h4> <?php esc_html_e( 'Import Logs', 'bookingpress-appointment-booking' ); ?></h4>
                    </bp-ui-col>
                    <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                        <bp-ui-form-item>
                            <bp-ui-switch class="bpa-swtich-control" v-model="debug_log_setting_form.migration_tool_import_debug_logs"></bp-ui-switch>
                        </bp-ui-form-item>
                    </bp-ui-col>
            </bp-ui-row>
            <bp-ui-row>
                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.migration_tool_import_debug_logs == true">
                        <div class="bpa-di__btn-item">
                            <bp-ui-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('migration_tool_import_debug_logs', '', '<?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?>')" ><?php esc_html_e( 'View log', 'bookingpress-appointment-booking' ); ?></bp-ui-button>
                        </div>
                        <div class="bpa-di__btn-item">
                            <bp-ui-popover placement="bottom" width="450" trigger="click">
                                <div class="bpa-dialog-download"> 
                                    <bp-ui-row type="flex">
                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">			
                                            <label for="start_time" class="bp-ui-form-item__label">
                                                <span class="bpa-form-label"><?php esc_html_e( 'Select log duration to download', 'bookingpress-appointment-booking' ); ?></span>
                                            </label>			
                                        </bp-ui-col>			
                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">											
                                            <bp-ui-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon">	
                                                <bp-ui-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></bp-ui-option>
                                            </bp-ui-select>										
                                        </bp-ui-col>		
                                    </bp-ui-row>										
                                    <bp-ui-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >											
                                            <bp-ui-date-picker popper-class="bpa-bp-ui-select--is-with-modal" class="bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" :clearable="false" value-format="yyyy-MM-dd" :first-day-of-week="bookingpress_start_of_week" range-separator=" - "> </bp-ui-date-picker>
                                        </bp-ui-col>
                                    </bp-ui-row>
                                    <bp-ui-row>													
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >										
                                            <bp-ui-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('migration_tool_import_debug_logs', select_download_log, download_log_daterange)" :disabled="is_disabled" >
                                                <span class="bpa-btn__label"><?php esc_html_e( 'Download', 'bookingpress-appointment-booking' ); ?></span>
                                                <div class="bpa-btn--loader__circles">
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                </div>
                                            </bp-ui-button>	
                                        </bp-ui-col>
                                    </bp-ui-row>	
                                </div>
                                <template #reference>
                                    <bp-ui-button class="bpa-btn bpa-btn__small" slot="reference" ><?php esc_html_e( 'Download Log', 'bookingpress-appointment-booking' ); ?></bp-ui-button>
                                </template>
                            </bp-ui-popover>	
                        </div>
                        <div class="bpa-di__btn-item">
                            <bp-ui-popconfirm 
                                confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                icon="false" 
                                title="<?php esc_html_e( 'Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking' ); ?>"
                                @confirm="bookingpess_clear_bebug_log('migration_tool_import_debug_logs')"
                                confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                cancel-button-type="bpa-btn bpa-btn__small" >
                                <bp-ui-button class="bpa-btn bpa-btn__small"><?php esc_html_e( 'Clear Log', 'bookingpress-appointment-booking' ); ?></bp-ui-button>                                
                            </bp-ui-popconfirm>
                        </div>
                    </div>
                </bp-ui-col>
            </bp-ui-row>
        </bp-ui-col>
    </bp-ui-row>
</div> 
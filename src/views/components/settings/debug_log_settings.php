<?php
    if ( ! defined( 'ABSPATH' ) ) { exit; }
    global $bookingpress_common_date_format;
?>
<bp-ui-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="debug_log_settings"  label="Debug Log" data-tab_name="debug_log_settings">
    <template #label>
        <i class="material-icons-round">bug_report</i>
        <?php esc_html_e('Debug Log', 'bookingpress-appointment-booking'); ?>
    </template>
    <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_tab_loader == '1'">
        <div class="bpa-back-loader"></div>
    </div>
    <div class="bpa-general-settings-tabs--pb__card">
        <bp-ui-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping">
            <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Debug Log Settings', 'bookingpress-appointment-booking'); ?></h1>
            </bp-ui-col>
            <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">                        
                    <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('debug_log_setting_form','debug_log_setting')" :disabled="is_disabled" >                    
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
        <div class="bpa-gs--tabs-pb__content-body bpa-gs--deubg-log__content-body">
            <bp-ui-form ref="debug_log_setting_form" :model="debug_log_setting_form" @submit.native.prevent>
                <div class="bpa-gs__cb--item">
                    <div class="bpa-gs__cb--item-heading">
                        <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Payment Debug Logs', 'bookingpress-appointment-booking'); ?></h4>
                    </div>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <bp-ui-row type="flex" class="bpa-debug-item__body">
                                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                    <h4> <?php esc_html_e('On Site method', 'bookingpress-appointment-booking'); ?></h4>
                                </bp-ui-col>
                                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                    <bp-ui-form-item>
                                        <bp-ui-switch class="bpa-swtich-control" v-model="debug_log_setting_form.on_site_payment"></bp-ui-switch>
                                    </bp-ui-form-item>
                                </bp-ui-col>
                            </bp-ui-row>
                            <bp-ui-row>
                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.on_site_payment == true">
                                        <div class="bpa-di__btn-item">
                                            <bp-ui-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('on-site','','<?php esc_html_e('On-Site', 'bookingpress-appointment-booking'); ?>')"><?php esc_html_e('View log', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                                        </div>
                                        <div class="bpa-di__btn-item">
                                            <bp-ui-popover placement="bottom" width="450" trigger="click" :teleported="true" v-model:visible="download_on_site_popover_visible">
                                                <div class="bpa-dialog-download"> 
                                                    <bp-ui-row type="flex">
                                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">            
                                                            <label for="start_time" class="bp-ui-form-item__label">
                                                                <span class="bpa-form-label"><?php esc_html_e('Select log duration to download', 'bookingpress-appointment-booking'); ?></span>
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
                                                            <bp-ui-date-picker range-separator=" - " @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" :clearable="false" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>"   popper-class="bpa-bp-ui-select--is-with-modal"  value-format="YYYY-MM-DD" :first-day-of-week="bookingpress_start_of_week"> </bp-ui-date-picker>
                                                        </bp-ui-col>
                                                    </bp-ui-row>
                                                    <bp-ui-row>                                                    
                                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >                                        
                                                            <bp-ui-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('on-site',select_download_log,download_log_daterange)" :disabled="is_disabled" >
                                                                <span class="bpa-btn__label"><?php esc_html_e('Download', 'bookingpress-appointment-booking'); ?></span>
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
                                                    <bp-ui-button @click="download_on_site_popover_visible = !download_on_site_popover_visible" class="bpa-btn bpa-btn__small"><?php esc_html_e('Download log', 'bookingpress-appointment-booking'); ?></bp-ui-button>                                                    
                                                </template>
                                            </bp-ui-popover>
                                        </div>
                                        <div class="bpa-di__btn-item">
                                            <bp-ui-popconfirm confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                icon="false"  title="<?php esc_html_e('Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking'); ?>" @confirm="bookingpess_clear_bebug_log('on-site')" confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger"  cancel-button-type="bpa-btn bpa-btn__small" >
                                                <bp-ui-button class="bpa-btn bpa-btn__small">
                                                    <?php esc_html_e('Clear log', 'bookingpress-appointment-booking'); ?>
                                                </bp-ui-button>         
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
                                    <h4><?php echo 'PayPal '.esc_html__('method', 'bookingpress-appointment-booking'); ?></h4>
                                </bp-ui-col>
                                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                    <bp-ui-form-item prop="on_site_payment">
                                        <bp-ui-switch class="bpa-swtich-control" v-model="debug_log_setting_form.paypal_payment"></bp-ui-switch>
                                    </bp-ui-form-item>                        
                                </bp-ui-col>
                            </bp-ui-row>
                            <bp-ui-row>
                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.paypal_payment == true">
                                        <div class="bpa-di__btn-item">
                                            <bp-ui-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('paypal', '', '<?php esc_html_e('Paypal', 'bookingpress-appointment-booking'); ?>')"><?php esc_html_e('View log', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                                        </div>
                                        <div class="bpa-di__btn-item">
                                            <bp-ui-popover placement="bottom" width="450" trigger="click" :teleported="true" v-model:visible="download_paypal_popover_visible">
                                                <div class="bpa-dialog-download">
                                                    <bp-ui-row type="flex">
                                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">        
                                                            <label for="start_time" class="bp-ui-form-item__label">
                                                                <span class="bpa-form-label"><?php esc_html_e('Select log duration to download', 'bookingpress-appointment-booking'); ?></span>
                                                            </label>        
                                                        </bp-ui-col>            
                                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">                                            
                                                            <bp-ui-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon" >        
                                                                <bp-ui-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></bp-ui-option>
                                                            </bp-ui-select>                                        
                                                        </bp-ui-col>        
                                                    </bp-ui-row>                                                                    
                                                    <bp-ui-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="24" :xl="24" >                                                
                                                            <bp-ui-date-picker range-separator=" - " @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control--date-range-picker bpa-select-download-log" :format="bpa_date_common_date_format" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" :clearable="false" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>"    popper-class="bpa-debug-log-dp .bpa-bp-ui-select--is-with-navbar" value-format="yyyy-MM-dd" :first-day-of-week="bookingpress_start_of_week"> </bp-ui-date-picker>
                                                        </bp-ui-col>
                                                    </bp-ui-row>
                                                    <bp-ui-row :gutter="24">
                                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >                                            
                                                            <bp-ui-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('paypal',select_download_log,download_log_daterange)" :disabled="is_disabled" >
                                                                <span class="bpa-btn__label"><?php esc_html_e('Download', 'bookingpress-appointment-booking'); ?></span>
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
                                                    <bp-ui-button @click="download_paypal_popover_visible = !download_paypal_popover_visible" class="bpa-btn bpa-btn__small"><?php esc_html_e('Download log', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                                                </template>
                                            </bp-ui-popover>
                                        </div>
                                        <div class="bpa-di__btn-item">
                                            <bp-ui-popconfirm 
                                                confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                icon="false" 
                                                title="<?php esc_html_e('Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking'); ?>" 
                                                @confirm="bookingpess_clear_bebug_log('paypal')"
                                                confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                cancel-button-type="bpa-btn bpa-btn__small" >                                    
                                                <bp-ui-button class="bpa-btn bpa-btn__small"><?php esc_html_e('Clear log', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                                            </bp-ui-popconfirm>
                                        </div>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </bp-ui-col>
                    </bp-ui-row>

                    <?php
                    if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_debug_logs_paymemt_logs_outside' ) ) {
                        BookingPressPro\admin\Settings::render_debug_logs_paymemt_logs_outside();
                    }
                    ?>
                </div>  
                <?php
                if( class_exists( 'BookingPressPro\admin\Settings') && method_exists( 'BookingPressPro\admin\Settings', 'render_debug_logs_outside' ) ) {
                    BookingPressPro\admin\Settings::render_debug_logs_outside();
                }
                ?>
                <?php
                if( class_exists( 'BookingPress\admin\Settings') && method_exists( 'BookingPress\admin\Settings', 'render_import_export_debug_logs' ) ) {
                    BookingPress\admin\Settings::render_import_export_debug_logs();
                }
                ?>
                <?php //do_action('bookingpress_lite_add_debug_log_outside'); ?>
            </bp-ui-form>
        </div>            
    </div>
</bp-ui-tab-pane>    
<?php require_once __DIR__ . '/ViewDebugLogModel.php'; ?>

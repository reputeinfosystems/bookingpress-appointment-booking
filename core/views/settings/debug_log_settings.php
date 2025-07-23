<?php
    if ( ! defined( 'ABSPATH' ) ) { exit; }
    global $bookingpress_common_date_format;
?>
<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="debug_log_settings"  label="Debug Log" data-tab_name="debug_log_settings">
    <span slot="label">
        <i class="material-icons-round">bug_report</i>
        <?php esc_html_e('Debug Log', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping">
            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Debug Log Settings', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">                        
                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('debug_log_setting_form','debug_log_setting')" :disabled="is_disabled" >                    
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
        <div class="bpa-gs--tabs-pb__content-body bpa-gs--deubg-log__content-body">
            <el-form ref="debug_log_setting_form" :model="debug_log_setting_form" @submit.native.prevent>
                <div class="bpa-gs__cb--item">
                    <div class="bpa-gs__cb--item-heading">
                        <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Payment Debug Logs', 'bookingpress-appointment-booking'); ?></h4>
                    </div>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-row type="flex" class="bpa-debug-item__body">
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                    <h4> <?php esc_html_e('On Site method', 'bookingpress-appointment-booking'); ?></h4>
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                    <el-form-item>
                                        <el-switch class="bpa-swtich-control" v-model="debug_log_setting_form.on_site_payment"></el-switch>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.on_site_payment == true">
                                        <div class="bpa-di__btn-item">
                                            <el-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('on-site','','<?php esc_html_e('On-Site', 'bookingpress-appointment-booking'); ?>')"><?php esc_html_e('View log', 'bookingpress-appointment-booking'); ?></el-button>
                                        </div>
                                        <div class="bpa-di__btn-item">
                                            <el-popover
                                                placement="bottom"
                                                width="450"
                                                trigger="click" >
                                                <div class="bpa-dialog-download"> 
                                                    <el-row type="flex">
                                                        <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">            
                                                            <label for="start_time" class="el-form-item__label">
                                                                <span class="bpa-form-label"><?php esc_html_e('Select log duration to download', 'bookingpress-appointment-booking'); ?></span>
                                                            </label>            
                                                        </el-col>            
                                                        <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">                                            
                                                            <el-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon">    
                                                                <el-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></el-option>
                                                            </el-select>                                        
                                                        </el-col>        
                                                    </el-row>                                        
                                                    <el-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >                                            
                                                            <el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" :clearable="false" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>"   range-separator=" - " popper-class="bpa-debug-log-dp .bpa-el-select--is-with-navbar bpa-date-range-picker-widget-wrapper" :clearable="false" value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"> </el-date-picker>
                                                        </el-col>
                                                    </el-row>
                                                    <el-row>                                                    
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >                                        
                                                            <el-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('on-site',select_download_log,download_log_daterange)" :disabled="is_disabled" >
                                                                <span class="bpa-btn__label"><?php esc_html_e('Download', 'bookingpress-appointment-booking'); ?></span>
                                                                <div class="bpa-btn--loader__circles">
                                                                    <div></div>
                                                                    <div></div>
                                                                    <div></div>
                                                                </div>
                                                            </el-button>    
                                                        </el-col>
                                                    </el-row>    
                                                </div>
                                                <el-button class="bpa-btn bpa-btn__small" slot="reference"><?php esc_html_e('Download log', 'bookingpress-appointment-booking'); ?></el-button>
                                            </el-popover>
                                        </div>
                                        <div class="bpa-di__btn-item">
                                            <el-popconfirm 
                                                confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                icon="false" 
                                                title="<?php esc_html_e('Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking'); ?>" 
                                                @confirm="bookingpess_clear_bebug_log('on-site')"
                                                confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                cancel-button-type="bpa-btn bpa-btn__small" >                                                                                    
                                                <el-button class="bpa-btn bpa-btn__small" slot="reference"><?php esc_html_e('Clear log', 'bookingpress-appointment-booking'); ?></el-button>         
                                            </el-popconfirm>
                                        </div>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-row type="flex" class="bpa-debug-item__body">
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                    <h4><?php echo 'PayPal '.esc_html__('method', 'bookingpress-appointment-booking'); ?></h4>
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                    <el-form-item prop="on_site_payment">
                                        <el-switch class="bpa-swtich-control" v-model="debug_log_setting_form.paypal_payment"></el-switch>
                                    </el-form-item>                        
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.paypal_payment == true">
                                        <div class="bpa-di__btn-item">
                                            <el-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('paypal', '', '<?php esc_html_e('Paypal', 'bookingpress-appointment-booking'); ?>')"><?php esc_html_e('View log', 'bookingpress-appointment-booking'); ?></el-button>
                                        </div>
                                        <div class="bpa-di__btn-item">
                                            <el-popover
                                                placement="bottom"
                                                width="450"                                    
                                                trigger="click" >
                                                <div class="bpa-dialog-download">
                                                    <el-row type="flex">
                                                        <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">        
                                                            <label for="start_time" class="el-form-item__label">
                                                                <span class="bpa-form-label"><?php esc_html_e('Select log duration to download', 'bookingpress-appointment-booking'); ?></span>
                                                            </label>        
                                                        </el-col>            
                                                        <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">                                            
                                                            <el-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon" >        
                                                                <el-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></el-option>
                                                            </el-select>                                        
                                                        </el-col>        
                                                    </el-row>                                                                    
                                                    <el-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                                        <el-col :xs="24" :sm="24" :md="12" :lg="24" :xl="24" >                                                
                                                            <el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control--date-range-picker bpa-select-download-log" :format="bpa_date_common_date_format" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" :clearable="false" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>"    popper-class="bpa-debug-log-dp .bpa-el-select--is-with-navbar" range-separator="<?php esc_html_e('To', 'bookingpress-appointment-booking'); ?>" value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"> </el-date-picker>
                                                        </el-col>
                                                    </el-row>
                                                    <el-row :gutter="24">
                                                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >                                            
                                                            <el-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('paypal',select_download_log,download_log_daterange)" :disabled="is_disabled" >
                                                                <span class="bpa-btn__label"><?php esc_html_e('Download', 'bookingpress-appointment-booking'); ?></span>
                                                                <div class="bpa-btn--loader__circles">
                                                                    <div></div>
                                                                    <div></div>
                                                                    <div></div>
                                                                </div>
                                                            </el-button>    
                                                        </el-col>
                                                    </el-row>    
                                                </div>
                                                <el-button class="bpa-btn bpa-btn__small" slot="reference"><?php esc_html_e('Download log', 'bookingpress-appointment-booking'); ?></el-button>
                                            </el-popover>
                                        </div>
                                        <div class="bpa-di__btn-item">
                                            <el-popconfirm 
                                                confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                icon="false" 
                                                title="<?php esc_html_e('Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking'); ?>" 
                                                @confirm="bookingpess_clear_bebug_log('paypal')"
                                                confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                cancel-button-type="bpa-btn bpa-btn__small" >                                    
                                                <el-button class="bpa-btn bpa-btn__small" slot="reference"><?php esc_html_e('Clear log', 'bookingpress-appointment-booking'); ?></el-button>
                                            </el-popconfirm>
                                        </div>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-col>
                    </el-row>
                </div>  
                <?php do_action('bookingpress_lite_add_debug_log_outside'); ?>
            </el-form>
        </div>            
    </div>
</el-tab-pane>    
<el-dialog custom-class="bpa-dialog bpa-dialog--debug-log" title="" :visible.sync="open_display_log_modal" :visible.sync="centerDialogVisible" >
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading"><?php esc_html_e('Debug Logs', 'bookingpress-appointment-booking'); ?> ({{open_view_model_gateway}})</h1>
            </el-col>
        </el-row>
    </div>    
    <div class="bpa-back-loader-container" v-if="is_display_loader_view == '1'">
        <div class="bpa-back-loader"></div>
    </div>    
    <div class="bpa-dialog-body">        
        <el-row type="flex" v-if="items.length == 0">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <div class="bpa-data-empty-view">
                    <div class="bpa-ev-left-vector">
                        <picture>
                            <source srcset="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp'); ?>" type="image/webp">
                            <img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png'); ?>">
                        </picture>
                    </div>
                    <div class="bpa-ev-right-content">                    
                        <h4><?php esc_html_e('No Record Found!', 'bookingpress-appointment-booking'); ?></h4>                        
                    </div>
                </div>
            </el-col>
        </el-row>
        <el-row v-if="items.length > 0"> 
            <el-column :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-container class="bpa-grid-list-container">                    
                    <el-table ref="multipleTable" :data="items">
                        <el-table-column width="100" align="center" prop="payment_debug_log_id" label="<?php esc_html_e('Log Id', 'bookingpress-appointment-booking'); ?>"></el-table-column>
                        <el-table-column width="300" prop="payment_debug_log_name" label="<?php esc_html_e('Log Name', 'bookingpress-appointment-booking'); ?>"></el-table-column>
                        <el-table-column prop="payment_debug_log_data" label="<?php esc_html_e('Log Data', 'bookingpress-appointment-booking'); ?>"></el-table-column>
                        <el-table-column width="200" align="center" prop="payment_debug_log_added_date" label="<?php esc_html_e('Log Added Date', 'bookingpress-appointment-booking'); ?>"></el-table-column>
                    </el-table>                                        
                </el-container>
            </el-column>
        </el-row>
        <el-row class="bpa-pagination" type="flex" v-if="items.length > 0"> <!-- Pagination -->
            <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
                <div class="bpa-pagination-left">
                    <p><?php esc_html_e('Showing', 'bookingpress-appointment-booking'); ?>&nbsp;<strong><u>{{ items.length }}</u></strong> <?php esc_html_e('out of', 'bookingpress-appointment-booking'); ?>&nbsp;<strong>{{ totalItems }}</strong></p>                    
                </div>
            </el-col>
            <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
                <el-pagination ref="bpa_pagination" @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></el-pagination>
            </el-col>
        </el-row>
    </div>
</el-dialog>


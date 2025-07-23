<?php
    if ( ! defined( 'ABSPATH' ) ) { exit; }
    global $bookingpress_ajaxurl;
?>
<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body"  name ="company_settings" label="company" data-tab_name="company_settings">
    <span slot="label">
        <i class="material-icons-round">apartment</i>
        <?php esc_html_e('Company', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Company Details', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">                    
                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('company_setting_form','company_setting')" :disabled="is_disabled" >                    
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
            <el-form id="company_setting_form" :rules="rules_company" ref="company_setting_form" :model="company_setting_form"  @submit.native.prevent>
                <div class="bpa-gs__cb--item">
                    <el-row :gutter="24" class="bpa-gs--tabs-pb__cb-item-row" >
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-gs__cb-item-left">
                            <el-upload class="bpa-upload-component" ref="avatarRef" action="<?php echo wp_nonce_url(admin_url('admin-ajax.php') . '?action=bookingpress_upload_company_avatar', 'bookingpress_upload_company_avatar'); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - esc_html is already used by wp_nonce_url function and it's false positive ?>" :on-success="bookingpress_upload_company_avatar_func" multiple="false" :show-file-list="comShowFileList" limit="1" :on-exceed="bookingpress_company_avatar_upload_limit" :on-error="bookingpress_company_avatar_upload_err" :on-remove="bookingpress_remove_company_avatar" :before-upload="checkUploadedFile" drag>
                                <span class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
                            <div class="bpa-upload-component__text" v-if="company_setting_form.company_avatar_url == ''"><?php esc_html_e('Please upload jpg/png/webp file', 'bookingpress-appointment-booking'); ?>  </div>
                            </el-upload>
                            <div class="bpa-uploaded-avatar__preview bpa-uploaded-avatar__preview--company-settings" v-if="company_setting_form.company_avatar_url != ''">
                                <button class="bpa-avatar-close-icon" @click="bookingpress_remove_company_avatar">
                                    <span class="material-icons-round">close</span>
                                </button>
                                <el-avatar shape="square" :src="company_setting_form.company_avatar_url" class="bpa-uploaded-avatar__picture"></el-avatar>
                            </div>                  
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Name', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18" >                
                            <el-form-item prop="company_name">
                                <el-input class="bpa-form-control" v-model="company_setting_form.company_name" placeholder="<?php esc_html_e('Enter company name', 'bookingpress-appointment-booking'); ?>"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Address', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18">
                            <el-form-item prop="company_address">
                                <el-input class="bpa-form-control" v-model="company_setting_form.company_address" placeholder="<?php esc_html_e('Enter company address', 'bookingpress-appointment-booking'); ?>"></el-input>        
                            </el-form-item>
                        </el-col>            
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Website', 'bookingpress-appointment-booking'); ?></h4>
                            <p class="bap-default-description"></p>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18">                        
                            <el-form-item prop="company_website">
                                <el-input class="bpa-form-control" v-model="company_setting_form.company_website" placeholder="<?php esc_html_e('Enter company website', 'bookingpress-appointment-booking'); ?>"></el-input>        
                            </el-form-item>
                        </el-col>                    
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18">
                            <el-form-item prop="company_phone_number" >
                                <vue-tel-input v-model="company_setting_form.company_phone_number" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props" ref="bpa_tel_input_field" :mode="vue_tel_mode" :auto-format="vue_tel_auto_format">
                                    <template v-slot:arrow-icon>
                                        <span class="material-icons-round">keyboard_arrow_down</span>
                                    </template>
                                </vue-tel-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>
            <el-form>        
        </div>        
    </div>
</el-tab-pane>

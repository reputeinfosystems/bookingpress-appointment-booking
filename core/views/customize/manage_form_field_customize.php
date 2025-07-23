<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
    <el-row type="flex" class="bpa-mlc-head-wrap">
        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
            <h1 class="bpa-page-heading"><?php esc_html_e('Field Settings', 'bookingpress-appointment-booking'); ?></h1>
        </el-col>
        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
            <div class="bpa-hw-right-btn-group">
                <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bpa_save_customize_settings('form_fields')" :disabled="is_disabled" >                    
                  <span class="bpa-btn__label"><?php esc_html_e('Save Changes', 'bookingpress-appointment-booking'); ?></span>
                  <div class="bpa-btn--loader__circles">                    
                      <div></div>
                      <div></div>
                      <div></div>
                  </div>
                </el-button>
            </div>
        </el-col>
    </el-row>
    <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
        <div class="bpa-back-loader"></div>
    </div>
    <el-container class="bpa-customize-main-container" id="bpa-main-container">
        <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <div class="bpa-customize-body-wrapper">                                                             
            <el-row type="flex">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <div class="bpa-customize-field-settings-body-container">
                        <el-row>
                            <draggable :list="field_settings_fields" class="list-group" ghost-class="ghost" @start="dragging = true" @end="endDragposistion" :move="updateFieldPos">
                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="(field_settings_data,index) in field_settings_fields">
                                    <div class="bpa-cfs-item-card list-group-item">
                                        <div class="bpa-cfs-ic--head">
                                            <div class="bpa-cfs-ic--head__type-label">
                                                <span class="material-icons-round">drag_indicator</span>
                                                <p>{{ field_settings_data.field_type }}</p>
                                            </div>
                                            <div class="bpa-cfs-ic--head__field-controls">
                                                <div class="bpa-cfs-ic--head__fc-swtich">
                                                   <el-switch v-model="field_settings_data.is_required" class="bpa-swtich-control" :disabled="field_settings_data.field_name == 'username' || field_settings_data.field_name == 'terms_and_conditions' ? true :false"></el-switch> 
                                                    <label><?php esc_html_e('Required', 'bookingpress-appointment-booking'); ?></label>
                                                </div>
                                                <div class="bpa-cfs-ic--head__fc-actions">
                                                    <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                        <div slot="content">
                                                            <span><?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?></span>
                                                        </div>
                                                        <el-popover placement="bottom" v-model="field_settings_data.is_edit">
                                                            <el-container class="bpa-field-settings-edit-container">
                                                                <div class="bpa-fs-item-settings-form-control-item">
                                                                    <label class="bpa-form-label"><?php esc_html_e('Label', 'bookingpress-appointment-booking'); ?></label>
                                                                    <el-input class="bpa-form-control" v-model="field_settings_data.label"></el-input>
                                                                </div>
                                                                <div class="bpa-fs-item-settings-form-control-item" v-show="field_settings_data.field_name != 'phone_number' && field_settings_data.field_name != 'terms_and_conditions' ">
                                                                    <label class="bpa-form-label"><?php esc_html_e('Placeholder', 'bookingpress-appointment-booking'); ?></label>
                                                                    <el-input class="bpa-form-control" v-model="field_settings_data.placeholder"></el-input>
                                                                </div>
                                                                <div class="bpa-fs-item-settings-form-control-item">
                                                                    <label class="bpa-form-label"><?php esc_html_e('Error message', 'bookingpress-appointment-booking'); ?></label>
                                                                    <el-input class="bpa-form-control" v-model="field_settings_data.error_message"></el-input>
                                                                </div>
                                                                <div class="bpa-fs-item-settings-form-control-item">
                                                                    <label class="bpa-form-label"><?php esc_html_e('Hide field on frontend', 'bookingpress-appointment-booking'); ?></label>
                                                                    <el-switch v-model="field_settings_data.is_hide" class="bpa-swtich-control"></el-switch>
                                                                </div>
                                                                <div class="bpa-customize--edit-label-popover--actions">
                                                                    <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldSettings(field_settings_data.field_name)"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></el-button>
                                                                </div>
                                                            </el-container>
                                                            <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                                <span class="material-icons-round">settings</span>
                                                            </el-button>
                                                        </el-popover>
                                                    </el-tooltip>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bpa-cfs-ic--body">
                                            <div class="bpa-cfs-ic--body__field-preview">
                                                <span class="bpa-form-label" v-html="field_settings_data.label" v-if="field_settings_data.field_type != 'terms_and_conditions'"></span>
                                                <el-input class="bpa-form-control"  v-if="field_settings_data.field_type != 'terms_and_conditions'" :placeholder="field_settings_data.placeholder"></el-input>
                                                <template v-if='field_settings_data.field_type == "terms_and_conditions"'>
                                                    <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" :label="field_settings_data.label" :key="">
                                                        <div v-html="field_settings_data.label"></div></el-checkbox>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </el-col>
                            </draggable>
                        </el-row>
                        <br/><br/>
                        <!-- <el-row>
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="field_settings_data in field_settings_fields" v-if="field_settings_data.field_name == 'note'">
                                <div class="bpa-cfs-item-card list-group-item">
                                    <div class="bpa-cfs-ic--head">
                                        <div class="bpa-cfs-ic--head__type-label">
                                            <span class="material-icons-round">drag_indicator</span>
                                            <p>{{ field_settings_data.field_type }}</p>
                                        </div>
                                        <div class="bpa-cfs-ic--head__field-controls">
                                            <div class="bpa-cfs-ic--head__fc-swtich">
                                               <el-switch v-model="field_settings_data.is_required" class="bpa-swtich-control"></el-switch> 
                                                <label><?php esc_html_e('Required', 'bookingpress-appointment-booking'); ?></label>
                                            </div>
                                            <div class="bpa-cfs-ic--head__fc-actions">
                                                <el-popover placement="bottom" v-model="field_settings_data.is_edit">
                                                    <el-container class="bpa-field-settings-edit-container">
                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                            <label class="bpa-form-label"><?php esc_html_e('Label', 'bookingpress-appointment-booking'); ?></label>
                                                            <el-input class="bpa-form-control" v-model="field_settings_data.label"></el-input>
                                                        </div>
                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_name != 'terms_and_conditions'">
                                                            <label class="bpa-form-label"><?php esc_html_e('Placeholder', 'bookingpress-appointment-booking'); ?></label>
                                                            <el-input class="bpa-form-control" v-model="field_settings_data.placeholder"></el-input>
                                                        </div>
                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                            <label class="bpa-form-label"><?php esc_html_e('Error Message', 'bookingpress-appointment-booking'); ?></label>
                                                            <el-input class="bpa-form-control" v-model="field_settings_data.error_message"></el-input>
                                                        </div>
                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                            <label class="bpa-form-label"><?php esc_html_e('Hide field on frontend', 'bookingpress-appointment-booking'); ?></label>
                                                            <el-switch v-model="field_settings_data.is_hide" class="bpa-swtich-control"></el-switch>
                                                        </div>
                                                        <div class="bpa-customize--edit-label-popover--actions">
                                                            <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldSettings(field_settings_data.field_name)"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></el-button>
                                                        </div>
                                                    </el-container>
                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                        <span class="material-icons-round">settings</span>
                                                    </el-button>
                                                </el-popover>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bpa-cfs-ic--body">
                                        <div class="bpa-cfs-ic--body__field-preview">
                                            <span class="bpa-form-label" v-text="field_settings_data.label"></span>
                                            <el-input class="bpa-form-control" :placeholder="field_settings_data.placeholder"></el-input>
                                        </div>
                                    </div>
                                </div>
                            </el-col>
                        </el-row> -->
                    </div>
                </el-col>
            </el-row>            
        </div>
    </el-container>    
</el-main>
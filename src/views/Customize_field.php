<div id="customize_form_field_root_app" class="bookingpress-customize-form-field bookingpress_page_wrapper">
    <?php
    if ( ! defined( 'ABSPATH' ) ) { exit; }
    if ( class_exists( '\BookingPressPro\admin\Customize' ) && method_exists( '\BookingPressPro\admin\Customize', 'render_customize_header' ) ) {
        \BookingPressPro\admin\Customize::render_customize_header();
    } else {
        require_once __DIR__ . '/components/Header.php';
    }
    ?>
    <div class="customize-form-field-app-root bookingpress_page_inner_wrapper" v-cloak id="customize-form-field-app-root">
        <bp-ui-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
            <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
                    <h1 class="bpa-page-heading"><?php esc_html_e('Field Settings', 'bookingpress-appointment-booking'); ?></h1>
                </bp-ui-col>
                <bp-ui-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                    <div class="bpa-hw-right-btn-group">
                        <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bpa_save_customize_settings('form_fields')" :disabled="is_disabled" >                    
                            <span class="bpa-btn__label"><?php esc_html_e('Save Changes', 'bookingpress-appointment-booking'); ?></span>
                            <div class="bpa-btn--loader__circles">                    
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </bp-ui-button>
                    </div>
                </bp-ui-col>
            </bp-ui-row>
            <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
                <div class="bpa-back-loader"></div>
            </div>
            <bp-ui-container class="bpa-customize-main-container" id="bpa-main-container">
                <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                    <div class="bpa-back-loader"></div>
                </div>
                <div class="bpa-customize-body-wrapper">                                                             
                    <bp-ui-row type="flex">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-customize-field-settings-body-container">
                                <bp-ui-row>
                                    <draggable :list="field_settings_fields" class="list-group" ghost-class="ghost" @start="dragging = true" @end="endDragposistion" :move="updateFieldPos" item-key="id">
                                        <template #item="{element: field_settings_data, index}">
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <div class="bpa-cfs-item-card list-group-item">
                                                    <div class="bpa-cfs-ic--head">
                                                        <div class="bpa-cfs-ic--head__type-label">
                                                            <span class="material-icons-round">drag_indicator</span>
                                                            <p v-if="'terms_and_conditions' == field_settings_data.field_type"><?php esc_html_e('Terms and Conditions', 'bookingpress-appointment-booking'); ?></p>
                                                            <p v-else>{{ field_settings_data.field_type }}</p>
                                                        </div>
                                                        <div class="bpa-cfs-ic--head__field-controls">
                                                            <div class="bpa-cfs-ic--head__fc-swtich">
                                                                <bp-ui-switch v-model="field_settings_data.is_required" class="bpa-swtich-control" :disabled="field_settings_data.field_name == 'username' || field_settings_data.field_name == 'terms_and_conditions' ? true : false"></bp-ui-switch> 
                                                                <label><?php esc_html_e('Required', 'bookingpress-appointment-booking'); ?></label>
                                                            </div>
                                                            <div class="bpa-cfs-ic--head__fc-actions">
                                                                <bp-ui-popover placement="bottom" v-model:visible="field_settings_data.is_edit" :width="320" trigger="click" :teleported="true">
                                                                    <template #reference>
                                                                        <span class="bpa-cfs-popover-trigger">
                                                                            <bp-ui-tooltip effect="dark" content="" placement="top" :open-delay="300" :disabled="field_settings_data.is_edit">
                                                                                <template #content>
                                                                                    <span><?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?></span>
                                                                                </template>
                                                                                <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click="field_settings_data.is_edit = !field_settings_data.is_edit">
                                                                                    <span class="material-icons-round">settings</span>
                                                                                </bp-ui-button>
                                                                            </bp-ui-tooltip>
                                                                        </span>
                                                                    </template>
                                                                    <bp-ui-container class="bpa-field-settings-edit-container">
                                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                                            <label class="bpa-form-label"><?php esc_html_e('Label', 'bookingpress-appointment-booking'); ?></label>
                                                                            <bp-ui-input class="bpa-form-control" v-model="field_settings_data.label"></bp-ui-input>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-show="field_settings_data.field_name != 'phone_number' && field_settings_data.field_name != 'terms_and_conditions' ">
                                                                            <label class="bpa-form-label"><?php esc_html_e('Placeholder', 'bookingpress-appointment-booking'); ?></label>
                                                                            <bp-ui-input class="bpa-form-control" v-model="field_settings_data.placeholder"></bp-ui-input>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                                            <label class="bpa-form-label"><?php esc_html_e('Error message', 'bookingpress-appointment-booking'); ?></label>
                                                                            <bp-ui-input class="bpa-form-control" v-model="field_settings_data.error_message"></bp-ui-input>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                                            <label class="bpa-form-label"><?php esc_html_e('Hide field on frontend', 'bookingpress-appointment-booking'); ?></label>
                                                                            <bp-ui-switch v-model="field_settings_data.is_hide" class="bpa-swtich-control"></bp-ui-switch>
                                                                        </div>
                                                                        <div class="bpa-customize--edit-label-popover--actions">
                                                                            <bp-ui-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldSettings(field_settings_data.field_name)"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                                                                        </div>
                                                                    </bp-ui-container>
                                                                </bp-ui-popover>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-cfs-ic--body">
                                                        <div class="bpa-cfs-ic--body__field-preview">
                                                            <span class="bpa-form-label" v-html="field_settings_data.label" v-if="field_settings_data.field_type != 'terms_and_conditions'"></span>
                                                            <bp-ui-input class="bpa-form-control"  v-if="field_settings_data.field_type != 'terms_and_conditions'" :placeholder="field_settings_data.placeholder"></bp-ui-input>
                                                            <template v-if='field_settings_data.field_type == "terms_and_conditions"'>
                                                                <bp-ui-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" :label="field_settings_data.label">
                                                                    <div v-html="field_settings_data.label"></div>
                                                                </bp-ui-checkbox>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </bp-ui-col>
                                        </template>
                                    </draggable>
                                </bp-ui-row>
                                <br/><br/>
                            </div>
                        </bp-ui-col>
                    </bp-ui-row>            
                </div>
            </bp-ui-container>    
        </bp-ui-main>
    </div>
</div>

<div id="customer_import_modal" v-cloak class="bookingpress-customer-import-dialog-container">
    <bp-ui-drawer class="bpa-drawer__import-customer" modal-append-to-body="false" @close="resetImportModal('customer_form_import')" v-model="import_customer_modal">
        <div class="bpa-dlt__heading-import">
            <h3><?php esc_html_e( 'Import Customer', 'bookingpress-appointment-booking' ); ?></h3>
        </div>
        <div class="bpa-dlt__body-import">		
            
            <div v-if="complete_import == '1'" class="ap-generate-payout-loader-container">
                <div class="ap-generate-payout-loader">                                
                    <div class="ap-loader-complete-txt ap-flex-center">
                        <div class="ap-loader-progress-txt"><?php esc_html_e('Total Customers :', 'bookingpress-appointment-booking'); ?> <span v-html="customers_total_count"></span></div>
                        <div class="ap-loader-progress-txt"><?php esc_html_e('Customers Imported :', 'bookingpress-appointment-booking'); ?> <span v-html="customers_import_count"></span></div>
                        <div class="ap-loader-progress-txt"><?php esc_html_e('Customers Not Imported:', 'bookingpress-appointment-booking'); ?> <span v-html="customers_not_import_count"></span></div>
                    </div>
                </div>
            </div>

            <div class="bpa-dlt-body-module-wrapper-import">
                
                <bp-ui-row type="flex" class="bpa-bac__wrapper">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">                    
                        <div class="bpa-dlt__form_title bpa-form-label"><?php esc_html_e('Upload csv file', 'bookingpress-appointment-booking'); ?></div>
                        <bp-ui-upload :auto-upload="true" class="bpa-form-control-import-file"  ref="importCustomerRef"  :action="upload_import_url"
                            :headers="upload_headers"
                            :on-success="bookingpress_upload_customer_import_file_func" 
                            :file-list="import_file_list" 
                            :multiple="false" 
                            :show-file-list="true" 
                            :limit="1" 
                            :on-exceed="bookingpress_image_upload_limit" 
                            :on-error="bookingpress_image_upload_err" 
                            :on-remove="bookingpress_remove_import_file" 
                            :before-upload="checkUploadedImportFile" 
                            drag>                
                            <label for="bpa-file-upload-two" class="bpa-form-control--file-upload-import">
                                <span class="bpa-fu__placeholder"><?php esc_html_e('Choose a file...', 'bookingpress-appointment-booking'); ?></span>
                                <span class="bpa-fu__btn"><?php esc_html_e('Upload', 'bookingpress-appointment-booking'); ?></span> 
                            </label>
                        </bp-ui-upload>
                    </bp-ui-col>
                </bp-ui-row>
                <bp-ui-row type="flex" class="bpa-bac__wrapper">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <bp-ui-form v-if="import_file_name != ''" ref="customer_form_import" :rules="bookingpress_customer_import_rules" require-asterisk-position="right" :model="bookingpress_import_fields" labbp-ui-position="top">
                            <div v-if="bookingpress_import_field_data.lenght != 0" class="bpa-import-field-data">                                
                                <bp-ui-row type="flex" class="bpa-import-field-body">
                                    <bp-ui-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-import-field__left-area">
                                        <div class="bpa-form-label"><?php esc_html_e( 'Create WordPress User', 'bookingpress-appointment-booking' ); ?></div>
                                    </bp-ui-col>
                                    <bp-ui-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-import-field__right-area">                            
                                        <bp-ui-switch class="bpa-swtich-control" v-model="is_wordpress_user_create_on_import"></bp-ui-switch>
                                    </bp-ui-col>
                                </bp-ui-row>    
                                <bp-ui-row type="flex" class="bpa-import-field-head">
                                    <bp-ui-col class="bpa-import-field__left-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">                                
                                        <div class="bpa-head-fields"><?php esc_html_e('Customer Field', 'bookingpress-appointment-booking'); ?></div>
                                    </bp-ui-col>
                                    <bp-ui-col class="bpa-import-field__right-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                        <div class="bpa-head-fields"><?php esc_html_e('CSV Column', 'bookingpress-appointment-booking'); ?></div>                              
                                    </bp-ui-col>
                                </bp-ui-row>
                                <template v-for="import_field in bookingpress_import_field_data" :key="import_field.field_key">
                                    <bp-ui-row 
                                        v-if="(import_field.is_userfield == '1' && is_wordpress_user_create_on_import == true) || (import_field.is_userfield == '0')" 
                                        type="flex" 
                                        class="bpa-import-field-body">
                                        <bp-ui-col class="bpa-import-field__left-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">                                
                                            <div :class="(import_field.is_required == '1') ? 'bpa-req-imp-fld' : ''" class="bpa-import-fields-label">
                                                {{import_field.field_label}}
                                            </div>
                                        </bp-ui-col>
                                        <bp-ui-col class="bpa-import-field__right-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                            <div class="bpa-import-fields-select">
                                                <bp-ui-form-item :prop="import_field.field_key">               
                                                    <bp-ui-select class="bpa-form-control" :popper-append-to-body="false" v-model="bookingpress_import_fields[import_field.field_key]" placeholder="<?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?>">
                                                        <bp-ui-option label="<?php esc_html_e('- Ignore this field -', 'bookingpress-appointment-booking'); ?>" value=""></bp-ui-option>                                                
                                                        <bp-ui-option v-for="item in import_file_fields" :key="item.key" :label="item.value" :value="item.key"></bp-ui-option>
                                                    </bp-ui-select>
                                                </bp-ui-form-item>
                                            </div>                              
                                        </bp-ui-col>
                                    </bp-ui-row>
                                </template>
                            </div>
                        </bp-ui-form>                    
                    </bp-ui-col>
                </bp-ui-row>
            </div>
        </div>
        <div class="bpa-dlt__footer-import">
            <bp-ui-button @click="import_customer_modal = false;" class="bpa-btn bp-ui-button--default"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></bp-ui-button>	
            <bp-ui-button  v-if="import_file_name != '' && complete_import == '0'" :class="(import_loading == '1') ? 'bpa-btn--is-loader' : ''" @click="importCustomer('customer_form_import')"  class="bpa-btn bpa-btn--primary" @click="">                    
                <span class="bpa-btn__label"><?php esc_html_e('Proceed', 'bookingpress-appointment-booking'); ?></span>
                <div class="bpa-btn--loader__circles">                    
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </bp-ui-button>
        </div> 
    </bp-ui-drawer>
</div>
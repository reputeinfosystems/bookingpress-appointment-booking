<div id="customer_export_modal" v-cloak class="bookingpress-customer-export-dialog-container">
    <bp-ui-dialog class="bpa-dialog bpa-dailog__small bpa-dialog--export-customers" id="customer_export_model" title="" v-model="ExportCustomerLite" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
        <div class="bpa-dialog-heading">
            <bp-ui-row type="flex">
                <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                    <h1 class="bpa-page-heading"><?php esc_html_e( 'Export Data', 'bookingpress-appointment-booking' ); ?></h1>
                </bp-ui-col>
            </bp-ui-row>
        </div>
        <div class="bpa-dialog-body">
            <bp-ui-container class="bpa-grid-list-container bpa-add-categpry-container">
                <div class="bpa-form-row">				
                    <bp-ui-row>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <bp-ui-form labbp-ui-position="top" @submit.prevent>
                                <div class="bpa-form-body-row">
                                    <bp-ui-row>
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                            <bp-ui-form-item>
                                                <bp-ui-checkbox-group v-model="export_checked_field_lite">		
                                                    <bp-ui-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-for="item in customer_export_field_list_lite" :key="item.name" :value="item.name">{{item.text}}</bp-ui-checkbox>
                                                </bp-ui-checkbox-group>									  
                                            </bp-ui-form-item>
                                        </bp-ui-col> 										
                                    </bp-ui-row>
                                </div>
                            </bp-ui-form>
                        </bp-ui-col>
                    </bp-ui-row>
                </div>
            </bp-ui-container>
        </div>
        <div class="bpa-dialog-footer">
            <div class="bpa-hw-right-btn-group">
                <bp-ui-button class="bpa-btn bpa-btn__medium" @click="close_export_customer_lite_model" ><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></bp-ui-button>
                <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :class="(is_export_button_loader_lite == '1') ? 'bpa-btn--is-loader' : ''" @click="
                bookingpress_export_customer_lite" :disabled="is_export_button_disabled_lite" >					
                <span class="bpa-btn__label"><?php esc_html_e( 'Export', 'bookingpress-appointment-booking' ); ?></span>
                <div class="bpa-btn--loader__circles">				    
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                </bp-ui-button>
            </div>
        </div>
    </bp-ui-dialog>
</div>
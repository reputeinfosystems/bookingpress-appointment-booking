<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body"  name ="payment_settings" label="payments" data-tab_name="payment_settings">
    <span slot="label">
        <i class="material-icons-round">account_balance_wallet</i>
        <?php esc_html_e('Payments', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card bpa-payment-settings-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping">
            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Payment Settings', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">    
                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('payment_setting_form','payment_setting')" :disabled="is_disabled" >                    
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
            <div class="bpa-gs__cb--item">
                <el-form :rules="rules_payment" ref="payment_setting_form" :model="payment_setting_form" @submit.native.prevent>
                    <div class="bpa-gs__cb--item">
                        <div class="bpa-gs__cb--item-heading">
                            <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Currency Settings', 'bookingpress-appointment-booking'); ?></h4>
                        </div>
                        <div class="bpa-gs__cb--item-body">
                            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">                    
                                    <h4> <?php esc_html_e('Currency', 'bookingpress-appointment-booking'); ?></h4>                        
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">    
                                    <el-form-item prop="payment_default_currency">
                                        <el-select @change="bookingpress_check_currency_status($event)" class="bpa-form-control" v-model="payment_setting_form.payment_default_currency"
                                            popper-class="bpa-el-select--is-with-navbar" filterable>
                                            <el-option  v-for="currency_data in currency_countries" :value="currency_data.code" :label="currency_data.name">
                                                <div class="bpa-fc__item--currency-custom-dropdown-item">
                                                    <el-image :src="'<?php echo esc_url_raw(BOOKINGPRESS_IMAGES_URL); ?>/country-flags/'+currency_data.iso+'.png'"></el-image>
                                                    <div class="bpa-fc__item--currency-custom-dropdown-item__body">
                                                        <p>{{ currency_data.name }}</p>
                                                        <span>{{ currency_data.symbol }}</span>
                                                    </div>
                                                </div>
                                            </el-option>
                                        </el-select>
                                    </el-form-item>
                                </el-col>                
                            </el-row>
                            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="bookingpress_currency_warnning == '1'">
                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <div class="bpa-toast-notification --bpa-warning">
                                        <div class="bpa-front-tn-body">
                                            <span class="material-icons-round">info</span>
                                            <p><?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?>: {{bookingpress_currency_warnning_msg}}</p>
                                        </div>
                                    </div>
                                </el-col>
                            </el-row> 
                            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                    <h4><?php esc_html_e('Currency symbol position', 'bookingpress-appointment-booking'); ?></h4>                    
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                    <el-form-item prop="price_symbol_position">
                                        <el-select  class="bpa-form-control" v-model="payment_setting_form.price_symbol_position"
                                        popper-class="bpa-el-select--is-with-navbar">
                                            <el-option v-for="price_data in price_symbol_position_val" :value="price_data.value" :label="price_data.text">{{ price_data.text }} - <span class="bookingpress_payment_ex_position_styles">{{ price_data.position_ex }}</span></el-option>
                                        </el-select>        
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                    <h4><?php esc_html_e('Currency separator', 'bookingpress-appointment-booking'); ?></h4>
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                    <el-form-item prop="price_separator_vals">
                                        <el-select class="bpa-form-control" v-model="payment_setting_form.price_separator"
                                        popper-class="bpa-el-select--is-with-navbar">
                                            <el-option v-for="price_data in price_separator_vals" :value="price_data.value" :label="price_data.text">
                                                <span>{{ price_data.text }}</span>
                                                <span class="bookingpress_payment_ex_position_styles">{{ price_data.separator_ex }}</span>
                                            </el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-row gutter="24" class="bpa-gs__pst-custom-price-sep" v-if="payment_setting_form.price_separator == 'Custom'">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                                            <el-form-item prop="price_separator_vals">
                                                <span class="bpa-form-label"><?php esc_html_e('Thousand Separator', 'bookingpress-appointment-booking'); ?></span>    
                                                <el-input class="bpa-form-control" maxlength="5" v-model="payment_setting_form.custom_comma_separator" placeholder="<?php esc_html_e('Enter Thousand Separator', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                                            <el-form-item prop="price_separator_vals">
                                                <span class="bpa-form-label"><?php esc_html_e('Decimal Separator', 'bookingpress-appointment-booking'); ?></span>
                                                <el-input class="bpa-form-control" maxlength="5" v-model="payment_setting_form.custom_dot_separator" placeholder="<?php esc_html_e('Enter Decimal Separator', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>
                                        </el-col>
                                    </el-row>
                                </el-col>
                            </el-row>
                            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                    <h4> <?php esc_html_e('Number of decimals', 'bookingpress-appointment-booking'); ?></h4>
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                    <el-form-item prop="price_number_of_decimals">
                                        <el-input-number class="bpa-form-control bpa-form-control--number" :min="0" :max="5" v-model="payment_setting_form.price_number_of_decimals" step-strictly></el-input-number>
                                    </el-form-item>    
                                </el-col>
                            </el-row>                       
                        </div>
                    </div>
                    <div class="bpa-gs__cb--item">
                        <div class="bpa-gs__cb--item-heading">
                            <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Payment Method Settings', 'bookingpress-appointment-booking'); ?></h4>
                        </div>
                        <div class="bpa-gs__cb--item-body">
                            <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                                    <h4> <?php esc_html_e('On Site', 'bookingpress-appointment-booking'); ?></h4>
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                    <el-form-item prop="on_site_payment">
                                        <el-switch class="bpa-swtich-control" v-model="payment_setting_form.on_site_payment"></el-switch>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                            <div class="bpa-pst-is-single-payment-box">
                                <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                                    <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                                        <h4> <?php esc_html_e('PayPal', 'bookingpress-appointment-booking'); ?></h4>
                                    </el-col>
                                    <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                        <el-form-item prop="paypal_payment">
                                            <el-switch class="bpa-swtich-control" v-model="payment_setting_form.paypal_payment"></el-switch>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                <div class="bpa-ns--sub-module__card" v-if="payment_setting_form.paypal_payment == true">
                                    <el-row type="flex" class="bpa-ns--sub-module__card--row">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                            <h4> <?php esc_html_e('Payment Mode', 'bookingpress-appointment-booking'); ?></h4>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                                            <el-radio v-model="payment_setting_form.paypal_payment_mode" label="sandbox">Sandbox</el-radio>
                                            <el-radio v-model="payment_setting_form.paypal_payment_mode" label="live">Live</el-radio>
                                        </el-col>
                                    </el-row>
                                    <el-row type="flex" class="bpa-ns--sub-module__card--row">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                            <h4> <?php esc_html_e('Payment Method', 'bookingpress-appointment-booking'); ?></h4>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                                            <el-radio v-model="payment_setting_form.paypal_payment_method_type" label="lagacy">Legacy</el-radio>
                                            <el-radio v-model="payment_setting_form.paypal_payment_method_type" label="popup">Pop-Up</el-radio>
                                        </el-col>
                                    </el-row>                                       
                                    <el-row v-if="payment_setting_form.paypal_payment_method_type == 'popup'" type="flex" class="bpa-ns--sub-module__card--row">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                            <h4> <?php esc_html_e('Client ID', 'bookingpress-appointment-booking'); ?></h4>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                            <el-form-item prop="paypal_client_id">
                                                <el-input class="bpa-form-control" v-model="payment_setting_form.paypal_client_id" placeholder="<?php esc_html_e('Enter Client ID', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>    
                                        </el-col>
                                    </el-row>
                                    <el-row v-if="payment_setting_form.paypal_payment_method_type == 'popup'" type="flex" class="bpa-ns--sub-module__card--row">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                            <h4> <?php esc_html_e('Client Secret', 'bookingpress-appointment-booking'); ?></h4>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                            <el-form-item prop="paypal_client_secret">
                                                <el-input class="bpa-form-control" v-model="payment_setting_form.paypal_client_secret" placeholder="<?php esc_html_e('Enter Client Secret', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>    
                                        </el-col>
                                    </el-row>                                      
                                    <el-row v-if="payment_setting_form.paypal_payment_method_type != 'popup'" type="flex" class="bpa-ns--sub-module__card--row">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                            <h4> <?php esc_html_e('Merchant Email', 'bookingpress-appointment-booking'); ?></h4>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                                            <el-form-item prop="paypal_merchant_email">
                                                <el-input class="bpa-form-control" type="email" v-model="payment_setting_form.paypal_merchant_email" placeholder="<?php esc_html_e('Enter email', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>
                                        </el-col>
                                    </el-row>
                                    <el-row v-if="payment_setting_form.paypal_payment_method_type != 'popup'" type="flex" class="bpa-ns--sub-module__card--row">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                            <h4> <?php esc_html_e('API Username', 'bookingpress-appointment-booking'); ?></h4>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                            <el-form-item prop="paypal_api_username">
                                                <el-input class="bpa-form-control" v-model="payment_setting_form.paypal_api_username" placeholder="<?php esc_html_e('Enter username', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>    
                                        </el-col>
                                    </el-row>
                                    <el-row v-if="payment_setting_form.paypal_payment_method_type != 'popup'" type="flex" class="bpa-ns--sub-module__card--row">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                            <h4> <?php esc_html_e('API Password', 'bookingpress-appointment-booking'); ?></h4>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">                                                            
                                            <el-form-item prop="paypal_api_password">
                                                <el-input class="bpa-form-control" v-model="payment_setting_form.paypal_api_password" placeholder="<?php esc_html_e('Enter password', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>
                                        </el-col>
                                    </el-row>
                                    <el-row v-if="payment_setting_form.paypal_payment_method_type != 'popup'" type="flex" class="bpa-ns--sub-module__card--row">
                                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                                            <h4> <?php esc_html_e('API Signature', 'bookingpress-appointment-booking'); ?></h4>
                                        </el-col>
                                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                                            <el-form-item prop="paypal_api_signature">
                                                <el-input class="bpa-form-control" v-model="payment_setting_form.paypal_api_signature" placeholder="<?php esc_html_e('Enter API signature', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>
                                        </el-col>
                                    </el-row> 
                                    <div v-if="payment_setting_form.paypal_payment_method_type != 'popup'" class="bpa-toast-notification --bpa-timezone-note">
                                        <div class="bpa-front-tn-body">
                                            <span class="material-icons-round">info</span> <p><strong><?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?>:</strong> <?php esc_html_e('It is advised to use the Pop-up method rather than the legacy method although both work fine but as PayPal believes it is better to use the Pop-up method.', 'bookingpress-appointment-booking'); ?> </p>
                                        </div>
                                    </div>                                                                      
                                </div>
                            </div>
                            <?php
                                do_action('bookingpress_gateway_listing_field');
                            ?>
                        </div>
                    </div>
                </el-form>
            </div>
        </div>    
    </div>
</el-tab-pane>

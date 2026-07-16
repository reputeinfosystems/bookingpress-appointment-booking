<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<bp-ui-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="notification_settings" label="notifications" data-tab_name="notification_settings">
    <template #label>
        <i class="material-icons-round">notifications_active</i>
        <?php esc_html_e('Notifications', 'bookingpress-appointment-booking'); ?>
    </template>
    <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_tab_loader == '1'">
        <div class="bpa-back-loader"></div>
    </div>
    <div class="bpa-general-settings-tabs--pb__card bpa-notification-settings-tabs--pb__card">
        <bp-ui-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Email Notification Settings', 'bookingpress-appointment-booking'); ?></h1>                
            </bp-ui-col>
            <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">                    
                    <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('notification_setting_form','notification_setting')" :disabled="is_disabled" >                    
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
        <div class="bpa-gs--tabs-pb__content-body">            
            <bp-ui-form :rules="rules_notification" ref="notification_setting_form" :model="notification_setting_form" @submit.native.prevent>
                <div class="bpa-gs__cb--item">
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="'wp_mail' == notification_setting_form.selected_mail_service && true == bpa_display_wpmail_notice">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-pg-warning-belt-box bpa-warning">
                                <p class="bpa-wbb__desc">
                                    <span class="material-icons-round bpa-wbb__desc-icon">warning</span>
                                    <span class="bpa-wbb__desc-content"><?php echo esc_html__('It seems that the email upon booking could not be sent. Your site may not be correctly configured to send emails. Please check the following error message','bookingpress-appointment-booking'); //phpcs:ignore ?></span>
                                </p>
                                <ul class="bpa-pg-warning-lists">
                                    <li v-for="error_msg in bpa_wpmail_failed_msg_data">{{error_msg}}</li>
                                </ul>
                            </div>
                        </bp-ui-col>
                    </bp-ui-row> 
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Email delivery method', 'bookingpress-appointment-booking'); ?></h4>                                        
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-radio v-model="notification_setting_form.selected_mail_service" value="wp_mail" label="wp_mail"><?php esc_html_e('WordPress default', 'bookingpress-appointment-booking'); ?></bp-ui-radio>
                            <bp-ui-radio v-model="notification_setting_form.selected_mail_service" value="php_mail" label="php_mail"><?php esc_html_e('PHP mail() function', 'bookingpress-appointment-booking'); ?></bp-ui-radio>
                            <bp-ui-radio v-model="notification_setting_form.selected_mail_service" value="smtp" label="smtp"><?php esc_html_e('SMTP method', 'bookingpress-appointment-booking'); ?></bp-ui-radio>
                            <bp-ui-radio v-model="notification_setting_form.selected_mail_service" value="Google_Gmail" label="Google_Gmail"><?php esc_html_e('Google/Gmail', 'bookingpress-appointment-booking'); ?></bp-ui-radio>
                        </bp-ui-col>
                    </bp-ui-row>
                    <div class="bpa-alert-message-default --bpa-is-am-warning" v-if="notification_setting_form.selected_mail_service == 'Google_Gmail'">
                        <bp-ui-alert type="warning" show-icon><?php esc_html_e('The Gmail mailer works well for sites that send low numbers of emails. However, Gmail\'s API has rate limitations and a number of additional restrictions that can lead to challenges during setup. If you expect to send a high volume of emails, or if you find that your web host is not compatible with the Gmail API restrictions, then we recommend considering a different mailer option.','bookingpress-appointment-booking'); ?></bp-ui-alert>
                    </div>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Sender name', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >
                            <bp-ui-form-item prop="sender_name">
                                <bp-ui-input class="bpa-form-control" v-model="notification_setting_form.sender_name" placeholder="<?php esc_html_e('Enter sender name', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>        
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Sender email', 'bookingpress-appointment-booking'); ?></h4>        
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">                            
                            <bp-ui-form-item prop="sender_email">    
                                <bp-ui-input class="bpa-form-control" type="email" v-model="notification_setting_form.sender_email" placeholder="<?php esc_html_e('example@example.com', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>        
                            </bp-ui-form-item>    
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e('Admin email', 'bookingpress-appointment-booking'); ?></h4>        
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">                            
                            <bp-ui-form-item prop="admin_email">    
                                <bp-ui-input class="bpa-form-control" type="email" v-model="notification_setting_form.admin_email" placeholder="<?php esc_html_e('Enter admin email', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>        
                            </bp-ui-form-item>    
                        </bp-ui-col>
                    </bp-ui-row>                
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Host name', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="smtp_host">    
                                <bp-ui-input class="bpa-form-control" v-model="notification_setting_form.smtp_host" placeholder="<?php esc_html_e('Host name', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>        
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Port', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="smtp_port">    
                                <bp-ui-input class="bpa-form-control" v-model="notification_setting_form.smtp_port" placeholder="<?php esc_html_e('Port', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>        
                            </bp-ui-form-item>    
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Secure connection', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="smtp_secure">    
                                <bp-ui-select class="bpa-form-control" placeholder="<?php esc_html_e('Select secure', 'bookingpress-appointment-booking'); ?>" v-model="notification_setting_form.smtp_secure">
                                    <bp-ui-option v-for="item in default_smtp_secure_options" :key="item.text" :label="item.text" :value="item.value"></bp-ui-option>
                                </bp-ui-select>                                
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>    
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Username', 'bookingpress-appointment-booking'); ?></h4>                    
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="smtp_username">    
                                <bp-ui-input class="bpa-form-control" v-model="notification_setting_form.smtp_username" placeholder="<?php esc_html_e('Username', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>        
                            </bp-ui-form-item>    
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Password', 'bookingpress-appointment-booking'); ?></h4>                
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="smtp_password">    
                                <bp-ui-input class="bpa-form-control" type="password" v-model="notification_setting_form.smtp_password" placeholder="<?php esc_html_e('Password', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>

                    <!-- for gmail notification start-->
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'Google_Gmail'">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Client ID', 'bookingpress-appointment-booking'); ?></h4>                
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="gmail_client_ID">    
                                <bp-ui-input class="bpa-form-control" type="text" v-model="notification_setting_form.gmail_client_ID" placeholder="<?php esc_html_e('Client ID', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'Google_Gmail'">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Client Secret', 'bookingpress-appointment-booking'); ?></h4>                
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="gmail_client_secret">    
                                <bp-ui-input class="bpa-form-control" type="password" v-model="notification_setting_form.gmail_client_secret" placeholder="<?php esc_html_e('Client Secret', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'Google_Gmail'">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Authorized redirect URI', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>                            
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                            <div class="bpa-gs__redirect-url-val">
                                <p><?php echo esc_url( get_home_url()) . '?page=bookingpress_gmailapi' ?></p>
                                <span class="material-icons-round" @click="bookingpress_gmail_insert_placeholder('<?php echo esc_url( get_home_url() ) .'?page=bookingpress_gmailapi' ?>','text')">content_copy</span>
                            </div>
                        </bp-ui-col>                            
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'Google_Gmail'">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                            <h4><?php esc_html_e( 'Authentication Token', 'bookingpress-appointment-booking' ); ?></h4>
                        </bp-ui-col>                            
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                            <bp-ui-form-item prop="gmail_client_secret">    
                                <bp-ui-input class="bpa-form-control" v-model="notification_setting_form.bookingpress_gmail_auth_token" :disabled="true"></bp-ui-input>
                            </bp-ui-form-item>
                        </bp-ui-col>                            
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'Google_Gmail'">
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                        </bp-ui-col>                            
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                            <div class="bp-col bp-col-36 bp-col-xs-12 bp-col-sm-12 bp-col-md-10 bp-col-lg-8 bp-col-xl-8">
                                <button type="button" class="el-button bpa-btn bpa-btn__medium bpa-btn--full-width el-button--default bpa-btn--primary" v-if="notification_setting_form.bookingpress_response_email == ''" @click='bookingpress_gmail_api_check();'>
                                    <span>Connect With Google Account</span>
                                </button>
                                <button type="button" class="el-button bpa-btn bpa-btn__medium bpa-btn--full-width el-button--default bpa-btn--primary" v-else @click='bookingpress_gmail_api_remove(notification_setting_form.bookingpress_gmail_auth_token, notification_setting_form.bookingpress_response_email, notification_setting_form.bookingpress_gmail_auth);'>
                                    <span>Remove from google account</span>
                                </button>
                            </div>
                            <div class="bp-col bp-col-36 bp-col-xs-12 bp-col-sm-12 bp-col-md-10 bp-col-lg-8 bp-col-xl-8" style="padding-top:10px;" v-if="notification_setting_form.bookingpress_response_email != ''">connect with {{notification_setting_form.bookingpress_response_email}}</div>
                        </bp-ui-col>                            
                    </bp-ui-row>
                    <!-- for gmail notification end-->
                </div>
            </bp-ui-form>
            <!-- for gmail notification test email start-->
            <div class="bpa-ns--sub-module__card" v-if="notification_setting_form.selected_mail_service == 'Google_Gmail'">
                <bp-ui-form :rules="rules_gmail_test_mail" ref="notification_gmail_test_mail_form" :model="notification_gmail_test_mail_form" @submit.native.prevent>                    
                    <h4><?php esc_html_e('Send test email notification', 'bookingpress-appointment-booking'); ?></h4>
                    <bp-ui-row type="flex" class="bpa-ns--sub-module__card--row">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4> <?php esc_html_e('To', 'bookingpress-appointment-booking'); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">                            
                            <bp-ui-form-item prop="gmail_test_receiver_email">
                                <bp-ui-input class="bpa-form-control" type="email" v-model="notification_gmail_test_mail_form.gmail_test_receiver_email"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-ns--sub-module__card--row">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e('Message', 'bookingpress-appointment-booking'); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="gmail_test_msg">    
                                <bp-ui-input class="bpa-form-control" type="textarea" v-model="notification_gmail_test_mail_form.gmail_test_msg"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-ns--sub-module__card--row --is-button">
                                <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn__medium" :class="(is_display_send_test_gmail_mail_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disable_send_test_gmail_email_btn" @click="bookingpress_send_test_gmail_email" >                    
                                  <span class="bpa-btn__label"><?php esc_html_e('Send Test Email', 'bookingpress-appointment-booking'); ?></span>
                                  <div class="bpa-btn--loader__circles">                    
                                      <div></div>
                                      <div></div>
                                      <div></div>
                                  </div>
                                </bp-ui-button>    
                            </div>
                        </bp-ui-col>
                    </bp-ui-row>                    
                    <bp-ui-row type="flex" v-if="notification_setting_form.selected_mail_service == 'Google_Gmail'">                            
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-toast-notification --error" :class="succesfully_send_test_gmail_email == 1 ? '--success' : ''" v-if="succesfully_send_test_gmail_email == 1 || error_send_test_gmail_email == 1">
                                <label class="bpa-text--primary-color" v-if="succesfully_send_test_gmail_email == 1">
                                    <?php esc_html_e('Test Email Sent Successfully', 'bookingpress-appointment-booking'); ?>
                                </label>
                                <label class="bpa-text--danger-color" v-if="error_send_test_gmail_email == 1" > {{error_text_of_test_gmail_email}}
                                </label>
                                
                            </div>                            
                        </bp-ui-col>
                    </bp-ui-row>                                    
                </bp-ui-form>
            </div>
            <!-- for gmail notification test email end-->
            <!-- for WordPress notification test email start-->
            <div class="bpa-ns--sub-module__card" v-if="notification_setting_form.selected_mail_service == 'wp_mail'">
                <bp-ui-form :rules="rules_wpmail_test_mail" ref="notification_wpmail_test_mail_form" :model="notification_wpmail_test_mail_form" @submit.native.prevent>                    
                    <h4><?php esc_html_e('Send test email notification', 'bookingpress-appointment-booking'); ?></h4>
                    <bp-ui-row type="flex" class="bpa-ns--sub-module__card--row">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4> <?php esc_html_e('To', 'bookingpress-appointment-booking'); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">                            
                            <bp-ui-form-item prop="wpmail_test_receiver_email">
                                <bp-ui-input class="bpa-form-control" type="email" v-model="notification_wpmail_test_mail_form.wpmail_test_receiver_email"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-ns--sub-module__card--row">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e('Message', 'bookingpress-appointment-booking'); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="wpmail_test_msg">    
                                <bp-ui-input class="bpa-form-control" type="textarea" v-model="notification_wpmail_test_mail_form.wpmail_test_msg"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-ns--sub-module__card--row --is-button">
                                <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn__medium" :class="(is_display_send_test_wpmail_mail_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disable_send_test_wpmail_email_btn" @click="bookingpress_send_test_wpmail_email" >                    
                                  <span class="bpa-btn__label"><?php esc_html_e('Send Test Email', 'bookingpress-appointment-booking'); ?></span>
                                  <div class="bpa-btn--loader__circles">                    
                                      <div></div>
                                      <div></div>
                                      <div></div>
                                  </div>
                                </bp-ui-button>    
                            </div>
                        </bp-ui-col>
                    </bp-ui-row>                    
                    <bp-ui-row type="flex" v-if="notification_setting_form.selected_mail_service == 'wp_mail'">                            
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-toast-notification --error" :class="succesfully_send_test_wpmail_email == 1 ? '--success' : ''" v-if="succesfully_send_test_wpmail_email == 1 || error_send_test_wpmail_email == 1">
                                <label class="bpa-text--primary-color" v-if="succesfully_send_test_wpmail_email == 1">
                                    <?php esc_html_e('Test Email Sent Successfully', 'bookingpress-appointment-booking'); ?>
                                </label>
                                <label class="bpa-text--danger-color" v-if="error_send_test_wpmail_email == 1" > {{error_text_of_test_wpmail_email}}
                                </label>
                            </div>                            
                        </bp-ui-col>
                    </bp-ui-row>                                    
                </bp-ui-form>
            </div>
            <!-- for WordPress notification test email end-->

            <div class="bpa-ns--sub-module__card" v-if="notification_setting_form.selected_mail_service == 'smtp'">
                <bp-ui-form :rules="rules_smtp_test_mail" ref="notification_smtp_test_mail_form" :model="notification_smtp_test_mail_form" @submit.native.prevent>                    
                    <h4><?php esc_html_e('Send test email notification', 'bookingpress-appointment-booking'); ?></h4>
                    <bp-ui-row type="flex" class="bpa-ns--sub-module__card--row">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4> <?php esc_html_e('To', 'bookingpress-appointment-booking'); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">                            
                            <bp-ui-form-item prop="smtp_test_receiver_email">
                                <bp-ui-input class="bpa-form-control" type="email" v-model="notification_smtp_test_mail_form.smtp_test_receiver_email"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex" class="bpa-ns--sub-module__card--row">    
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
                            <h4><?php esc_html_e('Message', 'bookingpress-appointment-booking'); ?></h4>
                        </bp-ui-col>
                        <bp-ui-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
                            <bp-ui-form-item prop="smtp_test_msg">    
                                <bp-ui-input class="bpa-form-control" type="textarea" v-model="notification_smtp_test_mail_form.smtp_test_msg"></bp-ui-input>    
                            </bp-ui-form-item>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row type="flex">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-ns--sub-module__card--row --is-button">
                                <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn__medium" :class="(is_display_send_test_mail_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disable_send_test_email_btn" @click="bookingpress_send_test_email" >                    
                                  <span class="bpa-btn__label"><?php esc_html_e('Send Test Email', 'bookingpress-appointment-booking'); ?></span>
                                  <div class="bpa-btn--loader__circles">                    
                                      <div></div>
                                      <div></div>
                                      <div></div>
                                  </div>
                                </bp-ui-button>    
                            </div>
                        </bp-ui-col>
                    </bp-ui-row>                    
                    <bp-ui-row type="flex" v-if="notification_setting_form.selected_mail_service == 'smtp'">                            
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-toast-notification --error" :class="succesfully_send_test_email == 1 ? '--success' : ''" v-if="succesfully_send_test_email == 1 || error_send_test_email == 1">
                                <label class="bpa-text--primary-color" v-if="succesfully_send_test_email == 1">
                                    <?php esc_html_e('Test Email Sent Successfully', 'bookingpress-appointment-booking'); ?>
                                </label>
                                <label class="bpa-text--danger-color" v-if="error_send_test_email == 1" > {{error_text_of_test_email}}
                                </label>
                                <bp-ui-link @click="open_smtp_error_modal()" v-if="error_send_test_email == 1 && smtp_mail_error_text != ''"><?php esc_html_e('Click here to see the full log', 'bookingpress-appointment-booking'); ?></bp-ui-link>
                            </div> 
                        </bp-ui-col>
                    </bp-ui-row> 
                </bp-ui-form>
            </div>    
            <?php
                do_action('bookingpress_add_notification_settings_section_v3');			
            ?> 
        </div>    
    </div>
</bp-ui-tab-pane>

<!-- <el-dialog custom-class="bpa-dialog bpa-dialog--smtp-notification-settings" title="" :visible.sync="smtp_error_modal" :visible.sync="centerDialogVisible" close-on-press-escape="close_modal_on_esc" :modal="smtp_error_modal">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading"><?php esc_html_e('SMTP Test Full Log', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
                <div class="bpa-hw-right-btn-group">
                    <el-button class="bpa-btn bpa-btn__medium" @click="close_smtp_error_modal()">
                        <span>close</span>
                    </el-button>
                </div>
            </el-col>
        </el-row>
    </div>
    <div class="bpa-dialog-body">
        <div class="bpa-dialog--sns__body" v-html="smtp_mail_error_text">
        </div>
    </div>    
</el-dialog> -->

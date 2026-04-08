<div id="customer_add_modal" v-cloak class="bookingpress-customer-dialog-container">
    <bp-ui-dialog v-model="openCustomerModal" fullscreen=true :close-on-press-escape="closeCustomerModalOnEscape" class="bpa-dialog bpa-dialog--fullscreen bpa-dialog--customer-modal bpa--is-page-non-scrollable-mob" :append-to-body="false" :class="openCustomerModal ? '--bpa-active' : ''" :show-close="false">
        <div class="bpa-dialog-heading">
            <bp-ui-row type="flex">
                <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                    <h1 class="bpa-page-heading" v-if="customer.update_id == 0"><?php esc_html_e('Add Customer', 'bookingpress-appointment-booking'); ?></h1>
                    <h1 class="bpa-page-heading" v-else><?php esc_html_e('Edit Customer', 'bookingpress-appointment-booking'); ?></h1>
                </bp-ui-col>
                <bp-ui-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
                    <bp-ui-button class="bpa-btn bpa-btn--primary " :class="is_display_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="saveCustomerDetails" :disabled="is_disabled" >
                        <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                        <div class="bpa-btn--loader__circles">
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </bp-ui-button> 
                    <bp-ui-button class="bpa-btn" @click="closeCustomerDialog()"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                </bp-ui-col>
            </bp-ui-row>
        </div>
        <div class="bpa-dialog-body">
            <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                <div class="bpa-back-loader"></div>
            </div>
            <div class="bpa-form-row">
                <bp-ui-row>
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <div class="bpa-db-sec-heading">
                            <bp-ui-row type="flex" align="middle">
                                <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                    <div class="db-sec-left">
                                        <h2 class="bpa-page-heading"><?php esc_html_e('Basic Details', 'bookingpress-appointment-booking'); ?></h2>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>            
                        <div class="bpa-default-card bpa-db-card">
                            <bp-ui-form ref="customer" :rules="rules" :model="customer" label-position="top" @submit.native.prevent>
                                <bp-ui-row :gutter="24">
                                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-group">
                                        <bp-ui-upload class="bpa-upload-component" ref="avatarRef" action="<?php echo wp_nonce_url(admin_url('admin-ajax.php') . '?action=bookingpress_upload_customer_avatar', 'bookingpress_upload_customer_avatar'); //phpcs:ignore ?>" :auto-upload="true" :on-success="bookingpress_upload_customer_avatar_func" :file-list="customer.avatar_list" :multiple="false" :show-file-list="cusShowFileList" limit="1" :on-exceed="bookingpress_image_upload_limit" :on-error="bookingpress_image_upload_err" :on-remove="bookingpress_remove_customer_avatar" :before-upload="checkUploadedFile" drag>
                                            <span class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
                                            <div class="bpa-upload-component__text" v-if="customer.avatar_url == ''"><?php esc_html_e('Please upload jpg/png/webp file', 'bookingpress-appointment-booking'); ?></div>
                                        </bp-ui-upload>
                                        <div class="bpa-uploaded-avatar__preview"  v-if="customer.avatar_url != ''">
                                            <button class="bpa-avatar-close-icon" @click="bookingpress_remove_customer_avatar">
                                                <span class="material-icons-round">close</span>
                                            </button>
                                            <bp-ui-avatar shape="square" :src="customer.avatar_url" class="bpa-uploaded-avatar__picture"></bp-ui-avatar>
                                        </div>
                                    </bp-ui-col>
                                </bp-ui-row>
                                <div class="bpa-form-body-row bpa-fbr--customer">
                                    <bp-ui-row :gutter="32" type="flex">
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="wp_user">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('WordPress User', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-select class="bpa-form-control" v-model="customer.wp_user" filterable placeholder="<?php esc_html_e( 'Start typing to fetch user.', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_get_existing_user_details($event)" reserve-keyword remote :remote-method="get_wordpress_users" popper-class="bpa-el-select--is-with-modal" v-cancel-read-only>
                                                    <bp-ui-option value="add_new" label="<?php esc_html_e( 'Create New', 'bookingpress-appointment-booking' ); ?>">
                                                        <i class="el-icon-plus"></i><span> <?php esc_html_e( 'Create New', 'bookingpress-appointment-booking' ); ?> </span>
                                                    </bp-ui-option>
                                                    <bp-ui-option v-if="loading_from_server" value="__loading__" :label="bookingpress_loading" disabled>
                                                        <span>{{ bookingpress_loading }}</span>
                                                    </bp-ui-option>
                                                    <bp-ui-option-group v-for="wp_user_list_cat in wpUsersList" :key="wp_user_list_cat.category" :label="wp_user_list_cat.category">
                                                        <bp-ui-option v-for="item in wp_user_list_cat.wp_user_data" :key="item.value" :label="item.label" :value="item.value">
                                                            <span>{{ item.label }}</span>
                                                        </bp-ui-option>
                                                    </bp-ui-option-group>
                                                    <template v-slot:empty>
                                                        <?php esc_html_e( 'Type to search or choose Add New', 'bookingpress-appointment-booking' ); ?> </span>
                                                    </template>
                                                </bp-ui-select>
                                            </bp-ui-form-item>                                                
                                        </bp-ui-col>                                        
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8" v-if="customer.wp_user =='add_new'">
                                            <bp-ui-form-item>
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Password', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-input class="bpa-form-control --bpa-fc-field-pass" type="password" v-model="customer.password" placeholder="<?php esc_html_e('Enter Password', 'bookingpress-appointment-booking'); ?>" :show-password="true" ></bp-ui-input>
                                            </bp-ui-form-item>                                            
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="username">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Username', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-input class="bpa-form-control" v-model="customer.username" id="username" name="username" placeholder="<?php esc_html_e('Enter Username', 'bookingpress-appointment-booking'); ?>" :disabled="customer.update_id != 0 ? true :false"></bp-ui-input>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="firstname">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('First Name', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-input class="bpa-form-control" v-model="customer.firstname" id="firstname" name="firstname" placeholder="<?php esc_html_e('Enter first name', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="lastname">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Last Name', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-input class="bpa-form-control" v-model="customer.lastname" id="lastname" name="lastname" placeholder="<?php esc_html_e('Enter last name', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>
                                            </bp-ui-form-item>
                                        </bp-ui-col>                                            
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="email">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Email', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-input class="bpa-form-control" v-model="customer.email" id="email" name="email" placeholder="<?php esc_html_e('Enter email', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="phone">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-tel-input v-model="customer.phone" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props" :dropdown-options="{ showSearchBox: false, showFlags: true }" ref="bpa_tel_input_field" :mode="vue_tel_mode" :auto-format="vue_tel_auto_format"></bp-ui-tel-input>
                                            </bp-ui-form-item>
                                        </bp-ui-col>            
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="note">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-input class="bpa-form-control" type="textarea" :rows="3" v-model="customer.note"></bp-ui-input>
                                            </bp-ui-form-item>
                                        </bp-ui-col> 
                                    </bp-ui-row>
                                </div>
                            </bp-ui-form>
                        </div>
                    </bp-ui-col>
                </bp-ui-row>
            </div>
        </div>
    </bp-ui-dialog>
</div>
<?php
    
    if ( ! defined( 'ABSPATH' ) ) { exit; }

    global $bookingpress_ajaxurl, $bookingpress_common_date_format;
?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-non-scrollable-mob" id="all-page-main-container">
    <el-row type="flex" class="bpa-mlc-head-wrap">
        <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
            <h1 class="bpa-page-heading"><?php esc_html_e('Manage Customers', 'bookingpress-appointment-booking'); ?></h1>
        </el-col>
        <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <div class="bpa-hw-right-btn-group">
                <el-button class="bpa-btn bpa-btn--primary" @click="open_add_customer_modal()"> 
                    <span class="material-icons-round">add</span> 
                    <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                </el-button>
            </div>
        </el-col>
    </el-row>
    <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
        <div class="bpa-back-loader"></div>
    </div>
    <div id="bpa-main-container">
        <div class="bpa-table-filter">
            <el-row type="flex" :gutter="32">
                <el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="16">
                    <el-input class="bpa-form-control" v-model="customerSearch" placeholder="<?php esc_html_e('Search customer', 'bookingpress-appointment-booking'); ?>"></el-input>
                </el-col>
                <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                    <div class="bpa-tf-btn-group">
                        <el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
                            <?php esc_html_e('Reset', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                        <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadCustomers(true)">
                            <?php esc_html_e('Apply', 'bookingpress-appointment-booking'); ?>
                        </el-button>
						<el-button class="bpa-btn bpa-btn--secondary bpa-btn__medium bpa-btn--full-width" @click="bookingpress_export_customer_data_lite">
							<span class="material-icons-round">open_in_new</span><?php esc_html_e( 'Export', 'bookingpress-appointment-booking' ); ?>
						</el-button>						
						<el-button class="bpa-btn bpa-btn--secondary bpa-btn__medium bpa-btn--full-width" @click="bookingpress_import_customer_data_open">
							<span class="material-icons-round">open_in_new</span><?php esc_html_e( 'Import', 'bookingpress-appointment-booking' ); ?>
						</el-button>
                    </div>
                </el-col>
            </el-row>
        </div>    
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
                        
                        <el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_customer_modal()"> 
                            <span class="material-icons-round">add</span> 
                            <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                    </div>
                </div>
            </el-col>
        </el-row>
        <el-row v-if="items.length > 0">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-container class="bpa-table-container">
                    <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                        <div class="bpa-back-loader"></div>
                    </div>
                    <div class="bpa-tc__wrapper" v-if="current_screen_size == 'desktop'">                        
                        <el-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange">
                            <el-table-column  type="selection"></el-table-column>                                                            
                            <el-table-column  prop="customer_fullname" label="<?php esc_html_e('Full Name', 'bookingpress-appointment-booking'); ?>" sortable sort-by="customer_username">
                                <template slot-scope="scope">                                                        
                                    <el-image class="bpa-table-column-avatar" :src="scope.row.customer_avatar"></el-image>
                                    <label v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</label>
                                    <label v-else-if="scope.row.customer_fullname != ''">{{ scope.row.customer_fullname }}</label>
                                    <label v-else>{{ scope.row.customer_email }}</label>
                                </template>
                            </el-table-column>
                            <el-table-column  prop="customer_email" label="<?php esc_html_e('Email', 'bookingpress-appointment-booking'); ?>" sortable sort-by="customer_email"></el-table-column>
                            <el-table-column  prop="customer_phone" label="<?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?>"></el-table-column>
                            <el-table-column  prop="customer_last_appointment" label="<?php esc_html_e('Recent Appointment', 'bookingpress-appointment-booking'); ?>" sortable>    
                            </el-table-column>
                            <el-table-column align="center" prop="customer_total_appointment" label="<?php esc_html_e('Total Appointments', 'bookingpress-appointment-booking'); ?>">
                                <template slot-scope="scope">
                                    <label>{{ scope.row.customer_total_appointment }}</label>
                                    <div class="bpa-table-actions-wrap">
                                        <div class="bpa-table-actions">                                    
                                            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                <div slot="content">
                                                    <span><?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?></span>
                                                </div>
                                                <el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editCustomerDetails(scope.row.customer_id)">
                                                    <span class="material-icons-round">mode_edit</span>
                                                </el-button>
                                            </el-tooltip>
                                            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                <div slot="content">
                                                    <span><?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?></span>
                                                </div>
                                                <el-popconfirm 
                                                    cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                    confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                    icon="false" 
                                                    title="<?php esc_html_e('Are you sure you want to delete this customer?', 'bookingpress-appointment-booking'); ?>" 
                                                    @confirm="deleteCustomer(scope.row.customer_id)" 
                                                    confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                    cancel-button-type="bpa-btn bpa-btn__small">
                                                    <el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                        <span class="material-icons-round">delete</span>
                                                    </el-button>
                                                </el-popconfirm>
                                            </el-tooltip>
                                        </div>
                                    </div>
                                </template>                        
                            </el-table-column>
                        </el-table>
                    </div>
                    <div class="bpa-tc__wrapper" v-if="current_screen_size == 'tablet'">
                        <el-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange">
                            <el-table-column  type="selection"></el-table-column>                                                            
                            <el-table-column  prop="customer_fullname" label="<?php esc_html_e('Full Name', 'bookingpress-appointment-booking'); ?>" sortable sort-by="customer_username">
                                <template slot-scope="scope">                                                        
                                    <el-image class="bpa-table-column-avatar" :src="scope.row.customer_avatar"></el-image>
                                    <label v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</label>
                                    <label v-else>{{ scope.row.customer_email }}</label>
                                </template>
                            </el-table-column>
                            <el-table-column  prop="customer_email" label="<?php esc_html_e('Email', 'bookingpress-appointment-booking'); ?>" sortable sort-by="customer_email"></el-table-column>
                            <el-table-column  prop="customer_phone" label="<?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?>">
                                <template slot-scope="scope">
                                    <label>{{ scope.row.customer_phone }}</label>
                                    <div class="bpa-table-actions-wrap">
                                        <div class="bpa-table-actions">                                    
                                            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                <div slot="content">
                                                    <span><?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?></span>
                                                </div>
                                                <el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editCustomerDetails(scope.row.customer_id)">
                                                    <span class="material-icons-round">mode_edit</span>
                                                </el-button>
                                            </el-tooltip>
                                            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                <div slot="content">
                                                    <span><?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?></span>
                                                </div>
                                                <el-popconfirm 
                                                    cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                    confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                    icon="false" 
                                                    title="<?php esc_html_e('Are you sure you want to delete this customer?', 'bookingpress-appointment-booking'); ?>" 
                                                    @confirm="deleteCustomer(scope.row.customer_id)" 
                                                    confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                    cancel-button-type="bpa-btn bpa-btn__small">
                                                    <el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                        <span class="material-icons-round">delete</span>
                                                    </el-button>
                                                </el-popconfirm>
                                            </el-tooltip>
                                        </div>
                                    </div>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                    <div class="bpa-tc__wrapper bpa-manage-customer-container--sm" v-if="current_screen_size == 'mobile'">
                        <el-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange" :show-header="false">
                            <el-table-column type="selection"></el-table-column>                                                            
                            <el-table-column>
                                <template slot-scope="scope">                                                        
                                    <div class="bpa-mcc__item-row-head">
                                        <el-image class="bpa-table-column-avatar" :src="scope.row.customer_avatar"></el-image>
                                        <label v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">
                                            {{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}
                                        </label>
                                        <label v-else>{{ scope.row.customer_email }}</label>
                                    </div>
                                    <p class="bpa-mcc__item-row-sm">{{ scope.row.customer_email }}</p>
                                    <p class="bpa-mcc__item-row-sm">{{ scope.row.customer_phone }}</p>
                                    <p class="bpa-mcc__item-row-sm">
                                        <span><?php esc_html_e('Recent Appointment:', 'bookingpress-appointment-booking'); ?></span> 
                                        {{ scope.row.customer_last_appointment }}
                                    </p>
                                    <p class="bpa-mcc__item-row-sm"><span>
                                        <?php esc_html_e('Total Appointments:', 'bookingpress-appointment-booking'); ?></span> 
                                        {{ scope.row.customer_total_appointment }}
                                    </p>
                                    <div class="bpa-mcc__item-btns-sm">
                                        <el-button class="bpa-btn bpa-btn__small bpa-btn__filled-light" @click.native.prevent="editCustomerDetails(scope.row.customer_id)">
                                            <span class="material-icons-round">mode_edit</span>
                                            <?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?>
                                        </el-button>
                                        <el-popconfirm 
                                            cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                            confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                            icon="false" 
                                            title="<?php esc_html_e('Are you sure you want to delete this customer?', 'bookingpress-appointment-booking'); ?>" 
                                            @confirm="deleteCustomer(scope.row.customer_id)" 
                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                            cancel-button-type="bpa-btn bpa-btn__small">
                                            <el-button type="text" slot="reference" class="bpa-btn bpa-btn__small bpa-btn__filled-light __danger">
                                                <span class="material-icons-round">delete</span>
                                                <?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>
                                            </el-button>
                                        </el-popconfirm>                                                                                
                                    </div>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </el-container>
            </el-col>
        </el-row>
        <el-row class="bpa-pagination" type="flex" v-if="items.length > 0"> <!-- Pagination -->
            <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
                <div class="bpa-pagination-left">
                    <p><?php esc_html_e('Showing', 'bookingpress-appointment-booking'); ?> <strong><u>{{ items.length }}</u></strong>&nbsp;<?php esc_html_e('out of', 'bookingpress-appointment-booking'); ?>&nbsp;<strong>{{ totalItems }}</strong></p>
                    <div class="bpa-pagination-per-page">
                        <p><?php esc_html_e('Per Page', 'bookingpress-appointment-booking'); ?></p>
                        <el-select v-model="pagination_length_val" placeholder="Select" @change="changePaginationSize($event)" class="bpa-form-control" popper-class="bpa-pagination-dropdown">
                            <el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
                        </el-select>
                    </div>
                </div>
            </el-col>
            <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
                <el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></el-pagination>
            </el-col>
            <el-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card">
                <el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="closeBulkAction">
                    <span class="material-icons-round">close</span>
                </el-button>
                <el-row type="flex" class="bpa-bac__wrapper">
                    <el-col class="bpa-bac__left-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                        <span class="material-icons-round">check_circle</span>
                        <p>{{ multipleSelection.length }}<?php esc_html_e(' Items Selected', 'bookingpress-appointment-booking'); ?></p>
                    </el-col>
                    <el-col class="bpa-bac__right-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                        <el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?>"
                        popper-class="bpa-dropdown--bulk-actions">
                            <el-option v-for="item in bulk_options" :key="item.value" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                        <el-button @click="bulk_actions" class="bpa-btn bpa-btn--primary bpa-btn__medium">
                            <?php esc_html_e('Go', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                    </el-col>
                </el-row>
            </el-container>        
        </el-row>
    </div>
</el-main>
<!-- Customer Modal -->

<el-dialog id="customer_add_modal" custom-class="bpa-dialog bpa-dialog--fullscreen bpa-dialog--customer-modal bpa--is-page-non-scrollable-mob" modal-append-to-body=false :visible.sync="open_customer_modal" :before-close="closeCustomerModal" fullscreen=true :close-on-press-escape="close_modal_on_esc">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
        <h1 class="bpa-page-heading" v-if="customer.update_id == 0"><?php esc_html_e('Add Customer', 'bookingpress-appointment-booking'); ?></h1>
        <h1 class="bpa-page-heading" v-else><?php esc_html_e('Edit Customer', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
                <el-button class="bpa-btn bpa-btn--primary " :class="is_display_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="saveCustomerDetails" :disabled="is_disabled" >
                    <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                    <div class="bpa-btn--loader__circles">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </el-button> 
                <el-button class="bpa-btn" @click="closeCustomerModal()"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></el-button>
            </el-col>
        </el-row>
    </div>
    
    <div class="bpa-dialog-body">
        <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <div class="bpa-form-row">
            <el-row>
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <div class="bpa-db-sec-heading">
                        <el-row type="flex" align="middle">
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <div class="db-sec-left">
                                    <h2 class="bpa-page-heading"><?php esc_html_e('Basic Details', 'bookingpress-appointment-booking'); ?></h2>
                                </div>
                            </el-col>
                        </el-row>
                    </div>            
                    <div class="bpa-default-card bpa-db-card">
                        <el-form ref="customer" :rules="rules" :model="customer" label-position="top" @submit.native.prevent>
                            <template>                            
                                <el-row :gutter="24">
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-group">
                                        <el-upload class="bpa-upload-component" ref="avatarRef" action="<?php echo wp_nonce_url(admin_url('admin-ajax.php') . '?action=bookingpress_upload_customer_avatar', 'bookingpress_upload_customer_avatar'); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - esc_html is already used by wp_nonce_url function and it's false positive ?>" :on-success="bookingpress_upload_customer_avatar_func" :file-list="customer.avatar_list" multiple="false" :show-file-list="cusShowFileList" limit="1" :on-exceed="bookingpress_image_upload_limit" :on-error="bookingpress_image_upload_err" :on-remove="bookingpress_remove_customer_avatar" :before-upload="checkUploadedFile" drag>
                                            <span class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
                                           <div class="bpa-upload-component__text" v-if="customer.avatar_url == ''"><?php esc_html_e('Please upload jpg/png/webp file', 'bookingpress-appointment-booking'); ?>                                           
                                           </div>
                                        </el-upload>
                                        <div class="bpa-uploaded-avatar__preview"  v-if="customer.avatar_url != ''">
                                            <button class="bpa-avatar-close-icon" @click="bookingpress_remove_customer_avatar">
                                                <span class="material-icons-round">close</span>
                                            </button>
                                            <el-avatar shape="square" :src="customer.avatar_url" class="bpa-uploaded-avatar__picture"></el-avatar>
                                        </div>
                                    </el-col>
                                </el-row>
                                <div class="bpa-form-body-row bpa-fbr--customer">
                                    <el-row :gutter="32" type="flex">
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="wp_user">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('WordPress User', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-select class="bpa-form-control" v-model="customer.wp_user" filterable placeholder="<?php esc_html_e( 'Start typing to fetch user.', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_get_existing_user_details($event)" reserve-keyword remote	:remote-method="get_wordpress_users"  :loading="bookingpress_loading" popper-class="bpa-el-select--is-with-modal" v-cancel-read-only>
													<el-option-group label="<?php esc_html_e( 'Create New User', 'bookingpress-appointment-booking' ); ?>">
														<template>
															<el-option value="add_new" label="<?php esc_html_e( 'Create New', 'bookingpress-appointment-booking' ); ?>">
																<i class="el-icon-plus" ></i>
																<span><?php esc_html_e( 'Create New', 'bookingpress-appointment-booking' ); ?></span>
															</el-option>
														</template>
													</el-option-group>
													<el-option-group v-for="wp_user_list_cat in wpUsersList" :key="wp_user_list_cat.category" :label="wp_user_list_cat.category">
														<template>
															<el-option v-for="item in wp_user_list_cat.wp_user_data" :key="item.value" :label="item.label" :value="item.value" >
																<span>{{ item.label }}</span>
															</el-option>
														</template>
													</el-option-group>
												</el-select>
                                            </el-form-item>                                                
                                        </el-col>                                        
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8" v-if="customer.wp_user =='add_new'">
                                            <el-form-item>
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Password', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-input class="bpa-form-control --bpa-fc-field-pass" type="password" v-model="customer.password" placeholder="<?php esc_html_e('Enter Password', 'bookingpress-appointment-booking'); ?>" :show-password="true" ></el-input>
                                            </el-form-item>                                            
                                        </el-col>
                                            <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="username">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Username', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-input class="bpa-form-control" v-model="customer.username" id="username" name="username" placeholder="<?php esc_html_e('Enter Username', 'bookingpress-appointment-booking'); ?>" :disabled="customer.update_id != 0 ? true :false"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        </el-col>
                                            <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="firstname">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('First Name', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-input class="bpa-form-control" v-model="customer.firstname" id="firstname" name="firstname" placeholder="<?php esc_html_e('Enter first name', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="lastname">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Last Name', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-input class="bpa-form-control" v-model="customer.lastname" id="lastname" name="lastname" placeholder="<?php esc_html_e('Enter last name', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>
                                        </el-col>                                            
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="email">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Email', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-input class="bpa-form-control" v-model="customer.email" id="email" name="email" placeholder="<?php esc_html_e('Enter email', 'bookingpress-appointment-booking'); ?>"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="phone">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <vue-tel-input v-model="customer.phone" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props" ref="bpa_tel_input_field" :mode="vue_tel_mode" :auto-format="vue_tel_auto_format">
                                                    <template v-slot:arrow-icon>
                                                        <span class="material-icons-round">keyboard_arrow_down</span>
                                                    </template>
                                                </vue-tel-input>
                                            </el-form-item>
                                        </el-col>            
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="note">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-input class="bpa-form-control" type="textarea" :rows="3" v-model="customer.note"></el-input>
                                            </el-form-item>
                                        </el-col> 
                                    </el-row>
                                </div>
                            </template>
                        </el-form>
                    </div>
                </el-col>
            </el-row>
        </div>
    </div>
</el-dialog>

<!-- Export Popup Start-->
<el-dialog custom-class="bpa-dialog bpa-dailog__small bpa-dialog--export-customers" id="customer_export_model" title="" :visible.sync="ExportCustomerLite" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Export Data', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">				
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item>
											  <el-checkbox-group v-model="export_checked_field_lite">									  						 
                                                <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-for="item in customer_export_field_list_lite" :label="item.name">{{item.text}}</el-checkbox>
											  </el-checkbox-group>									  
										</el-form-item>
									</el-col> 										
								</el-row>
							</div>
						</el-form>
					</el-col>
				</el-row>
			</div>
		</el-container>
	</div>
	<div class="bpa-dialog-footer">
		<div class="bpa-hw-right-btn-group">
			<el-button class="bpa-btn bpa-btn__medium" @click="close_export_customer_lite_model" ><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :class="(is_export_button_loader_lite == '1') ? 'bpa-btn--is-loader' : ''" @click="
			bookingpress_export_customer_lite" :disabled="is_export_button_disabled_lite" >					
			  <span class="bpa-btn__label"><?php esc_html_e( 'Export', 'bookingpress-appointment-booking' ); ?></span>
			  <div class="bpa-btn--loader__circles">				    
				  <div></div>
				  <div></div>
				  <div></div>
			  </div>
			</el-button>
		</div>
	</div>
</el-dialog>
<!-- Export Popup Over -->

<!-- Import Customer Popup -->
<el-drawer custom-class="bpa-drawer__import-customer" modal-append-to-body="false" @close="resetImportModal('customer_form_import')" :visible.sync="import_customer_modal">
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
            
            <el-row type="flex" class="bpa-bac__wrapper">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">                    
                    <div class="bpa-dlt__form_title bpa-form-label"><?php esc_html_e('Upload csv file', 'bookingpress-appointment-booking'); ?></div>
                    <el-upload class="bpa-form-control-import-file" ref="importCustomerRef" action="<?php echo wp_nonce_url(admin_url('admin-ajax.php') . '?action=bookingpress_upload_import_customer_file', 'bookingpress_upload_import_customer_file'); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - esc_html is already used by wp_nonce_url function and it's false positive ?>" :on-success="bookingpress_upload_customer_import_file_func" :file-list="import_file_list" multiple="false" show-file-list="true" limit="1" :on-exceed="bookingpress_image_upload_limit" :on-error="bookingpress_image_upload_err" :on-remove="bookingpress_remove_import_file" :before-upload="checkUploadedImportFile" drag>                
                        <label for="bpa-file-upload-two" class="bpa-form-control--file-upload-import">
                            <span class="bpa-fu__placeholder"><?php esc_html_e( 'Choose a file...', 'bookingpress-appointment-booking' ); ?></span>
                            <span class="bpa-fu__btn"> <?php esc_html_e( 'Upload', 'bookingpress-appointment-booking' ); ?></span> 
                        </label>
                    </el-upload>
                </el-col>
            </el-row>
            <el-row type="flex" class="bpa-bac__wrapper">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">

                    <el-form v-if="import_file_name != ''" ref="customer_form_import" :rules="bookingpress_customer_import_rules" require-asterisk-position="right" :model="bookingpress_import_fields" label-position="top">
                        <div v-if="bookingpress_import_field_data.lenght != 0" class="bpa-import-field-data">
                            
                            <el-row type="flex" class="bpa-import-field-body">
                                <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-import-field__left-area">
                                    <div class="bpa-form-label"><?php esc_html_e( 'Create WordPress User', 'bookingpress-appointment-booking' ); ?></div>
                                </el-col>
                                <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-import-field__right-area">                            
                                    <el-switch class="bpa-swtich-control" v-model="is_wordpress_user_create_on_import"></el-switch>
                                </el-col>
                            </el-row>                   
                            <el-row type="flex" class="bpa-import-field-head">
                                <el-col class="bpa-import-field__left-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">                                
                                    <div class="bpa-head-fields"><?php esc_html_e('Customer Field', 'bookingpress-appointment-booking'); ?></div>
                                </el-col>
                                <el-col class="bpa-import-field__right-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                    <div class="bpa-head-fields"><?php esc_html_e('CSV Column', 'bookingpress-appointment-booking'); ?></div>                              
                                </el-col>
                            </el-row>
                            <el-row v-for="import_field in bookingpress_import_field_data" v-if="(import_field.is_userfield == '1' && is_wordpress_user_create_on_import == true) || (import_field.is_userfield == '0')" type="flex" class="bpa-import-field-body">
                                <el-col class="bpa-import-field__left-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">                                
                                    <div :class="(import_field.is_required == '1')?'bpa-req-imp-fld':''" class="bpa-import-fields-label">{{import_field.field_label}}</div>
                                </el-col>
                                <el-col class="bpa-import-field__right-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                    <div class="bpa-import-fields-select">
                                        <el-form-item :prop="import_field.field_key">                                        
                                            <el-select class="bpa-form-control" :popper-append-to-body="false" v-model="bookingpress_import_fields[import_field.field_key]" placeholder="<?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?>">
                                                <el-option label="<?php esc_html_e('- Ignore this field -', 'bookingpress-appointment-booking'); ?>" value=""></el-option>                                                
                                                <el-option v-for="item in import_file_fields" :key="item.key" :label="item.value" :value="item.key"></el-option>
                                            </el-select>
                                        </el-form-item>
                                    </div>                              
                                </el-col>
                            </el-row>

                        </div>
                    </el-form>                    

                </el-col>
            </el-row>



		</div>
	</div>
	<div class="bpa-dlt__footer-import">
        <el-button @click="import_customer_modal = false;" class="bpa-btn el-button--default"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>	
        <el-button  v-if="import_file_name != '' && complete_import == '0'" :class="(import_loading == '1') ? 'bpa-btn--is-loader' : ''" @click="importCustomer('customer_form_import')"  class="bpa-btn bpa-btn--primary" @click="">                    
            <span class="bpa-btn__label"><?php esc_html_e('Proceed', 'bookingpress-appointment-booking'); ?></span>
            <div class="bpa-btn--loader__circles">                    
                <div></div>
                <div></div>
                <div></div>
            </div>
        </el-button>
	</div> 
</el-drawer>
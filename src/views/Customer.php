<div id="customer_root_app" class="bookingpress-customer bookingpress_page_wrapper">
    <?php
        if( class_exists( '\BookingPressPro\admin\Customer') && method_exists( '\BookingPressPro\admin\Customer', 'render_customer_header' ) ){
            \BookingPressPro\admin\Customer::render_customer_header();
        } else {
            require_once __DIR__ . '/components/Header.php';
        }
    ?>
    <div class="customer-app-root bookingpress_page_inner_wrapper" v-cloak id="customer-app-root">
        <bp-ui-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-non-scrollable-mob" id="all-page-main-container">
            <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                <bp-ui-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
                    <h1 class="bpa-page-heading"><?php esc_html_e('Manage Customers', 'bookingpress-appointment-booking'); ?></h1>
                </bp-ui-col>
                <bp-ui-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                    <div class="bpa-hw-right-btn-group">
                        <bp-ui-button class="bpa-btn bpa-btn--primary" @click="open_add_customer_modal()"> 
                            <span class="material-icons-round">add</span> 
                            <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                        </bp-ui-button>
                    </div>
                </bp-ui-col>
            </bp-ui-row>
            <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
                <div class="bpa-back-loader"></div>
            </div>
            <div id="bpa-main-container">
                <div class="bpa-table-filter">
                    <bp-ui-row type="flex" :gutter="32">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="16" :xl="16">
                            <bp-ui-input class="bpa-form-control" v-model="customerSearch" placeholder="<?php esc_html_e('Search customer', 'bookingpress-appointment-booking'); ?>"></bp-ui-input>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                            <div class="bpa-tf-btn-group">
                                <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
                                    <?php esc_html_e('Reset', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                                <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadCustomers(true)">
                                    <?php esc_html_e('Apply', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                                <bp-ui-button class="bpa-btn bpa-btn--secondary bpa-btn__medium bpa-btn--full-width" @click="bookingpress_export_customer_data_lite">
                                    <span class="material-icons-round">open_in_new</span><?php esc_html_e( 'Export', 'bookingpress-appointment-booking' ); ?>
                                </bp-ui-button>						
                                <bp-ui-button class="bpa-btn bpa-btn--secondary bpa-btn__medium bpa-btn--full-width" @click="bookingpress_import_customer_data_open">
                                    <span class="material-icons-round">open_in_new</span><?php esc_html_e( 'Import', 'bookingpress-appointment-booking' ); ?>
                                </bp-ui-button>
                            </div>
                        </bp-ui-col>
                    </bp-ui-row>
                </div>
                <bp-ui-row type="flex" v-if="items.length == 0">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <div class="bpa-data-empty-view">
                            <div class="bpa-ev-left-vector">
                                <picture>
                                    <source srcset="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp'); ?>" type="image/webp">
                                    <img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png'); ?>">
                                </picture>
                            </div>
                            <div class="bpa-ev-right-content">
                                <h4><?php esc_html_e('No Record Found!', 'bookingpress-appointment-booking'); ?></h4>                                
                                <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_customer_modal()"> 
                                    <span class="material-icons-round">add</span> 
                                    <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                            </div>
                        </div>
                    </bp-ui-col>
                </bp-ui-row>
                <bp-ui-row v-if="items.length > 0">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <bp-ui-container class="bpa-table-container">
                            <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                                <div class="bpa-back-loader"></div>
                            </div>
                            <div class="bpa-tc__wrapper" >         
                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'desktop'">           
                                <bp-ui-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange">
                                    <bp-ui-table-column min-width="30" type="selection"></bp-ui-table-column>           
                                    <bp-ui-table-column prop="customer_fullname" label="<?php esc_html_e('Full Name', 'bookingpress-appointment-booking'); ?>" sortable sort-by="customer_username">
                                        <template #default="scope">
                                            <bp-ui-image class="bpa-table-column-avatar" :src="scope.row.customer_avatar"></bp-ui-image>
                                            <label v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</label>
                                            <label v-else-if="scope.row.customer_fullname != ''">{{ scope.row.customer_fullname }}</label>
                                            <label v-else>{{ scope.row.customer_email }}</label>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column  prop="customer_email" label="<?php esc_html_e('Email', 'bookingpress-appointment-booking'); ?>" sortable sort-by="customer_email"></bp-ui-table-column>
                                    <bp-ui-table-column  prop="customer_phone" label="<?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?>"></bp-ui-table-column>
                                    <bp-ui-table-column  prop="customer_last_appointment" label="<?php esc_html_e('Recent Appointment', 'bookingpress-appointment-booking'); ?>" sortable>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column align="center" prop="customer_total_appointment" label="<?php esc_html_e('Total Appointments', 'bookingpress-appointment-booking'); ?>">
                                        <template #default="scope">
                                            <label>{{ scope.row.customer_total_appointment }}</label>
                                            <div class="bpa-table-actions-wrap">
                                                <div class="bpa-table-actions">                                    
                                                    <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                        <template #content>
                                                            <span><?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?></span>
                                                        </template>
                                                        <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editCustomerDetails(scope.row.customer_id)">
                                                            <span class="material-icons-round">mode_edit</span>
                                                        </bp-ui-button>
                                                    </bp-ui-tooltip>
                                                    <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                        <template #content>
                                                            <span><?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?></span>
                                                        </template>
                                                        <bp-ui-popconfirm 
                                                            cancbp-ui-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                            confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                            icon="false" 
                                                            title="<?php esc_html_e('Are you sure you want to delete this customer?', 'bookingpress-appointment-booking'); ?>" 
                                                            @confirm="deleteCustomer(scope.row.customer_id)" 
                                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                            cancbp-ui-button-type="bpa-btn bpa-btn__small">
                                                            <bp-ui-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                <span class="material-icons-round">delete</span>
                                                            </bp-ui-button>
                                                        </bp-ui-popconfirm>
                                                    </bp-ui-tooltip>
                                                </div>
                                            </div>
                                        </template>                  
                                    </bp-ui-table-column>
                                </bp-ui-table>
                            </div>
                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'tablet'">
                                <bp-ui-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange">
                                    <bp-ui-table-column  type="selection"></bp-ui-table-column>                                                            
                                    <bp-ui-table-column  prop="customer_fullname" label="<?php esc_html_e('Full Name', 'bookingpress-appointment-booking'); ?>" sortable sort-by="customer_username">
                                        <template #default="scope">                                                        
                                            <bp-ui-image class="bpa-table-column-avatar" :src="scope.row.customer_avatar"></bp-ui-image>
                                            <label v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</label>
                                            <label v-else>{{ scope.row.customer_email }}</label>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column  prop="customer_email" label="<?php esc_html_e('Email', 'bookingpress-appointment-booking'); ?>" sortable sort-by="customer_email"></bp-ui-table-column>
                                    <bp-ui-table-column  prop="customer_phone" label="<?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?>">
                                        <template #default="scope">
                                            <label>{{ scope.row.customer_phone }}</label>
                                            <div class="bpa-table-actions-wrap">
                                                <div class="bpa-table-actions">                                    
                                                    <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                        <template #content>
                                                            <span><?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?></span>
                                                        </template>
                                                        <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editCustomerDetails(scope.row.customer_id)">
                                                            <span class="material-icons-round">mode_edit</span>
                                                        </bp-ui-button>
                                                    </bp-ui-tooltip>
                                                    <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                        <template #content>
                                                            <span><?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?></span>
                                                        </template>
                                                        <bp-ui-popconfirm 
                                                            cancbp-ui-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                            confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                            icon="false" 
                                                            title="<?php esc_html_e('Are you sure you want to delete this customer?', 'bookingpress-appointment-booking'); ?>" 
                                                            @confirm="deleteCustomer(scope.row.customer_id)" 
                                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                            cancbp-ui-button-type="bpa-btn bpa-btn__small">
                                                            <bp-ui-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                <span class="material-icons-round">delete</span>
                                                            </bp-ui-button>
                                                        </bp-ui-popconfirm>
                                                    </bp-ui-tooltip>
                                                </div>
                                            </div>
                                        </template>
                                    </bp-ui-table-column> 
                                </bp-ui-table>
                            </div>
                            <div class="bpa-tc__wrapper bpa-manage-customer-container--sm" v-if="current_screen_size == 'mobile'">
                                <bp-ui-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange" :show-header="false">
                                    <bp-ui-table-column type="selection"></bp-ui-table-column>                                                            
                                    <bp-ui-table-column>
                                        <template #default="scope">                                                        
                                            <div class="bpa-mcc__item-row-head">
                                                <bp-ui-image class="bpa-table-column-avatar" :src="scope.row.customer_avatar"></bp-ui-image>
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
                                                <bp-ui-button class="bpa-btn bpa-btn__small bpa-btn__filled-light" @click.prevent="editCustomerDetails(scope.row.customer_id)">
                                                    <span class="material-icons-round">mode_edit</span>
                                                    <?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?>
                                                </bp-ui-button>
                                                <bp-ui-popconfirm 
                                                    cancbp-ui-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                                    confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                                    icon="false" 
                                                    title="<?php esc_html_e('Are you sure you want to delete this customer?', 'bookingpress-appointment-booking'); ?>" 
                                                    @confirm="deleteCustomer(scope.row.customer_id)" 
                                                    confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                    cancbp-ui-button-type="bpa-btn bpa-btn__small">
                                                    <bp-ui-button type="text" slot="reference" class="bpa-btn bpa-btn__small bpa-btn__filled-light __danger">
                                                        <span class="material-icons-round">delete</span>
                                                        <?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>
                                                    </bp-ui-button>
                                                </bp-ui-popconfirm>                                                                                
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                </bp-ui-table>
                            </div>
                        </bp-ui-container>
                    </bp-ui-col>
                </bp-ui-row>
                <bp-ui-row class="bpa-pagination" type="flex" v-if="items.length > 0"> 
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
                        <div class="bpa-pagination-left">
                            <p><?php esc_html_e('Showing', 'bookingpress-appointment-booking'); ?> <strong><u>{{ items.length }}</u></strong>&nbsp;<?php esc_html_e('out of', 'bookingpress-appointment-booking'); ?>&nbsp;<strong>{{ totalItems }}</strong></p>
                            <div class="bpa-pagination-per-page">
                                <p><?php esc_html_e('Per Page', 'bookingpress-appointment-booking'); ?></p>
                                <bp-ui-select v-model="pagination_length_val" placeholder="Select" @change="changePaginationSize($event)" class="bpa-form-control" popper-class="bpa-pagination-dropdown">
                                    <bp-ui-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></bp-ui-option>
                                </bp-ui-select>
                            </div>
                        </div>
                    </bp-ui-col>
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
                        <bp-ui-pagination  :key="perPage" @size-change="handleSizeChange" @current-change="handleCurrentChange" v-model:current-page="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></bp-ui-pagination>
                    </bp-ui-col>
                    <bp-ui-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card">
                        <bp-ui-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="closeBulkAction">
                            <span class="material-icons-round">close</span>
                        </bp-ui-button>
                        <bp-ui-row type="flex" class="bpa-bac__wrapper">
                            <bp-ui-col class="bpa-bac__left-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                <span class="material-icons-round">check_circle</span>
                                <p>{{ multipleSelection.length }}<?php esc_html_e(' Items Selected', 'bookingpress-appointment-booking'); ?></p>
                            </bp-ui-col>
                            <bp-ui-col class="bpa-bac__right-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                <bp-ui-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?>"
                                popper-class="bpa-dropdown--bulk-actions">
                                    <bp-ui-option v-for="item in bulk_options" :key="item.value" :label="item.label" :value="item.value"></bp-ui-option>
                                </bp-ui-select>
                                <bp-ui-button @click="bulk_actions" class="bpa-btn bpa-btn--primary bpa-btn__medium">
                                    <?php esc_html_e('Go', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                            </bp-ui-col>
                        </bp-ui-row>
                    </bp-ui-container>        
                </bp-ui-row>    
            </div>   
        </bp-ui-main>
    </div>          
</div>


<?php

if( class_exists( 'BookingPressPro\admin\Customer' ) && method_exists( 'BookingPressPro\admin\Customer', 'getCustomerViewComponents' ) ) {
    \BookingPressPro\admin\Customer::getCustomerViewComponents();
} else {    
    require_once __DIR__ . '/components/CustomerModel.php';
}
require_once __DIR__ . '/components/CustomerExportModel.php';
require_once __DIR__ . '/components/CustomerImportModel.php';
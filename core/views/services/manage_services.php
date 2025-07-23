<?php
    if ( ! defined( 'ABSPATH' ) ) { exit; }
    global $bookingpress_ajaxurl;
?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
    <el-row type="flex" class="bpa-mlc-head-wrap __services-screen">
        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-mlc-left-heading">
            <h1 class="bpa-page-heading"><?php esc_html_e('Manage Services', 'bookingpress-appointment-booking'); ?></h1>
        </el-col>
        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
            <div class="bpa-hw-right-btn-group">
                <el-button class="bpa-btn bpa-btn--primary" @click="open_add_service_modal('add')">
                    <span class="material-icons-round">add</span>
                    <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                </el-button>
                <el-button class="bpa-btn bpa-btn--default" @click="open_manage_category_modal = true">
                    <span class="material-icons-round">dns</span>
                    <?php esc_html_e('Manage Categories', 'bookingpress-appointment-booking'); ?>
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
                <el-col :xs="24" :sm="24" :md="24" :lg="9" :xl="10">
                    <span class="bpa-form-label"><?php esc_html_e('Service Name', 'bookingpress-appointment-booking'); ?></span>
                    <el-input class="bpa-form-control" v-model="search_service_name"
                        placeholder="<?php esc_html_e('Enter service name', 'bookingpress-appointment-booking'); ?>">
                    </el-input>
                </el-col>
                <el-col :xs="24" :sm="24" :md="24" :lg="9" :xl="10">
                    <span class="bpa-form-label"><?php esc_html_e('Service Category', 'bookingpress-appointment-booking'); ?></span>
                    <el-select class="bpa-form-control bpa-from-select-tab" v-model="search_service_category"
                        placeholder="<?php esc_html_e('Select category', 'bookingpress-appointment-booking'); ?>"
                        :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
                        <el-option v-for="item in search_categories" :key="item.bookingpress_category_id"
                            :label="item.bookingpress_category_name" :value="item.bookingpress_category_id"></el-option>
                    </el-select>
                </el-col>
                <el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="4">
                    <div class="bpa-tf-btn-group">
                        <el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
                            <?php esc_html_e('Reset', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                        <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width"
                            @click="loadServices">
                            <?php esc_html_e('Apply', 'bookingpress-appointment-booking'); ?>
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
                            <source srcset="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp'); ?>"
                                type="image/webp">
                            <img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png'); ?>">
                        </picture>
                    </div>
                    <div class="bpa-ev-right-content">
                        <h4><?php esc_html_e('No Record Found!', 'bookingpress-appointment-booking'); ?></h4>
                        
                        <el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_service_modal('add')"> 
                            <span class="material-icons-round">add</span>
                            <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                    </div>
                </div>
            </el-col>
        </el-row>
        <el-container class="bpa-grid-list-container bpa-grid-list--service-page">
            <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                <div class="bpa-back-loader"></div>
            </div>
            <el-row v-if="items.length > 0">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <div class="bpa-card bpa-card__heading-row">
                        <el-row type="flex">
                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <div class="bpa-card__item bpa-card__item--ischecbox">
                                    <el-checkbox v-model="is_multiple_checked" @change="selectAllServices($event)"></el-checkbox>
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Name', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <div class="bpa-card__item">
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Category', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <div class="bpa-card__item">
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Duration', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <div class="bpa-card__item">
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Price', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                        </el-row>
                    </div>
                </el-col>
                <draggable :list="items" :disabled="!enabled" class="list-group" ghost-class="ghost" @start="dragging = true" @end="updateServicePos($event)">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="items_data in items" :data-service_id="items_data.service_id">
                        <div class="bpa-card bpa-card__body-row list-group-item">
                            <div class="bpa-card__item--drag-icon-wrap">
                                <span class="material-icons-round">drag_indicator</span>
                            </div>
                            <el-row type="flex">
                                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                    <div class="bpa-card__item bpa-card__item--ischecbox">
                                        <el-tooltip effect="dark" content="" placement="top" v-if="items_data.service_bulk_action">
                                            <div slot="content">
                                                <span><?php esc_html_e('One or more appointments are associated with this service,', 'bookingpress-appointment-booking'); ?></span><br/>
                                                <span><?php esc_html_e('so you will not be able to delete it', 'bookingpress-appointment-booking'); ?></span>
                                            </div>
                                            <el-checkbox v-model="items_data.selected" :disabled=items_data.service_bulk_action @change="handleSelectionChange(event, $event, items_data.service_id)"></el-checkbox>
                                        </el-tooltip>
                                        <el-checkbox v-model="items_data.selected" :disabled=items_data.service_bulk_action @change="handleSelectionChange(event, $event, items_data.service_id)" v-else></el-checkbox>
                                        <img :src="items_data.service_img_details" class="bpa-card__item--service-thumbnail" v-if="items_data.service_img_details != ''">
                                        <h4 class="bpa-card__item__heading is--body-heading"> <span v-html="items_data.service_name"></span> <span class="bpa-card__item--id">(<?php esc_html_e('ID', 'bookingpress-appointment-booking'); ?>: {{ items_data.service_id }} )</span></h4>
                                    </div>
                                </el-col>
                                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                    <div class="bpa-card__item">
                                        <h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_category }}</h4>
                                    </div>
                                </el-col>
                                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                    <div class="bpa-card__item">
                                        <h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_duration }}</h4>
                                    </div>
                                </el-col>
                                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                    <div class="bpa-card__item">
                                        <h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_price }}</h4>
                                    </div>
                                </el-col>
                            </el-row>
                            <div class="bpa-table-actions-wrap">
                                <div class="bpa-table-actions">
                                    <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                        <div slot="content">
                                            <span><?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?></span>
                                        </div>
                                        <el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editServiceData(items_data.service_id)">
                                            <span class="material-icons-round">mode_edit</span>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                        <div slot="content">
                                            <span><?php esc_html_e('Duplicate', 'bookingpress-appointment-booking'); ?></span>
                                        </div>
                                        <el-button class="bpa-btn bpa-btn--icon-without-box __secondary" @click.native.prevent="bookingpress_duplicate_service(items_data.service_id)">
                                            <span class="material-icons-round">queue</span>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                        <div slot="content">
                                            <span><?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?></span>
                                        </div>
                                        <el-popconfirm 
                                            confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                            cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                            icon="false" 
                                            title="<?php esc_html_e('Are you sure you want to delete this service?', 'bookingpress-appointment-booking'); ?>" 
                                            @confirm="deleteService(items_data.service_id)" 
                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                            cancel-button-type="bpa-btn bpa-btn__small">
                                            <el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                <span class="material-icons-round">delete</span>
                                            </el-button>
                                        </el-popconfirm>
                                    </el-tooltip>
                                </div>
                            </div>
                        </div>
                    </el-col>
                </draggable>
            </el-row>
        </el-container>

        <el-row class="bpa-pagination" v-if="items.length > 0">
            <el-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card">
                <el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="clearBulkAction">
                    <span class="material-icons-round">close</span>
                </el-button>
                <el-row type="flex" class="bpa-bac__wrapper">
                    <el-col class="bpa-bac__left-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                        <span class="material-icons-round">check_circle</span>
                                    <b>{{ multipleSelection.length }}<?php esc_html_e(' Items Selected', 'bookingpress-appointment-booking'); ?></b>
                    </el-col>
                    <el-col class="bpa-bac__right-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                        <el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?>" popper-class="bpa-dropdown--bulk-actions">
                            <el-option v-for="item in bulk_options" :key="item.value" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                        <el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="delete_bulk_services()">
                            <?php esc_html_e('Go', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                    </el-col>
                </el-row>
            </el-container>
        </el-row>
    </div>
</el-main>

<!-- Service Category Listing Modal -->
<el-dialog custom-class="bpa-dialog bpa-dialog--manage-categories" title="" :visible.sync="open_manage_category_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" class="bpa-mlc-left-heading--is-visible-help">
                <h1 class="bpa-page-heading"><?php esc_html_e('Manage Categories', 'bookingpress-appointment-booking'); ?></h1>                
            </el-col>
            <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
                <div class="bpa-hw-right-btn-group">
                    <el-button class="bpa-btn bpa-btn__medium" slot="reference" :class="(open_add_category_modal == true) ? 'bpa-btn--primary' : ''" @click="open_add_category_modal_func(event)">
                        <span class="material-icons-round">add</span>
                        <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                    </el-button>
                </div>
            </el-col>
        </el-row>
    </div>
    <div class="bpa-dialog-body">
        <el-container class="bpa-grid-list-container bpa-grid-list--manage-categories">
            <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                <div class="bpa-back-loader"></div>
            </div>
            <el-row type="flex" v-if="category_items.length == 0">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <div class="bpa-data-empty-view bpa-data-empty-view--vertical">
                        <div class="bpa-ev-left-vector">
                            <picture>
                                <source srcset="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp'); ?>"
                                    type="image/webp">
                                <img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png'); ?>">
                            </picture>
                        </div>
                        <div class="bpa-ev-right-content">
                            <h4><?php esc_html_e('No Records Found!', 'bookingpress-appointment-booking'); ?></h4>
                            <p><?php echo stripslashes_deep( esc_html__('Start by clicking the Add New button', 'bookingpress-appointment-booking') ); //phpcs:ignore ?></p>
                        </div>
                    </div>
                </el-col>
            </el-row>
            
            <el-row v-else>
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <div class="bpa-card bpa-card__heading-row">
                        <el-row type="flex">
                            <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                                <div class="bpa-card__item bpa-card__item--ischecbox">
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Category Name', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                            <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                                <div class="bpa-card__item">
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Total Services', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                        </el-row>
                    </div>
                </el-col>
                
                <draggable :list="category_items" :disabled="!enabled" class="list-group" ghost-class="ghost" @start="dragging = true" @end="updateCategoryPos($event)">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="category_items_data in category_items">
                        <div class="bpa-card bpa-card__body-row">
                            <div class="bpa-card__item--drag-icon-wrap">
                                <span class="material-icons-round">drag_indicator</span>
                            </div>
                            <el-row type="flex">
                                <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                                    <div class="bpa-card__item bpa-card__item--ischecbox">
                                        <h4 class="bpa-card__item__heading is--body-heading">{{ category_items_data.category_name }} <span class="bpa-card__item--id">(<?php esc_html_e('ID', 'bookingpress-appointment-booking'); ?>: {{ category_items_data.category_id }} )</h4>
                                    </div>
                                </el-col>
                                <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                                    <div class="bpa-card__item">
                                        <h4 class="bpa-card__item__heading is--body-heading"><el-link @click="searchCategoryData(category_items_data.category_id)">{{ category_items_data.total_services }}</el-link></h4>
                                    </div>
                                </el-col>
                            </el-row>
                            <div class="bpa-table-actions-wrap">
                                <div class="bpa-table-actions">                                    
                                    <el-tooltip effect="dark" content="" placement="top" open-delay="300">                                    
                                        <div slot="content">
                                            <span><?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?></span>
                                        </div>
                                        <el-button class="bpa-btn bpa-btn--icon-without-box" @click="editServiceCategoryData(category_items_data.category_id, event)">
                                            <span class="material-icons-round">mode_edit</span>
                                        </el-button>
                                    </el-tooltip>
                                    <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                        <div slot="content">
                                            <span><?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?></span>
                                        </div>    
                                        <el-popconfirm 
                                            confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
                                            cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
                                            icon="false" 
                                            title="<?php esc_html_e('Are you sure you want to delete this category?', 'bookingpress-appointment-booking'); ?>" 
                                            @confirm="deleteServiceCategory(category_items_data.category_id)" 
                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                            cancel-button-type="bpa-btn bpa-btn__small">
                                            <el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                <span class="material-icons-round">delete</span>
                                            </el-button>
                                        </el-popconfirm>
                                    </el-tooltip>
                                </div>
                            </div>
                        </div>
                    </el-col>
                </draggable>
            </el-row>
        </el-container>
    </div>
    <div class="bpa-dialog-footer">
        <el-row>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <div class="bpa-df-btn-group">
                    <el-button class="bpa-btn bpa-btn__medium" @click="open_manage_category_modal = false"><?php esc_html_e('Close', 'bookingpress-appointment-booking'); ?></el-button>
                </div>
            </el-col>
        </el-row>
    </div>
</el-dialog>


<el-dialog id="bpa_category_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-category" title="" :visible.sync="open_add_category_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading" v-if="service_category.service_category_update_id == 0"><?php esc_html_e('Add Category', 'bookingpress-appointment-booking'); ?></h1>
                <h1 class="bpa-page-heading" v-else><?php esc_html_e('Edit Category', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            
        </el-row>
    </div>
    <div class="bpa-dialog-body">
        <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <el-container class="bpa-grid-list-container bpa-add-categpry-container">
            <div class="bpa-form-row">
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-form ref="service_category" :rules="categoryRules" :model="service_category" label-position="top" @submit.native.prevent>
                            <div class="bpa-form-body-row">
                                <el-row>
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <el-form-item prop="service_category_name">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Category Name', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-input class="bpa-form-control" v-model="service_category.service_category_name" id="service_category_name" name="service_category_name" placeholder="<?php esc_html_e('Enter category name', 'bookingpress-appointment-booking'); ?>" ref="serviceCatName"></el-input>
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
            <el-button class="bpa-btn bpa-btn--primary bpa-btn__small" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveCategoryDetails('service_category')" :disabled="is_disabled" >                    
                  <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                  <div class="bpa-btn--loader__circles">                    
                      <div></div>
                      <div></div>
                      <div></div>
                  </div>
            </el-button>
            <el-button class="bpa-btn bpa-btn__small" @click="open_add_category_modal = false"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></el-button>
        </div>
    </div>
</el-dialog>


<!-- Service Add Modal -->
<el-dialog custom-class="bpa-dialog bpa-dialog--fullscreen bpa-dialog--fullscreen__services bpa--is-page-scrollable-tablet" title=""
    :visible.sync="open_service_modal" top="32px" fullscreen=true :close-on-press-escape="close_modal_on_esc">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading" v-if="service.service_update_id == 0">
                    <?php esc_html_e('Add Service', 'bookingpress-appointment-booking'); ?></h1>
                <h1 class="bpa-page-heading" v-else><?php esc_html_e('Edit Service', 'bookingpress-appointment-booking'); ?>
                </h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
                <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveServiceData" :disabled="is_disabled" >                    
                      <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                      <div class="bpa-btn--loader__circles">                    
                          <div></div>
                          <div></div>
                          <div></div>
                      </div>
                </el-button>    
                <el-button class="bpa-btn" @click="closeServiceModal()">
                    <?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></el-button>
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
                        <el-form ref="service" :rules="rules" :model="service" label-position="top"
                            @submit.native.prevent>
                            <template>
                                <el-row :gutter="24">
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-group">
                                        <el-upload class="bpa-upload-component" ref="avatarRef"
                                            action="<?php echo wp_nonce_url(admin_url('admin-ajax.php') . '?action=bookingpress_upload_service', 'bookingpress_upload_service');//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - esc_html is already used by wp_nonce_url function and it's false positive ?>"
                                            :on-success="bookingpress_upload_service_func"
                                            :file-list="service.service_images_list" multiple="false"
                                            :show-file-list="serviceShowFileList" limit="1"
                                            :on-exceed="bookingpress_image_upload_limit"
                                            :on-error="bookingpress_image_upload_err"
                                            :on-remove="bookingpress_remove_service_img"
                                            :before-upload="checkUploadedFile" drag>
                                            <span
                                                class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
                                            <div class="bpa-upload-component__text" v-if="service.service_image == ''"><?php esc_html_e('Please upload jpg/png/webp file', 'bookingpress-appointment-booking'); ?></div>
                                        </el-upload>
                                        <div class="bpa-uploaded-avatar__preview" v-if="service.service_image != ''">
                                            <button class="bpa-avatar-close-icon" @click="bookingpress_remove_service_img">
                                                <span class="material-icons-round">close</span>
                                            </button>
                                            <el-avatar shape="square" :src="service.service_image" class="bpa-uploaded-avatar__picture"></el-avatar>
                                        </div>
                                    </el-col>
                                </el-row>
                                <div class="bpa-form-body-row">
                                    <el-row :gutter="32">
                                        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                            <el-form-item prop="service_name">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Service Name:', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-input class="bpa-form-control" v-model="service.service_name" id="service_name" name="service_name"
                                                    placeholder="<?php esc_html_e('Enter service name', 'bookingpress-appointment-booking'); ?>">
                                                </el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                            <el-form-item prop="service_category">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Category:', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-select class="bpa-form-control" v-model="service.service_category" filterable
                                                    placeholder="<?php esc_html_e('Select category', 'bookingpress-appointment-booking'); ?>"
                                                    popper-class="bpa-el-select--is-with-modal">
                                                    <el-option key="0" label="<?php esc_html_e( 'Select Category', 'bookingpress-appointment-booking' ); ?>" value="0"></el-option>
                                                    <el-option v-for="item in serviceCatOptions" :key="item.value"
                                                        :label="item.label" :value="item.value"></el-option>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        
                                    </el-row>
                                </div>
                                <div class="bpa-form-body-row">
                                    <el-row :gutter="32">
                                        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                            <el-form-item prop="service_duration_val">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Duration:', 'bookingpress-appointment-booking'); ?> </span>
                                                </template>
                                                <el-row :gutter="10">
                                                    <el-col :xs="18" :sm="18" :md="18" :lg="18" :xl="18">
                                                        <el-input-number class="bpa-form-control bpa-form-control--number" :min="1" v-model="service.service_duration_val" id="service_duration_val" name="service_duration_val" step-strictly></el-input-number>
                                                    </el-col>
                                                    <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                                        <el-select class="bpa-form-control" v-model="service.service_duration_unit" popper-class="bpa-el-select--is-with-modal">
                                                            <el-option key="m" label="<?php esc_html_e('Mins', 'bookingpress-appointment-booking'); ?>" value="m"></el-option>
                                                            <el-option key="h" label="<?php esc_html_e('Hours', 'bookingpress-appointment-booking'); ?>" value="h"></el-option>
                                                        </el-select>
                                                    </el-col>
                                                </el-row>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                            <el-form-item prop="service_price">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Price:', 'bookingpress-appointment-booking'); ?>({{service_price_currency}})</span>
                                                </template>
                                                <el-input class="bpa-form-control" @input=isNumberValidate($event) v-model="service.service_price" id="service_price" name="service_price" placeholder="0.00" v-if="price_number_of_decimals != '0'"></el-input>
                                                <el-input class="bpa-form-control" @input=isValidateZeroDecimal($event) v-model="service.service_price" id="service_price" name="service_price" placeholder="0" v-else></el-input>
                                            </el-form-item>
                                        </el-col>
                                    </el-row>
                                </div>
                                <div class="bpa-form-body-row">
                                    <el-row :gutter="32">
                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                            <el-form-item>
                                                <template #label>
                                                    <span
                                                        class="bpa-form-label"><?php esc_html_e('Description:', 'bookingpress-appointment-booking'); ?> </span>
                                                </template>
                                                <el-input class="bpa-form-control" v-model="service.service_description"
                                                    type="textarea" :rows="10"
                                                    placeholder="<?php esc_html_e('Description', 'bookingpress-appointment-booking'); ?>">
                                                </el-input>
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

<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div id="root_app" class="bookingpress-services bookingpress_page_wrapper">
    <?php
        if( class_exists( '\BookingPressPro\admin\Dashboard') && method_exists( '\BookingPressPro\admin\Dashboard', 'render_dashboard_header' ) ){
            \BookingPressPro\admin\Dashboard::render_dashboard_header();
        } else {
            require_once __DIR__ . '/components/Header.php';
        }
    ?>

    <div class="services-app-root bookingpress_page_inner_wrapper" id="services-app-root" v-cloak>
    <bp-ui-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
        <bp-ui-row type="flex" class="bpa-mlc-head-wrap __services-screen">
            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-mlc-left-heading">
                <h1 class="bpa-page-heading"><?php esc_html_e( 'Manage Services', 'bookingpress-appointment-booking' ); ?></h1>
            </bp-ui-col>
            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                <div class="bpa-hw-right-btn-group">
                    <bp-ui-button class="bpa-btn bpa-btn--primary" @click="open_add_service_modal('add')">
                        <span class="material-icons-round">add</span>
                        <?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
                    </bp-ui-button>
                    <bp-ui-button class="bpa-btn bpa-btn--default" :class="(open_manage_category_modal == true) ? '__bpa-is-active' : ''" @click="open_manage_category_modal = true">
                        <span class="material-icons-round">dns</span>
                        <?php esc_html_e( 'Manage Categories', 'bookingpress-appointment-booking' ); ?>
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
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="9" :xl="10">
                        <span class="bpa-form-label"><?php esc_html_e( 'Service Name', 'bookingpress-appointment-booking' ); ?></span>
                        <bp-ui-input class="bpa-form-control" v-model="search_service_name"
                            placeholder="<?php esc_html_e( 'Enter service name', 'bookingpress-appointment-booking' ); ?>">
                        </bp-ui-input>
                    </bp-ui-col>
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="9" :xl="10">
                        <span class="bpa-form-label"><?php esc_html_e( 'Service Category', 'bookingpress-appointment-booking' ); ?></span>
                        <bp-ui-select class="bpa-form-control bpa-from-select-tab" v-model="search_service_category"
                            placeholder="<?php esc_html_e( 'Select category', 'bookingpress-appointment-booking' ); ?>"
                            :popper-append-to-body="false" popper-class="bpa-ui-select--is-with-navbar">
                            <bp-ui-option key="all" label="<?php esc_html_e( 'All Categories', 'bookingpress-appointment-booking' ); ?>" value=""></bp-ui-option>
                            <bp-ui-option v-for="item in search_categories" :key="String(item.bookingpress_category_id)"
                                :label="item.bookingpress_category_name" :value="String(item.bookingpress_category_id)"></bp-ui-option>
                        </bp-ui-select> 
                    </bp-ui-col>
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="6" :xl="4">
                        <div class="bpa-tf-btn-group">
                            <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
                                <?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
                            </bp-ui-button>
                            <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadServices">
                                <?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
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
                                <source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
                                <img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
                            </picture>
                        </div>
                        <div class="bpa-ev-right-content">
                            <h4><?php esc_html_e( 'No Record Found!', 'bookingpress-appointment-booking' ); ?></h4>
                            <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_service_modal('add')"> 
                                <span class="material-icons-round">add</span>
                                <?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
                            </bp-ui-button>
                        </div>
                    </div>
                </bp-ui-col>
            </bp-ui-row>
            <bp-ui-container class="bpa-grid-list-container bpa-grid-list--service-page" v-if="items.length > 0">
                <bp-ui-row>
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <div class="bpa-card bpa-card__heading-row">
                            <bp-ui-row type="flex">
                                <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                    <div class="bpa-card__item bpa-card__item--ischecbox">
                                        <bp-ui-checkbox label="" v-model="is_multiple_checked" @change="selectAllServices($event)"></bp-ui-checkbox>
                                        <h4 class="bpa-card__item__heading"><?php esc_html_e( 'Name', 'bookingpress-appointment-booking' ); ?></h4>
                                    </div>
                                </bp-ui-col>
                                <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                    <div class="bpa-card__item">
                                        <h4 class="bpa-card__item__heading"><?php esc_html_e( 'Category', 'bookingpress-appointment-booking' ); ?></h4>
                                    </div>
                                </bp-ui-col>
                                <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                    <div class="bpa-card__item">
                                        <h4 class="bpa-card__item__heading"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?></h4>
                                    </div>
                                </bp-ui-col>
                                <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                    <div class="bpa-card__item">
                                        <h4 class="bpa-card__item__heading"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?></h4>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>
                    </bp-ui-col>
                </bp-ui-row>
                <bp-ui-row id="bpa_services_sortable_container">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="items_data in items" :key="items_data.service_id" :data-service_id="items_data.service_id">
                                <div class="bpa-card bpa-card__body-row list-group-item">
                                    <div class="bpa-card__item--drag-icon-wrap">
                                        <span class="material-icons-round">drag_indicator</span>
                                    </div>
                                    <bp-ui-row type="flex">
                                        <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                            <div class="bpa-card__item bpa-card__item--ischecbox">
                                                <bp-ui-tooltip effect="dark" placement="top" v-if="items_data.service_bulk_action">
                                                    <template #content>
                                                        <div>
                                                            <span><?php esc_html_e( 'One or more appointments are associated with this service,', 'bookingpress-appointment-booking' ); ?></span><br/>
                                                            <span><?php esc_html_e( 'so you will not be able to delete it', 'bookingpress-appointment-booking' ); ?></span>
                                                        </div>
                                                    </template>
                                                    <bp-ui-checkbox label="" v-model="items_data.selected" :disabled="items_data.service_bulk_action" @change="handleSelectionChange($event, items_data.service_id)"></bp-ui-checkbox>
                                                </bp-ui-tooltip>
                                                <bp-ui-checkbox label="" v-model="items_data.selected" :disabled="items_data.service_bulk_action" @change="handleSelectionChange($event, items_data.service_id)" v-else></bp-ui-checkbox>
                                                <img :src="items_data.service_img_details" class="bpa-card__item--service-thumbnail" v-if="items_data.service_img_details != ''">
                                                <h4 class="bpa-card__item__heading is--body-heading"> <span v-html="items_data.service_name"></span> <span class="bpa-card__item--id">(<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>: {{ items_data.service_id }} )</span></h4>
                                            </div>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                            <div class="bpa-card__item">
                                                <h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_category }}</h4>
                                            </div>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                            <div class="bpa-card__item">
                                                <h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_duration }}</h4>
                                            </div>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                            <div class="bpa-card__item">
                                                <h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_price }}</h4>
                                            </div>
                                        </bp-ui-col>
                                    </bp-ui-row>
                                    <div class="bpa-table-actions-wrap">
                                        <div class="bpa-table-actions">
                                            <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                <template #content>
                                                    <span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
                                                </template>
                                                <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click="editServiceData(items_data.service_id)">
                                                    <span class="material-icons-round">mode_edit</span>
                                                </bp-ui-button>
                                            </bp-ui-tooltip>
                                            <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                <template #content>
                                                    <span><?php esc_html_e( 'Duplicate', 'bookingpress-appointment-booking' ); ?></span>
                                                </template>
                                                <bp-ui-button class="bpa-btn bpa-btn--icon-without-box __secondary" @click="bookingpress_duplicate_service(items_data.service_id)">
                                                    <span class="material-icons-round">queue</span>
                                                </bp-ui-button>
                                            </bp-ui-tooltip>
                                            <bp-ui-popconfirm 
                                                placement="bottom-end"
                                                cancel-button-text="<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>" 
                                                confirm-button-text="<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>" 
                                                icon="false"
                                                title="<?php esc_html_e( 'Are you sure you want to delete this service?', 'bookingpress-appointment-booking' ); ?>" 
                                                @confirm="deleteService(items_data.service_id)"
                                                confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                cancel-button-type="bpa-btn bpa-btn__small">
                                                <template #reference>
                                                    <bp-ui-button type="text" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                        <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                            <template #content>
                                                                <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                            </template>
                                                            <span class="material-icons-round">delete</span>
                                                        </bp-ui-tooltip>
                                                    </bp-ui-button>
                                                </template>
                                            </bp-ui-popconfirm>
                                        </div>
                                    </div>
                                </div>
                            </bp-ui-col>
                </bp-ui-row>
            </bp-ui-container>

            <bp-ui-row class="bpa-pagination" v-if="items.length > 0" >
                <bp-ui-container v-if="multipleSelection.length > 0 && ( 'undefined' == typeof bookingpress_cap_service_bulk_action || bookingpress_cap_service_bulk_action == 1)" class="bpa-default-card bpa-bulk-actions-card">
                    <bp-ui-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="clearBulkAction">
                        <span class="material-icons-round">close</span>
                    </bp-ui-button>
                    <bp-ui-row type="flex" class="bpa-bac__wrapper">
                        <bp-ui-col class="bpa-bac__left-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <span class="material-icons-round">check_circle</span>
                            <b>{{ multipleSelection.length }} <?php esc_html_e( 'Items Selected', 'bookingpress-appointment-booking' ); ?></b>
                        </bp-ui-col>
                        <bp-ui-col class="bpa-bac__right-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                            <bp-ui-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-dropdown--bulk-actions">
                                <bp-ui-option v-for="item in bulk_options" :key="item.value" :label="item.label" :value="item.value"></bp-ui-option>
                            </bp-ui-select>
                            <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="delete_bulk_services()">
                                <?php esc_html_e( 'Go', 'bookingpress-appointment-booking' ); ?>
                            </bp-ui-button>
                        </bp-ui-col>
                    </bp-ui-row>
                </bp-ui-container>
            </bp-ui-row>
        </div>
    </bp-ui-main>

        <!-- Manage Categories Modal -->
        <bp-ui-dialog class="bpa-dialog bpa-dialog--manage-categories" :align-center="true" :modal="true" title="" v-model="open_manage_category_modal" :close-on-click-modal="true" :close-on-press-escape="true">
            <div class="bpa-dialog-heading">
                <bp-ui-row type="flex">
                    <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" class="bpa-mlc-left-heading--is-visible-help">
                        <h1 class="bpa-page-heading"><?php esc_html_e( 'Manage Categories', 'bookingpress-appointment-booking' ); ?></h1>                
                    </bp-ui-col>
                    <bp-ui-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
                        <div class="bpa-hw-right-btn-group">
                            <bp-ui-button class="bpa-btn bpa-btn__medium" :class="(open_add_category_modal == true) ? 'bpa-btn--primary' : ''" @click="open_add_category_modal_func($event)">
                                <span class="material-icons-round">add</span>
                                <?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
                            </bp-ui-button>
                        </div>
                    </bp-ui-col>
                </bp-ui-row>
            </div>
            <div class="bpa-dialog-body">
                <div class="bpa-grid-list-container bpa-grid-list--manage-categories">
                    <bp-ui-row type="flex" v-if="category_items.length == 0">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-data-empty-view bpa-data-empty-view--vertical">
                                <div class="bpa-ev-left-vector">
                                    <picture>
                                        <source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
                                        <img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
                                    </picture>
                                </div>
                                <div class="bpa-ev-right-content">
                                    <h4><?php esc_html_e( 'No Records Found!', 'bookingpress-appointment-booking' ); ?></h4>
                                    <p><?php echo stripslashes_deep( esc_html__( 'Start by clicking the Add New button', 'bookingpress-appointment-booking' ) ); ?></p>
                                </div>
                            </div>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row v-else>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <div class="bpa-card bpa-card__heading-row">
                                <bp-ui-row type="flex">
                                    <bp-ui-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                                        <div class="bpa-card__item bpa-card__item--ischecbox">
                                            <h4 class="bpa-card__item__heading"><?php esc_html_e( 'Category Name', 'bookingpress-appointment-booking' ); ?></h4>
                                        </div>
                                    </bp-ui-col>
                                    <bp-ui-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                                        <div class="bpa-card__item">
                                            <h4 class="bpa-card__item__heading"><?php esc_html_e( 'Total Services', 'bookingpress-appointment-booking' ); ?></h4>
                                        </div>
                                    </bp-ui-col>
                                </bp-ui-row>
                            </div>
                        </bp-ui-col>
                    </bp-ui-row>
                    <bp-ui-row v-if="category_items.length > 0" id="bpa_categories_sortable_container">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="cat in category_items" :key="cat.category_id">
                                    <div class="bpa-card bpa-card__body-row">
                                        <div class="bpa-card__item--drag-icon-wrap">
                                            <span class="material-icons-round">drag_indicator</span>
                                        </div>
                                        <bp-ui-row type="flex">
                                            <bp-ui-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                                                <div class="bpa-card__item bpa-card__item--ischecbox">
                                                    <h4 class="bpa-card__item__heading is--body-heading">{{ cat.category_name }} <span class="bpa-card__item--id">(<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>: {{ cat.category_id }} )</span></h4>
                                                </div>
                                            </bp-ui-col>
                                            <bp-ui-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                                                <div class="bpa-card__item">
                                                    <h4 class="bpa-card__item__heading is--body-heading"><a href="javascript:void(0);" @click="searchCategoryData(cat.category_id)">{{ cat.total_services }}</a></h4>
                                                </div>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                        <div class="bpa-table-actions-wrap">
                                            <div class="bpa-table-actions">
                                                <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                    <template #content>
                                                        <span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
                                                    </template>
                                                    <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click="editServiceCategoryData(cat.category_id, $event)">
                                                        <span class="material-icons-round">mode_edit</span>
                                                    </bp-ui-button>
                                                </bp-ui-tooltip>
                                                <bp-ui-popconfirm 
                                                    cancel-button-text="<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>" 
                                                    confirm-button-text="<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>" 
                                                    icon="false" 
                                                    title="<?php esc_html_e( 'Are you sure you want to delete this category?', 'bookingpress-appointment-booking' ); ?>" 
                                                    @confirm="deleteServiceCategory(cat.category_id)" 
                                                    confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                    cancel-button-type="bpa-btn bpa-btn__small">
                                                    <template #reference>
                                                        <bp-ui-button type="text" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                            <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                                <template #content>
                                                                    <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                                </template>
                                                                <span class="material-icons-round">delete</span>
                                                            </bp-ui-tooltip>
                                                        </bp-ui-button>
                                                    </template>
                                                </bp-ui-popconfirm>
                                            </div>
                                        </div>
                                    </div>
                                </bp-ui-col>
                    </bp-ui-row>
                </div>
            </div>
            <div class="bpa-dialog-footer">
                <bp-ui-row>
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <div class="bpa-df-btn-group">
                            <bp-ui-button class="bpa-btn bpa-btn__medium" @click="open_manage_category_modal = false"><?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?></bp-ui-button>
                        </div>
                    </bp-ui-col>
                </bp-ui-row>
            </div>
        </bp-ui-dialog>

        <!-- Add/Edit Category Modal -->
        <bp-ui-dialog id="bpa_category_add_modal" class="bpa-dialog bpa-dailog__small bpa-dialog--add-category" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-category" title="" v-model="open_add_category_modal" :modal="false" :close-on-click-modal="true" :close-on-press-escape="true">
            <div class="bpa-dialog-heading">
                <bp-ui-row type="flex">
                    <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                        <h1 class="bpa-page-heading" v-if="service_category.service_category_update_id == 0"><?php esc_html_e( 'Add Category', 'bookingpress-appointment-booking' ); ?></h1>
                        <h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Category', 'bookingpress-appointment-booking' ); ?></h1>
                    </bp-ui-col>
                </bp-ui-row>
            </div>
            <div class="bpa-dialog-body">
                <div class="bpa-grid-list-container bpa-add-categpry-container">
                    <div class="bpa-form-row">
                        <bp-ui-row>
                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <bp-ui-form ref="serviceCategoryRef" :rules="categoryRules" :model="service_category" label-position="top" @submit.prevent="saveCategoryDetails">
                                    <div class="bpa-form-body-row">
                                        <bp-ui-row>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-form-item prop="service_category_name" label="<?php esc_html_e( 'Category Name', 'bookingpress-appointment-booking' ); ?>">
                                                    <bp-ui-input class="bpa-form-control" v-model="service_category.service_category_name" maxlength="255" placeholder="<?php esc_html_e( 'Enter category name', 'bookingpress-appointment-booking' ); ?>"></bp-ui-input>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                </bp-ui-form>
                            </bp-ui-col>
                        </bp-ui-row>
                    </div>
                </div>
            </div>
            <div class="bpa-dialog-footer">
                <div class="bpa-hw-right-btn-group">
                    <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn__small" :class="(is_display_category_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveCategoryDetails" :disabled="is_disabled">
                        <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
                        <div class="bpa-btn--loader__circles"><div></div><div></div><div></div></div>
                    </bp-ui-button>
                    <bp-ui-button class="bpa-btn bpa-btn__small" @click="open_add_category_modal = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></bp-ui-button>
                </div>
            </div>
        </bp-ui-dialog>

        <!-- Add/Edit Service Fullscreen Modal -->
        <bp-ui-dialog class="bpa-dialog bpa-dialog--fullscreen bpa-dialog--fullscreen__services bpa--is-page-scrollable-tablet" custom-class="bpa-dialog bpa-dialog--fullscreen bpa-dialog--fullscreen__services bpa--is-page-scrollable-tablet" title="" v-model="open_service_modal" top="32px" :fullscreen="true">
            <div class="bpa-dialog-heading">
                <bp-ui-row type="flex">
                    <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                        <h1 class="bpa-page-heading" v-if="service.service_update_id == 0"><?php esc_html_e( 'Add Service', 'bookingpress-appointment-booking' ); ?></h1>
                        <h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Service', 'bookingpress-appointment-booking' ); ?></h1>
                    </bp-ui-col>
                    <bp-ui-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
                        <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveServiceData" :disabled="is_disabled">
                            <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
                            <div class="bpa-btn--loader__circles"><div></div><div></div><div></div></div>
                        </bp-ui-button>    
                        <bp-ui-button class="bpa-btn" @click="closeServiceModal()">
                            <?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>
                        </bp-ui-button>
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
                                            <h2 class="bpa-page-heading"><?php esc_html_e( 'Basic Details', 'bookingpress-appointment-booking' ); ?></h2>
                                        </div>
                                    </bp-ui-col>                            
                                </bp-ui-row>
                            </div>
                            <div class="bpa-default-card bpa-db-card">
                                <bp-ui-form ref="serviceRef" :rules="rules" :model="service" label-position="top">
                                    <bp-ui-row :gutter="24">
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-group">
                                            <bp-ui-upload class="bpa-upload-component" ref="avatarRef"
                                                :auto-upload="true"
                                                :headers="upload_headers"
                                                :action="upload_service_avatar_url"
                                                :on-success="bookingpress_upload_service_func"
                                                :multiple="false"
                                                :show-file-list="false"
                                                :limit="1"
                                                :on-exceed="bookingpress_image_upload_limit"
                                                :on-error="bookingpress_image_upload_err"
                                                :on-remove="bookingpress_remove_service_img"
                                                :before-upload="checkUploadedFile" drag>
                                                <span class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
                                                <div class="bpa-upload-component__text" v-if="service.service_image == ''"><?php esc_html_e( 'Please upload jpg/png/webp file with a maximum size of 1 MB', 'bookingpress-appointment-booking' ); ?></div>
                                            </bp-ui-upload>
                                            <div class="bpa-uploaded-avatar__preview" v-if="service.service_image != ''">
                                                <button class="bpa-avatar-close-icon" @click="bookingpress_remove_service_img">
                                                    <span class="material-icons-round">close</span>
                                                </button>
                                                <bp-ui-avatar shape="square" :src="service.service_image" class="bpa-uploaded-avatar__picture"></bp-ui-avatar>
                                            </div>
                                        </bp-ui-col>
                                    </bp-ui-row>
                                    <div class="bpa-form-body-row">
                                        <bp-ui-row :gutter="32">
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                                <bp-ui-form-item prop="service_name">
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php esc_html_e( 'Service Name:', 'bookingpress-appointment-booking' ); ?></span>
                                                    </template>
                                                    <bp-ui-input class="bpa-form-control" v-model="service.service_name" id="service_name" name="service_name" maxlength="255" placeholder="<?php esc_html_e( 'Enter service name', 'bookingpress-appointment-booking' ); ?>"></bp-ui-input>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                                <bp-ui-form-item prop="service_category">
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php esc_html_e( 'Category:', 'bookingpress-appointment-booking' ); ?></span>
                                                    </template>
                                                    <bp-ui-select id="service_category_select" ref="serviceCategorySelectRef" class="bpa-form-control" v-model="service.service_category" filterable placeholder="<?php esc_html_e( 'Select category', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-ui-select--is-with-modal" @change="onServiceCategoryChange">
                                                        <bp-ui-option key="0" label="<?php esc_html_e( 'Select Category', 'bookingpress-appointment-booking' ); ?>" value="0"></bp-ui-option>
                                                        <bp-ui-option key="__add_category__" value="__add_category__" class="bpa-option--add-category">
                                                            <span class="material-icons-round">add</span>
                                                            <?php esc_html_e( 'Add Category', 'bookingpress-appointment-booking' ); ?>
                                                        </bp-ui-option>
                                                        <bp-ui-option v-for="item in search_categories" :key="String(item.bookingpress_category_id)" :label="item.bookingpress_category_name" :value="String(item.bookingpress_category_id)"></bp-ui-option>
                                                    </bp-ui-select>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                    <div class="bpa-form-body-row">
                                        <bp-ui-row :gutter="32">
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                                <bp-ui-form-item prop="service_duration_val">
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php esc_html_e( 'Duration:', 'bookingpress-appointment-booking' ); ?></span>
                                                    </template>
                                                    <bp-ui-row :gutter="10" style="width: 100%;">
                                                        <bp-ui-col :span="18" :xs="18" :sm="18" :md="18" :lg="18" :xl="18">
                                                            <bp-ui-input-number class="bpa-form-control bpa-form-control--number" :min="1" v-model="service.service_duration_val" id="service_duration_val" name="service_duration_val" step-strictly style="width: 100%;" :disabled="typeof service.enable_custom_service_duration !== 'undefined' ? (service.enable_custom_service_duration == true || service.enable_custom_service_duration == 'true') : false"></bp-ui-input-number>
                                                        </bp-ui-col>
                                                        <bp-ui-col :span="6" :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                                            <bp-ui-select class="bpa-form-control" v-model="service.service_duration_unit" popper-class="bpa-ui-select--is-with-modal" style="width: 100%;" :disabled="typeof service.enable_custom_service_duration !== 'undefined' ? (service.enable_custom_service_duration == true || service.enable_custom_service_duration == 'true') : false">
                                                                <bp-ui-option key="m" label="<?php esc_html_e( 'Mins', 'bookingpress-appointment-booking' ); ?>" value="m"></bp-ui-option>
                                                                <bp-ui-option key="h" label="<?php esc_html_e( 'Hours', 'bookingpress-appointment-booking' ); ?>" value="h"></bp-ui-option>
                                                                <bp-ui-option key="d" label="<?php esc_html_e( 'Days', 'bookingpress-appointment-booking' ); ?>" value="d"></bp-ui-option>
                                                            </bp-ui-select>
                                                        </bp-ui-col>
                                                    </bp-ui-row>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                                <bp-ui-form-item prop="service_price">
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php esc_html_e( 'Price:', 'bookingpress-appointment-booking' ); ?>({{ service_price_currency }})</span>
                                                    </template>
                                                    <bp-ui-input class="bpa-form-control" @input="isNumberValidate($event)" v-model="service.service_price" id="service_price" name="service_price" placeholder="0.00" v-if="price_number_of_decimals != '0'" :disabled="typeof service.enable_custom_service_duration !== 'undefined' ? (service.enable_custom_service_duration == true || service.enable_custom_service_duration == 'true') : false"></bp-ui-input>
                                                    <bp-ui-input class="bpa-form-control" @input="isValidateZeroDecimal($event)" v-model="service.service_price" id="service_price" name="service_price" placeholder="0" v-else :disabled="typeof service.enable_custom_service_duration !== 'undefined' ? (service.enable_custom_service_duration == true || service.enable_custom_service_duration == 'true') : false"></bp-ui-input>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                    <div class="bpa-form-body-row">
                                        <bp-ui-row :gutter="32">
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-form-item>
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php esc_html_e( 'Description:', 'bookingpress-appointment-booking' ); ?></span>
                                                    </template>
                                                    <bp-ui-input class="bpa-form-control" v-model="service.service_description" type="textarea" :rows="10" placeholder="<?php esc_html_e( 'Description', 'bookingpress-appointment-booking' ); ?>"></bp-ui-input>
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
</div>

<?php require_once __DIR__ . '/components/SideMenuDrawer.php'; ?>

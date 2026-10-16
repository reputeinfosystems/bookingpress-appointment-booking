<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div id="root_app" class="bookingpress-payments bookingpress_page_wrapper">
    <?php
        if( class_exists( '\BookingPressPro\admin\Dashboard') && method_exists( '\BookingPressPro\admin\Dashboard', 'render_dashboard_header' ) ){
            \BookingPressPro\admin\Dashboard::render_dashboard_header();
        } else {
            require_once __DIR__ . '/components/Header.php';
        }
    ?>

    <div class="payments-app-root bookingpress_page_inner_wrapper" v-cloak id="payments-app-root">
        <bp-ui-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-non-scrollable-mob" id="all-page-main-container">
            <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                    <h1 class="bpa-page-heading"><?php esc_html_e('Manage Payments', 'bookingpress-appointment-booking'); ?></h1>
                </bp-ui-col>
            </bp-ui-row>
            <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
                <div class="bpa-back-loader"></div>
            </div>
            <div id="bpa-main-container">
                <div class="bpa-table-filter">
                    <bp-ui-row type="flex" :gutter="32">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="5" :xl="5">
                            <span class="bpa-form-label"><?php esc_html_e('Transaction Date', 'bookingpress-appointment-booking'); ?></span>
                            <bp-ui-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="search_data.search_range" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End Date', 'bookingpress-appointment-booking'); ?>" placement="bottom-start" :popper-append-to-body="false" :teleported="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker-widget-wrapper" range-separator=" - " :first-day-of-week="firstDayOfWeek" value-format="YYYY-MM-DD"></bp-ui-date-picker>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="5" :xl="5">
                            <span class="bpa-form-label"><?php esc_html_e('Customer Name', 'bookingpress-appointment-booking'); ?></span>
                            <bp-ui-select class="bpa-form-control bpa-from-select-tab" v-model="search_data.search_customer" multiple filterable collapse-tags placeholder="<?php esc_html_e( 'Start typing to fetch customer', 'bookingpress-appointment-booking' ); ?>" remote reserve-keyword :remote-method="bookingpress_get_search_customer_list" :loading="bookingpress_loading" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
                                <bp-ui-option v-for="item in search_customer_data" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                            </bp-ui-select>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="5" :xl="5">
                            <span class="bpa-form-label"><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></span>
                            <bp-ui-select class="bpa-form-control bpa-from-select-tab" v-model="search_data.search_service" multiple filterable collapse-tags placeholder="<?php esc_html_e('Select service', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
                                <bp-ui-option-group v-for="item in search_services_data" :key="item.category_name" :label="item.category_name">
                                    <bp-ui-option v-for="cat_services in item.category_services" :key="cat_services.service_id" :label="cat_services.service_name" :value="cat_services.service_id"></bp-ui-option>
                                </bp-ui-option-group>
                            </bp-ui-select>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="5" :xl="5">
                            <span class="bpa-form-label"><?php esc_html_e('Payment Status', 'bookingpress-appointment-booking'); ?></span>
                            <bp-ui-select class="bpa-form-control bpa-from-select-tab" v-model="search_data.search_status" filterable placeholder="<?php esc_html_e('Select status', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
                                <bp-ui-option label="<?php esc_html_e('All', 'bookingpress-appointment-booking'); ?>" value="all"></bp-ui-option>
                                <bp-ui-option v-for="item_data in payment_status_data" :key="item_data.text" :label="item_data.text" :value="item_data.value"></bp-ui-option>
                            </bp-ui-select>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4">
                            <span class="bpa-form-label">&nbsp;</span>
                            <div class="bpa-tf-btn-group">
                                <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
                                    <?php esc_html_e('Reset', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                                <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadPayments(true)">
                                    <?php esc_html_e('Apply', 'bookingpress-appointment-booking'); ?>
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
                            </div>
                        </div>
                    </bp-ui-col>
                </bp-ui-row>
                <bp-ui-row v-if="items.length > 0">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <bp-ui-container class="bpa-table-container --bpa-is-payments-screen">
                            <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_loader == '1'">
                                <div class="bpa-back-loader"></div>
                            </div>
                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'desktop'">
                                <bp-ui-table ref="multipleTable" :data="items" row-key="payment_log_id" class="bpa-manage-payment-items" @sort-change="handel_payment_appointment_data" @selection-change="handleSelectionChange" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
                                    <bp-ui-table-column type="expand" :expand-icon="CirclePlusFilled" :collapse-icon="RemoveFilled">
                                        <template #default="scope">
                                            <div class="bpa-view-payment-card">
                                                <div class="bpa-vpc--head">
                                                    <div class="bpa-vpc--head__left">
                                                        <h2><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h2>
                                                    </div>
                                                    <div class="bpa-hw-right-btn-group bpa-vpc--head__right">
                                                        <?php do_action('bookingpress_add_dynamic_buttons_for_view_payments'); ?>
                                                    </div>
                                                </div>
                                                <div class="bpa-vpc--body">
                                                    <div class="bpa-vpc__customer-summary-wrapper">
                                                        <div class="bpa-vpc-csw__item">
                                                            <div class="bpa-csw--customer-info">
                                                                <div class="bpa-ci--avatar">
                                                                    <img :src="scope.row.customer_avatar">
                                                                </div>
                                                                <div class="bpa-ci--body">
                                                                    <h4 v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</h4>
                                                                    <p>{{ scope.row.customer_email }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="bpa-vpc-csw__item">
                                                            <h4><?php esc_html_e('Mode', 'bookingpress-appointment-booking'); ?></h4>
                                                            <p>{{ scope.row.payment_gateway }}</p>
                                                        </div>
                                                        <div class="bpa-vpc-csw__item">
                                                            <h4><?php esc_html_e('Transaction ID', 'bookingpress-appointment-booking'); ?></h4>
                                                            <p>{{ scope.row.transaction_id }}</p>
                                                        </div>
                                                        <div class="bpa-vpc-csw__item">
                                                            <h4><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></h4>
                                                            <p :class="[(scope.row.payment_status == '1') ? 'bpa-cl-pt-main-green' : '', (scope.row.payment_status == '2') ? 'bpa-cl-sc-warning' : '']">{{ scope.row.payment_status_label }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-vpc__appointment-details">
                                                        <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Appointment Details', 'bookingpress-appointment-booking'); ?></h4>
                                                        <div class="bpa-vac-ap--items bpa-vac-ap--grid-items">
                                                            <div class="bpa-ap-item__head">
                                                                <div class="bpa-ih--item bpa-ih--item-service-name-cell">
                                                                    <h4><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></h4>
                                                                </div>
                                                                <div class="bpa-ih--item">
                                                                    <h4><?php esc_html_e('Date', 'bookingpress-appointment-booking'); ?></h4>
                                                                </div>
                                                                <div class="bpa-ih--item">
                                                                    <h4><?php esc_html_e('Time', 'bookingpress-appointment-booking'); ?></h4>
                                                                </div>
                                                                <div class="bpa-ih--item">
                                                                    <h4><?php esc_html_e('Price', 'bookingpress-appointment-booking'); ?></h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="bpa-vac-ap--grid-items">
                                                            <div class="bpa-ap-item__body">
                                                                <div class="bpa-ib--item-card">
                                                                    <div class="bpa-ib--item bpa-ib--item-service-name-cell">
                                                                        <p>{{ scope.row.payment_service }}</p>
                                                                    </div>
                                                                    <div class="bpa-ib--item">
                                                                        <p>{{ scope.row.appointment_date }}</p>
                                                                    </div>
                                                                    <div class="bpa-ib--item">
                                                                        <p v-if="scope.row.appointment_start_time != ''">{{ scope.row.appointment_start_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{ scope.row.appointment_end_time }}</p>
                                                                    </div>
                                                                    <div class="bpa-ib--item">
                                                                        <div class="bpa-ib__amount-row">
                                                                            <div class="bpa-ar__body">
                                                                                <p>{{ scope.row.payment_amount }}</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column type="selection"></bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_date" min-width="70" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable="custom" sort-by="sort_payment_date"></bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_customer" min-width="120" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable="custom"></bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_service" min-width="120" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable="custom"></bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_gateway" min-width="60" label="<?php esc_html_e( 'Method', 'bookingpress-appointment-booking' ); ?>">
                                        <template #default="scope">
                                            <div class="bpa-mpg__body">
                                                <p> {{scope.row.payment_gateway}}</p>
                                                <span v-if="scope.row.payment_gateway == 'manual'">(<?php esc_html_e('booked by admin', 'bookingpress-appointment-booking'); ?>)</span>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_status" min-width="80" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
                                        <template #default="scope">
                                            <div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''">
                                                <div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
                                                    <div class="bpa-btn--loader__circles">
                                                        <div></div>
                                                        <div></div>
                                                        <div></div>
                                                    </div>
                                                </div>
                                                <bp-ui-select class="bpa-form-control" :class="((scope.row.payment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.payment_status == '1' ? 'bpa-appointment-status--completed' : '')" v-model="scope.row.payment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-payment-status-dropdown-popper bpa-appointment-status-dropdown-popper" :teleported="true" @change="bookingpress_change_status(scope.row.payment_log_id, $event, scope.row)">
                                                    <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                        <bp-ui-option v-for="item in payment_status_data" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                    </bp-ui-option-group>
                                                </bp-ui-select>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_amount" min-width="60" label="<?php esc_html_e( 'Amount', 'bookingpress-appointment-booking' ); ?>" sort-by="payment_numberic_amount">
                                        <template #default="scope">
                                            <div class="bpa-mpi__amount-row">
                                                <div class="bpa-mpi__ar-body">
                                                    <span class="bpa-mpi__amount">{{ scope.row.payment_amount }}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="appointment_date" label="<?php esc_html_e( 'Appointment On', 'bookingpress-appointment-booking' ); ?>" sortable="custom" sort-by="sort_payment_appointment_date">
                                        <template #default="scope">
                                            <label>{{ scope.row.appointment_date }}</label>
                                            <div class="bpa-table-actions-wrap">
                                                <div class="bpa-table-actions">
                                                    <bp-ui-tooltip tabindex="-1" effect="dark" content="" placement="top" open-delay="300" v-if="scope.row.payment_status == '2'">
                                                        <template #content>
                                                            <span><?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?></span>
                                                        </template>
                                                        <bp-ui-button type="button" class="bpa-btn bpa-btn--icon-without-box __secondary" @click="bpa_approve_appointment(scope.row.payment_log_id)">
                                                            <span class="material-icons-round">done</span>
                                                        </bp-ui-button>
                                                    </bp-ui-tooltip>
                                                    <bp-ui-popconfirm
                                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>'
                                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>'
                                                            icon="false"
                                                            :placement="popConfirmPlacement"
                                                            title="<?php esc_html_e( 'Are you sure you want to delete this payment transaction?', 'bookingpress-appointment-booking' ); ?>"
                                                            @confirm="deletePaymentLog(scope.row.payment_log_id)"
                                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger"
                                                            cancel-button-type="bpa-btn bpa-btn__small">
                                                            <template #reference>
                                                                <span class="bpa-delete-role-reference">
                                                                    <bp-ui-tooltip effect="dark" content="<?php esc_attr_e( 'Delete', 'bookingpress-appointment-booking' ); ?>" placement="top" open-delay="300">
                                                                        <bp-ui-button type="text" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                            <span class="material-icons-round">delete</span>
                                                                        </bp-ui-button>
                                                                    </bp-ui-tooltip>
                                                                </span>
                                                            </template>
                                                        </bp-ui-tooltip>
                                                    </bp-ui-popconfirm>
                                                </div>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                </bp-ui-table>
                            </div>
                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'tablet'">
                                <bp-ui-table ref="multipleTable" :data="items" row-key="payment_log_id" class="bpa-manage-payment-items" @sort-change="handel_payment_appointment_data" @selection-change="handleSelectionChange" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
                                    <bp-ui-table-column type="expand">
                                        <template #default="scope">
                                            <div class="bpa-view-payment-card">
                                                <div class="bpa-vpc--head">
                                                    <div class="bpa-vpc--head__left">
                                                        <h2><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h2>
                                                    </div>
                                                    <div class="bpa-hw-right-btn-group bpa-vpc--head__right">
                                                        <?php do_action('bookingpress_add_dynamic_buttons_for_view_payments'); ?>
                                                    </div>
                                                </div>
                                                <div class="bpa-vpc--body">
                                                    <div class="bpa-vpc__customer-summary-wrapper">
                                                        <div class="bpa-vpc-csw__item">
                                                            <div class="bpa-csw--customer-info">
                                                                <div class="bpa-ci--avatar">
                                                                    <img :src="scope.row.customer_avatar">
                                                                </div>
                                                                <div class="bpa-ci--body">
                                                                    <h4 v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</h4>
                                                                    <p>{{ scope.row.customer_email }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="bpa-vpc-csw__item">
                                                            <h4><?php esc_html_e('Mode', 'bookingpress-appointment-booking'); ?></h4>
                                                            <p>{{ scope.row.payment_gateway }}</p>
                                                        </div>
                                                        <div class="bpa-vpc-csw__item">
                                                            <h4><?php esc_html_e('Transaction ID', 'bookingpress-appointment-booking'); ?></h4>
                                                            <p>{{ scope.row.transaction_id }}</p>
                                                        </div>
                                                        <div class="bpa-vpc-csw__item">
                                                            <h4><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></h4>
                                                            <p :class="[(scope.row.payment_status == '1') ? 'bpa-cl-pt-main-green' : '', (scope.row.payment_status == '2') ? 'bpa-cl-sc-warning' : '']">{{ scope.row.payment_status_label }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-vpc__appointment-details">
                                                        <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Appointment Details', 'bookingpress-appointment-booking'); ?></h4>
                                                        <div class="bpa-vac-ap--items">
                                                            <div class="bpa-ap-item__head">
                                                                <div class="bpa-ih--item">
                                                                    <h4><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></h4>
                                                                </div>
                                                                <div class="bpa-ih--item">
                                                                    <h4><?php esc_html_e('Date', 'bookingpress-appointment-booking'); ?></h4>
                                                                </div>
                                                                <div class="bpa-ih--item">
                                                                    <h4><?php esc_html_e('Time', 'bookingpress-appointment-booking'); ?></h4>
                                                                </div>
                                                                <div class="bpa-ih--item">
                                                                    <h4><?php esc_html_e('Price', 'bookingpress-appointment-booking'); ?></h4>
                                                                </div>
                                                            </div>
                                                            <div class="bpa-ap-item__body">
                                                                <div class="bpa-ib--item-card">
                                                                    <div class="bpa-ib--item">
                                                                        <p>{{ scope.row.payment_service }}</p>
                                                                    </div>
                                                                    <div class="bpa-ib--item">
                                                                        <p>{{ scope.row.appointment_date }}</p>
                                                                    </div>
                                                                    <div class="bpa-ib--item">
                                                                        <p v-if="scope.row.appointment_start_time != ''">{{ scope.row.appointment_start_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{ scope.row.appointment_end_time }}</p>
                                                                    </div>
                                                                    <div class="bpa-ib--item">
                                                                        <div class="bpa-ib__amount-row">
                                                                            <div class="bpa-ar__body">
                                                                                <p>{{ scope.row.payment_amount }}</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column type="selection"></bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_date" min-width="70" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="sort_payment_date"></bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_service" min-width="100" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable></bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_status" min-width="90" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
                                        <template #default="scope">
                                            <div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''">
                                                <div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
                                                    <div class="bpa-btn--loader__circles">
                                                        <div></div>
                                                        <div></div>
                                                        <div></div>
                                                    </div>
                                                </div>
                                                <bp-ui-select class="bpa-form-control" :class="((scope.row.payment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.payment_status == '1' ? 'bpa-appointment-status--completed' : '')" v-model="scope.row.payment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-payment-status-dropdown-popper bpa-appointment-status-dropdown-popper" :teleported="true" @change="bookingpress_change_status(scope.row.payment_log_id, $event, scope.row)">
                                                    <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                        <bp-ui-option v-for="item in payment_status_data" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                    </bp-ui-option-group>
                                                </bp-ui-select>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="payment_amount" min-width="70" label="<?php esc_html_e( 'Amount', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="payment_numberic_amount">
                                        <template #default="scope">
                                            <div class="bpa-mpi__amount-row">
                                                <div class="bpa-mpi__ar-body">
                                                    <span class="bpa-mpi__amount">{{ scope.row.payment_amount }}</span>
                                                </div>
                                            </div>
                                            <div class="bpa-table-actions-wrap">
                                                <div class="bpa-table-actions">
                                                    <bp-ui-tooltip tabindex="-1" effect="dark" content="" placement="top" open-delay="300" v-if="scope.row.payment_status == '2'">
                                                        <template #content>
                                                            <span><?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?></span>
                                                        </template>
                                                        <bp-ui-button type="button" class="bpa-btn bpa-btn--icon-without-box __secondary" @click="bpa_approve_appointment(scope.row.payment_log_id)">
                                                            <span class="material-icons-round">done</span>
                                                        </bp-ui-button>
                                                    </bp-ui-tooltip>                                                    
                                                    <bp-ui-popconfirm
                                                        confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>'
                                                        cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>'
                                                        icon="false"
                                                        :placement="popConfirmPlacement"
                                                        title="<?php esc_html_e( 'Are you sure you want to delete this payment transaction?', 'bookingpress-appointment-booking' ); ?>"
                                                        @confirm="deletePaymentLog(scope.row.payment_log_id)"
                                                        confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger"
                                                        cancel-button-type="bpa-btn bpa-btn__small">
                                                        <template #reference>
                                                            <span class="bpa-delete-role-reference">
                                                                <bp-ui-tooltip tabindex="-1" content="<?php esc_attr_e( 'Delete', 'bookingpress-appointment-booking' ); ?>" effect="dark" content="" placement="top" open-delay="300">
                                                                    <bp-ui-button type="text" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                        <span class="material-icons-round">delete</span>
                                                                    </bp-ui-button>
                                                                </bp-ui-tooltip>
                                                            </span>
                                                        </template>
                                                    </bp-ui-popconfirm>                                                    
                                                </div>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                </bp-ui-table>
                            </div>
                            <div class="bpa-tc__wrapper bpa-manage-payments-container--sm" v-if="current_screen_size == 'mobile'">
                                <bp-ui-table ref="multipleTable" :data="items" row-key="payment_log_id" class="bpa-manage-payment-items" @selection-change="handleSelectionChange" @row-click="bookingpress_full_row_clickable" :show-header="false" @expand-change="bookingpress_row_expand">
                                    <bp-ui-table-column type="expand">
                                        <template #default="scope">
                                            <div class="bpa-view-payment-card">
                                                <div class="bpa-vpc--head">
                                                    <div class="bpa-vpc--head__left">
                                                        <h2><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h2>
                                                        <p :class="[(scope.row.payment_status == '1') ? 'bpa-cl-pt-main-green' : '', (scope.row.payment_status == '2') ? 'bpa-cl-sc-warning' : '']">{{ scope.row.payment_status_label }}</p>
                                                    </div>
                                                    <div class="bpa-hw-right-btn-group bpa-vpc--head__right">
                                                        <?php do_action('bookingpress_add_dynamic_buttons_for_view_payments'); ?>
                                                    </div>
                                                </div>
                                                <div class="bpa-vpc--body">
                                                    <div class="bpa-csw--customer-info">
                                                        <div class="bpa-ci--avatar">
                                                            <img :src="scope.row.customer_avatar">
                                                        </div>
                                                        <div class="bpa-ci--body">
                                                            <h4 v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</h4>
                                                            <p>{{ scope.row.customer_email }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-vpc__customer-summary-wrapper">
                                                        <div class="bpa-vpc-csw__item">
                                                            <h4><?php esc_html_e('Mode', 'bookingpress-appointment-booking'); ?></h4>
                                                            <p>{{ scope.row.payment_gateway }}</p>
                                                        </div>
                                                        <div class="bpa-vpc-csw__item">
                                                            <h4><?php esc_html_e('Transaction ID', 'bookingpress-appointment-booking'); ?></h4>
                                                            <p>{{ scope.row.transaction_id }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-vpc__appointment-details">
                                                        <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Appointment Details', 'bookingpress-appointment-booking'); ?></h4>
                                                        <div class="bpa-vac-ap--items__sm">
                                                            <div class="bpa-ap-item__body">
                                                                <div class="bpa-ib--item-card">
                                                                    <div class="bpa-ib--item-head__sm">
                                                                        <div class="bpa-ih-left__sm">
                                                                            <p>{{ scope.row.payment_service }}</p>
                                                                        </div>
                                                                        <div class="bpa-ih-right__sm">
                                                                            <p>{{ scope.row.payment_amount }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bpa-ib--datetime__sm">
                                                                        <div class="bpa-dt__item">
                                                                            <span class="material-icons-round">calendar_today</span>
                                                                            <p>{{ scope.row.appointment_date }}</p>
                                                                        </div>
                                                                        <div class="bpa-dt__item">
                                                                            <span class="material-icons-round">access_time</span>
                                                                            <p v-if="scope.row.appointment_start_time != ''">{{ scope.row.appointment_start_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{ scope.row.appointment_end_time }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column type="selection"></bp-ui-table-column>
                                    <bp-ui-table-column>
                                        <template #default="scope">
                                            <div class="bpa-mpay-item__mob">
                                                <div class="bpa-mpay-item--head">
                                                    <div class="bpa-mpay-head__left">
                                                        <h4>{{ scope.row.payment_service }}</h4>
                                                        <span>{{ scope.row.payment_date }}</span>
                                                    </div>
                                                    <div class="bpa-mpay-head__right">
                                                        <p>{{ scope.row.payment_amount }}</p>
                                                    </div>
                                                </div>
                                                <div class="bpa-mpay-item--foot">
                                                    <div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''">
                                                        <div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
                                                            <div class="bpa-btn--loader__circles">
                                                                <div></div>
                                                                <div></div>
                                                                <div></div>
                                                            </div>
                                                        </div>
                                                        <bp-ui-select class="bpa-form-control" :class="((scope.row.payment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.payment_status == '1' ? 'bpa-appointment-status--completed' : '')" v-model="scope.row.payment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-payment-status-dropdown-popper bpa-appointment-status-dropdown-popper" :teleported="true" @change="bookingpress_change_status(scope.row.payment_log_id, $event, scope.row)">
                                                            <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                                <bp-ui-option v-for="item in payment_status_data" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                            </bp-ui-option-group>
                                                        </bp-ui-select>
                                                    </div>
                                                    <div class="bpa-mpay-fi__actions">
                                                        <bp-ui-button type="button" class="bpa-btn bpa-btn__filled-light __secondary" @click="bpa_approve_appointment(scope.row.payment_log_id)" v-if="scope.row.payment_status == '2'">
                                                            <span class="material-icons-round">done</span>
                                                        </bp-ui-button>
                                                        <bp-ui-popconfirm
                                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>'
                                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>'
                                                            icon="false"
                                                            title="<?php esc_html_e( 'Are you sure you want to delete this payment transaction?', 'bookingpress-appointment-booking' ); ?>"
                                                            @confirm="deletePaymentLog(scope.row.payment_log_id)"
                                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger"
                                                            cancel-button-type="bpa-btn bpa-btn__small">
                                                            <template #reference>
                                                                <bp-ui-button type="text" class="bpa-btn bpa-btn__filled-light __danger">
                                                                    <span class="material-icons-round">delete</span>
                                                                </bp-ui-button>
                                                            </template>
                                                        </bp-ui-popconfirm>
                                                    </div>
                                                </div>
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
                            <p><?php esc_html_e( 'Showing', 'bookingpress-appointment-booking' ); ?>&nbsp;<strong><u>{{ items.length }}</u></strong> <?php esc_html_e( 'out of', 'bookingpress-appointment-booking' ); ?>&nbsp;<strong>{{ totalItems }}</strong></p>
                            <div class="bpa-pagination-per-page">
                                <p><?php esc_html_e( 'Per Page', 'bookingpress-appointment-booking' ); ?></p>
                                <bp-ui-select v-model="pagination_length_val" placeholder="Select" @change="changePaginationSize($event)" class="bpa-form-control" popper-class="bpa-pagination-dropdown">
                                    <bp-ui-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></bp-ui-option>
                                </bp-ui-select>
                            </div>
                        </div>
                    </bp-ui-col>
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
                        <bp-ui-pagination :key="perPage" @current-change="handleCurrentChange" v-model:current-page="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></bp-ui-pagination>
                    </bp-ui-col>
                    <bp-ui-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card">
                        <bp-ui-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="closeBulkAction">
                            <span class="material-icons-round">close</span>
                        </bp-ui-button>
                        <bp-ui-row type="flex" class="bpa-bac__wrapper">
                            <bp-ui-col class="bpa-bac__left-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                <span class="material-icons-round">check_circle</span>
                                <p>{{ multipleSelection.length }}<?php esc_html_e( ' Items Selected', 'bookingpress-appointment-booking' ); ?></p>
                            </bp-ui-col>
                            <bp-ui-col class="bpa-bac__right-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                <bp-ui-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-dropdown--bulk-actions">
                                    <bp-ui-option v-for="bulk_action_data in bulk_options" :key="bulk_action_data.value" :label="bulk_action_data.label" :value="bulk_action_data.value"></bp-ui-option>
                                </bp-ui-select>
                                <bp-ui-button @click="bulk_actions" class="bpa-btn bpa-btn--primary bpa-btn__medium">
                                    <?php esc_html_e( 'Go', 'bookingpress-appointment-booking' ); ?>
                                </bp-ui-button>
                            </bp-ui-col>
                        </bp-ui-row>
                    </bp-ui-container>
                </bp-ui-row>
            </div>
        </bp-ui-main>
    </div>
</div>

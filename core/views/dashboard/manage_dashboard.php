<?php

    if ( ! defined( 'ABSPATH' ) ) { exit; }
    global $bookingpress_common_date_format;
?>

<el-main class="bpa-main-listing-card-container bpa-dashboard-container bpa--is-page-non-scrollable-mob" id="all-page-main-container">    
    <div class="bpa-default-card bpa-dashboard--summary">
        <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
            <div class="bpa-back-loader"></div>
        </div>
        <el-row type="flex" class="bpa-mlc-head-wrap">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
                <h1 class="bpa-page-heading"><?php esc_html_e('Dashboard', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                <div class="bpa-hw-right-btn-group">
                    <el-date-picker ref="bookingpress_custom_filter_rangepicker" v-model="custom_filter_val" class="bpa-form-control bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" @focus="bookingpress_remove_date_range_picker_focus" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker__is-filter-enabled bpa-date-range-picker-widget-wrapper" range-separator="-" :picker-options="bookingpress_picker_options" @change="select_dashboard_custom_date_filter($event)" value-format="yyyy-MM-dd" :clearable="false"></el-date-picker>
                </div>
            </el-col>
        </el-row>
        <div id="bpa-main-container">
            <div class="bpa-dashboard--summary-body">
                <div class="bpa-dashboard-summary">
                    <div class="bpa-dash-summary-item" @click="bookingpress_dashboard_redirect_filter(currently_selected_filter,'appointment','total')">
                        <h3 v-text="summary_data.total_appoint"></h3>
                        <p><?php esc_html_e('Total Appointments', 'bookingpress-appointment-booking'); ?></p>
                    </div>
                    <div class="bpa-dash-summary-item bpa-dash-summary-item__primary" @click="bookingpress_dashboard_redirect_filter(currently_selected_filter,'appointment','1')">
                        <h3 v-text="summary_data.approved_appoint"></h3>
                        <p><?php esc_html_e('Approved Appointments', 'bookingpress-appointment-booking'); ?></p>
                    </div>
                    <div class="bpa-dash-summary-item bpa-dash-summary-item__secondary" @click="bookingpress_dashboard_redirect_filter(currently_selected_filter,'appointment','2')">
                        <h3 v-text="summary_data.pending_appoint"></h3>
                        <p><?php esc_html_e('Pending Appointments', 'bookingpress-appointment-booking'); ?></p>
                    </div>
                    <div class="bpa-dash-summary-item bpa-dash-summary-item__royal-blue" @click="bookingpress_dashboard_redirect_filter(currently_selected_filter,'payment', '1')">
                        <h3 v-text="summary_data.total_revenue"></h3>
                        <p><?php esc_html_e('Revenue', 'bookingpress-appointment-booking'); ?></p>
                    </div>
                    <div class="bpa-dash-summary-item bpa-dash-summary-item__purple" @click="bookingpress_dashboard_redirect_filter(currently_selected_filter,'customer')">
                        <h3 v-text="summary_data.total_customers"></h3>
                        <p><?php esc_html_e('Customers', 'bookingpress-appointment-booking'); ?></p>
                    </div>
                </div>
            </div>
            <div class="bpa-dashboard--technical-analysis">
                <el-row type="flex" class="bpa-mlc-head-wrap">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading bpa-mlc-left-heading--is-visible-help">
                        <h1 class="bpa-page-heading"><?php esc_html_e('Technical Analysis', 'bookingpress-appointment-booking'); ?></h1>
                    </el-col>
                </el-row>
                <div class="bpa-dashboard--technical-analysis-body">
                    <el-row :gutter="24">
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                            <canvas id="appointments_charts"></canvas>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                            <canvas id="revenue_charts"></canvas>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                            <canvas id="customer_charts"></canvas>
                        </el-col>
                    </el-row>
                </div>
            </div>
            <el-row class="bpa-dashboard--upcoming-appointments">
                <el-row type="flex" class="bpa-mlc-head-wrap">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <h1 class="bpa-page-heading"><?php esc_html_e('Upcoming Appointments', 'bookingpress-appointment-booking'); ?></h1>
                    </el-col>
                </el-row>            
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
                            </div>
                        </div>
                    </el-col>
                </el-row>
                <el-row v-else>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-container class="bpa-table-container">
                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'desktop'">
                                <el-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" fit="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
                                    <el-table-column type="expand">
                                        <template slot-scope="scope">
                                            <div class="bpa-view-appointment-card">
                                                <div class="bpa-vac--head">
                                                    <div class="bpa-vac--head__left">											
                                                        <span><?php esc_html_e('Booking ID', 'bookingpress-appointment-booking'); ?>: #{{ scope.row.booking_id }}</span>
                                                        <div class="bpa-left__service-detail">
                                                            <h2>{{ scope.row.service_name }}</h2>
                                                            <span class="bpa-sd__price">{{ scope.row.appointment_payment }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-hw-right-btn-group bpa-vac--head__right">
                                                        <el-popconfirm 
                                                        cancel-button-text='<?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?>' 
                                                        confirm-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                        icon="false" 
                                                        title="<?php esc_html_e( 'Are you sure you want to cancel this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                        @confirm="bookingpress_change_status(scope.row.appointment_id, '3')" 
                                                        confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                        cancel-button-type="bpa-btn bpa-btn__small"
                                                        v-if="scope.row.appointment_status != '3'">
                                                            <el-button type="text" slot="reference" class="bpa-btn" v-if="scope.row.appointment_status != '3'">
                                                                <span class="material-icons-round">close</span>
                                                                <?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>
                                                            </el-button>
                                                        </el-popconfirm>&nbsp;
                                                    </div>
                                                </div>
                                                <div class="bpa-vac--body">
                                                    <el-row :gutter="56">
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="18">
                                                            <div class="bpa-vac-body--appointment-details">
                                                                <el-row :gutter="40">
                                                                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                        <div class="bpa-ad__basic-details">
                                                                            <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Basic Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span><?php esc_html_e('Date', 'bookingpress-appointment-booking'); ?></span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.view_appointment_date }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span><?php esc_html_e('Time', 'bookingpress-appointment-booking'); ?></span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.view_appointment_time }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.appointment_note != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.note}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.appointment_note }}</h4>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </el-col>
                                                                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                        <div class="bpa-ad__customer-details">
                                                                            <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Customer Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                            <div class="bpa-bd__item"  v-if="scope.row.customer_name != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.fullname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.customer_first_name != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                <span>{{form_field_data.firstname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_first_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head" v-if="scope.row.customer_last_name != ''">
                                                                                    <span>{{form_field_data.lastname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body" >
                                                                                    <h4>{{ scope.row.customer_last_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.customer_phone != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.phone_number}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_phone }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.email_address}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_email }}</h4>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </el-col>
                                                                </el-row>
                                                            </div>
                                                        </el-col>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
                                                            <div class="bpa-vac-body--payment-details">
                                                                <h4><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                <div class="bpa-pd__body">
                                                                    <div class="bpa-pd__item bpa-pd-method__item">
                                                                        <span><?php esc_html_e('Payment Method', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p>{{ scope.row.payment_method }}</p>
                                                                    </div>
                                                                    <div class="bpa-pd__item">
                                                                        <span><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p :class="((scope.row.appointment_status == '2') ? 'bpa-cl-pt-orange' : '') || (scope.row.appointment_status == '3' ? 'bpa-cl-black-200' : '') || (scope.row.appointment_status == '1' ? 'bpa-cl-pt-blue' : '') || (scope.row.appointment_status == '4' ? 'bpa-cl-danger' : '')">{{ scope.row.appointment_status_label }}</p>
                                                                    </div>
                                                                    <div class="bpa-pd__item bpa-pd-total__item">
                                                                        <span><?php esc_html_e('Total Amount', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p class="bpa-cl-pt-main-green">{{ scope.row.appointment_payment }}</p>
                                                                    </div>
                                                                </div>									
                                                            </div>
                                                        </el-col>
                                                    </el-row>										
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="booking_id" min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
                                        <template slot-scope="scope">
                                            <span>#{{ scope.row.booking_id }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="appointment_date" min-width="70" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable>
                                        <template slot-scope="scope">
                                            <label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="customer_name" min-width="120" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable>
                                        <template slot-scope="scope">
                                            <span v-if="scope.row.customer_name != ''">{{ scope.row.customer_name }}</span>
                                            <span v-else>{{ scope.row.customer_first_name }} {{ scope.row.customer_last_name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="service_name" min-width="120" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
                                    <el-table-column prop="appointment_duration" min-width="60" label="<?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>" sortable sort-by='bookingpress_service_duration_sortable'></el-table-column>
                                    <el-table-column prop="appointment_status" min-width="80" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
                                        <template slot-scope="scope">
                                            <div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''">
                                                <div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
                                                    <div class="bpa-btn--loader__circles">
                                                        <div></div>
                                                        <div></div>
                                                        <div></div>
                                                    </div>
                                                </div>
                                                <el-select class="bpa-form-control" :class="((scope.row.appointment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == '3' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == '1' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == '4' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event)" popper-class="bpa-appointment-status-dropdown-popper">
                                                    <el-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                        <el-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></el-option>
                                                    </el-option-group>
                                                </el-select>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="appointment_payment" min-width="60" label="<?php esc_html_e( 'Payment', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="payment_numberic_amount">
                                        <template slot-scope="scope">
                                            <div class="bpa-apc__amount-row">
                                                <div class="bpa-apc__ar-body">
                                                    <span class="bpa-apc__amount">{{ scope.row.appointment_payment }}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="created_date" label="<?php esc_html_e( 'Created Date', 'bookingpress-appointment-booking' ); ?>" sortable>
                                        <template slot-scope="scope">
                                            <label>{{ scope.row.created_date }}</label>
                                                <div class="bpa-table-actions-wrap">
                                                    <div class="bpa-table-actions">
                                                        <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                            <div slot="content">
                                                                <span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
                                                            </div>
                                                            <el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
                                                                <span class="material-icons-round">mode_edit</span>
                                                            </el-button>
                                                        </el-tooltip>
                                                    </div>
                                                </div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'tablet'">
                                <el-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" fit="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
                                    <el-table-column type="expand">
                                        <template slot-scope="scope">
                                            <div class="bpa-view-appointment-card">
                                                <div class="bpa-vac--head">
                                                    <div class="bpa-vac--head__left">											
                                                        <span><?php esc_html_e('Booking ID', 'bookingpress-appointment-booking'); ?>: #{{ scope.row.booking_id }}</span>
                                                        <div class="bpa-left__service-detail">
                                                            <h2>{{ scope.row.service_name }}</h2>
                                                            <span class="bpa-sd__price">{{ scope.row.appointment_payment }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-hw-right-btn-group bpa-vac--head__right">
                                                        <el-popconfirm 
                                                        cancel-button-text='<?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?>' 
                                                        confirm-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                        icon="false" 
                                                        title="<?php esc_html_e( 'Are you sure you want to cancel this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                        @confirm="bookingpress_change_status(scope.row.appointment_id, '3')" 
                                                        confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                        cancel-button-type="bpa-btn bpa-btn__small"
                                                        v-if="scope.row.appointment_status != '3'">
                                                            <el-button type="text" slot="reference" class="bpa-btn" v-if="scope.row.appointment_status != '3'">
                                                                <span class="material-icons-round">close</span>
                                                                <?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>
                                                            </el-button>
                                                        </el-popconfirm>&nbsp;
                                                    </div>
                                                </div>
                                                <div class="bpa-vac--body">
                                                    <el-row :gutter="56">
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="18">
                                                            <div class="bpa-vac-body--appointment-details">
                                                                <el-row :gutter="40">
                                                                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                        <div class="bpa-ad__basic-details">
                                                                            <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Basic Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span><?php esc_html_e('Date', 'bookingpress-appointment-booking'); ?></span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.view_appointment_date }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span><?php esc_html_e('Time', 'bookingpress-appointment-booking'); ?></span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.view_appointment_time }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.appointment_note != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.note}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.appointment_note }}</h4>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </el-col>
                                                                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                        <div class="bpa-ad__customer-details">
                                                                            <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Customer Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                            <div class="bpa-bd__item"  v-if="scope.row.customer_name != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.fullname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.customer_first_name != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                <span>{{form_field_data.firstname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_first_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head" v-if="scope.row.customer_last_name != ''">
                                                                                    <span>{{form_field_data.lastname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body" >
                                                                                    <h4>{{ scope.row.customer_last_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.customer_phone != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.phone_number}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_phone }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.email_address}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_email }}</h4>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </el-col>
                                                                </el-row>
                                                            </div>
                                                        </el-col>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
                                                            <div class="bpa-vac-body--payment-details">
                                                                <h4><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                <div class="bpa-pd__body">
                                                                    <div class="bpa-pd__item bpa-pd-method__item">
                                                                        <span><?php esc_html_e('Payment Method', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p>{{ scope.row.payment_method }}</p>
                                                                    </div>
                                                                    <div class="bpa-pd__item">
                                                                        <span><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p :class="((scope.row.appointment_status == '2') ? 'bpa-cl-pt-orange' : '') || (scope.row.appointment_status == '3' ? 'bpa-cl-black-200' : '') || (scope.row.appointment_status == '1' ? 'bpa-cl-pt-blue' : '') || (scope.row.appointment_status == '4' ? 'bpa-cl-danger' : '')">{{ scope.row.appointment_status_label }}</p>
                                                                    </div>
                                                                    <div class="bpa-pd__item bpa-pd-total__item">
                                                                        <span><?php esc_html_e('Total Amount', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p class="bpa-cl-pt-main-green">{{ scope.row.appointment_payment }}</p>
                                                                    </div>
                                                                </div>									
                                                            </div>
                                                        </el-col>
                                                    </el-row>										
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="booking_id" min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
                                        <template slot-scope="scope">
                                            <span>#{{ scope.row.booking_id }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="appointment_date" min-width="100" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable>
                                        <template slot-scope="scope">
                                            <label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
                                            <label class="bpa-item__date-col bpa-item__dt-col-duration-md">
                                                <span class="material-icons-round">schedule</span>
                                                {{ scope.row.appointment_duration }}
                                            </label>
                                        </template>
                                    </el-table-column>                                    
                                    <el-table-column prop="service_name" min-width="100" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>                                    
                                    <el-table-column prop="appointment_status" min-width="90" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
                                        <template slot-scope="scope">
                                            <div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''">
                                                <div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
                                                    <div class="bpa-btn--loader__circles">
                                                        <div></div>
                                                        <div></div>
                                                        <div></div>
                                                    </div>
                                                </div>
                                                <el-select class="bpa-form-control" :class="((scope.row.appointment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == '3' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == '1' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == '4' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event)" popper-class="bpa-appointment-status-dropdown-popper">
                                                    <el-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                        <el-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></el-option>
                                                    </el-option-group>
                                                </el-select>
                                            </div>
                                            <div class="bpa-table-actions-wrap">
                                                <div class="bpa-table-actions">
                                                    <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                        <div slot="content">
                                                            <span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
                                                        </div>
                                                        <el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
                                                            <span class="material-icons-round">mode_edit</span>
                                                        </el-button>
                                                    </el-tooltip>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                            <div class="bpa-tc__wrapper bpa-manage-appointment-container--sm" v-if="current_screen_size == 'mobile'">
                                <el-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" fit="false" @row-click="bookingpress_full_row_clickable" :show-header="false" @expand-change="bookingpress_row_expand">
                                    <el-table-column type="expand">
                                        <template slot-scope="scope">
                                            <div class="bpa-view-appointment-card">
                                                <div class="bpa-vac--head">
                                                    <div class="bpa-vac--head__left">											
                                                        <span><?php esc_html_e('Booking ID', 'bookingpress-appointment-booking'); ?>: #{{ scope.row.booking_id }}</span>
                                                        <div class="bpa-left__service-detail">
                                                            <h2>{{ scope.row.service_name }}</h2>
                                                            <span class="bpa-sd__price">{{ scope.row.appointment_payment }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-hw-right-btn-group bpa-vac--head__right">
                                                        <el-popconfirm 
                                                        cancel-button-text='<?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?>' 
                                                        confirm-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                        icon="false" 
                                                        title="<?php esc_html_e( 'Are you sure you want to cancel this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                        @confirm="bookingpress_change_status(scope.row.appointment_id, '3')" 
                                                        confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                        cancel-button-type="bpa-btn bpa-btn__small"
                                                        v-if="scope.row.appointment_status != '3'">
                                                            <el-button type="text" slot="reference" class="bpa-btn" v-if="scope.row.appointment_status != '3'">
                                                                <span class="material-icons-round">close</span>
                                                                <?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>
                                                            </el-button>
                                                        </el-popconfirm>&nbsp;
                                                    </div>
                                                </div>
                                                <div class="bpa-vac--body">
                                                    <el-row :gutter="56">
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="18">
                                                            <div class="bpa-vac-body--appointment-details">
                                                                <el-row :gutter="40">
                                                                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                        <div class="bpa-ad__basic-details">
                                                                            <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Basic Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span><?php esc_html_e('Date', 'bookingpress-appointment-booking'); ?></span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.view_appointment_date }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span><?php esc_html_e('Time', 'bookingpress-appointment-booking'); ?></span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.view_appointment_time }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.appointment_note != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.note}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.appointment_note }}</h4>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </el-col>
                                                                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                        <div class="bpa-ad__customer-details">
                                                                            <h4 class="bpa-vac__sec-heading"><?php esc_html_e('Customer Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                            <div class="bpa-bd__item"  v-if="scope.row.customer_name != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.fullname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.customer_first_name != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                <span>{{form_field_data.firstname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_first_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head" v-if="scope.row.customer_last_name != ''">
                                                                                    <span>{{form_field_data.lastname}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body" >
                                                                                    <h4>{{ scope.row.customer_last_name }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item" v-if="scope.row.customer_phone != ''">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.phone_number}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_phone }}</h4>
                                                                                </div>
                                                                            </div>
                                                                            <div class="bpa-bd__item">
                                                                                <div class="bpa-bd__item-head">
                                                                                    <span>{{form_field_data.email_address}}</span>
                                                                                </div>
                                                                                <div class="bpa-bd__item-body">
                                                                                    <h4>{{ scope.row.customer_email }}</h4>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </el-col>
                                                                </el-row>
                                                            </div>
                                                        </el-col>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
                                                            <div class="bpa-vac-body--payment-details">
                                                                <h4><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h4>
                                                                <div class="bpa-pd__body">
                                                                    <div class="bpa-pd__item bpa-pd-method__item">
                                                                        <span><?php esc_html_e('Payment Method', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p>{{ scope.row.payment_method }}</p>
                                                                    </div>
                                                                    <div class="bpa-pd__item">
                                                                        <span><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p :class="((scope.row.appointment_status == '2') ? 'bpa-cl-pt-orange' : '') || (scope.row.appointment_status == '3' ? 'bpa-cl-black-200' : '') || (scope.row.appointment_status == '1' ? 'bpa-cl-pt-blue' : '') || (scope.row.appointment_status == '4' ? 'bpa-cl-danger' : '')">{{ scope.row.appointment_status_label }}</p>
                                                                    </div>
                                                                    <div class="bpa-pd__item bpa-pd-total__item">
                                                                        <span><?php esc_html_e('Total Amount', 'bookingpress-appointment-booking'); ?></span>
                                                                        <p class="bpa-cl-pt-main-green">{{ scope.row.appointment_payment }}</p>
                                                                    </div>
                                                                </div>									
                                                            </div>
                                                        </el-col>
                                                    </el-row>										
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column>
                                        <template slot-scope="scope">
                                            <div class="bpa-ap-item__mob">
                                                <div class="bpa-api--head">
                                                    <h4>{{ scope.row.service_name }}</h4>
                                                    <div class="bpa-api--head-apointment-details">
                                                        <p><span class="material-icons-round">today</span>{{ scope.row.appointment_date }}</p>
                                                        <p><span class="material-icons-round">schedule</span>{{ scope.row.appointment_duration }}</p>
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
                                                        <el-select class="bpa-form-control" :class="((scope.row.appointment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == '3' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == '1' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == '4' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event)" popper-class="bpa-appointment-status-dropdown-popper">
                                                            <el-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                                <el-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></el-option>
                                                            </el-option-group>
                                                        </el-select>
                                                    </div>
                                                    <div class="bpa-mpay-fi__actions bpa-mac-fi__actions">
                                                        <el-button class="bpa-btn bpa-btn__small bpa-btn__filled-light" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
                                                            <span class="material-icons-round">mode_edit</span>
                                                        </el-button>
                                                        <el-popconfirm 
                                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                                            icon="false" 
                                                            title="<?php esc_html_e( 'Are you sure you want to delete this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                            @confirm="deleteAppointment(scope.$index, scope.row)" 
                                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                            cancel-button-type="bpa-btn bpa-btn__small">
                                                            <el-button type="text" slot="reference" class="bpa-btn bpa-btn__small bpa-btn__filled-light __danger">
                                                                <span class="material-icons-round">delete</span>
                                                            </el-button>
                                                        </el-popconfirm>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                        </el-container>
                    </el-col>
                </el-row>
            </el-row>
        </div>
    </div>
</el-main>
<el-dialog custom-class="bpa-dialog bpa-dialog--fullscreen bpa--is-page-non-scrollable-mob" modal-append-to-body=false :visible.sync="open_appointment_modal" :before-close="closeAppointmentModal" fullscreen=true :close-on-press-escape="close_modal_on_esc">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading" v-if="appointment_formdata.appointment_update_id == 0"><?php esc_html_e('Add Appointment', 'bookingpress-appointment-booking'); ?></h1>
                <h1 class="bpa-page-heading" v-else><?php esc_html_e('Edit Appointment', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
                <el-button class="bpa-btn bpa-btn--primary " :class="is_display_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="saveAppointmentBooking('appointment_formdata')" :disabled="is_disabled" >
                    <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                    <div class="bpa-btn--loader__circles">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </el-button> 
                <el-button class="bpa-btn" @click="closeAppointmentModal()"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></el-button>
            </el-col>
        </el-row>
    </div>
    <div class="bpa-dialog-body">
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
                        <el-form ref="appointment_formdata" :rules="rules" :model="appointment_formdata" label-position="top" @submit.native.prevent>
                            <template>                                
                                <div class="bpa-form-body-row">
                                    <el-row :gutter="32">
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">                                            
                                            <el-form-item prop="appointment_selected_customer">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Select Customer', 'bookingpress-appointment-booking'); ?></span>
                                                </template>                                                
                                                <el-select class="bpa-form-control" name="appointment_selected_customer" v-model="appointment_formdata.appointment_selected_customer" filterable placeholder="<?php esc_html_e( 'Start typing to fetch customer', 'bookingpress-appointment-booking' ); ?>" remote reserve-keyword :remote-method="bookingpress_get_customer_list" :loading="bookingpress_loading"  popper-class="bpa-el-select--is-with-modal" v-cancel-read-only>
                                                    <el-option v-for="item in appointment_customers_list" :key="item.value" :label="item.text" :value="item.value">
                                                        <span>{{ item.text }}</span>
                                                    </el-option>
                                                </el-select>   
                                            </el-form-item>
                                        </el-col>                                                    
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="appointment_selected_service">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Select Service', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-select class="bpa-form-control" @Change="bookingpress_appointment_change_service" v-model="appointment_formdata.appointment_selected_service" name="appointment_selected_service" filterable placeholder="<?php esc_html_e('Select service', 'bookingpress-appointment-booking'); ?>" >
                                                    <el-option-group v-for="service_cat_data in appointment_services_list" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
                                                        <template v-if="service_data.service_id == 0" v-for="service_data in service_cat_data.category_services">
                                                            <el-option :key="service_data.service_id" :label="service_data.service_name" :value="''" ></el-option>
                                                        </template>
                                                        <template v-else>
                                                            <el-option :key="service_data.service_id" :label="service_data.service_name+' ('+service_data.service_price+' )' + ' - ' +service_data.service_duration_formatted" :value="service_data.service_id"></el-option>
                                                        </template>
                                                    </el-option-group>
                                                </el-select>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                                            <el-form-item prop="appointment_booked_date">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Appointment Date', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-date-picker class="bpa-form-control bpa-form-control--date-picker" type="date" :format="bpa_date_common_date_format" v-model="appointment_formdata.appointment_booked_date" name="appointment_booked_date" :clearable="false" @change="select_appointment_booking_date($event)" :picker-options="pickerOptions" popper-class="bpa-el-datepicker-widget-wrapper" value-format="yyyy-MM-dd"></el-date-picker>
                                        </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                            <el-form-item prop="appointment_booked_time">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Appointment Time', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-select class="bpa-form-control" Placeholder="<?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?>" v-model="appointment_formdata.appointment_booked_time" filterable popper-class="bpa-el-select--is-with-modal" @Change="bookingpress_set_time($event,appointment_time_slot)">
                                                <el-option-group v-for="appointment_time_slot_data in appointment_time_slot" :key="appointment_time_slot_data.timeslot_label" :label="appointment_time_slot_data.timeslot_label">
                                                    <el-option v-for="appointment_time in appointment_time_slot_data.timeslots" :label="(appointment_time.formatted_start_time)+' to '+(appointment_time.formatted_end_time)" :value="appointment_time.store_start_time" :disabled="( appointment_time.is_disabled || appointment_time.max_capacity <= appointment_time.total_booked || appointment_time.max_capacity == 0 || appointment_time.is_booked == 1 )">
                                                    <span>{{ appointment_time.formatted_start_time  }} to {{appointment_time.formatted_end_time}}</span>
                                                    </el-option>	
                                                </el-option-group>
                                            </el-select>
                                        </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                            <el-form-item>
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Select Status', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-select class="bpa-form-control" v-model="appointment_formdata.appointment_status">
                                                <el-option v-for="status_data in appointment_status" :key="status_data.value" :label="status_data.text" :value="status_data.value">
                                                    <span>{{ status_data.text }}</span>
                                                </el-option>
                                            </el-select>
                                            </el-form-item>
                                        </el-col>
                                    </el-row>
                                </div>
                                <div class="bpa-form-body-row">
                                    <el-row :gutter="24">
                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                            <el-form-item>
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Internal note', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <el-input class="bpa-form-control" type="textarea" :rows="5" v-model="appointment_formdata.appointment_internal_note"></el-input>
                                            </el-form-item>
                                        </el-col>
                                    </el-row>
                                </div>                                
                                <div class="bpa-form-body-row">
                                    <el-row :gutter="24">
                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                            <el-form-item>
                                                <label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox v-model="appointment_formdata.appointment_send_notification"></el-checkbox> <?php esc_html_e('Do Not Send Notification', 'bookingpress-appointment-booking'); ?></label>
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

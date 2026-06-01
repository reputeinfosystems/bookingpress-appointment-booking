<div id="root_app" class="bookingpress-dashboard bookingpress_page_wrapper">
    <?php
        if( class_exists( '\BookingPressPro\admin\Dashboard') && method_exists( '\BookingPressPro\admin\Dashboard', 'render_dashboard_header' ) ){
            \BookingPressPro\admin\Dashboard::render_dashboard_header();
        } else {
            require_once __DIR__ . '/components/Header.php';
        }
    ?>

    <div class="dashboard-app-root bookingpress_page_inner_wrapper" v-cloak id="dashboard-app-root">
        <bp-ui-main class="bpa-main-listing-card-container bpa-dashboard-container bpa--is-page-non-scrollable-mob" id="all-page-main-container">    
            <div class="bpa-default-card bpa-dashboard--summary">
                <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
                    <div class="bpa-back-loader"></div>
                </div>
                <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                    <bp-ui-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
                        <h1 class="bpa-page-heading"><?php esc_html_e('Dashboard', 'bookingpress-appointment-booking'); ?></h1>
                    </bp-ui-col>
                    <bp-ui-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                        <div class="bpa-hw-right-btn-group">
                            <bp-ui-date-picker ref="bookingpress_custom_filter_rangepicker" v-model="custom_filter_val" class="bpa-form-control bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" :teleported="false" @focus="bookingpress_remove_date_range_picker_focus" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker__is-filter-enabled bpa-date-range-picker-widget-wrapper" range-separator="-" :shortcuts="bookingpress_picker_options" @change="select_dashboard_custom_date_filter($event)" value-format="YYYY-MM-DD" locale="<?php echo get_locale(); ?>" :clearable="false"></bp-ui-date-picker>
                        </div>
                    </bp-ui-col>
                </bp-ui-row>
                <div id="bpa-main-container">
                    <div class="bpa-dashboard--summary-body">
                        <?php
                            if( class_exists( 'BookingPressPro\admin\Dashboard') && method_exists( 'BookingPressPro\admin\Dashboard', 'render_dashboard_summary_filters' ) ) {
                                BookingPressPro\admin\Dashboard::render_dashboard_summary_filters();
                            } else {
                        ?>
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
                        <?php
                            }
                        ?>
                        
                    </div>
                    <div class="bpa-dashboard--technical-analysis">
                        <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading bpa-mlc-left-heading--is-visible-help">
                                <h1 class="bpa-page-heading"><?php esc_html_e('Technical Analysis', 'bookingpress-appointment-booking'); ?></h1>
                            </bp-ui-col>
                        </bp-ui-row>
                        <div class="bpa-dashboard--technical-analysis-body">
                            <bp-ui-row :gutter="24">
                                <bp-ui-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                                    <canvas id="appointments_charts"></canvas>
                                </bp-ui-col>
                                <bp-ui-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                                    <canvas id="revenue_charts"></canvas>
                                </bp-ui-col>
                                <bp-ui-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                                    <canvas id="customer_charts"></canvas>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>
                    </div>
                    <bp-ui-row class="bpa-dashboard--upcoming-appointments">
                        <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <h1 class="bpa-page-heading"><?php esc_html_e('Upcoming Appointments', 'bookingpress-appointment-booking'); ?></h1>
                            </bp-ui-col>
                        </bp-ui-row>            
                        <bp-ui-row v-if="items.length == 0">
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
                        <bp-ui-row v-else>
                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <bp-ui-container class="bpa-table-container">
                                    <?php
                                        if( class_exists( 'BookingPressPro\admin\Dashboard') && method_exists( 'BookingPressPro\admin\Dashboard', 'render_dashboard_appointments_table' ) ) {
                                            BookingPressPro\admin\Dashboard::render_dashboard_appointments_table();
                                        } else {
                                            ?>
                                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'desktop'">
                                                <bp-ui-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" fit="true" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
                                                    <bp-ui-table-column type="expand" :expand-icon="CirclePlusFilled" :collapse-icon="RemoveFilled">
                                                        <template #default="scope">
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
                                                                        <bp-ui-popconfirm 
                                                                        cancel-button-text='<?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?>' 
                                                                        confirm-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                                        icon="false" 
                                                                        title="<?php esc_html_e( 'Are you sure you want to cancel this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                                        @confirm="bookingpress_change_status(scope.row.appointment_id, '3', scope.row)" 
                                                                        confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                                        cancel-button-type="bpa-btn bpa-btn__small"
                                                                        v-if="scope.row.appointment_status != '3'">
                                                                            <bp-ui-button type="text" slot="reference" class="bpa-btn" v-if="scope.row.appointment_status != '3'">
                                                                                <span class="material-icons-round">close</span>
                                                                                <?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>
                                                                            </bp-ui-button>
                                                                        </bp-ui-popconfirm>&nbsp;
                                                                    </div>
                                                                </div>
                                                                <div class="bpa-vac--body">
                                                                    <bp-ui-row :gutter="56">
                                                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="16" :xl="18">
                                                                            <div class="bpa-vac-body--appointment-details">
                                                                                <bp-ui-row :gutter="40">
                                                                                    <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
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
                                                                                    </bp-ui-col>
                                                                                    <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
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
                                                                                    </bp-ui-col>
                                                                                </bp-ui-row>
                                                                            </div>
                                                                        </bp-ui-col>
                                                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
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
                                                                        </bp-ui-col>
                                                                    </bp-ui-row>										
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column prop="booking_id" :min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
                                                        <template #default="scope">
                                                            <span>#{{ scope.row.booking_id }}</span>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column prop="appointment_date" min-width="100" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable>
                                                        <template #default="scope">
                                                            <label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column prop="customer_name" min-width="120" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable>
                                                        <template #default="scope">
                                                            <span v-if="scope.row.customer_name != ''">{{ scope.row.customer_name }}</span>
                                                            <span v-else>{{ scope.row.customer_first_name }} {{ scope.row.customer_last_name }}</span>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column prop="service_name" min-width="120" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable></bp-ui-table-column>
                                                    <bp-ui-table-column prop="appointment_duration" min-width="60" label="<?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>" sortable sort-by='bookingpress_service_duration_sortable'></bp-ui-table-column>
                                                    <bp-ui-table-column prop="appointment_status" min-width="80" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
                                                        <template #default="scope">
                                                            <div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''">
                                                                <div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
                                                                    <div class="bpa-btn--loader__circles">
                                                                        <div></div>
                                                                        <div></div>
                                                                        <div></div>
                                                                    </div>
                                                                </div>
                                                                <bp-ui-select class="bpa-form-control bpa-appointment-status-dropdown-wrapper" :class="appointment_status_class[scope.row.appointment_status]" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" append-to="body" teleported="true" @change="bookingpress_change_status(scope.row.appointment_id, $event, scope.row)" popper-class="bpa-appointment-status-dropdown-popper">
                                                                    <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                                        <bp-ui-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                                    </bp-ui-option-group>
                                                                </bp-ui-select>
                                                            </div>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column prop="appointment_payment" min-width="60" label="<?php esc_html_e( 'Payment', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="payment_numberic_amount">
                                                        <template #default="scope">
                                                            <div class="bpa-apc__amount-row">
                                                                <div class="bpa-apc__ar-body">
                                                                    <span class="bpa-apc__amount">{{ scope.row.appointment_payment }}</span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column prop="created_date" label="<?php esc_html_e( 'Created Date', 'bookingpress-appointment-booking' ); ?>" sortable>
                                                        <template #default="scope">
                                                            <label>{{ scope.row.created_date }}</label>
                                                            <div class="bpa-table-actions-wrap">
                                                                <div class="bpa-table-actions">
                                                                    <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                                        <template #content>
                                                                            <span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
                                                                        </template>
                                                                        <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
                                                                            <span class="material-icons-round">mode_edit</span>
                                                                        </bp-ui-button>
                                                                    </bp-ui-tooltip>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </bp-ui-table-column>
                                                </bp-ui-table>
                                            </div>
                                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'tablet'">
                                                <bp-ui-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" fit="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
                                                    <bp-ui-table-column type="expand" :expand-icon="CirclePlusFilled" :collapse-icon="RemoveFilled">
                                                        <template #default="scope">
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
                                                                        <bp-ui-popconfirm 
                                                                        cancel-button-text='<?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?>' 
                                                                        confirm-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                                        icon="false" 
                                                                        title="<?php esc_html_e( 'Are you sure you want to cancel this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                                        @confirm="bookingpress_change_status(scope.row.appointment_id, '3', scope.row)" 
                                                                        confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                                        cancel-button-type="bpa-btn bpa-btn__small"
                                                                        v-if="scope.row.appointment_status != '3'">
                                                                            <bp-ui-button type="text" slot="reference" class="bpa-btn" v-if="scope.row.appointment_status != '3'">
                                                                                <span class="material-icons-round">close</span>
                                                                                <?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>
                                                                            </bp-ui-button>
                                                                        </bp-ui-popconfirm>&nbsp;
                                                                    </div>
                                                                </div>
                                                                <div class="bpa-vac--body">
                                                                    <bp-ui-row :gutter="56">
                                                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="16" :xl="18">
                                                                            <div class="bpa-vac-body--appointment-details">
                                                                                <bp-ui-row :gutter="40">
                                                                                    <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
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
                                                                                    </bp-ui-col>
                                                                                    <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
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
                                                                                    </bp-ui-col>
                                                                                </bp-ui-row>
                                                                            </div>
                                                                        </bp-ui-col>
                                                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
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
                                                                        </bp-ui-col>
                                                                    </bp-ui-row>										
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column prop="booking_id" min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
                                                        <template #default="scope">
                                                            <span>#{{ scope.row.booking_id }}</span>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column prop="appointment_date" min-width="100" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable>
                                                        <template #default="scope">
                                                            <label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
                                                            <label class="bpa-item__date-col bpa-item__dt-col-duration-md">
                                                                <span class="material-icons-round">schedule</span>
                                                                {{ scope.row.appointment_duration }}
                                                            </label>
                                                        </template>
                                                    </bp-ui-table-column>                                    
                                                    <bp-ui-table-column prop="service_name" min-width="100" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable></bp-ui-table-column>                                    
                                                    <bp-ui-table-column prop="appointment_status" min-width="90" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
                                                        <template #default="scope">
                                                            <div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''">
                                                                <div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
                                                                    <div class="bpa-btn--loader__circles">
                                                                        <div></div>
                                                                        <div></div>
                                                                        <div></div>
                                                                    </div>
                                                                </div>
                                                                <bp-ui-select class="bpa-form-control" :class="((scope.row.appointment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == '3' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == '1' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == '4' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event, scope.row)" popper-class="bpa-appointment-status-dropdown-popper">
                                                                    <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                                        <bp-ui-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                                    </bp-ui-option-group>
                                                                </bp-ui-select>
                                                            </div>
                                                            <div class="bpa-table-actions-wrap">
                                                                <div class="bpa-table-actions">
                                                                    <bp-ui-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                                        <div slot="content">
                                                                            <span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
                                                                        </div>
                                                                        <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
                                                                            <span class="material-icons-round">mode_edit</span>
                                                                        </bp-ui-button>
                                                                    </bp-ui-tooltip>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </bp-ui-table-column>
                                                </bp-ui-table>
                                            </div>
                                            <div class="bpa-tc__wrapper bpa-manage-appointment-container--sm" v-if="current_screen_size == 'mobile'">
                                                <bp-ui-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" fit="false" @row-click="bookingpress_full_row_clickable" :show-header="false" @expand-change="bookingpress_row_expand">
                                                    <bp-ui-table-column type="expand" :expand-icon="CirclePlusFilled" :collapse-icon="RemoveFilled">
                                                        <template #default="scope">
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
                                                                        <bp-ui-popconfirm 
                                                                        cancel-button-text='<?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?>' 
                                                                        confirm-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                                        icon="false" 
                                                                        title="<?php esc_html_e( 'Are you sure you want to cancel this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                                        @confirm="bookingpress_change_status(scope.row.appointment_id, '3', scope.row)" 
                                                                        confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                                        cancel-button-type="bpa-btn bpa-btn__small"
                                                                        v-if="scope.row.appointment_status != '3'">
                                                                            <bp-ui-button type="text" slot="reference" class="bpa-btn" v-if="scope.row.appointment_status != '3'">
                                                                                <span class="material-icons-round">close</span>
                                                                                <?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>
                                                                            </bp-ui-button>
                                                                        </bp-ui-popconfirm>&nbsp;
                                                                    </div>
                                                                </div>
                                                                <div class="bpa-vac--body">
                                                                    <bp-ui-row :gutter="56">
                                                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="16" :xl="18">
                                                                            <div class="bpa-vac-body--appointment-details">
                                                                                <bp-ui-row :gutter="40">
                                                                                    <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
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
                                                                                    </bp-ui-col>
                                                                                    <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
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
                                                                                    </bp-ui-col>
                                                                                </bp-ui-row>
                                                                            </div>
                                                                        </bp-ui-col>
                                                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
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
                                                                        </bp-ui-col>
                                                                    </bp-ui-row>										
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </bp-ui-table-column>
                                                    <bp-ui-table-column>
                                                        <template #default="scope">
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
                                                                        <bp-ui-select class="bpa-form-control" :class="((scope.row.appointment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == '3' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == '1' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == '4' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event, scope.row)" popper-class="bpa-appointment-status-dropdown-popper">
                                                                            <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                                                <bp-ui-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                                            </bp-ui-option-group>
                                                                        </bp-ui-select>
                                                                    </div>
                                                                    <div class="bpa-mpay-fi__actions bpa-mac-fi__actions">
                                                                        <bp-ui-button class="bpa-btn bpa-btn__small bpa-btn__filled-light" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
                                                                            <span class="material-icons-round">mode_edit</span>
                                                                        </bp-ui-button>
                                                                        <bp-ui-popconfirm 
                                                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                                                            icon="false" 
                                                                            title="<?php esc_html_e( 'Are you sure you want to delete this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                                            @confirm="deleteAppointment(scope.$index, scope.row)" 
                                                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                                            cancel-button-type="bpa-btn bpa-btn__small">
                                                                            <bp-ui-button type="text" slot="reference" class="bpa-btn bpa-btn__small bpa-btn__filled-light __danger">
                                                                                <span class="material-icons-round">delete</span>
                                                                            </bp-ui-button>
                                                                        </bp-ui-popconfirm>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </bp-ui-table-column>
                                                </bp-ui-table>
                                            </div>
                                            <?php
                                        }
                                    ?>
                                    
                                </bp-ui-container>
                            </bp-ui-col>
                        </bp-ui-row>
                    </bp-ui-row>
                </div>
            </div>
        </bp-ui-main>
    </div>

</div>

<?php

if( class_exists( 'BookingPressPro\admin\Dashboard' ) && method_exists( 'BookingPressPro\admin\Dashboard', 'getViewComponents' ) ) {
    \BookingPressPro\admin\Dashboard::getViewComponents();
} else {    
    require_once __DIR__ . '/components/AppointmentModel.php';
    require_once __DIR__ . '/components/CustomerModel.php';
    require_once __DIR__ . '/components/RescheduleModel.php';
    require_once __DIR__ . '/components/SideMenuDrawer.php';
}
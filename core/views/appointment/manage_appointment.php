<?php

    if ( ! defined( 'ABSPATH' ) ) { exit; }

    global $wpdb, $bookingpress_ajaxurl, $BookingPress,$bookingpress_common_date_format, $tbl_bookingpress_appointment_bookings;

    $bookingpress_count_record = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) as total FROM {$tbl_bookingpress_appointment_bookings}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-non-scrollable-mob" id="all-page-main-container">
    <el-row type="flex" class="bpa-mlc-head-wrap">
        <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
            <h1 class="bpa-page-heading"><?php esc_html_e('Manage Appointments', 'bookingpress-appointment-booking'); ?></h1>
        </el-col>        
        <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <div class="bpa-hw-right-btn-group">                
                <el-button class="bpa-btn bpa-btn--primary" @click="open_add_appointment_modal()"> 
                    <span class="material-icons-round">add</span> 
                    <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                </el-button>
                <el-button id="bpa-appointment-share-url-button" class="bpa-btn" @click="bookingpress_share_url_modal">
					<span class="material-icons-round">share</span>
					<?php esc_html_e( 'Share URL', 'bookingpress-appointment-booking' ); ?>
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
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                    <span class="bpa-form-label"><?php esc_html_e('Appointment Date', 'bookingpress-appointment-booking'); ?></span>
                    <el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="appointment_date_range" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker-widget-wrapper" range-separator=" - " value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"> </el-date-picker>
                </el-col>            
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                    <span class="bpa-form-label"><?php esc_html_e('Customer Name', 'bookingpress-appointment-booking'); ?></span>    
                    <el-select class="bpa-form-control bpa-from-select-tab" v-model="search_customer_name" multiple filterable collapse-tags placeholder="<?php esc_html_e( 'Start typing to fetch customer', 'bookingpress-appointment-booking' ); ?>" remote reserve-keyword	 :remote-method="bookingpress_get_search_customer_list" :loading="bookingpress_loading" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
						<el-option v-for="item in search_customer_list" :key="item.value" :label="item.text" :value="item.value"></el-option>
					</el-select>
                </el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                    <span class="bpa-form-label"><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></span>
                    <el-select class="bpa-form-control bpa-from-select-tab" v-model="search_service_name" multiple filterable collapse-tags 
                        placeholder="<?php esc_html_e('Select service', 'bookingpress-appointment-booking'); ?>"
                        :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
                       <el-option-group v-for="service_cat_data in appointment_services_data" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
                            <el-option v-for="service_data in service_cat_data.category_services" :key="service_data.service_id" :label="service_data.service_name" :value="service_data.service_id"></el-option>
                        </el-option-group>
                    </el-select>
                </el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                    <span class="bpa-form-label"><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></span>        
                    <el-select class="bpa-form-control bpa-from-select-tab" v-model="search_appointment_status" 
                        placeholder="<?php esc_html_e('Select status', 'bookingpress-appointment-booking'); ?>"
                        :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
                        <el-option label="<?php esc_html_e('All', 'bookingpress-appointment-booking'); ?>" value="all"></el-option>
                        <el-option v-for="item in search_status" :key="item.value" :label="item.text" :value="item.value"></el-option>
                    </el-select>
                </el-col>            
            </el-row><br>
            <el-row type="flex" :gutter="32">
                <el-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4">
                    <el-input class="bpa-form-control" v-model="search_appointment_id" placeholder="<?php esc_html_e('Appointment ID', 'bookingpress-appointment-booking'); ?>" @input="isOnlyNumber($event)" >    
                    </el-input>
                </el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                    <el-input class="bpa-form-control" v-model="search_appointment" placeholder="<?php esc_html_e('Search for customers, services...', 'bookingpress-appointment-booking'); ?>" >    
                    </el-input>
                </el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                    <div class="bpa-tf-btn-group">
                        <el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
                            <?php esc_html_e('Reset', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                        <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadAppointments(true)">
                            <?php esc_html_e('Apply', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                    </div>
                </el-col>
            </el-row><br>
        </div>
        <div id="bpa-loader-div">
            <el-row type="flex" v-show="items.length == 0">
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
                            
                            <el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_appointment_modal()">                         
                                <span class="material-icons-round">add</span> 
                                <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                            </el-button>
                        </div>
                    </div>
                </el-col>
            </el-row>
        </div>
        <el-row v-if="items.length > 0">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-container class="bpa-table-container">
                    <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_loader == '1'">
                        <div class="bpa-back-loader"></div>
                    </div>
                    <div class="bpa-tc__wrapper" v-if="current_screen_size == 'desktop'">
                        <el-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" @sort-change="handel_appointment_changes" @selection-change="handleSelectionChange" fit="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
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
                            <el-table-column type="selection"></el-table-column>
                            <el-table-column prop="booking_id" min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
                                <template slot-scope="scope">
                                    <span>#{{ scope.row.booking_id }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="appointment_date" min-width="70" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by="sort_appointment_date_time">
                                <template slot-scope="scope">
                                    <label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
                                </template>
                            </el-table-column>
                            <el-table-column prop="customer_name" min-width="120" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by='customer_name'></el-table-column>
                            <el-table-column prop="service_name" min-width="120" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by='service_name'></el-table-column>
                            <el-table-column prop="appointment_duration" min-width="60" label="<?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by='bookingpress_service_duration_sortable'></el-table-column>
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
                            <el-table-column prop="appointment_payment" min-width="60" label="<?php esc_html_e( 'Payment', 'bookingpress-appointment-booking' ); ?>" sort-by="payment_numberic_amount">
                                <template slot-scope="scope">
                                    <div class="bpa-apc__amount-row">
                                        <div class="bpa-apc__ar-body">
                                            <span class="bpa-apc__amount">{{ scope.row.appointment_payment }}</span>
                                        </div>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="created_date" label="<?php esc_html_e( 'Created Date', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by="bookingpress_appointment_created_date">
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
                                                    
                                                <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                    <div slot="content">
                                                        <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                    </div>
                                                    <el-popconfirm 
                                                        cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                        confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                                        icon="false" 
                                                        title="<?php esc_html_e( 'Are you sure you want to delete this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                        @confirm="deleteAppointment(scope.$index, scope.row)" 
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
                        <el-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" @sort-change="handel_appointment_changes" @selection-change="handleSelectionChange" fit="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
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
                            <el-table-column type="selection"></el-table-column>
                            <el-table-column prop="booking_id" min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
                                <template slot-scope="scope">
                                    <span>#{{ scope.row.booking_id }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="appointment_date" min-width="100" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="view_appointment_date">
                                <template slot-scope="scope">
                                    <label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
                                    <label class="bpa-item__date-col bpa-item__dt-col-duration-md">
										<span class="material-icons-round">schedule</span>
										{{ scope.row.appointment_duration }}
									</label>
                                </template>
                            </el-table-column>
                            <el-table-column prop="service_name" min-width="100" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable sort-by='service_name'></el-table-column>
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
                                            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                <div slot="content">
                                                    <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                </div>
                                                <el-popconfirm 
                                                    cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                    confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                                    icon="false" 
                                                    title="<?php esc_html_e( 'Are you sure you want to delete this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                    @confirm="deleteAppointment(scope.$index, scope.row)" 
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
                    <div class="bpa-tc__wrapper bpa-manage-appointment-container--sm" v-if="current_screen_size == 'mobile'">
                        <el-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" @selection-change="handleSelectionChange" fit="false" :show-header="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
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
                            <el-table-column type="selection"></el-table-column>
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
                                                <el-button class="bpa-btn bpa-btn__filled-light" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
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
                                                    <el-button type="text" slot="reference" class="bpa-btn bpa-btn__filled-light __danger">
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
            <el-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card" >
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
                        <el-button @click="bulk_actions()" class="bpa-btn bpa-btn--primary bpa-btn__medium">
                            <?php esc_html_e('Go', 'bookingpress-appointment-booking'); ?>
                        </el-button>
                    </el-col>
                </el-row>
            </el-container>        
        </el-row>
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
                <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveAppointmentBooking('appointment_formdata')" :disabled="is_disabled" >                    
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
                        <el-form ref="appointment_formdata" :rules="rules" :model="appointment_formdata" label-position="top" @submit.native.prevent>
                            <template>                                
                                <div class="bpa-form-body-row">
                                    <el-row :gutter="32">
                                        <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">                                            
                                            <el-form-item prop="appointment_selected_customer">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Select Customer', 'bookingpress-appointment-booking'); ?></span>
                                                </template>                                            
                                                <el-select class="bpa-form-control" name="appointment_selected_customer" v-model="appointment_formdata.appointment_selected_customer" filterable placeholder="<?php esc_html_e( 'Start typing to fetch customer', 'bookingpress-appointment-booking' ); ?>" @change="bpa_select_customer($event)" remote reserve-keyword :remote-method="bookingpress_get_customer_list" :loading="bookingpress_loading"  popper-class="bpa-el-select--is-with-modal" v-cancel-read-only >
                                                    <el-option value="add_new" label="Add New" v-if="bookingpress_edit_customers == 1">
														<i class="el-icon-plus" ></i>
														<span><?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?></span>
													</el-option>
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
                                                <el-select class="bpa-form-control" @Change="bookingpress_appointment_change_service()" v-model="appointment_formdata.appointment_selected_service" name="appointment_selected_service" filterable 
                                                placeholder="<?php esc_html_e('Select Service', 'bookingpress-appointment-booking'); ?>"
                                                popper-class="bpa-el-select--is-with-modal">
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
                                            <el-date-picker class="bpa-form-control bpa-form-control--date-picker" type="date" :format="bpa_date_common_date_format" v-model="appointment_formdata.appointment_booked_date" name="appointment_booked_date" popper-class="bpa-el-datepicker-widget-wrapper" type="date" :clearable="false" :picker-options="pickerOptions" @change="select_appointment_booking_date($event)" value-format="yyyy-MM-dd"></el-date-picker>
                                        </el-form-item>
                                        </el-col>
                                        <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                                            <el-form-item prop="appointment_booked_time">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Appointment Time', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-select class="bpa-form-control" Placeholder="<?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?>" v-model="appointment_formdata.appointment_booked_time" filterable popper-class="bpa-el-select--is-with-modal" @Change="bookingpress_set_time($event,appointment_time_slot)"> 
                                                <el-option-group v-for="appointment_time_slot_data in appointment_time_slot" :key="appointment_time_slot_data.timeslot_label" :label="appointment_time_slot_data.timeslot_label" >
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
                                                <el-option v-for="status_data in appointment_status" :label="status_data.text" :value="status_data.value">
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


<?php /* Share URL Modal */ ?>
<el-dialog custom-class="bpa-dialog bpa-dailog__small bpa-dialog--share-url" id="appointment_share_url" title="" :visible.sync="bpa_share_url_modal" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Share Appointment', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container">
			<div class="bpa-form-row">				
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form label-position="top" @submit.native.prevent :rules="share_url_rules" :model="share_url_form" ref="share_url_form">
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="selected_page_wp_id">
											<template #label>
												<span class="bpa-form-label"><?php echo esc_html__('Select Page', 'bookingpress-appointment-booking'); ?></span>
											</template>
                                            <el-select class="bpa-form-control" v-model="share_url_form.selected_page_wp_id" filterable collapse-tags placeholder="<?php esc_html_e( 'Search for Page', 'bookingpress-appointment-booking' ); ?>" remote reserve-keyword	 :remote-method="bookingpress_get_page_list"  @change="bookingpress_generate_share_url" :loading="bookingpress_loading" popper-class="bpa-el-select--is-with-modal">  
                                                <el-option :label="pages_list.title" :key="pages_list.id"  :value="pages_list.id" v-for="pages_list in all_share_pages_list"></el-option>
											</el-select>
                                        </el-form-item>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="selected_service_id">
											<template #label>
												<span class="bpa-form-label"><?php echo esc_html__('Select Service', 'bookingpress-appointment-booking'); ?></span>
											</template>
											<el-select v-model="share_url_form.selected_service_id"  class="bpa-form-control" filterable placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-el-select--is-with-modal" @change="bpa_enable_service_share">
												<el-option-group v-for="service_cat_data in appointment_services_list" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
													<template v-if="service_data.service_id == 0" v-for="service_data in service_cat_data.category_services">
														<el-option :key="service_data.service_id" :label="service_data.service_name" :value="''" ></el-option>
													</template>
													<template v-else>
														<el-option :key="service_data.service_id" :label="service_data.service_name+' ('+service_data.service_price+' )'" :value="service_data.service_id"></el-option>
													</template>
												</el-option-group>
											</el-select>
										</el-form-item>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-form-body-row bpa-dsu__checkbox-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item>
											<template #label>
												<span class="bpa-form-label"><?php echo esc_html__('Share With', 'bookingpress-appointment-booking'); ?></span>
											</template>
											<label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox v-model="share_url_form.email_sharing" @change="bpa_enable_service_share"></el-checkbox> <?php esc_html_e( 'Email', 'bookingpress-appointment-booking' ); ?></label>
										</el-form-item>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-form-body-row" v-if="share_url_form.email_sharing == true">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="sharing_email">
											<template #label>
												<span class="bpa-form-label"><?php echo esc_html__('Email', 'bookingpress-appointment-booking'); ?></span>
											</template>
											<el-input class="bpa-form-control" v-model="share_url_form.sharing_email" placeholder="<?php esc_html_e('Enter email address', 'bookingpress-appointment-booking'); ?>" @blur="bpa_enable_service_share"></el-input>
										</el-form-item>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item>
											<label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox v-model="share_url_form.allow_customer_to_modify" @change="bookingpress_generate_share_url"></el-checkbox> <?php esc_html_e( 'Customer can modify option', 'bookingpress-appointment-booking' ); ?></label>
										</el-form-item>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-form-body-row bpa-dsu__url-val-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item>
											<template #label>
												<span class="bpa-form-label"><?php echo esc_html__('URL', 'bookingpress-appointment-booking'); ?></span>
											</template>
											<el-input class="bpa-form-control" v-model="share_url_form.generated_url"></el-input>
											<span class="material-icons-round" @click="bookingpress_copy_share_url">content_copy</span>
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
			<el-button class="bpa-btn bpa-btn__medium bpa-btn--icon-without-box" @click="bookingpress_copy_share_url">
				<span class="material-icons-round">share</span>
				<?php esc_html_e( 'Copy URL', 'bookingpress-appointment-booking' ); ?>
			</el-button>
			<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :class="(is_share_button_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_share_button_disabled" @click="bpa_share_appointment_url('share_url_form')">
			  <span class="bpa-btn__label"><?php esc_html_e( 'Share', 'bookingpress-appointment-booking' ); ?></span>
			  <div class="bpa-btn--loader__circles">				    
				  <div></div>
				  <div></div>
				  <div></div>
			  </div>
			</el-button>
		</div>
	</div>
</el-dialog>

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
                                                <el-select class="bpa-form-control" v-model="customer.wp_user" filterable placeholder="<?php esc_html_e( 'Start typing to fetch user.', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_get_existing_user_details($event)" reserve-keyword remote	:remote-method="get_wordpress_users"  :loading="bookingpress_loading">
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
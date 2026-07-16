<div id="root_app" class="bookingpress-appointment bookingpress_page_wrapper">
    <?php
        if( class_exists( '\BookingPressPro\admin\Dashboard') && method_exists( '\BookingPressPro\admin\Dashboard', 'render_dashboard_header' ) ){
            \BookingPressPro\admin\Dashboard::render_dashboard_header();
        } else {
            require_once __DIR__ . '/components/Header.php';
        }
    ?>

    <div class="appointment-app-root bookingpress_page_inner_wrapper" v-cloak id="appointment-app-root">
        <bp-ui-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-non-scrollable-mob" id="all-page-main-container">
            <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                <bp-ui-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
                    <h1 class="bpa-page-heading"><?php esc_html_e('Manage Appointments', 'bookingpress-appointment-booking'); ?></h1>
                </bp-ui-col>        
                <bp-ui-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                    <div class="bpa-hw-right-btn-group">                
                        <bp-ui-button class="bpa-btn bpa-btn--primary" @click="open_add_appointment_modal"> 
                            <span class="material-icons-round">add</span> 
                            <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                        </bp-ui-button>
                        <bp-ui-button id="bpa-appointment-share-url-button" class="bpa-btn" @click="bookingpress_share_url_modal">
                            <span class="material-icons-round">share</span>
                            <?php esc_html_e( 'Share URL', 'bookingpress-appointment-booking' ); ?>
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
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
                            <span class="bpa-form-label"><?php esc_html_e('Appointment Date', 'bookingpress-appointment-booking'); ?></span>
                            <bp-ui-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker" :format="bpa_date_common_date_format" v-model="appointment_date_range" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" :locale="<?php echo get_locale(); ?>" placement="bottom-start" :popper-append-to-body="false" :teleported="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker-widget-wrapper" range-separator=" - " :first-day-of-week="firstDayOfWeek" value-format="YYYY-MM-DD"> </bp-ui-date-picker>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
                            <span class="bpa-form-label"><?php esc_html_e('Customer Name', 'bookingpress-appointment-booking'); ?></span>    
                            <bp-ui-select class="bpa-form-control bpa-from-select-tab" v-model="search_customer_name" multiple filterable collapse-tags placeholder="<?php esc_html_e( 'Start typing to fetch customer', 'bookingpress-appointment-booking' ); ?>" remote reserve-keyword :remote-method="bookingpress_get_search_customer_list" :loading="bookingpress_loading" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
                                <bp-ui-option v-if="bookingpress_loading == 'true'" value="__loading__" :label="bookingpress_loading" disabled>
                                    <span>{{ bookingpress_loading }}</span>
                                </bp-ui-option>
                                <bp-ui-option v-for="item in search_customer_list" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                            </bp-ui-select>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
                            <span class="bpa-form-label"><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></span>
                            <bp-ui-select class="bpa-form-control bpa-from-select-tab" v-model="search_service_name" multiple filterable collapse-tags placeholder="<?php esc_html_e('Select service', 'bookingpress-appointment-booking'); ?>" popper-class="bpa-el-select--is-with-navbar">
                                <bp-ui-option-group v-for="service_cat_data in appointment_services_data" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
                                    <bp-ui-option v-for="service_data in service_cat_data.category_services" :key="service_data.service_id" :label="service_data.service_name" :value="service_data.service_id"></bp-ui-option>
                                </bp-ui-option-group>
                            </bp-ui-select>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
                            <span class="bpa-form-label"><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></span>        
                            <bp-ui-select class="bpa-form-control bpa-from-select-tab" v-model="search_appointment_status" placeholder="<?php esc_html_e('Select status', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
                                <bp-ui-option label="<?php esc_html_e('All', 'bookingpress-appointment-booking'); ?>" value="all"></bp-ui-option>
                                <bp-ui-option v-for="item in search_status" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                            </bp-ui-select>
                        </bp-ui-col>
                    </bp-ui-row><br>
                    <bp-ui-row type="flex" :gutter="32">
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4">
                            <bp-ui-input class="bpa-form-control" v-model="search_appointment_id" placeholder="<?php esc_html_e('Appointment ID', 'bookingpress-appointment-booking'); ?>" @input="isOnlyNumber($event)" >    
                            </bp-ui-input>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                            <bp-ui-input class="bpa-form-control" v-model="search_appointment" placeholder="<?php esc_html_e('Search for customers, services...', 'bookingpress-appointment-booking'); ?>" >    
                            </bp-ui-input>
                        </bp-ui-col>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
                            <div class="bpa-tf-btn-group">
                                <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
                                    <?php esc_html_e('Reset', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                                <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadAppointments(true)">
                                    <?php esc_html_e('Apply', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                            </div>
                        </bp-ui-col>
                    </bp-ui-row><br>
                </div>
                <div id="bpa-loader-div">
                    <bp-ui-row type="flex" v-show="items.length == 0">
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
                                    
                                    <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_appointment_modal()">                         
                                        <span class="material-icons-round">add</span> 
                                        <?php esc_html_e('Add New', 'bookingpress-appointment-booking'); ?>
                                    </bp-ui-button>
                                </div>
                            </div>
                        </bp-ui-col>
                    </bp-ui-row>
                </div>
                <bp-ui-row v-if="items.length > 0">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <bp-ui-container class="bpa-table-container">
                            <div class="bpa-back-loader-container bpa-back-loader-inner-container" v-if="is_display_loader == '1'">
                                <div class="bpa-back-loader"></div>
                            </div>
                            <div class="bpa-tc__wrapper" v-if="current_screen_size == 'desktop'">
                                <bp-ui-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" row-key="appointment_id" @sort-change="handel_appointment_changes" @selection-change="handleSelectionChange" fit="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
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
                                                        width="auto"
                                                        popper-class="bpa-cancel-appointment-popconfirm --no-icon"
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
                                    <bp-ui-table-column type="selection"></bp-ui-table-column>
                                    <bp-ui-table-column prop="booking_id" min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
                                        <template #default="scope">
                                            <span>#{{ scope.row.booking_id }}</span>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="appointment_date" min-width="70" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by="sort_appointment_date_time">
                                        <template #default="scope">
                                            <label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="customer_name" min-width="120" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by='customer_name'></bp-ui-table-column>
                                    <bp-ui-table-column prop="service_name" min-width="120" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by='service_name'></bp-ui-table-column>
                                    <bp-ui-table-column prop="appointment_duration" min-width="60" label="<?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by='bookingpress_service_duration_sortable'></bp-ui-table-column>
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
                                                <bp-ui-select class="bpa-form-control bpa-appointment-status-dropdown-wrapper" :class="((scope.row.appointment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == '3' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == '1' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == '4' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event, scope.row)" popper-class="bpa-appointment-status-dropdown-popper" :teleported="true">
                                                    <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                        <bp-ui-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                    </bp-ui-option-group>
                                                </bp-ui-select>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="appointment_payment" min-width="60" label="<?php esc_html_e( 'Payment', 'bookingpress-appointment-booking' ); ?>" sort-by="payment_numberic_amount">
                                        <template #default="scope">
                                            <div class="bpa-apc__amount-row">
                                                <div class="bpa-apc__ar-body">
                                                    <span class="bpa-apc__amount">{{ scope.row.appointment_payment }}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="created_date" label="<?php esc_html_e( 'Created Date', 'bookingpress-appointment-booking' ); ?>" sortable="false" sort-by="bookingpress_appointment_created_date">
                                        <template #default="scope">
                                            <label>{{ scope.row.created_date }}</label>
                                                <div class="bpa-table-actions-wrap">
                                                    <div class="bpa-table-actions">
                                                        
                                                        <bp-ui-tooltip tabindex="-1" effect="dark" content="" placement="top" open-delay="300">
                                                            <template #content>
                                                                <span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
                                                            </template>
                                                            <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
                                                                <span class="material-icons-round">mode_edit</span>
                                                            </bp-ui-button>
                                                        </bp-ui-tooltip>
                                                            
                                                        <bp-ui-tooltip tabindex="-1" effect="dark" content="" placement="top" open-delay="300">
                                                            <template #content>
                                                                <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                            </template>
                                                            <bp-ui-popconfirm 
                                                                cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                                confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                                                icon="false" 
                                                                :placement="popConfirmPlacement"
                                                                title="<?php esc_html_e( 'Are you sure you want to delete this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                                @confirm="deleteAppointment(scope.$index, scope.row)" 
                                                                confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                                cancel-button-type="bpa-btn bpa-btn__small">
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
                                <bp-ui-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" @sort-change="handel_appointment_changes" @selection-change="handleSelectionChange" fit="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
                                    <bp-ui-table-column type="expand">
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
                                                        @confirm="bookingpress_change_status(scope.row.appointment_id, '3')" 
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
                                    <bp-ui-table-column type="selection"></bp-ui-table-column>
                                    <bp-ui-table-column prop="booking_id" min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
                                        <template #default="scope">
                                            <span>#{{ scope.row.booking_id }}</span>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="appointment_date" min-width="100" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="view_appointment_date">
                                        <template #default="scope">
                                            <label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
                                            <label class="bpa-item__date-col bpa-item__dt-col-duration-md">
                                                <span class="material-icons-round">schedule</span>
                                                {{ scope.row.appointment_duration }}
                                            </label>
                                        </template>
                                    </bp-ui-table-column>
                                    <bp-ui-table-column prop="service_name" min-width="100" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable sort-by='service_name'></bp-ui-table-column>
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
                                                <bp-ui-select class="bpa-form-control" :class="((scope.row.appointment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == '3' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == '1' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == '4' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event)" popper-class="bpa-appointment-status-dropdown-popper">
                                                    <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                        <bp-ui-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                    </bp-ui-option-group>
                                                </bp-ui-select>
                                            </div>
                                            <div class="bpa-table-actions-wrap">
                                                <div class="bpa-table-actions">
                                                    <bp-ui-tooltip tabindex="-1" effect="dark" content="" placement="top" open-delay="300">
                                                        <div slot="content">
                                                            <span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
                                                        </div>
                                                        <bp-ui-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
                                                            <span class="material-icons-round">mode_edit</span>
                                                        </bp-ui-button>
                                                    </bp-ui-tooltip>
                                                    <bp-ui-tooltip tabindex="-1" effect="dark" content="" placement="top" open-delay="300">
                                                        <div slot="content">
                                                            <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                        </div>
                                                        <bp-ui-popconfirm 
                                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                                            icon="false" 
                                                            title="<?php esc_html_e( 'Are you sure you want to delete this appointment?', 'bookingpress-appointment-booking' ); ?>" 
                                                            @confirm="deleteAppointment(scope.$index, scope.row)" 
                                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                            cancel-button-type="bpa-btn bpa-btn__small">
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
                            <div class="bpa-tc__wrapper bpa-manage-appointment-container--sm" v-if="current_screen_size == 'mobile'">
                                <bp-ui-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" @selection-change="handleSelectionChange" fit="false" :show-header="false" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
                                    <bp-ui-table-column type="expand">
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
                                                        @confirm="bookingpress_change_status(scope.row.appointment_id, '3')" 
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
                                    <bp-ui-table-column type="selection"></bp-ui-table-column>
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
                                                        <bp-ui-select class="bpa-form-control" :class="((scope.row.appointment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == '3' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == '1' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == '4' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event)" popper-class="bpa-appointment-status-dropdown-popper">
                                                            <bp-ui-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
                                                                <bp-ui-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></bp-ui-option>
                                                            </bp-ui-option-group>
                                                        </bp-ui-select>
                                                    </div>
                                                    <div class="bpa-mpay-fi__actions bpa-mac-fi__actions">
                                                        <bp-ui-button class="bpa-btn bpa-btn__filled-light" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
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
                                                            <bp-ui-button type="text" slot="reference" class="bpa-btn bpa-btn__filled-light __danger">
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
                        <bp-ui-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></bp-ui-pagination>
                    </bp-ui-col>
                    <bp-ui-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card" >
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
                                <bp-ui-button @click="bulk_actions()" class="bpa-btn bpa-btn--primary bpa-btn__medium">
                                    <?php esc_html_e('Go', 'bookingpress-appointment-booking'); ?>
                                </bp-ui-button>
                            </bp-ui-col>
                        </bp-ui-row>
                    </bp-ui-container>
                </bp-ui-row>
            </div>
        </bp-ui-main>

        <!-- Share Appointment URL Dialog -->
        <bp-ui-dialog class="bpa-dialog bpa-dailog__small bpa-dialog--share-url" id="appointment_share_url" title="" v-model="bpa_share_url_modal" :modal="false" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal" :teleport="true" :close-on-click-modal="true">
            <div class="bpa-dialog-heading">
                <bp-ui-row type="flex">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <h1 class="bpa-page-heading"><?php esc_html_e( 'Share Appointment', 'bookingpress-appointment-booking' ); ?></h1>
                    </bp-ui-col>
                </bp-ui-row>
            </div>
            <div class="bpa-dialog-body">
                <bp-ui-container class="bpa-grid-list-container">
                    <div class="bpa-form-row">				
                        <bp-ui-row>
                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <bp-ui-form label-position="top" @submit.native.prevent :rules="share_url_rules" :model="share_url_form" ref="share_url_form">
                                    <div class="bpa-form-body-row">
                                        <bp-ui-row>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-form-item prop="selected_page_wp_id">
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php echo esc_html__('Select Page', 'bookingpress-appointment-booking'); ?></span>
                                                    </template>
                                                    <bp-ui-select class="bpa-form-control" v-model="share_url_form.selected_page_wp_id" filterable collapse-tags placeholder="<?php esc_html_e( 'Search for Page', 'bookingpress-appointment-booking' ); ?>" remote reserve-keyword	 :remote-method="bookingpress_get_page_list"  @change="bookingpress_generate_share_url" :loading="bookingpress_loading" popper-class="bpa-el-select--is-with-modal">  
                                                        <bp-ui-option :label="pages_list.title" :key="pages_list.id"  :value="pages_list.id" v-for="pages_list in all_share_pages_list"></bp-ui-option>
                                                    </bp-ui-select>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                    <div class="bpa-form-body-row">
                                        <bp-ui-row>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-form-item prop="selected_service_id">
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php echo esc_html__('Select Service', 'bookingpress-appointment-booking'); ?></span>
                                                    </template>
                                                    <bp-ui-select v-model="share_url_form.selected_service_id"  class="bpa-form-control" filterable placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-el-select--is-with-modal" @change="bpa_enable_service_share">
                                                        <bp-ui-option-group v-for="service_cat_data in appointment_services_data" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
                                                            <template v-for="service_data in service_cat_data.category_services">
                                                                <bp-ui-option v-if="service_data.service_id == 0"  :key="service_data.service_id" :label="service_data.service_name" :value="''" ></bp-ui-option>
                                                                <bp-ui-option v-else :key="service_data.service_id" :label="service_data.service_name+' ('+service_data.service_price+' )'" :value="service_data.service_id"></bp-ui-option>
                                                            </template>
                                                        </bp-ui-option-group>
                                                    </bp-ui-select>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                    <div class="bpa-form-body-row bpa-dsu__checkbox-row">
                                        <bp-ui-row>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-form-item>
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php echo esc_html__('Share With', 'bookingpress-appointment-booking'); ?></span>
                                                    </template>
                                                    <label class="bpa-form-label bpa-custom-checkbox--is-label"> <bp-ui-checkbox v-model="share_url_form.email_sharing" label="<?php esc_html_e( 'Email', 'bookingpress-appointment-booking' ); ?>" @change="bpa_enable_service_share"></bp-ui-checkbox></label>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                    <div class="bpa-form-body-row" v-if="share_url_form.email_sharing == true">
                                        <bp-ui-row>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-form-item prop="sharing_email">
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php echo esc_html__('Email', 'bookingpress-appointment-booking'); ?></span>
                                                    </template>
                                                    <bp-ui-input class="bpa-form-control" v-model="share_url_form.sharing_email" placeholder="<?php esc_html_e('Enter email address', 'bookingpress-appointment-booking'); ?>" @blur="bpa_enable_service_share"></bp-ui-input>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                    <div class="bpa-form-body-row">
                                        <bp-ui-row>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-form-item>
                                                    <label class="bpa-form-label bpa-custom-checkbox--is-label"> <bp-ui-checkbox v-model="share_url_form.allow_customer_to_modify" @change="bookingpress_generate_share_url" label="<?php esc_html_e( 'Customer can modify option', 'bookingpress-appointment-booking' ); ?>"></bp-ui-checkbox></label>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                    <div class="bpa-form-body-row bpa-dsu__url-val-row">
                                        <bp-ui-row>
                                            <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <bp-ui-form-item>
                                                    <template #label>
                                                        <span class="bpa-form-label"><?php echo esc_html__('URL', 'bookingpress-appointment-booking'); ?></span>
                                                    </template>
                                                    <bp-ui-input class="bpa-form-control" v-model="share_url_form.generated_url"></bp-ui-input>
                                                    <span class="material-icons-round" @click="bookingpress_copy_share_url">content_copy</span>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </bp-ui-row>
                                    </div>
                                </bp-ui-form>
                            </bp-ui-col>
                        </bp-ui-row>
                    </div>
                </bp-ui-container>
            </div>
            <div class="bpa-dialog-footer">
                <div class="bpa-hw-right-btn-group">
                    <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--icon-without-box" @click="bookingpress_copy_share_url">
                        <span class="material-icons-round">share</span>
                        <?php esc_html_e( 'Copy URL', 'bookingpress-appointment-booking' ); ?>
                    </bp-ui-button>
                    <bp-ui-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :class="(is_share_button_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_share_button_disabled" @click="bpa_share_appointment_url('share_url_form')">
                    <span class="bpa-btn__label"><?php esc_html_e( 'Share', 'bookingpress-appointment-booking' ); ?></span>
                    <div class="bpa-btn--loader__circles">				    
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    </bp-ui-button>
                </div>
            </div>
        </bp-ui-dialog>
        <!-- Share Appointment URL Dialog -->
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
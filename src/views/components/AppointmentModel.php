<div v-cloak id="bookingpress-appointment-dialog" class="bookingpress-appointment-dialog-container">
    <bp-ui-dialog v-model="openAddNewAppointmentModel" fullscreen=true :close-on-press-escape="closeModelOnEscape" class="bpa-dialog bpa-dialog--fullscreen bpa-dialog--customer-modal bpa--is-page-non-scrollable-mob" :append-to-body="false" :class="openAddNewAppointmentModel ? '--bpa-active' : ''" :show-close="false">
        <div class="bpa-dialog-heading">
            <bp-ui-row class="row-bg" justify="space-between" type="flex">
                <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                    <h1 class="bpa-page-heading" v-if="appointment_formdata.appointment_update_id == '0'"><?php esc_html_e('Add Appointment', 'bookingpress-appointment-booking'); ?></h1>
                    <h1 class="bpa-page-heading" v-else><?php esc_html_e('Edit Appointment', 'bookingpress-appointment-booking'); ?></h1>
                </bp-ui-col>
                <bp-ui-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
                    <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveAppointmentBooking('appointment_formdata')"  :disabled="is_disabled">                    
                    <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                    <div class="bpa-btn--loader__circles">                    
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    </bp-ui-button>
                    <bp-ui-button class="bpa-btn bpa-btn-secondary" @click="closeAppointmentBookingModal"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                </bp-ui-col>
            </bp-ui-row>
        </div>
        <div class="bpa-dialog-body">
            <div class="bpa-form-row">
                <bp-ui-row type="flex">
                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <div class="bpa-default-card bpa-db-card">
                            <bp-ui-form ref="appointment_formdata" require-asterisk-position="right" :rules="rules" :model="appointment_formdata" label-position="top">
                                <div class="bpa-form-body-row">
                                    <bp-ui-row :gutter="24" type="flex">
                                        <bp-ui-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="appointment_selected_customer">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Select Customer', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-select class="bpa-form-control" name="appointment_selected_customer" v-model="appointment_formdata.appointment_selected_customer" filterable placeholder="<?php esc_html_e( 'Start typing to fetch customer', 'bookingpress-appointment-booking' ); ?>" remote reserve-keyword :remote-method="bookingpress_get_customer_list" @change="bpa_select_customer($event)" popper-class="bpa-el-select--is-with-modal" v-cancel-read-only>
                                                    <bp-ui-option v-if="bookingpress_edit_customers == 1" value="add_new" label="Add New">
                                                        <i class="el-icon-plus"></i> <span><?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?></span>
                                                    </bp-ui-option>
                                                    <bp-ui-option v-if="loading_from_server" value="__loading__" :label="bookingpress_loading" disabled>
                                                        <span>{{ bookingpress_loading }}</span>
                                                    </bp-ui-option>
                                                    <bp-ui-option v-for="item in appointment_customers_list" :key="item.value" :label="item.text" :value="item.value">
                                                        <span>{{ item.text }}</span>
                                                    </bp-ui-option>

                                                    <template v-slot:empty> <span v-if="bookingpress_edit_customers == 1">
                                                        <?php esc_html_e( 'Type to search or choose Add New', 'bookingpress-appointment-booking' ); ?> </span>
                                                        <span v-else><?php esc_html_e( 'Start typing to fetch customer', 'bookingpress-appointment-booking' ); ?> </span>
                                                    </template>
                                                </bp-ui-select>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                                            
                                            <bp-ui-form-item prop="appointment_selected_service">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Select Service', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-select class="bpa-form-control" v-model="appointment_formdata.appointment_selected_service" name="appointment_selected_service" filterable  placeholder="<?php esc_html_e('Select service', 'bookingpress-appointment-booking'); ?>" popper-class="bpa-el-select--is-with-modal" @Change="bookingpress_appointment_change_service()">
                                                    <bp-ui-option-group v-for="service_cat_data in appointment_services_list" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
                                                        <template v-for="service_data in service_cat_data.category_services">
                                                            <bp-ui-option v-if="service_data.service_id == 0" :key="service_data.service_id" :label="service_data.service_name" :value="''"></bp-ui-option>
                                                            <bp-ui-option v-else :key="service_data.service_id" :label="service_data.service_name+' ('+service_data.service_price+' )' + ' - ' +service_data.service_duration_formatted" :value="service_data.service_id"></bp-ui-option>
                                                        </template>
                                                    </bp-ui-option-group>
                                                </bp-ui-select>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                                            <bp-ui-form-item prop="appointment_booked_date">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Appointment Date', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-date-picker class="bpa-form-control bpa-form-control--date-picker" type="date" :format="bookingpress_date_common_date_format" v-model="appointment_formdata.appointment_booked_date" name="appointment_booked_date" :clearable="false" :first-day-of-week="firstDayOfWeek" :disabled-date="bookingpressDisabledDate" popper-class="bpa-el-select--is-with-modal bpa-el-datepicker-widget-wrapper" @change="select_appointment_booking_date($event)" value-format="YYYY-MM-DD"></bp-ui-date-picker>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="8" :lg="12" :xl="12">
                                            <bp-ui-form-item prop="appointment_booked_time">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Appointment Time', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?>" v-model="appointment_formdata.appointment_booked_time" @Change="bookingpress_set_time($event,appointment_time_slot)" :no-data-text="no_timeslots_available_text" filterable popper-class="bpa-el-select--is-with-modal">
                                                    <bp-ui-option-group v-for="appointment_time_slot_data in appointment_time_slot" :key="appointment_time_slot_data.timeslot_label" :label="appointment_time_slot_data.timeslot_label">
                                                        <bp-ui-option v-for="appointment_time in appointment_time_slot_data.timeslots" :label="(appointment_time.formatted_start_time)+' to '+(appointment_time.formatted_end_time)" :value="appointment_time.store_start_time" :disabled="( appointment_time.is_disabled || appointment_time.max_capacity <= appointment_time.total_booked || appointment_time.max_capacity == 0 || appointment_time.is_booked == 1 )">
                                                            <span>{{ appointment_time.formatted_start_time  }} to {{appointment_time.formatted_end_time}}</span>
                                                        </bp-ui-option>	
                                                    </bp-ui-option-group>
                                                </bp-ui-select>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="8" :lg="12" :xl="12">
                                            <bp-ui-form-item>
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Select Status', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-select class="bpa-form-control" v-model="appointment_formdata.appointment_status" popper-class="bpa-el-select--is-with-model" :options="BookingPressAppointmentStatus"></bp-ui-select>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                    </bpa-ui-row>
                                </div>
                                <div class="bpa-form-body-row">
                                    <bp-ui-row :gutter="24" type="flex">
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                            <bp-ui-form-item>
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Internal note', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-input class="bpa-form-control" type="textarea" :rows="5" v-model="appointment_formdata.appointment_internal_note"></bp-ui-input>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                    </bpa-ui-row>
                                </div>
                                <div class="bpa-form-body-row">
                                    <bp-ui-row :gutter="24" type="flex">
                                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                            <bp-ui-form-item>
                                                <label class="bpa-form-label bpa-custom-checkbox--is-label"> <bp-ui-checkbox label="" v-model="appointment_formdata.appointment_send_notification"></bp-ui-checkbox><?php esc_html_e('Do Not Send Notification', 'bookingpress-appointment-booking'); ?></label>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                    </bpa-ui-row>
                                </div>
                            </bp-ui-form>
                        </div>
                    </bp-ui-col>
                </bp-ui-row>
            </div>
        </div>
    </bp-ui-dialog>
</div>
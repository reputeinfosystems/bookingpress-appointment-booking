<div v-cloak id="bookingpress-reschedule-dialog" class="bookingpress-reschedule-dialog-container">
    <bp-ui-dialog v-model="openRescheduleModal" :fullscreen="false" :close-on-press-escape="closeRescheduleModalOnEscape" class="bpa-dialog bpa-dialog--reschedule-modal bpa--is-page-non-scrollable-mob" title="<?php esc_html_e('Reschedule Appointment', 'bookingpress-appointment-booking'); ?>" :append-to-body="false" :class="openRescheduleModal ? '--bpa-active' : ''" :show-close="true">
        <div class="bpa-dialog-body">
            <div class="bpa-reschedule-form">
                <bp-ui-form ref="reschedule_formdata" require-asterisk-position="right" :rules="rules" :model="reschedule_formdata" label-position="top" @submit.native.prevent>

                    <!-- Customer Name | Service Name -->
                    <div class="bpa-reschedule-customer-row">
                        <span class="bpa-reschedule-customer-name">{{reschedule_formdata.booking_customer}}</span>
                        <span class="bpa-reschedule-divider"></span>
                        <span class="bpa-reschedule-service-name">{{reschedule_formdata.booking_service}}</span>
                    </div>

                    <!-- Timeline: From / To -->
                    <div class="bpa-reschedule-timeline">

                        <!-- FROM section -->
                        <div class="bpa-reschedule-timeline-item bpa-reschedule-timeline--from">
                            <div class="bpa-reschedule-timeline-dot bpa-reschedule-dot--gray"></div>
                            <div class="bpa-reschedule-timeline-content">
                                <span class="bpa-reschedule-section-label bpa-reschedule-label--from"><?php esc_html_e('From', 'bookingpress-appointment-booking'); ?></span>
                                <div class="bpa-reschedule-from-info">
                                    <div class="bpa-reschedule-from-field">
                                        <span class="bpa-reschedule-field-icon">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.667 2.667H3.333C2.597 2.667 2 3.264 2 4v9.333c0 .737.597 1.334 1.333 1.334h9.334c.736 0 1.333-.597 1.333-1.334V4c0-.736-.597-1.333-1.333-1.333z" stroke="#535d71" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.667 1.333v2.667M5.333 1.333v2.667M2 6.667h12" stroke="#535d71" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        <span class="bpa-reschedule-from-text">{{reschedule_formdata.formatted_booking_date}}</span>
                                    </div>
                                    <span class="bpa-reschedule-from-separator"></span>
                                    <div class="bpa-reschedule-from-field">
                                        <span class="bpa-reschedule-field-icon">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="6.667" stroke="#535d71" stroke-width="1.2"/><path d="M8 4v4l2.667 1.333" stroke="#535d71" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        <span class="bpa-reschedule-from-text">{{reschedule_formdata.formatted_booking_time}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TO section -->
                        <div class="bpa-reschedule-timeline-item bpa-reschedule-timeline--to">
                            <div class="bpa-reschedule-timeline-dot bpa-reschedule-dot--green"></div>
                            <div class="bpa-reschedule-timeline-content">
                                <span class="bpa-reschedule-section-label bpa-reschedule-label--to"><?php esc_html_e('To', 'bookingpress-appointment-booking'); ?></span>
                                <div class="bpa-reschedule-to-fields">
                                    <div class="bpa-reschedule-to-field bpa-reschedule-to-field--date">
                                        <bp-ui-form-item prop="reschedule_date">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Appointment Date', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <bp-ui-date-picker class="bpa-form-control bpa-form-control--date-picker" type="date" format="DD MMMM, YYYY" v-model="reschedule_formdata.reschedule_date" :clearable="false" popper-class="bpa-el-select--is-with-modal bpa-el-datepicker-widget-wrapper" :first-day-of-week="firstDayOfWeek" @change="select_appointment_booking_date($event)" :disabled-date="bookingpressDisabledDate" locale="<?php echo get_locale(); ?>" value-format="YYYY-MM-DD"></bp-ui-date-picker>
                                        </bp-ui-form-item>
                                    </div>
                                    <div class="bpa-reschedule-to-field bpa-reschedule-to-field--time">
                                        <bp-ui-form-item prop="reschedule_time">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Appointment Time', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <bp-ui-select class="bpa-form-control" v-model="reschedule_formdata.reschedule_time" placeholder="<?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?>" popper-class="bpa-el-select--is-with-modal" filterable @change="bookingpress_set_reschedule_time($event,reschedule_time_options)">
                                                <bp-ui-option-group v-for="reschedule_time_option in reschedule_time_options" :key="reschedule_time_option.timeslot_label" :label="reschedule_time_option.timeslot_label">
                                                    <bp-ui-option v-for="reschedule_time in reschedule_time_option.timeslots" :label="(reschedule_time.formatted_start_time)+' to '+(reschedule_time.formatted_end_time)" :value="reschedule_time.store_start_time" :disabled="( reschedule_time.is_disabled || reschedule_time.max_capacity <= reschedule_time.total_booked || reschedule_time.max_capacity == 0 || reschedule_time.is_booked == 1 )">
                                                        <span>{{ reschedule_time.formatted_start_time  }} to {{reschedule_time.formatted_end_time}}</span>
                                                    </bp-ui-option>	
                                                </bp-ui-option-group>
                                            </bp-ui-select>
                                        </bp-ui-form-item>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Buttons -->
                    <div class="bpa-reschedule-footer">
                        <bp-ui-button class="bpa-btn bpa-btn-secondary" @click="closeRescheduleModalPopup"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                        <bp-ui-button class="bpa-btn bpa-btn--primary" :class="(is_display_reschedule_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="submitReschedule" :disabled="is_disabled">
                            <span class="bpa-btn__label"><?php esc_html_e('Reschedule', 'bookingpress-appointment-booking'); ?></span>
                            <div class="bpa-btn--loader__circles">                    
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </bp-ui-button>
                    </div>

                </bp-ui-form>
            </div>
        </div>
    </bp-ui-dialog>
</div>
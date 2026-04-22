"use strict";

import { createApp} from 'vue';

const BookingPressConfig = window.BookingPressConfig;
const rest_url = BookingPressConfig.rest_url;
let bookingpress_calendar;

let is_rescheduling = false;
let rescheduling_data = {};

function getModuleData(moduleId) {
    const el = document.getElementById(`wp-script-module-data-${moduleId}`);

    if (!el) {
        return {};
    }

    try {
        return JSON.parse(el.textContent || '{}');
    } catch (error) {
        console.error('Failed to parse module data:', error);
        return {};
    }
}


document.onreadystatechange = () => {
    document.body.classList.add('folded');
    if ('complete' === document.readyState) {

        const moduleData = getModuleData('bookingpress-calendar-loader');

        let BookingCalendarConfig = moduleData.calendar_config;

        bookingpress_calendar = new BpaCalendar(BookingCalendarConfig);

        bookingpress_calendar.mount('#bookingpress-calendar');
        window.BookingPressCalendarApp = bookingpress_calendar;
    }
}

function getEvents( eventObject ) {

    let PostData = {};

    if( eventObject?.start_date && eventObject?.end_date ) {
        PostData.start_date = eventObject.start_date;
        PostData.end_date = eventObject.end_date;
    }

    fetch(rest_url + '/calendar', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': BookingPressConfig.rest_nonce,
            'X-Calendar-Nonce': BookingPressConfig.nonce
        },
        body: JSON.stringify(PostData)
    })
        .then(response => response.json())
        .then(rest_response => {
            document.querySelector('.calendar-page-loader').style.display = 'none';

            if( eventObject?.append ) {
                bookingpress_calendar.appendData(rest_response.data);
                return;
            }

            bookingpress_calendar.loadData(rest_response.data);
        }).catch(error => {
            document.querySelector('.calendar-page-loader').style.display = 'none';
            console.error('Error fetching calendar data:', error);
        });
}

window.addEventListener('bookingpress:appointment-popover-status-change', (event) => {
    const { value, previousValue, booking } = event.detail;

    const moduleData = getModuleData('bookingpress-calendar-loader');

    const postData = {
        appointment_id: booking.id,
        new_status: value
    };

    fetch( rest_url + '/appointment/update-status', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': BookingPressConfig.rest_nonce,
            'X-Calendar-Nonce': BookingPressConfig.nonce
        },
        body: JSON.stringify(postData)
    })
        .then(response => response.json())
        .then(rest_response => {
            if( rest_response.success ) {
                let newStatus = postData.new_status;
                let booking_id = postData.appointment_id;

                window.BookingPressCalendarApp.updateBooking(booking_id, {
                    status: newStatus
                });
                
            } else {
                
            }
        });
});

document.addEventListener('DOMContentLoaded', () => {
    initRescheduleDialog();
});

const initRescheduleDialog = () => {
    const moduleData = getModuleData('bookingpress-calendar-loader');
    const appointmentModuleData = getModuleData('bookingpress-appointment-model');

    let BookingPressRescheduleComputedData = wp.hooks.applyFilters( 'bookingpress_appointment_reschedule_computed_data', {} );

    let BookingPressRescheduleMethodData = wp.hooks.applyFilters( 'bookingpress_appointment_reschedule_external_methods', {
        openRescheduleModalPopup() {
            this.openRescheduleModal = true;
        },
        bookingpressDisabledDate(time) {
            const dd = String(time.getDate()).padStart(2, '0');
            const mm = String(time.getMonth() + 1).padStart(2, '0');
            const yyyy = time.getFullYear();
            const selectedDate = `${yyyy}-${mm}-${dd}`;

            let normalizedDisabledDates = [];

            if (Array.isArray(this.disabledDates)) {
                normalizedDisabledDates = this.disabledDates;
            } else if (this.disabledDates && typeof this.disabledDates === 'object') {
                normalizedDisabledDates = Object.values(this.disabledDates);
            }

            const disableDate = normalizedDisabledDates.includes(selectedDate);

            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);

            const disablePastDate = time.getTime() < yesterday.getTime();

            return disableDate || disablePastDate;
        },
        closeRescheduleModalPopup( is_revert = false ) {

            this.$refs.reschedule_formdata.resetFields();
            this.openRescheduleModal = false;
        },
        normalizeDisabledDates(rawDates) {
            if (Array.isArray(rawDates)) {
                return rawDates;
            }

            if (rawDates && typeof rawDates === 'object') {
                return Object.values(rawDates);
            }

            return [];
        },
        appendDisabledDates(newDates) {
            const normalizedNewDates = this.normalizeDisabledDates(newDates);

            this.disabledDates = [
                ...new Set([
                    ...this.disabledDates,
                    ...normalizedNewDates
                ])
            ];
        },
        replaceDisabledDates(newDates) {
            const normalizedNewDates = this.normalizeDisabledDates(newDates);

            this.disabledDates = [...new Set(normalizedNewDates)];
        },
        select_appointment_booking_date(selected_value, skip_time_update = false) {
            const vm = this;
            vm.reschedule_formdata.reschedule_date = vm.reschedule_formdata.selected_date = selected_value;
            
            let postData = {
                'service_id': vm.reschedule_formdata.booking_service_id,
                'selected_date': selected_value,
                'appointment_data_obj': {
                    appointment_booked_date: selected_value,
                    appointment_selected_service: vm.reschedule_formdata.booking_service_id,
                    appointment_update_id: vm.reschedule_formdata.booking_id,
                }
            };

            if( !skip_time_update ) {
                this.reschedule_formdata.reschedule_time = '';
                this.reschedule_formdata.reschedule_end_time = '';
            }

            postData = wp.hooks.applyFilters('bookingpress_get_front_timing_set_additional_appointment_reschedule_xhr_data', postData, vm);

            fetch(rest_url + '/time', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body: JSON.stringify(postData)
            })
            .then(response => response.json())
            .then(rest_response => {
                if (rest_response.success) {
                    let timeslot_response_data = rest_response.data;

                    let morning_times = timeslot_response_data.morning_time;
                    let afternoon_times = timeslot_response_data.afternoon_time;
                    let evening_times = timeslot_response_data.evening_time;
                    let night_times = timeslot_response_data.night_time;
                    
                    let timeslot_data = {
                        morning_time: {
                            timeslot_label: "Morning",
                            timeslots: morning_times
                        },
                        afternoon_time: {
                            timeslot_label: "Afternoon",
                            timeslots: afternoon_times
                        },
                        evening_time: {
                            timeslot_label: "Evening",
                            timeslots: evening_times
                        },
                        night_time: {
                            timeslot_label: "Night",
                            timeslots: night_times
                        }
                    };

                    this.reschedule_time_options = timeslot_data;
                } else {
                    this.$notify({
                        title: 'Error',
                        message: rest_response.message,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }
            })
            .catch(error => {
                this.$notify({
                    title: 'Error',
                    message: 'Something went wrong while fetching time slot',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
            });
        },
        bookingpress_set_reschedule_time(event, time_slot_data) {
            const vm = this
            if(event != '' && time_slot_data != '') {
                for (let x in time_slot_data) {                      
                    var slot_data_arr = time_slot_data[x];
                    for(let y in slot_data_arr) {
                        var time_slot_data_arr = slot_data_arr[y];
                        for(let m in time_slot_data_arr) {                            
                            var data_arr  = time_slot_data_arr[m];
                            if(data_arr.store_start_time != undefined && data_arr.store_end_time != undefined && data_arr.store_start_time == event) {   
                                vm.reschedule_formdata.reschedule_end_time = data_arr.store_end_time;
                                wp.hooks.doAction('bookingpress_admin_reschedule_appointment_after_select_timeslot', data_arr, vm);
                            }
                        }                                                    
                    }                      
                }
            }
        },
        submitReschedule() {
            this.$refs.reschedule_formdata.validate((valid) => {
                if (valid) {
                    this.submitRescheduleForm();
                } else {
                    return false;
                }
            });
        },
        submitRescheduleForm() {
            const vm = this;
            let reschedule_data = {
                appointment_update_id: vm.reschedule_formdata.booking_id,
                appointment_booked_date: vm.reschedule_formdata.reschedule_date,
                appointment_booked_end_date: vm.reschedule_formdata.reschedule_end_date,
                appointment_booked_time: vm.reschedule_formdata.reschedule_time,
                appointment_booked_end_time: vm.reschedule_formdata.reschedule_end_time,
                appointment_selected_customer: vm.reschedule_formdata.booking_customer_id,
                appointment_selected_service: vm.reschedule_formdata.booking_service_id,
                appointment_custom_timing: vm.reschedule_formdata.appointment_custom_timing ?? false
            };

            reschedule_data = wp.hooks.applyFilters( 'bookingpress_modify_reschedule_data', reschedule_data, vm);
            console.log( reschedule_data );

            vm.is_display_reschedule_loader = true;
            vm.is_disabled = true;

            fetch(rest_url + '/appointment/reschedule', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body: JSON.stringify(reschedule_data)
            })
            .then(response => response.json())
            .then(rest_response => {
                if( rest_response.success ) {
                    vm.$notify({
                        title: 'Success',
                        message: rest_response.message,
                        type: 'success',
                        customClass: 'success_notification',
                        duration: 5000,
                    });
                    vm.is_display_reschedule_loader = false;
                    vm.is_disabled = false;
                    let new_appointment_details = rest_response.appointment_details;
                    
                    window.BookingPressCalendarApp.updateBooking(vm.reschedule_formdata.booking_id,new_appointment_details);
                    vm.closeRescheduleModalPopup();
                } else {
                    
                    vm.$notify({
                        title: 'Error',
                        message: rest_response.message,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: 5000,
                    });
                    vm.is_display_reschedule_loader = false;
                    vm.is_disabled = false;
                    vm.closeRescheduleModalPopup();
                }
            })
            .catch(error => {
                console.log( error );
                vm.$notify({
                    title: 'Error',
                    message: 'Something went wrong while rescheduling appointment',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: 5000,
                });
                vm.is_display_reschedule_loader = false;
                vm.is_disabled = false;
                vm.closeRescheduleModalPopup();
            });
        }
    } );

    const BookingPressRescheduleDialogConfig = {
        data() {
            let ModelConfigData = {
                openRescheduleModal: false,
                closeRescheduleModalOnEscape: true,
                reschedule_formdata:{
                    booking_id:'',
                    booking_date:'',
                    booking_service: '',
                    booking_customer: '',
                    formatted_booking_date: '',
                    formatted_booking_time: '',
                    reschedule_date:'',
                    reschedule_time:'',
                    reschedule_end_time: ''
                },
                appointment_formdata:{},
                disabledDates: appointmentModuleData.disabledDates,
                reschedule_time_options: [],
                firstDayOfWeek: appointmentModuleData.firstDayOfWeek,
                is_display_reschedule_loader: false,
                is_disabled: false,
                is_display_time: true,
                rules: {
                    reschedule_date: [
                        { required: true, message: 'Please select booking date', trigger: 'change' }
                    ],
                    reschedule_time: [
                        { required: true, message: 'Please select booking time', trigger: 'change' }
                    ]
                }
            }

            ModelConfigData = wp.hooks.applyFilters( 'bookingpress_modify_reschedule_config_data', ModelConfigData);

            return ModelConfigData;
        },
        methods: {
            ...BookingPressRescheduleMethodData
        },
        computed:{
            ...BookingPressRescheduleComputedData
        }
    }
    const BookingPressRescheduleDialog = createApp(BookingPressRescheduleDialogConfig);
    BookingPressRescheduleDialog.use(BookingPressUI);
    window.BookingPressRescheduleDialog = BookingPressRescheduleDialog.mount('#bookingpress-reschedule-dialog');
}

/** Open Reschedule Appointment Form */
window.addEventListener( 'bookingpress:appointment-popover-reschedule', (event) => {
    const { booking } = event.detail;
    
    if (window.BookingPressRescheduleDialog) {
        window.dispatchEvent(new CustomEvent('bookingpress:appointment-popover-close'));

        window.BookingPressRescheduleDialog.reschedule_formdata.booking_id = booking.id;
        window.BookingPressRescheduleDialog.reschedule_formdata.booking_date = booking.start_date;
        window.BookingPressRescheduleDialog.reschedule_formdata.booking_time = booking.start_time;
        window.BookingPressRescheduleDialog.reschedule_formdata.booking_end_time = booking.end_time;
        window.BookingPressRescheduleDialog.reschedule_formdata.booking_service = booking.serviceName;
        window.BookingPressRescheduleDialog.reschedule_formdata.booking_customer = booking.customerName;
        window.BookingPressRescheduleDialog.reschedule_formdata.formatted_booking_date = booking.metadata.formatted_booking_date;
        window.BookingPressRescheduleDialog.reschedule_formdata.formatted_booking_time = booking.metadata.formatted_booking_time;
        window.BookingPressRescheduleDialog.reschedule_formdata.booking_service_id = booking.serviceId;
        window.BookingPressRescheduleDialog.reschedule_formdata.booking_customer_id = booking.metadata.customerId;

        window.BookingPressRescheduleDialog.is_display_time = true;

        if( true == booking.metadata.isDayService ){
            window.BookingPressRescheduleDialog.is_display_time = false;
        }
        
        wp.hooks.doAction( 'bookingpress_modify_reschedule_form_data', booking );

        window.BookingPressRescheduleDialog.openRescheduleModalPopup();
    }
});
/** Open Reschedule Appointment Form */

window.addEventListener('bookingpress:calendar-month-range-change', (event) => {
    const { monthStartDate, monthEndDate } = event.detail
    
    document.querySelector('.calendar-page-loader').style.display = 'flex';

    getEvents({
        append: true,
        start_date: monthStartDate,
        end_date: monthEndDate
    });
})
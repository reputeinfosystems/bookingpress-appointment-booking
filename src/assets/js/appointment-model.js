"use strict";

import { createApp, ref } from 'vue';
import BookingPressUI from './bookingpress-ui.min.js';

const BookingPressConfig = window.BookingPressConfig;
const rest_url = BookingPressConfig.rest_url;

document.addEventListener('DOMContentLoaded', () => {
    initNewAppointmentDialog();
});

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

const initNewAppointmentDialog = () => {

    const moduleData = getModuleData('bookingpress-appointment-model');

    let BookingPressAppointmentExternalMethods = wp.hooks.applyFilters('bookingpress_appointment_external_methods', {
        bpa_get_target_parent( elem, selector ){
            if (!Element.prototype.matches) {
                Element.prototype.matches = Element.prototype.matchesSelector ||
                    Element.prototype.mozMatchesSelector ||
                    Element.prototype.msMatchesSelector ||
                    Element.prototype.oMatchesSelector ||
                    Element.prototype.webkitMatchesSelector ||
                    function(s) {
                        var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                            i = matches.length;
                        while (--i >= 0 && matches.item(i) !== this) {}
                        return i > -1;
                    };
            }

            var parents = [];

            for (; elem && elem !== document; elem = elem.parentNode) {
                if (selector) {
                    if (elem.matches(selector)) {
                        parents.push(elem);
                    }
                    continue;
                }
                parents.push(elem);
            }

            return parents;
        },
        bpa_adjust_popup_position( currentElement, selector, sourceCls = '', position, view = '' ){

            let paths = currentElement.path;
            let buttonElm = null;

          
            if( typeof paths != 'undefined' ){
                for( let x in paths ){
                    let currentPath = paths[x];
                    let currentPathNodeName = currentPath.nodeName;
                    if( "BUTTON" == currentPathNodeName || ( 'undefined' != typeof sourceCls && '' != sourceCls && currentPath.classList.contains(sourceCls)) ){
                        buttonElm = currentPath;
                        break;
                    }
                }
            } else {

                if( "BUTTON" == currentElement.target.nodeName || ( 'undefined' != typeof sourceCls && '' != sourceCls && currentElement.target.classList.contains(sourceCls) ) ){
                    buttonElm = currentElement.target;
                } else {
                    let par = this.bpa_get_target_parent( currentElement.target, 'button' );
                    
                    if( par.length > 0 ){
                        buttonElm = par[0];
                    } else {
                        let par = this.bpa_get_target_parent( currentElement.target, '.' + sourceCls );
                        if( par.length > 0 ){
                            buttonElm = par[0];
                        }
                    }
                }
            }
            
            if( null !== buttonElm ){
                let pos_x = buttonElm.getBoundingClientRect().left;
                let pos_y = buttonElm.getBoundingClientRect().top;
                
                pos_x = Math.ceil( pos_x );
                pos_y = Math.ceil( pos_y );

                let btn_height = buttonElm.offsetHeight;
                let pos_top = pos_y + btn_height + 20;

                
                (function(pos_x, buttonElm, class_selector){
                    setTimeout(function(){
                        let dialog__wrapper = document.querySelector( class_selector );
                        if( null !== dialog__wrapper ){

                            let viewportHeight = window.innerHeight;
                            let dialogHeight = dialog__wrapper.offsetHeight;
                            let spaceBelow = viewportHeight - pos_y - btn_height;
                            let spaceAbove = pos_y;

                            if(view == "tablet") {
                                if (spaceBelow >= dialogHeight) {
                                    pos_top = pos_y + btn_height + 20; // Show below
                                } else if (spaceAbove >= dialogHeight) {
                                    pos_top = pos_y - dialogHeight - 20; // Show above
                                } else {
                                    pos_top = Math.max(minMargin, (viewportHeight - dialogHeight) / 2);
                                }
                                if (pos_top < 0) pos_top = 10;
                                if (pos_top + dialogHeight > viewportHeight) pos_top = viewportHeight - dialogHeight - 10;
                            }

                            dialog__wrapper.style.position = '';
                            dialog__wrapper.style.margin = '';
                            dialog__wrapper.style.top = '';
                            dialog__wrapper.style.left = '0';

                            dialog__wrapper.style.position = 'absolute';
                            dialog__wrapper.style.margin = '0';
                            dialog__wrapper.style.top = parseInt(pos_top) + 'px';

                            let pos_to_place = pos_x + ( buttonElm.offsetWidth * 0.5 );
                            let dialog_pos_right = dialog__wrapper.offsetWidth + dialog__wrapper.getBoundingClientRect().left;
                            if( BookingPressConfig.is_rtl ){
                                if( '' != position && 'right' == position ){
                                    dialog_pos_right = dialog_pos_right - 50;
                                    dialog__wrapper.style.left = pos_to_place - dialog_pos_right + 'px';
                                } else {
                                    dialog_pos_right = dialog_pos_right - 30;
                                    dialog__wrapper.style.left = (pos_to_place -  ( dialog__wrapper.getBoundingClientRect().left + 40 ) ) + 'px';
                                }
                            } else {
                                if( '' != position && 'right' == position ){
                                    dialog_pos_right = dialog_pos_right - 30;
                                    dialog__wrapper.style.left = (pos_to_place -  ( dialog__wrapper.getBoundingClientRect().left + 40 ) ) + 'px';
                                } else {
                                    let currentElementId = currentElement.target.id;
                                    let is_mobile = false;
                                    if( null != document.getElementById('bpa-mobile-menu') ){
                                        let is_mob_menu_visible = getComputedStyle( document.getElementById('bpa-mobile-menu') ).display;
                                        if( 'block' == is_mob_menu_visible ){
                                            is_mobile = true;
                                        }
                                    }
                                    if( is_mobile == true && (('BUTTON' == currentElement.target.nodeName && currentElementId == 'bpa-appointment-share-url-button') || ( 'SPAN' == currentElement.target.nodeName && currentElement.target.parentNode != null && currentElement.target.parentNode.id == 'bpa-appointment-share-url-button' ) || ('SPAN' == currentElement.target.nodeName && currentElementId == 'bpa-appointment-share-url-span-txt')) ){
                                        let buttonWidth = document.getElementById( 'bpa-appointment-share-url-button' ).offsetWidth;   
                                        let popupWidth = dialog__wrapper.offsetWidth;
                                        if( pos_x <= 44 ){
                                            dialog_pos_right = dialog_pos_right - 110;
                                        }   
                                    }
                                    dialog_pos_right = dialog_pos_right - 50;
                                    dialog__wrapper.style.left = pos_to_place - dialog_pos_right + 'px';
                                }
                            }
                            
                        }
                    },10)
                })( pos_x, buttonElm, selector );
            }
        },
        get_formatted_date(iso_date) {

            if (true == /(\d{2})\T/.test(iso_date)) {
                let date_time_arr = iso_date.split('T');
                return date_time_arr[0];
            }
            var __date = new Date(iso_date);
            var __year = __date.getFullYear();
            var __month = __date.getMonth() + 1;
            var __day = __date.getDate();
            if (__day < 10) {
                __day = '0' + __day;
            }
            if (__month < 10) {
                __month = '0' + __month;
            }
            var formatted_date = __year + '-' + __month + '-' + __day;
            return formatted_date;
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
        bookingpress_number_format(number, decimals, decPoint, thousandsSep, skip_separator = false) {
            number = (number + "").replace(/[^0-9+\-Ee.]/g, "");
            const n = !isFinite(+number) ? 0 : +number;
            const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
            const sep = (typeof thousandsSep === "undefined") ? "," : thousandsSep;
            const dec = (typeof decPoint === "undefined" || true == skip_separator) ? "." : decPoint;
            let s = "";
            const toFixedFix = function (n, prec) {
                if (("" + n).indexOf("e") === -1) {
                    return +(Math.round(n + "e+" + prec) + "e-" + prec);
                } else {
                    const arr = ("" + n).split("e");
                    let sig = "";
                    if (+arr[1] + prec > 0) {
                        sig = "+";
                    }
                    return (+(Math.round(+arr[0] + "e" + sig + (+arr[1] + prec)) + "e-" + prec)).toFixed(prec);
                }
            };
            /* @todo: for IE parseFloat(0.55).toFixed(0) = 0; */
            s = (prec ? toFixedFix(n, prec).toString() : "" + Math.round(n)).split(".");
            if (false == skip_separator) {
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
            }
            if ((s[1] || "").length < prec) {
                s[1] = s[1] || "";
                s[1] += new Array(prec - s[1].length + 1).join("0");
            }
            if (true == skip_separator) {
                return parseFloat(s.join(dec));
            } else {
                return s.join(dec);
            }
        },
        openAppointmentDialog() {
            this.openAddNewAppointmentModel = true;
        },
        ResetAppointmentModel() {
            Object.assign(this.appointment_formdata, this.default_appointment_formdata);
        },
        closeAppointmentDialog() {
            this.openAddNewAppointmentModel = false;
        },
        saveAppointmentBooking() {
            const form = this.$refs.appointment_formdata;

            if (!form || typeof form.validate !== 'function') {
                console.error('Form ref does not expose validate()');
                return;
            }

            form.validate((valid) => {
                valid = wp.hooks.applyFilters('bookingpress_modify_request_after_validation', valid);

                if (valid) {
                    this.is_disabled = true;
                    this.is_display_save_loader = '1';
                    fetch(rest_url + '/appointment/create', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce,
                        },
                        body: JSON.stringify({
                            appointment_data: this.appointment_formdata
                        })
                    })
                        .then(response => response.json())
                        .then(rest_response => {
                            if (rest_response.data.variant === 'success') {
                                this.is_disabled = false;
                                this.is_display_save_loader = '0';

                                this.closeAppointmentBookingModal();

                                let new_appointment_details = {
                                    timeBookings: rest_response.data.appointment_details
                                };

                                BookingPressCalendarApp.appendData(new_appointment_details);


                                this.$notify({
                                    title: rest_response.data.title,
                                    message: rest_response.data.msg,
                                    type: 'success',
                                    duration: BookingPressConfig.notification_timeout
                                });
                            } else {
                                this.$notify({
                                    title: 'Error',
                                    message: rest_response.data.msg,
                                    type: 'error',
                                    customClass: 'error_notification',
                                    duration: BookingPressConfig.notification_timeout
                                });
                            }
                            this.closeAppointmentBookingModal();
                        })
                        .catch(error => {
                            this.is_disabled = false;
                            this.is_display_save_loader = '0';
                            this.$notify({
                                title: 'Error',
                                message: 'Something went wrong while saving appointment booking',
                                duration: BookingPressConfig.notification_timeout
                            });
                        });
                }
            });
        },
        closeAppointmentBookingModal() {
            const vm = this;
            const form = this.$refs.appointment_formdata;
            form.resetFields();
            this.appointment_customers_list = [];
            this.openAddNewAppointmentModel = false;
            wp.hooks.doAction('bookingpress_add_appointment_model_reset', vm);
        },
        bpa_select_customer(bookingpress_selected_customer) {
            const vm = this;
            if ('add_new' === bookingpress_selected_customer) {
                window.BookingPressCustomerDialog.openCustomerDialog();
            } else {
                vm.bookingpress_retrieve_custom_field_values(bookingpress_selected_customer);
            }
            wp.hooks.doAction('bookingpress_backend_after_select_customer', vm);
        },
        bookingpress_retrieve_custom_field_values(selected_customer_id) {
            const vm = this;
            let postData = { action: "bookingpress_get_customer_form_field_values", customer_id: selected_customer_id, _wpnonce: BookingPressConfig._wpnonce };
            axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(postData))
                .then(function (response) {
                    if (response.data.variant == 'success') {
                        let customer_form_fields = response.data.customer_form_fields;
                        for (let field_key in customer_form_fields) {
                            let field_value = customer_form_fields[field_key];
                            if ('undefined' != typeof vm.appointment_formdata.bookingpress_appointment_meta_fields_value[field_key]) {
                                vm.appointment_formdata.bookingpress_appointment_meta_fields_value[field_key] = field_value;
                            }
                        }
                    }
                }).catch(function (error) {
                    console.log(error);
                })
        },
        bookingpress_get_customer_list(query) {

            if (query.length > 0 || 'object' == typeof query) {
                this.loading_from_server = true;
                let searchData = { 'search': query };
                if ('object' == typeof query) {
                    searchData = { 'customer_id': query.customer_id };
                } else {
                    const search = (query || '').trim();
                    if (!search) {
                        this.loading_from_server = false;
                        this.appointment_customers_list = [];
                        return;
                    }
                    searchData = { 'search': search };
                }
                fetch(rest_url + '/customer', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce,
                    },
                    body: JSON.stringify(searchData)
                })
                    .then(response => response.json())
                    .then(rest_response => {
                        this.loading_from_server = false;
                        if (rest_response.success) {
                            this.appointment_customers_list = rest_response.data;
                            if ('object' == typeof query) {
                                this.appointment_formdata.appointment_selected_customer = rest_response.data[0].value;
                            }
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
                        this.loading_from_server = false;
                        this.$notify({
                            title: 'Error',
                            message: 'Something went wrong while fetching customer list',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout
                        });
                    });
            }
        },
        bookingpress_price_with_currency_symbol(price_amount, ignore_symbol = false) {
            const vm = this;
            if ("String" == typeof price_amount) {
                price_amount = parseFloat(price_amount);
            }

            let currency_separator = vm.bookingpress_currency_separator;
            let decimal_points = vm.bookingpress_decimal_points;

            if ("comma-dot" == currency_separator) {
                price_amount = vm.bookingpress_number_format(price_amount, decimal_points, ".", ",", ignore_symbol);
            } else if ("dot-comma" == currency_separator) {
                price_amount = vm.bookingpress_number_format(price_amount, decimal_points, ",", ".", ignore_symbol);
            } else if ("space-dot" == currency_separator) {
                price_amount = vm.bookingpress_number_format(price_amount, decimal_points, ".", " ", ignore_symbol);
            } else if ("space-comma" == currency_separator) {
                price_amount = vm.bookingpress_number_format(price_amount, decimal_points, ",", " ", ignore_symbol);
            } else if ("Custom" == currency_separator) {
                let custom_comma_separator = vm.bookingpress_custom_comma_separator;
                let custom_thousand_separator = vm.bookingpress_custom_thousand_separator;
                price_amount = vm.bookingpress_number_format(price_amount, decimal_points, custom_comma_separator, custom_thousand_separator);
            }

            if (true == ignore_symbol) {
                return price_amount;
            }

            let currency_symbol = vm.bookingpress_currency_symbol;
            let currency_symbol_pos = vm.bookingpress_currency_symbol_position;

            if ("before" == currency_symbol_pos) {
                price_amount = currency_symbol + price_amount;
            } else if ("before_with_space" == currency_symbol_pos) {
                price_amount = currency_symbol + " " + price_amount;
            } else if ("after" == currency_symbol_pos) {
                price_amount = price_amount + currency_symbol;
            } else if ("after_with_space" == currency_symbol_pos) {
                price_amount = price_amount + " " + currency_symbol;
            }

            return price_amount;

        },

        bookingpress_get_disable_dates(reset_timeslot_field = false, use_for_reschedule_popup = false) {
            const vm = this;
            let bookingpress_appointment_form_data = vm.appointment_formdata;
            if (true == reset_timeslot_field) {
                vm.appointment_formdata.appointment_booked_time = "";
            }

            var bookingpress_appointment_date = vm.appointment_formdata.appointment_booked_date;

            var bookingpress_moment_formatted_date = moment(bookingpress_appointment_date);
            bookingpress_appointment_date = bookingpress_moment_formatted_date.format('YYYY-MM-DD');

            let postData = {
                appointment_data_obj: bookingpress_appointment_form_data,
                service_id: bookingpress_appointment_form_data.appointment_selected_service,
                selected_date: bookingpress_appointment_date,
                bpa_fetch_data: true,
                selected_service: bookingpress_appointment_form_data.appointment_selected_service,
            };

            postData = wp.hooks.applyFilters('bookingpress_set_additional_appointment_xhr_data', postData, vm);

            fetch(rest_url + '/dates', {
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
                        let rescheduleConfig = window.BookingPressRescheduleDialog;

                        let response = rest_response.data;
                        let disableDates = response.days_off_disabled_dates;

                        let disableDates_arr = disableDates.split(',');

                        let disableDates_formatted = [];
                        disableDates_arr.forEach(function (date) {
                            let formatted_date = vm.get_formatted_date(date);
                            disableDates_formatted.push(formatted_date);
                        });

                        this.appendDisabledDates(disableDates_formatted);
                        if (true == use_for_reschedule_popup) {
                            rescheduleConfig.replaceDisabledDates(disableDates_formatted);
                        }

                        /** Time Slot changes start */
                        if (typeof response.front_timings !== "undefined") {

                            let timeslot_response_data = response.front_timings;
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

                            vm.appointment_time_slot = timeslot_data;

                        }
                        /** Time Slot changes start */

                        /** Reset booked date if it is disabled */
                        vm.disabledDates = disableDates_formatted;

                        if (typeof vm.disabledDates != 'undefined') {
                            if (typeof vm.disabledDates.length != 'undefined') {
                                if (vm.disabledDates.includes(vm.appointment_formdata.appointment_booked_date)) {
                                    vm.appointment_formdata.appointment_booked_date = response.selected_date;
                                }
                            }
                        }

                        if (true == use_for_reschedule_popup) {
                            rescheduleConfig.disableDates = disableDates_formatted;
                            if (typeof rescheduleConfig.disableDates != 'undefined') {
                                if (typeof rescheduleConfig.disabledDates.length != 'undefined') {
                                    if (rescheduleConfig.disabledDates.includes(rescheduleConfig.appointment_formdata.appointment_booked_date)) {
                                        rescheduleConfig.appointment_formdata.appointment_booked_date = response.selected_date;
                                    }
                                }
                            }
                        }

                        /** Reset booked date if it is disabled */

                        wp.hooks.doAction('bookingpress_additional_disable_dates', vm, response);

                    }
                })
                .catch(error => {
                    console.log(error);
                    this.$notify({
                        title: 'Error',
                        message: 'Something went wrong while fetching dates',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                });
        },
        select_appointment_booking_date(selected_value, skip_time_update = false) {
            const vm = this;
            vm.appointment_formdata.appointment_booked_date = vm.appointment_formdata.selected_date = selected_value;

            let postData = {
                'service_id': vm.appointment_formdata.appointment_selected_service,
                'selected_date': selected_value,
                'appointment_data_obj': vm.appointment_formdata
            };

            if (!skip_time_update) {
                this.appointment_formdata.appointment_booked_time = '';
                this.appointment_formdata.appointment_booked_end_time = '';
            }

            postData = wp.hooks.applyFilters('bookingpress_get_front_timing_set_additional_appointment_xhr_data', postData, vm);

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
                        //this.appointment_time_slot = rest_response.data;
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

                        this.appointment_time_slot = timeslot_data;

                        wp.hooks.doAction('bookingpress_backend_after_get_timeslot_response', vm, rest_response);

                        //wp.hooks.doAction('bookingpress_additional_disable_dates', vm);
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
                    console.log(error);
                    this.$notify({
                        title: 'Error',
                        message: 'Something went wrong while fetching time slot',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                });
        },
        default_timeConvertToTimestamp(timeString) {
            let [hours, minutes, seconds] = timeString.split(":").map(Number);
            return hours * 3600 + minutes * 60 + seconds;
        },
        bookingpress_appointment_change_service() {
            const vm = this;

            vm.appointment_time_slot = [];
            vm.appointment_formdata.appointment_booked_time = '';
            vm.appointment_formdata.appointment_booked_end_time = '';

            wp.hooks.doAction('bookingpress_appointment_change_service_action');

            let selected_date = vm.appointment_formdata.appointment_booked_date;
            //vm.select_appointment_booking_date(selected_date);
            vm.bookingpress_get_disable_dates();

        },
        bookingpress_set_time(event, time_slot_data) {
            const vm = this
            if (event != '' && time_slot_data != '') {
                for (let x in time_slot_data) {
                    var slot_data_arr = time_slot_data[x];
                    for (let y in slot_data_arr) {
                        var time_slot_data_arr = slot_data_arr[y];
                        for (let m in time_slot_data_arr) {
                            var data_arr = time_slot_data_arr[m];
                            if (data_arr.store_start_time != undefined && data_arr.store_end_time != undefined && data_arr.store_start_time == event) {
                                vm.appointment_formdata.appointment_booked_end_time = data_arr.store_end_time;
                                wp.hooks.doAction('bookingpress_admin_add_appointment_after_select_timeslot', data_arr, vm);
                            }
                        }
                    }
                }
            }
        }
    });

    let BookingPressAppointmentExternalComputedMethods = wp.hooks.applyFilters('bookingpress_appointment_external_computed_methods', {});

    let BookingPressAppointmentExternalWatchMethods = wp.hooks.applyFilters('bookingpress_appointment_external_watch_methods', {});

    const BookingPressAppointmentDialogConfig = {
        data() {
            const vm = this;

            let ModelConfigData = moduleData;/* {
                openAddNewAppointmentModel: false,
                closeModelOnEscape: true,
                appointment_formdata: {
                    appointment_update_id: 0,
                    appointment_selected_customer: '',
                    appointment_booked_date: new Date().toISOString().split('T')[0],
                    appointment_booked_time: '',
                    appointment_booked_end_time: '',
                    appointment_internal_note: '',
                    appointment_send_notification: false,
                    appointment_status: '1',
                    appointment_selected_service: ''
                },
                is_display_save_loader: 0,
                is_disabled: false,
                appointment_customers_list: [],
                appointment_services_list: moduleData.BookingPressServiceProviders,
                appointment_time_slot: [],
                appointment_status: moduleData.BookingPressAppointmentStatus,
                bookingpress_edit_customers: moduleData.bookingpress_edit_customers,
                bookingpress_payments: moduleData.bookingpress_payments,
                bookingpress_loading: 'Loading...',
                loading_from_server: false,
                bookingpress_date_common_date_format: moduleData.bookingpress_date_common_date_format,
                disabledDates: moduleData.disabledDates,
                rules: {
                    appointment_selected_customer: [
                        {
                            required: true,
                            message: 'Please select customer',
                            trigger: 'change'
                        }
                    ],
                    appointment_selected_service: [
                        {
                            required: true,
                            message: 'Please select service',
                            trigger: 'change'
                        }
                    ],
                    appointment_booked_date: [
                        {
                            required: true,
                            message: 'Please select booking date',
                            trigger: 'change'
                        }
                    ],
                    appointment_booked_time: [
                        {
                            required: true,
                            message: 'Please select booking time',
                            trigger: 'change'
                        }
                    ],
                    no_timeslots_available_text: 'No time slots available'
                }
            } */;
            ModelConfigData = wp.hooks.applyFilters('bookingpress_modify_appointment_model_data', ModelConfigData, vm);

            return ModelConfigData;
        },
        computed: {
            ...BookingPressAppointmentExternalComputedMethods
        },
        watch: {
            ...BookingPressAppointmentExternalWatchMethods
        },
        methods: {
            ...BookingPressAppointmentExternalMethods
        }
    }

    const BookingPressAppointmentDialog = createApp(BookingPressAppointmentDialogConfig);

    BookingPressAppointmentDialog.use(BookingPressUI);
    window.BookingPressAppointmentDialog = BookingPressAppointmentDialog.mount('#bookingpress-appointment-dialog');
}

/** Popover Events Handlers */

/** Open Add New Appointment Form */
window.addEventListener('bookingpress:open-add-new-appointment-form', () => {
    if (window.BookingPressAppointmentDialog) {
        window.BookingPressAppointmentDialog.openAppointmentDialog();
    }
});

/** Open Edit Appointment Form */
window.addEventListener('bookingpress:appointment-popover-edit', (event) => {
    const { booking } = event.detail;
    window.dispatchEvent(new CustomEvent('bookingpress:appointment-popover-close'));
    window.BookingPressAppointmentDialog.appointment_formdata.appointment_update_id = booking.id;
    window.BookingPressAppointmentDialog.openAppointmentDialog();

    fetch(rest_url + '/appointment/fetch', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': BookingPressConfig.rest_nonce,
        },
        body: JSON.stringify({ appointment_id: booking.id })
    })
        .then(response => response.json())
        .then(rest_response => {
            if (rest_response.success) {
                const vm = window.BookingPressAppointmentDialog;
                vm.appointment_customers_list = rest_response.data.appointment_customer_list;
                vm.appointment_formdata.appointment_selected_customer = rest_response.data.bookingpress_customer_id;

                vm.customer_id = vm.appointment_formdata.appointment_selected_customer;
                vm.bookingpress_get_customer_list({ customer_id: vm.customer_id });

                vm.appointment_formdata.appointment_selected_service = rest_response.data.bookingpress_service_id;
                vm.appointment_formdata.appointment_booked_date = rest_response.data.bookingpress_appointment_date;
                vm.appointment_formdata.appointment_booked_time = rest_response.data.bookingpress_appointment_time;
                vm.appointment_formdata.appointment_booked_end_time = rest_response.data.bookingpress_appointment_end_time;
                vm.appointment_formdata.appointment_internal_note = rest_response.data.bookingpress_appointment_internal_note;
                vm.appointment_time_slot = rest_response.data.appointment_time_slot;
                vm.appointment_formdata.appointment_status = rest_response.data.bookingpress_appointment_status;

                let selected_date = vm.appointment_formdata.appointment_booked_date;

                wp.hooks.doAction('bookingpress_edit_appointment_details', vm, rest_response);

                vm.bookingpress_get_disable_dates();

            }
        })
        .catch(error => {
            console.error('Something went wrong while fetching appointment data:', error);
        });
});
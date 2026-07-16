"use strict";

import { createApp } from 'vue';
import { BookingPressUI, CirclePlusFilled, RemoveFilled } from 'bookingpress-ui';

const BookingPressConfig = window.BookingPressConfig;
const rest_url = BookingPressConfig.rest_url;

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

const AppointmentModuleData = getModuleData('bookingpress-appointment-loader');

document.addEventListener('DOMContentLoaded', function () {
    initAppointmentLoader();
});

const initAppointmentLoader = () => {

    let AppointmentMethods = wp.hooks.applyFilters('bookingpress_appointment_methods', {
        open_add_appointment_modal: function () {
            window.BookingPressAppointmentDialog.openAppointmentDialog();
        },
        bookingpress_share_url_modal ( currentElement ) {
            const vm = this;
            vm.bpa_share_url_modal = true;

            if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
                vm.bpa_adjust_popup_position( currentElement, 'div#appointment-app-root .bp-dialog.bpa-dialog--share-url');
            }
        },
        resetFilter: function () {
            const vm = this;
            vm.search_appointment = '';
            vm.appointment_date_range = ''
            vm.search_customer_name = ''
            vm.search_service_name = ''
            vm.search_appointment_status = ''
            vm.search_appointment_id = ''
            wp.hooks.doAction( 'bookingpress_reset_appointment_filter', vm );
            vm.loadAppointments();
        },
        isOnlyNumber: function (evt) {
            const vm = this
            this.search_appointment_id = event.target.value.replace(/[^0-9]/g, "");
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
        toggleBusy() {
            if (this.is_display_loader == '1') {
                this.is_display_loader = '0'
            } else {
                this.is_display_loader = '1'
            }
        },
        handel_appointment_changes({ column, prop, order }) {
            this.bookingpress_previous_row_id = '';
            if (!order) {
                this.bpa_appointment_sort_by = '';
                this.bpa_appointment_sort_order = '';
            } else {
                this.bpa_appointment_sort_by = prop;
                this.bpa_appointment_sort_order = order === 'descending' ? 'DESC' : 'ASC';
            }

            this.loadAppointments();
        },
        bookingpress_full_row_clickable(row, column, event) {
            const vm = this;

            if (!row || !row.appointment_id) {
                return;
            }

            let targetElement = event.target || event.srcElement;
            let parentNode = vm.bpa_get_target_parent( targetElement, '.bpa-appointment-status-dropdown-wrapper, .bpa-table-actions-wrap' );

            if (0 < parentNode.length) {
                return;
            }

            wp.hooks.doAction( 'bookingpress_appointment_full_row_clickable', vm, row, column, event );

            vm.$refs.multipleTable.toggleRowExpansion(row);
        },
        bookingpress_row_expand(row, expandedRows) {
            const vm = this;

            if (!row || !row.appointment_id) {
                return;
            }

            const currentAppointmentId = String(row.appointment_id);

            const isCurrentRowExpanded = Array.isArray(expandedRows)
                ? expandedRows.some((expandedRow) => {
                    return String(expandedRow.appointment_id) === currentAppointmentId;
                })
                : !!expandedRows;

            // If current row is collapsed, clear previous row id only if it is same row.
            if (!isCurrentRowExpanded) {
                if (String(vm.bookingpress_previous_row_id) === currentAppointmentId) {
                    vm.bookingpress_previous_row_id = '';
                }

                return;
            }

            const previousAppointmentId = vm.bookingpress_previous_row_id;

            // Save current row as expanded row first.
            vm.bookingpress_previous_row_id = currentAppointmentId;

            // Close previous row if different.
            if (
                previousAppointmentId &&
                String(previousAppointmentId) !== currentAppointmentId
            ) {
                const previousRow = vm.items.find((item) => {
                    return String(item.appointment_id) === String(previousAppointmentId);
                });

                if (previousRow) {
                    vm.$refs.multipleTable.toggleRowExpansion(previousRow, false);
                }
            }
        },
        handleSelectionChange(val) {
            const appointment_items_obj = val
            this.multipleSelection = [];
            Object.values(appointment_items_obj).forEach(val => {
                this.multipleSelection.push({ appointment_id: val.appointment_id })
                this.bulk_action = 'bulk_action';
            });
        },
        bpa_get_target_parent(elem, selector) {
            if (!Element.prototype.matches) {
                Element.prototype.matches = Element.prototype.matchesSelector ||
                    Element.prototype.mozMatchesSelector ||
                    Element.prototype.msMatchesSelector ||
                    Element.prototype.oMatchesSelector ||
                    Element.prototype.webkitMatchesSelector ||
                    function (s) {
                        var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                            i = matches.length;
                        while (--i >= 0 && matches.item(i) !== this) { }
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
        async loadAppointments(resetPagination = false) {
            const vm = this;
            vm.toggleBusy();

            let bookingpress_module_type = '';
            let bookingpress_dashboard_filter_start_date = '';
            let bookingpress_dashboard_filter_end_date = '';
            let bookingpress_dashboard_filter_appointment_status = '';

            if (true == resetPagination) {
                vm.currentPage = 1;
            }
            bookingpress_module_type = sessionStorage.getItem("bookingpress_module_type");
            bookingpress_dashboard_filter_start_date = sessionStorage.getItem("bookingpress_dashboard_filter_start_date");
            bookingpress_dashboard_filter_end_date = sessionStorage.getItem("bookingpress_dashboard_filter_end_date");
            bookingpress_dashboard_filter_appointment_status = sessionStorage.getItem("bookingpress_dashboard_filter_appointment_status");

            sessionStorage.removeItem("bookingpress_module_type");
            sessionStorage.removeItem("bookingpress_dashboard_filter_start_date");
            sessionStorage.removeItem("bookingpress_dashboard_filter_end_date");
            sessionStorage.removeItem("bookingpress_dashboard_filter_appointment_status");

            if (bookingpress_module_type != '' && bookingpress_module_type == 'appointment' && bookingpress_dashboard_filter_start_date != '' && bookingpress_dashboard_filter_end_date != '') {
                if (bookingpress_dashboard_filter_appointment_status == '1') {
                    this.search_appointment_status = '1';
                } else if (bookingpress_dashboard_filter_appointment_status == '2') {
                    this.search_appointment_status = '2';
                }
                var appointment_date_range = [bookingpress_dashboard_filter_start_date, bookingpress_dashboard_filter_end_date];
                this.appointment_date_range = appointment_date_range;
            }

            let bookingpress_search_data = {
                'search_appointment': vm.search_appointment,
                'selected_date_range': vm.appointment_date_range,
                'customer_name': vm.search_customer_name,
                'service_name': vm.search_service_name,
                'appointment_status': vm.search_appointment_status,
                'search_appointment_id': vm.search_appointment_id
            };

            bookingpress_search_data = wp.hooks.applyFilters('bookingpress_modify_appointment_search_data', bookingpress_search_data, vm);

            let bodyParam = {
                perpage: this.perPage,
                currentpage: this.currentPage,
                search_data: bookingpress_search_data,
                sort_by: this.bpa_appointment_sort_by,
                sort_order: this.bpa_appointment_sort_order,
                appt_nonce: BookingPressConfig._wpnonce
            };

            fetch(rest_url + '/appointments', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify(bodyParam)
            })
            .then(response => response.json())
            .then(rest_response => {
                vm.toggleBusy();
                if (rest_response.success) {
                    vm.items = rest_response.data.items;
                    vm.totalItems = Number( rest_response.data.totalItems || 0 );
                    vm.form_field_data = rest_response.data.form_field_data;
                }

                wp.hooks.doAction( 'bookingpress_modify_appointment_success_response_data', rest_response, vm );
            })
            .catch(error => {
                vm.toggleBusy();
                console.error('Error fetching appointments:', error);
            });
        },
        bookingpress_get_search_customer_list: function (query) {
            const vm = this;
            if ('' !== query) {
                vm.bookingpress_loading = true;
                fetch(rest_url + '/customer', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce
                    },
                    body: JSON.stringify({ search: query })
                })
                .then(response => response.json())
                .then(rest_response => {
                    console.log(rest_response);
                    vm.bookingpress_loading = false;
                    console.log(vm.bookingpress_loading);
                    if (rest_response.success) {
                        console.log("INSIDE");
                        vm.search_customer_list = rest_response.data;
                    } else {
                        vm.$notify({
                            title: 'Error',
                            message: rest_response.message,
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout
                        });
                    }
                })
                .catch(error => {
                    this.bookingpress_loading = false;
                    console.error('Error fetching customer list:', error);
                });
            }
        },
        changePaginationSize(selectedPage){
            var total_recored_perpage = selectedPage;
            var current_page = this.changeCurrentPage(total_recored_perpage);                                        
            this.perPage = selectedPage;                    
            this.currentPage = current_page;    
            this.loadAppointments()
        },
        bookingpress_remove_date_range_picker_focus(){
            if( 'undefined' != typeof BookingPressConfig && 'undefined' != typeof BookingPressConfig.is_wp_mobile && true == BookingPressConfig.is_wp_mobile ){
                const datepickerinput = document.querySelectorAll(".el-range-input");
                if(typeof datepickerinput != "undefined"){
                    datepickerinput.forEach((dateItem) => {
                        dateItem.blur();
                    });                    
                }
            }
        },
        changeCurrentPage( perPage ){
            var total_item = Number( this.totalItems || 0 );
            var recored_perpage = perPage;
            var select_page =  this.currentPage;                
            var current_page = Math.ceil(total_item/recored_perpage);
            if(total_item <= recored_perpage ) {
                current_page = 1;
            } else if(select_page >= current_page ) {
                
            } else {
                current_page = select_page;
            }
            return current_page;
        },
        handleCurrentChange(val) {
            this.currentPage = val;
            this.loadAppointments()
        },
        bookingpress_change_status( appointment_id, new_status, scope_row ){
            const postData = {
                appointment_id: appointment_id,
                new_status: new_status
            };

            scope_row.change_status_loader = 1;

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
                scope_row.change_status_loader = 0;
                if( "undefined" != typeof rest_response.data && "undefined" != typeof rest_response.data.variant && 'error' == rest_response.data.variant ){
                    this.$notify({
                        title: rest_response.data.title,
                        message: rest_response.data.msg,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }

                if( 'undefined' != typeof rest_response.data && ('0' == rest_response.data || 0 == rest_response.data) ){
                    this.$notify({
                        title: 'Error',
                        message: AppointmentModuleData.status_change_messages.booked_slot,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                } else {
                    this.$notify({
                        title: 'Success',
                        message: AppointmentModuleData.status_change_messages.success,
                        type: 'success',
                        customClass: 'success_notification',
                        duration: BookingPressConfig.notification_timeout
                    });

                    const updateLabel = this.appointment_status.find(status => String(status.value) === String(new_status))?.text || '';
                    console.log( updateLabel );
                    this.updateAppointmentRow(appointment_id, { appointment_status_label: updateLabel, appointment_status: new_status });

                    //this.loadAppointments();
                }
            })
            .catch(error => {
                scope_row.change_status_loader = 0;
                console.error('Error updating appointment status:', error);
            });
        },
        updateAppointmentRow(appointmentId, updatedData){
            const index = this.items.findIndex((row) => {
               return String(row.appointment_id) === String(appointmentId)
            })

            if (index !== -1) {
                this.items.splice(index, 1, {
                 ...this.items[index],
                    ...updatedData,
                })
            }
        },
        bookingpress_enable_modal(){
            document.body.style.overflow = 'hidden';
        },
        bookingpress_disable_modal(){
            if(document.body.classList.contains("el-popup-parent--hidden")){
                document.body.classList.remove("el-popup-parent--hidden");
                document.body.style.paddingRight = "0px";
            }
            document.body.style.overflow = 'auto';
        },
        bpa_enable_service_share(){
            const vm = this;
            if(vm.share_url_form.selected_service_id != '' && vm.share_url_form.email_sharing == true && vm.share_url_form.sharing_email != '' && vm.share_url_form.selected_page_wp_id!=''){
                vm.is_share_button_disabled = false;
                vm.bookingpress_generate_share_url();
            }else{
                vm.is_share_button_disabled = true;
                vm.bookingpress_generate_share_url();
            }
        },
        bookingpress_copy_share_url(){
            const vm = this;
            var bpa_generated_url = vm.share_url_form.generated_url;
            var bookingpress_dummy_elem = document.createElement("textarea");
            document.body.appendChild(bookingpress_dummy_elem);
            bookingpress_dummy_elem.value = bpa_generated_url;
            bookingpress_dummy_elem.select();
            document.execCommand("copy");
            document.body.removeChild(bookingpress_dummy_elem);
            vm.$notify({
                title: AppointmentModuleData.common_messages.success,
                message: AppointmentModuleData.common_messages.url_copy_msg,
                type: 'success',
                customClass: 'success_notification',
                duration: BookingPressConfig.notification_timeout
            });
        },
        bookingpress_generate_share_url(){
            const vm = this;

            let shareAppointmentUrlData = {
                share_url_form_data: vm.share_url_form,
            };

            fetch( rest_url + '/generate-share-url', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify(shareAppointmentUrlData)
            })
            .then(response => response.json())
            .then(response => {
                if( response.data.variant == 'success' ){
                    vm.share_url_form.generated_url = response.data.generated_url;
                } else{
                    vm.$notify({
                        title: response.data.title,
                        message: response.data.msg,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });	
                }
            })
            .catch(error => {
                console.error('Error generating share URL:', error);
            });
        },
        bpa_share_appointment_url( share_url_form ){
            const vm = this;
            vm.$refs[share_url_form].validate((valid) => {
                if (valid) {
                    vm.is_share_button_loader = 1;
                    vm.is_share_button_disabled = true;
                    var appointment_generate_url_details = {
                        share_url_form_data: vm.share_url_form
                    }

                    fetch( rest_url + '/share-generated-appointment-url', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce
                        },
                        body: JSON.stringify(appointment_generate_url_details)
                    })
                    .then( response => response.json() )
                    .then( response => {
                        if(response.data.variant == "success"){
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: 'success',
                                customClass: 'success_notification',
                            });	
                            vm.is_share_button_loader = 0;
                            vm.is_share_button_disabled = false;
                            vm.bpa_share_url_modal = false;
                        }else{
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: 'error',
                                customClass: 'error_notification',
                            });	
                            vm.is_share_button_loader = 0;
                            vm.is_share_button_disabled = false;
                            vm.bpa_share_url_modal = false;
                        }
                    })
                    .catch( error => {
                        vm.is_share_button_loader = 0;
                        vm.is_share_button_disabled = false;
                        vm.bpa_share_url_modal = false;
                        console.error('Error sharing appointment URL:', error);
                    });
                }
            });
        },
        editAppointmentData( index, row ){
            const vm = this;
            let edit_id = row.appointment_id;

            window.BookingPressAppointmentDialog.appointment_formdata.appointment_update_id = edit_id;
            window.BookingPressAppointmentDialog.openAppointmentDialog();

            vm.is_editing_appointment = true;

            window.BookingPressAppointmentDialog.fetchAppointmentDataForEditing(edit_id);
        },
        bookingpress_get_page_list( query ){
            const vm = this;
            if (query !== '') {
                vm.bookingpress_loading = true;
                fetch( rest_url + '/share-url-page-list', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce
                    },
                    body: JSON.stringify({ search_page_str: query })
                })
                .then(response => response.json())
                .then(response => {
                    console.log( response );
                    vm.bookingpress_loading = false;
                    vm.all_share_pages_list = response.data.all_page_list;
                })
                .catch(error => {
                    vm.bookingpress_loading = false;
                    console.error('Error fetching share URL page list:', error);
                });
            } else {
                vm.all_share_pages_list = [];
            }
        },
        bulk_actions(){
            const vm = this;

            if( 'bulk_action' == vm.bulk_action ){
                vm.$notify({
                    title: 'Error',
                    message: AppointmentModuleData.common_messages.bulk_action_select,
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
            } else {
                if( vm.multipleSelection.length === 0 ){
                    vm.$notify({
                        title: 'Error',
                        message: AppointmentModuleData.common_messages.bulk_action_no_selection,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                } else {
                    if( 'delete' == vm.bulk_action ){
                        let postData = {
                            appointment_ids: vm.multipleSelection.map(item => item.appointment_id),
                            _wpnonce: BookingPressConfig._wpnonce
                        };

                        fetch( rest_url + '/appointment/bulk-delete', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': BookingPressConfig.rest_nonce
                            },
                            body: JSON.stringify(postData)
                        })
                        .then(response => response.json())
                        .then(response => {

                            if( response.success ){
                                vm.$notify({
                                    title: 'Success',
                                    message: response.data.msg,
                                    type: 'success',
                                    customClass: 'success_notification',
                                    duration: BookingPressConfig.notification_timeout
                                });
                                vm.closeBulkAction();
                                vm.loadAppointments();
                            } else if( response.data.variant == 'error' ){
                                vm.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: 'error',
                                    customClass: 'error_notification',
                                    duration: BookingPressConfig.notification_timeout
                                });
                            } else if( response.data.variant == 'warning' ){
                                vm.$notify({
                                    title: response.data.title,
                                    message: response.data.msg,
                                    type: 'warning',
                                    customClass: 'warning_notification',
                                    duration: BookingPressConfig.notification_timeout
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error performing bulk action:', error);
                        });
                    }
                }
            }
        },
        closeBulkAction(){
            this.$refs.multipleTable.clearSelection();
            this.bulk_action = 'bulk_action';
        }
    });

    const AppointmentConfig = {
        data() {
            let configData = {
                current_screen_size: 'desktop',
                search_customer_name: '',
                search_service_name: '',
                search_appointment_status: '',
                search_appointment_id: '',
                search_appointment: '',
                bookingpress_loading: false,
                search_customer_list: [],
                CirclePlusFilled,
                RemoveFilled,
                bookingpress_previous_row_id: '',
                bulk_options:[
                    {
                        'value': 'bulk_action',
                        'label': AppointmentModuleData.bulk_action_labels.bulk_action
                    },
                    {
                        'value': 'delete',
                        'label': AppointmentModuleData.bulk_action_labels.delete
                    }
                ],
                search_status: AppointmentModuleData.appointment_status,
                appointment_status: AppointmentModuleData.appointment_status,
                appointment_date_range: AppointmentModuleData.appointment_date_range,
                bpa_date_common_date_format: AppointmentModuleData.bpa_date_common_date_format,
                firstDayOfWeek: AppointmentModuleData.firstDayOfWeek,
                appointment_services_data: AppointmentModuleData.appointment_services_list,
                items: [],
                multipleSelection:[],
                is_display_loader: false,
                currentPage: 1,
                pagination_length: AppointmentModuleData.pagination_length,
                perPage: Number( AppointmentModuleData.perPage ),
                totalItems: 0,
                pagination_selected_length: AppointmentModuleData.pagination_selected_length,
                bpa_appointment_sort_by: '',
                bpa_appointment_sort_order: '',
                pagination_length_val: AppointmentModuleData.pagination_length_val,
                pagination_val: [
                    {
                        'text': 10,
                        'value': 10
                    },
                    {
                        'text': 20,
                        'value': 20
                    },
                    {
                        'text': 50,
                        'value': 50
                    },
                    {
                        'text': 100,
                        'value': 100
                    },
                    {
                        'text': 200,
                        'value': 200
                    },
                    {
                        'text': 300,
                        'value': 300
                    },
                    {
                        'text': 400,
                        'value': 400
                    },
                    {
                        'text': 500,
                        'value': 500
                    }
                ],
                /** Share URL Data Fields */
                bpa_share_url_modal: false,
                is_mask_display: false,
                share_url_form:{
                    'selected_page_id': AppointmentModuleData.selected_page_id,
                    'selected_page_wp_id':'',
                    'selected_service_id':'',
                    'generated_url': AppointmentModuleData.generated_url,
                    'allow_customer_to_modify': false,
                    'email_sharing': false,
                    'sharing_email': ''
                },
                all_share_pages: AppointmentModuleData.all_share_pages,
                all_share_pages_list: [],
                is_share_button_disabled: true,
                is_share_button_loader: '0',
                popConfirmPlacement: BookingPressConfig.is_rtl ? 'bottom-start' : 'bottom-end',
                share_url_rules:{
                    "selected_service_id":[
                        {
                            "required": true,
                            "trigger":"change",
                            "message": AppointmentModuleData.share_url_validation_messages.service_required
                        }
                    ],
                    "selected_page_wp_id":[
                        {
                            "required":true,
                            "trigger":"change",
                            "message": AppointmentModuleData.share_url_validation_messages.page_required
                        }
                    ],
                    "sharing_email":[
                        {
                            "required": true,
                            "trigger":"change",
                            "message": AppointmentModuleData.share_url_validation_messages.sharing_email
                        }
                    ]
                }
                /** Share URL Data Fields */
            };
            configData = wp.hooks.applyFilters('bookingpress_appointment_data', configData);

            return {
                ...configData,
            }
        },
        methods: {
            ...AppointmentMethods
        },
        mounted() {
            document.onreadystatechange = () => {
                if (document.readyState == "complete") {
                    setTimeout(function () {
                        if (document.getElementById('bpa-page-loading-loader') != null) {
                            document.getElementById('bpa-page-loading-loader').remove();
                            document.getElementById('bpa-main-container').style.display = 'block';
                            if (document.getElementById('bpa-page-loading-loader-2') != null) {
                                document.getElementById('bpa-page-loading-loader-2').remove();
                            }
                            if (document.getElementById('bpa-main-container-2') != null) {
                                document.getElementById('bpa-main-container-2').style.display = 'block';
                            }
                            if (document.getElementById('bpa-main-container-3') != null) {
                                document.getElementById('bpa-page-loading-loader-3').remove();
                                document.getElementById('bpa-main-container-3').style.display = 'block';
                            }
                            jQuery("#bpa-loader-div").show();
                        }
                    }, 2000);
                }
            };

            if (window.screen.width >= 1200) {
                this.current_screen_size = "desktop";
            } else if (window.screen.width < 1200 && window.screen.width >= 768) {
                this.current_screen_size = "tablet";
            } else if (window.screen.width < 768) {
                this.current_screen_size = "mobile";
            }

            window.addEventListener('resize', () => {
                if (window.screen.width >= 1200) {
                    this.current_screen_size = "desktop";
                } else if (window.screen.width < 1200 && window.screen.width >= 768) {
                    this.current_screen_size = "tablet";
                } else if (window.screen.width < 768) {
                    this.current_screen_size = "mobile";
                }
            });

            this.loadAppointments();
        }
    };

    const BookingPressAppointment = createApp(AppointmentConfig);
    BookingPressAppointment.use(BookingPressUI);
    window.AppointmentLoader = BookingPressAppointment.mount('#appointment-app-root');

}
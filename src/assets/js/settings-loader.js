"use strict";
 
import { createApp, h, watch } from 'vue';
import BookingPressUI from './bookingpress-ui.min.js';

import 'bookingpress-vcalendar';

const BookingPressConfig = window.BookingPressConfig;
const rest_url = BookingPressConfig.rest_url;
 
function getModuleData( moduleId ) {
    const el = document.getElementById( `wp-script-module-data-${ moduleId }` );
    if ( ! el ) {
        return {};
    }
    try {
        return JSON.parse( el.textContent || '{}' );
    } catch ( error ) {
        console.error( 'Failed to parse module data:', error );
        return {};
    }
}
 
document.addEventListener( 'DOMContentLoaded', () => {
    initSettingsWrapper();
    initViewDebugLogDialog();
    initAddBreakDialog();    
    //initAddHolidayDialog();    
} );

const initSettingsWrapper = () => {

    const settingsmoduleData = getModuleData('bookingpress-settings'); 

    let BookingPressSettingsExternalComputedMethods = wp.hooks.applyFilters('bookingpress_settings_external_computed_methods', {});

    let BookingPressSettingsExternalMethods = wp.hooks.applyFilters('bookingpress_settings_external_methods', {
        
        handleWrapperEvent(){
            document.addEventListener( 'click', function(e){            

                if( e.target == null || !e.target.classList.contains('bp-dialog__wrapper') ){
                    return false;
                }
                let all_highlighted_el = document.querySelectorAll('.vc-highlights.vc-day-layer');

                if( all_highlighted_el.length > 0 ){
                    for( let i = 0; i < all_highlighted_el.length; i++ ){
                        let current_el = all_highlighted_el[i];
                        if( current_el.querySelector('.bpa_selected_daysoff') != null ){
                            continue;
                        }
                        current_el.parentNode.removeChild( current_el );
                    }
                }
            });
        },
        toggleBusy() {
            this.modal_loading = !this.modal_loading
        },
        close_modal(modal_name){
            this.modals[modal_name+'_modal'] = false
        },
        savegeneralSettingsData(){
            const vm = this                
            var response_variant = vm.saveSettingsData('customer_setting_form','customer_setting', true)    
            if(response_variant != 'error') {
                vm.saveSettingsData('general_setting_form','general_setting', false)                
            }
        },
        getSettingsData(settingType, form_name) {
            const vm = this;
            vm.is_display_tab_loader = "1";
            fetch(BookingPressConfig.rest_url + '/settings/fetch', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body: JSON.stringify({
                    setting_type: settingType
                })
            })            
            .then(response => response.json())
            .then(rest_response => {

                vm.is_disabled = false;
                vm.is_display_loader = '0';
                if (rest_response.success) {
                    vm[form_name] = rest_response.data;
                    if(settingType == 'company_setting') {
                        vm.$refs.avatarRef.clearFiles();
                        /* if(rest_response.data.company_phone_country){
                            vm.bookingpress_tel_input_props.defaultCountry = rest_response.data.company_phone_country;
                            vm.$refs.bpa_tel_input_field._data.activeCountryCode = rest_response.data.company_phone_country;
                            vm.company_setting_form.company_phone_country = rest_response.data.company_phone_country;
                        } */
                        if(rest_response.data.company_avatar_url){
                            vm.company_setting_form.company_avatar_url = rest_response.data.company_avatar_url;
                            vm.company_setting_form.company_avatar_img = rest_response.data.company_avatar_img;
                        }
                    }

                    if(settingType == 'general_setting') {
                        if(rest_response.data.default_phone_country_code){
                            vm.bookingpress_tel_input_settings_props.defaultCountry = rest_response.data.default_phone_country_code;
                            vm.general_setting_form.default_phone_country_code = rest_response.data.default_phone_country_code;
                        }
                    }

                    wp.hooks.doAction( 'bookingpress_get_settings_details_response', vm, rest_response, settingType );
                    vm.is_display_tab_loader = "0";
                }

            })
            .catch(error => {
                console.error('Error fetching settings data:', error);
            });
        },

        saveSettingsData(form_name, setting_type, display_save_msg = true){
            const vm = this;
            if(form_name == "general_setting_form"){
                if("undefined" != typeof vm.general_setting_form.default_date_format){
                    vm.bpa_date_common_date_format = vm.general_setting_form.default_date_format;
                    let bpa_common_date_time_format = vm.general_setting_form.default_date_format + ' HH:mm';
                    vm.bpa_date_time_common_date_format = bpa_common_date_time_format;
                }
            }

            const processSaveRequest = () => {
                vm.is_disabled = true;
                vm.is_display_save_loader = '1';
                let rawFormData = vm[form_name];
                let saveFormData = {};
                if (Array.isArray(rawFormData)) {
                    Object.keys(rawFormData).forEach(function(key) {
                        saveFormData[key] = rawFormData[key];
                    });
                } else {
                    saveFormData = Object.assign({}, rawFormData);
                }
                saveFormData = wp.hooks.applyFilters('bookingpress_add_settings_more_postdata', saveFormData, form_name, vm)
                saveFormData.settingType = setting_type;
                fetch(BookingPressConfig.rest_url + '/settings/save', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce,
                    },
                    body: JSON.stringify(saveFormData)
                })
                .then(response => response.json())
                .then(rest_response => {
                    vm.is_disabled = false;
                    vm.is_display_save_loader = '0';
                    if(true == display_save_msg || "error" == rest_response.variant){
                        vm.$notify({
                            title: rest_response.title,
                            message: rest_response.msg,
                            type: rest_response.variant,
                            customClass: rest_response.variant + '_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    }
                    vm.isloading = false;
                    vm.toggleBusy();

                    wp.hooks.doAction( 'bookingpress_settings_response', vm, rest_response );
                })
                .catch(error => {
                    vm.is_disabled = false;
                    vm.is_display_save_loader = '0';
                    console.log(error);
                });
            };
            if(form_name == "customer_setting_form"){
                processSaveRequest();
            } else {
                vm.$refs[form_name].validate((valid) => {
                    if(valid){
                        processSaveRequest();
                    }
                });
            }
        }, 

        bookingpress_timesolts_afternoon_grouping( timing){
            const vm = this;
            vm.general_setting_form.bpa_evening_start_time = "";
            vm.general_setting_form.bpa_night_start_time = "";
        },  
        bookingpress_timesolts_evening_grouping(){
            const vm = this;
            vm.general_setting_form.bpa_night_start_time = "";
        },
        bpa_update_show_time_as_per_service_duration(){
            const vm = this;
            wp.hooks.doAction( 'bookingpress_update_time_slots_as_per_service_duration', vm );
        },
        bookingpress_general_tab_phone_country_change_func(bookingpress_country_obj_gst)
        {
            const vm = this;
            var bookingpress_general_tab_selected_country = bookingpress_country_obj_gst.iso2
            
            /* let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_general_tab_selected_country, true, 1 );
            if( '' != exampleNumber ){
                vm.bookingpress_tel_input_settings_props.inputOptions.placeholder = exampleNumber;
            } */
            vm.general_setting_form.default_phone_country_code = bookingpress_general_tab_selected_country
        },
        bookingpress_phone_country_change_func(bookingpress_country_obj) {
            const vm = this
            var bookingpress_selected_country = bookingpress_country_obj.iso2
            vm.company_setting_form.company_phone_country = bookingpress_selected_country
            //vm.customer.customer_phone_dial_code = bookingpress_country_obj.dialCode;
        },
        /* bookingpress_phone_country_change_func(bookingpress_country_obj){
            const vm = this
            var bookingpress_selected_country = bookingpress_country_obj.iso2
            let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_selected_country, true, 1 );
            if( '' != exampleNumber ){
                vm.bookingpress_tel_input_props.inputOptions.placeholder = exampleNumber;
            }
            vm.company_setting_form.company_phone_country = bookingpress_selected_country
        }, */
        bookingpress_upload_company_avatar_func(response, file, fileList){
            const vm2 = this
            if(response != ''){
                vm2.company_setting_form.company_avatar_url = response.upload_url
                vm2.company_setting_form.company_avatar_img = response.upload_file_name
            }
        },
        bookingpress_company_avatar_upload_limit(files, fileList){
            const vm2 = this
            if(files.length >= 1){
                vm2.$notify({
                    title: 'Error',
                    message: 'Multiple files not allowed',
                    type: 'error',
                    customClass: 'error_notification',
                    duration:BookingPressConfig.notification_timeout,
                });
            }
        },
        checkUploadedFile(file){
            const vm2 = this
            if(file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp'){
                vm2.$notify({
                    title: 'Error',
                    message: 'Please upload jpg/png file only',
                    type: 'error',
                    duration:BookingPressConfig.notification_timeout,
                });
                return false;
            }else{
                var bpa_image_size = parseInt(file.size / 1000000);
                if(bpa_image_size > 1){
                    vm2.$notify({
                        title: 'Error',
                        message: 'Please upload maximum 1 MB file only',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:BookingPressConfig.notification_timeout,
                    });                    
                    return false
                }
            }
        },
        bookingpress_company_avatar_upload_err(err, file, fileList){
            const vm2 = this
            var bookingpress_err_msg = 'Something went wrong'
            if(err != '' || err != undefined){
                bookingpress_err_msg = err
            }
            vm2.$notify({
                title: 'Error',
                message: bookingpress_err_msg,
                type: 'error',
                customClass: 'error_notification',
                duration:BookingPressConfig.notification_timeout,
            });
        },       
        async bookingpress_remove_company_avatar() {
            const vm = this;
            const upload_url = vm.company_setting_form.company_avatar_url;
            const upload_filename = vm.company_setting_form.company_avatar_img;

            try {
                
                const response = await fetch(BookingPressConfig.rest_url + '/settings/remove_company_avatar', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce,
                    },
                    body: JSON.stringify({
                        upload_file_url: upload_url,
                        upload_filename: upload_filename
                    }),
                });

                const data = await response.json();                
                vm.company_setting_form.company_avatar_url = '';
                vm.company_setting_form.company_avatar_img = '';
                vm.$refs.avatarRef.clearFiles();

            } catch (error) {
                console.log(error);
            }

        },
        filtered_start_timings(work_hours_day) {
            return work_hours_day.worktimes.filter(work_timings => {
                return (
                    work_timings.start_time !== this.workhours_timings[work_hours_day.day_name].end_time || this.workhours_timings[work_hours_day.day_name].end_time === 'Off'
                );
            });
        },

        filtered_end_timings(work_hours_day) {
            return work_hours_day.worktimes.filter(work_timings => {
                return (
                    work_timings.end_time > this.workhours_timings[work_hours_day.day_name].start_time || work_timings.end_time === '24:00:00'
                );
            });
        },
        bookingpress_check_workhour_value(workhour_time,work_hour_day) {    
            if(workhour_time == 'Off') {
                const vm = this
                vm.workhours_timings[work_hour_day].start_time = 'Off';
            }
        },
        bookingpress_set_workhour_value(worktime,work_hour_day) {
            const vm = this                
            if(vm.workhours_timings[work_hour_day].end_time == 'Off') {                    
                vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
                    if(currentValue.day_name == work_hour_day) {
                        currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
                            if(currentValue2.start_time == worktime) {
                                vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
                            }
                        });
                    }
                });                
            } else if(worktime >= vm.workhours_timings[work_hour_day].end_time ) {
                vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
                    if(currentValue.day_name == work_hour_day) {                       
                        currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
                            if(currentValue2.start_time == worktime) {
                                vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
                            }
                        });
                    }
                });
            } else if(worktime != 'off' && vm.workhours_timings[work_hour_day].end_time == undefined) {
                vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
                    if(currentValue.day_name == work_hour_day) {                       
                        currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
                            if(currentValue2.start_time == worktime) {
                                vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
                            }
                        });
                    }
                });
            }
        },            
        open_smtp_error_modal() {                
            const vm= this;
            vm.smtp_error_modal = true;
        },
        close_smtp_error_modal(){
            const vm= this;
            vm.smtp_error_modal = false;    
        },
        open_add_break_modal_func(currentElement, breakSelectedDay){
            const vm = this;
            var dialog_pos = currentElement.target.getBoundingClientRect();
            vm.break_modal_pos = (dialog_pos.top + 40)+'px'
            vm.break_modal_pos_right = (dialog_pos.right + 38)+'px';
            vm.open_add_break_modal = true          
            vm.$nextTick(() => {
                vm.$refs['break_timings'].resetFields()
            })
            vm.reset_edit_break_form()
            vm.break_selected_day = breakSelectedDay
            vm.bpa_adjust_popup_position( currentElement, 'div#breaks_add_modal.bp-dialog.bpa-dialog--add-break' );
        },
        customer_field_setting_popup(currentElement, customer_field_data){
            customer_field_data.is_edit = !customer_field_data.is_edit;     
            const vm = this; 
            //vm.bpa_adjust_popup_position( currentElement, 'div.bp-ui-popover section.bpa-field-settings-edit-container' );
            //vm.bpa_adjust_popup_position( currentElement, '.bpa-field-settings-' + fskey );
        },
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
                        console.log( dialog__wrapper );
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
        loadAttributes() {
            const vm = this;
            let daysoff_load_api_endpoint = wp.hooks.applyFilters('bookingpress_daysoff_load_api_endpoint', '/settings/load_daysoff_details');
            vm.is_display_tab_loader = "1";
            const response = fetch(rest_url + daysoff_load_api_endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify({
                    selected_year: vm.daysoff_selected_year
                })
            })
            .then( response => response.json() )
            .then( response => {
                if (response.variant == 'error') {
                    vm.$notify({
                        title: response.title,
                        message: response.msg,
                        type: response.variant,
                        customClass: response.variant + '_notification',
                        duration:BookingPressConfig.notification_timeout,
                    });
                } else {
                    vm.days = response.daysoff_data;
                }
                vm.holiday_range_temp.start = '';
                vm.holiday_range_temp.end = '';
                vm.holiday_range_possible_end_date = '';
                vm.is_display_tab_loader = "0";

            }).catch( error => {
                vm.is_display_tab_loader = "0";
            });
            
        },
        loadCalendarDates(selected_year = new Date().getFullYear()) {
            const vm = this;
            if (!vm.dayoffCalendarMounted) {
                return;
            }
            for (let i = 0; i <= 11; i++) {
                const calendarInstance = vm.dayoffCalendarMounted[i];
                if (calendarInstance && typeof calendarInstance.move === 'function') {
                    calendarInstance.move({
                        month: i + 1,
                        year: selected_year,
                    });
                } else if (calendarInstance && calendarInstance.vm && typeof calendarInstance.vm.move === 'function') {
                    calendarInstance.vm.move({
                        month: i + 1,
                        year: selected_year,
                    });
                }
            }
        },
        bookingpress_daysoff_selected_year(selectedValue) {
            const vm = this;
            if (selectedValue != undefined) {
                var bookingpress_selected_date_obj = new Date(selectedValue);
                var bookingpress_selected_year = bookingpress_selected_date_obj.getFullYear();
                vm.daysoff_selected_year = bookingpress_selected_year;
                this.loadCalendarDates(bookingpress_selected_year);
                this.loadAttributes();
            }
        },
        onDragHolidayCalendar(day){
            const vm = this;
            var selected_date = day.id;                
            if(vm.holiday_range_temp.start != '' && vm.holiday_range_temp.end == ''){
                vm.holiday_range_possible_end_date = selected_date;
            }else{
                vm.holiday_range_possible_end_date = '';
            }                
        },
        onDayClick(day) {
            const vm = this
            var is_edit = 0;
            var selected_date = day.id;
            var edit_dayoff_id = 0;
            vm.days_off_form.selected_date = selected_date;
            vm.days_off_form.selected_end_date = selected_date; 
            vm.days_off_form.daysoff_title = '';
            vm.days_off_form.is_repeat_days_off = false;
            vm.days.forEach(function(item, index, arr){
                if(item.id == selected_date){
                    is_edit = 1
                    vm.days_off_form.daysoff_title = item.off_name;
                    edit_dayoff_id  = item.dayoff_id; 
                    if(item.class == 'bpa-daysoff-calendar-col--item__highlight--yearly bpa_selected_daysoff'){
                        vm.days_off_form.is_repeat_days_off = true
                    }
                }
            });

            vm.days_off_form.dayoff_id = edit_dayoff_id; 
            vm.days_off_form.is_edit = is_edit                
            vm.open_add_daysoff_details = true                
            var dialog_pos_x = day.el.getBoundingClientRect().left - 253;
            var dialog_pos_y = day.el.getBoundingClientRect().top + 40;
            
            /* <?php if( is_rtl() ){ ?>
                var dialog_pos_x = day.el.getBoundingClientRect().left - 38;
            <?php } ?> */
            
            vm.$el.querySelector('#add_holiday_model_dialog .bp-dialog.bpa-add-dayoff-dialog').style.position = 'absolute';
            vm.$el.querySelector('#add_holiday_model_dialog .bp-dialog.bpa-add-dayoff-dialog').style.marginTop = '0px';
            vm.$el.querySelector('#add_holiday_model_dialog .bp-dialog.bpa-add-dayoff-dialog').style.top = dialog_pos_y + 'px';
            vm.$el.querySelector('#add_holiday_model_dialog .bp-dialog.bpa-add-dayoff-dialog').style.left = dialog_pos_x + 'px';            
            
            if(is_edit != 1){
                const idx = vm.days.findIndex(d => d.id === day.id);
                if (idx >= 0) {
                    this.days.splice(idx, 1);
                } else {
                    this.days.push({
                        id: day.id,
                        date: day.date,
                        class: 'bpa-daysoff-calendar-col--item__highlight--single-dayoff'
                    });
                }
            }
        },
        handleSizeChange(val) {                
            const vm = this
            var log_type = vm.open_view_model_gateway_name
            this.perPage = intval(val)
            this.bookingpess_view_log(log_type)
            wp.hooks.doAction( 'bookingpress_dynamic_add_method_for_pagination_size_change', this );         
        },   
        handleCurrentChange(val) {
            const vm = this
            var log_type = vm.open_view_model_gateway_name
            this.currentPage = val;                
            this.bookingpess_view_log(log_type, 'pagination');
            wp.hooks.doAction( 'bookingpress_dynamic_add_method_for_pagination_length_change', this );             
        },
        save_daysoff_details(form_name) {
            const vm = this;
            const daysoff_form = vm.$refs[form_name];

            if (!daysoff_form || typeof daysoff_form.validate !== 'function') {
                return;
            }

            daysoff_form.validate(function(valid) {
                if (!valid) {
                    return;
                }

                vm.is_disabled = true;
                vm.is_display_save_loader = '1';

                let daysoff_api_endpoint = wp.hooks.applyFilters('bookingpress_daysoff_api_endpoint', '/settings/save_daysoff_details');

                fetch(rest_url + daysoff_api_endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce
                    },
                    body: JSON.stringify({
                        days_off_form: vm.days_off_form
                    })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    vm.$notify({
                        title: data.title,
                        message: data.msg,
                        type: data.variant,
                        customClass: data.variant + '_notification',
                        duration: BookingPressConfig.notification_timeout
                    });

                    if (data.variant === 'success') {
                        vm.open_add_daysoff_details = false;
                    }
                })
                .catch(function(error) {
                    console.log(error);
                    vm.$notify({
                        title: 'Error',
                        message: 'Something went wrong..',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                })
                .finally(function() {
                    vm.is_disabled = false;
                    vm.is_display_save_loader = '0';
                });
            });
        },
        delete_dayoff() {
            const vm = this;
            fetch(rest_url + '/settings/delete_daysoff_details', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify({
                    days_off_form: vm.days_off_form
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.variant == 'error') {
                    vm.$notify({
                        title: data.title,
                        message: data.msg,
                        type: data.variant,
                        customClass: data.variant + '_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }

                if (vm.days_off_form.is_edit == '1' && data.variant == 'success') {
                    vm.$notify({
                        title: data.title,
                        message: data.msg,
                        type: data.variant,
                        customClass: data.variant + '_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }
                vm.open_add_daysoff_details = false;
            })
            .catch(function(error) {
                console.log(error);
            });
        },
        bookingpress_enable_modal(){
            document.body.style.overflow = 'hidden';
        },
        bookingpress_disable_modal(){
            if(document.body.classList.contains("bp-popup-parent--hidden")){
                document.body.classList.remove("bp-popup-parent--hidden");
                document.body.style.paddingRight = "0px";
            }
            document.body.style.overflow = 'auto';
        },
        getDayoffDateValue(value) {
            return value ? String(value).slice(0, 10) : '';
        },
        findDayoffForDate(selected_date) {
            const vm = this;

            return vm.days.find(function(item) {
                const start_date = vm.getDayoffDateValue(item.date || item.start || item.id);
                const end_date = vm.getDayoffDateValue(item.end_date || item.end || item.date || item.id);

                return selected_date >= start_date && selected_date <= end_date;
            });
        },
        getDayoffClickElement(day, event) {
            if (event && event.currentTarget instanceof Element) {
                return event.currentTarget.classList.contains('vc-day-content')
                    ? event.currentTarget
                    : event.currentTarget.querySelector('.vc-day-content');
            }

            if (event && event.target instanceof Element) {
                return event.target.closest('.vc-day-content');
            }

            const candidates = document.querySelectorAll(`.id-${day.id}.in-month .vc-day-content`);
            return candidates.length ? candidates[0] : null;
        },
        positionDayoffDialog(day_element) {
            if (!day_element) {
                return;
            }

            const rect = day_element.getBoundingClientRect();
            const dialog_pos_x = rect.left - (BookingPressConfig.is_rtl ? 38 : 253);
            const dialog_pos_y = rect.top + 40;

            setTimeout(function() {
                const dialog = document.querySelector('.bp-dialog.bpa-add-dayoff-dialog');
                if (!dialog) {
                    return;
                }

                dialog.style.position = 'absolute';
                dialog.style.marginTop = '0px';
                dialog.style.top = dialog_pos_y + 'px';
                dialog.style.left = dialog_pos_x + 'px';
            }, 100);
        },
        onDayClickRange(day, event) {
            const vm = this;
            const selected_date = day.id;
            const selected_daysoff = vm.findDayoffForDate(selected_date);
            const day_element = vm.getDayoffClickElement(day, event);

            vm.days_off_form.is_repeat_days_off = false;

            if (selected_daysoff) {
                if (vm.holiday_range_temp.start !== '') {
                    vm.holiday_range_temp.start = '';
                    vm.holiday_range_temp.end = '';
                    vm.holiday_range_possible_end_date = '';
                    return;
                }

                vm.days_off_form.daysoff_title = selected_daysoff.off_name;
                vm.days_off_form.selected_date = vm.getDayoffDateValue(selected_daysoff.date);
                vm.days_off_form.selected_end_date = vm.getDayoffDateValue(selected_daysoff.end_date || selected_daysoff.date);
                vm.days_off_form.dayoff_id = selected_daysoff.dayoff_id;
                vm.days_off_form.is_edit = 1;
                vm.days_off_form.is_repeat_days_off = String(selected_daysoff.class).indexOf('__highlight--yearly') !== -1;
                wp.hooks.doAction('bookingpress_daysoff_external_js_data', vm, selected_daysoff);
                vm.open_add_daysoff_details = true;
                vm.positionDayoffDialog(day_element);
                return;
            }

            if (vm.holiday_range_temp.start === '') {
                vm.holiday_range_temp.start = selected_date;
                vm.holiday_range_possible_end_date = selected_date;
                return;
            }

            const range_dates = [vm.holiday_range_temp.start, selected_date].sort();
            const start_date = range_dates[0];
            const end_date = range_dates[1];

            vm.holiday_range_temp.start = start_date;
            vm.holiday_range_temp.end = end_date;
            vm.holiday_range_possible_end_date = end_date;
            vm.days_off_form.daysoff_title = '';
            vm.days_off_form.is_repeat_days_off = false;
            vm.days_off_form.selected_date = start_date;
            vm.days_off_form.selected_end_date = end_date;
            vm.days_off_form.dayoff_id = 0;
            vm.days_off_form.is_edit = 0;
            wp.hooks.doAction('bookingpress_daysoff_external_js_data', vm, null);
            vm.open_add_daysoff_details = true;
            vm.positionDayoffDialog(day_element);
        },
        bookingpress_disable_dayoff_modal() {
            const vm = this;
            vm.holiday_range_temp.start = "";
            vm.holiday_range_temp.end = "";
            vm.holiday_range_possible_end_date = "";
            vm.loadAttributes();
            if(document.body.classList.contains("bp-popup-parent--hidden")){
                document.body.classList.remove("bp-popup-parent--hidden");
                document.body.style.paddingRight = "0px";
            }
            document.body.style.overflow = "auto";
        },
        bookingpress_check_currency_status(value) {
            const vm = this;
            fetch(rest_url + '/settings/check_currency_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce             
                },
                body: JSON.stringify({
                    bookingpress_currency: value
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.msg != '') {
                    vm.bookingpress_currency_warnning = '1';
                    vm.bookingpress_currency_warnning_msg = data.msg;
                } else {
                    vm.bookingpress_currency_warnning = '0';
                }
            })
            .catch(function(error) {
                console.log(error);
                vm.$notify({
                    title: 'Error',
                    message: 'Something went wrong..',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
            });
        },
        bookingpress_send_test_wpmail_email(){
            const vm = this;
            vm.$refs['notification_wpmail_test_mail_form'].validate(function(valid) {
                if (valid) {
                    vm.is_disabled = true;
                    vm.is_display_send_test_wpmail_mail_loader = '1';
                    vm.is_disable_send_test_wpmail_email_btn = true;
                    fetch(rest_url + '/settings/send_test_wpmail_email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce
                        },
                        body: JSON.stringify({
                            notification_formdata: vm.notification_setting_form,
                            notification_test_mail_formdata: vm.notification_wpmail_test_mail_form
                        })
                    })
                    .then(function(response) { return response.json();})
                    .then(function(data) {
                        vm.is_disabled = false;
                        vm.is_display_send_test_wpmail_mail_loader = '0';
                        vm.is_disable_send_test_wpmail_email_btn = false;

                        if (data.is_mail_sent == 1) {
                            vm.succesfully_send_test_wpmail_email = 1;
                            vm.error_send_test_wpmail_email = 0;
                            vm.wpmail_mail_error_text = '';
                            vm.error_text_of_test_wpmail_email = '';
                        } else {
                            vm.succesfully_send_test_wpmail_email = 0;
                            vm.error_send_test_wpmail_email = 1;
                            vm.error_text_of_test_wpmail_email = data.error_msg;
                            vm.wpmail_mail_error_text = data.error_log_msg;
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        vm.is_disabled = false;
                        vm.is_display_send_test_wpmail_mail_loader = '0';
                        vm.is_disable_send_test_wpmail_email_btn = false;
                        vm.$notify({
                            title: 'Error',
                            message: 'Something went wrong..',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    });
                }
            });
        },
        bookingpress_send_test_email(){
            const vm = this;
            vm.$refs['notification_smtp_test_mail_form'].validate(function(valid) {
                if (valid) {
                    vm.is_disabled = true;
                    vm.is_display_send_test_mail_loader = '1';
                    vm.is_disable_send_test_email_btn = true;
                    fetch(rest_url + '/settings/send_test_smtp_email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce
                        },
                        body: JSON.stringify({
                            notification_formdata: vm.notification_setting_form,
                            notification_test_mail_formdata: vm.notification_smtp_test_mail_form
                        })
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        vm.is_disabled = false;
                        vm.is_display_send_test_mail_loader = '0';
                        vm.is_disable_send_test_email_btn = false;

                        if (data.is_mail_sent == 1) {
                            vm.succesfully_send_test_email = 1;
                            vm.error_send_test_email = 0;
                            vm.smtp_mail_error_text = '';
                            vm.error_text_of_test_email = '';
                        } else {
                            vm.succesfully_send_test_email = 0;
                            vm.error_send_test_email = 1;
                            vm.error_text_of_test_email = data.error_msg;
                            vm.smtp_mail_error_text = data.error_log_msg;
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        vm.is_disabled = false;
                        vm.is_display_send_test_mail_loader = '0';
                        vm.is_disable_send_test_email_btn = false;
                        vm.$notify({
                            title: 'Error',
                            message: 'Something went wrong..',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    });
                }
            });
        },
        bookingpress_send_test_gmail_email(){
            const vm = this;

            vm.$refs['notification_gmail_test_mail_form'].validate(function(valid) {
                if (valid) {
                    vm.is_disabled = true;
                    vm.is_display_send_test_gmail_mail_loader = '1';
                    vm.is_disable_send_test_gmail_email_btn = true;

                    fetch(rest_url + '/settings/send_test_gmail_email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce
                        },
                        body: JSON.stringify({
                            notification_formdata: vm.notification_setting_form,
                            notification_test_mail_formdata: vm.notification_gmail_test_mail_form
                        })
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        vm.is_disabled = false;
                        vm.is_display_send_test_gmail_mail_loader = '0';
                        vm.is_disable_send_test_gmail_email_btn = false;

                        if (data.is_mail_sent == 1) {
                            vm.succesfully_send_test_gmail_email = 1;
                            vm.error_send_test_gmail_email = 0;
                            vm.gmail_mail_error_text = '';
                            vm.error_text_of_test_gmail_email = '';
                        } else {
                            vm.succesfully_send_test_gmail_email = 0;
                            vm.error_send_test_gmail_email = 1;
                            vm.error_text_of_test_gmail_email = data.error_msg;
                            vm.gmail_mail_error_text = data.error_log_msg;
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        vm.is_disabled = false;
                        vm.is_display_send_test_gmail_mail_loader = '0';
                        vm.is_disable_send_test_gmail_email_btn = false;
                        vm.$notify({
                            title: 'Error',
                            message: 'Something went wrong..',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    });
                }
            });
        },
        bookingpress_gmail_api_check(){
            const vm = this;

            if( vm.notification_setting_form.gmail_client_secret != '' && vm.notification_setting_form.gmail_client_ID != '' ){

                var bkp_gmail_id = vm.notification_setting_form.gmail_client_ID;
                var bkp_gmail_secret = vm.notification_setting_form.gmail_client_secret;
                
                let url = 'https://accounts.google.com/o/oauth2/auth';

                let oauth_url = url + '?response_type=code&access_type=offline&client_id='+bkp_gmail_id+'&redirect_uri='+vm.bookingpress_gmailapi_redirect_uri+'&state='+vm.gmail_oauth_state+'&scope=https://mail.google.com/&approval_prompt=force&include_granted_scopes=false';

                console.log(oauth_url)

                window.open( oauth_url, 'BookingPress Gmail API Authentication', 'height=500, width=500');
            }  
        },
        bookingpress_gmail_api_remove(auth_token, auth_email, auth_response){
            const vm = this;
            if (auth_token == '') {
                return false;
            } 
            fetch(rest_url + '/settings/signout_google_account', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify({
                    access_token: auth_token,
                    auth_email: auth_email,
                    access_token_data: auth_response
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                 if (data.variant == "success") {
                    vm.notification_setting_form.bookingpress_gmail_auth_token = '';
                    vm.notification_setting_form.bookingpress_response_email = '';
                    vm.notification_setting_form.bookingpress_gmail_auth = '';
                 }
            })
            .catch(function(error) {
                 console.log(error);
            });
        },
        bookingpess_view_log(log_type, request_from='', log_name='') {
            request_from = request_from || '';
            log_name = log_name || '';
            const vm = this;
            vm.open_display_log_modal = true;
            vm.is_display_loader_view = '1';

            if (request_from != 'pagination') {
                vm.items = '';
            }

            vm.open_view_model_gateway_name = log_type;
            if (log_name != '') {
                vm.open_view_model_gateway = log_name;
            }

            fetch(rest_url + '/settings/view_debug_payment_log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify({
                    bookingpress_debug_log_selector: log_type,
                    perpage: parseInt(vm.perPage),
                    currentpage: vm.currentPage
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                vm.is_display_loader_view = '0';
                vm.items = data.items;
                vm.totalItems = data.total;
            })
            .catch(function(error) {
                console.log(error);
                vm.is_display_loader_view = '0';
                vm.$notify({
                    title: 'Error',
                    message: 'Something went wrong..',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            });
        },
        bookingpress_trim_value(input_value){
            input_value = input_value.trim()
            this.days_off_form['daysoff_title'] = input_value
        },
        edit_workhour_data(currentElement,break_start_time, break_end_time, day_name,index){
            const vm = this                
            vm.reset_edit_break_form()
            var dialog_pos = currentElement.target.getBoundingClientRect();
            vm.break_modal_pos = (dialog_pos.top - 8)+'px'
            vm.break_modal_pos_right = (dialog_pos.right + 38)+'px';                
            vm.break_timings.start_time = break_start_time
            vm.break_timings.end_time = break_end_time
            vm.break_timings.edit_index = index
            vm.is_edit_break= 1;
            vm.open_add_break_modal = true
            vm.break_selected_day = day_name

            vm.bpa_adjust_popup_position( currentElement, 'div#breaks_add_modal .bp-dialog.bpa-dialog--add-break', 'bpa-bh__item' );
        },
        reset_edit_break_form(){
            const vm = this
            vm.break_timings.start_time = ''
            vm.break_timings.end_time = ''
            vm.break_timings.edit_index = ''
            vm.is_edit_break = 0
        },
        delete_breakhour(start_time, end_time, selected_day){
            const vm = this
            vm.selected_break_timings[selected_day].forEach(function(currentValue, index, arr){
                if(currentValue.start_time == start_time && currentValue.end_time == end_time)
                {
                    vm.selected_break_timings[selected_day].splice(index, 1);
                }
            });
        },
        bookingpress_gmail_insert_placeholder( event) {
            const vm = this
            var bookingpress_selected_placholder = event
            var bookingpress_dummy_elem = document.createElement("textarea");
            document.body.appendChild(bookingpress_dummy_elem);
            bookingpress_dummy_elem.value = bookingpress_selected_placholder;
            bookingpress_dummy_elem.select();
            document.execCommand("copy");
            document.body.removeChild(bookingpress_dummy_elem);
            vm.$notify({ title: 'Success', message:vm.bookingpress_redirect_url_success_msg, type: 'success', customClass: 'success_notification',duration:BookingPressConfig.notification_timeout});
        },
        bookingpess_clear_bebug_log(log_type){
            const vm = this;
            vm.is_display_loader = '1';
            fetch(rest_url + '/settings/clear_debug_payment_log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify({
                    bookingpress_debug_log_selector: log_type
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                vm.is_display_loader = '0';
                vm.$notify({
                    title: data.title,
                    message: data.msg,
                    type: data.variant,
                    customClass: data.variant + '_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            })
            .catch(function(error) {
                vm.is_display_loader = '0';
                vm.$notify({
                    title: 'Error',
                    message: 'Something went wrong..',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            });
        },
        bookingpress_download_log(log_type, selected_download_duration, download_log_daterange){
            const vm = this;
            vm.is_display_download_save_loader = '1';
            vm.is_disabled = true;

            var body_data = {
                bookingpress_debug_log_selector: log_type,
                bookingpress_selected_download_duration: selected_download_duration
            };

            if (selected_download_duration == 'custom') {
                body_data.bookingpress_selected_download_custom_duration = download_log_daterange;
            }

            fetch(rest_url + '/settings/download_debug_payment_log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify(body_data)
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                window.location.href = data.url;
                vm.is_display_download_save_loader = '0';
                vm.is_disabled = false;
            })
            .catch(function(error) {
                console.log(error);
                vm.is_display_download_save_loader = '0';
                vm.is_disabled = false;
                vm.$notify({
                    title: 'Error',
                    message: 'Something went wrong..',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            });
        },          
        filtered_break_end_timings(day) {
            return this.default_break_timings.filter(break_times => {
                return (
                    break_times.start_time > this.workhours_timings[day].start_time &&
                    break_times.start_time < this.workhours_timings[day].end_time &&
                    break_times.start_time > this.break_timings.start_time
                );
            });
        },
        filtered_break_start_timings(day) {
            return this.default_break_timings.filter(break_times => {
                return (
                    break_times.start_time > this.workhours_timings[day].start_time &&
                    break_times.start_time < this.workhours_timings[day].end_time
                );
            });
        },
        savebreakdata(){
            const vm = this;
            var is_edit = 0;
            vm.$refs['break_timings'].validate((valid) => {
                if(valid) {
                    var update = 0;
                    if(vm.break_timings.start_time > vm.break_timings.end_time) {
                        vm.$notify({
                            title: 'Error',
                            message: 'Start time is not greater than End time',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    } else if(vm.break_timings.start_time == vm.break_timings.end_time) {
                        vm.$notify({
                            title: 'Error',
                            message: 'Start time & End time are not same',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    } else if(vm.selected_break_timings[vm.break_selected_day] != '') {
                        vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
                            if(is_edit == 0) {
                                if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {
                                    is_edit = 1;
                                    vm.$notify({
                                        title: 'Error',
                                        message: 'Please enter valid time for break',
                                        type: 'error',
                                        customClass: 'error_notification',
                                        duration: BookingPressConfig.notification_timeout,
                                    });
                                } else if(currentValue['start_time'] == vm.break_timings.start_time && currentValue['end_time'] == vm.break_timings.end_time && (vm.break_timings.edit_index != index || vm.is_edit_break == 0)) {
                                    is_edit = 1;
                                    vm.$notify({
                                        title: 'Error',
                                        message: 'Break time already added',
                                        type: 'error',
                                        customClass: 'error_notification',
                                        duration: BookingPressConfig.notification_timeout,
                                    });
                                } else if(((currentValue['start_time'] < vm.break_timings.start_time && currentValue['end_time'] > vm.break_timings.start_time) || (currentValue['start_time'] < vm.break_timings.end_time && currentValue['end_time'] > vm.break_timings.end_time) || (currentValue['start_time'] > vm.break_timings.start_time && currentValue['end_time'] <= vm.break_timings.end_time) || (currentValue['start_time'] >= vm.break_timings.start_time && currentValue['end_time'] < vm.break_timings.end_time)) && (vm.break_timings.edit_index != index || vm.is_edit_break == 0)) {
                                    is_edit = 1;
                                    vm.$notify({
                                        title: 'Error',
                                        message: 'Break time already added',
                                        type: 'error',
                                        customClass: 'error_notification',
                                        duration: BookingPressConfig.notification_timeout,
                                    });
                                }
                            }
                        });
                        if(is_edit == 0) {
                            let formatted_start_time = "";
                            let formatted_end_time = "";
                            let start_time_string = "";
                            let end_time_string = "";
                            vm.default_break_timings.forEach(function(currentValue, index, arr) {
                                if(currentValue.start_time_val == vm.break_timings.start_time) {
                                    formatted_start_time = currentValue.formatted_start_time;
                                    start_time_string = currentValue.formatted_start_time;
                                }
                                if(currentValue.end_time_val == vm.break_timings.end_time) {
                                    formatted_end_time = currentValue.formatted_end_time;
                                    end_time_string = currentValue.formatted_end_time;
                                }
                            });
                            if(vm.break_selected_day != '' && vm.is_edit_break != 0) {
                                vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
                                    if(index == vm.break_timings.edit_index) {
                                        currentValue.start_time = vm.break_timings.start_time;
                                        currentValue.end_time = vm.break_timings.end_time;
                                        currentValue.formatted_start_time = formatted_start_time;
                                        currentValue.formatted_end_time = formatted_end_time;
                                    }
                                });
                            } else {
                                vm.selected_break_timings[vm.break_selected_day].push({
                                    start_time: vm.break_timings.start_time,
                                    end_time: vm.break_timings.end_time,
                                    formatted_start_time: formatted_start_time,
                                    formatted_end_time: formatted_end_time,
                                    start_time_string: start_time_string,
                                    end_time_string: end_time_string
                                });
                            }
                            vm.close_add_break_model();
                        }
                    } else {
                        if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {
                            vm.$notify({
                                title: 'Error',
                                message: 'Please enter valid time for break',
                                type: 'error',
                                customClass: 'error_notification',
                                duration: BookingPressConfig.notification_timeout,
                            });
                        } else {
                            let formatted_start_time = "";
                            let formatted_end_time = "";
                            let start_time_string = "";
                            let end_time_string = "";
                            vm.default_break_timings.forEach(function(currentValue, index, arr) {
                                if(currentValue.start_time_val == vm.break_timings.start_time) {
                                    formatted_start_time = currentValue.formatted_start_time;
                                    start_time_string = currentValue.formatted_start_time;
                                }
                                if(currentValue.end_time_val == vm.break_timings.end_time) {
                                    formatted_end_time = currentValue.formatted_end_time;
                                    end_time_string = currentValue.formatted_end_time;
                                }
                            });
                            vm.selected_break_timings[vm.break_selected_day].push({
                                start_time: vm.break_timings.start_time,
                                end_time: vm.break_timings.end_time,
                                formatted_start_time: formatted_start_time,
                                formatted_end_time: formatted_end_time,
                                start_time_string: start_time_string,
                                end_time_string: end_time_string
                            });
                            vm.close_add_break_model();
                        }
                    }
                }
            });
        },
        close_add_break_model() {
            const vm = this
            vm.$refs['break_timings'].resetFields()
            vm.reset_edit_break_form()                
            vm.open_add_break_modal = false;
        },
        saveEmployeeWorkhours(){
            const vm = this;
            vm.is_disabled = true;
            vm.is_display_save_loader = '1';
            fetch(rest_url + '/settings/save_default_work_hours', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify({
                    workhours_timings: vm.workhours_timings,
                    break_data: vm.selected_break_timings
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                vm.is_disabled = false;
                vm.is_display_save_loader = '0';
                vm.$notify({
                    title: data.title,
                    message: data.msg,
                    type: data.variant,
                    customClass: data.variant + '_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
                vm.reset_edit_break_form();
            })
            .catch(function(error) {
                vm.is_disabled = false;
                vm.is_display_save_loader = '0';
                console.log(error);
                vm.$notify({
                    title: 'Error',
                    message: 'Something went wrong..',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            });
        },        
        mountDaysoffCalendars() {
            const vm = this;

            vm.$nextTick(() => {
                vm.$nextTick(() => {

                    const run = () => {

                        const Bridge = window.BpVCalendar || null;

                        if (!Bridge || typeof Bridge.mountCalendar !== 'function') {
                            console.warn('BpVCalendar bridge is not loaded.');
                            return;
                        }

                        // Prevent duplicate mounting
                        if (!vm.dayoffCalendarMounted) {
                            vm.dayoffCalendarMounted = [];
                        }

                        for (let i = 0; i < 12; i++) {

                            const refName = 'dayoff_calendar_' + i;

                            let host = vm.$refs[refName];

                            // Vue 3 refs inside v-for return array
                            if (Array.isArray(host)) {
                                host = host[0];
                            }

                            if (!host) {
                                console.warn(`Calendar host not found: ${refName}`);
                                continue;
                            }

                            // Skip already mounted
                            if (vm.dayoffCalendarMounted[i]) {
                                continue;
                            }

                            const month = i + 1;

                            const initialProps = {
                                view: 'monthly',
                                navVisibility: 'hidden',
                                initialPage: {
                                    month: month,
                                    year: parseInt(vm.daysoff_default_year, 10),
                                },
                                firstDayOfWeek: parseInt(vm.first_day_of_week, 10) || 1,
                                locale: vm.normalizeVCalendarLocale(vm.site_locale),
                                timezone: vm.daysoff_timezone,
                                class: 'bpa-daysoff-calendar-col--item',
                                attributes: vm.attributes_range,
                                masks: {
                                    weekdays: 'WW',
                                    title: 'MMMM YYYY',
                                },
                                disablePageSwipe: true,
                            };

                            const handlers = {
                                onDayClick(day, event) {
                                    vm.onDayClickRange(day, event);

                                    vm.$nextTick(() => {
                                        vm.updateDaysoffCalendarAttributes(vm.attributes_range);
                                    });
                                },
                                onDayMouseEnter(day) {
                                    vm.onDragHolidayCalendar(day);
                                },
                            };

                            const mounted = Bridge.mountCalendar(
                                host,
                                initialProps,
                                handlers
                            );

                            if (mounted) {
                                vm.dayoffCalendarMounted[i] = mounted;
                            }
                        }
                    };

                    if (typeof requestAnimationFrame === 'function') {
                        requestAnimationFrame(run);
                    } else {
                        run();
                    }
                });
            });
        },
        ymdToDate(value) {
            if (!value) return null;

            const s = String(value).slice(0, 10);
            const parts = s.split('-');

            if (parts.length !== 3) return null;

            return new Date(
                parseInt(parts[0], 10),
                parseInt(parts[1], 10) - 1,
                parseInt(parts[2], 10)
            );
        },

        normalizeVCalendarLocale(value) {
            if (!value) return 'en';

            const locale = String(value).replace(/_/g, '-').trim();

            try {
                new Intl.DateTimeFormat(locale);
                return locale;
            } catch (e) {
                return 'en';
            }
        },
        bookingpress_remove_date_range_picker_focus() {
            if( 'undefined' != typeof BookingPressConfig && 'undefined' != typeof BookingPressConfig.is_wp_mobile && true == BookingPressConfig.is_wp_mobile ){
                const datepickerinput = document.querySelectorAll(".bp-range-input");
                if(typeof datepickerinput != "undefined"){
                    datepickerinput.forEach((dateItem) => {
                        dateItem.blur();
                    });                    
                }
            }
        },
    });

    const settingConfig = {
        /* setup(){
            watch(
                console.log( BookingPressSettings.selected_tab_name )
                /* () => this.selected_tab_name,
                (tab) => {
                    console.log( tab );
                } * /
            )
        }, */
        data() {

            const vm = this;

            let SettingConfigData = {
                // --- FROM PHP (settingsmoduleData) ---
                selected_tab_name: settingsmoduleData.selected_tab_name,
                bookingpress_redirect_url_success_msg: settingsmoduleData.bookingpress_redirect_url_success_msg,
                flags_img_url:                settingsmoduleData.flags_img_url,
                default_appointment_staus:    settingsmoduleData.default_appointment_staus,
                timepicker_options:           settingsmoduleData.timepicker_options,
                phone_countries_details:      settingsmoduleData.phone_countries_details,
                currency_countries:           settingsmoduleData.currency_countries,
                default_timeslot_options:     settingsmoduleData.default_timeslot_options,
                default_smtp_secure_options:  settingsmoduleData.default_smtp_secure_options,
                price_symbol_position_val:    settingsmoduleData.price_symbol_position_val,
                price_separator_vals:         settingsmoduleData.price_separator_vals,
                default_payment_method:       settingsmoduleData.default_payment_method,
                message_setting_form:         settingsmoduleData.message_setting_form,
                log_download_default_option:  settingsmoduleData.log_download_default_option,
                rules_dayoff:                 settingsmoduleData.rules_dayoff,
                rules_company:                settingsmoduleData.rules_company,
                rules_notification:           settingsmoduleData.rules_notification,
                rules_smtp_test_mail:         settingsmoduleData.rules_smtp_test_mail,
                rules_gmail_test_mail:        settingsmoduleData.rules_gmail_test_mail,
                rules_wpmail_test_mail:       settingsmoduleData.rules_wpmail_test_mail,
                rules_payment:                settingsmoduleData.rules_payment,
                rules_message:                settingsmoduleData.rules_message,
                rules_add_break:              settingsmoduleData.rules_add_break,
                days_off_rules:               settingsmoduleData.days_off_rules,
                perPage:                      settingsmoduleData.perPage,
                pagination_selected_length:   settingsmoduleData.pagination_selected_length,
                pagination_length:            settingsmoduleData.pagination_length,
                download_log_daterange:       settingsmoduleData.download_log_daterange,
                bookingpress_tel_input_settings_props: settingsmoduleData.bookingpress_tel_input_settings_props || { defaultCountry: 'us', inputOptions: { placeholder: '' }, validCharactersOnly: true },
                bookingpress_cmp_tel_input_settings_props: settingsmoduleData.bookingpress_cmp_tel_input_settings_props || { defaultCountry: 'us', inputOptions: { placeholder: '' }, validCharactersOnly: true },

                // --- STATIC JS ONLY ---
                is_display_loader:            '0',                
                is_display_tab_loader:        '0',   
                modal_loading:                false,
                is_disabled:                  false,
                is_display_save_loader:       '0',
                is_mask_display:              false,
                is_edit_break:                0,
                isloading:                    false,
                proper_body_class:            false,
                needHelpDrawer:               false,
                needHelpDrawerDirection:      'rtl',
                comShowFileList:              false,
                open_add_break_modal:         false,
                open_add_daysoff_details:     false,
                open_display_log_modal:       false,
                open_view_model_gateway:      '',
                open_view_model_gateway_name: '',
                succesfully_send_test_email:              0,
                error_send_test_email:                    0,
                error_text_of_test_email:                 '',
                is_disable_send_test_email_btn:           false,
                is_display_send_test_mail_loader:         '0',
                succesfully_send_test_gmail_email:        0,
                error_send_test_gmail_email:              0,
                error_text_of_test_gmail_email:           '',
                is_disable_send_test_gmail_email_btn:     false,
                is_display_send_test_gmail_mail_loader:   '0',
                is_disable_send_test_wpmail_email_btn:    false,
                is_display_send_test_wpmail_mail_loader:  '0',
                succesfully_send_test_wpmail_email:       0,
                error_send_test_wpmail_email:             0,
                smtp_mail_error_text:         '',
                smtp_error_modal:             false,
                gmail_mail_error_text:        '',
                bookingpress_currency_warnning_msg: '',
                bookingpress_currency_warnning:     '0',
                imageUrl:                     '',
                monday:                       'monday',
                add_work_hours_display:       '',
                work_type_modal:              'monday_work_hours',
                work_hours_days_arr:          [],
                work_start_time:              '',
                work_end_time:                '',
                final_work_hours_data:        [],
                days_off_year_filter:         new Date().getFullYear().toString(),
                daysoff_default_year:         new Date().getFullYear().toString(),
                daysoff_selected_year:        new Date().getFullYear().toString(),
                break_modal_pos:              '254px',
                break_modal_pos_right:        '',
                break_selected_day:           'Monday',
                default_break_timings:        [],
                days:                         [],
                items:                        [],
                multipleSelection:            [],
                totalItems:                   0,
                currentPage:                  1,
                pagination_length_val:        '10',
                select_download_log:          '7',
                edit_dayoff_name:             '',
                edit_dayoff_date:             '',
                edit_dayoff_repeat:           false,
                is_display_loader_view:       '0',
                is_display_download_save_loader: '0',
                rules_general:                {},

                default_pagination: [
                    { text: '10',  value: '10' },
                    { text: '20',  value: '20' },
                    { text: '50',  value: '50' },
                    { text: '100', value: '100' },
                    { text: '200', value: '200' },
                    { text: '300', value: '300' },
                    { text: '400', value: '400' },
                    { text: '500', value: '500' },
                ],

                workhours_timings: {
                    Monday:    { start_time: '09:00:00', end_time: '17:00:00' },
                    Tuesday:   { start_time: '09:00:00', end_time: '17:00:00' },
                    Wednesday: { start_time: '09:00:00', end_time: '17:00:00' },
                    Thursday:  { start_time: '09:00:00', end_time: '17:00:00' },
                    Friday:    { start_time: '09:00:00', end_time: '17:00:00' },
                    Saturday:  { start_time: 'Off',      end_time: 'Off' },
                    Sunday:    { start_time: 'Off',      end_time: 'Off' },
                },

                selected_break_timings: {
                    Monday: [], Tuesday: [], Wednesday: [],
                    Thursday: [], Friday: [], Saturday: [], Sunday: [],
                },

                modals: {
                    general_setting_modal:      false,
                    company_setting_modal:      false,
                    notification_setting_modal: false,
                    workhours_setting_modal:    false,
                    appointment_setting_modal:  false,
                    label_setting_modal:        false,
                    payment_setting_modal:      false,
                },

                break_timings: {
                    start_time: '',
                    end_time:   '',
                    edit_index: '',
                },

                days_off_form: {
                    dayoff_id:          0,
                    daysoff_title:      '',
                    is_repeat_days_off: false,
                    repeat_holiday_label: settingsmoduleData.repeat_holiday_label,
                    selected_date:      '',
                    selected_end_date:  '',
                    is_edit:            '',
                },

                general_setting_form: {
                    default_time_slot_step:            '30',
                    appointment_status:                '1',
                    onsite_appointment_status:         '2',
                    default_phone_country_code:        'us',
                    per_page_item:                     '20',
                    phone_number_mandatory:            false,
                    share_timeslot_between_services:   false,
                    use_already_loaded_vue:            false,
                    load_js_css_all_pages:             false,
                    show_time_as_per_service_duration: true,
                    default_time_slot:                 '30',
                    default_date_format:               'F j, Y',
                    general_setting_phone_number:      '',
                    anonymous_data:                    'false',
                    default_time_format:               'g:i a',
                    bpa_afternoon_start_time:          '12:00:00',
                    bpa_evening_start_time:            '16:00:00',
                    bpa_night_start_time:              '20:00:00',
                },

                company_setting_form: {
                    company_avatar_img:    '',
                    company_avatar_url:    '',
                    company_avatar_list:   [],
                    company_name:          '',
                    company_address:       '',
                    company_website:       '',
                    company_phone_country: 'us',
                    company_phone_number:  '',
                },

                // sender_name, sender_email, admin_email come from PHP (get_option)
                notification_setting_form: {
                    selected_mail_service:          'wp_mail',
                    sender_name:                    settingsmoduleData.notification_setting_form_sender_name,
                    sender_email:                   settingsmoduleData.notification_setting_form_sender_email,
                    admin_email:                    settingsmoduleData.notification_setting_form_admin_email,
                    success_url:                    '',
                    cancel_url:                     '',
                    smtp_host:                      '',
                    smtp_port:                      '',
                    smtp_secure:                    'Disabled',
                    smtp_username:                  '',
                    smtp_password:                  '',
                    gmail_client_ID:                '',
                    gmail_client_secret:            '',
                    gmail_redirect_url:             '',
                    gmail_auth_secret:              '',
                    bookingpress_gmail_auth:        '',
                    bookingpress_response_email:    '',
                    bookingpress_gmail_auth_token:  '',
                },

                notification_smtp_test_mail_form: {
                    smtp_test_receiver_email: '',
                    smtp_test_msg:            '',
                },

                notification_gmail_test_mail_form: {
                    gmail_test_receiver_email: '',
                    gmail_test_msg:            '',
                },

                notification_wpmail_test_mail_form: {
                    wpmail_test_receiver_email: '',
                    wpmail_test_msg:            '',
                },

                payment_setting_form: {
                    payment_default_currency:    'USD',
                    price_symbol_position:       'before',
                    price_separator:             'comma-dot',
                    price_number_of_decimals:    2,
                    on_site_payment:             true,
                    paypal_payment:              false,
                    paypal_payment_mode:         'sandbox',
                    paypal_payment_method_type:  'lagacy',
                    paypal_client_id:            '',
                    paypal_client_secret:        '',
                    paypal_merchant_email:       '',
                    paypal_api_username:         '',
                    paypal_api_password:         '',
                    paypal_api_signature:        '',
                    custom_comma_separator:      '',
                    custom_dot_separator:        '',
                },

                customer_setting_form: {
                    allow_wp_user_create: false,
                    allow_autologin_user: true,
                },

                debug_log_setting_form: {
                    on_site_payment: false,
                    paypal_payment:  false,
                },
                timeslots_grouping_list: settingsmoduleData.timeslots_grouping_list,
                upload_company_avatar_url: rest_url + '/settings/upload_company_avatar',   
                upload_headers: {
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },      
                daysoff_timezone: "UTC",
                first_day_of_week: settingsmoduleData.first_day_of_week,
                site_locale: settingsmoduleData.site_locale || 'en',
                bookingpress_alignment: settingsmoduleData.bookingpress_alignment || 'left',
                holiday_range_possible_end_date: "",
                holiday_range_temp: { start: '', end: ''},
                bpa_display_wpmail_notice: settingsmoduleData.bpa_display_wpmail_notice,
                bpa_wpmail_failed_msg_data: settingsmoduleData.bpa_wpmail_failed_msg_data,    
                
                is_display_export_loader: 0,
                export_log_data: '',
                continue_export_id: '',
                export_complete_msg: '',
                export_all_record: false,
                export_log_stop_id: '',
                export_last_download_file: '',
                last_export_file: '',

                migration_tool_form: {
                    ...settingsmoduleData.migration_tool_form,
                    import_file: [],
                    import_file_final: '',
                    import_data: '',
                    confirm_import_data: '',
                },
                is_display_import_loader: 0,
                import_log_data: '',
                continue_import_id: '',
                gmail_oauth_state: settingsmoduleData.gmail_oauth_state,
                bookingpress_gmailapi_redirect_uri: settingsmoduleData.bookingpress_gmailapi_redirect_uri,

                bpa_is_pro_active: settingsmoduleData.bpa_is_pro_active,
                dayoffCalendarMounted: null,
                dayoff_selected_date: '',
                bpa_date_common_date_format: settingsmoduleData.bpa_date_common_date_format,
                bookingpress_start_of_week: settingsmoduleData.bookingpress_start_of_week,
                disabledOtherDates: '',
                download_on_site_popover_visible: false,
                download_paypal_popover_visible: false,
                download_import_popover_visible: false,
                download_export_popover_visible: false,
                vue_tel_mode: 'international',
                vue_tel_auto_format: true,
            }

            SettingConfigData = wp.hooks.applyFilters('bookingpress_modify_settings_model_data', SettingConfigData);
            return SettingConfigData;
        },           
        mounted() {
             /* this.$nextTick(() => {
                    this.mountDaysoffCalendars();
                }); */
            document.onreadystatechange = () => {
                if (document.readyState == "complete") {
                    setTimeout(function(){
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
            this.bookingpress_settings_on_load_methods_func(); 
        },
        computed: {
            filtered_night_timeslots() {
                return this.timeslots_grouping_list.filter(slot => {
                    return (
                        this.general_setting_form.bpa_evening_start_time !== '' &&
                        slot.start_time > this.general_setting_form.bpa_evening_start_time
                    );
                });
            },
            filtered_evening_timeslots() {
                return this.timeslots_grouping_list.filter(slot => {
                    return slot.start_time > this.general_setting_form.bpa_afternoon_start_time;
                });
            },
            disablePastDates() {
                return {
                    disabledDate: (Time) => { 
                        var dd = String(Time.getDate()).padStart(2, '0');
                        var mm = String(Time.getMonth() + 1).padStart(2, '0');
                        var yyyy = Time.getFullYear();
                        var time = yyyy + '-' + mm + '-' + dd;

                        var disable_date = this.disabledOtherDates.indexOf(time) > -1; 
                        
                        var date = new Date();
                        date.setDate(date.getDate() - 1);
                        var disable_past_date = Time.getTime() < date.getTime();

                        if (disable_date == true) {
                            return disable_date;
                        } else {
                            return disable_past_date;
                        }
                    },
                    firstDayOfWeek: parseInt(settingsmoduleData.bookingpress_start_of_week),
                }
            },
            dates() {
                return this.days.map(day => ({
                    selected_date: day.date,
                    selected_end_date: day.end_date,
                    selected_class: day.class,
                }));
            },
            attributes_range() {
                const attributes_data = this.dates.map(date => ({
                    highlight: {
                        class: date.selected_class,
                    },
                    dates: {
                        start: date.selected_date,
                        end: date.selected_end_date,
                    },
                }));

                if (this.holiday_range_temp.start !== '') {
                    const possible_end_date = this.holiday_range_possible_end_date || this.holiday_range_temp.start;
                    const range_dates = [this.holiday_range_temp.start, possible_end_date].sort();
                    attributes_data.push({
                        highlight: true,
                        dates: {
                            start: range_dates[0],
                            end: range_dates[1],
                        },
                    });
                }

                return attributes_data;
            },

            ...BookingPressSettingsExternalComputedMethods
        },
        methods: {
            settings_tab_select(selected_tab){
                
                const vm = this
                sessionStorage.setItem("selected_tab", selected_tab.index)                
                vm.open_add_break_modal = false;
                const current_tabname = selected_tab.props?.name || selected_tab.paneName;
                sessionStorage.setItem("current_tabname", current_tabname)
                //vm.bpa_set_read_more_link();

                if(current_tabname == "general_settings"){
                    vm.getSettingsData('general_setting', 'general_setting_form')
                    if(vm.bpa_is_pro_active){
                        vm.getSettingsData('customer_setting', 'customer_setting_form')
                    }
                } else if (current_tabname == "company_settings") {
                    vm.getSettingsData('company_setting','company_setting_form')
                } else if (current_tabname == "labels_settings") {
                    vm.getSettingsData('label_setting', 'label_setting_form')    
                } else if (current_tabname == "notification_settings") {
                    vm.getSettingsData('notification_setting','notification_setting_form')
                } else if (current_tabname == "payment_settings") {
                    vm.getSettingsData('payment_setting', 'payment_setting_form')                    
                    vm.bookingpress_check_currency_status('');                    
                }else if (current_tabname == "debug_log_settings") {
                    vm.getSettingsData('debug_log_setting', 'debug_log_setting_form')
                } else if (current_tabname == "message_settings") {
                    vm.getSettingsData('message_setting', 'message_setting_form')
                }else if (current_tabname == "workhours_settings") {
                    const vm= this;         
                    vm.is_display_tab_loader = '1';
                    fetch(BookingPressConfig.rest_url + '/settings/get_default_work_hours_details', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce,
                        },                        
                    })
                    .then(res => res.json())
                    .then((response) => {
                        vm.is_disabled = false;
                        vm.is_display_loader = '0';
                        vm.is_display_tab_loader = '0';
                        vm.work_hours_days_arr = response.data;
                        response.data.forEach(function(currentValue, index, arr) {
                            vm.selected_break_timings[currentValue.day_name] = currentValue.break_times;
                        });
                        vm.workhours_timings = response.selected_workhours;
                        vm.default_break_timings = response.default_break_times;
                        wp.hooks.doAction( 'bookingpress_after_fetch_default_work_hours', this );
                    })
                    .catch((error) => {
                        console.log(error);
                        vm.is_display_tab_loader = '0';
                        vm.$notify({
                            title: 'Error',
                            message: 'Something went wrong..',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    });
                }else if( current_tabname == "dayoff_settings" ){
                    vm.loadAttributes();
                    vm.mountDaysoffCalendars();
                    vm.handleWrapperEvent();
                    if(vm.bpa_is_pro_active){
                        vm.getSpecialdays();
                    }
                }
                else{
                    wp.hooks.doAction( 'bookingpress_dynamic_get_settings_data', current_tabname, this );
                    /* vm.selected_tab_name = 'general_settings'
                    vm.getSettingsData('general_setting', 'general_setting_form')    */                 
                }  
            },
            bookingpress_settings_on_load_methods_func() {
                const vm= this;
                let selected_tab_name = sessionStorage.getItem("current_tabname");                
                if (selected_tab_name != null) {
                    this.selected_tab_name = selected_tab_name;
                } else {
                    selected_tab_name = this.selected_tab_name;
                }                
                if (selected_tab_name == "general_settings") {
                    this.getSettingsData('general_setting', 'general_setting_form');
                    if(vm.bpa_is_pro_active){
                        this.getSettingsData('customer_setting', 'customer_setting_form');
                    }
                } else if (selected_tab_name == "company_settings") {
                    this.getSettingsData('company_setting', 'company_setting_form');
                } else if (selected_tab_name == "notification_settings") {
                    this.getSettingsData('notification_setting', 'notification_setting_form');
                } else if (selected_tab_name == "workhours_settings") {           
                    const vm= this;         
                    vm.is_display_tab_loader = '1';
                    fetch(BookingPressConfig.rest_url + '/settings/get_default_work_hours_details', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce,
                        },                        
                    })
                    .then(res => res.json())
                    .then((response) => {
                        vm.is_disabled = false;
                        vm.is_display_loader = '0';
                        vm.is_display_tab_loader = '0';
                        vm.work_hours_days_arr = response.data;
                        response.data.forEach(function(currentValue, index, arr) {
                            vm.selected_break_timings[currentValue.day_name] = currentValue.break_times;
                        });
                        vm.workhours_timings = response.selected_workhours;
                        vm.default_break_timings = response.default_break_times;
                        wp.hooks.doAction( 'bookingpress_after_fetch_default_work_hours', this );
                    })
                    .catch((error) => {
                        console.log(error);
                        vm.$notify({
                            title: 'Error',
                            message: 'Something went wrong..',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    });

                } else if (selected_tab_name == "dayoff_settings") {
                    vm.loadAttributes();
                    vm.mountDaysoffCalendars();
                    vm.handleWrapperEvent();
                    if(vm.bpa_is_pro_active){
                        this.getSpecialdays(); // pro check PHP side thi handle thase
                    }
                } else if (selected_tab_name == "payment_settings") {
                    this.getSettingsData('payment_setting', 'payment_setting_form');
                    this.bookingpress_check_currency_status('');
                } else if (selected_tab_name == "message_settings") {
                    this.getSettingsData('message_setting', 'message_setting_form');
                } else if (selected_tab_name == 'debug_log_settings') {
                    this.getSettingsData('debug_log_setting', 'debug_log_setting_form');
                } 
                else {
                    wp.hooks.doAction( 'bookingpress_settings_add_dynamic_on_load_method', selected_tab_name, this );
                    if (!selected_tab_name || selected_tab_name == 'general_settings') {
                        this.selected_tab_name = 'general_settings';
                        this.getSettingsData('general_setting', 'general_setting_form');
                    }
                }
            },         
            updateDaysoffCalendarAttributes(newAttributes) {
                const vm = this;

                if (!Array.isArray(vm.dayoffCalendarMounted)) {
                    return;
                }

                vm.dayoffCalendarMounted.forEach((calendarInstance) => {
                    if (!calendarInstance) {
                        return;
                    }

                    if (typeof calendarInstance.setProps === 'function') {
                        calendarInstance.setProps({
                            attributes: newAttributes,
                        });
                    } else if (typeof calendarInstance.updateProps === 'function') {
                        calendarInstance.updateProps({
                            attributes: newAttributes,
                        });
                    } else {
                        console.warn('VCalendar mounted instance does not support prop updates.');
                    }
                });
            },   
            ...BookingPressSettingsExternalMethods            
        },
        watch:{
            selected_tab_name(newValue, oldValue) {
                if( 'dayoff_settings' == newValue ){
                    this.mountDaysoffCalendars();
                }
            },
            daysoff_default_year(){
                this.mountDaysoffCalendars();
            },
            attributes_range: {
               handler(newAttributes) {
                    this.updateDaysoffCalendarAttributes(newAttributes);
                },
                deep: true,
            },
        }
        
    };
    
    const BookingPressSettings = createApp(settingConfig);
    BookingPressSettings.use(BookingPressUI);  
    
    /* BookingPressSettings.component(
        'VCalendar',
        window.BpVCalendar.VCalendarPlugin
    );   */
    
    window.BookingPressSettings = BookingPressSettings.mount('#settings-app-root');
}

wp.hooks.addFilter( 'bookingpress_settings_external_methods', 'bookingpress-appointment-booking', function( ExternalMethods ){

    ExternalMethods.migaration_child_active = function(export_list,parent_key){
        var vm = this;
        var flag_return = false;
        if(typeof vm.migration_tool_form.export_list != "undefined" && vm.migration_tool_form.export_list != "" && vm.migration_tool_form.export_list.length != 0){
                for (const [key, value] of Object.entries(vm.migration_tool_form.export_list)) {
                    if(typeof value.related != "undefined" && value.related != "" && value.related.length != 0){
                        if(vm.migration_tool_form.bookingpress_export_list_data[key]){
                            for (const [key_iiner, value_inner] of Object.entries(value.related)){
                                if(parent_key == value_inner){
                                    return true;
                                }                                    
                            }
                        }                                 
                    }
                }
        }
        if(export_list.required_parent == 1){
            if(typeof export_list.child != "undefined" && export_list.child.length != 0){
                for (const [key, value] of Object.entries(export_list.child)) {                        
                    if(vm.migration_tool_form.bookingpress_export_list_data[key] == true){
                        //return true;
                    }
                }                                               
            }
        }                
        return false;
    }

    ExternalMethods.bpa_check_select_all_or_not = function(){
        var vm = this;
        var all_select = true;
        let all_export_list = vm.migration_tool_form.bookingpress_export_list_data;
        for (const [key, value] of Object.entries(all_export_list)) {
            if(vm.migration_tool_form.bookingpress_export_list_data[key] == false){
                all_select = false;
            }
        }
        return all_select;
    }

    ExternalMethods.bpa_select_all_export_list = function(all_select){
        var vm = this;
        vm.export_last_download_file = '';
        if(all_select){   
            vm.export_all_record = true;                 
            let all_export_list = vm.migration_tool_form.bookingpress_export_list_data;   
            for (const [key, value] of Object.entries(all_export_list)) {
                vm.migration_tool_form.bookingpress_export_list_data[key] = true;
            }                                                         
        }else{
            vm.export_all_record = false;
            let all_export_list = vm.migration_tool_form.bookingpress_export_list_data;                    
            for (const [key, value] of Object.entries(all_export_list)) {
                vm.migration_tool_form.bookingpress_export_list_data[key] = false;
            }
        }
    }

    ExternalMethods.bpa_select_export_list = function(export_list,event_data,parent_key=""){
        var vm = this;
        vm.export_last_download_file = '';             
        if(event_data){
            if(typeof export_list.child != "undefined" && export_list.child.length != 0){
                if(export_list.required_parent == 1){
                    for (const [key, value] of Object.entries(export_list.child)) {
                        vm.migration_tool_form.bookingpress_export_list_data[key] = true;
                    }  
                }                                             
            }                     
            if(typeof export_list.related != "undefined" && export_list.related != "" && export_list.related.length != 0){
                for (const [key, value] of Object.entries(export_list.related)) {
                    vm.migration_tool_form.bookingpress_export_list_data[value] = true;
                }                                               
            }                    
            if(parent_key != ""){
                vm.migration_tool_form.bookingpress_export_list_data[parent_key] = true;
            }
        }else{
            if(parent_key != ""){
                vm.migration_tool_form.bookingpress_export_list_data[parent_key] = false;
            }
            if(typeof export_list.child != "undefined" && export_list.child.length != 0){
                for (const [key, value] of Object.entries(export_list.child)) {
                    vm.migration_tool_form.bookingpress_export_list_data[key] = false;
                }                                               
            } 
            if(typeof export_list.related != "undefined" && export_list.related != "" && export_list.related.length != 0){
                for (const [key, value] of Object.entries(export_list.related)) {
                    vm.migration_tool_form.bookingpress_export_list_data[value] = false;
                }                                               
            }                                        
        }
    }

    ExternalMethods.bookingpress_import_data_continue_process_task = function() {
        const vm = this;
        vm.is_display_import_loader = "0";

        fetch(rest_url + '/settings/import_data_continue_process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': BookingPressConfig.rest_nonce
            },
            body: JSON.stringify({
                import_id: vm.continue_import_id
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            vm.is_display_export_loader = "0";
            if (data.variant == "success") {
                if (data.is_complete == "") {
                    if (typeof data.import_log_data != "undefined") {
                        vm.import_log_data = data.import_log_data;
                    }
                    vm.bookingpress_import_data_continue_process_task();
                } else {
                    vm.continue_import_id = "";
                    vm.migration_tool_form.import_data = "";
                    vm.import_log_data = "";
                    vm.$notify({
                        title: data.title,
                        message: data.msg,
                        type: data.variant,
                        customClass: data.variant + '_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }
            } else {
                vm.$notify({
                    title: data.title,
                    message: data.msg,
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
            }
        })
        .catch(function(error) {
            vm.is_display_export_loader = "0";
            vm.$notify({
                title: 'Error',
                message: 'Something went wrong..',
                type: 'error',
                customClass: 'error_notification',
                duration: BookingPressConfig.notification_timeout
            });
        });
    }

    ExternalMethods.bookingpress_import_data_task = function(){
         const vm = this;
        vm.is_display_import_loader = "1";

        var bookingpress_import_data = vm.migration_tool_form.import_data;
        var bookingpress_confirm_import_data = vm.migration_tool_form.confirm_import_data;        

        fetch(rest_url + '/settings/import-data-process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': BookingPressConfig.rest_nonce
            },
            body: JSON.stringify({
                bookingpress_import_data: bookingpress_import_data,
                confirm_import_data: bookingpress_confirm_import_data
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            vm.is_display_import_loader = "0";

            if (data.variant == "confirm") {
                var bodyElement = document.querySelector('body');
                if (!bodyElement.classList.contains('bpa_custom_warning_migration')) {
                    bodyElement.classList.add('bpa_custom_warning_migration');
                }

                setTimeout(function() {
                    vm.$confirm(data.msg, 'Warning', {
                        confirmButtonText: 'Continue',
                        cancelButtonText: 'Cancel',
                        type: 'warning',
                        customClass: 'bpa_custom_warning_notification'
                    }).then(() => {
                        vm.migration_tool_form.confirm_import_data = "Yes";
                        vm.bookingpress_import_data_task();
                    }).catch(() => {
                        vm.migration_tool_form.import_data = "";
                    });
                }, 1000);

            } else {
                if (data.variant == "success") {
                    vm.continue_import_id = data.import_id;
                    if (typeof data.import_log_data != "undefined") {
                        vm.import_log_data = data.import_log_data;
                    }
                    vm.migration_tool_form.import_data = "";
                    vm.bookingpress_import_data_continue_process_task();
                } else {
                    vm.$notify({
                        title: data.title,
                        message: data.msg,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout,
                    });
                }
            }
        })
        .catch(function(error) {
            vm.is_display_import_loader = "0";
            vm.$notify({
                title: 'Error',
                message: 'Something went wrong..',
                type: 'error',
                customClass: 'error_notification',
                duration: BookingPressConfig.notification_timeout,
            });
        });
    } 
    
    ExternalMethods.bookingpress_export_data_continue_process_task = function(){
         const vm = this;
        vm.is_display_export_loader = "0";

        fetch(rest_url + '/settings/export-data-continue-process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': BookingPressConfig.rest_nonce
            },
            body: JSON.stringify({
                export_id: vm.continue_export_id
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            vm.is_display_export_loader = "0";
            if (data.variant == "success") {
                if (vm.export_log_stop_id == "") {
                    if (data.is_complete == "") {
                        if (typeof data.export_log_data != "undefined") {
                            vm.export_log_data = data.export_log_data;
                        }
                        vm.bookingpress_export_data_continue_process_task();
                    } else {
                        if (typeof data.last_export_file != "undefined") {
                            vm.export_last_download_file = data.last_export_file;
                        }
                        vm.export_log_data = "";
                        vm.continue_export_id = "";
                        vm.export_complete_msg = "The export process has been successfully completed. Please click on the 'Download File' button below to retrieve your export file.";
                        setTimeout(function() {
                            vm.export_complete_msg = "";
                        }, 4000);
                        vm.$notify({
                            title: data.title,
                            message: data.msg,
                            type: data.variant,
                            customClass: data.variant + '_notification',
                            duration: BookingPressConfig.notification_timeout,
                        });
                    }
                }
            } else {
                vm.$notify({
                    title: data.title,
                    message: data.msg,
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            }
        })
        .catch(function(error) {
            vm.is_display_export_loader = "0";
            vm.$notify({
                title: 'Error',
                message: 'Something went wrong..',
                type: 'error',
                customClass: 'error_notification',
                duration: BookingPressConfig.notification_timeout,
            });
        });
    }

    ExternalMethods.bookingpress_export_data_task = function() {
        const vm = this;
        vm.is_display_export_loader = "1";
        var bookingpress_export_list_data = vm.migration_tool_form.bookingpress_export_list_data;
        fetch(rest_url + '/settings/export-data-process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': BookingPressConfig.rest_nonce
            },
            body: JSON.stringify({
                bookingpress_export_list_data: bookingpress_export_list_data
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            vm.is_display_export_loader = "0";
            if (data.variant == "success") {
                vm.bpa_select_all_export_list(false);
                if (data.export_id) {
                    vm.continue_export_id = data.export_id;
                    if (typeof data.export_log_data != "undefined") {
                        vm.export_log_data = data.export_log_data;
                    }
                    vm.bookingpress_export_data_continue_process_task();
                }
            } else {
                vm.$notify({
                    title: data.title,
                    message: data.msg,
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            }
        })
        .catch(function(error) {
            vm.is_display_export_loader = "0";
            vm.$notify({
                title: 'Error',
                message: 'Something went wrong..',
                type: 'error',
                customClass: 'error_notification',
                duration: BookingPressConfig.notification_timeout,
            });
        });
    }

    ExternalMethods.bookingpress_stop_export_process = function() {
        const vm = this;
        vm.is_display_export_loader = "1";
        vm.export_log_stop_id = vm.continue_export_id;
        fetch(rest_url + '/settings/export-data-stop', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': BookingPressConfig.rest_nonce
            },
            body: JSON.stringify({
                export_log_stop_id: vm.continue_export_id
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            vm.is_display_export_loader = "0";

            if (data.variant == "success") {
                vm.is_display_export_loader = "0";
                vm.continue_export_id = '';
                vm.export_log_data = '';
                vm.export_last_download_file = '';
                vm.export_log_stop_id = "";
            } else {
                vm.is_display_export_loader = "0";
                vm.continue_export_id = '';
                vm.export_log_data = '';
                vm.export_last_download_file = '';
                vm.export_log_stop_id = "";
                vm.$notify({
                    title: data.title,
                    message: data.msg,
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            }
        })
        .catch(function(error) {
            vm.is_display_export_loader = "0";
            vm.$notify({
                title: 'Error',
                message: 'Something went wrong..',
                type: 'error',
                customClass: 'error_notification',
                duration: BookingPressConfig.notification_timeout,
            });
        });
    }

    return ExternalMethods;
}, 10, 2);

const initViewDebugLogDialog = () => {

    const viewDebugLogConfig = {

    };

    const BookingPressDebugLog = createApp(viewDebugLogConfig);
    BookingPressDebugLog.use(BookingPressUI);    
    window.BookingPressDebugLog = BookingPressDebugLog.mount('#bookingpress-view-debug-dialog');
}   

const initAddBreakDialog = () => {

    const addBreakConfig = {
        methods: {
            
        },
    };
    const BookingPressAddBreakModel = createApp(addBreakConfig);
    BookingPressAddBreakModel.use(BookingPressUI);    
    window.BookingPressAddBreakModel = BookingPressAddBreakModel.mount('#bookingpress-add-break-dialog');
}   

const initAddHolidayDialog = () => {    
    const addHolidayConfig = {
        methods: {
            
        },
    };
    const BookingPressAddHolidayModel = createApp(addHolidayConfig);
    BookingPressAddHolidayModel.use(BookingPressUI);    
    window.BookingPressAddHolidayModel = BookingPressAddHolidayModel.mount('#add_holiday_model_dialog');
}   
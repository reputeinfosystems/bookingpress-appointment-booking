"use strict";

import { createApp } from 'vue';
import BookingPressUI from './bookingpress-ui.min.js';

function __(text, domain = 'bookingpress-appointment-booking') {
    if (window.wp && window.wp.i18n && typeof window.wp.i18n.__ === 'function') {
        return window.wp.i18n.__(text, domain);
    }
    return text;
}

function getRestUrl() {
    const moduleData = getModuleData('bookingpress-wizard-loader');
    if (moduleData && moduleData.rest_url) return moduleData.rest_url;
    const config = window.BookingPressConfig || {};
    return config.rest_url || (window.wpApiSettings && window.wpApiSettings.root ? window.wpApiSettings.root + 'bookingpress-app/v1' : '/wp-json/bookingpress-app/v1');
}

function getRestNonce() {
    const moduleData = getModuleData('bookingpress-wizard-loader');
    if (moduleData && moduleData.rest_nonce) return moduleData.rest_nonce;
    const config = window.BookingPressConfig || {};
    return config.rest_nonce || (window.wpApiSettings && window.wpApiSettings.nonce ? window.wpApiSettings.nonce : '');
}

function getAdminUrl() {
    const moduleData = getModuleData('bookingpress-wizard-loader');
    if (moduleData && moduleData.admin_url) return moduleData.admin_url;
    const config = window.BookingPressConfig || {};
    if (config.admin_url) return config.admin_url;
    return window.location.origin + window.location.pathname.replace(/admin\.php.*/, '');
}

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
    
    // We only initialize if the container is present
    if ( !document.getElementById('wizard_root_app') ) {
        return;
    }

    const wizardModuleData = getModuleData('bookingpress-wizard-loader');
    
    const wizardConfig = {
        data() {
            const defaults = {
                bookingpress_active_tab: 'company_settings',
                bookingpress_last_step_disabled: true,
                bookingpress_disabled_tabs: false,
                phone_countries_details: [],
                comShowFileList: false,
                working_hours_arr: [],
                bookingpress_currency_options: [],
                fonts_list: [],
                final_step_loader: '0',
                upload_company_avatar_url: getRestUrl() + '/settings/upload_company_avatar',
                upload_headers: { 'X-WP-Nonce': getRestNonce() },
                wizard_steps_data: {
                    company_fields_data: {
                        company_name: '',
                        address: '',
                        time_format: 'g:i a',
                        date_format: 'F j, Y',
                        country: '',
                        phone_no: '',
                        website: '',
                        logo: '',
                        logo_img: '',
                        logo_list: '',
                        anonymous_usage: true,
                    },
                    booking_options: {
                        monday: { start_time: '09:00:00', end_time: '17:00:00' },
                        tuesday: { start_time: '09:00:00', end_time: '17:00:00' },
                        wednesday: { start_time: '09:00:00', end_time: '17:00:00' },
                        thursday: { start_time: '09:00:00', end_time: '17:00:00' },
                        friday: { start_time: '09:00:00', end_time: '17:00:00' },
                        saturday: { start_time: 'off', end_time: 'off' },
                        sunday: { start_time: 'off', end_time: 'off' },
                        currency: 'USD',
                    },
                    service_options: {
                        service_details: [
                            {
                                service_name_label: __('Service Name', 'bookingpress-appointment-booking'),
                                price_label: __('Price', 'bookingpress-appointment-booking'),
                                duration_label: __('Duration', 'bookingpress-appointment-booking'),
                                description_label: __('Description', 'bookingpress-appointment-booking'),
                            }
                        ],
                        service_fields_details: [
                            {
                                service_name: '',
                                price: '',
                                duration_val: '30',
                                duration_unit: 'm',
                                description: '',
                            }
                        ],
                    },
                    styling_options: {
                        font_family: 'Poppins',
                        primary_color: '#12D488',
                        primary_background_color: '#e2faf1',
                        title_color: '#202C45',
                        subtitle_color: '#535D71',
                        content_color: '#727E95',
                    },
                    install_new_product: {
                        download_affilatepress: true,
                        download_arforms: true,
                        is_display_download_save_loader: '0',
                        is_disabled: false
                    }
                }
            };

            return {
                ...defaults,
                ...wizardModuleData,
                wizard_steps_data: {
                    ...defaults.wizard_steps_data,
                    ...(wizardModuleData.wizard_steps_data || {})
                }
            };
        },
        methods: {
            saveWizardSettings(showNotification = true) {
                const vm = this;
                const postData = { wizard_data: vm.wizard_steps_data };
                const restUrl = getRestUrl();
                const nonce = getRestNonce();
                
                return fetch(restUrl + '/wizard/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                    body: JSON.stringify(postData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.variant === 'success') {
                        if (showNotification) {
                            vm.$notify({ title: data.title, message: data.msg, type: 'success' });
                        }
                    } else if(data) {
                        vm.$notify({ title: data.title, message: data.msg, type: 'error' });
                    }
                }).catch( error => {
                    console.error('Error saving wizard settings', error);
                });
            },
            skipWizard() {
                const vm = this;
                const restUrl = getRestUrl();
                const nonce = getRestNonce();
                fetch(restUrl + '/wizard/skip', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP status ' + response.status);
                    }
                    return response.json();
                })
                .then( data => {
                    if (data && data.variant === 'success') {
                        const adminUrl = getAdminUrl();
                        window.location.href = adminUrl + 'admin.php?page=bookingpress';
                    } else {
                        const errMsg = (data && data.msg) ? data.msg : (data && data.message ? data.message : __('Error skipping wizard', 'bookingpress-appointment-booking'));
                        vm.$notify({ title: __('Error', 'bookingpress-appointment-booking'), message: errMsg, type: 'error', customClass: 'error_notification' });
                    }
                })
                .catch(error => {
                    vm.$notify({ title: __('Error', 'bookingpress-appointment-booking'), message: error.message || __('Something went wrong..', 'bookingpress-appointment-booking'), type: 'error', customClass: 'error_notification' });
                });
            },
            bookingpress_upload_company_avatar_func(response, file, fileList) {
                const vm2 = this;
                if (response) {
                    let res = response;
                    if (typeof response === 'string') {
                        try {
                            res = JSON.parse(response);
                        } catch (e) {
                            res = response;
                        }
                    }
                    const resData = (res && res.data) ? res.data : res;
                    if (resData && resData.upload_url) {
                        vm2.wizard_steps_data.company_fields_data.logo = resData.upload_url;
                        vm2.wizard_steps_data.company_fields_data.logo_img = resData.upload_file_name;
                    } else if (resData && (resData.error === 1 || resData.variant === 'error')) {
                        vm2.$notify({ title: __('Error', 'bookingpress-appointment-booking'), message: resData.msg || __('Failed to upload image', 'bookingpress-appointment-booking'), type: 'error', customClass: 'error_notification' });
                    }
                }
            },
            bookingpress_company_avatar_upload_limit(files, fileList) {
                const vm2 = this;
                if (files.length >= 1) {
                    vm2.$notify({ title: __('Error', 'bookingpress-appointment-booking'), message: __('Multiple files not allowed', 'bookingpress-appointment-booking'), type: 'error', customClass: 'error_notification' });
                }
            },
            bookingpress_company_avatar_upload_err(err, file, fileList) {
                const vm2 = this;
                vm2.$notify({ title: __('Error', 'bookingpress-appointment-booking'), message: err.message, type: 'error', customClass: 'error_notification' });
            },
            bookingpress_remove_company_avatar(file, fileList) {
                const vm = this;
                vm.wizard_steps_data.company_fields_data.logo = '';
                vm.wizard_steps_data.company_fields_data.logo_img = '';
            },
            checkUploadedFile(file) {
                const vm = this;
                if (file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp') {
                    vm.$notify({
                        title: __('Error', 'bookingpress-appointment-booking'),
                        message: __('Please upload jpg/png/webp file only', 'bookingpress-appointment-booking'),
                        type: 'error',
                        customClass: 'error_notification',
                    });
                    return false;
                } else {
                    var bpa_image_size = parseInt(file.size / 1000000);
                    if (bpa_image_size > 1) {
                        vm.$notify({
                            title: __('Error', 'bookingpress-appointment-booking'),
                            message: __('Please upload maximum 1 MB file only', 'bookingpress-appointment-booking'),
                            type: 'error',
                            customClass: 'error_notification',
                        });
                        return false;
                    }
                }
                return true;
            },
            bookingpress_open_url(url) {
                if (url) {
                    window.open(url, '_blank');
                }
            },
            bookingpress_add_service() {
                const vm = this;
                const bookingpress_default_obj = (vm.wizard_steps_data.service_options.service_details && vm.wizard_steps_data.service_options.service_details[0]) ? vm.wizard_steps_data.service_options.service_details[0] : { service_name_label: __('Service Name', 'bookingpress-appointment-booking'), price_label: __('Price', 'bookingpress-appointment-booking'), duration_label: __('Duration', 'bookingpress-appointment-booking'), description_label: __('Description', 'bookingpress-appointment-booking') };
                vm.wizard_steps_data.service_options.service_details.push(bookingpress_default_obj);
                vm.wizard_steps_data.service_options.service_fields_details.push({ service_name: '', price: '', duration_val: '30', duration_unit: 'm', description: '' });
            },
            bpa_add_more_service() {
                this.bookingpress_add_service();
            },
            bpa_remove_service(remove_index) {
                const vm = this;
                vm.wizard_steps_data.service_options.service_details.splice(remove_index, 1);
                vm.wizard_steps_data.service_options.service_fields_details.splice(remove_index, 1);
            },
            bookingpress_previous_tab(current_tab) {
                const vm = this;
                if (current_tab == "booking_options") vm.bookingpress_active_tab = 'company_settings';
                else if (current_tab == "service_options") vm.bookingpress_active_tab = 'booking_options';
                else if (current_tab == "styling_options") vm.bookingpress_active_tab = 'service_options';
                else if (current_tab == "final_step") vm.bookingpress_active_tab = 'styling_options';
            },
            bookingpress_next_tab(current_tab) {
                const vm = this;
                if (current_tab == "company_settings") vm.bookingpress_active_tab = 'booking_options';
                else if (current_tab == "booking_options") vm.bookingpress_active_tab = 'service_options';
                else if (current_tab == "service_options") vm.bookingpress_active_tab = 'styling_options';
                else if (current_tab == "styling_options") {
                    vm.bookingpress_last_step_disabled = false;
                    vm.bookingpress_active_tab = 'final_step';
                    vm.final_step_loader = '1';
                    vm.saveWizardSettings(false).then(() => {
                        vm.final_step_loader = '0';
                    });
                }
            },
            bookingpress_copy_content(copy_data) {
                const vm = this;
                var bookingpress_selected_placholder = copy_data;
                var bookingpress_dummy_elem = document.createElement("textarea");
                document.body.appendChild(bookingpress_dummy_elem);
                bookingpress_dummy_elem.value = bookingpress_selected_placholder;
                bookingpress_dummy_elem.select();
                document.execCommand("copy");
                document.body.removeChild(bookingpress_dummy_elem);
                vm.$notify({ title: __('Success', 'bookingpress-appointment-booking'), message: __('Text copied successfully.', 'bookingpress-appointment-booking'), type: 'success', customClass: 'success_notification' });
            },
            bookingpress_finish_wizard(download_affi, download_arf) {
                const vm = this;
                vm.wizard_steps_data.install_new_product.is_display_download_save_loader = 1;
                vm.wizard_steps_data.install_new_product.is_disabled = 1;
                const postData = { download_affi: download_affi, download_arf: download_arf };
                const restUrl = getRestUrl();
                const nonce = getRestNonce();
                fetch(restUrl + '/wizard/download-product', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                    body: JSON.stringify(postData)
                }).then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP status ' + response.status);
                    }
                    return response.json();
                }).then(data => {
                    vm.wizard_steps_data.install_new_product.is_display_download_save_loader = 0;
                    vm.wizard_steps_data.install_new_product.is_disabled = 0;
                    if (data && data.variant === 'success') {
                        const adminUrl = getAdminUrl();
                        window.location.href = adminUrl + 'admin.php?page=bookingpress';
                    } else {
                        const errMsg = (data && data.msg) ? data.msg : (data && data.message ? data.message : __('Error finishing wizard', 'bookingpress-appointment-booking'));
                        vm.$notify({ title: __('Error', 'bookingpress-appointment-booking'), message: errMsg, type: 'error', customClass: 'error_notification' });
                    }
                }).catch(error => {
                    vm.wizard_steps_data.install_new_product.is_display_download_save_loader = 0;
                    vm.wizard_steps_data.install_new_product.is_disabled = 0;
                    vm.$notify({ title: __('Error', 'bookingpress-appointment-booking'), message: error.message || __('Something went wrong..', 'bookingpress-appointment-booking'), type: 'error', customClass: 'error_notification' });
                });
            },
            bookingpress_skip_wizard() {
                this.skipWizard();
            },
            bookingpress_generate_alpha_color(selected_color) {
                const vm = this;
                var opacity_color = Math.round(Math.min(Math.max(0.12 || 1, 0), 1) * 255);
                var primary_background_color = selected_color + (opacity_color.toString(16).toUpperCase());
                vm.wizard_steps_data.styling_options.primary_background_color = primary_background_color;
            },
            bookingpress_set_workhour_value_wizard(worktime, work_hour_day) {
                const vm = this;
                if (vm.wizard_steps_data.booking_options[work_hour_day].end_time == 'off' || worktime >= vm.wizard_steps_data.booking_options[work_hour_day].end_time || (worktime != 'off' && vm.wizard_steps_data.booking_options[work_hour_day].end_time == undefined)) {
                    vm.working_hours_arr.forEach(function (currentValue, index, arr) {
                        if (currentValue.start_time == worktime) {
                            vm.wizard_steps_data.booking_options[work_hour_day].end_time = arr[index]['end_time'];
                        }
                    });
                }
            },
            bookingpress_check_workhour_value_wizard(workhour_time, work_hour_day) {
                if (workhour_time == 'off') {
                    const vm = this;
                    vm.wizard_steps_data.booking_options[work_hour_day].start_time = 'off';
                }
            }
        },
        mounted() {
            document.documentElement.classList.add('bpa-fullscreen-wizard-setup-html');
            document.body.classList.add('bpa-fullscreen-wizard-setup-container');
            document.querySelectorAll('[v-cloak]').forEach(el => el.removeAttribute('v-cloak'));
            console.log('Vue 3 Wizard Mounted');
        }
    };

    document.documentElement.classList.add('bpa-fullscreen-wizard-setup-html');
    document.body.classList.add('bpa-fullscreen-wizard-setup-container');
    const BookingPressWizard = createApp(wizardConfig);
    BookingPressWizard.use(BookingPressUI);
    window.BookingPressWizard = BookingPressWizard.mount('#wizard_root_app');
});

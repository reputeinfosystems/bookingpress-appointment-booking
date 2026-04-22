"use strict";

import { createApp, ref } from 'vue';
//import BookingPressUI from './bookingpress-ui.min.js';

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

document.addEventListener('DOMContentLoaded', () => {
    initNewCustomerDialog();
});

const initNewCustomerDialog = () => {
    const moduleData = getModuleData('bookingpress-appointment-model');

    const BookingPressCustomerDialogConfig = {
        data() {
            let ModelConfigData = {
                customer: {
                    update_id: 0,
                    avatar_url: '',
                    avatar_name: '',
                    wp_user: '',
                    firstname: '',
                    lastname: '',
                    email: '',
                    customer_phone_country: moduleData.customer_phone_country,
                    phone: '',
                    username: '',
                    wordpress_user_id: 0,
                },
                wpUsersList: [],
                vue_tel_mode: 'international',
                vue_tel_auto_format: true,
                bookingpress_tel_input_props: moduleData.bookingpress_tel_input_props,
                rules: {
                    wp_user: [
                        { required: moduleData.allow_customer_wp_user_create, message: 'Please select WordPress user', trigger: 'blur' }
                    ],
                    username: [
                        { required: true, message: 'Please enter username', trigger: 'blur' }
                    ],
                    firstname: [
                        { required: true, message: 'Please enter first name', trigger: 'blur' }
                    ],
                    lastname: [
                        { required: true, message: 'Please enter last name', trigger: 'blur' }
                    ],
                    email: [
                        { required: true, message: 'Please enter email', trigger: 'blur' }
                    ]
                },
                openCustomerModal: false,
                closeCustomerModalOnEscape: true,
                is_disabled: false,
                is_display_save_loader: '0',
                cusShowFileList: false,
                customer_avatar_list: []
            };
            return ModelConfigData;
        },
        methods: {
            bookingpress_get_existing_user_details(event) {
                
                if ('add_new' != event) {
                    fetch(rest_url + '/customer/get_existing_user_details', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce,
                        },
                        body: JSON.stringify({
                            wordpress_user_id: event,
                        })
                    })
                        .then(response => response.json())
                        .then(rest_response => {
                            this.customer.username = rest_response.data.username;
                            this.customer.firstname = rest_response.data.user_firstname;
                            this.customer.lastname = rest_response.data.user_lastname;
                            this.customer.email = rest_response.data.user_email;
                        });
                }
            },
            get_wordpress_users(query) {
                if (query !== '') {
                    fetch(rest_url + '/customer/fetch_wp_users', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce,
                        },
                        body: JSON.stringify({
                            query: query,
                            wordpress_user_id: this.customer.wordpress_user_id
                        })
                    })
                        .then(response => response.json())
                        .then(rest_response => {
                            this.wpUsersList = rest_response.data;
                        });
                } else {
                    this.wpUsersList = [];
                }
            },
            bookingpress_phone_country_change_func(bookingpress_country_obj) {
                const vm = this
                var bookingpress_selected_country = bookingpress_country_obj.iso2
                vm.customer.customer_phone_country = bookingpress_selected_country
                vm.customer.customer_phone_dial_code = bookingpress_country_obj.dialCode;
            },
            openCustomerDialog() {
                this.openCustomerModal = true;
            },
            closeCustomerDialog() {
                const form = this.$refs.customer;
                form.resetFields();
                this.openCustomerModal = false;
            },
            saveCustomerDetails() {
                const form = this.$refs.customer;
                if (!form || typeof form.validate !== 'function') {
                    console.error('Form ref does not expose validate()');
                    return;
                }

                form.validate((valid) => {
                    valid = wp.hooks.applyFilters('bookingpress_modify_request_after_validation', valid);
                    if (valid) {
                        this.is_disabled = true;
                        this.is_display_save_loader = '1';
                        fetch(rest_url + '/customer/create', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': BookingPressConfig.rest_nonce,
                            },
                            body: JSON.stringify({
                                customer_data: this.customer
                            })
                        })
                            .then(response => response.json())
                            .then(rest_response => {
                                this.is_disabled = false;
                                this.is_display_save_loader = '0';
                                
                                if (rest_response.data.variant == 'success') {
                                    this.closeCustomerDialog();
                                    this.customer.update_id = rest_response.data.customer_id;
                                    if ('undefined' != typeof window.BookingPressAppointmentDialog) {
                                        window.BookingPressAppointmentDialog.bookingpress_get_customer_list({ customer_id: rest_response.data.customer_id });
                                    }
                                    this.$notify({
                                        title: rest_response.data.title,
                                        message: rest_response.data.msg,
                                        type: rest_response.data.variant,
                                        customClass: rest_response.data.variant + '_notification',
                                        duration: BookingPressConfig.notification_timeout
                                    });
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
                                this.is_disabled = false;
                                this.is_display_save_loader = '0';
                                this.$notify({
                                    title: 'Error',
                                    message: 'Something went wrong while saving customer',
                                    type: 'error',
                                    customClass: 'error_notification',
                                    duration: BookingPressConfig.notification_timeout
                                });
                            });
                    }
                });
            },
            bookingpress_upload_customer_avatar_func(response, file, fileList) {
                const vm2 = this
                if (response != '') {
                    if ("undefined" != typeof response.error && 1 == response.error) {
                        vm2.bookingpress_image_upload_err(response.msg, file, fileList);
                        vm2.customer.avatar_url = ''
                        vm2.customer.avatar_name = ''
                        vm2.$refs.avatarRef.clearFiles()
                    } else {
                        vm2.customer.avatar_url = response.upload_url
                        vm2.customer.avatar_name = response.upload_file_name
                    }
                }
            },
            bookingpress_image_upload_limit(files, fileList) {
                const vm2 = this
                if (vm2.customer.avatar_url != '') {
                    vm2.$notify({
                        title: 'Error',
                        message: 'Multiple files not allowed',
                        type: 'error',
                        duration: 10000,
                    });
                }
            },
            bookingpress_image_upload_err(err, file, fileList) {
                const vm2 = this
                var bookingpress_err_msg = 'Something went wrong';
                if (err != '' || err != undefined) {
                    bookingpress_err_msg = err
                }
                vm2.$notify({
                    title: 'Error',
                    message: bookingpress_err_msg,
                    type: 'error',
                    duration: 10000,
                });
            },
            bookingpress_remove_customer_avatar(file, fileList) {
                const vm = this
                var upload_url = vm.customer.avatar_url

                const postData = new FormData();
                postData.append('action', 'bookingpress_remove_customer_avatar');
                postData.append('upload_file_url', upload_url);
                postData.append('_wpnonce', BookingPressConfig._wpnonce);

                fetch(BookingPressConfig.ajax_url, {
                    method: 'POST',
                    body: postData
                })
                    .then(response => response.json())
                    .then(rest_response => {
                        vm.customer.avatar_url = ''
                        vm.customer.avatar_name = ''
                        vm.$refs.avatarRef.clearFiles()
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
            checkUploadedFile(file) {
                const vm2 = this
                if (file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp') {
                    vm2.$notify({
                        title: 'Error',
                        message: 'Please upload jpg/png file only',
                        type: 'error',
                        duration: 10000,
                    });
                    return false
                } else {
                    var bpa_image_size = parseInt(file.size / 1000000);
                    if (bpa_image_size > 1) {
                        vm2.$notify({
                            title: 'Error',
                            message: 'Please upload maximum 1 MB file only',
                            type: 'error',
                            duration: 10000,
                        });
                        return false
                    }
                }
            }
        }
    }

    const BookingPressCustomerDialog = createApp(BookingPressCustomerDialogConfig);
    BookingPressCustomerDialog.use(BookingPressUI);
    window.BookingPressCustomerDialog = BookingPressCustomerDialog.mount('#customer_add_modal');
}
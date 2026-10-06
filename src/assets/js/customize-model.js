"use strict";

const getBookingPressConfig = () => window.BookingPressConfig || {};

const bookingpressRestRequest = (endpoint, postData = {}) => {
    const config = getBookingPressConfig();
    const rest_url = config.rest_url || '';
    return fetch(rest_url + endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': config.rest_nonce || '',
        },
        body: JSON.stringify(postData),
    }).then(response => response.json());
};

const bookingpressShowError = (vm, response) => {
    const config = getBookingPressConfig();
    vm.$notify({
        title: response.title || 'Error',
        message: response.msg || 'Something went wrong..',
        type: 'error',
        customClass: 'error_notification',
        duration: config.notification_timeout || 2000,
    });
};

export const customizePageMethods = {

    bookingpress_toggle_calendar() {
        const vm = this;

        if (vm.$refs.bookingpress_range_calendar) {
            vm.$refs.bookingpress_range_calendar.togglePopover();
        }
    },

    bpa_select_category(selected_category) {
        this.bookingpress_shortcode_form.selected_category = selected_category;
    },

    bpa_select_service(selected_service) {
        this.bookingpress_shortcode_form.selected_service = selected_service;
    },

    bpa_select_time(selected_time) {
        this.bookingpress_shortcode_form.selected_time = selected_time;
    },

    bpa_select_primary_color(selected_color) {
        const opacity_color = Math.round(
            Math.min(Math.max(0.12 || 1, 0), 1) * 255
        );

        const primary_background_color =
            selected_color + opacity_color.toString(16).toUpperCase();

        this.selected_colorpicker_values.primary_background_color =
            primary_background_color;

        this.bookingpress_change_border_color();
    },

    bpa_reset_bookingform() {
        const vm = this;

        vm.selected_colorpicker_values.background_color = '#FFF';
        vm.selected_colorpicker_values.footer_background_color = '#f4f7fb';
        vm.selected_colorpicker_values.border_color = '#CFD6E5';
        vm.selected_colorpicker_values.primary_color = '#12D488';
        vm.selected_colorpicker_values.primary_background_color = '#e2faf1';
        vm.selected_colorpicker_values.label_title_color = '#202C45';
        vm.selected_colorpicker_values.content_color = '#727E95';
        vm.selected_colorpicker_values.price_button_text_color = '#fff';
        vm.selected_font_values.title_font_family = 'Poppins';
        vm.selected_colorpicker_values.sub_title_color = '#535D71';
        vm.selected_colorpicker_values.border_alpha_color = '';

        vm.bookingpress_change_border_color();

        if (typeof wp !== 'undefined' && wp.hooks) {
            wp.hooks.doAction( 'bookingpress_reset_color_option_after', vm);
        }
    },

    bpa_save_booking_form_settings_data() {
        const vm = this;
        const postData = {
            tab_container_data: vm.tab_container_data,
            category_container_data: vm.category_container_data,
            service_container_data: vm.service_container_data,
            timeslot_container_data: vm.timeslot_container_data,
            colorpicker_values: vm.selected_colorpicker_values,
            font_values: vm.selected_font_values,
            front_label_edit_data: vm.front_label_edit_data,
            booking_form_settings: vm.booking_form_settings,
            summary_container_data: vm.summary_container_data,
            waiting_list_container_data: vm.waiting_list_container_data,
            recurring_appointment_container_data: vm.recurring_appointment_container_data,
        };

        if (typeof wp !== 'undefined' && wp.hooks) {
            wp.hooks.doAction('bookingpress_before_save_customize_form_settings_vue3',vm,postData);
        }

        bookingpressRestRequest('/customize/booking-form/save',postData)
        .then(response => {
            if (response.variant === 'error') {
                bookingpressShowError(vm, response);
                return;
            }
        })
        .catch(error => {
            if (error && error.message) {
                vm.$notify({
                    title: error.title || '',
                    message: error.message,
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            }
        });
    },

    bpa_save_field_settings_data() {
        const vm = this;

        if (typeof wp !== 'undefined' && wp.hooks) {
            wp.hooks.doAction( 'bookingpress_before_save_field_settings_method',vm);
        }

        bookingpressRestRequest('/customize/field-settings/save',{
                field_settings: vm.field_settings_fields,
            }
        )
        .then(response => {

            if (response.variant === 'error') {
                bookingpressShowError(vm, response);
                return;
            }

            if (typeof wp !== 'undefined' && wp.hooks) {
                wp.hooks.doAction(
                    'bookingpress_after_save_field_settings_method',
                    vm,
                    response
                );
            }

        })
        .catch(error => {
            if (error && error.message) {
                vm.$notify({
                    title: error.title || '',
                    message: error.message,
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            }
        });
    },

    bpa_save_field_my_booking_data() {
        const vm = this;

        return bookingpressRestRequest(
            '/customize/my-booking/save',
            {
                my_booking_field_settings:
                    vm.my_booking_field_settings,

                my_booking_selected_font_values:
                    vm.selected_font_values,

                my_booking_selected_colorpicker_values:
                    vm.selected_colorpicker_values,

                delete_account_content:
                    vm.delete_account_content,
            }
        )
        .then(response => {
            if (response.variant === 'error') {
                bookingpressShowError(vm, response);
            }

            return response;
        })
        .catch(error => {
            if (error && error.message) {
                vm.$notify({
                    title: error.title || '',
                    message: error.message,
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout,
                });
            }

            throw error;
        });
    },

    bpa_save_customize_settings(action) {
        const vm = this;

        vm.is_display_save_loader = '1';
        vm.is_disabled = 1;

        let bpa_validation_flag = false;
        let bpa_datetime_validation_flag = false;
        let bpa_cart_validation = false;

        if (action === 'form_fields') {

            vm.bpa_save_field_settings_data()
                .then(rest_response => {

                    vm.is_disabled = false;
                    vm.is_display_save_loader = '0';

                    if (rest_response.variant === 'error') {
                        bookingpressShowError(vm, rest_response);
                        return;
                    }

                    vm.$notify({
                        title: 'Success',
                        message: 'Customization settings saved successfully.',
                        type: 'success',
                        customClass: 'success_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                })
                .catch(error => {

                    vm.is_disabled = false;
                    vm.is_display_save_loader = '0';

                    vm.$notify({
                        title: 'Error',
                        message: 'Something went wrong..',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                });

        } else {

            if (typeof wp !== 'undefined' && wp.hooks) {
                wp.hooks.doAction(
                    'bookingpress_before_save_customize_other_settings_data',
                    vm
                );
            }

            if (!bpa_validation_flag) {

                const savePromises = [
                    vm.bpa_save_booking_form_settings_data(),
                    vm.bpa_save_field_my_booking_data()
                ];

                if (typeof vm.bpa_save_field_gift_card_form_settings_data === 'function') {
                    savePromises.push( vm.bpa_save_field_gift_card_form_settings_data());
                }

                if (typeof vm.bpa_save_field_package_booking_data === 'function') {
                    savePromises.push( vm.bpa_save_field_package_booking_data());
                }

                Promise.all(savePromises)
                    .then(rest_responses => {
                        vm.is_disabled = false;
                        vm.is_display_save_loader = '0';

                        vm.$notify({
                            title: 'Success',
                            message: 'Customization settings saved successfully.',
                            type: 'success',
                            customClass: 'success_notification',
                            duration: BookingPressConfig.notification_timeout
                        });

                        if (typeof wp !== 'undefined' && wp.hooks) {
                            wp.hooks.doAction(
                                'bookingpress_save_customize_other_settings_data',
                                vm
                            );
                        }
                    })
                    .catch(error => {
                        vm.is_disabled = false;
                        vm.is_display_save_loader = '0';

                        vm.$notify({
                            title: 'Error',
                            message: 'Something went wrong..',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout
                        });
                    });

            } else {

                vm.is_display_save_loader = '0';
                vm.is_disabled = 0;

                if (bpa_datetime_validation_flag) {

                    vm.$notify({
                        title: 'Error',
                        message: 'Date & Time cannot come immediately after Basic Details when it is the first step.',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });

                } else if (bpa_cart_validation) {

                    vm.$notify({
                        title: 'Error',
                        message: 'Cart must be the last step, or placed just before Basic Details when Basic Details is the last step.',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });

                } else {

                    vm.$notify({
                        title: 'Error',
                        message: 'You can not set the "Date & Time" step as the first step in the process.',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }
            }
        }
    },

    bookingpress_load_booking_form_data() {
    const vm = this;

        bookingpressRestRequest('/customize/booking-form')
            .then(response => {

                if (response.variant === 'error') {
                    bookingpressShowError(vm, response);
                    return;
                }

                const formdata = response.formdata || {};

                vm.tab_container_data =
                    formdata.tab_container_data || {};

                vm.category_container_data =
                    formdata.category_container_data || {};

                vm.service_container_data =
                    formdata.service_container_data || {};

                vm.cart_container_data =
                    formdata.cart_container_data || {};    

                vm.timeslot_container_data =
                    formdata.timeslot_container_data || {};

                vm.selected_colorpicker_values =
                    formdata.colorpicker_values || {};

                if (
                    formdata.font_values &&
                    typeof formdata.font_values.title_font_family !== 'undefined'
                ) {
                    vm.selected_font_values.title_font_family =
                        formdata.font_values.title_font_family;
                }

                vm.booking_form_settings =
                    formdata.booking_form_settings || {};
                
                if (typeof vm.booking_form_settings.bookingpress_form_sequance === 'string') {
                    vm.booking_form_settings.bookingpress_form_sequance = JSON.parse(vm.booking_form_settings.bookingpress_form_sequance);
                }

                vm.booking_form_sequence = vm.booking_form_settings.bookingpress_form_sequance || [];

                vm.bookingpress_change_form_sequence();

                vm.summary_container_data =
                    formdata.summary_container_data || {};

                vm.front_label_edit_data =
                    formdata.front_label_edit_data || {}; 

                if (typeof wp !== 'undefined' && wp.hooks) {
                    wp.hooks.doAction(
                        'bookingpress_add_booking_form_customize_data',
                        vm,
                        response
                    );
                }

            })
            .catch(rest_response => {

                if (rest_response && rest_response.data) {
                    vm.$notify({
                        title: rest_response.data.title,
                        message: rest_response.data.msg,
                        type: rest_response.data.variant,
                        customClass: rest_response.data.variant + '_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }
            });
    },

    bookingpress_load_my_booking_data() {
        const vm = this;

        bookingpressRestRequest('/customize/my-booking')
            .then(response => {

                if (response.variant === 'error') {
                    bookingpressShowError(vm, response);
                    return;
                }

                const formdata = response.formdata || {};

                const settings = formdata.booking_form_settings || {};

                vm.my_booking_field_settings = settings;

                vm.delete_account_content =
                    settings.delete_account_content || '';

                setTimeout(() => {
                    vm.bookingpress_change_border_color();
                }, 100);

            })
            .catch(rest_response => {

                if (rest_response && rest_response.data) {
                    vm.$notify({
                        title: rest_response.data.title,
                        message: rest_response.data.msg,
                        type: rest_response.data.variant,
                        customClass: rest_response.data.variant + '_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }
            });
    },

    endDragposistion() {
        const vm = this;

        if (
            typeof vm.drag_data.field_pos_update_id === 'undefined' ||
            typeof vm.drag_data.old_index === 'undefined' ||
            typeof vm.drag_data.new_index === 'undefined'
        ) {
            return;
        }

        const postData = {
            field_pos_update_id: vm.drag_data.field_pos_update_id,
            old_index: vm.drag_data.old_index,
            new_index: vm.drag_data.new_index,
        };

        bookingpressRestRequest( '/customize/field-position',postData)
        .then(response => {

            if (response.variant === 'error') {
                bookingpressShowError(vm, response);
            }

            vm.bookingpress_load_field_settings_data();

        })
        .catch(error => {

            vm.$notify({
                title: 'Error',
                message: 'Something went wrong..',
                type: 'error',
                customClass: 'error_notification',
                duration: BookingPressConfig.notification_timeout
            });

        });
    },

    updateFieldPos(e) {
        const vm = this;

        if (
            !e ||
            !e.draggedContext ||
            !e.draggedContext.element
        ) {
            return;
        }

        const field_pos_update_id = e.draggedContext.element.id;
        const old_index = e.draggedContext.index;
        const new_index = e.draggedContext.futureIndex;

        vm.drag_data = {
            field_pos_update_id: field_pos_update_id,
            old_index: old_index,
            new_index: new_index,
        };
    },

    closeFieldSettings() {
        const field_settings = this.field_settings_fields;

        field_settings.forEach(item => {
            item.is_edit = 0;
        });
    },

    open_custom_css_modal() {
        const vm = this;
        vm.add_custom_css_modal = true;

        vm.bookigpress_form_custom_css = vm.selected_colorpicker_values.custom_css;
    },

    close_custom_css_modal() {
        const vm = this;

        vm.add_custom_css_modal = false;
        vm.bookigpress_form_custom_css = '';
    },

    bookingpress_save_custom_css() {
        const vm = this;

        vm.selected_colorpicker_values.custom_css = vm.bookigpress_form_custom_css;

        vm.close_custom_css_modal();
    },

    bookingpress_change_tab(tabname) {
        const vm = this;
        vm.bookingpress_tab_change_loader = '1';

        setTimeout(() => {
            vm.bookingpress_tab_change_loader = '0';
            vm.activeTabName = tabname;
        }, 1000);
    },

    bookingpress_customize_form_tab_phone_country_change_func(bookingpress_country_obj) {
        const vm = this;

        if ( !bookingpress_country_obj || !bookingpress_country_obj.iso2) {
            return;
        }

        const bookingpress_selected_country = bookingpress_country_obj.iso2;

        if ( window.intlTelInputUtils && typeof window.intlTelInputUtils.getExampleNumber === 'function') {
            const exampleNumber =
                window.intlTelInputUtils.getExampleNumber( bookingpress_selected_country, true, 1 );
            if (exampleNumber !== '') {
                vm.bookingpress_tel_input_props.inputOptions.placeholder =
                    exampleNumber;
            }
        }
    },

    bookingpress_clear_datepicker() {
        this.appointment_date_range = '';
    },

    bookingpress_lite_after_change_position(event) {
        const vm = this;

        if (event === true) {
            vm.formActiveTab = '2';
        } else {
            vm.formActiveTab = '1';
        }
    },

    bookingpress_change_border_color() {
        const vm = this;

        const border_color = vm.selected_colorpicker_values.border_color;
        const opacity_color = Math.round( Math.min(Math.max(0.12 || 1, 0), 1) * 255);
        const border_rgba_color = border_color + opacity_color.toString(16).toUpperCase();

        vm.selected_colorpicker_values.border_alpha_color = border_rgba_color;

        const form_background_color = vm.selected_colorpicker_values.background_color;
        const panel_background_color = vm.selected_colorpicker_values.footer_background_color;
        const primary_color = vm.selected_colorpicker_values.primary_color;
        const sub_title_color = vm.selected_colorpicker_values.sub_title_color;
        const font_family = vm.selected_font_values.title_font_family;
        const title_color = vm.selected_colorpicker_values.label_title_color;
        const content_color = vm.selected_colorpicker_values.content_color;

        const css_data =
            '.bpa-front-cp__filter-dropdown,' +
            '.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card,' +
            '.bpa-front-module--bd-form .--bpa-country-dropdown .vti__dropdown-list,' +
            '.bpa-cbf--tabs .bp-tabs__nav-wrap,' +
            '.bp-ui-popconfirm.bp-popover.bp-popconfirm,' +
            '.bpa-tn__dropdown-menu,.bp-ui-popover{' +
            'background-color:' + form_background_color + '}' +

            '.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card,' +
            '.bpa-tn__dropdown-menu,' +
            '.bpa-front-module--bd-form .--bpa-country-dropdown .vti__dropdown-list,' +
            '.bpa-form-control.--bpa-country-dropdown .vti__dropdown,' +
            '.bpa-cbf--tabs .bp-tabs__nav-wrap,' +
            '.bpa-front-cp-my-appointment .bpa-form-control input,' +
            '.bpa-front-cp__filter-dropdown,' +
            '.bp-ui-popover,' +
            '.bp-ui-popconfirm .bp-ui-popconfirm__action,' +
            '.bp-ui-button.bp-ui-button--bpa-btn.bpa-btn__small.bp-ui-button--mini:not(.bpa-btn--danger),' +
            '.bp-button--small.bp-ui-popconfirm__cancel.bp-ui-button,' +
            '.bp-ui-popconfirm.bp-popover.bp-popconfirm,' +
            '.bpa-ci__service-actions .bpa-ci__sa-wrap{' +
            'border-color:' + border_color +
            '}' +

            '.bpa-front-ma-table__body .bpa-front-mat__row:nth-child(even),' +
            '.bpa-form-control.--bpa-country-dropdown .vti__dropdown-item.highlighted,' +
            '.bpa-ci__service-actions .bpa-ci__sa-wrap{' +
            'background-color:' + panel_background_color +
            '}' +

            '.bp-date-table td.current:not(.disabled) span,' +
            '.bpa-cart__item .bpa-ci__service-actions .bpa-btn--icon-without-box:hover,' +
            '.bpa-front-ma-table-actions-wrap .bpa-btn--icon-without-box:hover{' +
            'background-color:' + primary_color + ' !important' +
            '}' +

            '.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-btn--icon-without-box:hover span svg path{' +
            'fill: var(--bpa-cl-white)' +
            '}' +

            '.bp-picker-panel__content .bp-date-table th,' +
            '.bp-ui-popconfirm .bp-ui-popconfirm__main,' +
            '.bp-ui-button.bp-ui-button--bpa-btn.bpa-btn__small.bp-ui-button--mini{' +
            'color:' + sub_title_color +
            '}' +

            '.bp-picker-panel__content .bp-date-table td span,' +
            '.bp-picker-panel__content .bp-date-table th,' +
            '.bp-date-picker__header-label{' +
            'font-family:' + font_family +
            '}' +

            '.bp-picker-panel__content .bp-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span,' +
            '.bp-date-picker__header-label,' +
            '.bpa-front-cp-my-appointment .bpa-form-control input,' +
            '.bpa-form-control.--bpa-country-dropdown.bp-ui-input.bpa-form-control input{' +
            'color:' + title_color + ' !important' +
            '}' +

            '.bp-date-picker__header-label:hover,' +
            '.bp-date-table td.today span,' +
            '.bp-picker-panel__content .bp-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span:hover,' +
            '.bp-picker-panel__content .bp-date-table td:not(.current):not(.today) span:hover{' +
            'color:' + primary_color +
            '}' +

            '.bp-picker-panel .bp-ui-icon-d-arrow-left::before,' +
            '.bp-picker-panel .bp-ui-icon-arrow-left::before,' +
            '.bp-picker-panel .bp-ui-icon-arrow-right::before,' +
            '.bp-picker-panel .bp-ui-icon-d-arrow-right::before,' +
            '.bpa-customize-booking-form-preview-container .bpa-form-control input::placeholder,' +
            '.bpa-customize-booking-form-preview-container .bpa-form-control .bp-textarea__inner::placeholder,' +
            '.bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item > span.material-icons-round,' +
            '.bpa-form-control--date-picker .bp-ui-input__prefix,' +
            '.bpa-front-cp--fw__col.__bpa-is-search-icon span.material-icons-round{' +
            'color:' + content_color +
            '}' +

            '.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-btn--icon-without-box span svg path,' +
            '.bpa-ci__service-brief .bpa-ci__expand-icon path{' +
            'fill:' + content_color +
            '}' +

            '.bp-ui-button.bp-ui-button--bpa-btn.bpa-btn__small.bpa-btn--danger.bp-ui-button--mini,' +
            '.bpa-cart__item .bpa-ci__service-actions .bpa-btn--icon-without-box:hover .material-icons-round{' +
            'color:var(--bpa-cl-white) !important' +
            '}' +

            '.bpa-custom-checkbox--is-label .bp-ui-checkbox__inner , .bp-ui-root .bpa-front-cp--fw__row .bp-input__wrapper,' +
            '.bpa-cbf--preview-step__body-content  .bpa-form-control.bp-tel-input .bp-ui-tel-input__surface{' +
            'border-color:' + border_color + '!important' +
            '}' +

            '.bpa-custom-checkbox--is-label .bp-ui-checkbox__input.is-checked .bp-ui-checkbox__inner{' +
            'background-color:' + primary_color + '!important;' +
            'border-color:' + primary_color + '!important' +
            '}' +

            '.bpa-custom-checkbox--is-label .bp-ui-checkbox__input.is-checked+.bp-ui-checkbox__label{' +
            'color:' + primary_color + ' !important' +
            '}' +

            '.bpa-custom-checkbox--is-label .bp-ui-checkbox__label{' +
            'color:' + content_color + ' !important' +
            '}' +

            '.bpa-cart__item .bpa-ci__service-actions .bpa-btn--icon-without-box:hover{' +
            'border-color:' + primary_color + '!important' +
            '}' +

            '.bpa-customize-sm-card .bpa-customize-sm__default-avatar svg > rect{' +
            'fill:' + border_color + ' !important;' +
            'fill-opacity:0.5' +
            '}' +

            '.bpa-customize-sm-card .bpa-customize-sm__default-avatar svg path{' +
            'fill:' + form_background_color + ' !important' +
            '}' +

            '.bpa-customize-sm-card__body--name,' +
            '.bpa-customize-sm-card__inner-body--name,' +
            '.bpa-customize-sm-card__inner-body-item-wrapper{' +
            'color:' + title_color + ' !important' +
            '}' +

            '.bpa-customize-sm-card__inner-item-icon svg path{' +
            'stroke:' + title_color + ' !important' +
            '}' +

            '.bpa-customize-sm-card .bpa-customize-sm-card__inner-button{' +
            'background-color:' + border_color + '4d !important;' +
            'color:' + sub_title_color + ' !important' +
            '}' +

            '.bpa-customize-sm-card .bpa-customize-sm-card__inner-button svg path{' +
            'fill:' + border_color + ' !important' +
            '}' +

            '.bpa-customize-sm-card .bpa-customize-sm-card__inner-button.bpa-sm-card__active{' +
            'background-color:' + primary_color + ' !important;' +
            'color:' + vm.selected_colorpicker_values.price_button_text_color + ' !important' +
            '}' +

            '.bpa-customize-sm-card .bpa-customize-sm-card__inner-button.bpa-sm-card__active svg path{' +
            'fill:' + vm.selected_colorpicker_values.price_button_text_color + ' !important' +
            '}';

        let bookingpressStyle =
            document.getElementById(
                'bookingpress-customize-dynamic-style'
            );

        if (!bookingpressStyle) {
            bookingpressStyle =
                document.createElement('style');

            bookingpressStyle.id =
                'bookingpress-customize-dynamic-style';

            document.body.appendChild(bookingpressStyle);
        }

        bookingpressStyle.innerHTML = css_data;
    },

    bookingpress_hide_caching_notice() {
        const vm = this;

        bookingpressRestRequest( '/customize/dismiss-caching-notice')
        .then(response => {

            if (response.variant === 'success') {
                vm.is_disabled_caching_notice = true;
            } else {
                bookingpressShowError(vm, response);
            }

        })
        .catch(() => {
            console.error('bookingpress_hide_caching_notice error');
        });
    },

    bookingpress_close_caching_notice() {
        this.is_disabled_caching_notice = true;
    },
};
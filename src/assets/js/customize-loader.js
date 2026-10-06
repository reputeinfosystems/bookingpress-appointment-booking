"use strict";

import { createApp } from 'vue';
import BookingPressUI from './bookingpress-ui.min.js';
import draggable from './vuedraggable.min.js';
import { customizePageMethods } from './customize-model.js';

function getModuleData(moduleId) {
    const el = document.getElementById( `wp-script-module-data-${moduleId}`);
    if (!el) {
        return {};
    }

    try {
        return JSON.parse(el.textContent || '{}');
    } catch (error) {
        console.error( 'Failed to parse module data:', error);
        return {};
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initCustomizeWrapper();
});

const initCustomizeWrapper = () => {
    const BookingPressConfig = window.BookingPressConfig || {};
    const rest_url = BookingPressConfig.rest_url || '';
    const moduleData = getModuleData('bookingpress-customize-loader');

    const customizePageMethodsData = wp.hooks.applyFilters( 'bookingpress_customize_methods', customizePageMethods );

    const customizePageConfig = {

        data() {

            return {

                ...moduleData,

                rest_url: rest_url,

                is_display_loader:
                    moduleData.is_display_loader || '0',

                is_display_save_loader:moduleData.is_display_save_loader || '0',
                is_disabled:moduleData.is_disabled || 0,
                dragging:moduleData.dragging || false,
                drag_data:moduleData.drag_data || {},
                activeTabName: moduleData.activeTabName || 'booking_form',
                bpa_activeTabName:moduleData.bpa_activeTabName || 'booking_form',
                bookingpress_tab_change_loader: moduleData.bookingpress_tab_change_loader || '0',
                formActiveTab: moduleData.formActiveTab || '1',
                bookingpress_form_sequance_arr: moduleData.bookingpress_form_sequance_arr || {},
                appointment_date_range: moduleData.appointment_date_range || [],
                tab_container_data: moduleData.tab_container_data || {},
                cart_container_data: moduleData.cart_container_data || {},
                category_container_data: moduleData.category_container_data || {},
                service_container_data: moduleData.service_container_data || {},
                timeslot_container_data: moduleData.timeslot_container_data || {},
                summary_container_data: moduleData.summary_container_data || {},
                front_label_edit_data: moduleData.front_label_edit_data || {},
                bookingpress_shortcode_form:moduleData.bookingpress_shortcode_form || {},

                selected_colorpicker_values:
                    moduleData.selected_colorpicker_values || {
                        background_color: '#fff',
                        footer_background_color: '#f4f7fb',
                        border_color: '#CFD6E5',
                        primary_color: '#12D488',
                        primary_background_color: '#e2faf1',
                        label_title_color: '#202C45',
                        sub_title_color: '#535D71',
                        content_color: '#727E95',
                        price_button_text_color: '#fff',
                        custom_css: '',
                        border_alpha_color: '',
                    },

                selected_font_values:
                    moduleData.selected_font_values || {
                        title_font_size: '18',
                        title_font_family: 'Poppins',
                        content_font_size: '14',
                        sub_title_font_size: '16',
                    },

                booking_form_settings: moduleData.booking_form_settings || {},
                draggable_field_setting_fields: moduleData.draggable_field_setting_fields || [],
                field_settings_fields: moduleData.field_settings_fields || [],
                my_booking_field_settings: moduleData.my_booking_field_settings || {},
                add_custom_css_modal: moduleData.add_custom_css_modal || false,
                bookigpress_form_custom_css: moduleData.bookigpress_form_custom_css || '',
                delete_account_content: moduleData.delete_account_content || '',
                days_off_disabled_dates: moduleData.days_off_disabled_dates || '',
                bookingpress_all_global_pages: moduleData.bookingpress_all_global_pages || [],

                fonts_list: moduleData.fonts_list || [],

                bookingpress_tel_input_props:
                    moduleData.bookingpress_tel_input_props || {
                        defaultCountry: '',
                        inputOptions: {
                            placeholder: '',
                        },
                    },

                dummy_data:
                    moduleData.dummy_data || [],

                masks:
                    moduleData.masks || {
                        input: '',
                    },

                bookingpress_allow_wp_customer_create:moduleData.bookingpress_allow_wp_customer_create || '',

                is_disabled_caching_notice: moduleData.is_disabled_caching_notice || false,
                gift_card_form_settings: moduleData.gift_card_form_settings || {},
                package_booking_form_settings: moduleData.package_booking_form_settings || {},
                waiting_list_container_data: moduleData.waiting_list_container_data || {},
                recurring_appointment_container_data: moduleData.recurring_appointment_container_data || {},
            };
        },

        mounted() {
            const vm = this;
            if (window.screen.width >= 1200) {
                vm.current_screen_size = 'desktop';
            } else if (
                window.screen.width < 1200 &&
                window.screen.width >= 768
            ) {
                vm.current_screen_size = 'tablet';
            } else if (window.screen.width < 768) {
                vm.current_screen_size = 'mobile';
            }

            /*
            * form_fields page vs forms page
            */
            if (moduleData.bookingpress_customize_action === 'form_fields') {
                vm.bookingpress_load_field_settings_data();
            } else if (moduleData.bookingpress_customize_action === 'forms') {
                vm.bookingpress_load_booking_form_data();
                
                vm.bookingpress_load_my_booking_data();
                 if (typeof vm.bookingpress_load_gift_card_data === 'function') {
                    vm.bookingpress_load_gift_card_data();
                }
                
            }

            /*
            * Keep addon/custom dynamic initialization.
            */
            if (typeof wp !== 'undefined' && wp.hooks) {
                wp.hooks.doAction('bookingpress_customize_dynamic_onload_methods_after',vm);
            }

            /*
            * Remove the page loading loader after initialization.
            */
            setTimeout(function() {
                if (document.getElementById('bpa-page-loading-loader') !== null) {
                    document.getElementById('bpa-page-loading-loader').remove();

                    if (document.getElementById('bpa-main-container') !== null) {
                        document.getElementById('bpa-main-container').style.display = 'block';
                    }

                    if (document.getElementById('bpa-page-loading-loader-2') !== null) {
                        document.getElementById('bpa-page-loading-loader-2').remove();
                    }

                    if (document.getElementById('bpa-main-container-2') !== null) {
                        document.getElementById('bpa-main-container-2').style.display = 'block';
                    }

                    if (document.getElementById('bpa-page-loading-loader-3') !== null) {
                        document.getElementById('bpa-page-loading-loader-3').remove();
                    }

                    if (document.getElementById('bpa-main-container-3') !== null) {
                        document.getElementById('bpa-main-container-3').style.display = 'block';
                    }

                    if (typeof jQuery !== 'undefined') {
                        jQuery('#bpa-loader-div').show();
                    }
                }
            }, 2000);
        },

        methods: {
            ...customizePageMethodsData,
            ...(window.BookingPressCustomizeProMethods || {}),
            ...(window.BookingPressGiftCardCustomizeMethods || {}),
            ...(window.BookingPressPackageCustomizeMethods || {}),
        },
    };

    const BookingPressCustomize = createApp(customizePageConfig);
    BookingPressCustomize.use(BookingPressUI);
    BookingPressCustomize.component('draggable', draggable);
    const customizeRoot = document.getElementById('customize-form-app-root') || document.getElementById('customize-form-field-app-root');

    if (customizeRoot) {
        window.CustomizeLoader = BookingPressCustomize.mount(customizeRoot);
    }
};
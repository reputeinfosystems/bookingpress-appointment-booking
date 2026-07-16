"use strict";
import { createApp } from 'vue';
import BookingPressUI from './bookingpress-ui.min.js';

const BookingPressConfig = window.BookingPressConfig;

const rest_url = BookingPressConfig.rest_url;


document.addEventListener('DOMContentLoaded', () => {
    initHeaderWrapper();
    initNoticeWrapper();
    initHelperIconWrapper();
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
const initHeaderWrapper = () => {

    let headerExternalMethods = wp.hooks.applyFilters('bookingpress_header_wrapper_methods', {
        bpa_mobile_toggle_menu(){
            this.toggle_drawer = !this.toggle_drawer;
        },
        open_premium_modal(){
            const vm = this;
            let configData = getModuleData('bookingpress-sidemenu-drawer');

            vm.premium_modal = configData.show_sale_popup;
            vm.bookingpress_old_premium_modal = configData.show_original_popup;
        },
        bookingpress_redirect_premium_page(){
            window.open('https://www.bookingpressplugin.com/pricing/?utm_source=liteversion&utm_medium=plugin&utm_campaign=Upgrade+to+Premium&utm_id=bookingpress_2', '_blank');
        },
        bookingpress_redirect_sale_premium_page(){
            window.open('https://www.bookingpressplugin.com/pricing/?utm_source=LiteVersion&utm_medium=Popup&utm_campaign=SummerSale', '_blank');
        }
    });
    
    const headerConfig = {
        data(){
            let externalHeaderData = wp.hooks.applyFilters('bookingpress_external_header_data', {
                toggle_drawer: false,
                premium_modal: false,
                bookingpress_old_premium_modal: false,
                close_modal_on_esc: true
            });
            return {
                ...externalHeaderData
            };
        },
        methods: {
            ...headerExternalMethods
        },
        mounted(){
            const vm = this;
            let configData = getModuleData('bookingpress-sidemenu-drawer');

            if( 'undefined' != typeof configData.show_upgrade_model_on_load && true == configData.show_upgrade_model_on_load){
                vm.premium_modal = configData.show_sale_popup;
                vm.bookingpress_old_premium_modal = configData.show_original_popup;
            }
        },
        watch: {
            needHelpDrawer(val) {
                if (!val) {
                    document.body.classList.remove('bp-popup-parent--hidden');
                }
            },
            needHelpDrawer_add(val) {
                if (!val) {
                    document.body.classList.remove('bp-popup-parent--hidden');
                }
            }
        }
    };

    const BookingPressHeader = createApp( headerConfig );
    BookingPressHeader.use(BookingPressUI);
    window.BookingPressHeader = BookingPressHeader.mount('#bookingpress_header_wrapper');

}

document.addEventListener('DOMContentLoaded', () => {
    initNoticeWrapper();
});

const initNoticeWrapper = () => {
    let noticeExternalMethods = wp.hooks.applyFilters('bookingpress_notice_wrapper_methods', {});
    
    const noticeConfig = {
        data(){
            let externalNoticeData = wp.hooks.applyFilters('bookingpress_external_notice_data', {});
            return {
                ...externalNoticeData
            };
        },
        methods: {
            ...noticeExternalMethods
        }
    };

    const BookingPressNotice = createApp( noticeConfig );
    BookingPressNotice.use(BookingPressUI);
    window.BookingPressNotice = BookingPressNotice.mount('#bpa-admin-notices-wrapper');
};


const initHelperIconWrapper = () => {

    let helperExternalMethods = wp.hooks.applyFilters('bookingpress_helper_wrapper_methods', {

        bpa_set_read_more_link() {
            const vm = this;

            let module = vm.request_module || '';
            module = module .toString() .trim() .toLowerCase() .replace(/\s+/g, '_');

            const map = {
                dashboard: "https://www.bookingpressplugin.com/documents/dashboard/",
                services: "https://www.bookingpressplugin.com/documents/services/",
                customers: "https://www.bookingpressplugin.com/documents/customers/",
                calendar: "https://www.bookingpressplugin.com/documents/admin-calender-view/",
                appointments: "https://www.bookingpressplugin.com/documents/appointments/",
                notifications: "https://www.bookingpressplugin.com/documents/email-notifications-message/",
                payments: "https://www.bookingpressplugin.com/documents/payment/",
                general_settings: "https://www.bookingpressplugin.com/documents/general-settings/",
                company_settings: "https://www.bookingpressplugin.com/documents/company-settings/",
                notification_settings: "https://www.bookingpressplugin.com/documents/notifications-settings/",
                workhours_settings: "https://www.bookingpressplugin.com/documents/work-hours-settings/",
                dayoff_settings: "https://www.bookingpressplugin.com/documents/holidays-settings/",
                payment_settings: "https://www.bookingpressplugin.com/documents/payments-settings/",
                message_settings: "https://www.bookingpressplugin.com/documents/messages-settings/",
                debug_log_settings: "https://www.bookingpressplugin.com/documents/debug-log-settings/"
            };

            vm.read_more_link =
                map[module] || "https://www.bookingpressplugin.com/";
        },

        bpa_fab_floating_action_btn() {
            this.bpa_fab_floating_btn = 1;
        },

        bpa_fab_floating_close_btn() {
            this.bpa_fab_floating_btn = 0;
        },

        open_feature_request_url() {
            window.open('https://ideas.bookingpressplugin.com/', '_blank');
        },

        open_facebook_community_url() {
            window.open('https://www.facebook.com/groups/bookingpress/', '_blank');
        },

        open_youtube_channel_url() {
            window.open('https://www.youtube.com/@BookingPress/', '_blank');
        },
        
        closeNeedHelper() {
            this.needHelpDrawer = false;
            this.needHelpDrawer_add = false;
            document.body.classList.remove('bp-popup-parent--hidden');
        },
        bindDrawerOverlayClose() {
            const vm = this;

            vm.$nextTick(() => {
                const overlay = document.querySelector('.bp-modal-drawer');

                if (!overlay) return;

                overlay.onclick = function (e) {
                    if (e.target === overlay) {
                        vm.closeNeedHelper();
                    }
                };
            });
        },
        
        openNeedHelper(page_name = '', module_name = '', module_title = '') {
            const vm = this;
            vm.helpDrawerData = '';
            vm.is_display_drawer_loader = '1';
            vm.needHelpDrawer = false;

            vm.$nextTick(() => {
                vm.needHelpDrawer = true;
                vm.bindDrawerOverlayClose();
            });

            let help_page_name = 'list_' + vm.request_module;

            if (page_name !== '') {
                help_page_name = page_name;
            }

            let help_module_name = vm.request_module;

            if (module_name != '') {
                help_module_name = module_name;
            }

            if (module_title != '') {
                vm.request_module = module_title;
            }

            vm.request_module = help_module_name;

            this.$nextTick(() => {
                vm.bpa_set_read_more_link();
            });

            const postData = {
                module: help_module_name,
                page: help_page_name,
                type: 'list',
                _wpnonce: BookingPressConfig.rest_nonce
            };

            fetch(rest_url + '/help-drawer', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify(postData)
            })
            .then(res => res.json())
            .then(function (response) {

                vm.is_display_drawer_loader = '0';

                vm.helpDrawerData = response.data.helpDrawerData || response.data || response.message || '';

                this.$nextTick(() => {
                    const elements = document.querySelectorAll('.bpa-help-drawer__body-wrapper');
                    if (elements.length == 0) {
                        const header = document.querySelector('.bpa-hd-header');
                        if (header && header.parentNode) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'bpa-help-drawer__body-wrapper';

                            const next = header.nextElementSibling;

                            if (next) {
                                header.parentNode.insertBefore(wrapper, next);
                                wrapper.appendChild(next);
                            }
                        }
                    }

                    document.querySelectorAll('figure#watch_now_btn').forEach(function (el) {
                        const bookingpress_data_video_link = el.getAttribute('data-video');

                        if (bookingpress_data_video_link) {
                            const a = document.createElement('a');
                            a.href = bookingpress_data_video_link;
                            a.target = '_blank';

                            const img = el.querySelector('img');

                            if (img) {
                                a.appendChild(img.cloneNode(true));
                            }

                            el.innerHTML = '';
                            el.appendChild(a);
                        }
                    });

                });

            }.bind(vm))
            .catch(function (error) {
                console.log(error);
                vm.is_display_drawer_loader = '0';
            });
        },

        openNeedHelper_add(page_name = '', module_name = '', module_title = '') {
            const vm = this;

            vm.helpDrawerData = '';
            vm.is_display_drawer_loader = '1';
            vm.needHelpDrawer_add = !vm.needHelpDrawer_add;
            vm.needHelpDrawer_add = true;

            let help_page_name = 'add_' + module_name;

            if (page_name != '') {
                help_page_name = page_name;
            }

            let help_module_name = module_name;

            if (module_name == '' && window.bookingpress_help_default_module) {
                help_module_name = window.bookingpress_help_default_module;
            }

            if (module_title != '') {
                 vm.request_module = module_title;
            }

            const postData = {
                module: help_module_name,
                page: help_page_name,
                type: 'add'
            };

            fetch(rest_url + '/help-drawer', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify(postData)
            })
            .then(res => res.json())
            .then(data => {

                vm.is_display_drawer_loader = '0';
                vm.helpDrawerData = '';

                if (data.variant === 'success') {
                    vm.helpDrawerData = data.data.helpDrawerData;
                } else {
                    vm.helpDrawerData = data.message || 'Error';
                }
            })
            .catch(error => {
                console.log(error);
                vm.is_display_drawer_loader = '0';
            });
        },

        open_need_help_url() {
            document.body.classList.add('bp-popup-parent--hidden');
            const vm = this;

            const bpa_get_url_param = new URLSearchParams(window.location.search);
            const bpa_get_page = bpa_get_url_param.get('page');
            const bpa_get_action = bpa_get_url_param.get('action');

            const closeFab = () => {
                vm.bpa_fab_floating_btn = 0;
            };

            if (bpa_get_page == 'bookingpress_lite_wizard') {

                vm.request_module = 'lite_wizard';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_quick_start_guide','Quick Start Guide');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_addons') {

                vm.request_module = 'addons';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_license_settings', 'license_settings', 'Add-ons');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_calendar') {

                vm.request_module = 'calendar';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_calendar', 'Calendar');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_appointments') {

                vm.request_module = 'appointments';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_appointments', 'Appointments');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_payments') {

                vm.request_module = 'payments';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_payments', 'Payments');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_customers') {

                vm.request_module = 'customers';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_customers', 'Customers');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_services') {

                vm.request_module = 'services';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_services','Services');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_notifications') {

                vm.request_module = 'notifications';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_notifications', 'Notifications');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_customize' && !bpa_get_action) {

                vm.request_module = 'customize';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_customize', 'Customize');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_customize' && bpa_get_action == 'forms') {

                vm.request_module = 'customize';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_customize', 'Customize');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_customize' && bpa_get_action == 'form_fields') {

                vm.request_module = 'customize';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_customize_field', 'Customize');
                closeFab();

            } else if (bpa_get_page == 'bookingpress') {

                vm.request_module = 'dashboard';
                vm.bpa_set_read_more_link();
                vm.openNeedHelper('list_dashboard', 'Dashboard');
                closeFab();

            } else if (bpa_get_page == 'bookingpress_settings') {

                const selected_tab = sessionStorage.getItem("current_tabname");

                vm.request_module = selected_tab || 'general_settings';
                vm.bpa_set_read_more_link();

                if (!selected_tab) {
                    vm.openNeedHelper('list_general_settings',  'General Settings');

                } else if (selected_tab == 'company_settings') {
                    vm.openNeedHelper("list_company_settings", "Company Settings");

                } else if (selected_tab == 'general_settings') {
                    vm.openNeedHelper('list_general_settings','General Settings');

                } else if (selected_tab == 'notification_settings') {
                    vm.openNeedHelper('list_notification_settings','Notification Settings');

                } else if (selected_tab == 'workhours_settings') {
                    vm.openNeedHelper('list_workhours_settings',  'Working Hours Settings');

                } else if (selected_tab == 'dayoff_settings') {
                    vm.openNeedHelper('list_daysoff_settings', 'Holidays');

                } else if (selected_tab == 'payment_settings') {
                    vm.openNeedHelper('list_payment_settings',  'Payment Settings');

                } else if (selected_tab == 'message_settings') {
                    vm.openNeedHelper('list_message_settings','Message Settings');

                } else if (selected_tab == 'debug_log_settings') {
                    vm.openNeedHelper('list_debug_log_settings', 'Debug Log Settings');
                }

                closeFab();
            }
        }
    });

    const headerConfig = {
        data() {
            let externalData = wp.hooks.applyFilters('bookingpress_help_icon_wrapper_data', {});
            return {
                ...externalData,
                bpa_fab_floating_btn: 0,
                needHelpDrawer: false,
                needHelpDrawer_add: false,
                needHelpDrawerDirection: 'rtl',
                add_needHelpDrawerDirection: 'rtl',
                helpDrawerData: '',
                is_display_drawer_loader: '0',
                request_module: '',
                // read_more_link: ''
                read_more_link: ''
            };
        },

        methods: {
            ...helperExternalMethods
        }
    };

    const BookingPressHelper = createApp(headerConfig);
    BookingPressHelper.use(BookingPressUI);

    window.BookingPressHelper = BookingPressHelper.mount('#bookingpress_help_icon_wrapper');
};

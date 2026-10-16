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

const PaymentsModuleData = getModuleData('bookingpress-payments-loader');

document.addEventListener('DOMContentLoaded', function () {
    initPaymentsLoader();
});

const initPaymentsLoader = () => {

    let PaymentsMethods = wp.hooks.applyFilters('bookingpress_payment_methods', {
        toggleBusy() {
            if (this.is_display_loader == '1') {
                this.is_display_loader = '0'
            } else {
                this.is_display_loader = '1'
            }
        },
        notifyError( message, title ) {
            this.$notify({
                title: title || PaymentsModuleData.common_messages.error,
                message: message || PaymentsModuleData.common_messages.something_went_wrong,
                type: 'error',
                customClass: 'error_notification',
                duration: BookingPressConfig.notification_timeout
            });
        },
        notifyResponse( response ) {
            this.$notify({
                title: response.title,
                message: response.msg,
                type: response.variant,
                customClass: response.variant + '_notification',
                duration: BookingPressConfig.notification_timeout
            });
        },
        postRequest( endpoint, bodyParam ) {
            return fetch(rest_url + endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify({ ...bodyParam, _wpnonce: BookingPressConfig._wpnonce })
            })
            .then(response => response.json());
        },
        handleSelectionChange(val) {
            const payment_items_obj = val;
            this.multipleSelection = [];
            if (payment_items_obj && Object.keys(payment_items_obj).length > 0) {
                Object.values(payment_items_obj).forEach(val => {
                    this.multipleSelection.push({ payment_log_id: val.payment_log_id });
                });
            }
            this.bulk_action = 'bulk_action';
        },
        handleCurrentChange(val) {
            this.currentPage = val;
            this.loadPayments();
        },
        changeCurrentPage(perPage) {
            var total_item = Number(this.totalItems || 0);
            var recored_perpage = perPage;
            var select_page = this.currentPage;
            var current_page = Math.ceil(total_item / recored_perpage);
            if (total_item <= recored_perpage) {
                current_page = 1;
            } else if (select_page >= current_page) {

            } else {
                current_page = select_page;
            }
            return current_page;
        },
        changePaginationSize(selectedPage) {
            var total_recored_perpage = selectedPage;
            var current_page = this.changeCurrentPage(total_recored_perpage);
            this.perPage = Number(selectedPage);
            this.currentPage = current_page;
            this.loadPayments();
        },
        handel_payment_appointment_data({ column, prop, order }) {
            this.bookingpress_previous_row_id = '';
            if (!order) {
                this.bpa_payment_sort_by = '';
                this.bpa_payment_sort_order = '';
            } else {
                this.bpa_payment_sort_by = prop;
                this.bpa_payment_sort_order = order === 'descending' ? 'DESC' : 'ASC';
            }

            this.loadPayments();
        },
        bookingpress_apply_dashboard_filter() {
            const vm = this;

            let bookingpress_module_type = sessionStorage.getItem("bookingpress_module_type");
            let bookingpress_dashboard_filter_start_date = sessionStorage.getItem("bookingpress_dashboard_filter_start_date");
            let bookingpress_dashboard_filter_end_date = sessionStorage.getItem("bookingpress_dashboard_filter_end_date");
            let bookingpress_dashboard_filter_payment_status = sessionStorage.getItem("bookingpress_dashboard_filter_payment_status");

            sessionStorage.removeItem("bookingpress_module_type");
            sessionStorage.removeItem("bookingpress_dashboard_filter_start_date");
            sessionStorage.removeItem("bookingpress_dashboard_filter_end_date");
            sessionStorage.removeItem("bookingpress_dashboard_filter_payment_status");

            if ('payment' == bookingpress_module_type && bookingpress_dashboard_filter_start_date && bookingpress_dashboard_filter_end_date) {
                vm.search_data.search_range = [bookingpress_dashboard_filter_start_date, bookingpress_dashboard_filter_end_date];
                if (bookingpress_dashboard_filter_payment_status) {
                    vm.search_data.search_status = bookingpress_dashboard_filter_payment_status;
                }
            }
        },
        loadPayments(reset_pagination = false, show_loader = true) {
            const vm = this;

            if (show_loader) {
                vm.toggleBusy();
            }

            vm.bookingpress_apply_dashboard_filter();

            if (true == reset_pagination) {
                vm.currentPage = 1;
            }

            let bookingpress_search_data = wp.hooks.applyFilters('bookingpress_modify_payment_search_data', vm.search_data, vm);

            let bodyParam = {
                perpage: vm.perPage,
                currentpage: vm.currentPage,
                search_data: bookingpress_search_data,
                sort_by: vm.bpa_payment_sort_by,
                sort_order: vm.bpa_payment_sort_order
            };

            vm.postRequest('/payments', bodyParam)
            .then(rest_response => {
                if (rest_response.success) {
                    let totalItems = Number(rest_response.data.total || 0);
                    let items = rest_response.data.items || [];

                    if (items.length === 0 && totalItems > 0 && vm.currentPage > 1) {
                        let maxPage = Math.ceil(totalItems / parseInt(vm.perPage));
                        vm.currentPage = maxPage > 0 ? maxPage : 1;
                        vm.loadPayments(false, show_loader);
                        return;
                    }

                    if (show_loader) {
                        vm.toggleBusy();
                    }
                    vm.items = items;
                    vm.totalItems = totalItems;
                } else {
                    if (show_loader) {
                        vm.toggleBusy();
                    }
                    if ('undefined' != typeof rest_response.data) {
                        vm.notifyError(rest_response.data.msg, rest_response.data.title);
                    }
                }

                wp.hooks.doAction('bookingpress_after_get_payment_response', rest_response, vm);
            })
            .catch(error => {
                if (show_loader) {
                    vm.toggleBusy();
                }
                console.error('Error fetching payments:', error);
            });
        },
        loadPaymentWithoutLoader() {
            this.loadPayments(false, false);
        },
        closeBulkAction() {
            this.$refs.multipleTable.clearSelection();
            this.bulk_action = 'bulk_action';
        },
        deletePaymentLog(delete_id) {
            const vm = this;

            vm.postRequest('/payments/bulk-delete', { delete_ids: [{ payment_log_id: delete_id }] })
            .then(rest_response => {
                vm.notifyResponse(rest_response.data);
                if (vm.items.length === 1 && vm.currentPage > 1) {
                    vm.currentPage--;
                }
                vm.loadPayments();
            })
            .catch(error => {
                console.error('Error deleting payment:', error);
                vm.notifyError();
            });
        },
        bulk_actions() {
            const vm = this;

            if ('bulk_action' == vm.bulk_action) {
                vm.notifyError(PaymentsModuleData.common_messages.bulk_action_select);
            } else if (vm.multipleSelection.length == 0) {
                vm.notifyError(PaymentsModuleData.common_messages.bulk_action_no_selection);
            } else if ('delete' == vm.bulk_action) {
                let isLastPageAllDeleted = (vm.multipleSelection.length >= vm.items.length && vm.currentPage > 1);
                vm.postRequest('/payments/bulk-delete', { delete_ids: vm.multipleSelection })
                .then(rest_response => {
                    vm.notifyResponse(rest_response.data);
                    vm.multipleSelection = [];
                    if (isLastPageAllDeleted) {
                        vm.currentPage--;
                    }
                    vm.loadPayments();
                })
                .catch(error => {
                    console.error('Error performing bulk action:', error);
                    vm.notifyError();
                });
            } else {
                wp.hooks.doAction('bookingpress_payment_dynamic_bulk_action', vm);
            }
        },
        resetFilter() {
            const vm = this;
            vm.search_data.search_range = '';
            vm.search_data.search_customer = '';
            vm.search_data.search_service = '';
            vm.search_data.search_status = '';
            wp.hooks.doAction('bookingpress_payment_reset_filter', vm);
            vm.loadPayments();
        },
        bpa_approve_appointment(payment_log_id) {
            const vm = this;

            vm.postRequest('/payments/approve-appointment', { approve_id: payment_log_id })
            .then(rest_response => {
                vm.notifyResponse(rest_response.data);
                vm.loadPaymentWithoutLoader();
            })
            .catch(error => {
                console.error('Error approving appointment:', error);
                vm.notifyError();
            });
        },
        bookingpress_change_status(payment_log_id, new_status, scope_row) {
            const vm = this;

            scope_row.change_status_loader = 1;

            vm.postRequest('/payments/change-status', { payment_log_id: payment_log_id, payment_status: new_status })
            .then(rest_response => {
                scope_row.change_status_loader = 0;
                vm.notifyResponse(rest_response.data);

                if (rest_response.success) {
                    // Marking a payment as paid also approves its appointment, same as the legacy page.
                    if ('1' == new_status) {
                        vm.bpa_approve_appointment(payment_log_id);
                    }
                    vm.loadPaymentWithoutLoader();
                }
            })
            .catch(error => {
                scope_row.change_status_loader = 0;
                console.error('Error updating payment status:', error);
                vm.notifyError();
            });
        },
        bookingpress_get_search_customer_list(query) {
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
                    vm.bookingpress_loading = false;
                    if (rest_response.success) {
                        vm.search_customer_data = rest_response.data;
                    } else {
                        vm.notifyError(rest_response.message);
                    }
                })
                .catch(error => {
                    vm.bookingpress_loading = false;
                    console.error('Error fetching customer list:', error);
                });
            } else {
                vm.search_customer_data = [];
            }
        },
        bookingpress_remove_date_range_picker_focus() {
            if ('undefined' != typeof BookingPressConfig && 'undefined' != typeof BookingPressConfig.is_wp_mobile && true == BookingPressConfig.is_wp_mobile) {
                const datepickerinput = document.querySelectorAll(".el-range-input");
                if (typeof datepickerinput != "undefined") {
                    datepickerinput.forEach((dateItem) => {
                        dateItem.blur();
                    });
                }
            }
        },
        bpa_get_target_parent(elem, selector) {
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
        bookingpress_full_row_clickable(row, column, event) {
            const vm = this;

            if (!row || !row.payment_log_id) {
                return;
            }

            let targetElement = event.target || event.srcElement;
            let parentNode = vm.bpa_get_target_parent(targetElement, '.bpa-table-status-dropdown-wrapper, .bpa-table-actions-wrap, .bpa-mpay-item--foot');

            if (0 < parentNode.length) {
                return;
            }

            vm.$refs.multipleTable.toggleRowExpansion(row);
        },
        bookingpress_row_expand(row, expandedRows) {
            const vm = this;

            if (!row || !row.payment_log_id) {
                return;
            }

            const currentPaymentId = String(row.payment_log_id);

            const isCurrentRowExpanded = Array.isArray(expandedRows)
                ? expandedRows.some((expandedRow) => {
                    return String(expandedRow.payment_log_id) === currentPaymentId;
                })
                : !!expandedRows;

            if (!isCurrentRowExpanded) {
                if (String(vm.bookingpress_previous_row_id) === currentPaymentId) {
                    vm.bookingpress_previous_row_id = '';
                }
                return;
            }

            const previousPaymentId = vm.bookingpress_previous_row_id;

            vm.bookingpress_previous_row_id = currentPaymentId;

            // Keep only one row expanded at a time.
            if (previousPaymentId && String(previousPaymentId) !== currentPaymentId) {
                const previousRow = vm.items.find((item) => {
                    return String(item.payment_log_id) === String(previousPaymentId);
                });

                if (previousRow) {
                    vm.$refs.multipleTable.toggleRowExpansion(previousRow, false);
                }
            }
        },
    });

    const PaymentsConfig = {
        data() {
            let configData = {
                current_screen_size: 'desktop',
                is_display_loader: '0',
                bookingpress_loading: false,
                bookingpress_previous_row_id: '',
                popConfirmPlacement: BookingPressConfig.is_rtl ? 'bottom-start' : 'bottom-end',
                items: [],
                multipleSelection: [],
                totalItems: 0,
                CirclePlusFilled,
                RemoveFilled,
                currentPage: 1,
                perPage: Number(PaymentsModuleData.perPage),
                pagination_length: PaymentsModuleData.pagination_length,
                pagination_length_val: PaymentsModuleData.pagination_length_val,
                pagination_val: [
                    { text: '10', value: '10' },
                    { text: '20', value: '20' },
                    { text: '50', value: '50' },
                    { text: '100', value: '100' },
                    { text: '200', value: '200' },
                    { text: '300', value: '300' },
                    { text: '400', value: '400' },
                    { text: '500', value: '500' },
                ],
                bulk_action: 'bulk_action',
                bulk_options: PaymentsModuleData.bulk_options,
                bpa_payment_sort_by: '',
                bpa_payment_sort_order: '',
                bpa_date_common_date_format: PaymentsModuleData.bpa_date_common_date_format,
                firstDayOfWeek: PaymentsModuleData.firstDayOfWeek,
                search_customer_data: [],
                search_services_data: PaymentsModuleData.search_services_data,
                payment_status_data: PaymentsModuleData.payment_status_data,
                search_data: {
                    search_range: PaymentsModuleData.search_range,
                    search_customer: '',
                    search_service: '',
                    search_status: '',
                },
            };
            configData = wp.hooks.applyFilters('bookingpress_payment_data', configData);

            return {
                ...configData,
            }
        },
        methods: {
            ...PaymentsMethods
        },
        mounted() {
            document.onreadystatechange = () => {
                if (document.readyState == "complete") {
                    setTimeout(function () {
                        if (document.getElementById('bpa-page-loading-loader') != null) {
                            document.getElementById('bpa-page-loading-loader').remove();
                            document.getElementById('bpa-main-container').style.display = 'block';
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

            this.loadPayments();

            const bpaHidePopconfirmPopper = function(popper) {
                if (!popper) return;
                popper.style.setProperty('opacity', '0', 'important');
                popper.style.setProperty('pointer-events', 'none', 'important');
                // Clean up after the close animation completes (300ms + buffer)
                setTimeout(function() {
                    popper.style.removeProperty('opacity');
                    popper.style.removeProperty('pointer-events');
                }, 600);
            };

            const bpaFixPopconfirmClick = function(e) {
                if (!e || !e.target) return;
                const btn = e.target.closest('.bp-ui-popconfirm__cancel, .bp-ui-popconfirm__confirm');
                if (btn) {
                    const popper = btn.closest('.bp-popper.bp-popconfirm');
                    bpaHidePopconfirmPopper(popper);
                }
            };
            document.addEventListener('click', bpaFixPopconfirmClick, true);

            const bpaFixPopconfirmScroll = function() {
                document.querySelectorAll('.bp-popper.bp-popconfirm').forEach(function(popper) {
                    if (window.getComputedStyle(popper).display !== 'none') {
                        bpaHidePopconfirmPopper(popper);
                    }
                });
            };
            window.addEventListener('scroll', bpaFixPopconfirmScroll, { passive: true, capture: true });
            window.addEventListener('wheel', bpaFixPopconfirmScroll, { passive: true, capture: true });
            document.addEventListener('scroll', bpaFixPopconfirmScroll, { passive: true, capture: true });

            // Auto-close open popconfirms when user scrolls the page or container
            const bpaCloseOpenPoppers = () => {
                const openPoppers = document.querySelectorAll('.bp-popper.bp-ui-popconfirm, .bp-popover.bp-ui-popconfirm, .el-popconfirm');
                if (openPoppers.length > 0) {
                    document.body.click();
                }
            };
            window.addEventListener('scroll', bpaCloseOpenPoppers, { passive: true, capture: true });
            window.addEventListener('wheel', bpaCloseOpenPoppers, { passive: true, capture: true });
            document.addEventListener('scroll', bpaCloseOpenPoppers, { passive: true, capture: true }); 

            wp.hooks.doAction('bookingpress_payment_mounted', this);
        }
    };

    const BookingPressPayments = createApp(PaymentsConfig);
    BookingPressPayments.use(BookingPressUI);
    window.PaymentsLoader = BookingPressPayments.mount('#payments-app-root');

}

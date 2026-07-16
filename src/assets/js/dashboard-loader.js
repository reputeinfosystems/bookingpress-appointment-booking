"use strict";

import { createApp, ref } from 'vue';
import { BookingPressUI, CirclePlusFilled, RemoveFilled } from './bookingpress-ui.min.js';

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

const DashboardModuleData = getModuleData('bookingpress-dashboard-loader');

document.addEventListener('DOMContentLoaded', () => {
    initDashboardWrapper();
});

const initDashboardWrapper = () => {

    let bookingpress_appointment_chart = '';
    let revenue_chart = '';
    let customer_chart = '';

    let dashboardLoaderMethods = wp.hooks.applyFilters('bookingpress_dashboard_loader_methods', {
        loadSummary: function(){
            
            fetch(rest_url + '/dashboard/summary', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body:JSON.stringify({
                    selected_filter: this.currently_selected_filter,
                    custom_filter_val: this.custom_filter_val,
                    _wpnonce: BookingPressConfig._wpnonce
                })
            })
            .then( response => response.json() )
            .then( response => {
                if( response.success ){
                    const vm = this;
                    vm.summary_data.total_appoint = response.data.total_appointments;
                    vm.summary_data.approved_appoint = response.data.approved_appointments;
                    vm.summary_data.pending_appoint = response.data.pending_appointments;
                    vm.summary_data.cancelled_appoint = response.data.cancelled_appointments;
                    vm.summary_data.rejected_appoint = response.data.rejected_appointments;
                    vm.summary_data.total_revenue = response.data.total_revenue;
                    vm.summary_data.total_customers = response.data.total_customers;
                    wp.hooks.doAction( 'bookingpress_modify_dashboard_summary_response_data', response, vm );
                }
            })
            .catch( error => {
                console.error('Error fetching summary data:', error);
            });
        },
        loadCharts:function(){
            const vm = this;
            fetch( rest_url + '/dashboard/charts', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body: JSON.stringify({
                    selected_filter: this.currently_selected_filter,
                    custom_filter_val: this.custom_filter_val,
                    _wpnonce: BookingPressConfig._wpnonce
                })
            })
            .then( response => response.json() )
            .then( response => {
                
                if( response.success ){
                    // Handle chart data here
                    vm.appointment_chart_x_axis_data = response.data.chart_x_axis_vals
                    vm.revenue_chart_x_axis_data = response.data.chart_x_axis_vals
                    vm.total_approved_appointments = response.data.approved_appointments
                    vm.total_pending_appointments = response.data.pending_appointments
                    vm.total_revenue = response.data.total_revenue
                    vm.total_customers_data = response.data.total_customers

                    if(bookingpress_appointment_chart != '' && bookingpress_appointment_chart != undefined){
                        bookingpress_appointment_chart.destroy()
                    }

                    if(revenue_chart != '' && revenue_chart != undefined){
                        revenue_chart.destroy()
                    }

                    if(customer_chart != '' && customer_chart != undefined){
                        customer_chart.destroy()
                    }

                    const ctx = document.getElementById('appointments_charts').getContext('2d');
                    bookingpress_appointment_chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: vm.revenue_chart_x_axis_data,
                            datasets: [{
                                label: DashboardModuleData.chart_titles.approved_appointments,
                                data: vm.total_approved_appointments,
                                backgroundColor: [
                                    'rgba(18, 212, 136, 0.3)',
                                ],
                                borderColor: [
                                    'rgba(18, 212, 136, 1)',
                                ],
                                borderWidth: 1
                            },
                            {
                                label: DashboardModuleData.chart_titles.pending_appointments,
                                data: vm.total_pending_appointments,
                                backgroundColor: [
                                    'rgba(245, 174, 65, 0.3)',
                                ],
                                borderColor: [
                                    'rgba(245, 174, 65, 1)',
                                ],
                                borderWidth: 1    
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: false
                                }
                            },
                            maintainAspectRatio: false,
                            responsive: true,
                            plugins:{
                                title: {
                                    display: true,
                                    text: DashboardModuleData.chart_titles.appointments,
                                    font: {
                                        size: 16
                                    }
                                },                              
                                legend: {
                                    onClick: null,
                                    labels: {
                                        font: {
                                            size: 15
                                        }
                                    },                                    
                                },
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                }
                            }
                        }
                    });

                    const ctx2 = document.getElementById('revenue_charts').getContext('2d');
                    revenue_chart = new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: vm.revenue_chart_x_axis_data,
                            datasets: [{
                                label: DashboardModuleData.chart_titles.revenue,
                                data: vm.total_revenue,
                                backgroundColor: [
                                    'rgba(18, 212, 136, 0.3)',
                                ],
                                borderColor: [
                                    'rgba(18, 212, 136, 1)',
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    onClick: null,
                                    labels: {
                                        font: {
                                            size: 15
                                        }
                                    },
                                },
                                title: {
                                    display: true,
                                    text: DashboardModuleData.chart_titles.revenue,
                                    font: {
                                        size: 16
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += vm.chart_currency_symbol + ((context.parsed.y).toFixed(2))
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                }
                            }
                        }
                    });


                    const ctx3 = document.getElementById('customer_charts').getContext('2d');
                    customer_chart = new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: vm.revenue_chart_x_axis_data,
                            datasets: [{
                                label: DashboardModuleData.chart_titles.customers,
                                data: vm.total_customers_data,
                                backgroundColor: [
                                    'rgba(33, 103, 241, 0.3)',
                                ],
                                borderColor: [
                                    'rgba(33, 103, 241, 1)',
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: false
                                }
                            },
                            plugins:{
                                title: {
                                    display: true,
                                    text: DashboardModuleData.chart_titles.customers,
                                    font: {
                                        size: 16
                                    }
                                },
                                legend: {
                                    onClick: null,
                                    labels: {
                                        font: {
                                            size: 15
                                        }
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        font: {
                                            size:15
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            })
            .catch( error => {
                console.error('Error fetching chart data:', error);
            });
        },
        bookingpress_change_status:function( appointment_id, new_status, scope_row ){

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
                        message: DashboardModuleData.status_change_messages.booked_slot,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                } else {
                    this.$notify({
                        title: 'Success',
                        message: DashboardModuleData.status_change_messages.success,
                        type: 'success',
                        customClass: 'success_notification',
                        duration: BookingPressConfig.notification_timeout
                    });

                    this.loadUpcomingAppointments();
                }
            })
            .catch(error => {
                scope_row.change_status_loader = 0;
                console.error('Error updating appointment status:', error);
            });
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
        loadUpcomingAppointments:function(){
            const vm = this;
            fetch(rest_url + '/dashboard/upcoming-appointments', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce
                },
                body: JSON.stringify({
                    _wpnonce: BookingPressConfig._wpnonce
                })
            })
            .then( response => response.json() )
            .then( response => {
                if( response.success ){
                    // Handle the successful response here
                    vm.items = response.data.upcoming_appointments;
                    vm.totalItems = response.data.totalItems;
                    vm.form_field_data = response.data.form_field_data
                }
                
                wp.hooks.doAction( 'bookingpress_modify_appointment_success_response_data', response, vm );
            })
            .catch( error => {
                console.error('Error fetching upcoming appointments:', error);
            });
        },
        bookingpress_dashboard_redirect_filter:function( dashboard_filter, module, status='' ){
            const vm = this;
            let redirect_url = '';
            if( module == 'appointment' ){
                sessionStorage.setItem( "bookingpress_dashboard_filter_appointment_status", status )
                redirect_url = `${DashboardModuleData.redirect_urls.appointments}`;
            } else if( module == 'payment' ){
                sessionStorage.setItem( "bookingpress_dashboard_filter_payment_status", status )
                redirect_url = `${DashboardModuleData.redirect_urls.payments}`;
            } else if( module == 'customer' ){
                redirect_url = `${DashboardModuleData.redirect_urls.customers}`;
            }

            redirect_url = wp.hooks.applyFilters('bookingpress_dashboard_redirect_url', redirect_url, dashboard_filter, module, status);

            if( '' != module ){

                fetch( rest_url + '/dashboard/set-filter-session', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce,
                    },
                    body: JSON.stringify({
                        selected_filter: dashboard_filter,
                        custom_filter_val: vm.custom_filter_val,
                        _wpnonce: BookingPressConfig._wpnonce
                    })
                })
                .then( response => response.json() )
                .then( response => {
                    if( response.success ){
                        sessionStorage.setItem("bookingpress_module_type",module);
                        sessionStorage.setItem("bookingpress_dashboard_filter_start_date",response.data.bookingress_start_date);
                        sessionStorage.setItem("bookingpress_dashboard_filter_end_date",response.data.bookingress_end_date);
                        window.location.href = redirect_url;
                    }
                })
                .catch( error => {
                    console.error('Error setting filter session:', error);
                });

            }
        },
        select_dashboard_custom_date_filter:function( select_value ){
            this.loadSummary()
            this.loadCharts()
        },
        bookingpress_remove_date_range_picker_focus:function(){},
        bookingpress_full_row_clickable:function(row, $el, events){
            this.$refs.multipleTable.toggleRowExpansion(row);
        },
        bookingpress_row_expand:function( row, expanded){
            const vm = this
            if(vm.bookingpress_previous_row_obj != ''){
                vm.$refs.multipleTable.toggleRowExpansion(vm.bookingpress_previous_row_obj, false);
                if(vm.bookingpress_previous_row_obj != row){
                    vm.$refs.multipleTable.toggleRowExpansion(vm.bookingpress_previous_row_obj);
                    vm.bookingpress_previous_row_obj = row;
                }else{
                    if(expanded.length == undefined){
                        vm.$refs.multipleTable.toggleRowExpansion(row);
                    }
                    vm.bookingpress_previous_row_obj = '';
                }
            }else{
                if(expanded.length == undefined){
                    vm.$refs.multipleTable.toggleRowExpansion(row);
                }
                vm.bookingpress_previous_row_obj = row;
            }
        },
        editAppointmentData( index, row ){
            let edit_id = row.appointment_id;

            window.BookingPressAppointmentDialog.appointment_formdata.appointment_update_id = edit_id;
            window.BookingPressAppointmentDialog.openAppointmentDialog();

            this.is_editing_appointment = true;

            window.BookingPressAppointmentDialog.fetchAppointmentDataForEditing(edit_id);
        }
    });
    
    const dashboardConfig = {
        
        data() {
            let DashboardConfigData = {
                currently_selected_filter: 'custom',
                summary_data: {
                    'total_appoint': 0,
                    'approved_appoint': 0,
                    'pending_appoint': 0,
                    'cancelled_appoint': 0,
                    'rejected_appoint': 0,
                    'total_revenue': 0,
                    'total_customers': 0
                },
                CirclePlusFilled,
                RemoveFilled,
                items: [],
                is_editing_appointment: false,
                custom_filter_val: DashboardModuleData.custom_filter_val,
                bpa_date_common_date_format: DashboardModuleData.bpa_date_common_date_format,
                bpa_date_time_common_date_format: DashboardModuleData.bpa_date_time_common_date_format,
                current_screen_size: 'desktop',
                appointment_status: DashboardModuleData.appointment_status,
                chart_currency_symbol:DashboardModuleData.chart_currency_symbol,
                appointment_status_class:{
                    "1": "bpa-appointment-status--approved",
                    "2": "bpa-appointment-status--warning",
                    "3": "bpa-appointment-status--cancelled",
                    "4": "bpa-appointment-status--rejected"
                },
                bookingpress_picker_options: [
                    {
                        text: DashboardModuleData.bpa_shortcode_title.today,
                        value: () => {
                            const end = new Date();
                            const start = new Date();                            
                            return [start, end];
                        }
                    },
                    {
                        text: DashboardModuleData.bpa_shortcode_title.yesterday,
                        value: () => {
                            const end = new Date();
                            const start = new Date();
                            start.setDate(start.getDate() - 1);
                            end.setDate(end.getDate() - 1);
                            return [start, end];
                        }
                    },
                    {
                        text: DashboardModuleData.bpa_shortcode_title.tomorrow,
                        value: () => {
                            const end = new Date();
                            const start = new Date();
                            start.setDate(start.getDate() + 1);
                            end.setDate(end.getDate() + 1);
                            return [start, end];
                        }
                    },
                    {
                        text: DashboardModuleData.bpa_shortcode_title.this_week,
                        value: () => {
                            var bookingpress_date_obj = new Date();
                            var first_date = (bookingpress_date_obj.getDate() + 1) - bookingpress_date_obj.getDay();
                            var end_date = first_date + 6;
                            var first_date_obj = new Date(bookingpress_date_obj);
                            first_date_obj.setDate(first_date);
                            var end_date_obj = new Date(bookingpress_date_obj);
                            end_date_obj.setDate(end_date);
                            return [first_date_obj, end_date_obj];
                        }
                    },
                    {
                        text: DashboardModuleData.bpa_shortcode_title.last_week,
                        value: () => {
                            var first_date_obj = new Date(moment().day(-7));
                            var end_date_obj = new Date(moment().day(-1));
                            return [first_date_obj, end_date_obj];
                        }
                    }, 
                    {
                        text: DashboardModuleData.bpa_shortcode_title.this_month,
                        value: () => {
                            var bookingpress_current_month_obj = new Date();
                            var bookingpress_firstday = new Date(bookingpress_current_month_obj.getFullYear(), bookingpress_current_month_obj.getMonth(), 1);
                            var bookingpress_lastday = new Date(bookingpress_current_month_obj.getFullYear(), bookingpress_current_month_obj.getMonth() + 1, 0);
                            const end = bookingpress_lastday;
                            const start = bookingpress_firstday;
                            return [start, end];
                        }
                    }, 
                    {
                        text: DashboardModuleData.bpa_shortcode_title.last_month,
                        value: () => {
                            var bookingpress_date_obj = new Date();
                            var bookingpress_firstday_prv_month = new Date(bookingpress_date_obj.getFullYear(), bookingpress_date_obj.getMonth() - 1, 1);
                            var bookingpress_lastday_prv_month = new Date(bookingpress_date_obj.getFullYear(), bookingpress_date_obj.getMonth(), 0);
                            return [bookingpress_firstday_prv_month, bookingpress_lastday_prv_month];
                        }
                    },
                    {
                        text: DashboardModuleData.bpa_shortcode_title.this_year,
                        value: () => {
                            var bookingress_date_obj = new Date();
                            var bookingpress_first_day = new Date(bookingress_date_obj.getFullYear(), 0, 1);
                            var bookingpress_last_day = new Date(bookingress_date_obj.getFullYear(), 11, 31);
                            const end = bookingpress_last_day;
                            const start = bookingpress_first_day;
                            return [start, end];
                        }
                    },
                ]
            };
            DashboardConfigData = wp.hooks.applyFilters('bookingpress_dashboard_config_data', DashboardConfigData);
            
            return DashboardConfigData;
        },
        mounted() {
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

            if(window.screen.width >= 1200){
                this.current_screen_size = "desktop";
            }else if(window.screen.width < 1200 && window.screen.width >= 768){
                this.current_screen_size = "tablet";
            }else if(window.screen.width < 768){
                this.current_screen_size = "mobile";
            }  

            window.addEventListener('resize', () => {                                
                if(window.screen.width >= 1200){
                    this.current_screen_size = "desktop";
                }else if(window.screen.width < 1200 && window.screen.width >= 768){
                    this.current_screen_size = "tablet";
                }else if(window.screen.width < 768){
                    this.current_screen_size = "mobile";
                }
            });

            this.loadSummary();
            this.loadCharts();
            this.loadUpcomingAppointments();
        },
        methods: {
            ...dashboardLoaderMethods
        }
    };

    const BookingPressDashboard = createApp(dashboardConfig);
    BookingPressDashboard.use(BookingPressUI);
    window.DashboardLoader = BookingPressDashboard.mount('#dashboard-app-root');
};

wp.hooks.addAction( 'bookingpress_add_appointment_model_reset', 'bookingpress-dashboard', function( vm ){
    window.DashboardLoader.is_editing_appointment = false;
    window.DashboardLoader.loadUpcomingAppointments();
});
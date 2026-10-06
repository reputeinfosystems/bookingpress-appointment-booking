import { createApp } from 'vue';
import { BookingPressUI } from './bookingpress-ui.min.js';

window.__bookingpressNotificationsErrorHandler = (error, instance, info) => {
    console.error('[Notifications] Error:', error);
};

function getModuleData(moduleId) {
    const el = document.getElementById(`wp-script-module-data-${moduleId}`);
    if (!el) return {};
    try {
        return JSON.parse(el.textContent || '{}');
    } catch (e) {
        return {};
    }
}

const defaultNotificationsMap = {
    'approved': { name: 'Appointment Approved', key: 'appointment_approved', label: 'Appointment Approval Notification' },
    'pending': { name: 'Appointment Pending', key: 'appointment_pending', label: 'Appointment Pending Notification' },
    'reject': { name: 'Appointment Rejected', key: 'appointment_rejected', label: 'Appointment Rejection Notification' },
    'cancel': { name: 'Appointment Canceled', key: 'appointment_canceled', label: 'Appointment Cancellation Notification' },
    'share_url': { name: 'Share Appointment URL', key: 'share_appointment', label: 'Share Appointment Notification' },
    'appointment_approved': { name: 'Appointment Approved', key: 'appointment_approved', label: 'Appointment Approval Notification' },
    'appointment_pending': { name: 'Appointment Pending', key: 'appointment_pending', label: 'Appointment Pending Notification' },
    'appointment_rejected': { name: 'Appointment Rejected', key: 'appointment_rejected', label: 'Appointment Rejection Notification' },
    'appointment_canceled': { name: 'Appointment Canceled', key: 'appointment_canceled', label: 'Appointment Cancellation Notification' },
    'share_appointment': { name: 'Share Appointment URL', key: 'share_appointment', label: 'Share Appointment Notification' },
};

document.addEventListener('DOMContentLoaded', () => {
    const notificationsRoot = document.getElementById('notifications-app-root');
    if (!notificationsRoot) {
        return;
    }

    if (typeof window.BookingPressConfig !== 'undefined') {
        window.bookingpress_options = window.bookingpress_options || window.BookingPressConfig;
    }

    const moduleData = getModuleData('bookingpress-notifications-loader');
    const scriptData = Object.assign({}, window.bookingpress_notifications_data || {}, moduleData);

    const defaultStatus = scriptData.default_notification_status || {
        customer: {
            appointment_approved: true,
            appointment_pending: false,
            appointment_rejected: true,
            appointment_canceled: false,
            share_appointment: true,
        },
        employee: {
            appointment_approved: true,
            appointment_pending: false,
            appointment_rejected: true,
            appointment_canceled: false,
            share_appointment: true,
        },
    };

    const app = createApp({
        data() {
            return {
                default_notification_status: defaultStatus,
                bookingpress_selected_default_notification: 'appointment_approved',
                bookingpress_selected_default_notification_db_name: 'Appointment Approved',
                activeTabName: 'customer',
                bookingpress_email_notification_edit_text: 'Appointment Approval Notification',
                bookingpress_email_notification_subject: '',
                bookingpress_active_email_notification: 'appointment_approved',

                bookingpress_customer_placeholders: scriptData.bookingpress_customer_placeholders || [],
                bookingpress_service_placeholders: scriptData.bookingpress_service_placeholders || [],
                bookingpress_company_placeholders: scriptData.bookingpress_company_placeholders || [],
                bookingpress_appointment_placeholders: scriptData.bookingpress_appointment_placeholders || [],

                is_display_loader: '0',
                is_display_save_loader: '0',
                is_disabled: false,
                notification_duration: scriptData.bookingpress_notification_duration || 3000,
            };
        },
        methods: {
            bookingpress_select_email_notification(email_notification_type, email_notification_name, email_notification_key) {
                if (!email_notification_key && defaultNotificationsMap[email_notification_type]) {
                    email_notification_key = defaultNotificationsMap[email_notification_type].key;
                    email_notification_name = email_notification_name || defaultNotificationsMap[email_notification_type].name;
                }

                const notification_label = document.querySelector(`.bpa-en-left-notification-item[data-key="${email_notification_type}"]`);
                let label_text = (notification_label && notification_label.getAttribute('data-label'))
                    ? notification_label.getAttribute('data-label')
                    : (defaultNotificationsMap[email_notification_type]?.label || email_notification_name || email_notification_type);

                this.bookingpress_active_email_notification = email_notification_key || email_notification_type;
                this.bookingpress_email_notification_edit_text = label_text;
                this.bookingpress_selected_default_notification = email_notification_key || email_notification_type;
                this.bookingpress_selected_default_notification_db_name = email_notification_name || defaultNotificationsMap[email_notification_type]?.name || '';

                return this.bookingpress_get_notification_data(this.bookingpress_active_email_notification, this.bookingpress_selected_default_notification_db_name, this.activeTabName);
            },

            bookingpress_reset_default_email_notification() {
                this.bookingpress_email_notification_subject = '';
                const textarea = document.getElementById('bookingpress_email_notification_subject_message');
                if (textarea) {
                    textarea.value = '';
                }
                if (typeof tinyMCE !== 'undefined') {
                    const editor = tinyMCE.get('bookingpress_email_notification_subject_message') || tinyMCE.activeEditor;
                    if (editor && typeof editor.setContent === 'function') {
                        editor.setContent('');
                    }
                }
            },

            async bookingpress_get_all_default_notification_status() {
                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/notifications/status',
                        method: 'POST',
                    });
                    if (response && (response.customer || response.employee)) {
                        this.default_notification_status = response;
                    }
                } catch (error) {
                    console.error('[Notifications] get status error:', error);
                }
            },

            async bookingpress_get_notification_data(email_notification_key, email_notification_name = '', receiver_type = '') {
                this.is_display_loader = '1';
                const notificationName = email_notification_name || this.bookingpress_selected_default_notification_db_name;
                const receiverType = receiver_type || this.activeTabName;

                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/notifications/get',
                        method: 'POST',
                        data: {
                            bookingpress_notification_receiver_type: receiverType,
                            bookingpress_notification_type: 'default',
                            bookingpress_notification_name: notificationName,
                        },
                    });

                    this.bookingpress_reset_default_email_notification();

                    if (response && response.variant === 'success' && response.return_data && Object.keys(response.return_data).length > 0) {
                        const returnData = response.return_data;
                        this.bookingpress_email_notification_subject = returnData.bookingpress_notification_subject || '';

                        const msg = returnData.bookingpress_notification_message || '';
                        const textarea = document.getElementById('bookingpress_email_notification_subject_message');
                        if (textarea) {
                            textarea.value = msg;
                        }

                        setTimeout(() => {
                            if (typeof tinyMCE !== 'undefined') {
                                const editor = tinyMCE.get('bookingpress_email_notification_subject_message') || tinyMCE.activeEditor;
                                if (editor && typeof editor.setContent === 'function') {
                                    editor.setContent(msg);
                                }
                            }
                        }, 100);
                    }
                } catch (error) {
                    console.error('[Notifications] get data error:', error);
                    this.$notify({
                        title: 'Error',
                        message: 'Something went wrong..',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: this.notification_duration,
                    });
                } finally {
                    this.is_display_loader = '0';
                }
            },

            bookingpress_change_tab(tab) {
                let selectedTab = '';
                if (typeof tab === 'string') {
                    selectedTab = tab;
                } else if (tab && tab.props && tab.props.name) {
                    selectedTab = tab.props.name;
                } else if (tab && tab.paneName) {
                    selectedTab = tab.paneName;
                } else if (tab && typeof tab.name === 'string') {
                    selectedTab = tab.name;
                }

                if (selectedTab && (selectedTab === 'customer' || selectedTab === 'employee')) {
                    if (this.activeTabName === selectedTab && this.is_display_loader === '1') {
                        return;
                    }
                    this.activeTabName = selectedTab;
                }

                this.bookingpress_get_notification_data(
                    this.bookingpress_active_email_notification,
                    this.bookingpress_selected_default_notification_db_name,
                    this.activeTabName
                );
            },

            async bookingpress_save_email_notification_data() {
                this.is_disabled = true;
                this.is_display_save_loader = '1';

                if (typeof tinyMCE !== 'undefined') {
                    tinyMCE.triggerSave();
                }

                const textarea = document.getElementById('bookingpress_email_notification_subject_message');
                const notificationMsg = textarea ? textarea.value : '';

                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/notifications/save',
                        method: 'POST',
                        data: {
                            notification_receiver: this.activeTabName,
                            notification_name: this.bookingpress_selected_default_notification_db_name,
                            notification_subject: this.bookingpress_email_notification_subject,
                            notification_msg: notificationMsg,
                            default_notification_status: this.default_notification_status,
                            selected_default_notification: this.bookingpress_selected_default_notification,
                        },
                    });

                    this.$notify({
                        title: response.title || 'Success',
                        message: response.msg || 'Email notifications details updated successfully.',
                        type: response.variant || 'success',
                        customClass: (response.variant || 'success') + '_notification',
                        duration: this.notification_duration,
                    });
                } catch (error) {
                    console.error('[Notifications] save data error:', error);
                    this.$notify({
                        title: 'Error',
                        message: (error && error.message) ? error.message : 'Something went wrong..',
                        type: 'error',
                        customClass: 'error_notification',
                        duration: this.notification_duration,
                    });
                } finally {
                    this.is_disabled = false;
                    this.is_display_save_loader = '0';
                }
            },

            bookingpress_insert_placeholder(selected_tag) {
                const textarea = document.getElementById('bookingpress_email_notification_subject_message');
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('bookingpress_email_notification_subject_message') && !tinyMCE.get('bookingpress_email_notification_subject_message').isHidden()) {
                    tinyMCE.get('bookingpress_email_notification_subject_message').execCommand('mceInsertContent', false, selected_tag);
                } else if (textarea) {
                    const currentVal = textarea.value || '';
                    const startPos = textarea.selectionStart || 0;
                    const endPos = textarea.selectionEnd || 0;

                    const beforeString = currentVal.substring(0, startPos);
                    const afterString = currentVal.substring(endPos, currentVal.length);

                    textarea.value = beforeString + selected_tag + afterString;
                    textarea.selectionStart = startPos + selected_tag.length;
                    textarea.selectionEnd = startPos + selected_tag.length;
                    textarea.focus();
                }
            },
        },
        async mounted() {
            await this.bookingpress_get_all_default_notification_status();
            await this.bookingpress_select_email_notification('approved', 'Appointment Approved', 'appointment_approved');

            // Remove loading overlays and make container visible
            const loaders = document.querySelectorAll('#bpa-page-loading-loader, #bpa-page-loading-loader-2, #bpa-page-loading-loader-3');
            loaders.forEach(el => el.remove());

            const mainContainer = document.getElementById('bpa-main-container');
            if (mainContainer) {
                mainContainer.style.display = 'block';
            }

            const vcloakElements = document.querySelectorAll('[v-cloak]');
            vcloakElements.forEach(el => el.removeAttribute('v-cloak'));

            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 100);
        },
    });

    app.use(BookingPressUI);
    app.config.errorHandler = window.__bookingpressNotificationsErrorHandler;

    try {
        const mountedApp = app.mount('#notifications-app-root');
        window.notificationsApp = mountedApp;
        window.notificationsVue3App = app;
    } catch (mountError) {
        console.error('[Notifications] mount failed:', mountError);
        throw mountError;
    }
});

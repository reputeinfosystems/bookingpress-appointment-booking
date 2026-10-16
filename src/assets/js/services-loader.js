import { createApp } from 'vue';
import { BookingPressUI } from './bookingpress-ui.min.js';
import { Sortable } from './vuedraggable.min.js';

// Error Handler 

window.__bookingpressServicesErrorHandler = (error, instance, info) => {
    console.error('[Vue3 Services] Error:', error);
};

function __(text, domain = 'bookingpress-appointment-booking') {
    if (typeof window.wp !== 'undefined' && typeof window.wp.i18n !== 'undefined' && typeof window.wp.i18n.__ === 'function') {
        return window.wp.i18n.__(text, domain);
    }
    return text;
}

function getModuleData(moduleId) {
    const el = document.getElementById(`wp-script-module-data-${moduleId}`);
    if (!el) return {};
    try {
        return JSON.parse(el.textContent || '{}');
    } catch (e) {
        return {};
    }
}

/**
 * Extract items array from REST response regardless of shape.
 */
function extractItems(response) {
    if (!response) return [];
    if (response.items) return Array.isArray(response.items) ? response.items : Object.values(response.items);
    if (Array.isArray(response)) return response;
    if (response.data) {
        if (Array.isArray(response.data)) return response.data;
        if (response.data.items) return Array.isArray(response.data.items) ? response.data.items : Object.values(response.data.items);
        if (response.data.data && response.data.data.items) {
            return Array.isArray(response.data.data.items) ? response.data.data.items : Object.values(response.data.data.items);
        }
    }
    if (response.success && response.data) {
        if (response.data.items) return Array.isArray(response.data.items) ? response.data.items : Object.values(response.data.items);
    }
    return [];
}

/**
 * Normalize duration unit string ('m', 'h', 'd')
 */
function normalizeDurationUnit(unit) {
    if (!unit) return 'm';
    const u = String(unit).trim().toLowerCase();
    if (u === 'h' || u === 'hours' || u === 'hour' || u === 'hr' || u === 'hrs') return 'h';
    if (u === 'd' || u === 'days' || u === 'day') return 'd';
    return 'm';
}

document.addEventListener('DOMContentLoaded', () => {

    const servicesRoot = document.getElementById('services-app-root');
    if (!servicesRoot) {
        return;
    }

    const moduleData = getModuleData('bookingpress-services-loader');
    const scriptData = Object.assign({}, window.bookingpress_services_data || {}, moduleData);

    const app = createApp({
        data() {
            return {
                enabled: true,
                dragging: false,

                // Services list
                items: [],
                is_display_loader: '0',

                // Filter
                search_categories: (scriptData.search_categories || []).map(cat => ({
                    bookingpress_category_id: String(cat.bookingpress_category_id || cat.category_id || ''),
                    bookingpress_category_name: cat.bookingpress_category_name || cat.category_name || '',
                })),
                search_service_name: '',
                search_service_category: '',

                // Bulk actions
                is_multiple_checked: false,
                multipleSelection: [],
                bulk_action: 'bulk_action',
                bulk_options: (scriptData.bulk_options && scriptData.bulk_options.length) ? scriptData.bulk_options : [
                    { value: 'bulk_action', label: __('Bulk Action', 'bookingpress-appointment-booking') },
                    { value: 'delete', label: __('Delete', 'bookingpress-appointment-booking') },
                ],

                // Category modals
                open_manage_category_modal: false,
                open_add_category_modal: false,
                add_category_from_service_modal: false,
                _prev_service_category: '0',
                category_items: [],
                service_category: {
                    service_category_update_id: 0,
                    service_category_name: '',
                },
                categoryRules: {
                    service_category_name: [
                        { required: true, message: __('Please enter category name', 'bookingpress-appointment-booking'), trigger: 'blur', whitespace: true },
                        {
                            validator: (rule, value, callback) => {
                                if (!value || (typeof value === 'string' && value.trim() === '')) {
                                    callback(new Error(__('Please enter category name', 'bookingpress-appointment-booking')));
                                    return;
                                }
                                callback();
                            },
                            trigger: ['blur', 'change']
                        }
                    ],
                },

                // Service add/edit modal
                open_service_modal: false,
                is_display_save_loader: '0',
                is_display_category_save_loader: '0',
                is_disabled: false,
                upload_service_avatar_url: (typeof BookingPressConfig !== 'undefined' && BookingPressConfig.rest_url ? BookingPressConfig.rest_url : (window.wpApiSettings && window.wpApiSettings.root ? window.wpApiSettings.root + 'bookingpress-app/v1' : '/wp-json/bookingpress-app/v1')) + '/services/upload-image',
                upload_headers: {
                    'X-WP-Nonce': (typeof BookingPressConfig !== 'undefined' && BookingPressConfig.rest_nonce ? BookingPressConfig.rest_nonce : (window.wpApiSettings ? window.wpApiSettings.nonce : '')),
                },
                service: {
                    service_update_id: 0,
                    service_name: '',
                    service_category: '0',
                    service_duration_val: (scriptData.default_service_duration_val !== undefined ? parseInt(scriptData.default_service_duration_val) : (scriptData.service && scriptData.service.service_duration_val !== undefined ? parseInt(scriptData.service.service_duration_val) : 30)),
                    service_duration_unit: (scriptData.default_service_duration_unit !== undefined ? scriptData.default_service_duration_unit : (scriptData.service && scriptData.service.service_duration_unit !== undefined ? scriptData.service.service_duration_unit : 'm')),
                    service_price: '',
                    service_description: '',
                    service_image: '',
                    service_image_name: '',
                    service_attachment_id: 0,
                },
                service_price_currency: scriptData.service_price_currency || '$',
                price_number_of_decimals: scriptData.price_number_of_decimals || '2',
                rules: Object.assign({
                    service_name: [
                        { required: true, message: __('Please enter service name', 'bookingpress-appointment-booking'), trigger: 'blur' },
                    ],

                    service_duration_val: [
                        { required: true, message: __('Please enter duration', 'bookingpress-appointment-booking'), trigger: 'blur' },
                    ],
                    service_price: [
                        { required: true, message: __('Please enter price', 'bookingpress-appointment-booking'), trigger: 'blur' },
                    ],
                }, (scriptData && scriptData.rules) ? scriptData.rules : {}),
            };
        },

        watch: {
            open_manage_category_modal(val) {
                if (val) {
                    this.initCategoriesSortable();
                } else {
                    if (document.activeElement && typeof document.activeElement.blur === 'function') {
                        document.activeElement.blur();
                    }
                }
            },
            open_service_modal(val) {
                if (val) {
                    this.bookingpress_enable_modal();
                } else {
                    this.bookingpress_disable_modal();
                }
            },
        },

        mounted() {
            document.querySelectorAll('[v-cloak]').forEach(el => el.removeAttribute('v-cloak'));

            window.vm = this;
            window.vm2 = this;

            this.loadServices();
            this.loadCategories();

            // Auto-close open popconfirms when user scrolls the page or container (matching Vue 2 behavior)
            const bpaCloseOpenPoppers = () => {
                const openPoppers = document.querySelectorAll('.bp-popper.bp-ui-popconfirm, .bp-popover.bp-ui-popconfirm, .el-popconfirm');
                if (openPoppers.length > 0) {
                    document.body.click();
                }
            };
            window.addEventListener('scroll', bpaCloseOpenPoppers, { passive: true, capture: true });
            window.addEventListener('wheel', bpaCloseOpenPoppers, { passive: true, capture: true });
            document.addEventListener('scroll', bpaCloseOpenPoppers, { passive: true, capture: true });
        },

        methods: {
            bookingpress_enable_modal() {
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';
                const mainContainer = document.getElementById('all-page-main-container');
                if (mainContainer) {
                    mainContainer.style.overflow = 'hidden';
                }
            },
            bookingpress_disable_modal() {
                if (document.body.classList.contains("el-popup-parent--hidden")) {
                    document.body.classList.remove("el-popup-parent--hidden");
                    document.body.style.paddingRight = "0px";
                }
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
                const mainContainer = document.getElementById('all-page-main-container');
                if (mainContainer) {
                    mainContainer.style.overflow = '';
                }
            },
            // ─── Services ────────────────────────────────────────────────────────

            async loadServices() {

                this.is_display_loader = '1';

                try {
                    const reqData = {
                        search_service_name: this.search_service_name,
                        search_service_category: this.search_service_category,
                    };

                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services',
                        method: 'POST',
                        data: reqData,
                    });

                    this.items = extractItems(response);
                    if (this.items.length === 0 && response && response.services) {
                        this.items = Array.isArray(response.services) ? response.services : Object.values(response.services);
                    }
                    if (this.items.length === 0 && response && response.data && response.data.services) {
                        this.items = Array.isArray(response.data.services) ? response.data.services : Object.values(response.data.services);
                    }

                    this.initServicesSortable();

                } catch (error) {
                    console.error('[Vue3 Services] loadServices ERROR:', error);
                    if (error.code) console.error('[Vue3 Services] Error code:', error.code);
                    if (error.message) console.error('[Vue3 Services] Error message:', error.message);

                    if (error.message && error.message.includes('vue')) {
                        console.error('[Vue3 Services] Vue-related error detected:', error.message);
                    }

                    this.items = [];
                } finally {
                    this.is_display_loader = '0';
                    const loaders = document.querySelectorAll('#bpa-page-loading-loader, #bpa-page-loading-loader-2, #bpa-page-loading-loader-3');
                    loaders.forEach(el => el.remove());
                    const mainContainer = document.getElementById('bpa-main-container');
                    if (mainContainer) {
                        mainContainer.style.display = 'block';
                    }
                    document.querySelectorAll('[v-cloak]').forEach(el => {
                        el.removeAttribute('v-cloak');
                    });
                    this.initServicesSortable();
                }
            },

            resetFilter() {
                this.search_service_name = '';
                this.search_service_category = '';
                this.loadServices();
            },

            // ─── Bulk / selection ────────────────────────────────────────────────

            selectAllServices(val) {
                this.items.forEach(item => {
                    if (!item.service_bulk_action) {
                        item.selected = val;
                    }
                });
                this.updateSelection();
            },

            handleSelectionChange(val, serviceId) {
                this.updateSelection();
            },

            updateSelection() {
                this.multipleSelection = this.items.filter(item => item.selected);
                const selectableItems = this.items.filter(item => !item.service_bulk_action);
                this.is_multiple_checked = selectableItems.length > 0 && this.multipleSelection.length === selectableItems.length;
            },

            clearBulkAction() {
                this.multipleSelection = [];
                this.is_multiple_checked = false;
                this.items.forEach(item => { item.selected = false; });
                this.bulk_action = 'bulk_action';
            },

            async delete_bulk_services() {
                if (this.bulk_action === 'bulk_action') {
                    if (typeof this.$notify === 'function') {
                        this.$notify({
                            title: __('Error', 'bookingpress-appointment-booking'),
                            message: __('Please select any action.', 'bookingpress-appointment-booking'),
                            type: 'error',
                            customClass: 'error_notification',
                        });
                    }
                    return;
                }
                if (this.multipleSelection.length === 0) {
                    return;
                }
                const deleteIds = this.multipleSelection.map(item => item.service_id);
                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/bulk-delete',
                        method: 'POST',
                        data: { delete_ids: deleteIds },
                    });
                    if (response && response.msg && typeof this.$notify === 'function') {
                        this.$notify({
                            title: response.title || __('Success', 'bookingpress-appointment-booking'),
                            message: response.msg,
                            type: response.variant || 'success',
                            customClass: (response.variant || 'success') + '_notification',
                        });
                    }
                    this.clearBulkAction();
                    this.loadServices();
                } catch (error) {
                    console.error('[Vue3 Services] bulk delete ERROR:', error);
                }
            },

            open_add_service_modal(action) {
                if (action === 'add') {
                    this.is_display_loader = '0';
                    this.service = {
                        service_update_id: 0,
                        service_name: '',
                        service_category: '0',
                        service_duration_val: (scriptData.default_service_duration_val !== undefined ? parseInt(scriptData.default_service_duration_val) : (scriptData.service && scriptData.service.service_duration_val !== undefined ? parseInt(scriptData.service.service_duration_val) : 30)),
                        service_duration_unit: (scriptData.default_service_duration_unit !== undefined ? scriptData.default_service_duration_unit : (scriptData.service && scriptData.service.service_duration_unit !== undefined ? scriptData.service.service_duration_unit : 'm')),
                        service_price: '',
                        service_description: '',
                        service_image: '',
                        service_image_name: '',
                        service_attachment_id: 0,
                    };
                    this._prev_service_category = '0';
                    this.$nextTick(() => {
                        if (this.$refs.serviceRef && typeof this.$refs.serviceRef.resetFields === 'function') {
                            this.$refs.serviceRef.resetFields();
                        }
                        if (this.$refs.avatarRef && typeof this.$refs.avatarRef.clearFiles === 'function') {
                            this.$refs.avatarRef.clearFiles();
                        }
                    });
                }
                this.open_service_modal = true;
            },

            closeServiceModal() {
                this.open_service_modal = false;
                this.is_display_loader = '0';
                this.$nextTick(() => {
                    if (this.$refs.avatarRef && typeof this.$refs.avatarRef.clearFiles === 'function') {
                        this.$refs.avatarRef.clearFiles();
                    }
                    if (this.$refs.serviceRef && typeof this.$refs.serviceRef.resetFields === 'function') {
                        this.$refs.serviceRef.resetFields();
                    }
                });
            },

            async saveServiceData() {
                if (this.$refs.serviceRef) {
                    let valid = false;
                    try {
                        valid = await this.$refs.serviceRef.validate();
                    } catch (e) {
                        valid = false;
                    }
                    if (!valid) {
                        return;
                    }
                }

                this.is_display_save_loader = '1';
                this.is_disabled = true;
                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/save',
                        method: 'POST',
                        data: this.service,
                    });
                    if (response && response.variant === 'error') {
                        if (typeof this.$notify === 'function') {
                            this.$notify({
                                title: response.title || __('Error', 'bookingpress-appointment-booking'),
                                message: response.msg || __('Something went wrong..', 'bookingpress-appointment-booking'),
                                type: 'error',
                                customClass: 'error_notification',
                            });
                        }
                        this.is_display_save_loader = '0';
                        this.is_disabled = false;
                        return;
                    }

                    if (response && response.msg && typeof this.$notify === 'function') {
                        this.$notify({
                            title: response.title || __('Success', 'bookingpress-appointment-booking'),
                            message: response.msg,
                            type: response.variant || 'success',
                            customClass: (response.variant || 'success') + '_notification',
                        });
                    }
                    this.closeServiceModal();
                    this.loadServices();
                } catch (error) {
                    console.error('[Vue3 Services] saveServiceData ERROR:', error);
                } finally {
                    this.is_display_save_loader = '0';
                    this.is_disabled = false;
                }
            },

            async editServiceData(serviceId) {
                this.is_display_loader = '1';
                this.open_service_modal = true;
                this.service.service_update_id = serviceId;

                this.$nextTick(() => {
                    if (this.$refs.avatarRef && typeof this.$refs.avatarRef.clearFiles === 'function') {
                        this.$refs.avatarRef.clearFiles();
                    }
                });

                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/edit',
                        method: 'POST',
                        data: { service_id: serviceId },
                    });

                    if (response && response.variant === 'success') {
                        const imgDetails = response.service_image_details;
                        const imgUrl = (imgDetails && imgDetails[0] && imgDetails[0].url) ? imgDetails[0].url : '';
                        const imgName = (imgDetails && imgDetails[0] && imgDetails[0].name) ? imgDetails[0].name : '';

                        const catId = (response.category_id !== undefined && response.category_id !== null && response.category_id !== '') ? response.category_id : (response.bookingpress_category_id || '0');
                        this.service = {
                            service_update_id: response.service_id || serviceId,
                            service_name: response.service_name || '',
                            service_category: String(catId),
                            service_duration_val: parseInt(response.service_duration) || 30,
                            service_duration_unit: normalizeDurationUnit(response.service_duration_unit),
                            service_price: response.service_price || '0.00',
                            service_description: response.service_description || '',
                            service_image: imgUrl,
                            service_image_name: imgName,
                            service_attachment_id: 0,
                        };
                        this._prev_service_category = String(catId);
                    } else {
                        const found = this.items.find(i => i.service_id == serviceId);
                        if (found) {
                            const catId = (found.category_id !== undefined && found.category_id !== null && found.category_id !== '') ? found.category_id : (found.bookingpress_category_id || '0');
                            this.service = {
                                service_update_id: found.service_id,
                                service_name: found.service_name || '',
                                service_category: String(catId),
                                service_duration_val: parseInt(found.service_duration_val) || 30,
                                service_duration_unit: normalizeDurationUnit(found.service_duration_unit),
                                service_price: found.service_price_raw || found.service_price || '0.00',
                                service_description: found.service_description || '',
                                service_image: found.service_img_details || '',
                                service_image_name: found.service_image_name || '',
                                service_attachment_id: found.service_attachment_id || 0,
                            };
                        }
                    }
                } catch (error) {
                    console.error('[Vue3 Services] editServiceData error:', error);
                    const found = this.items.find(i => i.service_id == serviceId);
                    if (found) {
                        const catId = (found.category_id !== undefined && found.category_id !== null && found.category_id !== '') ? found.category_id : (found.bookingpress_category_id || '0');
                        this.service = {
                            service_update_id: found.service_id,
                            service_name: found.service_name || '',
                            service_category: String(catId),
                            service_duration_val: parseInt(found.service_duration_val) || 30,
                            service_duration_unit: normalizeDurationUnit(found.service_duration_unit),
                            service_price: found.service_price_raw || found.service_price || '0.00',
                            service_description: found.service_description || '',
                            service_image: found.service_img_details || '',
                            service_image_name: found.service_image_name || '',
                            service_attachment_id: found.service_attachment_id || 0,
                        };
                    }
                } finally {
                    this.is_display_loader = '0';
                }
            },

            openEditService(serviceId) {
                return this.editServiceData(serviceId);
            },

            async duplicateService(serviceId) {
                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/duplicate',
                        method: 'POST',
                        data: { service_id: serviceId },
                    });
                    if (response && response.msg && typeof this.$notify === 'function') {
                        this.$notify({
                            title: response.title || __('Success', 'bookingpress-appointment-booking'),
                            message: response.msg,
                            type: response.variant || 'success',
                            customClass: (response.variant || 'success') + '_notification',
                        });
                    }
                    await this.loadServices();
                    this.multipleSelection = [];
                    const dupId = response && (response.duplicate_serv_id || (response.data && response.data.duplicate_serv_id));
                    if (dupId) {
                        this.openEditService(dupId);
                    }
                } catch (error) {
                    console.error('[Vue3 Services] duplicateService ERROR:', error);
                }
            },

            bookingpress_duplicate_service(serviceId) {
                return this.duplicateService(serviceId);
            },

            async deleteService(serviceId) {
                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/delete',
                        method: 'POST',
                        data: { service_id: serviceId },
                    });
                    if (response && response.msg && typeof this.$notify === 'function') {
                        this.$notify({
                            title: response.title || __('Success', 'bookingpress-appointment-booking'),
                            message: response.msg,
                            type: response.variant || 'success',
                            customClass: (response.variant || 'success') + '_notification',
                        });
                    }
                    this.loadServices();
                } catch (error) {
                    console.error('[Vue3 Services] deleteService ERROR:', error);
                }
            },

            async loadCategories() {
                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/categories',
                        method: 'POST',
                    });
                    this.category_items = extractItems(response);

                    // Refresh search_categories used in filter + service form dropdowns
                    this.search_categories = this.category_items.map(cat => ({
                        bookingpress_category_id: String(cat.category_id || cat.bookingpress_category_id || ''),
                        bookingpress_category_name: cat.category_name || cat.bookingpress_category_name || '',
                    }));

                    this.initCategoriesSortable();
                } catch (error) {
                    console.error('[Vue3 Services] loadCategories ERROR:', error);
                }
            },

            open_add_category_modal_func(currentElement) {
                this.service_category = {
                    service_category_update_id: 0,
                    service_category_name: '',
                };
                this.open_add_category_modal = true;
                if (currentElement) {
                    this.bpa_adjust_popup_position(currentElement, '#bpa_category_add_modal.bp-dialog, div#bpa_category_add_modal .bp-dialog, div#bpa_category_add_modal, .bpa-dialog--add-category', '', 'right');
                }
                this.$nextTick(() => {
                    if (this.$refs.serviceCategoryRef && typeof this.$refs.serviceCategoryRef.resetFields === 'function') {
                        this.$refs.serviceCategoryRef.resetFields();
                    }
                });
            },

            // open add-category modal
            onServiceCategoryChange(val) {
                if (val === '__add_category__') {
                    // Revert the dropdown to the previous valid selection immediately
                    this.$nextTick(() => {
                        this.service.service_category = this._prev_service_category;
                    });
                    
                    this.add_category_from_service_modal = true;
                    this.service_category = {
                        service_category_update_id: 0,
                        service_category_name: '',
                    };
                    this.open_add_category_modal = true;

                    this.$nextTick(() => {
                        let targetElement = null;
                        if (this.$refs.serviceCategorySelectRef) {
                            targetElement = this.$refs.serviceCategorySelectRef.$el || this.$refs.serviceCategorySelectRef;
                        }
                        if (!targetElement) {
                            targetElement = document.getElementById('service_category_select')
                                || document.querySelector('#service_category_select')
                                || document.querySelector('.bpa-dialog--fullscreen__services [prop="service_category"] .bp-ui-select')
                                || document.querySelector('.bpa-dialog--fullscreen__services [prop="service_category"]')
                                || document.querySelector('.bpa-dialog--fullscreen__services .bp-ui-select');
                        }
                        if (targetElement) {
                            this.bpa_adjust_popup_position(targetElement, '#bpa_category_add_modal.bp-dialog, div#bpa_category_add_modal .bp-dialog, div#bpa_category_add_modal, .bpa-dialog--add-category', '', 'right');
                        }

                        if (this.$refs.serviceCategoryRef && typeof this.$refs.serviceCategoryRef.resetFields === 'function') {
                            this.$refs.serviceCategoryRef.resetFields();
                        }
                    });
                } else {
                    // Store as the last valid category
                    this._prev_service_category = val;
                }
            },

            editServiceCategoryData(catId, currentElement) {
                const found = this.category_items.find(c => c.category_id == catId);
                if (found) {
                    this.service_category = {
                        service_category_update_id: catId,
                        service_category_name: found.category_name,
                    };
                    this.open_add_category_modal = true;
                    if (currentElement) {
                        this.bpa_adjust_popup_position(currentElement, '#bpa_category_add_modal.bp-dialog, div#bpa_category_add_modal .bp-dialog, div#bpa_category_add_modal, .bpa-dialog--add-category', '', 'right');
                    }
                }
            },

            async saveCategoryDetails() {
                if (this.is_display_category_save_loader === '1' || this.is_disabled) {
                    return;
                }

                if (this.$refs.serviceCategoryRef) {
                    let valid = false;
                    try {
                        valid = await this.$refs.serviceCategoryRef.validate();
                    } catch (e) {
                        valid = false;
                    }
                    if (!valid) return;
                }

                if (!this.service_category.service_category_name || (typeof this.service_category.service_category_name === 'string' && this.service_category.service_category_name.trim() === '')) {
                    return;
                }

                this.is_display_category_save_loader = '1';
                this.is_disabled = true;
                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/categories/save',
                        method: 'POST',
                        data: this.service_category,
                    });
                    if (response && response.variant === 'error') {
                        if (typeof this.$notify === 'function') {
                            this.$notify({
                                title: response.title || __('Error', 'bookingpress-appointment-booking'),
                                message: response.msg || __('Please add valid data for category name.', 'bookingpress-appointment-booking'),
                                type: 'error',
                                customClass: 'error_notification',
                            });
                        }
                        return;
                    }
                    if (response && response.msg && typeof this.$notify === 'function') {
                        this.$notify({
                            title: response.title || __('Success', 'bookingpress-appointment-booking'),
                            message: response.msg,
                            type: response.variant || 'success',
                            customClass: (response.variant || 'success') + '_notification',
                        });
                    }
                    this.open_add_category_modal = false;
                    const savedCategoryName = this.service_category.service_category_name;
                    const openedFromServiceModal = this.add_category_from_service_modal;
                    this.add_category_from_service_modal = false;
                    await this.loadCategories();
                    // Auto-select the newly created category in the service form
                    if (openedFromServiceModal && savedCategoryName) {
                        const newCat = this.search_categories.find(
                            cat => cat.bookingpress_category_name === savedCategoryName
                        );
                        if (newCat) {
                            this.service.service_category = String(newCat.bookingpress_category_id);
                        }
                    } else {
                        this.loadServices();
                    }
                } catch (error) {
                    console.error('[Vue3 Services] saveCategoryDetails error:', error);
                } finally {
                    this.is_display_category_save_loader = '0';
                    this.is_disabled = false;
                }
            },

            bpa_get_target_parent(elem, selector) {
                let parents = [];
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

            bpa_adjust_popup_position(currentElement, selector, sourceCls = '', position = '', view = '') {
                if (!currentElement) return;

                let buttonElm = null;
                if (currentElement.$el) {
                    buttonElm = currentElement.$el;
                } else if (currentElement instanceof HTMLElement || (currentElement && currentElement.nodeType === 1)) {
                    buttonElm = currentElement;
                } else if (currentElement.target || currentElement.currentTarget || typeof currentElement.composedPath === 'function') {
                    let target = currentElement.target || currentElement.currentTarget;
                    if (target) {
                        if (target.nodeName === 'BUTTON' || (sourceCls && target.classList && target.classList.contains(sourceCls))) {
                            buttonElm = target;
                        } else if (typeof target.closest === 'function') {
                            buttonElm = target.closest('button') || (sourceCls ? target.closest('.' + sourceCls) : null) || target.closest('.bpa-btn') || target;
                        } else {
                            buttonElm = target;
                        }
                    }

                    if (!buttonElm && typeof currentElement.composedPath === 'function') {
                        let paths = currentElement.composedPath();
                        if (paths && paths.length) {
                            for (let i = 0; i < paths.length; i++) {
                                let p = paths[i];
                                if (p && (p.nodeName === 'BUTTON' || (sourceCls && p.classList && p.classList.contains(sourceCls)) || (p.classList && p.classList.contains('bpa-btn')))) {
                                    buttonElm = p;
                                    break;
                                }
                            }
                        }
                    }

                    if (!buttonElm && currentElement.currentTarget) {
                        buttonElm = currentElement.currentTarget;
                    }
                }

                if (!buttonElm && currentElement && typeof currentElement.getBoundingClientRect === 'function') {
                    buttonElm = currentElement;
                }

                if (null !== buttonElm) {
                    let savedRect = null;
                    let savedBtnWidth = 0;
                    let savedBtnHeight = 0;
                    if (buttonElm && typeof buttonElm.getBoundingClientRect === 'function') {
                        savedRect = buttonElm.getBoundingClientRect();
                        savedBtnWidth = buttonElm.offsetWidth || Math.ceil(savedRect.width) || 40;
                        savedBtnHeight = buttonElm.offsetHeight || Math.ceil(savedRect.height) || 40;
                    }

                    const applyPosition = () => {
                        let dialog__wrapper = document.querySelector(selector) 
                            || document.querySelector('#bpa_category_add_modal .bp-dialog')
                            || document.querySelector('#bpa_category_add_modal .el-dialog')
                            || document.querySelector('#bpa_category_add_modal')
                            || document.querySelector('.bpa-dialog--add-category')
                            || document.querySelector('.bpa-dialog--add-extra-service') 
                            || document.querySelector('.bpa-dialog--add-assign-staff');

                        if (null !== dialog__wrapper) {
                            if (dialog__wrapper.classList.contains('bp-overlay') || dialog__wrapper.classList.contains('bp-overlay-dialog') || dialog__wrapper.classList.contains('el-overlay')) {
                                let innerDialog = dialog__wrapper.querySelector('.bp-dialog, .el-dialog, .bpa-dialog, .bpa-dailog__small');
                                if (innerDialog) {
                                    dialog__wrapper = innerDialog;
                                }
                            }

                            let rect = (buttonElm && typeof buttonElm.getBoundingClientRect === 'function' && buttonElm.getBoundingClientRect().width > 0)
                                ? buttonElm.getBoundingClientRect()
                                : savedRect;

                            if (!rect) return false;

                            let pos_x = Math.ceil(rect.left);
                            let pos_y = Math.ceil(rect.top);
                            let btn_height = (buttonElm && buttonElm.offsetHeight) || savedBtnHeight || Math.ceil(rect.height) || 40;
                            let btn_width = (buttonElm && buttonElm.offsetWidth) || savedBtnWidth || Math.ceil(rect.width) || 100;
                            let pos_top = pos_y + btn_height + 12;

                            let viewportWidth = window.innerWidth;
                            let dialogWidth = dialog__wrapper.offsetWidth || 320;

                            dialog__wrapper.style.setProperty('position', 'absolute', 'important');
                            dialog__wrapper.style.setProperty('margin', '0 0 40px 0', 'important');
                            dialog__wrapper.style.setProperty('top', parseInt(pos_top) + 'px', 'important');
                            dialog__wrapper.style.setProperty('right', 'auto', 'important');

                            let button_center = pos_x + (btn_width * 0.5);
                            let ideal_left = (position === 'right') ? (button_center - dialogWidth + 42) : (button_center - 42);
                            let actual_left = ideal_left;

                            if (actual_left + dialogWidth > viewportWidth - 20) {
                                actual_left = viewportWidth - dialogWidth - 20;
                            }
                            if (actual_left < 20) {
                                actual_left = 20;
                            }

                            dialog__wrapper.style.setProperty('left', parseInt(actual_left) + 'px', 'important');

                            let arrow_left = button_center - actual_left - 5;
                            if (arrow_left < 20) {
                                arrow_left = 20;
                            }
                            if (arrow_left > dialogWidth - 30) {
                                arrow_left = dialogWidth - 30;
                            }
                            dialog__wrapper.style.setProperty('--bpa-custom-notification-arrow-left', parseInt(arrow_left) + 'px');

                            let overlayDialog = dialog__wrapper.closest('.bp-overlay-dialog') || dialog__wrapper.closest('.bp-overlay') || dialog__wrapper.closest('.el-overlay');
                            if (overlayDialog) {
                                overlayDialog.scrollTop = 0;
                                overlayDialog.style.setProperty('pointer-events', 'auto', 'important');
                                overlayDialog.style.setProperty('overflow-y', 'auto', 'important');
                                overlayDialog.style.setProperty('background-color', 'transparent', 'important');
                                overlayDialog.style.setProperty('background', 'transparent', 'important');
                            }
                            let modalDialog = dialog__wrapper.closest('.bp-modal-dialog') || dialog__wrapper.closest('.bp-overlay');
                            if (modalDialog) {
                                modalDialog.style.setProperty('pointer-events', 'auto', 'important');
                                modalDialog.style.setProperty('background-color', 'transparent', 'important');
                                modalDialog.style.setProperty('background', 'transparent', 'important');
                            }
                            return true;
                        }
                        return false;
                    };

                    applyPosition();
                    if (typeof requestAnimationFrame === 'function') {
                        requestAnimationFrame(() => {
                            applyPosition();
                        });
                    }
                    setTimeout(() => { applyPosition(); }, 30);
                    setTimeout(() => { applyPosition(); }, 80);
                    setTimeout(() => { applyPosition(); }, 180);
                    setTimeout(() => { applyPosition(); }, 300);
                }
            },

            async deleteServiceCategory(catId) {
                try {
                    const response = await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/categories/delete',
                        method: 'POST',
                        data: { category_id: catId },
                    });
                    if (response && response.msg && typeof this.$notify === 'function') {
                        this.$notify({
                            title: response.title || __('Success', 'bookingpress-appointment-booking'),
                            message: response.msg,
                            type: response.variant || 'success',
                            customClass: (response.variant || 'success') + '_notification',
                        });
                    }
                    await this.loadCategories(); // also refreshes search_categories
                    this.loadServices();
                } catch (error) {
                    console.error('[Vue3 Services] deleteServiceCategory error:', error);
                }
            },

            searchCategoryData(catId) {
                this.search_service_category = catId;
                this.open_manage_category_modal = false;
                this.loadServices();
            },

            async updateServicePos(currentElement) {
                this.dragging = false;
                const new_index = currentElement.newIndex;
                const old_index = currentElement.oldIndex;
                if (new_index === old_index || new_index === undefined || old_index === undefined) {
                    return;
                }
                const page = parseInt(this.currentPage || 1, 10);
                const perPage = parseInt(this.perPage || 10, 10);
                const old_pos = (page - 1) * perPage + old_index + 1;
                const new_pos = (page - 1) * perPage + new_index + 1;

                try {
                    await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/update-position',
                        method: 'POST',
                        data: {
                            old_position: old_pos,
                            new_position: new_pos,
                            currentPage: this.currentPage,
                            perPage: this.perPage,
                        },
                    });
                } catch (error) {
                    console.error('[Vue3 Services] updateServicePos error:', error);
                    if (typeof this.$notify === 'function') {
                        this.$notify({
                            title: __('Error', 'bookingpress-appointment-booking'),
                            message: __('Something went wrong..', 'bookingpress-appointment-booking'),
                            type: 'error',
                            customClass: 'error_notification',
                        });
                    }
                }
            },

            async updateCategoryPos(currentElement) {
                this.dragging = false;
                const new_index = currentElement.newIndex;
                const old_index = currentElement.oldIndex;
                if (new_index === old_index || new_index === undefined || old_index === undefined) {
                    return;
                }
                const old_pos = old_index + 1;
                const new_pos = new_index + 1;

                try {
                    await wp.apiFetch({
                        path: '/bookingpress-app/v1/services/categories/update-position',
                        method: 'POST',
                        data: {
                            old_position: old_pos,
                            new_position: new_pos,
                            currentPage: this.currentPage,
                            perPage: this.perPage,
                        },
                    });
                    await this.loadCategories();
                } catch (error) {
                    console.error('[Vue3 Services] updateCategoryPos error:', error);
                    if (typeof this.$notify === 'function') {
                        this.$notify({
                            title: __('Error', 'bookingpress-appointment-booking'),
                            message: __('Something went wrong..', 'bookingpress-appointment-booking'),
                            type: 'error',
                            customClass: 'error_notification',
                        });
                    }
                }
            },

            initServicesSortable() {
                this.$nextTick(() => {
                    const container = document.getElementById('bpa_services_sortable_container');
                    if (!container) return;
                    if (container._sortable) {
                        container._sortable.destroy();
                    }
                    container._sortable = Sortable.create(container, {
                        handle: '.bpa-card__item--drag-icon-wrap',
                        animation: 150,
                        ghostClass: 'ghost',
                        onEnd: (evt) => {
                            this.updateServicePos({
                                newIndex: evt.newIndex,
                                oldIndex: evt.oldIndex,
                            });
                        },
                    });
                });
            },

            initCategoriesSortable() {
                this.$nextTick(() => {
                    const container = document.getElementById('bpa_categories_sortable_container');
                    if (!container) return;
                    if (container._sortable) {
                        container._sortable.destroy();
                    }
                    container._sortable = Sortable.create(container, {
                        handle: '.bpa-card__item--drag-icon-wrap',
                        animation: 150,
                        ghostClass: 'ghost',
                        onEnd: (evt) => {
                            this.updateCategoryPos({
                                newIndex: evt.newIndex,
                                oldIndex: evt.oldIndex,
                            });
                        },
                    });
                });
            },

            // ─── Image upload ────────────────────────────────────────────────────

            checkUploadedFile(file) {
                const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/webp';
                const isLt1M = file.size / 1024 / 1024 < 1;

                if (!isJPG) {
                    if (typeof this.$notify === 'function') {
                        this.$notify({
                            title: __('Error', 'bookingpress-appointment-booking'),
                            message: __('Avatar picture must be JPG, PNG or WEBP format!', 'bookingpress-appointment-booking'),
                            type: 'error',
                            customClass: 'error_notification',
                        });
                    }
                    return false;
                }
                if (!isLt1M) {
                    if (typeof this.$notify === 'function') {
                        this.$notify({
                            title: __('Error', 'bookingpress-appointment-booking'),
                            message: __('Avatar picture size cannot exceed 1MB!', 'bookingpress-appointment-booking'),
                            type: 'error',
                            customClass: 'error_notification',
                        });
                    }
                    return false;
                }
                return true;
            },

            bookingpress_upload_service_func(response, file, fileList) {
                const vm2 = this;
                if (!response) return;

                // 1. Direct server response passed from on-success
                if (typeof response.error !== 'undefined') {
                    if (response.error === 1 || response.error === '1') {
                        vm2.bookingpress_image_upload_err(response.msg, file, fileList);
                        vm2.service.service_image = '';
                        vm2.service.service_image_name = '';
                        if (vm2.$refs.avatarRef && typeof vm2.$refs.avatarRef.clearFiles === 'function') {
                            vm2.$refs.avatarRef.clearFiles();
                        }
                    } else {
                        vm2.service.service_image = response.upload_url;
                        vm2.service.service_image_name = response.upload_file_name;
                    }
                    return;
                }

                // 2. Fallback: If response is a File object
                const fileObj = (response && response.raw) ? response.raw : ((file && file.raw) ? file.raw : (response instanceof File ? response : null));
                if (fileObj) {
                    const formData = new FormData();
                    formData.append('file', fileObj);

                    const rest_url = (typeof BookingPressConfig !== 'undefined' && BookingPressConfig.rest_url) ? BookingPressConfig.rest_url : (window.wpApiSettings && window.wpApiSettings.root ? window.wpApiSettings.root + 'bookingpress-app/v1' : '/wp-json/bookingpress-app/v1');
                    const nonce = (typeof BookingPressConfig !== 'undefined' && BookingPressConfig.rest_nonce) ? BookingPressConfig.rest_nonce : (window.wpApiSettings ? window.wpApiSettings.nonce : '');

                    fetch(rest_url + '/services/upload-image', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-WP-Nonce': nonce,
                        },
                        body: formData,
                    })
                        .then(res => res.json())
                        .then(resData => {
                            if (resData.error === 1 || resData.error === '1') {
                                vm2.bookingpress_image_upload_err(resData.msg, file, fileList);
                                vm2.service.service_image = '';
                                vm2.service.service_image_name = '';
                                if (vm2.$refs.avatarRef && typeof vm2.$refs.avatarRef.clearFiles === 'function') {
                                    vm2.$refs.avatarRef.clearFiles();
                                }
                            } else {
                                vm2.service.service_image = resData.upload_url;
                                vm2.service.service_image_name = resData.upload_file_name;
                            }
                        })
                        .catch(err => {
                            console.error('[Vue3 Services] upload error:', err);
                            vm2.bookingpress_image_upload_err(__('Something went wrong..', 'bookingpress-appointment-booking'), file, fileList);
                        });
                }
            },

            bookingpress_image_upload_limit(files, fileList) {
                if (this.$refs.avatarRef && typeof this.$refs.avatarRef.clearFiles === 'function') {
                    this.$refs.avatarRef.clearFiles();
                }
                const file = files && files[0] ? files[0] : null;
                if (file && this.$refs.avatarRef && typeof this.$refs.avatarRef.handleStart === 'function') {
                    this.$refs.avatarRef.handleStart(file);
                    return;
                }
                if (this.service.service_image != '') {
                    if (typeof this.$notify === 'function') {
                        this.$notify({
                            title: __('Error', 'bookingpress-appointment-booking'),
                            message: __('Multiple files not allowed', 'bookingpress-appointment-booking'),
                            type: 'error',
                            customClass: 'error_notification',
                        });
                    }
                }
            },

            bookingpress_image_upload_err(err, file, fileList) {
                let bookingpress_err_msg = __('Something went wrong', 'bookingpress-appointment-booking');
                if (err != '' && err != undefined) {
                    bookingpress_err_msg = err;
                }
                if (typeof this.$notify === 'function') {
                    this.$notify({
                        title: __('Error', 'bookingpress-appointment-booking'),
                        message: bookingpress_err_msg,
                        type: 'error',
                        customClass: 'error_notification',
                    });
                }
            },

            bookingpress_remove_service_img() {
                this.service.service_image = '';
                this.service.service_image_name = '';
                this.service.service_attachment_id = 0;
                if (this.$refs.avatarRef && typeof this.$refs.avatarRef.clearFiles === 'function') {
                    this.$refs.avatarRef.clearFiles();
                }
            },

            // ─── Price validation  ────────────────────────────────

            isNumberValidate(value) {
                const val = value.replace(/[^0-9.]/g, '');
                const parts = val.split('.');
                if (parts.length > 2) {
                    this.service.service_price = parts[0] + '.' + parts.slice(1).join('');
                } else {
                    this.service.service_price = val;
                }
            },

            isValidateZeroDecimal(value) {
                this.service.service_price = value.replace(/[^0-9]/g, '');
            },
        },
    });

    app.use(BookingPressUI);

    app.config.errorHandler = window.__bookingpressServicesErrorHandler;

    try {
        const mountedApp = app.mount('#services-app-root');

        // Expose to global scope for debugging
        window.servicesApp = mountedApp;
        window.servicesVue3App = app;

        // Remove v-cloak now that Vue 3 has compiled and mounted the DOM
        const vcloakElements = document.querySelectorAll('[v-cloak]');
        vcloakElements.forEach(el => el.removeAttribute('v-cloak'));

    } catch (mountError) {
        throw mountError;
    }

});

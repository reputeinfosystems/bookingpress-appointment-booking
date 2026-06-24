"use strict";
import { createApp } from 'vue';
import BookingPressUI from './bookingpress-ui.min.js';

document.addEventListener('DOMContentLoaded', () => {
    initHeaderWrapper();
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
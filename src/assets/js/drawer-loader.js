"use strict";
import { createApp } from 'vue';
import BookingPressUI from './bookingpress-ui.min.js';

document.addEventListener('DOMContentLoaded', () => {
    initHeaderWrapper();
});

const initHeaderWrapper = () => {

    let headerExternalMethods = wp.hooks.applyFilters('bookingpress_header_wrapper_methods', {
        bpa_mobile_toggle_menu(){
            this.toggle_drawer = !this.toggle_drawer;
        }
    });
    
    const headerConfig = {
        data(){
            let externalHeaderData = wp.hooks.applyFilters('bookingpress_external_header_data', {
                toggle_drawer: false
            });
            return {
                ...externalHeaderData
            };
        },
        methods: {
            ...headerExternalMethods
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
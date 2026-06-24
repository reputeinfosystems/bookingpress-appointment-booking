"use strict";

import { createApp } from 'vue';
import BookingPressUI from './bookingpress-ui.min.js';

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
    initAddonsWrapper();
});

const initAddonsWrapper = () => {
    const moduleData = getModuleData('bookingpress-addons-loader');

    let addonMountedMethods = wp.hooks.applyFilters('bookingpress_addon_mounted_methods', {
        bookingpress_get_remote_addons_lite_list: function(){

            const vm = this;
            
            vm.is_display_loader = '1';

            fetch(rest_url + '/addons/list', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                }
            })
            .then(res => res.json())
            .then((response) => {
                //console.log("RAW RESPONSE:", response); // Add this
                //vm.bpa_lite_addons_new = response.addons_response || {};
                vm.bpa_lite_addons_new.features = response.addons_response.features || {};
                vm.bpa_lite_addons_new.payment_gateways = response.addons_response.payment_gateways || {};
                vm.bpa_lite_addons_new.integrations = response.addons_response.integrations || {};
                //console.log("SET DATA:", this.bpa_lite_addons_new); // Add this
                vm.is_display_loader = '0';
                //console.log(this.bpa_lite_addons_new)
                vm.appendAddonCss(response.css || '');                 
            })
            .catch((err) => {
                vm.is_display_loader = '0';
                console.error("API ERROR:", err);
            });
        },
        appendAddonCss: function(addonCss){
            if (!addonCss) {
                return;
            }
            const styleNode = document.createElement('style');
            styleNode.setAttribute('type', 'text/css');
            styleNode.appendChild(document.createTextNode(addonCss));
            document.head.appendChild(styleNode);
        }
    });

    const externalAddonData = window.BookingPressExternalData || {};

    const addonsConfig = {
        data() {
            let AddonConfigData = {
                bpa_lite_addons_new: {},     
                wp_nonce: moduleData.wp_nonce || moduleData._wpnonce || '',
                is_display_loader: '0',
                ...externalAddonData
            };
            return AddonConfigData;
        },
        mounted() {
            document.onreadystatechange = () => {
                if (document.readyState == "complete") {
                    setTimeout(function(){
                        if (document.getElementById('bpa-page-loading-loader') != null) {
                            document.getElementById('bpa-page-loading-loader').remove();
                            document.getElementById('bpa-main-container').style.display = 'block';
                        }
                    }, 2000);
                }
            }
            this.bookingpress_get_remote_addons_lite_list();
        },
        methods: {
            ...addonMountedMethods
        },       
    };

    const BookingPressAddons = createApp(addonsConfig);
    BookingPressAddons.use(BookingPressUI);
    window.BookingPressAddons = BookingPressAddons.mount('#addons-app-root');
};

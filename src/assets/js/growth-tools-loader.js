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
    initGrowthtoolsWrapper();
});
const initGrowthtoolsWrapper = () => {

    const moduleData = getModuleData('bookingpress-growth-tools-loader');

    let growthpluginMountedMethods = wp.hooks.applyFilters('bookingpress_growth_plugin_methods', {

        bpa_download_plugins( plugin_data ){
            
            if( plugin_data == 'armember' ){

                const vm = this;
                vm.is_disabled = true;
                vm.is_display_save_loader = '1';
                vm.savebtnloading = true;

                fetch(rest_url + '/growth_tools/bookingpress_get_armember', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce,
                    },
                })
                .then(res => res.json())
                .then((response) => {
                    if(response.variant == 'success' ){    
                        vm.$notify({
                            title: response.title,
                            message: response.msg,
                            type: response.variant,
                            duration: BookingPressConfig.notification_timeout
                        });
                        location.reload();
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
            };

            if( plugin_data == 'arforms' ){

                const vm = this;

                vm.is_disabled = true;
                vm.is_display_arforms_save_loader = '1';
                vm.savebtnloading = true;

                fetch(rest_url + '/growth_tools/bookingpress_get_arforms', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce,
                    },
                })
                .then(res => res.json())
                .then((response) => {
                    if( response.variant == 'success' ){    
                        vm.$notify({
                            title: response.title,
                            message: response.msg,
                            type: response.variant,
                            duration: BookingPressConfig.notification_timeout
                        });
                        location.reload();
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
            };

            if( plugin_data == 'affiliatepress' ){

                const vm = this;

                vm.is_disabled = true;
                vm.is_display_affiliatepress_save_loader = '1';
                vm.savebtnloading = true;

                fetch(rest_url + '/growth_tools/bookingpress_get_affiliatepress', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce,
                    },
                })
                .then(res => res.json())
                .then((response) => {
                    if( response.variant == 'success' ){    
                        vm.$notify({
                            title: response.title,
                            message: response.msg,
                            type: response.variant,
                            duration: BookingPressConfig.notification_timeout
                        });
                        location.reload();
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
            };
            if( plugin_data == 'arprice' ){

                const vm = this;

                vm.is_disabled = true;
                vm.is_display_arprice_save_loader = '1';
                vm.savebtnloading = true;

                fetch(rest_url + '/growth_tools/bookingpress_get_arprice', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': BookingPressConfig.rest_nonce,
                    },
                })
                .then(res => res.json())
                .then((response) => {
                    if( response.variant == 'success' ){    
                        vm.$notify({
                            title: response.title,
                            message: response.msg,
                            type: response.variant,
                            duration: BookingPressConfig.notification_timeout
                        });
                        location.reload();
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
            };
        },

    });

     const GrowthToolsConfig = {
        data() {
            let GrowthPluginConfigData = {
                'is_display_loader'          : '0',
                'is_disabled'                : false,
                'is_display_save_loader'     : '0',
                'is_display_arforms_save_loader' : '0',
                'is_display_arprice_save_loader' : '0',
                'is_display_affiliatepress_save_loader' : '0',
            };
            return GrowthPluginConfigData;
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
            
        },
        methods: {
            ...growthpluginMountedMethods
        },  
    };

    const BookingPressGrowthPlugin = createApp(GrowthToolsConfig);
    BookingPressGrowthPlugin.use(BookingPressUI);
    window.BookingPressGrowthPlugin = BookingPressGrowthPlugin.mount('#growth-tools-app-root');
}
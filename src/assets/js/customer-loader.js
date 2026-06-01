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
    initExportCustomerDialog();
    initImportCustomerDialog();
    initCustomerWrapper();
});

const initImportCustomerDialog = () => {
    const customerModuleData = getModuleData('bookingpress-customer-loader');

    let BookingPressCustomerImportComputedData = wp.hooks.applyFilters( 'bookingpress_customer_import_computed_data', {} );

    let BookingPressCustomerImportMethodData = wp.hooks.applyFilters( 'bookingpress_customer_import_external_methods', {
        bookingpress_remove_import_file(){
            const vm = this;
            vm.import_file_fields = [];
            vm.import_file_name   = "";
        },    
        resetImportModal(){
            const vm = this;
            vm.import_file_fields = [];                 
            vm.import_file_list = [];
            vm.import_file_name = "";
            vm.import_loading = "0";
            vm.complete_import = "0";
            vm.customers_total_count = "0";
            vm.customers_import_count = "0";
            vm.customers_not_import_count = "0";
            vm.is_wordpress_user_create_on_import = false;
            vm.bookingpress_import_fields = JSON.parse(JSON.stringify(customerModuleData.bookingpress_import_fields_org));
            vm.bookingpress_import_field_data = JSON.parse(JSON.stringify(customerModuleData.bookingpress_import_field_data_org));
        },
        checkUploadedImportFile(file){
            const vm2 = this;              
            if(file.type != 'text/csv'){
                vm2.$notify({
                    title: 'Error',
                    message: 'Please upload csv file only',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
                return false
            }else{
                var bpa_image_size = parseInt(file.size / 50000000);
                if(bpa_image_size > 1){
                    vm2.$notify({
                        title: 'Error',
                        message: 'Please upload maximum 50 MB file only',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:BookingPressConfig.notification_timeout
                    });                    
                    return false
                }
            }
        },
        bookingpress_upload_customer_import_file_func(response, file, fileList){
            const vm2 = this
            if(response != ''){
                if(response != "" && response.error == 0){
                    vm2.import_file_fields = response.import_file_fields;
                    vm2.import_file_name = response.import_file_name;
                }else{
                    
                    if( "undefined" != typeof response.error && 1 == response.error ){
                        vm2.bookingpress_image_upload_err( response.msg, file, fileList );
                        customerModuleData.customer.avatar_url = ''
                        customerModuleData.customer.avatar_name = ''
                        vm2.$refs.avatarRef.clearFiles()
                    } else {
                        customerModuleData.customer.avatar_url = response.upload_url;
                        customerModuleData.customer.avatar_name = response.upload_file_name;
                    }

                }
            }
        },   
        bookingpress_image_upload_err(err, file, fileList){
            const vm2 = this
            var bookingpress_err_msg = 'Something went wrong';
            if(err != '' || err != undefined){
                bookingpress_err_msg = err
            }
            vm2.$notify({
                title: 'Error',
                message: bookingpress_err_msg,
                type: 'error',
                customClass: 'error_notification',
                duration:BookingPressConfig.notification_timeout
            });
        },
        bookingpress_image_upload_limit(files, fileList){
            const vm2 = this
                if(customerModuleData.avatar_url != ''){
                vm2.$notify({
                    title: 'Error',
                    message: 'Multiple files not allowed',
                    type: 'error',
                    customClass: 'error_notification',
                    duration:BookingPressConfig.notification_timeout
                });
            }
        },
        importCustomer(form_ref) {
            const vm = this;
            vm.$refs[form_ref].validate((valid) => {
                if (valid) {
                    const postData = {
                        import_file_fields: vm.bookingpress_import_fields,
                        import_file_name:   vm.import_file_name,
                        create_wp_user:     vm.is_wordpress_user_create_on_import,
                    };

                    vm.import_loading  = "1";
                    vm.complete_import = "0";

                    fetch(rest_url + '/customer/import', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce,
                        },
                        body: JSON.stringify(postData)
                    })
                    .then(res => res.json())
                    .then(rest_response => {
                        if (rest_response.variant == 'success') {
                            if (typeof rest_response.customers_total_count != 'undefined') {
                                vm.customers_total_count = rest_response.customers_total_count;
                            }
                            if (typeof rest_response.customers_import_count != 'undefined') {
                                vm.customers_import_count = rest_response.customers_import_count;
                            }
                            if (typeof rest_response.customers_not_import_count != 'undefined') {
                                vm.customers_not_import_count = rest_response.customers_not_import_count;
                            }

                            // CustomerLoader reload karo
                            if ('undefined' != typeof window.CustomerLoader) {
                                window.CustomerLoader.loadCustomers();
                            }

                            wp.hooks.doAction('bookingpress_customer_imported', vm, rest_response);

                            setTimeout(() => {
                                vm.import_loading  = "0";
                                vm.complete_import = "1";
                            }, 1000);

                        } else {
                            vm.import_loading          = "0";
                            vm.import_customer_modal   = false;
                            vm.resetImportModal();
                            vm.$notify({
                                title:       rest_response.title,
                                message:     rest_response.msg,
                                type:        rest_response.variant,
                                customClass: rest_response.variant + '_notification',
                                duration:    BookingPressConfig.notification_timeout
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Import error:', error);
                        vm.import_loading        = "0";
                        vm.import_customer_modal = false;
                        vm.resetImportModal();
                        vm.$notify({
                            title:       'Error',
                            message:     'Something went wrong..',
                            type:        'error',
                            customClass: 'error_notification',
                            duration:    BookingPressConfig.notification_timeout
                        });
                    });

                } else {
                    return false;
                }
            });
        },
        

    } );

    const BookingPressCustomerImportDialogConfig = {
        data() {
            return {    
                import_customer_modal: false,
                complete_import: '0',
                import_file_fields: [],
                import_file_list: [],
                import_file_name: "",
                import_loading: "0",
                customers_total_count: "0",
                customers_import_count: "0",
                customers_not_import_count: "0",
                is_wordpress_user_create_on_import: false,   
                bookingpress_import_fields : JSON.parse(JSON.stringify(customerModuleData.bookingpress_import_fields_org)),
                bookingpress_import_field_data: JSON.parse(JSON.stringify(customerModuleData.bookingpress_import_field_data_org)),
                bookingpress_customer_import_rules: customerModuleData.bookingpress_customer_import_rules,
                upload_import_url: rest_url + '/customer/import-file',
                upload_headers: {
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
            }
        },
        methods: {
            ...BookingPressCustomerImportMethodData
        },
        computed:{
            ...BookingPressCustomerImportComputedData
        }
    }

    const BookingPressCustomerImportDialog = createApp(BookingPressCustomerImportDialogConfig);
    BookingPressCustomerImportDialog.use(BookingPressUI);
    window.BookingPressCustomerImportDialog = BookingPressCustomerImportDialog.mount('#customer_import_modal');

}

const initExportCustomerDialog = () => {

    //const moduleData = getModuleData('bookingpress-calendar-loader');
    const customerModuleData = getModuleData('bookingpress-customer-loader');

    let BookingPressCustomerExportComputedData = wp.hooks.applyFilters( 'bookingpress_customer_export_computed_data', {} );

    let BookingPressCustomerExportMethodData = wp.hooks.applyFilters( 'bookingpress_customer_export_external_methods', {

        close_export_customer_lite_model(){
            this.ExportCustomerLite = false;
            this.export_checked_field_lite = JSON.parse(JSON.stringify(this.export_checked_field_lite_org));
        },
        bookingpress_enable_modal(){
            document.body.style.overflow = 'hidden';
        },
        bookingpress_disable_modal(){
            if(document.body.classList.contains("el-popup-parent--hidden")){
                document.body.classList.remove("el-popup-parent--hidden");
                document.body.style.paddingRight = "0px";
            }
            document.body.style.overflow = 'auto';
        },
        bookingpress_export_customer_lite(){
            const vm = this;	
            vm.is_export_button_disabled_lite= true;
            vm.is_export_button_loader_lite= '1';
            var bookingpress_search_data = { search_name: vm.customerSearch,search_date_range: vm.customer_search_range }

            const postData = {
                export_field: vm.export_checked_field_lite,
                search_data: bookingpress_search_data,
            };

            fetch(rest_url + '/customer/export_customer_fetch', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body: JSON.stringify(postData),
            })
            .then(res => res.json())
            .then((response) => {
                vm.is_export_button_disabled_lite= false;
                vm.is_export_button_loader_lite= '0';					
                vm.close_export_customer_lite_model();	

                if(response.data != 'undefined') {
                    var export_data;
                    var csv = ''; 
                    if(response.data != '') {
                        export_data = response.data;						
                        export_data.forEach(function(row){					    				
                            csv += row.join(',');
                                csv += "\n";
                        });	 
                    }		
                    const anchor = document.createElement('a');
                    anchor.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);	
                    anchor.target = '_blank';
                    anchor.download = 'Bookingpress-export-customer.csv';					    
                    anchor.click();
                }	
            })
            .catch((error) => {
                window.CustomerLoader.toggleBusy();
                console.error('loadCustomers error:', error);
            });
        },        
        close_export_customer_lite_model(){
            const vm = this;
            vm.ExportCustomerLite = false;
            vm.export_checked_field_lite = JSON.parse(JSON.stringify(vm.export_checked_field_lite_org));
        },    
    } );

    const BookingPressCustomerExportDialogConfig = {

        data() {
            return {    
                ExportCustomerLite: false,
                customer_export_field_list_lite: customerModuleData.customer_export_field_list_lite,
                export_checked_field_lite: customerModuleData.export_checked_field_lite,        
                export_checked_field_lite_org: customerModuleData.export_checked_field_lite_org, 
                is_mask_display: true,                                                           
                is_export_button_loader_lite: customerModuleData.is_export_button_loader_lite,  
                is_export_button_disabled_lite: customerModuleData.is_export_button_disabled_lite, 
            }
        },
        methods: {
            ...BookingPressCustomerExportMethodData
        },
        computed:{
            ...BookingPressCustomerExportComputedData
        }
    }


    const BookingPressCustomerExportDialog = createApp(BookingPressCustomerExportDialogConfig);
    BookingPressCustomerExportDialog.use(BookingPressUI);
    window.BookingPressCustomerExportDialog = BookingPressCustomerExportDialog.mount('#customer_export_modal');
}


const initCustomerWrapper = () => {
    
    let customerPageMountedMethods = wp.hooks.applyFilters('bookingpress_customer_page_mounted_methods', {

        checkUploadedImportFile(file){
            const vm2 = this;              
            if(file.type != 'text/csv'){
                vm2.$notify({
                    title: 'Error',
                    message: 'Please upload csv file only',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
                return false
            }else{
                var bpa_image_size = parseInt(file.size / 50000000);
                if(bpa_image_size > 1){
                    vm2.$notify({
                        title: 'Error',
                        message: 'Please upload maximum 50 MB file only',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:BookingPressConfig.notification_timeout
                    });                    
                    return false
                }
            }
        },

        loadCustomers(rest_pagination = false) {
            const vm = this;
            vm.toggleBusy();

            let bookingpress_module_type         = sessionStorage.getItem('bookingpress_module_type') || '';
            let bookingpress_filter_start_date   = sessionStorage.getItem('bookingpress_dashboard_filter_start_date') || '';
            let bookingpress_filter_end_date     = sessionStorage.getItem('bookingpress_dashboard_filter_end_date') || '';
            let selected_date_range              = '';

            sessionStorage.removeItem('bookingpress_module_type');
            sessionStorage.removeItem('bookingpress_dashboard_filter_start_date');
            sessionStorage.removeItem('bookingpress_dashboard_filter_end_date');

            if (rest_pagination === true) {
                vm.currentPage = 1;
            }

            if ( bookingpress_module_type === 'customer' && bookingpress_filter_start_date !== '' && bookingpress_filter_end_date !== '') {
                selected_date_range = [bookingpress_filter_start_date, bookingpress_filter_end_date];
                vm.customer_search_range = selected_date_range;
            }

            const postData = {
                perpage:      parseInt(vm.perPage),
                currentpage:  vm.currentPage,
                search_data: {
                    search_name:        vm.customerSearch,
                    selected_date_range: selected_date_range,
                },
                filter:           vm.currently_selected_filter,
                custom_filter_val: vm.custom_filter_val,
            };

            fetch(rest_url + '/customer/fetch', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body: JSON.stringify(postData),
            })
            .then(res => res.json())
            .then((response) => {
                vm.toggleBusy();

                if (response.variant === 'error') {
                    vm.$notify({
                        title:       response.title,
                        message:     response.msg,
                        type:        'error',
                        customClass: 'error_notification',
                        duration:    BookingPressConfig.notification_duration || 3000,
                    });
                } else {
                    vm.items      = response.items;
                    vm.totalItems = parseInt(response.total);
                }
            })
            .catch((error) => {
                vm.toggleBusy();
                console.error('loadCustomers error:', error);
            });
        },
        toggleBusy() {
            if(this.is_display_loader == '1'){
                this.is_display_loader = '0'
            }else{
                this.is_display_loader = '1'
            }
        },
        handleSelectionChange(val) {
            this.multipleSelection = [];
            const customer_items_obj = val
            Object.values(customer_items_obj).forEach(val => {
                this.multipleSelection.push({customer_id : val.customer_id})
                this.bulk_action = 'bulk_action';
            });
        },
        closeBulkAction(){
            this.$refs.multipleTable.clearSelection();
            this.bulk_action = 'bulk_action';
        },
        open_add_customer_modal(){                
            window.BookingPressCustomerDialog.openCustomerDialog();
        },
        resetFilter(){
            const vm2 = this
            vm2.customerSearch =''; 
            vm2.customer_search_range = '';                          
            vm2.loadCustomers()
        },
        closeCustomerModal() {
            window.BookingPressCustomerDialog.closeCustomerDialog();
        },
        close_export_customer_lite_model(){
            const vm = this;
            vm.ExportCustomerLite = false;
            vm.export_checked_field_lite = JSON.parse(JSON.stringify(vm.export_checked_field_lite_org));
        },   
        bookingpress_enable_modal(){
            document.body.style.overflow = 'hidden';
        },
        bookingpress_disable_modal(){
            if(document.body.classList.contains("el-popup-parent--hidden")){
                document.body.classList.remove("el-popup-parent--hidden");
                document.body.style.paddingRight = "0px";
            }
            document.body.style.overflow = 'auto';
        },  
        changePaginationSize(selectedPage) {
           /*  var total_recored_perpage = selectedPage;
            var current_page = this.changeCurrentPage(total_recored_perpage);                                        
            this.perPage = selectedPage;                    
            this.currentPage = current_page;
            console.log( this.currentPage );
            this.loadCustomers() */
             
            this.perPage = parseInt(selectedPage);
            this.currentPage = 1;
            this.loadCustomers();

        },
        changeCurrentPage(perPage) {
            console.log( perPage );
            var total_item = this.totalItems;
            var recored_perpage = perPage;
            var select_page =  this.currentPage;                
            var current_page = Math.ceil(total_item/recored_perpage);
            if(total_item <= recored_perpage ) {
                current_page = 1;
            } else if(select_page >= current_page ) {
                
            } else {
                current_page = select_page;
            }
            return current_page;
        },
        handleCurrentChange(val) {
            this.currentPage = val;
            this.loadCustomers()
        }, 
        handleSizeChange(val) {
            this.perPage = val
            this.loadCustomers()
        },
        bulk_actions() {
            const vm2 = this
            
            if(this.bulk_action == "bulk_action")
            {
                vm2.$notify({
                    title: 'Error',
                    message: 'Please select any action',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
            }
            else
            {
                if(this.multipleSelection.length > 0 && this.bulk_action == "delete")
                {
                    fetch(rest_url + '/customer/bulk-delete', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': BookingPressConfig.rest_nonce,
                        },
                        body: JSON.stringify({
                            delete_ids: this.multipleSelection,
                            bulk_action: 'delete'
                        })
                    })
                    .then(response => response.json())
                    .then(rest_response => {
                        if (rest_response.variant == 'success') {
                            this.$notify({
                                title: rest_response.title,
                                message: rest_response.msg,
                                type: rest_response.variant,
                                customClass: rest_response.variant + '_notification',
                                duration: BookingPressConfig.notification_timeout
                            });
                            this.loadCustomers();
                            this.multipleSelection = [];
                            this.totalItems = this.items.length;
                        } else {
                            this.$notify({
                                title: 'Error',
                                message: rest_response.message,
                                type: 'error',
                                customClass: 'error_notification',
                                duration: BookingPressConfig.notification_timeout
                            });
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        this.$notify({
                            title: 'Error',
                            message: 'Something went wrong..',
                            type: 'error',
                            customClass: 'error_notification',
                            duration: BookingPressConfig.notification_timeout
                        });
                    });

                } else {    
                    if(this.multipleSelection.length == 0) {                                
                        vm2.$notify({
                            title: 'Error',
                            message: 'Please select one or more records.',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:BookingPressConfig.notification_timeout
                        });
                    }else{
                        wp.hooks.doAction('bookingpress_customer_dynamic_bulk_action');                        
                    }                            
                }
            }
        },
        deleteCustomer(delete_id) {
             fetch(rest_url + '/customer/delete', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body: JSON.stringify({
                    delete_id: delete_id
                })
            })
            .then(response => response.json())
            .then(rest_response => {
                if (rest_response.variant == 'success') {
                    wp.hooks.doAction('bookingpress_customer_deleted', this, rest_response);
                    this.$notify({
                        title: rest_response.title,
                        message: rest_response.msg,
                        type: rest_response.variant,
                        customClass: rest_response.variant + '_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                    this.loadCustomers();
                } else {
                    this.$notify({
                        title: 'Error',
                        message: rest_response.message,
                        type: 'error',
                        customClass: 'error_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }
            })
            .catch(error => {
                console.log(error);
                this.$notify({
                    title: 'Error',
                    message: 'Something went wrong..',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
            });
        },
        bookingpress_export_customer_data_lite(currentElement){
            window.BookingPressCustomerExportDialog.ExportCustomerLite = true;
            if( typeof this.bpa_adjust_popup_position != 'undefined' ){
                this.bpa_adjust_popup_position( currentElement, 'div#customer_export_model.bp-dialog.bpa-dailog__small');
            } 
        },
        bpa_get_target_parent( elem, selector ){
            if (!Element.prototype.matches) {
                Element.prototype.matches = Element.prototype.matchesSelector ||
                    Element.prototype.mozMatchesSelector ||
                    Element.prototype.msMatchesSelector ||
                    Element.prototype.oMatchesSelector ||
                    Element.prototype.webkitMatchesSelector ||
                    function(s) {
                        var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                            i = matches.length;
                        while (--i >= 0 && matches.item(i) !== this) {}
                        return i > -1;
                    };
            }

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
        bpa_adjust_popup_position( currentElement, selector, sourceCls = '', position, view = '' ){

            let paths = currentElement.path;
            let buttonElm = null;

          
            if( typeof paths != 'undefined' ){
                for( let x in paths ){
                    let currentPath = paths[x];
                    let currentPathNodeName = currentPath.nodeName;
                    if( "BUTTON" == currentPathNodeName || ( 'undefined' != typeof sourceCls && '' != sourceCls && currentPath.classList.contains(sourceCls)) ){
                        buttonElm = currentPath;
                        break;
                    }
                }
            } else {

                if( "BUTTON" == currentElement.target.nodeName || ( 'undefined' != typeof sourceCls && '' != sourceCls && currentElement.target.classList.contains(sourceCls) ) ){
                    buttonElm = currentElement.target;
                } else {
                    let par = this.bpa_get_target_parent( currentElement.target, 'button' );
                    
                    if( par.length > 0 ){
                        buttonElm = par[0];
                    } else {
                        let par = this.bpa_get_target_parent( currentElement.target, '.' + sourceCls );
                        if( par.length > 0 ){
                            buttonElm = par[0];
                        }
                    }
                }
            }
            
            if( null !== buttonElm ){
                let pos_x = buttonElm.getBoundingClientRect().left;
                let pos_y = buttonElm.getBoundingClientRect().top;
                
                pos_x = Math.ceil( pos_x );
                pos_y = Math.ceil( pos_y );

                let btn_height = buttonElm.offsetHeight;
                let pos_top = pos_y + btn_height + 20;

                
                (function(pos_x, buttonElm, class_selector){
                    setTimeout(function(){
                        let dialog__wrapper = document.querySelector( class_selector );
                        if( null !== dialog__wrapper ){

                            let viewportHeight = window.innerHeight;
                            let dialogHeight = dialog__wrapper.offsetHeight;
                            let spaceBelow = viewportHeight - pos_y - btn_height;
                            let spaceAbove = pos_y;

                            if(view == "tablet") {
                                if (spaceBelow >= dialogHeight) {
                                    pos_top = pos_y + btn_height + 20; // Show below
                                } else if (spaceAbove >= dialogHeight) {
                                    pos_top = pos_y - dialogHeight - 20; // Show above
                                } else {
                                    pos_top = Math.max(minMargin, (viewportHeight - dialogHeight) / 2);
                                }
                                if (pos_top < 0) pos_top = 10;
                                if (pos_top + dialogHeight > viewportHeight) pos_top = viewportHeight - dialogHeight - 10;
                            }

                            dialog__wrapper.style.position = '';
                            dialog__wrapper.style.margin = '';
                            dialog__wrapper.style.top = '';
                            dialog__wrapper.style.left = '0';

                            dialog__wrapper.style.position = 'absolute';
                            dialog__wrapper.style.margin = '0';
                            dialog__wrapper.style.top = parseInt(pos_top) + 'px';

                            let pos_to_place = pos_x + ( buttonElm.offsetWidth * 0.5 );
                            let dialog_pos_right = dialog__wrapper.offsetWidth + dialog__wrapper.getBoundingClientRect().left;
                            if( BookingPressConfig.is_rtl ){
                                if( '' != position && 'right' == position ){
                                    dialog_pos_right = dialog_pos_right - 50;
                                    dialog__wrapper.style.left = pos_to_place - dialog_pos_right + 'px';
                                } else {
                                    dialog_pos_right = dialog_pos_right - 30;
                                    dialog__wrapper.style.left = (pos_to_place -  ( dialog__wrapper.getBoundingClientRect().left + 40 ) ) + 'px';
                                }
                            } else {
                                if( '' != position && 'right' == position ){
                                    dialog_pos_right = dialog_pos_right - 30;
                                    dialog__wrapper.style.left = (pos_to_place -  ( dialog__wrapper.getBoundingClientRect().left + 40 ) ) + 'px';
                                } else {
                                    let currentElementId = currentElement.target.id;
                                    let is_mobile = false;
                                    if( null != document.getElementById('bpa-mobile-menu') ){
                                        let is_mob_menu_visible = getComputedStyle( document.getElementById('bpa-mobile-menu') ).display;
                                        if( 'block' == is_mob_menu_visible ){
                                            is_mobile = true;
                                        }
                                    }
                                    if( is_mobile == true && (('BUTTON' == currentElement.target.nodeName && currentElementId == 'bpa-appointment-share-url-button') || ( 'SPAN' == currentElement.target.nodeName && currentElement.target.parentNode != null && currentElement.target.parentNode.id == 'bpa-appointment-share-url-button' ) || ('SPAN' == currentElement.target.nodeName && currentElementId == 'bpa-appointment-share-url-span-txt')) ){
                                        let buttonWidth = document.getElementById( 'bpa-appointment-share-url-button' ).offsetWidth;   
                                        let popupWidth = dialog__wrapper.offsetWidth;
                                        if( pos_x <= 44 ){
                                            dialog_pos_right = dialog_pos_right - 110;
                                        }   
                                    }
                                    dialog_pos_right = dialog_pos_right - 50;
                                    dialog__wrapper.style.left = pos_to_place - dialog_pos_right + 'px';
                                }
                            }
                            
                        }
                    },10)
                })( pos_x, buttonElm, selector );
            }
        },
        bookingpress_import_customer_data_open(){
            window.BookingPressCustomerImportDialog.import_customer_modal = true;
        },
        editCustomerDetails(edit_id){
            window.BookingPressCustomerDialog.customer.update_id = edit_id;
            this.open_add_customer_modal();
            fetch(rest_url + '/customer/edit', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BookingPressConfig.rest_nonce,
                },
                body: JSON.stringify({
                    edit_id: edit_id
                })
            })
            .then(response => response.json())
            .then(rest_response => {
                if (rest_response.variant == 'success') {
                    var edit_customer_details = rest_response.edit_data;

                    window.BookingPressCustomerDialog.customer.update_id = edit_customer_details.bookingpress_customer_id;
                    if (edit_customer_details.bookingpress_wpuser_id != '') {
                        window.BookingPressCustomerDialog.customer.wp_user = parseInt(edit_customer_details.bookingpress_wpuser_id);
                    } else {
                        window.BookingPressCustomerDialog.customer.wp_user = '';
                    }
                    //window.BookingPressCustomerDialog.wordpress_user_id   = this.customer.wp_user;
                    window.BookingPressCustomerDialog.customer.username                          = edit_customer_details.bookingpress_user_name;
                    window.BookingPressCustomerDialog.customer.firstname                         = edit_customer_details.bookingpress_user_firstname;
                    window.BookingPressCustomerDialog.customer.lastname                          = edit_customer_details.bookingpress_user_lastname;
                    window.BookingPressCustomerDialog.customer.email                             = edit_customer_details.bookingpress_user_email;
                    window.BookingPressCustomerDialog.customer.phone                             = edit_customer_details.bookingpress_user_phone;
                    window.BookingPressCustomerDialog.customer.note                              = edit_customer_details.note;
                    window.BookingPressCustomerDialog.customer.avatar_url                        = edit_customer_details.avatar_url;
                    window.BookingPressCustomerDialog.customer.avatar_name                       = edit_customer_details.avatar_name;
                    window.BookingPressCustomerDialog.customer.customer_phone_country            = edit_customer_details.bookingpress_user_country_phone;
                    window.BookingPressCustomerDialog.bookingpress_tel_input_props.defaultCountry = edit_customer_details.bookingpress_user_country_phone;
                    //window.BookingPressCustomerDialog.$refs.bpa_tel_input_field._data.activeCountryCode = edit_customer_details.bookingpress_user_country_phone;
                    window.BookingPressCustomerDialog.wpUsersList                                = edit_customer_details.wp_user_list;

                    

                    wp.hooks.doAction('bookingpress_customer_edit_details', window.BookingPressCustomerDialog, rest_response);

                } else {
                    this.$notify({
                        title: rest_response.title,
                        message: rest_response.msg,
                        type: rest_response.variant,
                        customClass: rest_response.variant + '_notification',
                        duration: BookingPressConfig.notification_timeout
                    });
                }
            })
            .catch(error => {
                console.log(error);
                this.$notify({
                    title: 'Error',
                    message: 'Something went wrong..',
                    type: 'error',
                    customClass: 'error_notification',
                    duration: BookingPressConfig.notification_timeout
                });
            });
        },
    });

    const customerPageConfig = {
        data() {
            let moduleData = getModuleData('bookingpress-customer-loader');

            let CustomerConfigData =  {
                items: [],        
                totalItems: 0,
                currentPage: 1,
                perPage: moduleData.perPage,
                is_display_loader: '0',
                current_screen_size: 'desktop',
                customerSearch: '',
                customer_search_range: '',
                currently_selected_filter: '',
                custom_filter_val: '',
                current_screen_size: 'desktop',       
                openCustomerModal: false,
                bulk_action: 'bulk_action',         
                loading: false,
                multipleSelection: [],
                customer: {
                    avatar_url: '',
                    avatar_name: '',
                    avatar_url: '',
                    wp_user :null,
                    username:'',
                    firstname :'',
                    lastname:'',
                    email :'',
                    phone   :'',
                    customer_phone_country:'',
                    customer_phone_dial_code:'',
                    note    :'',
                    update_id:0,
                    _wpnonce:'',
                    password:'',
                },
                customer_detail_save: false,
                wpUsersList: [],
                savebtnloading: false,
                columnSequenceModal: false,
                pagination_length_val: moduleData.pagination_length_val,
                pagination_length: moduleData.pagination_length,
                cusShowFileList: false,
                is_disabled : false,
                is_display_save_loader :'0',
                bookingpress_tel_input_props: moduleData.bookingpress_tel_input_props,
                bulk_options: moduleData.bulk_options,
                phone_countries_details: moduleData.phone_countries_details,
                vue_tel_mode: moduleData.vue_tel_mode,
                vue_tel_auto_format: moduleData.vue_tel_auto_format,
                rules: moduleData.rules,
                pagination_val : [
                    { text: '10', value: '10' },
                    { text: '20', value: '20' },
                    { text: '50', value: '50' },
                    { text: '100', value: '100' },
                    { text: '200', value: '200' },
                    { text: '300', value: '300' },
                    { text: '400', value: '400' },
                    { text: '500', value: '500' },
                ],                                

            }
            return CustomerConfigData;
        },
        mounted() {
            if(window.screen.width >= 1200){
                this.current_screen_size = "desktop";
            }else if(window.screen.width < 1200 && window.screen.width >= 768){
                this.current_screen_size = "tablet";
            }else if(window.screen.width < 768){
                this.current_screen_size = "mobile";
            } 
            this.perPage = parseInt(this.pagination_length_val);
            this.loadCustomers();
        },
        methods: {
            ...customerPageMountedMethods
        }
    };

    const BookingPressCustomer = createApp(customerPageConfig);
    BookingPressCustomer.use(BookingPressUI);
    window.CustomerLoader = BookingPressCustomer.mount('#customer-app-root');
}



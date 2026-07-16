<div v-cloak id="bookingpress-view-debug-dialog" class="bookingpress-view-dialog-container">
    <bp-ui-dialog close-on-press-escape="true" :close-on-press-escape="true" class="bpa-dialog bpa-dialog--debug-log" title="" v-model="open_display_log_modal" :append-to-body="true" style="margin-top:15vh;" :close-on-click-modal="true" :modal="true">
        <div class="bpa-dialog-heading">
            <bp-ui-row type="flex">
                <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                    <h1 class="bpa-page-heading"><?php esc_html_e('Debug Logs', 'bookingpress-appointment-booking'); ?> ({{open_view_model_gateway}})</h1>
                </bp-ui-col>
            </bp-ui-row>
        </div>    
        <div class="bpa-back-loader-container" v-if="is_display_loader_view == '1'">
            <div class="bpa-back-loader"></div>
        </div>    
        <div class="bpa-dialog-body">        
            <bp-ui-row type="flex" v-if="items.length == 0">
                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <div class="bpa-data-empty-view">
                        <div class="bpa-ev-left-vector">
                            <picture>
                                <source srcset="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp'); ?>" type="image/webp">
                                <img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png'); ?>">
                            </picture>
                        </div>
                        <div class="bpa-ev-right-content">                    
                            <h4><?php esc_html_e('No Record Found!', 'bookingpress-appointment-booking'); ?></h4>                        
                        </div>
                    </div>
                </bp-ui-col>
            </bp-ui-row>
            <bp-ui-row v-if="items.length > 0"> 
                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <bp-ui-container class="bpa-grid-list-container">                    
                        <bp-ui-table ref="multipleTable" :data="items">
                            <bp-ui-table-column width="100" align="center" prop="payment_debug_log_id" label="<?php esc_html_e('Log Id', 'bookingpress-appointment-booking'); ?>"></bp-ui-table-column>
                            <bp-ui-table-column width="300" prop="payment_debug_log_name" label="<?php esc_html_e('Log Name', 'bookingpress-appointment-booking'); ?>"></bp-ui-table-column>
                            <bp-ui-table-column prop="payment_debug_log_data" label="<?php esc_html_e('Log Data', 'bookingpress-appointment-booking'); ?>"></bp-ui-table-column>
                            <bp-ui-table-column width="200" align="center" prop="payment_debug_log_added_date" label="<?php esc_html_e('Log Added Date', 'bookingpress-appointment-booking'); ?>"></bp-ui-table-column>
                        </bp-ui-table>                                        
                    </bp-ui-container>
                </bp-ui-col>
            </bp-ui-row>
            <bp-ui-row class="bpa-pagination" type="flex" v-if="items.length > 0"> <!-- Pagination -->
                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
                    <div class="bpa-pagination-left">
                        <p><?php esc_html_e('Showing', 'bookingpress-appointment-booking'); ?>&nbsp;<strong><u>{{ items.length }}</u></strong> <?php esc_html_e('out of', 'bookingpress-appointment-booking'); ?>&nbsp;<strong>{{ totalItems }}</strong></p>                    
                    </div>
                </bp-ui-col>
                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
                    <bp-ui-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" v-model:current-page="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage" :key="perPage"></bp-ui-pagination>
                </bp-ui-col>
            </bp-ui-row>
        </div>
    </bp-ui-dialog>
</div>
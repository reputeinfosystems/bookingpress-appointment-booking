<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div id="bookingpress_shortcode_form">
    <div class="bookingpress_shortcode_popup_btn_wrapper">
        <span class="bookingpress_logo_btn" @click="bookingpress_open_form_shortcode_popup"></span>
        <span class="bookingpress_spacer"></span>
    </div>
    <el-dialog custom-class="bpa-dialog bpa-dialog-bookingpress_shortcode" title="" :visible.sync="
        open_bookingpress_shortcode_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" :append-to-body="append_modal_to_body">
        <div class="bpa-dialog-heading">
            <el-row type="flex">                
                <el-col :xs="12" :sm="12" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading--is-visible-help">
                    <h1 class="bpa-page-heading"><?php esc_html_e('BookingPress Shortcodes', 'bookingpress-appointment-booking'); ?></h1>            
                </el-col>
            </el-row>
        </div>
        <div class="bpa-dialog-body">
                <el-row :gutter="12">                    
                    <el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">                                                
                        <label class="bpa-form-label"><?php esc_html_e('Please select shortcode which you want to add in a page/post', 'bookingpress-appointment-booking'); ?></label>
                    </el-col>    
                    <el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="16">
                        <el-select v-model="selected_bookingpress_shortcode" class="bpa-form-control" placeholder="<?php esc_html_e('Select shortcode', 'bookingpress-appointment-booking'); ?>">
                            <el-option label="<?php esc_html_e('Booking Forms - WordPress Booking Plugin', 'bookingpress-appointment-booking'); ?>" value="bookingpress_form"></el-option>
                            <el-option label="<?php esc_html_e('Customer Panel - BookingPress Appointment Plugin', 'bookingpress-appointment-booking'); ?>" value="bookingpress_my_appointments"></el-option>
                        </el-select>                        
                </el-row>        
                <el-row :gutter="24">                
                    <el-col :xs="24" :sm="24" :md="24" :lg="04" :xl="04" class="bpa-gs__cb-item-right" >                                    
                    <el-button class="bpa-btn bpa-btn--primary" @click="add_bookingpress_shortcode"><?php esc_html_e('Insert', 'bookingpress-appointment-booking'); ?></el-button>
                    </el-col>    
                </el-row>
        </div>
    </el-dialog>
</div>

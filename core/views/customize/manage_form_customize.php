<?php

    if ( ! defined( 'ABSPATH' ) ) { exit; }

    global $BookingPress, $bookingpress_common_date_format,$bookingpress_global_options;    
    $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
    $bookingpress_default_time_format = $bookingpress_global_options_arr['wp_default_time_format'];
    $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
    $bookingpress_price          = $BookingPress->bookingpress_price_formatter_with_currency_symbol(1000);
    $bookingpress_service_price1 = $BookingPress->bookingpress_price_formatter_with_currency_symbol(350);
    $bookingpress_service_price2 = $BookingPress->bookingpress_price_formatter_with_currency_symbol(150);
?>
<link rel="stylesheet" :href="'https://fonts.googleapis.com/css?family='+selected_font_values.title_font_family"> <?php //phpcs:ignore ?>

<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
    <el-row type="flex" class="bpa-mlc-head-wrap">
        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
            <h1 class="bpa-page-heading"><?php esc_html_e('Customize', 'bookingpress-appointment-booking'); ?></h1>
        </el-col>
        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
            <div class="bpa-hw-right-btn-group">
                <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bpa_save_customize_settings('forms')" :disabled="is_disabled" >                    
                  <span class="bpa-btn__label"><?php esc_html_e('Save Changes', 'bookingpress-appointment-booking'); ?></span>
                  <div class="bpa-btn--loader__circles">                    
                      <div></div>
                      <div></div>
                      <div></div>
                  </div>
                </el-button>
            </div>
        </el-col>
    </el-row>    
    <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
        <div class="bpa-back-loader"></div>
    </div>
    <el-container class="bpa-customize-main-container" id="bpa-main-container">          
        <div class="bpa-customize-body-wrapper">                        
            <div class="bpa-cmc--tab-menu">                                
                <div class="bpa-cms-tm__body">
                    <el-radio-group v-model="bpa_activeTabName" @change ="bookingpress_change_tab(bpa_activeTabName)">
                        <el-radio-button label="booking_form"><?php esc_html_e('Booking Form', 'bookingpress-appointment-booking'); ?></el-radio-button>
                        <el-radio-button label="my_bookings"><?php esc_html_e('Customer Panel', 'bookingpress-appointment-booking'); ?></el-radio-button>
                    </el-radio-group>
                </div>
            </div>                           
            <div class="bpa-customize-step-top-panel">
                <div class="bpa-tp__body">
                    <div class="bpa-tp__body-item">                           
                        <label class="bpa-form-label"><?php esc_html_e('Font:', 'bookingpress-appointment-booking'); ?></label>                  
                        <el-select @change="bookingpress_change_border_color" v-model="selected_font_values.title_font_family" class="bpa-form-control" popper-class="bpa-el-select--is-with-navbar" filterable>
                            <el-option-group v-for="item_data in fonts_list" :key="item_data.label" :label="item_data.label">
                                <el-option v-for="item in item_data.options" :key="item" :label="item" :value="item"></el-option>
                            </el-option-group>
                        </el-select>                            
                    </div>
                    <div class="bpa-tp__body-item __bpa-is-style-type">
                        <div class="bpa-bi__title">
                            <label class="bpa-form-label"><?php esc_html_e('Main color:', 'bookingpress-appointment-booking'); ?></label>
                        </div>
                        <div class="bpa-bi__body">
                            <el-tooltip effect="dark" content="<?php esc_html_e('Primary color', 'bookingpress-appointment-booking'); ?>" placement="top" open-delay="300">
                                <el-color-picker class="bpa-customize-tp__color-picker" v-model="selected_colorpicker_values.primary_color" @change="bpa_select_primary_color" ></el-color-picker>
                            </el-tooltip>
                            <el-tooltip effect="dark" content="<?php esc_html_e('Form background color', 'bookingpress-appointment-booking'); ?>" placement="top" open-delay="300">
                                <el-color-picker class="bpa-customize-tp__color-picker" v-model="selected_colorpicker_values.background_color" @change="bookingpress_change_border_color"></el-color-picker>
                            </el-tooltip>
                            <el-tooltip effect="dark" content="<?php esc_html_e('Panel background color', 'bookingpress-appointment-booking'); ?>" placement="top" open-delay="300">
                                <el-color-picker class="bpa-customize-tp__color-picker"v-model="selected_colorpicker_values.footer_background_color" @change="bookingpress_change_border_color"></el-color-picker>
                            </el-tooltip>
                            <el-tooltip effect="dark" content="<?php esc_html_e('Border color', 'bookingpress-appointment-booking'); ?>" placement="top" open-delay="300" >
                                <el-color-picker class="bpa-customize-tp__color-picker"v-model="selected_colorpicker_values.border_color" @change="bookingpress_change_border_color"></el-color-picker>
                            </el-tooltip>
                        </div>
                    </div>                                                                                                          
                    <div class="bpa-tp__body-item __bpa-is-style-type">
                        <div class="bpa-bi__title">
                            <label class="bpa-form-label"><?php esc_html_e('Form color:', 'bookingpress-appointment-booking'); ?></label>
                        </div>
                        <div class="bpa-bi__body">
                            <el-tooltip effect="dark" content="<?php esc_html_e('Title color', 'bookingpress-appointment-booking'); ?>" placement="top" open-delay="300">
                                <el-color-picker class="bpa-customize-tp__color-picker" v-model="selected_colorpicker_values.label_title_color" @change="bookingpress_change_border_color"></el-color-picker>
                            </el-tooltip>
                            <el-tooltip effect="dark" content="<?php esc_html_e('Sub title color', 'bookingpress-appointment-booking'); ?>" placement="top" open-delay="300">
                                <el-color-picker class="bpa-customize-tp__color-picker" v-model="selected_colorpicker_values.sub_title_color" @change="bookingpress_change_border_color"></el-color-picker>
                            </el-tooltip>  
                            <el-tooltip effect="dark" content="<?php esc_html_e('Content color', 'bookingpress-appointment-booking'); ?>" placement="top" open-delay="300">
                                <el-color-picker class="bpa-customize-tp__color-picker" v-model="selected_colorpicker_values.content_color" @change="bookingpress_change_border_color"></el-color-picker>
                            </el-tooltip>    
                        </div>
                    </div>  
                    <div class="bpa-tp__body-item">    
                        <label class="bpa-form-label"><?php esc_html_e('Price & Button text:', 'bookingpress-appointment-booking'); ?></label>
                        <el-color-picker class="bpa-customize-tp__color-picker" v-model="selected_colorpicker_values.price_button_text_color"></el-color-picker>
                    </div>  
                    <div class="bpa-tp__body-item">
                        <div class="bpa-bi__title">
                            <label class="bpa-form-label" ><?php esc_html_e('Actions:', 'bookingpress-appointment-booking'); ?></label>
                        </div>
                        <div class="bpa-bi__body">
                            <el-button class="bpa-btn bpa-btn__medium bpa-btn__filled-light" @click="open_custom_css_modal()">.css { }</el-button>
                            <el-popconfirm 
                                confirm-button-text='<?php esc_html_e('Yes', 'bookingpress-appointment-booking'); ?>' 
                                cancel-button-text='<?php esc_html_e('No', 'bookingpress-appointment-booking'); ?>' 
                                icon="false"
                                @confirm="bpa_reset_bookingform()" 
                                title="<?php esc_html_e('Are you sure you want to reset the settings?', 'bookingpress-appointment-booking'); ?>" @confirm="delete_breakhour(break_data.start_time, break_data.end_time, work_hours_day.day_name)" 
                                confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                cancel-button-type="bpa-btn bpa-btn__small">
                                    <el-button slot="reference" class="bpa-btn bpa-btn__filled-light bpa-tp__reset-btn">
                                        <span class="material-icons-round">refresh</span>
                                    </el-button>
                            </el-popconfirm> 
                        </div>
                    </div>                      
                </div>                                      
            </div>                           
            <el-tabs class="bpa-tabs bpa-tabs--customize-module" v-model="activeTabName">                    
                <div class="bpa-back-loader-container" v-if="bookingpress_tab_change_loader == '1'">
                    <div class="bpa-back-loader"></div>
                </div>
                <el-tab-pane name="booking_form" v-if="bookingpress_tab_change_loader == '0'">                                           
                    <div class="bpa-customize-step-content-container __bpa-is-sidebar">
                        <el-row type="flex">
                            <el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
                                <div class="bpa-customize-step-side-panel">
                                    <div class="bpa-cs-sp--heading">
                                        <h4><?php esc_html_e('Form Settings', 'bookingpress-appointment-booking'); ?></h4>                                        
                                    </div>
                                    <div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">
                                        <div class="bpa-sm--item">
                                            <h5 class="bpa-sm-sub-heading--item"><?php esc_html_e( 'Common Form Settings', 'bookingpress-appointment-booking'); ?></h5>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Booking wizard tabs position', 'bookingpress-appointment-booking'); ?></label>
                                            <el-select v-model="booking_form_settings.booking_form_tabs_position" class="bpa-form-control"
                                                popper-class="bpa-el-select--is-with-navbar">
                                                <el-option label="<?php esc_html_e('Left', 'bookingpress-appointment-booking'); ?>" value="left"><?php esc_html_e('Left', 'bookingpress-appointment-booking'); ?></el-option>
                                                <el-option label="<?php esc_html_e('Top', 'bookingpress-appointment-booking'); ?>" value="top"><?php esc_html_e('Top', 'bookingpress-appointment-booking'); ?></el-option>
                                            </el-select>                                            
                                        </div> 
                                    </div>
                                    <div class="bpa-cs-sp-sub-module--separator"></div>
                                    <div class="bpa-cs-sp-sub-module bpa-sm--swtich">
                                        <div class="bpa-sm--item">
                                            <h5 class="bpa-sm-sub-heading--item"><?php esc_html_e( 'Service Step Settings', 'bookingpress-appointment-booking'); ?></h5>
                                        </div>
                                        <div class="bpa-sm--item --bpa-is-flexbox">
                                            <label class="bpa-form-label"><?php esc_html_e('Hide service selection step', 'bookingpress-appointment-booking'); ?></label>
                                            <el-switch v-model="booking_form_settings.hide_category_service_selection"  @change="bookingpress_lite_after_change_position" class="bpa-swtich-control"></el-switch>
                                        </div>
                                        <div class="bpa-sm--item --bpa-is-flexbox">
                                            <label class="bpa-form-label"><?php echo stripslashes_deep( esc_html__('Select "All" as default category', 'bookingpress-appointment-booking') ); //phpcs:ignore ?></label>
                                            <el-switch v-model="booking_form_settings.default_select_all_category" class="bpa-swtich-control"></el-switch>
                                        </div>
                                        <div class="bpa-sm--item --bpa-is-flexbox">
                                            <label class="bpa-form-label"><?php esc_html_e('Hide service description', 'bookingpress-appointment-booking'); ?></label>
                                            <el-switch v-model="booking_form_settings.display_service_description" class="bpa-swtich-control"></el-switch>
                                        </div>
                                        <div class="bpa-sm--item --bpa-is-flexbox">
                                            <label class="bpa-form-label"><?php esc_html_e('Hide booked time slots', 'bookingpress-appointment-booking'); ?></label>
                                            <el-switch v-model="booking_form_settings.hide_already_booked_slot" class="bpa-swtich-control"></el-switch>
                                        </div>
                                    </div>
                                    <div class="bpa-cs-sp-sub-module--separator"></div>
                                    <div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">
                                                                         
                                        <div class="bpa-sm--item">                
                                            <h5 class="bpa-sm-sub-heading--item"><?php esc_html_e('Booking Redirection Options', 'bookingpress-appointment-booking'); ?></h5>
                                        </div>    
                                        <div class="bpa-sm--item">                                                            
                                            <label class="bpa-form-label"><?php esc_html_e('Upon success', 'bookingpress-appointment-booking'); ?></label>                                            
                                            <el-select v-model="booking_form_settings.after_booking_redirection" class="bpa-form-control" filterable
                                                 popper-class="bpa-el-select--is-with-navbar bpa-el-select--is-customize-left-panel">
                                                <el-option v-for="item in bookingpress_all_global_pages" :key="item.post_title" :label="item.post_title"  :value="''+item.ID"></el-option>                
                                            </el-select>
                                        </div>    
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Upon failure', 'bookingpress-appointment-booking'); ?></label>                                                                    
                                            <el-select v-model="booking_form_settings.after_failed_payment_redirection" class="bpa-form-control" filterable popper-class="bpa-el-select--is-with-navbar bpa-el-select--is-customize-left-panel">
                                                <el-option v-for="item in bookingpress_all_global_pages" :key="item.post_title" :label="item.post_title" :value="''+item.ID"></el-option>                                                
                                            </el-select>                                            
                                        </div>                                  
                                    </div>      
                                </div>
                            </el-col>                                   
                            <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                                <div class="bpa-customize-booking-form-preview-container">
                                    <el-tabs class="bpa-tabs bpa-cbf--tabs" v-model="formActiveTab">                                        
                                        <el-tab-pane name="1"  v-if="booking_form_settings.hide_category_service_selection == false">
                                            <template #label>
                                                <a :class="formActiveTab == '1' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '1' ? { 'color': selected_colorpicker_values.primary_color,'font-family': selected_font_values.title_font_family} : {'color': selected_colorpicker_values.sub_title_color,'font-size': selected_font_values.content_font_size+'px','font-family': selected_font_values.title_font_family} ]">
                                                    <span class="material-icons-round" :style="[ formActiveTab == '1' ? { 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color  } : {'color': selected_colorpicker_values.content_color,'font-size': selected_font_values.color_font_size+'px','border-color': selected_colorpicker_values.border_color} ]">dns</span>
                                                    {{ tab_container_data.service_title }}
                                                </a>
                                            </template>
                                            <div class="bpa-cbf--preview-step" :style="{ 'background': selected_colorpicker_values.background_color,'border-color': selected_colorpicker_values.border_color }">
                                                <div class="bpa-cbf--preview-step__body-content">
                                                    <div class="bpa-cbf--preview--module-container __category-module">
                                                        <el-row>
                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24"> 
                                                                <div class="bpa-front-module-heading" v-text="category_container_data.category_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family}" ></div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row>                                                            
                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                <div class="bpa-front-cat-items-wrapper">
                                                                    <div class="bpa-front-cat-items">
																		<el-tag class="bpa-front-ci-pill" :class="(bookingpress_shortcode_form.selected_category == 'all') ? '__bpa-is-active' : ''" @click="bpa_select_category('all')" :style="[bookingpress_shortcode_form.selected_category == 'all' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
																			<label :style="[bookingpress_shortcode_form.selected_category == 'all' ? { 'color': selected_colorpicker_values.label_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'} : { 'color': selected_colorpicker_values.content_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}]">{{category_container_data.all_category_title}}</label>
																			<i class="material-icons-round" :style="[bookingpress_shortcode_form.selected_category == 'all' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]" v-if="bookingpress_shortcode_form.selected_category == 'all'">check_circle</i>
																		</el-tag>
                                                                        <el-tag class="bpa-front-ci-pill" :class="(bookingpress_shortcode_form.selected_category == 'low_consultancy') ? '__bpa-is-active' : ''" @click="bpa_select_category('low_consultancy')" :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                            <label :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy' ? { 'color': selected_colorpicker_values.label_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'} : { 'color': selected_colorpicker_values.content_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}]"><?php esc_html_e('Sample category 1', 'bookingpress-appointment-booking'); ?></label>
                                                                            <i class="material-icons-round" :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]" v-if="bookingpress_shortcode_form.selected_category == 'low_consultancy'">check_circle</i>
                                                                        </el-tag>
                                                                        <el-tag class="bpa-front-ci-pill" :class="(bookingpress_shortcode_form.selected_category == 'entertainment_2') ? '__bpa-is-active' : ''" @click="bpa_select_category('entertainment_2')" :style="[bookingpress_shortcode_form.selected_category == 'entertainment_2' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                            <label :style="[bookingpress_shortcode_form.selected_category == 'entertainment_2' ? { 'color': selected_colorpicker_values.label_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'} : { 'color': selected_colorpicker_values.content_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}]"><?php esc_html_e('Sample category 2', 'bookingpress-appointment-booking'); ?></label>
                                                                            <i class="material-icons-round" :style="[bookingpress_shortcode_form.selected_category == 'entertainment_2' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]" v-if="bookingpress_shortcode_form.selected_category == 'entertainment_2'">check_circle</i>
                                                                        </el-tag>
                                                                        <el-tag class="bpa-front-ci-pill" :class="(bookingpress_shortcode_form.selected_category == 'real_estate_2') ? '__bpa-is-active' : ''" @click="bpa_select_category('real_estate_2')" :style="[bookingpress_shortcode_form.selected_category == 'real_estate_2' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                            <label :style="[bookingpress_shortcode_form.selected_category == 'real_estate_2' ? { 'color': selected_colorpicker_values.label_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'} : { 'color': selected_colorpicker_values.content_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}]"><?php esc_html_e('Sample category 3', 'bookingpress-appointment-booking'); ?></label>
                                                                            <i class="material-icons-round" :style="[bookingpress_shortcode_form.selected_category == 'real_estate_2' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]" v-if="bookingpress_shortcode_form.selected_category == 'real_estate_2'">check_circle</i>
                                                                        </el-tag>                                                                       
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                    </div>
                                                    <div class="bpa-cbf--preview--module-container __service-module">
                                                        <el-row>
                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                <div class="bpa-front-module-heading" v-text="service_container_data.service_heading_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family}"></div>                                     
                                                            </el-col>
                                                        </el-row>
                                                        <el-row :gutter="32">
                                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                <div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_service == 'chronic_disease_management_1') ? ' __bpa-is-selected' : ''" @click="bpa_select_service('chronic_disease_management_1')">
                                                                    <div class="bpa-front-si-card" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_1' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                        <div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_service == 'chronic_disease_management_1'">
                                                                            <span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_1' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
                                                                        </div>
                                                                        <div class="bpa-front-si-card__left">
                                                                            <div class="bpa-front-si__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
                                                                                <svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/></svg>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bpa-front-si__card-body">
                                                                            <div class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.title_font_size+'px'}"><?php esc_html_e('Sample service 1', 'bookingpress-appointment-booking'); ?></div>
                                                                            <p class="--bpa-is-desc" v-if="booking_form_settings.display_service_description == false" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family}">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam varius viverra lectus</p>
                                                                            <div class="bpa-front-si-cb__specs">
                                                                                <div class="bpa-front-si-cb__specs-item">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family}">{{ booking_form_settings.service_duration_label }} </p>
                                                                                    <strong :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">30 {{booking_form_settings.book_appointment_min_text}}</strong>
                                                                                </div>
                                                                                <div class="bpa-front-si-cb__specs-item">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family}">{{ booking_form_settings.service_price_label }}</p>
                                                                                    <strong class="bpa-front-text-primary-color --is-service-price" :style="{ 'background-color': selected_colorpicker_values.primary_color, 'color': selected_colorpicker_values.price_button_text_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}"><?php echo esc_html($bookingpress_service_price1); ?></strong>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                <div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_service == 'chronic_disease_management_2') ? ' __bpa-is-selected' : ''" @click="bpa_select_service('chronic_disease_management_2')">
                                                                    <div class="bpa-front-si-card" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_2' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                        <div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_service == 'chronic_disease_management_2'">
                                                                            <span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_2' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
                                                                        </div>
                                                                        <div class="bpa-front-si-card__left">
                                                                            <div class="bpa-front-si__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
                                                                                <svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/></svg>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bpa-front-si__card-body">
                                                                            <div class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.title_font_size+'px' }"><?php esc_html_e('Sample service 2', 'bookingpress-appointment-booking'); ?></div>
                                                                            <p class="--bpa-is-desc" v-if="booking_form_settings.display_service_description == false" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }" >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam varius viverra lectus</p>
                                                                            <div class="bpa-front-si-cb__specs">
                                                                                <div class="bpa-front-si-cb__specs-item">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ booking_form_settings.service_duration_label }}</p>
                                                                                    <strong :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px' }">1 {{booking_form_settings.book_appointment_hours_text}}</strong>
                                                                                </div>
                                                                                <div class="bpa-front-si-cb__specs-item">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ booking_form_settings.service_price_label }}</p> 
                                                                                    <strong class="bpa-front-text-primary-color --is-service-price" :style="{ 'background-color': selected_colorpicker_values.primary_color, 'color': selected_colorpicker_values.price_button_text_color, 'font-family': selected_font_values.title_font_family }"><?php echo esc_html($bookingpress_service_price2); ?></strong>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>                                                            
                                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                <div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_service == 'chronic_disease_management_3') ? ' __bpa-is-selected' : ''" @click="bpa_select_service('chronic_disease_management_3')">
                                                                    <div class="bpa-front-si-card" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_3' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                        <div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_service == 'chronic_disease_management_3'">
                                                                            <span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_3' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
                                                                        </div>
                                                                        <div class="bpa-front-si-card__left">
                                                                            <div class="bpa-front-si__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
                                                                                <svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/></svg>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bpa-front-si__card-body">
                                                                            <div class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.title_font_size+'px' }"><?php esc_html_e('Sample service 3', 'bookingpress-appointment-booking'); ?></div>
                                                                            <p class="--bpa-is-desc" v-if="booking_form_settings.display_service_description == false" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }" >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam varius viverra lectus</p>
                                                                            <div class="bpa-front-si-cb__specs">
                                                                                <div class="bpa-front-si-cb__specs-item">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family}">{{ booking_form_settings.service_duration_label }}</p>
                                                                                    <strong :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px' }">1 {{booking_form_settings.book_appointment_hours_text}}</strong>
                                                                                </div>
                                                                                <div class="bpa-front-si-cb__specs-item">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ booking_form_settings.service_price_label }}</p>
                                                                                    <strong class="bpa-front-text-primary-color --is-service-price" :style="{ 'background-color': selected_colorpicker_values.primary_color, 'color': selected_colorpicker_values.price_button_text_color, 'font-family': selected_font_values.title_font_family }"><?php echo esc_html($bookingpress_service_price2); ?></strong>
                                                                                </div>
                                                                            </div>                                                                            
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                                <div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_service == 'chronic_disease_management_4') ? ' __bpa-is-selected' : ''" @click="bpa_select_service('chronic_disease_management_4')">
                                                                    <div class="bpa-front-si-card" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_4' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                        <div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_service == 'chronic_disease_management_4'">
                                                                            <span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_4' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
                                                                        </div>
                                                                        <div class="bpa-front-si-card__left">
                                                                            <div class="bpa-front-si__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
                                                                                <svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/></svg>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bpa-front-si__card-body">
                                                                            <div class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.title_font_size+'px'}"><?php esc_html_e('Sample service 4', 'bookingpress-appointment-booking'); ?></div>
                                                                            <p class="--bpa-is-desc" v-if="booking_form_settings.display_service_description == false" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family}" >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam varius viverra lectus</p>
                                                                            <div class="bpa-front-si-cb__specs">
                                                                                <div class="bpa-front-si-cb__specs-item">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family}">{{ booking_form_settings.service_duration_label }}</p>
                                                                                    <strong :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">30 {{booking_form_settings.book_appointment_min_text}}</strong>
                                                                                </div>
                                                                                <div class="bpa-front-si-cb__specs-item">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ booking_form_settings.service_price_label }}</p>
                                                                                    <strong class="bpa-front-text-primary-color --is-service-price" :style="{ 'background-color': selected_colorpicker_values.primary_color, 'color': selected_colorpicker_values.price_button_text_color, 'font-family': selected_font_values.title_font_family}"><?php echo esc_html($bookingpress_service_price2); ?></strong>
                                                                                </div>
                                                                            </div>                                                                           
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                    </div>
                                                </div>
                                                <div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.background_color,'border-color':selected_colorpicker_values.border_color}">
                                                    <el-button class="bpa-btn bpa-btn--primary bpa-btn--front-preview" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color,'font-size': selected_font_values.sub_title_font_size+'px','font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">
                                                        <span class="bpa--text-ellipsis">{{ booking_form_settings.next_button_text}} <strong>{{tab_container_data.datetime_title }}</strong></span>
                                                        <span class="material-icons-round">east</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                        </el-tab-pane>                                        
                                        <el-tab-pane name="2">
                                            <template #label>
                                                <a :class="formActiveTab == '2' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '2' ? { 'color': selected_colorpicker_values.primary_color,'font-family': selected_font_values.title_font_family} : {'color': selected_colorpicker_values.sub_title_color,'font-size': selected_font_values.content_font_size+'px','font-family': selected_font_values.title_font_family} ]">
                                                    <span class="material-icons-round" :style="[ formActiveTab == '2' ? { 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color } : {'color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color} ]">date_range</span>
                                                    {{ tab_container_data.datetime_title }}
                                                </a>                                                
                                            </template>
                                            <div class="bpa-cbf--preview-step __is-calendar-step" :style="{ 'background': selected_colorpicker_values.background_color,'border-color': selected_colorpicker_values.border_color }">
                                                <div class="bpa-cbf--preview-step__body-content">                                                    
                                                    <div class="bpa-cbf--preview--module-container __cal-and-time">
                                                        <el-row>
                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                <div class="bpa-front-module-heading datetime-front-title" v-text="tab_container_data.datetime_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"></div>
                                                                <div class="bpa-front-datetime-step-note bpa-front-module--note-desc" v-html="timeslot_container_data.date_time_step_note" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family}" ></div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row :gutter="24" type="flex">
                                                            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                                                <div class="bpa-front--dt__calendar">
                                                                    <table :style="{'border-color': selected_colorpicker_values.border_color}">
                                                                        <tr :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">
                                                                            <td colspan="1"><span class="material-icons-round">keyboard_arrow_left</span></td>
                                                                            <td colspan="5" :style="{'font-size': selected_font_values.sub_title_font_size+'px'}"><?php echo esc_html__('June','bookingpress-appointment-booking')?> 2022 </td>
                                                                            <td colspan="1"><span class="material-icons-round">keyboard_arrow_right</span></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}"><?php echo esc_html__('Mon','bookingpress-appointment-booking')?></td>
                                                                            <td :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}"><?php echo esc_html__('Tue','bookingpress-appointment-booking')?></td>
                                                                            <td :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}"><?php echo esc_html__('Wed','bookingpress-appointment-booking')?></td>
                                                                            <td :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}"><?php echo esc_html__('Thu','bookingpress-appointment-booking')?></td>
                                                                            <td :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}"><?php echo esc_html__('Fri','bookingpress-appointment-booking')?></td>
                                                                            <td :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}"><?php echo esc_html__('Sat','bookingpress-appointment-booking')?></td>
                                                                            <td :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}"><?php echo esc_html__('Sun','bookingpress-appointment-booking')?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="bpa-front-dtc__cell-disabled" :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">30</td>
                                                                            <td class="bpa-front-dtc__cell-disabled" :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">31</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">1</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">2</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">3</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">4</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">5</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">6</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">7</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">8</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">9</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">10</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">11</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">12</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">13</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">14</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">15</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">16</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">17</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">18</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">19</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">20</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">21</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">22</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">23</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'color': selected_colorpicker_values.content_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color}">24</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">25</td>
                                                                            <td :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">26</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">27</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">28</td>
                                                                            <td :style="{'background-color':selected_colorpicker_values.primary_color,'color': 'white','font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'border-color': selected_colorpicker_values.border_color,'color': selected_colorpicker_values.price_button_text_color}">29</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color}">30</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable bpa-front-dtc__cell-disabled" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">1</td>
                                                                            <td class="bpa-front-dtc__cell-disabled" :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">2</td>
                                                                            <td class="bpa-front-dtc__cell-disabled" :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">3</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="bpa-front-dtc__cell-avaliable bpa-front-dtc__cell-disabled" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family ,'border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">4</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable bpa-front-dtc__cell-disabled" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family ,'border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">5</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable bpa-front-dtc__cell-disabled" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family ,'border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">6</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable bpa-front-dtc__cell-disabled" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family ,'border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">7</td>
                                                                            <td class="bpa-front-dtc__cell-avaliable bpa-front-dtc__cell-disabled" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color }">8</td>
                                                                            <td class="bpa-front-dtc__cell-disabled" :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color }">9</td>
                                                                            <td class="bpa-front-dtc__cell-disabled" :style="{'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px','border-color': selected_colorpicker_values.border_color,'background-color':selected_colorpicker_values.border_alpha_color,'color': selected_colorpicker_values.content_color}">10</td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </el-col>         
                                                            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                                                                <div class="bpa-front--dt__time-slots" :style="{'border-color': selected_colorpicker_values.border_color}">
                                                                    <el-row>
                                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                            <div class="bpa-front-module-heading" v-text="timeslot_container_data.timeslot_text" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family}"></div>
                                                                        </el-col>
                                                                    </el-row>
                                                                    <div class="bpa-front--dt__ts-body">
                                                                        <div class="bpa-front--dt__ts-body--row">
                                                                           
                                                                            <div class="bpa-front--dt-ts__sub-heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">{{ timeslot_container_data.morning_text }}</div>
                                                                            <div class="bpa-front--dt__ts-body--items">
                                                                                <div class="bpa-front--dt__ts-body--item __bpa-is-disabled" :style="{
                                                                                    'background-color':selected_colorpicker_values.border_alpha_color,'border-color':selected_colorpicker_values.border_color}">
                                                                                    <span :style=" { 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }" >
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('09:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('09:30'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '09:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('09:30')" :style="[bookingpress_shortcode_form.selected_time == '09:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('09:30'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('10:00'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '10:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('10:00')" :style="[bookingpress_shortcode_form.selected_time == '10:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('10:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('10:30'))); ?></span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '10:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('10:30')" :style="[bookingpress_shortcode_form.selected_time == '10:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                        <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('10:30'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('11:00'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '11:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('11:00')" :style="[bookingpress_shortcode_form.selected_time == '11:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('11:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('11:30'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bpa-front--dt__ts-body--row">
                                                                            <div class="bpa-front--dt-ts__sub-heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">{{ timeslot_container_data.afternoon_text }}</div>
                                                                            <div class="bpa-front--dt__ts-body--items">
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '12:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('12:00')" :style="[bookingpress_shortcode_form.selected_time == '12:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('12:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('12:30'))); ?></span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '12:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('12:30')" :style="[bookingpress_shortcode_form.selected_time == '12:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('12:30'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('13:00'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '13:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('13:00')" :style="[bookingpress_shortcode_form.selected_time == '13:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('13:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('13:30'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '13:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('13:30')" :style="[bookingpress_shortcode_form.selected_time == '13:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('13:30'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('14:00'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bpa-front--dt__ts-body--row">
                                                                            
                                                                            <div class="bpa-front--dt-ts__sub-heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">{{ timeslot_container_data.evening_text }}</div>    
                                                                            <div class="bpa-front--dt__ts-body--items">
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '17:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('17:00')" :style="[bookingpress_shortcode_form.selected_time == '17:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                        <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('17:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('17:30'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '17:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('17:30')" :style="[bookingpress_shortcode_form.selected_time == '17:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('17:30'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('18:00'))); ?></span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '18:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('18:00')" :style="[bookingpress_shortcode_form.selected_time == '18:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('18:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('18:30'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '18:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('18:30')" :style="[bookingpress_shortcode_form.selected_time == '18:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('18:30'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('19:00'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bpa-front--dt__ts-body--row">
                                                                            <div class="bpa-front--dt-ts__sub-heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">{{ timeslot_container_data.night_text }}</div>
                                                                            <div class="bpa-front--dt__ts-body--items">
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '20:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('20:00')" :style="[bookingpress_shortcode_form.selected_time == '20:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('20:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('20:30'))); ?></span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '20:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('20:30')" :style="[bookingpress_shortcode_form.selected_time == '20:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('20:30'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('21:00'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '21:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('21:00')" :style="[bookingpress_shortcode_form.selected_time == '21:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('21:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('21:30'))); ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '21:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('21:30')" :style="[bookingpress_shortcode_form.selected_time == '21:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : {'border-color': selected_colorpicker_values.border_color}]">
                                                                                    <span :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    <?php echo esc_html(date_i18n($bookingpress_default_time_format,strtotime('21:30'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('22:00'))); ?>
                                                                                </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                    </div>
                                                </div>
                                                <div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.background_color,'border-color':selected_colorpicker_values.border_color }">
                                                    <el-button class="bpa-btn bpa-btn--borderless" :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}" v-if="booking_form_settings.hide_category_service_selection == false">
                                                        <span class="material-icons-round">west</span>
                                                        <span class="bpa--text-ellipsis">{{ booking_form_settings.goback_button_text }}</span>
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--primary bpa-btn--front-preview" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color,'font-size': selected_font_values.sub_title_font_size+'px','font-family': selected_font_values.title_font_family ,'font-size': selected_font_values.sub_title_font_size+'px'}">
                                                        <span class="bpa--text-ellipsis">{{ booking_form_settings.next_button_text}} <strong>{{ tab_container_data.basic_details_title }}</strong></span>
                                                        <span class="material-icons-round">east</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                        </el-tab-pane>
                                        <el-tab-pane name="3">
                                            <template #label>
                                                <a :class="formActiveTab == '3' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '3' ? { 'color': selected_colorpicker_values.primary_color,'font-family': selected_font_values.title_font_family} : {'color': selected_colorpicker_values.sub_title_color,'font-size': selected_font_values.content_font_size+'px','font-family': selected_font_values.title_font_family} ]">
                                                    <span class="material-icons-round" :style="[ formActiveTab == '3' ? { 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color } : {'color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color} ]">article</span>
                                                    {{ tab_container_data.basic_details_title }}
                                                </a>
                                            </template>
                                            <div class="bpa-cbf--preview-step __is-basic-details-step" :style="{ 'background': selected_colorpicker_values.background_color,'border-color': selected_colorpicker_values.border_color}">
                                                <div class="bpa-cbf--preview-step__body-content">                                                    
                                                    <div class="bpa-cbf--preview--module-container">
                                                        <el-row>
                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                <div class="bpa-front-module-heading" v-text="tab_container_data.basic_details_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"></div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row>
                                                            <el-form ref="appointment_step_form_data">
                                                                <el-col>
                                                                    <div class="bpa-front-module--bd-form">
                                                                    <el-row>
                                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                <el-form-item ref="customer_username">
                                                                                    <template #label>
                                                                                        <span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px' }"><?php esc_html_e('Username', 'bookingpress-appointment-booking'); ?></span>        
                                                                                    </template>
                                                                                    <div class="bpa-form-control bpa-custom-form-control">
                                                                                        <input type="text" placeholder="<?php esc_html_e('Please enter username', 'bookingpress-appointment-booking'); ?>" :style="{ 'color': selected_colorpicker_values.label_title_color+' !important', 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color }" ></input>
                                                                                    </div>
                                                                                </el-form-item>
                                                                            </el-col>
                                                                        </el-row>
                                                                        <el-row>
                                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                <el-form-item ref="customer_name">
                                                                                    <template #label>
                                                                                        <span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px' }"><?php esc_html_e('Name', 'bookingpress-appointment-booking'); ?></span>        
                                                                                    </template>
                                                                                    <div class="bpa-form-control bpa-custom-form-control">
                                                                                        <input type="text" placeholder="<?php esc_html_e('Please enter your name', 'bookingpress-appointment-booking'); ?>" :style="{ 'color': selected_colorpicker_values.label_title_color+' !important', 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color }" ></input>
                                                                                    </div>
                                                                                </el-form-item>
                                                                            </el-col>
                                                                        </el-row>
                                                                        <el-row>
                                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                <el-form-item prop="customer_phone" ref="customer_phone">
                                                                                    <template #label>
                                                                                        <span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px' }"><?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?></span>
                                                                                    </template>
                                                                                    <vue-tel-input v-model="bookingpress_shortcode_form.cusomter_phone" class="bpa-form-control --bpa-country-dropdown" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.content_font_size +'px', 'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color+' !important'}" @country-changed="bookingpress_customize_form_tab_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props">
                                                                                        <template v-slot:arrow-icon>
                                                                                            <span class="material-icons-round">keyboard_arrow_down</span>
                                                                                        </template>
                                                                                    </vue-tel-input>
                                                                                </el-form-item>
                                                                            </el-col>
                                                                        </el-row>
                                                                        <el-row>
                                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                <el-form-item>
                                                                                    <template #label>
                                                                                        <span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px' }"><?php esc_html_e('Email', 'bookingpress-appointment-booking'); ?> </span>
                                                                                    </template>
                                                                                    <div class="bpa-form-control bpa-custom-form-control">
                                                                                    <input type="email" placeholder="<?php esc_html_e('Please enter your email', 'bookingpress-appointment-booking'); ?>" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color }"></input>
                                                                                    </div>
                                                                                </el-form-item>
                                                                            </el-col>
                                                                        </el-row>
                                                                        <el-row>
                                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                <el-form-item>
                                                                                    <template #label>
                                                                                        <span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px' }"><?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?></span>
                                                                                    </template>
                                                                                    <div class="bpa-form-control bpa-custom-form-control">
                                                                                    <textarea class="el-textarea__inner" rows="3" placeholder="<?php esc_html_e('Please enter your note', 'bookingpress-appointment-booking'); ?>" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color }"></textarea>
                                                                                    </div>
                                                                                </el-form-item>
                                                                            </el-col>
                                                                        </el-row>
                                                                        <el-row>
                                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                <el-form-item>
                                                                                    <label class="bpa-form-label bpa-custom-checkbox--is-label"> 
                                                                                        <el-checkbox :style="{'border-color':selected_colorpicker_values.border_color}">
                                                                                            <span :style="{'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family, 'border-color': selected_colorpicker_values.border_color}"> <?php esc_html_e( 'I agree with', 'bookingpress-appointment-booking' ); ?> <a href="#" :style="{'color':selected_colorpicker_values.primary_color}"><?php esc_html_e( 'terms & conditions', 'bookingpress-appointment-booking' ); ?></a></span>
                                                                                        </el-checkbox> 
                                                                                    </label>
                                                                                </el-form-item>
                                                                            </el-col>
                                                                        </el-row>
                                                                    </div>
                                                                </el-col>
                                                            </el-form>
                                                        </el-row>
                                                    </div>
                                                </div>
                                                <div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.background_color,'border-color':selected_colorpicker_values.border_color }">
                                                    <el-button class="bpa-btn bpa-btn--borderless"  :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">
                                                        <span class="material-icons-round">west</span>
                                                        <span>{{ booking_form_settings.goback_button_text }}</span>
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--primary" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color,'font-size': selected_font_values.sub_title_font_size+'px','font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">
                                                        <span>{{ booking_form_settings.next_button_text}} <strong>{{ tab_container_data.summary_title }}</strong></span>
                                                        <span class="material-icons-round">east</span>
                                                    </el-button>
                                                </div>
                                            </div>
                                        </el-tab-pane>
                                        <el-tab-pane name="4">
                                            <template #label>
                                                <a :class="formActiveTab == '4' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '4' ? { 'color': selected_colorpicker_values.primary_color,'font-family': selected_font_values.title_font_family} : {'color': selected_colorpicker_values.sub_title_color,'font-size': selected_font_values.content_font_size+'px','font-family': selected_font_values.title_font_family} ]">
                                                    <span class="material-icons-round" :style="[ formActiveTab == '4' ? { 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color } : {'color': selected_colorpicker_values.content_color,'border-color': selected_colorpicker_values.border_color} ]">assignment_turned_in</span>
                                                    {{ tab_container_data.summary_title }}
                                                </a>                                                                               
                                            </template>
                                            <div class="bpa-cbf--preview-step __is-summary-step" :style="{ 'background': selected_colorpicker_values.background_color,'border-color': selected_colorpicker_values.border_color }">
                                                <div class="bpa-cbf--preview-step__body-content">                                                    
                                                    <div class="bpa-cbf--preview--module-container">
                                                        <el-row>
                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                <div class="bpa-front-module-container bpa-front-module--booking-summary">
                                                                    <div class="bpa-front-module--bs-head">
                                                                    <svg width="137" height="99" viewBox="0 0 137 99" fill="none" xmlns="http://www.w3.org/2000/svg" class="bpa-head__vector">
                                                                        <path d="M15.8625 62.0651H97.6116C98.7623 62.0661 99.8656 62.5237 100.679 63.3374C101.493 64.1511 101.951 65.2543 101.952 66.4051V79.6535C101.951 80.8042 101.493 81.9075 100.679 82.7212C99.8656 83.5349 98.7623 83.9924 97.6116 83.9935H15.8625C14.7117 83.9924 13.6085 83.5349 12.7948 82.7212C11.9811 81.9075 11.5235 80.8042 11.5225 79.6535V66.4051C11.5235 65.2543 11.9811 64.1511 12.7948 63.3374C13.6085 62.5237 14.7117 62.0661 15.8625 62.0651Z" fill="#E9EDF5"/>
                                                                        <path d="M15.7854 30.7217H97.5345C98.6852 30.7227 99.7885 31.1803 100.602 31.994C101.416 32.8077 101.873 33.911 101.875 35.0617V48.3101C101.873 49.4608 101.416 50.5641 100.602 51.3778C99.7885 52.1915 98.6852 52.6491 97.5345 52.6501H15.7854C14.6346 52.6491 13.5313 52.1915 12.7177 51.3778C11.904 50.5641 11.4464 49.4608 11.4454 48.3101V35.0617C11.4464 33.911 11.904 32.8077 12.7177 31.994C13.5313 31.1803 14.6346 30.7227 15.7854 30.7217ZM11.9022 48.3101C11.9037 49.3395 12.3133 50.3264 13.0412 51.0543C13.7691 51.7822 14.7559 52.1918 15.7854 52.1933H97.5345C98.5639 52.1918 99.5508 51.7822 100.279 51.0543C101.007 50.3264 101.416 49.3395 101.418 48.3101V35.0617C101.416 34.0322 101.007 33.0454 100.279 32.3175C99.5508 31.5896 98.5639 31.18 97.5345 31.1785H15.7854C14.7559 31.18 13.7691 31.5896 13.0412 32.3175C12.3133 33.0454 11.9037 34.0322 11.9022 35.0617V48.3101Z" fill="#535D71"/>
                                                                        <path d="M26.9395 47.8533H66.2023C66.687 47.8533 67.1517 47.6608 67.4944 47.3181C67.8371 46.9754 68.0297 46.5106 68.0297 46.026C68.0297 45.5413 67.8371 45.0765 67.4944 44.7338C67.1517 44.3911 66.687 44.1986 66.2023 44.1986H26.9395C26.4548 44.1986 25.99 44.3911 25.6473 44.7338C25.3046 45.0765 25.1121 45.5413 25.1121 46.026C25.1121 46.5106 25.3046 46.9754 25.6473 47.3181C25.99 47.6608 26.4548 47.8533 26.9395 47.8533Z" fill="#E9EDF5"/>
                                                                        <path d="M53.2079 40.0871H66.2023C66.687 40.0871 67.1517 39.8946 67.4944 39.5519C67.8371 39.2092 68.0297 38.7444 68.0297 38.2597C68.0297 37.7751 67.8371 37.3103 67.4944 36.9676C67.1517 36.6249 66.687 36.4324 66.2023 36.4324H53.2079C52.7232 36.4324 52.2584 36.6249 51.9157 36.9676C51.573 37.3103 51.3805 37.7751 51.3805 38.2597C51.3805 38.7444 51.573 39.2092 51.9157 39.5519C52.2584 39.8946 52.7232 40.0871 53.2079 40.0871Z" fill="#E9EDF5"/>
                                                                        <path class="" d="M80.8983 49.24C82.3923 49.24 83.8527 48.797 85.095 47.9669C86.3372 47.1369 87.3054 45.9572 87.8771 44.5769C88.4489 43.1966 88.5985 41.6778 88.307 40.2125C88.0155 38.7472 87.2961 37.4012 86.2397 36.3448C85.1833 35.2884 83.8373 34.5689 82.372 34.2775C80.9067 33.986 79.3879 34.1356 78.0076 34.7073C76.6274 35.2791 75.4476 36.2472 74.6176 37.4895C73.7876 38.7317 73.3445 40.1921 73.3445 41.6861C73.3468 43.6888 74.1434 45.6088 75.5595 47.025C76.9756 48.4411 78.8956 49.2377 80.8983 49.24Z" :fill="selected_colorpicker_values.primary_color" />
                                                                        <path d="M79.7164 44.3433C79.529 44.3436 79.3466 44.283 79.1966 44.1705L79.1874 44.1636L77.2295 42.6659C77.1386 42.5964 77.0623 42.5098 77.005 42.4108C76.9476 42.3118 76.9103 42.2025 76.8953 42.0892C76.8802 41.9758 76.8877 41.8606 76.9172 41.7501C76.9467 41.6396 76.9977 41.536 77.0673 41.4452C77.1369 41.3544 77.2237 41.2783 77.3227 41.2211C77.4218 41.1639 77.5311 41.1268 77.6445 41.1119C77.7579 41.097 77.8731 41.1046 77.9836 41.1343C78.094 41.164 78.1976 41.2152 78.2882 41.2849L79.5564 42.2574L82.553 38.3479C82.6225 38.2572 82.7092 38.1812 82.8081 38.124C82.9069 38.0669 83.0161 38.0298 83.1293 38.0148C83.2426 37.9999 83.3576 38.0073 83.468 38.0369C83.5783 38.0664 83.6817 38.1173 83.7724 38.1868L83.7726 38.187L83.754 38.2128L83.7731 38.187C83.956 38.3276 84.0756 38.5349 84.1058 38.7635C84.1359 38.9922 84.0742 39.2234 83.934 39.4066L80.4092 44.003C80.3277 44.1089 80.2229 44.1946 80.1028 44.2534C79.9828 44.3122 79.8509 44.3427 79.7172 44.3423L79.7164 44.3433Z" fill="white"/>
                                                                        <path d="M50.9109 66.3674H132.66C133.811 66.3685 134.914 66.8261 135.728 67.6397C136.541 68.4534 136.999 69.5567 137 70.7074V83.9558C136.999 85.1066 136.541 86.2099 135.728 87.0235C134.914 87.8372 133.811 88.2948 132.66 88.2958H50.9109C49.7601 88.2948 48.6568 87.8372 47.8432 87.0235C47.0295 86.2099 46.5719 85.1066 46.5709 83.9558V70.7074C46.5719 69.5567 47.0295 68.4534 47.8432 67.6397C48.6568 66.8261 49.7601 66.3685 50.9109 66.3674Z" fill="white"/>
                                                                        <path d="M50.9109 66.3674H132.66C133.811 66.3685 134.914 66.8261 135.728 67.6397C136.541 68.4534 136.999 69.5567 137 70.7074V83.9558C136.999 85.1066 136.541 86.2099 135.728 87.0235C134.914 87.8372 133.811 88.2948 132.66 88.2958H50.9109C49.7601 88.2948 48.6568 87.8372 47.8432 87.0235C47.0295 86.2099 46.5719 85.1066 46.5709 83.9558V70.7074C46.5719 69.5567 47.0295 68.4534 47.8432 67.6397C48.6568 66.8261 49.7601 66.3685 50.9109 66.3674ZM47.0277 83.9558C47.0292 84.9853 47.4388 85.9721 48.1667 86.7001C48.8946 87.428 49.8814 87.8375 50.9109 87.839H132.66C133.689 87.8375 134.676 87.428 135.404 86.7001C136.132 85.9721 136.542 84.9853 136.543 83.9558V70.7074C136.542 69.678 136.132 68.6911 135.404 67.9632C134.676 67.2353 133.689 66.8257 132.66 66.8243H50.9109C49.8814 66.8257 48.8946 67.2353 48.1667 67.9632C47.4388 68.6911 47.0292 69.678 47.0277 70.7074V83.9558Z" fill="#535D71"/>
                                                                        <path d="M62.0646 83.4989H101.327C101.812 83.4989 102.277 83.3063 102.62 82.9636C102.962 82.6209 103.155 82.1561 103.155 81.6715C103.155 81.1868 102.962 80.722 102.62 80.3793C102.277 80.0366 101.812 79.8441 101.327 79.8441H62.0646C61.58 79.8441 61.1152 80.0366 60.7725 80.3793C60.4298 80.722 60.2373 81.1868 60.2373 81.6715C60.2373 82.1561 60.4298 82.6209 60.7725 82.9636C61.1152 83.3063 61.58 83.4989 62.0646 83.4989Z" fill="#E9EDF5"/>
                                                                        <path d="M88.3324 75.7326H101.327C101.567 75.7326 101.804 75.6854 102.026 75.5935C102.248 75.5017 102.449 75.3671 102.619 75.1974C102.789 75.0277 102.923 74.8263 103.015 74.6046C103.107 74.3829 103.154 74.1452 103.154 73.9052C103.154 73.6653 103.107 73.4277 103.015 73.2059C102.923 72.9842 102.789 72.7828 102.619 72.6131C102.449 72.4434 102.248 72.3088 102.026 72.217C101.804 72.1252 101.567 72.0779 101.327 72.0779H88.3324C87.8478 72.0779 87.383 72.2704 87.0403 72.6131C86.6976 72.9558 86.5051 73.4206 86.5051 73.9052C86.5051 74.3899 86.6976 74.8547 87.0403 75.1974C87.383 75.5401 87.8478 75.7326 88.3324 75.7326Z" fill="#E9EDF5"/>
                                                                        <path d="M116.024 84.8853C117.518 84.8854 118.978 84.4423 120.22 83.6123C121.463 82.7823 122.431 81.6026 123.002 80.2223C123.574 78.842 123.724 77.3232 123.432 75.8579C123.141 74.3926 122.421 73.0466 121.365 71.9902C120.309 70.9338 118.963 70.2143 117.497 69.9229C116.032 69.6314 114.513 69.781 113.133 70.3527C111.753 70.9244 110.573 71.8926 109.743 73.1348C108.913 74.3771 108.47 75.8375 108.47 77.3315C108.472 79.3342 109.269 81.2542 110.685 82.6704C112.101 84.0865 114.021 84.8831 116.024 84.8853Z" :fill="selected_colorpicker_values.primary_color" />
                                                                        <path d="M114.842 79.9884C114.654 79.9887 114.472 79.9281 114.322 79.8157L114.313 79.8087L112.355 78.311C112.264 78.2416 112.188 78.1549 112.13 78.0559C112.073 77.957 112.036 77.8477 112.021 77.7343C112.006 77.6209 112.013 77.5057 112.043 77.3952C112.072 77.2847 112.123 77.1811 112.193 77.0903C112.262 76.9996 112.349 76.9234 112.448 76.8662C112.547 76.809 112.656 76.7719 112.77 76.757C112.883 76.7421 112.998 76.7498 113.109 76.7795C113.219 76.8091 113.323 76.8603 113.414 76.93L114.682 77.9025L117.678 73.993C117.748 73.9024 117.835 73.8263 117.933 73.7692C118.032 73.712 118.141 73.6749 118.255 73.66C118.368 73.645 118.483 73.6525 118.593 73.682C118.704 73.7115 118.807 73.7625 118.898 73.832L118.879 73.858L118.898 73.8322C119.081 73.9727 119.201 74.18 119.231 74.4087C119.261 74.6373 119.2 74.8686 119.059 75.0517L115.535 79.6481C115.453 79.754 115.348 79.8397 115.228 79.8985C115.108 79.9574 114.976 79.9878 114.843 79.9874L114.842 79.9884Z" fill="white"/>
                                                                        <path d="M32.7836 98.9999H0.260187C0.191181 98.9999 0.124997 98.9725 0.0762029 98.9237C0.0274083 98.8749 0 98.8087 0 98.7397C0 98.6707 0.0274083 98.6045 0.0762029 98.5557C0.124997 98.5069 0.191181 98.4795 0.260187 98.4795H32.7836C32.8526 98.4795 32.9188 98.5069 32.9676 98.5557C33.0164 98.6045 33.0438 98.6707 33.0438 98.7397C33.0438 98.8087 33.0164 98.8749 32.9676 98.9237C32.9188 98.9725 32.8526 98.9999 32.7836 98.9999Z" fill="#202C45"/>
                                                                        <path d="M28.0083 17.5336C27.7067 17.724 27.4505 17.9782 27.2577 18.2783C27.065 18.5784 26.9404 18.9171 26.8927 19.2706C26.845 19.624 26.8754 19.9837 26.9818 20.3241C27.0881 20.6646 27.2678 20.9776 27.5082 21.2411L25.1438 26.2902L27.8616 28.4754L31.1005 21.2982C31.5164 20.865 31.759 20.2943 31.7822 19.6943C31.8054 19.0942 31.6077 18.5064 31.2266 18.0424C30.8454 17.5784 30.3073 17.2702 29.7141 17.1765C29.121 17.0827 28.514 17.2097 28.0083 17.5336V17.5336Z" fill="#FFB8B8"/>
                                                                        <path d="M14.4212 19.5826C15.1694 19.1331 16.062 18.9889 16.9138 19.1798C17.7656 19.3708 18.5112 19.8822 18.996 20.6081L23.8341 27.8524L25.5138 24.1495C25.6186 23.9183 25.8044 23.7334 26.0361 23.6296C26.2679 23.5258 26.5295 23.5103 26.7719 23.586L29.307 24.3779C29.4394 24.4193 29.5622 24.4867 29.6682 24.5763C29.7742 24.6658 29.8613 24.7756 29.9242 24.8993C29.9871 25.023 30.0247 25.158 30.0347 25.2964C30.0447 25.4348 30.0269 25.5737 29.9824 25.7052L28.2471 30.8294C28.0275 31.4776 27.6608 32.0661 27.1755 32.5487C26.6903 33.0313 26.0998 33.3948 25.4505 33.6108C24.8011 33.8268 24.1105 33.8894 23.4329 33.7935C22.7552 33.6977 22.1091 33.4461 21.545 33.0586C21.2815 32.8774 21.0383 32.6683 20.8198 32.4348L13.69 24.8167C13.3438 24.4468 13.0847 24.0041 12.9317 23.521C12.7787 23.038 12.7356 22.5269 12.8057 22.0251C12.8758 21.5233 13.0573 21.0435 13.3368 20.6209C13.6163 20.1982 13.9868 19.8435 14.4212 19.5826Z" fill="#CFD6E6"/>
                                                                        <path d="M30.0722 12.0974C30.202 12.1007 30.3253 12.1553 30.4148 12.2495C30.5044 12.3436 30.5529 12.4694 30.5496 12.5992L30.5079 14.2807L30.5922 14.2884L30.5619 15.4305L30.4796 15.4202L30.3299 21.4442C30.3248 21.6503 30.2449 21.8476 30.105 21.9992C29.9651 22.1507 29.7749 22.2462 29.5697 22.2678L25.7393 22.6711C25.6461 22.6809 25.5518 22.6702 25.4631 22.6397C25.3744 22.6092 25.2935 22.5597 25.2259 22.4947C25.1584 22.4296 25.1059 22.3506 25.0721 22.2631C25.0384 22.1756 25.0242 22.0818 25.0305 21.9882L25.6115 13.4105C25.6237 13.2303 25.6968 13.0597 25.8189 12.9266C25.9409 12.7935 26.1046 12.7059 26.283 12.6782L29.9808 12.1037C30.011 12.0988 30.0416 12.0967 30.0722 12.0974Z" fill="#202C45"/>
                                                                        <path d="M27.1076 12.9902L29.0515 12.6882C29.0865 12.6827 29.1187 12.6659 29.1432 12.6403C29.1677 12.6147 29.183 12.5818 29.1869 12.5466C29.1912 12.5067 29.2086 12.4694 29.2363 12.4404C29.264 12.4114 29.3005 12.3923 29.3401 12.3862L29.7496 12.3226C29.8055 12.3139 29.8626 12.3176 29.9169 12.3334C29.9712 12.3492 30.0214 12.3767 30.0638 12.414C30.1063 12.4513 30.1401 12.4975 30.1628 12.5493C30.1856 12.601 30.1966 12.6572 30.1953 12.7137L29.9848 21.4474C29.9822 21.5558 29.9397 21.6594 29.8656 21.7385C29.7915 21.8176 29.6908 21.8667 29.5828 21.8762L25.7552 22.2166C25.7064 22.2209 25.6572 22.2146 25.611 22.1982C25.5648 22.1818 25.5227 22.1556 25.4875 22.1215C25.4524 22.0873 25.425 22.0459 25.4073 22.0002C25.3896 21.9545 25.382 21.9055 25.385 21.8566L25.9014 13.3581C25.9091 13.2329 25.9593 13.1141 26.0437 13.0213C26.1282 12.9286 26.2418 12.8675 26.3658 12.8483L26.7975 12.7812C26.8133 12.8487 26.8541 12.9077 26.9116 12.9464C26.9691 12.9852 27.0391 13.0008 27.1076 12.9902Z" fill="white"/>
                                                                        <path d="M18.0885 95.8415L21.063 95.8413L22.4776 84.368L18.0875 84.3686L18.0885 95.8415Z" fill="#FFB8B8"/>
                                                                        <path d="M26.8003 98.603L17.4519 98.6039L17.4515 94.992L23.1881 94.9914C23.6624 94.9914 24.1321 95.0848 24.5703 95.2663C25.0086 95.4477 25.4068 95.7137 25.7422 96.0491C26.0776 96.3845 26.3437 96.7826 26.5253 97.2208C26.7068 97.659 26.8003 98.1287 26.8003 98.603L26.8003 98.603Z" fill="#202C45"/>
                                                                        <path d="M8.94684 95.8415L11.9213 95.8413L13.3359 84.368L8.9458 84.3686L8.94684 95.8415Z" fill="#FFB8B8"/>
                                                                        <path d="M17.6581 98.603L8.30959 98.6039L8.30927 94.992L14.0458 94.9914C15.0038 94.9914 15.9225 95.3718 16.5999 96.0491C17.2773 96.7264 17.658 97.6451 17.6581 98.603L17.6581 98.603Z" fill="#202C45"/>
                                                                        <path d="M17.6582 15.6833C21.1875 15.6833 24.0486 12.8222 24.0486 9.29281C24.0486 5.76345 21.1875 2.90234 17.6582 2.90234C14.1288 2.90234 11.2677 5.76345 11.2677 9.29281C11.2677 12.8222 14.1288 15.6833 17.6582 15.6833Z" fill="#FFB8B8"/>
                                                                        <path d="M20.9469 26.3904C20.8947 24.7474 20.4322 23.1436 19.6014 21.7251C18.7707 20.3067 17.5982 19.1187 16.1908 18.2694C14.5398 17.2965 12.7327 16.9171 11.295 18.3548C10.4317 19.2397 9.74034 20.2774 9.25615 21.4149C7.92622 24.5147 7.83921 28.0072 9.01314 31.1694L12.435 40.623L20.3549 41.481C20.5036 41.4971 20.6541 41.481 20.7961 41.4337C20.938 41.3863 21.0681 41.309 21.1775 41.2068C21.2868 41.1047 21.3728 40.9802 21.4297 40.8417C21.4865 40.7033 21.5129 40.5543 21.5069 40.4048L20.9469 26.3904Z" fill="#535D71"/>
                                                                        <path d="M12.9372 39.4589C12.9372 39.4589 7.02024 41.6678 8.56723 51.0212C9.95541 59.4144 8.85224 87.273 8.61112 92.9044C8.59964 93.1728 8.6925 93.4352 8.87023 93.6367C9.04796 93.8382 9.29678 93.963 9.56452 93.9851L13.0185 94.2729C13.289 94.2955 13.5577 94.2114 13.7671 94.0387C13.9765 93.866 14.1102 93.6183 14.1396 93.3484L15.9234 76.9635C15.9304 76.8992 15.9611 76.8398 16.0095 76.7969C16.0579 76.754 16.1206 76.7307 16.1852 76.7314C16.2499 76.7322 16.312 76.7571 16.3593 76.8011C16.4067 76.8452 16.4359 76.9053 16.4413 76.9698L17.7706 92.7359C17.7929 93.0015 17.9164 93.2484 18.1155 93.4257C18.3146 93.603 18.5741 93.6971 18.8405 93.6887L21.7417 93.5969C21.8784 93.5926 22.0129 93.5613 22.1375 93.505C22.2622 93.4486 22.3745 93.3683 22.468 93.2685C22.5616 93.1687 22.6345 93.0514 22.6827 92.9234C22.7309 92.7954 22.7534 92.6592 22.7489 92.5225L21.04 40.5514L12.9372 39.4589Z" fill="#535D71"/>
                                                                        <path d="M18.9407 8.8717C21.0779 9.74826 23.8073 8.72431 24.8406 6.65829C25.874 4.59227 25.0562 1.79419 23.0729 0.609824C21.0896 -0.574543 18.2383 0.0325306 16.9095 1.9221C15.8286 -0.0252986 12.9969 -0.521775 11.0526 0.564842C9.10839 1.65146 8.04938 3.92749 7.92378 6.15122C7.79818 8.37495 8.47424 10.5656 9.29896 12.6346C10.633 15.9813 14.9276 17.7833 18.2502 16.3905C16.7291 14.125 17.0294 10.8096 18.9407 8.8717Z" fill="#202C45"/>
                                                                        <path d="M27.5531 18.3085C27.4963 18.3086 27.4409 18.2902 27.3954 18.256L27.3926 18.2539L26.7984 17.7994C26.7709 17.7783 26.7478 17.752 26.7304 17.722C26.7131 17.6919 26.7018 17.6588 26.6973 17.6244C26.6927 17.5901 26.695 17.5551 26.704 17.5216C26.7129 17.4881 26.7284 17.4567 26.7495 17.4292C26.7706 17.4017 26.7969 17.3786 26.8269 17.3612C26.857 17.3439 26.8901 17.3326 26.9245 17.3281C26.9589 17.3235 26.9938 17.3258 27.0273 17.3348C27.0608 17.3437 27.0922 17.3592 27.1197 17.3803L27.5045 17.6754L28.414 16.489C28.4351 16.4615 28.4614 16.4384 28.4914 16.421C28.5214 16.4037 28.5545 16.3924 28.5889 16.3879C28.6232 16.3834 28.6582 16.3856 28.6916 16.3946C28.7251 16.4035 28.7565 16.419 28.784 16.4401L28.7841 16.4402L28.7784 16.448L28.7842 16.4402C28.8397 16.4828 28.876 16.5457 28.8852 16.6151C28.8943 16.6845 28.8756 16.7547 28.8331 16.8103L27.7634 18.2052C27.7386 18.2373 27.7068 18.2633 27.6704 18.2812C27.634 18.299 27.5939 18.3082 27.5534 18.3081L27.5531 18.3085Z" :fill="selected_colorpicker_values.primary_color" />
                                                                        <path d="M25.5268 30.6751C25.1983 30.5362 24.8433 30.471 24.4869 30.4841C24.1305 30.4972 23.7812 30.5882 23.4638 30.7509C23.1464 30.9136 22.8685 31.1438 22.6497 31.4255C22.4309 31.7072 22.2765 32.0334 22.1974 32.3812L16.7021 33.3226L16.4597 36.8016L24.2014 35.3632C24.7945 35.4573 25.4015 35.3306 25.9074 35.0071C26.4133 34.6836 26.783 34.1857 26.9464 33.6079C27.1098 33.03 27.0555 32.4123 26.7938 31.8718C26.5322 31.3312 26.0814 30.9055 25.5268 30.6751L25.5268 30.6751Z" fill="#FFB8B8"/>
                                                                        <path d="M16.0873 20.6895C16.8836 21.0473 17.5116 21.6978 17.8411 22.5061C18.1706 23.3144 18.1764 24.2186 17.8572 25.031L14.6718 33.139L18.6708 32.404C18.9205 32.3581 19.1784 32.4051 19.3959 32.5361C19.6134 32.667 19.7755 32.873 19.8517 33.1152L20.6489 35.6486C20.6905 35.781 20.7053 35.9203 20.6923 36.0585C20.6793 36.1966 20.6388 36.3308 20.5732 36.453C20.5076 36.5753 20.4182 36.6832 20.3103 36.7705C20.2024 36.8577 20.0781 36.9224 19.9448 36.9609L14.7471 38.4617C14.0896 38.6515 13.397 38.6864 12.7238 38.5635C12.0505 38.4407 11.4149 38.1635 10.8668 37.7536C10.3188 37.3438 9.87314 36.8125 9.56495 36.2015C9.25676 35.5905 9.09439 34.9163 9.09058 34.232C9.0888 33.9122 9.12167 33.5931 9.18861 33.2804L11.3726 23.0775C11.4787 22.582 11.6942 22.1166 12.0034 21.7152C12.3127 21.3138 12.7077 20.9867 13.1597 20.7577C13.6118 20.5288 14.1093 20.4038 14.6158 20.392C15.1224 20.3802 15.6251 20.4819 16.0873 20.6895Z" fill="#CFD6E6"/>
                                                                    </svg>
                                                                        <div class="bpa-front-module-heading" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ tab_container_data.summary_title }}</div>                                                                        
                                                                       
                                                                        <p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ summary_container_data.summary_content_text }}</p>
                                                                        
                                                                        <div class="bpa-front-summary-step-note bpa-front-module--note-desc" v-html="summary_container_data.summary_step_note" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family}" ></div>                                    
                                                                            
                                                                    </div>
                                                                    <div class="bpa-front-module--bs-summary-content">
                                                                        <div class="bpa-front-module--bs-summary-content-item" :style="{'border-color':selected_colorpicker_values.border_color}">
                                                                            <span :style="{'color': 
                                                                            selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px'}">
                                                                            {{front_label_edit_data.service_text}}</span>
                                                                            <div class="bpa-front-bs-sm__item-val" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size':selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family}">
                                                                            <?php esc_html_e('Sample service 1', 'bookingpress-appointment-booking'); ?></div>
                                                                        </div>
                                                                        <div class="bpa-front-module--bs-summary-content-item" :style="{'border-color':selected_colorpicker_values.border_color}">
                                                                            <span :style="{'color': 
                                                                            selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px'}">{{front_label_edit_data.date_time_text}}</span>
                                                                            <div class="bpa-front-bs-sm__item-val" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                            <?php
                                                                            echo esc_html(date_i18n($bookingpress_default_date_format, current_time('timestamp'))).', '. 
                                                                            esc_html(date_i18n($bookingpress_default_time_format,strtotime('10:00'))).' - '.esc_html(date_i18n($bookingpress_default_time_format,strtotime('10:30'))) ?></div>
                                                                        </div>
                                                                        <div class="bpa-front-module--bs-summary-content-item">
                                                                            <span :style="{'color': 
                                                                            selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.content_font_size+'px' }">{{front_label_edit_data.customer_text}}</span>
                                                                            <div class="bpa-front-bs-sm__item-val" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.sub_title_font_size+'px', 'font-family': selected_font_values.title_font_family }">Jerry G. Lugo</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bpa-front-module--bs-amount-details" :style="{'border-color':selected_colorpicker_values.border_color}">
                                                                        <div class="bpa-fm--bs-amount-item">
                                                                            <div class="bpa-front-total-payment-amount-label" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px' }">{{front_label_edit_data.total_amount_text}}
                                                                            </div>
                                                                            <div class="bpa-front-module--bs-ad--price" :style="{ 'color': selected_colorpicker_values.primary_color, 'font-family': selected_font_values.title_font_family }" class="bpa-front-module--bs-ad--price"><?php echo esc_html($bookingpress_price); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row>
                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                <div class="bpa-front-module-container bpa-front-module--payment-methods">
                                                                    <div class="bpa-front-module--pm-head">                                                                        
                                                                        <div class="bpa-front-module-heading" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family  }">{{ summary_container_data.payment_method_text }}</div>
                                                                    </div>
                                                                    <div class="bpa-front-module--pm-body">
                                                                        <div class="bpa-front--pm-body-items">
                                                                            <div class="bpa-front-module--pm-body__item __bpa-is-selected" :style="{ 'border-color': selected_colorpicker_values.primary_color }">
                                                                                <span class="material-icons-round">storefront</span>
                                                                                <div class="bpa-front-si-card--pm-label-wrap">
                                                                                    <p :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family  }">{{front_label_edit_data.locally_text}}</p>
                                                                                </div>
                                                                                <div class="bpa-front-si-card--checkmark-icon" >
                                                                                    <span class="material-icons-round" :style="{ 'color': selected_colorpicker_values.primary_color }">check_circle</span>
                                                                                </div>
                                                                                
                                                                            </div>
                                                                            <div class="bpa-front-module--pm-body__item" :style="{'border-color':selected_colorpicker_values.border_color}">
                                                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M17.9588 8.24063L8.75722 18.2812H5.38222C5.14786 18.2812 4.96036 18.0469 5.00723 17.8125L7.25722 3.5625C7.30412 3.23438 7.58537 3 7.91347 3H13.6322C17.5697 3.14062 18.6479 5.15622 17.9447 8.25002L17.9588 8.24063Z" fill="#002C8A"/>
                                                                                    <path d="M18.1088 7.3125C19.5151 8.0625 19.8432 9.4687 19.3744 11.3437C18.7651 14.1094 16.9369 15.2812 14.2651 15.3281L13.5151 15.375C13.2338 15.375 13.0463 15.5625 12.9994 15.8437L12.3901 19.5469C12.3432 19.875 12.0619 20.1094 11.7338 20.1094H8.9213C8.6869 20.1094 8.4994 19.875 8.5463 19.6406L9.57755 12.9375C9.6244 12.7031 18.1088 7.3125 18.1088 7.3125Z" fill="#009BE1"/>
                                                                                    <path d="M9.52148 13.2656L10.459 7.31252C10.4897 7.17152 10.5661 7.04458 10.6762 6.95138C10.7864 6.85818 10.9242 6.80388 11.0683 6.79688H15.5683C16.6465 6.79688 17.4433 6.98437 18.0996 7.31252C17.8652 9.37502 16.8808 12.7031 12.0996 12.7969H10.0371C9.80268 12.7969 9.56833 12.9844 9.52148 13.2656Z" fill="#001F6B"/>
                                                                                </svg>
                                                                                <div class="bpa-front-si-card--pm-label-wrap" >
                                                                                    <p :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.title_font_family }">
                                                                                    {{front_label_edit_data.paypal_text}}</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                    </div>
                                                </div>
                                                <div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.background_color,'border-color':selected_colorpicker_values.border_color }">
                                                    <el-button class="bpa-btn bpa-btn--borderless" :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}">
                                                        <span class="material-icons-round">west</span>
                                                        {{ booking_form_settings.goback_button_text }}
                                                    </el-button>
                                                    <el-button class="bpa-btn bpa-btn--primary" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color,color: selected_colorpicker_values.price_button_text_color,'font-size': selected_font_values.sub_title_font_size+'px','font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px' }">
                                                        {{ booking_form_settings.book_appointment_btn_text }}                                                    
                                                    </el-button>
                                                </div>
                                            </div>
                                        </el-tab-pane>
                                    </el-tabs>
                                </div>
                            </el-col> 
                            <el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
                                <div class="bpa-customize-step-side-panel">
                                    <div class="bpa-cs-sp--heading">
                                        <h4><?php esc_html_e('Label Settings', 'bookingpress-appointment-booking'); ?></h4>
                                    </div>                  
                                    <div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">                                        
                                        <h5 class="bpa-sm-sub-heading--item"><?php esc_html_e('Common field labels', 'bookingpress-appointment-booking'); ?></h5>                                        
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Go back button', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="booking_form_settings.goback_button_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Next button', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="booking_form_settings.next_button_text" class="bpa-form-control"></el-input>        
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Book appointment button', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="booking_form_settings.book_appointment_btn_text" class="bpa-form-control"></el-input>        
                                        </div>                                                
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Minutes label', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="booking_form_settings.book_appointment_min_text" class="bpa-form-control"></el-input>        
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Hours label', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="booking_form_settings.book_appointment_hours_text" class="bpa-form-control"></el-input>        
                                        </div>
                                        <div class="bpa-cs-sp-sub-module--separator"></div>
                                        <h5 class="bpa-sm-sub-heading--item"><?php esc_html_e('Service step labels', 'bookingpress-appointment-booking'); ?></h5>                                        
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Service step title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="tab_container_data.service_title " class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Category title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="category_container_data.category_title" class="bpa-form-control"></el-input>
                                        </div>              
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('All category', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="category_container_data.all_category_title" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Service title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="service_container_data.service_heading_title" class="bpa-form-control"></el-input>
                                        </div>                                                      
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Service duration', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="booking_form_settings.service_duration_label" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Service price', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="booking_form_settings.service_price_label" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-cs-sp-sub-module--separator"></div>
                                        <h5 class="bpa-sm-sub-heading--item"><?php esc_html_e('Date & Time step labels', 'bookingpress-appointment-booking'); ?></h5>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Date & Time step title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="tab_container_data.datetime_title" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Time slot title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="timeslot_container_data.timeslot_text" class="bpa-form-control"></el-input>
                                        </div>              
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Morning time slot title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="timeslot_container_data.morning_text" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Afternoon time slot title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="timeslot_container_data.afternoon_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Evening time slot title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="timeslot_container_data.evening_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Night time slot title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="timeslot_container_data.night_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Date & time step note', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input type="textarea" :rows="2" v-model="timeslot_container_data.date_time_step_note"> </el-input>
                                        </div>
                                        <div class="bpa-cs-sp-sub-module--separator"></div>
                                        <h5 class="bpa-sm-sub-heading--item"><?php esc_html_e('Basic Details Step labels', 'bookingpress-appointment-booking'); ?></h5>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Basic Details step title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="tab_container_data.basic_details_title" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-cs-sp-sub-module--separator"></div>   
                                        <h5 class="bpa-sm-sub-heading--item"><?php esc_html_e('Summary Step labels', 'bookingpress-appointment-booking'); ?></h5>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Summery step title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="tab_container_data.summary_title" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Summary description', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="summary_container_data.summary_content_text" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Service summary title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="front_label_edit_data.service_text" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Date & time summary title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="front_label_edit_data.date_time_text" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Customer summary title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="front_label_edit_data.customer_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Summary step note', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input type="textarea" :rows="2" v-model="summary_container_data.summary_step_note"> </el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Appointment details summary title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="front_label_edit_data.appointment_details" class="bpa-form-control"></el-input>
                                        </div>   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Total amount title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="front_label_edit_data.total_amount_text" class="bpa-form-control"></el-input>
                                        </div>                                                      
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Payment method title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="summary_container_data.payment_method_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Pay locally payment title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="front_label_edit_data.locally_text" class="bpa-form-control"></el-input>
                                        </div>                                   
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('PayPal payment title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="front_label_edit_data.paypal_text" class="bpa-form-control"></el-input>
                                        </div>                                   
                                    </div>   
                                </div>
                            </el-col>
                        </el-row>
                    </div>                    
                </el-tab-pane>
                <el-tab-pane name="my_bookings" v-if="bookingpress_tab_change_loader == '0'">  
                    <div class="bpa-customize-step-content-container __bpa-is-sidebar">
                        <el-row type="flex">
                            <el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
                                <div class="bpa-customize-step-side-panel">
                                    <div class="bpa-cs-sp--heading">
                                        <h4><?php esc_html_e('Content Settings', 'bookingpress-appointment-booking'); ?></h4>
                                    </div>
                                    <div class="bpa-cs-sp-sub-module bpa-sm--swtich">
                                        <div class="bpa-sm--item --bpa-is-flexbox">
                                            <label class="bpa-form-label"><?php esc_html_e('Allow cancel appointment', 'bookingpress-appointment-booking'); ?></label>
                                            <el-switch v-model="my_booking_field_settings.allow_to_cancel_appointment" class="bpa-swtich-control"></el-switch>
                                        </div>            
                                    </div>                                       
                                    <div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">
                                        <div class="bpa-sm--item"  v-if="my_booking_field_settings.allow_to_cancel_appointment">
                                            <label class="bpa-form-label"><?php esc_html_e('Appointment cancellation confirmation', 'bookingpress-appointment-booking'); ?></label>
                                            <el-select v-model="my_booking_field_settings.appointment_cancellation_confirmation" class="bpa-form-control" filterable popper-class="bpa-el-select--is-with-navbar">
                                                <el-option v-for="item in bookingpress_all_global_pages" :key="item.post_title" :label="item.post_title"  :value="''+item.ID"></el-option>
                                            </el-select>
                                        </div>
                                        <div class="bpa-sm--item" v-if="my_booking_field_settings.allow_to_cancel_appointment">
                                            <label class="bpa-form-label"><?php esc_html_e('Cancelled appointment redirection', 'bookingpress-appointment-booking'); ?></label>
                                            <el-select v-model="my_booking_field_settings.after_cancelled_appointment_redirection" class="bpa-form-control" filterable popper-class="bpa-el-select--is-with-navbar">
                                                <el-option v-for="item in bookingpress_all_global_pages" :key="item.post_title" :label="item.post_title"  :value="''+item.ID"></el-option>
                                            </el-select>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Delete account content', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input type="textarea" v-model="delete_account_content" :rows="10" class="bpa-form-control"/>
                                        </div>
                                    </div>                                            
                                </div>
                            </el-col>
                            <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                                <div class="bpa-customize-booking-form-preview-container --bpa-my-bookings">
                                    <div class="bpa-cmb-step-preview" :style="{ 'background': selected_colorpicker_values.background_color }">                                
                                        <div class="bpa-front-cp-my-appointment">
                                            <div class="bpa-front-cp-top-navbar" :style="{'border-color':selected_colorpicker_values.border_color}">
                                                <div class="bpa-cp-tn__left">
                                                    <div class="bpa-front-module-heading"  :style="{'color': selected_colorpicker_values.label_title_color, 'font-family': selected_font_values.title_font_family}">{{ my_booking_field_settings.mybooking_title_text }}</div>	                                                    
                                                </div>
                                                <div class="bpa-cp-tn__right">
                                                    <el-dropdown trigger="click" placement="top">
                                                        <div class="bpa-tn__dropdown-head">
                                                            <img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/profile-img.jpg'); ?>" class="bpa-cp-pd__avatar">
                                                            <div class="bpa-cp-pd__title" :style="{'color': selected_colorpicker_values.label_title_color, 'font-family': selected_font_values.title_font_family+' !important'}">Jerome Bell</div>
                                                            <span class="material-icons-round">keyboard_arrow_down</span>
                                                        </div>
                                                        <el-dropdown-menu class="bpa-tn__dropdown-menu" slot="dropdown">
                                                            <el-dropdown-item class="bpa-tn__dropdown-item">
                                                                <a href="javascript:void(0)" class="bpa-tm__item __bpa-is-active" :style="{'color': selected_colorpicker_values.primary_color,'font-family': selected_font_values.title_font_family+' !important'}">
                                                                    <span class="material-icons-round" :style="{'color': selected_colorpicker_values.primary_color}">assignment</span> {{my_booking_field_settings.my_appointment_menu_title}}
                                                                </a>
                                                            </el-dropdown-item>
                                                            <el-dropdown-item class="bpa-tn__dropdown-item">
                                                                <a href="javascript:void(0)" class="bpa-tm__item" :style="{'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family+' !important'}">
                                                                    <span class="material-icons-round" :style="{'color': selected_colorpicker_values.content_color }">delete</span>{{my_booking_field_settings.delete_appointment_menu_title}}
                                                                </a>
                                                            </el-dropdown-item>
                                                        </el-dropdown-menu>
                                                    </el-dropdown>
                                                </div>
                                            </div>                                                                                
                                            <div class="bpa-front-cp--filter-wrapper">
                                                <div class="bpa-front-cp--fw__row">
                                                    <div class="bpa-form-control bpa-custom-form-control bpa-front-cp--fw__col bpa-front-cp--fw__date-picker-col">
                                                        <el-date-picker class="bpa-form-control bpa-form-control--date-picker" :format="bpa_date_common_date_format"  v-model="appointment_date_range[0]" type="date" :placeholder="my_booking_field_settings.search_date_title" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-el-datepicker-widget-wrapper bpa-front-cp__filter-dropdown" :style="{'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color}" :picker-options="filter_pickerOptions"> </el-date-picker>
                                                        <el-date-picker class="bpa-form-control bpa-form-control--date-picker" :format="bpa_date_common_date_format"  v-model="appointment_date_range[1]" type="date" :placeholder="my_booking_field_settings.search_end_date_title" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-el-datepicker-widget-wrapper bpa-front-cp__filter-dropdown" :style="{'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color}" :picker-options="filter_pickerOptions"> </el-date-picker>
                                                    </div>
                                                    <div class="bpa-form-control bpa-custom-form-control bpa-front-cp--fw__col __bpa-is-search-icon">
                                                        <span class="material-icons-round">search</span>
                                                        <input type="text" :style="{'color': selected_colorpicker_values.label_title_color,  'font-family': selected_font_values.title_font_family,'border-color':selected_colorpicker_values.border_color}" :placeholder="my_booking_field_settings.search_appointment_title" ></input>
                                                    </div>
                                                    <div class="bpa-front-cp--fw__col">
                                                        <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" :style="{'background': selected_colorpicker_values.primary_color,'border-color':selected_colorpicker_values.primary_color,'color': selected_colorpicker_values.price_button_text_color,'font-family': selected_font_values.title_font_family }">{{my_booking_field_settings.apply_button_title}}</el-button>
                                                    </div>
                                                </div>
                                            </div>
                                        <div class="bpa-front-cp-ma-table__is-admin">
                                            <div class="bpa-front-ma-table__head" :style="{'border-color':selected_colorpicker_values.border_color}">
                                                <div class="bpa-mat-head__item --bpa-is-flex" :style="{'color': selected_colorpicker_values.label_title_color,'font-family': selected_font_values.title_font_family }">
                                                    <?php esc_html_e('Id', 'bookingpress-appointment-booking'); ?>
                                                    <span class="material-icons-round">unfold_more</span>
                                                </div>
                                                <div class="bpa-mat-head__item --bpa-is-flex" :style="{'color': selected_colorpicker_values.label_title_color,'font-family': selected_font_values.title_font_family }">
                                                    <?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?>
                                                    <span class="material-icons-round">unfold_more</span>
                                                </div>
                                                <div class="bpa-mat-head__item --bpa-is-flex" :style="{'color': selected_colorpicker_values.label_title_color,'font-family': selected_font_values.title_font_family }">
                                                    <?php esc_html_e('Date', 'bookingpress-appointment-booking'); ?>
                                                    <span class="material-icons-round">unfold_more</span>                                                                            
                                                </div>
                                                <div class="bpa-mat-head__item" :style="{'color': selected_colorpicker_values.label_title_color,'font-family': selected_font_values.title_font_family }">
                                                    <?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?>
                                                </div>
                                                <div class="bpa-mat-head__item" :style="{'color': selected_colorpicker_values.label_title_color,'font-family': selected_font_values.title_font_family }">
                                                    <?php esc_html_e('Payment', 'bookingpress-appointment-booking'); ?>
                                                </div>
                                            </div>
                                            <div class="bpa-front-ma-table__body">
                                                <div class="bpa-front-mat__row" v-for="row_data in dummy_data">
                                                    <div class="bpa-mat-body__item --bpa-is-flex --bpa-is-expand-icon">
                                                        <span class="material-icons-round" :style="{'color': selected_colorpicker_values.content_color   }">add_circle_outline</span>
                                                        <span :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family }">{{row_data.id}}</span>
                                                    </div>
                                                    <div class="bpa-mat-body__item" :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family}">{{row_data.appointment_service_name}}</div>
                                                    <div class="bpa-mat-body__item --bpa-is-mat-body-date-info">
                                                        <div class="bpa-ma-date-time-details">
                                                            <div class="bpa-ma-dt__date-val" :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family }">{{row_data.appointment_date}}</div>
                                                            <div class="bpa-ma-dt__time-val" :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family }"><span class="material-icons-round" :style="{'color': selected_colorpicker_values.content_color}">access_time</span>30 mins</div>
                                                        </div>
                                                    </div>
                                                    <div class="bpa-mat-body__item">
                                                        <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                                                            <div slot="content">
                                                                <span><?php esc_html_e( 'Pending', 'bookingpress-appointment-booking' ); ?></span>
                                                            </div>
                                                            <div class="bpa-ma-status-box __bpa-is-pending">
                                                                <div class="bpa-sb__circle"></div>
                                                            </div>
                                                        </el-tooltip>
                                                    </div>
                                                    <div class="bpa-mat-body__item">
                                                        <span :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family }"> {{row_data.appointment_payment}}</span>
                                                        <div class="bpa-front-ma-table-actions-wrap" v-show="my_booking_field_settings.allow_to_cancel_appointment == true">
                                                            <div class="bpa-front-ma-taw__card">										
                                                                    <el-tooltip effect="dark" content="<?php esc_html_e('Cancel Appointment', 'bookingpress-appointment-booking'); ?>" placement="top"  open-delay="300">
                                                                        <el-popconfirm 
                                                                        :confirm-button-text='my_booking_field_settings.cancel_appointment_yes_btn_text' 
                                                                        :cancel-button-text='my_booking_field_settings.cancel_appointment_no_btn_text' 
                                                                        icon="false" 
                                                                        :title="my_booking_field_settings.cancel_appointment_confirmation_message"                                         
                                                                        confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                                                        cancel-button-type="bpa-btn bpa-btn__small">
                                                                            <el-button slot="reference" class="bpa-btn bpa-btn--icon-without-box">
                                                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.0397 3.49904H14.7892C15.6137 3.49904 16.2882 4.17361 16.2882 4.99808V9.8203C15.8477 9.42598 15.3419 9.10298 14.7892 8.86944V7.24664H4.29592V14.7418C4.29592 15.1541 4.6332 15.4914 5.04544 15.4914H7.55047C7.72418 16.0334 7.98125 16.5381 8.30658 16.9904H4.29592C3.46395 16.9904 2.79688 16.3158 2.79688 15.4914L2.80437 4.99808C2.80437 4.17361 3.46395 3.49904 4.29592 3.49904H5.04544V2.74952C5.04544 2.33728 5.38272 2 5.79496 2C6.20719 2 6.54448 2.33728 6.54448 2.74952V3.49904H12.5406V2.74952C12.5406 2.33728 12.8779 2 13.2902 2C13.7024 2 14.0397 2.33728 14.0397 2.74952V3.49904Z" />
                                                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.27344 13.8431C8.27344 16.2817 10.2502 18.2584 12.6888 18.2584C15.1274 18.2584 17.1041 16.2817 17.1041 13.8431C17.1041 11.4045 15.1274 9.42773 12.6888 9.42773C10.2502 9.42773 8.27344 11.4045 8.27344 13.8431ZM13.3151 13.8411L14.5622 12.5944L14.5603 12.5925L14.5617 12.5912L13.9372 11.9668L12.689 13.215L11.4408 11.9668L10.8164 12.5912L10.8175 12.5923L10.8154 12.5944L12.0627 13.8413L10.8189 15.0851L10.8204 15.0867L10.8154 15.0918L11.4401 15.7165L12.6888 14.4674L13.9375 15.7165L14.5622 15.0918L14.5574 15.0869L14.5592 15.0851L13.3151 13.8411Z" />
                                                                                </svg>
                                                                            </el-button>
                                                                        </el-popconfirm>
                                                                    </el-tooltip>										
                                                                </div>
                                                            </div>
                                                        </div>                                                    
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </el-col>
                            <el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
                                <div class="bpa-customize-step-side-panel">
                                    <div class="bpa-cs-sp--heading">
                                        <h4><?php esc_html_e('Label Settings', 'bookingpress-appointment-booking'); ?></h4>                               
                                    </div>
                                    <div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">       
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('My booking title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.mybooking_title_text " class="bpa-form-control"></el-input>
                                        </div>    
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Apply button', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.apply_button_title" class="bpa-form-control"></el-input>
                                        </div>              
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Search appointment placeholder', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.search_appointment_title" class="bpa-form-control"></el-input>
                                        </div>             
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Start date placeholder', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.search_date_title" class="bpa-form-control"></el-input>
                                        </div>  
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('End date placeholder', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.search_end_date_title" class="bpa-form-control"></el-input>
                                        </div>                                    
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('My appointments title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.my_appointment_menu_title " class="bpa-form-control"></el-input>
                                        </div>                                  
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Delete account title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.delete_appointment_menu_title" class="bpa-form-control"></el-input>
                                        </div>                                                                                                  
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('ID title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.id_main_heading" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Service title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.service_main_heading" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Date title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.date_main_heading" class="bpa-form-control"></el-input>
                                        </div>                                  
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Status title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.status_main_heading" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Payment title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.payment_main_heading" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Booking id title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.booking_id_heading" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Booking time title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.booking_time_title" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Payment details title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.payment_details_title" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Payment method title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.payment_method_title" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Total amount title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.total_amount_title" class="bpa-form-control"></el-input>
                                        </div>                                   

                                        <div class="bpa-sm--item">
                                            <h5><?php esc_html_e('Cancel appointment messages', 'bookingpress-appointment-booking'); ?></h5>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Cancel appointment title', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.cancel_appointment_title" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Confirmation message', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.cancel_appointment_confirmation_message" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('No button text', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.cancel_appointment_no_btn_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Yes button text', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.cancel_appointment_yes_btn_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <h5><?php esc_html_e('Cancel Appointment Confirmation Messages', 'bookingpress-appointment-booking'); ?></h5>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Booking ID Label', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.cancel_booking_id_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Service label', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.cancel_service_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Date & Time label', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.cancel_date_time_text" class="bpa-form-control"></el-input>
                                        </div>
                                        <div class="bpa-sm--item">
                                            <label class="bpa-form-label"><?php esc_html_e('Confirm cancellation button text', 'bookingpress-appointment-booking'); ?></label>
                                            <el-input v-model="my_booking_field_settings.cancel_button_text" class="bpa-form-control"></el-input>
                                        </div>
                                    </div>    
                                </div>
                            </el-col>
                        </el-row>
                    </div>
                </el-tab-pane>
            </el-tabs>
        </div>    
    </el-container>    
</el-main>
<el-dialog custom-class="bpa-dialog bpa-dialog---bookingform_custom_css" title="" :visible.sync="add_custom_css_modal" :visible.sync="centerDialogVisible" close-on-press-escape="close_modal_on_esc" :modal="add_custom_css_modal" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading"><?php esc_html_e('Custom CSS', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
        </el-row>
    </div>
    <div class="bpa-dialog-body">
        <div class="bpa-dialog--custom_css_body">
            <el-input type="textarea" :rows="18" class="bpa-form-control" v-model="bookigpress_form_custom_css"/>
        </div>
        <div class="bpa-hw-right-btn-group bpa-custom-css-block">
            <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" @click="bookingpress_save_custom_css()" >                    
                <span class="bpa-btn__label"><?php esc_html_e('OK', 'bookingpress-appointment-booking'); ?></span>                        
            </el-button> 
            <el-button class="bpa-btn bpa-btn__medium" @click="close_custom_css_modal()">
                <span><?php esc_html_e('Close', 'bookingpress-appointment-booking'); ?></span>
            </el-button>                    
        </div>
    </div>    
</el-dialog>

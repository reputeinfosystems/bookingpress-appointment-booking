<div id="addon_root_app" class="bookingpress-addons bookingpress_page_wrapper">
    <?php require_once __DIR__ . '/components/Header.php'; ?>

    <div class="addons-app-root bookingpress_page_inner_wrapper" v-cloak id="addons-app-root">
        <bp-ui-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet bpa-addon-list-card" id="all-page-main-container">
            <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
                <div class="bpa-back-loader"></div>
            </div>

        <!--     <bp-ui-row type="flex" class="bpa-mlc-head-wrap">
                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                    <h1 class="bpa-page-heading bpa-adddons-page-heading"><?php esc_html_e( 'Add-ons', 'bookingpress-appointment-booking' ); ?></h1>
                </bp-ui-col>
            </bp-ui-row> -->

            <div id="bpa-main-container">
                <bp-ui-container class="bpa-addons-container">
                    <?php
                        if( class_exists( 'BookingPressPro\admin\Addons' ) && method_exists( 'BookingPressPro\admin\Addons', 'render_additional_modules' ) ){
                            BookingPressPro\admin\Addons::render_additional_modules();
                        }
                    ?>
                    <div class="bpa-addon-sub-list-wrapper bpa-addon-feature-list-wrapper">
                        <bp-ui-row v-for="(addonsList, category) in bpa_lite_addons_new" :key="category">
                            <bp-ui-row type="flex" class="bpa-mlc-head-wrap" :class="category + '-bpa-mlc-head-wrap'">
                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                                    <h1 class="bpa-page-heading bpa-adddons-page-heading" v-if="category == 'features'"><?php esc_html_e( 'Feature Add-ons', 'bookingpress-appointment-booking' ); ?></h1>
                                    <h1 class="bpa-page-heading bpa-adddons-page-heading" v-else-if="category == 'payment_gateways'"><?php esc_html_e( 'Payment Gateways', 'bookingpress-appointment-booking' ); ?></h1>
                                    <h1 class="bpa-page-heading bpa-adddons-page-heading" v-else-if="category == 'integrations'"><?php esc_html_e( 'Integrations', 'bookingpress-appointment-booking' ); ?></h1>
                                </bp-ui-col>
                            </bp-ui-row>

                            <bp-ui-row :gutter="30" class="bpa-addons-items-row">
                                <bp-ui-col
                                    :xs="12"
                                    :sm="12"
                                    :md="12"
                                    :lg="8"
                                    :xl="6"
                                    v-for="addons in addonsList"
                                    :key="addons.addon_key"
                                    class="bpa-addons-items-col"
                                >
                                    <div class="bpa-addon-item" :id="addons.addon_key + '_activate_addon'">
                                        <span class="bpa-ai-icon" :class="addons.addon_icon_slug"></span>
                                        <div class="bpa-ai-name">
                                            <h3>{{ addons.addon_name }}</h3>
                                        </div>
                                        <div class="bpa-ai-desc">
                                            <p>{{ addons.addon_description }}</p>
                                        </div>
                                        <?php
                                            if( class_exists( 'BookingPressPro\admin\Addons' ) && method_exists( 'BookingPressPro\admin\Addons', 'render_addon_additional_buttons' ) ){
                                                BookingPressPro\admin\Addons::render_addon_additional_buttons();
                                            } else {
                                        ?>
                                            <div class="bpa-ai-btns">
                                                <bp-ui-row type="flex">
                                                    <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                                        <bp-ui-button class="bpa-btn bpa-btn--primary bpa-btn--full-width" @click="open_premium_modal()">
                                                            <span class="bpa-btn__label"><?php esc_html_e( 'Upgrade to Pro', 'bookingpress-appointment-booking' ); ?></span>
                                                        </bp-ui-button>
                                                    </bp-ui-col>
                                                </bp-ui-row>
                                            </div>
                                        <?php
                                            }
                                        ?>
                                        <div class="bpa-ai-doc-link">
                                            <bp-ui-link :href="addons.addon_documentation" target="_blank">
                                                <i class="material-icons-round">description</i><?php esc_html_e( 'Read More', 'bookingpress-appointment-booking' ); ?>
                                            </bp-ui-link>
                                        </div>
                                        <?php
                                            if( class_exists( 'BookingPressPro\admin\Addons' ) && method_exists( 'BookingPressPro\admin\Addons', 'render_addon_additional_buttons' ) ){
                                                BookingPressPro\admin\Addons::render_addon_additional_content();                                            
                                            }
                                        ?>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </bp-ui-row>
                    </div>
                </bp-ui-container>
            </div>
        </bp-ui-main>
    </div>
</div>
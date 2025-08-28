<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<nav class="bpa-header-navbar bpa-header-navbar--v2">
    <div class="bpa-header-navbar-wrap bpa-growth-tools">
        <div class="bpa-navbar-brand">
            <a href="<?php echo esc_url( admin_url() . 'admin.php?page=bookingpress' ); ?>" class="navbar-logo">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="64" height="64" rx="12"/>
                    <path d="M50 18.9608V47.2745C50 49.3359 48.325 51 46.25 51H17.75C15.675 51 14 49.3359 14 47.2745V18.9608C14 16.8993 15.675 15.2353 17.75 15.2353H23V14.1176C23 13.7451 23.375 13 24.125 13C24.875 13 25.25 13.7451 25.25 14.1176V18.5882C25.25 18.9608 24.875 19.7059 24.125 19.7059C23.375 19.7059 23 18.9608 23 18.5882V17.4706H18.5C17.25 17.4706 16.25 18.4641 16.25 19.7059V46.5294C16.25 47.7712 17.25 48.7647 18.5 48.7647H45.5C46.75 48.7647 47.75 47.7712 47.75 46.5294V19.7059C47.75 18.4641 46.75 17.4706 45.5 17.4706H41C41 17.4706 41 18.0418 41 18.5882C41 18.9608 40.625 19.7059 39.875 19.7059C39.125 19.7059 38.75 18.9608 38.75 18.5882V17.4706H33.125C32.5 17.4706 32 16.9739 32 16.3529C32 15.732 32.5 15.2353 33.125 15.2353H38.75V14.1176C38.75 13.7451 39.125 13 39.875 13C40.625 13 41 13.7451 41 14.1176V15.2353H46.25C48.325 15.2353 50 16.8993 50 18.9608Z" fill="white"/>
                    <path d="M37.2501 30.8823C37.2501 30.8823 38.0001 30.1372 38.0001 27.9019C38.0001 24.1765 35.7501 23.4314 32.7501 23.4314H26.0001V39.0784H30.5001V43.549H32.7501V39.0784C35.3501 39.0784 37.1751 39.0784 38.5251 37.4144C39.1751 36.6196 39.5001 35.6013 39.5001 34.5582C39.5001 34.0118 39.4251 33.4654 39.3001 33.1176C38.9751 32.2732 38.7501 31.6274 37.2501 30.8823ZM35.0001 36.8431C34.2501 36.8431 32.7501 36.8431 32.7501 36.8431C32.7501 36.8431 32.7501 36.098 32.7501 34.6078C32.7501 33.366 33.7501 32.3725 35.0001 32.3725C36.2001 32.3725 37.2501 33.3412 37.2501 34.6078C37.2501 35.9242 36.1501 36.8431 35.0001 36.8431ZM33.1751 30.6836C32.8001 30.8575 32.4251 31.081 32.0751 31.3294C31.2501 31.9503 30.5001 32.8444 30.5001 34.6078V36.8431H28.2501V25.6667H32.7501C34.5501 25.6667 35.7501 26.4118 35.7501 27.9019C35.7501 29.268 34.7251 29.9137 33.1751 30.6836Z" fill="white"/>
                </svg>
            </a>
        </div>
        <span class="bpa_growth_tools_heading"> Growth Plugins </span> 
	</div>
</nav>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet bpa-growth-tools" id="all-page-main-container">	
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>	
	<div id="bpa-main-container">		
		<el-container class="bpa-growth-tools-container">
			<div class="bpa-growth-tools-sub-list-wrapper">
            <?php  
                global $BookingPress;
                if( ! $BookingPress->bpa_is_pro_active() ){ ?>
                <div class="bpa_set_bg_image">
                    <el-row type="flex" class="bpa-mlc-head-wrap">
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                            <div class="bpa-gt-heading-cls">
                                <div class="bpa-gt-page-heading">BookingPress: Top-rated Premium <span class="bpa-gt-page-heading bpa-page-heading-highlight">Appointment Booking Plugin</div>
                            </div>
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-mlc-head-wrap">
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                            <div class="bpa-gt-heading-inner-cls">
                                <div class="bpa-gt-page-inner-contain">
                                    Upgrade to BookingPress Premium and revolutionize your booking experience! With our Premium package, you can effortlessly streamline the booking process for your customers and automate various employee tasks. Don't wait any longer - take the leap to unlock the full potential of the BookingPress Plugin, and enjoy a more efficient, user-friendly, and feature-rich solution that will help your business thrive. Upgrade today and experience the difference!
                                </div>
                            </div>
                            <div class="bpa-gt-heading-inner-heading"> 1,00,000+ Users ❤️ BookingPress </div>
                        </el-col>
                    </el-row>
                </div>
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <div class="bpa-pre-features-box">
                            <div class="bpa-feature-list-head"> <span class="bpa-page-heading-highlight"> Premium </span> Features Highlight </div>
                            <ul class="bpa-feature-list-cls">
                                <li class="bpa-feature-list-li"> 20+ payment gateways free to use </li>
                                <li class="bpa-feature-list-li"> Manage Unlimited Business Location </li>
                                <li class="bpa-feature-list-li"> Recurring Appointments </li>
                                <li class="bpa-feature-list-li"> Separate Staff Member Panel </li>
                                <li class="bpa-feature-list-li"> WhatsApp, SMS & Email Notifications </li>
                                <li class="bpa-feature-list-li"> Reports & Conversion Tracking facility </li>
                            </ul>
                        </div> 
					</el-col>
				</el-row>
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <div class="bpa-upgrade-premium-cls">
                            <a href="https://www.bookingpressplugin.com/pricing/?utm_source=liteversion&utm_medium=plugin&utm_campaign=Upgrade+to+Premium&utm_id=bookingpress_2" class="bpa-upgrade-premium-btn" target="_blank"> Upgrade to Premium </a>
                        </div> 
					</el-col>
				</el-row>
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <span class="bpa-growth-tools-spacer"></span>
					</el-col>
				</el-row>
            <?php } ?>
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <div class="bpa-gt-page-inner-block-heading"> Our <span class="bpa-page-heading-highlight"> Family WordPress Plugins </span> </div>
					</el-col>
				</el-row>
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <div class="bpa-gt-heading-main-inner-cls">
						    <div class="bpa-gt-page-inner-contain">
                            You will get the same user-friendly experience throughout all of our plugins. Enjoy single-window 24/7 support for all our plugins. All of our plugins are compatible with each other.
                            </div>
                        </div>
					</el-col>
				</el-row>
                <div class="bpa-sec-space"> </div>
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <div class="bpa-plugin-details-cls">
                            <div class="bpa-gt-plugin-icon">
                                <span class="bpa-plugin-icon bpa-afp-icon"></span>
                            </div>
                            <div class="bpa-gt-plugin-dec">
                                <div class="bpa-plugin-heading afp-heading"> AffiliatePress <span class="bpa-plugin-heading-cls">- WordPress Affiliate Plugin</span> </div>
                                <div class="bpa-plugin-heading-desc"> Seamlessly compatible with WooCommerce, ARMember, BookingPress, and 20+ other platforms, AffiliatePress makes running affiliate programs effortless. With multiple commission types, powerful tracking, and a modern interface, it’s the smarter choice over costly and complex affiliate tools. </div>
                                <div class="bpa-plugin-key-feature"> Key Features: </div>
                                <ul class="bpa-feature-list-cls-plugin-dec">
                                    <li class="bpa-feature-list-li-plugin"> Easy Affiliates Management </li>
                                    <li class="bpa-feature-list-li-plugin"> Multiple Commission Types </li>
                                    <li class="bpa-feature-list-li-plugin"> Customized Affiliate Signup </li>
                                    <li class="bpa-feature-list-li-plugin"> Advanced Reports </li>
                                    <li class="bpa-feature-list-li-plugin"> Realtime Commission Tracking </li>
                                    <li class="bpa-feature-list-li-plugin"> AI-Powered Fraud Detection </li>
                                </ul>
                                <div style="margin-top:40px;">
                                    <a href="https://www.affiliatepressplugin.com" target="_blank" class="bpa-learnmore-btn afp-plugin"> Learn More </a>
                                    <?php if ( (is_plugin_active('affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php')) || file_exists( WP_PLUGIN_DIR . '/affiliatepress-affiliate-marketing/affiliatepress-affiliate-marketing.php')  ) { ?>
                                        <el-button class="el-button bpa-btn bpa-install-btn afp-plugin" disabled="is_disabled">
                                            <span class="bpa-btn__label"> Installed </span> 
                                        <?php } else {
                                            $plugin_name = 'affiliatepress-affiliate-marketing';
                                            $plugin_install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_name ), 'install-plugin_' . $plugin_name ).'&bpa_utm=bpa_growth_tool';

                                            //window.location.href = '<?php echo $plugin_install_url; // phpcs:ignore
                                            ?>
                                            <el-button class="el-button bpa-btn bpa-install-btn afp-plugin" @click="bpa_download_plugins('affiliatepress')" :class="(is_display_affiliatepress_save_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disabled">
                                            <span class="bpa-btn__label"> Install </span> 
                                        <?php }  ?>
                                        <div class="bpa-btn--loader__circles">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                    </el-button>
                                </div>
                            </div>
                        </div>
					</el-col>
				</el-row>
                <hr class="bpa-section-line"> 
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <div class="bpa-plugin-details-cls">
                            <div class="bpa-gt-plugin-icon">
                                <span class="bpa-plugin-icon bpa-arm-icon"></span>
                            </div>
                            <div class="bpa-gt-plugin-dec">
                                <div class="bpa-plugin-heading"> ARMember <span class="bpa-plugin-heading-cls">- WordPress Membership Plugin </span> </div>
                                <div class="bpa-plugin-heading-desc"> Can you imagine a WordPress Membership Plugin that is ridiculously easy to operate, offers a wide range of features, excels in performance, and boasts a modern user interface? It's very different and much better than even the most popular membership plugins available here. </div>
                                <div class="bpa-plugin-key-feature"> Key Features: </div>
                                <ul class="bpa-feature-list-cls-plugin-dec">
                                    <li class="bpa-feature-list-li-plugin"> Membership Setup Wizard </li>
                                    <li class="bpa-feature-list-li-plugin"> Email Notification Templates </li>
                                    <li class="bpa-feature-list-li-plugin"> Unlimited Membership Levels </li>
                                    <li class="bpa-feature-list-li-plugin"> Live form Editor </li>
                                    <li class="bpa-feature-list-li-plugin"> Create Free & Paid Memberships </li>
                                    <li class="bpa-feature-list-li-plugin"> Captcha Free Anti-spam Facility </li>
                                </ul>
                                <div style="margin-top:40px;">
                                    <a href="https://www.armemberplugin.com" target="_blank" class="bpa-learnmore-btn"> Learn More </a>
                                    <?php if ( (is_plugin_active('armember-membership/armember-membership.php')) || file_exists( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php')  ) { ?>
                                        <el-button class="el-button bpa-btn bpa-install-btn arm-plugin" disabled="is_disabled">
                                            <span class="bpa-btn__label"> Installed </span> 
                                        <?php } else {
                                            $plugin_name = 'armember-membership';
                                            $plugin_install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_name ), 'install-plugin_' . $plugin_name ).'&bpa_utm=bpa_growth_tool';

                                            //window.location.href = '<?php echo $plugin_install_url; // phpcs:ignore
                                            ?>
                                            <el-button class="el-button bpa-btn bpa-install-btn arm-plugin" @click="bpa_download_plugins('armember')" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disabled">
                                            <span class="bpa-btn__label"> Install </span> 
                                        <?php }  ?>
                                        <div class="bpa-btn--loader__circles">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                    </el-button>
                                </div>
                            </div>
                        </div>
					</el-col>
				</el-row>
                <hr class="bpa-section-line"> 
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <div class="bpa-plugin-details-cls">
                            <div class="bpa-gt-plugin-icon">
                                <span class="bpa-plugin-icon bpa-arf-icon"></span>
                            </div>
                            <div class="bpa-gt-plugin-dec">
                                <div class="bpa-plugin-heading arf-heading"> ARForms <span class="bpa-plugin-heading-cls">- WordPress Form Builder Plugin </span> </div>
                                <div class="bpa-plugin-heading-desc"> ARForms is an all-in-one WordPress form builder plugin. It not only allows you to create contact forms for your site but also empowers you to build WordPress forms such as feedback forms, survey forms, and various other types of forms with responsive designs. </div>
                                <div class="bpa-plugin-key-feature"> Key Features: </div>
                                <ul class="bpa-feature-list-cls-plugin-dec">
                                    <li class="bpa-feature-list-li-plugin"> Real-Time Form Editor </li>
                                    <li class="bpa-feature-list-li-plugin"> Multi-Column Option </li>
                                    <li class="bpa-feature-list-li-plugin"> Styling & Unlimited Color Option </li>
                                    <li class="bpa-feature-list-li-plugin"> Built-In Anti Spam Protection </li>
                                    <li class="bpa-feature-list-li-plugin"> Material & Rounded Style Forms </li>
                                    <li class="bpa-feature-list-li-plugin"> Popular Page Builders Support </li>
                                </ul>
                                <div style="margin-top:40px;">
                                    <a href="https://www.arformsplugin.com" target="_blank"  class="bpa-learnmore-btn arf-plugin"> Learn More </a>
                                        <?php if ( (is_plugin_active('arforms-form-builder/arforms-form-builder.php')) || file_exists( WP_PLUGIN_DIR . '/arforms-form-builder/arforms-form-builder.php') ) { ?>
                                        <el-button class="el-button bpa-btn bpa-install-btn arf-plugin" disabled="is_disabled">
                                            <span class="bpa-btn__label"> Installed </span> 
                                        <?php } else {
                                            $plugin_name = 'arforms-form-builder';
                                            $plugin_install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_name ), 'install-plugin_' . $plugin_name );
                                            //window.location.href = '<?php echo $plugin_install_url; // phpcs:ignore
                                            ?>
                                            <el-button class="el-button bpa-btn bpa-install-btn arf-plugin" @click="bpa_download_plugins('arforms')" :class="(is_display_arforms_save_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disabled">
                                            <span class="bpa-btn__label"> Install </span> 
                                        <?php }  ?>
                                        <div class="bpa-btn--loader__circles">                    
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                    </el-button>
                                </div>
                            </div>
                        </div>
					</el-col>
				</el-row>
                <hr class="bpa-section-line">
                <el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
                        <div class="bpa-plugin-details-cls">
                            <div class="bpa-gt-plugin-icon">
                                <span class="bpa-plugin-icon bpa-arp-icon"></span>
                            </div>
                            <div class="bpa-gt-plugin-dec">
                                <div class="bpa-plugin-heading arp-heading"> ARPrice <span class="bpa-plugin-heading-cls">- WordPress Pricing Table Plugin </span> </div>
                                <div class="bpa-plugin-heading-desc">ARPrice is a WordPress pricing table plugin that enables you to effortlessly craft responsive pricing tables and plan comparison tables. With its powerful and flexible real-time editor, you can swiftly design pricing tables, using multiple templates, to suit various WordPress themes. </div>
                                <div class="bpa-plugin-key-feature"> Key Features: </div>
                                <ul class="bpa-feature-list-cls-plugin-dec">
                                    <li class="bpa-feature-list-li-plugin"> Real-time Pricing Table Editor </li>
                                    <li class="bpa-feature-list-li-plugin"> Unlimited Color Options </li>
                                    <li class="bpa-feature-list-li-plugin"> Create Team Showcases </li>
                                    <li class="bpa-feature-list-li-plugin"> Translation Ready </li>
                                    <li class="bpa-feature-list-li-plugin"> Responsive Pricing Tables </li>
                                    <li class="bpa-feature-list-li-plugin"> Multi-Site Compatible </li>
                                </ul>
                                <div style="margin-top:40px;">

                                    <a href="https://www.arpriceplugin.com" target="_blank"  class="bpa-learnmore-btn arp-plugin"> Learn More </a>
                                    <?php if ((is_plugin_active('arprice-responsive-pricing-table/arprice-responsive-pricing-table.php')) || file_exists( WP_PLUGIN_DIR . '/arprice-responsive-pricing-table/arprice-responsive-pricing-table.php')  ) { ?>
                                        <el-button class="el-button bpa-btn bpa-install-btn arp-plugin" disabled="is_disabled">
                                            <span class="bpa-btn__label"> Installed </span> 
                                        <?php } else {
                                            $plugin_name = 'arprice-responsive-pricing-table';
                                            $plugin_install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_name ), 'install-plugin_' . $plugin_name );

                                            //window.location.href = '<?php echo $plugin_install_url;// phpcs:ignore
                                            ?>
                                            <el-button class="el-button bpa-btn bpa-install-btn arp-plugin" @click="bpa_download_plugins('arprice')" :class="(is_display_arprice_save_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disabled">
                                            <span class="bpa-btn__label"> Install </span> 
                                        <?php }  ?>
                                        <div class="bpa-btn--loader__circles">                    
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                    </el-button>
                                </div>
                            </div>
                        </div>
					</el-col>
				</el-row>
            </div>
        </el-container>
    </div>
</el-main>
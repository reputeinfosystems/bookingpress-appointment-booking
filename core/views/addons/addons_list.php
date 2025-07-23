<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet bpa-addon-list-card" id="all-page-main-container">	
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>	
	<div id="bpa-main-container">		
		<el-container class="bpa-addons-container">
			<div class="bpa-addon-sub-list-wrapper bpa-addon-feature-list-wrapper">
				<el-row v-for="(addonsList, category) in bpa_lite_addons">
					<el-row type="flex" class="bpa-mlc-head-wrap" :class="category+'-bpa-mlc-head-wrap'">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
							<!-- <h1 class="bpa-page-heading"><?php esc_html_e( 'Add-ons', 'bookingpress-appointment-booking' ); ?></h1> -->
							<h1 class="bpa-page-heading bpa-adddons-page-heading" v-if="category == 'features'"><?php esc_html_e( 'Feature Add-ons', 'bookingpress-appointment-booking' ); ?></h1>
							<h1 class="bpa-page-heading bpa-adddons-page-heading" v-if="category == 'payment_gateways'"><?php esc_html_e( 'Payment Gateways', 'bookingpress-appointment-booking' ); ?></h1>
							<h1 class="bpa-page-heading bpa-adddons-page-heading" v-if="category == 'integrations'"><?php esc_html_e( 'Integrations', 'bookingpress-appointment-booking' ); ?></h1>
						</el-col>
					</el-row>
					<el-row :gutter="30" class="bpa-addons-items-row">
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="6" v-for="addons in addonsList" class="bpa-addons-items-col">
							<div class="bpa-addon-item" :id="addons.addon_key+'_activate_addon'">
								<span class="bpa-ai-icon" :class="addons.addon_icon_slug"></span>
								<div class="bpa-ai-name">
									<h3>{{ addons.addon_name }}</h3>
								</div>
								<div class="bpa-ai-desc">
									<p>{{ addons.addon_description }}</p>							
								</div>
								<div class="bpa-ai-btns">
									<el-row type="flex">
										<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">										
											<el-button class="bpa-btn bpa-btn--primary bpa-btn--full-width" @click="open_premium_modal()">
												<span class="bpa-btn__label"><?php esc_html_e( 'Upgrade to Pro', 'bookingpress-appointment-booking' ); ?></span>
											</el-button>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-ai-doc-link">
									<el-link :href="addons.addon_documentation" target="_blank">
										<i class="material-icons-round">description</i><?php esc_html_e( 'Read More', 'bookingpress-appointment-booking' ); ?>
									</el-link>
								</div>
							</div>
						</el-col>
					</el-row>
				</el-row>
			</div>
		</el-container>
	</div>
</el-main>

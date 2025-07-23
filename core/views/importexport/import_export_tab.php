<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body" name="migration_tool_settings" label="conversion_tracking" data-tab_name="migration_tool_settings">
	<span slot="label">		       
		<span class="bpa-set-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g clip-path="url(#clip0_625_1118)">
				<path d="M15.884 13.0882L10.5898 7.79412L15.884 2.5L17.3663 4.00882L14.6398 6.73529H23.2957V8.85294H14.6398L17.3663 11.5794L15.884 13.0882Z" fill="white" stroke="white" stroke-width="0.4"/>
				<path d="M5.92941 19.9912L7.41176 21.5L12.7059 16.2059L7.41176 10.9117L5.92941 12.4206L8.65588 15.147H0V17.2647H8.65588L5.92941 19.9912Z" fill="white" stroke="white" stroke-width="0.4"/>
				</g>
				<defs>
				<clipPath id="clip0_625_1118">
				<rect width="24" height="24" fill="white"/>
				</clipPath>
				</defs>
			</svg>            
        </span>        
		<?php esc_html_e( 'Import/Export', 'bookingpress-appointment-booking' ); ?>
	</span>
	<div class="bpa-general-settings-tabs--pb__card bpa-migration-settings-tabs--pbtab">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-mlc-head-wrap-settings-migr bpa-gs-tabs--pb__heading">		   
            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Import/Export Tool', 'bookingpress-appointment-booking' ); ?></h1>		
			</el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">									
										
				</div>
			</el-col>
		</el-row>
		<div class="bpa-gs--tabs-pb__content-body bpa-migration-settings-tabs--pb__card">
				<el-form method="post" ref="migration_tool_form" :model="migration_tool_form">
					<div class="bpa-export-sec">	
						<div class="bpa-gs__cb--item">
							<div class="bpa-gs__cb--item-heading">
								<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Export', 'bookingpress-appointment-booking' ); ?></h4>
							</div>
							<div class="bpa-gs__cb--item-body">							
								<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
									<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs__cb-item-left">
										<h4><?php esc_html_e( 'Select Export Data', 'bookingpress-appointment-booking' ); ?></h4>
									</el-col>
									<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs__cb-item-right">								  	
										<div class="bpa-migra-inner-field">
											<div :class="((continue_export_id != '' || export_complete_msg != '')?'bpa-export-inner-fields-cont':'')" class="export-inner-fields">
												<?php wp_nonce_field('bpa_wp_nonce_export'); ?>	
												<input type="hidden" name="action" value="bookingpress_migration_export">
												<div class="bpa-export-scroll-builder">
													<div class="bpa-export-scroll-header">	
														<div class="bpa-export-item">									
															<label class="bpa-form-label bpa-custom-checkbox--is-label bpa-custom-checkbox--is-label-tit"> <el-checkbox @change="bpa_select_all_export_list($event)" v-model="export_all_record"></el-checkbox> <?php echo esc_html__('Select ALL','bookingpress-appointment-booking'); ?> </label>
														</div>
													</div>
													<div class="bpa-export-scroll-body">
														<div v-if="(migration_tool_form.export_list.length != 0)" class="bpa-export-list">											

															<div v-for="(export_list,keys) in migration_tool_form.export_list" class="bpa-export-item">									
																<label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox :disabled="migaration_child_active(export_list,keys)" v-model="migration_tool_form.bookingpress_export_list_data[keys]" @change="bpa_select_export_list(export_list,$event)"></el-checkbox><span v-html="export_list.name"></span>  <span v-if="typeof export_list.total_record != 'undefined'">({{export_list.total_record}}) </span></label>
																<div v-if="(export_list.child.length != 0)" v-for="(export_list_child,child_keys) in export_list.child" class="bpa-export-item bpa-export-item-child">										
																	<label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox :disabled="migaration_child_active(export_list_child,child_keys)" v-model="migration_tool_form.bookingpress_export_list_data[child_keys]" @change="bpa_select_export_list(export_list_child,$event,keys)"></el-checkbox> <span v-html="export_list_child.name"></span> <span v-if="typeof export_list_child.total_record != 'undefined'">({{export_list_child.total_record}}) </span></label>
																</div>
															</div>								
															<!--<label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox v-model="appointment_formdata.appointment_send_notification"></el-checkbox> <?php esc_html_e( 'Do Not Send Notifications', 'bookingpress-appointment-booking' ); ?></label>-->

														</div>											
													</div>
												</div>
												<div v-if="export_log_data.length != 0 || export_complete_msg != ''" class="bpa-export-data-log">
												<div class="bpa-field-outer-container">
													<div v-if="export_complete_msg != ''" class="bpa-cfs-ic__body">
														<div class="bpa-mig-export-msg" v-html="export_complete_msg"></div>		
													</div>
													<div v-if="export_log_data.length != 0" v-for="export_log_single in export_log_data" class="bpa-cfs-ic__body">														
														<div  class="bpa-cfs-ic--head">
															<div class="bpa-cfs-ic--head__type-label">												 
																<div v-if="export_log_single.export_complete != '1' || 1 == 1" class="bpa-loader-sm-row"></div>
															</div>
															<div class="bpa-cfs-ic--head__field-controls">
																<div class="bpa-cfs-ic--head__fc-actions">													
																	<!--<span class="material-icons-round">drag_indicator</span>-->
																</div>
															</div>
														</div>
														<div  class="bpa-cfs-ic--body">
															<div v-for="export_log_single_data in export_log_single.export_detail" v-if="export_log_single_data.export_detail_record_hide == '0'" class="bpa-cfs-ic--body__field-preview">												
																<div class="export-log-row">
																	<div class="export-log-row-single"><span class="export-log-data-type">{{export_log_single_data.label}}</span> <span class="export-log-data-total">({{export_log_single_data.export_detail_total_record}}/{{export_log_single_data.export_detail_last_record}})</span></div>
																</div>
															</div>

														</div>
													</div>

												</div>												
												</div>
											</div>
											<el-button v-if="(continue_export_id == '' || continue_export_id == '0') && export_last_download_file == ''" class="bpa-btn bpa-btn--primary bpa-btn__medium bpa-btn__export_data" @click="bookingpress_export_data_task()" :class="(is_display_export_loader == '1' || continue_export_id != '') ? 'bpa-btn--is-loader' : ''" :disabled="(is_display_export_loader == '1')?true:false">                    
												<span class="bpa-btn__label"><?php esc_html_e('Export', 'bookingpress-appointment-booking'); ?></span>
												<div class="bpa-btn--loader__circles">                    
													<div></div>
													<div></div>
													<div></div>
												</div>
											</el-button>

											<el-button v-if="continue_export_id != '' && continue_export_id != '0'" class="bpa-btn bpa-btn--primary bpa-btn__medium bpa-btn--danger bpa-btn__export_data" :class="(is_display_export_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_stop_export_process()">                    
												<span class="bpa-btn__label"><?php esc_html_e('Stop Export', 'bookingpress-appointment-booking'); ?></span>
												<div class="bpa-btn--loader__circles">                    
													<div></div>
													<div></div>
													<div></div>
												</div>
											</el-button>	

											<div v-if="export_last_download_file != ''" class="export-file">																							
												<a class="el-button bpa-btn bpa-btn--primary bpa-btn__medium bpa-btn__export_data el-button--default bpa-ex-download" :href="export_last_download_file" download><?php esc_html_e( 'Download File', 'bookingpress-appointment-booking' ); ?></a>
											</div>	

										</div>	
									</el-col>
								</el-row>												
							</div>
						</div>
					</div>	
					<div class="bpa-import-sec">
						<div class="bpa-gs__cb--item">	
							<div class="bpa-gs__cb--item-heading">
								<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Import', 'bookingpress-appointment-booking' ); ?></h4>
							</div>
							<div class="bpa-gs__cb--item-body">
								<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
									<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs__cb-item-left">
										<h4><?php esc_html_e( 'Import File Data', 'bookingpress-appointment-booking' ); ?></h4>
									</el-col>
									<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs__cb-item-right">
										<div class="bpa-migra-inner-field">	
											<div :class="(continue_import_id != ''?'bpa-export-inner-fields-cont':'')" class="export-inner-fields">
												<div class="import-inner-field-box">													
													<el-input class="bpa-form-control bpa-import-txt-box" placeholder="<?php esc_html_e('Copy and paste the exported file content', 'bookingpress-appointment-booking'); ?>" type="textarea" :rows="20" v-model="migration_tool_form.import_data"></el-input>						
													<el-button @click="bookingpress_import_data_task()" :class="(is_display_import_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="(continue_import_id != '')?true:false" class="bpa-btn bpa-btn--primary bpa-btn__medium bpa-btn__export_data">                    
														<span class="bpa-btn__label"><?php esc_html_e('Import', 'bookingpress-appointment-booking'); ?></span>
														<div class="bpa-btn--loader__circles">                
															<div></div>
															<div></div>
															<div></div>
														</div>
													</el-button>
												</div>
												<div v-if="import_log_data.length != 0" class="bpa-field-outer-container bpa-export-data-log">
													<div class="bpa-field-outer-container">	
														<div v-for="export_log_single in import_log_data" class="bpa-cfs-ic__body">
															<div class="bpa-cfs-ic--head">
																<div class="bpa-cfs-ic--head__type-label">												 
																	<div v-if="export_log_single.export_complete != '1' || 1 == 1" class="bpa-loader-sm-row"></div>
																</div>
																<div class="bpa-cfs-ic--head__field-controls">
																	<div class="bpa-cfs-ic--head__fc-actions">													
																		<!--<span class="material-icons-round">drag_indicator</span>-->
																	</div>
																</div>
															</div>
															<div class="bpa-cfs-ic--body">
																<div v-for="export_log_single_data in export_log_single.import_detail" v-if="export_log_single_data.detail_import_display == '1'" class="bpa-cfs-ic--body__field-preview">												
																	<div class="export-log-row">
																		<div class="export-log-row-single"><span class="export-log-data-type">{{export_log_single_data.label}}</span> <span class="export-log-data-total">({{export_log_single_data.detail_import_total_record}}/{{export_log_single_data.detail_import_last_record}})</span></div>
																	</div>
																</div>

															</div>
														</div>
													</div>		
												</div>	
											</div>
										</div>	
									</el-col>
								</el-row>	
							</div>
						</div>	
					</div>
				</el-form>

			
		</div>
	</div>	
</el-tab-pane>
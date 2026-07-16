<div v-cloak id="bookingpress-add-break-dialog" class="bookingpress-add-break-dialog-container">
    <?php
    if (! is_rtl() ) {
        ?>
            <bp-ui-dialog id="breaks_add_modal" class="bpa-dialog bpa-dailog__small bpa-dialog--add-break" title="" v-model="open_add_break_modal" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display"  @open="bookingpress_enable_modal" :close-on-click-modal="true" @close="bookingpress_disable_modal" :append-to-body="true">
        <?php
    } else {
        ?>
            <bp-ui-dialog id="breaks_add_modal" class="bpa-dialog bpa-dailog__small bpa-dialog--add-break" title="" v-model="open_add_break_modal" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display" @open="bookingpress_enable_modal" :close-on-click-modal="true" @close="bookingpress_disable_modal" :append-to-body="true">
        <?php
    }
    ?>
        <div class="bpa-dialog-heading">
            <bp-ui-row type="flex">
                <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" v-if="is_edit_break == '0'">
                    <h1 class="bpa-page-heading"><?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?></h1>
                </bp-ui-col>
                <bp-ui-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" v-else>
                    <h1 class="bpa-page-heading"><?php esc_html_e( 'Edit Break', 'bookingpress-appointment-booking' ); ?></h1>
                </bp-ui-col>			
            </bp-ui-row>
        </div>
        <div class="bpa-dialog-body">
            <bp-ui-container class="bpa-grid-list-container bpa-add-categpry-container">
                <div class="bpa-form-row">
                    <bp-ui-row>
                        <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <bp-ui-form :rules="rules_add_break" ref="break_timings" :model="break_timings" labbp-ui-position="top" @submit.native.prevent>
                                <div class="bpa-form-body-row">
                                    <bp-ui-row :gutter="24">
                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                            <bp-ui-form-item prop="start_time">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('Start Time', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-select v-model="break_timings.start_time" class="bpa-form-control bpa-form-control__left-icon" filterable placeholder="<?php esc_html_e('Start Time', 'bookingpress-appointment-booking'); ?>">
                                                    <template #prefix>
                                                        <span class="material-icons-round">access_time</span>
                                                    </template>
                                                    <bp-ui-option v-for="break_times in filtered_break_start_timings(break_selected_day)" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time"></bp-ui-option>
                                                </bp-ui-select>                                                
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                        <bp-ui-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                            <bp-ui-form-item prop="end_time">
                                                <template #label>
                                                    <span class="bpa-form-label"><?php esc_html_e('End Time', 'bookingpress-appointment-booking'); ?></span>
                                                </template>
                                                <bp-ui-select v-model="break_timings.end_time" class="bpa-form-control bpa-form-control__left-icon" filterable placeholder="<?php esc_html_e('End Time', 'bookingpress-appointment-booking'); ?>">
                                                    <template #prefix>
                                                        <span class="material-icons-round">access_time</span>
                                                    </template>
                                                    <bp-ui-option v-for="break_times in filtered_break_end_timings(break_selected_day)" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time"></bp-ui-option>
                                                </bp-ui-select>
                                            </bp-ui-form-item>
                                        </bp-ui-col>
                                    </bp-ui-row>
                                </div>
                            </bp-ui-form>
                        </bp-ui-col>
                    </bp-ui-row>
                </div>
            </bp-ui-container>
        </div>
        <div class="bpa-dialog-footer">
            <div class="bpa-hw-right-btn-group">
                <bp-ui-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="savebreakdata"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></bp-ui-button>
                <bp-ui-button class="bpa-btn bpa-btn__small" @click="close_add_break_model()"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></bp-ui-button>
            </div>
        </div>
    </bp-ui-dialog>
</div>
<bp-ui-dialog class="bpa-dialog bpa-dialog--upgrade-to-premium bpa-dialog--black-friday-sale" :modal="true" modal-append-to-body=false v-model="premium_modal" :close-on-press-escape="close_modal_on_esc" :close-on-click-modal="true" v-cloak>
    <div class="bpa-dialog-heading" @click="premium_modal = false;"></div>
    <div class="bpa-dialog-body" @click="bookingpress_redirect_sale_premium_page">    
    </div>
</bp-ui-dialog>

<bp-ui-dialog class="bpa-dialog bpa-dialog--upgrade-to-premium" :close-on-click-modal="true" :modal="true" modal-append-to-body=false v-model="bookingpress_old_premium_modal" :close-on-press-escape="close_modal_on_esc" v-cloak>
    <div class="bpa-dialog-heading">
        <div class="bpa-dialog-utp__head-wrap">
            <h3><?php esc_html_e('Unlock the Powerful Pro Features', 'bookingpress-appointment-booking'); ?></h3>
        </div>
    </div>
    <div class="bpa-dialog-body">
        <div class="bpa-utp__body-item-wrap">
            <h4><?php esc_html_e('Scale your appointment scheduling business', 'bookingpress-appointment-booking'); ?></h4>
            <p><?php esc_html_e('Simplify the booking experiences for your customers, automate employee', 'bookingpress-appointment-booking'); ?></p>
            <p style="line-height: 0 !important; margin-top: -20px !important;"><?php esc_html_e('management, and grow your business with even more features.', 'bookingpress-appointment-booking'); ?></p>
            <div class="bpa-utp__key-features">
                <h5><?php esc_html_e('Amazing Features', 'bookingpress-appointment-booking'); ?></h5>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Award Winning Design', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('60+ Premium add-ons totally free', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('20+ Payment gateways completely free', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Effective employee scheduling', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Smooth two-way sync of bookings across calendars', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Understand your business better with reports', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
                <div class="bpa-kf__item-row">
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('Email, Whatsapp, SMS & Telegram Notification', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                    <div class="bpa-kf__item">
                        <div class="bpa-kf__item-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.8917 30.4181C8.21163 29.3987 0.891315 21.5862 1.91062 13.9061C2.92992 6.22596 9.98218 0.826308 17.6623 1.84561C25.3424 2.86491 30.7421 9.91718 29.7228 17.5973C28.7034 25.2774 23.5719 31.4374 15.8917 30.4181Z" fill="#12D488"/>
                                <g clip-path="url(#clip0_3659_12149)">
                                    <path d="M13.8842 19.1L11.095 16.3108C10.7815 15.9974 10.2752 15.9974 9.96167 16.3108C9.64819 16.6243 9.64819 17.1307 9.96167 17.4442L13.3215 20.8041C13.635 21.1175 14.1414 21.1175 14.4549 20.8041L22.9591 12.2999C23.2726 11.9864 23.2726 11.48 22.9591 11.1665C22.6456 10.853 22.1392 10.853 21.8257 11.1665L13.8842 19.1Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_3659_12149">
                                        <rect width="19.2911" height="19.2911" fill="white" transform="translate(6.65039 6.10261)"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="bpa-kf__item-title"><?php esc_html_e('24/7 Real-time Support', 'bookingpress-appointment-booking'); ?></div>
                    </div>
                </div>
            </div> 
            <div class="bpa-utp-comparison-btns">
                <h4><?php esc_html_e('Check out our complete comparison', 'bookingpress-appointment-booking'); ?></h4>
                <div class="boa-cb__wrap">
                    <bp-ui-link href="https://www.bookingpressplugin.com/bookingpress-lite-vs-premium" :underline="false" target="_blank" class="bpa-btn bpa-btn__medium bpa_lite_pro_link"><?php esc_html_e('Lite vs Premium', 'bookingpress-appointment-booking'); ?></bp-ui-link>
                </div>
            </div>
        </div>
    </div>
    <div class="bpa-dialog-footer">
        <bp-ui-button class="bpa-btn bpa-btn--primary" @click="bookingpress_redirect_premium_page">
            <?php esc_html_e('Upgrade to BookingPress Pro Now', 'bookingpress-appointment-booking'); ?>
        </bp-ui-button>
    </div>
</bp-ui-dialog>
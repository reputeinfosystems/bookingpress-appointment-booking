<?php
    if ( ! defined( 'ABSPATH' ) ) { exit; }
    $requested_module = ( ! empty($_REQUEST['page']) && ( $_REQUEST['page'] != 'bookingpress' ) ) ? sanitize_text_field(str_replace('bookingpress_', '', $_REQUEST['page'])) : 'dashboard'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason - $_REQUEST['page'] sanitized properly

    if( isset($requested_module) && 'growth_tools' != $requested_module  ){
        $bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/bookingpress_header.php';
        $bookingpress_load_file_name = apply_filters('bookingpress_modify_header_content', $bookingpress_load_file_name,1);
        require $bookingpress_load_file_name;

    }
    do_action( 'bookingpress_page_admin_notices' );
    
    echo '<div class="bookingpress_page_inner_wrapper" v-cloak>';
        do_action('bookingpress_' . $requested_module . '_dynamic_view_load');
    echo '</div>';
?>
<div class="bpa-fab-component">
    <div class="bpa-fc__active-box" :class="bpa_fab_floating_btn == 1 ? '__bpa-is-active' : ''">
        <div class="bpa-fc__item">
            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                <div slot="content">
                    <span><?php esc_html_e( 'Need Help?', 'bookingpress-appointment-booking' ); ?></span>
                </div>
                <el-button class="bpa-btn bpa-btn--icon-without-box" @click="open_need_help_url()">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_5488_17507)">
                            <path d="M13.9987 2.33301C7.5587 2.33301 2.33203 7.55967 2.33203 13.9997C2.33203 20.4397 7.5587 25.6663 13.9987 25.6663C20.4387 25.6663 25.6654 20.4397 25.6654 13.9997C25.6654 7.55967 20.4387 2.33301 13.9987 2.33301ZM15.1654 22.1663H12.832V19.833H15.1654V22.1663ZM17.5804 13.1247L16.5304 14.198C15.947 14.793 15.527 15.3297 15.317 16.1697C15.2237 16.543 15.1654 16.963 15.1654 17.4997H12.832V16.9163C12.832 16.3797 12.9254 15.8663 13.0887 15.388C13.322 14.7113 13.707 14.1047 14.197 13.6147L15.6437 12.1447C16.1804 11.6313 16.437 10.8613 16.2854 10.0447C16.1337 9.20467 15.4804 8.49301 14.6637 8.25967C13.3687 7.89801 12.167 8.63301 11.782 9.74134C11.642 10.173 11.2804 10.4997 10.8254 10.4997H10.4754C9.7987 10.4997 9.33203 9.84634 9.5187 9.19301C10.0204 7.47801 11.4787 6.17134 13.287 5.89134C15.0604 5.61134 16.752 6.53301 17.802 7.99134C19.1787 9.89301 18.7704 11.9347 17.5804 13.1247V13.1247Z" fill="white"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_5488_17507">
                                <rect width="28" height="28" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </el-button>
            </el-tooltip>
        </div>
        <div class="bpa-fc__item">
            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                <div slot="content">
                    <span><?php esc_html_e( 'Feature Requests', 'bookingpress-appointment-booking' ); ?></span>
                </div>
                <el-button class="bpa-btn bpa-btn--icon-without-box" @click="open_feature_request_url">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_5488_17506)">
                        <path d="M10.4987 24.4997C10.4987 25.083 10.9654 25.6663 11.6654 25.6663H16.332C17.032 25.6663 17.4987 25.083 17.4987 24.4997V23.333H10.4987V24.4997ZM13.9987 2.33301C9.4487 2.33301 5.83203 5.94967 5.83203 10.4997C5.83203 13.2997 7.23203 15.7497 9.33203 17.1497V19.833C9.33203 20.4163 9.7987 20.9997 10.4987 20.9997H17.4987C18.1987 20.9997 18.6654 20.4163 18.6654 19.833V17.1497C20.7654 15.633 22.1654 13.183 22.1654 10.4997C22.1654 5.94967 18.5487 2.33301 13.9987 2.33301Z" fill="white"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_5488_17506">
                                <rect width="28" height="28" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </el-button>
            </el-tooltip>
        </div>
        <div class="bpa-fc__item">
            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                <div slot="content">
                    <span><?php esc_html_e( 'Facebook Community', 'bookingpress-appointment-booking' ); ?></span>
                </div>
                <el-button class="bpa-btn bpa-btn--icon-without-box" @click="open_facebook_community_url">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.00706 18.8219C5.71367 18.7798 5.40178 18.7562 5.07012 18.7562C2.62732 18.7562 1.26305 20.0452 0.740045 20.6874C0.655217 20.7978 0.609936 20.933 0.609936 21.0727C0.609936 21.0765 0.609931 21.0803 0.609293 21.0835C0.605467 21.3195 0.605469 21.632 0.605469 21.9452C0.605469 22.2973 0.891207 22.583 1.24328 22.583H5.81763C5.74556 22.3802 5.70793 22.1646 5.70793 21.9452C5.70793 21.2194 5.70793 20.3545 5.71048 19.8436C5.71048 19.4801 5.81444 19.1261 6.00706 18.8219ZM20.3775 22.583H7.62136C7.45234 22.583 7.2897 22.516 7.17043 22.3961C7.05052 22.2769 6.98355 22.1142 6.98355 21.9452C6.98355 21.2213 6.98355 20.3602 6.9861 19.8474C6.9861 19.8462 6.9861 19.8449 6.9861 19.8436C6.9861 19.6969 7.03713 19.5553 7.12961 19.4418C7.92113 18.538 10.3129 16.2049 13.9994 16.2049C18.172 16.2049 20.2672 18.5336 20.9082 19.4118C20.979 19.5196 21.0153 19.6414 21.0153 19.7658V21.9452C21.0153 22.1142 20.9484 22.2769 20.8284 22.3961C20.7092 22.516 20.5465 22.583 20.3775 22.583ZM22.1812 22.583H26.7556C27.1077 22.583 27.3934 22.2973 27.3934 21.9452V21.072C27.3934 20.9311 27.3468 20.7946 27.2614 20.683C26.7358 20.0452 25.3709 18.7562 22.9287 18.7562C22.6143 18.7562 22.3177 18.7772 22.0384 18.8155C22.2036 19.1044 22.2909 19.4322 22.2909 19.7671V21.9452C22.2909 22.1646 22.2533 22.3802 22.1812 22.583ZM5.07012 11.1025C3.30977 11.1025 1.88108 12.5312 1.88108 14.2915C1.88108 16.0519 3.30977 17.4805 5.07012 17.4805C6.83047 17.4805 8.25916 16.0519 8.25916 14.2915C8.25916 12.5312 6.83047 11.1025 5.07012 11.1025ZM22.9287 11.1025C21.1684 11.1025 19.7397 12.5312 19.7397 14.2915C19.7397 16.0519 21.1684 17.4805 22.9287 17.4805C24.6891 17.4805 26.1178 16.0519 26.1178 14.2915C26.1178 12.5312 24.6891 11.1025 22.9287 11.1025ZM13.9994 6C11.5356 6 9.53478 8.0008 9.53478 10.4647C9.53478 12.9285 11.5356 14.9293 13.9994 14.9293C16.4633 14.9293 18.4641 12.9285 18.4641 10.4647C18.4641 8.0008 16.4633 6 13.9994 6Z" fill="white"/>
                    </svg>
                </el-button>
            </el-tooltip>
        </div>
        <div class="bpa-fc__item">
            <el-tooltip effect="dark" content="" placement="top" open-delay="300">
                <div slot="content">
                    <span><?php esc_html_e( 'YouTube Channel', 'bookingpress-appointment-booking' ); ?></span>
                </div>
                <el-button class="bpa-btn bpa-btn--icon-without-box" @click="open_youtube_channel_url">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M24.8115 8.31591C24.1704 7.17545 23.4746 6.96568 22.0579 6.88591C20.6426 6.78989 17.0839 6.75 14.0008 6.75C10.9118 6.75 7.35162 6.78989 5.93787 6.88443C4.52412 6.96568 3.82685 7.17398 3.1798 8.31591C2.51946 9.45489 2.17969 11.4167 2.17969 14.8706V14.8824C2.17969 18.3215 2.51946 20.2981 3.1798 21.4252C3.82685 22.5657 4.52264 22.7725 5.93639 22.8685C7.35162 22.9513 10.9118 23 14.0008 23C17.0839 23 20.6426 22.9512 22.0593 22.87C23.4761 22.774 24.1718 22.5672 24.813 21.4267C25.4792 20.2995 25.8161 18.323 25.8161 14.8839V14.872C25.8161 11.4167 25.4792 9.45489 24.8115 8.31591Z" fill="white"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.043 19.307V10.4434L18.4293 14.8752L11.043 19.307Z" fill="#125CD4"/>
                    </svg>
                </el-button>
            </el-tooltip>
        </div>
        <div class="bpa-ab__close-icon" @click="bpa_fab_floating_close_btn">
            <el-button class="bpa-btn bpa-btn--icon-without-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_5488_17508)">
                        <path d="M18.2987 5.70973C17.9087 5.31973 17.2787 5.31973 16.8887 5.70973L11.9988 10.5897L7.10875 5.69973C6.71875 5.30973 6.08875 5.30973 5.69875 5.69973C5.30875 6.08973 5.30875 6.71973 5.69875 7.10973L10.5888 11.9997L5.69875 16.8897C5.30875 17.2797 5.30875 17.9097 5.69875 18.2997C6.08875 18.6897 6.71875 18.6897 7.10875 18.2997L11.9988 13.4097L16.8887 18.2997C17.2787 18.6897 17.9087 18.6897 18.2987 18.2997C18.6887 17.9097 18.6887 17.2797 18.2987 16.8897L13.4087 11.9997L18.2987 7.10973C18.6787 6.72973 18.6787 6.08973 18.2987 5.70973V5.70973Z" fill="white"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_5488_17508">
                            <rect width="24" height="24" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
            </el-button>
        </div>
    </div>
    <div class="bpa-fc__inactive-box">
        <div class="bpa-fc__item">
            <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpa_fab_floating_action_btn">
                <svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_1129_9706)">
                        <path d="M5.73704 28.3466C6.17083 28.7805 6.61584 29.1807 7.0869 29.5524C7.18697 29.6313 7.33225 29.6216 7.42238 29.5315L12.7922 24.1617C12.9048 24.049 12.8819 23.867 12.7476 23.7812C12.2661 23.4737 11.8115 23.1104 11.3924 22.6913C10.9867 22.2856 10.6364 21.8446 10.333 21.3854C10.2459 21.2537 10.0652 21.2332 9.95356 21.3449L4.5905 26.708C4.49915 26.7993 4.48994 26.9472 4.57085 27.0479C4.933 27.4984 5.31845 27.9281 5.73704 28.3466Z" fill="white" />
                        <path d="M24.8157 31.0143C24.9613 30.933 24.9881 30.73 24.8702 30.612L19.1297 24.8715C19.0699 24.8117 18.9842 24.7886 18.9019 24.8082C17.6578 25.104 16.3679 25.1039 15.1299 24.8021C15.0474 24.7819 14.9611 24.8049 14.901 24.865L9.16771 30.5983C9.04988 30.7161 9.07642 30.9189 9.22183 31.0004C14.0512 33.7054 19.9798 33.7121 24.8157 31.0143Z" fill="white" />
                        <path d="M26.9858 29.5728C27.464 29.1943 27.916 28.7871 28.3568 28.3463C28.7684 27.9347 29.147 27.5121 29.5023 27.0688C29.583 26.9681 29.5736 26.8204 29.4824 26.7291L24.1213 21.3679C24.009 21.2556 23.8278 21.2781 23.7414 21.4114C23.4475 21.8651 23.0994 22.2929 22.7015 22.691C22.2757 23.1167 21.8142 23.4866 21.3215 23.7961C21.186 23.8813 21.1618 24.0639 21.275 24.177L26.65 29.5521C26.7402 29.6423 26.8857 29.652 26.9858 29.5728Z" fill="white" />
                        <path d="M3.10188 24.9037C3.18359 25.0485 3.38593 25.0748 3.50351 24.9571L9.22957 19.231C9.29017 19.1705 9.31299 19.0835 9.29212 19.0004C8.97677 17.7474 8.9642 16.4306 9.26594 15.1653C9.28553 15.0831 9.26252 14.9975 9.20279 14.9378L3.46208 9.197C3.34419 9.07911 3.14123 9.10577 3.05991 9.2513C0.348413 14.1024 0.368668 20.0593 3.10188 24.9037Z" fill="white" />
                        <path d="M15.0332 9.29377C16.3347 8.95951 17.7055 8.95927 19.0009 9.28736C19.0839 9.30842 19.1707 9.28565 19.2312 9.22506L24.9573 3.49899C25.0749 3.38134 25.0487 3.17894 24.9038 3.09717C20.023 0.343497 14.0099 0.350445 9.14237 3.11798C8.99801 3.20006 8.9722 3.40197 9.0896 3.51937L14.8019 9.23178C14.8627 9.29249 14.95 9.3152 15.0332 9.29377Z" fill="white" />
                        <path d="M23.7821 12.734C23.8679 12.8683 24.0499 12.8913 24.1626 12.7785L29.5324 7.40878C29.6225 7.31865 29.6322 7.17337 29.5533 7.07329C28.8609 6.19571 27.9869 5.31136 27.0488 4.55716C26.9482 4.47625 26.8002 4.48552 26.7089 4.5768L21.3458 9.93993C21.2342 10.0516 21.2547 10.2323 21.3864 10.3194C22.3527 10.9578 23.1564 11.754 23.7821 12.734Z" fill="white" />
                        <path d="M31.0058 9.22284C30.9243 9.07742 30.7215 9.05082 30.6036 9.16871L24.8704 14.9019C24.8103 14.962 24.7875 15.0482 24.8078 15.1307C25.1223 16.4111 25.1096 17.755 24.7813 19.0354C24.7599 19.1187 24.7826 19.2061 24.8435 19.267L30.5555 24.979C30.673 25.0964 30.8749 25.0706 30.9569 24.9261C33.7108 20.0736 33.7313 14.0886 31.0058 9.22284Z" fill="white" />
                        <path d="M6.99514 4.58039C6.06369 5.32684 5.18716 6.21748 4.49123 7.0969C4.41203 7.19697 4.42161 7.34245 4.51186 7.43269L9.88688 12.8078C10.0001 12.921 10.1826 12.8968 10.2678 12.7613C10.8696 11.8034 11.716 10.9482 12.6524 10.3413C12.7858 10.2549 12.8083 10.0738 12.6959 9.96146L7.33483 4.60029C7.24362 4.50906 7.09583 4.49966 6.99514 4.58039Z" fill="white" />
                    </g>
                    <defs>
                        <clipPath id="clip0_1129_9706">
                            <rect width="34" height="34.001" fill="white" transform="translate(0.460938 0.460938)" />
                        </clipPath>
                    </defs>
                </svg>
            </el-button>
        </div>
    </div>    
</div>
<el-drawer custom-class="bpa-help-drawer" :visible.sync="needHelpDrawer" :direction="needHelpDrawerDirection" v-cloak>
    <el-container>
        <div class="bpa-back-loader-container" v-if="is_display_drawer_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <div class="bpa-hd-header">
            <h1 class="bpa-page-heading">{{ requested_module }}</h1>
            <el-link :href="read_more_link" :underline="false" target="_blank" class="bpa-btn bpa-btn__small"><?php esc_html_e('Read more', 'bookingpress-appointment-booking'); ?></el-link>
        </div>
        <div class="bpa-hd-body bp_new_single_content" v-html="helpDrawerData"></div>
    </el-container>    
</el-drawer>
<el-drawer custom-class="bpa-help-drawer" :visible.sync="needHelpDrawer_add" :direction="add_needHelpDrawerDirection" v-cloak>
    <el-container>
        <div class="bpa-back-loader-container" v-if="is_display_drawer_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <div class="bpa-hd-header">
            <h1 class="bpa-page-heading">{{ requested_module }}</h1>
            <el-link :href="read_more_link" :underline="false" target="_blank" class="bpa-btn bpa-btn__small"><?php esc_html_e('Read more', 'bookingpress-appointment-booking'); ?></el-link>
        </div>
        <div class="bpa-hd-body bp_new_single_content" v-html="helpDrawerData"></div>
    </el-container>    
</el-drawer>

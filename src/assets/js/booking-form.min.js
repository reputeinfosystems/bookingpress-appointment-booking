/**
 * BookingPress — Vue 3 frontend booking form (entry module).
 *
 * Script module handle: `bookingpress-form-vue3`
 * Declared dependencies (via wp_register_script_module): `vue`, `bookingpress-ui`.
 *
 * Step 3C adds the real `service` tab (category pills + service grid),
 * selection methods, preselection-on-mount, a minimal tab-swap
 * `bookingpress_step_navigation()`, and a no-services placeholder.
 * Other tabs remain empty shells (Step 3D/3E/3F).
 *
 * Usage (from loader):
 *   import { mountBookingFormInstance } from 'bookingpress-form-vue3';
 *   mountBookingFormInstance(instanceState);
 */

"use strict";

import { createApp, reactive, ref, computed, provide, onMounted, nextTick, watch } from 'vue';
// Bare specifiers — resolved via WordPress's script-module import map.
// Using relative paths (e.g. `./bookingpress-ui.min.js`) would fetch a
// SECOND copy without the `?ver=` query WP registered the module with,
// defeating the import map and causing duplicate network requests.
import BookingPressUI from 'bookingpress-ui';
// Side-effect import: this populates `window.BpVCalendar` (DatePicker
// component + createDatePickerApp factory) before any Vue code runs.
// WordPress script-module deps only pre-resolve the URL in the import
// map — the bundle only actually executes when something imports it.
import 'bookingpress-vcalendar';
// Legacy-parity SVG assets for the Date & Time step (animated loader +
// "no time slots available" illustration). Imported from a sibling module
// to keep the large SVG literals out of this file's diff. The markup is
// copied verbatim from core/views/frontend/appointment_booking_form.php
// so the existing `.bpa-front-loader` / `.bpa-front__no-timeslots-body`
// CSS in bookingpress_front.css keeps working without any change.
import { LOADER_SVG, NO_SLOTS_SVG } from './bp-datetime-svg.js';

// `bookingpress-ui` is declared as a WP script-module dependency of
// `bookingpress-form-vue3`, so it has self-registered on window by the
// time this module evaluates. Matches src/assets/js/calendar-loader.js.
//const BookingPressUI = window.BookingPressUI;

const ROOT_ID_PREFIX = 'bookingpress-form-vue3-';

const TABS = Object.freeze(['service', 'datetime', 'basic_details', 'summary']);

/**
 * Legacy parity (appointment_booking_form.php:1022-1103):
 * "No categories and services added" empty-state illustration.
 * Rendered via `v-html` so Vue's template compiler doesn't need to
 * walk the deeply-nested SVG tree (large inline SVGs inside a
 * template literal have historically caused the compiler to
 * produce unexpected output when a single attribute is parsed
 * ambiguously — using v-html sidesteps the entire concern and
 * matches how NO_SLOTS_SVG / LOADER_SVG are injected elsewhere).
 * Filter ids are namespaced (`bpa_vue3_empty_filter*`) so they do
 * not collide with the no-time-slots illustration on the datetime
 * panel if both were ever to render on the same page.
 */
const EMPTY_VIEW_SVG = `<svg viewBox="0 0 120 121" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M108.486 103.08C101.042 112.139 86.7296 109.719 75.3575 112.572C64.3105 115.344 53.4388 123.192 42.6284 119.606C31.8342 116.026 27.8283 103.242 20.6455 94.4249C13.5882 85.7617 2.04617 79.3615 0.797986 68.2575C-0.448903 57.1649 8.61128 47.9531 14.3452 38.376C19.5416 29.6967 24.6347 21.093 32.6953 14.9808C41.3289 8.43396 51.0768 2.35675 61.9118 2.30667C72.8285 2.25621 82.086 9.1904 91.5052 14.709C101.484 20.5552 114.441 24.5839 118.451 35.4317C122.456 46.2671 113.129 57.2263 111.445 68.6549C109.732 80.2849 115.949 93.9976 108.486 103.08Z" class="bpa-front-dev__panel-bg"/>
    <g filter="url(#bpa_vue3_empty_filter0)">
        <rect x="16.3105" y="27.8936" width="95.3718" height="22.2173" rx="11.1086" class="bpa-front-dev__form-bg"/>
    </g>
    <circle cx="27.1474" cy="39.0009" r="5.41885" class="bpa-front-dev__primary-bg"/>
    <rect x="37.9863" y="39.542" width="41.1833" height="2.16754" rx="1.08377" fill="#F4F7FB"/>
    <rect x="37.9863" y="36.0215" width="13.5471" height="2.16754" rx="1.08377" fill="#F4F7FB"/>
    <rect x="53.4297" y="36.0215" width="25.7395" height="2.16754" rx="1.08377" fill="#F4F7FB"/>
    <rect x="84.5859" y="34.9375" width="21.6754" height="8.12828" rx="4" fill="#F4F7FB"/>
    <g filter="url(#bpa_vue3_empty_filter1)">
        <rect x="16.3105" y="54.1748" width="95.3718" height="22.2173" rx="11.1086" class="bpa-front-dev__form-bg"/>
    </g>
    <circle cx="27.1474" cy="65.2831" r="5.41885" fill="#E8ECF5"/>
    <rect x="37.9863" y="65.8252" width="41.1833" height="2.16754" rx="1.08377" fill="#E8ECF5"/>
    <rect x="37.9863" y="62.3037" width="13.5471" height="2.16754" rx="1.08377" fill="#DDE1ED"/>
    <rect x="53.4297" y="62.3037" width="25.7395" height="2.16754" rx="1.08377" fill="#E8ECF5"/>
    <rect x="84.5859" y="61.2197" width="21.6754" height="8.12828" rx="4" fill="#F4F7FB"/>
    <g filter="url(#bpa_vue3_empty_filter2)">
        <rect x="16.3105" y="80.4541" width="95.3718" height="22.2173" rx="11.1086" class="bpa-front-dev__form-bg"/>
    </g>
    <circle cx="27.1474" cy="91.5644" r="5.41885" fill="#E8ECF5"/>
    <rect x="37.9863" y="92.1064" width="41.1833" height="2.16754" rx="1.08377" fill="#E8ECF5"/>
    <rect x="37.9863" y="88.582" width="13.5471" height="2.16754" rx="1.08377" fill="#DDE1ED"/>
    <rect x="53.4297" y="88.582" width="25.7395" height="2.16754" rx="1.08377" fill="#E8ECF5"/>
    <rect x="84.5859" y="87.499" width="21.6754" height="8.12828" rx="4" class="bpa-front-dev__primary-bg"/>
    <path d="M10.6699 62.6393C11.3924 62.6393 11.6694 61.9455 11.7176 61.5986C11.7176 62.3164 12.4642 62.6058 12.8375 62.6537C11.9704 62.6537 11.7296 63.3953 11.7176 63.7662C11.7176 62.9623 11.0191 62.6752 10.6699 62.6393Z" stroke="#F4B125" stroke-opacity="0.6" stroke-linejoin="round"/>
    <line x1="11.4707" y1="60.4463" x2="11.4707" y2="60.3625" stroke="#F4B125" stroke-opacity="0.6" stroke-linecap="round"/>
    <line x1="11.4707" y1="65.8652" x2="11.4707" y2="65.1312" stroke="#F4B125" stroke-opacity="0.6" stroke-linecap="round"/>
    <path d="M13.4863 62.709H14.7869" stroke="#F4B125" stroke-opacity="0.6" stroke-linecap="round"/>
    <path d="M8.7207 62.709H9.53353" stroke="#F4B125" stroke-opacity="0.6" stroke-linecap="round"/>
    <path d="M10.3483 40.076L10.35 40.0813H10.3556L10.3511 40.0846L10.3528 40.0898L10.3483 40.0866L10.3438 40.0898L10.3455 40.0846L10.3411 40.0813H10.3466L10.3483 40.076Z" class="bpa-front-dev__primary-bg"/>
    <path d="M117.915 48.4764L117.916 48.4817H117.922L117.917 48.485L117.919 48.4902L117.915 48.487L117.91 48.4902L117.912 48.485L117.907 48.4817H117.913L117.915 48.4764Z" class="bpa-front-dev__primary-bg"/>
    <path d="M84.5866 111.606L84.5883 111.612H84.5938L84.5894 111.615L84.5911 111.62L84.5866 111.617L84.5821 111.62L84.5838 111.615L84.5793 111.612H84.5849L84.5866 111.606Z" stroke="#F5AE41"/>
    <circle cx="56.1379" cy="1.88181" r="0.854713" stroke="#EE2445" stroke-opacity="0.7"/>
    <circle cx="111.681" cy="79.0998" r="0.854713" stroke="#EE2445" stroke-opacity="0.6"/>
    <circle cx="2.76292" cy="79.0993" r="0.854713" stroke="#EE2445" stroke-opacity="0.6"/>
    <circle cx="69.9579" cy="15.9723" r="0.541885" fill="#2166F1"/>
    <line x1="43.9062" y1="16.5115" x2="43.9062" y2="20.0337" stroke="#01CB62" stroke-opacity="0.3"/>
    <line x1="45.3027" y1="18.6365" x2="41.7805" y2="18.6365" stroke="#01CB62" stroke-opacity="0.3"/>
    <line x1="21.3262" y1="105.778" x2="61.9479" y2="105.778" stroke="#DCE4F5" stroke-width="3" stroke-linecap="round"/>
    <line x1="69.0176" y1="105.778" x2="87.9639" y2="105.778" stroke="#DCE4F5" stroke-width="3" stroke-linecap="round"/>
    <line x1="95.8379" y1="105.778" x2="114.784" y2="105.778" stroke="#DCE4F5" stroke-width="3" stroke-linecap="round"/>
    <path d="M92.9902 15.9169C93.8934 15.9169 94.2396 15.0496 94.2998 14.616C94.2998 15.5131 95.233 15.875 95.6997 15.9348C94.6159 15.9348 94.3148 16.8619 94.2998 17.3254C94.2998 16.3206 93.4268 15.9617 92.9902 15.9169Z" stroke="#F4B125" stroke-linejoin="round"/>
    <line x1="94.1113" y1="13.3025" x2="94.1113" y2="12.9478" stroke="#F4B125" stroke-linecap="round"/>
    <line x1="94.1113" y1="20.0769" x2="94.1113" y2="18.9094" stroke="#F4B125" stroke-linecap="round"/>
    <path d="M96.5098 16.0056H98.1354" stroke="#F4B125" stroke-linecap="round"/>
    <path d="M90.5488 16.0056H91.5649" stroke="#F4B125" stroke-linecap="round"/>
    <defs>
        <filter id="bpa_vue3_empty_filter0" x="8.31055" y="21.8936" width="111.372" height="38.2173" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="2"/>
            <feGaussianBlur stdDeviation="4"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.129412 0 0 0 0 0.403922 0 0 0 0 0.945098 0 0 0 0.1 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>
        </filter>
        <filter id="bpa_vue3_empty_filter1" x="8.31055" y="48.1748" width="111.372" height="38.2173" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="2"/>
            <feGaussianBlur stdDeviation="4"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.129412 0 0 0 0 0.403922 0 0 0 0 0.945098 0 0 0 0.1 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>
        </filter>
        <filter id="bpa_vue3_empty_filter2" x="8.31055" y="74.4541" width="111.372" height="38.2173" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="2"/>
            <feGaussianBlur stdDeviation="4"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.129412 0 0 0 0 0.403922 0 0 0 0 0.945098 0 0 0 0.1 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>
        </filter>
    </defs>
</svg>`;

/**
 * Summary-panel decorative illustration (appointment_booking_form.php
 * L804-L833). Copied byte-for-byte so the CSS rules keyed on
 * `.bpa-head__vector` / `.bpa-head__vector-item` style the paths
 * without any additional declarations. Rendered via `v-html` to skip
 * the Vue 3 template compiler (same approach as EMPTY_VIEW_SVG above).
 */
const SUMMARY_HEAD_SVG = `<svg width="137" height="99" viewBox="0 0 137 99" fill="none" xmlns="http://www.w3.org/2000/svg" class="bpa-head__vector" aria-hidden="true">
    <path d="M15.8625 62.0651H97.6116C98.7623 62.0661 99.8656 62.5237 100.679 63.3374C101.493 64.1511 101.951 65.2543 101.952 66.4051V79.6535C101.951 80.8042 101.493 81.9075 100.679 82.7212C99.8656 83.5349 98.7623 83.9924 97.6116 83.9935H15.8625C14.7117 83.9924 13.6085 83.5349 12.7948 82.7212C11.9811 81.9075 11.5235 80.8042 11.5225 79.6535V66.4051C11.5235 65.2543 11.9811 64.1511 12.7948 63.3374C13.6085 62.5237 14.7117 62.0661 15.8625 62.0651Z" fill="#E9EDF5"/>
    <path d="M15.7854 30.7217H97.5345C98.6852 30.7227 99.7885 31.1803 100.602 31.994C101.416 32.8077 101.873 33.911 101.875 35.0617V48.3101C101.873 49.4608 101.416 50.5641 100.602 51.3778C99.7885 52.1915 98.6852 52.6491 97.5345 52.6501H15.7854C14.6346 52.6491 13.5313 52.1915 12.7177 51.3778C11.904 50.5641 11.4464 49.4608 11.4454 48.3101V35.0617C11.4464 33.911 11.904 32.8077 12.7177 31.994C13.5313 31.1803 14.6346 30.7227 15.7854 30.7217ZM11.9022 48.3101C11.9037 49.3395 12.3133 50.3264 13.0412 51.0543C13.7691 51.7822 14.7559 52.1918 15.7854 52.1933H97.5345C98.5639 52.1918 99.5508 51.7822 100.279 51.0543C101.007 50.3264 101.416 49.3395 101.418 48.3101V35.0617C101.416 34.0322 101.007 33.0454 100.279 32.3175C99.5508 31.5896 98.5639 31.18 97.5345 31.1785H15.7854C14.7559 31.18 13.7691 31.5896 13.0412 32.3175C12.3133 33.0454 11.9037 34.0322 11.9022 35.0617V48.3101Z" fill="#535D71"/>
    <path d="M26.9395 47.8533H66.2023C66.687 47.8533 67.1517 47.6608 67.4944 47.3181C67.8371 46.9754 68.0297 46.5106 68.0297 46.026C68.0297 45.5413 67.8371 45.0765 67.4944 44.7338C67.1517 44.3911 66.687 44.1986 66.2023 44.1986H26.9395C26.4548 44.1986 25.99 44.3911 25.6473 44.7338C25.3046 45.0765 25.1121 45.5413 25.1121 46.026C25.1121 46.5106 25.3046 46.9754 25.6473 47.3181C25.99 47.6608 26.4548 47.8533 26.9395 47.8533Z" fill="#E9EDF5"/>
    <path d="M53.2079 40.0871H66.2023C66.687 40.0871 67.1517 39.8946 67.4944 39.5519C67.8371 39.2092 68.0297 38.7444 68.0297 38.2597C68.0297 37.7751 67.8371 37.3103 67.4944 36.9676C67.1517 36.6249 66.687 36.4324 66.2023 36.4324H53.2079C52.7232 36.4324 52.2584 36.6249 51.9157 36.9676C51.573 37.3103 51.3805 37.7751 51.3805 38.2597C51.3805 38.7444 51.573 39.2092 51.9157 39.5519C52.2584 39.8946 52.7232 40.0871 53.2079 40.0871Z" fill="#E9EDF5"/>
    <path class="bpa-head__vector-item" d="M80.8983 49.24C82.3923 49.24 83.8527 48.797 85.095 47.9669C86.3372 47.1369 87.3054 45.9572 87.8771 44.5769C88.4489 43.1966 88.5985 41.6778 88.307 40.2125C88.0155 38.7472 87.2961 37.4012 86.2397 36.3448C85.1833 35.2884 83.8373 34.5689 82.372 34.2775C80.9067 33.986 79.3879 34.1356 78.0076 34.7073C76.6274 35.2791 75.4476 36.2472 74.6176 37.4895C73.7876 38.7317 73.3445 40.1921 73.3445 41.6861C73.3468 43.6888 74.1434 45.6088 75.5595 47.025C76.9756 48.4411 78.8956 49.2377 80.8983 49.24Z"/>
    <path d="M79.7164 44.3433C79.529 44.3436 79.3466 44.283 79.1966 44.1705L79.1874 44.1636L77.2295 42.6659C77.1386 42.5964 77.0623 42.5098 77.005 42.4108C76.9476 42.3118 76.9103 42.2025 76.8953 42.0892C76.8802 41.9758 76.8877 41.8606 76.9172 41.7501C76.9467 41.6396 76.9977 41.536 77.0673 41.4452C77.1369 41.3544 77.2237 41.2783 77.3227 41.2211C77.4218 41.1639 77.5311 41.1268 77.6445 41.1119C77.7579 41.097 77.8731 41.1046 77.9836 41.1343C78.094 41.164 78.1976 41.2152 78.2882 41.2849L79.5564 42.2574L82.553 38.3479C82.6225 38.2572 82.7092 38.1812 82.8081 38.124C82.9069 38.0669 83.0161 38.0298 83.1293 38.0148C83.2426 37.9999 83.3576 38.0073 83.468 38.0369C83.5783 38.0664 83.6817 38.1173 83.7724 38.1868L83.7726 38.187L83.754 38.2128L83.7731 38.187C83.956 38.3276 84.0756 38.5349 84.1058 38.7635C84.1359 38.9922 84.0742 39.2234 83.934 39.4066L80.4092 44.003C80.3277 44.1089 80.2229 44.1946 80.1028 44.2534C79.9828 44.3122 79.8509 44.3427 79.7172 44.3423L79.7164 44.3433Z" fill="white"/>
    <path d="M50.9109 66.3674H132.66C133.811 66.3685 134.914 66.8261 135.728 67.6397C136.541 68.4534 136.999 69.5567 137 70.7074V83.9558C136.999 85.1066 136.541 86.2099 135.728 87.0235C134.914 87.8372 133.811 88.2948 132.66 88.2958H50.9109C49.7601 88.2948 48.6568 87.8372 47.8432 87.0235C47.0295 86.2099 46.5719 85.1066 46.5709 83.9558V70.7074C46.5719 69.5567 47.0295 68.4534 47.8432 67.6397C48.6568 66.8261 49.7601 66.3685 50.9109 66.3674Z" fill="white"/>
    <path d="M50.9109 66.3674H132.66C133.811 66.3685 134.914 66.8261 135.728 67.6397C136.541 68.4534 136.999 69.5567 137 70.7074V83.9558C136.999 85.1066 136.541 86.2099 135.728 87.0235C134.914 87.8372 133.811 88.2948 132.66 88.2958H50.9109C49.7601 88.2948 48.6568 87.8372 47.8432 87.0235C47.0295 86.2099 46.5719 85.1066 46.5709 83.9558V70.7074C46.5719 69.5567 47.0295 68.4534 47.8432 67.6397C48.6568 66.8261 49.7601 66.3685 50.9109 66.3674ZM47.0277 83.9558C47.0292 84.9853 47.4388 85.9721 48.1667 86.7001C48.8946 87.428 49.8814 87.8375 50.9109 87.839H132.66C133.689 87.8375 134.676 87.428 135.404 86.7001C136.132 85.9721 136.542 84.9853 136.543 83.9558V70.7074C136.542 69.678 136.132 68.6911 135.404 67.9632C134.676 67.2353 133.689 66.8257 132.66 66.8243H50.9109C49.8814 66.8257 48.8946 67.2353 48.1667 67.9632C47.4388 68.6911 47.0292 69.678 47.0277 70.7074V83.9558Z" fill="#535D71"/>
    <path d="M62.0646 83.4989H101.327C101.812 83.4989 102.277 83.3063 102.62 82.9636C102.962 82.6209 103.155 82.1561 103.155 81.6715C103.155 81.1868 102.962 80.722 102.62 80.3793C102.277 80.0366 101.812 79.8441 101.327 79.8441H62.0646C61.58 79.8441 61.1152 80.0366 60.7725 80.3793C60.4298 80.722 60.2373 81.1868 60.2373 81.6715C60.2373 82.1561 60.4298 82.6209 60.7725 82.9636C61.1152 83.3063 61.58 83.4989 62.0646 83.4989Z" fill="#E9EDF5"/>
    <path d="M88.3324 75.7326H101.327C101.567 75.7326 101.804 75.6854 102.026 75.5935C102.248 75.5017 102.449 75.3671 102.619 75.1974C102.789 75.0277 102.923 74.8263 103.015 74.6046C103.107 74.3829 103.154 74.1452 103.154 73.9052C103.154 73.6653 103.107 73.4277 103.015 73.2059C102.923 72.9842 102.789 72.7828 102.619 72.6131C102.449 72.4434 102.248 72.3088 102.026 72.217C101.804 72.1252 101.567 72.0779 101.327 72.0779H88.3324C87.8478 72.0779 87.383 72.2704 87.0403 72.6131C86.6976 72.9558 86.5051 73.4206 86.5051 73.9052C86.5051 74.3899 86.6976 74.8547 87.0403 75.1974C87.383 75.5401 87.8478 75.7326 88.3324 75.7326Z" fill="#E9EDF5"/>
    <path class="bpa-head__vector-item" d="M116.024 84.8853C117.518 84.8854 118.978 84.4423 120.22 83.6123C121.463 82.7823 122.431 81.6026 123.002 80.2223C123.574 78.842 123.724 77.3232 123.432 75.8579C123.141 74.3926 122.421 73.0466 121.365 71.9902C120.309 70.9338 118.963 70.2143 117.497 69.9229C116.032 69.6314 114.513 69.781 113.133 70.3527C111.753 70.9244 110.573 71.8926 109.743 73.1348C108.913 74.3771 108.47 75.8375 108.47 77.3315C108.472 79.3342 109.269 81.2542 110.685 82.6704C112.101 84.0865 114.021 84.8831 116.024 84.8853Z"/>
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
    <path class="bpa-head__vector-item" d="M27.5531 18.3085C27.4963 18.3086 27.4409 18.2902 27.3954 18.256L27.3926 18.2539L26.7984 17.7994C26.7709 17.7783 26.7478 17.752 26.7304 17.722C26.7131 17.6919 26.7018 17.6588 26.6973 17.6244C26.6927 17.5901 26.695 17.5551 26.704 17.5216C26.7129 17.4881 26.7284 17.4567 26.7495 17.4292C26.7706 17.4017 26.7969 17.3786 26.8269 17.3612C26.857 17.3439 26.8901 17.3326 26.9245 17.3281C26.9589 17.3235 26.9938 17.3258 27.0273 17.3348C27.0608 17.3437 27.0922 17.3592 27.1197 17.3803L27.5045 17.6754L28.414 16.489C28.4351 16.4615 28.4614 16.4384 28.4914 16.421C28.5214 16.4037 28.5545 16.3924 28.5889 16.3879C28.6232 16.3834 28.6582 16.3856 28.6916 16.3946C28.7251 16.4035 28.7565 16.419 28.784 16.4401L28.7841 16.4402L28.7784 16.448L28.7842 16.4402C28.8397 16.4828 28.876 16.5457 28.8852 16.6151C28.8943 16.6845 28.8756 16.7547 28.8331 16.8103L27.7634 18.2052C27.7386 18.2373 27.7068 18.2633 27.6704 18.2812C27.634 18.299 27.5939 18.3082 27.5534 18.3081L27.5531 18.3085Z"/>
    <path d="M25.5268 30.6751C25.1983 30.5362 24.8433 30.471 24.4869 30.4841C24.1305 30.4972 23.7812 30.5882 23.4638 30.7509C23.1464 30.9136 22.8685 31.1438 22.6497 31.4255C22.4309 31.7072 22.2765 32.0334 22.1974 32.3812L16.7021 33.3226L16.4597 36.8016L24.2014 35.3632C24.7945 35.4573 25.4015 35.3306 25.9074 35.0071C26.4133 34.6836 26.783 34.1857 26.9464 33.6079C27.1098 33.03 27.0555 32.4123 26.7938 31.8718C26.5322 31.3312 26.0814 30.9055 25.5268 30.6751Z" fill="#FFB8B8"/>
    <path d="M16.0873 20.6895C16.8836 21.0473 17.5116 21.6978 17.8411 22.5061C18.1706 23.3144 18.1764 24.2186 17.8572 25.031L14.6718 33.139L18.6708 32.404C18.9205 32.3581 19.1784 32.4051 19.3959 32.5361C19.6134 32.667 19.7755 32.873 19.8517 33.1152L20.6489 35.6486C20.6905 35.781 20.7053 35.9203 20.6923 36.0585C20.6793 36.1966 20.6388 36.3308 20.5732 36.453C20.5076 36.5753 20.4182 36.6832 20.3103 36.7705C20.2024 36.8577 20.0781 36.9224 19.9448 36.9609L14.7471 38.4617C14.0896 38.6515 13.397 38.6864 12.7238 38.5635C12.0505 38.4407 11.4149 38.1635 10.8668 37.7536C10.3188 37.3438 9.87314 36.8125 9.56495 36.2015C9.25676 35.5905 9.09439 34.9163 9.09058 34.232C9.0888 33.9122 9.12167 33.5931 9.18861 33.2804L11.3726 23.0775C11.4787 22.582 11.6942 22.1166 12.0034 21.7152C12.3127 21.3138 12.7077 20.9867 13.1597 20.7577C13.6118 20.5288 14.1093 20.4038 14.6158 20.392C15.1224 20.3802 15.6251 20.4819 16.0873 20.6895Z" fill="#CFD6E6"/>
</svg>`;

/**
 * Deep-clone-shaped copy of the PHP-emitted `instance.state` into a
 * fresh mutable object so `reactive()` owns every nested key.
 */
function hydrateState(raw) {
    const src = (raw && typeof raw === 'object') ? raw : {};
    return {
        appointment_step_form_data:     { ...(src.appointment_step_form_data || {}) },
        bookingpress_all_services_data: src.bookingpress_all_services_data || {},
        bookingpress_all_categories:    Array.isArray(src.bookingpress_all_categories)
                                          ? src.bookingpress_all_categories.slice()
                                          : [],
        service_categories:             Array.isArray(src.service_categories)
                                          ? src.service_categories.slice()
                                          : [],
        bookingpress_sidebar_step_data: src.bookingpress_sidebar_step_data || {},
        bookingpress_current_tab:       src.bookingpress_current_tab || 'service',
        // Step 3E — Basic Details.
        // `customer_form_fields` is the canonical array (matches the legacy
        // PHP emit and the v-for in the legacy template). The server also
        // emits `customer_form_fields_data` as a temporary back-compat
        // alias; we mirror both into state so any Pro/addon code-path that
        // still reads the old slot continues to function. Both arrays point
        // at the same descriptors — they are NOT separately mutable.
        customer_form_fields:           Array.isArray(src.customer_form_fields)
                                          ? src.customer_form_fields.slice()
                                          : (Array.isArray(src.customer_form_fields_data) ? src.customer_form_fields_data.slice() : []),
        customer_form_fields_data:      Array.isArray(src.customer_form_fields_data)
                                          ? src.customer_form_fields_data.slice()
                                          : (Array.isArray(src.customer_form_fields) ? src.customer_form_fields.slice() : []),
        customer_details_rule:          (src.customer_details_rule && typeof src.customer_details_rule === 'object')
                                          ? src.customer_details_rule
                                          : {},
        // Lite hard-wires this to 0 (non-logged-in fallback). Pro uses 1
        // when a logged-in user's username should be locked.
        check_bookingpress_username_set: (src.check_bookingpress_username_set != null) ? src.check_bookingpress_username_set : 0,
        // Flipped `true` once BpUiForm.validate() has succeeded at least
        // once on the basic_details step. Consumed by the existing
        // summary-tab gate in `bookingpress_step_navigation`.
        is_basic_details_validated:     !!src.is_basic_details_validated,
        v_calendar_available_dates:     Array.isArray(src.v_calendar_available_dates)
                                          ? src.v_calendar_available_dates.slice()
                                          : [],
        v_calendar_available_only_date: [],
        v_calendar_blocked_dates:       Array.isArray(src.v_calendar_blocked_dates)
                                          ? src.v_calendar_blocked_dates.slice()
                                          : [],
        v_calendar_disable_dates:       [],
        v_calendar_check_month_dates:   false,
        v_calendar_next_month_dates:    '',
        isHoldBookingRequest:           false,
        days_off_disabled_dates:        '',
        // Legacy v2-flow state (bookingpress_disable_date_xhr_v2 +
        // bookingpress_retrieve_future_month_details).
        v_calendar_timeslots_data:      (src.v_calendar_timeslots_data && typeof src.v_calendar_timeslots_data === 'object') ? { ...src.v_calendar_timeslots_data } : {},
        v_calendar_time_token_data:     (src.v_calendar_time_token_data && typeof src.v_calendar_time_token_data === 'object') ? { ...src.v_calendar_time_token_data } : {},
        v_calendar_attributes:          Array.isArray(src.v_calendar_attributes) ? src.v_calendar_attributes.slice() : [],
        v_calendar_attributes_current:  (src.v_calendar_attributes_current && typeof src.v_calendar_attributes_current === 'object') ? { ...src.v_calendar_attributes_current } : {},
        v_calendar_default_label:       src.v_calendar_default_label || '',
        isLoadTimeLoader:               src.isLoadTimeLoader || '0',
        isLoadDateTimeCalendarLoad:     src.isLoadDateTimeCalendarLoad || '0',
        no_timeslot_available:          !!src.no_timeslot_available,
        bpa_current_selected_date:      src.bpa_current_selected_date || '',
        // Mobile-only toggle: '0' = time-slots view (back-button shows
        // selected date); '1' = calendar replaces the time-slots. Desktop
        // CSS ignores this state and always shows both columns. Default
        // must be '0' so phone users land on slots + date-trigger button —
        // matches legacy `.__sm` module flow (default '0' in
        // class.bookingpress_appointment_bookings.php:11579).
        displayResponsiveCalendar:      src.displayResponsiveCalendar != null ? src.displayResponsiveCalendar : '0',
        current_screen_size:            src.current_screen_size || 'desktop',
        // Legacy time-bucket thresholds (bookingpress_categories_timeslots).
        bpa_afternoon_slots_timing:     (src.bpa_afternoon_slots_timing != null) ? src.bpa_afternoon_slots_timing : 12,
        bpa_evening_slots_timing:       (src.bpa_evening_slots_timing   != null) ? src.bpa_evening_slots_timing   : 17,
        bpa_night_slots_timing:         (src.bpa_night_slots_timing     != null) ? src.bpa_night_slots_timing     : 21,
        service_timing: {
            morning_time:   [],
            afternoon_time: [],
            evening_time:   [],
            night_time:     [],
            ...(src.service_timing || {}),
        },
        is_booking_form_empty_loader:                src.is_booking_form_empty_loader || '0',
        hide_category_selection:                     !!src.hide_category_selection,
        hide_category_service:                       src.hide_category_service || '0',
        is_service_loaded_from_url:                  src.is_service_loaded_from_url || '0',
        bookingpress_display_no_service_placeholder: !!src.bookingpress_display_no_service_placeholder,
        display_service_description:                 src.display_service_description || '0',
        is_display_error:                            src.is_display_error || '0',
        is_error_msg:                                src.is_error_msg || '',
        strings:                                     src.strings || {},
        bpa_front_date_format:                       src.bpa_front_date_format || 'Y-m-d',
        bpa_front_date_time_format:                  src.bpa_front_date_time_format || 'Y-m-d H:i',
        bookingpress_site_date:                      src.bookingpress_site_date || '',
        booking_cal_maxdate:                         src.booking_cal_maxdate || '',
        first_day_of_week:                           Number(src.first_day_of_week) || 1,
        site_locale:                                 src.site_locale || 'en',
        is_timeslot_loading:                         src.is_timeslot_loading || '0',
        is_date_loading:                             src.is_date_loading || '0',
        // Outer `#bpa-front-tabs` orientation modifier. Legacy uses
        // `bpa-front-tabs--left` when the customize setting is 'left',
        // else the default `--bpa-top` layout.
        bookingpress_tabs_position:                  src.bookingpress_tabs_position || '',
        bookingpress_container_dynamic_class:        src.bookingpress_container_dynamic_class || '',
        // Legacy re-paint flag used during category filtering (legacy line 8252/8273).
        isLoadClass:                                 1,
        // Step 3F — Summary tab. Flat top-level keys mirror legacy shape so
        // addons that read/write them via the root model keep working.
        on_site_payment:                             (src.on_site_payment != null) ? src.on_site_payment : '',
        paypal_payment:                              (src.paypal_payment  != null) ? src.paypal_payment  : '',
        is_only_onsite_enabled:                      (src.is_only_onsite_enabled != null) ? src.is_only_onsite_enabled : '0',
        // PayPal config surfaced to the Summary step. `paypal_payment_method_type`
        // is the gating signal — when equal to 'popup', selecting PayPal
        // activates the SDK button path (legacy L710). `paypal_client_id`
        // is public-safe (it already appears in the SDK URL).
        paypal_payment_method_type:                  src.paypal_payment_method_type || '',
        paypal_client_id:                            src.paypal_client_id || '',
        // PayPal SDK UI flags — initial values mirror legacy (L6973-6977).
        paypal_button_loader:                        src.paypal_button_loader || 'false',
        paypal_success_url:                          src.paypal_success_url  || '',
        paypal_cancel_url:                           src.paypal_cancel_url   || '',
        paypal_booking_form_redirection_mode:        src.paypal_booking_form_redirection_mode || '',
        isLoadBookingLoader:                         src.isLoadBookingLoader || '0',
        isBookingDisabled:                           !!src.isBookingDisabled,
        // Legacy gate (template L1005): default submit renders when this is
        // 'false'. Initial value is 'false' (matches legacy L6973) so the
        // default button shows on first render; flipped to 'true' by
        // `select_payment_method` when PayPal is selected with popup mode,
        // which hides the default submit and reveals the SDK container.
        show_paypal_popup_button:                    src.show_paypal_popup_button || 'false',
        // Mount point for gateway redirect HTML (PayPal). Never written in
        // Lite; preserved as a wire-compat seam for Pro's `variant: redirect`
        // response path.
        bookingpress_external_html:                  src.bookingpress_external_html || '',
        bookingpress_total_amount_text:              src.bookingpress_total_amount_text || '',
        bookingpress_book_appointment_btn_text:      src.bookingpress_book_appointment_btn_text || '',
        summary_step_note:                           src.summary_step_note || '',
    };
}

export function createBookingFormApp(instance) {
    const RootComponent = {
        name: 'BookingPressFormVue3Root',

        setup() {
            const state = reactive(hydrateState(instance && instance.state));

            // ---- Tab state machine ----
            const currentTab = computed({
                get: () => state.bookingpress_current_tab,
                set: (value) => {
                    if (TABS.includes(value)) {
                        state.bookingpress_current_tab = value;
                    }
                },
            });
            const isTab = (name) => state.bookingpress_current_tab === name;

            // ---- Computed: guarantee a leading "All" pill ----
            // Legacy `bookingpress_retrieve_all_categories` usually prepends a
            // category_id=0 "All" entry, but in some tenant data it is absent.
            // Prepending here is idempotent and keeps the template simple.
            const categoriesWithAll = computed(() => {
                const cats = Array.isArray(state.bookingpress_all_categories)
                    ? state.bookingpress_all_categories.slice()
                    : [];
                const hasAll = cats.some((c) => parseInt(c && c.category_id, 10) === 0);
                if (!hasAll) {
                    const allLabel = (state.strings && state.strings.all_category_text) || 'All';
                    cats.unshift({
                        category_id:    0,
                        category_name:  allLabel,
                        total_services: 0,
                        is_visible:     true,
                    });
                }
                return cats;
            });

            // ---- Computed: sorted services (legacy `bpasortedServices`) ----
            const bpasortedServices = computed(() => {
                const out = [];
                const src = state.bookingpress_all_services_data || {};
                for (const k in src) {
                    if (Object.prototype.hasOwnProperty.call(src, k)) {
                        out.push(src[k]);
                    }
                }
                // Stable numeric compare — returns 0 for equal positions.
                return out.sort((a, b) => {
                    const ap = parseInt(a && a.bookingpress_service_position, 10) || 0;
                    const bpos = parseInt(b && b.bookingpress_service_position, 10) || 0;
                    return ap - bpos;
                });
            });

            // ---- Computed: selected service details lookup ----
            const selectedServiceDetails = computed(() => {
                const id = state.appointment_step_form_data.selected_service;
                if (!id) return null;
                const src = state.bookingpress_all_services_data || {};
                return src[String(id)] || src[id] || null;
            });

            // ---- Helpers (formatting stubs — real formatting in Step 3D/3F) ----
            const formatDate  = (v) => (v == null ? '' : String(v));
            const formatTime  = (v) => (v == null ? '' : String(v));
            // Prices are pre-formatted server-side by bookingpress_price_formatter_with_currency_symbol().
            const formatPrice = (v) => (v == null ? '' : String(v));

            /**
             * Issue 8 — format a "YYYY-MM-DD" date using the format token
             * in `state.bpa_front_date_format`. The server emits a mixed
             * token vocabulary (VCalendar v3 / date-fns + moment.js):
             *   - MMMM d, yyyy   →  April 27, 2026   (default)
             *   - yyyy-MM-dd     →  2026-04-27
             *   - MM/dd/yyyy     →  04/27/2026
             *   - dd/MM/yyyy     →  27/04/2026
             *   - dd.MM.yyyy     →  27.04.2026
             *   - dd-MM-yyyy     →  27-04-2026
             * Some legacy paths also emit moment-style uppercase tokens
             * (`YYYY`, `DD`, `D`); we accept either casing so the summary
             * never renders strange characters. Falls back to the raw
             * input on parse failure so the summary never renders empty.
             */
            function formatBookedDate(rawValue) {
                const raw = (rawValue == null) ? '' : String(rawValue).trim();
                if (!raw) return '';
                const ymd = raw.slice(0, 10);
                const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd);
                if (!m) return raw;
                const yyyy = m[1];
                const mm   = m[2];
                const dd   = m[3];
                const dateObj = new Date(Number(yyyy), Number(mm) - 1, Number(dd));
                if (isNaN(dateObj.getTime())) return raw;
                const fmt = String(state.bpa_front_date_format || 'MMMM d, yyyy');

                // Locale-aware long month name. Mirrors the legacy
                // `moment(...).locale(site_locale).format('MMMM')` behaviour.
                const rawLocale = state.site_locale || 'en';
                const locale    = String(rawLocale).replace(/_/g, '-');
                let monthLong = '';
                try {
                    monthLong = new Intl.DateTimeFormat(locale, { month: 'long' }).format(dateObj);
                } catch (_) {
                    try { monthLong = new Intl.DateTimeFormat('en', { month: 'long' }).format(dateObj); }
                    catch (__) { monthLong = String(Number(mm)); }
                }
                const dayShort   = String(Number(dd));    // unpadded day
                const monthShort = String(Number(mm));   // unpadded month

                // Single-pass token replacement. Longer tokens come first
                // so `MMMM` matches before `MM`/`M`, and `yyyy` before
                // `MM`/etc. Both upper- and lower-case variants resolve to
                // the same value because the server may emit either.
                const tokenMap = {
                    'MMMM': monthLong,
                    'YYYY': yyyy,
                    'yyyy': yyyy,
                    'MM':   mm,
                    'DD':   dd,
                    'dd':   dd,
                    'M':    monthShort,
                    'D':    dayShort,
                    'd':    dayShort,
                };
                return fmt.replace(
                    /MMMM|YYYY|yyyy|MM|DD|dd|M|D|d/g,
                    function (token) {
                        return Object.prototype.hasOwnProperty.call(tokenMap, token)
                            ? tokenMap[token]
                            : token;
                    }
                );
            }

            /* ---- Mobile date trigger (legacy .__sm back-btn) ------------------
             * Label shown inside `.bpa-front--dt__ts-sm-back-btn`. Legacy uses
             * the Vue 2 filter `| bookingpress_format_date` which calls the
             * plugin's PHP-format-aware formatter. In Vue 3 we render a
             * human-readable "Month DD, YYYY" string via Intl so the visual
             * matches the screenshot (`April 23, 2026`) without pulling in a
             * PHP-format parser. Falls back to the raw `YYYY-MM-DD` string
             * if Intl is unavailable or the date is invalid. */
            const selectedDateLabel = computed(() => {
                const raw = state.appointment_step_form_data.selected_date || '';
                if (!raw) return '';
                // Accept "YYYY-MM-DD" or "YYYY-MM-DD HH:mm:ss".
                const ymd = String(raw).slice(0, 10);
                const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd);
                if (!m) return String(raw);
                const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
                if (isNaN(d.getTime())) return String(raw);
                // WordPress `determine_locale()` returns `en_US`-style tags
                // (underscore separator). `Intl.DateTimeFormat` expects BCP 47
                // (`en-US`, hyphen separator) and throws `RangeError` on the
                // underscore form in strict browsers, which kicks us into the
                // ISO-string fallback below and displays `2026-04-24` on the
                // mobile date pill instead of `April 24, 2026`. Normalise
                // before passing to Intl.
                const rawLocale = state.site_locale || 'en';
                const locale    = String(rawLocale).replace(/_/g, '-');
                try {
                    return new Intl.DateTimeFormat(locale, {
                        year: 'numeric', month: 'long', day: 'numeric',
                    }).format(d);
                } catch (_) {
                    // Last-ditch fallback: try plain English rather than
                    // ISO — still preserves the human-readable intent.
                    try {
                        return new Intl.DateTimeFormat('en', {
                            year: 'numeric', month: 'long', day: 'numeric',
                        }).format(d);
                    } catch (__) {
                        return ymd;
                    }
                }
            });

            /* Legacy `displayCalendar()` (class.bookingpress_appointment_bookings
             * .php:8918) — flips the mobile view to calendar. Desktop never
             * invokes this because the button is hidden by CSS @media.
             *
             * The legacy guard (`selected_date empty || available_dates empty`)
             * was dropped: on Vue3, `fetchAvailableDates` is fired with
             * `is_preselect=false` and an empty initial `selected_date`, so
             * the available-dates array briefly starts empty after mount.
             * Legacy Vue2 primed `selected_date` via `is_preselect=true` so
             * the guard never actually blocked; in Vue3 the guard prevented
             * the mobile date pill from ever opening the calendar. Opening
             * the calendar always-on-tap matches user intent and the Vue2
             * runtime behaviour observed via Playwright. */
            function openResponsiveCalendar() {
                state.displayResponsiveCalendar = '1';
            }

            // ---- Methods ----

            /**
             * Category selection — legacy parity (line 8249).
             * Pro-only staff branch intentionally omitted in Step 3C.
             */
            function bpa_select_category(selected_cat_id /*, selected_cat_name, total_services, total_category */) {
                const selected_cat_name = arguments[1] || '';
                const catId             = parseInt(selected_cat_id, 10) || 0;

                state.isLoadClass = 0;

                const services = state.bookingpress_all_services_data || {};
                if (!selected_cat_id || 0 === catId) {
                    for (const sid in services) {
                        if (Object.prototype.hasOwnProperty.call(services, sid)) {
                            services[sid].is_visible = true;
                        }
                    }
                } else {
                    for (const sid in services) {
                        if (Object.prototype.hasOwnProperty.call(services, sid)) {
                            const svc    = services[sid];
                            const svcCat = parseInt(svc && svc.bookingpress_category_id, 10);
                            svc.is_visible = (svcCat === catId);
                        }
                    }
                }

                // Re-paint flag — legacy uses setTimeout(..., 1) to force DOM update.
                nextTick(() => { state.isLoadClass = 1; });

                state.appointment_step_form_data.selected_category = (selected_cat_id != null) ? String(selected_cat_id) : '';
                state.appointment_step_form_data.selected_cat_name = selected_cat_name;
            }

            /**
             * Service selection — legacy parity (line 8337).
             * Only sets the state fields needed for continuity. Date-clearing
             * + real availability fetch are owned by Step 3D.
             */
            function selectService(selected_service_id, service_name, service_price, service_price_without_currency, is_move_to_next, service_duration_val, service_duration_unit) {
                if (selected_service_id == null || selected_service_id === '') return;

                const sid      = String(selected_service_id);
                const prevSid  = String(state.appointment_step_form_data.selected_service || '');
                const services = state.bookingpress_all_services_data || {};
                const svc      = services[sid] || services[selected_service_id] || {};

                state.appointment_step_form_data.selected_service               = sid;
                state.appointment_step_form_data.selected_service_name          = service_name || svc.bookingpress_service_name || '';
                state.appointment_step_form_data.selected_service_price         = service_price || svc.bookingpress_service_price || '';
                state.appointment_step_form_data.service_price_without_currency =
                    (service_price_without_currency != null && service_price_without_currency !== '')
                        ? service_price_without_currency
                        : (svc.service_price_without_currency != null ? svc.service_price_without_currency : '');
                state.appointment_step_form_data.base_price_without_currency =
                    state.appointment_step_form_data.service_price_without_currency;

                state.appointment_step_form_data.selected_service_duration =
                    (service_duration_val != null && service_duration_val !== '')
                        ? service_duration_val
                        : (svc.bookingpress_service_duration_val || '');
                state.appointment_step_form_data.selected_service_duration_unit =
                    (service_duration_unit != null && service_duration_unit !== '')
                        ? service_duration_unit
                        : (svc.bookingpress_service_duration_unit || '');

                // Legacy side effect: when service changes, clear any date +
                // time already picked and refetch availability. A sentinel
                // `-2` on service_timing signals "loading" so the UI can
                // render the time-slot skeleton while /time is in flight.
                if (prevSid !== sid) {
                    state.appointment_step_form_data.selected_date                    = '';
                    state.appointment_step_form_data.selected_start_time              = '';
                    state.appointment_step_form_data.selected_end_time                = '';
                    state.appointment_step_form_data.selected_formatted_start_time    = '';
                    state.appointment_step_form_data.selected_formatted_end_time      = '';
                    state.appointment_step_form_data.selected_formatted_start_end_time = '';
                    state.appointment_step_form_data.selected_formatted_booked_date   = '';
                    state.service_timing.morning_time   = [];
                    state.service_timing.afternoon_time = [];
                    state.service_timing.evening_time   = [];
                    state.service_timing.night_time     = [];
                }
                fetchAvailableDates(sid);

                if ('true' === is_move_to_next || is_move_to_next === true) {
                    const step = state.bookingpress_sidebar_step_data[state.bookingpress_current_tab];
                    if (step && step.next_tab_name) {
                        bookingpress_step_navigation(step.next_tab_name, step.next_tab_name, step.previous_tab_name);
                    }
                }

                // Legacy `is_allow_navigate` opens the next step once a service is picked.
                const nextStep = state.bookingpress_sidebar_step_data.datetime;
                if (nextStep) {
                    nextStep.is_allow_navigate = 1;
                }
            }

            // Legacy alias — addons / inline hooks may reference this name.
            const selectDate = selectService;

            // ---- REST helpers (Step 3D) -----------------------------------
            const restBase  = (instance && instance.restUrl)   || '';
            const restNonce = (instance && instance.restNonce) || '';
            const ajaxUrl   = (instance && instance.ajaxUrl)   || ((typeof window !== 'undefined' && window.ajaxurl) ? window.ajaxurl : '');
            const wpNonce   = (instance && instance.nonce)     || '';

            /**
             * Port of legacy `generateSpamCaptcha` / `loadSpamProtection`
             * (class.bookingpress_appointment_bookings.php L6088-6126).
             * Fires `bookingpress_generate_spam_captcha` once on mount to
             * populate `appointment_step_form_data.spam_captcha`. The
             * captcha value is later echoed back by the booking submit
             * handler — without it, the server-side spam check on
             * `bookingpress_front_save_appointment_booking` rejects the
             * booking. Vue3 previously skipped this call entirely.
             */
            function generateSpamCaptcha() {
                if (!ajaxUrl) return Promise.resolve(null);
                // Prefer a live `#_wpnonce` input if one is present on the
                // page (legacy behaviour) — otherwise fall back to the
                // module nonce. Matches legacy resolution at L6090-L6098.
                let nonceVal = wpNonce;
                try {
                    const el = (typeof document !== 'undefined')
                        ? document.getElementById('_wpnonce')
                        : null;
                    if (el && el.value) nonceVal = el.value;
                } catch (_) { /* noop */ }

                const body = new URLSearchParams();
                body.set('action',   'bookingpress_generate_spam_captcha');
                body.set('_wpnonce', nonceVal);

                return fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                })
                    .then((r) => r.json())
                    .then((json) => {
                        const data = (json && json.data) ? json.data : (json || {});
                        if (json && json.variant !== 'error' && data && data.captcha_val) {
                            state.appointment_step_form_data.spam_captcha = data.captcha_val;
                        } else if (data && data.updated_nonce) {
                            // Legacy parity: refresh the on-page nonce input
                            // when the server rotates it (L6105-L6109).
                            try {
                                const el = document.getElementById('_wpnonce');
                                if (el) el.value = data.updated_nonce;
                            } catch (_) { /* noop */ }
                        }
                        return data;
                    })
                    .catch((err) => {
                        try { console.log(err); } catch (_) { /* noop */ }
                        return null;
                    });
            }

            function loadSpamProtection() {
                return generateSpamCaptcha();
            }

            function toIsoYmd(v) {
                if (!v) return '';
                if (typeof v === 'string') {
                    const m = v.match(/^(\d{4})-(\d{2})-(\d{2})/);
                    if (m) return `${m[1]}-${m[2]}-${m[3]}`;
                    const d = new Date(v);
                    if (!isNaN(d.getTime())) {
                        const yr = d.getFullYear();
                        const mo = String(d.getMonth() + 1).padStart(2, '0');
                        const da = String(d.getDate()).padStart(2, '0');
                        return `${yr}-${mo}-${da}`;
                    }
                    return '';
                }
                if (v instanceof Date) {
                    const yr = v.getFullYear();
                    const mo = String(v.getMonth() + 1).padStart(2, '0');
                    const da = String(v.getDate()).padStart(2, '0');
                    return `${yr}-${mo}-${da}`;
                }
                return '';
            }

            /**
             * Merge a comma-separated `days_off_disabled_dates_string` into
             * `state.v_calendar_disable_dates` (legacy parity — line 9817/9979).
             */
            function mergeBlockedDatesString(csv) {
                if (!csv) return;
                const parts = String(csv).split(',');
                const list = Array.isArray(state.v_calendar_disable_dates)
                    ? state.v_calendar_disable_dates.slice()
                    : [];
                for (let i = 0; i < parts.length; i++) {
                    const s = parts[i];
                    if (typeof s !== 'string' || !s) continue;
                    if (list.indexOf(s) < 0) list.push(s);
                }
                state.v_calendar_disable_dates  = list;
                state.v_calendar_blocked_dates  = list
                    .map((s) => (typeof s === 'string' ? s.slice(0, 10) : ''))
                    .filter(Boolean);
            }

            // ===========================================================
            //  Step 3D — Legacy v2 date/time flow (admin-ajax parity).
            // ===========================================================
            //  Mirrors the legacy methods at
            //  class.bookingpress_appointment_bookings.php:
            //    - bookingpress_disable_date_xhr_v2            (line 9308)
            //    - bookingpress_retrieve_future_month_details  (line 9521)
            //    - bookingpress_retrieve_future_month_details_single (line 9619)
            //    - bookingpress_working_dates_data             (line 9062)
            //    - bookingpress_categories_timeslots           (line 9704)
            //    - dayClicked                                  (line 8498)
            //    - bpaMoveMonth                                (line 10069)
            //
            //  Lite default has `$bookingpress_use_legacy_functions = 'false'`,
            //  which dispatches to `bookingpress_disable_date_xhr_v2` — i.e.
            //  admin-ajax action `bookingpress_fetch_timeslot_data` for the
            //  initial month, then `bookingpress_retrieve_entire_month_details`
            //  for each of the next 3 months. `bookingpress_get_whole_day_appointments`
            //  is a Pro/day-duration-only path and is NOT used here.
            //  ===========================================================

            /**
             * Lightweight fallback for `bookingpress_format_time`. The server
             * already populates `formatted_start_time` / `formatted_end_time`
             * from its settings-aware formatter, so this is only used when a
             * raw store_start_time slips through.
             */
            function bookingpressFormatTime(value) {
                return value == null ? '' : String(value);
            }

            /**
             * Port of `bookingpress_working_dates_data` (legacy line 9062).
             * Walks the server-emitted `working_details` map and produces:
             *   - available_dates              ("YYYY-MM-DD 00:00:00" strings)
             *   - updated_working_hour_details (per-date slot arrays, with
             *                                   formatted_*, client_*, and
             *                                   slot_timestamp populated)
             *   - selected_date
             *
             * No `wp.hooks.applyFilters(...)` calls are invoked in this Lite
             * port — those filter chains in legacy serve timezone-aware and
             * day-service rewriting (Pro/server-timezone features). The Lite
             * default with no timezone override produces an identity pass
             * through those filters, which is what we mirror here.
             */
            function bookingpressWorkingDatesData(working_hour_details, response_data) {
                const updated = {};
                const available = [];
                let first = '';

                const whd = working_hour_details || {};
                for (const wdate in whd) {
                    if (!Object.prototype.hasOwnProperty.call(whd, wdate)) continue;

                    const list = Array.isArray(whd[wdate]) ? whd[wdate].slice() : [];
                    list.sort((a, b) => (parseInt(a && a.counter_pos, 10) || 0) - (parseInt(b && b.counter_pos, 10) || 0));

                    let x = 0;
                    for (let i = 0; i < list.length; i++) {
                        const wh = list[i] || {};
                        const stTime = wh.store_start_time || '';
                        const etTime = wh.store_end_time   || '';
                        wh.client_start_time = stTime;
                        wh.client_end_time   = etTime;
                        const updated_wdate = wdate;
                        const updated_edate = wh.selected_end_date || wdate;

                        if (typeof updated[updated_wdate] === 'undefined') {
                            updated[updated_wdate] = [];
                            if (available.indexOf(updated_wdate + ' 00:00:00') < 0) {
                                available.push(updated_wdate + ' 00:00:00');
                            }
                        }
                        if (first === '') first = updated_wdate;

                        wh.client_date     = updated_wdate;
                        wh.client_end_date = updated_edate;

                        const startHour = (stTime || '').split(':')[0] || '0';
                        const formattedStart = bookingpressFormatTime(stTime);
                        const formattedEnd   = bookingpressFormatTime(etTime);
                        wh.formatted_start_time     = wh.formatted_start_time     || formattedStart;
                        wh.formatted_end_time       = wh.formatted_end_time       || formattedEnd;
                        wh.formatted_start_end_time = wh.formatted_start_end_time
                            || (formattedStart + ' - ' + formattedEnd);
                        wh.start_hour     = startHour;
                        wh.slot_timestamp = new Date(updated_wdate + ' ' + stTime).getTime();

                        updated[updated_wdate][x++] = wh;
                    }
                }

                const rd = response_data || {};
                if (rd.pre_selected_date === true && rd.selected_date) {
                    first = String(rd.selected_date);
                }
                return { available_dates: available, updated_working_hour_details: updated, selected_date: first };
            }

            /**
             * Port of `bookingpress_categories_timeslots` (legacy line 9704).
             * Buckets pre-processed slot objects into morning / afternoon /
             * evening / night using the per-tenant bucket thresholds.
             */
            function bookingpressCategoriesTimeslots(timeslot_details) {
                const AFTERNOON = parseInt(state.bpa_afternoon_slots_timing, 10) || 12;
                const EVENING   = parseInt(state.bpa_evening_slots_timing,   10) || 17;
                const NIGHT     = parseInt(state.bpa_night_slots_timing,     10) || 21;
                const out = { morning_time: [], afternoon_time: [], evening_time: [], night_time: [] };
                if (!Array.isArray(timeslot_details)) return out;

                let x = 0;
                for (let i = 0; i < timeslot_details.length; i++) {
                    const td = timeslot_details[i];
                    if (!td || td.is_next_day === true) continue;
                    const sh = parseInt(td.start_hour, 10) || 0;
                    if (sh >= 0 && sh < AFTERNOON)             out.morning_time.push(td);
                    else if (sh >= AFTERNOON && sh < EVENING)  out.afternoon_time.push(td);
                    else if (sh >= EVENING   && sh < NIGHT)    out.evening_time.push(td);
                    else                                       out.night_time.push(td);
                    x++;
                }
                if (timeslot_details.length > x) {
                    for (let i = 0; i < timeslot_details.length; i++) {
                        const td = timeslot_details[i];
                        if (td && td.is_next_day === true) out.night_time.push(td);
                    }
                }
                for (const k in out) {
                    out[k].sort((a, b) => (parseInt(a.slot_timestamp, 10) || 0) - (parseInt(b.slot_timestamp, 10) || 0));
                }
                return out;
            }

            /**
             * Absorb `vcal_attributes` / `vcal_capacity_attrs` from a server
             * response. Shared between the initial fetch and each
             * progressive-month response (legacy lines 9418 and 9562).
             */
            function absorbVCalAttributes(data) {
                const attrs = data && data.vcal_attributes;
                if (attrs && typeof attrs === 'object' && !Array.isArray(attrs)) {
                    const arr = Array.isArray(state.v_calendar_attributes) ? state.v_calendar_attributes.slice() : [];
                    const cur = Object.assign({}, state.v_calendar_attributes_current || {});
                    let k = arr.length + 1;
                    for (const d in attrs) {
                        if (!Object.prototype.hasOwnProperty.call(attrs, d)) continue;
                        cur[d] = attrs[d];
                        arr.push({ key: k++, dates: d, customData: { title: attrs[d] } });
                    }
                    state.v_calendar_attributes         = arr;
                    state.v_calendar_attributes_current = cur;
                }
                const cap = data && data.vcal_capacity_attrs;
                if (cap && typeof cap === 'object' && !Array.isArray(cap)) {
                    const blocked = Array.isArray(state.v_calendar_blocked_dates) ? state.v_calendar_blocked_dates.slice() : [];
                    for (const d in cap) {
                        if (!Object.prototype.hasOwnProperty.call(cap, d)) continue;
                        if (String(cap[d]) === '0' && blocked.indexOf(d) < 0) blocked.push(d);
                    }
                    state.v_calendar_blocked_dates = blocked;
                }
            }

            /**
             * Port of `bookingpress_disable_date_xhr_v2` (legacy line 9308).
             * Admin-ajax call to `bookingpress_fetch_timeslot_data` — this is
             * the Lite default flow (`$bookingpress_use_legacy_functions=false`).
             */
            function fetchAvailableDates(serviceId, selectedDate) {
                if (!ajaxUrl) return Promise.resolve(null);
                let sid = serviceId;
                if (!sid && state.appointment_step_form_data.selected_service) {
                    sid = state.appointment_step_form_data.selected_service;
                }
                if (!sid) return Promise.resolve(null);

                const preselect = !!selectedDate;

                state.isLoadTimeLoader           = '1';
                state.isLoadDateTimeCalendarLoad = '1';
                state.is_date_loading            = '1';
                if (!preselect) {
                    state.appointment_step_form_data.selected_start_time = '';
                    state.appointment_step_form_data.selected_end_time   = '';
                }
                state.service_timing                = '-3';
                state.no_timeslot_available         = false;
                state.v_calendar_check_month_dates  = false;
                state.v_calendar_next_month_dates   = '';
                state.days_off_disabled_dates       = '';
                state.isHoldBookingRequest          = false;
                state.v_calendar_attributes         = [];
                state.v_calendar_attributes_current = {};
                state.v_calendar_blocked_dates      = [];

                // Legacy line 9340: regenerate per-request form token when
                // no cart items exist yet.
                const apt = state.appointment_step_form_data;
                if (!Array.isArray(apt.cart_items) || apt.cart_items.length === 0) {
                    apt.bookingpress_form_token = (apt.bookingpress_uniq_id || '') + '_' + Math.random().toString(36).slice(2);
                }

                const body = new URLSearchParams();
                body.set('action',               'bookingpress_fetch_timeslot_data');
                body.set('service_id',           String(sid));
                body.set('selected_service',     String(sid));
                body.set('selected_date',        String(selectedDate || ''));
                body.set('is_preselect',         preselect ? 'true' : 'false');
                body.set('_wpnonce',             wpNonce);
                body.set('appointment_data_obj', JSON.stringify(apt));

                return fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                })
                    .then((r) => r.json())
                    .then((json) => {
                        const data = (json && json.data) ? json.data : (json || {});
                        state.is_date_loading = '0';

                        const working = data.working_details || {};
                        const wh      = bookingpressWorkingDatesData(working, data);

                        let selectedDateOut = data.selected_date || '';
                        const preselected = (data.pre_selected_date === true) ? data.selected_date : '';
                        state.appointment_step_form_data.selected_date = '';
                        selectedDateOut = preselected || wh.selected_date || selectedDateOut;

                        state.v_calendar_available_dates = wh.available_dates.slice();
                        state.v_calendar_timeslots_data  = wh.updated_working_hour_details;

                        const onlyDates = [];
                        for (let i = 0; i < state.v_calendar_available_dates.length; i++) {
                            const v = state.v_calendar_available_dates[i];
                            if (typeof v === 'string') onlyDates.push(v.split(' ')[0]);
                        }
                        if (onlyDates.indexOf(selectedDateOut) < 0 && wh.selected_date && wh.selected_date !== selectedDateOut) {
                            selectedDateOut = wh.selected_date;
                        }
                        state.v_calendar_available_only_date = onlyDates;
                        state.no_timeslot_available          = false;

                        absorbVCalAttributes(data);
                        state.v_calendar_default_label   = data.max_capacity_capacity || state.v_calendar_default_label || '';
                        state.v_calendar_time_token_data = data.working_hour_timing_token || {};

                        if (selectedDateOut) {
                            setTimeout(() => {
                                state.appointment_step_form_data.selected_date = selectedDateOut;
                                if (vcalendarBridge.vm) {
                                    // The parent watcher will normalize string→Date; still
                                    // push an explicit Date so the month-move below lands on
                                    // the right page even if the watcher hasn't fired yet.
                                    try { vcalendarBridge.vm.selected_date = ymdToDate(selectedDateOut); } catch (e) { /* noop */ }
                                    const refs = vcalendarBridge.vm.$refs;
                                    if (refs && refs.bkp_front_calendar && typeof refs.bkp_front_calendar.move === 'function') {
                                        try { refs.bkp_front_calendar.move(selectedDateOut); } catch (e) { /* noop */ }
                                    }
                                }
                            }, 10);
                        }

                        if (wh.updated_working_hour_details && wh.updated_working_hour_details[selectedDateOut]) {
                            state.service_timing = bookingpressCategoriesTimeslots(wh.updated_working_hour_details[selectedDateOut]);
                        } else {
                            state.service_timing        = { morning_time: [], afternoon_time: [], evening_time: [], night_time: [] };
                            state.no_timeslot_available = true;
                        }

                        state.isLoadTimeLoader           = '0';
                        state.isLoadDateTimeCalendarLoad = '0';

                        // Legacy line 9481 — kick off progressive walker.
                        // Match the legacy LOOSE equality check exactly:
                        //   if( "undefined" == typeof response.data.stop_check
                        //       || false == response.data.stop_check ) { recurse; }
                        // i.e. "continue when stop_check is missing or loosely
                        // equal to false". Strict `!== true` was too lax — it
                        // kept recursing when the server emitted `1` or any
                        // truthy-non-boolean stop marker, overshooting the
                        // configured max date.
                        if ((typeof data.stop_check === 'undefined' || data.stop_check == false) && data.next_month_date) {
                            bookingpressRetrieveFutureMonthDetails(data.next_month_date, 1);
                        }

                        // Back-compat: if the server also emitted the
                        // legacy disable-date CSV, merge it; and keep
                        // max_available_date as booking_cal_maxdate.
                        mergeBlockedDatesString(data.days_off_disabled_dates_string || '');
                        if (data.max_available_date) state.booking_cal_maxdate = String(data.max_available_date);

                        return data;
                    })
                    .catch((err) => {
                        state.is_date_loading            = '0';
                        state.isLoadTimeLoader           = '0';
                        state.isLoadDateTimeCalendarLoad = '0';
                        try { console.log(err); } catch (e) { /* noop */ }
                        return null;
                    });
            }

            /**
             * Port of `bookingpress_retrieve_future_month_details` (legacy line 9521).
             * Walks forward month-by-month up to counter 3. On counter===4 it
             * stops and stashes `v_calendar_next_month_dates` so that
             * `bpaMoveMonth` can resume when the user scrolls that far.
             * Action: `bookingpress_retrieve_entire_month_details` via admin-ajax.
             */
            function bookingpressRetrieveFutureMonthDetails(next_month_date, counter) {
                if (!ajaxUrl) return;
                const ct = parseInt(counter, 10) || 1;
                let startDate = next_month_date || '';
                if (!startDate && Array.isArray(state.v_calendar_available_dates) && state.v_calendar_available_dates.length) {
                    startDate = state.v_calendar_available_dates[state.v_calendar_available_dates.length - 1];
                }

                // Client-side stop condition: do NOT walk past the
                // configured `booking_cal_maxdate`. Even if the server
                // keeps returning a `next_month_date` (e.g. Pro addons that
                // extend the window), the site's configured max is the
                // authoritative upper bound for the picker.
                if (state.booking_cal_maxdate && startDate) {
                    const startYmd = String(startDate).slice(0, 10);
                    const maxYmd   = String(state.booking_cal_maxdate).slice(0, 10);
                    if (startYmd > maxYmd) {
                        state.v_calendar_check_month_dates = false;
                        state.v_calendar_next_month_dates  = '';
                        state.isHoldBookingRequest         = false;
                        state.isLoadDateTimeCalendarLoad   = '0';
                        return;
                    }
                }

                // Hard ceiling — legacy uses `4 == counter` (strict match at
                // counter 4). When `bpaMoveMonth` computes a starting counter
                // like `0 - monthDiff` and the user jumps multiple months
                // ahead, the starting counter can land PAST 4 (e.g. 5, 8) or
                // deep negative, causing the recursive walker to blow past
                // the legacy 3-month window and fire 6–12 month-detail
                // AJAX calls. Using `>=` here matches legacy walker depth
                // regardless of the starting counter.
                if (ct >= 4) {
                    state.v_calendar_check_month_dates = true;
                    state.v_calendar_next_month_dates  = next_month_date || '';
                    state.isHoldBookingRequest         = false;
                    return;
                }
                state.isHoldBookingRequest = true;

                const body = new URLSearchParams();
                body.set('action',               'bookingpress_retrieve_entire_month_details');
                body.set('from_date',            String(startDate || ''));
                body.set('counter',              String(ct));
                body.set('_wpnonce',             wpNonce);
                body.set('appointment_data_obj', JSON.stringify(state.appointment_step_form_data || {}));

                fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                })
                    .then((r) => r.json())
                    .then((json) => {
                        const data    = (json && json.data) ? json.data : (json || {});
                        const working = data.working_details || {};
                        const wh      = bookingpressWorkingDatesData(working, data);

                        state.v_calendar_available_dates = (state.v_calendar_available_dates || []).concat(wh.available_dates);
                        state.v_calendar_time_token_data = Object.assign({}, state.v_calendar_time_token_data || {}, data.working_hour_timing_token || {});
                        absorbVCalAttributes(data);

                        const onlyDates = [];
                        for (let i = 0; i < state.v_calendar_available_dates.length; i++) {
                            const v = state.v_calendar_available_dates[i];
                            if (typeof v === 'string') onlyDates.push(v.split(' ')[0]);
                        }
                        state.v_calendar_available_only_date = onlyDates;
                        state.v_calendar_timeslots_data      = Object.assign({}, state.v_calendar_timeslots_data || {}, wh.updated_working_hour_details);
                        state.isLoadDateTimeCalendarLoad     = '0';

                        const nextCounter = ct + 1;
                        const nextMonthYmd = data.next_month_date ? String(data.next_month_date).slice(0, 10) : '';
                        const maxYmd       = state.booking_cal_maxdate ? String(state.booking_cal_maxdate).slice(0, 10) : '';
                        const pastMax      = !!(maxYmd && nextMonthYmd && nextMonthYmd > maxYmd);
                        // Legacy line 9610 recursion guard — LOOSE comparison
                        // so `1` / `"true"` also stops the walker, matching
                        // `if( "undefined" == typeof response.data.stop_check
                        //     || response.data.stop_check == false )`.
                        const shouldContinueWalker = (typeof data.stop_check === 'undefined' || data.stop_check == false);
                        if (shouldContinueWalker && data.next_month_date && !pastMax) {
                            bookingpressRetrieveFutureMonthDetails(data.next_month_date, nextCounter);
                        } else {
                            // Past-max or server stop: don't stash a cursor,
                            // so `bpaMoveMonth` won't re-trigger past the
                            // configured boundary.
                            if (pastMax) {
                                state.v_calendar_check_month_dates = false;
                                state.v_calendar_next_month_dates  = '';
                            }
                            state.isHoldBookingRequest = false;
                        }
                    })
                    .catch((err) => {
                        state.isHoldBookingRequest = false;
                        try { console.log(err); } catch (e) { /* noop */ }
                    });
            }

            /**
             * Port of `bookingpress_retrieve_future_month_details_single`
             * (legacy line 9619). Fires a single-month fetch for the month
             * the user jumped to, then resumes the 3-month walker on the
             * stashed `next_month_dates`.
             */
            function bookingpressRetrieveFutureMonthDetailsSingle(next_month_date, next_month_dates, counter) {
                if (!ajaxUrl) return;
                const ct = parseInt(counter, 10) || 1;
                let startDate = next_month_date || '';
                if (!startDate && Array.isArray(state.v_calendar_available_dates) && state.v_calendar_available_dates.length) {
                    startDate = state.v_calendar_available_dates[state.v_calendar_available_dates.length - 1];
                }

                // Same max-date stop as the main walker.
                if (state.booking_cal_maxdate && startDate) {
                    const startYmd = String(startDate).slice(0, 10);
                    const maxYmd   = String(state.booking_cal_maxdate).slice(0, 10);
                    if (startYmd > maxYmd) {
                        state.v_calendar_check_month_dates = false;
                        state.v_calendar_next_month_dates  = '';
                        state.isHoldBookingRequest         = false;
                        state.isLoadDateTimeCalendarLoad   = '0';
                        return;
                    }
                }

                state.v_calendar_check_month_dates = true;
                state.v_calendar_next_month_dates  = next_month_date || '';
                state.isHoldBookingRequest         = false;

                const body = new URLSearchParams();
                body.set('action',               'bookingpress_retrieve_entire_month_details');
                body.set('from_date',            String(startDate || ''));
                body.set('counter',              String(ct));
                body.set('_wpnonce',             wpNonce);
                body.set('appointment_data_obj', JSON.stringify(state.appointment_step_form_data || {}));

                fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                })
                    .then((r) => r.json())
                    .then((json) => {
                        const data    = (json && json.data) ? json.data : (json || {});
                        const working = data.working_details || {};
                        const wh      = bookingpressWorkingDatesData(working, data);

                        state.v_calendar_available_dates = (state.v_calendar_available_dates || []).concat(wh.available_dates);
                        state.v_calendar_time_token_data = Object.assign({}, state.v_calendar_time_token_data || {}, data.working_hour_timing_token || {});
                        absorbVCalAttributes(data);

                        const onlyDates = [];
                        for (let i = 0; i < state.v_calendar_available_dates.length; i++) {
                            const v = state.v_calendar_available_dates[i];
                            if (typeof v === 'string') onlyDates.push(v.split(' ')[0]);
                        }
                        state.v_calendar_available_only_date = onlyDates;
                        state.v_calendar_timeslots_data      = Object.assign({}, state.v_calendar_timeslots_data || {}, wh.updated_working_hour_details);
                        state.isLoadDateTimeCalendarLoad     = '0';

                        // Resume the 3-month walker from the original stashed cursor.
                        bookingpressRetrieveFutureMonthDetails(next_month_dates || '', ct);
                    })
                    .catch((err) => {
                        try { console.log(err); } catch (e) { /* noop */ }
                    });
            }

            /**
             * Port of `bpaMoveMonth` (legacy line 10069). Called by the
             * VCalendar mini-app on `@update:from-page`. Two branches:
             *   (1) user navigated exactly onto the stashed next-month
             *       cursor  → resume the 3-month walker from counter 1.
             *   (2) user jumped past the cursor → single-month fetch for
             *       the visible month, then resume from counter = 0-monthDiff.
             */
            function bpaMoveMonth(page) {
                if (!page) return;
                if (!state.v_calendar_check_month_dates) return;
                if (!state.v_calendar_next_month_dates)  return;
                if (state.isHoldBookingRequest)          return;

                const parts = String(state.v_calendar_next_month_dates).split('-');
                const next_page_year  = parseInt(parts[0], 10);
                const next_page_month = parseInt(parts[1], 10);
                const current_month   = parseInt(page.month, 10);
                const current_year    = parseInt(page.year,  10);
                if (!next_page_year || !next_page_month || !current_month || !current_year) return;

                if (current_year === next_page_year && current_month === next_page_month) {
                    state.isLoadDateTimeCalendarLoad = '1';
                    bookingpressRetrieveFutureMonthDetails(state.v_calendar_next_month_dates, 1);
                } else if ((current_year === next_page_year && current_month > next_page_month) || current_year > next_page_year) {
                    const current_date   = new Date(current_year + '-' + String(current_month).padStart(2, '0') + '-01');
                    const next_page_date = new Date(next_page_year + '-' + String(next_page_month).padStart(2, '0') + '-01');
                    const monthDiff      = current_date.getMonth() - next_page_date.getMonth();
                    const month_         = (String(current_month).length === 1) ? ('0' + String(current_month)) : String(current_month);
                    const nextMonthDate  = current_year + '-' + month_ + '-01';
                    const counter        = 0 - monthDiff;
                    state.isLoadDateTimeCalendarLoad = '1';
                    bookingpressRetrieveFutureMonthDetailsSingle(nextMonthDate, state.v_calendar_next_month_dates, counter);
                }
            }

            /**
             * Time-slot bucketing for a given date — reads from the
             * pre-loaded `v_calendar_timeslots_data` cache populated by
             * the v2 flow. Legacy `dayClicked` (line 8498) uses the same
             * cache — there is no extra AJAX call. Kept as a Promise so
             * existing call-sites (provide/injections) stay compatible.
             */
            function fetchTimeslots(serviceId, selectedDate) {
                if (!selectedDate) return Promise.resolve(null);
                const bucket = state.v_calendar_timeslots_data && state.v_calendar_timeslots_data[selectedDate];
                if (bucket) {
                    state.service_timing        = bookingpressCategoriesTimeslots(bucket);
                    state.no_timeslot_available = false;
                } else {
                    state.service_timing        = { morning_time: [], afternoon_time: [], evening_time: [], night_time: [] };
                    state.no_timeslot_available = true;
                }
                return Promise.resolve({ service_timing: state.service_timing });
            }

            /**
             * VCalendar day-click handler — exact port of legacy `dayClicked`
             * (line 8498). Checks the slot is in `v_calendar_available_dates`,
             * not in `v_calendar_blocked_dates`, and not already selected;
             * flips `service_timing` to the `-2` "loading" sentinel, writes
             * the date into state, and (after a 10ms tick, to let the
             * `-2` skeleton paint) buckets the pre-loaded slot cache
             * through `bookingpress_categories_timeslots`.
             */
            function dayClicked(day) {
                if (!day) return;
                const dayId = day.id || day.dateString || toIsoYmd(day.date || day);
                if (!dayId) return;

                const available = Array.isArray(state.v_calendar_available_dates) ? state.v_calendar_available_dates : [];
                if (available.indexOf(dayId + ' 00:00:00') < 0 && available.indexOf(dayId) < 0) return;
                // Issue 6 — when the user navigates back from a later step
                // (e.g. basic_details → datetime via Go Back) and clicks the
                // same date that was last selected, the legacy guard
                // `bpa_current_selected_date === dayId` previously short-
                // circuited *all* re-selection. We now only short-circuit
                // when the slots for the same date are already populated AND
                // the user has not yet picked a slot (i.e. nothing to refresh).
                // This allows a back-then-forward flow to re-render the
                // bucket so a fresh `selectTiming` click registers cleanly.
                if (state.bpa_current_selected_date === dayId
                    && state.service_timing
                    && typeof state.service_timing === 'object'
                    && !state.appointment_step_form_data.selected_start_time
                ) {
                    return;
                }
                const blocked = state.v_calendar_blocked_dates || [];
                if (Array.isArray(blocked) && blocked.indexOf(dayId) > -1) return;
                // Never accept a click past the configured max (legacy parity).
                if (state.booking_cal_maxdate && dayId > String(state.booking_cal_maxdate).slice(0, 10)) return;

                state.service_timing                            = '-2';
                state.appointment_step_form_data.selected_date  = dayId;
                state.bpa_current_selected_date                 = dayId;
                state.no_timeslot_available                     = false;

                /* Legacy `.__sm` parity: picking a day on mobile returns the
                 * user to the time-slots view. No-op on desktop because the
                 * state isn't read there (both columns are CSS-visible). */
                state.displayResponsiveCalendar                 = '0';

                setTimeout(() => {
                    const bucket = state.v_calendar_timeslots_data && state.v_calendar_timeslots_data[dayId];
                    if (bucket) {
                        state.service_timing = bookingpressCategoriesTimeslots(bucket);
                    } else {
                        state.service_timing        = { morning_time: [], afternoon_time: [], evening_time: [], night_time: [] };
                        state.no_timeslot_available = true;
                    }
                    state.appointment_step_form_data.selected_start_time               = '';
                    state.appointment_step_form_data.selected_end_time                 = '';
                    state.appointment_step_form_data.selected_formatted_start_time     = '';
                    state.appointment_step_form_data.selected_formatted_end_time       = '';
                    state.appointment_step_form_data.selected_formatted_start_end_time = '';
                }, 10);
            }

            /**
             * Persist the selected day's `tokenData` into the PHP transient
             * `bpa_front_timings_<form_token>_<date>` via the admin-ajax
             * endpoint `bpa_set_timeslot_token` (class.bookingpress_…:1603).
             *
             * Why this has to exist: on submit, the server calls
             * `bookingpress_before_book_appointment_func` (line 2771) which
             * reads that transient and does
             *     $timings = array_values( $bpa_front_timings_data );   // L3003
             *     array_column( $timings, 'store_start_time' );         // L3007
             * If this AJAX never fires, the transient is an empty string and
             * `array_values('')` raises a TypeError. Legacy fires the same
             * call from `bookingpress_update_timestep_token` (line 9017)
             * right after `selectTiming` — we mirror that here.
             *
             * `v_calendar_time_token_data` was populated earlier by the
             * timeslot-fetch response (`working_hour_timing_token`).
             */
            function bookingpress_update_timestep_token() {
                if (!ajaxUrl) return;
                const selected_date =
                    state.appointment_step_form_data.store_selected_date
                    || state.appointment_step_form_data.selected_date
                    || '';
                if (!selected_date) return;

                const token_map = state.v_calendar_time_token_data || {};
                const bucket    = token_map[selected_date];
                if (!Array.isArray(bucket) || !bucket.length) return;

                const token_data = bucket[0];
                if (!token_data) return;

                const body = new URLSearchParams();
                body.set('action',       'bpa_set_timeslot_token');
                body.set('tokenData',    String(token_data));
                body.set('selectedDate', String(selected_date));
                body.set('_wpnonce',     wpNonce);

                fetch(ajaxUrl, {
                    method:      'POST',
                    credentials: 'same-origin',
                    headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body:        body.toString(),
                }).catch(() => { /* fire-and-forget — server is source of truth */ });
            }

            /**
             * Time-slot selection — legacy parity (line 8728). Persists the
             * six time keys, mirrors the `store_*` / `customer_*` shadow
             * fields the legacy save endpoint relies on, fires the transient-
             * populating AJAX (`bpa_set_timeslot_token`), and auto-advances
             * to basic_details — matching legacy's `selectTiming` which
             * ends with `bookingpress_step_navigation(...)` then
             * `bookingpress_update_timestep_token()`.
             */
            function selectTiming(start_time, end_time, store_start_time, store_end_time, store_booked_date, formatted_start_time, formatted_end_time, time_details) {
                if (start_time == null) return;

                const td = (time_details && typeof time_details === 'object') ? time_details : {};

                state.appointment_step_form_data.selected_start_time             = store_start_time != null ? store_start_time : start_time;
                state.appointment_step_form_data.selected_end_time               = store_end_time != null ? store_end_time : (end_time || '');
                state.appointment_step_form_data.selected_formatted_start_time   = formatted_start_time != null ? formatted_start_time : start_time;
                state.appointment_step_form_data.selected_formatted_end_time     = formatted_end_time != null ? formatted_end_time : (end_time || '');
                state.appointment_step_form_data.selected_formatted_start_end_time =
                    td.formatted_start_end_time
                    || (state.appointment_step_form_data.selected_formatted_start_time
                        + (state.appointment_step_form_data.selected_formatted_end_time
                            ? ' - ' + state.appointment_step_form_data.selected_formatted_end_time
                            : ''));
                if (store_booked_date) {
                    state.appointment_step_form_data.selected_date = store_booked_date;
                }

                // Legacy parity (line 8741-8747): shadow the `store_*` copy of
                // the selection so the transient lookup on submit works for
                // timezone-shifted flows. Set unconditionally when the slot
                // carries store_* fields; otherwise left as-is.
                if (store_start_time != null && store_end_time != null && store_booked_date) {
                    state.appointment_step_form_data.store_start_time       = store_start_time;
                    state.appointment_step_form_data.store_end_time         = store_end_time;
                    state.appointment_step_form_data.store_selected_date    = store_booked_date;
                    state.appointment_step_form_data.store_selected_end_date = td.selected_end_date || store_booked_date;
                }

                // Legacy parity (line 8749-8752): customer_* mirrors for the
                // thank-you / email templates. Lite templates don't read
                // these but addons and Pro do, so keep the shape.
                state.appointment_step_form_data.customer_selected_date     = td.client_date     || state.appointment_step_form_data.selected_date || '';
                state.appointment_step_form_data.customer_selected_end_date = td.client_end_date || td.client_date || state.appointment_step_form_data.selected_date || '';
                state.appointment_step_form_data.customer_selected_time     = td.client_start_time || state.appointment_step_form_data.selected_start_time || '';
                state.appointment_step_form_data.customer_selected_end_time = td.client_end_time   || state.appointment_step_form_data.selected_end_time   || '';

                const basicStep = state.bookingpress_sidebar_step_data.basic_details;
                if (basicStep) basicStep.is_allow_navigate = 1;

                // Auto-advance, then persist the tokenData into the PHP
                // transient. Order matches legacy (step nav BEFORE token
                // update) — both are fire-and-forget from the click path.
                bookingpress_step_navigation('basic_details', 'basic_details', 'datetime');
                bookingpress_update_timestep_token();
            }

            // ---- Basic Details (Step 3E) ----

            // Template ref attached to the BpUiForm. Populated once the
            // basic_details panel mounts; stays `null` on earlier steps so
            // calling `.validate()` before the panel renders is safe.
            const formRef = ref(null);

            // Issue 1 — per-field validation error map. Stored as a ref
            // (not on `state`) so the reactivity is unambiguous and the
            // template `v-if` re-evaluates immediately after each
            // assignment. Vue auto-unwraps refs in template expressions,
            // so the template can use `basicDetailsErrors[key]` directly.
            const basicDetailsErrors = ref({});

            // Timer handle for the 6-second error auto-hide (legacy L7761).
            // Kept in setup scope so a fresh `bookingpress_set_error_msg`
            // call cancels any in-flight timer before starting a new one —
            // prevents a late-firing timer from clearing a freshly-set
            // error, and avoids stacking multiple timers on rapid errors.
            let errorAutoHideTimer = null;

            /**
             * Clear the top-of-panel error toast. Mirrors legacy
             * `bookingpress_remove_error_msg()` (class.bookingpress_…:7765).
             * Also cancels any pending auto-hide timer so a fresh clear
             * never races with one about to fire.
             */
            function bookingpress_remove_error_msg() {
                if (errorAutoHideTimer != null) {
                    clearTimeout(errorAutoHideTimer);
                    errorAutoHideTimer = null;
                }
                state.is_display_error = '0';
                state.is_error_msg     = '';
            }

            /**
             * Surface an error message in the top-of-panel toast and arm
             * the 6-second auto-hide timer — legacy parity (L7739-7764).
             *
             * Why auto-hide matters: the submit flow gates on
             * `is_display_error != '1'` (legacy L7813, mirrored in
             * bookingpress_process_to_book_appointment). Without the timer,
             * a stale error would permanently block subsequent submits.
             */
            function bookingpress_set_error_msg(msg) {
                if (errorAutoHideTimer != null) {
                    clearTimeout(errorAutoHideTimer);
                    errorAutoHideTimer = null;
                }
                state.is_display_error = '1';
                state.is_error_msg     = String(msg || '');
                errorAutoHideTimer = setTimeout(() => {
                    errorAutoHideTimer = null;
                    state.is_display_error = '0';
                    state.is_error_msg     = '';
                }, 6000);
            }

            /**
             * Synchronous, best-effort port of the legacy el-form
             * `$refs.appointment_step_form_data.validate(cb)` call at
             * class.bookingpress_…:~10222. Walks `customer_details_rule`
             * entries and checks each required rule against the current
             * `appointment_step_form_data[v_model_value]` value.
             *
             * Returns `true` if every required field passes; otherwise
             * returns `false` AND surfaces the first failing rule's message
             * via `bookingpress_set_error_msg`. Deliberately synchronous —
             * Step 3E does not rewire `bookingpress_step_navigation` to be
             * async.
             *
             * If the BpUiForm ref exposes a `.validate()` method (Element-
             * Plus-style), we also fire-and-forget it so per-field error
             * underlines appear, but its async callback is not awaited.
             */
            function validateBasicDetails() {
                const rules = state.customer_details_rule || {};
                const data  = state.appointment_step_form_data || {};
                let firstInvalidKey = '';

                // Issue 1 — field-level error tracking. We populate
                // `state.basic_details_errors` synchronously with each
                // field's failing message; the template renders these
                // beneath their corresponding bp-ui-form-item so users see
                // a per-field error (matching legacy `el-form-item__error`).
                const errorMap = {};

                // NOTE: BpUiForm/ElForm's own per-rule validation is
                // intentionally NOT triggered here. Doing so makes
                // ElFormItem render its own `el-form-item__error` /
                // `bp-form-item__error` underneath each field — which
                // overlays the manual error rendered by the template
                // from `basicDetailsErrors`, producing two stacked
                // (visually duplicated) messages. We own validation +
                // rendering manually below so the user sees a single
                // error and clear-on-input behaviour stays consistent.

                const keys = Object.keys(rules);
                for (let i = 0; i < keys.length; i++) {
                    const key       = keys[i];
                    const ruleList  = Array.isArray(rules[key]) ? rules[key] : [rules[key]];
                    const value     = data[key];
                    let fieldFailedMsg = '';

                    for (let j = 0; j < ruleList.length; j++) {
                        const rule = ruleList[j] || {};

                        if (rule.required === true) {
                            const isEmpty =
                                (value == null)
                                || (typeof value === 'string' && value.trim() === '')
                                || (Array.isArray(value) && value.length === 0)
                                || (typeof value === 'boolean' && value === false && key === 'appointment_terms_conditions');
                            if (isEmpty) {
                                fieldFailedMsg = rule.message || 'This field is required';
                                break; // stop scanning this field's rules
                            }
                        }

                        if (rule.type === 'email' && value && typeof value === 'string') {
                            // RFC-5322 lite check; matches Element-Plus's default
                            // email validator closely enough for parity without
                            // pulling in async-validator.
                            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim())) {
                                fieldFailedMsg = rule.message || 'Please enter a valid email address';
                                break;
                            }
                        }
                    }

                    if (fieldFailedMsg) {
                        errorMap[key] = fieldFailedMsg;
                        if (!firstInvalidKey) firstInvalidKey = key;
                    }
                }

                basicDetailsErrors.value = errorMap;

                if (firstInvalidKey) {
                    // Issue 1: field-level errors only — per-field `<span>` is
                    // rendered by the template via state.basic_details_errors.
                    // Do NOT raise the top-of-step toast (legacy parity).
                    bookingpress_remove_error_msg();
                    // Issue 1 (4): focus the first invalid field so screen-
                    // reader and keyboard users land on the failing input.
                    focusBasicDetailsFirstInvalid(firstInvalidKey);
                    return false;
                }
                bookingpress_remove_error_msg();
                return true;
            }

            /**
             * Issue 1 — clear the per-field error message for `key` once
             * the user starts editing. Bound on `@input` from each input
             * in the Basic Details template so the inline error vanishes
             * as the user corrects the field (legacy parity: el-form-item
             * clears its error on any input event when rule.trigger=blur).
             */
            function clearBasicDetailsFieldError(key) {
                if (!key) return;
                const cur = basicDetailsErrors.value || {};
                if (cur[key]) {
                    const next = Object.assign({}, cur);
                    delete next[key];
                    basicDetailsErrors.value = next;
                }
            }

            /**
             * Issue 1 — focus the first invalid Basic Details field after a
             * failed validation. The BpUiForm `formRef` exposes a
             * `scrollToField(prop)` helper in some builds; we walk the DOM
             * fallback path so this works regardless of which BpUiForm
             * version is bundled. The `data-bp-field` attribute is set on
             * each `bp-ui-form-item` wrapper in the template (see Issue 1
             * template change in this same patch) so we can target the
             * first invalid input by prop name.
             */
            function focusBasicDetailsFirstInvalid(key) {
                if (!key) return;
                nextTick(() => {
                    const root = (typeof document !== 'undefined' && instance && instance.instanceId)
                        ? document.getElementById(ROOT_ID_PREFIX + instance.instanceId)
                        : null;
                    if (!root) return;
                    const wrap = root.querySelector('[data-bp-field="' + key + '"]');
                    if (!wrap) return;
                    const target = wrap.querySelector('input, textarea, select')
                        || wrap.querySelector('[tabindex]')
                        || wrap;
                    if (target && typeof target.focus === 'function') {
                        try { target.focus({ preventScroll: false }); } catch (e) { target.focus(); }
                    }
                    if (target && typeof target.scrollIntoView === 'function') {
                        try {
                            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } catch (e) { /* IE11-style fallback */ }
                    }
                });
            }

            /**
             * Issue 2 — auto-focus the first input on Basic Details step
             * entry. Mirrors the legacy form's "click into firstname" UX
             * after navigating from Date & Time. Skipped when the user is
             * navigating away (the watcher only acts on incoming).
             */
            function focusBasicDetailsFirstField() {
                nextTick(() => {
                    const root = (typeof document !== 'undefined' && instance && instance.instanceId)
                        ? document.getElementById(ROOT_ID_PREFIX + instance.instanceId)
                        : null;
                    if (!root) return;
                    const panel = root.querySelector('.bpa-front-tabs--panel-body[data-tab="basic_details"].__bpa-is-active');
                    if (!panel) return;
                    const target = panel.querySelector('input:not([disabled]):not([type="hidden"]), textarea:not([disabled])');
                    if (target && typeof target.focus === 'function') {
                        try { target.focus({ preventScroll: true }); } catch (e) { target.focus(); }
                    }
                });
            }

            /**
             * BpUiTelInput @country-changed handler.
             *
             * BpUiTelInput emits the selected country object whenever the
             * user picks a different flag in the dropdown (and once on
             * mount if `auto-default-country` resolved a country from the
             * browser). Keep the ISO2 code and dial-code copied onto the
             * reactive form-data bucket so downstream booking submission
             * sees the same two fields the legacy frontend sent.
             *
             * Tolerant of missing fields on the payload — vue-tel-input
             * upstream occasionally emits a bare object while the pick
             * animation is settling.
             */
            function onPhoneCountryChanged(country) {
                if (!country || typeof country !== 'object') return;
                if (country.iso2) {
                    state.appointment_step_form_data.customer_phone_country = String(country.iso2).toLowerCase();
                }
                if (country.dialCode !== undefined && country.dialCode !== null) {
                    state.appointment_step_form_data.customer_phone_dial_code = String(country.dialCode);
                }
            }

            // ---- Summary (Step 3F) ----

            // Customer name fallback chain (legacy appointment_booking_form.php
            // L841-L844): customer_name → customer_firstname/lastname → email.
            // Computed off the reactive `appointment_step_form_data` so changes
            // made by going back to Basic Details surface immediately.
            const customerDisplayName = computed(() => {
                const d = state.appointment_step_form_data || {};
                if (d.customer_name != null && String(d.customer_name).trim() !== '') {
                    return d.customer_name;
                }
                const first = (d.customer_firstname != null) ? String(d.customer_firstname) : '';
                const last  = (d.customer_lastname  != null) ? String(d.customer_lastname)  : '';
                if (first !== '' || last !== '') {
                    return (first + ' ' + last).trim();
                }
                return d.customer_email || '';
            });

            // Legacy uses loose-equal `service_price_without_currency != '0'`
            // to toggle the amount row and the payment section. Keep the
            // same loose-compare semantics so addons that mutate that key
            // with a string ('0') or a number (0) behave identically.
            const isFreeService = computed(() => {
                // eslint-disable-next-line eqeqeq
                return state.appointment_step_form_data.service_price_without_currency == '0';
            });

            // Wrapper visibility gate for the payment-methods section
            // (legacy L875).
            const showPaymentMethods = computed(() => {
                // eslint-disable-next-line eqeqeq
                return !isFreeService.value && state.is_only_onsite_enabled != '1';
            });

            // Tracks whether ANY configured gateway is visible. When false
            // (both flags literal 'false'), legacy renders the empty-state
            // card; otherwise at least one gateway card is rendered.
            const hasAnyPaymentGateway = computed(() => {
                const onSite = state.on_site_payment;
                const pp     = state.paypal_payment;
                const onSiteOn = (onSite !== 'false' && onSite !== '');
                const paypalOn = (pp !== 'false' && pp !== '');
                return onSiteOn || paypalOn;
            });

            // Returns the gateway key when EXACTLY one payment method is
            // configured (so the UI should hide the picker but still
            // pre-select that method behind the scenes — legacy parity:
            // class.bookingpress_…:10342-10349 auto-clicks the only card).
            // Returns null when zero or multiple are configured.
            const singlePaymentMethod = computed(() => {
                const onSiteOn = (state.on_site_payment !== 'false' && state.on_site_payment !== '');
                const paypalOn = (state.paypal_payment !== 'false' && state.paypal_payment !== '');
                if (onSiteOn && !paypalOn) return 'on-site';
                if (paypalOn && !onSiteOn) return 'paypal';
                return null;
            });

            // Hide the picker UI when there is only one method (or none).
            // The single method is pre-selected in the watcher below; the
            // gateway-selection events still fire so PayPal popup-mode
            // renders the SDK button on the Summary step automatically.
            const showPaymentMethodPicker = computed(() => {
                return hasAnyPaymentGateway.value && !singlePaymentMethod.value;
            });

            /**
             * Render PayPal SDK buttons into `#paypal-button-container` —
             * port of legacy `bookingpress_after_selecting_payment_method_data`
             * (class.bookingpress_…:733-813). Invoked only when PayPal is
             * selected and popup mode is active. The SDK script is already
             * enqueued by PHP `bookingpress_paypal_scripts_add` when popup
             * mode is enabled, so `window.paypal` is expected to exist.
             *
             * The createOrder handler posts to `bookingpress_paypal_booking_validate_lite`
             * with the current appointment payload; the PHP handler uses the
             * server-side client secret to talk to PayPal. onApprove posts
             * to `bookingpress_paypal_booking_payment_confirm_lite` and
             * redirects to the returned success URL on success.
             *
             * Defensive: no-ops if the SDK is not available (popup mode off,
             * paypal_client_id missing, or network failure loading the SDK
             * script). `paypal_client_id` empty is also a no-op — legacy
             * surfaces an error in that branch; we keep the error path via
             * the existing `bookingpress_set_error_msg` helper.
             */
            function renderPayPalButtons() {
                if (typeof window === 'undefined' || !window.paypal || typeof window.paypal.Buttons !== 'function') {
                    return;
                }
                if (!state.paypal_client_id) {
                    bookingpress_set_error_msg('Client ID is required.');
                    return;
                }
                const container = document.getElementById('paypal-button-container');
                if (!container) return;
                container.innerHTML = '';

                try {
                    window.paypal.Buttons({
                        createOrder: async () => {
                            const body = new URLSearchParams();
                            body.set('action',   'bookingpress_paypal_booking_validate_lite');
                            body.set('_wpnonce', wpNonce);
                            body.set('appointment_data', JSON.stringify(state.appointment_step_form_data || {}));
                            try {
                                const resp = await fetch(ajaxUrl, {
                                    method:      'POST',
                                    credentials: 'same-origin',
                                    headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                                    body:        body.toString(),
                                });
                                const data = await resp.json();
                                if (data && data.variant !== 'error' && data.order_id) {
                                    state.paypal_success_url = data.paypal_success_url || '';
                                    state.paypal_cancel_url  = data.paypal_cancel_url  || '';
                                    state.paypal_booking_form_redirection_mode = data.paypal_booking_form_redirection_mode || '';
                                    return data.order_id;
                                }
                                bookingpress_set_error_msg((data && data.msg) || 'Failed to create PayPal order');
                                return 0;
                            } catch (e) {
                                bookingpress_set_error_msg('Failed to create PayPal order');
                                return 0;
                            }
                        },
                        onCancel: function () {},
                        onApprove: (data, actions) => {
                            return actions.order.capture().then((orderData) => {
                                state.paypal_button_loader = 'true';
                                const body = new URLSearchParams();
                                body.set('action',                   'bookingpress_paypal_booking_payment_confirm_lite');
                                body.set('_wpnonce',                 wpNonce);
                                body.set('bookingpress_payment_res', JSON.stringify(orderData));
                                return fetch(ajaxUrl, {
                                    method:      'POST',
                                    credentials: 'same-origin',
                                    headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                                    body:        body.toString(),
                                })
                                    .then((r) => r.json())
                                    .then((respData) => {
                                        setTimeout(() => { state.paypal_button_loader = 'false'; }, 500);
                                        if (respData && respData.variant !== 'error') {
                                            if (state.paypal_success_url) {
                                                window.location.href = state.paypal_success_url;
                                            }
                                        } else {
                                            bookingpress_set_error_msg((respData && respData.msg) || 'Payment failed');
                                        }
                                    })
                                    .catch(() => {
                                        setTimeout(() => { state.paypal_button_loader = 'false'; }, 500);
                                    });
                            });
                        },
                        style: { layout: 'vertical', color: 'gold', shape: 'pill', label: 'paypal', fundingicons: false },
                    }).render('#paypal-button-container');
                } catch (e) {
                    // SDK threw during init — fall back to default button
                    // by resetting the popup flag, so the user is never
                    // stranded with no way to submit.
                    state.show_paypal_popup_button = 'false';
                }
            }

            /**
             * Payment-method selection. Legacy parity (class.bookingpress_…:
             * 8900 + 700-818). Writes `selected_payment_method` and — when
             * popup mode is active — flips `show_paypal_popup_button` so
             * the default submit button hides and the PayPal SDK container
             * reveals. Non-PayPal selections restore the default button.
             */
            function select_payment_method(method) {
                const m = (method != null) ? String(method) : '';
                state.appointment_step_form_data.selected_payment_method = m;

                // Only flip the popup flag when popup mode is configured.
                // Other modes (redirect, etc.) keep the default button.
                if (state.paypal_payment_method_type === 'popup') {
                    if (m === 'paypal') {
                        state.show_paypal_popup_button = 'true';
                        // Defer SDK render to next tick so the container
                        // div the template gates behind `show_paypal_popup_button`
                        // has actually mounted before .render() targets it.
                        nextTick(() => { renderPayPalButtons(); });
                    } else {
                        state.show_paypal_popup_button = 'false';
                        const container = (typeof document !== 'undefined') ? document.getElementById('paypal-button-container') : null;
                        if (container) container.innerHTML = '';
                    }
                }
            }

            /**
             * POST helper — mirrors legacy `axios.post(ajax, Qs.stringify(...))`
             * with the standard admin-ajax form-encoded shape. Kept here so
             * `book_appointment` / `bookingpress_process_to_book_appointment`
             * share a single submission surface.
             */
            function bpaAjaxPost(action, extra) {
                const ajaxUrl = (instance && instance.ajaxUrl) ? instance.ajaxUrl : '';
                const nonce   = (instance && instance.nonce)   ? instance.nonce   : '';
                const body = new URLSearchParams();
                body.set('action', action);
                body.set('_wpnonce', nonce);
                // IMPORTANT — send the WHOLE appointment_step_form_data bucket
                // verbatim. Unknown addon-injected fields MUST be preserved.
                body.set('appointment_data', JSON.stringify(state.appointment_step_form_data || {}));
                if (extra && typeof extra === 'object') {
                    for (const k in extra) {
                        if (Object.prototype.hasOwnProperty.call(extra, k)) {
                            body.set(k, extra[k] == null ? '' : String(extra[k]));
                        }
                    }
                }
                return fetch(ajaxUrl, {
                    method:      'POST',
                    credentials: 'same-origin',
                    headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body:        body.toString(),
                }).then((resp) => resp.json());
            }

            /**
             * Always-on loader reset. Called from every terminal branch of
             * the submit flow so the button can never stay stuck disabled.
             */
            function releaseBookingLoader() {
                state.isLoadBookingLoader = '0';
                state.isBookingDisabled   = false;
            }

            /**
             * Inject server-returned HTML (PayPal redirect form) into the
             * `#bpa-external-script` container and run the trailing <script>
             * tag inline. Lite never returns `variant: redirect`, so this
             * path is dormant — the function exists purely to preserve
             * Pro wire-compatibility.
             *
             * Scope is tight on purpose: only HTML produced by the trusted
             * server-side gateway flow ever reaches this sink. No other
             * caller writes to `bookingpress_external_html`.
             */
            function runExternalRedirectScript() {
                setTimeout(() => {
                    const host = document.getElementById('bpa-external-script');
                    if (!host) return;
                    const scripts = host.querySelectorAll('script');
                    if (!scripts.length) return;
                    const text = scripts[scripts.length - 1].textContent;
                    if (text) {
                        // Legacy parity: `eval(text)` of the trailing script
                        // block emitted by the PayPal redirect form.
                        // eslint-disable-next-line no-eval
                        try { eval(text); } catch (e) { /* noop */ }
                    }
                }, 50);
            }

            /**
             * Final booking save — legacy `bookingpress_process_to_book_appointment`
             * (class.bookingpress_…:7811). Response branches:
             *   variant === 'redirect'     → inject redirect_data + run script
             *   variant === 'redirect_url' → window.location.href = redirect_data
             *   variant === 'error'        → toast
             *   default                    → clear error (thank-you flow
             *                                 belongs to Step 3G).
             *
             * The loader/disabled pair is reset in EVERY branch, including
             * network failures, so the button can never stay stuck.
             */
            function bookingpress_process_to_book_appointment() {
                if (state.is_display_error == '1') {
                    releaseBookingLoader();
                    return;
                }
                return bpaAjaxPost('bookingpress_front_save_appointment_booking')
                    .then((data) => {
                        releaseBookingLoader();
                        data = data || {};
                        if (data.variant === 'redirect') {
                            bookingpress_remove_error_msg();
                            state.bookingpress_external_html = data.redirect_data || '';
                            runExternalRedirectScript();
                        } else if (data.variant === 'redirect_url') {
                            bookingpress_remove_error_msg();
                            if (data.redirect_data) {
                                window.location.href = data.redirect_data;
                            }
                        } else if (data.variant === 'error') {
                            bookingpress_set_error_msg(data.msg || '');
                        } else {
                            bookingpress_remove_error_msg();
                        }
                        if (data.error_type === 'dayoff') {
                            state.service_timing = {
                                morning_time: [], afternoon_time: [],
                                evening_time: [], night_time: [],
                            };
                        }
                    })
                    .catch(() => {
                        // Network / parse failure. Release the loader and
                        // surface a generic error so the user can retry.
                        releaseBookingLoader();
                        bookingpress_set_error_msg(
                            (typeof window !== 'undefined' && window.navigator && !window.navigator.onLine)
                                ? 'Network unavailable. Please check your connection and try again.'
                                : 'Something went wrong. Please try again.'
                        );
                    });
            }

            /**
             * Entry point wired to the footer submit button. Legacy parity:
             * Lite skips the pre-book validation call (that action is Pro-
             * only; Lite does not register it). We set the loader flags
             * then dispatch straight to the save endpoint.
             *
             * Idempotent against rapid clicks — the second entry bails
             * because `isBookingDisabled` is already true.
             */
            function book_appointment() {
                if (state.isBookingDisabled) {
                    return; // duplicate-submit guard
                }
                state.isLoadBookingLoader = '1';
                state.isBookingDisabled   = true;
                bookingpress_process_to_book_appointment();
            }

            /**
             * Minimal tab transition — Step 3C/3D/3E scope.
             *
             * Kept synchronous (Step 3E ground rule). Basic-details → any
             * other forward tab validates first; going back never validates.
             */
            // Legacy parity (class.bookingpress_appointment_bookings.php L10207):
            //   bookingpress_step_navigation(current_tab, next_tab, previous_tab, is_strict_validate = 1)
            // The FIRST arg is the TARGET tab (legacy L10304 assigns
            // `vm.bookingpress_current_tab = current_tab`). The 2nd/3rd args
            // are the next/previous pointers the caller wants wired onto the
            // target step's state — they are NOT the target.
            //
            // Earlier Vue3 code treated the 2nd arg as the target, which
            // caused sidebar clicks and Basic Details "Go Back" to land on
            // the wrong tab (the user-reported Issues 4 & 5).
            function bookingpress_step_navigation(current_tab, next_tab, previous_tab, is_strict_validate) {
                const target = current_tab;
                if (!TABS.includes(target)) return;
                if (typeof is_strict_validate === 'undefined') is_strict_validate = 1;

                // Clear any stale top-of-panel error on each navigation
                // attempt; validators re-raise it below if they fail.
                bookingpress_remove_error_msg();

                // Gate: datetime requires a service.
                if (target === 'datetime'
                    && !state.appointment_step_form_data.selected_service) {
                    return;
                }
                // Gate: basic_details requires both date + time (Step 3D).
                if (target === 'basic_details'
                    && (!state.appointment_step_form_data.selected_date
                        || !state.appointment_step_form_data.selected_start_time)) {
                    return;
                }

                // Gate: leaving basic_details FORWARDS validates the form.
                // Going back (target === 'datetime' | 'service') never does.
                const current = state.bookingpress_current_tab;
                if (current === 'basic_details' && target === 'summary') {
                    const ok = validateBasicDetails();
                    if (!ok) {
                        // Stay on basic_details; toast is already set.
                        return;
                    }
                    state.is_basic_details_validated = true;
                    const basic = state.bookingpress_sidebar_step_data.basic_details;
                    if (basic) basic.is_allow_navigate = 1;
                }

                // Gate: summary entry also requires a prior successful
                // basic-details validation (covers sidebar-click entries
                // where the user jumps directly to summary from elsewhere).
                if (target === 'summary') {
                    if (!state.is_basic_details_validated) {
                        const basic = state.bookingpress_sidebar_step_data.basic_details;
                        if (!basic || basic.is_allow_navigate != 1) return;
                    }
                }

                // Mark the step we are LEAVING as allow-navigate (legacy
                // L10302 sets this before flipping current_tab, so when the
                // user clicks back the previously-visited step remains
                // reachable via the sidebar).
                const leavingStep = state.bookingpress_sidebar_step_data[current];
                if (leavingStep) leavingStep.is_allow_navigate = 1;

                state.bookingpress_current_tab = target;
                // Legacy L10305-10306 tracks the caller-supplied next/prev
                // on separate top-level vm vars (`bookingpress_next_tab`,
                // `bookingpress_previous_tab`) — it does NOT mutate the
                // per-step `next_tab_name` / `previous_tab_name` fields.
                // Mirror that: stash them on `state` without clobbering the
                // step-definition pointers, so Go Back wiring from one step
                // doesn't corrupt traversal from another.
                state.bookingpress_next_tab = (typeof next_tab !== 'undefined' && next_tab !== null) ? next_tab : '';
                state.bookingpress_previous_tab = (typeof previous_tab !== 'undefined' && previous_tab !== null) ? previous_tab : '';
                const step = state.bookingpress_sidebar_step_data[target];
                if (step) {
                    step.is_allow_navigate = 1;
                }
            }

            // Focus helpers — port of legacy keyboard navigation
            // (class.bookingpress_…:7946-8232). The Vue3 markup keeps the
            // legacy roving-tabindex pattern: each group wrapper has
            // `tabindex="0"` with `@focus` programmatically forwarding to
            // its first child item, and arrow keys move focus between
            // items. Items themselves carry `tabindex="-1"` so a single
            // Tab press lands inside the group, then arrows take over.
            function bpaScopedRoot() {
                if (typeof document === 'undefined' || !instance || !instance.instanceId) return null;
                return document.getElementById(ROOT_ID_PREFIX + instance.instanceId);
            }
            function focustFirstCategory() {
                nextTick(() => {
                    const root = bpaScopedRoot();
                    if (!root) return;
                    const item = root.querySelector('.bpa-front-cat-items-wrapper .bpa-front-ci-pill');
                    if (item) try { item.focus({ preventScroll: true }); } catch (e) { item.focus(); }
                });
            }
            function focusFirstService() {
                nextTick(() => {
                    const root = bpaScopedRoot();
                    if (!root) return;
                    const items = root.querySelectorAll('.bpa-front-module--service .bpa-fm--si--col:not([style*="display: none"]) .bpa-front-si-card');
                    if (items.length) try { items[0].focus({ preventScroll: true }); } catch (e) { items[0].focus(); }
                });
            }
            function focusFirstTimeSlot() {
                nextTick(() => {
                    const root = bpaScopedRoot();
                    if (!root) return;
                    const item = root.querySelector('.bpa-front--dt__ts-body .bpa-front--dt__ts-body--item:not(.__bpa-is-disabled)')
                              || root.querySelector('.bpa-front--dt__ts-body .bpa-front--dt__ts-body--item');
                    if (item) try { item.focus({ preventScroll: true }); } catch (e) { item.focus(); }
                });
            }
            function focusFirstPaymentGateway() {
                nextTick(() => {
                    const root = bpaScopedRoot();
                    if (!root) return;
                    const item = root.querySelector('.bpa-front-module--payment-methods .bpa-front-module--pm-body__item');
                    if (item) try { item.focus({ preventScroll: true }); } catch (e) { item.focus(); }
                });
            }
            function bpaArrowMoveFocus(items, currentIndex, direction) {
                if (!items || !items.length) return;
                const len = items.length;
                let nextIndex = (currentIndex + direction + len) % len;
                try { items[nextIndex].focus({ preventScroll: true }); } catch (e) { items[nextIndex].focus(); }
            }
            function bpa_handle_category_keypress(index, event) {
                const root = bpaScopedRoot();
                if (!root) return;
                const items = root.querySelectorAll('.bpa-front-cat-items-wrapper .bpa-front-ci-pill');
                if (!items.length) return;
                let currentIndex = -1;
                for (let i = 0; i < items.length; i++) {
                    if (items[i] === document.activeElement) { currentIndex = i; break; }
                }
                if (currentIndex === -1) return;
                const k = event.key;
                if (k === 'Enter' || k === ' ' || k === 'Spacebar') {
                    event.preventDefault();
                    if (typeof event.target.click === 'function') event.target.click();
                } else if (k === 'ArrowRight' || k === 'ArrowDown') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, +1);
                } else if (k === 'ArrowLeft' || k === 'ArrowUp') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, -1);
                }
            }
            function bpa_handle_service_keypress(index, event) {
                const root = bpaScopedRoot();
                if (!root) return;
                const items = root.querySelectorAll('.bpa-front-module--service .bpa-fm--si--col:not([style*="display: none"]) .bpa-front-si-card');
                if (!items.length) return;
                let currentIndex = -1;
                for (let i = 0; i < items.length; i++) {
                    if (items[i] === document.activeElement) { currentIndex = i; break; }
                }
                if (currentIndex === -1) return;
                const k = event.key;
                let itemsPerRow = 2;
                if (typeof window !== 'undefined' && window.innerWidth < 950) itemsPerRow = 1;
                if (k === 'Enter' || k === ' ' || k === 'Spacebar') {
                    event.preventDefault();
                    if (typeof event.target.click === 'function') event.target.click();
                } else if (k === 'ArrowRight') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, +1);
                } else if (k === 'ArrowLeft') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, -1);
                } else if (k === 'ArrowDown') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, +itemsPerRow);
                } else if (k === 'ArrowUp') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, -itemsPerRow);
                }
            }
            function bpa_handle_timeslot_keypress(event) {
                const root = bpaScopedRoot();
                if (!root) return;
                const items = root.querySelectorAll('.bpa-front--dt__ts-body .bpa-front--dt__ts-body--item');
                if (!items.length) return;
                let currentIndex = -1;
                for (let i = 0; i < items.length; i++) {
                    if (items[i] === document.activeElement) { currentIndex = i; break; }
                }
                if (currentIndex === -1) return;
                const k = event.key;
                const itemsPerRow = 2;
                if (k === 'ArrowRight') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, +1);
                } else if (k === 'ArrowLeft') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, -1);
                } else if (k === 'ArrowDown') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, +itemsPerRow);
                } else if (k === 'ArrowUp') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, -itemsPerRow);
                }
            }
            function bpa_handle_pg_keypress(event) {
                const root = bpaScopedRoot();
                if (!root) return;
                const items = root.querySelectorAll('.bpa-front-module--payment-methods .bpa-front-module--pm-body__item');
                if (!items.length) return;
                let currentIndex = -1;
                for (let i = 0; i < items.length; i++) {
                    if (items[i] === document.activeElement) { currentIndex = i; break; }
                }
                if (currentIndex === -1) return;
                const k = event.key;
                if (k === 'Enter' || k === ' ' || k === 'Spacebar') {
                    event.preventDefault();
                    if (typeof event.target.click === 'function') event.target.click();
                } else if (k === 'ArrowRight' || k === 'ArrowDown') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, +1);
                } else if (k === 'ArrowLeft' || k === 'ArrowUp') {
                    event.preventDefault();
                    bpaArrowMoveFocus(items, currentIndex, -1);
                }
            }

            const ready            = ref(false);
            const calendarHost     = ref(null);
            const vcalendarBridge  = { app: null, vm: null };

            // v-calendar v3 shipped in bp-vcalendar.js uses Date objects for
            // its `v-model` and `disabled-dates` entries. Legacy strings
            // ("YYYY-MM-DD") must be converted to Date instances or the
            // values are silently dropped. These tiny helpers localize that.
            function ymdToDate(v) {
                if (!v) return null;
                if (v instanceof Date) return isNaN(v.getTime()) ? null : v;
                const s = String(v).slice(0, 10);
                if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) {
                    const d = new Date(v);
                    return isNaN(d.getTime()) ? null : d;
                }
                const parts = s.split('-');
                return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            }
            function mapYmdToDate(list) {
                const out = [];
                if (!Array.isArray(list)) return out;
                for (let i = 0; i < list.length; i++) {
                    const d = ymdToDate(list[i]);
                    if (d) out.push(d);
                }
                return out;
            }

            // WordPress ships locale as `en_US` / `fr_FR` / `pt_BR` /
            // `zh_CN` etc. `Intl.DateTimeFormat` — which v-calendar v3's
            // DatePicker setup uses — is strict BCP 47 and REJECTS
            // underscores (`new Intl.DateTimeFormat('en_US')` throws
            // `RangeError: invalid language tag`). When the DatePicker's
            // setup throws, its `provide()` call for the DatePicker
            // context never runs, and downstream DatePickerBase/Pages
            // injection fails with:
            //   "DatePicker context missing..."
            //   "can't access property modelValue, a.value is undefined"
            // So we MUST normalize before handing locale to VCalendar.
            function normalizeLocale(value) {
                if (!value) return 'en';
                const s = String(value).replace(/_/g, '-').trim();
                // Guard against a bad/unknown tag so one oddly-formatted
                // site_locale can never crash the picker again.
                try {
                    // Will throw RangeError on an invalid tag.
                    new Intl.DateTimeFormat(s);
                    return s;
                } catch (e) {
                    return 'en';
                }
            }

            /**
             * Mount the secondary VCalendar Vue app (its own Vue runtime)
             * into the datetime tab's calendar host div.
             *
             * ROOT CAUSE of the prior blank-calendar bug:
             *   The Vue runtime bundled inside bp-vcalendar.js is the
             *   RUNTIME-ONLY build (no template compiler). Passing
             *   `{ template: '<div><VDatePicker/></div>' }` to createApp
             *   silently produced an app with no render function, so the
             *   mini-app mounted but rendered nothing. The bundle literally
             *   contains the warning string "Component provided template
             *   option but runtime compilation is not supported" — but
             *   prod-mode warnings are stripped, so it failed invisibly.
             *
             * FIX: use the bridge's `mountDatePicker(host, props, handlers)`
             * factory, which builds the mini-app with an options-API
             * component whose `render()` calls `h(DatePicker, props)`
             * directly. Runtime compilation is never invoked.
             *
             * Other v-calendar v3 gotchas already handled:
             *   • `vc-light` class is now on the host div (templated at
             *     markup level) so CSS vars resolve.
             *   • `available-dates` / `model-config` props were removed
             *     in v3 — whitelisting is enforced by dayClicked() instead.
             *   • v-model uses a Date object, not a YYYY-MM-DD string.
             */
            function mountVCalendar() {
                if (vcalendarBridge.vm) return;
                const host = calendarHost.value;
                const Bridge = (typeof window !== 'undefined') ? window.BpVCalendar : null;
                if (!host || !Bridge || typeof Bridge.mountDatePicker !== 'function') return;

                const minDate = state.bookingpress_site_date
                    ? ymdToDate(state.bookingpress_site_date)
                    : new Date();
                const maxDate = state.booking_cal_maxdate
                    ? ymdToDate(state.booking_cal_maxdate)
                    : new Date(minDate.getFullYear() + 2, minDate.getMonth(), minDate.getDate());

                // Props for the <VDatePicker> root. Keys are camelCase
                // because they go through `h()`, not a template parser.
                //
                // Legacy parity (issue 3 — calendar header/row alignment):
                // VCalendar v3 defaults to narrow single-letter weekday labels
                // ("M T W T F S S") and a smaller title, which compresses the
                // calendar column and makes it visually uneven vs the time-slot
                // column. Legacy (VCalendar v2) rendered 3-letter weekdays and a
                // larger "April 2026" title. We force that format here via the
                // `masks` prop so the calendar matches the legacy footprint.
                const initialProps = {
                    mode:            'date',
                    modelValue:      ymdToDate(state.appointment_step_form_data.selected_date || ''),
                    minDate:         minDate,
                    maxDate:         maxDate,
                    disabledDates:   mapYmdToDate(state.v_calendar_blocked_dates || []),
                    firstDayOfWeek:  parseInt(state.first_day_of_week, 10) || 1,
                    locale:          normalizeLocale(state.site_locale),
                    isRequired:      true,
                    class:           'bpa-front-v-date-picker',
                    masks:           {
                        // 'WWW' = 3-letter weekday (Mon, Tue, …), 'WW' =
                        // 2-letter. Legacy used the 3-letter form.
                        weekdays: 'WWW',
                        // Title such as "April 2026". v3's default 'MMMM YYYY'
                        // is the same string — re-stated explicitly so any
                        // future VCalendar default drift doesn't regress us.
                        title: 'MMMM YYYY',
                    },
                };

                const handlers = {
                    onUpdate(v) {
                        // v-model update from picker. In v-calendar v3 the
                        // `available-dates` prop (the v2 whitelist) was
                        // removed, so disabled Saturdays / Sundays / past
                        // max-date days remain visually greyed but still
                        // emit model updates when clicked. Gate here using
                        // the exact dayClicked() rules; on rejection, push
                        // the previously-accepted date back into the
                        // picker so the failed click doesn't leave the
                        // rejected day visually selected.
                        if (!(v instanceof Date) || isNaN(v.getTime())) {
                            const prev = state.appointment_step_form_data.selected_date;
                            if (vcalendarBridge.vm) {
                                vcalendarBridge.vm.p.modelValue = prev ? ymdToDate(prev) : null;
                            }
                            return;
                        }
                        const y = v.getFullYear();
                        const m = String(v.getMonth() + 1).padStart(2, '0');
                        const d = String(v.getDate()).padStart(2, '0');
                        const ymd = `${y}-${m}-${d}`;

                        const available = Array.isArray(state.v_calendar_available_dates) ? state.v_calendar_available_dates : [];
                        const blocked   = Array.isArray(state.v_calendar_blocked_dates)   ? state.v_calendar_blocked_dates   : [];
                        const isAvail   = available.indexOf(ymd + ' 00:00:00') >= 0 || available.indexOf(ymd) >= 0;
                        const isBlocked = blocked.indexOf(ymd) > -1;
                        const pastMax   = !!(state.booking_cal_maxdate && ymd > String(state.booking_cal_maxdate).slice(0, 10));
                        if (!isAvail || isBlocked || pastMax) {
                            // Revert picker to the previously-accepted date.
                            const prev = state.appointment_step_form_data.selected_date;
                            setTimeout(() => {
                                if (vcalendarBridge.vm) {
                                    vcalendarBridge.vm.p.modelValue = prev ? ymdToDate(prev) : null;
                                }
                            }, 0);
                            return;
                        }

                        dayClicked({
                            id:   ymd,
                            date: v,
                            year: y,
                            month: v.getMonth() + 1,
                            day:  v.getDate(),
                        });
                    },
                    onDayClick(day) {
                        dayClicked(day);
                    },
                    onMonthPage(page) {
                        if (!page) return;
                        // `did-move` in v-calendar v3 fires with an array
                        // of page objects. Normalize to a single page.
                        const p = Array.isArray(page) ? page[0] : page;
                        if (!p) return;
                        bpaMoveMonth({
                            month: parseInt(p.month, 10),
                            year:  parseInt(p.year,  10),
                        });
                    },
                };

                const mounted = Bridge.mountDatePicker(host, initialProps, handlers);
                if (!mounted) return;
                vcalendarBridge.app = mounted.app;
                vcalendarBridge.vm  = mounted.vm;

                // Push updates from parent → mini-app reactive props.
                // `vm.p` is the reactive prop bag managed by the bridge.
                watch(
                    () => (state.v_calendar_blocked_dates || []).slice(),
                    (next) => {
                        if (!vcalendarBridge.vm) return;
                        vcalendarBridge.vm.p.disabledDates = mapYmdToDate(next);
                    }
                );
                watch(
                    () => state.booking_cal_maxdate,
                    (next) => {
                        if (!vcalendarBridge.vm || !next) return;
                        const d = ymdToDate(next);
                        if (d) vcalendarBridge.vm.p.maxDate = d;
                    }
                );
                watch(
                    () => state.appointment_step_form_data.selected_date,
                    (next) => {
                        if (!vcalendarBridge.vm) return;
                        vcalendarBridge.vm.p.modelValue = next ? ymdToDate(next) : null;
                    }
                );
            }

            // Legacy parity: VCalendar v2 stamped `.is-disabled` on each
            // `.vc-day-content` that was outside `available-dates` or inside
            // `disabled-dates`. VCalendar v3 dropped the `available-dates`
            // prop and applies disabled state inconsistently for future
            // off-days that weren't in the initially-fetched server window.
            //
            // This sweep mirrors legacy's final DOM shape by:
            //   (a) For every `.vc-day-content` under the calendar host,
            //       read its `data-date` attribute (YYYY-MM-DD).
            //   (b) If the date is NOT in `v_calendar_available_dates` OR
            //       IS in `v_calendar_blocked_dates`, stamp `.is-disabled`
            //       and set `aria-disabled="true"` — matching legacy v2.
            //   (c) Otherwise remove the class so the element becomes
            //       selectable again (e.g. after async month-fetch fills
            //       new available_dates).
            //
            // Runs after each calendar render via MutationObserver, after
            // each month-page change, and after server state updates.
            function normalizeDisabledDayMarkers() {
                const host = calendarHost.value;
                if (!host) return;
                const available = Array.isArray(state.v_calendar_available_dates)
                    ? state.v_calendar_available_dates : [];
                const blocked = Array.isArray(state.v_calendar_blocked_dates)
                    ? state.v_calendar_blocked_dates : [];

                // Normalize available into a Set of YYYY-MM-DD for O(1) lookup.
                const availSet = Object.create(null);
                for (let i = 0; i < available.length; i++) {
                    const v = available[i];
                    if (typeof v !== 'string' || !v) continue;
                    availSet[v.slice(0, 10)] = true;
                }
                const blockedSet = Object.create(null);
                for (let j = 0; j < blocked.length; j++) {
                    const v = blocked[j];
                    if (typeof v !== 'string' || !v) continue;
                    blockedSet[v.slice(0, 10)] = true;
                }

                // Only enforce the `available-dates` whitelist if we actually
                // have a non-empty list (otherwise every cell would be stamped
                // disabled before the initial fetch completes).
                const hasAvail = Object.keys(availSet).length > 0;

                const days = host.querySelectorAll('.vc-day');
                for (let k = 0; k < days.length; k++) {
                    const day = days[k];
                    const contentEls = day.querySelectorAll('.vc-day-content');
                    if (!contentEls.length) continue;
                    // `data-date` was set by VCalendar v2; v3 uses its own
                    // storage but exposes the YMD via `data-date` as well.
                    // Fall back to the day cell's id class (`id-YYYY-MM-DD`).
                    let ymd = day.getAttribute('data-date') || '';
                    if (!ymd) {
                        const cls = day.className || '';
                        const m = cls.match(/\bid-(\d{4}-\d{2}-\d{2})\b/);
                        if (m) ymd = m[1];
                    }
                    if (!ymd) continue;
                    ymd = ymd.slice(0, 10);

                    let shouldDisable = false;
                    if (blockedSet[ymd]) shouldDisable = true;
                    if (hasAvail && !availSet[ymd]) shouldDisable = true;
                    // Honor VCalendar v3's own disabled markers (min/max).
                    for (let c = 0; c < contentEls.length; c++) {
                        if (contentEls[c].classList.contains('vc-disabled')
                            || contentEls[c].getAttribute('aria-disabled') === 'true') {
                            shouldDisable = true;
                            break;
                        }
                    }

                    for (let c = 0; c < contentEls.length; c++) {
                        const el = contentEls[c];
                        if (shouldDisable) {
                            if (!el.classList.contains('is-disabled')) {
                                el.classList.add('is-disabled');
                            }
                            if (el.getAttribute('aria-disabled') !== 'true') {
                                el.setAttribute('aria-disabled', 'true');
                            }
                        } else {
                            if (el.classList.contains('is-disabled')) {
                                el.classList.remove('is-disabled');
                            }
                            if (el.getAttribute('aria-disabled') === 'true') {
                                el.setAttribute('aria-disabled', 'false');
                            }
                        }
                    }
                }
            }

            // Wire a MutationObserver to the calendar host so the sweep
            // re-runs whenever VCalendar v3 re-renders (month nav, model
            // change, async prop update). Installed once on mount.
            let _disabledMarkerObserver = null;
            function ensureDisabledMarkerObserver() {
                const host = calendarHost.value;
                if (!host || _disabledMarkerObserver) return;
                if (typeof MutationObserver !== 'function') return;
                let rafHandle = 0;
                _disabledMarkerObserver = new MutationObserver(() => {
                    if (rafHandle) return;
                    rafHandle = (typeof requestAnimationFrame === 'function')
                        ? requestAnimationFrame(() => {
                            rafHandle = 0;
                            normalizeDisabledDayMarkers();
                        })
                        : setTimeout(() => {
                            rafHandle = 0;
                            normalizeDisabledDayMarkers();
                        }, 0);
                });
                _disabledMarkerObserver.observe(host, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'aria-disabled'],
                });
            }

            // Lazy-mount the calendar when the datetime tab first becomes
            // active. Double-tick + rAF gives Vue's reactive style update
            // time to flip the panel to display:block before VCalendar
            // measures layout (it bails out on display:none containers).
            function scheduleMountVCalendar() {
                nextTick(() => {
                    nextTick(() => {
                        if (typeof requestAnimationFrame === 'function') {
                            requestAnimationFrame(() => {
                                mountVCalendar();
                                ensureDisabledMarkerObserver();
                                normalizeDisabledDayMarkers();
                            });
                        } else {
                            mountVCalendar();
                            ensureDisabledMarkerObserver();
                            normalizeDisabledDayMarkers();
                        }
                    });
                });
            }
            watch(
                () => state.bookingpress_current_tab,
                (tab) => {
                    if (tab === 'datetime') scheduleMountVCalendar();
                    // Issue 2 — auto-focus the first input on Basic Details
                    // step entry (cursor lands in firstname after stepping
                    // forward from Date & Time).
                    if (tab === 'basic_details') focusBasicDetailsFirstField();
                    // Pre-select the only configured payment method on
                    // Summary entry, AND fire `select_payment_method()` so
                    // gateway-specific side effects (PayPal SDK button
                    // render under popup mode) run automatically — legacy
                    // parity with the auto-click block at L10342-10349 of
                    // class.bookingpress_appointment_bookings.php.
                    if (tab === 'summary') {
                        const only = singlePaymentMethod.value;
                        if (only) {
                            // Clear any stale selection that would prevent
                            // the SDK render from re-running on re-entry.
                            const cur = state.appointment_step_form_data.selected_payment_method;
                            // Always re-fire so PayPal popup re-mounts the
                            // SDK buttons after each summary entry. The
                            // function itself is idempotent for on-site.
                            nextTick(() => {
                                select_payment_method(only);
                                if (only !== cur) {
                                    state.appointment_step_form_data.selected_payment_method = only;
                                }
                            });
                        }
                    }
                }
            );

            // Issue 3 — guarantee per-field validation errors clear as the
            // user fills the fields, regardless of which event the
            // underlying input component emits. The template-level
            // `@input` handler covers native `bp-ui-input` and
            // `bp-ui-tel-input`, but a deep watcher on the form-data
            // object is the belt-and-braces fallback: every time any
            // tracked key transitions from empty/falsy to a non-empty
            // value, the matching error in `basicDetailsErrors` is
            // dropped. Using `flush: 'post'` so the input value has been
            // committed by Vue before we re-evaluate.
            watch(
                () => state.appointment_step_form_data,
                (data) => {
                    if (!basicDetailsErrors.value) return;
                    const errKeys = Object.keys(basicDetailsErrors.value);
                    if (errKeys.length === 0) return;
                    let changed = false;
                    const next = Object.assign({}, basicDetailsErrors.value);
                    for (let i = 0; i < errKeys.length; i++) {
                        const k = errKeys[i];
                        const v = data ? data[k] : null;
                        const filled =
                            (v != null)
                            && (typeof v !== 'string' || v.trim() !== '')
                            && (!Array.isArray(v) || v.length > 0)
                            && (typeof v !== 'boolean' || v === true);
                        if (filled) {
                            delete next[k];
                            changed = true;
                        }
                    }
                    if (changed) basicDetailsErrors.value = next;
                },
                { deep: true, flush: 'post' }
            );

            // ---- Lifecycle ----
            onMounted(() => {
                // URL failsafe: if ?s_id or ?bpservice_id is in the URL and
                // PHP didn't seed state (tenant DB mismatch, etc.), mirror
                // the legacy force-flags client-side so the datetime tab
                // still takes over.
                try {
                    const usp = new URLSearchParams(window.location.search);
                    const urlSid = usp.get('s_id') || usp.get('bpservice_id');
                    if (urlSid) {
                        // Legacy parity: URL-loaded services set
                        // `is_service_loaded_from_url = '1'` which causes the
                        // onload switcher to jump to the datetime tab. We do
                        // NOT also force `hide_category_service = '1'` —
                        // legacy leaves `hide_category_service` mirroring the
                        // admin setting, so the sidebar Service step and the
                        // datetime Go Back button remain visible.
                        state.is_service_loaded_from_url = '1';
                        if (!state.appointment_step_form_data.selected_service) {
                            state.appointment_step_form_data.selected_service = String(urlSid);
                        }
                    }
                } catch (e) { /* noop */ }

                // Legacy mounted() → bpa_select_category(selected_category).
                const seeded = state.appointment_step_form_data.selected_category;
                bpa_select_category(seeded || 0, state.appointment_step_form_data.selected_cat_name || '');

                // If the shortcode preselected a concrete service, legacy code
                // (line 7422) calls selectDate(..., "false") then auto-advances
                // to datetime when hide_category_service / is_service_loaded_from_url
                // are truthy (line 7436). We must (a) clear selected_service
                // BEFORE calling selectService so the prevSid !== sid branch
                // runs and fires fetchAvailableDates, (b) advance the tab, and
                // (c) mount the calendar on next tick.
                const seededService = state.appointment_step_form_data.selected_service;
                if (seededService) {
                    const seededName  = state.appointment_step_form_data.selected_service_name;
                    const seededPrice = state.appointment_step_form_data.selected_service_price;
                    const seededPwc   = state.appointment_step_form_data.service_price_without_currency;
                    const seededDur   = state.appointment_step_form_data.selected_service_duration;
                    const seededDurU  = state.appointment_step_form_data.selected_service_duration_unit;

                    state.appointment_step_form_data.selected_service = '';

                    selectService(
                        seededService,
                        seededName,
                        seededPrice,
                        seededPwc,
                        'false',
                        seededDur,
                        seededDurU
                    );

                    if (state.hide_category_service == '1'
                        || state.is_service_loaded_from_url == '1') {
                        state.bookingpress_current_tab = 'datetime';
                        const dt = state.bookingpress_sidebar_step_data.datetime;
                        if (dt) dt.is_allow_navigate = 1;
                        scheduleMountVCalendar();
                    }
                }

                // Legacy parity: fire the spam-captcha seed on mount —
                // `loadSpamProtection()` at L5997 of
                // class.bookingpress_appointment_bookings.php. Populates
                // `appointment_step_form_data.spam_captcha` so the final
                // booking submit passes the server-side spam check.
                try { loadSpamProtection(); } catch (_) { /* noop */ }

                state.is_booking_form_empty_loader = '0';
                ready.value = true;
            });

            // ---- Provide per-instance context ----
            provide('bpInstance', instance);
            provide('bpState', state);
            provide('bpTabs', { list: TABS, current: currentTab, isTab });
            provide('bpComputed', { bpasortedServices, selectedServiceDetails, categoriesWithAll, customerDisplayName, isFreeService, showPaymentMethods, hasAnyPaymentGateway });
            provide('bpMethods', {
                bpa_select_category,
                selectService,
                selectDate,
                bookingpress_step_navigation,
                dayClicked,
                selectTiming,
                fetchAvailableDates,
                fetchTimeslots,
                // Step 3F — Summary.
                select_payment_method,
                book_appointment,
                bookingpress_process_to_book_appointment,
            });
            provide('bpHelpers', { formatDate, formatTime, formatPrice });

            const str = (key, fallback) => {
                const s = state.strings || {};
                return (s && s[key]) ? s[key] : (fallback || '');
            };

            return {
                state,
                currentTab,
                isTab,
                ready,
                instanceId: (instance && instance.instanceId) || '',
                categoriesWithAll,
                bpasortedServices,
                selectedServiceDetails,
                bpa_select_category,
                selectService,
                selectDate,
                bookingpress_step_navigation,
                dayClicked,
                selectTiming,
                fetchAvailableDates,
                fetchTimeslots,
                calendarHost,
                focustFirstCategory,
                focusFirstService,
                focusFirstTimeSlot,
                focusFirstPaymentGateway,
                bpa_handle_category_keypress,
                bpa_handle_service_keypress,
                bpa_handle_timeslot_keypress,
                bpa_handle_pg_keypress,
                formatDate,
                formatTime,
                formatPrice,
                formatBookedDate,
                str,
                // Legacy parity: SVG markup for the datetime loader and the
                // "no time slots available" illustration. Exposed so the
                // template can inject them with v-html, matching the legacy
                // template's inline SVGs exactly.
                LOADER_SVG,
                NO_SLOTS_SVG,
                EMPTY_VIEW_SVG,
                SUMMARY_HEAD_SVG,
                // Mobile (≤576px) "date trigger" flow — legacy `.__sm` parity.
                selectedDateLabel,
                openResponsiveCalendar,
                // Step 3E — Basic Details.
                formRef,
                basicDetailsErrors,
                validateBasicDetails,
                clearBasicDetailsFieldError,
                bookingpress_remove_error_msg,
                bookingpress_set_error_msg,
                onPhoneCountryChanged,
                // Step 3F — Summary.
                customerDisplayName,
                isFreeService,
                showPaymentMethods,
                hasAnyPaymentGateway,
                singlePaymentMethod,
                showPaymentMethodPicker,
                select_payment_method,
                book_appointment,
                bookingpress_process_to_book_appointment,
            };
        },

        template: `
            <div
                class="bpa-frontend-vue3-root"
                :data-instance="instanceId"
                :data-current-tab="state.bookingpress_current_tab"
            >
                <!-- Legacy parity shell: #bpa-front-tabs + orientation modifier.
                     Required so bookingpress_front.css + dynamic customize CSS
                     target the expected ancestor selectors, and so the sidebar
                     tab menu has somewhere to live.

                     Legacy parity (appointment_booking_form.php:49): the
                     entire tab shell — sidebar, panels, and footer — is
                     suppressed when there are no services to book, and the
                     empty-state illustration below is shown instead. -->
                <div
                    v-if="!state.bookingpress_display_no_service_placeholder"
                    id="bpa-front-tabs"
                    class="bpa-front-tabs bpa-front-tabs--vertical-left"
                    style="display: flex"
                    :class="state.bookingpress_tabs_position === 'left' ? 'bpa-front-tabs--left' : '--bpa-top'"
                >
                    <!-- ============ SIDEBAR STEP MENU (legacy parity) ============ -->
                    <div class="bpa-front-tab-menu">
                        <template v-for="(sidebar_step, step_key) in state.bookingpress_sidebar_step_data" :key="step_key">
                            <a
                                href="javascript:void(0)"
                                v-if="sidebar_step.is_display_step == 1 || typeof sidebar_step.is_display_step === 'undefined'"
                                class="bpa-front-tab-menu--item bpa_focusable"
                                :class="[
                                    (state.bookingpress_current_tab == sidebar_step.tab_value) ? '__bpa-is-active' : '',
                                    (sidebar_step.is_allow_navigate == 0) ? 'bpa-front-disabled-menu-item' : ''
                                ]"
                                @click="bookingpress_step_navigation(sidebar_step.tab_value, sidebar_step.next_tab_name, sidebar_step.previous_tab_name)"
                            >
                                <span class="bpa-front-tm--item-icon material-icons-round" v-html="sidebar_step.tab_icon"></span>
                                <div class="bpa-front-tm--item-label">{{ sidebar_step.tab_name }}</div>
                            </a>
                        </template>
                    </div>

                    <!-- ============ SERVICE TAB (Step 3C) ============ -->
                    <!-- Legacy parity (core/views/frontend/appointment_booking_form.php L57):
                         no \`__service\` class modifier; the entire panel is hidden
                         (not just its body) when \`hide_category_service == '1'\`. -->
                    <div
                        class="bpa-front-tabs--panel-body"
                        :class="{ '__bpa-is-active': isTab('service') }"
                        :style="{ display: isTab('service') ? 'block' : 'none', width: '100%' }"
                        data-tab="service"
                        v-if="state.hide_category_service != '1'"
                    >
                    <div class="bpa-front-default-card">
                        <div
                            class="bpa-front-toast-notification --bpa-error"
                            v-if="state.is_display_error == '1'"
                            :aria-label="state.is_error_msg"
                        >
                            <div class="bpa-front-tn-body">
                                <p>{{ state.is_error_msg }}</p>
                            </div>
                        </div>

                        <div class="bpa-front-dc--body">
                            <!-- Category + service picker — legacy shows this unconditionally
                                 within the service panel (the panel itself is hidden via v-if
                                 when hide_category_service == '1', matching legacy L57).
                                 NOTE: no outer bare \`<template>\` here — Vue 3 compiles a
                                 directive-less \`<template>\` into an inert HTML <template>
                                 element, hiding its children. Keep the two rows as direct
                                 siblings of \`.bpa-front-dc--body\`. -->
                                <!-- (2) Category pills via categoriesWithAll (guaranteed All pill) -->
                                <bp-ui-row v-if="!state.hide_category_selection">
                                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <div class="bpa-front-module-container bpa-front-module--category">
                                            <bp-ui-row>
                                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                    <div
                                                        class="bpa-front-module-heading"
                                                        :aria-label="str('category_title', 'Select Category')"
                                                    >
                                                        {{ str('category_title', 'Select Category') }}
                                                    </div>
                                                </bp-ui-col>
                                            </bp-ui-row>
                                            <bp-ui-row>
                                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                    <div class="bpa-front-cat-items-wrapper">
                                                        <div
                                                            class="bpa-front-cat-items"
                                                            data-group="category"
                                                            role="toolbar"
                                                            tabindex="0"
                                                            @focus="focustFirstCategory()"
                                                        >
                                                            <template v-for="(cat_data, cat_index) in categoriesWithAll" :key="cat_data.category_id">
                                                                <!-- Legacy parity (core/views/frontend/appointment_booking_form.php L78):
                                                                     plain \`<span>\` carrying the \`el-tag el-tag--light\` classes so the
                                                                     existing Element-Plus tag CSS applies verbatim. Using \`<bp-ui-tag>\`
                                                                     here produced a different DOM shape and lost the pill styling. -->
                                                                <span
                                                                    v-if="typeof cat_data.is_visible === 'undefined' || cat_data.is_visible === true"
                                                                    class="bpa-front-ci-pill el-tag el-tag--light bpa_focusable"
                                                                    :class="{ '__bpa-is-active': String(state.appointment_step_form_data.selected_category || '0') === String(cat_data.category_id) }"
                                                                    role="button"
                                                                    tabindex="-1"
                                                                    @click="bpa_select_category(cat_data.category_id, cat_data.category_name, cat_data.total_services)"
                                                                    @keypress.enter="bpa_select_category(cat_data.category_id, cat_data.category_name, cat_data.total_services)"
                                                                    @keydown="bpa_handle_category_keypress(cat_index, $event)"
                                                                >
                                                                    <div class="bpa-front-ci-item-title" :aria-label="cat_data.category_name">
                                                                        {{ cat_data.category_name }}
                                                                    </div>
                                                                    <svg
                                                                        v-if="String(state.appointment_step_form_data.selected_category || '0') === String(cat_data.category_id)"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        viewBox="0 0 24 24"
                                                                        aria-hidden="true"
                                                                    >
                                                                        <path d="M0 0h24v24H0V0z" fill="none"/>
                                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/>
                                                                    </svg>
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </bp-ui-col>
                                            </bp-ui-row>
                                        </div>
                                    </bp-ui-col>
                                </bp-ui-row>

                                <!-- Service grid -->
                                <bp-ui-row>
                                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <div class="bpa-front-module-container bpa-front-module--service">
                                            <bp-ui-row>
                                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                    <div
                                                        class="bpa-front-module-heading"
                                                        :aria-label="str('service_heading_title', 'Select Service')"
                                                    >
                                                        {{ str('service_heading_title', 'Select Service') }}
                                                    </div>
                                                </bp-ui-col>
                                            </bp-ui-row>
                                            <div
                                                class="bpa-front-module--service-items-row"
                                                data-group="services"
                                                role="list"
                                                tabindex="0"
                                                @focus="focusFirstService"
                                            >
                                                <template v-for="(service_details, service_index) in bpasortedServices" :key="service_details.bookingpress_service_id">
                                                    <div
                                                        class="bpa-fm--si--col"
                                                        v-if="state.isLoadClass == 1 && service_details.is_visible === true"
                                                    >
                                                        <div
                                                            class="bpa-front-module--service-item"
                                                            role="listitem"
                                                            :class="{
                                                                '__bpa-is-selected': String(state.appointment_step_form_data.selected_service) === String(service_details.bookingpress_service_id),
                                                                '__bpa-is-description-enable': state.display_service_description == 1 && service_details.bookingpress_service_description
                                                            }"
                                                        >
                                                            <div
                                                                class="bpa-front-si-card bpa_focusable"
                                                                tabindex="-1"
                                                                role="button"
                                                                @keydown.enter="selectService(service_details.bookingpress_service_id, service_details.bookingpress_service_name, service_details.bookingpress_service_price, service_details.service_price_without_currency, 'true')"
                                                                @keydown="bpa_handle_service_keypress(service_index, $event)"
                                                                @click="selectService(service_details.bookingpress_service_id, service_details.bookingpress_service_name, service_details.bookingpress_service_price, service_details.service_price_without_currency, 'true')"
                                                            >
                                                                <div class="bpa-front-si-card--checkmark-icon" v-if="(String(state.appointment_step_form_data.selected_service) === String(service_details.bookingpress_service_id) || (typeof window.bookingpress_service_extra_cls_services !== 'undefined' && typeof window.bookingpress_service_extra_cls_services[service_details.bookingpress_service_id] !== 'undefined')) && typeof window.is_club_service === 'undefined'">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/></svg>
</div>
<div class="bpa-front-si-card--checkmark-icon-multiservice" v-if="(typeof window.is_club_service !== 'undefined' && (window.is_club_service == 'true' || window.is_club_service == true))">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" v-if="typeof window.bookingpress_service_extra_cls_services !== 'undefined' && typeof window.bookingpress_service_extra_cls_services[service_details.bookingpress_service_id] !== 'undefined'">
        <rect x="2" y="2" width="20" height="20" rx="6" style="fill:var(--bpa-pt-main-green)"/>
        <rect x="6" y="6" width="12" height="12" rx="6" style="fill:var( --bpa-pt-price-button-text-color )"/>
        <g clip-path="url(#clip0_3047_24293)">
        <path d="M12 5C8.136 5 5 8.136 5 12C5 15.864 8.136 19 12 19C15.864 19 19 15.864 19 12C19 8.136 15.864 5 12 5ZM10.103 15.003L7.59 12.49C7.317 12.217 7.317 11.776 7.59 11.503C7.863 11.23 8.304 11.23 8.577 11.503L10.6 13.519L15.416 8.703C15.689 8.43 16.13 8.43 16.403 8.703C16.676 8.976 16.676 9.417 16.403 9.69L11.09 15.003C10.824 15.276 10.376 15.276 10.103 15.003Z" style="fill:var(--bpa-pt-main-green)"/>
        </g>
        <defs>
        <clipPath id="clip0_3047_24293">
        <rect width="24" height="24" style="fill:var( --bpa-pt-price-button-text-color )"/>
        </clipPath>
        </defs>
    </svg>
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" v-else>
        <rect x="0.499083" y="0.499083" width="19.0018" height="19.0018" rx="5.50092" stroke="#CFD6E6" stroke-width="0.998165"/>
        <path d="M10 6.5V13.5" stroke="#727E95" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6.49902 10H13.499" stroke="#727E95" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</div>
                                                                <div class="bpa-front-si-card__left" v-if="!service_details.use_placeholder">
                                                                    <img :src="service_details.img_url" :alt="service_details.bookingpress_service_name">
                                                                </div>
                                                                <div class="bpa-front-si-card__left" v-else>
                                                                    <div class="bpa-front-si__default-img">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13.2 7.07L10.25 11l2.25 3c.33.44.24 1.07-.2 1.4-.44.33-1.07.25-1.4-.2-1.05-1.4-2.31-3.07-3.1-4.14-.4-.53-1.2-.53-1.6 0l-4 5.33c-.49.67-.02 1.61.8 1.61h18c.82 0 1.29-.94.8-1.6l-7-9.33c-.4-.54-1.2-.54-1.6 0z"/></svg>
                                                                    </div>
                                                                </div>
                                                                <div class="bpa-front-si__card-body">
                                                                    <div
                                                                        class="bpa-front-si__card-body--heading"
                                                                        :aria-label="service_details.bookingpress_service_name"
                                                                    >
                                                                        {{ service_details.bookingpress_service_name }}
                                                                    </div>
                                                                    <p
                                                                        v-if="state.display_service_description == 1 && service_details.bookingpress_service_description"
                                                                        class="--bpa-is-desc"
                                                                        :aria-label="service_details.bookingpress_service_description"
                                                                        v-html="service_details.bookingpress_service_description"
                                                                    ></p>
                                                                    <div class="bpa-front-si-cb__specs">
                                                                        <div class="bpa-front-si-cb__specs-item">
                                                                            <p>
                                                                                {{ str('service_duration_text', 'Duration:') }}
                                                                                <strong>{{ service_details.bookingpress_service_duration_val }} {{ service_details.bookingpress_service_duration_label }}</strong>
                                                                            </p>
                                                                        </div>
                                                                        <div
                                                                            class="bpa-front-si-cb__specs-item"
                                                                            v-if="Number(service_details.service_price_without_currency) !== 0"
                                                                        >
                                                                            <p>
                                                                                {{ str('service_price_text', 'Price:') }}
                                                                                <strong class="--is-service-price">{{ service_details.bookingpress_service_price }}</strong>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </bp-ui-col>
                                </bp-ui-row>
                        </div>

                        <!-- Footer: ALWAYS rendered so Next remains reachable
                             even when the picker is hidden (correction #1). -->
                        <div class="bpa-front-dc--footer">
                            <bp-ui-row>
                                <bp-ui-col>
                                    <div class="bpa-front-tabs--foot">
                                        <bp-ui-button
                                            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa_focusable"
                                            :disabled="!state.appointment_step_form_data.selected_service"
                                            @click="bookingpress_step_navigation(state.bookingpress_sidebar_step_data['service'].next_tab_name, state.bookingpress_sidebar_step_data['service'].next_tab_name, state.bookingpress_sidebar_step_data['service'].previous_tab_name)"
                                        >
                                            {{ str('next_btn_text', 'Next') }}&nbsp;<strong>{{ state.bookingpress_sidebar_step_data[state.bookingpress_sidebar_step_data['service'].next_tab_name]?.tab_name }}</strong>
                                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" aria-hidden="true"><rect fill="none" height="24" width="24"/><path d="M14.29,5.71L14.29,5.71c-0.39,0.39-0.39,1.02,0,1.41L18.17,11H3c-0.55,0-1,0.45-1,1v0c0,0.55,0.45,1,1,1h15.18l-3.88,3.88 c-0.39,0.39-0.39,1.02,0,1.41l0,0c0.39,0.39,1.02,0.39,1.41,0l5.59-5.59c0.39-0.39,0.39-1.02,0-1.41L15.7,5.71 C15.32,5.32,14.68,5.32,14.29,5.71z"/></svg>
                                        </bp-ui-button>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>
                    </div>
                </div>

                <!-- ============ DATETIME TAB (Step 3D) ============ -->
                <!-- Legacy parity (appointment_booking_form.php L148): bare
                     \`bpa-front-tabs--panel-body\` class, no \`__datetime\` modifier. -->
                <div
                    class="bpa-front-tabs--panel-body"
                    :class="{ '__bpa-is-active': isTab('datetime') }"
                    :style="{ display: isTab('datetime') ? 'block' : 'none', width: '100%' }"
                    data-tab="datetime"
                    tabindex="0"
                >
                    <div class="bpa-front-default-card">
                        <div class="bpa-front-dc--body">
                            <!-- Legacy parity (appointment_booking_form.php L159):
                                 container class is \`bpa-front-module--date-and-time\`
                                 (NOT \`--date-time\`). -->
                            <div class="bpa-front-module-container bpa-front-module--date-and-time">
                                <!-- Legacy parity (appointment_booking_form.php L192-196):
                                     module heading + optional note row above the wrapper. -->
                                <bp-ui-row>
                                    <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <div
                                            class="bpa-front-module-heading"
                                            :aria-label="str('datetime_tab_name', 'Date & Time')"
                                        >
                                            {{ str('datetime_tab_name', 'Date & Time') }}
                                        </div>
                                        <div
                                            class="bpa-front-module--note-desc"
                                            v-if="str('date_time_step_note', '') != ''"
                                            v-html="str('date_time_step_note', '')"
                                        ></div>
                                    </bp-ui-col>
                                </bp-ui-row>
                                <!--
                                  Legacy parity: the datetime body uses flex via
                                  \`.bpa-front--dt__wrapper\` / \`.bpa-front--dt__col\`
                                  (NOT bp-ui-row/col grid), and each column flips to
                                  \`bpa-front-dt-col__is-visible\` once progressive
                                  loading is done. Mirrors core/views/frontend/
                                  appointment_booking_form.php lines 198-205.
                                -->
                                <!--
                                  Legacy \`.__sm\` parity: on mobile (≤576px) the
                                  wrapper toggles between two mutually-exclusive
                                  views via \`displayResponsiveCalendar\`:
                                    '0' → time-slots + date-trigger button
                                    '1' → calendar replaces slots
                                  Desktop CSS ignores these modifier classes and
                                  always shows both columns side-by-side.
                                -->
                                <div
                                    class="bpa-front--dt__wrapper"
                                    :class="state.displayResponsiveCalendar == '1'
                                        ? 'bpa-sm-show-calendar'
                                        : 'bpa-sm-show-slots'"
                                >
                                    <div
                                        class="bpa-front--dt__col bpa-front--dt__col--calendar"
                                        :class="state.isLoadDateTimeCalendarLoad == '0' ? 'bpa-front-dt-col__is-visible' : ''"
                                    >
                                        <div class="bpa-front--dt__calendar" tabindex="0" role="region" :aria-label="str('calendar_text', 'Booking calendar')">
                                            <!-- VCalendar mini-app host; mounted by mountVCalendar().
                                                 \`vc-light\` must be on a wrapper so v-calendar v3's
                                                 CSS vars (--vc-color, --vc-bg, --vc-border, ...) resolve. -->
                                            <div ref="calendarHost" class="bpa-front--dt__calendar-host vc-light"></div>
                                            <div v-if="state.is_date_loading == '1'" class="bpa-front--dt__loader">&nbsp;</div>
                                        </div>
                                    </div>
                                    <div
                                        class="bpa-front--dt__col bpa-front--dt__col--slots"
                                        :class="state.isLoadDateTimeCalendarLoad == '0' ? 'bpa-front-dt-col__is-visible' : ''"
                                    >
                                        <!-- Legacy parity: \`tabIndex\` + \`ref\` + \`@focus\` on the
                                             time-slots column so keyboard focus lands on the
                                             first slot. Mirrors appointment_booking_form.php L205. -->
                                        <div class="bpa-front--dt__time-slots" tabindex="0" ref="timeSlotItemsGroup" @focus="focusFirstTimeSlot">
                                            <!-- Loader sentinel: \`service_timing === '-2'\` (date-click
                                                 in flight) or '-3' (service-change refetch). Matches
                                                 legacy v-if="service_timing == '-2' && service_timing != 'null'".
                                                 The inner SVG is the legacy animated loader (copied
                                                 verbatim from appointment_booking_form.php L208-233) so
                                                 the \`.bpa-front-loader\` rule in bookingpress_front.css
                                                 sizes and fills it correctly. -->
                                            <div
                                                v-if="state.service_timing === '-2' || state.service_timing === '-3'"
                                                class="bpa-front-loader-container"
                                                role="status"
                                                aria-live="polite"
                                                :aria-label="str('loading_text', 'Loading time slots')"
                                            >
                                                <div class="bpa-front-loader" v-html="LOADER_SVG"></div>
                                            </div>

                                            <!--
                                              Mobile (≤576px) "date trigger" button — legacy
                                              \`.__sm\` parity (appointment_booking_form.php
                                              L502-506). Click flips to calendar view; CSS
                                              hides this whole block on tablet+desktop.
                                            -->
                                            <div class="bpa-front--dt__ts-sm-back-btn">
                                                <button
                                                    type="button"
                                                    class="bpa-front-btn bpa_focusable"
                                                    :aria-label="selectedDateLabel || str('select_date_text', 'Select date')"
                                                    @click="openResponsiveCalendar"
                                                >
                                                    <span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                                                        <span class="bpa-front--dt__ts-sm-back-btn-label">{{ selectedDateLabel || str('select_date_text', 'Select date') }}</span>
                                                    </span>
                                                </button>
                                            </div>

                                            <div class="bpa-front--dt__ts-heading">
                                                <div
                                                    class="bpa-front-module-heading"
                                                    :aria-label="str('timeslot_text', 'Available Time Slots')"
                                                >
                                                    {{ str('timeslot_text', 'Available Time Slots') }}
                                                </div>
                                            </div>

                                            <!-- Empty helper: no date picked yet. Not a legacy case
                                                 (legacy auto-selects the first available date on
                                                 mount so this state is rare), so show just the
                                                 explanatory text in \`.bpa-front-ntb__val\` for
                                                 consistent vertical alignment, without the heavy
                                                 illustration SVG. -->
                                            <div
                                                v-if="!state.appointment_step_form_data.selected_date && state.service_timing !== '-2' && state.service_timing !== '-3' && date_time_step_note != ''"
                                                class="bpa-front--dt__ts-body bpa-front__no-timeslots-body"
                                                :aria-label="str('date_time_step_note', 'Please select a date to view available time slots.')"
                                            >
                                                <div class="bpa-front-ntb__val">{{ str('date_time_step_note', 'Please select a date to view available time slots.') }}</div>
                                            </div>

                                            <!-- No slots available for the selected date.
                                                 Legacy parity: inline illustration SVG + the message
                                                 wrapped in \`.bpa-front-ntb__val\` (see L239-348 of the
                                                 legacy template). -->
                                            <div
                                                v-else-if="state.no_timeslot_available === true && state.service_timing !== '-2' && state.service_timing !== '-3'"
                                                class="bpa-front--dt__ts-body bpa-front__no-timeslots-body"
                                                :aria-label="str('no_timeslot_available', 'No time slots available for the selected date.')"
                                            >
                                                <div v-html="NO_SLOTS_SVG"></div>
                                                <div class="bpa-front-ntb__val">{{ str('no_timeslot_available', 'No time slots available for the selected date.') }}</div>
                                            </div>

                                            <!-- Buckets. Outer v-if guards against string sentinels
                                                 ('-2' / '-3' / ''); inner v-if's use Array.isArray so
                                                 a missing bucket key can never crash the template. -->
                                            <div
                                                v-else-if="state.service_timing && typeof state.service_timing === 'object'"
                                                class="bpa-front--dt__ts-body"
                                            >
                                                <div
                                                    v-if="Array.isArray(state.service_timing.morning_time) && state.service_timing.morning_time.length"
                                                    class="bpa-front--dt__ts-body--row"
                                                    data-slot="morning"
                                                >
                                                    <div class="bpa-front--dt-ts__sub-heading" role="heading" aria-level="3">{{ str('morning_text', 'Morning') }}</div>
                                                    <div class="bpa-front--dt__ts-body--items">
                                                        <template v-for="(slot, idx) in state.service_timing.morning_time" :key="'m-' + idx">
                                                            <div
                                                                class="bpa-front--dt__ts-body--item bpa_focusable"
                                                                role="button"
                                                                tabindex="-1"
                                                                :class="{ '__bpa-is-selected': String(state.appointment_step_form_data.selected_start_time) === String(slot.store_start_time || slot.start_time), '__bpa-is-disabled': !!(slot.is_booked || slot.disable_flag_timeslot) }"
                                                                :aria-label="slot.formatted_start_end_time"
                                                                :aria-pressed="String(state.appointment_step_form_data.selected_start_time) === String(slot.store_start_time || slot.start_time) ? 'true' : 'false'"
                                                                :aria-disabled="!!(slot.is_booked || slot.disable_flag_timeslot)"
                                                                @click="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown.enter.prevent="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown.space.prevent="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown="bpa_handle_timeslot_keypress($event)"
                                                            >
                                                                <span>{{ slot.formatted_start_end_time }}</span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                <div
                                                    v-if="Array.isArray(state.service_timing.afternoon_time) && state.service_timing.afternoon_time.length"
                                                    class="bpa-front--dt__ts-body--row"
                                                    data-slot="afternoon"
                                                >
                                                    <div class="bpa-front--dt-ts__sub-heading" role="heading" aria-level="3">{{ str('afternoon_text', 'Afternoon') }}</div>
                                                    <div class="bpa-front--dt__ts-body--items">
                                                        <template v-for="(slot, idx) in state.service_timing.afternoon_time" :key="'a-' + idx">
                                                            <div
                                                                class="bpa-front--dt__ts-body--item bpa_focusable"
                                                                role="button"
                                                                tabindex="-1"
                                                                :class="{ '__bpa-is-selected': String(state.appointment_step_form_data.selected_start_time) === String(slot.store_start_time || slot.start_time), '__bpa-is-disabled': !!(slot.is_booked || slot.disable_flag_timeslot) }"
                                                                :aria-label="slot.formatted_start_end_time"
                                                                :aria-pressed="String(state.appointment_step_form_data.selected_start_time) === String(slot.store_start_time || slot.start_time) ? 'true' : 'false'"
                                                                :aria-disabled="!!(slot.is_booked || slot.disable_flag_timeslot)"
                                                                @click="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown.enter.prevent="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown.space.prevent="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown="bpa_handle_timeslot_keypress($event)"
                                                            >
                                                                <span>{{ slot.formatted_start_end_time }}</span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                <div
                                                    v-if="Array.isArray(state.service_timing.evening_time) && state.service_timing.evening_time.length"
                                                    class="bpa-front--dt__ts-body--row"
                                                    data-slot="evening"
                                                >
                                                    <div class="bpa-front--dt-ts__sub-heading" role="heading" aria-level="3">{{ str('evening_text', 'Evening') }}</div>
                                                    <div class="bpa-front--dt__ts-body--items">
                                                        <template v-for="(slot, idx) in state.service_timing.evening_time" :key="'e-' + idx">
                                                            <div
                                                                class="bpa-front--dt__ts-body--item bpa_focusable"
                                                                role="button"
                                                                tabindex="-1"
                                                                :class="{ '__bpa-is-selected': String(state.appointment_step_form_data.selected_start_time) === String(slot.store_start_time || slot.start_time), '__bpa-is-disabled': !!(slot.is_booked || slot.disable_flag_timeslot) }"
                                                                :aria-label="slot.formatted_start_end_time"
                                                                :aria-pressed="String(state.appointment_step_form_data.selected_start_time) === String(slot.store_start_time || slot.start_time) ? 'true' : 'false'"
                                                                :aria-disabled="!!(slot.is_booked || slot.disable_flag_timeslot)"
                                                                @click="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown.enter.prevent="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown.space.prevent="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown="bpa_handle_timeslot_keypress($event)"
                                                            >
                                                                <span>{{ slot.formatted_start_end_time }}</span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                <div
                                                    v-if="Array.isArray(state.service_timing.night_time) && state.service_timing.night_time.length"
                                                    class="bpa-front--dt__ts-body--row"
                                                    data-slot="night"
                                                >
                                                    <div class="bpa-front--dt-ts__sub-heading" role="heading" aria-level="3">{{ str('night_text', 'Night') }}</div>
                                                    <div class="bpa-front--dt__ts-body--items">
                                                        <template v-for="(slot, idx) in state.service_timing.night_time" :key="'n-' + idx">
                                                            <div
                                                                class="bpa-front--dt__ts-body--item bpa_focusable"
                                                                role="button"
                                                                tabindex="-1"
                                                                :class="{ '__bpa-is-selected': String(state.appointment_step_form_data.selected_start_time) === String(slot.store_start_time || slot.start_time), '__bpa-is-disabled': !!(slot.is_booked || slot.disable_flag_timeslot) }"
                                                                :aria-label="slot.formatted_start_end_time"
                                                                :aria-pressed="String(state.appointment_step_form_data.selected_start_time) === String(slot.store_start_time || slot.start_time) ? 'true' : 'false'"
                                                                :aria-disabled="!!(slot.is_booked || slot.disable_flag_timeslot)"
                                                                @click="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown.enter.prevent="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown.space.prevent="!slot.is_booked && !slot.disable_flag_timeslot && selectTiming(slot.start_time, slot.end_time, slot.store_start_time, slot.store_end_time, slot.store_booked_date, slot.formatted_start_time, slot.formatted_end_time, slot)"
                                                                @keydown="bpa_handle_timeslot_keypress($event)"
                                                            >
                                                                <span>{{ slot.formatted_start_end_time }}</span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Legacy parity (appointment_booking_form.php L696):
                             footer receives the dynamic class (sticky / in-flow), and
                             Go Back uses \`bpa-front-btn--borderless\` with a leading
                             arrow SVG. Go Back is hidden when \`hide_category_service == '1'\`
                             (the service tab isn't reachable in that mode). -->
                        <div class="bpa-front-dc--footer">
                            <bp-ui-row>
                                <bp-ui-col>
                                    <div class="bpa-front-tabs--foot">
                                        <bp-ui-button
                                            v-if="state.hide_category_service != '1'"
                                            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--borderless bpa_focusable"
                                            :aria-label="str('goback_btn_text', 'Go Back')"
                                            @click="bookingpress_step_navigation(state.bookingpress_sidebar_step_data['datetime'].previous_tab_name, state.bookingpress_sidebar_step_data['datetime'].next_tab_name, state.bookingpress_sidebar_step_data['datetime'].previous_tab_name)"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" aria-hidden="true"><rect fill="none" height="24" width="24"/><path d="M9.7,18.3L9.7,18.3c0.39-0.39,0.39-1.02,0-1.41L5.83,13H21c0.55,0,1-0.45,1-1v0c0-0.55-0.45-1-1-1H5.83l3.88-3.88 c0.39-0.39,0.39-1.02,0-1.41l0,0c-0.39-0.39-1.02-0.39-1.41,0L2.7,11.3c-0.39,0.39-0.39,1.02,0,1.41l5.59,5.59 C8.68,18.68,9.32,18.68,9.7,18.3z"/></svg>
                                            {{ str('goback_btn_text', 'Go Back') }}
                                        </bp-ui-button>
                                        <bp-ui-button
                                            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa_focusable"
                                            :aria-label="str('next_btn_text', 'Next') + ' ' + (state.bookingpress_sidebar_step_data[state.bookingpress_sidebar_step_data['datetime'].next_tab_name]?.tab_name || '')"
                                            :disabled="!state.appointment_step_form_data.selected_date || !state.appointment_step_form_data.selected_start_time"
                                            @click="bookingpress_step_navigation(state.bookingpress_sidebar_step_data['datetime'].next_tab_name, state.bookingpress_sidebar_step_data['datetime'].next_tab_name, state.bookingpress_sidebar_step_data['datetime'].previous_tab_name)"
                                        >
                                            {{ str('next_btn_text', 'Next') }}&nbsp;<strong>{{ state.bookingpress_sidebar_step_data[state.bookingpress_sidebar_step_data['datetime'].next_tab_name]?.tab_name }}</strong>
                                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" aria-hidden="true"><rect fill="none" height="24" width="24"/><path d="M14.29,5.71L14.29,5.71c-0.39,0.39-0.39,1.02,0,1.41L18.17,11H3c-0.55,0-1,0.45-1,1v0c0,0.55,0.45,1,1,1h15.18l-3.88,3.88 c-0.39,0.39-0.39,1.02,0,1.41l0,0c0.39,0.39,1.02,0.39,1.41,0l5.59-5.59c0.39-0.39,0.39-1.02,0-1.41L15.7,5.71 C15.32,5.32,14.68,5.32,14.29,5.71z"/></svg>
                                        </bp-ui-button>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>
                    </div>
                </div>

                <!-- ============ BASIC DETAILS TAB (Step 3E) ============ -->
                <!-- Legacy parity: bare \`bpa-front-tabs--panel-body\` class, no
                     \`__basic-details\` modifier. -->
                <div
                    class="bpa-front-tabs--panel-body"
                    :class="{ '__bpa-is-active': isTab('basic_details') }"
                    :style="{ display: isTab('basic_details') ? 'block' : 'none', width: '100%' }"
                    data-tab="basic_details"
                    tabindex="1"
                >
                    <div class="bpa-front-default-card">
                        <!-- Legacy parity: top-of-panel error toast. Same DOM
                             shape as the legacy template (L717-721), so the
                             existing \`.bpa-front-toast-notification.--bpa-error\`
                             rule in bookingpress_front.css styles it. -->
                        <div
                            v-if="state.is_display_error == '1'"
                            class="bpa-front-toast-notification --bpa-error"
                            :aria-label="state.is_error_msg"
                        >
                            <div class="bpa-front-tn-body">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"/></svg>
                                <p>{{ state.is_error_msg }}</p>
                            </div>
                        </div>

                        <div class="bpa-front-dc--body">
                            <!--
                              Legacy DOM shape (bookingpress_front.css targets these
                              class names directly): a plain .bpa-front-module-container
                              wraps .bpa-front-module-heading + .bpa-front-module--bd-form,
                              which holds a SINGLE meaningful grid row (.bpa-bd-fields-row)
                              with per-field columns. Outer BpUi grid wrappers were
                              dropped: (a) BpUiCol without :span/breakpoints collapses
                              (BpUiCol has no default span), and (b) the wrappers added
                              no layout value — the form content is already full-width
                              inside .bpa-front-dc--body.
                            -->
                            <div class="bpa-front-module-container bpa-front-module--basic-details">
                                <div
                                    class="bpa-front-module-heading"
                                    :aria-label="str('basic_details_tab_name', 'Your Details')"
                                >
                                    {{ str('basic_details_tab_name', 'Your Details') }}
                                </div>

                                <!--
                                  ref="formRef" is the Composition-API handle used by
                                  validateBasicDetails(). The DOM \`ref\` attribute name
                                  is \`formRef\` (not \`appointment_step_form_data\`) —
                                  addon selectors that relied on the legacy name no
                                  longer apply to the Vue 3 mount, which is fine for
                                  Step 3E since no Lite addon consumes it.

                                  BpUiForm renders as a native <form>, so the
                                  \`.bpa-front-module--bd-form\` class is applied
                                  directly to it to preserve legacy selector targeting
                                  (bookingpress_front.css keys off that class name).
                                -->
                                <bp-ui-form
                                    ref="formRef"
                                    class="bpa-front-module--bd-form"
                                    :model="state.appointment_step_form_data"
                                    label-position="top"
                                    require-asterisk-position="right"
                                >
                                    <!--
                                      Legacy parity (appointment_booking_form.php L736-737):
                                      three fields per row on \`md\`+ viewports (span 8),
                                      stacked full-width on \`xs\`/\`sm\`. Matches the legacy
                                      visual density of Basic Details.
                                    -->
                                    <bp-ui-row :gutter="24" class="bpa-bd-fields-row">
                                        <template v-for="f in state.customer_form_fields" :key="f.id">
                                            <bp-ui-col
                                                v-if="!f.is_hide"
                                                :xs="24"
                                                :sm="24"
                                                :md="8"
                                                :lg="8"
                                                :xl="8"
                                                :class="f.field_name === 'terms_and_conditions' ? 'bpa_terms_conditions' : ''"
                                                :data-bp-field="f.v_model_value"
                                            >
                                                <bp-ui-form-item :prop="f.v_model_value">
                                                    <template #label v-if="f.label && f.field_name !== 'terms_and_conditions'">
                                                        <span class="bpa-front-form-label">{{ f.label }}</span>
                                                    </template>

                                                    <bp-ui-input
                                                        v-if="f.field_name === 'note'"
                                                        type="textarea"
                                                        v-model="state.appointment_step_form_data[f.v_model_value]"
                                                        class="bpa-front-form-control"
                                                        :placeholder="f.placeholder"
                                                        @input="clearBasicDetailsFieldError(f.v_model_value)"
                                                    />

                                                    <bp-ui-input
                                                        v-else-if="f.field_name === 'email_address'"
                                                        type="email"
                                                        v-model="state.appointment_step_form_data[f.v_model_value]"
                                                        class="bpa-front-form-control"
                                                        :placeholder="f.placeholder"
                                                        @input="clearBasicDetailsFieldError(f.v_model_value)"
                                                    />

                                                    <!--
                                                      phone_number: use the Vue 3-compatible telephone
                                                      component exported by bookingpress-ui.js
                                                      (BpUiTelInput, registered as <bp-ui-tel-input>).
                                                      v-model binds to customer_phone just like the
                                                      plain input did, so validation rules already
                                                      keyed off customer_phone continue to apply.
                                                      defaultCountry comes from the ISO code seeded by
                                                      PHP (default_phone_country_code setting), and
                                                      the component emits country-changed with the
                                                      selected country object — we capture the dial
                                                      code into state.customer_phone_dial_code so
                                                      downstream booking submission has access to it.

                                                      Issue 4 — placeholder = sample number (not the
                                                      configured static placeholder). The vue-tel-input
                                                      library exposes \`inputOptions.dynamicPlaceholder\`
                                                      which uses libphonenumber to render an example
                                                      national number for the selected country (e.g.
                                                      "201-555-0123" for US). We do NOT pass the
                                                      configured \`f.placeholder\` here, mirroring legacy
                                                      Vue 2 behaviour where the sample number was the
                                                      visible hint.
                                                    -->
                                                    <bp-ui-tel-input
                                                        v-else-if="f.field_name === 'phone_number'"
                                                        v-model="state.appointment_step_form_data[f.v_model_value]"
                                                        class="bpa-front-form-control --bpa-country-dropdown bpa-front-form-control--tel"
                                                        :default-country="state.appointment_step_form_data.customer_phone_country || ''"
                                                        :auto-default-country="!state.appointment_step_form_data.customer_phone_country"
                                                        :dropdown-options="{ showFlags: true, showSearchBox: false, showDialCodeInSelection: false, showDialCodeInList: true }"
                                                        :input-options="{ dynamicPlaceholder: true }"
                                                        @country-changed="onPhoneCountryChanged"
                                                        @input="clearBasicDetailsFieldError(f.v_model_value)"
                                                    />

                                                    <bp-ui-checkbox
                                                        v-else-if="f.field_name === 'terms_and_conditions'"
                                                        v-model="state.appointment_step_form_data[f.v_model_value]"
                                                        class="bpa-front-form-control--checkbox"
                                                        :label="true"
                                                        :name="f.v_model_value"
                                                    >
                                                        <span v-html="f.label"></span>
                                                    </bp-ui-checkbox>

                                                    <bp-ui-input
                                                        v-else
                                                        v-model="state.appointment_step_form_data[f.v_model_value]"
                                                        class="bpa-front-form-control"
                                                        :placeholder="f.placeholder"
                                                        :disabled="state.check_bookingpress_username_set == 1 && f.field_name === 'username'"
                                                        @input="clearBasicDetailsFieldError(f.v_model_value)"
                                                    />

                                                    <!-- Issue 1 — per-field error message rendered when
                                                         validateBasicDetails populated state.basic_details_errors.
                                                         Cleared on next @input. Mirrors the legacy
                                                         \`el-form-item__error\` placement (below the input). -->
                                                    <div
                                                        v-if="basicDetailsErrors[f.v_model_value]"
                                                        class="bpa-front-form-control--error bp-form-item__error"
                                                        role="alert"
                                                        aria-live="polite"
                                                    >
                                                        {{ basicDetailsErrors[f.v_model_value] }}
                                                    </div>
                                                </bp-ui-form-item>
                                            </bp-ui-col>
                                        </template>
                                    </bp-ui-row>
                                </bp-ui-form>
                            </div>
                        </div>

                        <!-- Legacy parity (appointment_booking_form.php L772-784):
                             borderless Go Back with leading back-arrow SVG;
                             nav args read from \`sidebar_step_data['basic_details']\`. -->
                        <div class="bpa-front-dc--footer">
                            <bp-ui-row>
                                <bp-ui-col>
                                    <div class="bpa-front-tabs--foot">
                                        <bp-ui-button
                                            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--borderless bpa_focusable"
                                            :aria-label="str('goback_btn_text', 'Go Back')"
                                            @click="bookingpress_step_navigation(state.bookingpress_sidebar_step_data['basic_details'].previous_tab_name, state.bookingpress_sidebar_step_data['basic_details'].next_tab_name, state.bookingpress_sidebar_step_data['basic_details'].previous_tab_name)"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" aria-hidden="true"><rect fill="none" height="24" width="24"/><path d="M9.7,18.3L9.7,18.3c0.39-0.39,0.39-1.02,0-1.41L5.83,13H21c0.55,0,1-0.45,1-1v0c0-0.55-0.45-1-1-1H5.83l3.88-3.88 c0.39-0.39,0.39-1.02,0-1.41l0,0c-0.39-0.39-1.02-0.39-1.41,0L2.7,11.3c-0.39,0.39-0.39,1.02,0,1.41l5.59,5.59 C8.68,18.68,9.32,18.68,9.7,18.3z"/></svg>
                                            {{ str('goback_btn_text', 'Go Back') }}
                                        </bp-ui-button>
                                        <bp-ui-button
                                            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa_focusable"
                                            :aria-label="str('next_btn_text', 'Next') + ' ' + (state.bookingpress_sidebar_step_data[state.bookingpress_sidebar_step_data['basic_details'].next_tab_name]?.tab_name || '')"
                                            @click="bookingpress_step_navigation(state.bookingpress_sidebar_step_data['basic_details'].next_tab_name, state.bookingpress_sidebar_step_data['basic_details'].next_tab_name, state.bookingpress_sidebar_step_data['basic_details'].previous_tab_name)"
                                        >
                                            {{ str('next_btn_text', 'Next') }}&nbsp;<strong>{{ state.bookingpress_sidebar_step_data[state.bookingpress_sidebar_step_data['basic_details'].next_tab_name]?.tab_name }}</strong>
                                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" aria-hidden="true"><rect fill="none" height="24" width="24"/><path d="M14.29,5.71L14.29,5.71c-0.39,0.39-0.39,1.02,0,1.41L18.17,11H3c-0.55,0-1,0.45-1,1v0c0,0.55,0.45,1,1,1h15.18l-3.88,3.88 c-0.39,0.39-0.39,1.02,0,1.41l0,0c0.39,0.39,1.02,0.39,1.41,0l5.59-5.59c0.39-0.39,0.39-1.02,0-1.41L15.7,5.71 C15.32,5.32,14.68,5.32,14.29,5.71z"/></svg>
                                        </bp-ui-button>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>
                    </div>
                </div>

                <!-- ============ SUMMARY TAB (Step 3F) ============ -->
                <!-- Legacy parity: bare \`bpa-front-tabs--panel-body\` class, no
                     \`__summary\` modifier. -->
                <div
                    class="bpa-front-tabs--panel-body"
                    :class="{ '__bpa-is-active': isTab('summary') }"
                    :style="{ display: isTab('summary') ? 'block' : 'none', width: '100%' }"
                    data-tab="summary"
                    tabindex="1"
                >
                    <div class="bpa-front-default-card">
                        <!-- Top-of-panel error toast (same shape as Basic Details). -->
                        <div
                            v-if="state.is_display_error == '1'"
                            class="bpa-front-toast-notification --bpa-error"
                            :aria-label="state.is_error_msg"
                        >
                            <div class="bpa-front-tn-body">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"/></svg>
                                <p>{{ state.is_error_msg }}</p>
                            </div>
                        </div>

                        <div class="bpa-front-dc--body">
                            <!-- ========== Region A: booking summary card ========== -->
                            <bp-ui-row>
                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <div class="bpa-front-module-container bpa-front-module--booking-summary bpa-fm__booking-summary-v47">
                                        <div class="bpa-front-module--bs-head">
                                            <!-- Legacy decorative header vector — injected via
                                                 v-html to mirror appointment_booking_form.php
                                                 L802-L833 byte-for-byte. -->
                                            <div v-html="SUMMARY_HEAD_SVG"></div>
                                            <div
                                                class="bpa-front-module-heading"
                                                :aria-label="str('summary_tab_name', 'Summary')"
                                            >
                                                {{ str('summary_tab_name', 'Summary') }}
                                            </div>
                                            <p
                                                v-if="str('summary_content_text', '')"
                                                :aria-label="str('summary_content_text', '')"
                                            >{{ str('summary_content_text', '') }}</p>
                                            <div
                                                v-if="state.summary_step_note"
                                                class="bpa-front-module--note-desc"
                                                v-html="state.summary_step_note"
                                            ></div>
                                        </div>

                                        <!-- Customer block. Fallback chain: fullname →
                                             firstname/lastname → email. Bound to the
                                             reactive form-data so a back-navigation edit
                                             updates the summary immediately. -->
                                        <div class="bpa-front-module--bs-summary-content bpa-front-module--bs-customer-detail">
                                            <div class="bpa-front-module--bs-summary-content-item">
                                                <span :aria-label="str('customer_text', 'Customer')">{{ str('customer_text', 'Customer') }}</span>
                                                <div
                                                    class="bpa-front-bs-sm__item-val"
                                                    :aria-label="customerDisplayName"
                                                >{{ customerDisplayName }}</div>
                                            </div>
                                        </div>

                                        <!-- Desktop: service + date/time rows. -->
                                        <div class="bpa-front-module--bs-summary-content bpa-front-summary-content__lg">
                                            <div class="bpa-front-module--bs-summary-content-item">
                                                <span :aria-label="str('service_text', 'Service')">{{ str('service_text', 'Service') }}</span>
                                                <div
                                                    class="bpa-front-bs-sm__item-val"
                                                    :aria-label="state.appointment_step_form_data.selected_service_name"
                                                >{{ state.appointment_step_form_data.selected_service_name }}</div>
                                            </div>
                                            <div class="bpa-front-module--bs-summary-content-item">
                                                <span :aria-label="str('date_time_text', 'Date & Time')">{{ str('date_time_text', 'Date & Time') }}</span>
                                                <div class="bpa-front-bs-sm__item-val">
                                                    {{ state.appointment_step_form_data.selected_formatted_booked_date || formatBookedDate(state.appointment_step_form_data.selected_date) }},
                                                    {{ state.appointment_step_form_data.selected_formatted_start_time || state.appointment_step_form_data.selected_start_time }} - {{ state.appointment_step_form_data.selected_formatted_end_time || state.appointment_step_form_data.selected_end_time }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mobile: combined Appointment Details block. -->
                                        <div class="bpa-front-module--bs-summary-content bpa-front-summary-content__sm">
                                            <div class="bpa-front-module--bs-summary-content-item">
                                                <span :aria-label="str('appointment_details_title_text', 'Appointment Details')">{{ str('appointment_details_title_text', 'Appointment Details') }}</span>
                                                <div class="bpa-front-bs-sm__item-vals">
                                                    <div class="bpa-front-bs-sm__item-val">{{ state.appointment_step_form_data.selected_service_name }}</div>
                                                    <div class="bpa-front-bs-sm__item-val">
                                                        {{ state.appointment_step_form_data.selected_formatted_booked_date || formatBookedDate(state.appointment_step_form_data.selected_date) }},
                                                        {{ state.appointment_step_form_data.selected_formatted_start_time || state.appointment_step_form_data.selected_start_time }}<span
                                                            v-if="state.appointment_step_form_data.selected_formatted_end_time || state.appointment_step_form_data.selected_end_time"
                                                        > - {{ state.appointment_step_form_data.selected_formatted_end_time || state.appointment_step_form_data.selected_end_time }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total amount row — hidden for free services. -->
                                        <div
                                            v-if="!isFreeService"
                                            class="bpa-front-module--bs-amount-details"
                                        >
                                            <div class="bpa-fm--bs-amount-item">
                                                <div
                                                    class="bpa-bs-ai__item"
                                                    v-html="state.bookingpress_total_amount_text || str('total_amount_text', 'Total Amount Payable')"
                                                ></div>
                                                <div
                                                    class="bpa-front-module--bs-ad--price"
                                                    :aria-label="state.appointment_step_form_data.selected_service_price"
                                                >{{ state.appointment_step_form_data.selected_service_price }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>

                            <!-- ========== Region B: payment methods ========== -->
                            <bp-ui-row v-if="showPaymentMethods && showPaymentMethodPicker">
                                <bp-ui-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                    <div class="bpa-front-module-container bpa-front-module--payment-methods">
                                        <!-- Empty-state card: no gateway configured. -->
                                        <div
                                            v-if="!hasAnyPaymentGateway"
                                            class="bpa-front-module--pm__empty-view"
                                        >
                                            <div
                                                class="bpa-front-pm-ev__title"
                                                :aria-label="str('no_payment_method_available', 'No payment method is available for this booking.')"
                                            >{{ str('no_payment_method_available', 'No payment method is available for this booking.') }}</div>
                                        </div>

                                        <!-- Gateway cards. Each card is an independent
                                             v-if keyed off the server setting string
                                             with loose-compare semantics (legacy parity). -->
                                        <div v-else>
                                            <div class="bpa-front-module--pm-head">
                                                <div
                                                    class="bpa-front-module-heading"
                                                    :aria-label="str('payment_method_text', 'Select Payment Method')"
                                                >{{ str('payment_method_text', 'Select Payment Method') }}</div>
                                            </div>
                                            <div class="bpa-front-module--pm-body">
                                                <div
                                                    class="bpa-front--pm-body-items"
                                                    tabindex="0"
                                                    @focus="focusFirstPaymentGateway"
                                                >
                                                    <div
                                                        v-if="state.on_site_payment !== 'false' && state.on_site_payment !== ''"
                                                        class="bpa-front-module--pm-body__item bpa_focusable"
                                                        :class="{ '__bpa-is-selected': state.appointment_step_form_data.selected_payment_method === 'on-site' }"
                                                        role="button"
                                                        tabindex="-1"
                                                        :aria-label="str('locally_text', 'Pay Locally')"
                                                        @click="select_payment_method('on-site')"
                                                        @keydown.enter="select_payment_method('on-site')"
                                                        @keydown="bpa_handle_pg_keypress($event)"
                                                    >
                                                        <svg class="bpa-front-pm-pay-local-icon" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><g><g><rect fill="none" height="24" width="24"/><rect fill="none" height="24" width="24"/></g></g><g><path d="M21.9,7.89l-1.05-3.37c-0.22-0.9-1-1.52-1.91-1.52H5.05c-0.9,0-1.69,0.63-1.9,1.52L2.1,7.89C1.64,9.86,2.95,11,3,11.06V19 c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2v-7.94C22.12,9.94,22.09,8.65,21.9,7.89z M13,5h1.96l0.54,3.52C15.59,9.23,15.11,10,14.22,10 C13.55,10,13,9.41,13,8.69V5z M6.44,8.86C6.36,9.51,5.84,10,5.23,10C4.3,10,3.88,9.03,4.04,8.36L5.05,5h1.97L6.44,8.86z M11,8.69 C11,9.41,10.45,10,9.71,10c-0.75,0-1.3-0.7-1.22-1.48L9.04,5H11V8.69z M18.77,10c-0.61,0-1.14-0.49-1.21-1.14L16.98,5l1.93-0.01 l1.05,3.37C20.12,9.03,19.71,10,18.77,10z"/></g></svg>
                                                        <p v-html="str('locally_text', 'Pay Locally')"></p>
                                                        <div
                                                            v-if="state.appointment_step_form_data.selected_payment_method === 'on-site'"
                                                            class="bpa-front-si-card--checkmark-icon"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/></svg>
                                                        </div>
                                                    </div>

                                                    <div
                                                        v-if="state.paypal_payment !== 'false' && state.paypal_payment !== ''"
                                                        class="bpa-front-module--pm-body__item bpa_focusable"
                                                        :class="{ '__bpa-is-selected': state.appointment_step_form_data.selected_payment_method === 'paypal' }"
                                                        role="button"
                                                        tabindex="-1"
                                                        :aria-label="str('paypal_text', 'PayPal')"
                                                        @click="select_payment_method('paypal')"
                                                        @keydown.enter="select_payment_method('paypal')"
                                                        @keydown="bpa_handle_pg_keypress($event)"
                                                    >
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M17.9588 8.24063L8.75722 18.2812H5.38222C5.14786 18.2812 4.96036 18.0469 5.00723 17.8125L7.25722 3.5625C7.30412 3.23438 7.58537 3 7.91347 3H13.6322C17.5697 3.14062 18.6479 5.15622 17.9447 8.25002L17.9588 8.24063Z" fill="#002C8A"/>
                                                            <path d="M18.1088 7.3125C19.5151 8.0625 19.8432 9.4687 19.3744 11.3437C18.7651 14.1094 16.9369 15.2812 14.2651 15.3281L13.5151 15.375C13.2338 15.375 13.0463 15.5625 12.9994 15.8437L12.3901 19.5469C12.3432 19.875 12.0619 20.1094 11.7338 20.1094H8.9213C8.6869 20.1094 8.4994 19.875 8.5463 19.6406L9.57755 12.9375C9.6244 12.7031 18.1088 7.3125 18.1088 7.3125Z" fill="#009BE1"/>
                                                            <path d="M9.52148 13.2656L10.459 7.31252C10.4897 7.17152 10.5661 7.04458 10.6762 6.95138C10.7864 6.85818 10.9242 6.80388 11.0683 6.79688H15.5683C16.6465 6.79688 17.4433 6.98437 18.0996 7.31252C17.8652 9.37502 16.8808 12.7031 12.0996 12.7969H10.0371C9.80268 12.7969 9.56833 12.9844 9.52148 13.2656Z" fill="#001F6B"/>
                                                        </svg>
                                                        <p v-html="str('paypal_text', 'PayPal')"></p>
                                                        <div
                                                            v-if="state.appointment_step_form_data.selected_payment_method === 'paypal'"
                                                            class="bpa-front-si-card--checkmark-icon"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/></svg>
                                                        </div>
                                                    </div>
                                                    <!-- Pro extension seam: \`do_action('bpa_front_add_payment_gateway')\`
                                                         from the legacy template renders additional
                                                         gateway cards here. Pro filters the initial
                                                         state payload via \`bookingpress_form_vue3_initial_state\`
                                                         and can plug additional cards in a later step. -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>

                        <!-- ========== Region C: footer ========== -->
                        <div class="bpa-front-dc--footer">
                            <bp-ui-row>
                                <bp-ui-col>
                                    <div class="bpa-front-tabs--foot">
                                        <bp-ui-button
                                            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--borderless bpa_focusable"
                                            :aria-label="str('goback_btn_text', 'Go Back')"
                                            @click="bookingpress_step_navigation(state.bookingpress_sidebar_step_data['summary'].previous_tab_name, state.bookingpress_sidebar_step_data['summary'].next_tab_name, state.bookingpress_sidebar_step_data['summary'].previous_tab_name)"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><rect fill="none" height="24" width="24"/><path d="M9.7,18.3L9.7,18.3c0.39-0.39,0.39-1.02,0-1.41L5.83,13H21c0.55,0,1-0.45,1-1v0c0-0.55-0.45-1-1-1H5.83l3.88-3.88 c0.39-0.39,0.39-1.02,0-1.41l0,0c-0.39-0.39-1.02-0.39-1.41,0L2.7,11.3c-0.39,0.39-0.39,1.02,0,1.41l5.59,5.59 C8.68,18.68,9.32,18.68,9.7,18.3z"/></svg>
                                            {{ str('goback_btn_text', 'Go Back') }}
                                        </bp-ui-button>

                                        <!-- PayPal SDK popup mount.
                                             Legacy parity (class.bookingpress_…:
                                             219-232 + template L1004-1005):
                                             when popup mode is on and the user
                                             has selected PayPal, the default
                                             submit button hides and the SDK
                                             renders its own buttons into
                                             \`#paypal-button-container\`. The
                                             wrapping loader is shown while a
                                             capture is in-flight
                                             (paypal_button_loader == 'true'). -->
                                        <div
                                            v-if="state.show_paypal_popup_button === 'true'"
                                            class="bpa-front-paypal-popup"
                                        >
                                            <bp-ui-button
                                                v-if="state.paypal_button_loader === 'true'"
                                                class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa-loader-button bpa-front-btn--is-loader"
                                                :disabled="true"
                                            >
                                                <span class="bpa-btn__label"></span>
                                                <div class="bpa-front-btn--loader__circles">
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                </div>
                                            </bp-ui-button>
                                            <div
                                                v-show="state.paypal_button_loader !== 'true'"
                                                id="paypal-button-container"
                                            ></div>
                                        </div>

                                        <!-- Default submit button. Legacy gate
                                             (template L1005): render when
                                             \`show_paypal_popup_button === 'false'\`.
                                             This is the active button for
                                             on-site / non-popup gateways and
                                             the initial unselected state. -->
                                        <bp-ui-button
                                            v-if="state.show_paypal_popup_button === 'false'"
                                            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary summery-book-appointment-btn bpa_focusable"
                                            :class="{ 'bpa-front-btn--is-loader': state.isLoadBookingLoader == '1' }"
                                            :disabled="state.isBookingDisabled"
                                            :aria-label="state.bookingpress_book_appointment_btn_text || str('book_appointment_btn_text', 'Book Appointment')"
                                            @click="book_appointment()"
                                        >
                                            <span
                                                class="bpa-btn__label"
                                                v-html="state.bookingpress_book_appointment_btn_text || str('book_appointment_btn_text', 'Book Appointment')"
                                            ></span>
                                            <div class="bpa-front-btn--loader__circles">
                                                <div></div>
                                                <div></div>
                                                <div></div>
                                            </div>
                                        </bp-ui-button>
                                    </div>
                                </bp-ui-col>
                            </bp-ui-row>
                        </div>

                        <!-- Dormant sink for server-side PayPal redirect HTML.
                             Lite never populates \`bookingpress_external_html\`;
                             kept for Pro wire-compatibility. The trailing
                             <script> tag inside is executed by
                             runExternalRedirectScript() after a 50ms delay,
                             mirroring legacy parity. -->
                        <div
                            id="bpa-external-script"
                            v-html="state.bookingpress_external_html"
                        ></div>
                    </div>
                </div>
                </div><!-- /summary panel; #bpa-front-tabs closes below after the last panel -->

                <!-- Legacy parity (appointment_booking_form.php:1020-1103):
                     when the admin has no bookable services, hide the entire
                     tab shell above and render the illustrated empty state
                     here. SVG is copied byte-for-byte from the legacy view
                     (including filter ids) so the shared \`bookingpress_front.css\`
                     rules keyed on \`.bpa-front-dev__*\` classes keep styling
                     the paths without duplicating them here. -->
                <div
                    v-if="state.bookingpress_display_no_service_placeholder"
                    id="bpa-front-data-empty-view"
                    class="bpa-front-data-empty-view __bpa-is-guest-view"
                >
                    <!-- SVG injected via v-html so Vue's template compiler
                         does not walk the deeply-nested SVG tree. Same
                         pattern as NO_SLOTS_SVG / LOADER_SVG elsewhere in
                         this file. -->
                    <div class="bpa-front-dev__illustration" v-html="EMPTY_VIEW_SVG"></div>
                    <div class="bpa-front-dev__title" :aria-label="str('no_categories_services_text', 'No categories and services added!')">{{ str('no_categories_services_text', 'No categories and services added!') }}</div>
                </div>
            </div>
        `,
    };

    const app = createApp(RootComponent);

    if (BookingPressUI) {
        app.use(BookingPressUI);
    }

    // Expose the per-instance payload to descendant components without
    // polluting globals (plugin parity with Element Plus globals).
    app.config.globalProperties.$bpInstance = instance;
    app.provide('bpInstance', instance);

    return app;
}

/**
 * Locate the per-instance DOM container and mount the Vue app into it.
 *
 * Vue's `app.mount(el)` clears the element's existing children, which
 * removes the server-rendered loader shell emitted by Step 1's view.
 */
export function mountBookingFormInstance(instance) {
    if (!instance || !instance.instanceId) {
        return null;
    }

    const container = document.getElementById(ROOT_ID_PREFIX + instance.instanceId);
    if (!container) {
        return null;
    }
    if (container.dataset.bpVue3Mounted === '1') {
        return null;
    }

    const app = createBookingFormApp(instance);
    const vm  = app.mount(container);

    container.dataset.bpVue3Mounted = '1';
    return { app, vm, container, instance };
}

export default {
    createBookingFormApp,
    mountBookingFormInstance,
};

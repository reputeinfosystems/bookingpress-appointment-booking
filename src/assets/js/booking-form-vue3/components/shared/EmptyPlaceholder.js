/**
 * EmptyPlaceholder — the "blocked form" empty illustration.
 *
 * Legacy parity port of the released `#bpa-front-data-empty-view` block
 * (see `core/views/frontend/appointment_booking_form.php`). The DOM and class
 * names are byte-for-byte identical so the empty-view CSS already shipped in
 * `src/assets/css/booking-form.css` (the `.bpa-frontend-vue3
 * #bpa-front-data-empty-view` rules) applies unchanged — no new styling.
 *
 * It renders only when the root flips it on (`state.config.showEmptyPlaceholder`,
 * the Vue 3 analog of the legacy `bookingpress_display_no_service_placeholder`).
 * Lite never sets that flag, so this component is dormant on a Lite-only render.
 * A Pro feature flips the flag via `FILTER_INITIAL_STATE` — e.g. the Staff
 * Member module, which blocks the form when it is active but no staff exists.
 *
 * The title copy is data-driven from `state.strings.no_categories_services`
 * (default "No categories and services added!"), matching the legacy message
 * for this view.
 */
import { computed, inject } from 'vue';

export default {
  name: 'EmptyPlaceholder',
  setup() {
    const state = inject('state');

    // Same default the backend uses (StateBuilder::compose_strings →
    // `no_categories_services`). Guard in case strings is missing.
    const title = computed(() => {
      const s = (state && state.strings) || {};
      return s.no_categories_services || 'No categories and services added!';
    });

    return { title };
  },
  // The SVG is the released illustration verbatim — purely decorative, so it
  // stays inline (no v-html, no escaping concerns) and the title `<div>` is a
  // sibling exactly as in the legacy markup.
  template: `
    <div id="bpa-front-data-empty-view" class="bpa-front-data-empty-view __bpa-is-guest-view">
      <svg viewBox="0 0 120 121" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M108.486 103.08C101.042 112.139 86.7296 109.719 75.3575 112.572C64.3105 115.344 53.4388 123.192 42.6284 119.606C31.8342 116.026 27.8283 103.242 20.6455 94.4249C13.5882 85.7617 2.04617 79.3615 0.797986 68.2575C-0.448903 57.1649 8.61128 47.9531 14.3452 38.376C19.5416 29.6967 24.6347 21.093 32.6953 14.9808C41.3289 8.43396 51.0768 2.35675 61.9118 2.30667C72.8285 2.25621 82.086 9.1904 91.5052 14.709C101.484 20.5552 114.441 24.5839 118.451 35.4317C122.456 46.2671 113.129 57.2263 111.445 68.6549C109.732 80.2849 115.949 93.9976 108.486 103.08Z" class="bpa-front-dev__panel-bg"/>
        <g filter="url(#filter0_d_4344_13430)">
          <rect x="16.3105" y="27.8936" width="95.3718" height="22.2173" rx="11.1086" class="bpa-front-dev__form-bg"/>
        </g>
        <circle cx="27.1474" cy="39.0009" r="5.41885" class="bpa-front-dev__primary-bg"/>
        <rect x="37.9863" y="39.542" width="41.1833" height="2.16754" rx="1.08377" fill="#F4F7FB"/>
        <rect x="37.9863" y="36.0215" width="13.5471" height="2.16754" rx="1.08377" fill="#F4F7FB"/>
        <rect x="53.4297" y="36.0215" width="25.7395" height="2.16754" rx="1.08377" fill="#F4F7FB"/>
        <rect x="84.5859" y="34.9375" width="21.6754" height="8.12828" rx="4" fill="#F4F7FB"/>
        <g filter="url(#filter1_d_4344_13430)">
          <rect x="16.3105" y="54.1748" width="95.3718" height="22.2173" rx="11.1086" class="bpa-front-dev__form-bg"/>
        </g>
        <circle cx="27.1474" cy="65.2831" r="5.41885" fill="#E8ECF5"/>
        <rect x="37.9863" y="65.8252" width="41.1833" height="2.16754" rx="1.08377" fill="#E8ECF5"/>
        <rect x="37.9863" y="62.3037" width="13.5471" height="2.16754" rx="1.08377" fill="#DDE1ED"/>
        <rect x="53.4297" y="62.3037" width="25.7395" height="2.16754" rx="1.08377" fill="#E8ECF5"/>
        <rect x="84.5859" y="61.2197" width="21.6754" height="8.12828" rx="4" fill="#F4F7FB"/>
        <g filter="url(#filter2_d_4344_13430)">
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
          <filter id="filter0_d_4344_13430" x="8.31055" y="21.8936" width="111.372" height="38.2173" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="2"/>
            <feGaussianBlur stdDeviation="4"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.129412 0 0 0 0 0.403922 0 0 0 0 0.945098 0 0 0 0.1 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_4344_13430"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_4344_13430" result="shape"/>
          </filter>
          <filter id="filter1_d_4344_13430" x="8.31055" y="48.1748" width="111.372" height="38.2173" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="2"/>
            <feGaussianBlur stdDeviation="4"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.129412 0 0 0 0 0.403922 0 0 0 0 0.945098 0 0 0 0.1 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_4344_13430"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_4344_13430" result="shape"/>
          </filter>
          <filter id="filter2_d_4344_13430" x="8.31055" y="74.4541" width="111.372" height="38.2173" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="2"/>
            <feGaussianBlur stdDeviation="4"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.129412 0 0 0 0 0.403922 0 0 0 0 0.945098 0 0 0 0.1 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_4344_13430"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_4344_13430" result="shape"/>
          </filter>
        </defs>
      </svg>
      <div class="bpa-front-dev__title">{{ title }}</div>
    </div>
  `,
};
